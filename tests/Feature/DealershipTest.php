<?php

declare(strict_types=1);

use App\Enum\DevStatus;
use App\Enum\ReminderFrequency;
use App\Models\Contact;
use App\Models\DealerEmail;
use App\Models\Dealership;
use App\Models\Progress;
use App\Models\SentEmail;
use App\Models\Store;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

describe('Dealership Creation', function () {
    it('can create a dealership with valid data', function () {
        $dealershipData = [
            'user_id' => $this->user->id,
            'name' => 'Prime Motors',
            'address' => '123 Main St',
            'city' => 'Detroit',
            'state' => 'MI',
            'zip_code' => '48201',
            'phone' => '(555) 123-4567',
            'email' => 'contact@primemotors.com',
            'current_solution_name' => 'Legacy CRM',
            'current_solution_use' => 'Basic lead tracking',
            'notes' => 'Interested in upgrading their system',
            'status' => 'Active',
            'rating' => 'Hot',
            'type' => 'Automotive',
            'in_development' => false,
            'dev_status' => DevStatus::NO_CONTACT,
        ];

        $dealership = Dealership::create($dealershipData);

        expect($dealership)->toBeInstanceOf(Dealership::class)
            ->and($dealership->name)->toBe('Prime Motors')
            ->and($dealership->type)->toBe('Automotive')
            ->and($dealership->rating)->toBe('Hot')
            ->and($dealership->in_development)->toBeFalse()
            ->and($dealership->dev_status)->toBe(DevStatus::NO_CONTACT);
    });

    it('can create a dealership using factory', function () {
        $dealership = Dealership::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Custom Dealership',
            'type' => 'RV',
        ]);

        expect($dealership->name)->toBe('Custom Dealership')
            ->and($dealership->type)->toBe('RV')
            ->and($dealership->user_id)->toBe($this->user->id);
    });

    it('can create multiple dealerships with different types', function () {
        $types = ['Automotive', 'RV', 'Motorsports', 'Maritime', 'Association'];

        foreach ($types as $type) {
            $dealership = Dealership::factory()->create([
                'user_id' => $this->user->id,
                'type' => $type,
            ]);

            expect($dealership->type)->toBe($type);
        }

        expect(Dealership::count())->toBe(5);
    });
});

describe('Dealership Attributes', function () {
    it('casts in_development to boolean', function () {
        $dealership = Dealership::factory()->create([
            'user_id' => $this->user->id,
            'in_development' => 1,
        ]);

        expect($dealership->in_development)->toBeTrue();

        $dealership->update(['in_development' => 0]);
        $dealership->refresh();

        expect($dealership->in_development)->toBeFalse();
    });

    it('casts dev_status to DevStatus enum', function () {
        $dealership = Dealership::factory()->create([
            'user_id' => $this->user->id,
            'dev_status' => 'in_contact',
        ]);

        expect($dealership->dev_status)->toBe(DevStatus::IN_CONTACT)
            ->and($dealership->dev_status->getLabel())->toBe('In Contact');
    });

    it('handles all dev status values correctly', function () {
        $statuses = [
            'no_contact' => DevStatus::NO_CONTACT,
            'reached_out' => DevStatus::REACHED_OUT,
            'in_contact' => DevStatus::IN_CONTACT,
        ];

        foreach ($statuses as $statusValue => $expectedEnum) {
            $dealership = Dealership::factory()->create([
                'user_id' => $this->user->id,
                'dev_status' => $statusValue,
            ]);

            expect($dealership->dev_status)->toBe($expectedEnum);
        }
    });
});

describe('Dealership Fillable Fields', function () {
    it('allows mass assignment of all fillable fields', function () {
        $data = [
            'user_id' => $this->user->id,
            'name' => 'Test Dealership',
            'address' => '456 Test Ave',
            'city' => 'Test City',
            'state' => 'TS',
            'zip_code' => '12345',
            'phone' => '555-TEST',
            'email' => 'test@test.com',
            'current_solution_name' => 'Test CRM',
            'current_solution_use' => 'Testing',
            'notes' => 'Test notes',
            'status' => 'Test Status',
            'rating' => 'Cold',
            'type' => 'Automotive',
            'in_development' => true,
            'dev_status' => 'no_contact',
        ];

        $dealership = Dealership::create($data);

        foreach ($data as $key => $value) {
            if ($key === 'dev_status') {
                expect($dealership->dev_status)->toBe(DevStatus::NO_CONTACT);
            } elseif ($key === 'in_development') {
                expect($dealership->in_development)->toBeTrue();
            } else {
                expect($dealership->$key)->toBe($value);
            }
        }
    });
});

