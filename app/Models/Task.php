<?php

declare(strict_types=1);

namespace App\Models;

use App\Enum\TaskPriority;
use App\Enum\TaskType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Laravel\Scout\Searchable;

/**
 * @property int $id
 * @property int $user_id
 * @property int $created_by_user_id
 * @property int|null $dealership_id
 * @property int|null $contact_id
 * @property string $title
 * @property string|null $description
 * @property TaskType $type
 * @property TaskPriority $priority
 * @property Carbon|null $due_date
 * @property Carbon|null $completed_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read User $user
 * @property-read User $createdBy
 * @property-read Dealership|null $dealership
 * @property-read Contact|null $contact
 */
class Task extends Model
{
    /** @use HasFactory<\Database\Factories\TaskFactory> */
    use HasFactory, Searchable;

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

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /**
     * @return BelongsTo<Dealership, $this>
     */
    public function dealership(): BelongsTo
    {
        return $this->belongsTo(Dealership::class);
    }

    /**
     * @return BelongsTo<Contact, $this>
     */
    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
        ];
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

    /**
     * @param  Builder<self>  $query
     */
    public function scopeIncomplete(Builder $query): void
    {
        $query->whereNull('completed_at');
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeCompleted(Builder $query): void
    {
        $query->whereNotNull('completed_at');
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeOverdue(Builder $query): void
    {
        $query->whereNull('completed_at')
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', now());
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeDueToday(Builder $query): void
    {
        $query->whereNull('completed_at')
            ->whereDate('due_date', today());
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeForUser(Builder $query, User $user): void
    {
        $query->where('user_id', $user->id);
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeWithPriority(Builder $query, ?string $priority): void
    {
        if (! $priority) {
            return;
        }

        $query->where('priority', $priority);
    }

    /**
     * @param  Builder<self>  $query
     */
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
