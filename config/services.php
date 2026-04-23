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

    'sslcommerz' => [
        'store_id' => env('SSLCZ_STORE_ID'),
        'store_password' => env('SSLCZ_STORE_PASSWORD'),
        'sandbox' => env('SSLCOMMERZ_SANDBOX', true),
        'currency' => env('SSLCOMMERZ_CURRENCY', 'BDT'),
        'sandbox_url' => env('SSLCOMMERZ_SANDBOX_URL', 'https://sandbox.sslcommerz.com'),
        'live_url' => env('SSLCOMMERZ_LIVE_URL', 'https://securepay.sslcommerz.com'),
    ],

    'robi_sms' => [
        'url' => env('ROBI_SMS_URL'),
        'token' => env('ROBI_SMS_TOKEN'),
        'token_header' => env('ROBI_SMS_TOKEN_HEADER', 'Authorization'),
        'sender_id' => env('ROBI_SMS_SENDER_ID'),
        'to_field' => env('ROBI_SMS_TO_FIELD', 'to'),
        'message_field' => env('ROBI_SMS_MESSAGE_FIELD', 'message'),
        'sender_field' => env('ROBI_SMS_SENDER_FIELD', 'sender_id'),
        'timeout' => (int) env('ROBI_SMS_TIMEOUT', 10),
    ],

];
