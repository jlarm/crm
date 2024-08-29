<?php

namespace App\Console\Commands;

use App\Mail\DealerEmailMail;
use App\Models\DealerEmail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SendDealerEmailCommand extends Command
{
    protected $signature = 'dealer:send';

    protected $description = 'Send emails to dealer contacts';

    public function handle(): void
    {
        $this->info('Sending dealer emails...');
        $today = now()->format('Y-m-d');

        $emails = DealerEmail::query()
            ->where("paused", false)
            ->where(function ($query) use ($today) {
                $query
                    ->whereDate("start_date", now()->toDateString())
                    ->orWhere(function ($subQuery) use ($today) {
                        $subQuery->whereRaw(
                            DB::connection()->getDriverName() === "sqlite"
                                ? "DATE(julianday(last_sent) + frequency) = ?"
                                : "DATE_ADD(last_sent, INTERVAL frequency DAY) = ?",
                            [$today]
                        );
                    });
            })
            ->get();

        foreach ($emails as $email) {
            $this->info("Sending email with subject: $email->subject...");
            foreach ($email->recipients as $recipient) {
                $this->info("Sending email to $recipient...");
                Mail::to($recipient)->send(new DealerEmailMail($email));
                $email->update(['last_sent' => now()->format('Y-m-d')]);
            }
        }
        $this->info('Dealer emails sent!');
    }
}
