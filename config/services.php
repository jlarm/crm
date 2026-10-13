<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
        'webhook_signing_key' => env('MAILGUN_WEBHOOK_SIGNING_KEY'),
        // Replies to dealership emails go to reply+{token}@ this domain, which a Mailgun route posts back to the CRM.
        'inbound_domain' => env('MAILGUN_INBOUND_DOMAIN'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'claude' => [
        'api_key' => env('CLAUDE_API_KEY'),
        'api_url' => env('CLAUDE_API_URL', 'https://api.anthropic.com/v1/messages'),
    ],

];
