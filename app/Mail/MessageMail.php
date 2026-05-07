<?php

declare(strict_types=1);

namespace App\Mail;

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

    /**
     * @var Dealership
     */
    public $dealer;

    /**
     * @param  string  $subject
     */
    public function __construct(Dealership $dealer, public mixed $sender, public $subject, public mixed $message)
    {
        $this->dealer = $dealer;
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
                'link' => route('dealerships.show', $this->dealer),
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