describe('Dealership Relationships', function () {
    it('belongs to many users', function () {
        $dealership = Dealership::factory()->create(['user_id' => $this->user->id]);
        $anotherUser = User::factory()->create();

        // Attach multiple users to the dealership
        $dealership->users()->attach([$this->user->id, $anotherUser->id]);

        expect($dealership->users)->toHaveCount(2)
            ->and($dealership->users->pluck('id')->toArray())
            ->toContain($this->user->id, $anotherUser->id);
    });

    it('has many stores', function () {
        $dealership = Dealership::factory()->create(['user_id' => $this->user->id]);

        // Create stores manually since Store doesn't have a factory
        for ($i = 0; $i < 3; $i++) {
            Store::create([
                'user_id' => $this->user->id,
                'dealership_id' => $dealership->id,
                'name' => "Store {$i}",
                'address' => "123 Main St {$i}",
                'city' => 'Test City',
                'state' => 'TS',
                'zip_code' => '12345',
                'phone' => '555-0123',
                'current_solution_name' => 'Test Solution',
                'current_solution_use' => 'Basic tracking',
            ]);
        }

        expect($dealership->stores)->toHaveCount(3)
            ->and($dealership->stores->first())->toBeInstanceOf(Store::class);
    });

    it('has many contacts', function () {
        $dealership = Dealership::factory()->create(['user_id' => $this->user->id]);

        // Create contacts manually since Contact doesn't have a factory
        for ($i = 0; $i < 5; $i++) {
            Contact::create([
                'dealership_id' => $dealership->id,
                'name' => "Contact {$i}",
                'email' => "contact{$i}@example.com",
                'phone' => '555-0123',
                'position' => 'Manager',
                'primary_contact' => $i === 0,
            ]);
        }

        expect($dealership->contacts)->toHaveCount(5)
            ->and($dealership->contacts->first())->toBeInstanceOf(Contact::class);
    });

    it('has many progress entries', function () {
        $dealership = Dealership::factory()->create(['user_id' => $this->user->id]);

        // Create progress entries manually since Progress doesn't have a factory
        for ($i = 0; $i < 4; $i++) {
            Progress::create([
                'dealership_id' => $dealership->id,
                'user_id' => $this->user->id,
                'details' => "Test progress details {$i}",
                'date' => now(),
            ]);
        }

        expect($dealership->progresses)->toHaveCount(4)
            ->and($dealership->progresses->first())->toBeInstanceOf(Progress::class);
    });

    it('has many dealer emails', function () {
        $dealership = Dealership::factory()->create(['user_id' => $this->user->id]);

        // Create dealer emails manually since DealerEmail doesn't have a factory
        for ($i = 0; $i < 2; $i++) {
            DealerEmail::create([
                'dealership_id' => $dealership->id,
                'user_id' => $this->user->id,
                'recipients' => ["test{$i}@example.com"],
                'subject' => "Test Subject {$i}",
                'message' => "Test message {$i}",
                'frequency' => ReminderFrequency::Immediate,
            ]);
        }

        expect($dealership->dealerEmails)->toHaveCount(2)
            ->and($dealership->dealerEmails->first())->toBeInstanceOf(DealerEmail::class);
    });

    it('has many sent emails', function () {
        $dealership = Dealership::factory()->create(['user_id' => $this->user->id]);

        // Create sent emails manually since SentEmail doesn't have a factory
        for ($i = 0; $i < 3; $i++) {
            SentEmail::create([
                'dealership_id' => $dealership->id,
                'user_id' => $this->user->id,
                'recipient' => "recipient{$i}@example.com",
                'message_id' => "test-message-{$i}",
                'subject' => "Test Email {$i}",
                'tracking_data' => [],
            ]);
        }

        expect($dealership->sentEMails)->toHaveCount(3)
            ->and($dealership->sentEMails->first())->toBeInstanceOf(SentEmail::class);
    });

    it('can load all relationships together', function () {
        $dealership = Dealership::factory()->create(['user_id' => $this->user->id]);

        // Create related records manually
        Store::create([
            'user_id' => $this->user->id,
            'dealership_id' => $dealership->id,
            'name' => 'Test Store',
            'address' => '123 Test St',
            'city' => 'Test City',
            'state' => 'TS',
            'zip_code' => '12345',
            'phone' => '555-0123',
        ]);

        Contact::create([
            'dealership_id' => $dealership->id,
            'name' => 'Test Contact',
            'email' => 'test@example.com',
            'phone' => '555-0123',
            'position' => 'Manager',
            'primary_contact' => true,
        ]);

        Progress::create([
            'dealership_id' => $dealership->id,
            'user_id' => $this->user->id,
            'details' => 'Test progress details',
            'date' => now(),
        ]);

        DealerEmail::create([
            'dealership_id' => $dealership->id,
            'user_id' => $this->user->id,
            'recipients' => ['test@example.com'],
            'subject' => 'Test Subject',
            'message' => 'Test message',
            'frequency' => ReminderFrequency::Immediate,
        ]);

        $loadedDealership = Dealership::with([
            'stores',
            'contacts',
            'progresses',
            'dealerEmails',
            'users',
        ])->find($dealership->id);

        expect($loadedDealership->relationLoaded('stores'))->toBeTrue()
            ->and($loadedDealership->relationLoaded('contacts'))->toBeTrue()
            ->and($loadedDealership->relationLoaded('progresses'))->toBeTrue()
            ->and($loadedDealership->relationLoaded('dealerEmails'))->toBeTrue()
            ->and($loadedDealership->relationLoaded('users'))->toBeTrue();
    });
});

