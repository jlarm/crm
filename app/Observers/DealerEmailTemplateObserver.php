<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\DealerEmailTemplate;
use Storage;

class DealerEmailTemplateObserver
{
    public function updated(DealerEmailTemplate $dealerEmailTemplate): void
    {
        if ($dealerEmailTemplate->isDirty('attachment_path')) {
            Storage::disk('public')->delete($dealerEmailTemplate->getOriginal('attachment'));
        }
    }

    public function deleted(DealerEmailTemplate $dealerEmailTemplate): void
    {
        if (! is_null($dealerEmailTemplate->attachment_path)) {
            Storage::disk('public')->delete($dealerEmailTemplate->attachment_path);
        }
    }
}
