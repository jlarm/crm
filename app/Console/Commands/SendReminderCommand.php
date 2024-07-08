<?php

namespace App\Console\Commands;

use App\Mail\ReminderMail;
use App\Models\Reminder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendReminderCommand extends Command
{
    protected $signature = 'reminder:send';

    protected $description = 'Command description';

    public function handle(): void
    {
        $reminders = Reminder::query()
            ->where("pause", false)
            ->orWhere("start_date", now()->format("Y-m-d"))
            ->whereRaw("DATE_ADD(last_sent, INTERVAL sending_frequency DAY) = CURDATE()")
            ->get();

        foreach ($reminders as $reminder) {
            Mail::to($reminder->user->email)->send(new ReminderMail($reminder));
        }
    }
}
