<?php

declare(strict_types=1);

use App\Actions\Dealerships\BuildWeeklyDigest;
use App\Actions\Dealerships\SummarizeWeeklyDigest;
use App\Ai\Agents\WeeklyDigestWriter;
use App\Enum\OpportunityStage;
use App\Models\Dealership;
use App\Models\Opportunity;
use App\Models\OpportunityActivity;
use App\Models\Task;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->dealership = Dealership::factory()->create(['user_id' => $this->user->id, 'status' => 'active', 'name' => 'Lakeside RV']);
    $this->actingAs($this->user);
});

function dealIn(Dealership $dealership, OpportunityStage $stage, array $attributes = []): Opportunity
{
    return Opportunity::factory()->create([
        'dealership_id' => $dealership->id,
        'stage' => $stage->value,
        'stage_entered_at' => now()->subMonths(2),
        'created_at' => now()->subMonths(2),
        'closed_at' => null,
        ...$attributes,
    ]);
}

describe('BuildWeeklyDigest', function () {
    it('counts this week\'s stage moves in the user\'s book', function () {
        $advanced = dealIn($this->dealership, OpportunityStage::Demo);
        $slipped = dealIn($this->dealership, OpportunityStage::Proposal);
        $won = dealIn($this->dealership, OpportunityStage::Negotiation, ['actual_value' => 12000]);
        $lost = dealIn($this->dealership, OpportunityStage::Qualified);

        $advanced->update(['stage' => OpportunityStage::Proposal->value]);
        $slipped->update(['stage' => OpportunityStage::Qualified->value]);
        $won->update(['stage' => OpportunityStage::Won->value]);
        $lost->update(['stage' => OpportunityStage::Lost->value]);
        dealIn($this->dealership, OpportunityStage::Prospect, ['stage_entered_at' => now(), 'created_at' => now()]);

        $digest = app(BuildWeeklyDigest::class)($this->user);

        expect($digest['counts'])->toMatchArray([
            'advanced' => 1,
            'slipped' => 1,
            'won' => 1,
            'lost' => 1,
            'newDeals' => 1,
            'wonValue' => 12000.0,
        ])->and($digest['moves'])->toHaveCount(5)
            ->and(collect($digest['moves'])->firstWhere('opportunityId', $advanced->id))->toMatchArray([
                'direction' => 'advanced',
                'from' => ['value' => 'demo', 'label' => 'Demo'],
                'to' => ['value' => 'proposal', 'label' => 'Proposal'],
                'dealership' => ['id' => $this->dealership->id, 'name' => 'Lakeside RV'],
                'changedBy' => $this->user->name,
            ]);
    });

    it('ignores moves older than a week and deals outside the user\'s book', function () {
        $old = dealIn($this->dealership, OpportunityStage::Demo);
        $this->travelTo(now()->subDays(8));
        $old->update(['stage' => OpportunityStage::Proposal->value]);
        $this->travelBack();

        $someoneElses = Dealership::factory()->create(['user_id' => User::factory()->create()->id, 'status' => 'active']);
        dealIn($someoneElses, OpportunityStage::Demo)->update(['stage' => OpportunityStage::Proposal->value]);

        $digest = app(BuildWeeklyDigest::class)($this->user);

        expect($digest['moves'])->toBe([])
            ->and($digest['counts']['advanced'])->toBe(0);
    });

    it('lists open deals with no activity in 21 days as needing attention', function () {
        $quiet = dealIn($this->dealership, OpportunityStage::Demo, ['name' => 'Quiet deal']);
        OpportunityActivity::factory()->create(['opportunity_id' => $quiet->id, 'occurred_at' => now()->subDays(30)->toDateString()]);
        $active = dealIn($this->dealership, OpportunityStage::Demo);
        OpportunityActivity::factory()->create(['opportunity_id' => $active->id, 'occurred_at' => now()->subDays(2)->toDateString()]);
        dealIn($this->dealership, OpportunityStage::Won);

        $digest = app(BuildWeeklyDigest::class)($this->user);

        expect($digest['quietDeals'])->toHaveCount(1)
            ->and($digest['quietDeals'][0])->toMatchArray(['id' => $quiet->id, 'daysSinceActivity' => 30]);
    });

    it('counts the user\'s activities and tasks', function () {
        $deal = dealIn($this->dealership, OpportunityStage::Demo);
        OpportunityActivity::factory()->count(2)->create(['opportunity_id' => $deal->id, 'user_id' => $this->user->id]);
        Task::factory()->create(['user_id' => $this->user->id, 'due_date' => now()->addDays(3), 'completed_at' => null]);
        Task::factory()->create(['user_id' => $this->user->id, 'due_date' => now()->subDays(3), 'completed_at' => null]);
        Task::factory()->create(['user_id' => $this->user->id, 'completed_at' => now()->subDay()]);

        $counts = app(BuildWeeklyDigest::class)($this->user)['counts'];

        expect($counts['activitiesLogged'])->toBe(2)
            ->and($counts['tasksDueThisWeek'])->toBe(1)
            ->and($counts['tasksOverdue'])->toBe(1)
            ->and($counts['tasksCompleted'])->toBe(1);
    });
});

describe('SummarizeWeeklyDigest', function () {
    it('writes a summary once and reuses it while the digest is unchanged', function () {
        $calls = 0;
        WeeklyDigestWriter::fake(function () use (&$calls): string {
            $calls++;

            return 'You moved Fleet renewal to Proposal.';
        });
        dealIn($this->dealership, OpportunityStage::Demo)->update(['stage' => OpportunityStage::Proposal->value]);
        $digest = app(BuildWeeklyDigest::class)($this->user);

        $first = app(SummarizeWeeklyDigest::class)($this->user, $digest);
        $second = app(SummarizeWeeklyDigest::class)($this->user, $digest);

        expect($first)->toBe('You moved Fleet renewal to Proposal.')
            ->and($second)->toBe($first);
        expect($calls)->toBe(1);
    });

    it('skips the AI when nothing happened', function () {
        WeeklyDigestWriter::fake();

        $summary = app(SummarizeWeeklyDigest::class)($this->user, app(BuildWeeklyDigest::class)($this->user));

        expect($summary)->toBeNull();
        WeeklyDigestWriter::assertNeverPrompted();
    });

    it('returns null instead of failing the dashboard when the AI errors', function () {
        WeeklyDigestWriter::fake(fn () => throw new RuntimeException('Provider down'));
        dealIn($this->dealership, OpportunityStage::Demo)->update(['stage' => OpportunityStage::Proposal->value]);

        expect(app(SummarizeWeeklyDigest::class)($this->user, app(BuildWeeklyDigest::class)($this->user)))->toBeNull();
    });
});

it('puts the digest on the dashboard and defers the AI summary', function () {
    WeeklyDigestWriter::fake(['Quiet week.']);
    dealIn($this->dealership, OpportunityStage::Demo)->update(['stage' => OpportunityStage::Proposal->value]);

    $this->get('/dashboard')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Dashboard')
            ->where('weeklyDigest.counts.advanced', 1)
            ->missing('weeklyDigestSummary')
            ->loadDeferredProps(fn (AssertableInertia $reload) => $reload
                ->where('weeklyDigestSummary', 'Quiet week.')));
});
