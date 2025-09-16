<?php

namespace App\Jobs;

use App\Models\SentEmail;
use App\Services\EmailTrackingService;
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
                    $mailable = new DealerEmailMail($dealerEmail, $name);
                    $sentMessage = Mail::to($recipient)->send($mailable);

                    // Generate a unique tracking ID for this email
                    $trackingId = 'laravel-' . $dealerEmail->id . '-' . md5($recipient . now()->timestamp);

                    // Create sent email record - Mailgun will update with real message ID via webhook
                    SentEmail::create([
                        'user_id' => $dealerEmail->user_id,
                        'dealership_id' => $dealerEmail->dealership_id,
                        'recipient' => $recipient,
                        'subject' => $mailable->subject,
                        'message_id' => $trackingId, // Temporary ID, will be updated by webhook
                        'tracking_data' => [
                            'sent_at' => now()->toISOString(),
                            'temporary_id' => true,
                            'dealer_email_id' => $dealerEmail->id,
                        ],
                    ]);

                    Log::info('Email sent successfully', [
                        'recipient' => $recipient,
                        'tracking_id' => $trackingId,
                        'dealer_email_id' => $dealerEmail->id,
                    ]);

                } catch (\Exception $e) {
                    Log::error('Failed to send dealer email', [
                        'error' => $e->getMessage(),
                        'recipient' => $recipient,
                        'dealer_email_id' => $dealerEmail->id,
                    ]);
                }
            }

            $dealerEmail->last_sent = now()->format('Y-m-d');
            $dealerEmail->save();

        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }
}
