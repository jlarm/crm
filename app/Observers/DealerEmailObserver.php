<?php

declare(strict_types=1);

namespace App\Observers;

use App\Jobs\SendDealerEmail;
use App\Models\DealerEmail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DealerEmailObserver
{
    public function created(DealerEmail $dealerEmail): void
    {
        $frequencyValue = $dealerEmail->frequency?->value;
        $isFrequencyZero = $frequencyValue === 0;
        $isStartDateToday = Carbon::parse($dealerEmail->start_date)->isToday();

        if ($isFrequencyZero && $isStartDateToday) {
            Log::info('Sending one off email');
            SendDealerEmail::dispatch($dealerEmail);
        } else {
            Log::info('Conditions not met for immediate sending', [
                'id' => $dealerEmail->id,
                'frequency' => $frequencyValue,
                'start_date' => $dealerEmail->start_date,
                'current_date' => now()->toDateString(),
            ]);
        }
    }

    public function updated(DealerEmail $dealerEmail): void
    {
        $original = $dealerEmail->getOriginal('attachment');
        if ($dealerEmail->isDirty('attachment') && is_string($original)) {
            Storage::disk('public')->delete($original);
        }
    }

    public function deleted(DealerEmail $dealerEmail): void
    {
        if (! is_null($dealerEmail->attachment)) {
            Storage::disk('public')->delete($dealerEmail->attachment);
        }
    }
}
