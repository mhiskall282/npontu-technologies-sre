<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\OperationalNotification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OperationalNotification>
 */
class OperationalNotificationFactory extends Factory
{
    protected $model = OperationalNotification::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(4),
            'message' => fake()->paragraph(1),
            'type' => fake()->randomElement(['system', 'escalation', 'handover', 'assignment', 'chat']),
            'priority' => fake()->randomElement(['critical', 'high', 'medium', 'info']),
            'action_route' => '/activities',
            'metadata' => ['env' => 'production'],
            'read_at' => null,
        ];
    }

    public function read(): static
    {
        return $this->state(['read_at' => now()]);
    }

    public function unread(): static
    {
        return $this->state(['read_at' => null]);
    }

    public function critical(): static
    {
        return $this->state(['priority' => 'critical']);
    }
}
