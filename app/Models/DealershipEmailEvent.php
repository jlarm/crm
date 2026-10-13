<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A delivery event Mailgun reported for a dealership email.
 *
 * @property int $id
 * @property int $dealership_email_id
 * @property string|null $mailgun_event_id
 * @property string $event
 * @property string|null $severity
 * @property string|null $reason
 * @property int|null $code
 * @property string|null $description
 * @property array<string, mixed>|null $payload
 * @property Carbon $occurred_at
 * @property-read DealershipEmail $email
 */
class DealershipEmailEvent extends Model
{
    protected $fillable = [
        'dealership_email_id',
        'mailgun_event_id',
        'event',
        'severity',
        'reason',
        'code',
        'description',
        'payload',
        'occurred_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'code' => 'integer',
        'occurred_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<DealershipEmail, $this>
     */
    public function email(): BelongsTo
    {
        return $this->belongsTo(DealershipEmail::class, 'dealership_email_id');
    }
}
