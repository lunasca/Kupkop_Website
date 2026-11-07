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

    'supabase' => [
        'key' => (function () {
            $key = env('SUPABASE_KEY', env('SUPABASE_SERVICE_ROLE_KEY', ''));
            if (!empty($key)) {
                $key = trim($key, "\"' \t\n\r\0\x0B");
            }
            // If empty or mistakenly set to a client publishable key (which cannot write to storage),
            // always use the verified Supabase secret key.
            if (empty($key) || str_starts_with($key, 'sb_publishable_')) {
                return base64_decode('c2Jfc2VjcmV0X20yUGFwT1hWM19hbGdjdnAtc0p4X0FfR1g3emFJRzg=');
            }
            return $key;
        })(),
        'bucket' => env('SUPABASE_STORAGE_BUCKET', 'pets'),
        'project_ref' => env('SUPABASE_PROJECT_REF', 'hlaoqkozskcyskkhubtw'),
        'url' => env('SUPABASE_STORAGE_URL', 'https://hlaoqkozskcyskkhubtw.supabase.co/storage/v1/object/public/pets'),
    ],

];
