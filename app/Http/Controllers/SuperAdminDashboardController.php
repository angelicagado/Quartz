<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Event;
use App\Models\EventParticipant;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class SuperAdminDashboardController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('SuperAdminDashboard', [
            'stats' => [
                'upcomingEvents' => Event::query()->where('start_time', '>', now())->count(),
                'pendingApprovals' => EventParticipant::query()->where('status', 'registered')->count(),
                'totalEvents' => Event::query()->count(),
            ],
            'recentScans' => $this->recentScans(),
            'lineData' => $this->monthlyGrowth(),
            'barData' => $this->eventDistribution(),
        ]);
    }

    /**
     * The most recent attendance scans across all events.
     *
     * @return array<int, array{id: int, scan_type: string, scanned_at: Carbon, participant: array{name: string}, event: array{title: string}}>
     */
    private function recentScans(): array
    {
        return Attendance::query()
            ->with(['user:id,name', 'event:id,title'])
            ->latest('scanned_at')
            ->take(10)
            ->get()
            ->map(fn (Attendance $scan): array => [
                'id' => $scan->id,
                'scan_type' => $scan->scan_type,
                'scanned_at' => $scan->scanned_at,
                'participant' => [
                    'name' => $scan->user?->name ?? 'Guest',
                ],
                'event' => [
                    'title' => $scan->event?->title ?? '',
                ],
            ])
            ->all();
    }

    /**
     * Events created and participants registered per month for the last 6 months.
     *
     * @return array<int, array{name: string, events: int, participants: int}>
     */
    private function monthlyGrowth(): array
    {
        return collect(range(5, 0))
            ->map(function (int $monthsAgo): array {
                $month = now()->subMonths($monthsAgo);
                $start = $month->copy()->startOfMonth();
                $end = $month->copy()->endOfMonth();

                return [
                    'name' => $month->format('M'),
                    'events' => Event::query()->whereBetween('created_at', [$start, $end])->count(),
                    'participants' => EventParticipant::query()->whereBetween('created_at', [$start, $end])->count(),
                ];
            })
            ->all();
    }

    /**
     * Event counts grouped by attendance type.
     *
     * @return array<int, array{name: string, value: int}>
     */
    private function eventDistribution(): array
    {
        $labels = [
            'one-time' => 'One-time',
            'am-pm' => 'AM / PM',
        ];

        return Event::query()
            ->selectRaw('attendance_type, COUNT(*) as total')
            ->groupBy('attendance_type')
            ->pluck('total', 'attendance_type')
            ->map(fn (int $total, string $type): array => [
                'name' => $labels[$type] ?? $type,
                'value' => $total,
            ])
            ->values()
            ->all();
    }
}