describe('Dealership Business Logic', function () {
    it('calculates total store count correctly', function () {
        $dealership = Dealership::factory()->create(['user_id' => $this->user->id]);

        // Test with no stores (should be 1 - base count)
        expect($dealership->total_store_count)->toBe(1);

        // Add some stores
        for ($i = 0; $i < 3; $i++) {
            Store::create([
                'user_id' => $this->user->id,
                'dealership_id' => $dealership->id,
                'name' => "Store {$i}",
                'address' => "123 Main St {$i}",
                'city' => 'Test City',
                'state' => 'TS',
                'zip_code' => '12345',
                'phone' => '555-0123',
            ]);
        }
        $dealership->refresh();

        expect($dealership->total_store_count)->toBe(4); // 3 stores + 1 base
    });

    it('returns correct list type for each dealership type', function () {
        $typeMapping = [
            'Automotive' => 'f694f7fd-dbb9-489d-bced-03e2fbee78af',
            'RV' => '2d97d6ea-90a0-4b49-90df-980a258884b2',
            'Motorsports' => 'd2a68b06-08e4-4e76-a714-151e07a5a907',
            'Maritime' => '59c46030-5429-4ffd-a192-42926b9b17eb',
        ];

        foreach ($typeMapping as $type => $expectedId) {
            $dealership = Dealership::factory()->create([
                'user_id' => $this->user->id,
                'type' => $type,
            ]);

            expect($dealership->getListType())->toBe($expectedId);
        }
    });

    it('returns default value for unknown dealership type', function () {
        $dealership = Dealership::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'Unknown Type',
        ]);

        expect($dealership->getListType())->toBe('default_value');
    });

    it('handles association type correctly', function () {
        $dealership = Dealership::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'Association',
        ]);

        expect($dealership->getListType())->toBe('default_value');
    });

    it('tracks development status changes', function () {
        $dealership = Dealership::factory()->create([
            'user_id' => $this->user->id,
            'in_development' => false,
            'dev_status' => DevStatus::NO_CONTACT,
        ]);

        expect($dealership->in_development)->toBeFalse()
            ->and($dealership->dev_status)->toBe(DevStatus::NO_CONTACT);

        $dealership->update([
            'in_development' => true,
            'dev_status' => DevStatus::IN_CONTACT,
        ]);

        expect($dealership->in_development)->toBeTrue()
            ->and($dealership->dev_status)->toBe(DevStatus::IN_CONTACT);
    });
});

