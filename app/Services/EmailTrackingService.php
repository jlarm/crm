<?php

namespace App\Services;

use App\Models\SentEmail;
use Illuminate\Mail\SentMessage;
use Illuminate\Support\Facades\Log;

class EmailTrackingService
{
    public function recordSentEmail(
        SentMessage $sentMessage,
        int $userId,
        int $dealershipId,
        string $recipient,
        string $subject
    ): ?SentEmail {
        try {
            // Extract message ID from the sent message
            $messageId = $this->extractMessageId($sentMessage);

            if (!$messageId) {
                Log::warning('Could not extract message ID from sent email', [
                    'recipient' => $recipient,
                    'subject' => $subject
                ]);
                return null;
            }

            return SentEmail::create([
                'user_id' => $userId,
                'dealership_id' => $dealershipId,
                'recipient' => $recipient,
                'message_id' => $messageId,
                'subject' => $subject,
                'tracking_data' => [
                    'sent_at' => now()->toISOString(),
                    'transport' => $sentMessage->getSymfonySentMessage()?->getTransport()?->__toString(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to record sent email', [
                'error' => $e->getMessage(),
                'recipient' => $recipient,
                'user_id' => $userId,
            ]);

            return null;
        }
    }

    private function extractMessageId(SentMessage $sentMessage): ?string
    {
        try {
            // Try to get from the original message first
            $originalMessage = $sentMessage->getOriginalMessage();

            if ($originalMessage && $originalMessage->getHeaders()->has('Message-ID')) {
                $messageId = $originalMessage->getHeaders()->get('Message-ID')->getBody();
                return trim($messageId, '<>'); // Remove angle brackets if present
            }

            // Try to get from Symfony sent message
            $symphonyMessage = $sentMessage->getSymfonySentMessage();

            if ($symphonyMessage) {
                // For Mailgun, we can try to get it from the envelope or debug info
                $envelope = $symphonyMessage->getEnvelope();
                if ($envelope) {
                    // Generate a unique ID based on envelope data
                    $sender = $envelope->getSender()->getAddress();
                    $recipients = implode(',', array_map(fn($r) => $r->getAddress(), $envelope->getRecipients()));
                    return 'laravel-' . md5($sender . $recipients . now()->timestamp);
                }
            }

            // Fallback: generate a unique ID
            return 'laravel-' . uniqid() . '-' . time();

        } catch (\Exception $e) {
            Log::error('Error extracting message ID', ['error' => $e->getMessage()]);

            // Final fallback
            return 'fallback-' . uniqid() . '-' . time();
        }
    }

    public function addTrackingPixel(string $emailContent, string $messageId): string
    {
        // Add a tracking pixel to the email content
        $trackingPixel = $this->generateTrackingPixel($messageId);

        // Insert before closing body tag if it exists, otherwise append
        if (strpos($emailContent, '</body>') !== false) {
            return str_replace('</body>', $trackingPixel . '</body>', $emailContent);
        }

        return $emailContent . $trackingPixel;
    }

    public function wrapLinksWithTracking(string $emailContent, string $messageId): string
    {
        // This is a basic implementation - in production you might want a more sophisticated approach
        $pattern = '/href="([^"]+)"/i';

        return preg_replace_callback($pattern, function ($matches) use ($messageId) {
            $originalUrl = $matches[1];

            // Skip if it's already a tracking URL or certain types of links
            if (strpos($originalUrl, 'track-click') !== false ||
                strpos($originalUrl, 'mailto:') === 0 ||
                strpos($originalUrl, 'tel:') === 0) {
                return $matches[0];
            }

            $trackingUrl = route('mailgun.click-track', [
                'message_id' => $messageId,
                'url' => urlencode($originalUrl)
            ]);

            return 'href="' . $trackingUrl . '"';
        }, $emailContent);
    }

    private function generateTrackingPixel(string $messageId): string
    {
        $trackingUrl = route('mailgun.open-track', ['message_id' => $messageId]);

        return sprintf(
            '<img src="%s" width="1" height="1" style="display:none;" alt="" />',
            $trackingUrl
        );
    }
}