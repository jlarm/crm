<?php

declare(strict_types=1);

use App\Models\Contact;
use App\Models\Dealership;
use App\Models\Opportunity;
use App\Models\User;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

    $this->admin = User::factory()->create(['name' => 'Ada Admin']);
    $this->admin->assignRole('super_admin');
});

it('is only open to super admins', function () {
    actingAs(User::factory()->create());

    get(route('activity-log.index'))->assertForbidden();
});

it('lists changes with who made them, what changed and a link to the record', function () {
    $rep = User::factory()->create(['name' => 'Rita Rep']);
    actingAs($rep);
    $dealership = Dealership::factory()->create(['user_id' => $rep->id]);
    $contact = Contact::factory()->create(['dealership_id' => $dealership->id, 'name' => 'Pat Smith', 'position' => 'GM']);
    $contact->update(['position' => 'Owner']);

    actingAs($this->admin);

    get(route('activity-log.index', ['type' => 'contact', 'event' => 'updated']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('ActivityLog/Index')
            ->has('entries.data', 1)
            ->where('entries.data.0.actor.name', 'Rita Rep')
            ->where('entries.data.0.type', 'Contact')
            ->where('entries.data.0.subject', 'Pat Smith')
            ->where('entries.data.0.url', route('dealerships.show', ['dealership' => $dealership->id, 'tab' => 'contacts']))
            ->where('entries.data.0.changes', [['field' => 'position', 'old' => 'GM', 'new' => 'Owner']])
        );
});

it('filters by user and date range', function () {
    $rep = User::factory()->create();
    $other = User::factory()->create();

    $this->travelTo('2026-09-01 10:00');
    actingAs($rep);
    Dealership::factory()->create(['user_id' => $rep->id, 'name' => 'Old Motors']);

    $this->travelTo('2026-10-01 10:00');
    Dealership::factory()->create(['user_id' => $rep->id, 'name' => 'New Motors']);
    actingAs($other);
    Dealership::factory()->create(['user_id' => $other->id, 'name' => 'Someone Else Motors']);

    actingAs($this->admin);

    get(route('activity-log.index', ['user' => $rep->id, 'type' => 'dealership', 'from' => '2026-09-15', 'to' => '2026-10-31']))
        ->assertInertia(fn ($page) => $page
            ->has('entries.data', 1)
            ->where('entries.data.0.subject', 'New Motors')
        );
});

it('keeps the name of someone whose account was deleted', function () {
    $rep = User::factory()->create(['name' => 'Gone Gary']);
    actingAs($rep);
    Dealership::factory()->create(['user_id' => $rep->id]);
    $rep->delete();

    actingAs($this->admin);

    get(route('activity-log.index', ['type' => 'dealership']))
        ->assertInertia(fn ($page) => $page->where('entries.data.0.actor.name', 'Gone Gary'));
});

it('never shows password or two-factor fields', function () {
    activity()->performedOn($this->admin)->causedBy($this->admin)->event('updated')->withProperties([
        'attributes' => ['name' => 'Ada Admin', 'password' => 'new-hash', 'two_factor_secret' => 'secret'],
        'old' => ['name' => 'Ada', 'password' => 'old-hash', 'two_factor_secret' => null],
    ])->log('User updated');

    actingAs($this->admin);

    get(route('activity-log.index', ['type' => 'user']))
        ->assertInertia(fn ($page) => $page->where('entries.data.0.changes', [['field' => 'name', 'old' => 'Ada', 'new' => 'Ada Admin']]));
});

it('rejects a date range that ends before it starts', function () {
    actingAs($this->admin);

    get(route('activity-log.index', ['from' => '2026-10-10', 'to' => '2026-10-01']))->assertSessionHasErrors('to');
});

it('records changes to opportunities', function () {
    actingAs($this->admin);
    $opportunity = Opportunity::factory()->create(['dealership_id' => Dealership::factory()->create(['user_id' => $this->admin->id])->id]);

    $opportunity->update(['next_action' => 'Send the contract']);

    $entry = Activity::where('subject_type', Opportunity::class)->where('event', 'updated')->sole();
    expect($entry->description)->toBe('Opportunity updated')
        ->and($entry->causer_id)->toBe($this->admin->id)
        ->and($entry->properties['attributes']['next_action'])->toBe('Send the contract');
});

it('lists entries for records whose model no longer exists in the app', function () {
    Activity::create([
        'log_name' => 'default',
        'description' => 'MarketingList created',
        'subject_type' => 'App\\Models\\MarketingList',
        'subject_id' => 1,
        'event' => 'created',
        'causer_type' => User::class,
        'causer_id' => $this->admin->id,
        'properties' => ['attributes' => ['name' => 'Spring promo']],
    ]);

    actingAs($this->admin);

    get(route('activity-log.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('entries.data.0.type', 'MarketingList')
            ->where('entries.data.0.subject', 'Spring promo')
            ->where('entries.data.0.url', null)
        );
});

it('shows JSON, list and HTML values as readable text', function () {
    activity()->performedOn($this->admin)->causedBy($this->admin)->event('created')->withProperties([
        'attributes' => [
            'name' => 'Ada Admin',
            'filters' => '{"states":["WY","IL"],"search":null,"primary_only":false,"direction":"asc"}',
            'body' => '<p>Hi Pat,</p><p>See our guide.</p>',
        ],
    ])->log('User created');

    actingAs($this->admin);

    get(route('activity-log.index', ['type' => 'user']))
        ->assertInertia(fn ($page) => $page->where('entries.data.0.changes', [
            ['field' => 'name', 'old' => null, 'new' => 'Ada Admin'],
            ['field' => 'filters', 'old' => null, 'new' => 'states: WY, IL · direction: asc'],
            ['field' => 'body', 'old' => null, 'new' => 'Hi Pat, See our guide.'],
        ]));
});
