@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-8">🔧 Admin Dashboard</h1>
    <p class="text-gray-600 mb-8">Beheer van alle admin functies en beschikbare tools</p>

    <!-- Admin Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        
        {{-- Database Tools - alleen tonen als feature actief is --}}
        @hasFeature('database_tools')
        <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow">
            <div class="flex items-center mb-4">
                <div class="p-3 rounded-lg" style="background-color: #c8e1eb;">
                    <svg class="w-6 h-6" style="color: #111;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 1.79 4 4 4h8c0-2.21-1.79-4-4-4H4V7z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 3v18l-8-6V9l8-6z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 ml-3">Database Tools</h3>
            </div>
            <p class="text-gray-600 mb-4">Beheer database & notities. Bekijk en beheer database tabellen, staff notities en systeemdata.</p>
            <a href="{{ route('admin.database.tools') }}" class="inline-flex items-center font-medium" style="color: #111;">
                Database Beheer Openen
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
        @endhasFeature

        {{-- Email Beheer - alleen tonen als feature actief is --}}
        @hasFeature('email_beheer')
        <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow">
            <div class="flex items-center mb-4">
                <div class="p-3 rounded-lg" style="background-color: #c8e1eb;">
                    <svg class="w-6 h-6" style="color: #111;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 ml-3">Email Beheer</h3>
            </div>
            <p class="text-gray-600 mb-4">Beheer email templates, instellingen en logs. Inclusief verjaardagen en automatische herinneringen.</p>
            <div class="flex flex-wrap gap-4">
                <a href="/admin/email/templates" class="inline-flex items-center font-medium" style="color: #111;">
                    Templates
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
                <a href="/admin/email/triggers" class="inline-flex items-center font-medium" style="color: #111;">
                    Triggers
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
                <a href="/admin/email/logs" class="inline-flex items-center font-medium" style="color: #111;">
                    Logs
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
                <a href="/admin/email/settings" class="inline-flex items-center font-medium" style="color: #111;">
                    Email Huisstijl
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        </div>
        @endhasFeature

        {{-- Bikefit Uitleenbeheer - alleen tonen als feature actief is --}}
        @hasFeature('testzadels')
        <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow">
            <div class="flex items-center mb-4">
                <div class="p-3 rounded-lg" style="background-color: #c8e1eb;">
                    <svg class="w-6 h-6" style="color: #111;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 ml-3">Bikefit Uitleenbeheer</h3>
            </div>
            <p class="text-gray-600 mb-4">Uitlening & retour beheer. Beheer testzadel uitleningen, herinneringen en retour administratie.</p>
            <a href="/testzadels" class="inline-flex items-center font-medium" style="color: #111;">
                Uitleenbeheer Openen
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
        @endhasFeature

        {{-- Rechten & Rollen Beheer - alleen tonen als feature actief is --}}
        @hasFeature('gebruikersbeheer')
        <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow">
            <div class="flex items-center mb-4">
                <div class="p-3 rounded-lg" style="background-color: #c8e1eb;">
                    <svg class="w-6 h-6" style="color: #111;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 ml-3">Rechten & Rollen Beheer</h3>
            </div>
            <p class="text-gray-600 mb-4">Gebruikersbeheer & toegang. Beheer gebruikersrollen, tabblad toegang, test permissions en login activiteit.</p>
            <a href="/admin/users" class="inline-flex items-center font-medium" style="color: #111;">
                Gebruikersbeheer Openen
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
        @endhasFeature

        {{-- Prestaties & Commissies - alleen tonen als feature actief is --}}
        @hasFeature('prestaties')
        <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow">
            <div class="flex items-center mb-4">
                <div class="p-3 rounded-lg" style="background-color: #c8e1eb;">
                    <svg class="w-6 h-6" style="color: #111;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 ml-3">Prestaties & Commissies</h3>
            </div>
            <p class="text-gray-600 mb-4">Coach prestaties beheren. Diensten configureren, commissies instellen en kwartaaloverzichten bekijken.</p>
            <div class="flex gap-4">
                <a href="{{ route('admin.prestaties.diensten.index') }}" class="inline-flex items-center font-medium" style="color: #111;">
                    Diensten
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
                <a href="{{ route('admin.medewerkers.commissies.index') }}" class="inline-flex items-center font-medium" style="color: #111;">
                    Commissies
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
                <a href="{{ route('admin.prestaties.overzicht') }}" class="inline-flex items-center font-medium" style="color: #111;">
                    Overzicht
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        </div>
        @endhasFeature

        {{-- Analytics Dashboard - alleen tonen als feature actief is --}}
        @hasFeature('analytics')
        <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow">
            <div class="flex items-center mb-4">
                <div class="p-3 rounded-lg" style="background-color: #c8e1eb;">
                    <svg class="w-6 h-6" style="color: #111;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 ml-3">Analytics Dashboard</h3>
            </div>
            <p class="text-gray-600 mb-4">Uitgebreide statistieken & grafieken. KPI's, omzet trends, diensten verdeling en medewerker prestaties inzichtelijk maken.</p>
            <a href="{{ route('admin.analytics.index') }}" class="inline-flex items-center font-medium" style="color: #111;">
                Analytics Dashboard Openen
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
        @endhasFeature

        {{-- Rapporten Opmaken - alleen tonen als feature actief is --}}
        @hasFeature('rapporten_opmaken')
        <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow">
            <div class="flex items-center mb-4">
                <div class="p-3 rounded-lg" style="background-color: #c8e1eb;">
                    <svg class="w-6 h-6" style="color: #111;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 ml-3">Rapporten Opmaken</h3>
            </div>
            <p class="text-gray-600 mb-4">Personaliseer rapporten met eigen header, footer, logo, kleuren en contactgegevens. Maak professionele rapporten in jouw huisstijl!</p>
            <a href="{{ route('admin.rapporten.instellingen') }}" class="inline-flex items-center font-medium" style="color: #111;">
                Rapporten Configureren
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
        @endhasFeature

        {{-- Custom Branding - alleen tonen als feature ACTIEF is voor deze organisatie EN user is admin --}}
        @php
            $user = auth()->user();
            $showCustomBranding = false;
            
            if ($user && $user->organisatie_id && $user->organisatie) {
                // Check of feature actief is via pivot tabel
                $featurePivot = $user->organisatie->features()
                    ->where('key', 'custom_branding')
                    ->wherePivot('is_actief', true)
                    ->first();
                
                // Toon alleen als feature actief is EN user is admin
                $showCustomBranding = $featurePivot && $user->isAdminOfOrganisatie($user->organisatie_id);
            }
        @endphp
        
        @if($showCustomBranding)
        <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow">
            <div class="flex items-center mb-4">
                <div class="p-3 rounded-lg" style="background-color: #c8e1eb;">
                    <svg class="w-6 h-6" style="color: #111;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 ml-3">Custom Branding</h3>
            </div>
            <p class="text-gray-600 mb-4">Personaliseer logo's, kleuren en huisstijl voor rapporten en emails. Maak de app helemaal van jou!</p>
            <a href="{{ route('branding.index') }}" class="inline-flex items-center font-medium" style="color: #111;">
                Branding Beheren
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
        @endif

        {{-- Branding & Layout - alleen voor admins als feature actief is --}}
        @hasFeature('branding_layout')
        @if(auth()->user()->isBeheerder())
        <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow">
            <div class="flex items-center mb-4">
                <div class="p-3 rounded-lg" style="background-color: #c8e1eb;">
                    <svg class="w-6 h-6" style="color: #111;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 ml-3">Applicatie branding & Layout</h3>
            </div>
            <p class="text-gray-600 mb-4">Personaliseer de app voor je organisatie. Upload logo, pas themakleuren aan en maak de app helemaal van jou!</p>
            <a href="{{ route('branding.index') }}" class="inline-flex items-center font-medium" style="color: #111;">
                Branding Configureren
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
        @endif
        @endhasFeature

        {{-- Inspanningstesten Instellingen - alleen tonen als feature actief is --}}
        @hasFeature('inspanningstesten')
        <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow">
            <div class="flex items-center mb-4">
                <div class="p-3 rounded-lg" style="background-color: #c8e1eb;">
                    <svg class="w-6 h-6" style="color: #111;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 ml-3">Inspanningstesten Instellingen</h3>
            </div>
            <p class="text-gray-600 mb-4">Configureer inspanningstesten templates, zones en protocollen. Stel standaardwaarden en rapporten in voor inspanningstesten.</p>
            <a href="{{ route('admin.inspanningstesten.instellingen') }}" class="inline-flex items-center font-medium" style="color: #111;">
                Instellingen Openen
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
        @endhasFeature

    </div>
</div>
@endsection