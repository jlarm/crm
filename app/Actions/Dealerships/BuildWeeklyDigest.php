<?php

declare(strict_types=1);

namespace App\Actions\Dealerships;

use App\Actions\Opportunities\ListOpenDeals;
use App\Enum\OpportunityStage;
use App\Models\Opportunity;
use App\Models\OpportunityActivity;
use App\Models\OpportunityStageChange;
use App\Models\Task;
use App\Models\User;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

final class BuildWeeklyDigest
{
    /**
     * The last seven days of deal movement in a user's book, plus what needs attention next.
     *
     * "Advanced" and "slipped" compare positions along the happy path (Prospect → Won),
     * so a deal that skips a stage still counts once. Creations are counted as new deals,
     * not moves. Quiet deals use the same 21-day threshold as the sales dashboard.
     *
     * @return array{periodStart: string, periodEnd: string, counts: array{newDeals: int, advanced: int, slipped: int, won: int, lost: int, wonValue: float, activitiesLogged: int, tasksCompleted: int, tasksDueThisWeek: int, tasksOverdue: int}, moves: list<array{id: int, opportunityId: int, name: string, dealership: array{id: int, name: string}, from: array{value: string, label: string}|null, to: array{value: string, label: string}, direction: string, changedAt: string, changedBy: string|null}>, quietDeals: list<array{id: int, name: string, dealership: array{id: int, name: string}, stageLabel: string, estimatedValue: float, daysSinceActivity: int|null}>}
     */
    public function __invoke(User $user, int $moveLimit = 8, int $quietLimit = 5): array
    {
        $since = now()->subDays(7)->startOfDay();

        $changes = OpportunityStageChange::query()
            ->with(['opportunity:id,name,dealership_id,actual_value,estimated_value', 'opportunity.dealership:id,name', 'user:id,name'])
            ->whereHas('opportunity', fn (Builder $opportunity) => $opportunity->whereHas(
                'dealership',
                fn (Builder $dealership) => $dealership->forUser($user)->whereNot('status', 'imported'),
            ))
            ->where('changed_at', '>=', $since)
            ->orderByDesc('changed_at')
            ->orderByDesc('id')
            ->get();

        $counts = ['newDeals' => 0, 'advanced' => 0, 'slipped' => 0, 'won' => 0, 'lost' => 0, 'wonValue' => 0.0];
        $moves = [];

        foreach ($changes as $change) {
            $direction = $this->direction($change->from_stage, $change->to_stage);

            match ($direction) {
                'new' => $counts['newDeals']++,
                'won' => $counts['won']++,
                'lost' => $counts['lost']++,
                'advanced' => $counts['advanced']++,
                'slipped' => $counts['slipped']++,
                default => null,
            };

            if ($direction === 'won') {
                $counts['wonValue'] += (float) ($change->opportunity->actual_value ?? $change->opportunity->estimated_value ?? 0);
            }

            if (count($moves) < $moveLimit) {
                $moves[] = [
                    'id' => $change->id,
                    'opportunityId' => $change->opportunity_id,
                    'name' => $change->opportunity->name,
                    'dealership' => [
                        'id' => $change->opportunity->dealership->id,
                        'name' => $change->opportunity->dealership->name,
                    ],
                    'from' => $change->from_stage === null ? null : ['value' => $change->from_stage->value, 'label' => $change->from_stage->getLabel()],
                    'to' => ['value' => $change->to_stage->value, 'label' => $change->to_stage->getLabel()],
                    'direction' => $direction,
                    'changedAt' => $change->changed_at->toDateString(),
                    'changedBy' => $change->user?->name,
                ];
            }
        }

        return [
            'periodStart' => $since->toDateString(),
            'periodEnd' => now()->toDateString(),
            'counts' => [
                ...$counts,
                'activitiesLogged' => OpportunityActivity::query()
                    ->where('user_id', $user->id)
                    ->where('created_at', '>=', $since)
                    ->count(),
                'tasksCompleted' => Task::forUser($user)->where('completed_at', '>=', $since)->count(),
                'tasksDueThisWeek' => Task::forUser($user)
                    ->whereNull('completed_at')
                    ->whereDate('due_date', '>=', now()->toDateString())
                    ->whereDate('due_date', '<=', now()->addDays(6)->toDateString())
                    ->count(),
                'tasksOverdue' => Task::forUser($user)->overdue()->count(),
            ],
            'moves' => $moves,
            'quietDeals' => $this->quietDeals($user, $quietLimit),
        ];
    }

    private function direction(?OpportunityStage $from, OpportunityStage $to): string
    {
        if ($from === null) {
            return 'new';
        }

        if ($to === OpportunityStage::Won || $to === OpportunityStage::Lost) {
            return $to->value;
        }

        $order = array_filter(
            OpportunityStage::cases(),
            fn (OpportunityStage $stage): bool => $stage !== OpportunityStage::Lost,
        );

        return array_search($to, $order, true) > array_search($from, $order, true) ? 'advanced' : 'slipped';
    }

    /**
     * Open deals in the user's book with no logged activity in the quiet window, longest-quiet first.
     *
     * @return list<array{id: int, name: string, dealership: array{id: int, name: string}, stageLabel: string, estimatedValue: float, daysSinceActivity: int|null}>
     */
    private function quietDeals(User $user, int $limit): array
    {
        $cutoff = now()->subDays(ListOpenDeals::QUIET_AFTER_DAYS)->startOfDay();

        $lastActivity = OpportunityActivity::query()
            ->selectRaw('MAX(COALESCE(opportunity_activities.occurred_at, opportunity_activities.created_at))')
            ->whereColumn('opportunity_activities.opportunity_id', 'opportunities.id');

        return array_values(Opportunity::query()
            ->with('dealership:id,name')
            ->open()
            ->whereHas('dealership', fn (Builder $dealership) => $dealership->forUser($user)->whereNot('status', 'imported'))
            ->whereDoesntHave('activities', fn (Builder $activities) => $activities->whereRaw(
                'COALESCE(opportunity_activities.occurred_at, opportunity_activities.created_at) >= ?',
                [$cutoff],
            ))
            ->select(['id', 'name', 'dealership_id', 'stage', 'estimated_value'])
            ->addSelect(['last_activity_at' => $lastActivity])
            ->orderByRaw('last_activity_at IS NOT NULL')
            ->orderBy('last_activity_at')
            ->orderByDesc('estimated_value')
            ->limit($limit)
            ->get()
            ->map(function (Opportunity $opportunity): array {
                $lastActivityAt = $opportunity->getAttribute('last_activity_at');
                $touchedAt = is_string($lastActivityAt) || $lastActivityAt instanceof DateTimeInterface
                    ? Carbon::parse($lastActivityAt)
                    : null;

                return [
                    'id' => $opportunity->id,
                    'name' => $opportunity->name,
                    'dealership' => ['id' => $opportunity->dealership->id, 'name' => $opportunity->dealership->name],
                    'stageLabel' => $opportunity->stage->getLabel(),
                    'estimatedValue' => (float) ($opportunity->estimated_value ?? 0),
                    'daysSinceActivity' => $touchedAt === null ? null : (int) $touchedAt->startOfDay()->diffInDays(now()->startOfDay()),
                ];
            })
            ->all());
    }
}
