<?php

declare(strict_types=1);

namespace App\Actions\Opportunities;

use App\Enum\OpportunityStage;
use App\Models\OpportunityStageChange;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

final class BuildStageConversion
{
    /**
     * How deals move through the open stages, built from the stage change history.
     *
     * A deal "reached" a stage if it ever sat in that stage or any later one, so deals
     * that skip a stage still count as having passed it. Lost never counts as progress.
     * Days in stage averages only completed stays, i.e. ones followed by another change.
     *
     * @return list<array{stage: string, label: string, reached: int, advanced: int, conversionRate: int|null, avgDaysInStage: int|null}>
     */
    public function __invoke(): array
    {
        $order = $this->progressionOrder();

        /** @var Collection<int, Collection<int, OpportunityStageChange>> $changesByOpportunity */
        $changesByOpportunity = OpportunityStageChange::query()
            ->select(['opportunity_id', 'to_stage', 'changed_at'])
            ->orderBy('changed_at')
            ->orderBy('id')
            ->get()
            ->groupBy('opportunity_id');

        /** @var array<string, int> $reached */
        $reached = array_fill_keys(array_keys($order), 0);

        /** @var array<string, list<float>> $stayDays */
        $stayDays = array_fill_keys(array_keys($order), []);

        foreach ($changesByOpportunity as $changes) {
            $furthest = -1;

            foreach ($changes->values() as $index => $change) {
                $stage = $change->to_stage->value;

                if (isset($order[$stage])) {
                    $furthest = max($furthest, $order[$stage]);
                }

                $next = $changes->values()->get($index + 1);

                if ($next instanceof OpportunityStageChange && isset($stayDays[$stage])) {
                    $stayDays[$stage][] = $this->daysBetween($change->changed_at, $next->changed_at);
                }
            }

            foreach ($order as $stage => $position) {
                if ($furthest >= $position) {
                    $reached[$stage]++;
                }
            }
        }

        $openStages = array_values(array_filter(
            OpportunityStage::cases(),
            fn (OpportunityStage $stage): bool => $stage->isOpen(),
        ));

        return array_map(function (OpportunityStage $stage) use ($order, $reached, $stayDays): array {
            $nextStage = array_search($order[$stage->value] + 1, $order, true);
            $advanced = is_string($nextStage) ? $reached[$nextStage] : 0;
            $stays = $stayDays[$stage->value];

            return [
                'stage' => $stage->value,
                'label' => $stage->getLabel(),
                'reached' => $reached[$stage->value],
                'advanced' => $advanced,
                'conversionRate' => $reached[$stage->value] > 0
                    ? (int) round(($advanced / $reached[$stage->value]) * 100)
                    : null,
                'avgDaysInStage' => $stays === [] ? null : (int) round(array_sum($stays) / count($stays)),
            ];
        }, $openStages);
    }

    /**
     * Position of each stage along the happy path; Lost is deliberately absent.
     *
     * @return array<string, int>
     */
    private function progressionOrder(): array
    {
        $order = [];

        foreach (OpportunityStage::cases() as $stage) {
            if ($stage === OpportunityStage::Lost) {
                continue;
            }

            $order[$stage->value] = count($order);
        }

        return $order;
    }

    private function daysBetween(CarbonInterface $from, CarbonInterface $to): float
    {
        return max(0.0, $from->diffInSeconds($to) / 86400);
    }
}
