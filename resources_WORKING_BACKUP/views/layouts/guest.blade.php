<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Bonami app') }}</title>
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon.png?s=32">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon.png?s=16">
    <link rel="shortcut icon" type="image/png" href="/favicon.png?s=32">
    <link rel="apple-touch-icon" href="/favicon.png?s=180">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Responsive overrides for small screens -->
        <style>
        @media (max-width: 768px) {
                .guest-card {
            flex-direction: column !important;
                    max-width: 100% !important;
                    width: 100% !important;
                    height: auto !important;
                    border-radius: 0.75rem; /* match rounded-2xl */
                }
                .guest-image { display: none !important; }
                .guest-form-inner {
                    margin-top: 20px !important;
                    max-width: 100% !important;
                }
                .page-wrap { padding: 1rem; }
            }
            @media (min-width: 769px) and (max-width: 1024px) { /* tablets */
                .guest-card {
                    max-width: 760px !important; /* comfortable width on tablets */
                    height: 500px !important;
                }
            }
        </style>
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex items-center justify-center bg-gray-100 page-wrap">
          <div class="guest-card flex flex-row bg-white shadow-lg rounded-2xl overflow-hidden"
              style="max-width: {{ $containerMaxW ?? '720px' }}; height: {{ $containerH ?? '480px' }};">
                <!-- Left: Image -->
             <div class="guest-image flex justify-center items-center overflow-hidden h-full rounded-l-2xl"
                 style="flex: {{ $imageFlex ?? '0 0 42%' }}; min-width: {{ $imageMinW ?? '300px' }};">
                <img src="/login_side.jpg" alt="Login visual" class="h-full w-full object-cover" style="object-position:center;" />
                </div>
                <!-- Right: Form -->
                <div class="h-full flex-1 flex flex-col justify-center items-center {{ $formPadding ?? 'p-8' }}">
                    <div class="w-full md:max-w-md guest-form-inner" style="margin-top: {{ $formTop ?? '80px' }};">
                        <div class="flex justify-center mb-6 md:hidden">
                            <a href="/">
                                <img src="/logo_bonami.png" alt="Logo" style="width:7em;height:auto;">
                            </a>
                        </div>
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
