<?php

declare(strict_types=1);

namespace App\Models;

use App\Enum\ActivityType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpportunityActivity extends Model
{
    /** @use HasFactory<\Database\Factories\OpportunityActivityFactory> */
    use HasFactory;

    protected $fillable = [
        'opportunity_id',
        'user_id',
        'type',
        'details',
        'occurred_at',
    ];

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(Opportunity::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return [
            'type' => ActivityType::class,
            'occurred_at' => 'date',
        ];
    }
}
