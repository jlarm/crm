<?php

declare(strict_types=1);

use App\Enum\ReminderFrequency;
use App\Mail\DealerEmailMail;
use App\Models\Contact;
use App\Models\DealerEmail;
use App\Models\SentEmail;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

beforeEach(function (): void {
    Mail::fake();
    $this->actingAs(User::factory()->create());
});

describe('dealer:send', function (): void {
    it('sends the email to each recipient and logs a SentEmail record', function (): void {
        $email = DealerEmail::factory()->active()->weekly()->create([
            'recipients' => ['one@example.test', 'two@example.test'],
            'next_send_date' => now()->format('Y-m-d'),
            'last_sent' => null,
        ]);

        Contact::factory()->create([
            'email' => 'one@example.test',
            'name' => 'First Contact',
        ]);

        $this->artisan('dealer:send')->assertExitCode(0);

        Mail::assertSent(DealerEmailMail::class, 2);
        Mail::assertSent(DealerEmailMail::class, fn ($mail): bool => $mail->hasTo('one@example.test'));
        Mail::assertSent(DealerEmailMail::class, fn ($mail): bool => $mail->hasTo('two@example.test'));

        expect(SentEmail::count())->toBe(2)
            ->and(SentEmail::where('recipient', 'one@example.test')->exists())->toBeTrue()
            ->and(SentEmail::where('recipient', 'two@example.test')->exists())->toBeTrue();
    });

    it('updates last_sent and next_send_date based on frequency for recurring emails', function (): void {
        Carbon::setTestNow('2026-05-07');

        $email = DealerEmail::factory()->active()->weekly()->create([
            'recipients' => ['only@example.test'],
            'next_send_date' => '2026-05-07',
            'last_sent' => null,
        ]);

        $this->artisan('dealer:send')->assertExitCode(0);

        $email->refresh();

        expect($email->last_sent->format('Y-m-d'))->toBe('2026-05-07')
            ->and($email->next_send_date->format('Y-m-d'))->toBe('2026-05-14');

        Carbon::setTestNow();
    });

    it('does not send emails whose frequency is Immediate (-1)', function (): void {
        // The command's query filters to frequency > 0 OR (frequency = 0 AND
        // last_sent NULL); an Immediate (-1) email matches neither branch.
        $email = DealerEmail::factory()->active()->immediate()->create([
            'recipients' => ['imm@example.test'],
            'next_send_date' => null,
            'last_sent' => null,
            'frequency' => ReminderFrequency::Immediate,
        ]);

        $this->artisan('dealer:send')->assertExitCode(0);

        Mail::assertNothingSent();
        expect($email->fresh()->last_sent)->toBeNull();
    });

    it('sends one-time emails (frequency = Once) when last_sent is null', function (): void {
        $email = DealerEmail::factory()->active()->create([
            'recipients' => ['once@example.test'],
            'frequency' => ReminderFrequency::Once,
            'last_sent' => null,
            'next_send_date' => null,
        ]);

        $this->artisan('dealer:send')->assertExitCode(0);

        Mail::assertSent(DealerEmailMail::class, 1);
        expect(SentEmail::count())->toBe(1);
    });

    it('does not send one-time emails (frequency = Once) once last_sent is populated', function (): void {
        DealerEmail::factory()->active()->create([
            'recipients' => ['once@example.test'],
            'frequency' => ReminderFrequency::Once,
            'last_sent' => now()->subDay(),
            'next_send_date' => null,
        ]);

        $this->artisan('dealer:send')->assertExitCode(0);

        Mail::assertNothingSent();
        expect(SentEmail::count())->toBe(0);
    });

    it('does not send paused emails', function (): void {
        DealerEmail::factory()->paused()->weekly()->create([
            'recipients' => ['paused@example.test'],
            'next_send_date' => now()->format('Y-m-d'),
            'last_sent' => null,
        ]);

        $this->artisan('dealer:send')->assertExitCode(0);

        Mail::assertNothingSent();
        expect(SentEmail::count())->toBe(0);
    });

    it('does not send recurring emails when next_send_date is in the future', function (): void {
        DealerEmail::factory()->active()->weekly()->create([
            'recipients' => ['future@example.test'],
            'next_send_date' => now()->addDays(3)->format('Y-m-d'),
            'last_sent' => now()->subDays(4),
        ]);

        $this->artisan('dealer:send')->assertExitCode(0);

        Mail::assertNothingSent();
    });

    it('sends recurring emails when next_send_date is null', function (): void {
        DealerEmail::factory()->active()->weekly()->create([
            'recipients' => ['nullnext@example.test'],
            'next_send_date' => null,
            'last_sent' => null,
        ]);

        $this->artisan('dealer:send')->assertExitCode(0);

        Mail::assertSent(DealerEmailMail::class, 1);
    });

    it('handles emails with no recipients gracefully', function (): void {
        $email = DealerEmail::factory()->active()->weekly()->create([
            'recipients' => [],
            'next_send_date' => now()->format('Y-m-d'),
            'last_sent' => null,
        ]);

        $this->artisan('dealer:send')->assertExitCode(0);

        Mail::assertNothingSent();
        expect(SentEmail::count())->toBe(0);

        $email->refresh();
        expect($email->last_sent)->not->toBeNull();
    });
});
