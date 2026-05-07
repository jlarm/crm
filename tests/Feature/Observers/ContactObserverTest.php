<?php

declare(strict_types=1);

use App\Events\ContactTagSync;
use App\Models\Contact;
use App\Models\Dealership;
use App\Models\User;
use App\Observers\ContactObserver;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Spatie\MailcoachSdk\Exceptions\ResourceNotFound;
use Spatie\MailcoachSdk\Facades\Mailcoach;
use Spatie\MailcoachSdk\Resources\EmailList;
use Spatie\MailcoachSdk\Resources\Subscriber;

function obsListMock(): EmailList
{
    /** @var EmailList $mock */
    $mock = Mockery::mock(EmailList::class);

    return $mock;
}

function obsSubscriberMock(): Subscriber
{
    /** @var Subscriber $mock */
    $mock = Mockery::mock(Subscriber::class);

    return $mock;
}

beforeEach(function (): void {
    ContactObserver::$syncMailcoach = true;
    config()->set('mailcoach-sdk.api_token', 'test-token');
    config()->set('mailcoach-sdk.endpoint', 'https://example.test');
    Log::spy();
    if (User::count() === 0) {
        User::factory()->create();
    }
});

afterEach(function (): void {
    ContactObserver::$syncMailcoach = true;
});

it('marks other dealership contacts as non-primary when a contact is created', function (): void {
    ContactObserver::$syncMailcoach = false;

    $dealership = Dealership::factory()->create(['type' => 'Automotive']);
    $existing = Contact::factory()->create([
        'dealership_id' => $dealership->id,
        'primary_contact' => true,
    ]);

    $new = Contact::factory()->create([
        'dealership_id' => $dealership->id,
        'primary_contact' => true,
    ]);

    expect($existing->fresh()->primary_contact)->toBeFalse()
        ->and($new->fresh()->primary_contact)->toBeTrue();
});

it('does not error in created when dealership is missing', function (): void {
    ContactObserver::$syncMailcoach = false;

    $contact = new Contact([
        'dealership_id' => 99999,
        'name' => 'Lonely',
        'email' => 'lonely@example.com',
    ]);
    $contact->save();

    expect($contact->fresh())->not->toBeNull();
});

it('returns early in created when syncMailcoach is disabled', function (): void {
    ContactObserver::$syncMailcoach = false;
    Event::fake([ContactTagSync::class]);
    Mailcoach::shouldReceive('emailList')->never();

    $dealership = Dealership::factory()->create(['type' => 'Automotive']);
    Contact::factory()->create(['dealership_id' => $dealership->id]);

    Event::assertNotDispatched(ContactTagSync::class);
});

it('dispatches ContactTagSync on created with acting user name', function (): void {
    Event::fake([ContactTagSync::class]);

    $list = obsListMock();
    /** @var Mockery\MockInterface $list */
    $list->shouldReceive('subscriber')->andReturn(null);
    Mailcoach::shouldReceive('emailList')->andReturn($list);
    Mailcoach::shouldReceive('createSubscriber')->andReturn(obsSubscriberMock());

    $user = User::factory()->create(['name' => 'Actor McUserface']);
    $this->actingAs($user);

    $dealership = Dealership::factory()->create(['type' => 'Automotive']);
    Contact::factory()->create([
        'dealership_id' => $dealership->id,
        'email' => 'new@example.com',
    ]);

    Event::assertDispatched(ContactTagSync::class, fn (ContactTagSync $e): bool => $e->actingUserName === 'Actor McUserface');
});

it('dispatches ContactTagSync on updated', function (): void {
    ContactObserver::$syncMailcoach = false;

    $dealership = Dealership::factory()->create(['type' => 'Automotive']);
    $contact = Contact::factory()->create([
        'dealership_id' => $dealership->id,
        'email' => 'upd@example.com',
    ]);

    Event::fake([ContactTagSync::class]);
    ContactObserver::$syncMailcoach = true;

    $list = obsListMock();
    /** @var Mockery\MockInterface $list */
    $list->shouldReceive('subscriber')->andReturn(null);
    Mailcoach::shouldReceive('emailList')->andReturn($list);
    Mailcoach::shouldReceive('createSubscriber')->andReturn(obsSubscriberMock());

    $contact->update(['name' => 'Updated Name']);

    Event::assertDispatched(ContactTagSync::class, fn (ContactTagSync $e): bool => $e->contact->id === $contact->id);
});

it('returns early on updated when syncMailcoach is disabled', function (): void {
    ContactObserver::$syncMailcoach = false;
    $dealership = Dealership::factory()->create(['type' => 'Automotive']);
    $contact = Contact::factory()->create(['dealership_id' => $dealership->id]);

    Event::fake([ContactTagSync::class]);
    Mailcoach::shouldReceive('emailList')->never();

    $contact->update(['name' => 'Whatever']);

    Event::assertNotDispatched(ContactTagSync::class);
});

it('deletes the Mailcoach subscriber on contact deletion', function (): void {
    ContactObserver::$syncMailcoach = false;
    $dealership = Dealership::factory()->create(['type' => 'Automotive']);
    $contact = Contact::factory()->create([
        'dealership_id' => $dealership->id,
        'email' => 'delete-me@example.com',
    ]);
    ContactObserver::$syncMailcoach = true;

    $subscriber = obsSubscriberMock();
    /** @var Mockery\MockInterface $subscriber */
    $subscriber->shouldReceive('delete')->once();

    $list = obsListMock();
    /** @var Mockery\MockInterface $list */
    $list->shouldReceive('subscriber')->with('delete-me@example.com')->andReturn($subscriber);

    Mailcoach::shouldReceive('emailList')->andReturn($list);

    $contact->delete();
});

