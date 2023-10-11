<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Store extends Model
{
    protected $fillable = [
        'user_id',
        'dealership_id',
        'name',
        'address',
        'city',
        'state',
        'zip_code',
        'phone',
        'current_solution_name',
        'current_solution_use',
    ];

    public function dealership(): BelongsTo
    {
        return $this->belongsTo(Dealership::class);
    }
}
