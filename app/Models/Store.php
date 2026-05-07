<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Scout\Searchable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Store extends Model
{
    /** @use HasFactory<\Database\Factories\StoreFactory> */
    use HasFactory, LogsActivity, Searchable;

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

    /**
     * @return BelongsTo<Dealership, $this>
     */
    public function dealership(): BelongsTo
    {
        return $this->belongsTo(Dealership::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'phone' => $this->phone,
            'current_solution_name' => $this->current_solution_name,
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->setDescriptionForEvent(fn (string $eventName): string => "Store {$eventName}");
    }
}
