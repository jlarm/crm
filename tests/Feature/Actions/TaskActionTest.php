<?php

declare(strict_types=1);

use App\Actions\Tasks\BuildTaskFormOptions;
use App\Actions\Tasks\BuildTaskStats;
use App\Actions\Tasks\ListUpcomingTasks;
use App\Enum\TaskPriority;
use App\Enum\TaskType;
use App\Models\Dealership;
use App\Models\Task;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
});

describe('BuildTaskStats', function () {
    it('counts the user\'s tasks by state', function () {
        Task::factory()->for($this->user)->create(['due_date' => now()->addWeek()]);
        Task::factory()->for($this->user)->overdue()->create();
        Task::factory()->for($this->user)->create(['due_date' => today()]);
        Task::factory()->for($this->user)->completed()->create();
        Task::factory()->completed()->create();

        expect((new BuildTaskStats)($this->user))->toBe([
            'incomplete' => 3,
            'overdue' => 1,
            'dueToday' => 1,
            'completedThisWeek' => 1,
        ]);
    });
});

describe('ListUpcomingTasks', function () {
    it('orders overdue, then due today, then by priority', function () {
        $later = Task::factory()->for($this->user)->high()->create(['due_date' => now()->addWeek()]);
        $todayLow = Task::factory()->for($this->user)->create(['due_date' => today(), 'priority' => TaskPriority::Low->value]);
        $todayHigh = Task::factory()->for($this->user)->high()->create(['due_date' => today()]);
        $overdue = Task::factory()->for($this->user)->overdue()->create();
        Task::factory()->for($this->user)->completed()->create();

        $ids = array_column((new ListUpcomingTasks)($this->user), 'id');

        expect($ids)->toBe([$overdue->id, $todayHigh->id, $todayLow->id, $later->id]);
    });

    it('respects the limit', function () {
        Task::factory()->for($this->user)->count(3)->create();

        expect((new ListUpcomingTasks)($this->user, 2))->toHaveCount(2);
    });
});

describe('BuildTaskFormOptions', function () {
    it('returns users, non-imported dealerships, and enum options', function () {
        Dealership::factory()->create(['status' => 'active']);
        Dealership::factory()->create(['status' => 'imported']);

        $options = (new BuildTaskFormOptions)();

        expect($options['allUsers'])->toHaveCount(1)
            ->and($options['allDealerships'])->toHaveCount(1)
            ->and($options['types'])->toHaveCount(count(TaskType::cases()))
            ->and($options['priorities'][0])->toHaveKeys(['value', 'label']);
    });
});
