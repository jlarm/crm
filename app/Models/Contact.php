<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Contact extends Model
{
    use LogsActivity;

    /**
     * The relationships that should touch the parent model when updated.
     *
     * @var array
     */
    protected $touches = ['tags'];

    protected $fillable = [
        'dealership_id',
        'name',
        'email',
        'phone',
        'position',
        'primary_contact',
        'linkedin_link'
    ];

    protected $casts = [
        'primary_contact' => 'boolean',
    ];

    public function dealership(): BelongsTo
    {
        return $this->belongsTo(Dealership::class);
    }

    public function progresses(): HasMany
    {
        return $this->hasMany(Progress::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->setDescriptionForEvent(fn (string $eventName): string => "Contact {$eventName}");
    }
}
