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

    'mailcoach' => [
        'lists' => [
            'automotive' => env('MAILCOACH_LIST_AUTOMOTIVE', 'f694f7fd-dbb9-489d-bced-03e2fbee78af'),
            'rv' => env('MAILCOACH_LIST_RV', '2d97d6ea-90a0-4b49-90df-980a258884b2'),
            'motorsports' => env('MAILCOACH_LIST_MOTORSPORTS', 'd2a68b06-08e4-4e76-a714-151e07a5a907'),
            'maritime' => env('MAILCOACH_LIST_MARITIME', '59c46030-5429-4ffd-a192-42926b9b17eb'),
        ],
    ],

];
