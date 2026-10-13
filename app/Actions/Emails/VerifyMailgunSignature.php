<?php

declare(strict_types=1);

namespace App\Actions\Emails;

/**
 * Checks that a webhook really came from Mailgun and isn't a replay of an old one.
 */
final class VerifyMailgunSignature
{
    private const int MAX_AGE_SECONDS = 15 * 60;

    public function __invoke(mixed $timestamp, mixed $token, mixed $signature): bool
    {
        $signingKey = config('services.mailgun.webhook_signing_key');

        if (! is_string($signingKey) || $signingKey === '' || ! is_string($token) || ! is_string($signature) || ! is_numeric($timestamp)) {
            return false;
        }

        if (abs(now()->timestamp - (int) $timestamp) > self::MAX_AGE_SECONDS) {
            return false;
        }

        return hash_equals(hash_hmac('sha256', $timestamp.$token, $signingKey), $signature);
    }
}
