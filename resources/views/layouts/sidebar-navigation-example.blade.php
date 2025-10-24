{{-- Klanten menu item - alleen tonen als feature actief is --}}
@hasFeature('klantenbeheer')
<a href="{{ route('klanten.index') }}" 
   class="sidebar-link {{ request()->routeIs('klanten.*') ? 'active' : '' }}">
    <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
    </svg>
    <span>Klanten</span>
    <span class="badge">{{ \App\Models\Klant::where('organisatie_id', auth()->user()->organisatie_id)->count() }}</span>
</a>
@endhasFeature

{{-- Bikefits menu item - alleen tonen als feature actief is --}}
@hasFeature('bikefits')
<a href="{{ route('klanten.bikefits.index') }}" 
   class="sidebar-link {{ request()->routeIs('*bikefits*') ? 'active' : '' }}">
    <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
    </svg>
    <span>Bikefit Metingen</span>
</a>
@endhasFeature

{{-- Inspanningstesten menu item - alleen tonen als feature actief is --}}
@hasFeature('inspanningstesten')
<a href="{{ route('klanten.inspanningstesten.index') }}" 
   class="sidebar-link {{ request()->routeIs('*inspanningstesten*') ? 'active' : '' }}">
    <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
    </svg>
    <span>Inspanningstesten</span>
</a>
@endhasFeature

{{-- Veldtesten menu item - alleen tonen als feature actief is --}}
@hasFeature('veldtesten')
<a href="{{ route('klanten.veldtesten.index') }}" 
   class="sidebar-link {{ request()->routeIs('*veldtesten*') ? 'active' : '' }}">
    <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
    </svg>
    <span>Veldtesten</span>
</a>
@endhasFeature

{{-- Testzadels menu item - alleen tonen als feature actief is --}}
@hasFeature('testzadels')
<a href="{{ route('testzadels.index') }}" 
   class="sidebar-link {{ request()->routeIs('testzadels.*') ? 'active' : '' }}">
    <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
    </svg>
    <span>Testzadels</span>
</a>
@endhasFeature

{{-- Sjablonen menu item - alleen tonen als feature actief is --}}
@hasFeature('sjablonen')
<a href="{{ route('sjablonen.index') }}" 
   class="sidebar-link {{ request()->routeIs('sjablonen.*') ? 'active' : '' }}">
    <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
    </svg>
    <span>Sjablonen</span>
</a>
@endhasFeature

{{-- Medewerkers menu item - alleen tonen als feature actief is --}}
@hasFeature('medewerkerbeheer')
<a href="{{ route('medewerkers.index') }}" 
   class="sidebar-link {{ request()->routeIs('medewerkers.*') ? 'active' : '' }}">
    <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
    </svg>
    <span>Medewerkers</span>
</a>
@endhasFeature

{{-- Instagram menu item - alleen tonen als feature actief is --}}
@hasFeature('instagram')
<a href="{{ route('instagram.index') }}" 
   class="sidebar-link {{ request()->routeIs('instagram.*') ? 'active' : '' }}">
    <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
    </svg>
    <span>Instagram</span>
</a>
@endhasFeature

{{-- Nieuwsbrief menu item - alleen tonen als feature actief is --}}
@hasFeature('nieuwsbrief')
<a href="{{ route('nieuwsbrief.index') }}" 
   class="sidebar-link {{ request()->routeIs('nieuwsbrief.*') ? 'active' : '' }}">
    <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
    </svg>
    <span>Nieuwsbrief</span>
</a>
@endhasFeature

{{-- Database Tools menu item - alleen tonen als feature actief is --}}
@hasFeature('database_tools')
<a href="{{ route('database-tools.index') }}" 
   class="sidebar-link {{ request()->routeIs('database-tools.*') ? 'active' : '' }}">
    <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>
    </svg>
    <span>Database Tools</span>
</a>
@endhasFeature

{{-- Analytics menu item - alleen tonen als feature actief is --}}
@hasFeature('analytics')
<a href="{{ route('analytics.index') }}" 
   class="sidebar-link {{ request()->routeIs('analytics.*') ? 'active' : '' }}">
    <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
    </svg>
    <span>Analytics</span>
</a>
@endhasFeature

{{-- API Settings menu item - alleen tonen als feature actief is --}}
@hasFeature('api_toegang')
<a href="{{ route('api.settings') }}" 
   class="sidebar-link {{ request()->routeIs('api.*') ? 'active' : '' }}">
    <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
    </svg>
    <span>API Toegang</span>
</a>
@endhasFeature

{{-- Custom Branding menu item - alleen tonen als feature actief is --}}
@hasFeature('custom_branding')
<a href="{{ route('branding.index') }}" 
   class="sidebar-link {{ request()->routeIs('branding.*') ? 'active' : '' }}">
    <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
    </svg>
    <span>Custom Branding</span>
</a>
@endhasFeature