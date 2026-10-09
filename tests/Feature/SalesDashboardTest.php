<?php

declare(strict_types=1);

use App\Enum\OpportunityStage;
use App\Models\Dealership;
use App\Models\Opportunity;
use App\Models\OpportunityActivity;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->user = User::factory()->create();
    actingAs($this->user);
});

describe('OpportunityStage enum', function () {
    it('correctly identifies open stages', function () {
        expect(OpportunityStage::Prospect->isOpen())->toBeTrue();
        expect(OpportunityStage::Contacted->isOpen())->toBeTrue();
        expect(OpportunityStage::Won->isOpen())->toBeFalse();
        expect(OpportunityStage::Lost->isOpen())->toBeFalse();
    });

    it('returns correct open values', function () {
        $open = OpportunityStage::openValues();

        expect($open)->toContain('prospect', 'contacted', 'qualified', 'demo', 'proposal', 'negotiation')
            ->not->toContain('won', 'lost');
    });

    it('returns correct closed values', function () {
        expect(OpportunityStage::closedValues())->toBe(['won', 'lost']);
    });

    it('has labels for all cases', function () {
        foreach (OpportunityStage::cases() as $stage) {
            expect($stage->getLabel())->toBeString()->not->toBeEmpty();
        }
    });

    it('has colors for all cases', function () {
        foreach (OpportunityStage::cases() as $stage) {
            expect($stage->getColor())->toBeString()->not->toBeEmpty();
        }
    });
});

describe('Opportunity model scopes', function () {
    it('scopes to won opportunities', function () {
        $dealership = Dealership::factory()->create(['user_id' => $this->user->id]);
        Opportunity::factory()->won()->count(2)->create(['dealership_id' => $dealership->id]);
        Opportunity::factory()->lost()->count(3)->create(['dealership_id' => $dealership->id]);
        Opportunity::factory()->open()->count(4)->create(['dealership_id' => $dealership->id]);

        expect(Opportunity::won()->count())->toBe(2);
    });

    it('scopes to lost opportunities', function () {
        $dealership = Dealership::factory()->create(['user_id' => $this->user->id]);
        Opportunity::factory()->won()->count(2)->create(['dealership_id' => $dealership->id]);
        Opportunity::factory()->lost()->count(3)->create(['dealership_id' => $dealership->id]);

        expect(Opportunity::lost()->count())->toBe(3);
    });

    it('scopes to open opportunities', function () {
        $dealership = Dealership::factory()->create(['user_id' => $this->user->id]);
        Opportunity::factory()->open()->count(4)->create(['dealership_id' => $dealership->id]);
        Opportunity::factory()->won()->count(2)->create(['dealership_id' => $dealership->id]);
        Opportunity::factory()->lost()->count(1)->create(['dealership_id' => $dealership->id]);

        expect(Opportunity::open()->count())->toBe(4);
    });

    it('scopes to opportunities closing this month', function () {
        $dealership = Dealership::factory()->create(['user_id' => $this->user->id]);

        Opportunity::factory()->create([
            'dealership_id' => $dealership->id,
            'stage' => OpportunityStage::Proposal->value,
            'expected_close_date' => now()->startOfMonth(),
        ]);

        Opportunity::factory()->create([
            'dealership_id' => $dealership->id,
            'stage' => OpportunityStage::Proposal->value,
            'expected_close_date' => now()->addMonths(2),
        ]);

        expect(Opportunity::closingThisMonth()->count())->toBe(1);
    });
});

describe('Dealership::opportunities relationship', function () {
    it('has a working opportunities relationship', function () {
        $dealership = Dealership::factory()->create(['user_id' => $this->user->id]);
        Opportunity::factory()->count(3)->create(['dealership_id' => $dealership->id]);

        expect($dealership->opportunities()->count())->toBe(3);
    });
});

