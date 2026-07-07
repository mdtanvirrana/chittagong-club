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
        'store_id' => env('SSLCOMMERZ_STORE_ID', env('SSLCZ_STORE_ID')),
        'store_password' => env('SSLCOMMERZ_STORE_PASSWORD', env('SSLCZ_STORE_PASSWORD')),
        'sandbox' => env('SSLCOMMERZ_SANDBOX', env('SSLCZ_IS_SANDBOX', true)),
        'currency' => env('SSLCOMMERZ_CURRENCY', 'BDT'),
        'sandbox_url' => env('SSLCOMMERZ_SANDBOX_URL', 'https://sandbox.sslcommerz.com'),
        'live_url' => env('SSLCOMMERZ_LIVE_URL', 'https://securepay.sslcommerz.com'),
        'success_url' => env('SSLCOMMERZ_SUCCESS_URL', env('SSLCZ_SUCCESS_URL')),
        'fail_url' => env('SSLCOMMERZ_FAIL_URL', env('SSLCZ_FAIL_URL')),
        'cancel_url' => env('SSLCOMMERZ_CANCEL_URL', env('SSLCZ_CANCEL_URL')),
        'ipn_url' => env('SSLCOMMERZ_IPN_URL', env('SSLCZ_IPN_URL')),
    ],

    'mobile_app' => [
        'payment_return_url' => env('CCL_APP_PAYMENT_RETURN_URL', 'cclapps://payment-result'),
    ],

    'expo' => [
        'access_token' => env('EXPO_ACCESS_TOKEN'),
    ],

    'robi_sms' => [
        'url' => env('ROBI_SMS_URL', 'https://msg.mram.com.bd/smsapi'),
        'api_key' => env('ROBI_SMS_API_KEY'),
        'type' => env('ROBI_SMS_TYPE', 'text'),
        'sender_id' => env('ROBI_SMS_SENDER_ID', '8809601019288'),
        'timeout' => (int) env('ROBI_SMS_TIMEOUT', 10),
    ],

];
