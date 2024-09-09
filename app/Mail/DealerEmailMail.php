<?php

namespace App\Mail;

use App\Models\DealerEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DealerEmailMail extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;
    public mixed $body;
    public $attachment;
    public $attachmentName;
    public function __construct(private readonly DealerEmail $dealerEmail, private readonly ?string $name) {
        if ($dealerEmail->template) {

            if ($this->dealerEmail->customize_email) {
                $this->subject = $dealerEmail->subject;
                $this->body = $dealerEmail->message;
            } else {
                $this->subject = $dealerEmail->template->subject;
                $this->body = $dealerEmail->template->body;
            }

            if ($this->dealerEmail->customize_attachment) {
                $this->attachment = $dealerEmail->attachment;
                $this->attachmentName = $dealerEmail->attachment_name;
            } else {
                $this->attachment = $dealerEmail->template->attachment_path;
                $this->attachmentName = $dealerEmail->template->attachment_name;
            }

            $this->body = str_replace('{{contact_name}}', $this->name, $this->body);

        } else {
            $this->subject = $dealerEmail->subject;
            $this->body = $dealerEmail->message;
            $this->attachment = $dealerEmail->attachment;
            $this->attachmentName = $dealerEmail->attachment_name;
        }

    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address($this->dealerEmail->user->email, $this->dealerEmail->user->name . ' from ARMP'),
            subject: $this->subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.dealer-email',
            with: [
                'message' => $this->body,
                'user' => $this->dealerEmail->user->name,
            ],
        );
    }

    public function attachments(): array
    {
        if ($this->attachment) {
            return [
                Attachment::fromStorageDisk('public', $this->attachment)->as($this->attachmentName),
            ];
        }

        return [];
    }
}
