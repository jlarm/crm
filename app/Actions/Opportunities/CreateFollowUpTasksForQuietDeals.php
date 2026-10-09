<?php

declare(strict_types=1);

namespace App\Actions\Opportunities;

use App\Enum\OpportunityStage;
use App\Enum\TaskPriority;
use App\Enum\TaskType;
use App\Models\Opportunity;
use App\Models\Task;
use Illuminate\Database\Eloquent\Builder;

final class CreateFollowUpTasksForQuietDeals
{
    /**
     * Give the dealership owner a follow-up task for each open deal nobody has touched.
     *
     * A deal is touched when an activity is logged, its stage moves, or it is created.
     * Deals that already have an open task, or got a task within the window, are left
     * alone so completing a task without logging activity does not trigger a new one daily.
     *
     * @return int The number of tasks created.
     */
    public function __invoke(int $quietAfterDays = 14): int
    {
        $cutoff = now()->subDays($quietAfterDays)->startOfDay();

        $opportunities = Opportunity::query()
            ->with('dealership:id,user_id')
            ->whereNotIn('stage', OpportunityStage::closedValues())
            ->where('created_at', '<', $cutoff)
            ->where(fn (Builder $query) => $query->whereNull('stage_entered_at')->orWhere('stage_entered_at', '<', $cutoff))
            ->whereHas('dealership', fn (Builder $query) => $query->whereNotNull('user_id'))
            ->whereDoesntHave('activities', fn (Builder $query) => $query->whereRaw(
                'COALESCE(opportunity_activities.occurred_at, opportunity_activities.created_at) >= ?',
                [$cutoff],
            ))
            ->whereNotIn('id', Task::query()
                ->select('opportunity_id')
                ->whereNotNull('opportunity_id')
                ->where(fn (Builder $query) => $query->whereNull('completed_at')->orWhere('created_at', '>=', $cutoff)))
            ->get();

        foreach ($opportunities as $opportunity) {
            $ownerId = $opportunity->dealership->user_id;

            if ($ownerId === null) {
                continue;
            }

            Task::create([
                'user_id' => $ownerId,
                'created_by_user_id' => $ownerId,
                'dealership_id' => $opportunity->dealership_id,
                'opportunity_id' => $opportunity->id,
                'title' => 'Follow up: '.$opportunity->name,
                'description' => sprintf('No activity logged on this %s deal in %d+ days.', mb_strtolower($opportunity->stage->getLabel()), $quietAfterDays),
                'type' => TaskType::FollowUp,
                'priority' => TaskPriority::Medium,
                'due_date' => now()->toDateString(),
            ]);
        }

        return $opportunities->count();
    }
}
