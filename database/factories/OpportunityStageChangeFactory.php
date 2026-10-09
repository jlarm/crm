<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enum\OpportunityStage;
use App\Models\Opportunity;
use App\Models\OpportunityStageChange;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OpportunityStageChange>
 */
class OpportunityStageChangeFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'opportunity_id' => Opportunity::factory(),
            'user_id' => null,
            'from_stage' => null,
            'to_stage' => OpportunityStage::Prospect->value,
            'changed_at' => now(),
        ];
    }
}
