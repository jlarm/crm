<?php

declare(strict_types=1);

namespace App\Actions\Tasks;

use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Models\User;

final class ListUpcomingTasks
{
    /**
     * @return array<int, mixed>
     */
    public function __invoke(User $user, int $limit = 10): array
    {
        return Task::forUser($user)
            ->with(['dealership:id,name', 'contact:id,name'])
            ->incomplete()
            ->orderByDueDateUrgency()
            ->orderByPriority()
            ->orderBy('due_date')
            ->limit($limit)
            ->get()
            ->map(fn (Task $task): array => TaskResource::make($task)->resolve())
            ->all();
    }
}
