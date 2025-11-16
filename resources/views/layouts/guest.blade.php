@props(['branding' => null])

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
        @if(config('app.env') === 'local')
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <!-- Productie: gebruik gecompileerde assets of CDN -->
            <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
        @endif
    </head>
    <body class="font-sans antialiased">
        {{ $slot }}
    </body>
</html>
