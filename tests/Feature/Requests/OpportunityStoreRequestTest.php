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
});

function validOpportunityPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Major Deal',
        'stage' => OpportunityStage::Prospect->value,
        'estimated_value' => 5000.50,
        'probability' => 50,
        'expected_close_date' => '2026-12-01',
        'next_action' => 'Follow up with email',
    ], $overrides);
}

describe('OpportunityStoreRequest', function () {
    it('passes validation with valid data', function () {
        $this->post("/dealerships/{$this->dealership->id}/opportunities", validOpportunityPayload())
            ->assertSessionHasNoErrors();

        expect(Opportunity::where('name', 'Major Deal')->exists())->toBeTrue();
    });

    it('passes with only required fields', function () {
        $this->post("/dealerships/{$this->dealership->id}/opportunities", [
            'name' => 'Minimal',
            'stage' => OpportunityStage::Qualified->value,
        ])->assertSessionHasNoErrors();
    });

    it('requires name', function () {
        $this->post("/dealerships/{$this->dealership->id}/opportunities", validOpportunityPayload(['name' => '']))
            ->assertSessionHasErrors('name');
    });

    it('rejects name exceeding 255 chars', function () {
        $this->post("/dealerships/{$this->dealership->id}/opportunities", validOpportunityPayload(['name' => str_repeat('a', 256)]))
            ->assertSessionHasErrors('name');
    });

    it('requires stage', function () {
        $this->post("/dealerships/{$this->dealership->id}/opportunities", validOpportunityPayload(['stage' => '']))
            ->assertSessionHasErrors('stage');
    });

    it('rejects invalid stage value', function () {
        $this->post("/dealerships/{$this->dealership->id}/opportunities", validOpportunityPayload(['stage' => 'invented']))
            ->assertSessionHasErrors('stage');
    });

    it('rejects negative estimated_value', function () {
        $this->post("/dealerships/{$this->dealership->id}/opportunities", validOpportunityPayload(['estimated_value' => -1]))
            ->assertSessionHasErrors('estimated_value');
    });

    it('rejects non-numeric estimated_value', function () {
        $this->post("/dealerships/{$this->dealership->id}/opportunities", validOpportunityPayload(['estimated_value' => 'not-a-number']))
            ->assertSessionHasErrors('estimated_value');
    });

    it('rejects probability below 0', function () {
        $this->post("/dealerships/{$this->dealership->id}/opportunities", validOpportunityPayload(['probability' => -1]))
            ->assertSessionHasErrors('probability');
    });

    it('rejects probability above 100', function () {
        $this->post("/dealerships/{$this->dealership->id}/opportunities", validOpportunityPayload(['probability' => 101]))
            ->assertSessionHasErrors('probability');
    });

    it('rejects invalid expected_close_date', function () {
        $this->post("/dealerships/{$this->dealership->id}/opportunities", validOpportunityPayload(['expected_close_date' => 'not-a-date']))
            ->assertSessionHasErrors('expected_close_date');
    });

    it('rejects next_action exceeding 255 chars', function () {
        $this->post("/dealerships/{$this->dealership->id}/opportunities", validOpportunityPayload(['next_action' => str_repeat('a', 256)]))
            ->assertSessionHasErrors('next_action');
    });
});
