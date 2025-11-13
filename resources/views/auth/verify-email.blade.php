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
    <title>E-mail Verificatie - {{ config('app.name', 'Bonami app') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
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
        <!-- Linker kant: E-mail verificatie (50%) -->
        <div class="login-form-section">
            <div class="w-full max-w-md">
                <!-- Organisatie Logo (vast, niet aanpasbaar via branding) -->
                <div class="flex justify-center mb-8">
                    <img src="{{ asset('images/logo_login.png') }}" alt="Logo" class="h-16 w-auto">
                </div>

                <!-- Titel -->
                <h2 class="text-3xl font-semibold text-center mb-4" style="color: {{ $loginTextColor }}">
                    E-mail Verificatie
                </h2>

                <!-- Uitleg tekst -->
                <p class="text-center text-sm mb-8" style="color: {{ $loginTextColor }}">
                    Bedankt voor je registratie! Voordat je kunt beginnen, moet je je e-mailadres verifiëren door op de link te klikken die we je zojuist hebben gemaild. Als je de e-mail niet hebt ontvangen, sturen we je graag een nieuwe.
                </p>

                @if (session('status') == 'verification-link-sent')
                    <div class="mb-6 p-4 rounded-lg text-sm font-medium" style="background-color: #d1fae5; color: #065f46;">
                        Er is een nieuwe verificatielink verstuurd naar het e-mailadres dat je hebt opgegeven bij de registratie.
                    </div>
                @endif

                <div class="space-y-4">
                    <!-- Verstuur verificatie e-mail opnieuw -->
                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <button 
                            type="submit"
                            class="w-full py-3 px-4 rounded-lg font-medium text-white transition-colors duration-200"
                            style="background-color: {{ $loginButtonColor }}"
                            onmouseover="this.style.backgroundColor='{{ $loginButtonHoverColor }}'"
                            onmouseout="this.style.backgroundColor='{{ $loginButtonColor }}'"
                        >
                            Verificatie E-mail Opnieuw Versturen
                        </button>
                    </form>

                    <!-- Uitloggen -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button 
                            type="submit"
                            class="w-full text-center text-sm underline hover:no-underline transition"
                            style="color: {{ $loginLinkColor }}"
                        >
                            Uitloggen
                        </button>
                    </form>
                </div>
                
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
