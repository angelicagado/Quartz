<?php

namespace App\Http\Controllers;

use App\Models\Event;
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
            ->with('organizer:id,name')
            ->where('end_time', '>=', now())
            ->orderBy('start_time', 'asc')
            ->get()
            ->map(fn (Event $event) => [
                'id' => $event->id,
                'title' => $event->title,
                'description' => $event->description,
                'start_time' => $event->start_time,
                'end_time' => $event->end_time,
                'registration_start_date' => $event->registration_start_date,
                'registration_end_date' => $event->registration_end_date,
                'organizer' => $event->organizer ? [
                    'name' => $event->organizer->name,
                ] : null,
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
        $event->load('organizer:id,name');

        return Inertia::render('Public/Events/Show', [
            'event' => [
                'id' => $event->id,
                'title' => $event->title,
                'description' => $event->description,
                'start_time' => $event->start_time,
                'end_time' => $event->end_time,
                'registration_start_date' => $event->registration_start_date,
                'registration_end_date' => $event->registration_end_date,
                'registration_type' => $event->registration_type,
                'organizer' => $event->organizer ? [
                    'name' => $event->organizer->name,
                ] : null,
            ],
        ]);
    }
}
