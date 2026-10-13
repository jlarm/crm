<?php

declare(strict_types=1);

use App\Models\Contact;
use App\Models\Dealership;
use App\Models\Store;
use App\Models\User;
use App\Support\TitleCase;

it('title-cases text written in all capitals', function (string $input, string $expected) {
    expect(TitleCase::fromAllCaps($input))->toBe($expected);
})->with([
    'dealer name' => ['5 CORNERS DODGE CHRYSLER JEEP RAM', '5 Corners Dodge Chrysler Jeep Ram'],
    'acronyms and small words' => ['MCDONALD RV CENTER OF ELGIN', 'McDonald RV Center of Elgin'],
    'company suffixes' => ['SARASOTA TOY CARS, LLC', 'Sarasota Toy Cars, LLC'],
    'apostrophes and numbers' => ["O'BRIEN 4X4 MOTORS", "O'Brien 4x4 Motors"],
    'street address' => ['123 N MAIN ST, STE 1ST', '123 N Main St, Ste 1st'],
    'state route' => ['1305 SC-72', '1305 SC-72'],
    'hyphenated brand' => ['MERCEDES-BENZ OF NAPERVILLE', 'Mercedes-Benz of Naperville'],
    'job title' => ['GENERAL SALES MANAGER', 'General Sales Manager'],
    'job title acronyms' => ['CFO/COO', 'CFO/COO'],
]);

it('keeps text that is already in mixed case or lowercase', function (string $input) {
    expect(TitleCase::fromAllCaps($input))->toBe($input);
})->with(['McKinney Ford', 'BMW of Elgin', 'smith ford', 'A', '12345']);

it('saves all-caps names, addresses and positions in title case', function () {
    $user = User::factory()->create();
    $dealership = Dealership::factory()->create([
        'user_id' => $user->id,
        'name' => '5 CORNERS DODGE CHRYSLER JEEP RAM',
        'address' => '200 JACARANDA BLVD',
        'city' => 'VENICE',
        'state' => 'FL',
        'email' => 'SALES@5CORNERS.TEST',
    ]);
    $store = Store::factory()->create(['dealership_id' => $dealership->id, 'name' => 'NORTH LOT', 'city' => 'ELGIN']);
    $contact = Contact::factory()->create(['dealership_id' => $dealership->id, 'name' => 'BRIAN WILLIS', 'position' => 'GENERAL MANAGER']);

    expect($dealership->fresh()->only(['name', 'address', 'city', 'state', 'email']))->toBe([
        'name' => '5 Corners Dodge Chrysler Jeep Ram',
        'address' => '200 Jacaranda Blvd',
        'city' => 'Venice',
        'state' => 'FL',
        'email' => 'SALES@5CORNERS.TEST',
    ])
        ->and($store->fresh()->only(['name', 'city']))->toBe(['name' => 'North Lot', 'city' => 'Elgin'])
        ->and($contact->fresh()->only(['name', 'position']))->toBe(['name' => 'Brian Willis', 'position' => 'General Manager']);
});

it('leaves mixed-case names as typed when saving', function () {
    $dealership = Dealership::factory()->create(['user_id' => User::factory()->create()->id, 'name' => 'McKinney BMW of Elgin']);

    expect($dealership->fresh()->name)->toBe('McKinney BMW of Elgin');
});

it('keeps route codes after accented words', function () {
    expect(TitleCase::fromAllCaps('ÉCOLE ROAD AT NY-17'))->toBe('École Road at NY-17');
});
