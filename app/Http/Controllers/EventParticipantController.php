<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\Event;
use App\Models\EventParticipant;
use App\Models\User;
use App\Services\QrCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class EventParticipantController extends Controller
{
    public function __construct(private QrCodeService $qrCodeService) {}

    /**
     * Display a list of participants for the specified event.
     */
    public function index(Event $event): Response
    {
        $participants = $event->eventParticipants()
            ->with('user:id,name,email')
            ->latest()
            ->get()
            ->map(fn (EventParticipant $ep) => [
                'id' => $ep->id,
                'status' => $ep->status,
                'qr_code_url' => $ep->qr_token
                    ? route('events.participants.qr', [$event, $ep])
                    : null,
                'user' => $ep->user,
                'created_at' => $ep->created_at,
            ]);

        return Inertia::render('Events/Participants', [
            'event' => [
                'id' => $event->id,
                'title' => $event->title,
                'registration_type' => $event->registration_type,
            ],
            'participants' => $participants,
        ]);
    }

    /**
     * Stream a participant's QR code for the event as an SVG.
     */
    public function qrImage(Event $event, EventParticipant $participant): HttpResponse|RedirectResponse
    {
        if ($participant->event_id !== $event->id || $participant->qr_token === null) {
            return back()->with('error', 'No QR code is available for this participant.');
        }

        return response($this->qrCodeService->svgFor($participant))
            ->header('Content-Type', 'image/svg+xml');
    }

    /**
     * Add a participant manually to the event.
     */
    public function store(Request $request, Event $event): RedirectResponse
    {
        $request->validate([
            'user_id' => ['nullable', 'exists:users,id'],
            'name' => ['required_without:user_id', 'nullable', 'string', 'max:255'],
            'email' => ['required_without:user_id', 'nullable', 'email'],
        ]);

        if ($request->filled('user_id')) {
            $user = User::findOrFail($request->integer('user_id'));
        } else {
            /** @var User $user */
            $user = User::firstOrCreate(
                ['email' => $request->string('email')->toString()],
                [
                    'name' => $request->string('name')->toString(),
                    'password' => Hash::make(Str::random(16)),
                ]
            );
            $user->assignRole('participant');
        }

        $alreadyRegistered = $event->eventParticipants()
            ->where('user_id', $user->id)
            ->exists();

        if ($alreadyRegistered) {
            return back()->with('error', 'User is already registered for this event.');
        }

        $participant = $event->eventParticipants()->create([
            'user_id' => $user->id,
            'status' => 'registered',
        ]);

        $this->qrCodeService->ensureTokenFor($participant);

        return back()->with('success', 'Participant added successfully.');
    }

    /**
     * Update participant status.
     */
    public function update(Request $request, Event $event, EventParticipant $participant): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:registered,confirmed,attended,cancelled'],
        ]);

        $participant->update($validated);

        return back()->with('success', 'Participant status updated successfully.');
    }

    /**
     * Remove a participant from the event.
     */
    public function destroy(Event $event, EventParticipant $eventParticipant): RedirectResponse
    {
        $eventParticipant->delete();

        return back()->with('success', 'Participant removed successfully.');
    }

    /**
     * Handle CSV upload of participant list.
     */
    public function uploadCsv(Request $request, Event $event): RedirectResponse
    {
        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);

        $file = $request->file('csv_file');

        if ($file === null) {
            return back()->with('error', 'No file uploaded.');
        }

        $path = $file->getRealPath();
        $handle = fopen($path, 'r');

        if ($handle === false) {
            return back()->with('error', 'Could not read CSV file.');
        }

        $header = fgetcsv($handle);
        $imported = 0;
        $skipped = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if ($header === false || $header === null || count($row) < 2) {
                continue;
            }

            $data = array_combine($header, $row);

            if ($data === false) {
                continue;
            }

            $name = trim($data['name'] ?? $data[0] ?? '');
            $email = trim($data['email'] ?? $data[1] ?? '');

            if (empty($name) || empty($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $skipped++;

                continue;
            }

            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => Hash::make(Str::random(16)),
                ]
            );

            if (! $user->hasRole('participant')) {
                $user->assignRole('participant');
            }

            $alreadyRegistered = $event->eventParticipants()
                ->where('user_id', $user->id)
                ->exists();

            if ($alreadyRegistered) {
                $skipped++;

                continue;
            }

            $participant = $event->eventParticipants()->create([
                'user_id' => $user->id,
                'status' => 'registered',
            ]);

            $this->qrCodeService->ensureTokenFor($participant);
            $imported++;
        }

        fclose($handle);

        return back()->with('success', "CSV imported: {$imported} participants added, {$skipped} skipped.");
    }

    /**
     * Manually issue a certificate to a participant.
     */
    public function issueCertificate(Event $event, User $user): RedirectResponse
    {
        if (! $event->certificate_enabled) {
            return back()->with('error', 'Certificates are not enabled for this event.');
        }

        if ($event->certificateTemplate === null) {
            return back()->with('error', 'No certificate template has been configured.');
        }

        $participant = $event->eventParticipants()->where('user_id', $user->id)->first();

        if ($participant === null) {
            return back()->with('error', 'User is not registered for this event.');
        }

        // Check if certificate already exists
        $existing = Certificate::where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing) {
            return back()->with('error', 'Participant has already been issued a certificate.');
        }

        Certificate::create([
            'event_id' => $event->id,
            'user_id' => $user->id,
            'certificate_number' => 'CERT-'.$event->id.'-'.$user->id.'-'.strtoupper(Str::random(5)),
            'issue_date' => now(),
        ]);

        return back()->with('success', 'Certificate issued successfully.');
    }
}
