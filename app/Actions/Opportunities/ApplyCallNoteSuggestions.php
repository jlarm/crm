<?php

declare(strict_types=1);

namespace App\Actions\Opportunities;

use App\Models\Contact;
use App\Models\Opportunity;
use App\Models\OpportunityActivity;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class ApplyCallNoteSuggestions
{
    /**
     * Log the activity and apply the AI suggestions the rep approved, all or nothing.
     *
     * When a summary was approved it leads the activity details, with the rep's raw
     * notes kept underneath so nothing they wrote is lost.
     *
     * @param  array{type: string, details: string, occurred_at?: string|null}  $activity
     * @param  array{summary: string, stage: string|null, rating: string|null, tasks: list<array{title: string, type: string, priority: string, due_date: string|null, contact_id: int|null}>, contacts: list<array{name: string, position: string|null, email: string|null, phone: string|null}>}  $suggestions
     */
    public function __invoke(Opportunity $opportunity, User $user, array $activity, array $suggestions): OpportunityActivity
    {
        return DB::transaction(function () use ($opportunity, $user, $activity, $suggestions): OpportunityActivity {
            $details = $suggestions['summary'] !== ''
                ? "Summary:\n".$suggestions['summary']."\n\nNotes:\n".$activity['details']
                : $activity['details'];

            $loggedActivity = $opportunity->activities()->create([
                'type' => $activity['type'],
                'details' => $details,
                'occurred_at' => $activity['occurred_at'] ?? null,
                'user_id' => $user->id,
            ]);

            foreach ($suggestions['tasks'] as $task) {
                Task::create([
                    ...$task,
                    'user_id' => $user->id,
                    'created_by_user_id' => $user->id,
                    'dealership_id' => $opportunity->dealership_id,
                    'opportunity_id' => $opportunity->id,
                ]);
            }

            foreach ($suggestions['contacts'] as $contact) {
                Contact::create([...$contact, 'dealership_id' => $opportunity->dealership_id]);
            }

            if ($suggestions['stage'] !== null && $suggestions['stage'] !== $opportunity->stage->value) {
                $opportunity->update(['stage' => $suggestions['stage']]);
            }

            if ($suggestions['rating'] !== null) {
                $opportunity->dealership->update(['rating' => $suggestions['rating']]);
            }

            return $loggedActivity;
        });
    }
}
