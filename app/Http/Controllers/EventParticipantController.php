<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventParticipant;
use App\Models\User;
use App\Services\QrCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
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
                'qr_code_path' => $ep->qr_code_path
                    ? Storage::url($ep->qr_code_path)
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

        $this->qrCodeService->generateFor($participant);

        return back()->with('success', 'Participant added successfully.');
    }

    /**
     * Remove a participant from the event.
     */
    public function destroy(Event $event, EventParticipant $eventParticipant): RedirectResponse
    {
        if ($eventParticipant->qr_code_path) {
            Storage::delete($eventParticipant->qr_code_path);
        }

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

            $this->qrCodeService->generateFor($participant);
            $imported++;
        }

        fclose($handle);

        return back()->with('success', "CSV imported: {$imported} participants added, {$skipped} skipped.");
    }
}
