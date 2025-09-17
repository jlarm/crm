<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProgressCategory extends Model
{
    protected $fillable = [
        'name',
    ];

    public function progresses(): HasMany
    {
        return $this->hasMany(Progress::class);
    }
}
