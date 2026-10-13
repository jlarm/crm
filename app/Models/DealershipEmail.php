<?php

declare(strict_types=1);

namespace App\Models;

use App\Enum\EmailDirection;
use App\Enum\EmailFailureReason;
use App\Enum\EmailStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * An email sent to, or received from, a dealership contact through the CRM.
 *
 * Replies point at the email that started the thread through parent_id.
 *
 * @property int $id
 * @property int $dealership_id
 * @property int|null $contact_id
 * @property int|null $user_id
 * @property int|null $parent_id
 * @property EmailDirection $direction
 * @property string $from_email
 * @property string|null $from_name
 * @property string $to_email
 * @property string $subject
 * @property string $body_text
 * @property string|null $body_html
 * @property string $message_id
 * @property string|null $in_reply_to
 * @property string|null $reply_token
 * @property EmailStatus $status
 * @property EmailFailureReason|null $status_reason
 * @property string|null $status_detail
 * @property Carbon|null $sent_at
 * @property Carbon|null $delivered_at
 * @property Carbon|null $opened_at
 * @property Carbon|null $failed_at
 * @property Carbon|null $read_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Dealership $dealership
 * @property-read Contact|null $contact
 * @property-read User|null $user
 * @property-read DealershipEmail|null $parent
 */
class DealershipEmail extends Model
{
    /** @use HasFactory<\Database\Factories\DealershipEmailFactory> */
    use HasFactory;

    protected $fillable = [
        'dealership_id',
        'contact_id',
        'user_id',
        'parent_id',
        'direction',
        'from_email',
        'from_name',
        'to_email',
        'subject',
        'body_text',
        'body_html',
        'message_id',
        'in_reply_to',
        'reply_token',
        'status',
        'status_reason',
        'status_detail',
        'sent_at',
        'delivered_at',
        'opened_at',
        'failed_at',
        'read_at',
    ];

    protected $casts = [
        'direction' => EmailDirection::class,
        'status' => EmailStatus::class,
        'status_reason' => EmailFailureReason::class,
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'opened_at' => 'datetime',
        'failed_at' => 'datetime',
        'read_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<Dealership, $this>
     */
    public function dealership(): BelongsTo
    {
        return $this->belongsTo(Dealership::class);
    }

    /**
     * @return BelongsTo<Contact, $this>
     */
    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    /**
     * @return BelongsTo<DealershipEmail, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * @return HasMany<DealershipEmail, $this>
     */
    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * @return HasMany<DealershipEmailEvent, $this>
     */
    public function events(): HasMany
    {
        return $this->hasMany(DealershipEmailEvent::class);
    }

    /**
     * @return HasMany<DealershipEmailAttachment, $this>
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(DealershipEmailAttachment::class);
    }

    /**
     * Contacts' replies nobody has opened in the CRM yet.
     *
     * @param  Builder<DealershipEmail>  $query
     */
    public function scopeUnreadReplies(Builder $query): void
    {
        $query->where('direction', EmailDirection::Inbound)->whereNull('read_at');
    }

    public function isOutbound(): bool
    {
        return $this->direction === EmailDirection::Outbound;
    }
}
