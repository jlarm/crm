<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\DealershipEmail;
use App\Models\DealershipEmailAttachment;
use App\Models\DealershipEmailEvent;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin DealershipEmail
 */
final class DealershipEmailResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var DealershipEmail $email */
        $email = $this->resource;

        return [
            'id' => $email->id,
            'parentId' => $email->parent_id,
            'direction' => $email->direction->value,
            'from' => ['email' => $email->from_email, 'name' => $email->from_name],
            'to' => $email->to_email,
            'contact' => $email->contact ? ['id' => $email->contact->id, 'name' => $email->contact->name] : null,
            'sentBy' => $email->user ? ['id' => $email->user->id, 'name' => $email->user->name] : null,
            'subject' => $email->subject,
            'body' => $email->body_text,
            // Only HTML the CRM sanitized itself; a contact's HTML is never rendered.
            'bodyHtml' => $email->isOutbound() ? $email->body_html : null,
            'status' => $email->status->value,
            'statusLabel' => $email->status->label(),
            'statusTone' => $email->status->tone(),
            'statusReason' => $email->status_reason?->label(),
            'statusDetail' => $email->status_detail,
            'sentAt' => $email->sent_at?->toIso8601String(),
            'deliveredAt' => $email->delivered_at?->toIso8601String(),
            'openedAt' => $email->opened_at?->toIso8601String(),
            'failedAt' => $email->failed_at?->toIso8601String(),
            'readAt' => $email->read_at?->toIso8601String(),
            'createdAt' => $email->created_at->toIso8601String(),
            'attachments' => $email->attachments->map(fn (DealershipEmailAttachment $attachment): array => [
                'id' => $attachment->id,
                'filename' => $attachment->filename,
                'size' => $attachment->size,
                'url' => route('dealerships.emails.attachments.show', [$email->dealership_id, $attachment->id]),
            ])->all(),
            'events' => $email->events->map(fn (DealershipEmailEvent $event): array => [
                'id' => $event->id,
                'event' => $event->event,
                'severity' => $event->severity,
                'description' => $event->description,
                'occurredAt' => $event->occurred_at->toIso8601String(),
            ])->all(),
        ];
    }
}
