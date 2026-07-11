<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventParticipant;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class PublicEventController extends Controller
{
    /**
     * Display a paginated list of upcoming and ongoing events for guest users.
     */
    public function index(): Response
    {
        $events = Event::query()
            ->with('organizers:id,name')
            ->where('end_time', '>=', now())
            ->orderBy('start_time', 'asc')
            ->get()
            ->map(fn (Event $event) => [
                'id' => $event->id,
                'title' => $event->title,
                'description' => $event->description,
                'address' => $event->address,
                'start_time' => $event->start_time,
                'end_time' => $event->end_time,
                'registration_start_date' => $event->registration_start_date,
                'registration_end_date' => $event->registration_end_date,
                'organizers' => $event->organizers->map(fn ($o) => [
                    'name' => $o->name,
                ]),
            ]);

        return Inertia::render('Public/Events/Index', [
            'events' => $events,
        ]);
    }

    /**
     * Display the specified event.
     */
    public function show(Event $event): Response
    {
        $event->load('organizers:id,name');

        $participant = null;
        if (Auth::check()) {
            $participant = EventParticipant::query()
                ->where('event_id', $event->id)
                ->where('user_id', Auth::id())
                ->first();
        }

        return Inertia::render('Public/Events/Show', [
            'event' => [
                'id' => $event->id,
                'title' => $event->title,
                'description' => $event->description,
                'address' => $event->address,
                'start_time' => $event->start_time,
                'end_time' => $event->end_time,
                'registration_start_date' => $event->registration_start_date,
                'registration_end_date' => $event->registration_end_date,
                'registration_type' => $event->registration_type,
                'attendance_type' => $event->attendance_type,
                'certificate_enabled' => $event->certificate_enabled,
                'evaluation_required' => $event->evaluation_required,
                'is_registered' => $participant !== null,
                'qr_code_url' => $participant?->qr_code_url,
                'organizers' => $event->organizers->map(fn ($o) => [
                    'name' => $o->name,
                ]),
            ],
        ]);
    }
}
