<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dealership extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'address',
        'city',
        'state',
        'zip_code',
        'phone',
        'email',
        'current_solution_name',
        'current_solution_use',
        'notes',
        'status',
        'rating',
        'type',
        'in_development',
    ];

    protected $casts = [
        'in_development' => 'boolean',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function stores(): HasMany
    {
        return $this->hasMany(Store::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    public function progresses(): HasMany
    {
        return $this->hasMany(Progress::class);
    }

    protected static function boot(): void
    {
        parent::boot();

        static::created(function ($dealership) {
            $dealership->user_id = auth()->user()->id;
        });

    }

    public function getTotalStoreCountAttribute(): int
    {
        return $this->stores()->count() + 1;
    }

    public function getListType(): string
    {
        $types = [
            'Automotive' => 'f694f7fd-dbb9-489d-bced-03e2fbee78af',
            'RV' => '2d97d6ea-90a0-4b49-90df-980a258884b2',
            'Motorsports' => 'd2a68b06-08e4-4e76-a714-151e07a5a907',
            'Maritime' => '59c46030-5429-4ffd-a192-42926b9b17eb',
        ];

        return $types[$this->type];
    }
}
