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

    /*
    | AI doctor search. When nobody in the database matches a search, an AI model with web search looks for
    | real clinics near the chosen city and the results are saved. Leave ANTHROPIC_API_KEY empty to switch
    | the feature off.
    */
    'anthropic' => [
        'key' => env('ANTHROPIC_API_KEY'),
        'model' => env('ANTHROPIC_MODEL', 'claude-sonnet-5'),
        'version' => env('ANTHROPIC_VERSION', '2023-06-01'),
        'timeout' => (int) env('ANTHROPIC_TIMEOUT', 80),
        'budget' => (int) env('ANTHROPIC_BUDGET', 85),
        'max_results' => (int) env('AI_SEARCH_MAX_RESULTS', 5),
        'per_user_per_day' => (int) env('AI_SEARCH_PER_USER_PER_DAY', 5),
        'global_per_day' => (int) env('AI_SEARCH_GLOBAL_PER_DAY', 60),
        'repeat_after_days' => (int) env('AI_SEARCH_REPEAT_AFTER_DAYS', 7),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
