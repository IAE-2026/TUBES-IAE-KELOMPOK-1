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

    /*
    |--------------------------------------------------------------------------
    | IAE Central Services (SSO, SOAP Audit, RabbitMQ)
    |--------------------------------------------------------------------------
    */
    'iae' => [
        'base_url'     => env('IAE_BASE_URL', 'https://iae-sso.virtualfri.id'),
        'api_key'      => env('IAE_API_KEY', 'KEY-MHS-124'),
        'team_id'      => env('IAE_TEAM_ID', 'TEAM-01'),
        'sso_email'    => env('SSO_EMAIL', 'warga22@ktp.iae.id'),
        'sso_password' => env('SSO_PASSWORD', 'KtpDigital2026!'),
    ],

];
