<?php

declare(strict_types=1);

use App\Models\Dealership;
use App\Models\Task;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->user = User::factory()->create();
    actingAs($this->user);
});

describe('Dashboard index', function () {
    it('renders the Dashboard component with required props', function () {
        get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Dashboard')
                ->has('dealerships')
                ->has('filters')
                ->has('filterOptions.statuses')
                ->has('filterOptions.ratings')
                ->has('filterOptions.types')
                ->has('taskStats.incomplete')
                ->has('taskStats.overdue')
                ->has('taskStats.dueToday')
                ->has('taskStats.completedThisWeek')
                ->has('upcomingTasks')
                ->has('taskFormData.allUsers')
                ->has('taskFormData.allDealerships')
                ->has('taskFormData.types')
                ->has('taskFormData.priorities')
            );
    });

    it('defaults scope to mine when scope is omitted', function () {
        get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where('filters.scope', 'mine'));
    });

    it('accepts the all scope', function () {
        get(route('dashboard', ['scope' => 'all']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where('filters.scope', 'all'));
    });

    it('excludes imported dealerships by default', function () {
        Dealership::factory()->create(['user_id' => $this->user->id, 'status' => 'imported', 'name' => 'Imported Co']);
        Dealership::factory()->create(['user_id' => $this->user->id, 'status' => 'active', 'name' => 'Active Co']);

        get(route('dashboard', ['scope' => 'all']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where('filters.include_imported', ''));
    });

    it('includes imported dealerships when include_imported is set', function () {
        get(route('dashboard', ['scope' => 'all', 'include_imported' => '1']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where('filters.include_imported', '1'));
    });

    it('filters by status', function () {
        get(route('dashboard', ['scope' => 'all', 'status' => 'active']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where('filters.status', 'active'));
    });

    it('filters by rating', function () {
        get(route('dashboard', ['rating' => 'hot']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where('filters.rating', 'hot'));
    });

    it('filters by type', function () {
        get(route('dashboard', ['type' => 'Automotive']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where('filters.type', 'Automotive'));
    });

    it('passes through search input', function () {
        get(route('dashboard', ['search' => 'Prime']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where('filters.search', 'Prime'));
    });

    it('accepts sort and direction', function () {
        get(route('dashboard', ['sort' => 'name', 'direction' => 'desc']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('filters.sort', 'name')
                ->where('filters.direction', 'desc')
            );
    });

    it('counts upcoming tasks for the user', function () {
        Task::factory()->count(3)->create(['user_id' => $this->user->id, 'created_by_user_id' => $this->user->id]);

        get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->has('upcomingTasks'));
    });

    it('requires authentication', function () {
        auth()->logout();
        get(route('dashboard'))->assertRedirect(route('login'));
    });
});
