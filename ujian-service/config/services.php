<?php

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

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | SIAKAD Integration
    |--------------------------------------------------------------------------
    |
    | Configuration for integration with main SIAKAD application
    | Used for syncing siswa data and reference data (kelas, mapel)
    |
    */

    'siakad' => [
        'base_url' => env('SIAKAD_BASE_URL', 'http://localhost:8000'),
        'service_key' => env('SIAKAD_SERVICE_KEY', ''),
        'sync_enabled' => env('SIAKAD_SYNC_ENABLED', true),
        'sync_interval' => env('SIAKAD_SYNC_INTERVAL', 3600), // seconds
    ],

];