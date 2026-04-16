<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enum\ActivityType;
use App\Models\Opportunity;
use App\Models\OpportunityActivity;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OpportunityActivity>
 */
class OpportunityActivityFactory extends Factory
{
    public function definition(): array
    {
        return [
            'opportunity_id' => Opportunity::factory(),
            'user_id' => User::factory(),
            'type' => fake()->randomElement(ActivityType::cases())->value,
            'details' => fake()->paragraph(),
            'occurred_at' => fake()->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
        ];
    }

    public function call(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => ActivityType::Call->value,
        ]);
    }

    public function note(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => ActivityType::Note->value,
        ]);
    }

    public function email(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => ActivityType::Email->value,
        ]);
    }
}
