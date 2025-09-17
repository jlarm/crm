<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Dealership>
 */
class DealershipFactory extends Factory
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
            'name' => $this->faker->company().' '.$this->faker->randomElement(['Motors', 'Auto', 'RV', 'Marine']),
            'address' => $this->faker->streetAddress(),
            'city' => $this->faker->city(),
            'state' => $this->faker->stateAbbr(),
            'zip_code' => $this->faker->postcode(),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->companyEmail(),
            'current_solution_name' => $this->faker->randomElement(['Legacy CRM', 'Excel Sheets', 'Paper Records', 'Old Software']),
            'current_solution_use' => $this->faker->randomElement(['Basic tracking', 'Lead management', 'Customer database', 'Sales tracking']),
            'notes' => $this->faker->optional()->paragraph(),
            'status' => $this->faker->randomElement(['Active', 'Inactive', 'Pending']),
            'rating' => $this->faker->randomElement(['Hot', 'Warm', 'Cold']),
            'type' => $this->faker->randomElement(['Automotive', 'RV', 'Motorsports', 'Maritime', 'Association']),
            'in_development' => $this->faker->boolean(30),
            'dev_status' => $this->faker->randomElement(['no_contact', 'reached_out', 'in_contact']),
        ];
    }
}
