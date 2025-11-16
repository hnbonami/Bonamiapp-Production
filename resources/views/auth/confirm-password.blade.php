@php
    // Haal branding instellingen op voor organisatie (dynamisch per subdomain/organisatie)
    $organisatieId = 1; // Standaard organisatie, later dynamisch maken
    $branding = \App\Models\OrganisatieBranding::where('organisatie_id', $organisatieId)->first();
    
    // Fallback naar default waarden
    $loginTextColor = $branding->login_text_color ?? '#374151';
    $loginButtonColor = $branding->login_button_color ?? '#7fb432';
    $loginButtonHoverColor = $branding->login_button_hover_color ?? '#6a9929';
    $loginLinkColor = $branding->login_link_color ?? '#374151';
    
    // Achtergrondafbeelding - alleen tonen als deze bestaat in branding
    $loginBackgroundImage = ($branding && $branding->login_background_image) 
        ? (app()->environment('production') 
            ? asset('uploads/' . $branding->login_background_image)
            : asset('storage/' . $branding->login_background_image))
        : null;
    
    // Achtergrond video - heeft voorrang boven afbeelding
    $loginBackgroundVideo = ($branding && $branding->login_background_video) 
        ? (app()->environment('production') 
            ? asset('uploads/' . $branding->login_background_video)
            : asset('storage/' . $branding->login_background_video))
        : null;
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Bevestig Wachtwoord - {{ config('app.name', 'Bonami app') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Scripts -->
    @if(config('app.env') === 'local')
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    @endif
    
    <style>
        /* Zorg voor volledige hoogte en 50-50 split */
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }
        
        .login-container {
            display: flex;
            min-height: 100vh;
            width: 100%;
        }
        
        .login-form-section {
            flex: 1;
            min-width: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: white;
            padding: 2rem;
        }
        
        .login-image-section {
            flex: 1;
            min-width: 50%;
            background-color: #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-image-section img {
            width: 100%;
            height: 100vh;
            object-fit: cover;
        }
        
        .login-image-section video {
            width: 100%;
            height: 100vh;
            object-fit: cover;
        }
        
        @media (max-width: 1023px) {
            .login-image-section {
                display: none;
            }
            .login-form-section {
                min-width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Linker kant: Bevestig wachtwoord formulier (50%) -->
        <div class="login-form-section">
            <div class="w-full max-w-md">
                <!-- Organisatie Logo (vast, niet aanpasbaar via branding) -->
                <div class="flex justify-center mb-8">
                    <img src="{{ asset('images/logo_login.png') }}" alt="Logo" class="h-16 w-auto">
                </div>

                <!-- Titel -->
                <h2 class="text-3xl font-semibold text-center mb-4" style="color: {{ $loginTextColor }}">
                    Bevestig Wachtwoord
                </h2>

                <!-- Uitleg tekst -->
                <p class="text-center text-sm mb-8" style="color: {{ $loginTextColor }}">
                    Dit is een beveiligd gebied van de applicatie. Bevestig je wachtwoord om door te gaan.
                </p>

                <form method="POST" action="{{ route('password.confirm') }}" class="space-y-6">
                    @csrf

                    <!-- Wachtwoord -->
                    <div>
                        <label for="password" class="block text-sm font-medium mb-2" style="color: {{ $loginTextColor }}">
                            Wachtwoord
                        </label>
                        <input 
                            id="password" 
                            type="password" 
                            name="password" 
                            required 
                            autofocus
                            autocomplete="current-password"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-opacity-50 focus:border-transparent transition"
                            style="--tw-ring-color: {{ $loginButtonColor }}"
                        />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <!-- Bevestig button -->
                    <button 
                        type="submit"
                        class="w-full py-3 px-4 rounded-lg font-medium text-white transition-colors duration-200"
                        style="background-color: {{ $loginButtonColor }}"
                        onmouseover="this.style.backgroundColor='{{ $loginButtonHoverColor }}'"
                        onmouseout="this.style.backgroundColor='{{ $loginButtonColor }}'"
                    >
                        Bevestigen
                    </button>
                </form>
                
                <!-- Footer Logo - VAST (niet wijzigbaar via branding) -->
                <div class="mt-8 pt-4 border-t border-gray-200">
                    <div class="flex justify-center">
                        <img src="{{ asset('images/login-footer-logo.png') }}" alt="Powered by" class="h-8 opacity-60">
                    </div>
                </div>
            </div>
        </div>

        <!-- Rechter kant: Achtergrond video of afbeelding (50%) -->
        <div class="login-image-section">
            @if($loginBackgroundVideo)
                <!-- Video heeft voorrang -->
                <video autoplay muted loop playsinline id="loginVideo">
                    <source src="{{ $loginBackgroundVideo }}" type="video/mp4">
                    Uw browser ondersteunt geen video.
                </video>
            @elseif($loginBackgroundImage)
                <!-- Fallback naar afbeelding -->
                <img 
                    src="{{ $loginBackgroundImage }}" 
                    alt="Login achtergrond"
                />
            @else
                <!-- Geen media beschikbaar -->
                <div class="text-gray-400 text-sm">Login visual</div>
            @endif
        </div>
    </div>
    
    <script>
        // Forceer video autoplay bij laden van de pagina
        document.addEventListener('DOMContentLoaded', function() {
            const video = document.getElementById('loginVideo');
            if (video) {
                // Probeer video af te spelen
                video.play().catch(function(error) {
                    console.log('Video autoplay geblokkeerd door browser:', error);
                    // Als autoplay geblokkeerd is, probeer het opnieuw met gebruikersinteractie
                    document.addEventListener('click', function() {
                        video.play();
                    }, { once: true });
                });
            }
        });
    </script>
</body>
</html>
