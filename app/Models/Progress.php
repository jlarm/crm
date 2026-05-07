<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property int $id
 * @property int|null $user_id
 * @property int|null $dealership_id
 * @property int|null $contact_id
 * @property int|null $progress_category_id
 * @property string|null $details
 * @property Carbon|null $date
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read User|null $user
 * @property-read Dealership|null $dealership
 * @property-read Contact|null $contact
 * @property-read ProgressCategory|null $category
 */
class Progress extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'progresses';

    protected $fillable = [
        'user_id',
        'dealership_id',
        'contact_id',
        'details',
        'date',
        'progress_category_id',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
     * @return BelongsTo<ProgressCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ProgressCategory::class, 'progress_category_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->setDescriptionForEvent(fn (string $eventName): string => "Progress {$eventName}");
    }
}
