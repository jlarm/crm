<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property int $id
 * @property string $name
 */
class Tag extends Model
{
    /** @use HasFactory<\Database\Factories\TagFactory> */
    use HasFactory;

    use LogsActivity;

    public const int MAX_NAME_LENGTH = 50;

    protected $fillable = [
        'name',
    ];

    protected $casts = [
        'name' => 'string',
    ];

    /**
     * Ids for the named tags, creating any that don't exist yet. Names match case-insensitively,
     * so "trade show" reuses an existing "Trade Show" tag.
     *
     * @param  array<int, mixed>  $names
     * @return list<int>
     */
    public static function idsForNames(array $names): array
    {
        $wanted = [];

        foreach ($names as $name) {
            if (! is_string($name)) {
                continue;
            }

            $name = Str::squish($name);

            if ($name !== '') {
                $wanted[mb_strtolower($name)] ??= $name;
            }
        }

        if ($wanted === []) {
            return [];
        }

        $existing = [];

        foreach (self::query()->whereIn(DB::raw('LOWER(name)'), array_keys($wanted))->orderBy('id')->get(['id', 'name']) as $tag) {
            $existing[mb_strtolower($tag->name)] ??= $tag->id;
        }

        $ids = [];

        foreach ($wanted as $key => $name) {
            $ids[] = $existing[$key] ?? self::create(['name' => $name])->id;
        }

        return $ids;
    }

    /**
     * Names of the tags in use on dealerships, for filters and suggestions.
     *
     * @return list<string>
     */
    public static function dealershipTagNames(): array
    {
        $names = [];

        foreach (self::query()->whereHas('dealerships')->orderBy('name')->get(['name']) as $tag) {
            $names[mb_strtolower($tag->name)] ??= $tag->name;
        }

        return array_values($names);
    }

    /**
     * @return BelongsToMany<Contact, $this>
     */
    public function contacts(): BelongsToMany
    {
        return $this->belongsToMany(Contact::class);
    }

    /**
     * @return BelongsToMany<Dealership, $this>
     */
    public function dealerships(): BelongsToMany
    {
        return $this->belongsToMany(Dealership::class)->withTimestamps();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->setDescriptionForEvent(fn (string $eventName): string => 'Tag '.$eventName);
    }
}
