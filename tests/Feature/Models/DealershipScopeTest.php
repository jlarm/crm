<?php

declare(strict_types=1);

use App\Models\Dealership;
use App\Models\User;

describe('Dealership scopeForUser', function (): void {
    it('returns no results when no user is provided', function (): void {
        $user = User::factory()->create();
        Dealership::factory()->count(2)->create(['user_id' => $user->id]);

        // Calling with null should be a no-op and return all dealerships unchanged.
        $results = Dealership::query()->forUser(null)->get();

        expect($results)->toHaveCount(2);
    });

    it('matches dealerships owned by the user or assigned via pivot', function (): void {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $owned = Dealership::factory()->create(['user_id' => $user->id]);
        $shared = Dealership::factory()->create(['user_id' => $other->id]);
        $shared->users()->attach($user->id);
        Dealership::factory()->create(['user_id' => $other->id]);

        $results = Dealership::query()->forUser($user)->get();

        expect($results->pluck('id')->all())->toEqualCanonicalizing([$owned->id, $shared->id]);
    });
});

describe('Dealership scopeSortBy', function (): void {
    it('falls back to ordering by name when an unknown sort is provided', function (): void {
        $user = User::factory()->create();
        Dealership::factory()->create(['user_id' => $user->id, 'name' => 'B']);
        Dealership::factory()->create(['user_id' => $user->id, 'name' => 'A']);

        $results = Dealership::query()->sortBy('not-a-real-column', 'desc')->get();

        expect($results->pluck('name')->all())->toBe(['A', 'B']);
    });
});
