<?php

declare(strict_types=1);

namespace App\Actions\Tasks;

use App\Models\Task;
use App\Models\User;

final class BuildTaskStats
{
    /**
     * @return array{incomplete: int, overdue: int, dueToday: int, completedThisWeek: int}
     */
    public function __invoke(User $user): array
    {
        return [
            'incomplete' => Task::forUser($user)->incomplete()->count(),
            'overdue' => Task::forUser($user)->overdue()->count(),
            'dueToday' => Task::forUser($user)->dueToday()->count(),
            'completedThisWeek' => Task::forUser($user)
                ->completed()
                ->where('completed_at', '>=', now()->startOfWeek())
                ->count(),
        ];
    }
}
