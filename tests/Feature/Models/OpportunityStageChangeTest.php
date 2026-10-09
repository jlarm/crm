<?php

declare(strict_types=1);

use App\Enum\OpportunityStage;
use App\Models\Opportunity;
use App\Models\User;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->user = User::factory()->create();
    actingAs($this->user);
});

it('records the starting stage when a deal is created', function () {
    $user = $this->user;

    $opportunity = Opportunity::factory()->create(['stage' => OpportunityStage::Prospect->value]);

    expect($opportunity->stageChanges)->toHaveCount(1)
        ->and($opportunity->stageChanges->first()->from_stage)->toBeNull()
        ->and($opportunity->stageChanges->first()->to_stage)->toBe(OpportunityStage::Prospect)
        ->and($opportunity->stageChanges->first()->user_id)->toBe($user->id);
});

it('records each stage move and resets when the stage was entered', function () {
    $opportunity = Opportunity::factory()->create([
        'stage' => OpportunityStage::Prospect->value,
        'stage_entered_at' => now()->subMonth(),
    ]);

    $opportunity->update(['stage' => OpportunityStage::Demo->value]);

    $change = $opportunity->stageChanges()->get()->last();

    expect($change->from_stage)->toBe(OpportunityStage::Prospect)
        ->and($change->to_stage)->toBe(OpportunityStage::Demo)
        ->and($opportunity->fresh()->stage_entered_at->isToday())->toBeTrue();
});

it('does not record a change when other fields are edited', function () {
    $opportunity = Opportunity::factory()->create(['stage' => OpportunityStage::Prospect->value]);

    $opportunity->update(['name' => 'Renamed']);

    expect($opportunity->stageChanges()->count())->toBe(1);
});

it('stamps closed_at when a deal is won and clears it when reopened', function () {
    $opportunity = Opportunity::factory()->create(['stage' => OpportunityStage::Negotiation->value, 'closed_at' => null]);

    $opportunity->update(['stage' => OpportunityStage::Won->value]);
    expect($opportunity->fresh()->closed_at?->isToday())->toBeTrue();

    $opportunity->update(['stage' => OpportunityStage::Negotiation->value]);
    expect($opportunity->fresh()->closed_at)->toBeNull();
});
