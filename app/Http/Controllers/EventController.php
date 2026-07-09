<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class EventController extends Controller
{
    /**
     * Display a paginated list of events with their organizer.
     */
    public function index(): Response
    {
        $events = Event::query()
            ->with('organizer')
            ->withCount(['eventParticipants', 'attendances'])
            ->latest()
            ->get()
            ->map(fn (Event $event) => [
                'id' => $event->id,
                'title' => $event->title,
                'start_time' => $event->start_time,
                'end_time' => $event->end_time,
                'registration_start_date' => $event->registration_start_date,
                'registration_end_date' => $event->registration_end_date,
                'registration_type' => $event->registration_type,
                'evaluation_required' => $event->evaluation_required,
                'certificate_enabled' => $event->certificate_enabled,
                'organizer' => $event->organizer ? [
                    'id' => $event->organizer->id,
                    'name' => $event->organizer->name,
                ] : null,
                'participants_count' => $event->event_participants_count,
                'attendances_count' => $event->attendances_count,
            ]);

        return Inertia::render('Events/Index', [
            'events' => $events,
        ]);
    }

    /**
     * Show the form for creating a new event.
     */
    public function create(): Response
    {
        $organizers = User::role('event_organizer')
            ->select(['id', 'name', 'email'])
            ->orderBy('name')
            ->get();

        return Inertia::render('Events/Create', [
            'organizers' => $organizers,
        ]);
    }

    /**
     * Store a newly created event in storage.
     */
    public function store(StoreEventRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $sessions = $validated['sessions'];
        unset($validated['sessions']);

        $event = Event::create($validated);

        $event->sessions()->createMany($sessions);

        return redirect()->route('events.show', $event)
            ->with('success', 'Event created successfully.');
    }

    /**
     * Display the specified event with participants and attendance counts.
     */
    public function show(Event $event): Response
    {
        $event->load([
            'organizer:id,name,email',
            'eventParticipants.user:id,name,email',
            'evaluationForm.questions',
            'certificateTemplate',
            'sessions',
        ]);

        $event->loadCount(['eventParticipants', 'attendances']);

        return Inertia::render('Events/Show', [
            'event' => [
                'id' => $event->id,
                'title' => $event->title,
                'description' => $event->description,
                'start_time' => $event->start_time,
                'end_time' => $event->end_time,
                'registration_start_date' => $event->registration_start_date,
                'registration_end_date' => $event->registration_end_date,
                'registration_type' => $event->registration_type,
                'evaluation_required' => $event->evaluation_required,
                'certificate_enabled' => $event->certificate_enabled,
                'organizer' => $event->organizer,
                'participants_count' => $event->event_participants_count,
                'attendances_count' => $event->attendances_count,
                'has_evaluation_form' => $event->evaluationForm !== null,
                'evaluation_form' => $event->evaluationForm,
                'has_certificate_template' => $event->certificateTemplate !== null,
                'certificate_template' => $event->certificateTemplate,
                'sessions' => $event->sessions,
            ],
        ]);
    }

    /**
     * Show the form for editing the specified event.
     */
    public function edit(Event $event): Response
    {
        $event->load('sessions');

        $organizers = User::role('event_organizer')
            ->select(['id', 'name', 'email'])
            ->orderBy('name')
            ->get();

        return Inertia::render('Events/Edit', [
            'event' => [
                'id' => $event->id,
                'title' => $event->title,
                'description' => $event->description,
                'start_time' => $event->start_time,
                'end_time' => $event->end_time,
                'registration_start_date' => $event->registration_start_date,
                'registration_end_date' => $event->registration_end_date,
                'registration_type' => $event->registration_type,
                'evaluation_required' => $event->evaluation_required,
                'certificate_enabled' => $event->certificate_enabled,
                'organizer_id' => $event->organizer_id,
                'sessions' => $event->sessions,
            ],
            'organizers' => $organizers,
        ]);
    }

    /**
     * Update the specified event in storage.
     */
    public function update(UpdateEventRequest $request, Event $event): RedirectResponse
    {
        $validated = $request->validated();
        $sessionsData = $validated['sessions'];
        unset($validated['sessions']);

        $event->update($validated);

        // Update sessions (delete removed, update existing, create new)
        $existingSessionIds = $event->sessions()->pluck('id')->toArray();
        $updatedSessionIds = [];

        foreach ($sessionsData as $sessionData) {
            if (isset($sessionData['id']) && in_array($sessionData['id'], $existingSessionIds)) {
                // Update existing
                $event->sessions()->where('id', $sessionData['id'])->update($sessionData);
                $updatedSessionIds[] = $sessionData['id'];
            } else {
                // Create new
                $newSession = $event->sessions()->create($sessionData);
                $updatedSessionIds[] = $newSession->id;
            }
        }

        // Delete sessions that were removed
        $sessionsToDelete = array_diff($existingSessionIds, $updatedSessionIds);
        if (!empty($sessionsToDelete)) {
            $event->sessions()->whereIn('id', $sessionsToDelete)->delete();
        }

        return redirect()->route('events.show', $event)
            ->with('success', 'Event updated successfully.');
    }

    /**
     * Remove the specified event from storage.
     */
    public function destroy(Event $event): RedirectResponse
    {
        $event->delete();

        return redirect()->route('events.index')
            ->with('success', 'Event deleted successfully.');
    }

    /**
     * Display events managed by the currently authenticated organizer.
     */
    public function myEvents(Request $request): Response
    {
        /** @var User $user */
        $user = Auth::user();

        $events = Event::query()
            ->where('organizer_id', $user->id)
            ->withCount(['eventParticipants', 'attendances'])
            ->latest()
            ->get()
            ->map(fn (Event $event) => [
                'id' => $event->id,
                'title' => $event->title,
                'start_time' => $event->start_time,
                'end_time' => $event->end_time,
                'registration_type' => $event->registration_type,
                'attendance_type' => $event->attendance_type,
                'evaluation_required' => $event->evaluation_required,
                'certificate_enabled' => $event->certificate_enabled,
                'participants_count' => $event->event_participants_count,
                'attendances_count' => $event->attendances_count,
            ]);

        return Inertia::render('Events/MyEvents', [
            'events' => $events,
        ]);
    }

    /**
     * Display the monitoring dashboard for a specific event.
     */
    public function monitor(Event $event): Response
    {
        $event->load([
            'organizer:id,name,email',
            'eventParticipants.user:id,name,email',
            'attendances.user:id,name,email',
            'evaluationForm.questions',
        ]);

        $event->loadCount(['eventParticipants', 'attendances']);

        $attendedUserIds = $event->attendances->pluck('user_id')->unique()->values();

        return Inertia::render('Events/Monitor', [
            'event' => [
                'id' => $event->id,
                'title' => $event->title,
                'description' => $event->description,
                'start_time' => $event->start_time,
                'end_time' => $event->end_time,
                'attendance_type' => $event->attendance_type,
                'evaluation_required' => $event->evaluation_required,
                'certificate_enabled' => $event->certificate_enabled,
                'organizer' => $event->organizer,
                'participants_count' => $event->event_participants_count,
                'attendances_count' => $event->attendances_count,
                'attended_count' => $attendedUserIds->count(),
                'participants' => $event->eventParticipants->map(fn ($ep) => [
                    'id' => $ep->id,
                    'status' => $ep->status,
                    'user' => $ep->user,
                    'has_attended' => $attendedUserIds->contains($ep->user_id),
                ]),
                'recent_attendances' => $event->attendances
                    ->sortByDesc('scanned_at')
                    ->take(20)
                    ->values()
                    ->map(fn ($a) => [
                        'id' => $a->id,
                        'scan_type' => $a->scan_type,
                        'scanned_at' => $a->scanned_at,
                        'user' => $a->user,
                    ]),
                'has_evaluation_form' => $event->evaluationForm !== null,
            ],
        ]);
    }
}
