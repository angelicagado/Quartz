<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Event;
use App\Models\EventParticipant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
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

        if ($event->attendance_type === 'one-time') {
            $alreadyScanned = Attendance::query()
                ->where('event_id', $event->id)
                ->where('user_id', $participant->user_id)
                ->exists();

            if ($alreadyScanned) {
                return response()->json([
                    'status' => 'already_scanned',
                    'message' => 'Attendance already recorded for this participant.',
                    'participant_name' => $participant->user->name,
                ]);
            }

            Attendance::create([
                'event_id' => $event->id,
                'user_id' => $participant->user_id,
                'scan_type' => 'one-time',
                'scanned_at' => now(),
            ]);

            $participant->update(['status' => 'attended']);

            return response()->json([
                'status' => 'success',
                'message' => 'Attendance recorded successfully.',
                'participant_name' => $participant->user->name,
                'scanned_at' => now()->toDateTimeString(),
            ]);
        }

        // am-pm attendance logic
        $scanType = $this->determineAmPmScanType($event->id, $participant->user_id);

        if ($scanType === null) {
            return response()->json([
                'status' => 'already_scanned',
                'message' => 'All attendance slots have been recorded for this participant.',
                'participant_name' => $participant->user->name,
            ]);
        }

        Attendance::create([
            'event_id' => $event->id,
            'user_id' => $participant->user_id,
            'scan_type' => $scanType,
            'scanned_at' => now(),
        ]);

        $participant->update(['status' => 'attended']);

        return response()->json([
            'status' => 'success',
            'message' => "Attendance recorded: {$scanType}.",
            'scan_type' => $scanType,
            'participant_name' => $participant->user->name,
            'scanned_at' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Determine the next AM/PM scan type for a participant.
     *
     * Scan order: am_in -> am_out -> pm_in -> pm_out
     * Returns null if all slots are already recorded.
     */
    private function determineAmPmScanType(int $eventId, int $userId): ?string
    {
        $scannedTypes = Attendance::query()
            ->where('event_id', $eventId)
            ->where('user_id', $userId)
            ->pluck('scan_type')
            ->toArray();

        $order = ['am_in', 'am_out', 'pm_in', 'pm_out'];

        foreach ($order as $type) {
            if (! in_array($type, $scannedTypes)) {
                return $type;
            }
        }

        return null;
    }
}
