@extends('layouts.app')

@section('title', 'Rollen Beheer - Bonami Sportcoaching')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">🔐 Rollen Beheer</h1>
            <p class="text-gray-600 mt-2">Overzicht van gebruikersrollen en rechten</p>
        </div>
        
        <div class="flex gap-4">
            <a href="{{ route('admin.users.index') }}" class="text-white px-4 py-2 rounded-lg transition-colors" style="background-color: #c8e1eb; color: #1f2937;" onmouseover="this.style.backgroundColor='#b0d4e0'" onmouseout="this.style.backgroundColor='#c8e1eb'">
                ← Terug naar Gebruikers
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-xl shadow-lg p-4">
            <div class="flex items-center">
                <div class="bg-blue-100 p-2 rounded-lg mr-3">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase">Totaal</p>
                    <p class="text-xl font-bold text-gray-900">{{ $roleStats['total'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-4">
            <div class="flex items-center">
                <div class="bg-purple-100 p-2 rounded-lg mr-3">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase">Admins</p>
                    <p class="text-xl font-bold text-gray-900">{{ $roleStats['admin'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-4">
            <div class="flex items-center">
                <div class="bg-orange-100 p-2 rounded-lg mr-3">
                    <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m8 0V6a2 2 0 012 2v6a2 2 0 01-2 2H8a2 2 0 01-2-2V8a2 2 0 012-2V6"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase">Medewerkers</p>
                    <p class="text-xl font-bold text-gray-900">{{ $roleStats['medewerker'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-4">
            <div class="flex items-center">
                <div class="bg-cyan-100 p-2 rounded-lg mr-3">
                    <svg class="w-5 h-5 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase">Klanten</p>
                    <p class="text-xl font-bold text-gray-900">{{ $roleStats['klant'] ?? 0 }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Roles Overview -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">Beschikbare Rollen</h2>
        </div>
        
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($roles as $role)
                    <div class="border rounded-lg p-4 hover:shadow-md transition-shadow">
                        <div class="flex items-center mb-3">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                {{ $role['color'] === 'purple' ? 'bg-purple-100 text-purple-800' : '' }}
                                {{ $role['color'] === 'orange' ? 'bg-orange-100 text-orange-800' : '' }}
                                {{ $role['color'] === 'cyan' ? 'bg-cyan-100 text-cyan-800' : '' }}">
                                {{ $role['name'] }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-600 mb-3">{{ $role['description'] }}</p>
                        <div class="text-xs text-gray-500">
                            <strong>Rechten:</strong> {{ $role['permissions'] }}
                        </div>
                        <div class="mt-3 text-sm font-medium text-gray-700">
                            Gebruikers: {{ $roleStats[$role['key']] ?? 0 }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection