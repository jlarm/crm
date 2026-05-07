<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property int $id
 * @property string $file_name
 * @property string $file_path
 */
class PdfAttachment extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = ['file_name', 'file_path'];

    /**
     * @return MorphToMany<DealerEmail, $this>
     */
    public function attachable(): MorphToMany
    {
        return $this->morphedByMany(DealerEmail::class, 'attachable');
    }

    /**
     * @return MorphToMany<DealerEmailTemplate, $this>
     */
    public function attachableTemplate(): MorphToMany
    {
        return $this->morphedByMany(DealerEmailTemplate::class, 'attachable');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->setDescriptionForEvent(fn (string $eventName): string => "Attachment {$eventName}");
    }
}
