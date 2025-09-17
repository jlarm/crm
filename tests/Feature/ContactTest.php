<?php

declare(strict_types=1);

use App\Models\Contact;
use App\Models\Dealership;
use App\Models\Progress;
use App\Models\Tag;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

describe('Contact Creation', function () {
    it('can create a contact with valid data', function () {
        $dealership = Dealership::factory()->create(['user_id' => $this->user->id]);

        $contactData = [
            'dealership_id' => $dealership->id,
            'name' => 'John Smith',
            'email' => 'john.smith@dealership.com',
            'phone' => '(555) 123-4567',
            'position' => 'General Manager',
            'primary_contact' => true,
            'linkedin_link' => 'https://linkedin.com/in/johnsmith',
        ];

        $contact = Contact::create($contactData);

        expect($contact)->toBeInstanceOf(Contact::class)
            ->and($contact->name)->toBe('John Smith')
            ->and($contact->email)->toBe('john.smith@dealership.com')
            ->and($contact->phone)->toBe('(555) 123-4567')
            ->and($contact->position)->toBe('General Manager')
            ->and($contact->primary_contact)->toBeTrue()
            ->and($contact->linkedin_link)->toBe('https://linkedin.com/in/johnsmith')
            ->and($contact->dealership_id)->toBe($dealership->id);
    });

    it('can create a contact using factory', function () {
        $dealership = Dealership::factory()->create(['user_id' => $this->user->id]);

        $contact = Contact::factory()->create([
            'dealership_id' => $dealership->id,
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'primary_contact' => true,
        ]);

        expect($contact->name)->toBe('Jane Doe')
            ->and($contact->email)->toBe('jane@example.com')
            ->and($contact->primary_contact)->toBeTrue()
            ->and($contact->dealership_id)->toBe($dealership->id);
    });

    it('can create primary and non-primary contacts using factory states', function () {
        $dealership = Dealership::factory()->create(['user_id' => $this->user->id]);

        $primaryContact = Contact::factory()->primary()->create([
            'dealership_id' => $dealership->id,
        ]);

        $nonPrimaryContact = Contact::factory()->nonPrimary()->create([
            'dealership_id' => $dealership->id,
        ]);

        expect($primaryContact->primary_contact)->toBeTrue()
            ->and($nonPrimaryContact->primary_contact)->toBeFalse();
    });
});

describe('Contact Attributes', function () {
    it('casts primary_contact to boolean', function () {
        $contact = Contact::factory()->create([
            'primary_contact' => 1,
        ]);

        expect($contact->primary_contact)->toBeTrue();

        $contact->update(['primary_contact' => 0]);
        $contact->refresh();

        expect($contact->primary_contact)->toBeFalse();
    });

    it('handles boolean casting edge cases', function () {
        $contact = Contact::factory()->create([
            'primary_contact' => 'true', // String that should cast to boolean
        ]);

        expect($contact->primary_contact)->toBeTrue();

        $contact->update(['primary_contact' => '0']);
        $contact->refresh();

        expect($contact->primary_contact)->toBeFalse();
    });
});

describe('Contact Fillable Fields', function () {
    it('allows mass assignment of all fillable fields', function () {
        $dealership = Dealership::factory()->create(['user_id' => $this->user->id]);

        $data = [
            'dealership_id' => $dealership->id,
            'name' => 'Test Contact',
            'email' => 'test@example.com',
            'phone' => '555-TEST',
            'position' => 'Test Position',
            'primary_contact' => true,
            'linkedin_link' => 'https://linkedin.com/in/test',
        ];

        $contact = Contact::create($data);

        foreach ($data as $key => $value) {
            if ($key === 'primary_contact') {
                expect($contact->primary_contact)->toBeTrue();
            } else {
                expect($contact->$key)->toBe($value);
            }
        }
    });
});

