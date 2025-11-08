@php
    if (!isset($route)) {
        $route = request()->path();
    }
    
    // Haal branding op voor ingelogde gebruiker
    $organisatieBranding = null;
    if (auth()->check() && auth()->user()->organisatie_id) {
        $organisatieBranding = \App\Models\OrganisatieBranding::where('organisatie_id', auth()->user()->organisatie_id)
            ->where('is_actief', true)
            ->first();
    }
@endphp
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Performance Pulse</title>
    <!-- Favicon - Performance Pulse Logo -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo_login.png?v=2') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo_login.png?v=2') }}">
    <!-- Bunny Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,800|georgia:400,700|times-new-roman:400,700|arial:400,700|courier-new:400,700&display=swap" rel="stylesheet" />
            <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Dark Mode CSS -->
    <link rel="stylesheet" href="{{ asset('css/darkmode.css') }}">
    
    <!-- Dark Mode Script (load meteen voor flicker-free) -->
    <script src="{{ asset('js/darkmode.js') }}"></script>
    
    {{-- Custom Branding CSS Variabelen --}}
    @if(isset($organisatieBranding) && $organisatieBranding)
        <style>
            :root {
                @foreach($organisatieBranding->getCssVariables() as $var => $value)
                    {{ $var }}: {{ $value }};
                @endforeach
            }
            
            /* Pas topbar achtergrond aan met branding kleur */
            #topbar {
                background: {{ $organisatieBranding->navbar_achtergrond ?? '#c8e1eb' }} !important;
            }
            
            /* Pas navbar tekst kleur aan */
            #topbar .topbar-grid,
            #topbar #nav-toggle,
            #topbar #user-menu-button {
                color: {{ $organisatieBranding->navbar_tekst_kleur ?? '#000000' }} !important;
            }
            
            /* Pas sidebar achtergrond aan */
            aside {
                background: {{ $organisatieBranding->sidebar_achtergrond ?? '#FFFFFF' }} !important;
            }
            
            /* Pas sidebar tekst kleur aan */
            aside a {
                color: {{ $organisatieBranding->sidebar_tekst_kleur ?? '#374151' }} !important;
            }
            
            /* Pas sidebar active state achtergrond aan via attribuut selector */
            aside a[style*="background:#f6fbfe"],
            aside a[style*="background:#07455f"],
            aside a[style*="background: #f6fbfe"],
            aside a[style*="background: #07455f"],
            aside a.bg-blue-50 {
                background: {{ $organisatieBranding->sidebar_actief_achtergrond ?? '#f6fbfe' }} !important;
            }
            
            /* Pas sidebar active state lijn aan (verticaal lijntje links) via attribuut selector */
            aside a span[style*="background:#c1dfeb"],
            aside a span[style*="background: #c1dfeb"] {
                background: {{ $organisatieBranding->sidebar_actief_lijn ?? '#c1dfeb' }} !important;
            }
            
            /* Pas badge kleuren aan */
            .bg-\\[\\#c1dfeb\\],
            span[class*="bg-[#c1dfeb]"] {
                background-color: {{ $organisatieBranding->sidebar_actief_achtergrond ?? '#c1dfeb' }} !important;
            }
            
            .text-\\[\\#08474f\\],
            span[class*="text-[#08474f]"] {
                color: {{ $organisatieBranding->sidebar_tekst_kleur ?? '#08474f' }} !important;
            }
            
            /* Dark mode ondersteuning */
            @media (prefers-color-scheme: dark) {
                body.dark-mode {
                    background: {{ $organisatieBranding->dark_achtergrond ?? '#1F2937' }} !important;
                    color: {{ $organisatieBranding->dark_tekst ?? '#F9FAFB' }} !important;
                }
                
                body.dark-mode #topbar {
                    background: {{ $organisatieBranding->dark_navbar_achtergrond ?? '#111827' }} !important;
                }
                
                body.dark-mode aside {
                    background: {{ $organisatieBranding->dark_sidebar_achtergrond ?? '#111827' }} !important;
                }
            }
        </style>
    @endif
    
    <!-- Testzadels specific fixes for ALL testzadels pages -->
    @if(request()->is('testzadels*'))
        <link rel="stylesheet" href="{{ asset('css/testzadels.css') }}">
        <script src="{{ asset('js/testzadels-fixes.js') }}" defer></script>
        
        <!-- INLINE CSS for immediate fix - Position next to sidebar -->
        <style>
            /* EMERGENCY TESTZADELS LAYOUT FIX - Next to sidebar */
            body main#app-main {
                margin-left: 16rem !important;
                padding-left: 2rem !important;
                padding-right: 2rem !important;
                width: calc(100% - 16rem) !important;
            }
            body main#app-main > * {
                margin-left: 0 !important;
                margin-right: 0 !important;
                padding-left: 0 !important;
                padding-right: 0 !important;
                max-width: none !important;
                width: 100% !important;
            }
            body main#app-main [class*="mx-auto"],
            body main#app-main [class*="max-w-"] {
                margin-left: 0 !important;
                margin-right: 0 !important;
                max-width: none !important;
            }
        </style>
    @endif
    <style>
        body {
            font-family: 'Figtree', Arial, sans-serif;
        }
        
        /* MOBILE-FIRST TOPBAR FIXES */
        #topbar { 
            height: 56px; 
            z-index: 40; 
            pointer-events: auto;
        }
        
        /* Mobile topbar grid - better alignment */
        .topbar-grid {
            display: grid;
            grid-template-columns: auto 1fr auto;
            align-items: center;
            height: 56px;
            padding: 0 1rem;
            gap: 1rem;
        }
        
        /* Hamburger button - ensure clickability */
        #nav-toggle {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            pointer-events: auto;
            cursor: pointer;
            border: none;
            background: transparent;
            color: #374151;
            border-radius: 8px;
            transition: background-color 0.2s;
        }
        
        #nav-toggle:hover {
            background-color: rgba(0, 0, 0, 0.05);
        }
        
        /* Logo center alignment */
        .topbar-logo {
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        /* User menu - fix mobile alignment */
        .topbar-user {
            display: flex;
            align-items: center;
            justify-content: flex-end;
        }
        
        #user-menu-button {
            display: flex;
            align-items: center;
            height: 44px;
            background: transparent;
            border: none;
            cursor: pointer;
            font-weight: 600;
            font-size: 16px;
            color: black;
            padding: 0 8px;
            border-radius: 8px;
            transition: background-color 0.2s;
            pointer-events: auto;
        }
        
        #user-menu-button:hover {
            background-color: rgba(0, 0, 0, 0.05);
        }
        
        /* Avatar specifieke sizing */
            /* Avatar specifieke sizing - TOPBAR ALTIJD KLEIN */
            #topbar-avatar, #topbar-avatar-placeholder {
                width: 36px !important;
                height: 36px !important;
                min-width: 36px !important;
                min-height: 36px !important;
                max-width: 36px !important;
                max-height: 36px !important;
                border-radius: 50% !important;
                margin-right: 8px;
                flex-shrink: 0;
            }        /* Remove the problematic profile click icon on mobile */
        #profile-click-icon {
            display: none;
        }
        
        /* Desktop adjustments */
        @media (min-width: 768px) {
            #app-main { 
                margin-left: 240px; 
            }
            
            .topbar-grid {
                padding: 0 1.5rem;
                /* Desktop: relatieve positionering voor container */
                position: relative;
                display: flex;
                align-items: center;
                justify-content: center; /* Center everything, then position user menu absolute */
            }
            
            #nav-toggle {
                display: none;
            }
            
            /* Hide hamburger container completely on desktop */
            .topbar-grid > div:first-child {
                display: none;
            }
            
            /* Center logo on desktop - eenvoudiger zonder absolute */
            .topbar-logo {
                display: flex;
                justify-content: center;
                align-items: center;
            }
            
            /* Position user menu absolute to the right */
            .topbar-user {
                position: absolute;
                right: 1.5rem;
                top: 50%;
                transform: translateY(-50%);
                display: flex;
                align-items: center;
                justify-content: flex-end;
            }
            
            /* Desktop user menu fixes */
            #user-menu-button {
                gap: 8px;
                padding: 0 12px;
            }
            
            #topbar-avatar, #topbar-avatar-placeholder {
                margin-right: 8px;
            }
        }
        
        /* Nav tabs: underline ook onder icoon met offset */
        .nav-tab { position: relative; }
        .nav-tab.active-tab { padding-bottom: 8px; }
        .nav-tab.active-tab::after {
            content: '';
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            height: 2px;
            background-color: #9bb3bd;
            border-radius: 1px;
        }
        
        /* Mobile nav improvements */
        #mobile-nav {
            background: white;
            border-bottom: 1px solid #e5e7eb;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            -webkit-overflow-scrolling: touch; /* Smooth scrolling op iOS */
        }
        
        #mobile-nav a {
            display: flex;
            align-items: center;
            padding: 12px 24px;
            font-weight: 600;
            font-size: 16px;
            color: #374151;
            text-decoration: none;
            border-bottom: 1px solid #f3f4f6;
            transition: background-color 0.2s;
            min-height: 48px; /* Ensure touch-friendly size */
        }
        
        #mobile-nav a:hover,
        #mobile-nav a:active {
            background-color: #f9fafb;
        }
        
        #mobile-nav a.active,
        #mobile-nav a[class*="bg-blue-50"] {
            background-color: #f0f9ff;
            color: #0369a1;
            border-left: 4px solid #0369a1;
        }
        
        /* Mobile nav section headers */
        #mobile-nav .border-t {
            margin-top: 8px;
            padding-top: 8px;
        }
    </style>
