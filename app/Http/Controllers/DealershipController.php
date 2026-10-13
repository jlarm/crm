<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enum\TaskPriority;
use App\Enum\TaskType;
use App\Http\Requests\DealershipStoreRequest;
use App\Http\Requests\DealershipUpdateRequest;
use App\Http\Resources\DealershipEmailResource;
use App\Http\Resources\DealershipShowResource;
use App\Http\Resources\TaskResource;
use App\Models\Dealership;
use App\Models\DealershipEmail;
use App\Models\ProgressCategory;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final class DealershipController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Dealership/Create', [
            'allUsers' => User::query()->select('id', 'name')->orderBy('name')->get(),
        ]);
    }

    public function store(DealershipStoreRequest $request): RedirectResponse
    {
        /** @var array<string, mixed> $data */
        $data = $request->safe()->except(['user_ids']);

        $dealership = Dealership::create([
            ...$data,
            'user_id' => auth()->id(),
        ]);

        /** @var array<int, int> $userIds */
        $userIds = $request->validated('user_ids', []);
        $authId = auth()->id();

        if ($authId !== null && ! in_array($authId, $userIds, true)) {
            $userIds[] = (int) $authId;
        }

        $dealership->users()->sync($userIds);

        return redirect()->route('dealerships.show', $dealership)
            ->with('success', 'Dealership created successfully.');
    }

    public function show(Dealership $dealership): Response
    {
        $dealership->load([
            'users' => fn (\Illuminate\Database\Eloquent\Relations\Relation $query) => $query->select('id', 'name'),
            'stores',
            'contacts',
            'opportunities' => fn (\Illuminate\Database\Eloquent\Relations\Relation $query) => $query->with(['activities.user'])->orderBy('created_at', 'desc'),
        ]);

        $tasks = $dealership->tasks()
            ->with(['user', 'createdBy', 'contact'])
            ->orderByPriority()
            ->orderBy('due_date')
            ->get()
            ->map(fn (Task $task): array => TaskResource::make($task)->resolve());

        return Inertia::render('Dealership/Show', [
            'dealership' => DealershipShowResource::make($dealership)->resolve(),
            'allUsers' => User::query()->select('id', 'name')->orderBy('name')->get(),
            'tasks' => $tasks,
            'taskFilterOptions' => [
                'types' => TaskType::options(),
                'priorities' => TaskPriority::options(),
            ],
            'progressCategories' => ProgressCategory::query()->select('id', 'name')->orderBy('id')->get(),
            'emailThreads' => Inertia::defer(fn (): array => $this->emailThreads($dealership)),
            'unreadReplyCount' => fn (): int => $dealership->emails()->unreadReplies()->count(),
        ]);
    }

    public function update(DealershipUpdateRequest $request, Dealership $dealership): RedirectResponse
    {
        /** @var array<string, mixed> $data */
        $data = $request->safe()->except(['user_ids']);
        $dealership->update($data);

        if ($request->has('user_ids')) {
            /** @var array<int, int> $userIds */
            $userIds = $request->validated('user_ids', []);
            $dealership->users()->sync($userIds);
        }

        return back()->with('success', 'Dealership updated successfully.');
    }

    /**
     * Each thread is the email that started it plus the replies on both sides, oldest first.
     *
     * @return list<list<array<string, mixed>>>
     */
    private function emailThreads(Dealership $dealership): array
    {
        $emails = $dealership->emails()
            ->with([
                'contact:id,name',
                'user:id,name',
                'attachments',
                'events' => fn (HasMany $query) => $query->orderBy('occurred_at'),
            ])
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        return $emails
            ->groupBy(fn (DealershipEmail $email): int => $email->parent_id ?? $email->id)
            ->sortByDesc(fn (Collection $thread): string => $thread->last()->created_at->toIso8601String())
            ->map(fn (Collection $thread): array => $thread
                ->map(fn (DealershipEmail $email): array => DealershipEmailResource::make($email)->resolve())
                ->values()
                ->all())
            ->values()
            ->all();
    }
}
