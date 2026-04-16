<?php

declare(strict_types=1);

namespace App\Models;

use App\Enum\TaskPriority;
use App\Enum\TaskType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    /** @use HasFactory<\Database\Factories\TaskFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'created_by_user_id',
        'dealership_id',
        'contact_id',
        'title',
        'description',
        'type',
        'priority',
        'due_date',
        'completed_at',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function dealership(): BelongsTo
    {
        return $this->belongsTo(Dealership::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function isCompleted(): bool
    {
        return $this->completed_at !== null;
    }

    public function isOverdue(): bool
    {
        return $this->due_date !== null
            && $this->due_date->isPast()
            && ! $this->isCompleted();
    }

    public function scopeIncomplete(Builder $query): void
    {
        $query->whereNull('completed_at');
    }

    public function scopeCompleted(Builder $query): void
    {
        $query->whereNotNull('completed_at');
    }

    public function scopeOverdue(Builder $query): void
    {
        $query->whereNull('completed_at')
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', now());
    }

    public function scopeDueToday(Builder $query): void
    {
        $query->whereNull('completed_at')
            ->whereDate('due_date', today());
    }

    public function scopeForUser(Builder $query, User $user): void
    {
        $query->where('user_id', $user->id);
    }

    public function scopeWithPriority(Builder $query, ?string $priority): void
    {
        if (! $priority) {
            return;
        }

        $query->where('priority', $priority);
    }

    public function scopeWithType(Builder $query, ?string $type): void
    {
        if (! $type) {
            return;
        }

        $query->where('type', $type);
    }

    protected function casts(): array
    {
        return [
            'type' => TaskType::class,
            'priority' => TaskPriority::class,
            'due_date' => 'date',
            'completed_at' => 'datetime',
        ];
    }
}
