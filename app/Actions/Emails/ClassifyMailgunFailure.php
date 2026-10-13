<?php

declare(strict_types=1);

namespace App\Actions\Emails;

use App\Enum\EmailFailureReason;

/**
 * Turns a Mailgun "failed" event into a reason a rep can act on.
 *
 * Mailgun's own `reason` covers its suppressions; for everything else the
 * receiving server's SMTP code and message are the only signal, and wording
 * varies by provider, so this matches the common phrasings.
 */
final class ClassifyMailgunFailure
{
    /**
     * @param  array<string, mixed>  $eventData
     */
    public function __invoke(array $eventData): EmailFailureReason
    {
        $reason = is_string($eventData['reason'] ?? null) ? $eventData['reason'] : '';
        $deliveryStatus = is_array($eventData['delivery-status'] ?? null) ? $eventData['delivery-status'] : [];
        $code = is_numeric($deliveryStatus['code'] ?? null) ? (int) $deliveryStatus['code'] : 0;
        $message = mb_strtolower(implode(' ', array_filter([
            is_string($deliveryStatus['message'] ?? null) ? $deliveryStatus['message'] : null,
            is_string($deliveryStatus['description'] ?? null) ? $deliveryStatus['description'] : null,
        ])));

        if (str_starts_with($reason, 'suppress-')) {
            return EmailFailureReason::Suppressed;
        }

        if ($code === 552 || preg_match('/quota|mailbox (is )?full|mailbox full|insufficient (system )?storage|over ?quota/', $message) === 1) {
            return EmailFailureReason::MailboxFull;
        }

        if (preg_match('/does not exist|doesn\'t exist|unknown user|user unknown|no such user|invalid recipient|recipient (address )?rejected|address not found|mailbox not found|mailbox unavailable|unrouteable|no mailbox/', $message) === 1) {
            return EmailFailureReason::AddressNotFound;
        }

        if (preg_match('/spam|blocked|block ?list|black ?list|policy|reputation|denied|refused|not authorized|dmarc/', $message) === 1 || $code === 554) {
            return EmailFailureReason::Blocked;
        }

        if (in_array($code, [550, 551, 553], true) || $reason === 'bounce') {
            return EmailFailureReason::AddressNotFound;
        }

        return EmailFailureReason::Other;
    }
}
