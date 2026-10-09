<?php

declare(strict_types=1);

use App\Enum\OpportunityStage;
use App\Enum\TaskType;
use App\Models\Dealership;
use App\Models\Opportunity;
use App\Models\OpportunityActivity;
use App\Models\Task;
use App\Models\User;

use function Pest\Laravel\artisan;

beforeEach(function () {
    $this->rep = User::factory()->create();
    $this->dealership = Dealership::factory()->create(['user_id' => $this->rep->id]);
});

function quietDeal(Dealership $dealership, array $attributes = []): Opportunity
{
    return Opportunity::factory()->create([
        'dealership_id' => $dealership->id,
        'stage' => OpportunityStage::Demo->value,
        'stage_entered_at' => now()->subDays(30),
        'created_at' => now()->subDays(30),
        ...$attributes,
    ]);
}

it('creates a follow-up task for the dealership owner on a quiet deal', function () {
    $deal = quietDeal($this->dealership, ['name' => 'Fleet renewal']);
    OpportunityActivity::factory()->create(['opportunity_id' => $deal->id, 'occurred_at' => now()->subDays(20)->toDateString()]);

    artisan('opportunities:follow-up-tasks')->assertSuccessful();

    $task = Task::sole();

    expect($task->user_id)->toBe($this->rep->id)
        ->and($task->opportunity_id)->toBe($deal->id)
        ->and($task->dealership_id)->toBe($this->dealership->id)
        ->and($task->type)->toBe(TaskType::FollowUp)
        ->and($task->title)->toBe('Follow up: Fleet renewal')
        ->and($task->due_date->isToday())->toBeTrue();
});

it('skips deals that were recently worked, moved, created or closed', function () {
    $worked = quietDeal($this->dealership);
    OpportunityActivity::factory()->create(['opportunity_id' => $worked->id, 'occurred_at' => now()->subDays(3)->toDateString()]);
    quietDeal($this->dealership, ['stage_entered_at' => now()->subDays(3)]);
    quietDeal($this->dealership, ['created_at' => now()->subDays(3), 'stage_entered_at' => now()->subDays(3)]);
    quietDeal($this->dealership, ['stage' => OpportunityStage::Won->value]);

    artisan('opportunities:follow-up-tasks')->assertSuccessful();

    expect(Task::count())->toBe(0);
});

it('does not pile up tasks on the same deal', function () {
    $deal = quietDeal($this->dealership);

    artisan('opportunities:follow-up-tasks');
    artisan('opportunities:follow-up-tasks');
    expect(Task::count())->toBe(1);

    Task::sole()->update(['completed_at' => now()]);
    artisan('opportunities:follow-up-tasks');
    expect(Task::count())->toBe(1);

    $this->travel(15)->days();
    artisan('opportunities:follow-up-tasks');
    expect(Task::where('opportunity_id', $deal->id)->count())->toBe(2);
});