</head>
<body>
    <!-- Bovenste strook met logo, hamburger (mobile) en profielknop rechts -->
    @php $routeName = optional(request()->route())->getName() ?? ''; @endphp
    <div id="topbar" class="w-full fixed top-0 left-0 right-0 z-40" style="background:{{ $organisatieBranding->navbar_achtergrond ?? '#c8e1eb' }};">
        <div class="topbar-grid" style="color:{{ $organisatieBranding->navbar_tekst_kleur ?? '#000000' }};">
            <!-- Left: Hamburger (mobile only) -->
            <div class="flex items-center">
                <button id="nav-toggle" class="md:hidden focus:outline-none" aria-label="Menu" style="color:{{ $organisatieBranding->navbar_tekst_kleur ?? '#374151' }};">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>
            
            <!-- Center: Logo - VAST Performancepulse logo (niet wijzigbaar) -->
            <div class="topbar-logo">
                <img src="{{ asset('images/logo_login.png') }}" alt="Logo" style="height:35px;" />
            </div>
            
            <!-- Right: User menu -->
            <div class="topbar-user">
                @auth
                    @php
                        $avatar = Auth::user()->avatar_path ?? null;
                        $voornaam = explode(' ', Auth::user()->name)[0] ?? '';
                        $isStaff = Auth::user() && in_array(Auth::user()->role, ['admin', 'medewerker']);
                        $unreadNotes = $isStaff ? \App\Models\StaffNote::where('is_new', true)->count() : 0;
                    @endphp
                    
                    <!-- Dark Mode Toggle -->
                    <x-dark-mode-toggle />
                    
                    <div class="relative">
                        <button id="user-menu-button" class="flex items-center gap-2">
                            <!-- Avatar -->
                            <div class="relative">
                                @if($avatar)
                                    <img id="topbar-avatar" src="{{ asset('storage/'.$avatar) }}" alt="Avatar" class="object-cover" />
                                @else
                                    <div id="topbar-avatar-placeholder" class="bg-gray-200 flex items-center justify-center text-gray-500 font-semibold">
                                        {{ strtoupper(substr($voornaam,0,1)) }}
                                    </div>
                                @endif
                                {{-- Verberg notification badge in topbar
                                @if($unreadNotes > 0)
                                    <span class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs font-bold rounded-full flex items-center justify-center">{{ $unreadNotes }}</span>
                                @endif
                                --}}
                            </div>
                            
                            <!-- Name + Arrow (desktop only) -->
                            <div class="hidden md:flex items-center gap-1">
                                <span>{{ $voornaam }}</span>
                                <svg class="w-4 h-4 text-gray-700" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </button>

                        <!-- Dropdown menu -->
                        <div id="user-menu" class="hidden absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-lg shadow-lg z-10">
                            {{-- Alle gebruikers gaan naar instellingen pagina --}}
                            <a href="/instellingen" class="flex items-center gap-2 px-4 py-2 text-gray-700 hover:bg-gray-50">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                Instellingen
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="flex items-center gap-2 w-full text-left px-4 py-2 text-rose-600 hover:bg-rose-50">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                    </svg>
                                    Uitloggen
                                </button>
                            </form>
                        </div>
                    </div>
                @endauth
            </div>
        </div>
    <!-- Mobile nav dropdown onder de bovenbalk (vast onder de header) -->
    <div id="mobile-nav" class="md:hidden hidden fixed left-0 right-0 z-30 bg-white border-b border-gray-200 shadow-lg overflow-y-auto" style="top: 56px; max-height: calc(100vh - 56px);">
        <div class="py-2">
            <a href="{{ route('dashboard.index') }}" class="block px-6 py-3 text-gray-900 font-medium hover:bg-gray-50 {{ str_starts_with($routeName, 'dashboard') ? 'bg-blue-50 border-l-4 border-blue-500' : '' }}">
                Dashboard
            </a>
            
            @if(Auth::user() && Auth::user()->role === 'klant')
                {{-- Klanten gaan naar hun klant show pagina (profiel overzicht) --}}
                @php
                    $mobileIngelogdeKlant = \App\Models\Klant::where('email', Auth::user()->email)->first();
                @endphp
                @if($mobileIngelogdeKlant)
                <a href="{{ route('klanten.show', $mobileIngelogdeKlant->id) }}" class="block px-6 py-3 text-gray-900 font-medium hover:bg-gray-50 {{ request()->is('klanten/' . $mobileIngelogdeKlant->id) ? 'bg-blue-50 border-l-4 border-blue-500' : '' }}">
                    Mijn Profiel
                </a>
                @endif
            @endif
            
            @if(Auth::user() && (Auth::user()->isBeheerder() || Auth::user()->isMedewerker()))
                <a href="/staff-notes" class="flex items-center justify-between px-6 py-3 text-gray-900 font-medium hover:bg-gray-50 {{ request()->is('staff-notes*') ? 'bg-blue-50 border-l-4 border-blue-500' : '' }}">
                    Notities
                    @php $unreadNotes = \App\Models\StaffNote::where('is_new', true)->count(); @endphp
                    @if($unreadNotes > 0)
                        <span class="ml-1 inline-flex items-center justify-center px-2 py-0.5 text-xs font-medium bg-red-500 text-white rounded-full">{{ $unreadNotes }}</span>
                    @endif
                </a>
            @endif
            
            {{-- Klanten - alleen tonen als feature actief is --}}
            @hasFeature('klantenbeheer')
            @if(Auth::user() && !Auth::user()->isKlant())
                <a href="/klanten" class="flex items-center justify-between px-6 py-3 text-gray-900 font-medium hover:bg-gray-50 {{ str_starts_with($routeName, 'klanten') ? 'bg-blue-50 border-l-4 border-blue-500' : '' }}">
                    Klanten 
                    <span class="ml-1 inline-flex items-center justify-center px-2 py-0.5 text-xs font-medium bg-[#c1dfeb] text-[#08474f] rounded-full">{{ \App\Models\Klant::where('organisatie_id', auth()->user()->organisatie_id)->count() }}</span>
                </a>
            @endif
            @endhasFeature
            
            {{-- Medewerkers - alleen tonen als feature actief is --}}
            @hasFeature('medewerkerbeheer')
            @if(Auth::user() && Auth::user()->isBeheerder())
                <a href="/medewerkers" class="flex items-center justify-between px-6 py-3 text-gray-900 font-medium hover:bg-gray-50 {{ str_starts_with($routeName, 'medewerkers') ? 'bg-blue-50 border-l-4 border-blue-500' : '' }}">
                    Medewerkers 
                    <span class="ml-1 inline-flex items-center justify-center px-2 py-0.5 text-xs font-medium bg-[#c1dfeb] text-[#08474f] rounded-full">{{ \App\Models\User::where('role', '!=', 'klant')->where('organisatie_id', auth()->user()->organisatie_id)->count() }}</span>
                </a>
            @endif
            @endhasFeature
            
            {{-- Instagram - alleen tonen als feature actief is --}}
            @hasFeature('instagram')
            @if(Auth::user() && Auth::user()->isBeheerder())
                <a href="/instagram" class="flex items-center justify-between px-6 py-3 text-gray-900 font-medium hover:bg-gray-50 {{ request()->is('instagram*') ? 'bg-blue-50 border-l-4 border-blue-500' : '' }}">
                    Instagram
                    <span class="ml-1 inline-flex items-center justify-center px-2 py-0.5 text-xs font-medium bg-[#c1dfeb] text-[#08474f] rounded-full">{{ \App\Models\InstagramPost::count() }}</span>
                </a>
            @endif
            @endhasFeature
            
            {{-- Nieuwsbrief - alleen tonen als feature actief is --}}
            @hasFeature('nieuwsbrief')
            @if(Auth::user() && Auth::user()->isBeheerder())
                <a href="/nieuwsbrieven" class="flex items-center justify-between px-6 py-3 text-gray-900 font-medium hover:bg-gray-50 {{ (request()->is('nieuwsbrieven*') || request()->is('newsletters*') || request()->is('nieuwsbrief*')) ? 'bg-blue-50 border-l-4 border-blue-500' : '' }}">
                    Nieuwsbrief
                    <span class="ml-1 inline-flex items-center justify-center px-2 py-0.5 text-xs font-medium bg-[#c1dfeb] text-[#08474f] rounded-full">{{ \App\Models\Newsletter::count() }}</span>
                </a>
            @endif
            @endhasFeature
            
            {{-- Sjablonen - alleen tonen als feature actief is --}}
            @hasFeature('sjablonen')
            @if(Auth::user() && Auth::user()->isBeheerder())
                <a href="/sjablonen" class="flex items-center justify-between px-6 py-3 text-gray-900 font-medium hover:bg-gray-50 {{ request()->is('sjablonen*') ? 'bg-blue-50 border-l-4 border-blue-500' : '' }}">
                    Sjablonen 
                    <span class="ml-1 inline-flex items-center justify-center px-2 py-0.5 text-xs font-medium bg-[#c1dfeb] text-[#08474f] rounded-full">{{ \App\Models\Sjabloon::count() }}</span>
                </a>
            @endif
            @endhasFeature
            
            {{-- Prestaties - alleen tonen als feature actief is --}}
            @hasFeature('prestaties')
            @if(Auth::user() && (Auth::user()->isBeheerder() || Auth::user()->isMedewerker()))
                <a href="/prestaties" class="flex items-center justify-between px-6 py-3 text-gray-900 font-medium hover:bg-gray-50 {{ request()->is('prestaties*') || request()->is('admin/prestaties*') ? 'bg-blue-50 border-l-4 border-blue-500' : '' }}">
                    Prestaties
                    @if(Auth::user()->isMedewerker() && !Auth::user()->isBeheerder())
                        @php
                            $huidigKwartaal = 'Q' . now()->quarter;
                            $mijnPrestaties = \App\Models\Prestatie::where('user_id', Auth::id())
                                ->where('jaar', now()->year)
                                ->where('kwartaal', $huidigKwartaal)
                                ->count();
                        @endphp
                        @if($mijnPrestaties > 0)
                            <span class="ml-1 inline-flex items-center justify-center px-2 py-0.5 text-xs font-medium bg-[#c1dfeb] text-[#08474f] rounded-full">{{ $mijnPrestaties }}</span>
                        @endif
                    @endif
                </a>
            @endif
            @endhasFeature
            
            {{-- Analytics - alleen tonen als feature actief is en voor admin/medewerkers, net boven Beheer --}}
            @hasFeature('analytics')
            @if(Auth::user() && (Auth::user()->isBeheerder() || Auth::user()->isMedewerker()))
                <div class="border-t border-gray-200 mt-2 pt-2">
                    <a href="/admin/analytics" class="block px-6 py-3 text-gray-900 font-medium hover:bg-gray-50 {{ request()->is('admin/analytics*') ? 'bg-blue-50 border-l-4 border-blue-500' : '' }}">
                        Analytics
                    </a>
                </div>
            @endif
            @endhasFeature
            
            @if(Auth::user() && Auth::user()->isBeheerder())
                <div class="border-t border-gray-200 mt-2 pt-2">
                    <a href="/admin" class="block px-6 py-3 text-gray-900 font-medium hover:bg-gray-50 {{ request()->is('admin*') && !request()->is('admin/analytics*') ? 'bg-blue-50 border-l-4 border-blue-500' : '' }}">
                        Beheer
                    </a>
                </div>
            @endif
            
            {{-- SuperAdmin Organisaties link --}}
            @if(Auth::user() && Auth::user()->isSuperAdmin())
                <div class="border-t border-gray-200 mt-2 pt-2">
                    <a href="/organisaties" class="flex items-center justify-between px-6 py-3 text-gray-900 font-medium hover:bg-gray-50 {{ request()->is('organisaties*') ? 'bg-blue-50 border-l-4 border-blue-500' : '' }}">
                        Organisaties
                        <span class="ml-1 inline-flex items-center justify-center px-2 py-0.5 text-xs font-medium bg-purple-100 text-purple-700 rounded-full">{{ \App\Models\Organisatie::count() }}</span>
                    </a>
                </div>
            @endif
        </div>
    </div>
    </div>
    <!-- Spacer zodat content niet onder vaste header valt; verhoogd met extra 15px op verzoek -->
    <div class="h-6" style="height:49px;"></div>
    <!-- Layout: Sidebar (md+) + Content -->
    <div class="md:flex">
        <!-- Sidebar (desktop) -->
    <aside class="hidden md:flex md:flex-col bg-white border-r border-gray-200 fixed left-0 right-auto z-50 overflow-y-auto pointer-events-auto" style="top:56px; bottom:0; width:240px;">
            <!-- Logo Sidebar - WIJZIGBAAR via branding -->
            <div class="flex items-center justify-center py-4 px-3 border-b border-gray-100">
                @if(isset($organisatieBranding) && $organisatieBranding && $organisatieBranding->logo_pad)
                    <img src="{{ asset('storage/' . $organisatieBranding->logo_pad) }}" alt="Organisatie Logo" style="height: 80px; width: auto;">
                @else
                    <img src="{{ asset('images/performancepulse-logo.png') }}" alt="Performancepulse" style="height: 80px; width: auto;">
                @endif
            </div>
            
            <nav class="flex-1 px-0 pt-0 pb-1 space-y-6">
                <a href="{{ route('dashboard.index') }}" class="relative flex items-center gap-3 pl-24 pr-3 py-2 transition-colors {{ request()->is('dashboard*') ? 'text-gray-900' : 'text-gray-700 hover:text-gray-900' }}" style="margin-top:24px;padding-left:48px;{{ request()->is('dashboard*') ? 'background:' . ($organisatieBranding->sidebar_actief_achtergrond ?? '#f6fbfe') : '' }}">
                    @if(request()->is('dashboard*'))
                        <span style="position:absolute;left:0;top:0;bottom:0;width:5px;background:{{ $organisatieBranding->sidebar_actief_lijn ?? '#c1dfeb' }};"></span>
                    @endif
                    <svg width="22" height="22" fill="none" viewBox="0 0 20 20"><path d="M3 9.5L10 4l7 5.5V16a1 1 0 0 1-1 1h-3.5a.5.5 0 0 1-.5-.5V13a1 1 0 0 0-1-1h-2a1 1 0 0 0-1 1v3.5a.5.5 0 0 1-.5.5H4a1 1 0 0 1-1-1V9.5z" stroke="#9bb3bd" stroke-width="1.5"/></svg>
                    <span class="font-medium text-[17px]">Dashboard</span>
                </a>
                                @if(Auth::user() && Auth::user()->role === 'klant')
                {{-- Klanten gaan naar hun klant show pagina (profiel overzicht) --}}
                @php
                    $ingelogdeKlant = \App\Models\Klant::where('email', Auth::user()->email)->first();
                @endphp
                @if($ingelogdeKlant)
                <a href="{{ route('klanten.show', $ingelogdeKlant->id) }}" class="relative flex items-center gap-3 pl-24 pr-3 py-2 transition-colors {{ request()->is('klanten/' . $ingelogdeKlant->id) ? 'text-gray-900' : 'text-gray-700 hover:text-gray-900' }}" style="padding-left:48px;{{ request()->is('klanten/' . $ingelogdeKlant->id) ? 'background:' . ($organisatieBranding->sidebar_actief_achtergrond ?? '#f6fbfe') : '' }}">
                    @if(request()->is('klanten/' . $ingelogdeKlant->id))
                        <span style="position:absolute;left:0;top:0;bottom:0;width:5px;background:{{ $organisatieBranding->sidebar_actief_lijn ?? '#c1dfeb' }};"></span>
                    @endif
                    <svg width="22" height="22" fill="none" viewBox="0 0 20 20"><circle cx="10" cy="7" r="4" stroke="#9bb3bd" stroke-width="1.5"/><path d="M3 17c0-2.5 3-4 7-4s7 1.5 7 4v1a1 1 0 0 1-1 1h-9a1 1 0 0 1-1-1v-1z" stroke="#9bb3bd" stroke-width="1.5"/></svg>
                    <span class="font-medium text-[17px]">Mijn Profiel</span>
                </a>
                @endif
                @endif
                @if(Auth::user() && (Auth::user()->isBeheerder() || Auth::user()->isMedewerker()))
                    @include('components.sidebar-notes-tab')
                @endif
                
                {{-- Klanten - alleen tonen als feature actief is --}}
                @hasFeature('klantenbeheer')
                @if(Auth::user() && !Auth::user()->isKlant())
                <a href="/klanten" class="relative flex items-center gap-3 pl-24 pr-3 py-2 transition-colors {{ request()->is('klanten*') ? 'text-gray-900' : 'text-gray-700 hover:text-gray-900' }}" style="padding-left:48px;{{ request()->is('klanten*') ? 'background:' . ($organisatieBranding->sidebar_actief_achtergrond ?? '#f6fbfe') : '' }}">
                    @if(request()->is('klanten*'))
                        <span style="position:absolute;left:0;top:0;bottom:0;width:5px;background:{{ $organisatieBranding->sidebar_actief_lijn ?? '#c1dfeb' }};"></span>
                    @endif
                    <svg width="22" height="22" viewBox="0 0 20 20" fill="none"><g stroke="#9bb3bd" stroke-width="1.5" fill="none"><circle cx="7" cy="8" r="2.2"/><circle cx="13" cy="8" r="2.2"/><path d="M4.5 15c0-2.1 3.5-3.5 5.5-3.5s5.5 1.4 5.5 3.5v1a1 1 0 0 1-1 1h-9a1 1 0 0 1-1-1v-1z"/></g></svg>
                    <span class="font-medium text-[17px]">Klanten</span>
                    <span class="ml-1 inline-flex items-center justify-center px-2 py-0.5 text-xs font-medium bg-[#c1dfeb] text-[#08474f] rounded-full">{{ \App\Models\Klant::where('organisatie_id', auth()->user()->organisatie_id)->count() }}</span>
                </a>
                @endif
                @endhasFeature
                
                {{-- Medewerkers - alleen tonen als feature actief is --}}
                @hasFeature('medewerkerbeheer')
                @if(Auth::user() && Auth::user()->isBeheerder())
                <a href="/medewerkers" class="relative flex items-center gap-3 pl-24 pr-3 py-2 transition-colors {{ request()->is('medewerkers*') ? 'text-gray-900' : 'text-gray-700 hover:text-gray-900' }}" style="padding-left:48px;{{ request()->is('medewerkers*') ? 'background:' . ($organisatieBranding->sidebar_actief_achtergrond ?? '#f6fbfe') : '' }}">
                    @if(request()->is('medewerkers*'))
                        <span style="position:absolute;left:0;top:0;bottom:0;width:5px;background:{{ $organisatieBranding->sidebar_actief_lijn ?? '#c1dfeb' }};"></span>
                    @endif
                    <svg width="22" height="22" fill="none" viewBox="0 0 20 20"><rect x="4" y="8" width="12" height="7" rx="2" stroke="#9bb3bd" stroke-width="1.5"/><path d="M8 8V6.5A2.5 2.5 0 0 1 10.5 4h-1A2.5 2.5 0 0 1 12 6.5V8" stroke="#9bb3bd" stroke-width="1.5"/></svg>
                    <span class="font-medium text-[17px]">Medewerkers</span>
                    <span class="ml-1 inline-flex items-center justify-center px-2 py-0.5 text-xs font-medium bg-[#c1dfeb] text-[#08474f] rounded-full">{{ \App\Models\User::where('role', '!=', 'klant')->where('organisatie_id', auth()->user()->organisatie_id)->count() }}</span>
                </a>
                @endif
                @endhasFeature
                
                {{-- Instagram - alleen tonen als feature actief is --}}
                @hasFeature('instagram')
                @if(Auth::user() && Auth::user()->isBeheerder())
                <a href="/instagram" class="relative flex items-center gap-3 pl-24 pr-3 py-2 transition-colors {{ request()->is('instagram*') ? 'text-gray-900' : 'text-gray-700 hover:text-gray-900' }}" style="padding-left:48px;{{ request()->is('instagram*') ? 'background:#f6fbfe' : '' }}">
                    @if(request()->is('instagram*'))
                        <span style="position:absolute;left:0;top:0;bottom:0;width:5px;background:#c1dfeb;"></span>
                    @endif
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#9bb3bd" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17" cy="7" r="1"/></svg>
                    <span class="font-medium text-[17px]">Instagram</span>
                    <span class="ml-px inline-flex items-center justify-center px-2 py-0.5 text-xs font-medium bg-[#c1dfeb] text-[#08474f] rounded-full">{{ \App\Models\InstagramPost::count() }}</span>
                </a>
                @endif
                @endhasFeature
                
                {{-- Nieuwsbrief - alleen tonen als feature actief is --}}
                @hasFeature('nieuwsbrief')
                @if(Auth::user() && Auth::user()->isBeheerder())
                <a href="/nieuwsbrieven" class="relative flex items-center gap-3 pl-24 pr-3 py-2 transition-colors {{ (request()->is('nieuwsbrieven*') || request()->is('newsletters*') || request()->is('nieuwsbrief*')) ? 'text-gray-900' : 'text-gray-700 hover:text-gray-900' }}" style="padding-left:48px;{{ (request()->is('nieuwsbrieven*') || request()->is('newsletters*') || request()->is('nieuwsbrief*')) ? 'background:#f6fbfe' : '' }}">
                    @if(request()->is('nieuwsbrief*') || request()->is('nieuwsbrieven*') || request()->is('newsletters*'))
                        <span style="position:absolute;left:0;top:0;bottom:0;width:5px;background:#c1dfeb;"></span>
                    @endif
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#9bb3bd" stroke-width="1.5"><path d="M4 6h16v12H4z"/><path d="M4 6l8 6 8-6"/></svg>
                    <span class="font-medium text-[17px]">Nieuwsbrief</span>
                    <span class="ml-px inline-flex items-center justify-center px-2 py-0.5 text-xs font-medium bg-[#c1dfeb] text-[#08474f] rounded-full">{{ \App\Models\Newsletter::count() }}</span>
                </a>
                @endif
                @endhasFeature
                
                {{-- Sjablonen - alleen tonen als feature actief is --}}
                @hasFeature('sjablonen')
                @if(Auth::user() && Auth::user()->isBeheerder())
                <a href="/sjablonen" class="relative flex items-center gap-3 pl-24 pr-3 py-2 transition-colors {{ request()->is('sjablonen*') ? 'text-gray-900' : 'text-gray-700 hover:text-gray-900' }}" style="padding-left:48px;{{ request()->is('sjablonen*') ? 'background:#f6fbfe' : '' }}">
                    @if(request()->is('sjablonen*'))
                        <span style="position:absolute;left:0;top:0;bottom:0;width:5px;background:#c1dfeb;"></span>
                    @endif
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#9bb3bd" stroke-width="1.5"><rect x="3" y="4" width="18" height="6" rx="1"/><rect x="3" y="14" width="10" height="6" rx="1"/></svg>
                    <span class="font-medium text-[17px]">Sjablonen</span>
                    <span class="ml-1 inline-flex items-center justify-center px-2 py-0.5 text-xs font-medium bg-[#c1dfeb] text-[#08474f] rounded-full">{{ \App\Models\Sjabloon::count() }}</span>
                </a>
                @endif
                @endhasFeature
                
                {{-- Prestaties - alleen tonen als feature actief is en voor medewerkers --}}
                @hasFeature('prestaties')
                @if(Auth::user() && (Auth::user()->isBeheerder() || Auth::user()->isMedewerker()))
                <a href="/prestaties" class="relative flex items-center gap-3 pl-24 pr-3 py-2 transition-colors {{ request()->is('prestaties*') || request()->is('admin/prestaties*') ? 'text-gray-900' : 'text-gray-700 hover:text-gray-900' }}" style="padding-left:48px;{{ request()->is('prestaties*') || request()->is('admin/prestaties*') ? 'background:#f6fbfe' : '' }}">
                    @if(request()->is('prestaties*') || request()->is('admin/prestaties*'))
                        <span style="position:absolute;left:0;top:0;bottom:0;width:5px;background:#c1dfeb;"></span>
                    @endif
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#9bb3bd" stroke-width="1.5"><path d="M3 13h4l3-9 4 18 3-9h4"/></svg>
                    <span class="font-medium text-[17px]">Prestaties</span>
                    @if(Auth::user()->isMedewerker() && !Auth::user()->isBeheerder())
                        @php
                            $huidigKwartaal = 'Q' . now()->quarter;
                            $mijnPrestaties = \App\Models\Prestatie::where('user_id', Auth::id())
                                ->where('jaar', now()->year)
                                ->where('kwartaal', $huidigKwartaal)
                                ->count();
                        @endphp
                        @if($mijnPrestaties > 0)
                            <span class="ml-1 inline-flex items-center justify-center px-2 py-0.5 text-xs font-medium bg-[#c1dfeb] text-[#08474f] rounded-full">{{ $mijnPrestaties }}</span>
                        @endif
                    @endif
                </a>
                @endif
                @endhasFeature
                
                {{-- Analytics - alleen tonen als feature actief is en net boven Beheer sectie --}}
                @hasFeature('analytics')
                @if(Auth::user() && (Auth::user()->isBeheerder() || Auth::user()->isMedewerker()))
                <div class="mt-6">
                    <a href="/admin/analytics" class="relative flex items-center gap-3 pl-24 pr-3 py-2 transition-colors {{ request()->is('admin/analytics*') ? 'text-gray-900' : 'text-gray-700 hover:text-gray-900' }}" style="padding-left:48px;{{ request()->is('admin/analytics*') ? 'background:#f6fbfe' : '' }}">
                        @if(request()->is('admin/analytics*'))
                            <span style="position:absolute;left:0;top:0;bottom:0;width:5px;background:#c1dfeb;"></span>
                        @endif
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#9bb3bd" stroke-width="1.5"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                        <span class="font-medium text-[17px]">Analytics</span>
                    </a>
                </div>
                @endif
                @endhasFeature
            
            <!-- Beheer-tabblad echt helemaal onderaan -->
            @if(Auth::user() && Auth::user()->isBeheerder())
                <div class="mt-8">
                    @include('components.sidebar-admin-tab')
                </div>
            @endif
            
            <!-- SuperAdmin Organisaties link -->
            @if(Auth::user() && Auth::user()->isSuperAdmin())
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <a href="/organisaties" class="relative flex items-center gap-3 pl-24 pr-3 py-2 transition-colors {{ request()->is('organisaties*') ? 'text-gray-900' : 'text-gray-700 hover:text-gray-900' }}" style="padding-left:48px;{{ request()->is('organisaties*') ? 'background:#f6fbfe' : '' }}">
                        @if(request()->is('organisaties*'))
                            <span style="position:absolute;left:0;top:0;bottom:0;width:5px;background:#c1dfeb;"></span>
                        @endif
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#9bb3bd" stroke-width="1.5"><path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        <span class="font-medium text-[17px]">Organisaties</span>
                        <span class="ml-1 inline-flex items-center justify-center px-2 py-0.5 text-xs font-medium bg-purple-100 text-purple-700 rounded-full">{{ \App\Models\Organisatie::count() }}</span>
                    </a>
                </div>
            @endif
            
            <!-- Footer Logo onderaan sidebar - VAST (niet wijzigbaar) -->
            <div class="mt-auto pt-2 pb-2 px-7 border-t border-gray-200" style="background: transparent;">
                <div class="flex items-center justify-center" style="position: relative; z-index: 10;">
                    <img src="{{ asset('images/sidebar-footer-logo.png') }}" alt="Footer Logo" style="max-width: 180px; width: 100%; height: auto; opacity: 0.7; position: relative; z-index: 10;">
                </div>
            </div>
            </nav>
                </aside>

        <!-- Main content -->
    <main id="app-main" class="flex-1 relative z-0 px-4 py-6 md:px-8">
                {{-- Render optional header slot (used by <x-app-layout>) --}}
                @if(View::hasSection('header') || isset($header))
                    <header class="mb-6">
                        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                            {!! $header ?? '' !!}
                        </div>
                    </header>
                @endif

                {{-- Support both component-based slots and traditional section('content') --}}
                {!! $slot ?? '' !!}
                @yield('content')
        </main>
    </div>
        <!-- Flowbite JS voor interactieve componenten -->
        <script src="https://unpkg.com/flowbite@2.3.0/dist/flowbite.min.js"></script>
    {{-- Mobile + Profile dropdown JS --}}
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                console.log('🔧 Mobile JS loaded');
                
                // Mobile navigation toggle
                const navToggle = document.getElementById('nav-toggle');
                const mobileNav = document.getElementById('mobile-nav');
                
                console.log('🔧 Elements found:', {
                    navToggle: !!navToggle,
                    mobileNav: !!mobileNav,
                    navToggleVisible: navToggle ? window.getComputedStyle(navToggle).display !== 'none' : false
                });
                
                if (navToggle && mobileNav) {
                    navToggle.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        console.log('🍔 Hamburger clicked!');
                        
                        const wasHidden = mobileNav.classList.contains('hidden');
                        mobileNav.classList.toggle('hidden');
                        
                        console.log('📱 Mobile nav state:', {
                            wasHidden: wasHidden,
                            nowHidden: mobileNav.classList.contains('hidden'),
                            topValue: window.getComputedStyle(mobileNav).top
                        });
                    });
                    
                    // Close mobile nav when clicking outside
                    document.addEventListener('click', function(e) {
                        if (!navToggle.contains(e.target) && !mobileNav.contains(e.target)) {
                            mobileNav.classList.add('hidden');
                        }
                    });
                }
                
                // Profile dropdown
                const menuButton = document.getElementById('user-menu-button');
                const userMenu = document.getElementById('user-menu');
                
                console.log('🔧 Profile elements found:', {
                    menuButton: !!menuButton,
                    userMenu: !!userMenu
                });
                
                if (menuButton && userMenu) {
                    menuButton.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        console.log('👤 Profile button clicked!');
                        
                        const wasHidden = userMenu.classList.contains('hidden');
                        userMenu.classList.toggle('hidden');
                        
                        console.log('📋 Profile menu state:', {
                            wasHidden: wasHidden,
                            nowHidden: userMenu.classList.contains('hidden')
                        });
                    });
                    
                    // Close profile dropdown when clicking outside
                    document.addEventListener('click', function(event) {
                        if (!menuButton.contains(event.target) && !userMenu.contains(event.target)) {
                            userMenu.classList.add('hidden');
                        }
                    });
                }
            });
        </script>
    {{-- Render any pushed scripts from views (e.g. upload drag/drop) --}}
    @stack('scripts')
    @yield('scripts')
</body>
</html>
