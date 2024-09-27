<?php

namespace App\Jobs;

use App\Models\SentEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\DealerEmailMail;
use Illuminate\Support\Facades\Log;
use App\Models\Contact;
use App\Models\DealerEmail;
use Illuminate\Support\Facades\File;

class SendDealerEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $dealerEmail;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(DealerEmail $dealerEmail)
    {
        $this->dealerEmail = $dealerEmail;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $this->dealerEmail->load('pdfAttachments');

        $this->sendDealerEmails($this->dealerEmail);

    }

    private function sendDealerEmails(DealerEmail $dealerEmail): void
    {
        try {

            if (empty($dealerEmail->recipients)) {
                return;
            }


            foreach ($dealerEmail->recipients as $recipient) {
                $contact = Contact::where('email', $recipient)->first();
                $name = $contact ? $contact->name : '';


                try {
                    Mail::to($recipient)->send(new DealerEmailMail($dealerEmail, $name));

                    SentEmail::create([
                       'user_id' => $dealerEmail->user_id,
                       'dealership_id' => $dealerEmail->dealership_id,
                       'recipient' => $recipient,
                    ]);
                } catch (\Exception $e) {
                    Log::error($e->getMessage());
                }
            }

            $dealerEmail->last_sent = now()->format('Y-m-d');
            $dealerEmail->save();

        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }
}
