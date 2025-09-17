<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class SentEmail extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'user_id',
        'dealership_id',
        'recipient',
        'message_id',
        'subject',
        'tracking_data',
    ];

    protected $casts = [
        'tracking_data' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function dealership(): BelongsTo
    {
        return $this->belongsTo(Dealership::class);
    }

    public function trackingEvents(): HasMany
    {
        return $this->hasMany(EmailTrackingEvent::class);
    }

    public function wasOpened(): bool
    {
        return $this->trackingEvents()->opened()->exists();
    }

    public function wasClicked(): bool
    {
        return $this->trackingEvents()->clicked()->exists();
    }

    public function wasBounced(): bool
    {
        return $this->trackingEvents()->bounced()->exists();
    }

    public function openCount(): int
    {
        return $this->trackingEvents()->opened()->count();
    }

    public function clickCount(): int
    {
        return $this->trackingEvents()->clicked()->count();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->setDescriptionForEvent(fn (string $eventName): string => "Email {$eventName}");
    }
}
