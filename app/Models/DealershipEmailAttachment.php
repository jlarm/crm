<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A file sent with, or received on, a dealership email. Stored on the private local disk.
 *
 * @property int $id
 * @property int $dealership_email_id
 * @property string $filename
 * @property string $path
 * @property string|null $mime_type
 * @property int $size
 * @property-read DealershipEmail $email
 */
class DealershipEmailAttachment extends Model
{
    protected $fillable = [
        'dealership_email_id',
        'filename',
        'path',
        'mime_type',
        'size',
    ];

    protected $casts = [
        'size' => 'integer',
    ];

    /**
     * @return BelongsTo<DealershipEmail, $this>
     */
    public function email(): BelongsTo
    {
        return $this->belongsTo(DealershipEmail::class, 'dealership_email_id');
    }
}
