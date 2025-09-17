<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProgressCategory>
 */
class ProgressCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement([
                'Initial Contact',
                'Follow-up',
                'Demo Scheduled',
                'Proposal Sent',
                'Contract Negotiation',
                'Deal Closed',
                'Implementation',
                'Support Request',
                'Feedback',
            ]),
        ];
    }
}
