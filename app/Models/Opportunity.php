<?php

declare(strict_types=1);

namespace App\Models;

use App\Enum\OpportunityStage;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property int $id
 * @property int $dealership_id
 * @property string $name
 * @property OpportunityStage $stage
 * @property CarbonImmutable|null $stage_entered_at
 * @property int|null $probability
 * @property string|null $estimated_value
 * @property string|null $actual_value
 * @property Carbon|null $expected_close_date
 * @property string|null $next_action
 * @property Carbon|null $follow_up_date
 * @property string|null $lost_reason
 * @property string|null $lost_reason_code
 * @property Carbon|null $contract_sent_date
 * @property Carbon|null $contract_signed_date
 * @property Carbon|null $contract_renewal_date
 * @property CarbonImmutable|null $closed_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Dealership $dealership
 * @property-read \Illuminate\Database\Eloquent\Collection<int, OpportunityActivity> $activities
 */
class Opportunity extends Model
{
    /** @use HasFactory<\Database\Factories\OpportunityFactory> */
    use HasFactory;

    use LogsActivity;

    protected $fillable = [
        'dealership_id',
        'name',
        'stage',
        'stage_entered_at',
        'probability',
        'estimated_value',
        'actual_value',
        'expected_close_date',
        'next_action',
        'follow_up_date',
        'lost_reason',
        'lost_reason_code',
        'contract_sent_date',
        'contract_signed_date',
        'contract_renewal_date',
        'closed_at',
    ];

    /**
     * @return BelongsTo<Dealership, $this>
     */
    public function dealership(): BelongsTo
    {
        return $this->belongsTo(Dealership::class);
    }

    /**
     * @return HasMany<OpportunityStageChange, $this>
     */
    public function stageChanges(): HasMany
    {
        return $this->hasMany(OpportunityStageChange::class)->orderBy('changed_at')->orderBy('id');
    }

    /**
     * @return HasMany<OpportunityActivity, $this>
     */
    public function activities(): HasMany
    {
        return $this->hasMany(OpportunityActivity::class)->orderByDesc('occurred_at')->orderByDesc('id');
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeWon(Builder $query): void
    {
        $query->where('stage', OpportunityStage::Won->value);
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeLost(Builder $query): void
    {
        $query->where('stage', OpportunityStage::Lost->value);
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeOpen(Builder $query): void
    {
        $query->whereNotIn('stage', OpportunityStage::closedValues());
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeClosingThisMonth(Builder $query): void
    {
        $query->whereNotIn('stage', OpportunityStage::closedValues())
            ->whereNotNull('expected_close_date')
            ->whereMonth('expected_close_date', now()->month)
            ->whereYear('expected_close_date', now()->year);
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeWonLastMonth(Builder $query): void
    {
        $query->where('stage', OpportunityStage::Won->value)
            ->whereNotNull('closed_at')
            ->whereMonth('closed_at', now()->subMonth()->month)
            ->whereYear('closed_at', now()->subMonth()->year);
    }

    /**
     * Keep stage timestamps in step with the stage and record every move.
     *
     * Entering Won or Lost stamps closed_at; reopening a deal clears it. Values set
     * explicitly in the same save win, so imports and factories can backdate them.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->setDescriptionForEvent(fn (string $eventName): string => 'Opportunity '.$eventName);
    }

    protected static function booted(): void
    {
        static::saving(function (Opportunity $opportunity): void {
            if (! $opportunity->exists || $opportunity->isDirty('stage')) {
                if (! $opportunity->isDirty('stage_entered_at') || $opportunity->stage_entered_at === null) {
                    $opportunity->stage_entered_at = now();
                }

                if (! $opportunity->isDirty('closed_at')) {
                    $opportunity->closed_at = $opportunity->stage->isOpen() ? null : now();
                }
            }
        });

        static::created(function (Opportunity $opportunity): void {
            $opportunity->recordStageChange(null);
        });

        static::updated(function (Opportunity $opportunity): void {
            if (! $opportunity->wasChanged('stage')) {
                return;
            }

            $from = $opportunity->getRawOriginal('stage');

            $opportunity->recordStageChange(is_string($from) ? $from : null);
        });
    }

    protected function casts(): array
    {
        return [
            'stage' => OpportunityStage::class,
            'stage_entered_at' => 'datetime',
            'expected_close_date' => 'date',
            'follow_up_date' => 'date',
            'contract_sent_date' => 'date',
            'contract_signed_date' => 'date',
            'contract_renewal_date' => 'date',
            'closed_at' => 'date',
            'probability' => 'integer',
            'estimated_value' => 'decimal:2',
            'actual_value' => 'decimal:2',
        ];
    }

    private function recordStageChange(?string $fromStage): void
    {
        $this->stageChanges()->create([
            'user_id' => auth()->id(),
            'from_stage' => $fromStage,
            'to_stage' => $this->stage->value,
            'changed_at' => $this->stage_entered_at ?? now(),
        ]);
    }
}
