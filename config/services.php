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
    | N8N Integration Configuration
    |--------------------------------------------------------------------------
    |
    | Global configuration for N8N workflow automation platform.
    | This is used for AI processing, OCR, and QuickBooks integration.
    |
    */
    'n8n' => [
        'enabled' => env('N8N_ENABLED', false),
        'webhook_url' => env('N8N_WEBHOOK_URL'),
        'webhook_secret' => env('N8N_WEBHOOK_SECRET'),
        'timeout' => env('N8N_TIMEOUT', 60),
        'max_retries' => env('N8N_MAX_RETRIES', 3),
        'retry_delay' => env('N8N_RETRY_DELAY', 2),

        // Fallback específico para AI chat (usa N8N_AI_CHAT_* do .env)
        'ai_chat' => [
            'webhook_url' => env('N8N_AI_CHAT_WEBHOOK_URL'),
            'webhook_secret' => env('N8N_AI_CHAT_WEBHOOK_SECRET'),
            'timeout' => env('N8N_AI_CHAT_TIMEOUT', 30),
            'max_retries' => env('N8N_AI_CHAT_MAX_RETRIES', 3),
            'retry_delay' => env('N8N_AI_CHAT_RETRY_DELAY', 1),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Stripe Payment Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Stripe payment processing.
    |
    */
    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],

];
