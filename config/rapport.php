<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Rapport Default Instellingen
    |--------------------------------------------------------------------------
    |
    | Deze instellingen worden gebruikt voor organisaties die GEEN
    | "rapporten_opmaken" feature hebben geactiveerd.
    |
    | Dit zijn de standaard Performance Pulse branding waarden.
    |
    */

    'defaults' => [
        'header_tekst' => 'Performance Pulse Rapport',
        'footer_tekst' => '© ' . date('Y') . ' Performance Pulse - Sportcoaching',
        'primaire_kleur' => '#c8e1eb',
        'secundaire_kleur' => '#111111',
        'lettertype' => 'Arial',
        'paginanummering_tonen' => true,
        'paginanummering_positie' => 'rechtsonder',
        'contactgegevens_in_footer' => false,
        'qr_code_tonen' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Upload Instellingen
    |--------------------------------------------------------------------------
    */

    'uploads' => [
        'logo' => [
            'max_size' => 2048, // KB
            'mimes' => ['jpeg', 'png', 'jpg', 'svg'],
            'path' => 'rapporten/logos',
        ],
        'voorblad_foto' => [
            'max_size' => 5120, // KB
            'mimes' => ['jpeg', 'png', 'jpg'],
            'path' => 'rapporten/voorbladfotos',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Lettertype Opties
    |--------------------------------------------------------------------------
    */

    'lettertypen' => [
        'Arial' => 'Arial, sans-serif',
        'Tahoma' => 'Tahoma, sans-serif',
        'Calibri' => 'Calibri, sans-serif',
        'Helvetica' => 'Helvetica, Arial, sans-serif',
    ],

    /*
    |--------------------------------------------------------------------------
    | Paginanummering Posities
    |--------------------------------------------------------------------------
    */

    'paginanummering_posities' => [
        'rechtsonder' => 'Rechtsonder',
        'rechtsboven' => 'Rechtsboven',
        'linksonder' => 'Linksonder',
        'linksboven' => 'Linksboven',
        'midden' => 'Midden (onder)',
    ],

    /*
    |--------------------------------------------------------------------------
    | QR Code Posities
    |--------------------------------------------------------------------------
    */

    'qr_code_posities' => [
        'rechtsonder' => 'Rechtsonder',
        'linksboven' => 'Linksboven',
        'footer' => 'In Footer',
    ],

    /*
    |--------------------------------------------------------------------------
    | QR Code Instellingen
    |--------------------------------------------------------------------------
    */

    'qr_code' => [
        'size' => 150, // pixels
        'margin' => 0,
        'format' => 'svg', // svg of png
    ],
];
