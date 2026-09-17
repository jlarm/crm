<?php

declare(strict_types=1);

use App\Actions\Dealerships\ListDealerships;
use App\Models\Dealership;
use App\Models\Task;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->action = new ListDealerships;
});

it('limits to the user\'s dealerships by default', function () {
    $mine = Dealership::factory()->create(['user_id' => $this->user->id, 'status' => 'active']);
    Dealership::factory()->create(['user_id' => User::factory(), 'status' => 'active']);

    $result = ($this->action)($this->user, []);

    expect($result->total())->toBe(1)
        ->and($result->items()[0]['id'])->toBe($mine->id);
});

it('returns every dealership when scope is all', function () {
    Dealership::factory()->create(['user_id' => $this->user->id, 'status' => 'active']);
    Dealership::factory()->create(['user_id' => User::factory(), 'status' => 'active']);

    expect(($this->action)($this->user, ['scope' => 'all'])->total())->toBe(2);
});

it('hides imported dealerships unless asked', function () {
    Dealership::factory()->create(['user_id' => $this->user->id, 'status' => 'imported']);

    expect(($this->action)($this->user, [])->total())->toBe(0)
        ->and(($this->action)($this->user, ['include_imported' => '1'])->total())->toBe(1);
});

it('filters by status and rating', function () {
    Dealership::factory()->create(['user_id' => $this->user->id, 'status' => 'active', 'rating' => 'hot']);
    Dealership::factory()->create(['user_id' => $this->user->id, 'status' => 'inactive', 'rating' => 'cold']);

    expect(($this->action)($this->user, ['status' => 'active'])->total())->toBe(1)
        ->and(($this->action)($this->user, ['rating' => 'cold'])->total())->toBe(1);
});

it('includes an open task count', function () {
    $dealership = Dealership::factory()->create(['user_id' => $this->user->id, 'status' => 'active']);
    $dealership->tasks()->saveMany([
        Task::factory()->make(['user_id' => $this->user->id]),
        Task::factory()->completed()->make(['user_id' => $this->user->id]),
    ]);

    $result = ($this->action)($this->user, []);

    expect($result->items()[0]['openTasksCount'])->toBe(1);
});
