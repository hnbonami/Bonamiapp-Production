<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Security Headers
    |--------------------------------------------------------------------------
    |
    | Deze headers beschermen tegen XSS, clickjacking en andere attacks
    |
    */

    'headers' => [
        'X-Frame-Options' => 'SAMEORIGIN',
        'X-Content-Type-Options' => 'nosniff',
        'X-XSS-Protection' => '1; mode=block',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'Permissions-Policy' => 'camera=(), microphone=(), geolocation=()',
    ],

    /*
    |--------------------------------------------------------------------------
    | File Upload Beveiliging
    |--------------------------------------------------------------------------
    */

    'uploads' => [
        // Toegestane bestandstypes
        'allowed_mimes' => [
            'images' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
            'documents' => ['pdf', 'doc', 'docx', 'xls', 'xlsx'],
            'all' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'doc', 'docx', 'xls', 'xlsx'],
        ],
        
        // Mime types (voor extra check)
        'mime_types' => [
            'images' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
            'documents' => ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        ],
        
        // Maximum bestandsgrootte (in kilobytes)
        'max_size' => [
            'avatar' => 2048,      // 2MB voor avatars
            'document' => 10240,   // 10MB voor documenten
            'bikefit_foto' => 5120, // 5MB voor bikefit foto's
            'rapport' => 15360,    // 15MB voor rapporten
        ],
        
        // Image dimensions
        'dimensions' => [
            'avatar' => [
                'min_width' => 100,
                'min_height' => 100,
                'max_width' => 4000,
                'max_height' => 4000,
            ],
            'bikefit_foto' => [
                'min_width' => 200,
                'min_height' => 200,
                'max_width' => 6000,
                'max_height' => 6000,
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    */

    'rate_limits' => [
        'login' => 5,           // Max 5 login pogingen per minuut
        'register' => 5,        // Max 5 registraties per minuut
        'password_reset' => 3,  // Max 3 password resets per minuut
        'api' => 60,            // Max 60 API requests per minuut
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Policy
    |--------------------------------------------------------------------------
    */

    'passwords' => [
        'min_length' => 12,
        'require_uppercase' => true,
        'require_lowercase' => true,
        'require_numbers' => true,
        'require_special_chars' => true,
        'prevent_common' => true,
    ],

];