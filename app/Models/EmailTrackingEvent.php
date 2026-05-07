<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailTrackingEvent extends Model
{
    /** @use HasFactory<\Database\Factories\EmailTrackingEventFactory> */
    use HasFactory;

    public const string EVENT_DELIVERED = 'delivered';

    public const string EVENT_OPENED = 'opened';

    public const string EVENT_CLICKED = 'clicked';

    public const string EVENT_BOUNCED = 'bounced';

    public const string EVENT_COMPLAINED = 'complained';

    public const string EVENT_UNSUBSCRIBED = 'unsubscribed';

    protected $fillable = [
        'sent_email_id',
        'event_type',
        'message_id',
        'recipient_email',
        'url',
        'user_agent',
        'ip_address',
        'mailgun_data',
        'event_timestamp',
    ];

    protected $casts = [
        'mailgun_data' => 'array',
        'event_timestamp' => 'datetime',
    ];

    /**
     * @return BelongsTo<SentEmail, $this>
     */
    public function sentEmail(): BelongsTo
    {
        return $this->belongsTo(SentEmail::class);
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeOpened(Builder $query): void
    {
        $query->where('event_type', self::EVENT_OPENED);
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeClicked(Builder $query): void
    {
        $query->where('event_type', self::EVENT_CLICKED);
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeDelivered(Builder $query): void
    {
        $query->where('event_type', self::EVENT_DELIVERED);
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeBounced(Builder $query): void
    {
        $query->where('event_type', self::EVENT_BOUNCED);
    }
}
