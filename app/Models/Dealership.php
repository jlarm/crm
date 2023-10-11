<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
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
    ];

    public function stores(): HasMany
    {
        return $this->hasMany(Store::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }
}
