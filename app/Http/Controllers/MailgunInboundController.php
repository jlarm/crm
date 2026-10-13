<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Emails\VerifyMailgunSignature;
use App\Enum\EmailDirection;
use App\Enum\EmailStatus;
use App\Mail\ForwardedReplyMail;
use App\Models\DealershipEmail;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Replies to CRM emails, posted by a Mailgun route on the inbound domain.
 *
 * Each reply is saved on its thread and passed on to the rep's own mailbox.
 * Anything we can't match is acknowledged and dropped, so Mailgun doesn't retry it.
 */
final class MailgunInboundController extends Controller
{
    public function __invoke(Request $request, VerifyMailgunSignature $verifySignature): Response
    {
        if (! $verifySignature($request->input('timestamp'), $request->input('token'), $request->input('signature'))) {
            return response('Unauthorized', 401);
        }

        $original = $this->findOriginal($request);

        if ($original === null) {
            Log::info('Inbound email did not match a CRM thread', ['recipient' => $request->input('recipient')]);

            return response('OK', 200);
        }

        $messageId = $this->bareMessageId($request->input('Message-Id')) ?? Str::uuid()->toString().'@inbound';

        if (DealershipEmail::where('message_id', $messageId)->exists()) {
            return response('OK', 200);
        }

        [$fromEmail, $fromName] = $this->parseFrom($request);

        $reply = DB::transaction(function () use ($request, $original, $messageId, $fromEmail, $fromName): DealershipEmail {
            $contactId = $original->dealership->contacts()
                ->whereRaw('LOWER(email) = ?', [mb_strtolower($fromEmail)])
                ->value('id');

            $reply = DealershipEmail::create([
                'dealership_id' => $original->dealership_id,
                'contact_id' => $contactId,
                'user_id' => $original->user_id,
                'parent_id' => $original->parent_id ?? $original->id,
                'direction' => EmailDirection::Inbound,
                'from_email' => $fromEmail,
                'from_name' => $fromName,
                'to_email' => (string) $request->input('recipient'),
                'subject' => mb_substr($request->string('subject')->trim()->value() ?: '(no subject)', 0, 255),
                'body_text' => $this->text($request),
                'body_html' => $request->string('body-html')->value() ?: null,
                'message_id' => $messageId,
                'in_reply_to' => $this->bareMessageId($request->input('In-Reply-To')),
                'status' => EmailStatus::Received,
            ]);

            foreach ($request->allFiles() as $key => $file) {
                if (! str_starts_with((string) $key, 'attachment-') || ! $file instanceof UploadedFile) {
                    continue;
                }

                $reply->attachments()->create([
                    'filename' => $file->getClientOriginalName(),
                    'path' => $file->store('dealership-emails/'.$reply->id, 'local'),
                    'mime_type' => $file->getClientMimeType(),
                    'size' => $file->getSize(),
                ]);
            }

            return $reply;
        });

        // A reply proves the email arrived, even if Mailgun's delivery event hasn't come in.
        if ($original->isOutbound() && $original->status->canBecome(EmailStatus::Delivered)) {
            $original->update([
                'status' => EmailStatus::Delivered,
                'status_reason' => null,
                'status_detail' => null,
                'delivered_at' => $original->delivered_at ?? now(),
            ]);
        }

        $rep = $original->user;

        if ($rep?->email) {
            Mail::mailer('dealership')->to($rep->email, $rep->name)->queue(new ForwardedReplyMail($reply));
        }

        return response('OK', 200);
    }

    private function findOriginal(Request $request): ?DealershipEmail
    {
        $recipient = mb_strtolower((string) $request->input('recipient'));

        if (preg_match('/reply\+([a-z0-9]+)@/', $recipient, $matches) === 1) {
            $original = DealershipEmail::with('dealership')->where('reply_token', $matches[1])->first();

            if ($original !== null) {
                return $original;
            }
        }

        $inReplyTo = $this->bareMessageId($request->input('In-Reply-To'));

        return $inReplyTo === null ? null : DealershipEmail::with('dealership')->where('message_id', $inReplyTo)->first();
    }

    /**
     * @return array{0: string, 1: string|null}
     */
    private function parseFrom(Request $request): array
    {
        $from = (string) $request->input('from');

        if (preg_match('/^\s*"?([^"<]*?)"?\s*<([^>]+)>/', $from, $matches) === 1) {
            return [mb_strtolower(mb_trim($matches[2])), mb_trim($matches[1]) ?: null];
        }

        $sender = mb_trim($from) ?: (string) $request->input('sender');

        return [mb_strtolower($sender), null];
    }

    private function text(Request $request): string
    {
        foreach (['stripped-text', 'body-plain'] as $field) {
            $value = mb_trim($request->string($field)->value());

            if ($value !== '') {
                return $value;
            }
        }

        $html = $request->string('body-html')->value();

        return mb_trim(html_entity_decode(strip_tags($html))) ?: '(empty message)';
    }

    private function bareMessageId(mixed $value): ?string
    {
        if (! is_string($value) || mb_trim($value) === '') {
            return null;
        }

        return mb_substr(mb_trim(explode(' ', mb_trim($value))[0], " <>\t"), 0, 255);
    }
}
