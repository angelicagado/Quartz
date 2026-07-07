<?php

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = fake()->dateTimeBetween('-1 month', '+1 month');

        return [
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'start_time' => $start,
            'end_time' => (clone $start)->modify('+2 hours'),
            'registration_type' => fake()->randomElement(['static', 'public']),
            'attendance_type' => fake()->randomElement(['one-time', 'am-pm']),
            'evaluation_required' => false,
            'certificate_enabled' => false,
        ];
    }
}
