<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SentEmail extends Model
{
    protected $fillable = [
        'user_id',
        'dealership_id',
        'recipient',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function dealership(): BelongsTo
    {
        return $this->belongsTo(Dealership::class);
    }
}
