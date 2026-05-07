<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Dealership;
use App\Models\Store;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class SearchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $query = mb_trim((string) $request->string('q'));

        if (mb_strlen($query) < 2) {
            return response()->json([]);
        }

        $dealerships = Dealership::search($query)
            ->take(5)
            ->get()
            ->map(fn (Dealership $d): array => [
                'type' => 'dealership',
                'id' => $d->id,
                'label' => $d->name,
                'subtitle' => collect([$d->city, $d->state])->filter()->implode(', '),
                'meta' => $d->type,
                'url' => route('dealerships.show', $d),
            ]);

        $contacts = Contact::search($query)
            ->take(5)
            ->get()
            ->load('dealership')
            ->map(fn (Contact $c): array => [
                'type' => 'contact',
                'id' => $c->id,
                'label' => $c->name,
                'subtitle' => $c->dealership?->name,
                'meta' => $c->position,
                'url' => route('dealerships.show', $c->dealership_id),
            ]);

        $stores = Store::search($query)
            ->take(5)
            ->get()
            ->load('dealership')
            ->map(fn (Store $s): array => [
                'type' => 'store',
                'id' => $s->id,
                'label' => $s->name,
                'subtitle' => $s->dealership?->name,
                'meta' => collect([$s->city, $s->state])->filter()->implode(', ') ?: null,
                'url' => route('dealerships.show', $s->dealership_id),
            ]);

        $userId = $request->user()?->id;

        $tasks = Task::search($query)
            ->take(20)
            ->get()
            ->when($userId, fn (\Illuminate\Database\Eloquent\Collection $collection) => $collection->where('user_id', $userId))
            ->take(5)
            ->load(['dealership', 'contact'])
            ->map(fn (Task $t): array => [
                'type' => 'task',
                'id' => $t->id,
                'label' => $t->title,
                'subtitle' => $t->dealership?->name ?? $t->contact?->name, // @phpstan-ignore nullsafe.neverNull
                'meta' => $t->isCompleted() ? 'Completed' : ($t->due_date?->format('M j, Y')),
                'url' => route('tasks.index', ['highlight' => $t->id]),
            ]);

        return response()->json(
            $dealerships
                ->concat($contacts)
                ->concat($stores)
                ->concat($tasks)
                ->values()
        );
    }
}
