<?php

declare(strict_types=1);

use App\Models\Dealership;
use App\Models\Progress;
use App\Models\Reminder;
use App\Models\Task;
use App\Models\User;
use Spatie\Activitylog\Models\Activity;

describe('User relationships', function (): void {
    it('belongs to many dealerships', function (): void {
        $user = User::factory()->create();
        $dealership = Dealership::factory()->create(['user_id' => $user->id]);
        $user->dealerships()->attach($dealership->id);

        expect($user->dealerships)->toHaveCount(1)
            ->and($user->dealerships->first())->toBeInstanceOf(Dealership::class);
    });

    it('has many progress entries', function (): void {
        $user = User::factory()->create();
        $dealership = Dealership::factory()->create(['user_id' => $user->id]);

        Progress::create([
            'dealership_id' => $dealership->id,
            'user_id' => $user->id,
            'details' => 'Test',
            'date' => now(),
        ]);

        expect($user->progresses)->toHaveCount(1)
            ->and($user->progresses->first())->toBeInstanceOf(Progress::class);
    });

    it('has many reminders', function (): void {
        $user = User::factory()->create();

        Reminder::create([
            'user_id' => $user->id,
            'dev_rel' => false,
            'title' => 'Hi',
            'message' => 'Hi',
            'start_date' => '2025-01-01',
            'sending_frequency' => App\Enum\ReminderFrequency::Weekly->value,
        ]);

        expect($user->reminders)->toHaveCount(1)
            ->and($user->reminders->first())->toBeInstanceOf(Reminder::class);
    });

    it('has many tasks', function (): void {
        $user = User::factory()->create();
        Task::factory()->count(2)->create([
            'user_id' => $user->id,
            'created_by_user_id' => $user->id,
        ]);

        expect($user->tasks)->toHaveCount(2)
            ->and($user->tasks->first())->toBeInstanceOf(Task::class);
    });

    it('has many dealer emails', function (): void {
        $user = User::factory()->create();
        $this->actingAs($user);

        App\Models\DealerEmail::factory()->count(2)->create(['user_id' => $user->id]);

        expect($user->dealerEmails)->toHaveCount(2);
    });

    it('has many sent emails', function (): void {
        $user = User::factory()->create();
        App\Models\SentEmail::factory()->count(2)->create(['user_id' => $user->id]);

        expect($user->sentEmails)->toHaveCount(2);
    });

    it('exposes morph many activities', function (): void {
        $user = User::factory()->create();

        // Manually insert an activity record where the user is the causer
        // (causer != subject, so the morphMany relation on User retrieves it).
        Activity::create([
            'log_name' => 'default',
            'description' => 'demo',
            'causer_type' => User::class,
            'causer_id' => $user->id,
        ]);

        expect($user->activities()->count())->toBe(1)
            ->and($user->activities()->first())->toBeInstanceOf(Activity::class);
    });
});
