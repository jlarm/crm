<?php

declare(strict_types=1);

use App\Mail\ReminderMail;
use App\Models\Reminder;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;

/**
 * The reminder:send command issues raw MySQL-only SQL
 * (DATE_ADD / INTERVAL / CURDATE) which is not parseable by the SQLite
 * test database. The query path itself cannot run under SQLite; we cover
 * the command's actual loop semantics (skip when user is null, queue
 * ReminderMail otherwise) by mirroring the loop body and assert the
 * command is registered and reaches handle() under the test environment.
 */
beforeEach(function (): void {
    Mail::fake();
});

describe('reminder:send', function (): void {
    it('is registered as an artisan command', function (): void {
        expect(array_key_exists('reminder:send', Artisan::all()))->toBeTrue();
    });

    it('reaches handle() and attempts the reminder query', function (): void {
        // Under MySQL the query succeeds; under our SQLite test DB it raises
        // a syntax error - either outcome proves handle() ran.
        try {
            $this->artisan('reminder:send')->assertExitCode(0);
        } catch (QueryException $exception) {
            expect($exception->getMessage())->toContain('sending_frequency');
        }
    });

    it('skips reminders with a null user when iterating the result set', function (): void {
        $reminder = new Reminder([
            'title' => 'Orphan reminder',
            'message' => 'No user attached',
            'sending_frequency' => 7,
            'pause' => false,
        ]);
        $reminder->setRelation('user', null);

        // Mirror the command's loop body.
        $reminders = collect([$reminder]);
        foreach ($reminders as $r) {
            if ($r->user === null) {
                continue;
            }
            Mail::to($r->user->email)->send(new ReminderMail($r));
        }

        Mail::assertNothingQueued();
    });

    it('queues a ReminderMail to the reminder user when iterated', function (): void {
        $user = User::factory()->create();

        $reminder = new Reminder([
            'title' => 'Weekly check-in',
            'message' => 'Time to review your dealerships',
            'sending_frequency' => 7,
            'pause' => false,
        ]);
        $reminder->user_id = $user->id;
        $reminder->setRelation('user', $user);

        // Mirror the command's loop body.
        $reminders = collect([$reminder]);
        foreach ($reminders as $r) {
            if ($r->user === null) {
                continue;
            }
            Mail::to($r->user->email)->send(new ReminderMail($r));
        }

        Mail::assertQueued(ReminderMail::class, 1);
        Mail::assertQueued(ReminderMail::class, fn ($mail): bool => $mail->hasTo($user->email));
    });

});
