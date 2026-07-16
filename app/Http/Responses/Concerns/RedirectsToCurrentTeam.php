<?php

namespace App\Http\Responses\Concerns;

use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Laravel\Fortify\Fortify;

trait RedirectsToCurrentTeam
{
    /**
     * Build the post-authentication redirect for the current user.
     *
     * Privileged users are always sent to their own dashboard and never honor a
     * stored "intended" URL, since that URL may point at the participant portal
     * (e.g. from a guarded /portal/* link visited while logged out) and would
     * otherwise drop an admin into the wrong module.
     */
    protected function redirectAfterLogin(Request $request): RedirectResponse
    {
        $path = $this->redirectPathForCurrentTeam($request, Fortify::redirects('login'));

        if ($request->user()?->hasRole(['super_admin', 'admin'])) {
            return redirect()->to($path);
        }

        return redirect()->intended($path);
    }

    protected function redirectPathForCurrentTeam(Request $request, string $redirect): string
    {
        if ($request->user()?->hasRole(['super_admin', 'admin'])) {
            return '/super-admin/dashboard';
        }

        $team = $this->currentTeam($request);

        URL::defaults(['current_team' => $team->slug]);

        return $redirect;
    }

    protected function currentTeam(Request $request): Team
    {
        $user = $request->user();

        abort_if(! $user, 403);

        $team = $user->currentTeam ?? $user->personalTeam();

        abort_if(! $team, 403);

        return $team;
    }
}