describe('Sales Dashboard page', function () {
    it('renders the page successfully', function () {
        get('/sales')->assertOk()->assertInertia(
            fn ($page) => $page->component('SalesDashboard/Index')
                ->has('kpis')
                ->has('pipelineByStage')
                ->has('repPerformance')
        );
    });

    it('returns correct kpi structure', function () {
        $dealership = Dealership::factory()->create(['user_id' => $this->user->id]);
        Opportunity::factory()->won()->count(2)->create(['dealership_id' => $dealership->id]);
        Opportunity::factory()->lost()->count(3)->create(['dealership_id' => $dealership->id]);
        Opportunity::factory()->open()->count(4)->create(['dealership_id' => $dealership->id]);

        get('/sales')->assertInertia(
            fn ($page) => $page
                ->has('kpis.pipelineValue')
                ->has('kpis.openCount')
                ->has('kpis.wonCount')
                ->has('kpis.winRate')
                ->has('kpis.avgDealSize')
                ->has('kpis.avgDaysToClose')
                ->has('kpis.closingThisMonthCount')
                ->has('kpis.wonLastMonthCount')
                ->where('kpis.openCount', 4)
                ->where('kpis.wonCount', 2)
                ->where('kpis.winRate', 40)
        );
    });

    it('returns pipeline by stage with all stages', function () {
        get('/sales')->assertInertia(
            fn ($page) => $page->has('pipelineByStage', count(OpportunityStage::cases()))
        );
    });

    it('requires authentication', function () {
        auth()->logout();
        get('/sales')->assertRedirect('/login');
    });
});

describe('Sales Dashboard open deals', function () {
    it('lists open deals with their dealership and rep, most recently worked first', function () {
        $dealership = Dealership::factory()->create(['user_id' => $this->user->id, 'name' => 'Lakeside RV']);
        $quiet = Opportunity::factory()->open()->create(['dealership_id' => $dealership->id, 'name' => 'Quiet deal']);
        $active = Opportunity::factory()->open()->create(['dealership_id' => $dealership->id, 'name' => 'Active deal']);
        $untouched = Opportunity::factory()->open()->create(['dealership_id' => $dealership->id, 'name' => 'Untouched deal']);
        Opportunity::factory()->won()->create(['dealership_id' => $dealership->id]);

        OpportunityActivity::factory()->create(['opportunity_id' => $quiet->id, 'occurred_at' => now()->subDays(40)->toDateString()]);
        OpportunityActivity::factory()->count(2)->create(['opportunity_id' => $active->id, 'occurred_at' => now()->subDays(2)->toDateString()]);

        get('/sales')->assertInertia(
            fn ($page) => $page
                ->has('openDeals.data', 3)
                ->where('openDeals.data.0.id', $active->id)
                ->where('openDeals.data.0.dealership', ['id' => $dealership->id, 'name' => 'Lakeside RV'])
                ->where('openDeals.data.0.repName', $this->user->name)
                ->where('openDeals.data.0.daysSinceActivity', 2)
                ->where('openDeals.data.0.recentActivityCount', 2)
                ->where('openDeals.data.1.id', $quiet->id)
                ->where('openDeals.data.1.daysSinceActivity', 40)
                ->where('openDeals.data.1.recentActivityCount', 0)
                ->where('openDeals.data.2.id', $untouched->id)
                ->where('openDeals.data.2.lastActivityAt', null)
        );
    });

    it('pages open deals using the deals_page query string', function () {
        $dealership = Dealership::factory()->create(['user_id' => $this->user->id]);
        Opportunity::factory()->open()->count(17)->create(['dealership_id' => $dealership->id]);

        get('/sales?deals_page=2')->assertInertia(
            fn ($page) => $page
                ->has('openDeals.data', 2)
                ->where('openDeals.current_page', 2)
                ->where('openDeals.last_page', 2)
                ->where('openDeals.total', 17)
        );
    });
});

