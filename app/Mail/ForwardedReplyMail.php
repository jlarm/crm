<?php

declare(strict_types=1);

namespace App\Mail;

use App\Mail\Concerns\FormatsPlainTextBody;
use App\Models\DealershipEmail;
use App\Models\DealershipEmailAttachment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Attachment;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Passes a contact's reply on to the rep's own mailbox. Replying to it goes straight to the contact.
 */
final class ForwardedReplyMail extends Mailable implements ShouldQueue
{
    use FormatsPlainTextBody;
    use Queueable;
    use SerializesModels;

    public function __construct(public readonly DealershipEmail $reply) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(
                (string) config('mail.mailers.dealership.from.address'),
                (string) config('mail.mailers.dealership.from.name'),
            ),
            replyTo: [new Address($this->reply->from_email, $this->reply->from_name)],
            subject: $this->reply->subject,
        );
    }

    public function content(): Content
    {
        $sender = $this->reply->from_name ?: $this->reply->from_email;

        return new Content(
            view: 'emails.dealership-email',
            text: 'emails.dealership-email-text',
            with: [
                'bodyHtml' => null,
                'paragraphs' => $this->paragraphsFrom($this->reply->body_text),
                'bodyText' => $this->reply->body_text,
                'signature' => null,
                'note' => [
                    'text' => sprintf('%s (%s) replied. It\'s saved on %s in the CRM.', $sender, $this->reply->from_email, $this->reply->dealership->name),
                    'url' => route('dealerships.show', $this->reply->dealership_id),
                    'link' => 'Open dealership',
                ],
            ],
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return $this->reply->attachments
            ->map(fn (DealershipEmailAttachment $attachment): Attachment => Attachment::fromStorageDisk('local', $attachment->path)
                ->as($attachment->filename)
                ->withMime($attachment->mime_type ?? 'application/octet-stream'))
            ->all();
    }
}
