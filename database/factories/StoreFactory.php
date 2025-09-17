<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Store>
 */
class StoreFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => 1,
            'dealership_id' => \App\Models\Dealership::factory(),
            'name' => $this->faker->company().' '.$this->faker->randomElement(['Store', 'Location', 'Branch', 'Outlet']),
            'address' => $this->faker->streetAddress(),
            'city' => $this->faker->city(),
            'state' => $this->faker->stateAbbr(),
            'zip_code' => $this->faker->postcode(),
            'phone' => $this->faker->phoneNumber(),
            'current_solution_name' => $this->faker->randomElement(['Legacy POS', 'Excel Tracking', 'Paper Records', 'Old Software', 'Custom System']),
            'current_solution_use' => $this->faker->randomElement(['Inventory tracking', 'Sales management', 'Customer database', 'Basic reporting', 'Order processing']),
        ];
    }
}
