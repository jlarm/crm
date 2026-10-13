<?php

declare(strict_types=1);

use App\Models\Dealership;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('clears a shown flash message on partial reloads so it is not toasted again', function () {
    $user = User::factory()->create();
    actingAs($user);
    $dealership = Dealership::factory()->create(['user_id' => $user->id]);

    get(route('dealerships.show', $dealership))
        ->assertInertia(fn ($page) => $page
            ->where('flash.success', null)
            ->reloadOnly('emailThreads', fn ($reload) => $reload
                ->has('emailThreads')
                ->where('flash.success', null)
            )
        );

    $this->withSession(['success' => 'Email is on its way.']);

    get(route('dealerships.show', $dealership))
        ->assertInertia(fn ($page) => $page->where('flash.success', 'Email is on its way.'));
});
