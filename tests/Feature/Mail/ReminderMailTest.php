<?php

declare(strict_types=1);

use App\Enum\ReminderFrequency;
use App\Mail\ReminderMail;
use App\Models\Reminder;
use App\Models\User;

describe('ReminderMail', function (): void {
    it('builds the envelope with the reminder title and a no-reply from address', function (): void {
        $user = User::factory()->create();
        $reminder = Reminder::create([
            'user_id' => $user->id,
            'dev_rel' => false,
            'title' => 'Time to follow up',
            'message' => 'Body',
            'start_date' => '2025-01-01',
            'sending_frequency' => ReminderFrequency::Daily->value,
        ]);

        $mail = new ReminderMail($reminder);
        $envelope = $mail->envelope();

        expect($envelope->subject)->toBe('Time to follow up')
            ->and($envelope->from?->address)->toBe('no-reply@crm-armp.com');
    });

    it('renders the reminder markdown view with the message', function (): void {
        $user = User::factory()->create();
        $reminder = Reminder::create([
            'user_id' => $user->id,
            'dev_rel' => false,
            'title' => 'Subject',
            'message' => 'The body of the reminder',
            'start_date' => '2025-01-01',
            'sending_frequency' => ReminderFrequency::Daily->value,
        ]);

        $mail = new ReminderMail($reminder);
        $content = $mail->content();

        expect($content->markdown)->toBe('emails.reminder')
            ->and($content->with['message'])->toBe('The body of the reminder');
    });

    it('returns no attachments', function (): void {
        $user = User::factory()->create();
        $reminder = Reminder::create([
            'user_id' => $user->id,
            'dev_rel' => false,
            'title' => 'A',
            'message' => 'B',
            'start_date' => '2025-01-01',
            'sending_frequency' => ReminderFrequency::Daily->value,
        ]);

        expect((new ReminderMail($reminder))->attachments())->toBe([]);
    });
});
