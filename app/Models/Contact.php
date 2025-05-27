<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\MailcoachSdk\Facades\Mailcoach;
use Illuminate\Support\Facades\Log;

class Contact extends Model
{
    use LogsActivity;

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
        if (!$this->dealership) {
            return;
        }

        try {
            $list = Mailcoach::emailList($this->dealership->getListType());

            if ($list->subscriber($this->email)) {
                return;
            }

            if (empty($this->email)) {
                return;
            }

            $name_parts = explode(' ', trim($this->name ?? ''));
            $first_name = $name_parts[0] ?? '';
            $last_name = $name_parts[1] ?? '';

            if (count($name_parts) > 1) {
                $last_name = implode(' ', array_slice($name_parts, 1));
            }

            $tags = [];
            if (!empty($this->position)) {
                $tags[] = $this->position;
            }

            if ($this->dealership->name) {
                $tags[] = $this->dealership->name;
            }

            if (auth()->check() && auth()->user()) {
                $tags[] = auth()->user()->name;
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
        } catch (\Spatie\MailcoachSdk\Exceptions\ResourceNotFound $e) {
            return;
        } catch(\Exception $e) {
            Log::error('Error in Contact->handleSavedEvent: ' . $e->getMessage());
            return;
        }
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->setDescriptionForEvent(fn (string $eventName): string => "Contact {$eventName}");
    }
}
