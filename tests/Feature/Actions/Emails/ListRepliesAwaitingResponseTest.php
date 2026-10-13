<?php

declare(strict_types=1);

use App\Actions\Emails\ListRepliesAwaitingResponse;
use App\Models\Dealership;
use App\Models\DealershipEmail;
use App\Models\User;

beforeEach(function () {
    $this->rep = User::factory()->create();
    $this->dealership = Dealership::factory()->create(['user_id' => $this->rep->id, 'name' => 'Smith Ford']);
});

function emailThread(User $rep, Dealership $dealership): DealershipEmail
{
    return DealershipEmail::factory()->create(['dealership_id' => $dealership->id, 'user_id' => $rep->id, 'created_at' => now()->subDays(3)]);
}

function replyTo(DealershipEmail $thread, array $attributes = []): DealershipEmail
{
    return DealershipEmail::factory()->inbound()->create([
        'dealership_id' => $thread->dealership_id,
        'user_id' => $thread->user_id,
        'parent_id' => $thread->id,
        ...$attributes,
    ]);
}

it('lists threads where the contact wrote last, newest first', function () {
    $older = replyTo(emailThread($this->rep, $this->dealership), ['subject' => 'Re: Older', 'created_at' => now()->subDays(2)]);
    $newer = replyTo(emailThread($this->rep, $this->dealership), ['subject' => 'Re: Newer', 'created_at' => now()->subDay(), 'read_at' => now()]);

    $result = (new ListRepliesAwaitingResponse)($this->rep);

    expect($result['total'])->toBe(2)
        ->and(array_column($result['items'], 'subject'))->toBe(['Re: Newer', 'Re: Older'])
        ->and($result['items'][0]['unread'])->toBeFalse()
        ->and($result['items'][1]['unread'])->toBeTrue()
        ->and($result['items'][1]['threadId'])->toBe($older->parent_id)
        ->and($result['items'][1]['dealership'])->toBe(['id' => $this->dealership->id, 'name' => 'Smith Ford']);
});

it('leaves out threads the rep has already answered', function () {
    $thread = emailThread($this->rep, $this->dealership);
    replyTo($thread, ['created_at' => now()->subDays(2)]);
    DealershipEmail::factory()->create(['dealership_id' => $this->dealership->id, 'user_id' => $this->rep->id, 'parent_id' => $thread->id]);

    expect((new ListRepliesAwaitingResponse)($this->rep))->toBe(['total' => 0, 'items' => []]);
});

it('shows only the latest of several replies in a thread', function () {
    $thread = emailThread($this->rep, $this->dealership);
    replyTo($thread, ['subject' => 'Re: First', 'created_at' => now()->subDays(2)]);
    replyTo($thread, ['subject' => 'Re: Second', 'created_at' => now()->subDay()]);

    $result = (new ListRepliesAwaitingResponse)($this->rep);

    expect($result['total'])->toBe(1)
        ->and($result['items'][0]['subject'])->toBe('Re: Second');
});

it('only lists replies to the rep\'s own emails', function () {
    $someoneElse = User::factory()->create();
    replyTo(emailThread($someoneElse, $this->dealership));

    expect((new ListRepliesAwaitingResponse)($this->rep)['total'])->toBe(0);
});
