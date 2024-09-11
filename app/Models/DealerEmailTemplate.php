<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Storage;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class DealerEmailTemplate extends Model
{
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
}
