<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'Bonami Sportcoaching')</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ url('/favicon.png?s=32') }}" sizes="32x32">
    <link rel="icon" type="image/png" href="{{ url('/favicon.png?s=16') }}" sizes="16x16">
    
    <!-- UNIFORM STYLING SYSTEM - Load order is important! -->
    <link rel="stylesheet" href="{{ asset('css/variables.css') }}">
    <link rel="stylesheet" href="{{ asset('css/buttons.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components.css') }}">
    <link rel="stylesheet" href="{{ asset('css/darkmode.css') }}">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- App specific styles -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    
    @stack('styles')
</head>
<body>
    <!-- Dark Mode Toggle (visible on all pages) -->
    <button 
        data-dark-mode-toggle 
        class="dark-mode-toggle-btn"
        aria-label="Toggle dark mode"
        title="Dark Mode (Ctrl+Shift+D)"
    >
        <i class="fas fa-moon"></i>
    </button>

    @yield('content')

    <!-- UNIFORM STYLING SYSTEM - JavaScript -->
    <script src="{{ asset('js/darkmode.js') }}"></script>
    <script src="{{ asset('js/app.js') }}"></script>
    
    @stack('scripts')
</body>
</html>
