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
                <div class="bg-blue-100 p-3 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 1.79 4 4 4h8c0-2.21-1.79-4-4-4H4V7z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 3v18l-8-6V9l8-6z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 ml-3">Database Tools</h3>
            </div>
            <p class="text-gray-600 mb-4">Beheer database & notities. Bekijk en beheer database tabellen, staff notities en systeemdata.</p>
            <a href="{{ route('admin.database.tools') }}" class="inline-flex items-center text-blue-600 hover:text-blue-800 font-medium">
                Database Beheer Openen
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
        @endhasFeature

        {{-- Email Beheer - alleen tonen als feature actief is --}}
        @hasFeature('sjablonen')
        <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow">
            <div class="flex items-center mb-4">
                <div class="bg-green-100 p-3 rounded-lg">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 ml-3">Email Beheer</h3>
            </div>
            <p class="text-gray-600 mb-4">Beheer email templates, instellingen en logs. Inclusief verjaardagen en automatische herinneringen.</p>
            <a href="/admin/email" class="inline-flex items-center text-green-600 hover:text-green-800 font-medium">
                Email Beheer Openen
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
        @endhasFeature

        {{-- Bikefit Uitleenbeheer - alleen tonen als feature actief is --}}
        @hasFeature('testzadels')
        <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow">
            <div class="flex items-center mb-4">
                <div class="bg-purple-100 p-3 rounded-lg">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 ml-3">Bikefit Uitleenbeheer</h3>
            </div>
            <p class="text-gray-600 mb-4">Uitlening & retour beheer. Beheer testzadel uitleningen, herinneringen en retour administratie.</p>
            <a href="/testzadels" class="inline-flex items-center text-purple-600 hover:text-purple-800 font-medium">
                Uitleenbeheer Openen
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
        @endhasFeature

        {{-- Rechten & Rollen Beheer - altijd tonen voor admins --}}
        <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow">
            <div class="flex items-center mb-4">
                <div class="p-3 rounded-lg" style="background-color: #c8e1eb;">
                    <svg class="w-6 h-6" style="color: #2563eb;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 ml-3">Rechten & Rollen Beheer</h3>
            </div>
            <p class="text-gray-600 mb-4">Gebruikersbeheer & toegang. Beheer gebruikersrollen, tabblad toegang, test permissions en login activiteit.</p>
            <a href="/admin/users" class="inline-flex items-center font-medium" style="color: #2563eb; hover:color: #1d4ed8;">
                Gebruikersbeheer Openen
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>

    </div>
</div>
@endsection