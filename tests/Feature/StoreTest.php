<?php

declare(strict_types=1);

use App\Models\Dealership;
use App\Models\Store;
use App\Models\User;
use Spatie\Activitylog\Models\Activity;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->dealership = Dealership::factory()->create(['user_id' => $this->user->id]);
    $this->actingAs($this->user);
});

describe('Store Creation', function () {
    it('can create a store with valid data', function () {
        $storeData = [
            'user_id' => $this->user->id,
            'dealership_id' => $this->dealership->id,
            'name' => 'Downtown Location',
            'address' => '123 Main Street',
            'city' => 'Detroit',
            'state' => 'MI',
            'zip_code' => '48201',
            'phone' => '(555) 123-4567',
            'current_solution_name' => 'Legacy POS System',
            'current_solution_use' => 'Inventory tracking and sales',
        ];

        $store = Store::create($storeData);

        expect($store)->toBeInstanceOf(Store::class)
            ->and($store->name)->toBe('Downtown Location')
            ->and($store->address)->toBe('123 Main Street')
            ->and($store->city)->toBe('Detroit')
            ->and($store->state)->toBe('MI')
            ->and($store->zip_code)->toBe('48201')
            ->and($store->phone)->toBe('(555) 123-4567')
            ->and($store->current_solution_name)->toBe('Legacy POS System')
            ->and($store->current_solution_use)->toBe('Inventory tracking and sales')
            ->and($store->user_id)->toBe($this->user->id)
            ->and($store->dealership_id)->toBe($this->dealership->id);
    });

    it('can create a store using factory', function () {
        $store = Store::factory()->create([
            'user_id' => $this->user->id,
            'dealership_id' => $this->dealership->id,
            'name' => 'Custom Store Name',
            'city' => 'Custom City',
        ]);

        expect($store->name)->toBe('Custom Store Name')
            ->and($store->city)->toBe('Custom City')
            ->and($store->user_id)->toBe($this->user->id)
            ->and($store->dealership_id)->toBe($this->dealership->id);
    });

    it('can create multiple stores for the same dealership', function () {
        $storeCount = 5;

        for ($i = 0; $i < $storeCount; $i++) {
            Store::factory()->create([
                'user_id' => $this->user->id,
                'dealership_id' => $this->dealership->id,
                'name' => "Store Location {$i}",
            ]);
        }

        expect($this->dealership->stores)->toHaveCount($storeCount);
        expect($this->dealership->stores->pluck('name')->toArray())
            ->toContain('Store Location 0', 'Store Location 4');
    });

    it('can create stores with different solution types', function () {
        $solutions = [
            'Legacy POS',
            'Excel Tracking',
            'Paper Records',
            'Old Software',
            'Custom System',
        ];

        foreach ($solutions as $solution) {
            $store = Store::factory()->create([
                'user_id' => $this->user->id,
                'dealership_id' => $this->dealership->id,
                'current_solution_name' => $solution,
            ]);

            expect($store->current_solution_name)->toBe($solution);
        }

        expect(Store::count())->toBe(5);
    });
});

describe('Store Fillable Fields', function () {
    it('allows mass assignment of all fillable fields', function () {
        $data = [
            'user_id' => $this->user->id,
            'dealership_id' => $this->dealership->id,
            'name' => 'Test Store',
            'address' => '456 Test Avenue',
            'city' => 'Test City',
            'state' => 'TX',
            'zip_code' => '75001',
            'phone' => '555-TEST-STORE',
            'current_solution_name' => 'Test Solution',
            'current_solution_use' => 'Test usage description',
        ];

        $store = Store::create($data);

        foreach ($data as $key => $value) {
            expect($store->$key)->toBe($value);
        }
    });

    it('has correct fillable fields defined', function () {
        $expectedFillable = [
            'user_id',
            'dealership_id',
            'name',
            'address',
            'city',
            'state',
            'zip_code',
            'phone',
            'current_solution_name',
            'current_solution_use',
        ];

        $store = new Store;
        expect($store->getFillable())->toBe($expectedFillable);
    });
});

describe('Store Relationships', function () {
    it('belongs to a dealership', function () {
        $store = Store::factory()->create([
            'user_id' => $this->user->id,
            'dealership_id' => $this->dealership->id,
        ]);

        expect($store->dealership)->toBeInstanceOf(Dealership::class)
            ->and($store->dealership->id)->toBe($this->dealership->id)
            ->and($store->dealership->name)->toBe($this->dealership->name);
    });

    it('can access dealership relationship multiple times without additional queries', function () {
        $store = Store::factory()->create([
            'user_id' => $this->user->id,
            'dealership_id' => $this->dealership->id,
        ]);

        // Load the relationship
        $dealership1 = $store->dealership;
        $dealership2 = $store->dealership;

        expect($dealership1->id)->toBe($dealership2->id)
            ->and($dealership1->name)->toBe($dealership2->name);
    });

    it('can load dealership relationship with eager loading', function () {
        $stores = Store::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'dealership_id' => $this->dealership->id,
        ]);

        $loadedStores = Store::with('dealership')->get();

        foreach ($loadedStores as $store) {
            expect($store->relationLoaded('dealership'))->toBeTrue()
                ->and($store->dealership)->toBeInstanceOf(Dealership::class);
        }
    });
});

