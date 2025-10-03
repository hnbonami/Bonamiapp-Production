{{-- ADMIN SIDEBAR - ALLE FUNCTIES ZICHTBAAR --}}
@auth
    <div class="flex-shrink-0 w-64 bg-white shadow-lg">
        <div class="flex flex-col h-full">
            <div class="flex-1 flex flex-col pt-5 pb-4 overflow-y-auto">
                <nav class="mt-5 flex-1 px-2 space-y-1">
                    {{-- Dashboard - altijd zichtbaar --}}
                    <a href="{{ route('dashboard') }}" class="text-gray-600 hover:bg-gray-50 hover:text-gray-900 group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('dashboard') ? 'bg-gray-100 text-gray-900' : '' }}">
                        <svg class="text-gray-400 mr-3 h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"/>
                        </svg>
                        Dashboard
                    </a>
                    
                    {{-- Klanten - ALTIJD ZICHTBAAR --}}
                    <a href="{{ route('klanten.index') }}" class="text-gray-600 hover:bg-gray-50 hover:text-gray-900 group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('klanten.*') ? 'bg-gray-100 text-gray-900' : '' }}">
                        <svg class="text-gray-400 mr-3 h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/>
                        </svg>
                        Klanten
                    </a>
                    
                    {{-- Inspanningstests - ALTIJD ZICHTBAAR --}}
                    <a href="{{ route('inspanningstests.index') }}" class="text-gray-600 hover:bg-gray-50 hover:text-gray-900 group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('inspanningstests.*') ? 'bg-gray-100 text-gray-900' : '' }}">
                        <svg class="text-gray-400 mr-3 h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        Inspanningstests
                    </a>
                    
                    {{-- Medewerkers - ALTIJD ZICHTBAAR --}}
                    <a href="{{ route('medewerkers.index') }}" class="text-gray-600 hover:bg-gray-50 hover:text-gray-900 group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('medewerkers.*') ? 'bg-gray-100 text-gray-900' : '' }}">
                        <svg class="text-gray-400 mr-3 h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        Medewerkers
                    </a>
                    
                    {{-- Nieuwsbrief - ALTIJD ZICHTBAAR --}}
                    <a href="{{ route('nieuwsbrief') }}" class="text-gray-600 hover:bg-gray-50 hover:text-gray-900 group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('nieuwsbrief') ? 'bg-gray-100 text-gray-900' : '' }}">
                        <svg class="text-gray-400 mr-3 h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                        </svg>
                        Nieuwsbrief
                    </a>
                    
                    {{-- Instagram - ALTIJD ZICHTBAAR --}}
                    <a href="{{ route('instagram.index') }}" class="text-gray-600 hover:bg-gray-50 hover:text-gray-900 group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('instagram.*') ? 'bg-gray-100 text-gray-900' : '' }}">
                        <svg class="text-gray-400 mr-3 h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Instagram
                    </a>
                    
                    {{-- Profiel - altijd zichtbaar --}}
                    <a href="{{ route('profile.edit') }}" class="text-gray-600 hover:bg-gray-50 hover:text-gray-900 group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('profile.*') ? 'bg-gray-100 text-gray-900' : '' }}">
                        <svg class="text-gray-400 mr-3 h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Profiel
                    </a>
                    
                    {{-- Debug info voor admin --}}
                    @if(app()->environment('local'))
                        <div class="mt-4 p-2 bg-blue-50 rounded text-xs text-blue-600">
                            <strong>DEBUG:</strong><br>
                            User: {{ auth()->user()->name }}<br>
                            Email: {{ auth()->user()->email }}<br>
                            ID: {{ auth()->user()->id }}
                        </div>
                    @endif
                </nav>
            </div>
        </div>
    </div>
@endauth