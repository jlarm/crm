<?php

declare(strict_types=1);

use App\Models\Contact;
use App\Models\Dealership;
use App\Models\User;
use App\Observers\ContactObserver;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\delete;
use function Pest\Laravel\patch;
use function Pest\Laravel\post;
use function Pest\Laravel\put;

beforeEach(function () {
    ContactObserver::$syncMailcoach = false;
    $this->user = User::factory()->create();
    actingAs($this->user);
    $this->dealership = Dealership::factory()->create(['user_id' => $this->user->id]);
});

afterEach(function () {
    ContactObserver::$syncMailcoach = true;
});

describe('DealershipContactController store', function () {
    it('creates a contact for the dealership', function () {
        post(route('dealerships.contacts.store', $this->dealership), [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '555-1234',
            'position' => 'Owner',
            'primary_contact' => true,
        ])->assertRedirect();

        $this->assertDatabaseHas('contacts', [
            'dealership_id' => $this->dealership->id,
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'primary_contact' => true,
        ]);
    });

    it('validates required name', function () {
        post(route('dealerships.contacts.store', $this->dealership), [
            'email' => 'foo@bar.com',
        ])->assertSessionHasErrors('name');
    });

    it('validates email format', function () {
        post(route('dealerships.contacts.store', $this->dealership), [
            'name' => 'Bob',
            'email' => 'not-an-email',
        ])->assertSessionHasErrors('email');
    });
});

describe('DealershipContactController update', function () {
    it('updates a contact', function () {
        $contact = Contact::factory()->create(['dealership_id' => $this->dealership->id, 'name' => 'Old']);

        put(route('dealerships.contacts.update', [$this->dealership, $contact]), [
            'name' => 'New Name',
            'email' => 'new@example.com',
        ])->assertRedirect();

        expect($contact->fresh()->name)->toBe('New Name');
    });

    it('aborts when contact does not belong to dealership', function () {
        $other = Dealership::factory()->create(['user_id' => $this->user->id]);
        $contact = Contact::factory()->create(['dealership_id' => $other->id]);

        put(route('dealerships.contacts.update', [$this->dealership, $contact]), [
            'name' => 'New Name',
        ])->assertNotFound();
    });
});

describe('DealershipContactController togglePrimary', function () {
    it('sets the contact as primary and clears others', function () {
        $existingPrimary = Contact::factory()->primary()->create(['dealership_id' => $this->dealership->id]);
        $contact = Contact::factory()->nonPrimary()->create(['dealership_id' => $this->dealership->id]);

        patch(route('dealerships.contacts.primary', [$this->dealership, $contact]))->assertRedirect();

        expect($contact->fresh()->primary_contact)->toBeTrue()
            ->and($existingPrimary->fresh()->primary_contact)->toBeFalse();
    });

    it('clears primary when toggling an already-primary contact', function () {
        $contact = Contact::factory()->primary()->create(['dealership_id' => $this->dealership->id]);

        patch(route('dealerships.contacts.primary', [$this->dealership, $contact]))->assertRedirect();

        expect($contact->fresh()->primary_contact)->toBeFalse();
    });

    it('aborts when contact does not belong to dealership', function () {
        $other = Dealership::factory()->create(['user_id' => $this->user->id]);
        $contact = Contact::factory()->create(['dealership_id' => $other->id]);

        patch(route('dealerships.contacts.primary', [$this->dealership, $contact]))->assertNotFound();
    });
});

describe('DealershipContactController destroy', function () {
    it('deletes the contact', function () {
        $contact = Contact::factory()->create(['dealership_id' => $this->dealership->id]);

        delete(route('dealerships.contacts.destroy', [$this->dealership, $contact]))->assertRedirect();

        $this->assertDatabaseMissing('contacts', ['id' => $contact->id]);
    });

    it('aborts when contact does not belong to dealership', function () {
        $other = Dealership::factory()->create(['user_id' => $this->user->id]);
        $contact = Contact::factory()->create(['dealership_id' => $other->id]);

        delete(route('dealerships.contacts.destroy', [$this->dealership, $contact]))->assertNotFound();
    });
});
