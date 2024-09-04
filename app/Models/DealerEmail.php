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
        'dealer_email_template_id',
        'customize_email',
        'customize_attachment',
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
        'customize_email' => 'boolean',
        'customize_attachment' => 'boolean'
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

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($dealerEmail) {
            $dealerEmail->user_id = auth()->id();
        });
    }
}
