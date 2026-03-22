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

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],

    'anthropic' => [
        'api_key' => env('ANTHROPIC_API_KEY'),
        // 若 .env 寫 ANTHROPIC_MODEL=（空值）會導致 model 為空 → API 回 400
        'model' => trim((string) env('ANTHROPIC_MODEL', '')) ?: 'claude-3-5-sonnet-20241022',
    ],

    /**
     * 塔羅解牌：預設 Google Gemini；若要 Anthropic / Groq / Grok 請設 AI_PROVIDER
     */
    'ai' => [
        'provider' => strtolower(trim((string) env('AI_PROVIDER', 'gemini'))) ?: 'gemini',
    ],

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        // 2.0 Flash 免費額度常為 0 或已限流；2.5 Flash-Lite 較適合免費層高頻文字
        'model' => trim((string) env('GEMINI_MODEL', '')) ?: 'gemini-2.5-flash-lite',
    ],

    'groq' => [
        'api_key' => env('GROQ_API_KEY'),
        'model' => trim((string) env('GROQ_MODEL', '')) ?: 'llama-3.3-70b-versatile',
    ],

    'xai' => [
        'api_key' => env('XAI_API_KEY'),
        'model' => trim((string) env('XAI_MODEL', '')) ?: 'grok-4-fast-non-reasoning',
    ],

];

