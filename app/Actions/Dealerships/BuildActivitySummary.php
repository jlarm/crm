<?php

declare(strict_types=1);

namespace App\Actions\Dealerships;

use App\Models\Progress;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

final class BuildActivitySummary
{
    private const string TOUCHED_AT = 'COALESCE(progresses.date, progresses.created_at)';

    /**
     * Logged contacts across a user's book: a seven-day trend and the latest entries.
     *
     * Days are counted inclusively, so "this week" is today and the six days before it.
     *
     * @return array{thisWeek: int, lastWeek: int, recent: list<array{id: int, details: string, date: string, category: string|null, author: string|null, dealership: array{id: int, name: string}|null}>}
     */
    public function __invoke(User $user, int $limit = 5): array
    {
        $today = now()->startOfDay();
        $weekStart = $today->copy()->subDays(6)->toDateString();
        $previousWeekStart = $today->copy()->subDays(13)->toDateString();

        $recent = array_values($this->query($user)
            ->with(['dealership:id,name', 'category:id,name', 'user:id,name'])
            ->orderByRaw('COALESCE(progresses.date, progresses.created_at) DESC')
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->map(fn (Progress $progress): array => [
                'id' => $progress->id,
                'details' => Str::limit(mb_trim(strip_tags((string) $progress->details)), 140),
                'date' => ($progress->date ?? $progress->created_at)->toDateString(),
                'category' => $progress->category?->name,
                'author' => $progress->user?->name,
                'dealership' => $progress->dealership === null ? null : [
                    'id' => $progress->dealership->id,
                    'name' => (string) $progress->dealership->name,
                ],
            ])
            ->all());

        return [
            'thisWeek' => $this->query($user)->whereRaw(self::TOUCHED_AT.' >= ?', [$weekStart])->count(),
            'lastWeek' => $this->query($user)
                ->whereRaw(self::TOUCHED_AT.' >= ?', [$previousWeekStart])
                ->whereRaw(self::TOUCHED_AT.' < ?', [$weekStart])
                ->count(),
            'recent' => $recent,
        ];
    }

    /**
     * @return Builder<Progress>
     */
    private function query(User $user): Builder
    {
        return Progress::query()->whereHas(
            'dealership',
            fn (Builder $dealership) => $dealership->forUser($user)->whereNot('status', 'imported'),
        );
    }
}
