<?php

declare(strict_types=1);

use App\Enum\OpportunityStage;
use App\Models\Dealership;
use App\Models\Opportunity;
use App\Models\User;

describe('Opportunity dealership relation', function (): void {
    it('belongs to a dealership', function (): void {
        $user = User::factory()->create();
        $dealership = Dealership::factory()->create(['user_id' => $user->id]);
        $opportunity = Opportunity::factory()->create(['dealership_id' => $dealership->id]);

        expect($opportunity->dealership)->not->toBeNull()
            ->and($opportunity->dealership->id)->toBe($dealership->id);
    });
});

describe('Opportunity scopeOpen', function (): void {
    it('excludes opportunities in closed stages', function (): void {
        $user = User::factory()->create();
        $dealership = Dealership::factory()->create(['user_id' => $user->id]);

        $open = Opportunity::factory()->create([
            'dealership_id' => $dealership->id,
            'stage' => OpportunityStage::Qualified,
        ]);
        Opportunity::factory()->create([
            'dealership_id' => $dealership->id,
            'stage' => OpportunityStage::Won,
        ]);
        Opportunity::factory()->create([
            'dealership_id' => $dealership->id,
            'stage' => OpportunityStage::Lost,
        ]);

        $results = Opportunity::query()->open()->get();

        expect($results->pluck('id')->all())->toBe([$open->id]);
    });
});
