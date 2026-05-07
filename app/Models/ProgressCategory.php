<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 */
class ProgressCategory extends Model
{
    /** @use HasFactory<\Database\Factories\ProgressCategoryFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    /**
     * @return HasMany<Progress, $this>
     */
    public function progresses(): HasMany
    {
        return $this->hasMany(Progress::class);
    }
}
