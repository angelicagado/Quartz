<?php

namespace App\Http\Controllers;

use App\Http\Requests\Settings\ParticipantProfileUpdateRequest;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class ParticipantProfileController extends Controller
{
    /**
     * Show the participant's profile page.
     */
    public function edit(): Response
    {
        return Inertia::render('Portal/Profile', [
            'passwordRules' => Password::defaults()->toPasswordRulesString(),
        ]);
    }

    /**
     * Update the participant's account details and profile information.
     */
    public function update(ParticipantProfileUpdateRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $user->fill($request->only('name', 'email'));

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $profile = $user->profile ?? new Profile;

        $profile->fill($request->only('birthdate', 'bio', 'phone'));

        if ($request->hasFile('avatar')) {
            if ($profile->avatar_path !== null) {
                Storage::disk('public')->delete($profile->avatar_path);
            }

            $profile->avatar_path = $request->file('avatar')->store('avatars', 'public');
        }

        $profile->save();

        if ($user->profile_id !== $profile->id) {
            $user->profile_id = $profile->id;
        }

        $user->save();

        return back()->with('success', 'Profile updated successfully.');
    }
}
