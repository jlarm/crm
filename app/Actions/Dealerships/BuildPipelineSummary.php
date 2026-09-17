<?php

declare(strict_types=1);

namespace App\Actions\Dealerships;

use App\Enum\OpportunityStage;
use App\Models\Opportunity;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

final class BuildPipelineSummary
{
    /**
     * Open-deal totals for the dealerships in a user's book, with the open stages in funnel order.
     *
     * @return array{openValue: float, openCount: int, weightedValue: float, closingThisMonthCount: int, wonThisMonthCount: int, wonThisMonthValue: float, stages: list<array{stage: string, label: string, count: int, value: float}>}
     */
    public function __invoke(User $user): array
    {
        $rows = $this->query($user)
            ->open()
            ->select('stage')
            ->selectRaw('COUNT(*) as total, SUM(COALESCE(estimated_value, 0)) as value')
            ->selectRaw('SUM(COALESCE(estimated_value, 0) * COALESCE(probability, 0) / 100) as weighted')
            ->groupBy('stage')
            ->get()
            ->keyBy(fn (Opportunity $row): string => $row->stage->value);

        $stages = [];
        $openValue = 0.0;
        $openCount = 0;
        $weightedValue = 0.0;

        foreach (OpportunityStage::cases() as $stage) {
            if (! $stage->isOpen()) {
                continue;
            }

            $row = $rows->get($stage->value);
            $count = $this->toInt($row?->getAttribute('total'));
            $value = $this->toFloat($row?->getAttribute('value'));

            $openCount += $count;
            $openValue += $value;
            $weightedValue += $this->toFloat($row?->getAttribute('weighted'));

            $stages[] = [
                'stage' => $stage->value,
                'label' => $stage->getLabel(),
                'count' => $count,
                'value' => $value,
            ];
        }

        $wonThisMonth = $this->query($user)
            ->won()
            ->whereNotNull('closed_at')
            ->where('closed_at', '>=', now()->startOfMonth()->toDateString())
            ->selectRaw('COUNT(*) as total, SUM(COALESCE(actual_value, estimated_value, 0)) as value')
            ->first();

        return [
            'openValue' => round($openValue, 2),
            'openCount' => $openCount,
            'weightedValue' => round($weightedValue, 2),
            'closingThisMonthCount' => $this->query($user)->closingThisMonth()->count(),
            'wonThisMonthCount' => $this->toInt($wonThisMonth?->getAttribute('total')),
            'wonThisMonthValue' => round($this->toFloat($wonThisMonth?->getAttribute('value')), 2),
            'stages' => $stages,
        ];
    }

    /**
     * @return Builder<Opportunity>
     */
    private function query(User $user): Builder
    {
        return Opportunity::query()->whereHas(
            'dealership',
            fn (Builder $dealership) => $dealership->forUser($user)->whereNot('status', 'imported'),
        );
    }

    private function toInt(mixed $value): int
    {
        return is_numeric($value) ? (int) $value : 0;
    }

    private function toFloat(mixed $value): float
    {
        return is_numeric($value) ? (float) $value : 0.0;
    }
}
