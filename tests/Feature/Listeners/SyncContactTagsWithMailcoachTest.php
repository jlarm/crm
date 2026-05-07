<?php

declare(strict_types=1);

use App\Events\ContactTagSync;
use App\Listeners\SyncContactTagsWithMailcoach;
use App\Models\Contact;
use App\Models\Dealership;
use App\Models\Tag;
use App\Models\User;
use App\Observers\ContactObserver;
use Illuminate\Support\Facades\Log;
use Spatie\MailcoachSdk\Exceptions\ResourceNotFound;
use Spatie\MailcoachSdk\Facades\Mailcoach;
use Spatie\MailcoachSdk\Resources\EmailList;
use Spatie\MailcoachSdk\Resources\Subscriber;

beforeEach(function (): void {
    ContactObserver::$syncMailcoach = false;
    config()->set('mailcoach-sdk.api_token', 'test-token');
    config()->set('mailcoach-sdk.endpoint', 'https://example.test');
    Log::spy();
    User::factory()->create();
});

afterEach(function (): void {
    ContactObserver::$syncMailcoach = true;
});

function listMock(): EmailList
{
    /** @var EmailList $mock */
    $mock = Mockery::mock(EmailList::class);

    return $mock;
}

function subscriberMock(): Subscriber
{
    /** @var Subscriber $mock */
    $mock = Mockery::mock(Subscriber::class);

    return $mock;
}

it('returns early and logs warning when contact has no dealership', function (): void {
    $dealership = Dealership::factory()->create(['type' => 'Automotive']);
    $contact = Contact::factory()->create(['dealership_id' => $dealership->id]);
    $contact->setRelation('dealership', null);

    Mailcoach::shouldReceive('emailList')->never();

    (new SyncContactTagsWithMailcoach())->handle(new ContactTagSync($contact, 'Tester'));

    Log::shouldHaveReceived('warning')
        ->with(Mockery::pattern('/No dealership associated/'), Mockery::any());
});

it('returns early when contact email is empty', function (): void {
    $dealership = Dealership::factory()->create(['type' => 'Automotive']);
    $contact = Contact::factory()->create([
        'dealership_id' => $dealership->id,
        'email' => '',
    ]);

    $list = listMock();
    /** @var Mockery\MockInterface $list */
    $list->shouldReceive('subscriber')->never();

    Mailcoach::shouldReceive('emailList')->once()->andReturn($list);

    (new SyncContactTagsWithMailcoach())->handle(new ContactTagSync($contact, null));
});

it('syncs tags by adding and removing diffs against current Mailcoach tags', function (): void {
    $dealership = Dealership::factory()->create(['type' => 'Automotive', 'name' => 'Acme Motors']);
    $contact = Contact::factory()->create([
        'dealership_id' => $dealership->id,
        'email' => 'driver@example.com',
        'position' => 'Manager',
    ]);
    $tag = Tag::create(['name' => 'VIP']);
    $contact->tags()->attach($tag->id);

    $existingTagObject = (object) ['name' => 'StaleTag'];

    $subscriber = subscriberMock();
    $subscriber->uuid = 'sub-uuid';
    $subscriber->email = 'driver@example.com';
    $subscriber->tags = [$existingTagObject, 'Manager'];
    /** @var Mockery\MockInterface $subscriber */
    $subscriber->shouldReceive('addTags')
        ->once()
        ->with(Mockery::on(function (array $tags): bool {
            sort($tags);

            return $tags === ['Acme Motors', 'Acting User', 'VIP'];
        }))
        ->andReturnSelf();
    $subscriber->shouldReceive('removeTags')
        ->once()
        ->with(['StaleTag'])
        ->andReturnSelf();

    $list = listMock();
    /** @var Mockery\MockInterface $list */
    $list->shouldReceive('subscriber')->with('driver@example.com')->andReturn($subscriber);

    Mailcoach::shouldReceive('emailList')->once()->andReturn($list);

    (new SyncContactTagsWithMailcoach())->handle(new ContactTagSync($contact, 'Acting User'));

    Log::shouldHaveReceived('info')->with(Mockery::pattern('/Successfully synced/'), Mockery::any());
});

