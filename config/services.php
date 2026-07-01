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

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // QRIS Dinamis (qris.online / OkeConnect)
    'qris' => [
        'base_url' => env('QRIS_BASE_URL', 'https://qris.interactive.co.id/restapi/qris'),
        'mid' => env('QRIS_MID'),
        'nmid' => env('QRIS_NMID'),
        'apikey' => env('QRIS_APIKEY'),
        // Masa berlaku QR (menit)
        'expiry_minutes' => (int) env('QRIS_EXPIRY_MINUTES', 30),
    ],

];
