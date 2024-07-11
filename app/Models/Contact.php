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
        'primary_contact',
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

    protected static function boot()
    {
        parent::boot();

        static::created(function ($model) {
            $dealer = Dealership::where('id', $model->dealership_id)->first();
            $dealer->contacts()->where('id', '!=', $model->id)->update(['primary_contact' => false]);

            $model->handleSavedEvent();
        });

        static::deleted(function ($model) {
            $list = Mailcoach::emailList($model->dealership->getListType());
            $sub = $list->subscriber($model->email);
            if ($sub) {
                $sub->delete();
            }
        });
    }

    protected function handleSavedEvent(): void
    {
        $list = Mailcoach::emailList($this->dealership->getListType());

        if ($list->subscriber($this->email) === null) {
            return;
        }

        if ($list->subscriber($this->email)) {
            return;
        }

        if ($this->email) {
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

            $tags[] = auth()->user()->name;

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
}