it('does nothing on delete when subscriber lookup returns null', function (): void {
    ContactObserver::$syncMailcoach = false;
    $dealership = Dealership::factory()->create(['type' => 'Automotive']);
    $contact = Contact::factory()->create([
        'dealership_id' => $dealership->id,
        'email' => 'absent@example.com',
    ]);
    ContactObserver::$syncMailcoach = true;

    $list = obsListMock();
    /** @var Mockery\MockInterface $list */
    $list->shouldReceive('subscriber')->andReturn(null);

    Mailcoach::shouldReceive('emailList')->andReturn($list);

    $contact->delete();
})->throwsNoExceptions();

it('skips deletion sync when syncMailcoach is disabled', function (): void {
    ContactObserver::$syncMailcoach = false;
    $dealership = Dealership::factory()->create(['type' => 'Automotive']);
    $contact = Contact::factory()->create(['dealership_id' => $dealership->id]);

    Mailcoach::shouldReceive('emailList')->never();

    $contact->delete();
})->throwsNoExceptions();

it('skips Mailcoach create on saved when getListType is default_value', function (): void {
    Event::fake([ContactTagSync::class]);
    Mailcoach::shouldReceive('emailList')->never();
    Mailcoach::shouldReceive('createSubscriber')->never();

    // type 'Association' is not in the listType map -> 'default_value'
    $dealership = Dealership::factory()->create(['type' => 'Association']);
    Contact::factory()->create([
        'dealership_id' => $dealership->id,
        'email' => 'whatever@example.com',
    ]);

    Log::shouldHaveReceived('warning')->with(Mockery::pattern("/'default_value'/"));
});

it('skips Mailcoach sync in handleSavedEvent when email is empty', function (): void {
    Event::fake([ContactTagSync::class]);

    $list = obsListMock();
    /** @var Mockery\MockInterface $list */
    $list->shouldReceive('subscriber')->never();
    Mailcoach::shouldReceive('emailList')->andReturn($list);
    Mailcoach::shouldReceive('createSubscriber')->never();

    $dealership = Dealership::factory()->create(['type' => 'Automotive']);
    Contact::factory()->create([
        'dealership_id' => $dealership->id,
        'email' => '',
    ]);

    Log::shouldHaveReceived('warning')->with(Mockery::pattern('/Empty email/'));
});

it('updates an existing Mailcoach subscriber when found', function (): void {
    Event::fake([ContactTagSync::class]);

    $subscriber = obsSubscriberMock();
    $subscriber->uuid = 'existing-uuid';

    $list = obsListMock();
    /** @var Mockery\MockInterface $list */
    $list->shouldReceive('subscriber')->andReturn($subscriber);

    Mailcoach::shouldReceive('emailList')->andReturn($list);
    Mailcoach::shouldReceive('updateSubscriber')
        ->once()
        ->with('existing-uuid', Mockery::on(function (array $attributes): bool {
            return ($attributes['first_name'] ?? null) === 'Jane'
                && ($attributes['last_name'] ?? null) === 'Doe Smith';
        }))
        ->andReturn(obsSubscriberMock());
    Mailcoach::shouldReceive('createSubscriber')->never();

    $dealership = Dealership::factory()->create(['type' => 'Automotive']);
    Contact::factory()->create([
        'dealership_id' => $dealership->id,
        'email' => 'jane@example.com',
        'name' => 'Jane Doe Smith',
    ]);
});

it('creates a Mailcoach subscriber when none exists', function (): void {
    Event::fake([ContactTagSync::class]);

    $list = obsListMock();
    /** @var Mockery\MockInterface $list */
    $list->shouldReceive('subscriber')->andReturn(null);

    Mailcoach::shouldReceive('emailList')->andReturn($list);
    Mailcoach::shouldReceive('createSubscriber')
        ->once()
        ->andReturn(obsSubscriberMock());
    Mailcoach::shouldReceive('updateSubscriber')->never();

    $dealership = Dealership::factory()->create(['type' => 'Automotive']);
    Contact::factory()->create([
        'dealership_id' => $dealership->id,
        'email' => 'fresh@example.com',
        'name' => 'Solo',
    ]);
});

it('catches ResourceNotFound during handleSavedEvent', function (): void {
    Event::fake([ContactTagSync::class]);

    Mailcoach::shouldReceive('emailList')->andThrow(new ResourceNotFound('missing'));

    $dealership = Dealership::factory()->create(['type' => 'Automotive']);
    Contact::factory()->create([
        'dealership_id' => $dealership->id,
        'email' => 'rnf@example.com',
    ]);

    Log::shouldHaveReceived('error')->with(Mockery::pattern('/ResourceNotFound for contact/'), Mockery::any());
});

it('catches generic Exception during handleSavedEvent', function (): void {
    Event::fake([ContactTagSync::class]);

    Mailcoach::shouldReceive('emailList')->andThrow(new RuntimeException('weird'));

    $dealership = Dealership::factory()->create(['type' => 'Automotive']);
    Contact::factory()->create([
        'dealership_id' => $dealership->id,
        'email' => 'ex@example.com',
    ]);

    Log::shouldHaveReceived('error')->with(Mockery::pattern('/Exception for contact/'), Mockery::any());
});

it('warns and skips handleSavedEvent when contact has no dealership relation', function (): void {
    Event::fake([ContactTagSync::class]);

    Mailcoach::shouldReceive('emailList')->never();

    // Make a transient contact without a dealership - call observer directly
    $contact = new Contact(['name' => 'Orphan', 'email' => 'orphan@example.com']);
    $contact->id = 12345;

    (new ContactObserver())->updated($contact);

    Log::shouldHaveReceived('warning')->with(Mockery::pattern('/No dealership found for contact/'));
});
