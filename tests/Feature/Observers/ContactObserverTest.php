<?php

declare(strict_types=1);

use App\Models\Contact;
use App\Models\Dealership;
use App\Models\User;

beforeEach(function (): void {
    if (User::count() === 0) {
        User::factory()->create();
    }
});

it('marks other dealership contacts as non-primary when a contact is created', function (): void {
    $dealership = Dealership::factory()->create(['type' => 'Automotive']);
    $existing = Contact::factory()->create([
        'dealership_id' => $dealership->id,
        'primary_contact' => true,
    ]);

    $new = Contact::factory()->create([
        'dealership_id' => $dealership->id,
        'primary_contact' => true,
    ]);

    expect($existing->fresh()->primary_contact)->toBeFalse()
        ->and($new->fresh()->primary_contact)->toBeTrue();
});

it('does not error in created when dealership is missing', function (): void {
    $contact = new Contact([
        'dealership_id' => 99999,
        'name' => 'Lonely',
        'email' => 'lonely@example.com',
    ]);
    $contact->save();

    expect($contact->fresh())->not->toBeNull();
});
