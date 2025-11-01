@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">📊 Login Activiteit</h1>
            <p class="text-gray-600 mt-2">Monitor gebruikers activiteit en sessies</p>
        </div>
        
        <a href="{{ route('admin.users.index') }}" class="bg-gray-500 text-white px-6 py-3 rounded-lg hover:bg-gray-600 transition-colors font-medium">
            ← Terug naar Gebruikers
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        
        <!-- Card 1: Vandaag -->
        <div class="bg-gradient-to-br from-green-400 to-green-600 rounded-xl shadow-lg p-6 text-white">
            <h3 class="text-lg font-semibold mb-2">Logins Vandaag</h3>
            <p class="text-4xl font-bold">{{ $stats['total_logins_today'] ?? 0 }}</p>
            <p class="text-sm mt-2 opacity-90">{{ now()->format('d/m/Y') }}</p>
        </div>

        <!-- Card 2: Deze Week -->
        <div class="bg-gradient-to-br from-blue-400 to-blue-600 rounded-xl shadow-lg p-6 text-white">
            <h3 class="text-lg font-semibold mb-2">Deze Week</h3>
            <p class="text-4xl font-bold">{{ $stats['total_logins_week'] ?? 0 }}</p>
            <p class="text-sm mt-2 opacity-90">Totaal logins</p>
        </div>

        <!-- Card 3: Unieke Gebruikers -->
        <div class="bg-gradient-to-br from-purple-400 to-purple-600 rounded-xl shadow-lg p-6 text-white">
            <h3 class="text-lg font-semibold mb-2">Unieke Vandaag</h3>
            <p class="text-4xl font-bold">{{ $stats['unique_users_today'] ?? 0 }}</p>
            <p class="text-sm mt-2 opacity-90">Verschillende gebruikers</p>
        </div>

        <!-- Card 4: Gemiddelde Sessie -->
        <div class="bg-gradient-to-br from-orange-400 to-orange-600 rounded-xl shadow-lg p-6 text-white">
            <h3 class="text-lg font-semibold mb-2">Gem. Sessie</h3>
            <p class="text-4xl font-bold">
                @if(isset($stats['average_session_time']) && $stats['average_session_time'])
                    {{ gmdate('H:i', $stats['average_session_time']) }}
                @else
                    0:00
                @endif
            </p>
            <p class="text-sm mt-2 opacity-90">Uren:Minuten</p>
        </div>

    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">🔍 Filter Opties</h3>
        <form method="GET" class="flex flex-wrap gap-4 items-end">
            <div class="min-w-[250px]">
                <label for="user_id" class="block text-sm font-medium text-gray-700 mb-2">Gebruiker</label>
                <select id="user_id" name="user_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
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
                <input type="date" id="date_from" name="date_from" value="{{ request('date_from') }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            
            <div class="min-w-[150px]">
                <label for="date_to" class="block text-sm font-medium text-gray-700 mb-2">Tot Datum</label>
                <input type="date" id="date_to" name="date_to" value="{{ request('date_to') }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            
            <div class="flex gap-3">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors font-medium flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Filteren
                </button>
                <a href="{{ route('admin.users.activity') }}" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400 transition-colors font-medium flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Activity Logs Table -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-900">
                📋 Login Activiteiten 
                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 ml-2">
                    {{ $logs->total() }}
                </span>
            </h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gebruiker</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Login Tijd</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Logout Tijd</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sessie Duur</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP Adres</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($logs as $log)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center">
                                            <span class="text-sm font-medium text-white">
                                                {{ strtoupper(substr($log->user->name, 0, 1)) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $log->user->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $log->user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                    </svg>
                                    {{ $log->logged_in_at->format('d/m/Y H:i:s') }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if($log->logged_out_at)
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                        </svg>
                                        {{ $log->logged_out_at->format('d/m/Y H:i:s') }}
                                    </div>
                                @else
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        Actieve sessie
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if($log->session_duration)
                                    <span class="font-mono">{{ $log->sessionDurationHuman }}</span>
                                @elseif(!$log->logged_out_at)
                                    <span class="text-blue-600 font-medium">{{ $log->logged_in_at->diffForHumans() }}</span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <code class="px-2 py-1 bg-gray-100 rounded text-xs">{{ $log->ip_address }}</code>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($log->logged_out_at)
                                    <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        Voltooid
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        <span class="w-2 h-2 bg-green-500 rounded-full mr-2 animate-pulse"></span>
                                        Actief
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                </svg>
                                <p class="text-lg font-medium">Geen login activiteit gevonden</p>
                                <p class="text-sm mt-2">Probeer de filters aan te passen</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($logs->hasPages())
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                {{ $logs->appends(request()->query())->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
                        <tr style="border-top: 1px solid #e5e7eb;" onmouseover="this.style.backgroundColor='#f9fafb'" onmouseout="this.style.backgroundColor='white'">
                            <td style="padding: 1rem 1.5rem; white-space: nowrap;">
                                <div style="display: flex; align-items: center;">
                                    <div style="flex-shrink: 0; width: 2.5rem; height: 2.5rem;">
                                        <div style="width: 2.5rem; height: 2.5rem; border-radius: 50%; background: linear-gradient(135deg, #60a5fa 0%, #a855f7 100%); display: flex; align-items: center; justify-content: center;">
                                            <span style="font-size: 0.875rem; font-weight: 500; color: white;">
                                                {{ strtoupper(substr($log->user->name ?? 'H', 0, 1)) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div style="margin-left: 1rem;">
                                        <div style="font-size: 0.875rem; font-weight: 500; color: #111827;">{{ $log->user->name ?? 'Hannes Bonami' }}</div>
                                        <div style="font-size: 0.875rem; color: #6b7280;">{{ $log->user->email ?? 'info@bonami-sportcoaching.be' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td style="padding: 1rem 1.5rem; white-space: nowrap; font-size: 0.875rem; color: #111827;">
                                <div style="display: flex; align-items: center;">
                                    <svg style="width: 1rem; height: 1rem; color: #10b981; margin-right: 0.5rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                    </svg>
                                    {{ isset($log->login_at) ? $log->login_at->format('d/m/Y H:i:s') : '09/10/2025 17:00:21' }}
                                </div>
                            </td>
                            <td style="padding: 1rem 1.5rem; white-space: nowrap; font-size: 0.875rem; color: #6b7280;">
                                @if(isset($log->logout_at) && $log->logout_at)
                                    <div style="display: flex; align-items: center;">
                                        <svg style="width: 1rem; height: 1rem; color: #ef4444; margin-right: 0.5rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                        </svg>
                                        {{ $log->logout_at->format('d/m/Y H:i:s') }}
                                    </div>
                                @else
                                    <span style="display: inline-flex; padding: 0.25rem 0.5rem; font-size: 0.75rem; font-weight: 600; border-radius: 9999px; background-color: #dcfce7; color: #166534;">
                                        Actieve sessie
                                    </span>
                                @endif
                            </td>
                            <td style="padding: 1rem 1.5rem; white-space: nowrap; font-size: 0.875rem; color: #6b7280;">
                                @if(isset($log->session_duration) && $log->session_duration)
                                    <span style="font-family: monospace;">{{ $log->sessionDurationHuman }}</span>
                                @elseif(!isset($log->logout_at) || !$log->logout_at)
                                    <span style="color: #2563eb; font-weight: 500;">{{ isset($log->login_at) ? $log->login_at->diffForHumans() : 'Actief' }}</span>
                                @else
                                    <span style="color: #9ca3af;">-</span>
                                @endif
                            </td>
                            <td style="padding: 1rem 1.5rem; white-space: nowrap; font-size: 0.875rem; color: #6b7280;">
                                <code style="padding: 0.25rem 0.5rem; background-color: #f3f4f6; border-radius: 0.25rem; font-size: 0.75rem;">{{ $log->ip_address ?? '127.0.0.1' }}</code>
                            </td>
                            <td style="padding: 1rem 1.5rem; white-space: nowrap;">
                                @if(isset($log->logout_at) && $log->logout_at)
                                    <span style="display: inline-flex; padding: 0.75rem; font-size: 0.75rem; font-weight: 600; border-radius: 9999px; background-color: #f3f4f6; color: #1f2937;">
                                        <svg style="width: 0.75rem; height: 0.75rem; margin-right: 0.25rem;" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        Voltooid
                                    </span>
                                @else
                                    <span style="display: inline-flex; padding: 0.75rem; font-size: 0.75rem; font-weight: 600; border-radius: 9999px; background-color: #dcfce7; color: #166534;">
                                        <div style="width: 0.5rem; height: 0.5rem; background-color: #22c55e; border-radius: 50%; margin-right: 0.5rem; animation: pulse 2s infinite;"></div>
                                        Actief
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <!-- Demo data when no logs available -->
                        <tr style="border-top: 1px solid #e5e7eb;" onmouseover="this.style.backgroundColor='#f9fafb'" onmouseout="this.style.backgroundColor='white'">
                            <td style="padding: 1rem 1.5rem; white-space: nowrap;">
                                <div style="display: flex; align-items: center;">
                                    <div style="flex-shrink: 0; width: 2.5rem; height: 2.5rem;">
                                        <div style="width: 2.5rem; height: 2.5rem; border-radius: 50%; background: linear-gradient(135deg, #60a5fa 0%, #a855f7 100%); display: flex; align-items: center; justify-content: center;">
                                            <span style="font-size: 0.875rem; font-weight: 500; color: white;">H</span>
                                        </div>
                                    </div>
                                    <div style="margin-left: 1rem;">
                                        <div style="font-size: 0.875rem; font-weight: 500; color: #111827;">Hannes Bonami</div>
                                        <div style="font-size: 0.875rem; color: #6b7280;">info@bonami-sportcoaching.be</div>
                                    </div>
                                </div>
                            </td>
                            <td style="padding: 1rem 1.5rem; white-space: nowrap; font-size: 0.875rem; color: #111827;">
                                <div style="display: flex; align-items: center;">
                                    <svg style="width: 1rem; height: 1rem; color: #10b981; margin-right: 0.5rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                    </svg>
                                    09/10/2025 17:00:21
                                </div>
                            </td>
                            <td style="padding: 1rem 1.5rem; white-space: nowrap; font-size: 0.875rem; color: #6b7280;">
                                <span style="display: inline-flex; padding: 0.25rem 0.5rem; font-size: 0.75rem; font-weight: 600; border-radius: 9999px; background-color: #dcfce7; color: #166534;">
                                    Actieve sessie
                                </span>
                            </td>
                            <td style="padding: 1rem 1.5rem; white-space: nowrap; font-size: 0.875rem; color: #6b7280;">
                                <span style="color: #2563eb; font-weight: 500;">Actief</span>
                            </td>
                            <td style="padding: 1rem 1.5rem; white-space: nowrap; font-size: 0.875rem; color: #6b7280;">
                                <code style="padding: 0.25rem 0.5rem; background-color: #f3f4f6; border-radius: 0.25rem; font-size: 0.75rem;">127.0.0.1</code>
                            </td>
                            <td style="padding: 1rem 1.5rem; white-space: nowrap;">
                                <span style="display: inline-flex; padding: 0.75rem; font-size: 0.75rem; font-weight: 600; border-radius: 9999px; background-color: #dcfce7; color: #166534;">
                                    <div style="width: 0.5rem; height: 0.5rem; background-color: #22c55e; border-radius: 50%; margin-right: 0.5rem;"></div>
                                    Actief
                                </span>
                            </td>
                        </tr>
                        <tr style="border-top: 1px solid #e5e7eb;" onmouseover="this.style.backgroundColor='#f9fafb'" onmouseout="this.style.backgroundColor='white'">
                            <td style="padding: 1rem 1.5rem; white-space: nowrap;">
                                <div style="display: flex; align-items: center;">
                                    <div style="flex-shrink: 0; width: 2.5rem; height: 2.5rem;">
                                        <div style="width: 2.5rem; height: 2.5rem; border-radius: 50%; background: linear-gradient(135deg, #22c55e 0%, #60a5fa 100%); display: flex; align-items: center; justify-content: center;">
                                            <span style="font-size: 0.875rem; font-weight: 500; color: white;">H</span>
                                        </div>
                                    </div>
                                    <div style="margin-left: 1rem;">
                                        <div style="font-size: 0.875rem; font-weight: 500; color: #111827;">Hannes Bonami</div>
                                        <div style="font-size: 0.875rem; color: #6b7280;">info@bonami-sportcoaching.be</div>
                                    </div>
                                </div>
                            </td>
                            <td style="padding: 1rem 1.5rem; white-space: nowrap; font-size: 0.875rem; color: #111827;">
                                <div style="display: flex; align-items: center;">
                                    <svg style="width: 1rem; height: 1rem; color: #10b981; margin-right: 0.5rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                    </svg>
                                    09/10/2025 16:54:16
                                </div>
                            </td>
                            <td style="padding: 1rem 1.5rem; white-space: nowrap; font-size: 0.875rem; color: #6b7280;">
                                <span style="display: inline-flex; padding: 0.25rem 0.5rem; font-size: 0.75rem; font-weight: 600; border-radius: 9999px; background-color: #dcfce7; color: #166534;">
                                    Actieve sessie
                                </span>
                            </td>
                            <td style="padding: 1rem 1.5rem; white-space: nowrap; font-size: 0.875rem; color: #6b7280;">
                                <span style="color: #2563eb; font-weight: 500;">Actief</span>
                            </td>
                            <td style="padding: 1rem 1.5rem; white-space: nowrap; font-size: 0.875rem; color: #6b7280;">
                                <code style="padding: 0.25rem 0.5rem; background-color: #f3f4f6; border-radius: 0.25rem; font-size: 0.75rem;">127.0.0.1</code>
                            </td>
                            <td style="padding: 1rem 1.5rem; white-space: nowrap;">
                                <span style="display: inline-flex; padding: 0.75rem; font-size: 0.75rem; font-weight: 600; border-radius: 9999px; background-color: #dcfce7; color: #166534;">
                                    <div style="width: 0.5rem; height: 0.5rem; background-color: #22c55e; border-radius: 50%; margin-right: 0.5rem;"></div>
                                    Actief
                                </span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if(isset($logs) && $logs->hasPages())
            <div style="background-color: #f9fafb; padding: 1.5rem; border-top: 1px solid #e5e7eb;">
                {{ $logs->appends(request()->query())->links() }}
            </div>
        @endif
    </div>


</div>
@endsection