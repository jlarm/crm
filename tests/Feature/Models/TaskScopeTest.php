<?php

declare(strict_types=1);

use App\Enum\TaskPriority;
use App\Enum\TaskType;
use App\Models\Task;
use App\Models\User;

describe('Task scope filters', function (): void {
    it('filters by priority when one is provided', function (): void {
        $user = User::factory()->create();

        Task::factory()->create([
            'user_id' => $user->id,
            'created_by_user_id' => $user->id,
            'priority' => TaskPriority::High,
        ]);
        Task::factory()->create([
            'user_id' => $user->id,
            'created_by_user_id' => $user->id,
            'priority' => TaskPriority::Low,
        ]);

        $high = Task::query()->withPriority(TaskPriority::High->value)->get();
        $all = Task::query()->withPriority(null)->get();

        expect($high)->toHaveCount(1)
            ->and($high->first()->priority)->toBe(TaskPriority::High)
            ->and($all)->toHaveCount(2);
    });

    it('filters by type when one is provided', function (): void {
        $user = User::factory()->create();

        Task::factory()->create([
            'user_id' => $user->id,
            'created_by_user_id' => $user->id,
            'type' => TaskType::Call,
        ]);
        Task::factory()->create([
            'user_id' => $user->id,
            'created_by_user_id' => $user->id,
            'type' => TaskType::Email,
        ]);

        $calls = Task::query()->withType(TaskType::Call->value)->get();
        $all = Task::query()->withType(null)->get();

        expect($calls)->toHaveCount(1)
            ->and($calls->first()->type)->toBe(TaskType::Call)
            ->and($all)->toHaveCount(2);
    });
});
