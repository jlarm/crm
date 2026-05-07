<?php

declare(strict_types=1);

use App\Models\Dealership;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

describe('DealershipIndexRequest', function () {
    it('passes with no query parameters', function () {
        $this->get('/dashboard')->assertOk();
    });

    it('passes with all valid filters', function () {
        Dealership::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'active',
            'rating' => 'hot',
            'type' => 'Automotive',
        ]);

        $this->get('/dashboard?'.http_build_query([
            'search' => 'Acme',
            'status' => 'active',
            'rating' => 'hot',
            'type' => 'Automotive',
            'scope' => 'mine',
            'include_imported' => '1',
            'sort' => 'name',
            'direction' => 'asc',
            'page' => 1,
        ]))->assertOk();
    });

    it('rejects invalid status', function () {
        $this->get('/dashboard?status=bogus')
            ->assertSessionHasErrors('status');
    });

    it('rejects invalid rating', function () {
        $this->get('/dashboard?rating=spicy')
            ->assertSessionHasErrors('rating');
    });

    it('rejects invalid scope', function () {
        $this->get('/dashboard?scope=somebody')
            ->assertSessionHasErrors('scope');
    });

    it('rejects invalid sort', function () {
        $this->get('/dashboard?sort=unknown_column')
            ->assertSessionHasErrors('sort');
    });

    it('rejects invalid direction', function () {
        $this->get('/dashboard?direction=sideways')
            ->assertSessionHasErrors('direction');
    });

    it('rejects non-integer page', function () {
        $this->get('/dashboard?page=abc')
            ->assertSessionHasErrors('page');
    });

    it('rejects page below minimum', function () {
        $this->get('/dashboard?page=0')
            ->assertSessionHasErrors('page');
    });

    it('rejects search exceeding max length', function () {
        $this->get('/dashboard?search='.str_repeat('a', 256))
            ->assertSessionHasErrors('search');
    });

    it('rejects type exceeding max length', function () {
        $this->get('/dashboard?type='.str_repeat('a', 256))
            ->assertSessionHasErrors('type');
    });
});
