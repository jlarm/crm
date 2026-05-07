<?php

declare(strict_types=1);

namespace App\Models;

use App\Enum\ReminderFrequency;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property int $id
 * @property int|null $user_id
 * @property int|null $dealership_id
 * @property int|null $dealer_email_template_id
 * @property bool $customize_email
 * @property bool $customize_attachment
 * @property array<int, string>|null $recipients
 * @property string|null $attachment
 * @property string|null $attachment_name
 * @property string|null $subject
 * @property string|null $message
 * @property CarbonImmutable|null $start_date
 * @property CarbonImmutable|null $last_sent
 * @property CarbonImmutable|null $next_send_date
 * @property ReminderFrequency|null $frequency
 * @property bool $paused
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read User|null $user
 * @property-read Dealership|null $dealership
 * @property-read DealerEmailTemplate|null $template
 * @property-read Collection<int, PdfAttachment> $pdfAttachments
 */
class DealerEmail extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'user_id',
        'dealership_id',
        'dealer_email_template_id',
        'customize_email',
        'customize_attachment',
        'recipients',
        'attachment',
        'subject',
        'message',
        'start_date',
        'last_sent',
        'next_send_date',
        'frequency',
        'paused',
        'attachment_name',
    ];

    protected $casts = [
        'start_date' => 'date:Y-m-d',
        'last_sent' => 'date:Y-m-d',
        'paused' => 'boolean',
        'recipients' => 'array',
        'frequency' => ReminderFrequency::class,
        'customize_email' => 'boolean',
        'customize_attachment' => 'boolean',
        'next_send_date' => 'date:Y-m-d',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Dealership, $this>
     */
    public function dealership(): BelongsTo
    {
        return $this->belongsTo(Dealership::class);
    }

    /**
     * @return BelongsTo<DealerEmailTemplate, $this>
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(DealerEmailTemplate::class, 'dealer_email_template_id');
    }

    /**
     * @return MorphToMany<PdfAttachment, $this>
     */
    public function pdfAttachments(): MorphToMany
    {
        return $this->morphToMany(PdfAttachment::class, 'attachable');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->setDescriptionForEvent(fn (string $eventName): string => "Dealer Email {$eventName}");
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($dealerEmail): void {
            $dealerEmail->user_id = auth()->id();
        });
    }
}
