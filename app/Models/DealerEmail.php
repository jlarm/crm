<?php

declare(strict_types=1);

namespace App\Models;

use App\Enum\ReminderFrequency;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class DealerEmail extends Model
{
    use LogsActivity;

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
        'next_send_date',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function dealership(): BelongsTo
    {
        return $this->belongsTo(Dealership::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(DealerEmailTemplate::class, 'dealer_email_template_id');
    }

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
