<?php

declare(strict_types=1);

use App\Enum\ReminderFrequency;
use App\Models\DealerEmail;
use App\Models\User;

beforeEach(function (): void {
    $this->actingAs(User::factory()->create());
});

/**
 * The command finds DealerEmails with frequency > 0 and a null
 * next_send_date and recalculates next_send_date as last_sent + frequency
 * days.
 */
describe('update:send', function (): void {
    it('sets next_send_date for weekly emails with a null next_send_date', function (): void {
        $email = DealerEmail::factory()->active()->create([
            'frequency' => ReminderFrequency::Weekly,
            'last_sent' => '2026-05-01',
            'next_send_date' => null,
        ]);

        $this->artisan('update:send')->assertExitCode(0);

        $email->refresh();

        expect($email->next_send_date->format('Y-m-d'))->toBe('2026-05-08');
    });

    it('skips emails with a null last_sent', function (): void {
        $email = DealerEmail::factory()->active()->create([
            'frequency' => ReminderFrequency::Weekly,
            'last_sent' => null,
            'next_send_date' => null,
        ]);

        $this->artisan('update:send')->assertExitCode(0);

        expect($email->fresh()->next_send_date)->toBeNull();
    });

    it('does not touch emails whose frequency is zero or negative', function (): void {
        $immediate = DealerEmail::factory()->active()->create([
            'frequency' => ReminderFrequency::Immediate,
            'last_sent' => '2026-05-01',
            'next_send_date' => null,
        ]);

        $once = DealerEmail::factory()->active()->create([
            'frequency' => ReminderFrequency::Once,
            'last_sent' => '2026-05-01',
            'next_send_date' => null,
        ]);

        $this->artisan('update:send')->assertExitCode(0);

        expect($immediate->fresh()->next_send_date)->toBeNull()
            ->and($once->fresh()->next_send_date)->toBeNull();
    });

    it('does not touch emails whose next_send_date is already set', function (): void {
        $email = DealerEmail::factory()->active()->create([
            'frequency' => ReminderFrequency::Weekly,
            'last_sent' => '2026-05-01',
            'next_send_date' => '2026-05-15',
        ]);

        $this->artisan('update:send')->assertExitCode(0);

        expect($email->fresh()->next_send_date->format('Y-m-d'))->toBe('2026-05-15');
    });

    it('updates multiple eligible emails in one run', function (): void {
        $weekly = DealerEmail::factory()->active()->create([
            'frequency' => ReminderFrequency::Weekly,
            'last_sent' => '2026-05-01',
            'next_send_date' => null,
        ]);

        $monthly = DealerEmail::factory()->active()->create([
            'frequency' => ReminderFrequency::Monthly,
            'last_sent' => '2026-04-01',
            'next_send_date' => null,
        ]);

        $this->artisan('update:send')->assertExitCode(0);

        expect($weekly->fresh()->next_send_date->format('Y-m-d'))->toBe('2026-05-08')
            ->and($monthly->fresh()->next_send_date->format('Y-m-d'))->toBe('2026-05-01');
    });
});
