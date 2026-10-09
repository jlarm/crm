<?php

declare(strict_types=1);

namespace App\Models;

use App\Enum\OpportunityStage;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $opportunity_id
 * @property int|null $user_id
 * @property OpportunityStage|null $from_stage
 * @property OpportunityStage $to_stage
 * @property CarbonImmutable $changed_at
 * @property CarbonImmutable $created_at
 * @property CarbonImmutable $updated_at
 * @property-read Opportunity $opportunity
 * @property-read User|null $user
 */
class OpportunityStageChange extends Model
{
    /** @use HasFactory<\Database\Factories\OpportunityStageChangeFactory> */
    use HasFactory;

    protected $fillable = [
        'opportunity_id',
        'user_id',
        'from_stage',
        'to_stage',
        'changed_at',
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
            'from_stage' => OpportunityStage::class,
            'to_stage' => OpportunityStage::class,
            'changed_at' => 'datetime',
        ];
    }
}
