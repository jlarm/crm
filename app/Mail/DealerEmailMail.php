<?php

namespace App\Mail;

use App\Models\DealerEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DealerEmailMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(private readonly DealerEmail $dealerEmail) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address($this->dealerEmail->user->email),
            subject: $this->dealerEmail->subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.dealer-email',
            with: [
                'message' => $this->dealerEmail->message,
                'user' => $this->dealerEmail->user->name,
            ],
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromStorageDisk('public', $this->dealerEmail->attachment),
        ];
    }
}
