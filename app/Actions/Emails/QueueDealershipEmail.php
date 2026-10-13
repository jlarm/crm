<?php

declare(strict_types=1);

namespace App\Actions\Emails;

use App\Enum\EmailDirection;
use App\Enum\EmailStatus;
use App\Jobs\SendDealershipEmail;
use App\Models\Contact;
use App\Models\Dealership;
use App\Models\DealershipEmail;
use App\Models\ProgressCategory;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Saves a rep's email to a contact, logs it as an Email touchpoint and queues it for sending.
 */
final class QueueDealershipEmail
{
    public function __construct(private readonly ConvertEmailHtmlToText $convertToText) {}

    /**
     * @param  string  $bodyHtml  Already sanitized with SanitizeEmailHtml.
     * @param  list<UploadedFile>  $attachments
     */
    public function __invoke(
        User $rep,
        Dealership $dealership,
        Contact $contact,
        string $subject,
        string $bodyHtml,
        ?DealershipEmail $replyingTo = null,
        array $attachments = [],
    ): DealershipEmail {
        $email = DB::transaction(function () use ($rep, $dealership, $contact, $subject, $bodyHtml, $replyingTo, $attachments): DealershipEmail {
            $email = $dealership->emails()->create([
                'contact_id' => $contact->id,
                'user_id' => $rep->id,
                'parent_id' => $replyingTo === null ? null : ($replyingTo->parent_id ?? $replyingTo->id),
                'direction' => EmailDirection::Outbound,
                'from_email' => $rep->email,
                'from_name' => $rep->name,
                'to_email' => (string) $contact->email,
                'subject' => $subject,
                'body_text' => ($this->convertToText)($bodyHtml),
                'body_html' => $bodyHtml,
                'message_id' => sprintf('%s@%s', Str::uuid(), Str::after($rep->email, '@')),
                'in_reply_to' => $replyingTo?->message_id,
                'reply_token' => Str::lower(Str::random(32)),
                'status' => EmailStatus::Queued,
            ]);

            foreach ($attachments as $file) {
                $email->attachments()->create([
                    'filename' => $file->getClientOriginalName(),
                    'path' => $file->store('dealership-emails/'.$email->id, 'local'),
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ]);
            }

            $dealership->progresses()->create([
                'user_id' => $rep->id,
                'contact_id' => $contact->id,
                'progress_category_id' => ProgressCategory::where('name', 'Email')->value('id'),
                'date' => today(),
                'details' => 'Sent from CRM: '.$subject,
            ]);

            return $email;
        });

        SendDealershipEmail::dispatch($email);

        return $email;
    }
}
