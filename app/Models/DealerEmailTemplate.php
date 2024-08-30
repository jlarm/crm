<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Storage;

class DealerEmailTemplate extends Model
{
    protected $fillable = [
        'name',
        'subject',
        'body',
        'attachment_path',
        'attachment_name',
    ];
}