describe('Sales Dashboard open deal filters', function () {
    it('filters open deals by rep and stage', function () {
        $otherRep = User::factory()->create();
        $mine = Dealership::factory()->create(['user_id' => $this->user->id]);
        $theirs = Dealership::factory()->create(['user_id' => $otherRep->id]);
        $myDemo = Opportunity::factory()->create(['dealership_id' => $mine->id, 'stage' => OpportunityStage::Demo->value]);
        Opportunity::factory()->create(['dealership_id' => $mine->id, 'stage' => OpportunityStage::Proposal->value]);
        Opportunity::factory()->create(['dealership_id' => $theirs->id, 'stage' => OpportunityStage::Demo->value]);

        get('/sales?rep='.$this->user->id.'&stage=demo')->assertInertia(
            fn ($page) => $page
                ->has('openDeals.data', 1)
                ->where('openDeals.data.0.id', $myDemo->id)
                ->where('dealFilters.rep', (string) $this->user->id)
                ->where('dealFilters.stage', 'demo')
        );
    });

    it('filters to quiet deals with no activity in 21 days', function () {
        $dealership = Dealership::factory()->create(['user_id' => $this->user->id]);
        $quiet = Opportunity::factory()->open()->create(['dealership_id' => $dealership->id]);
        $untouched = Opportunity::factory()->open()->create(['dealership_id' => $dealership->id]);
        $active = Opportunity::factory()->open()->create(['dealership_id' => $dealership->id]);
        OpportunityActivity::factory()->create(['opportunity_id' => $quiet->id, 'occurred_at' => now()->subDays(22)->toDateString()]);
        OpportunityActivity::factory()->create(['opportunity_id' => $active->id, 'occurred_at' => now()->subDays(20)->toDateString()]);

        get('/sales?activity=quiet')->assertInertia(
            fn ($page) => $page->where(
                'openDeals.data',
                fn ($deals) => collect($deals)->pluck('id')->sort()->values()->all() === collect([$quiet->id, $untouched->id])->sort()->values()->all()
            )
        );
    });

    it('filters by close date this month or overdue', function () {
        $dealership = Dealership::factory()->create(['user_id' => $this->user->id]);
        $thisMonth = Opportunity::factory()->open()->create(['dealership_id' => $dealership->id, 'expected_close_date' => now()->endOfMonth()->toDateString()]);
        $overdue = Opportunity::factory()->open()->create(['dealership_id' => $dealership->id, 'expected_close_date' => now()->subMonths(2)->toDateString()]);
        Opportunity::factory()->open()->create(['dealership_id' => $dealership->id, 'expected_close_date' => now()->addMonths(2)->toDateString()]);

        get('/sales?closing=this_month')->assertInertia(
            fn ($page) => $page->has('openDeals.data', 1)->where('openDeals.data.0.id', $thisMonth->id)
        );

        get('/sales?closing=overdue')->assertInertia(
            fn ($page) => $page->has('openDeals.data', 1)->where('openDeals.data.0.id', $overdue->id)
        );
    });

    it('rejects filter values it does not understand', function () {
        get('/sales?stage=won&closing=someday')->assertSessionHasErrors(['stage', 'closing']);
    });

    it('offers only reps who own a dealership with an open deal', function () {
        $idleRep = User::factory()->create();
        Dealership::factory()->create(['user_id' => $idleRep->id]);
        $dealership = Dealership::factory()->create(['user_id' => $this->user->id]);
        Opportunity::factory()->open()->create(['dealership_id' => $dealership->id]);

        get('/sales')->assertInertia(
            fn ($page) => $page
                ->has('repOptions', 1)
                ->where('repOptions.0.value', (string) $this->user->id)
        );
    });
});

describe('Sales Dashboard stage conversion', function () {
    it('counts deals reaching each stage and how many moved on', function () {
        $dealership = Dealership::factory()->create(['user_id' => $this->user->id]);

        $this->travelTo(now()->subDays(10));
        $advanced = Opportunity::factory()->create(['dealership_id' => $dealership->id, 'stage' => OpportunityStage::Demo->value, 'stage_entered_at' => null, 'closed_at' => null]);
        $stalled = Opportunity::factory()->create(['dealership_id' => $dealership->id, 'stage' => OpportunityStage::Demo->value, 'stage_entered_at' => null, 'closed_at' => null]);
        $this->travelBack();

        $this->travelTo(now()->subDays(4));
        $advanced->update(['stage' => OpportunityStage::Proposal->value]);
        $this->travelBack();

        get('/sales')->assertInertia(
            fn ($page) => $page->where('stageConversion', function ($stages) {
                $demo = collect($stages)->firstWhere('stage', 'demo');

                return $demo['reached'] === 2
                    && $demo['advanced'] === 1
                    && $demo['conversionRate'] === 50
                    && $demo['avgDaysInStage'] === 6;
            })
        );
    });
});
