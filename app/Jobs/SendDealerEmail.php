<?php

namespace App\Jobs;

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
        $this->debugLog('SendDealerEmail job started');

        $this->dealerEmail->load('pdfAttachments');
        $this->debugLog('PDF attachments loaded');

        $this->sendDealerEmails($this->dealerEmail);

        $this->debugLog('SendDealerEmail job completed');
    }

    private function sendDealerEmails(DealerEmail $dealerEmail): void
    {
        try {
            $this->debugLog('sendDealerEmails method started');

            if (empty($dealerEmail->recipients)) {
                $this->debugLog('No recipients found, exiting sendDealerEmails');
                return;
            }

            $this->debugLog('Attempting to send one-off emails. Recipient count: ' . count($dealerEmail->recipients));

            foreach ($dealerEmail->recipients as $recipient) {
                $contact = Contact::where('email', $recipient)->first();
                $name = $contact ? $contact->name : '';

                $this->debugLog("Preparing to send email to: $recipient, Name: $name");

                try {
                    Mail::to($recipient)->send(new DealerEmailMail($dealerEmail, $name));
                    $this->debugLog("Successfully sent email to: $recipient");
                } catch (\Exception $e) {
                    $this->debugLog("Failed to send email to $recipient. Error: " . $e->getMessage(), 'error');
                }
            }

            $dealerEmail->last_sent = now()->format('Y-m-d');
            $dealerEmail->save();
            $this->debugLog('Updated last_sent date: ' . $dealerEmail->last_sent);

        } catch (\Exception $e) {
            $this->debugLog('Error in sendDealerEmails: ' . $e->getMessage(), 'error');
        }
    }

    private function debugLog($message, $level = 'info')
    {
        // Attempt to log using Laravel's Log facade
        Log::$level($message, ['dealer_email_id' => $this->dealerEmail->id]);

        // Attempt to write directly to a file
        $logPath = storage_path('logs/dealer_email_debug.log');
        $logMessage = '[' . now() . '] ' . strtoupper($level) . ': ' . $message . ' [dealer_email_id: ' . $this->dealerEmail->id . "]\n";
        File::append($logPath, $logMessage);

        // Attempt to use PHP's error_log function
        error_log($logMessage);
    }
}
