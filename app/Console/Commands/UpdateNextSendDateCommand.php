<?php

namespace App\Console\Commands;

use App\Models\DealerEmail;
use Illuminate\Console\Command;

class UpdateNextSendDateCommand extends Command
{
    protected $signature = 'update:send';

    protected $description = 'Update the next_send_date';

    public function handle(): void
    {
        $emails = DealerEmail::query()
            ->where('frequency', '>', 0)
            ->where('next_send_date', null)
            ->get();

        foreach ($emails as $email) {
            $email->update([
                'next_send_date' => $email->last_sent->addDays($email->frequency->value)->format('Y-m-d'),
            ]);
        }
    }
}
