<?php

declare(strict_types=1);

use App\Models\Contact;
use App\Models\Dealership;
use App\Models\Store;
use App\Models\Task;
use App\Models\User;

it('returns results across dealerships, contacts, stores and tasks', function () {
    $user = User::factory()->create();

    $dealership = Dealership::factory()->create([
        'name' => 'Acme Auto Plaza',
    ]);

    Contact::factory()->create([
        'name' => 'Acme Sales Manager',
        'dealership_id' => $dealership->id,
    ]);

    Store::factory()->create([
        'name' => 'Acme Downtown Store',
        'dealership_id' => $dealership->id,
        'user_id' => $user->id,
    ]);

    Task::factory()->create([
        'title' => 'Follow up with Acme team',
        'user_id' => $user->id,
        'created_by_user_id' => $user->id,
    ]);

    $response = $this->actingAs($user)
        ->getJson(route('search', ['q' => 'Acme']));

    $response->assertOk();

    $types = collect($response->json())->pluck('type')->unique()->values()->all();

    expect($types)->toContain('dealership', 'contact', 'store', 'task');
});

it('does not return tasks belonging to other users', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    Task::factory()->create([
        'title' => 'Confidential thunderstorm task',
        'user_id' => $otherUser->id,
        'created_by_user_id' => $otherUser->id,
    ]);

    $response = $this->actingAs($user)
        ->getJson(route('search', ['q' => 'thunderstorm']));

    $response->assertOk();

    $tasks = collect($response->json())->where('type', 'task');

    expect($tasks)->toHaveCount(0);
});

it('returns an empty array for short queries', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->getJson(route('search', ['q' => 'a']));

    $response->assertOk()->assertExactJson([]);
});
