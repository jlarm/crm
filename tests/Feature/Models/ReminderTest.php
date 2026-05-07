<?php

declare(strict_types=1);

use App\Enum\ReminderFrequency;
use App\Models\Reminder;
use App\Models\User;

describe('Reminder model', function (): void {
    it('can be created and exposes its casted attributes', function (): void {
        $user = User::factory()->create();

        $reminder = Reminder::create([
            'user_id' => $user->id,
            'dev_rel' => true,
            'title' => 'Follow up with dealer',
            'message' => 'Send the latest brochure',
            'start_date' => '2025-01-01',
            'sending_frequency' => ReminderFrequency::Weekly->value,
            'pause' => false,
        ]);

        expect($reminder->title)->toBe('Follow up with dealer')
            ->and($reminder->dev_rel)->toBeTrue()
            ->and($reminder->sending_frequency)->toBe(ReminderFrequency::Weekly)
            ->and($reminder->start_date->format('Y-m-d'))->toBe('2025-01-01');
    });

    it('belongs to a user', function (): void {
        $user = User::factory()->create();
        $reminder = Reminder::create([
            'user_id' => $user->id,
            'dev_rel' => false,
            'title' => 'Title',
            'message' => 'Body',
            'start_date' => '2025-01-01',
            'sending_frequency' => ReminderFrequency::Daily->value,
        ]);

        expect($reminder->user)->not->toBeNull()
            ->and($reminder->user->is($user))->toBeTrue();
    });

    it('assigns the authenticated user id when none is provided on creation', function (): void {
        $user = User::factory()->create();
        $this->actingAs($user);

        $reminder = Reminder::create([
            'dev_rel' => false,
            'title' => 'Auto user',
            'message' => 'auto',
            'start_date' => '2025-01-01',
            'sending_frequency' => ReminderFrequency::Daily->value,
        ]);

        expect($reminder->user_id)->toBe($user->id);
    });

    it('logs activity on create', function (): void {
        $user = User::factory()->create();
        $reminder = Reminder::create([
            'user_id' => $user->id,
            'dev_rel' => false,
            'title' => 'Logged',
            'message' => 'logged',
            'start_date' => '2025-01-01',
            'sending_frequency' => ReminderFrequency::Daily->value,
        ]);

        expect($reminder->activities()->count())->toBeGreaterThan(0);
    });
});
