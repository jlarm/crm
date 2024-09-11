<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
class PdfAttachment extends Model
{
    use HasFactory;

    protected $fillable = ['file_name', 'file_path'];

    public function attachable(): MorphMany
    {
        return $this->morphByMany(DealerEmail::class, 'attachable');
    }

    public function attachableTemplate(): MorphMany
    {
        return $this->morphByMany(DealerEmailTemplate::class, 'attachable');
    }
}
