<?php

declare(strict_types=1);

namespace App\Actions\Opportunities;

use App\Enum\OpportunityStage;
use App\Models\Opportunity;
use App\Models\OpportunityActivity;
use DateTimeInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

final class ListOpenDeals
{
    /**
     * Open opportunities with their dealership and rep, most recently worked first.
     *
     * "Worked" means a logged opportunity activity (call, email or note). Deals with
     * no activity yet fall to the bottom, ordered by expected close date.
     *
     * @return LengthAwarePaginator<int, array{id: int, name: string, stage: string, stageLabel: string, estimatedValue: float, probability: int|null, expectedCloseDate: string|null, nextAction: string|null, dealership: array{id: int, name: string}, repName: string|null, lastActivityAt: string|null, daysSinceActivity: int|null, recentActivityCount: int}>
     */
    public function __invoke(int $perPage = 15, int $recentDays = 30): LengthAwarePaginator
    {
        $lastActivity = OpportunityActivity::query()
            ->selectRaw('MAX(COALESCE(opportunity_activities.occurred_at, opportunity_activities.created_at))')
            ->whereColumn('opportunity_activities.opportunity_id', 'opportunities.id');

        $recentActivityCount = OpportunityActivity::query()
            ->selectRaw('COUNT(*)')
            ->whereColumn('opportunity_activities.opportunity_id', 'opportunities.id')
            ->whereRaw('COALESCE(opportunity_activities.occurred_at, opportunity_activities.created_at) >= ?', [
                now()->subDays($recentDays)->startOfDay(),
            ]);

        return Opportunity::query()
            ->whereNotIn('opportunities.stage', OpportunityStage::closedValues())
            ->join('dealerships', 'dealerships.id', '=', 'opportunities.dealership_id')
            ->leftJoin('users', 'users.id', '=', 'dealerships.user_id')
            ->select([
                'opportunities.id',
                'opportunities.name',
                'opportunities.stage',
                'opportunities.estimated_value',
                'opportunities.probability',
                'opportunities.expected_close_date',
                'opportunities.next_action',
                'opportunities.dealership_id',
                'dealerships.name as dealership_name',
                'users.name as rep_name',
            ])
            ->addSelect([
                'last_activity_at' => $lastActivity,
                'recent_activity_count' => $recentActivityCount,
            ])
            ->orderByRaw('last_activity_at IS NULL')
            ->orderByDesc('last_activity_at')
            ->orderByRaw('opportunities.expected_close_date IS NULL')
            ->orderBy('opportunities.expected_close_date')
            ->orderBy('opportunities.id')
            ->paginate($perPage, pageName: 'deals_page')
            ->withQueryString()
            ->through(function (Opportunity $opportunity): array {
                $lastActivityAt = $opportunity->getAttribute('last_activity_at');
                $workedAt = is_string($lastActivityAt) || $lastActivityAt instanceof DateTimeInterface
                    ? Carbon::parse($lastActivityAt)
                    : null;
                $recentCount = $opportunity->getAttribute('recent_activity_count');
                $dealershipName = $opportunity->getAttribute('dealership_name');
                $repName = $opportunity->getAttribute('rep_name');

                return [
                    'id' => $opportunity->id,
                    'name' => $opportunity->name,
                    'stage' => $opportunity->stage->value,
                    'stageLabel' => $opportunity->stage->getLabel(),
                    'estimatedValue' => (float) ($opportunity->estimated_value ?? 0),
                    'probability' => $opportunity->probability,
                    'expectedCloseDate' => $opportunity->expected_close_date?->toDateString(),
                    'nextAction' => $opportunity->next_action,
                    'dealership' => [
                        'id' => $opportunity->dealership_id,
                        'name' => is_string($dealershipName) ? $dealershipName : '',
                    ],
                    'repName' => is_string($repName) ? $repName : null,
                    'lastActivityAt' => $workedAt?->toDateString(),
                    'daysSinceActivity' => $workedAt === null ? null : (int) $workedAt->startOfDay()->diffInDays(now()->startOfDay()),
                    'recentActivityCount' => is_numeric($recentCount) ? (int) $recentCount : 0,
                ];
            });
    }
}
