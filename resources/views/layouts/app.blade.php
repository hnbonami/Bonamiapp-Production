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
          /* Topbar: keep fixed height and z-index. Let the container pass pointer
              events through so content beneath is clickable, but re-enable
              pointer events for interactive header children only. */
          #topbar { height:56px; z-index: 40; /* pointer-events: none; */ }
          /* Make only actual header controls interactive so clicks on the
              central header area pass through to the page beneath. */
          #topbar button, #topbar a, #topbar img, #topbar input, #nav-toggle, #user-menu-button { pointer-events: auto; }
        
        /* ALLEEN topbar avatar klein houden - nergens anders! */
        #topbar-avatar, #topbar-avatar-placeholder {
            width: 36px !important;
            height: 36px !important;
            max-width: 36px !important;
            max-height: 36px !important;
            min-width: 36px !important;
            min-height: 36px !important;
        }
        
        /* Desktop: zorg dat de content rechts naast de sidebar staat */
        @media (min-width: 768px) {
            #app-main { margin-left: 240px; }
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
    </style>
</head>
<body>
    <!-- Bovenste strook met logo, hamburger (mobile) en profielknop rechts -->
    @php $routeName = optional(request()->route())->getName() ?? ''; @endphp
    <div id="topbar" class="w-full fixed top-0 left-0 right-0 z-40" style="background:#c8e1eb;">
        <div class="grid grid-cols-3 h-14 px-4 relative items-center">
            <div class="flex items-center h-14">
                <button id="nav-toggle" class="md:hidden text-gray-800 focus:outline-none p-2" aria-label="Menu">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
            </div>
            <div class="flex items-center justify-center h-14">
                <img src="/logo_bonami.png" alt="Logo" class="block align-middle" style="height:35px; margin:0; display:block;" />
            </div>
            <div class="flex items-center justify-end h-14 relative">
                @auth
                    @php
                        $avatar = Auth::user()->avatar_path ?? null;
                        $voornaam = explode(' ', Auth::user()->name)[0] ?? '';
                    @endphp
                    <div class="relative">
                        <button id="user-menu-button" class="inline-flex items-center h-9 bg-transparent border-none cursor-pointer font-bold text-base text-black px-3 py-0 focus:outline-none leading-none align-middle" style="margin-top:0;">
                            @php
                                $isStaff = Auth::user() && in_array(Auth::user()->role, ['admin', 'medewerker']);
                                $unreadNotes = $isStaff ? \App\Models\StaffNote::where('is_new', true)->count() : 0;
                            @endphp
                            <span class="relative" style="display:inline-block;margin-right:12px;">
                                @if($avatar)
                                    <img id="topbar-avatar" src="{{ asset('storage/'.$avatar) }}" alt="Avatar" class="w-9 h-9 rounded-full object-cover flex-none" style="width:36px;height:36px;border-radius:9999px;object-fit:cover;" />
                                @else
                                    <div id="topbar-avatar-placeholder" class="w-9 h-9 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 font-semibold flex-none" style="width:36px;height:36px;border-radius:9999px;">
                                        {{ strtoupper(substr($voornaam,0,1)) }}
                                    </div>
                                @endif
                                @if($unreadNotes > 0)
                                    <span class="absolute" style="top:-6px;right:-6px;width:20px;height:20px;display:flex;align-items:center;justify-content:center;background:#e11d48;color:#fff;font-size:13px;font-weight:700;border-radius:50%;z-index:20;line-height:20px;box-shadow:0 1px 4px #0002;">{{ $unreadNotes }}</span>
                                @endif
                            </span>
                            <span class="mr-1">{{ $voornaam }}</span>
                            <svg class="w-3 h-3 text-gray-700" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>
                        </button>

                        <!-- Subtiel, zichtbaar zwart icoon rechts van de profielknop; witte achtergrond + border voor contrast -->
                        <button id="profile-click-icon" type="button" class="inline-flex items-center justify-center w-8 h-8 ml-0 text-black opacity-95 hover:opacity-100 focus:outline-none" aria-label="Open profielmenu" title="Open profielmenu" style="pointer-events:auto; transform: translate(-8px, 6px);">
                            <!-- larger black caret only -->
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <polyline points="6 9 12 15 18 9" />
                            </svg>
                        </button>

                        <div id="user-menu" class="hidden absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-lg shadow-lg z-10">
                            <a href="/instellingen" class="block px-4 py-2 text-gray-700 hover:bg-gray-50">                                Mijn profiel</a>
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
    <div id="mobile-nav" class="md:hidden hidden fixed top-14 left-0 right-0 z-30 flex-col gap-2 pb-3 bg-gray-50 border-b border-gray-200" style="padding-left:2em;padding-right:1rem;">
        <a href="{{ route('dashboard') }}" class="block font-semibold text-base py-2 border-b-2 {{ str_starts_with($routeName, 'dashboard') ? 'border-[#c8e1eb] text-black' : 'border-transparent text-gray-900' }} transition-all duration-200 hover:text-black">Dashboard</a>
        @if(Auth::user() && Auth::user()->role !== 'klant')
            <a href="/klanten" class="flex items-center justify-between font-semibold text-base py-2 border-b-2 {{ str_starts_with($routeName, 'klanten') ? 'border-[#c8e1eb] text-black' : 'border-transparent text-gray-900' }} transition-all duration-200 hover:text-black">Klanten <span class="ml-1 inline-flex items-center justify-center px-2 py-0.5 text-xs font-medium bg-[#c1dfeb] text-[#08474f] rounded-full">{{ \App\Models\Klant::count() }}</span></a>
        @endif
        @if(Auth::user() && Auth::user()->role === 'admin')
            <a href="/medewerkers" class="flex items-center justify-between font-semibold text-base py-2 border-b-2 {{ str_starts_with($routeName, 'medewerkers') ? 'border-[#c8e1eb] text-black' : 'border-transparent text-gray-900' }} transition-all duration-200 hover:text-black">Medewerkers <span class="ml-1 inline-flex items-center justify-center px-2 py-0.5 text-xs font-medium bg-[#c1dfeb] text-[#08474f] rounded-full">{{ \App\Models\Medewerker::count() }}</span></a>
        @endif
        @if(Auth::user() && Auth::user()->role !== 'klant')
            <a href="/sjabloon-manager" class="flex items-center justify-between font-semibold text-base py-2 border-b-2 {{ str_starts_with($routeName, 'sjabloon-manager') || str_starts_with($routeName, 'templates') ? 'border-[#c8e1eb] text-black' : 'border-transparent text-gray-900' }} transition-all duration-200 hover:text-black">Sjablonen <span class="ml-1 inline-flex items-center justify-center px-2 py-0.5 text-xs font-medium bg-[#c1dfeb] text-[#08474f] rounded-full">{{ \App\Models\Template::count() }}</span></a>
        @endif
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
                <a href="{{ $sidebarKlant ? route('klanten.show', ['klanten' => $sidebarKlant->id]) : route('dashboard') }}" class="relative flex items-center gap-3 pl-24 pr-3 py-2 transition-colors {{ request()->is('klanten/*') ? 'text-gray-900' : 'text-gray-700 hover:text-gray-900' }}" style="padding-left:48px;{{ request()->is('klanten/*') ? 'background:#f6fbfe' : '' }}">
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
                @if(Auth::user() && Auth::user()->role !== 'klant')
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
    <main id="app-main" class="flex-1 relative z-0" style="padding: 2em;">
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
    {{-- Minimal profile dropdown JS (no AJAX/modal) --}}
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const menuButton = document.getElementById('user-menu-button');
                const userMenu = document.getElementById('user-menu');
                const profileIcon = document.getElementById('profile-click-icon');
                if (menuButton && userMenu) {
                    menuButton.addEventListener('click', function(e) {
                        e.stopPropagation();
                        userMenu.classList.toggle('hidden');
                    });
                    if(profileIcon){
                        profileIcon.addEventListener('click', function(e){
                            e.stopPropagation();
                            userMenu.classList.toggle('hidden');
                            // focus the menu button for accessibility
                            menuButton.focus();
                        });
                    }
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
</body>
</html>
