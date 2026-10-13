<?php

declare(strict_types=1);

namespace App\Enum;

/**
 * Where an email stands, as far as Mailgun can tell us.
 *
 * "Delivered" means the recipient's mail server accepted it. Whether it then
 * landed in the inbox or the spam folder can't be known.
 */
enum EmailStatus: string
{
    case Queued = 'queued';
    case Sent = 'sent';
    case Deferred = 'deferred';
    case Delivered = 'delivered';
    case Opened = 'opened';
    case Failed = 'failed';
    case Bounced = 'bounced';
    case Complained = 'complained';
    case Received = 'received';

    public function label(): string
    {
        return match ($this) {
            self::Queued => 'Sending',
            self::Sent => 'Sent',
            self::Deferred => 'Delayed',
            self::Delivered => 'Delivered',
            self::Opened => 'Opened',
            self::Failed => 'Not sent',
            self::Bounced => 'Bounced',
            self::Complained => 'Marked as spam',
            self::Received => 'Received',
        };
    }

    /**
     * Badge colour: success, neutral, warning or danger.
     */
    public function tone(): string
    {
        return match ($this) {
            self::Delivered, self::Opened, self::Received => 'success',
            self::Queued, self::Sent => 'neutral',
            self::Deferred => 'warning',
            self::Failed, self::Bounced, self::Complained => 'danger',
        };
    }

    /**
     * Statuses only move up this ladder, so a late "delivered" event can't undo
     * "opened", while a bounce or spam complaint always wins.
     */
    public function rank(): int
    {
        return match ($this) {
            self::Queued, self::Received => 0,
            self::Sent => 1,
            self::Deferred => 2,
            self::Delivered => 3,
            self::Opened => 4,
            self::Failed, self::Bounced => 5,
            self::Complained => 6,
        };
    }

    public function canBecome(self $next): bool
    {
        return $next->rank() > $this->rank();
    }
}
