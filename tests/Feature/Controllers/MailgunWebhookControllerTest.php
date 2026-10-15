<?php

declare(strict_types=1);

use App\Models\EmailTrackingEvent;
use App\Models\SentEmail;
use Illuminate\Support\Facades\URL;

use function Pest\Laravel\get;

beforeEach(function () {
    config()->set('services.mailgun.webhook_signing_key', 'webhook-key');
});

describe('MailgunWebhookController handleEvent', function () {
    it('records a tracking event for a known sent email', function () {
        $sentEmail = SentEmail::factory()->create();

        postMailgunWebhook([
            'event-data' => [
                'event' => 'delivered',
                'recipient' => $sentEmail->recipient,
                'timestamp' => now()->timestamp,
                'message' => [
                    'headers' => [
                        'message-id' => $sentEmail->message_id,
                    ],
                ],
            ],
        ])->assertOk();

        $this->assertDatabaseHas('email_tracking_events', [
            'sent_email_id' => $sentEmail->id,
            'event_type' => EmailTrackingEvent::EVENT_DELIVERED,
        ]);
    });

    it('returns 400 when event-data is missing', function () {
        postMailgunWebhook([])->assertStatus(400);
    });

    it('returns 401 when signature verification fails', function () {
        config()->set('services.mailgun.webhook_signing_key', 'secret-key');

        postMailgunWebhook([
            'event-data' => ['event' => 'delivered'],
            'signature' => [
                'timestamp' => '123',
                'token' => 'abc',
                'signature' => 'wrong',
            ],
        ])->assertStatus(401);
    });

    it('returns 401 when no signing key is configured', function () {
        config()->set('services.mailgun.webhook_signing_key', null);
        $sentEmail = SentEmail::factory()->create();

        postMailgunWebhook([
            'event-data' => [
                'event' => 'complained',
                'recipient' => $sentEmail->recipient,
                'timestamp' => now()->timestamp,
                'message' => ['headers' => ['message-id' => $sentEmail->message_id]],
            ],
        ])->assertStatus(401);

        expect(EmailTrackingEvent::count())->toBe(0);
    });

    it('returns 401 for a correctly signed but stale request', function () {
        $timestamp = (string) now()->subHour()->timestamp;
        $token = 'old-token';

        postMailgunWebhook([
            'signature' => [
                'timestamp' => $timestamp,
                'token' => $token,
                'signature' => hash_hmac('sha256', $timestamp.$token, 'webhook-key'),
            ],
            'event-data' => ['event' => 'delivered'],
        ])->assertStatus(401);
    });

    it('returns 401 when signature is not an array', function () {
        config()->set('services.mailgun.webhook_signing_key', 'secret-key');

        postMailgunWebhook([
            'event-data' => ['event' => 'delivered'],
            'signature' => 'not-an-array',
        ])->assertStatus(401);
    });

    it('returns 500 when an exception is thrown processing an event', function () {
        $sentEmail = SentEmail::factory()->create();

        // A non-numeric timestamp string makes Carbon::createFromTimestamp throw,
        // exercising the outer catch in handleEvent.
        Illuminate\Support\Facades\Log::shouldReceive('error')->atLeast()->once();

        postMailgunWebhook([
            'event-data' => [
                'event' => 'delivered',
                'recipient' => $sentEmail->recipient,
                'timestamp' => 'not-a-timestamp',
                'message' => ['headers' => ['message-id' => $sentEmail->message_id]],
            ],
        ])->assertStatus(500);
    });

    it('logs and skips events missing required fields', function () {
        Illuminate\Support\Facades\Log::spy();

        postMailgunWebhook([
            'event-data' => [
                // missing event, recipient, message-id
                'timestamp' => now()->timestamp,
            ],
        ])->assertOk();

        Illuminate\Support\Facades\Log::shouldHaveReceived('warning')
            ->withArgs(fn ($msg) => str_contains($msg, 'Missing required data'));
    });

    it('reuses a temporary tracking id when the real Mailgun id arrives later', function () {
        $sentEmail = SentEmail::factory()->create([
            'recipient' => 'temp@example.com',
            'message_id' => 'laravel-temp-id-1',
            'tracking_data' => ['temporary_id' => true],
        ]);

        postMailgunWebhook([
            'event-data' => [
                'event' => 'delivered',
                'recipient' => 'temp@example.com',
                'timestamp' => now()->timestamp,
                'message' => ['headers' => ['message-id' => 'real-mailgun-id']],
            ],
        ])->assertOk();

        $sentEmail->refresh();
        expect($sentEmail->tracking_data['temporary_id'])->toBeFalse()
            ->and($sentEmail->tracking_data['mailgun_message_id'])->toBe('real-mailgun-id')
            ->and($sentEmail->message_id)->toBe('laravel-temp-id-1'); // preserved
    });

    it('accepts a valid signature', function () {
        config()->set('services.mailgun.webhook_signing_key', 'secret-key');

        $timestamp = (string) now()->timestamp;
        $token = 'abc';
        $expected = hash_hmac('sha256', $timestamp.$token, 'secret-key');

        $sentEmail = SentEmail::factory()->create();

        postMailgunWebhook([
            'signature' => [
                'timestamp' => $timestamp,
                'token' => $token,
                'signature' => $expected,
            ],
            'event-data' => [
                'event' => 'opened',
                'recipient' => $sentEmail->recipient,
                'timestamp' => now()->timestamp,
                'message' => ['headers' => ['message-id' => $sentEmail->message_id]],
            ],
        ])->assertOk();

        $this->assertDatabaseHas('email_tracking_events', [
            'sent_email_id' => $sentEmail->id,
            'event_type' => EmailTrackingEvent::EVENT_OPENED,
        ]);
    });

    it('still returns 200 when no matching sent email is found', function () {
        postMailgunWebhook([
            'event-data' => [
                'event' => 'delivered',
                'recipient' => 'nobody@example.com',
                'timestamp' => now()->timestamp,
                'message' => ['headers' => ['message-id' => 'unknown-id']],
            ],
        ])->assertOk();
    });

    it('ignores unmapped event types', function () {
        $sentEmail = SentEmail::factory()->create();

        postMailgunWebhook([
            'event-data' => [
                'event' => 'rejected',
                'recipient' => $sentEmail->recipient,
                'timestamp' => now()->timestamp,
                'message' => ['headers' => ['message-id' => $sentEmail->message_id]],
            ],
        ])->assertOk();

        expect(EmailTrackingEvent::count())->toBe(0);
    });

    it('maps various event names to internal event types', function () {
        $cases = [
            'opened' => EmailTrackingEvent::EVENT_OPENED,
            'clicked' => EmailTrackingEvent::EVENT_CLICKED,
            'permanent_fail' => EmailTrackingEvent::EVENT_BOUNCED,
            'failed' => EmailTrackingEvent::EVENT_BOUNCED,
            'complained' => EmailTrackingEvent::EVENT_COMPLAINED,
            'unsubscribed' => EmailTrackingEvent::EVENT_UNSUBSCRIBED,
        ];

        foreach ($cases as $mailgunEvent => $internal) {
            $sentEmail = SentEmail::factory()->create();

            postMailgunWebhook([
                'event-data' => [
                    'event' => $mailgunEvent,
                    'recipient' => $sentEmail->recipient,
                    'timestamp' => now()->timestamp,
                    'message' => ['headers' => ['message-id' => $sentEmail->message_id]],
                ],
            ])->assertOk();

            $this->assertDatabaseHas('email_tracking_events', [
                'sent_email_id' => $sentEmail->id,
                'event_type' => $internal,
            ]);
        }
    });
});

