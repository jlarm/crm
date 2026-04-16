<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enum\OpportunityStage;
use App\Models\Dealership;
use App\Models\Opportunity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Opportunity>
 */
class OpportunityFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $stage = fake()->randomElement(OpportunityStage::cases());
        $createdAt = fake()->dateTimeBetween('-12 months', 'now');

        return [
            'dealership_id' => Dealership::factory(),
            'name' => fake()->company().' - '.fake()->words(3, true),
            'stage' => $stage->value,
            'stage_entered_at' => $createdAt,
            'probability' => fake()->numberBetween(10, 90),
            'estimated_value' => fake()->randomFloat(2, 1000, 50000),
            'actual_value' => null,
            'expected_close_date' => fake()->dateTimeBetween('now', '+6 months'),
            'next_action' => fake()->optional()->sentence(),
            'follow_up_date' => fake()->optional()->dateTimeBetween('now', '+30 days'),
            'lost_reason' => null,
            'lost_reason_code' => null,
            'contract_sent_date' => null,
            'contract_signed_date' => null,
            'contract_renewal_date' => null,
            'closed_at' => null,
            'created_at' => $createdAt,
        ];
    }

    public function won(): static
    {
        return $this->state(function (array $attributes) {
            $closedAt = fake()->dateTimeBetween('-6 months', 'now');

            return [
                'stage' => OpportunityStage::Won->value,
                'actual_value' => fake()->randomFloat(2, 1000, 50000),
                'closed_at' => $closedAt,
                'contract_signed_date' => $closedAt,
            ];
        });
    }

    public function lost(): static
    {
        return $this->state(fn (array $attributes) => [
            'stage' => OpportunityStage::Lost->value,
            'lost_reason' => fake()->sentence(),
            'lost_reason_code' => fake()->randomElement(['price', 'competitor', 'no_budget', 'timing']),
            'closed_at' => fake()->dateTimeBetween('-6 months', 'now'),
        ]);
    }

    public function open(): static
    {
        return $this->state(fn (array $attributes) => [
            'stage' => fake()->randomElement(OpportunityStage::openValues()),
            'closed_at' => null,
        ]);
    }
}
