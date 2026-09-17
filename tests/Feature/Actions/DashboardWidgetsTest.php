<?php

declare(strict_types=1);

use App\Actions\Dealerships\BuildActivitySummary;
use App\Actions\Dealerships\BuildBookSummary;
use App\Actions\Dealerships\BuildPipelineSummary;
use App\Actions\Dealerships\ListDealershipsGoingQuiet;
use App\Models\Dealership;
use App\Models\Opportunity;
use App\Models\Progress;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
});

describe('BuildBookSummary', function (): void {
    it('counts the user\'s dealerships by rating', function (): void {
        Dealership::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'status' => 'active',
            'rating' => 'hot',
        ]);
        Dealership::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'active',
            'rating' => 'cold',
        ]);

        expect((new BuildBookSummary)($this->user))
            ->toBe(['total' => 3, 'hot' => 2, 'warm' => 0, 'cold' => 1]);
    });

    it('normalises ratings that were stored capitalised', function (): void {
        Dealership::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'active',
            'rating' => 'Warm',
        ]);

        expect((new BuildBookSummary)($this->user)['warm'])->toBe(1);
    });

    it('ignores imported dealerships and other users\' dealerships', function (): void {
        Dealership::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'imported',
            'rating' => 'hot',
        ]);
        Dealership::factory()->create([
            'user_id' => User::factory(),
            'status' => 'active',
            'rating' => 'hot',
        ]);

        expect((new BuildBookSummary)($this->user))
            ->toBe(['total' => 0, 'hot' => 0, 'warm' => 0, 'cold' => 0]);
    });
});

describe('ListDealershipsGoingQuiet', function (): void {
    it('lists only hot and warm dealerships', function (): void {
        $hot = Dealership::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'active',
            'rating' => 'hot',
        ]);
        Dealership::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'active',
            'rating' => 'cold',
        ]);

        $result = (new ListDealershipsGoingQuiet)($this->user);

        expect($result)->toHaveCount(1)
            ->and($result[0]['id'])->toBe($hot->id);
    });

    it('puts hot dealerships ahead of warm ones', function (): void {
        Dealership::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'active',
            'rating' => 'warm',
            'name' => 'Aaa Warm Motors',
        ]);
        Dealership::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'active',
            'rating' => 'hot',
            'name' => 'Zzz Hot Motors',
        ]);

        expect(array_column((new ListDealershipsGoingQuiet)($this->user), 'rating'))
            ->toBe(['hot', 'warm']);
    });

    it('reports dealerships with no logged contact first and marks them null', function (): void {
        $touched = Dealership::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'active',
            'rating' => 'hot',
        ]);
        Progress::create([
            'user_id' => $this->user->id,
            'dealership_id' => $touched->id,
            'details' => 'Called them',
            'date' => now()->subDays(10),
        ]);

        $untouched = Dealership::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'active',
            'rating' => 'hot',
        ]);

        $result = (new ListDealershipsGoingQuiet)($this->user);

        expect($result[0]['id'])->toBe($untouched->id)
            ->and($result[0]['daysSinceTouch'])->toBeNull()
            ->and($result[1]['id'])->toBe($touched->id)
            ->and($result[1]['daysSinceTouch'])->toBe(10);
    });

    it('orders touched dealerships stalest first', function (): void {
        $recent = Dealership::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'active',
            'rating' => 'hot',
        ]);
        $stale = Dealership::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'active',
            'rating' => 'hot',
        ]);

        Progress::create([
            'user_id' => $this->user->id,
            'dealership_id' => $recent->id,
            'details' => 'Recent call',
            'date' => now()->subDays(3),
        ]);
        Progress::create([
            'user_id' => $this->user->id,
            'dealership_id' => $stale->id,
            'details' => 'Old call',
            'date' => now()->subDays(200),
        ]);

        expect(array_column((new ListDealershipsGoingQuiet)($this->user), 'id'))
            ->toBe([$stale->id, $recent->id]);
    });

    it('uses the most recent progress entry as the last contact', function (): void {
        $dealership = Dealership::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'active',
            'rating' => 'hot',
        ]);

        foreach ([90, 5, 40] as $daysAgo) {
            Progress::create([
                'user_id' => $this->user->id,
                'dealership_id' => $dealership->id,
                'details' => "Contact {$daysAgo} days ago",
                'date' => now()->subDays($daysAgo),
            ]);
        }

        expect((new ListDealershipsGoingQuiet)($this->user)[0]['daysSinceTouch'])->toBe(5);
    });

    it('respects the limit', function (): void {
        Dealership::factory()->count(4)->create([
            'user_id' => $this->user->id,
            'status' => 'active',
            'rating' => 'hot',
        ]);

        expect((new ListDealershipsGoingQuiet)($this->user, 2))->toHaveCount(2);
    });

    it('excludes imported dealerships and other users\' dealerships', function (): void {
        Dealership::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'imported',
            'rating' => 'hot',
        ]);
        Dealership::factory()->create([
            'user_id' => User::factory(),
            'status' => 'active',
            'rating' => 'hot',
        ]);

        expect((new ListDealershipsGoingQuiet)($this->user))->toBeEmpty();
    });
});