describe('Dealership Validation and Edge Cases', function () {
    it('handles null and empty values gracefully', function () {
        $dealership = Dealership::factory()->create([
            'user_id' => $this->user->id,
            'current_solution_name' => null,
            'current_solution_use' => null,
            'notes' => null,
            'address' => '',
        ]);

        expect($dealership->current_solution_name)->toBeNull()
            ->and($dealership->current_solution_use)->toBeNull()
            ->and($dealership->notes)->toBeNull()
            ->and($dealership->address)->toBe('');
    });

    it('handles very long text fields', function () {
        $longText = str_repeat('Lorem ipsum dolor sit amet, ', 100);

        $dealership = Dealership::factory()->create([
            'user_id' => $this->user->id,
            'notes' => $longText,
            'current_solution_use' => $longText,
        ]);

        expect(mb_strlen($dealership->notes))->toBeGreaterThan(500)
            ->and(mb_strlen($dealership->current_solution_use))->toBeGreaterThan(500);
    });

    it('preserves special characters in text fields', function () {
        $specialText = 'Test with special chars: @#$%^&*()_+{}[]|;":,./<>?';

        $dealership = Dealership::factory()->create([
            'user_id' => $this->user->id,
            'name' => $specialText,
            'notes' => $specialText,
        ]);

        expect($dealership->name)->toBe($specialText)
            ->and($dealership->notes)->toBe($specialText);
    });

    it('handles boolean casting edge cases', function () {
        $dealership = Dealership::factory()->create([
            'user_id' => $this->user->id,
            'in_development' => 'true', // String that should cast to boolean
        ]);

        expect($dealership->in_development)->toBeTrue();

        $dealership->update(['in_development' => '0']);
        $dealership->refresh();

        expect($dealership->in_development)->toBeFalse();
    });

    it('maintains data integrity after multiple updates', function () {
        $dealership = Dealership::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Original Name',
            'type' => 'Automotive',
            'rating' => 'Hot',
        ]);

        // Perform multiple updates
        $dealership->update(['name' => 'Updated Name']);
        $dealership->update(['type' => 'RV']);
        $dealership->update(['rating' => 'Cold']);

        $dealership->refresh();

        expect($dealership->name)->toBe('Updated Name')
            ->and($dealership->type)->toBe('RV')
            ->and($dealership->rating)->toBe('Cold');
    });

    it('handles concurrent user assignments', function () {
        $dealership = Dealership::factory()->create(['user_id' => $this->user->id]);
        $users = User::factory()->count(5)->create();

        // Attach all users at once
        $dealership->users()->attach($users->pluck('id'));

        expect($dealership->users)->toHaveCount(5);

        // Detach some users
        $dealership->users()->detach($users->take(2)->pluck('id'));

        expect($dealership->fresh()->users)->toHaveCount(3);
    });

    it('works with different dev status enum values', function () {
        $statuses = [
            DevStatus::NO_CONTACT,
            DevStatus::REACHED_OUT,
            DevStatus::IN_CONTACT,
        ];

        foreach ($statuses as $status) {
            $dealership = Dealership::factory()->create([
                'user_id' => $this->user->id,
                'dev_status' => $status,
            ]);

            expect($dealership->dev_status)->toBe($status)
                ->and($dealership->dev_status->getLabel())->toBeString();
        }
    });

    it('handles missing related records gracefully', function () {
        $dealership = Dealership::factory()->create(['user_id' => $this->user->id]);

        // Test accessing relationships with no related records
        expect($dealership->stores)->toHaveCount(0)
            ->and($dealership->contacts)->toHaveCount(0)
            ->and($dealership->progresses)->toHaveCount(0)
            ->and($dealership->dealerEmails)->toHaveCount(0)
            ->and($dealership->sentEMails)->toHaveCount(0);

        // Total store count should still work
        expect($dealership->total_store_count)->toBe(1);
    });
});
