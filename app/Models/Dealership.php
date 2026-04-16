<?php

declare(strict_types=1);

namespace App\Models;

use App\Enum\DevStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Dealership extends Model
{
    use HasFactory, LogsActivity, Searchable;

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
        'rating',
        'type',
        'in_development',
        'dev_status',
    ];

    protected $casts = [
        'in_development' => 'boolean',
        'dev_status' => DevStatus::class,
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function stores(): HasMany
    {
        return $this->hasMany(Store::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function progresses(): HasMany
    {
        return $this->hasMany(Progress::class);
    }

    public function opportunities(): HasMany
    {
        return $this->hasMany(Opportunity::class);
    }

    public function dealerEmails(): HasMany
    {
        return $this->hasMany(DealerEmail::class);
    }

    public function sentEmails(): HasMany
    {
        return $this->hasMany(SentEmail::class);
    }

    public function getTotalStoreCountAttribute(): int
    {
        return $this->stores()->count() + 1;
    }

    public function getListType(): string
    {
        $types = [
            'Automotive' => config('services.mailcoach.lists.automotive'),
            'RV' => config('services.mailcoach.lists.rv'),
            'Motorsports' => config('services.mailcoach.lists.motorsports'),
            'Maritime' => config('services.mailcoach.lists.maritime'),
        ];

        return $types[$this->type] ?? 'default_value';
    }

    /**
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'city' => $this->city,
            'state' => $this->state,
            'email' => $this->email,
            'phone' => $this->phone,
            'type' => $this->type,
            'rating' => $this->rating,
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->setDescriptionForEvent(fn (string $eventName): string => "Dealership {$eventName}");
    }

    public function scopeSearch(Builder $query, ?string $search): void
    {
        if (! $search) {
            return;
        }

        $query->where(function (Builder $q) use ($search): void {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('city', 'like', "%{$search}%");
        });
    }

    public function scopeWithRating(Builder $query, ?string $rating): void
    {
        if (! $rating) {
            return;
        }

        $query->where('rating', $rating);
    }

    public function scopeWithType(Builder $query, ?string $type): void
    {
        if (! $type) {
            return;
        }

        $query->where('type', $type);
    }

    public function scopeForUser(Builder $query, ?User $user): void
    {
        if (! $user) {
            return;
        }

        $query->where(function (Builder $q) use ($user): void {
            $q->where('user_id', $user->id)
                ->orWhereHas('users', fn (Builder $r) => $r->where('users.id', $user->id));
        });
    }

    public function scopeSortBy(Builder $query, ?string $sort, ?string $direction = 'asc'): void
    {
        if (! $sort) {
            $query->orderBy('name', 'asc');

            return;
        }

        $allowedSorts = ['name', 'city', 'state', 'status', 'rating'];

        if (in_array($sort, $allowedSorts, true)) {
            $query->orderBy($sort, $direction === 'desc' ? 'desc' : 'asc');
        } else {
            $query->orderBy('name', 'asc');
        }
    }
}
