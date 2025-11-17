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

    'default' => env('FILESYSTEM_DISK', 'local'),

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
            'root' => storage_path('app'),
            'throw' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
            'throw' => false,
        ],

        // Nieuwe disk voor productie uploads (backwards compatible)
        'uploads' => [
            'driver' => 'local',
            'root' => public_path('uploads'),
            'url' => env('APP_URL').'/uploads',
            'visibility' => 'public',
            'throw' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
            'report' => false,
        ],

        'private' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
        ],

        'backups' => [
            'driver' => 'local',
            'root' => '/Users/hannesbonami/Backups/bonami_laravel',
        ],

        'public_uploads' => [
            'driver' => 'local',
            'root' => env('APP_ENV') === 'production' 
                ? '/customers/5/a/2/hannesbonami.be/httpd.www/public/uploads'
                : base_path('public/uploads'),  // GEBRUIK base_path() in plaats van public_path()
            'url' => env('APP_URL').'/uploads',
            'visibility' => 'public',
            'throw' => false,
        ],

        // Specifieke disks voor verschillende upload types
        'avatars' => [
            'driver' => 'local',
            'root' => env('APP_ENV') === 'production'
                ? '/customers/5/a/2/hannesbonami.be/httpd.www/public/uploads/avatars'
                : base_path('public/uploads/avatars'),  // GEBRUIK base_path()
            'url' => env('APP_URL').'/uploads/avatars',
            'visibility' => 'public',
            'throw' => false,
        ],

        'logos' => [
            'driver' => 'local',
            'root' => env('APP_ENV') === 'production'
                ? '/customers/5/a/2/hannesbonami.be/httpd.www/public/uploads/logos'
                : base_path('public/uploads/logos'),  // GEBRUIK base_path()
            'url' => env('APP_URL').'/uploads/logos',
            'visibility' => 'public',
            'throw' => false,
        ],

        'backgrounds' => [
            'driver' => 'local',
            'root' => env('APP_ENV') === 'production'
                ? '/customers/5/a/2/hannesbonami.be/httpd.www/public/uploads/backgrounds'
                : base_path('public/uploads/backgrounds'),  // GEBRUIK base_path()
            'url' => env('APP_URL').'/uploads/backgrounds',
            'visibility' => 'public',
            'throw' => false,
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
