<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enum\TaskPriority;
use App\Enum\TaskType;
use App\Http\Requests\TaskStoreRequest;
use App\Http\Requests\TaskUpdateRequest;
use App\Http\Resources\TaskResource;
use App\Models\Dealership;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class TaskController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Task::query()
            ->with(['user', 'createdBy', 'dealership', 'contact'])
            ->forUser($request->user());

        $filter = $request->input('filter', 'incomplete');

        match ($filter) {
            'completed' => $query->completed(),
            'overdue' => $query->overdue(),
            default => $query->incomplete(),
        };

        $query->withPriority($request->input('priority'))
            ->withType($request->input('type'));

        if ($request->filled('dealership_id')) {
            $query->where('dealership_id', $request->integer('dealership_id'));
        }

        $tasks = $query
            ->orderByRaw("CASE priority WHEN 'high' THEN 1 WHEN 'medium' THEN 2 ELSE 3 END")
            ->orderBy('due_date')
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString()
            ->through(fn ($task) => TaskResource::make($task)->resolve());

        return Inertia::render('Tasks/Index', [
            'tasks' => $tasks,
            'filters' => [
                'filter' => $filter,
                'priority' => $request->input('priority', ''),
                'type' => $request->input('type', ''),
                'dealership_id' => $request->input('dealership_id', ''),
            ],
            'filterOptions' => [
                'types' => collect(TaskType::cases())->map(fn ($case) => [
                    'value' => $case->value,
                    'label' => $case->label(),
                ]),
                'priorities' => collect(TaskPriority::cases())->map(fn ($case) => [
                    'value' => $case->value,
                    'label' => $case->label(),
                ]),
            ],
            'allUsers' => User::query()->select('id', 'name')->orderBy('name')->get(),
            'allDealerships' => Dealership::query()
                ->select('id', 'name')
                ->whereNot('status', 'imported')
                ->orderBy('name')
                ->get(),
            'summary' => [
                'incomplete' => Task::forUser($request->user())->incomplete()->count(),
                'overdue' => Task::forUser($request->user())->overdue()->count(),
                'dueToday' => Task::forUser($request->user())->dueToday()->count(),
            ],
        ]);
    }

    public function store(TaskStoreRequest $request): RedirectResponse
    {
        Task::create([
            ...$request->validated(),
            'created_by_user_id' => $request->user()->id,
        ]);

        return back()->with('success', 'Task created successfully.');
    }

    public function update(TaskUpdateRequest $request, Task $task): RedirectResponse
    {
        $task->update($request->validated());

        return back()->with('success', 'Task updated successfully.');
    }

    public function destroy(Task $task): RedirectResponse
    {
        $task->delete();

        return back()->with('success', 'Task deleted successfully.');
    }
}
