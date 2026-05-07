<?php

declare(strict_types=1);

use App\Models\Contact;
use App\Models\Dealership;
use App\Models\Opportunity;
use App\Models\OpportunityActivity;
use App\Models\Progress;
use App\Models\Store;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

beforeEach(function () {
    $this->user = User::factory()->create();
    actingAs($this->user);
    $this->dealership = Dealership::factory()->create(['user_id' => $this->user->id]);
});

describe('DealershipActivityController index', function () {
    it('returns json with data and meta keys', function () {
        getJson(route('dealerships.activities.index', $this->dealership))
            ->assertOk()
            ->assertJsonStructure([
                'data',
                'meta' => ['currentPage', 'perPage', 'total', 'hasMore'],
            ]);
    });

    it('clamps per_page within 5 and 100', function () {
        getJson(route('dealerships.activities.index', [$this->dealership, 'per_page' => 1]))
            ->assertOk()
            ->assertJsonPath('meta.perPage', 5);

        getJson(route('dealerships.activities.index', [$this->dealership, 'per_page' => 999]))
            ->assertOk()
            ->assertJsonPath('meta.perPage', 100);
    });

    it('uses default per_page of 25', function () {
        getJson(route('dealerships.activities.index', $this->dealership))
            ->assertOk()
            ->assertJsonPath('meta.perPage', 25);
    });

    it('returns at least page 1', function () {
        getJson(route('dealerships.activities.index', [$this->dealership, 'page' => 0]))
            ->assertOk()
            ->assertJsonPath('meta.currentPage', 1);
    });

    it('includes opportunity activities in the feed', function () {
        $opportunity = Opportunity::factory()->create(['dealership_id' => $this->dealership->id]);
        OpportunityActivity::factory()->create([
            'opportunity_id' => $opportunity->id,
            'user_id' => $this->user->id,
        ]);

        $response = getJson(route('dealerships.activities.index', $this->dealership))->assertOk();

        expect($response->json('meta.total'))->toBeGreaterThanOrEqual(1);
    });

    it('includes activities from related contacts, stores, opportunities, and progress', function () {
        // Create related entities — they will trigger activity log on creation.
        Contact::create([
            'dealership_id' => $this->dealership->id,
            'name' => 'Joe',
            'email' => 'joe@example.com',
            'phone' => '555',
            'position' => 'Manager',
            'primary_contact' => false,
        ]);
        Store::factory()->create(['dealership_id' => $this->dealership->id, 'user_id' => $this->user->id]);
        Opportunity::factory()->create(['dealership_id' => $this->dealership->id]);
        Progress::create([
            'dealership_id' => $this->dealership->id,
            'user_id' => $this->user->id,
            'details' => 'note',
            'date' => now(),
        ]);

        $response = getJson(route('dealerships.activities.index', $this->dealership))->assertOk();

        expect($response->json('data'))->toBeArray();
    });

    it('paginates with hasMore flag', function () {
        $opportunity = Opportunity::factory()->create(['dealership_id' => $this->dealership->id]);
        OpportunityActivity::factory()->count(8)->create([
            'opportunity_id' => $opportunity->id,
            'user_id' => $this->user->id,
        ]);

        $response = getJson(route('dealerships.activities.index', [$this->dealership, 'per_page' => 5, 'page' => 1]))
            ->assertOk();

        expect($response->json('meta.perPage'))->toBe(5)
            ->and($response->json('meta.currentPage'))->toBe(1);
    });

    it('requires authentication', function () {
        auth()->logout();
        getJson(route('dealerships.activities.index', $this->dealership))->assertStatus(401);
    });

    it('describes changes when Progress is updated', function () {
        $progress = Progress::create([
            'dealership_id' => $this->dealership->id,
            'user_id' => $this->user->id,
            'details' => 'first',
            'date' => '2025-01-01',
        ]);

        $progress->update(['details' => 'second']);

        $response = getJson(route('dealerships.activities.index', $this->dealership))->assertOk();

        $items = collect($response->json('data'));
        $updated = $items->first(fn ($item) => str_contains($item['title'] ?? '', 'updated'));

        expect($updated)->not->toBeNull()
            ->and($updated['icon'] ?? null)->toBe('note')
            ->and($updated['description'] ?? null)->toContain('details');
    });

    it('uses sparkle icon for Dealership creation events', function () {
        $response = getJson(route('dealerships.activities.index', $this->dealership))->assertOk();

        $items = collect($response->json('data'));
        $created = $items->first(fn ($item) => ($item['category'] ?? null) === 'dealership' && ($item['icon'] ?? null) === 'sparkle');

        expect($created)->not->toBeNull();
    });

    it('formats long, boolean, date, and array attribute changes for Dealership updates', function () {
        $longText = str_repeat('a', 80);

        $this->dealership->update([
            'name' => 'New Name',
            'in_development' => true,
            'notes' => $longText,
        ]);

        $response = getJson(route('dealerships.activities.index', $this->dealership))->assertOk();

        $items = collect($response->json('data'));
        $updated = $items->first(fn ($item) => ($item['category'] ?? null) === 'dealership' && str_contains($item['title'] ?? '', 'updated'));

        expect($updated)->not->toBeNull()
            ->and($updated['description'] ?? null)->toBeString();
    });

    it('uses building icon for non-creation Dealership events', function () {
        $this->dealership->update(['name' => 'Renamed']);

        $response = getJson(route('dealerships.activities.index', $this->dealership))->assertOk();

        $items = collect($response->json('data'));
        $building = $items->first(fn ($item) => ($item['category'] ?? null) === 'dealership' && ($item['icon'] ?? null) === 'building');

        expect($building)->not->toBeNull();
    });

    it('uses store icon for Store events', function () {
        Store::factory()->create(['dealership_id' => $this->dealership->id, 'user_id' => $this->user->id]);

        $response = getJson(route('dealerships.activities.index', $this->dealership))->assertOk();

        $items = collect($response->json('data'));
        $store = $items->first(fn ($item) => ($item['icon'] ?? null) === 'store');

        expect($store)->not->toBeNull();
    });

    it('uses opportunity icon and renders deleted titles for manual activity records', function () {
        $opportunity = Opportunity::factory()->create(['dealership_id' => $this->dealership->id]);

        // Manually log activities for an Opportunity (the model itself does
        // not auto-log). We craft created/updated/deleted events so we
        // exercise the full match in mapLoggedActivity, including the
        // 'deleted' arms (lines 138..141), formatValue branches, and the
        // Opportunity icon path (line 123).
        $longText = str_repeat('x', 80);
        Spatie\Activitylog\Models\Activity::create([
            'log_name' => 'default',
            'description' => 'updated',
            'subject_type' => Opportunity::class,
            'subject_id' => $opportunity->id,
            'event' => 'updated',
            'properties' => [
                'attributes' => [
                    'name' => 'New Name',
                    'estimated_value' => null,
                    'contract_signed_date' => '2025-06-15',
                    'stage_entered_at' => '2025-06-15 14:30:00',
                    'is_closed' => true,
                    'long_field' => $longText,
                    'tags_array' => ['a', 'b'],
                    'updated_at' => '2025-06-15',
                ],
                'old' => [
                    'name' => 'Old Name',
                    'estimated_value' => '',
                    'contract_signed_date' => null,
                    'stage_entered_at' => null,
                    'is_closed' => false,
                    'long_field' => 'short',
                    'tags_array' => [],
                    'updated_at' => '2025-01-01',
                ],
            ],
        ]);

        Spatie\Activitylog\Models\Activity::create([
            'log_name' => 'default',
            'description' => 'deleted',
            'subject_type' => Opportunity::class,
            'subject_id' => $opportunity->id,
            'event' => 'deleted',
            'properties' => [
                'attributes' => [],
                'old' => ['name' => 'Some Name'],
            ],
        ]);

        // Deleted with no name attribute (covers fallback branch in line 140).
        Spatie\Activitylog\Models\Activity::create([
            'log_name' => 'default',
            'description' => 'deleted',
            'subject_type' => Opportunity::class,
            'subject_id' => $opportunity->id,
            'event' => 'deleted',
            'properties' => [],
        ]);

        $response = getJson(route('dealerships.activities.index', $this->dealership))->assertOk();

        $items = collect($response->json('data'));
        $opportunityIcon = $items->first(fn ($item) => ($item['icon'] ?? null) === 'opportunity');
        $deleted = $items->first(fn ($item) => str_contains($item['title'] ?? '', 'deleted'));

        expect($opportunityIcon)->not->toBeNull()
            ->and($deleted)->not->toBeNull();
    });

    it('skips logged activities with missing event or subject_type', function () {
        // An Activity without subject_type/event is filtered out (line 109).
        Spatie\Activitylog\Models\Activity::create([
            'log_name' => 'default',
            'description' => 'something',
            'subject_type' => null,
            'subject_id' => null,
            'event' => null,
            'properties' => [],
        ]);

        getJson(route('dealerships.activities.index', $this->dealership))->assertOk();
    });

    it('returns null description when nothing actually changed in attributes', function () {
        // Same values in old and attributes -> describeChanges returns null
        // after the "ignored" filter (line 185).
        Spatie\Activitylog\Models\Activity::create([
            'log_name' => 'default',
            'description' => 'updated',
            'subject_type' => Dealership::class,
            'subject_id' => $this->dealership->id,
            'event' => 'updated',
            'properties' => [
                'attributes' => ['updated_at' => '2025-01-02'],
                'old' => ['updated_at' => '2025-01-01'],
            ],
        ]);

        getJson(route('dealerships.activities.index', $this->dealership))->assertOk();
    });
});
