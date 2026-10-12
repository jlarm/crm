<?php

declare(strict_types=1);

use App\Models\Contact;
use App\Models\Dealership;
use App\Models\Progress;
use App\Models\ProgressCategory;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\delete;
use function Pest\Laravel\getJson;
use function Pest\Laravel\post;

beforeEach(function () {
    $this->user = User::factory()->create();
    actingAs($this->user);
    $this->dealership = Dealership::factory()->create(['user_id' => $this->user->id]);
    $this->phone = ProgressCategory::factory()->create(['name' => 'Phone']);
});

describe('DealershipProgressController store', function () {
    it('logs an outside activity for the dealership', function () {
        $contact = Contact::factory()->create(['dealership_id' => $this->dealership->id]);

        post(route('dealerships.progress.store', $this->dealership), [
            'progress_category_id' => $this->phone->id,
            'contact_id' => $contact->id,
            'date' => '2026-10-01',
            'details' => 'Called the GM about renewal.',
        ])->assertRedirect()->assertSessionHas('success', 'Activity logged.');

        $progress = Progress::sole();
        expect($progress->dealership_id)->toBe($this->dealership->id)
            ->and($progress->user_id)->toBe($this->user->id)
            ->and($progress->contact_id)->toBe($contact->id)
            ->and($progress->progress_category_id)->toBe($this->phone->id)
            ->and($progress->date->toDateString())->toBe('2026-10-01')
            ->and($progress->details)->toBe('Called the GM about renewal.');
    });

    it('allows logging without a contact', function () {
        post(route('dealerships.progress.store', $this->dealership), [
            'progress_category_id' => $this->phone->id,
            'date' => now()->toDateString(),
            'details' => 'Left a voicemail at the front desk.',
        ])->assertSessionHasNoErrors();

        expect(Progress::sole()->contact_id)->toBeNull();
    });

    it('requires a type, date and notes', function () {
        post(route('dealerships.progress.store', $this->dealership), [])
            ->assertSessionHasErrors(['progress_category_id', 'date', 'details']);

        expect(Progress::count())->toBe(0);
    });

    it('rejects a contact from another dealership', function () {
        $otherContact = Contact::factory()->create();

        post(route('dealerships.progress.store', $this->dealership), [
            'progress_category_id' => $this->phone->id,
            'contact_id' => $otherContact->id,
            'date' => now()->toDateString(),
            'details' => 'Called.',
        ])->assertSessionHasErrors(['contact_id']);
    });

    it('rejects a date in the future', function () {
        $this->freezeTime();

        post(route('dealerships.progress.store', $this->dealership), [
            'progress_category_id' => $this->phone->id,
            'date' => now()->addDay()->toDateString(),
            'details' => 'Called.',
        ])->assertSessionHasErrors(['date']);
    });

    it('redirects guests to login', function () {
        auth()->logout();

        post(route('dealerships.progress.store', $this->dealership), [])->assertRedirect(route('login'));
    });
});

describe('DealershipProgressController destroy', function () {
    it('deletes a logged activity', function () {
        $progress = Progress::factory()->create(['dealership_id' => $this->dealership->id]);

        delete(route('dealerships.progress.destroy', [$this->dealership, $progress]))
            ->assertRedirect();

        expect(Progress::find($progress->id))->toBeNull();
    });

    it('returns 404 for an activity from another dealership', function () {
        $progress = Progress::factory()->create();

        delete(route('dealerships.progress.destroy', [$this->dealership, $progress]))
            ->assertNotFound();

        expect(Progress::find($progress->id))->not->toBeNull();
    });
});

describe('logged activities in the dealership feed', function () {
    it('shows a logged call with its contact, notes and author', function () {
        $contact = Contact::factory()->create(['dealership_id' => $this->dealership->id, 'name' => 'Pat Lee']);
        $progress = Progress::factory()->create([
            'dealership_id' => $this->dealership->id,
            'user_id' => $this->user->id,
            'contact_id' => $contact->id,
            'progress_category_id' => $this->phone->id,
            'date' => now()->toDateString(),
            'details' => 'Discussed renewal.',
        ]);

        $items = collect(getJson(route('dealerships.activities.index', $this->dealership))->assertOk()->json('data'));

        expect($items->firstWhere('id', 'progress_'.$progress->id))->toMatchArray([
            'icon' => 'call',
            'title' => 'Phone call with Pat Lee',
            'description' => 'Discussed renewal.',
            'actor' => ['id' => $this->user->id, 'name' => $this->user->name],
            'dateOnly' => false,
            'progressId' => $progress->id,
        ])->and($items->where('title', 'Progress note created'))->toBeEmpty();
    });

    it('shows a back-dated activity on its date without a time', function () {
        $this->freezeTime();
        $progress = Progress::factory()->create([
            'dealership_id' => $this->dealership->id,
            'date' => now()->subDays(3)->toDateString(),
        ]);

        $item = collect(getJson(route('dealerships.activities.index', $this->dealership))->json('data'))
            ->firstWhere('id', 'progress_'.$progress->id);

        expect($item['dateOnly'])->toBeTrue()
            ->and($item['occurredAt'])->toBe(now()->subDays(3)->startOfDay()->toIso8601String());
    });
});