it('logs debug when tags are already in sync', function (): void {
    $dealership = Dealership::factory()->create(['type' => 'Automotive', 'name' => 'Sync Co']);
    $contact = Contact::factory()->create([
        'dealership_id' => $dealership->id,
        'email' => 'sync@example.com',
        'position' => 'Owner',
    ]);

    $subscriber = subscriberMock();
    $subscriber->uuid = 'uuid-1';
    $subscriber->email = 'sync@example.com';
    $subscriber->tags = ['Owner', 'Sync Co'];
    /** @var Mockery\MockInterface $subscriber */
    $subscriber->shouldReceive('addTags')->never();
    $subscriber->shouldReceive('removeTags')->never();

    $list = listMock();
    /** @var Mockery\MockInterface $list */
    $list->shouldReceive('subscriber')->andReturn($subscriber);

    Mailcoach::shouldReceive('emailList')->once()->andReturn($list);

    (new SyncContactTagsWithMailcoach())->handle(new ContactTagSync($contact, null));

    Log::shouldHaveReceived('debug')->with(Mockery::pattern('/already in sync/'), Mockery::any());
});

it('does nothing on the inner block when subscriber lookup returns null', function (): void {
    $dealership = Dealership::factory()->create(['type' => 'Automotive']);
    $contact = Contact::factory()->create([
        'dealership_id' => $dealership->id,
        'email' => 'missing@example.com',
        'position' => 'Tech',
    ]);

    $list = listMock();
    /** @var Mockery\MockInterface $list */
    $list->shouldReceive('subscriber')->with('missing@example.com')->andReturn(null);

    Mailcoach::shouldReceive('emailList')->once()->andReturn($list);

    (new SyncContactTagsWithMailcoach())->handle(new ContactTagSync($contact, null));
});

it('catches ResourceNotFound from inner add/remove operations', function (): void {
    $dealership = Dealership::factory()->create(['type' => 'Automotive', 'name' => 'Acme']);
    $contact = Contact::factory()->create([
        'dealership_id' => $dealership->id,
        'email' => 'inner-rnf@example.com',
        'position' => 'Manager',
    ]);

    $subscriber = subscriberMock();
    $subscriber->uuid = 'uuid-2';
    $subscriber->email = 'inner-rnf@example.com';
    $subscriber->tags = [];
    /** @var Mockery\MockInterface $subscriber */
    $subscriber->shouldReceive('addTags')->andThrow(new ResourceNotFound('not found'));
    $subscriber->shouldReceive('removeTags')->never();

    $list = listMock();
    /** @var Mockery\MockInterface $list */
    $list->shouldReceive('subscriber')->andReturn($subscriber);

    Mailcoach::shouldReceive('emailList')->once()->andReturn($list);

    (new SyncContactTagsWithMailcoach())->handle(new ContactTagSync($contact, null));

    Log::shouldHaveReceived('warning')->with(Mockery::pattern('/Mailcoach resource not found during tag add\/remove/'), Mockery::any());
});

it('catches generic Exception from inner add/remove operations', function (): void {
    $dealership = Dealership::factory()->create(['type' => 'Automotive', 'name' => 'Acme']);
    $contact = Contact::factory()->create([
        'dealership_id' => $dealership->id,
        'email' => 'inner-ex@example.com',
        'position' => 'Manager',
    ]);

    $subscriber = subscriberMock();
    $subscriber->uuid = 'uuid-3';
    $subscriber->email = 'inner-ex@example.com';
    $subscriber->tags = [];
    /** @var Mockery\MockInterface $subscriber */
    $subscriber->shouldReceive('addTags')->andThrow(new RuntimeException('boom'));

    $list = listMock();
    /** @var Mockery\MockInterface $list */
    $list->shouldReceive('subscriber')->andReturn($subscriber);

    Mailcoach::shouldReceive('emailList')->once()->andReturn($list);

    (new SyncContactTagsWithMailcoach())->handle(new ContactTagSync($contact, null));

    Log::shouldHaveReceived('error')->with(Mockery::pattern('/Error updating Mailcoach subscriber tags/'), Mockery::any());
});

