<?php

namespace App\Console\Commands;

use App\Mail\DealerEmailMail;
use App\Models\Contact;
use App\Models\DealerEmail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendDealerEmailCommand extends Command
{
    protected $signature = 'dealer:send';

    protected $description = 'Send emails to dealer contacts';

    public function handle(): void
    {
        $emails = DealerEmail::query()
            ->where("paused", false)
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->where('frequency', '>', 0)
                        ->where(function ($innerQ) {
                            $innerQ->whereNull('next_send_date')
                                ->orWhere('next_send_date', '<=', now()->format('Y-m-d'));
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
            }

            $email->last_sent = now()->format('Y-m-d');

            if ($email->frequency > 0) {
                $email->next_send_date = now()->addDays($email->frequency)->format('Y-m-d');
            } else {
                $email->next_send_date = null;
            }

            $email->save();
        }
    }
}