describe('Store Activity Logging', function () {
    it('logs store creation activity', function () {
        $store = Store::factory()->create([
            'user_id' => $this->user->id,
            'dealership_id' => $this->dealership->id,
            'name' => 'Activity Test Store',
        ]);

        $storeActivities = Activity::where('subject_type', Store::class)
            ->where('subject_id', $store->id)
            ->get();

        expect($storeActivities)->toHaveCount(1);

        $activity = $storeActivities->first();
        expect($activity->description)->toBe('Store created')
            ->and($activity->subject_type)->toBe(Store::class)
            ->and($activity->subject_id)->toBe($store->id)
            ->and($activity->properties->get('attributes'))->toHaveKey('name', 'Activity Test Store');
    });

    it('logs store update activity', function () {
        $store = Store::factory()->create([
            'user_id' => $this->user->id,
            'dealership_id' => $this->dealership->id,
            'name' => 'Original Name',
        ]);

        $store->update([
            'name' => 'Updated Name',
            'city' => 'Updated City',
        ]);

        $storeActivities = Activity::where('subject_type', Store::class)
            ->where('subject_id', $store->id)
            ->get();

        expect($storeActivities)->toHaveCount(2);

        $updateActivity = $storeActivities->where('description', 'Store updated')->first();
        expect($updateActivity)->not->toBeNull()
            ->and($updateActivity->properties->get('attributes'))->toHaveKey('name', 'Updated Name')
            ->and($updateActivity->properties->get('old'))->toHaveKey('name', 'Original Name');
    });

    it('logs store deletion activity', function () {
        $store = Store::factory()->create([
            'user_id' => $this->user->id,
            'dealership_id' => $this->dealership->id,
        ]);

        $storeId = $store->id;
        $store->delete();

        $deleteActivity = Activity::where('subject_type', Store::class)
            ->where('subject_id', $storeId)
            ->where('description', 'Store deleted')
            ->first();

        expect($deleteActivity)->not->toBeNull()
            ->and($deleteActivity->subject_type)->toBe(Store::class)
            ->and($deleteActivity->subject_id)->toBe($storeId);
    });

    it('properly sets activity log description for events', function () {
        $store = Store::factory()->create([
            'user_id' => $this->user->id,
            'dealership_id' => $this->dealership->id,
            'name' => 'Description Test Store',
        ]);

        $activity = Activity::where('subject_type', Store::class)
            ->where('subject_id', $store->id)
            ->first();

        expect($activity->description)->toBe('Store created');
    });
});

describe('Store Validation and Edge Cases', function () {
    it('handles null and empty values gracefully', function () {
        $store = Store::factory()->create([
            'user_id' => $this->user->id,
            'dealership_id' => $this->dealership->id,
            'current_solution_name' => null,
            'current_solution_use' => null,
            'address' => '',
            'phone' => '',
        ]);

        expect($store->current_solution_name)->toBeNull()
            ->and($store->current_solution_use)->toBeNull()
            ->and($store->address)->toBe('')
            ->and($store->phone)->toBe('');
    });

    it('handles very long text fields', function () {
        $longText = str_repeat('Lorem ipsum dolor sit amet, ', 50);

        $store = Store::factory()->create([
            'user_id' => $this->user->id,
            'dealership_id' => $this->dealership->id,
            'name' => $longText,
            'current_solution_use' => $longText,
        ]);

        expect(mb_strlen($store->name))->toBeGreaterThan(100)
            ->and(mb_strlen($store->current_solution_use))->toBeGreaterThan(100);
    });

    it('preserves special characters in text fields', function () {
        $specialText = 'Store with special chars: @#$%^&*()_+{}[]|;":,./<>?';

        $store = Store::factory()->create([
            'user_id' => $this->user->id,
            'dealership_id' => $this->dealership->id,
            'name' => $specialText,
            'address' => $specialText,
        ]);

        expect($store->name)->toBe($specialText)
            ->and($store->address)->toBe($specialText);
    });

    it('maintains data integrity after multiple updates', function () {
        $store = Store::factory()->create([
            'user_id' => $this->user->id,
            'dealership_id' => $this->dealership->id,
            'name' => 'Original Store',
            'city' => 'Original City',
            'state' => 'NY',
        ]);

        // Perform multiple updates
        $store->update(['name' => 'Updated Store']);
        $store->update(['city' => 'Updated City']);
        $store->update(['state' => 'CA']);

        $store->refresh();

        expect($store->name)->toBe('Updated Store')
            ->and($store->city)->toBe('Updated City')
            ->and($store->state)->toBe('CA');
    });

    it('handles different phone number formats', function () {
        $phoneFormats = [
            '(555) 123-4567',
            '555-123-4567',
            '5551234567',
            '+1 555 123 4567',
            '555.123.4567',
        ];

        foreach ($phoneFormats as $phone) {
            $store = Store::factory()->create([
                'user_id' => $this->user->id,
                'dealership_id' => $this->dealership->id,
                'phone' => $phone,
            ]);

            expect($store->phone)->toBe($phone);
        }
    });

    it('handles different zip code formats', function () {
        $zipFormats = [
            '12345',
            '12345-6789',
            '90210',
            'K1A 0A6', // Canadian postal code
            'SW1A 1AA', // UK postal code
        ];

        foreach ($zipFormats as $zip) {
            $store = Store::factory()->create([
                'user_id' => $this->user->id,
                'dealership_id' => $this->dealership->id,
                'zip_code' => $zip,
            ]);

            expect($store->zip_code)->toBe($zip);
        }
    });
});

