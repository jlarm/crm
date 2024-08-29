<?php

namespace App\Console\Commands;

use App\Mail\DealerEmailMail;
use App\Models\DealerEmail;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SendDealerEmailCommand extends Command
{
    protected $signature = 'dealer:send';

    protected $description = 'Send emails to dealer contacts';

    public function handle(): void
    {
        $emails = DealerEmail::query()
            ->where("paused", false)
            ->get();

        foreach ($emails as $email) {
            if ($email->start_date->isToday() || $email->last_sent->addDays($email->frequency->value)->isToday()) {
                foreach ($email->recipients as $recipient) {
                    Mail::to($recipient)->send(new DealerEmailMail($email));
                    $email->update(['last_sent' => now()->format('Y-m-d')]);
                }
            }
        }
    }
}
