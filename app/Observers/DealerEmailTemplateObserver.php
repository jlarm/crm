<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\DealerEmailTemplate;
use Storage;

class DealerEmailTemplateObserver
{
    public function updated(DealerEmailTemplate $dealerEmailTemplate): void
    {
        $original = $dealerEmailTemplate->getOriginal('attachment');
        if ($dealerEmailTemplate->isDirty('attachment_path') && is_string($original)) {
            Storage::disk('public')->delete($original);
        }
    }

    public function deleted(DealerEmailTemplate $dealerEmailTemplate): void
    {
        if (! is_null($dealerEmailTemplate->attachment_path)) {
            Storage::disk('public')->delete($dealerEmailTemplate->attachment_path);
        }
    }
}
