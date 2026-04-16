<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\RedirectResponse;

final class TaskCompleteController extends Controller
{
    public function __invoke(Task $task): RedirectResponse
    {
        $wasCompleted = $task->isCompleted();

        $task->update(['completed_at' => $wasCompleted ? null : now()]);

        return back()->with('success', $wasCompleted ? 'Task reopened.' : 'Task completed.');
    }
}
