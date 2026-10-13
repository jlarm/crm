<?php

declare(strict_types=1);

namespace App\Mail;

use App\Mail\Concerns\FormatsPlainTextBody;
use App\Models\DealershipEmail;
use App\Models\DealershipEmailAttachment;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Mail\Attachment;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\HtmlString;

/**
 * An email a rep writes to a dealership contact, in the CRM's default template.
 *
 * It's sent from the rep's own address with the rep BCC'd, and carries our own
 * Message-ID so Mailgun's delivery events can be matched back to it. Replies go
 * to the thread's inbound address when one is configured.
 */
final class DealershipEmailMail extends Mailable
{
    use FormatsPlainTextBody;
    use Queueable;
    use SerializesModels;

    public function __construct(public readonly DealershipEmail $email) {}

    public function envelope(): Envelope
    {
        $rep = $this->email->user;

        return new Envelope(
            from: new Address($this->email->from_email, $this->email->from_name),
            to: [new Address($this->email->to_email, $this->email->contact?->name)],
            bcc: $rep?->email ? [new Address($rep->email, $rep->name)] : [],
            replyTo: [new Address($this->replyToAddress(), $this->email->from_name)],
            subject: $this->email->subject,
            tags: ['dealership-email'],
            metadata: ['dealership_email_id' => (string) $this->email->id],
        );
    }

    public function headers(): Headers
    {
        $threadId = $this->email->parent_id;

        /** @var list<string> $references */
        $references = $threadId === null ? [] : DealershipEmail::query()
            ->where(fn (Builder $query) => $query->whereKey($threadId)->orWhere('parent_id', $threadId))
            ->whereKeyNot($this->email->id)
            ->orderBy('id')
            ->pluck('message_id')
            ->all();

        return new Headers(
            messageId: $this->email->message_id,
            references: $references,
            text: $this->email->in_reply_to ? ['In-Reply-To' => '<'.$this->email->in_reply_to.'>'] : [],
        );
    }

    public function content(): Content
    {
        $rep = $this->email->user;

        return new Content(
            view: 'emails.dealership-email',
            text: 'emails.dealership-email-text',
            with: [
                'bodyHtml' => $this->email->body_html === null ? null : $this->styledHtml($this->email->body_html),
                'paragraphs' => $this->paragraphsFrom($this->email->body_text),
                'bodyText' => $this->email->body_text,
                'signature' => [
                    'name' => $this->email->from_name,
                    'phone' => $rep?->phone ? format_phone($rep->phone) : null,
                    'email' => $this->email->from_email,
                    'company' => 'Automotive Risk Management Partners',
                ],
                'note' => null,
            ],
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return $this->email->attachments
            ->map(fn (DealershipEmailAttachment $attachment): Attachment => Attachment::fromStorageDisk('local', $attachment->path)
                ->as($attachment->filename)
                ->withMime($attachment->mime_type ?? 'application/octet-stream'))
            ->all();
    }

    /**
     * Inline styles for the editor's tags, since many email clients ignore stylesheets.
     */
    private function styledHtml(string $html): HtmlString
    {
        return new HtmlString(preg_replace(
            ['#<p>#', '#<(ul|ol)>#', '#<li>#', '#<a #'],
            [
                '<p style="margin:0 0 14px 0;">',
                '<$1 style="margin:0 0 14px 0;padding-left:24px;">',
                '<li style="margin:0 0 4px 0;">',
                '<a style="color:#1a73e8;" ',
            ],
            $html,
        ) ?? $html);
    }

    private function replyToAddress(): string
    {
        $inboundDomain = config('services.mailgun.inbound_domain');

        if (is_string($inboundDomain) && $inboundDomain !== '' && $this->email->reply_token !== null) {
            return sprintf('reply+%s@%s', $this->email->reply_token, $inboundDomain);
        }

        return $this->email->from_email;
    }
}
