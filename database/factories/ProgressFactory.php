<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Progress>
 */
class ProgressFactory extends Factory
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
            'contact_id' => \App\Models\Contact::factory(),
            'details' => fake()->paragraph(3),
            'date' => fake()->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
            'progress_category_id' => \App\Models\ProgressCategory::factory(),
        ];
    }

    public function recent(): static
    {
        return $this->state(fn (array $attributes): array => [
            'date' => fake()->dateTimeBetween('-1 week', 'now')->format('Y-m-d'),
        ]);
    }

    public function old(): static
    {
        return $this->state(fn (array $attributes): array => [
            'date' => fake()->dateTimeBetween('-1 year', '-6 months')->format('Y-m-d'),
        ]);
    }

    public function withoutContact(): static
    {
        return $this->state(fn (array $attributes): array => [
            'contact_id' => null,
        ]);
    }
}