describe('MailgunWebhookController trackOpen', function () {
    it('returns a tracking pixel and records an open event', function () {
        $sentEmail = SentEmail::factory()->create();

        $response = get(route('mailgun.open-track', ['message_id' => $sentEmail->message_id]));

        $response->assertOk();
        expect($response->headers->get('Content-Type'))->toBe('image/png');

        $this->assertDatabaseHas('email_tracking_events', [
            'sent_email_id' => $sentEmail->id,
            'message_id' => $sentEmail->message_id,
            'event_type' => EmailTrackingEvent::EVENT_OPENED,
        ]);
    });

    it('returns the pixel even for unknown message ids', function () {
        $response = get(route('mailgun.open-track', ['message_id' => 'nope']));

        $response->assertOk();
        expect($response->headers->get('Content-Type'))->toBe('image/png');
    });

    it('still returns the pixel when an exception is thrown during tracking', function () {
        $sentEmail = SentEmail::factory()->create();

        // Force EmailTrackingEvent::firstOrCreate to throw to enter trackOpen catch.
        EmailTrackingEvent::saving(function (): never {
            throw new RuntimeException('boom');
        });

        Illuminate\Support\Facades\Log::shouldReceive('error')->atLeast()->once();
        Illuminate\Support\Facades\Log::shouldReceive('info')->andReturnNull();
        Illuminate\Support\Facades\Log::shouldReceive('warning')->andReturnNull();

        $response = get(route('mailgun.open-track', ['message_id' => $sentEmail->message_id]));

        $response->assertOk();
        expect($response->headers->get('Content-Type'))->toBe('image/png');
    });
});

