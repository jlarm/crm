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
