<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventParticipant;
use App\Models\User;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ParticipantController extends Controller
{
    /**
     * Display a paginated list of publicly accessible events for the portal.
     */
    public function index(): Response
    {
        /** @var User $user */
        $user = Auth::user();

        $registeredEventIds = EventParticipant::query()
            ->where('user_id', $user->id)
            ->pluck('event_id');

        $events = Event::query()
            ->where(function ($query) {
                $query->where('registration_type', 'public')
                    ->where('end_time', '>=', now());
            })
            ->orWhereHas('eventParticipants', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->withCount('eventParticipants')
            ->latest('start_time')
            ->paginate(12)
            ->through(fn (Event $event) => [
                'id' => $event->id,
                'title' => $event->title,
                'description' => $event->description,
                'start_time' => $event->start_time,
                'end_time' => $event->end_time,
                'attendance_type' => $event->attendance_type,
                'participants_count' => $event->event_participants_count,
                'is_registered' => $registeredEventIds->contains($event->id),
                'certificate_enabled' => $event->certificate_enabled,
                'status' => $event->end_time < now() ? 'completed' : ($event->start_time <= now() ? 'ongoing' : 'upcoming'),
            ]);

        return Inertia::render('Portal/Events', [
            'events' => $events,
        ]);
    }

    /**
     * Register the authenticated user as a participant for the given event.
     */
    public function register(Request $request, Event $event): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if ($event->registration_type !== 'public') {
            return back()->with('error', 'This event is not open for public registration.');
        }

        $alreadyRegistered = EventParticipant::query()
            ->where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->exists();

        if ($alreadyRegistered) {
            return back()->with('error', 'You are already registered for this event.');
        }

        $participant = EventParticipant::create([
            'event_id' => $event->id,
            'user_id' => $user->id,
            'status' => 'registered',
        ]);

        $this->generateQrCode($participant);

        return redirect()->route('portal.qr', $event)
            ->with('success', 'You have successfully registered for this event!');
    }

    /**
     * Show the participant's QR code for the given event.
     */
    public function qrCode(Event $event): Response|RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $participant = EventParticipant::query()
            ->where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->first();

        if ($participant === null) {
            return redirect()->route('portal.events')
                ->with('error', 'You are not registered for this event.');
        }

        return Inertia::render('Portal/QrCode', [
            'event' => [
                'id' => $event->id,
                'title' => $event->title,
                'start_time' => $event->start_time,
                'end_time' => $event->end_time,
            ],
            'participant' => [
                'id' => $participant->id,
                'status' => $participant->status,
                'qr_code_url' => $participant->qr_code_path
                    ? Storage::url(str_replace('public/', '', $participant->qr_code_path))
                    : null,
            ],
        ]);
    }

    /**
     * Generate a QR code SVG for the given participant and store it.
     */
    private function generateQrCode(EventParticipant $participant): void
    {
        $token = Str::random(40);

        $participant->update(['qr_token' => $token]);

        $scanUrl = route('attendance.scan', ['event' => $participant->event_id])
            .'?token='.$token;

        $renderer = new ImageRenderer(
            new RendererStyle(300),
            new SvgImageBackEnd
        );

        $writer = new Writer($renderer);
        $svgContent = $writer->writeString($scanUrl);

        $relativePath = 'qrcodes/event_'.$participant->event_id.'_participant_'.$participant->id.'.svg';

        Storage::disk('public')->put($relativePath, $svgContent);

        $participant->update(['qr_code_path' => 'public/'.$relativePath]);
    }
}
