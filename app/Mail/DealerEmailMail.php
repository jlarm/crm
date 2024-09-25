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

            $this->body = str_replace('{{contact_name}}', $this->name, $this->body);

        } else {
            $this->subject = $dealerEmail->subject;
            $this->body = $dealerEmail->message;
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
                'user' => $this->dealerEmail->user,
            ],
        );
    }

    public function attachments()
    {
        $attachments = [];

        if ($this->dealerEmail->pdfAttachments) {
            foreach ($this->dealerEmail->pdfAttachments as $attachment) {
                $attachments[] = Attachment::fromStorageDisk('public', $attachment->file_path)->as($attachment->file_name);
            }
        }

        return $attachments;
    }
}
