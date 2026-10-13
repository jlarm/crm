<?php

declare(strict_types=1);

use App\Enum\EmailFailureReason;
use App\Enum\EmailStatus;
use App\Models\DealershipEmail;
use App\Models\DealershipEmailEvent;
use App\Models\EmailTrackingEvent;
use App\Models\SentEmail;

use function Pest\Laravel\postJson;

beforeEach(function () {
    config()->set('services.mailgun.webhook_signing_key', null);

    $this->email = DealershipEmail::factory()->create(['message_id' => 'abc@armp.test', 'status' => EmailStatus::Sent]);
});

/**
 * @param  array<string, mixed>  $overrides
 */
function dealershipEmailEvent(DealershipEmail $email, string $event, array $overrides = []): array
{
    return ['event-data' => [
        'id' => fake()->uuid(),
        'event' => $event,
        'timestamp' => now()->timestamp,
        'recipient' => $email->to_email,
        'user-variables' => ['dealership_email_id' => (string) $email->id],
        'message' => ['headers' => ['message-id' => $email->message_id]],
        ...$overrides,
    ]];
}

it('marks the email delivered', function () {
    postJson(route('mailgun.webhook'), dealershipEmailEvent($this->email, 'delivered'))->assertOk();

    $email = $this->email->fresh();
    expect($email->status)->toBe(EmailStatus::Delivered)
        ->and($email->delivered_at)->not->toBeNull()
        ->and($email->events()->sole()->event)->toBe('delivered');
});

it('records a permanent failure as a bounce with the reason', function () {
    postJson(route('mailgun.webhook'), dealershipEmailEvent($this->email, 'failed', [
        'severity' => 'permanent',
        'reason' => 'generic',
        'delivery-status' => ['code' => 552, 'message' => 'Mailbox full'],
    ]))->assertOk();

    $email = $this->email->fresh();
    expect($email->status)->toBe(EmailStatus::Bounced)
        ->and($email->status_reason)->toBe(EmailFailureReason::MailboxFull)
        ->and($email->status_detail)->toBe('552 Mailbox full')
        ->and($email->failed_at)->not->toBeNull();
});

it('records a temporary failure as delayed, then clears it once delivered', function () {
    postJson(route('mailgun.webhook'), dealershipEmailEvent($this->email, 'failed', [
        'severity' => 'temporary',
        'delivery-status' => ['code' => 421, 'message' => 'Try again later'],
    ]));

    expect($this->email->fresh()->status)->toBe(EmailStatus::Deferred);

    postJson(route('mailgun.webhook'), dealershipEmailEvent($this->email, 'delivered'));

    $email = $this->email->fresh();
    expect($email->status)->toBe(EmailStatus::Delivered)
        ->and($email->status_reason)->toBeNull();
});

it('records a spam complaint', function () {
    postJson(route('mailgun.webhook'), dealershipEmailEvent($this->email, 'complained'));

    $email = $this->email->fresh();
    expect($email->status)->toBe(EmailStatus::Complained)
        ->and($email->status_reason)->toBe(EmailFailureReason::SpamComplaint);
});

it('does not move the status backwards when events arrive out of order', function () {
    postJson(route('mailgun.webhook'), dealershipEmailEvent($this->email, 'opened'));
    postJson(route('mailgun.webhook'), dealershipEmailEvent($this->email, 'delivered'));

    $email = $this->email->fresh();
    expect($email->status)->toBe(EmailStatus::Opened)
        ->and($email->delivered_at)->not->toBeNull();
});

it('ignores a webhook Mailgun sends twice', function () {
    $payload = dealershipEmailEvent($this->email, 'delivered');

    postJson(route('mailgun.webhook'), $payload)->assertOk();
    postJson(route('mailgun.webhook'), $payload)->assertOk();

    expect(DealershipEmailEvent::count())->toBe(1);
});

it('matches by Message-ID when the user variables are missing', function () {
    $payload = dealershipEmailEvent($this->email, 'delivered');
    unset($payload['event-data']['user-variables']);
    $payload['event-data']['message']['headers']['message-id'] = '<abc@armp.test>';

    postJson(route('mailgun.webhook'), $payload)->assertOk();

    expect($this->email->fresh()->status)->toBe(EmailStatus::Delivered);
});

it('leaves legacy dealer email events on the old path', function () {
    $sentEmail = SentEmail::factory()->create();

    postJson(route('mailgun.webhook'), ['event-data' => [
        'event' => 'delivered',
        'recipient' => $sentEmail->recipient,
        'timestamp' => now()->timestamp,
        'message' => ['headers' => ['message-id' => $sentEmail->message_id]],
    ]])->assertOk();

    expect(EmailTrackingEvent::where('sent_email_id', $sentEmail->id)->count())->toBe(1)
        ->and(DealershipEmailEvent::count())->toBe(0);
});
