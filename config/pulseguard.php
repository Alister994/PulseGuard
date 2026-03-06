<?php

return [

    'name' => env('PULSEGUARD_APP_NAME', 'PulseGuard'),

    /*
    |--------------------------------------------------------------------------
    | HTTP Check Settings
    |--------------------------------------------------------------------------
    */
    'http' => [
        'timeout' => (int) env('PULSEGUARD_HTTP_TIMEOUT', 10),
        'user_agent' => env('PULSEGUARD_HTTP_USER_AGENT', 'PulseGuard-Uptime-Monitor/1.0'),
    ],

    /*
    |--------------------------------------------------------------------------
    | SSL Check Settings
    |--------------------------------------------------------------------------
    */
    'ssl' => [
        'alert_days_before' => (int) env('PULSEGUARD_SSL_ALERT_DAYS', 30),
        'timeout' => (int) env('PULSEGUARD_SSL_TIMEOUT', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | Alert Channels
    |--------------------------------------------------------------------------
    */
    'alerts' => [
        'slack' => [
            'enabled' => (bool) env('PULSEGUARD_SLACK_ENABLED', false),
            'webhook_url' => env('PULSEGUARD_SLACK_WEBHOOK_URL'),
        ],
        'telegram' => [
            'enabled' => (bool) env('PULSEGUARD_TELEGRAM_ENABLED', false),
            'bot_token' => env('PULSEGUARD_TELEGRAM_BOT_TOKEN'),
            'chat_id' => env('PULSEGUARD_TELEGRAM_CHAT_ID'),
        ],
        'mail' => [
            'enabled' => (bool) env('PULSEGUARD_MAIL_ALERTS_ENABLED', true),
            'addresses' => array_filter(explode(',', env('PULSEGUARD_ALERT_EMAILS', ''))),
        ],
        'webhook' => [
            'enabled' => (bool) env('PULSEGUARD_WEBHOOK_ENABLED', false),
            'url' => env('PULSEGUARD_WEBHOOK_URL'),
            'secret_header' => env('PULSEGUARD_WEBHOOK_SECRET_HEADER'),
            'secret_value' => env('PULSEGUARD_WEBHOOK_SECRET_VALUE'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue / Batch Settings
    |--------------------------------------------------------------------------
    */
    'queue' => [
        'connection' => env('PULSEGUARD_QUEUE_CONNECTION', env('QUEUE_CONNECTION', 'database')),
        'chunk_size' => (int) env('PULSEGUARD_CHUNK_SIZE', 10),
        'retry_attempts' => (int) env('PULSEGUARD_RETRY_ATTEMPTS', 3),
        'retry_after' => (int) env('PULSEGUARD_RETRY_AFTER', 90),
    ],

];
