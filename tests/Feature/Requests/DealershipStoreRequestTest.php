<?php

declare(strict_types=1);

use App\Models\Dealership;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

function validDealershipStorePayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Prime Motors',
        'address' => '123 Main St',
        'city' => 'Detroit',
        'state' => 'MI',
        'zip_code' => '48201',
        'phone' => '(555) 123-4567',
        'type' => 'Automotive',
        'current_solution_name' => 'Legacy CRM',
        'current_solution_use' => 'Basic lead tracking',
        'notes' => 'Some notes',
        'status' => 'active',
        'rating' => 'hot',
        'user_ids' => [],
    ], $overrides);
}

describe('DealershipStoreRequest', function () {
    it('passes validation with valid data', function () {
        $this->post('/dealerships', validDealershipStorePayload())
            ->assertSessionHasNoErrors();

        expect(Dealership::where('name', 'Prime Motors')->exists())->toBeTrue();
    });

    it('passes with valid user_ids referencing existing users', function () {
        $other = User::factory()->create();

        $this->post('/dealerships', validDealershipStorePayload([
            'user_ids' => [$other->id],
        ]))->assertSessionHasNoErrors();
    });

    it('requires name', function () {
        $this->post('/dealerships', validDealershipStorePayload(['name' => '']))
            ->assertSessionHasErrors('name');
    });

    it('rejects name exceeding 255 chars', function () {
        $this->post('/dealerships', validDealershipStorePayload(['name' => str_repeat('a', 256)]))
            ->assertSessionHasErrors('name');
    });

    it('requires type', function () {
        $this->post('/dealerships', validDealershipStorePayload(['type' => '']))
            ->assertSessionHasErrors('type');
    });

    it('rejects invalid type', function () {
        $this->post('/dealerships', validDealershipStorePayload(['type' => 'Spaceships']))
            ->assertSessionHasErrors('type');
    });

    it('requires status', function () {
        $this->post('/dealerships', validDealershipStorePayload(['status' => '']))
            ->assertSessionHasErrors('status');
    });

    it('rejects invalid status', function () {
        $this->post('/dealerships', validDealershipStorePayload(['status' => 'pending']))
            ->assertSessionHasErrors('status');
    });

    it('requires rating', function () {
        $this->post('/dealerships', validDealershipStorePayload(['rating' => '']))
            ->assertSessionHasErrors('rating');
    });

    it('rejects invalid rating', function () {
        $this->post('/dealerships', validDealershipStorePayload(['rating' => 'lukewarm']))
            ->assertSessionHasErrors('rating');
    });

    it('rejects state exceeding 2 chars', function () {
        $this->post('/dealerships', validDealershipStorePayload(['state' => 'MICH']))
            ->assertSessionHasErrors('state');
    });

    it('rejects zip_code exceeding 10 chars', function () {
        $this->post('/dealerships', validDealershipStorePayload(['zip_code' => str_repeat('1', 11)]))
            ->assertSessionHasErrors('zip_code');
    });

    it('rejects phone exceeding 20 chars', function () {
        $this->post('/dealerships', validDealershipStorePayload(['phone' => str_repeat('5', 21)]))
            ->assertSessionHasErrors('phone');
    });

    it('rejects city exceeding 100 chars', function () {
        $this->post('/dealerships', validDealershipStorePayload(['city' => str_repeat('a', 101)]))
            ->assertSessionHasErrors('city');
    });

    it('rejects user_ids that do not exist', function () {
        $this->post('/dealerships', validDealershipStorePayload([
            'user_ids' => [9999999],
        ]))->assertSessionHasErrors('user_ids.0');
    });
});
