<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Event;
use App\Models\TeamInvitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response|RedirectResponse
    {
        $user = $request->user();

        // Participants (end users) land on their registered-events portal, not the team dashboard.
        if ($user->hasRole('participant') && ! $user->hasAnyRole(['super_admin', 'admin', 'event_organizer'])) {
            return redirect()->route('portal.my-events');
        }

        if ($user->hasRole(['super_admin', 'admin'])) {
            return redirect()->route('super-admin.dashboard');
        }

        if ($user->hasRole('event_organizer')) {
            return Inertia::render('OrganizerDashboard', [
                'stats' => [
                    'totalEvents' => Event::whereHas('organizers', fn ($q) => $q->where('user_id', $user->id))->count(),
                    'todaysEvents' => Event::whereHas('organizers', fn ($q) => $q->where('user_id', $user->id))
                        ->whereDate('start_time', today())
                        ->count(),
                    'totalScans' => Attendance::whereHas('event', function ($query) use ($user) {
                        $query->whereHas('organizers', fn ($q) => $q->where('user_id', $user->id));
                    })->count(),
                ],
                'liveEvents' => Event::whereHas('organizers', fn ($q) => $q->where('user_id', $user->id))
                    ->where(function ($query) {
                        $query->whereDate('start_time', today())
                            ->orWhere(function ($q) {
                                $q->where('start_time', '<=', now())
                                    ->where('end_time', '>=', now());
                            });
                    })->with('organizers')->orderBy('start_time')->take(5)->get(),
            ]);
        }

        // For participants and other roles, show the default dashboard
        $email = strtolower($request->user()->email);

        $pendingInvitations = TeamInvitation::query()
            ->with(['inviter', 'team'])
            ->whereRaw('LOWER(email) = ?', [$email])
            ->whereNull('accepted_at')
            ->where(fn ($query) => $query
                ->whereNull('expires_at')
                ->orWhere('expires_at', '>=', now()))
            ->latest()
            ->get()
            ->map(fn (TeamInvitation $invitation) => [
                'code' => $invitation->code,
                'inviterName' => $invitation->inviter->name,
                'team' => [
                    'name' => $invitation->team->name,
                    'slug' => $invitation->team->slug,
                ],
            ]);

        return Inertia::render('Dashboard', [
            'pendingInvitations' => $pendingInvitations,
        ]);
    }
}
