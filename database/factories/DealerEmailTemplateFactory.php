<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DealerEmailTemplate>
 */
class DealerEmailTemplateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['Introduction', 'Follow-up', 'Product Demo', 'Partnership Proposal']).' Email Template',
            'subject' => $this->faker->sentence(4),
            'body' => '<p>Hello {{contact_name}},</p><p>'.$this->faker->paragraph().'</p><p>Best regards,<br>Sales Team</p>',
            'attachment_path' => null,
            'attachment_name' => null,
        ];
    }
}
