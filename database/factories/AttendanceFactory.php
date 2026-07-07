<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attendance>
 */
class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'user_id' => User::factory(),
            'scan_type' => fake()->randomElement(['am_in', 'pm_in', 'one-time']),
            'scanned_at' => now(),
        ];
    }
}