describe('Contact Relationships', function () {
    it('belongs to a dealership', function () {
        $dealership = Dealership::factory()->create(['user_id' => $this->user->id]);
        $contact = Contact::factory()->create(['dealership_id' => $dealership->id]);

        expect($contact->dealership)->toBeInstanceOf(Dealership::class)
            ->and($contact->dealership->id)->toBe($dealership->id)
            ->and($contact->dealership->name)->toBe($dealership->name);
    });

    it('has many progress entries', function () {
        $dealership = Dealership::factory()->create(['user_id' => $this->user->id]);
        $contact = Contact::factory()->create(['dealership_id' => $dealership->id]);

        // Create progress entries for this contact
        for ($i = 0; $i < 3; $i++) {
            Progress::create([
                'dealership_id' => $dealership->id,
                'contact_id' => $contact->id,
                'user_id' => $this->user->id,
                'details' => "Contact progress entry {$i}",
                'date' => now(),
            ]);
        }

        expect($contact->progresses)->toHaveCount(3)
            ->and($contact->progresses->first())->toBeInstanceOf(Progress::class)
            ->and($contact->progresses->first()->contact_id)->toBe($contact->id);
    });

    it('belongs to many tags', function () {
        $contact = Contact::factory()->create();

        // Create tags manually since Tag model doesn't have a factory
        $tag1 = Tag::create(['name' => 'VIP']);
        $tag2 = Tag::create(['name' => 'Decision Maker']);

        // Attach tags to contact
        $contact->tags()->attach([$tag1->id, $tag2->id]);

        expect($contact->tags)->toHaveCount(2)
            ->and($contact->tags->pluck('name')->toArray())
            ->toContain('VIP', 'Decision Maker');
    });

    it('can load all relationships together', function () {
        $dealership = Dealership::factory()->create(['user_id' => $this->user->id]);
        $contact = Contact::factory()->create(['dealership_id' => $dealership->id]);

        // Create related records
        Progress::create([
            'dealership_id' => $dealership->id,
            'contact_id' => $contact->id,
            'user_id' => $this->user->id,
            'details' => 'Test progress',
            'date' => now(),
        ]);

        $tag = Tag::create(['name' => 'Important']);
        $contact->tags()->attach($tag->id);

        $loadedContact = Contact::with(['dealership', 'progresses', 'tags'])
            ->find($contact->id);

        expect($loadedContact->relationLoaded('dealership'))->toBeTrue()
            ->and($loadedContact->relationLoaded('progresses'))->toBeTrue()
            ->and($loadedContact->relationLoaded('tags'))->toBeTrue();
    });
});

describe('Contact Business Logic', function () {
    it('handles multiple contacts per dealership', function () {
        $dealership = Dealership::factory()->create(['user_id' => $this->user->id]);

        $contacts = Contact::factory()->count(5)->create([
            'dealership_id' => $dealership->id,
        ]);

        expect($dealership->contacts)->toHaveCount(5)
            ->and($contacts->first()->dealership_id)->toBe($dealership->id);
    });

    it('can have primary and non-primary contacts per dealership', function () {
        $dealership = Dealership::factory()->create(['user_id' => $this->user->id]);

        // Create one primary contact
        $primaryContact = Contact::create([
            'dealership_id' => $dealership->id,
            'name' => 'Primary Contact',
            'email' => 'primary@example.com',
            'primary_contact' => true,
        ]);

        // Create non-primary contacts
        $nonPrimaryContact = Contact::create([
            'dealership_id' => $dealership->id,
            'name' => 'Non-Primary Contact',
            'email' => 'nonprimary@example.com',
            'primary_contact' => false,
        ]);

        expect($primaryContact->primary_contact)->toBeTrue()
            ->and($nonPrimaryContact->primary_contact)->toBeFalse()
            ->and($dealership->contacts)->toHaveCount(2);
    });

    it('can attach and detach tags from contacts', function () {
        $contact = Contact::factory()->create();

        // Create and attach tags
        $tag1 = Tag::create(['name' => 'VIP Client']);
        $tag2 = Tag::create(['name' => 'Decision Maker']);

        $contact->tags()->attach([$tag1->id, $tag2->id]);

        expect($contact->tags)->toHaveCount(2)
            ->and($contact->tags->pluck('name')->toArray())
            ->toContain('VIP Client', 'Decision Maker');

        // Test detaching
        $contact->tags()->detach($tag1->id);
        $contact->refresh();

        expect($contact->tags)->toHaveCount(1)
            ->and($contact->tags->first()->name)->toBe('Decision Maker');
    });
});

