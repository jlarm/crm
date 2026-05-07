<?php

declare(strict_types=1);

use App\Enum\OpportunityStage;
use App\Models\Dealership;
use App\Models\Opportunity;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\delete;
use function Pest\Laravel\post;
use function Pest\Laravel\put;

beforeEach(function () {
    $this->user = User::factory()->create();
    actingAs($this->user);
    $this->dealership = Dealership::factory()->create(['user_id' => $this->user->id]);
});

describe('DealershipOpportunityController store', function () {
    it('creates an opportunity', function () {
        post(route('dealerships.opportunities.store', $this->dealership), [
            'name' => 'Big Deal',
            'stage' => OpportunityStage::Prospect->value,
            'estimated_value' => 25000,
            'probability' => 60,
        ])->assertRedirect();

        $this->assertDatabaseHas('opportunities', [
            'dealership_id' => $this->dealership->id,
            'name' => 'Big Deal',
            'stage' => OpportunityStage::Prospect->value,
        ]);
    });

    it('validates required name and stage', function () {
        post(route('dealerships.opportunities.store', $this->dealership), [])
            ->assertSessionHasErrors(['name', 'stage']);
    });

    it('rejects invalid stage', function () {
        post(route('dealerships.opportunities.store', $this->dealership), [
            'name' => 'Foo',
            'stage' => 'not-a-real-stage',
        ])->assertSessionHasErrors('stage');
    });
});

describe('DealershipOpportunityController update', function () {
    it('updates an opportunity', function () {
        $opportunity = Opportunity::factory()->create(['dealership_id' => $this->dealership->id]);

        put(route('dealerships.opportunities.update', [$this->dealership, $opportunity]), [
            'name' => 'Renamed',
            'stage' => OpportunityStage::Qualified->value,
        ])->assertRedirect();

        expect($opportunity->fresh()->name)->toBe('Renamed')
            ->and($opportunity->fresh()->stage)->toBe(OpportunityStage::Qualified);
    });
});

describe('DealershipOpportunityController destroy', function () {
    it('deletes the opportunity', function () {
        $opportunity = Opportunity::factory()->create(['dealership_id' => $this->dealership->id]);

        delete(route('dealerships.opportunities.destroy', [$this->dealership, $opportunity]))->assertRedirect();

        $this->assertDatabaseMissing('opportunities', ['id' => $opportunity->id]);
    });
});
