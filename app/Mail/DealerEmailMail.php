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
            from: new Address('no-reply@crm-armp.com'),
            subject: $this->dealerEmail->subject,
        );
    }

    public function content(): Content
    {
        $user = $this->dealerEmail->user;

        return new Content(
            markdown: 'emails.dealer-email',
            with: [
                'message' => $this->dealerEmail->message,
                'user' => $user->name,
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
