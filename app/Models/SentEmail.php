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
        if ($this->relationLoaded('trackingEvents')) {
            return $this->trackingEvents->contains('event_type', EmailTrackingEvent::EVENT_OPENED);
        }

        return $this->trackingEvents()->opened()->exists();
    }

    public function wasClicked(): bool
    {
        if ($this->relationLoaded('trackingEvents')) {
            return $this->trackingEvents->contains('event_type', EmailTrackingEvent::EVENT_CLICKED);
        }

        return $this->trackingEvents()->clicked()->exists();
    }

    public function wasBounced(): bool
    {
        if ($this->relationLoaded('trackingEvents')) {
            return $this->trackingEvents->contains('event_type', EmailTrackingEvent::EVENT_BOUNCED);
        }

        return $this->trackingEvents()->bounced()->exists();
    }

    public function openCount(): int
    {
        if ($this->relationLoaded('trackingEvents')) {
            return $this->trackingEvents->where('event_type', EmailTrackingEvent::EVENT_OPENED)->count();
        }

        return $this->trackingEvents()->opened()->count();
    }

    public function clickCount(): int
    {
        if ($this->relationLoaded('trackingEvents')) {
            return $this->trackingEvents->where('event_type', EmailTrackingEvent::EVENT_CLICKED)->count();
        }

        return $this->trackingEvents()->clicked()->count();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->setDescriptionForEvent(fn (string $eventName): string => "Email {$eventName}");
    }
}
