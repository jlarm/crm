<?php

declare(strict_types=1);

namespace App\Actions\Emails;

use App\Enum\EmailFailureReason;
use App\Enum\EmailStatus;
use App\Models\DealershipEmail;
use App\Models\DealershipEmailEvent;
use Illuminate\Support\Carbon;

/**
 * Stores a Mailgun event for a dealership email and moves the email's status forward.
 *
 * Mailgun retries webhooks and can deliver events out of order, so events are
 * deduplicated by their Mailgun id and the status never moves backwards.
 */
final class RecordDealershipEmailEvent
{
    public function __construct(private readonly ClassifyMailgunFailure $classifyFailure) {}

    /**
     * @param  array<string, mixed>  $eventData
     */
    public function __invoke(DealershipEmail $email, array $eventData): ?DealershipEmailEvent
    {
        $event = is_string($eventData['event'] ?? null) ? $eventData['event'] : null;
        $eventId = is_string($eventData['id'] ?? null) ? $eventData['id'] : null;

        if ($event === null) {
            return null;
        }

        if ($eventId !== null && DealershipEmailEvent::where('mailgun_event_id', $eventId)->exists()) {
            return null;
        }

        $deliveryStatus = is_array($eventData['delivery-status'] ?? null) ? $eventData['delivery-status'] : [];
        $severity = is_string($eventData['severity'] ?? null) ? $eventData['severity'] : null;
        $description = $this->describe($deliveryStatus);
        $occurredAt = is_numeric($eventData['timestamp'] ?? null)
            ? Carbon::createFromTimestamp((float) $eventData['timestamp'])
            : now();

        $recorded = $email->events()->create([
            'mailgun_event_id' => $eventId,
            'event' => $event,
            'severity' => $severity,
            'reason' => is_string($eventData['reason'] ?? null) ? mb_substr($eventData['reason'], 0, 32) : null,
            'code' => is_numeric($deliveryStatus['code'] ?? null) ? (int) $deliveryStatus['code'] : null,
            'description' => $description,
            'payload' => $eventData,
            'occurred_at' => $occurredAt,
        ]);

        $this->applyToEmail($email, $event, $severity, $eventData, $description, $occurredAt);

        return $recorded;
    }

    /**
     * @param  array<string, mixed>  $eventData
     */
    private function applyToEmail(DealershipEmail $email, string $event, ?string $severity, array $eventData, ?string $description, Carbon $occurredAt): void
    {
        $status = match ($event) {
            'accepted' => EmailStatus::Sent,
            'delivered' => EmailStatus::Delivered,
            'opened', 'clicked' => EmailStatus::Opened,
            'failed' => $severity === 'temporary' ? EmailStatus::Deferred : EmailStatus::Bounced,
            'complained' => EmailStatus::Complained,
            default => null,
        };

        if ($status === null) {
            return;
        }

        $timestampColumn = match ($status) {
            EmailStatus::Delivered => 'delivered_at',
            EmailStatus::Opened => 'opened_at',
            EmailStatus::Bounced, EmailStatus::Complained => 'failed_at',
            default => null,
        };

        if ($timestampColumn !== null && $email->{$timestampColumn} === null) {
            $email->{$timestampColumn} = $occurredAt;
        }

        if ($email->status->canBecome($status)) {
            $email->status = $status;
            $email->status_reason = match ($status) {
                EmailStatus::Deferred, EmailStatus::Bounced => ($this->classifyFailure)($eventData),
                EmailStatus::Complained => EmailFailureReason::SpamComplaint,
                default => null,
            };
            $email->status_detail = in_array($status, [EmailStatus::Deferred, EmailStatus::Bounced], true) ? $description : null;
        }

        $email->save();
    }

    /**
     * @param  array<mixed>  $deliveryStatus
     */
    private function describe(array $deliveryStatus): ?string
    {
        $parts = array_filter([
            is_numeric($deliveryStatus['code'] ?? null) && (int) $deliveryStatus['code'] > 0 ? (string) $deliveryStatus['code'] : null,
            is_string($deliveryStatus['message'] ?? null) ? mb_trim($deliveryStatus['message']) : null,
            is_string($deliveryStatus['description'] ?? null) ? mb_trim($deliveryStatus['description']) : null,
        ]);

        return $parts === [] ? null : mb_substr(implode(' ', array_unique($parts)), 0, 1000);
    }
}
