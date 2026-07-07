<?php

use App\Models\Attendance;
use App\Models\Event;
use App\Models\EventParticipant;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;

function superAdmin(): User
{
    Role::findOrCreate('super_admin');

    return User::factory()->create()->assignRole('super_admin');
}

test('guests are redirected from the super admin dashboard', function () {
    $this->get(route('super-admin.dashboard'))
        ->assertRedirect(route('login'));
});

test('non super admins are forbidden from the super admin dashboard', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('super-admin.dashboard'))
        ->assertForbidden();
});

test('super admins are redirected to their dashboard after login', function () {
    $admin = superAdmin();

    $this->post(route('login.store'), [
        'email' => $admin->email,
        'password' => 'password',
    ])->assertRedirect('/super-admin/dashboard');
});

test('super admins can view the dashboard with computed stats', function () {
    $admin = superAdmin();

    Event::factory()->create(['start_time' => now()->addWeek()]);
    $pastEvent = Event::factory()->create(['start_time' => now()->subWeek()]);

    EventParticipant::factory()->create([
        'event_id' => $pastEvent->id,
        'status' => 'registered',
    ]);

    Attendance::factory()->create(['event_id' => $pastEvent->id]);

    $this->actingAs($admin)
        ->get(route('super-admin.dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('SuperAdminDashboard')
            ->where('stats.upcomingEvents', 1)
            ->where('stats.totalEvents', 2)
            ->where('stats.pendingApprovals', 1)
            ->has('recentScans', 1)
            ->has('lineData', 6)
            ->has('barData')
        );
});
