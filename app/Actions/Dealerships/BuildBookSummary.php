<?php

declare(strict_types=1);

namespace App\Actions\Dealerships;

use App\Models\Dealership;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class BuildBookSummary
{
    /**
     * Headline counts for the dealerships a user is responsible for.
     *
     * @return array{total: int, hot: int, warm: int, cold: int}
     */
    public function __invoke(User $user): array
    {
        /** @var array<int, object{rating: string|null, total: int}> $rows */
        $rows = Dealership::query()
            ->forUser($user)
            ->whereNot('status', 'imported')
            ->groupBy('rating')
            ->select('rating', DB::raw('COUNT(*) as total'))
            ->get()
            ->all();

        $summary = ['total' => 0, 'hot' => 0, 'warm' => 0, 'cold' => 0];

        foreach ($rows as $row) {
            $total = (int) $row->total;
            $summary['total'] += $total;

            $rating = mb_strtolower((string) $row->rating);

            if (array_key_exists($rating, $summary) && $rating !== 'total') {
                $summary[$rating] += $total;
            }
        }

        return $summary;
    }
}
