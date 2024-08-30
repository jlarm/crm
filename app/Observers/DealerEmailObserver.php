<?php

namespace App\Observers;

use App\Models\DealerEmail;
use Storage;

class DealerEmailObserver
{
    public function updated(DealerEmail $dealerEmail): void
    {
        if ($dealerEmail->isDirty('attachment')) {
            Storage::disk('public')->delete($dealerEmail->getOriginal('attachment'));
        }
    }

    public function deleted(DealerEmail $dealerEmail): void
    {
        if (! is_null($dealerEmail->attachment)) {
            Storage::disk('public')->delete($dealerEmail->attachment);
        }
    }
}
