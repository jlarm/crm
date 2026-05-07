<?php

declare(strict_types=1);

use App\Http\Resources\DealershipResource;
use App\Models\Dealership;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

describe('DealershipResource', function () {
    it('returns the expected shape when resolved directly', function () {
        $dealership = Dealership::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Acme Motors',
            'city' => 'Detroit',
            'state' => 'MI',
            'status' => 'active',
            'rating' => 'hot',
        ]);

        $resolved = (new DealershipResource($dealership))->toArray(Request::create('/'));

        expect($resolved)
            ->toHaveKeys(['id', 'name', 'city', 'state', 'status', 'statusLabel', 'rating', 'ratingLabel', 'openTasksCount'])
            ->and($resolved['id'])->toBe($dealership->id)
            ->and($resolved['name'])->toBe('Acme Motors')
            ->and($resolved['city'])->toBe('Detroit')
            ->and($resolved['state'])->toBe('MI')
            ->and($resolved['status'])->toBe('active')
            ->and($resolved['statusLabel'])->toBe('Active')
            ->and($resolved['rating'])->toBe('hot')
            ->and($resolved['ratingLabel'])->toBe('Hot')
            ->and($resolved['openTasksCount'])->toBe(0);
    });

    it('uses the open_tasks_count attribute when present', function () {
        $dealership = Dealership::factory()->create(['user_id' => $this->user->id]);
        $dealership->open_tasks_count = 7;

        $resolved = (new DealershipResource($dealership))->toArray(Request::create('/'));

        expect($resolved['openTasksCount'])->toBe(7);
    });

    it('exposes the resource via the dashboard inertia response', function () {
        $dealership = Dealership::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Inertia Dealership',
            'status' => 'active',
            'rating' => 'warm',
        ]);
        $dealership->users()->sync([$this->user->id]);

        Task::factory()->create([
            'dealership_id' => $dealership->id,
            'user_id' => $this->user->id,
            'created_by_user_id' => $this->user->id,
            'completed_at' => null,
        ]);

        $this->get('/dashboard')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('dealerships.data', 1)
                ->where('dealerships.data.0.name', 'Inertia Dealership')
                ->where('dealerships.data.0.statusLabel', 'Active')
                ->where('dealerships.data.0.ratingLabel', 'Warm')
                ->where('dealerships.data.0.openTasksCount', 1)
            );
    });
});
