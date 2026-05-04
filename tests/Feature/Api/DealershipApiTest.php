<?php

declare(strict_types=1);

use App\Models\Contact;
use App\Models\Dealership;
use App\Models\Store;
use App\Models\User;
use Illuminate\Support\Facades\Route;

it('rejects non-GET methods on dealerships', function (string $method) {
    $this->json($method, '/api/dealerships')->assertMethodNotAllowed();
})->with(['POST', 'PUT', 'PATCH', 'DELETE']);

it('exposes no write routes under /api/dealerships', function () {
    $writeRoutes = collect(Route::getRoutes())
        ->filter(fn ($r) => str_starts_with($r->uri(), 'api/dealerships'))
        ->reject(fn ($r) => array_intersect($r->methods(), ['GET', 'HEAD']) !== []
            && array_intersect($r->methods(), ['POST', 'PUT', 'PATCH', 'DELETE']) === []);

    expect($writeRoutes)->toBeEmpty();
});

it('returns dealerships with stores and contacts', function () {
    $user = User::factory()->create();
    $dealership = Dealership::factory()->create(['name' => 'Prime Motors', 'user_id' => $user->id]);
    $store = Store::factory()->create(['dealership_id' => $dealership->id]);
    $contact = Contact::factory()->create(['dealership_id' => $dealership->id]);

    $this->getJson('/api/dealerships')
        ->assertOk()
        ->assertJsonPath('data.0.id', $dealership->id)
        ->assertJsonPath('data.0.name', 'Prime Motors')
        ->assertJsonPath('data.0.stores.0.id', $store->id)
        ->assertJsonPath('data.0.contacts.0.id', $contact->id);
});
