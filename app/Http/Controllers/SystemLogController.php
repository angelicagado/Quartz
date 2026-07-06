<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Inertia\Inertia;
use Inertia\Response;

class SystemLogController extends Controller
{
    /**
     * Display a listing of the system logs.
     */
    public function index(): Response
    {
        $logs = Attendance::query()
            ->with(['user', 'event'])
            ->latest('scanned_at')
            ->paginate(50)
            ->through(fn (Attendance $log) => [
                'id' => $log->id,
                'scan_type' => $log->scan_type,
                'scanned_at' => $log->scanned_at->toIso8601String(),
                'participant' => [
                    'name' => $log->user ? $log->user->name : 'Unknown',
                    'user' => [
                        'name' => $log->user ? $log->user->name : 'Unknown',
                    ],
                    'event' => [
                        'title' => $log->event ? $log->event->title : 'Deleted Event',
                    ],
                ],
                'scanner' => [
                    'name' => 'System Operator',
                ],
            ]);

        return Inertia::render('SuperAdmin/Logs', [
            'logs' => $logs,
        ]);
    }
}
