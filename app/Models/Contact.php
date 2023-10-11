<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contact extends Model
{
    protected $fillable = [
        'dealership_id',
        'name',
        'email',
        'phone',
        'position',
    ];

    public function dealership(): BelongsTo
    {
        return $this->belongsTo(Dealership::class);
    }
}
