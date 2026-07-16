<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Event;
use App\Models\EventParticipant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AttendanceController extends Controller
{
    /**
     * Display the attendance scanner interface.
     */
    public function index(): Response
    {
        $events = Event::query()
            ->where('end_time', '>=', now())
            ->orderBy('start_time', 'asc')
            ->get(['id', 'title', 'start_time', 'end_time', 'attendance_type']);

        return Inertia::render('Attendances/Index', [
            'events' => $events,
        ]);
    }

    /**
     * Process a global QR code scan without a specific event bound to the route.
     */
    public function globalScan(Request $request): JsonResponse
    {
        $request->validate([
            'qr_token' => ['required', 'string'],
        ]);

        $token = $request->string('qr_token')->toString();

        $participant = EventParticipant::query()
            ->with(['event', 'user'])
            ->where('qr_token', $token)
            ->first();

        if ($participant === null) {
            return response()->json([
                'status' => 'invalid',
                'message' => 'Invalid QR code or participant not registered.',
            ], 422);
        }

        return $this->processScanRecord($participant->event, $participant);
    }

    /**
     * Process a QR code scan to record attendance.
     *
     * Returns JSON with status: success | already_scanned | invalid
     */
    public function scan(Request $request, Event $event): JsonResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
        ]);

        $token = $request->string('token')->toString();

        $participant = EventParticipant::query()
            ->where('event_id', $event->id)
            ->where('qr_token', $token)
            ->first();

        if ($participant === null) {
            return response()->json([
                'status' => 'invalid',
                'message' => 'Invalid QR code or participant not registered for this event.',
            ], 422);
        }

        return $this->processScanRecord($event, $participant);
    }

    private function processScanRecord(Event $event, EventParticipant $participant): JsonResponse
    {
        $session = $event->sessions()
            ->where('start_time', '<=', now()->addMinutes(30))
            ->where('end_time', '>=', now()->subMinutes(30))
            ->first();

        if (! $session) {
            $session = $event->sessions()->orderBy('start_time')->first();
        }

        if (! $session) {
            return response()->json([
                'status' => 'invalid',
                'message' => 'No active sessions found for this event.',
            ], 422);
        }

        $alreadyScannedIn = Attendance::query()
            ->where('event_id', $event->id)
            ->where('user_id', $participant->user_id)
            ->where('event_session_id', $session->id)
            ->where('type', 'check_in')
            ->exists();

        if ($alreadyScannedIn) {
            if (! $session->requires_checkout) {
                return response()->json([
                    'status' => 'already_scanned',
                    'message' => 'Attendance already recorded for this session.',
                    'participant_name' => $participant->user->name,
                    'event_title' => $event->title,
                    'data' => [
                        'type' => 'check_in',
                        'scanned_at' => now()->toIso8601String(),
                    ],
                ]);
            }

            $alreadyScannedOut = Attendance::query()
                ->where('event_id', $event->id)
                ->where('user_id', $participant->user_id)
                ->where('event_session_id', $session->id)
                ->where('type', 'check_out')
                ->exists();

            if ($alreadyScannedOut) {
                return response()->json([
                    'status' => 'already_scanned',
                    'message' => 'Both check-in and check-out recorded for this session.',
                    'participant_name' => $participant->user->name,
                    'event_title' => $event->title,
                    'data' => [
                        'type' => 'check_out',
                        'scanned_at' => now()->toIso8601String(),
                    ],
                ]);
            }

            $attendance = Attendance::create([
                'event_id' => $event->id,
                'user_id' => $participant->user_id,
                'event_session_id' => $session->id,
                'type' => 'check_out',
                'scanned_at' => now(),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Check-out recorded successfully.',
                'participant_name' => $participant->user->name,
                'event_title' => $event->title,
                'scanned_at' => now()->toDateTimeString(),
                'data' => $attendance,
            ]);
        }

        $attendance = Attendance::create([
            'event_id' => $event->id,
            'user_id' => $participant->user_id,
            'event_session_id' => $session->id,
            'type' => 'check_in',
            'scanned_at' => now(),
        ]);

        $participant->update(['status' => 'attended']);

        return response()->json([
            'status' => 'success',
            'message' => 'Check-in recorded successfully.',
            'participant_name' => $participant->user->name,
            'event_title' => $event->title,
            'scanned_at' => now()->toDateTimeString(),
            'data' => $attendance,
        ]);
    }
}
