<?php

declare(strict_types=1);

namespace App\Actions\Dealerships;

use App\Ai\Agents\WeeklyDigestWriter;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

final class SummarizeWeeklyDigest
{
    /**
     * A short AI-written recap of the digest, or null when there's nothing to say or the AI fails.
     *
     * Cached against the digest's own contents, so the recap only regenerates when the
     * underlying numbers change, and at most once every few hours per user otherwise.
     * Failures are not cached, so the next page load tries again.
     *
     * @param  array{periodStart: string, periodEnd: string, counts: array<string, int|float>, moves: list<array<string, mixed>>, quietDeals: list<array<string, mixed>>}  $digest
     */
    public function __invoke(User $user, array $digest): ?string
    {
        if (! $this->hasAnythingToSay($digest)) {
            return null;
        }

        $facts = json_encode($digest, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '{}';
        $cacheKey = sprintf('weekly-digest-summary:%d:%s', $user->id, md5($facts));

        $cached = Cache::get($cacheKey);
        if (is_string($cached)) {
            return $cached;
        }

        try {
            $summary = mb_trim((new WeeklyDigestWriter)->prompt($facts)->text);
        } catch (Throwable $e) {
            Log::warning('[SummarizeWeeklyDigest] Weekly digest summary failed.', [
                'user_id' => $user->id,
                'exception' => $e,
            ]);

            return null;
        }

        if ($summary === '') {
            return null;
        }

        Cache::put($cacheKey, $summary, now()->addHours(6));

        return $summary;
    }

    /**
     * @param  array{periodStart: string, periodEnd: string, counts: array<string, int|float>, moves: list<array<string, mixed>>, quietDeals: list<array<string, mixed>>}  $digest
     */
    private function hasAnythingToSay(array $digest): bool
    {
        return $digest['moves'] !== []
            || $digest['quietDeals'] !== []
            || ($digest['counts']['activitiesLogged'] ?? 0) > 0
            || ($digest['counts']['tasksDueThisWeek'] ?? 0) > 0
            || ($digest['counts']['tasksOverdue'] ?? 0) > 0;
    }
}
