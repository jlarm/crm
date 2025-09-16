<?php

namespace App\Http\Controllers;

use App\Models\EmailTrackingEvent;
use App\Models\SentEmail;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

class MailgunWebhookController extends Controller
{
    public function handleEvent(Request $request): Response
    {
        try {
            // Verify webhook signature (optional but recommended)
            if (!$this->verifySignature($request)) {
                Log::warning('Invalid Mailgun webhook signature', ['data' => $request->all()]);
                return response('Unauthorized', 401);
            }

            $eventData = $request->input('event-data');

            if (!$eventData) {
                Log::warning('No event-data in Mailgun webhook', ['data' => $request->all()]);
                return response('Bad Request', 400);
            }

            $this->processEvent($eventData);

            return response('OK', 200);
        } catch (\Exception $e) {
            Log::error('Error processing Mailgun webhook', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return response('Internal Server Error', 500);
        }
    }

    private function processEvent(array $eventData): void
    {
        $messageId = $eventData['message']['headers']['message-id'] ?? null;
        $event = $eventData['event'] ?? null;
        $timestamp = $eventData['timestamp'] ?? now()->timestamp;
        $recipient = $eventData['recipient'] ?? null;

        if (!$messageId || !$event || !$recipient) {
            Log::warning('Missing required data in Mailgun event', ['eventData' => $eventData]);
            return;
        }

        // Find the sent email record by Mailgun message ID
        $sentEmail = SentEmail::where('message_id', $messageId)->first();

        // If not found, try to find by recipient and recent timestamp for temporary IDs
        if (!$sentEmail) {
            $sentEmail = SentEmail::where('recipient', $recipient)
                ->where('created_at', '>=', now()->subHour()) // Within last hour
                ->whereJsonContains('tracking_data->temporary_id', true)
                ->first();

            // Update with real Mailgun message ID if found
            if ($sentEmail) {
                $sentEmail->update([
                    'message_id' => $messageId,
                    'tracking_data' => array_merge($sentEmail->tracking_data ?? [], [
                        'temporary_id' => false,
                        'mailgun_message_id' => $messageId,
                        'updated_at' => now()->toISOString(),
                    ])
                ]);

                Log::info('Updated sent email with real Mailgun message ID', [
                    'sent_email_id' => $sentEmail->id,
                    'old_message_id' => $sentEmail->getOriginal('message_id'),
                    'new_message_id' => $messageId,
                ]);
            }
        }

        if (!$sentEmail) {
            Log::warning('No matching sent email found', [
                'message_id' => $messageId,
                'recipient' => $recipient,
                'event' => $event
            ]);
            return;
        }

        // Map Mailgun events to our event types
        $eventType = $this->mapEventType($event);

        if (!$eventType) {
            Log::info('Unmapped event type', ['event' => $event]);
            return;
        }

        // Create tracking event
        EmailTrackingEvent::create([
            'sent_email_id' => $sentEmail->id,
            'event_type' => $eventType,
            'message_id' => $messageId,
            'recipient_email' => $recipient,
            'url' => $eventData['url'] ?? null,
            'user_agent' => $eventData['user-agent'] ?? null,
            'ip_address' => $eventData['ip'] ?? null,
            'mailgun_data' => $eventData,
            'event_timestamp' => \Carbon\Carbon::createFromTimestamp($timestamp),
        ]);

        Log::info('Email tracking event recorded', [
            'event_type' => $eventType,
            'message_id' => $messageId,
            'recipient' => $recipient,
            'sent_email_id' => $sentEmail->id,
        ]);
    }

    private function mapEventType(string $mailgunEvent): ?string
    {
        return match ($mailgunEvent) {
            'delivered' => EmailTrackingEvent::EVENT_DELIVERED,
            'opened' => EmailTrackingEvent::EVENT_OPENED,
            'clicked' => EmailTrackingEvent::EVENT_CLICKED,
            'permanent_fail', 'failed' => EmailTrackingEvent::EVENT_BOUNCED,
            'complained' => EmailTrackingEvent::EVENT_COMPLAINED,
            'unsubscribed' => EmailTrackingEvent::EVENT_UNSUBSCRIBED,
            default => null,
        };
    }

    private function verifySignature(Request $request): bool
    {
        // Get webhook signing key from config
        $signingKey = config('services.mailgun.webhook_signing_key');

        if (!$signingKey) {
            // If no signing key configured, skip verification
            return true;
        }

        $signature = $request->input('signature');

        if (!$signature) {
            return false;
        }

        $timestamp = $signature['timestamp'] ?? '';
        $token = $signature['token'] ?? '';
        $providedSignature = $signature['signature'] ?? '';

        $expectedSignature = hash_hmac('sha256', $timestamp . $token, $signingKey);

        return hash_equals($expectedSignature, $providedSignature);
    }

    public function trackOpen(Request $request, string $messageId): Response
    {
        try {
            \Log::info('Tracking pixel accessed', [
                'message_id' => $messageId,
                'user_agent' => $request->userAgent(),
                'ip' => $request->ip()
            ]);

            $sentEmail = SentEmail::where('message_id', $messageId)->first();

            if ($sentEmail) {
                \Log::info('Found SentEmail record', ['sent_email_id' => $sentEmail->id]);

                // Create tracking event for manual pixel tracking
                $trackingEvent = EmailTrackingEvent::firstOrCreate([
                    'sent_email_id' => $sentEmail->id,
                    'message_id' => $messageId,
                    'recipient_email' => $sentEmail->recipient,
                    'event_type' => EmailTrackingEvent::EVENT_OPENED,
                ], [
                    'user_agent' => $request->userAgent(),
                    'ip_address' => $request->ip(),
                    'event_timestamp' => now(),
                    'mailgun_data' => [
                        'source' => 'tracking_pixel',
                        'user_agent' => $request->userAgent(),
                        'ip' => $request->ip(),
                    ],
                ]);

                \Log::info('Tracking event created/found', ['event_id' => $trackingEvent->id]);
            } else {
                \Log::warning('No SentEmail found for message_id', ['message_id' => $messageId]);
            }

            // Return a 1x1 transparent pixel
            return response(base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg=='))
                ->header('Content-Type', 'image/png')
                ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
                ->header('Pragma', 'no-cache');
        } catch (\Exception $e) {
            Log::error('Error tracking email open', [
                'message_id' => $messageId,
                'error' => $e->getMessage()
            ]);

            // Still return the pixel even if tracking fails
            return response(base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg=='))
                ->header('Content-Type', 'image/png');
        }
    }

    public function trackClick(Request $request, string $messageId): RedirectResponse|Response
    {
        try {
            $url = $request->query('url');

            if (!$url) {
                return response('Bad Request', 400);
            }

            $decodedUrl = urldecode($url);
            $sentEmail = SentEmail::where('message_id', $messageId)->first();

            if ($sentEmail) {
                // Create tracking event for click
                EmailTrackingEvent::create([
                    'sent_email_id' => $sentEmail->id,
                    'message_id' => $messageId,
                    'recipient_email' => $sentEmail->recipient,
                    'event_type' => EmailTrackingEvent::EVENT_CLICKED,
                    'url' => $decodedUrl,
                    'user_agent' => $request->userAgent(),
                    'ip_address' => $request->ip(),
                    'event_timestamp' => now(),
                    'mailgun_data' => [
                        'source' => 'click_tracking',
                        'user_agent' => $request->userAgent(),
                        'ip' => $request->ip(),
                        'original_url' => $decodedUrl,
                    ],
                ]);
            }

            // Redirect to the original URL
            return redirect($decodedUrl);
        } catch (\Exception $e) {
            Log::error('Error tracking email click', [
                'message_id' => $messageId,
                'error' => $e->getMessage()
            ]);

            // Redirect to the URL even if tracking fails
            $url = $request->query('url');
            if ($url) {
                return redirect(urldecode($url));
            }

            return response('Error', 500);
        }
    }
}
