<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
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

    // Donation bank gateways. Both default to sandbox mode (DonationGatewayFactory
    // routes to MockDonationGateway) until merchant onboarding is complete and
    // NbeDonationGateway/CibDonationGateway are implemented against the bank's
    // integration guide — see those classes for details.
    'nbe' => [
        'sandbox' => env('NBE_SANDBOX', true),
        'merchant_id' => env('NBE_MERCHANT_ID'),
        'api_key' => env('NBE_API_KEY'),
        'webhook_secret' => env('NBE_WEBHOOK_SECRET'),
    ],

    'cib' => [
        'sandbox' => env('CIB_SANDBOX', true),
        'merchant_id' => env('CIB_MERCHANT_ID'),
        'api_key' => env('CIB_API_KEY'),
        'webhook_secret' => env('CIB_WEBHOOK_SECRET'),
    ],

    'youtube' => [
        'channel_id' => env('YOUTUBE_CHANNEL_ID'),
        'webhook_secret' => env('YOUTUBE_WEBHOOK_SECRET'),
        'api_key' => env('YOUTUBE_API_KEY'),
    ],

];
