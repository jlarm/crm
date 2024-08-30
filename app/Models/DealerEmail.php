<?php

namespace App\Models;

use App\Enum\ReminderFrequency;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DealerEmail extends Model
{
    protected $fillable = [
        'user_id',
        'dealership_id',
        'recipients',
        'attachment',
        'subject',
        'message',
        'start_date',
        'last_sent',
        'frequency',
        'paused',
        'attachment_name'
    ];

    protected $casts = [
        'start_date' => 'date:Y-m-d',
        'last_sent' => 'date:Y-m-d',
        'paused' => 'boolean',
        'recipients' => 'array',
        'frequency' => ReminderFrequency::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function dealership(): BelongsTo
    {
        return $this->belongsTo(Dealership::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($dealerEmail) {
            $dealerEmail->user_id = auth()->id();
        });
    }
}
