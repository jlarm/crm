<?php

declare(strict_types=1);

use App\Models\Dealership;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
    $this->dealership = Dealership::factory()->create(['user_id' => $this->user->id]);
});

function validDealershipUpdatePayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Updated Dealership',
        'address' => '500 New St',
        'city' => 'Chicago',
        'state' => 'IL',
        'zip_code' => '60601',
        'phone' => '555-1212',
        'current_solution_name' => 'New CRM',
        'current_solution_use' => 'Full tracking',
        'notes' => 'Updated notes',
        'status' => 'active',
        'rating' => 'warm',
        'user_ids' => [],
    ], $overrides);
}

describe('DealershipUpdateRequest', function () {
    it('passes validation with valid data', function () {
        $this->put("/dealerships/{$this->dealership->id}", validDealershipUpdatePayload())
            ->assertSessionHasNoErrors();

        expect($this->dealership->fresh()->name)->toBe('Updated Dealership');
    });

    it('requires name', function () {
        $this->put("/dealerships/{$this->dealership->id}", validDealershipUpdatePayload(['name' => '']))
            ->assertSessionHasErrors('name');
    });

    it('rejects name exceeding 255 chars', function () {
        $this->put("/dealerships/{$this->dealership->id}", validDealershipUpdatePayload(['name' => str_repeat('a', 256)]))
            ->assertSessionHasErrors('name');
    });

    it('requires status', function () {
        $this->put("/dealerships/{$this->dealership->id}", validDealershipUpdatePayload(['status' => '']))
            ->assertSessionHasErrors('status');
    });

    it('rejects invalid status', function () {
        $this->put("/dealerships/{$this->dealership->id}", validDealershipUpdatePayload(['status' => 'archived']))
            ->assertSessionHasErrors('status');
    });

    it('requires rating', function () {
        $this->put("/dealerships/{$this->dealership->id}", validDealershipUpdatePayload(['rating' => '']))
            ->assertSessionHasErrors('rating');
    });

    it('rejects invalid rating', function () {
        $this->put("/dealerships/{$this->dealership->id}", validDealershipUpdatePayload(['rating' => 'frigid']))
            ->assertSessionHasErrors('rating');
    });

    it('rejects state exceeding 2 chars', function () {
        $this->put("/dealerships/{$this->dealership->id}", validDealershipUpdatePayload(['state' => 'ILLINOIS']))
            ->assertSessionHasErrors('state');
    });

    it('rejects zip_code exceeding 10 chars', function () {
        $this->put("/dealerships/{$this->dealership->id}", validDealershipUpdatePayload(['zip_code' => str_repeat('1', 11)]))
            ->assertSessionHasErrors('zip_code');
    });

    it('rejects user_ids that do not exist', function () {
        $this->put("/dealerships/{$this->dealership->id}", validDealershipUpdatePayload([
            'user_ids' => [9999999],
        ]))->assertSessionHasErrors('user_ids.0');
    });

    it('accepts valid user_ids', function () {
        $other = User::factory()->create();

        $this->put("/dealerships/{$this->dealership->id}", validDealershipUpdatePayload([
            'user_ids' => [$other->id, $this->user->id],
        ]))->assertSessionHasNoErrors();
    });
});
