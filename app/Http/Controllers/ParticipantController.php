<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\EvaluationResponse;
use App\Models\Event;
use App\Models\EventParticipant;
use App\Models\User;
use App\Services\QrCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ParticipantController extends Controller
{
    public function __construct(private QrCodeService $qrCodeService) {}

    /**
     * Display a paginated list of publicly accessible events for the portal.
     */
    public function index(Request $request): Response
    {
        /** @var User $user */
        $user = Auth::user();

        $search = $request->string('search')->toString();

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
            ->withQueryString()
            ->through(fn(Event $event) => [
                'id' => $event->id,
                'title' => $event->title,
                'description' => $event->description,
                'start_time' => $event->start_time,
                'end_time' => $event->end_time,
                'status' => $this->eventStatus($event),
                'registration_type' => $event->registration_type,
                'participants_count' => $event->event_participants_count,
                'is_registered' => $registeredEventIds->contains($event->id),
                'certificate_enabled' => $event->certificate_enabled,
                'status' => $event->end_time < now() ? 'completed' : ($event->start_time <= now() ? 'ongoing' : 'upcoming'),
            ]);

        return Inertia::render('Portal/Events', [
            'events' => $events,
            'filters' => ['search' => $search],
        ]);
    }

    /**
     * Derive a display status for an event from its schedule.
     */
    private function eventStatus(Event $event): string
    {
        if ($event->end_time->isPast()) {
            return 'completed';
        }

        if ($event->start_time->isPast()) {
            return 'ongoing';
        }

        return 'upcoming';
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

        $this->qrCodeService->generateFor($participant);

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
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email,
                ],
            ],
        ]);
    }

    /**
     * Display the authenticated participant's registered events with per-event next actions.
     */
    public function myEvents(): Response
    {
        /** @var User $user */
        $user = Auth::user();

        $registrations = EventParticipant::query()
            ->where('user_id', $user->id)
            ->with(['event.evaluationForm.questions'])
            ->latest()
            ->get();

        $events = $registrations->map(function (EventParticipant $participant) use ($user) {
            $event = $participant->event;

            $evaluationRequired = $event->evaluation_required;
            $form = $event->evaluationForm;
            $evaluationAvailable = $form !== null && $form->questions->isNotEmpty();

            $evaluationSubmitted = false;
            if ($evaluationAvailable) {
                $evaluationSubmitted = EvaluationResponse::query()
                    ->whereIn('evaluation_question_id', $form->questions->pluck('id'))
                    ->where('user_id', $user->id)
                    ->exists();
            }

            $certificateAvailable = $event->certificate_enabled
                && $participant->status !== 'registered'
                && (! $evaluationRequired || $evaluationSubmitted);

            return [
                'id' => $event->id,
                'title' => $event->title,
                'description' => $event->description,
                'start_time' => $event->start_time,
                'end_time' => $event->end_time,
                'attendance_type' => $event->attendance_type,
                'status' => $participant->status,
                'has_qr' => $participant->qr_code_path !== null,
                'evaluation_required' => $evaluationRequired,
                'evaluation_available' => $evaluationAvailable,
                'evaluation_submitted' => $evaluationSubmitted,
                'certificate_available' => $certificateAvailable,
            ];
        });

        return Inertia::render('Portal/MyEvents', [
            'events' => $events,
        ]);
    }

    /**
     * List the certificates the authenticated participant has earned.
     */
    public function certificates(): Response
    {
        /** @var User $user */
        $user = Auth::user();

        $certificates = Certificate::query()
            ->where('user_id', $user->id)
            ->with('event:id,title,start_time')
            ->latest('issue_date')
            ->get()
            ->map(fn(Certificate $certificate) => [
                'id' => $certificate->id,
                'certificate_number' => $certificate->certificate_number,
                'issue_date' => $certificate->issue_date,
                'event' => [
                    'id' => $certificate->event->id,
                    'title' => $certificate->event->title,
                    'start_time' => $certificate->event->start_time,
                ],
                'download_url' => route('portal.certificate.download', $certificate->event_id),
            ]);

        return Inertia::render('Portal/Certificates', [
            'certificates' => $certificates,
        ]);
    }
}
