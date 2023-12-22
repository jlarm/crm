<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MailcoachSdk\Facades\Mailcoach;

class Contact extends Model
{
    protected $fillable = [
        'dealership_id',
        'name',
        'email',
        'phone',
        'position',
    ];

    public function dealership(): BelongsTo
    {
        return $this->belongsTo(Dealership::class);
    }

    public function progresses(): HasMany
    {
        return $this->hasMany(Progress::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::saved(function ($model) {
            $model->handleSavedEvent();
        });
    }

    protected function handleSavedEvent(): void
    {
        $name = explode(' ', $this->name);
        $first_name = $name[0];
        $last_name = $name[1];

        $tags = [];
        if ($this->position) {
            $tags[] = $this->position;
        }

        if ($this->dealership->name) {
            $tags[] = $this->dealership->name;
        }

        $sub = Mailcoach::createSubscriber(
            emailListUuid: $this->dealership->getListType(),
            attributes: [
                'first_name' => $first_name,
                'last_name' => $last_name,
                'email' => $this->email,
                'tags' => $tags,
            ]
        );
    }
}
