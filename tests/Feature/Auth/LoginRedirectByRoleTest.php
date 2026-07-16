<?php

use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    foreach (['super_admin', 'admin', 'event_organizer', 'participant'] as $role) {
        Role::findOrCreate($role);
    }
});

test('super_admin login lands on super admin dashboard', function () {
    $user = User::factory()->create()->assignRole('super_admin');

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect('/super-admin/dashboard');
});

test('participant login lands on portal', function () {
    $user = User::factory()->create()->assignRole('participant');

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    // default home is /dashboard which then redirects participants to portal
    $response->assertRedirect('/dashboard');
});

test('super_admin login ignores a stale portal intended url', function () {
    $user = User::factory()->create()->assignRole('super_admin');

    // Simulate hitting a guarded portal page while logged out -> stores url.intended
    $this->get('/portal/events')->assertRedirect(route('login'));

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect('/super-admin/dashboard');
});
