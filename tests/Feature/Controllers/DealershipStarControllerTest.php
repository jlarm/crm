<?php

declare(strict_types=1);

use App\Models\Dealership;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\put;

beforeEach(function () {
    $this->user = User::factory()->create();
    actingAs($this->user);
    $this->dealership = Dealership::factory()->create(['user_id' => $this->user->id, 'name' => 'Smith Ford']);
});

it('stars a dealership, once, for the signed-in user', function () {
    put(route('dealerships.star.store', $this->dealership))->assertRedirect();
    put(route('dealerships.star.store', $this->dealership))->assertRedirect();

    expect($this->user->starredDealerships()->pluck('dealerships.id')->all())->toBe([$this->dealership->id]);
});

it('unstars a dealership', function () {
    $this->user->starredDealerships()->attach($this->dealership);

    delete(route('dealerships.star.destroy', $this->dealership))->assertRedirect();

    expect($this->user->starredDealerships()->count())->toBe(0);
});

it('keeps stars personal', function () {
    $colleague = User::factory()->create();
    $colleague->starredDealerships()->attach($this->dealership);

    get(route('dealerships.show', $this->dealership))
        ->assertInertia(fn ($page) => $page->where('isStarred', false));

    put(route('dealerships.star.store', $this->dealership));

    get(route('dealerships.show', $this->dealership))
        ->assertInertia(fn ($page) => $page->where('isStarred', true));
});

it('lists the user\'s starred dealerships alphabetically on the dashboard', function () {
    $zeta = Dealership::factory()->create(['user_id' => $this->user->id, 'name' => 'Zeta Motors', 'city' => 'Elgin', 'state' => 'IL', 'rating' => 'Hot']);
    $this->user->starredDealerships()->attach([$zeta->id, $this->dealership->id]);
    User::factory()->create()->starredDealerships()->attach(Dealership::factory()->create(['user_id' => $this->user->id, 'name' => 'Not Mine']));

    get(route('dashboard'))
        ->assertInertia(fn ($page) => $page
            ->has('starredDealerships', 2)
            ->where('starredDealerships.0.name', 'Smith Ford')
            ->where('starredDealerships.1', [
                'id' => $zeta->id,
                'name' => 'Zeta Motors',
                'city' => 'Elgin',
                'state' => 'IL',
                'rating' => 'hot',
                'ratingLabel' => 'Hot',
            ])
        );
});

it('redirects guests to the login page', function () {
    auth()->logout();

    put(route('dealerships.star.store', $this->dealership))->assertRedirect(route('login'));
});
