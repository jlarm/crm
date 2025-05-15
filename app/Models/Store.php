<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Store extends Model
{
    use LogsActivity;

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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->setDescriptionForEvent(fn (string $eventName): string => "Store {$eventName}");
    }
}
