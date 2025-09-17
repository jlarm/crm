<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PdfAttachment extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = ['file_name', 'file_path'];

    public function attachable(): MorphMany
    {
        return $this->morphByMany(DealerEmail::class, 'attachable');
    }

    public function attachableTemplate(): MorphMany
    {
        return $this->morphByMany(DealerEmailTemplate::class, 'attachable');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->setDescriptionForEvent(fn (string $eventName): string => "Attachment {$eventName}");
    }
}
