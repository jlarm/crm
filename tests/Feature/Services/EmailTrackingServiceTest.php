<?php

declare(strict_types=1);

use App\Models\SentEmail;
use App\Services\EmailTrackingService;
use Illuminate\Mail\SentMessage;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\SentMessage as SymfonySentMessage;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

function makeSymfonySentMessage(?string $messageId = null): SymfonySentMessage
{
    $email = new Email;
    $email->from(new Address('sender@example.com', 'Sender'))
        ->to(new Address('recipient@example.com'))
        ->subject('Hello')
        ->text('Body');

    if ($messageId !== null) {
        $email->getHeaders()->addIdHeader('Message-ID', $messageId);
    }

    $envelope = new Envelope(
        new Address('sender@example.com'),
        [new Address('recipient@example.com')],
    );

    return new SymfonySentMessage($email, $envelope);
}

function makeSentMessage(?string $messageId = null): SentMessage
{
    return new SentMessage(makeSymfonySentMessage($messageId));
}

describe('EmailTrackingService', function (): void {
    it('records a sent email with extracted Message-ID', function (): void {
        $service = new EmailTrackingService;
        $sent = makeSentMessage('abc123@mail.test');

        $record = $service->recordSentEmail($sent, 1, 2, 'recipient@example.com', 'Hello');

        expect($record)->toBeInstanceOf(SentEmail::class)
            ->and($record->message_id)->toBe('abc123@mail.test')
            ->and($record->recipient)->toBe('recipient@example.com')
            ->and($record->subject)->toBe('Hello')
            ->and($record->user_id)->toBe(1)
            ->and($record->dealership_id)->toBe(2);
    });

    it('falls back to a generated message id when no Message-ID header exists', function (): void {
        $service = new EmailTrackingService;
        $sent = makeSentMessage(null);

        $record = $service->recordSentEmail($sent, 1, 2, 'recipient@example.com', 'Hello');

        expect($record)->toBeInstanceOf(SentEmail::class)
            ->and($record->message_id)->toStartWith('laravel-');
    });

    it('inserts a tracking pixel before </body> when present', function (): void {
        $service = new EmailTrackingService;
        $html = '<html><body><p>Hi</p></body></html>';

        $result = $service->addTrackingPixel($html, 'msg-1');

        expect($result)->toContain('<img')
            ->and($result)->toContain('msg-1')
            ->and($result)->toEndWith('</body></html>');
    });

    it('appends a tracking pixel when no </body> tag exists', function (): void {
        $service = new EmailTrackingService;
        $result = $service->addTrackingPixel('Plain text', 'msg-2');

        expect($result)->toStartWith('Plain text')
            ->and($result)->toContain('<img');
    });

    it('wraps regular href links with a tracking URL', function (): void {
        $service = new EmailTrackingService;
        $html = '<a href="https://example.com/page">Go</a>';

        $result = $service->wrapLinksWithTracking($html, 'msg-3');

        expect($result)->toContain('track/click/msg-3')
            ->and($result)->toContain(urlencode(urlencode('https://example.com/page')));
    });

    it('does not wrap mailto, tel, or already-tracked links', function (): void {
        $service = new EmailTrackingService;
        $html = '<a href="mailto:a@b.com">A</a><a href="tel:5551234567">B</a><a href="https://crm.test/track-click/foo">C</a>';

        $result = $service->wrapLinksWithTracking($html, 'msg-4');

        expect($result)->toContain('href="mailto:a@b.com"')
            ->and($result)->toContain('href="tel:5551234567"')
            ->and($result)->toContain('track-click/foo');
    });

    it('returns null and logs when the extracted Message-ID is empty', function (): void {
        $service = new EmailTrackingService;

        // Build a real Email with an IdentificationHeader whose internal id
        // is empty after a mb_trim('<>') in extractMessageId. We achieve this
        // by reflecting the IdentificationHeader's id property to ''.
        $email = new Email;
        $email->from(new Address('sender@example.com'))
            ->to(new Address('recipient@example.com'))
            ->subject('Hello')
            ->text('Body');
        $email->getHeaders()->addIdHeader('Message-ID', 'placeholder@example.com');

        $header = $email->getHeaders()->get('Message-ID');
        $reflection = new ReflectionObject($header);
        $idsProp = $reflection->getProperty('ids');
        $idsProp->setAccessible(true);
        $idsProp->setValue($header, ['']);
        $idsAsAddressesProp = $reflection->getProperty('idsAsAddresses');
        $idsAsAddressesProp->setAccessible(true);
        $idsAsAddressesProp->setValue($header, []);

        $envelope = new Envelope(
            new Address('sender@example.com'),
            [new Address('recipient@example.com')],
        );

        $sent = new SentMessage(new SymfonySentMessage($email, $envelope));

        Log::shouldReceive('warning')->once()->with(
            'Could not extract message ID from sent email',
            Mockery::on(fn ($ctx) => ($ctx['recipient'] ?? null) === 'recipient@example.com')
        );

        $record = $service->recordSentEmail($sent, 1, 2, 'recipient@example.com', 'Hello');

        expect($record)->toBeNull();
    });

    it('falls back to a generated id via the Symfony envelope path when no Message-ID is present', function (): void {
        $service = new EmailTrackingService;

        // Email without any Message-ID header forces the envelope fallback (line 113).
        $email = new Email;
        $email->from(new Address('sender@example.com'))
            ->to(new Address('recipient@example.com'))
            ->subject('Hello')
            ->text('Body');

        $envelope = new Envelope(
            new Address('sender@example.com'),
            [new Address('recipient@example.com')],
        );

        $sent = new SentMessage(new SymfonySentMessage($email, $envelope));

        $record = $service->recordSentEmail($sent, 1, 2, 'recipient@example.com', 'Hello');

        expect($record)->toBeInstanceOf(SentEmail::class)
            ->and($record->message_id)->toStartWith('laravel-');
    });

    it('uses a fallback id when extractMessageId throws', function (): void {
        $service = new EmailTrackingService;

        // Mock a SentMessage that throws inside extractMessageId, so the inner catch
        // (lines 115-119) returns a 'fallback-...' message id.
        $sent = Mockery::mock(SentMessage::class);
        $sent->shouldReceive('getOriginalMessage')->andThrow(new RuntimeException('boom'));

        Log::shouldReceive('error')->atLeast()->once();

        $record = $service->recordSentEmail($sent, 1, 2, 'recipient@example.com', 'Hello');

        expect($record)->toBeInstanceOf(SentEmail::class)
            ->and($record->message_id)->toStartWith('fallback-');
    });

    it('returns null and logs an error when SentEmail::create throws', function (): void {
        $service = new EmailTrackingService;
        $sent = makeSentMessage('outer-catch@example.com');

        // Force the outer try/catch (lines 46-53) by passing an invalid foreign key
        // type that triggers an exception during create. Using SentEmail::saving event
        // is cleaner: mock saving on the model.
        SentEmail::saving(function (): void {
            throw new RuntimeException('create blew up');
        });

        Log::shouldReceive('error')->once()->with(
            'Failed to record sent email',
            Mockery::on(fn ($ctx) => ($ctx['recipient'] ?? null) === 'recipient@example.com'),
        );

        $record = $service->recordSentEmail($sent, 1, 2, 'recipient@example.com', 'Hello');

        expect($record)->toBeNull();

        SentEmail::flushEventListeners();
    });
});
