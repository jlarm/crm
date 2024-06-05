<?php

namespace App\Mail;

use App\Filament\Resources\DealershipResource;
use App\Models\Dealership;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MessageMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $dealer;
    public $sender;
    public $subject;
    public $message;

    public function __construct(Dealership $dealer, $sender, $subject, $message)
    {
        $this->dealer = $dealer;
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
            markdown: 'emails.message',
            with: [
                'dealership' => $this->dealer,
                'message' => $this->message,
                'link' => DealershipResource::getUrl('edit', ['record' => $this->dealer]),
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
