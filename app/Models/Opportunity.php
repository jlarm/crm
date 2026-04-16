<?php

declare(strict_types=1);

namespace App\Models;

use App\Enum\OpportunityStage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Opportunity extends Model
{
    /** @use HasFactory<\Database\Factories\OpportunityFactory> */
    use HasFactory;

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

    public function dealership(): BelongsTo
    {
        return $this->belongsTo(Dealership::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(OpportunityActivity::class)->orderByDesc('occurred_at')->orderByDesc('id');
    }

    public function scopeWon(Builder $query): void
    {
        $query->where('stage', OpportunityStage::Won->value);
    }

    public function scopeLost(Builder $query): void
    {
        $query->where('stage', OpportunityStage::Lost->value);
    }

    public function scopeOpen(Builder $query): void
    {
        $query->whereNotIn('stage', OpportunityStage::closedValues());
    }

    public function scopeClosingThisMonth(Builder $query): void
    {
        $query->whereNotIn('stage', OpportunityStage::closedValues())
            ->whereNotNull('expected_close_date')
            ->whereMonth('expected_close_date', now()->month)
            ->whereYear('expected_close_date', now()->year);
    }

    public function scopeWonLastMonth(Builder $query): void
    {
        $query->where('stage', OpportunityStage::Won->value)
            ->whereNotNull('closed_at')
            ->whereMonth('closed_at', now()->subMonth()->month)
            ->whereYear('closed_at', now()->subMonth()->year);
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
}
