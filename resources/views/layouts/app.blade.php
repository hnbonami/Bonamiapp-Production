@php
    if (!isset($route)) {
        $route = request()->path();
    }
@endphp
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Bonami.app')</title>
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon.png?s=32">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon.png?s=16">
    <link rel="shortcut icon" type="image/png" href="/favicon.png?s=32">
    <link rel="apple-touch-icon" href="/favicon.png?s=180">
    <!-- Bunny Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,800|georgia:400,700|times-new-roman:400,700|arial:400,700|courier-new:400,700&display=swap" rel="stylesheet" />
            <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
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
        #topbar-avatar, #topbar-avatar-placeholder {
            width: 36px !important;
            height: 36px !important;
            border-radius: 50%;
            margin-right: 8px;
            flex-shrink: 0;
        }
        
        /* Remove the problematic profile click icon on mobile */
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
    <div id="topbar" class="w-full fixed top-0 left-0 right-0 z-40" style="background:#c8e1eb;">
        <div class="topbar-grid">
            <!-- Left: Hamburger (mobile only) -->
            <div class="flex items-center">
                <button id="nav-toggle" class="md:hidden text-gray-800 focus:outline-none" aria-label="Menu">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>
            
            <!-- Center: Logo -->
            <div class="topbar-logo">
                <img src="/logo_bonami.png" alt="Logo" style="height:35px;" />
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
                            @if(Auth::user()->role === 'klant')
                                @php
                                    $menuKlant = \App\Models\Klant::where('email', Auth::user()->email)->first();
                                @endphp
                                <a href="{{ $menuKlant ? route('klanten.show', $menuKlant->id) : route('dashboard') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-50">Mijn profiel</a>
                            @else
                                <a href="/instellingen" class="block px-4 py-2 text-gray-700 hover:bg-gray-50">Mijn profiel</a>
                            @endif
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="block w-full text-left px-4 py-2 text-rose-600 hover:bg-rose-50">Uitloggen</button>
                            </form>
                        </div>
                    </div>
                @endauth
            </div>
        </div>
    <!-- Mobile nav dropdown onder de bovenbalk (vast onder de header) -->
    <div id="mobile-nav" class="md:hidden hidden fixed left-0 right-0 z-30 bg-white border-b border-gray-200 shadow-lg overflow-y-auto" style="top: 56px; max-height: calc(100vh - 56px);">
        <div class="py-2">
            <a href="{{ route('dashboard') }}" class="block px-6 py-3 text-gray-900 font-medium hover:bg-gray-50 {{ str_starts_with($routeName, 'dashboard') ? 'bg-blue-50 border-l-4 border-blue-500' : '' }}">
                Dashboard
            </a>
            
            @if(Auth::user() && Auth::user()->role === 'klant')
                @php
                    $mobileKlant = \App\Models\Klant::where('email', Auth::user()->email)->first();
                @endphp
                <a href="{{ $mobileKlant ? route('klanten.show', $mobileKlant->id) : route('dashboard') }}" class="block px-6 py-3 text-gray-900 font-medium hover:bg-gray-50 {{ request()->is('klanten/*') ? 'bg-blue-50 border-l-4 border-blue-500' : '' }}">
                    Profiel
                </a>
            @endif            @if(Auth::user() && in_array(Auth::user()->role, ['admin', 'medewerker']))
                <a href="/staff-notes" class="flex items-center justify-between px-6 py-3 text-gray-900 font-medium hover:bg-gray-50 {{ request()->is('staff-notes*') ? 'bg-blue-50 border-l-4 border-blue-500' : '' }}">
                    Notities
                    @php $unreadNotes = \App\Models\StaffNote::where('is_new', true)->count(); @endphp
                    @if($unreadNotes > 0)
                        <span class="ml-1 inline-flex items-center justify-center px-2 py-0.5 text-xs font-medium bg-red-500 text-white rounded-full">{{ $unreadNotes }}</span>
                    @endif
                </a>
            @endif
            
            @if(Auth::user() && Auth::user()->role !== 'klant')
                <a href="/klanten" class="flex items-center justify-between px-6 py-3 text-gray-900 font-medium hover:bg-gray-50 {{ str_starts_with($routeName, 'klanten') ? 'bg-blue-50 border-l-4 border-blue-500' : '' }}">
                    Klanten 
                    <span class="ml-1 inline-flex items-center justify-center px-2 py-0.5 text-xs font-medium bg-[#c1dfeb] text-[#08474f] rounded-full">{{ \App\Models\Klant::count() }}</span>
                </a>
            @endif
            
            @if(Auth::user() && Auth::user()->role === 'admin')
                <a href="/medewerkers" class="flex items-center justify-between px-6 py-3 text-gray-900 font-medium hover:bg-gray-50 {{ str_starts_with($routeName, 'medewerkers') ? 'bg-blue-50 border-l-4 border-blue-500' : '' }}">
                    Medewerkers 
                    <span class="ml-1 inline-flex items-center justify-center px-2 py-0.5 text-xs font-medium bg-[#c1dfeb] text-[#08474f] rounded-full">{{ \App\Models\Medewerker::count() }}</span>
                </a>
            @endif
            
            @if(Auth::user() && Auth::user()->role === 'admin')
                <a href="/instagram" class="flex items-center justify-between px-6 py-3 text-gray-900 font-medium hover:bg-gray-50 {{ request()->is('instagram*') ? 'bg-blue-50 border-l-4 border-blue-500' : '' }}">
                    Instagram
                    <span class="ml-1 inline-flex items-center justify-center px-2 py-0.5 text-xs font-medium bg-[#c1dfeb] text-[#08474f] rounded-full">{{ \App\Models\InstagramPost::count() }}</span>
                </a>
                
                <a href="/nieuwsbrieven" class="flex items-center justify-between px-6 py-3 text-gray-900 font-medium hover:bg-gray-50 {{ (request()->is('nieuwsbrieven*') || request()->is('newsletters*') || request()->is('nieuwsbrief*')) ? 'bg-blue-50 border-l-4 border-blue-500' : '' }}">
                    Nieuwsbrief
                    <span class="ml-1 inline-flex items-center justify-center px-2 py-0.5 text-xs font-medium bg-[#c1dfeb] text-[#08474f] rounded-full">{{ \App\Models\Newsletter::count() }}</span>
                </a>
                
                <a href="/sjablonen" class="flex items-center justify-between px-6 py-3 text-gray-900 font-medium hover:bg-gray-50 {{ request()->is('sjablonen*') ? 'bg-blue-50 border-l-4 border-blue-500' : '' }}">
                    Sjablonen 
                    <span class="ml-1 inline-flex items-center justify-center px-2 py-0.5 text-xs font-medium bg-[#c1dfeb] text-[#08474f] rounded-full">{{ \App\Models\Sjabloon::count() }}</span>
                </a>
            @endif
            
            @if(Auth::user() && Auth::user()->role === 'admin')
                <div class="border-t border-gray-200 mt-2 pt-2">
                    <div class="px-6 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">Beheer</div>
                    <a href="/users" class="flex items-center justify-between px-6 py-3 text-gray-900 font-medium hover:bg-gray-50 {{ request()->is('users*') ? 'bg-blue-50 border-l-4 border-blue-500' : '' }}">
                        Gebruikers
                        <span class="ml-1 inline-flex items-center justify-center px-2 py-0.5 text-xs font-medium bg-[#c1dfeb] text-[#08474f] rounded-full">{{ \App\Models\User::count() }}</span>
                    </a>
                    <a href="/testzadels" class="flex items-center justify-between px-6 py-3 text-gray-900 font-medium hover:bg-gray-50 {{ request()->is('testzadels*') ? 'bg-blue-50 border-l-4 border-blue-500' : '' }}">
                        Testzadels
                        <span class="ml-1 inline-flex items-center justify-center px-2 py-0.5 text-xs font-medium bg-[#c1dfeb] text-[#08474f] rounded-full">{{ \App\Models\Testzadel::count() }}</span>
                    </a>
                    <a href="/email-integratie" class="block px-6 py-3 text-gray-900 font-medium hover:bg-gray-50 {{ request()->is('email-integratie*') ? 'bg-blue-50 border-l-4 border-blue-500' : '' }}">
                        Email Integratie
                    </a>
                    <a href="/database-backup" class="block px-6 py-3 text-gray-900 font-medium hover:bg-gray-50 {{ request()->is('database-backup*') ? 'bg-blue-50 border-l-4 border-blue-500' : '' }}">
                        Database Backup
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
            <nav class="flex-1 px-0 pt-0 pb-1 space-y-6">
                <a href="{{ route('dashboard') }}" class="relative flex items-center gap-3 pl-24 pr-3 py-2 transition-colors {{ request()->is('dashboard*') ? 'text-gray-900' : 'text-gray-700 hover:text-gray-900' }}" style="margin-top:24px;padding-left:48px;{{ request()->is('dashboard*') ? 'background:#f6fbfe' : '' }}">
                    @if(request()->is('dashboard*'))
                        <span style="position:absolute;left:0;top:0;bottom:0;width:5px;background:#c1dfeb;"></span>
                    @endif
                    <svg width="22" height="22" fill="none" viewBox="0 0 20 20"><path d="M3 9.5L10 4l7 5.5V16a1 1 0 0 1-1 1h-3.5a.5.5 0 0 1-.5-.5V13a1 1 0 0 0-1-1h-2a1 1 0 0 0-1 1v3.5a.5.5 0 0 1-.5.5H4a1 1 0 0 1-1-1V9.5z" stroke="#9bb3bd" stroke-width="1.5"/></svg>
                    <span class="font-medium text-[17px]">Dashboard</span>
                </a>
                                @if(Auth::user() && Auth::user()->role === 'klant')
                @php
                    $sidebarKlant = \App\Models\Klant::where('email', Auth::user()->email)->first();
                @endphp
                <a href="{{ $sidebarKlant ? route('klanten.show', $sidebarKlant->id) : route('dashboard') }}" class="relative flex items-center gap-3 pl-24 pr-3 py-2 transition-colors {{ request()->is('klanten/*') ? 'text-gray-900' : 'text-gray-700 hover:text-gray-900' }}" style="padding-left:48px;{{ request()->is('klanten/*') ? 'background:#f6fbfe' : '' }}">
                    @if(request()->is('klanten/*'))
                        <span style="position:absolute;left:0;top:0;bottom:0;width:5px;background:#c1dfeb;"></span>
                    @endif
                    <svg width="22" height="22" fill="none" viewBox="0 0 20 20"><circle cx="10" cy="7" r="4" stroke="#9bb3bd" stroke-width="1.5"/><path d="M3 17c0-2.5 3-4 7-4s7 1.5 7 4" stroke="#9bb3bd" stroke-width="1.5"/></svg>
                    <span class="font-medium text-[17px]">Profiel</span>
                </a>
                @endif
                @if(Auth::user() && in_array(Auth::user()->role, ['admin', 'medewerker']))
                    @include('components.sidebar-notes-tab')
                @endif
                @if(Auth::user() && Auth::user()->role !== 'klant')
                <a href="/klanten" class="relative flex items-center gap-3 pl-24 pr-3 py-2 transition-colors {{ request()->is('klanten*') ? 'text-gray-900' : 'text-gray-700 hover:text-gray-900' }}" style="padding-left:48px;{{ request()->is('klanten*') ? 'background:#f6fbfe' : '' }}">
                    @if(request()->is('klanten*'))
                        <span style="position:absolute;left:0;top:0;bottom:0;width:5px;background:#c1dfeb;"></span>
                    @endif
                    <svg width="22" height="22" viewBox="0 0 20 20" fill="none"><g stroke="#9bb3bd" stroke-width="1.5" fill="none"><circle cx="7" cy="8" r="2.2"/><circle cx="13" cy="8" r="2.2"/><path d="M4.5 15c0-2.1 3.5-3.5 5.5-3.5s5.5 1.4 5.5 3.5v1a1 1 0 0 1-1 1h-9a1 1 0 0 1-1-1v-1z"/></g></svg>
                    <span class="font-medium text-[17px]">Klanten</span>
                    <span class="ml-1 inline-flex items-center justify-center px-2 py-0.5 text-xs font-medium bg-[#c1dfeb] text-[#08474f] rounded-full">{{ \App\Models\Klant::count() }}</span>
                </a>
                @endif
                @if(Auth::user() && Auth::user()->role === 'admin')
                <a href="/medewerkers" class="relative flex items-center gap-3 pl-24 pr-3 py-2 transition-colors {{ request()->is('medewerkers*') ? 'text-gray-900' : 'text-gray-700 hover:text-gray-900' }}" style="padding-left:48px;{{ request()->is('medewerkers*') ? 'background:#f6fbfe' : '' }}">
                    @if(request()->is('medewerkers*'))
                        <span style="position:absolute;left:0;top:0;bottom:0;width:5px;background:#c1dfeb;"></span>
                    @endif
                    <svg width="22" height="22" fill="none" viewBox="0 0 20 20"><rect x="4" y="8" width="12" height="7" rx="2" stroke="#9bb3bd" stroke-width="1.5"/><path d="M8 8V6.5A2.5 2.5 0 0 1 10.5 4h-1A2.5 2.5 0 0 1 12 6.5V8" stroke="#9bb3bd" stroke-width="1.5"/></svg>
                    <span class="font-medium text-[17px]">Medewerkers</span>
                    <span class="ml-1 inline-flex items-center justify-center px-2 py-0.5 text-xs font-medium bg-[#c1dfeb] text-[#08474f] rounded-full">{{ \App\Models\Medewerker::count() }}</span>
                </a>
                @endif
            <!-- Beheer-tabblad helemaal onderaan -->
            @if(Auth::user() && Auth::user()->role === 'admin')
            @endif
                @if(Auth::user() && Auth::user()->role === 'admin')
                <!-- Instagram -->
                <a href="/instagram" class="relative flex items-center gap-3 pl-24 pr-3 py-2 transition-colors {{ request()->is('instagram*') ? 'text-gray-900' : 'text-gray-700 hover:text-gray-900' }}" style="padding-left:48px;{{ request()->is('instagram*') ? 'background:#f6fbfe' : '' }}">
                    @if(request()->is('instagram*'))
                        <span style="position:absolute;left:0;top:0;bottom:0;width:5px;background:#c1dfeb;"></span>
                    @endif
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#9bb3bd" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17" cy="7" r="1"/></svg>
                    <span class="font-medium text-[17px]">Instagram</span>
                    <span class="ml-px inline-flex items-center justify-center px-2 py-0.5 text-xs font-medium bg-[#c1dfeb] text-[#08474f] rounded-full">{{ \App\Models\InstagramPost::count() }}</span>
                </a>
                <!-- Nieuwsbrief -->
                <a href="/nieuwsbrieven" class="relative flex items-center gap-3 pl-24 pr-3 py-2 transition-colors {{ (request()->is('nieuwsbrieven*') || request()->is('newsletters*') || request()->is('nieuwsbrief*')) ? 'text-gray-900' : 'text-gray-700 hover:text-gray-900' }}" style="padding-left:48px;{{ (request()->is('nieuwsbrieven*') || request()->is('newsletters*') || request()->is('nieuwsbrief*')) ? 'background:#f6fbfe' : '' }}">
                    @if(request()->is('nieuwsbrief*') || request()->is('nieuwsbrieven*') || request()->is('newsletters*'))
                        <span style="position:absolute;left:0;top:0;bottom:0;width:5px;background:#c1dfeb;"></span>
                    @endif
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#9bb3bd" stroke-width="1.5"><path d="M4 6h16v12H4z"/><path d="M4 6l8 6 8-6"/></svg>
                    <span class="font-medium text-[17px]">Nieuwsbrief</span>
                    <span class="ml-px inline-flex items-center justify-center px-2 py-0.5 text-xs font-medium bg-[#c1dfeb] text-[#08474f] rounded-full">{{ \App\Models\Newsletter::count() }}</span>
                </a>
                <!-- Sjablonen (report templates) -->
                <a href="/sjablonen" class="relative flex items-center gap-3 pl-24 pr-3 py-2 transition-colors {{ request()->is('sjablonen*') ? 'text-gray-900' : 'text-gray-700 hover:text-gray-900' }}" style="padding-left:48px;{{ request()->is('sjablonen*') ? 'background:#f6fbfe' : '' }}">
                    @if(request()->is('sjablonen*'))
                        <span style="position:absolute;left:0;top:0;bottom:0;width:5px;background:#c1dfeb;"></span>
                    @endif
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#9bb3bd" stroke-width="1.5"><rect x="3" y="4" width="18" height="6" rx="1"/><rect x="3" y="14" width="10" height="6" rx="1"/></svg>
                    <span class="font-medium text-[17px]">Sjablonen</span>
                    <span class="ml-1 inline-flex items-center justify-center px-2 py-0.5 text-xs font-medium bg-[#c1dfeb] text-[#08474f] rounded-full">{{ \App\Models\Sjabloon::count() }}</span>
                </a>
                @endif
            <!-- Beheer-tabblad echt helemaal onderaan -->
            @if(Auth::user() && Auth::user()->role === 'admin')
                <div class="mt-8">
                    @include('components.sidebar-admin-tab')
                </div>
            @endif
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
