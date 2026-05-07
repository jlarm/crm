<?php

declare(strict_types=1);

namespace App\Models;

use App\Enum\DevStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Laravel\Scout\Searchable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property int $id
 * @property int|null $user_id
 * @property string $name
 * @property string|null $address
 * @property string|null $city
 * @property string|null $state
 * @property string|null $zip_code
 * @property string|null $phone
 * @property string|null $email
 * @property string|null $current_solution_name
 * @property string|null $current_solution_use
 * @property string|null $notes
 * @property string|null $status
 * @property string|null $rating
 * @property string|null $type
 * @property bool $in_development
 * @property DevStatus|null $dev_status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Collection<int, Store> $stores
 * @property-read Collection<int, Contact> $contacts
 * @property-read Collection<int, Task> $tasks
 * @property-read Collection<int, Progress> $progresses
 * @property-read Collection<int, Opportunity> $opportunities
 * @property-read Collection<int, DealerEmail> $dealerEmails
 * @property-read Collection<int, SentEmail> $sentEmails
 * @property-read Collection<int, User> $users
 * @property-read int|null $open_tasks_count
 * @property-read int $total_store_count
 */
class Dealership extends Model
{
    /** @use HasFactory<\Database\Factories\DealershipFactory> */
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

    /**
     * @return BelongsToMany<User, $this>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * @return HasMany<Store, $this>
     */
    public function stores(): HasMany
    {
        return $this->hasMany(Store::class);
    }

    /**
     * @return HasMany<Contact, $this>
     */
    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    /**
     * @return HasMany<Task, $this>
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /**
     * @return HasMany<Progress, $this>
     */
    public function progresses(): HasMany
    {
        return $this->hasMany(Progress::class);
    }

    /**
     * @return HasMany<Opportunity, $this>
     */
    public function opportunities(): HasMany
    {
        return $this->hasMany(Opportunity::class);
    }

    /**
     * @return HasMany<DealerEmail, $this>
     */
    public function dealerEmails(): HasMany
    {
        return $this->hasMany(DealerEmail::class);
    }

    /**
     * @return HasMany<SentEmail, $this>
     */
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

        $value = $types[$this->type] ?? null;

        return is_string($value) ? $value : 'default_value';
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

    /**
     * @param  Builder<self>  $query
     */
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

    /**
     * @param  Builder<self>  $query
     */
    public function scopeWithRating(Builder $query, ?string $rating): void
    {
        if (! $rating) {
            return;
        }

        $query->where('rating', $rating);
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

    /**
     * @param  Builder<self>  $query
     */
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

    /**
     * @param  Builder<self>  $query
     */
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