describe('MailgunWebhookController trackClick', function () {
    it('redirects and records a click event', function () {
        $sentEmail = SentEmail::factory()->create();

        get(clickTrackingUrl($sentEmail->message_id, 'https://example.com/path'))
            ->assertRedirect('https://example.com/path');

        $this->assertDatabaseHas('email_tracking_events', [
            'sent_email_id' => $sentEmail->id,
            'event_type' => EmailTrackingEvent::EVENT_CLICKED,
            'url' => 'https://example.com/path',
        ]);
    });

    it('returns 400 when no url is provided', function () {
        get(URL::signedRoute('mailgun.click-track', ['message_id' => 'whatever']))
            ->assertStatus(400);
    });

    it('refuses an unsigned link instead of redirecting', function () {
        get(route('mailgun.click-track', ['message_id' => 'whatever']).'?url='.urlencode('https://evil.example/login'))
            ->assertForbidden();
    });

    it('refuses a signed link whose url was swapped', function () {
        $signed = clickTrackingUrl('msg-1', 'https://example.com/path');
        $tampered = str_replace(urlencode(urlencode('https://example.com/path')), urlencode(urlencode('https://evil.example/login')), $signed);

        get($tampered)->assertForbidden();
    });

    it('redirects without recording when sent email is unknown', function () {
        get(clickTrackingUrl('unknown', 'https://example.com'))
            ->assertRedirect('https://example.com');

        expect(EmailTrackingEvent::count())->toBe(0);
    });

    it('still redirects to the original url when an exception occurs during tracking', function () {
        $sentEmail = SentEmail::factory()->create();

        // Force EmailTrackingEvent::create to throw to enter the trackClick catch.
        EmailTrackingEvent::saving(function (): never {
            throw new RuntimeException('boom');
        });

        Illuminate\Support\Facades\Log::shouldReceive('error')->atLeast()->once();

        get(clickTrackingUrl($sentEmail->message_id, 'https://example.com/redirect'))
            ->assertRedirect('https://example.com/redirect');
    });

});

/**
 * A click-tracking link as EmailTrackingService writes it into an email.
 */
function clickTrackingUrl(string $messageId, string $url): string
{
    $html = (new App\Services\EmailTrackingService)->wrapLinksWithTracking('<a href="'.$url.'">x</a>', $messageId);

    return Illuminate\Support\Str::between($html, 'href="', '"');
}
