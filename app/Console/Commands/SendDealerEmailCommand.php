<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Mail\DealerEmailMail;
use App\Models\Contact;
use App\Models\DealerEmail;
use App\Models\SentEmail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendDealerEmailCommand extends Command
{
    protected $signature = 'dealer:send';

    protected $description = 'Send emails to dealer contacts';

    public function handle(): void
    {
        $emails = DealerEmail::query()
            ->where('paused', false)
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->where('frequency', '>', 0)
                        ->where(function ($innerQ) {
                            $innerQ->whereNull('next_send_date')
                                ->orWhere('next_send_date', '=', now()->format('Y-m-d'));
                        });
                })->orWhere(function ($q) {
                    $q->where('frequency', 0)
                        ->whereNull('last_sent');
                });
            })
            ->get();

        foreach ($emails as $email) {
            foreach ($email->recipients as $recipient) {
                $name = Contact::where('email', $recipient)->first()->name;
                Mail::to($recipient)->send(new DealerEmailMail($email, $name));
                SentEmail::create([
                    'user_id' => $email->user_id,
                    'dealership_id' => $email->dealership_id,
                    'recipient' => $recipient,
                ]);
            }

            $email->update([
                'last_sent' => now()->format('Y-m-d'),
                'next_send_date' => $email->frequency->value > 0 ? now()->addDays($email->frequency->value)->format('Y-m-d') : null,
            ]);
        }
    }
}
