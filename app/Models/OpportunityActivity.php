<?php

declare(strict_types=1);

namespace App\Models;

use App\Enum\ActivityType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $opportunity_id
 * @property int $user_id
 * @property ActivityType $type
 * @property string $details
 * @property Carbon|null $occurred_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Opportunity $opportunity
 * @property-read User $user
 */
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

    /**
     * @return BelongsTo<Opportunity, $this>
     */
    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(Opportunity::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
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
