<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enum\TaskPriority;
use App\Enum\TaskType;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $userId = User::factory();

        return [
            'user_id' => $userId,
            'created_by_user_id' => $userId,
            'dealership_id' => null,
            'contact_id' => null,
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->paragraph(),
            'type' => fake()->randomElement(TaskType::cases())->value,
            'priority' => fake()->randomElement(TaskPriority::cases())->value,
            'due_date' => fake()->optional()->dateTimeBetween('now', '+30 days'),
            'completed_at' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'completed_at' => now(),
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'due_date' => fake()->dateTimeBetween('-30 days', '-1 day'),
            'completed_at' => null,
        ]);
    }

    public function high(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => TaskPriority::High->value,
        ]);
    }
}
