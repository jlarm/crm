<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SentEmail>
 */
class SentEmailFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'dealership_id' => \App\Models\Dealership::factory(),
            'recipient' => fake()->safeEmail(),
            'message_id' => fake()->unique()->uuid().'@mailgun.example.com',
            'subject' => fake()->sentence(4),
            'tracking_data' => [
                'mailgun_id' => fake()->uuid(),
                'sent_at' => now()->toISOString(),
                'tags' => ['automated', 'dealer-email'],
            ],
        ];
    }

    public function withTracking(): static
    {
        return $this->state(fn (array $attributes): array => [
            'tracking_data' => [
                'mailgun_id' => fake()->uuid(),
                'sent_at' => now()->toISOString(),
                'tags' => ['automated', 'dealer-email'],
                'tracking_enabled' => true,
                'click_tracking' => true,
                'open_tracking' => true,
            ],
        ]);
    }
}
