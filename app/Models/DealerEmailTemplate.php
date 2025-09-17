<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class DealerEmailTemplate extends Model
{
    use HasFactory, LogsActivity;

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

    public function dealerEmails(): HasMany
    {
        return $this->hasMany(DealerEmail::class, 'dealer_email_template_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->setDescriptionForEvent(fn (string $eventName): string => "Dealer Email Template {$eventName}");
    }
}
