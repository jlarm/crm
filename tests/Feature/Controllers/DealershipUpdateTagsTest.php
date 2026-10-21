<?php

declare(strict_types=1);

use App\Models\Dealership;
use App\Models\Tag;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\put;

beforeEach(function () {
    $this->user = User::factory()->create();
    actingAs($this->user);
    $this->dealership = Dealership::factory()->create(['user_id' => $this->user->id, 'status' => 'active', 'rating' => 'warm']);
});

function dealershipForm(Dealership $dealership, array $overrides = []): array
{
    return array_merge([
        'name' => $dealership->name,
        'status' => $dealership->status,
        'rating' => $dealership->rating,
    ], $overrides);
}

it('replaces a dealership\'s tags on save, creating new ones', function () {
    $old = Tag::factory()->create(['name' => 'Old List']);
    $kept = Tag::factory()->create(['name' => 'Referral']);
    $this->dealership->tags()->attach([$old->id, $kept->id]);

    put(route('dealerships.update', $this->dealership), dealershipForm($this->dealership, ['tags' => ['referral', 'Trade Show']]))
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    expect($this->dealership->tags()->pluck('name')->all())->toBe(['Referral', 'Trade Show'])
        ->and(Tag::count())->toBe(3);
});

it('clears every tag when the form posts an empty tags field', function () {
    $this->dealership->tags()->attach(Tag::factory()->create());

    put(route('dealerships.update', $this->dealership), dealershipForm($this->dealership, ['tags' => '']))
        ->assertSessionHasNoErrors();

    expect($this->dealership->tags()->count())->toBe(0);
});

it('leaves tags alone when the form does not send them', function () {
    $this->dealership->tags()->attach(Tag::factory()->create());

    put(route('dealerships.update', $this->dealership), dealershipForm($this->dealership));

    expect($this->dealership->tags()->count())->toBe(1);
});

it('rejects overly long tag names', function () {
    put(route('dealerships.update', $this->dealership), dealershipForm($this->dealership, ['tags' => [str_repeat('a', 51)]]))
        ->assertSessionHasErrors('tags.0');
});

it('shows the dealership\'s tags and the tags in use', function () {
    $this->dealership->tags()->attach(Tag::factory()->create(['name' => 'Referral']));
    Tag::factory()->create(['name' => 'Unused']);

    get(route('dealerships.show', $this->dealership))
        ->assertInertia(fn ($page) => $page
            ->where('dealership.tags', ['Referral'])
            ->where('tagOptions', ['Referral']));
});