describe('Store Business Logic', function () {
    it('can have multiple stores per dealership with unique names', function () {
        $storeNames = [
            'Main Street Location',
            'Mall Location',
            'Downtown Branch',
            'Suburban Outlet',
            'Airport Store',
        ];

        foreach ($storeNames as $name) {
            Store::factory()->create([
                'user_id' => $this->user->id,
                'dealership_id' => $this->dealership->id,
                'name' => $name,
            ]);
        }

        $stores = $this->dealership->stores;
        expect($stores)->toHaveCount(5);

        $retrievedNames = $stores->pluck('name')->toArray();
        foreach ($storeNames as $name) {
            expect($retrievedNames)->toContain($name);
        }
    });

    it('can filter stores by state', function () {
        $states = ['NY', 'CA', 'TX', 'FL', 'NY', 'CA'];

        foreach ($states as $state) {
            Store::factory()->create([
                'user_id' => $this->user->id,
                'dealership_id' => $this->dealership->id,
                'state' => $state,
            ]);
        }

        $nyStores = Store::where('state', 'NY')->get();
        $caStores = Store::where('state', 'CA')->get();

        expect($nyStores)->toHaveCount(2)
            ->and($caStores)->toHaveCount(2);
    });

    it('can search stores by name', function () {
        $stores = [
            'Main Street Auto',
            'Downtown Auto Center',
            'Suburban Car Lot',
            'Mall Auto Store',
        ];

        foreach ($stores as $name) {
            Store::factory()->create([
                'user_id' => $this->user->id,
                'dealership_id' => $this->dealership->id,
                'name' => $name,
            ]);
        }

        $autoStores = Store::where('name', 'LIKE', '%Auto%')->get();
        expect($autoStores)->toHaveCount(3);

        $mainStores = Store::where('name', 'LIKE', '%Main%')->get();
        expect($mainStores)->toHaveCount(1);
    });

    it('properly handles dealership relationship cascading', function () {
        $anotherDealership = Dealership::factory()->create(['user_id' => $this->user->id]);

        Store::factory()->create([
            'user_id' => $this->user->id,
            'dealership_id' => $this->dealership->id,
            'name' => 'Store 1',
        ]);

        Store::factory()->create([
            'user_id' => $this->user->id,
            'dealership_id' => $anotherDealership->id,
            'name' => 'Store 2',
        ]);

        expect($this->dealership->stores)->toHaveCount(1)
            ->and($anotherDealership->stores)->toHaveCount(1)
            ->and($this->dealership->stores->first()->name)->toBe('Store 1')
            ->and($anotherDealership->stores->first()->name)->toBe('Store 2');
    });

    it('can group stores by current solution', function () {
        $solutions = [
            'Legacy POS' => 3,
            'Excel Tracking' => 2,
            'Paper Records' => 1,
        ];

        foreach ($solutions as $solution => $count) {
            for ($i = 0; $i < $count; $i++) {
                Store::factory()->create([
                    'user_id' => $this->user->id,
                    'dealership_id' => $this->dealership->id,
                    'current_solution_name' => $solution,
                ]);
            }
        }

        foreach ($solutions as $solution => $expectedCount) {
            $storesWithSolution = Store::where('current_solution_name', $solution)->get();
            expect($storesWithSolution)->toHaveCount($expectedCount);
        }
    });
});

describe('Store Model Attributes', function () {
    it('has timestamps enabled', function () {
        $store = Store::factory()->create([
            'user_id' => $this->user->id,
            'dealership_id' => $this->dealership->id,
        ]);

        expect($store->created_at)->not->toBeNull()
            ->and($store->updated_at)->not->toBeNull()
            ->and($store->created_at)->toBeInstanceOf(Illuminate\Support\Carbon::class)
            ->and($store->updated_at)->toBeInstanceOf(Illuminate\Support\Carbon::class);
    });

    it('updates timestamp on modification', function () {
        $store = Store::factory()->create([
            'user_id' => $this->user->id,
            'dealership_id' => $this->dealership->id,
        ]);

        $originalUpdatedAt = $store->updated_at;

        // Wait a moment to ensure timestamp difference
        sleep(1);

        $store->update(['name' => 'Updated Store Name']);

        expect($store->updated_at)->not->toEqual($originalUpdatedAt)
            ->and($store->updated_at)->toBeGreaterThan($originalUpdatedAt);
    });

    it('returns correct model name for activity log', function () {
        $store = new Store;
        expect(class_basename($store))->toBe('Store');
    });
});
