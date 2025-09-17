<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Mail\DealerEmailMail;
use App\Models\Contact;
use App\Models\DealerEmail;
use App\Models\SentEmail;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendDealerEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(protected DealerEmail $dealerEmail) {}

    /**
     * Execute the job.
     */
    public function handle(): void
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
                    // Generate a unique tracking ID for this email
                    $trackingId = 'laravel-'.$dealerEmail->id.'-'.md5($recipient.now()->timestamp);

                    // Create sent email record BEFORE sending so tracking works immediately
                    $sentEmail = SentEmail::create([
                        'user_id' => $dealerEmail->user_id,
                        'dealership_id' => $dealerEmail->dealership_id,
                        'recipient' => $recipient,
                        'subject' => $dealerEmail->customize_email ? $dealerEmail->subject : ($dealerEmail->template->subject ?? 'Email'),
                        'message_id' => $trackingId,
                        'tracking_data' => [
                            'sent_at' => now()->toISOString(),
                            'temporary_id' => true,
                            'dealer_email_id' => $dealerEmail->id,
                        ],
                    ]);

                    $mailable = new DealerEmailMail($dealerEmail, $name, $trackingId);

                    $sentMessage = Mail::to($recipient)->send($mailable);

                    \Log::info('Email sent with tracking', [
                        'tracking_id' => $trackingId,
                        'recipient' => $recipient,
                        'sent_email_id' => $sentEmail->id,
                    ]);

                    Log::info('Email sent successfully', [
                        'recipient' => $recipient,
                        'tracking_id' => $trackingId,
                        'dealer_email_id' => $dealerEmail->id,
                    ]);

                } catch (Exception $e) {
                    Log::error('Failed to send dealer email', [
                        'error' => $e->getMessage(),
                        'recipient' => $recipient,
                        'dealer_email_id' => $dealerEmail->id,
                    ]);
                }
            }

            $dealerEmail->last_sent = now()->format('Y-m-d');
            $dealerEmail->save();

        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }
}
