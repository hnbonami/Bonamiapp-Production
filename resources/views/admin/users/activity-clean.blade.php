@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">📊 Login Activiteit TEST</h1>
            <p class="text-gray-600 mt-2">Monitor gebruikers activiteit en sessies</p>
        </div>
        
        <a href="{{ route('admin.users.index') }}" class="bg-gray-500 text-white px-6 py-3 rounded-lg hover:bg-gray-600 transition-colors font-medium">
            ← Terug naar Gebruikers
        </a>
    </div>

    <!-- Statistics Cards - Kleiner en zachter -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        
        <!-- Vandaag Card -->
        <div class="bg-white rounded-lg shadow-md p-4 hover:shadow-lg transition-shadow border-l-4 border-green-400" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(5, 150, 105, 0.05) 100%);">
            <div class="flex items-center">
                <div class="bg-green-100 p-2 rounded-lg mr-3">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">Vandaag</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['total_logins_today'] ?? '2' }}</p>
                </div>
            </div>
        </div>

        <!-- Deze Week Card -->
        <div class="bg-white rounded-lg shadow-md p-4 hover:shadow-lg transition-shadow border-l-4 border-blue-400" style="background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(29, 78, 216, 0.05) 100%);">
            <div class="flex items-center">
                <div class="bg-blue-100 p-2 rounded-lg mr-3">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">Deze Week</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['total_logins_week'] ?? '15' }}</p>
                </div>
            </div>
        </div>

        <!-- Unieke Vandaag Card -->
        <div class="bg-white rounded-lg shadow-md p-4 hover:shadow-lg transition-shadow border-l-4 border-purple-400" style="background: linear-gradient(135deg, rgba(139, 92, 246, 0.1) 0%, rgba(124, 58, 237, 0.05) 100%);">
            <div class="flex items-center">
                <div class="bg-purple-100 p-2 rounded-lg mr-3">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">Unieke Vandaag</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['unique_users_today'] ?? '1' }}</p>
                </div>
            </div>
        </div>

        <!-- Gem. Sessie Card -->
        <div class="bg-white rounded-lg shadow-md p-4 hover:shadow-lg transition-shadow border-l-4 border-orange-400" style="background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(217, 119, 6, 0.05) 100%);">
            <div class="flex items-center">
                <div class="bg-orange-100 p-2 rounded-lg mr-3">
                    <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">Gem. Sessie</p>
                    <p class="text-2xl font-bold text-gray-800">
                        @if(isset($stats['average_session_time']) && $stats['average_session_time'])
                            {{ gmdate('H:i', $stats['average_session_time']) }}
                        @else
                            1:23
                        @endif
                    </p>
                </div>
            </div>
        </div>

    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">🔍 Filter Opties</h3>
        <form method="GET" class="flex flex-wrap gap-4 items-end">
            <div class="min-w-[200px]">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Zoeken</label>
                <input type="text" name="search" id="search" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                       placeholder="Zoek op naam, email..." value="{{ request('search') }}">
            </div>
            <div class="min-w-[200px]">
                <label for="user_id" class="block text-sm font-medium text-gray-700 mb-2">Gebruiker</label>
                <select name="user_id" id="user_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Alle gebruikers</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }} ({{ $user->email }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-[150px]">
                <label for="date_from" class="block text-sm font-medium text-gray-700 mb-2">Van Datum</label>
                <input type="date" name="date_from" id="date_from" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" value="{{ request('date_from') }}">
            </div>
            <div class="min-w-[150px]">
                <label for="date_to" class="block text-sm font-medium text-gray-700 mb-2">Tot Datum</label>
                <input type="date" name="date_to" id="date_to" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" value="{{ request('date_to') }}">
            </div>
            <div class="flex gap-3">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors font-medium inline-flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Filteren
                </button>
                <a href="{{ route('admin.users.activity') }}" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400 transition-colors font-medium inline-flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Login Activities Table - Netter en compacter -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-medium text-gray-900 flex items-center">
                📋 Login Activiteiten 
                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    {{ $loginActivities->total() }}
                </span>
            </h3>
        </div>
        
        @if($loginActivities->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gebruiker</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Login Tijd</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Logout Tijd</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sessie Duur</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP Adres</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($loginActivities as $activity)
                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if($activity->user)
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-8 w-8">
                                                <div class="h-8 w-8 rounded-full bg-gradient-to-r from-blue-400 to-purple-500 flex items-center justify-center">
                                                    <span class="text-xs font-medium text-white">
                                                        {{ strtoupper(substr($activity->user->name, 0, 1)) }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm font-medium text-gray-900">{{ $activity->user->name }}</div>
                                                <div class="text-xs text-gray-500">{{ $activity->user->email }}</div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-red-600 text-sm flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                            </svg>
                                            Gebruiker niet gevonden
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="flex items-center text-sm text-gray-900">
                                        <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                        </svg>
                                        {{ $activity->logged_in_at->format('d/m/Y H:i') }}
                                    </div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if($activity->logged_out_at)
                                        <div class="flex items-center text-sm text-gray-900">
                                            <svg class="w-4 h-4 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013 3v1"/>
                                            </svg>
                                            {{ $activity->logged_out_at->format('d/m/Y H:i') }}
                                        </div>
                                    @else
                                        <span class="text-sm text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if($activity->logged_out_at)
                                        @php
                                            $duration = $activity->logged_out_at->diffInMinutes($activity->logged_in_at);
                                            $hours = floor($duration / 60);
                                            $minutes = $duration % 60;
                                        @endphp
                                        <span class="text-sm text-gray-600">
                                            @if($hours > 0)
                                                {{ $hours }}u {{ $minutes }}m
                                            @else
                                                {{ $minutes }}m
                                            @endif
                                        </span>
                                    @else
                                        <span class="text-sm text-blue-600 font-medium">Actief</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-mono bg-gray-100 text-gray-800">
                                        {{ $activity->ip_address }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if($activity->logged_out_at)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            <div class="w-1.5 h-1.5 bg-gray-400 rounded-full mr-1.5"></div>
                                            Uitgelogd
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <div class="w-1.5 h-1.5 bg-green-400 rounded-full mr-1.5 animate-pulse"></div>
                                            Actief
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                {{ $loginActivities->appends(request()->query())->links() }}
            </div>
        @else
            <div class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Geen login activiteiten</h3>
                <p class="mt-1 text-sm text-gray-500">Er zijn geen login activiteiten voor de geselecteerde criteria.</p>
            </div>
        @endif
    </div>
</div>
@endsection