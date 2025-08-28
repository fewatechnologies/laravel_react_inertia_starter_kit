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

    /*
    |--------------------------------------------------------------------------
    | Aakash SMS Service Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Aakash SMS API service used for sending SMS
    | notifications and OTP verification in the multi-dashboard system.
    |
    */

    'aakash_sms' => [
        'base_url' => env('AAKASH_SMS_BASE_URL', 'https://sms.aakashsms.com/sms/'),
        'token' => env('AAKASH_SMS_TOKEN'),
        'test_phone' => env('TEST_PHONE_NUMBER', '9843223774'),
    ],

];