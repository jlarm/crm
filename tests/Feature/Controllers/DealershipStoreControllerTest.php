<?php

declare(strict_types=1);

use App\Models\Dealership;
use App\Models\Store;
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

describe('DealershipStoreController store', function () {
    it('creates a store with the current user attached', function () {
        post(route('dealerships.stores.store', $this->dealership), [
            'name' => 'Main Store',
            'address' => '1 Test St',
            'city' => 'Townsville',
            'state' => 'TS',
            'zip_code' => '12345',
            'phone' => '555-0001',
        ])->assertRedirect();

        $this->assertDatabaseHas('stores', [
            'dealership_id' => $this->dealership->id,
            'name' => 'Main Store',
            'user_id' => $this->user->id,
        ]);
    });

    it('validates required name', function () {
        post(route('dealerships.stores.store', $this->dealership), [])
            ->assertSessionHasErrors('name');
    });
});

describe('DealershipStoreController update', function () {
    it('updates a store', function () {
        $store = Store::factory()->create(['dealership_id' => $this->dealership->id, 'user_id' => $this->user->id]);

        put(route('dealerships.stores.update', [$this->dealership, $store]), [
            'name' => 'New Name',
            'city' => 'Detroit',
        ])->assertRedirect();

        expect($store->fresh()->name)->toBe('New Name')
            ->and($store->fresh()->city)->toBe('Detroit');
    });

    it('aborts when store does not belong to the dealership', function () {
        $other = Dealership::factory()->create(['user_id' => $this->user->id]);
        $store = Store::factory()->create(['dealership_id' => $other->id, 'user_id' => $this->user->id]);

        put(route('dealerships.stores.update', [$this->dealership, $store]), [
            'name' => 'Anything',
        ])->assertNotFound();
    });
});

describe('DealershipStoreController destroy', function () {
    it('deletes a store', function () {
        $store = Store::factory()->create(['dealership_id' => $this->dealership->id, 'user_id' => $this->user->id]);

        delete(route('dealerships.stores.destroy', [$this->dealership, $store]))->assertRedirect();

        $this->assertDatabaseMissing('stores', ['id' => $store->id]);
    });

    it('aborts when store does not belong to the dealership', function () {
        $other = Dealership::factory()->create(['user_id' => $this->user->id]);
        $store = Store::factory()->create(['dealership_id' => $other->id, 'user_id' => $this->user->id]);

        delete(route('dealerships.stores.destroy', [$this->dealership, $store]))->assertNotFound();
    });
});
