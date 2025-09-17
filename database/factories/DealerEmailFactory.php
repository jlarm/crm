<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DealerEmail>
 */
class DealerEmailFactory extends Factory
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
            'dealer_email_template_id' => \App\Models\DealerEmailTemplate::factory(),
            'customize_email' => fake()->boolean(30),
            'customize_attachment' => fake()->boolean(20),
            'recipients' => [
                fake()->safeEmail(),
                fake()->safeEmail(),
            ],
            'attachment' => fake()->optional()->filePath(),
            'attachment_name' => fake()->optional()->words(2, true).'.pdf',
            'subject' => fake()->sentence(4),
            'message' => fake()->paragraphs(3, true),
            'start_date' => fake()->dateTimeBetween('-1 month', '+1 week'),
            'last_sent' => fake()->optional()->dateTimeBetween('-1 month', 'now'),
            'next_send_date' => fake()->dateTimeBetween('now', '+1 month'),
            'frequency' => fake()->randomElement([
                \App\Enum\ReminderFrequency::Immediate,
                \App\Enum\ReminderFrequency::Daily,
                \App\Enum\ReminderFrequency::Weekly,
                \App\Enum\ReminderFrequency::Monthly,
            ]),
            'paused' => fake()->boolean(10),
        ];
    }

    public function paused(): static
    {
        return $this->state(fn (array $attributes): array => [
            'paused' => true,
        ]);
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes): array => [
            'paused' => false,
        ]);
    }

    public function immediate(): static
    {
        return $this->state(fn (array $attributes): array => [
            'frequency' => \App\Enum\ReminderFrequency::Immediate,
        ]);
    }

    public function weekly(): static
    {
        return $this->state(fn (array $attributes): array => [
            'frequency' => \App\Enum\ReminderFrequency::Weekly,
        ]);
    }

    public function customized(): static
    {
        return $this->state(fn (array $attributes): array => [
            'customize_email' => true,
            'customize_attachment' => true,
        ]);
    }
}
