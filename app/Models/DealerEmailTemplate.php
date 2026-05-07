<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property int $id
 * @property string $name
 * @property string|null $subject
 * @property string|null $body
 * @property string|null $attachment_path
 * @property string|null $attachment_name
 * @property-read Collection<int, PdfAttachment> $pdfAttachments
 * @property-read Collection<int, DealerEmail> $dealerEmails
 */
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

    /**
     * @return MorphToMany<PdfAttachment, $this>
     */
    public function pdfAttachments(): MorphToMany
    {
        return $this->morphToMany(PdfAttachment::class, 'attachable');
    }

    /**
     * @return HasMany<DealerEmail, $this>
     */
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