describe('BuildPipelineSummary', function (): void {
    it('totals open deals by stage in funnel order', function (): void {
        $dealership = Dealership::factory()->create(['user_id' => $this->user->id, 'status' => 'active']);

        Opportunity::factory()->create(['dealership_id' => $dealership->id, 'stage' => 'demo', 'estimated_value' => 1000, 'probability' => 50]);
        Opportunity::factory()->create(['dealership_id' => $dealership->id, 'stage' => 'demo', 'estimated_value' => 3000, 'probability' => 10]);
        Opportunity::factory()->create(['dealership_id' => $dealership->id, 'stage' => 'prospect', 'estimated_value' => 500, 'probability' => 0]);
        Opportunity::factory()->create(['dealership_id' => $dealership->id, 'stage' => 'lost', 'estimated_value' => 9000]);

        $summary = (new BuildPipelineSummary)($this->user);

        expect($summary['openCount'])->toBe(3)
            ->and($summary['openValue'])->toBe(4500.0)
            ->and($summary['weightedValue'])->toBe(800.0)
            ->and(array_column($summary['stages'], 'stage'))
            ->toBe(['prospect', 'contacted', 'qualified', 'demo', 'proposal', 'negotiation'])
            ->and($summary['stages'][3])->toMatchArray(['count' => 2, 'value' => 4000.0]);
    });

    it('counts deals won since the start of the month', function (): void {
        $dealership = Dealership::factory()->create(['user_id' => $this->user->id, 'status' => 'active']);

        Opportunity::factory()->create(['dealership_id' => $dealership->id, 'stage' => 'won', 'actual_value' => 2500, 'closed_at' => now()]);
        Opportunity::factory()->create(['dealership_id' => $dealership->id, 'stage' => 'won', 'actual_value' => 9999, 'closed_at' => now()->subMonthsNoOverflow(2)]);

        $summary = (new BuildPipelineSummary)($this->user);

        expect($summary['wonThisMonthCount'])->toBe(1)
            ->and($summary['wonThisMonthValue'])->toBe(2500.0);
    });

    it('ignores deals outside the user\'s book', function (): void {
        $imported = Dealership::factory()->create(['user_id' => $this->user->id, 'status' => 'imported']);
        $someoneElses = Dealership::factory()->create(['user_id' => User::factory(), 'status' => 'active']);

        Opportunity::factory()->create(['dealership_id' => $imported->id, 'stage' => 'demo']);
        Opportunity::factory()->create(['dealership_id' => $someoneElses->id, 'stage' => 'demo']);

        expect((new BuildPipelineSummary)($this->user)['openCount'])->toBe(0);
    });
});

describe('BuildActivitySummary', function (): void {
    it('compares logged contacts this week with the week before', function (): void {
        $dealership = Dealership::factory()->create(['user_id' => $this->user->id, 'status' => 'active']);

        foreach ([0, 6, 7, 13, 14] as $daysAgo) {
            Progress::create([
                'user_id' => $this->user->id,
                'dealership_id' => $dealership->id,
                'details' => "Contact {$daysAgo} days ago",
                'date' => now()->subDays($daysAgo)->toDateString(),
            ]);
        }

        $summary = (new BuildActivitySummary)($this->user);

        expect($summary['thisWeek'])->toBe(2)
            ->and($summary['lastWeek'])->toBe(2);
    });

    it('lists the latest entries first with their dealership', function (): void {
        $dealership = Dealership::factory()->create(['user_id' => $this->user->id, 'status' => 'active', 'name' => 'Lakeside RV']);

        Progress::create(['user_id' => $this->user->id, 'dealership_id' => $dealership->id, 'details' => 'Older', 'date' => now()->subDays(5)->toDateString()]);
        Progress::create(['user_id' => $this->user->id, 'dealership_id' => $dealership->id, 'details' => '<p>Newest</p>', 'date' => now()->toDateString()]);

        $recent = (new BuildActivitySummary)($this->user)['recent'];

        expect(array_column($recent, 'details'))->toBe(['Newest', 'Older'])
            ->and($recent[0]['dealership'])->toBe(['id' => $dealership->id, 'name' => 'Lakeside RV'])
            ->and($recent[0]['author'])->toBe($this->user->name);
    });

    it('ignores entries outside the user\'s book', function (): void {
        $someoneElses = Dealership::factory()->create(['user_id' => User::factory(), 'status' => 'active']);

        Progress::create(['user_id' => $this->user->id, 'dealership_id' => $someoneElses->id, 'details' => 'Not mine', 'date' => now()->toDateString()]);

        $summary = (new BuildActivitySummary)($this->user);

        expect($summary['thisWeek'])->toBe(0)
            ->and($summary['recent'])->toBeEmpty();
    });
});
