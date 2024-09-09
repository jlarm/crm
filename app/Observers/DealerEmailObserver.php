<?php

namespace App\Observers;

use App\Models\DealerEmail;
use App\Models\Contact;
use Illuminate\Support\Facades\Mail;
use App\Mail\DealerEmailMail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DealerEmailObserver
{
    public function created(DealerEmail $dealerEmail): void
    {
        $isFrequencyZero = $dealerEmail->frequency->value === 0;
        $isStartDateToday = Carbon::parse($dealerEmail->start_date)->isToday();

        if ($isFrequencyZero && $isStartDateToday) {
            Log::info('Attempting to send dealer emails', ['id' => $dealerEmail->id]);
            $this->sendDealerEmails($dealerEmail);
        } else {
            Log::info('Conditions not met for immediate sending', [
                'id' => $dealerEmail->id,
                'frequency' => $dealerEmail->frequency,
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

    private function sendDealerEmails(DealerEmail $dealerEmail): void
    {
        try {
            if (empty($dealerEmail->recipients)) {
                Log::warning('No recipients found for DealerEmail', ['id' => $dealerEmail->id]);
                return;
            }

            foreach ($dealerEmail->recipients as $recipient) {
                $contact = Contact::where('email', $recipient)->first();
                $name = $contact ? $contact->name : '';

                try {
                    Mail::to($recipient)->send(new DealerEmailMail($dealerEmail, $name));
                    Log::info('Email sent successfully', ['email' => $recipient]);
                } catch (\Exception $e) {
                    Log::error('Failed to send email to recipient', ['email' => $recipient, 'error' => $e->getMessage()]);
                }
            }

            // Update last_sent date
            $dealerEmail->last_sent = now()->format('Y-m-d');
            $dealerEmail->save();
            Log::info('DealerEmail last_sent updated', ['id' => $dealerEmail->id, 'last_sent' => $dealerEmail->last_sent]);
        } catch (\Exception $e) {
            Log::error('Error in sendDealerEmails', ['id' => $dealerEmail->id, 'error' => $e->getMessage()]);
        }
    }
}
