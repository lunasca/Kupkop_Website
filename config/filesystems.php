<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'supabase'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('ASSET_URL')
                ?: (env('APP_URL') && !in_array(env('APP_URL'), ['http://localhost', 'http://127.0.0.1'])
                    ? rtrim(env('APP_URL'), '/').'/storage'
                    : '/storage'),
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID', env('SUPABASE_STORAGE_KEY')),
            'secret' => env('AWS_SECRET_ACCESS_KEY', env('SUPABASE_STORAGE_SECRET')),
            'region' => env('AWS_DEFAULT_REGION', env('SUPABASE_STORAGE_REGION', 'ap-northeast-1')),
            'bucket' => env('AWS_BUCKET', env('SUPABASE_STORAGE_BUCKET', 'pets')),
            'url' => env('AWS_URL', env('SUPABASE_STORAGE_URL', 'https://hlaoqkozskcyskkhubtw.supabase.co/storage/v1/object/public/pets')),
            'endpoint' => env('AWS_ENDPOINT', env('SUPABASE_STORAGE_ENDPOINT', 'https://hlaoqkozskcyskkhubtw.storage.supabase.co/storage/v1/s3')),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', env('SUPABASE_STORAGE_USE_PATH_STYLE_ENDPOINT', true)),
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        'supabase' => [
            'driver' => 's3',
            'key' => env('SUPABASE_STORAGE_KEY', env('AWS_ACCESS_KEY_ID')),
            'secret' => env('SUPABASE_STORAGE_SECRET', env('AWS_SECRET_ACCESS_KEY')),
            'region' => env('SUPABASE_STORAGE_REGION', env('AWS_DEFAULT_REGION', 'ap-northeast-1')),
            'bucket' => env('SUPABASE_STORAGE_BUCKET', env('AWS_BUCKET', 'pets')),
            'url' => env('SUPABASE_STORAGE_URL', env('AWS_URL', 'https://hlaoqkozskcyskkhubtw.supabase.co/storage/v1/object/public/pets')),
            'endpoint' => env('SUPABASE_STORAGE_ENDPOINT', env('AWS_ENDPOINT', 'https://hlaoqkozskcyskkhubtw.storage.supabase.co/storage/v1/s3')),
            'use_path_style_endpoint' => env('SUPABASE_STORAGE_USE_PATH_STYLE_ENDPOINT', env('AWS_USE_PATH_STYLE_ENDPOINT', true)),
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
