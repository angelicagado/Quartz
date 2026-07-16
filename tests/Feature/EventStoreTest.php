<?php

use App\Models\Event;
use App\Models\User;
use Spatie\Permission\Models\Role;

function eventAdmin(): User
{
    Role::findOrCreate('admin');

    return User::factory()->create()->assignRole('admin');
}

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function validEventPayload(array $overrides = []): array
{
    return array_merge([
        'title' => 'Annual Design Summit',
        'description' => 'A brief overview of the event.',
        'start_time' => now()->addWeek()->format('Y-m-d\TH:i'),
        'end_time' => now()->addWeek()->addHours(3)->format('Y-m-d\TH:i'),
        'registration_type' => 'public',
        'attendance_type' => 'one-time',
        'evaluation_required' => false,
        'certificate_enabled' => false,
    ], $overrides);
}

test('an admin can create an event with the fields sent by the modal', function () {
    $admin = eventAdmin();

    $response = $this->actingAs($admin)
        ->post(route('events.store'), validEventPayload());

    $event = Event::first();

    expect($event)->not->toBeNull()
        ->and($event->title)->toBe('Annual Design Summit')
        ->and($event->registration_type)->toBe('public')
        ->and($event->attendance_type)->toBe('one-time');

    $response->assertRedirect(route('events.show', $event));
});

test('start_time and end_time are required', function () {
    $admin = eventAdmin();

    $this->actingAs($admin)
        ->post(route('events.store'), validEventPayload([
            'start_time' => '',
            'end_time' => '',
        ]))
        ->assertSessionHasErrors(['start_time', 'end_time']);

    expect(Event::count())->toBe(0);
});

test('end_time must be after start_time', function () {
    $admin = eventAdmin();

    $this->actingAs($admin)
        ->post(route('events.store'), validEventPayload([
            'start_time' => now()->addWeek()->format('Y-m-d\TH:i'),
            'end_time' => now()->addWeek()->subHour()->format('Y-m-d\TH:i'),
        ]))
        ->assertSessionHasErrors(['end_time']);
});

test('registration_type and attendance_type only accept schema enum values', function () {
    $admin = eventAdmin();

    $this->actingAs($admin)
        ->post(route('events.store'), validEventPayload([
            'registration_type' => 'private',
            'attendance_type' => 'single',
        ]))
        ->assertSessionHasErrors(['registration_type', 'attendance_type']);
});

test('guests cannot create events', function () {
    $this->post(route('events.store'), validEventPayload())
        ->assertRedirect(route('login'));

    expect(Event::count())->toBe(0);
});

test('the event window is derived from the first and last session times', function () {
    $admin = eventAdmin();

    $date = now()->addWeek()->format('Y-m-d');

    $this->actingAs($admin)
        ->post(route('events.store'), [
            'title' => 'Two-Session Conference',
            'start_time' => "{$date}T00:00",
            'end_time' => "{$date}T23:59",
            'registration_type' => 'open',
            'evaluation_required' => false,
            'certificate_enabled' => false,
            'sessions' => [
                [
                    'name' => 'Morning Session',
                    'start_time' => "{$date}T09:00",
                    'end_time' => "{$date}T11:00",
                    'requires_checkout' => false,
                ],
                [
                    'name' => 'Afternoon Session',
                    'start_time' => "{$date}T13:00",
                    'end_time' => "{$date}T16:00",
                    'requires_checkout' => false,
                ],
            ],
        ]);

    $event = Event::first();

    expect($event)->not->toBeNull()
        ->and($event->start_time->format('Y-m-d\TH:i'))->toBe("{$date}T09:00")
        ->and($event->end_time->format('Y-m-d\TH:i'))->toBe("{$date}T16:00");
});
