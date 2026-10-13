<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enum\EmailDirection;
use App\Enum\EmailStatus;
use App\Models\Contact;
use App\Models\Dealership;
use App\Models\DealershipEmail;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<DealershipEmail>
 */
class DealershipEmailFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'dealership_id' => Dealership::factory()->state(['user_id' => User::factory()]),
            'contact_id' => Contact::factory(),
            'user_id' => User::factory(),
            'direction' => EmailDirection::Outbound,
            'from_email' => fake()->safeEmail(),
            'from_name' => fake()->name(),
            'to_email' => fake()->safeEmail(),
            'subject' => fake()->sentence(4),
            'body_text' => fake()->paragraphs(2, true),
            'message_id' => Str::uuid()->toString().'@example.com',
            'reply_token' => Str::random(32),
            'status' => EmailStatus::Sent,
            'sent_at' => now(),
        ];
    }

    public function queued(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => EmailStatus::Queued,
            'sent_at' => null,
        ]);
    }

    public function inbound(): static
    {
        return $this->state(fn (array $attributes): array => [
            'direction' => EmailDirection::Inbound,
            'reply_token' => null,
            'status' => EmailStatus::Received,
            'sent_at' => null,
        ]);
    }
}
