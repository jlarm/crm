<?php

declare(strict_types=1);

use App\Enum\OpportunityStage;
use App\Models\Dealership;
use App\Models\Opportunity;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
    $this->dealership = Dealership::factory()->create(['user_id' => $this->user->id]);
    $this->opportunity = Opportunity::factory()->create([
        'dealership_id' => $this->dealership->id,
    ]);
});

function validOpportunityUpdatePayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Updated Opportunity',
        'stage' => OpportunityStage::Demo->value,
        'estimated_value' => 12000,
        'probability' => 75,
        'expected_close_date' => '2026-11-30',
        'next_action' => 'Schedule demo',
    ], $overrides);
}

describe('OpportunityUpdateRequest', function () {
    it('passes validation with valid data', function () {
        $this->put(
            "/dealerships/{$this->dealership->id}/opportunities/{$this->opportunity->id}",
            validOpportunityUpdatePayload()
        )->assertSessionHasNoErrors();

        expect($this->opportunity->fresh()->name)->toBe('Updated Opportunity');
    });

    it('requires name', function () {
        $this->put(
            "/dealerships/{$this->dealership->id}/opportunities/{$this->opportunity->id}",
            validOpportunityUpdatePayload(['name' => ''])
        )->assertSessionHasErrors('name');
    });

    it('rejects name exceeding 255 chars', function () {
        $this->put(
            "/dealerships/{$this->dealership->id}/opportunities/{$this->opportunity->id}",
            validOpportunityUpdatePayload(['name' => str_repeat('a', 256)])
        )->assertSessionHasErrors('name');
    });

    it('requires stage', function () {
        $this->put(
            "/dealerships/{$this->dealership->id}/opportunities/{$this->opportunity->id}",
            validOpportunityUpdatePayload(['stage' => ''])
        )->assertSessionHasErrors('stage');
    });

    it('rejects invalid stage', function () {
        $this->put(
            "/dealerships/{$this->dealership->id}/opportunities/{$this->opportunity->id}",
            validOpportunityUpdatePayload(['stage' => 'unknown'])
        )->assertSessionHasErrors('stage');
    });

    it('rejects negative estimated_value', function () {
        $this->put(
            "/dealerships/{$this->dealership->id}/opportunities/{$this->opportunity->id}",
            validOpportunityUpdatePayload(['estimated_value' => -100])
        )->assertSessionHasErrors('estimated_value');
    });

    it('rejects probability outside 0-100', function () {
        $this->put(
            "/dealerships/{$this->dealership->id}/opportunities/{$this->opportunity->id}",
            validOpportunityUpdatePayload(['probability' => 150])
        )->assertSessionHasErrors('probability');
    });

    it('rejects invalid expected_close_date', function () {
        $this->put(
            "/dealerships/{$this->dealership->id}/opportunities/{$this->opportunity->id}",
            validOpportunityUpdatePayload(['expected_close_date' => 'garbage'])
        )->assertSessionHasErrors('expected_close_date');
    });

    it('rejects next_action exceeding 255 chars', function () {
        $this->put(
            "/dealerships/{$this->dealership->id}/opportunities/{$this->opportunity->id}",
            validOpportunityUpdatePayload(['next_action' => str_repeat('a', 256)])
        )->assertSessionHasErrors('next_action');
    });
});
