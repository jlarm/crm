<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClientMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $sender;

    public $subject;

    public $message;

    public function __construct($sender, $subject, $message)
    {
        $this->sender = $sender;
        $this->subject = $subject;
        $this->message = $message;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address($this->sender->email, $this->sender->name),
            subject: $this->subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.client',
            with: [
                'message' => $this->message,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
