<?php

namespace App\Mail;

use App\Models\Reminder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReminderMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Reminder $reminder) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address('no-reply@crm-armp.com'),
            subject: $this->reminder->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.reminder',
            with: [
                'message' => $this->reminder->message
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
