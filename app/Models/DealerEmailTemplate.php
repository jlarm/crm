<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Storage;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class DealerEmailTemplate extends Model
{
    use LogsActivity;
    
    protected $fillable = [
        'name',
        'subject',
        'body',
        'attachment_path',
        'attachment_name',
    ];

    public function pdfAttachments(): MorphToMany
    {
        return $this->morphToMany(PdfAttachment::class, 'attachable');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->setDescriptionForEvent(fn (string $eventName): string => "Dealer Email Template {$eventName}");
    }
}
