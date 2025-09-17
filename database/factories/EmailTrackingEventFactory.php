<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EmailTrackingEvent>
 */
class EmailTrackingEventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sent_email_id' => \App\Models\SentEmail::factory(),
            'event_type' => fake()->randomElement([
                \App\Models\EmailTrackingEvent::EVENT_DELIVERED,
                \App\Models\EmailTrackingEvent::EVENT_OPENED,
                \App\Models\EmailTrackingEvent::EVENT_CLICKED,
            ]),
            'message_id' => fake()->uuid().'@mailgun.example.com',
            'recipient_email' => fake()->safeEmail(),
            'url' => fake()->optional(0.3)->url(),
            'user_agent' => fake()->userAgent(),
            'ip_address' => fake()->ipv4(),
            'mailgun_data' => [
                'id' => fake()->uuid(),
                'timestamp' => now()->timestamp,
                'event' => fake()->randomElement(['delivered', 'opened', 'clicked']),
            ],
            'event_timestamp' => fake()->dateTimeBetween('-1 week', 'now'),
        ];
    }

    public function opened(): static
    {
        return $this->state(fn (array $attributes): array => [
            'event_type' => \App\Models\EmailTrackingEvent::EVENT_OPENED,
            'url' => null,
            'mailgun_data' => [
                'id' => fake()->uuid(),
                'timestamp' => now()->timestamp,
                'event' => 'opened',
            ],
        ]);
    }

    public function clicked(): static
    {
        return $this->state(fn (array $attributes): array => [
            'event_type' => \App\Models\EmailTrackingEvent::EVENT_CLICKED,
            'url' => fake()->url(),
            'mailgun_data' => [
                'id' => fake()->uuid(),
                'timestamp' => now()->timestamp,
                'event' => 'clicked',
                'url' => fake()->url(),
            ],
        ]);
    }

    public function bounced(): static
    {
        return $this->state(fn (array $attributes): array => [
            'event_type' => \App\Models\EmailTrackingEvent::EVENT_BOUNCED,
            'url' => null,
            'mailgun_data' => [
                'id' => fake()->uuid(),
                'timestamp' => now()->timestamp,
                'event' => 'bounced',
                'severity' => 'permanent',
                'reason' => 'Invalid domain',
            ],
        ]);
    }

    public function delivered(): static
    {
        return $this->state(fn (array $attributes): array => [
            'event_type' => \App\Models\EmailTrackingEvent::EVENT_DELIVERED,
            'url' => null,
            'mailgun_data' => [
                'id' => fake()->uuid(),
                'timestamp' => now()->timestamp,
                'event' => 'delivered',
            ],
        ]);
    }
}
