<?php

declare(strict_types=1);

namespace App\Actions\Dealerships;

use App\Models\Dealership;
use App\Models\Progress;
use App\Models\User;
use DateTimeInterface;
use Illuminate\Support\Carbon;

final class ListDealershipsGoingQuiet
{
    /**
     * Rated dealerships in a user's book with the oldest logged contact, most urgent first.
     *
     * "Contact" means a progress entry, the touchpoint a rep records by hand. Bulk email
     * sends are deliberately ignored so an automated campaign cannot mask a neglected lead.
     *
     * @return array<int, array{id: int, name: string, city: string|null, state: string|null, rating: string, ratingLabel: string, lastTouchAt: string|null, daysSinceTouch: int|null}>
     */
    public function __invoke(User $user, int $limit = 6): array
    {
        $lastTouch = Progress::query()
            ->selectRaw('MAX(COALESCE(progresses.date, progresses.created_at))')
            ->whereColumn('progresses.dealership_id', 'dealerships.id');

        return Dealership::query()
            ->forUser($user)
            ->whereNot('status', 'imported')
            ->whereRaw('LOWER(rating) IN (?, ?)', ['hot', 'warm'])
            ->select('id', 'name', 'city', 'state', 'rating')
            ->addSelect(['last_touch_at' => $lastTouch])
            ->orderByRaw("CASE LOWER(rating) WHEN 'hot' THEN 1 ELSE 2 END")
            ->orderByRaw('last_touch_at IS NOT NULL')
            ->orderBy('last_touch_at')
            ->orderBy('name')
            ->limit($limit)
            ->get()
            ->map(function (Dealership $dealership): array {
                $lastTouchAt = $dealership->getAttribute('last_touch_at');
                $touchedAt = is_string($lastTouchAt) || $lastTouchAt instanceof DateTimeInterface
                    ? Carbon::parse($lastTouchAt)
                    : null;

                return [
                    'id' => $dealership->id,
                    'name' => (string) $dealership->name,
                    'city' => $dealership->city,
                    'state' => $dealership->state,
                    'rating' => mb_strtolower((string) $dealership->rating),
                    'ratingLabel' => ucfirst(mb_strtolower((string) $dealership->rating)),
                    'lastTouchAt' => $touchedAt?->toDateString(),
                    'daysSinceTouch' => $touchedAt === null ? null : (int) $touchedAt->startOfDay()->diffInDays(now()->startOfDay()),
                ];
            })
            ->all();
    }
}
