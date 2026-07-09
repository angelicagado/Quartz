<?php

use App\Actions\Fortify\CreateNewUser;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::findOrCreate('participant');
});

function profileParticipant(): User
{
    $user = User::factory()->create(['profile_id' => Profile::create()->id]);
    $user->assignRole('participant');

    return $user;
}

test('self-registration creates and links a profile', function () {
    $user = app(CreateNewUser::class)->create([
        'name' => 'New Attendee',
        'email' => 'attendee@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    expect($user->profile_id)->not->toBeNull()
        ->and($user->profile)->toBeInstanceOf(Profile::class);
});

test('the profile page renders for a participant', function () {
    $user = profileParticipant();

    $this->actingAs($user)
        ->get(route('portal.profile.edit'))
        ->assertInertia(fn (Assert $page) => $page->component('Portal/Profile'));
});

test('a participant can update their account and profile details', function () {
    $user = profileParticipant();

    $this->actingAs($user)
        ->patch(route('portal.profile.update'), [
            'name' => 'Updated Name',
            'email' => $user->email,
            'birthdate' => '1999-05-20',
            'bio' => 'Lifelong learner.',
            'phone' => '09171234567',
        ])
        ->assertRedirect();

    $user->refresh();

    expect($user->name)->toBe('Updated Name')
        ->and($user->profile->bio)->toBe('Lifelong learner.')
        ->and($user->profile->phone)->toBe('09171234567')
        ->and($user->profile->birthdate->format('Y-m-d'))->toBe('1999-05-20');
});

test('uploading an avatar stores the file and sets the path', function () {
    Storage::fake('public');

    $user = profileParticipant();

    $this->actingAs($user)
        ->patch(route('portal.profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => UploadedFile::fake()->image('me.jpg'),
        ])
        ->assertRedirect();

    $path = $user->refresh()->profile->avatar_path;

    expect($path)->not->toBeNull();
    Storage::disk('public')->assertExists($path);
});

test('changing the email clears the verification timestamp', function () {
    $user = profileParticipant();

    expect($user->email_verified_at)->not->toBeNull();

    $this->actingAs($user)
        ->patch(route('portal.profile.update'), [
            'name' => $user->name,
            'email' => 'changed@example.com',
        ])
        ->assertRedirect();

    expect($user->refresh()->email_verified_at)->toBeNull();
});