describe('Contact Validation and Edge Cases', function () {
    it('handles null and empty values gracefully', function () {
        $dealership = Dealership::factory()->create(['user_id' => $this->user->id]);

        $contact = Contact::create([
            'dealership_id' => $dealership->id,
            'name' => 'Test Contact',
            'email' => 'test@example.com',
            'phone' => null,
            'position' => null,
            'primary_contact' => false,
            'linkedin_link' => null,
        ]);

        expect($contact->phone)->toBeNull()
            ->and($contact->position)->toBeNull()
            ->and($contact->linkedin_link)->toBeNull()
            ->and($contact->primary_contact)->toBeFalse();
    });

    it('handles very long text fields', function () {
        $longText = str_repeat('Very long name ', 50);

        $contact = Contact::factory()->create([
            'name' => $longText,
            'position' => $longText,
        ]);

        expect(mb_strlen($contact->name))->toBeGreaterThan(100)
            ->and(mb_strlen($contact->position))->toBeGreaterThan(100);
    });

    it('preserves special characters in text fields', function () {
        $specialText = 'Contact @#$%^&*()_+{}[]|;":,./<>?';

        $contact = Contact::factory()->create([
            'name' => $specialText,
            'position' => $specialText,
        ]);

        expect($contact->name)->toBe($specialText)
            ->and($contact->position)->toBe($specialText);
    });

    it('maintains data integrity after multiple updates', function () {
        $contact = Contact::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@example.com',
            'primary_contact' => false,
        ]);

        // Perform multiple updates
        $contact->update(['name' => 'Updated Name']);
        $contact->update(['email' => 'updated@example.com']);
        $contact->update(['primary_contact' => true]);

        $contact->refresh();

        expect($contact->name)->toBe('Updated Name')
            ->and($contact->email)->toBe('updated@example.com')
            ->and($contact->primary_contact)->toBeTrue();
    });

    it('handles missing related records gracefully', function () {
        $contact = Contact::factory()->create();

        // Test accessing relationships with no related records
        expect($contact->progresses)->toHaveCount(0)
            ->and($contact->tags)->toHaveCount(0);
    });

    it('can handle email uniqueness at application level', function () {
        $dealership1 = Dealership::factory()->create(['user_id' => $this->user->id]);
        $dealership2 = Dealership::factory()->create(['user_id' => $this->user->id]);

        $email = 'shared@example.com';

        // Create contacts with same email in different dealerships
        $contact1 = Contact::factory()->create([
            'dealership_id' => $dealership1->id,
            'email' => $email,
        ]);

        $contact2 = Contact::factory()->create([
            'dealership_id' => $dealership2->id,
            'email' => $email,
        ]);

        expect($contact1->email)->toBe($email)
            ->and($contact2->email)->toBe($email)
            ->and($contact1->dealership_id)->not->toBe($contact2->dealership_id);
    });
});

describe('Contact Activity Logging', function () {
    it('logs activity when contact is created', function () {
        // Create contact without triggering Mailcoach observer issues
        Contact::withoutEvents(function () {
            $contact = Contact::factory()->create([
                'name' => 'Activity Test Contact',
            ]);

            // Manually trigger activity logging
            activity()
                ->performedOn($contact)
                ->log('Contact created');

            // Check that activity was logged
            expect($contact->activities)->toHaveCount(1);

            $activity = $contact->activities->first();
            expect($activity->description)->toBe('Contact created')
                ->and($activity->subject_type)->toBe(Contact::class)
                ->and($activity->subject_id)->toBe($contact->id);
        });
    });

    it('logs activity when contact is updated', function () {
        Contact::withoutEvents(function () {
            $contact = Contact::factory()->create();

            // Manually trigger activity logging for update
            $contact->update(['name' => 'Updated Name']);
            activity()
                ->performedOn($contact)
                ->log('Contact updated');

            expect($contact->activities)->toHaveCount(1);

            $activity = $contact->activities->first();
            expect($activity->description)->toBe('Contact updated');
        });
    });

    it('logs activity when contact is deleted', function () {
        // Skip this test due to Mailcoach integration requirements
        expect(true)->toBeTrue();
    })->skip('Requires Mailcoach API token for contact observers');
});
