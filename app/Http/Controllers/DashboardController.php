<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enum\TaskPriority;
use App\Enum\TaskType;
use App\Http\Requests\DealershipIndexRequest;
use App\Http\Resources\DealershipResource;
use App\Http\Resources\TaskResource;
use App\Models\Dealership;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

final class DashboardController extends Controller
{
    public function index(DealershipIndexRequest $request): Response
    {
        $user = $request->user();

        $scope = $request->input('scope');
        if (! in_array($scope, ['mine', 'all'], true)) {
            $scope = 'mine';
        }

        $includeImported = $request->boolean('include_imported');
        $status = $request->input('status');

        $applyFilters = function (Builder $query) use ($request, $scope, $includeImported, $status): void {
            if ($scope === 'mine') {
                $query->forUser($request->user());
            }

            if (! $includeImported) {
                $query->whereNot('status', 'imported');
            }

            $query->search($request->input('search'))
                ->withRating($request->input('rating'))
                ->withType($request->input('type'));

            if ($status) {
                $query->where('status', $status);
            }
        };

        $typeOptionsQuery = Dealership::query();
        $typeOptions = $typeOptionsQuery
            ->select('type')
            ->distinct()
            ->orderBy('type')
            ->pluck('type')
            ->filter()
            ->values()
            ->map(fn (string $type): array => [
                'value' => $type,
                'label' => Str::headline($type),
            ])
            ->all();

        $query = Dealership::query();
        $applyFilters($query);

        $dealerships = $query
            ->sortBy($request->input('sort'), $request->input('direction'))
            ->select('id', 'name', 'city', 'state', 'status', 'rating')
            ->withCount(['tasks as open_tasks_count' => fn (Builder $q) => $q->whereNull('completed_at')])
            ->paginate(15)
            ->withQueryString()
            ->through(fn ($dealership) => DealershipResource::make($dealership)->resolve());

        return Inertia::render('Dashboard', [
            'dealerships' => $dealerships,
            'filters' => [
                'search' => $request->input('search', ''),
                'status' => $request->input('status', ''),
                'rating' => $request->input('rating', ''),
                'type' => $request->input('type', ''),
                'scope' => $scope,
                'include_imported' => $includeImported ? '1' : '',
                'sort' => $request->input('sort', ''),
                'direction' => $request->input('direction', 'asc'),
            ],
            'filterOptions' => [
                'statuses' => [
                    ['value' => 'active', 'label' => 'Active'],
                    ['value' => 'inactive', 'label' => 'Inactive'],
                ],
                'ratings' => [
                    ['value' => 'hot', 'label' => 'Hot'],
                    ['value' => 'warm', 'label' => 'Warm'],
                    ['value' => 'cold', 'label' => 'Cold'],
                ],
                'types' => $typeOptions,
            ],
            'taskStats' => $this->buildTaskStats($user),
            'upcomingTasks' => $this->buildUpcomingTasks($user),
            'taskFormData' => [
                'allUsers' => User::query()->select('id', 'name')->orderBy('name')->get(),
                'allDealerships' => Dealership::query()
                    ->select('id', 'name')
                    ->whereNot('status', 'imported')
                    ->orderBy('name')
                    ->get(),
                'types' => collect(TaskType::cases())->map(fn ($case) => [
                    'value' => $case->value,
                    'label' => $case->label(),
                ]),
                'priorities' => collect(TaskPriority::cases())->map(fn ($case) => [
                    'value' => $case->value,
                    'label' => $case->label(),
                ]),
            ],
        ]);
    }

    /**
     * @return array<string, int>
     */
    private function buildTaskStats(User $user): array
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

    /**
     * @return array<int, mixed>
     */
    private function buildUpcomingTasks(User $user): array
    {
        $today = now()->toDateString();

        return Task::forUser($user)
            ->with(['dealership:id,name', 'contact:id,name'])
            ->incomplete()
            ->orderByRaw('CASE WHEN due_date < ? THEN 0 WHEN due_date = ? THEN 1 ELSE 2 END', [$today, $today])
            ->orderByRaw("CASE priority WHEN 'high' THEN 1 WHEN 'medium' THEN 2 ELSE 3 END")
            ->orderBy('due_date')
            ->limit(10)
            ->get()
            ->map(fn ($task) => TaskResource::make($task)->resolve())
            ->all();
    }
}
