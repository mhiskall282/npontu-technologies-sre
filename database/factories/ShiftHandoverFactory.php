<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ShiftHandover;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ShiftHandover>
 */
class ShiftHandoverFactory extends Factory
{
    protected $model = ShiftHandover::class;

    public function definition(): array
    {
        return [
            'date' => today()->format('Y-m-d'),
            'shift' => fake()->randomElement(['morning', 'afternoon', 'night']),
            'outgoing_lead_id' => User::factory(),
            'incoming_lead_id' => User::factory(),
            'summary' => fake()->paragraph(),
            'incidents' => fake()->optional()->sentence(),
            'pending_tasks_count' => fake()->numberBetween(0, 5),
            'completed_tasks_count' => fake()->numberBetween(5, 20),
            'signed_at' => now(),
            'accepted_at' => null,
            'accepted_by_id' => null,
            'acceptance_remarks' => null,
        ];
    }

    public function accepted(): static
    {
        return $this->state(fn (array $attributes) => [
            'accepted_at' => now(),
            'accepted_by_id' => User::factory(),
            'acceptance_remarks' => 'Shift verified and accepted.',
        ]);
    }
}