it('catches ResourceNotFound thrown by emailList lookup at outer level', function (): void {
    $dealership = Dealership::factory()->create(['type' => 'Automotive']);
    $contact = Contact::factory()->create([
        'dealership_id' => $dealership->id,
        'email' => 'outer-rnf@example.com',
    ]);

    Mailcoach::shouldReceive('emailList')->andThrow(new ResourceNotFound('list missing'));

    (new SyncContactTagsWithMailcoach())->handle(new ContactTagSync($contact, null));

    Log::shouldHaveReceived('warning')->with(Mockery::pattern('/Mailcoach resource not found \(likely list/'), Mockery::any());
});

it('catches generic Exception thrown by emailList at outer level', function (): void {
    $dealership = Dealership::factory()->create(['type' => 'Automotive']);
    $contact = Contact::factory()->create([
        'dealership_id' => $dealership->id,
        'email' => 'outer-ex@example.com',
    ]);

    Mailcoach::shouldReceive('emailList')->andThrow(new RuntimeException('outer boom'));

    (new SyncContactTagsWithMailcoach())->handle(new ContactTagSync($contact, null));

    Log::shouldHaveReceived('error')->with(Mockery::pattern('/Error in SyncContactTagsWithMailcoach/'), Mockery::any());
});

it('skips appending acting user name when null and still syncs', function (): void {
    $dealership = Dealership::factory()->create(['type' => 'Automotive', 'name' => 'NoActor Inc']);
    $contact = Contact::factory()->create([
        'dealership_id' => $dealership->id,
        'email' => 'noactor@example.com',
        'position' => 'Driver',
    ]);

    $subscriber = subscriberMock();
    $subscriber->uuid = 'uuid-na';
    $subscriber->email = 'noactor@example.com';
    $subscriber->tags = [];
    /** @var Mockery\MockInterface $subscriber */
    $subscriber->shouldReceive('addTags')
        ->once()
        ->with(Mockery::on(function (array $tags): bool {
            sort($tags);

            return $tags === ['Driver', 'NoActor Inc'];
        }))
        ->andReturnSelf();
    $subscriber->shouldReceive('removeTags')->never();

    $list = listMock();
    /** @var Mockery\MockInterface $list */
    $list->shouldReceive('subscriber')->andReturn($subscriber);

    Mailcoach::shouldReceive('emailList')->once()->andReturn($list);

    (new SyncContactTagsWithMailcoach())->handle(new ContactTagSync($contact, null));
});

it('handles missing fresh model gracefully and still syncs base tags', function (): void {
    $dealership = Dealership::factory()->create(['type' => 'Automotive', 'name' => 'Ghost Co']);
    $contact = Contact::factory()->make([
        'dealership_id' => $dealership->id,
        'email' => 'ghost@example.com',
        'position' => 'Phantom',
    ]);
    $contact->id = 999999;
    $contact->setRelation('dealership', $dealership);

    $subscriber = subscriberMock();
    $subscriber->uuid = 'uuid-ghost';
    $subscriber->email = 'ghost@example.com';
    $subscriber->tags = [];
    /** @var Mockery\MockInterface $subscriber */
    $subscriber->shouldReceive('addTags')->once()->andReturnSelf();
    $subscriber->shouldReceive('removeTags')->never();

    $list = listMock();
    /** @var Mockery\MockInterface $list */
    $list->shouldReceive('subscriber')->andReturn($subscriber);

    Mailcoach::shouldReceive('emailList')->once()->andReturn($list);

    (new SyncContactTagsWithMailcoach())->handle(new ContactTagSync($contact, 'Actor'));

    Log::shouldHaveReceived('warning')->with(Mockery::pattern('/Failed to refresh Contact model/'), Mockery::any());
});
