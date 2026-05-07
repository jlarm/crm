<?php

declare(strict_types=1);

namespace App\Models;

use App\Enum\ReminderFrequency;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property int $id
 * @property int|null $user_id
 * @property bool $dev_rel
 * @property string $title
 * @property string|null $message
 * @property Carbon|null $start_date
 * @property Carbon|null $last_sent
 * @property ReminderFrequency|null $sending_frequency
 * @property bool $pause
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read User|null $user
 */
class Reminder extends Model
{
    use LogsActivity;

    protected $fillable = [
        'user_id',
        'dev_rel',
        'title',
        'message',
        'start_date',
        'last_sent',
        'sending_frequency',
        'pause',
    ];

    protected $casts = [
        'dev_rel' => 'boolean',
        'start_date' => 'date',
        'last_sent' => 'date',
        'sending_frequency' => ReminderFrequency::class,
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->setDescriptionForEvent(fn (string $eventName): string => 'Reminder '.$eventName);
    }

    protected static function booted(): void
    {
        static::creating(function (Reminder $reminder): void {
            if (! $reminder->user_id) {
                $authId = auth()->id();
                $reminder->user_id = $authId === null ? null : (int) $authId;
            }
        });
    }
}
