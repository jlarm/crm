<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Contact>
 */
class ContactFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'dealership_id' => \App\Models\Dealership::factory(),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'position' => fake()->randomElement(['Manager', 'Owner', 'Sales Director', 'General Manager', 'Service Manager']),
            'primary_contact' => fake()->boolean(30), // 30% chance of being primary
            'linkedin_link' => fake()->optional(0.6)->url(),
        ];
    }

    public function primary(): static
    {
        return $this->state(fn (array $attributes): array => [
            'primary_contact' => true,
        ]);
    }

    public function nonPrimary(): static
    {
        return $this->state(fn (array $attributes): array => [
            'primary_contact' => false,
        ]);
    }
}
