<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Event;
use App\Models\EventParticipant;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    /**
     * Display the reports overview with per-event statistics.
     */
    public function index(): Response
    {
        $events = Event::query()
            ->with('organizers:id,name')
            ->withCount([
                'eventParticipants',
                'attendances',
                'eventParticipants as completed_count' => function ($query): void {
                    $query->where('status', 'completed');
                },
                'eventParticipants as attended_count' => function ($query): void {
                    $query->where('status', 'attended');
                },
            ])
            ->latest('start_time')
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
                'organizers' => $event->organizers->map(fn ($o) => [
                    'id' => $o->id,
                    'name' => $o->name,
                ]),
                'stats' => [
                    'registered' => $event->event_participants_count,
                    'attended' => $event->attended_count,
                    'completed' => $event->completed_count,
                    'attendance_records' => $event->attendances_count,
                    'attendance_rate' => $event->event_participants_count > 0
                        ? round(($event->attended_count / $event->event_participants_count) * 100, 1)
                        : 0,
                    'completion_rate' => $event->event_participants_count > 0
                        ? round(($event->completed_count / $event->event_participants_count) * 100, 1)
                        : 0,
                ],
            ]);

        $totals = [
            'total_events' => $events->count(),
            'total_participants' => EventParticipant::query()->distinct('user_id')->count(),
            'total_attendances' => Attendance::query()->count(),
            'total_completed' => EventParticipant::query()->where('status', 'completed')->count(),
        ];

        return Inertia::render('Reports/Index', [
            'events' => $events,
            'totals' => $totals,
        ]);
    }
}
