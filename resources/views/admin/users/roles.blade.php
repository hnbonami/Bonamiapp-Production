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
    <div class="grid grid-cols-3 md:grid-cols-5 gap-3 mb-8">
        <div class="bg-white rounded-xl shadow-lg p-3">
            <div class="flex flex-col items-center text-center">
                <div class="bg-blue-100 p-2 rounded-lg mb-2">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                    </svg>
                </div>
                <p class="text-xs font-medium text-gray-500 uppercase mb-1">Totaal</p>
                <p class="text-2xl font-bold text-gray-900">{{ $roleStats['total'] ?? 0 }}</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-3">
            <div class="flex flex-col items-center text-center">
                <div class="bg-red-100 p-2 rounded-lg mb-2">
                    <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                    </svg>
                </div>
                <p class="text-xs font-medium text-gray-500 uppercase mb-1">Superadmin</p>
                <p class="text-2xl font-bold text-gray-900">{{ $roleStats['superadmin'] ?? 0 }}</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-3">
            <div class="flex flex-col items-center text-center">
                <div class="bg-purple-100 p-2 rounded-lg mb-2">
                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <p class="text-xs font-medium text-gray-500 uppercase mb-1">Admins</p>
                <p class="text-2xl font-bold text-gray-900">{{ $roleStats['admin'] ?? 0 }}</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-3">
            <div class="flex flex-col items-center text-center">
                <div class="bg-orange-100 p-2 rounded-lg mb-2">
                    <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m8 0V6a2 2 0 012 2v6a2 2 0 01-2 2H8a2 2 0 01-2-2V8a2 2 0 012-2V6"/>
                    </svg>
                </div>
                <p class="text-xs font-medium text-gray-500 uppercase mb-1">Medewerkers</p>
                <p class="text-2xl font-bold text-gray-900">{{ $roleStats['medewerker'] ?? 0 }}</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-3">
            <div class="flex flex-col items-center text-center">
                <div class="bg-cyan-100 p-2 rounded-lg mb-2">
                    <svg class="w-4 h-4 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <p class="text-xs font-medium text-gray-500 uppercase mb-1">Klanten</p>
                <p class="text-2xl font-bold text-gray-900">{{ $roleStats['klant'] ?? 0 }}</p>
            </div>
        </div>
    </div>

    <!-- Roles Overview -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Beschikbare Rollen</h2>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rol</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Beschrijving</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rechten</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aantal Gebruikers</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($roles as $role)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                    {{ $role['color'] === 'purple' ? 'bg-purple-100 text-purple-800' : '' }}
                                    {{ $role['color'] === 'orange' ? 'bg-orange-100 text-orange-800' : '' }}
                                    {{ $role['color'] === 'cyan' ? 'bg-cyan-100 text-cyan-800' : '' }}">
                                    {{ $role['name'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">{{ $role['description'] }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-500">{{ $role['permissions'] }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-semibold text-gray-900">{{ $roleStats[$role['key']] ?? 0 }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection