<?php

declare(strict_types=1);

use App\Enum\TaskPriority;
use App\Enum\TaskType;
use App\Models\Dealership;
use App\Models\Task;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

describe('Task Index', function () {
    it('renders the tasks page', function () {
        Task::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'created_by_user_id' => $this->user->id,
        ]);

        $response = $this->get('/tasks');

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Tasks/Index')
                ->has('tasks.data', 3)
                ->has('summary')
                ->has('filterOptions')
            );
    });

    it('only shows tasks assigned to the authenticated user', function () {
        $otherUser = User::factory()->create();

        Task::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'created_by_user_id' => $this->user->id,
        ]);
        Task::factory()->count(3)->create([
            'user_id' => $otherUser->id,
            'created_by_user_id' => $otherUser->id,
        ]);

        $response = $this->get('/tasks');

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('tasks.data', 2)
            );
    });

    it('filters by completed status', function () {
        Task::factory()->create([
            'user_id' => $this->user->id,
            'created_by_user_id' => $this->user->id,
            'completed_at' => now(),
        ]);
        Task::factory()->create([
            'user_id' => $this->user->id,
            'created_by_user_id' => $this->user->id,
            'completed_at' => null,
        ]);

        $this->get('/tasks?filter=completed')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->has('tasks.data', 1));

        $this->get('/tasks?filter=incomplete')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->has('tasks.data', 1));
    });

    it('filters overdue tasks', function () {
        Task::factory()->overdue()->create([
            'user_id' => $this->user->id,
            'created_by_user_id' => $this->user->id,
        ]);
        Task::factory()->create([
            'user_id' => $this->user->id,
            'created_by_user_id' => $this->user->id,
            'due_date' => now()->addDays(5),
        ]);

        $this->get('/tasks?filter=overdue')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->has('tasks.data', 1));
    });
});

describe('Task Store', function () {
    it('creates a task with valid data', function () {
        $dealership = Dealership::factory()->create(['user_id' => $this->user->id]);

        $this->post('/tasks', [
            'title' => 'Call the GM',
            'description' => 'Follow up on proposal',
            'type' => TaskType::Call->value,
            'priority' => TaskPriority::High->value,
            'due_date' => now()->addDays(3)->toDateString(),
            'user_id' => $this->user->id,
            'dealership_id' => $dealership->id,
        ])->assertRedirect();

        $this->assertDatabaseHas('tasks', [
            'title' => 'Call the GM',
            'type' => TaskType::Call->value,
            'priority' => TaskPriority::High->value,
            'user_id' => $this->user->id,
            'created_by_user_id' => $this->user->id,
            'dealership_id' => $dealership->id,
        ]);
    });

    it('validates required fields', function () {
        $this->post('/tasks', [])
            ->assertSessionHasErrors(['title', 'type', 'priority', 'user_id']);
    });

    it('validates enum values', function () {
        $this->post('/tasks', [
            'title' => 'Test task',
            'type' => 'invalid_type',
            'priority' => 'invalid_priority',
            'user_id' => $this->user->id,
        ])->assertSessionHasErrors(['type', 'priority']);
    });
});

describe('Task Update', function () {
    it('updates a task', function () {
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'created_by_user_id' => $this->user->id,
        ]);

        $this->put("/tasks/{$task->id}", [
            'title' => 'Updated Title',
            'type' => TaskType::Email->value,
            'priority' => TaskPriority::Low->value,
            'user_id' => $this->user->id,
        ])->assertRedirect();

        $task->refresh();
        expect($task->title)->toBe('Updated Title')
            ->and($task->type)->toBe(TaskType::Email)
            ->and($task->priority)->toBe(TaskPriority::Low);
    });
});

describe('Task Destroy', function () {
    it('deletes a task', function () {
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'created_by_user_id' => $this->user->id,
        ]);

        $this->delete("/tasks/{$task->id}")->assertRedirect();

        $this->assertModelMissing($task);
    });
});

describe('Task Complete', function () {
    it('marks a task as completed', function () {
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'created_by_user_id' => $this->user->id,
            'completed_at' => null,
        ]);

        $this->patch("/tasks/{$task->id}/complete")->assertRedirect();

        $task->refresh();
        expect($task->completed_at)->not->toBeNull();
    });

    it('reopens a completed task', function () {
        $task = Task::factory()->completed()->create([
            'user_id' => $this->user->id,
            'created_by_user_id' => $this->user->id,
        ]);

        $this->patch("/tasks/{$task->id}/complete")->assertRedirect();

        $task->refresh();
        expect($task->completed_at)->toBeNull();
    });
});

describe('Task Model', function () {
    it('casts type and priority to enums', function () {
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'created_by_user_id' => $this->user->id,
            'type' => TaskType::Demo->value,
            'priority' => TaskPriority::Medium->value,
        ]);

        expect($task->type)->toBe(TaskType::Demo)
            ->and($task->priority)->toBe(TaskPriority::Medium);
    });

    it('detects overdue tasks correctly', function () {
        $overdue = Task::factory()->overdue()->create([
            'user_id' => $this->user->id,
            'created_by_user_id' => $this->user->id,
        ]);
        $future = Task::factory()->create([
            'user_id' => $this->user->id,
            'created_by_user_id' => $this->user->id,
            'due_date' => now()->addDay(),
        ]);

        expect($overdue->isOverdue())->toBeTrue()
            ->and($future->isOverdue())->toBeFalse();
    });
});
