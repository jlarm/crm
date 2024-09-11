<?php

namespace App\Observers;

use App\Models\DealerEmail;
use App\Models\Contact;
use Illuminate\Support\Facades\Mail;
use App\Mail\DealerEmailMail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Jobs\SendDealerEmail;

class DealerEmailObserver
{
    public function created(DealerEmail $dealerEmail): void
    {
        // Check if frequency is 0 and start_date is today
        $isFrequencyZero = $dealerEmail->frequency->value === 0;
        $isStartDateToday = Carbon::parse($dealerEmail->start_date)->isToday();

        if ($isFrequencyZero && $isStartDateToday) {
            // Dispatch the job to handle email sending
            SendDealerEmail::dispatch($dealerEmail);
        } else {
            Log::info('Conditions not met for immediate sending', [
                'id' => $dealerEmail->id,
                'frequency' => $dealerEmail->frequency->value,
                'start_date' => $dealerEmail->start_date,
                'current_date' => now()->toDateString()
            ]);
        }
    }

    public function updated(DealerEmail $dealerEmail): void
    {
        if ($dealerEmail->isDirty('attachment') && !is_null($dealerEmail->getOriginal('attachment'))) {
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
