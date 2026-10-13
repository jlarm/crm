<?php

declare(strict_types=1);

namespace App\Actions\Dealerships;

use App\Models\Dealership;
use App\Models\User;

final class ListStarredDealerships
{
    /**
     * The dealerships a user starred, alphabetically, for quick access from the dashboard.
     *
     * @return list<array{id: int, name: string, city: string|null, state: string|null, rating: string|null, ratingLabel: string|null}>
     */
    public function __invoke(User $user): array
    {
        return $user->starredDealerships()
            ->select('dealerships.id', 'dealerships.name', 'dealerships.city', 'dealerships.state', 'dealerships.rating')
            ->orderBy('dealerships.name')
            ->get()
            ->map(fn (Dealership $dealership): array => [
                'id' => $dealership->id,
                'name' => (string) $dealership->name,
                'city' => $dealership->city,
                'state' => $dealership->state,
                'rating' => $dealership->rating ? mb_strtolower((string) $dealership->rating) : null,
                'ratingLabel' => $dealership->rating ? ucfirst(mb_strtolower((string) $dealership->rating)) : null,
            ])
            ->values()
            ->all();
    }
}
