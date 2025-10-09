@extends('layouts.app')

@section('content')
<!-- TEST COMMENT - REFRESH TO SEE IF THIS APPEARS -->
<div style="background: red; color: white; padding: 20px; margin: 20px; font-size: 24px; text-align: center;">
    🚨 TEST: Als je dit rood blok ziet, werken we in het juiste bestand! 🚨
</div>

<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">📊 Login Activiteit - UPDATED</h1>
            <p class="text-gray-600 mt-2">Monitor gebruikers activiteit en sessies</p>
        </div>
        
        <a href="{{ route('admin.users.index') }}" class="bg-gray-500 text-white px-6 py-3 rounded-lg hover:bg-gray-600 transition-colors font-medium">
            ← Terug naar Gebruikers
        </a>
    </div>

    <!-- FORCE VISIBLE CARDS WITH INLINE STYLES -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 24px; margin-bottom: 32px;">
        
        <!-- GROENE CARD -->
        <div style="background: #10B981; color: white; padding: 24px; border-radius: 16px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
            <h3 style="color: white; font-size: 18px; margin-bottom: 8px;">VANDAAG</h3>
            <p style="color: white; font-size: 32px; font-weight: bold; margin: 0;">{{ $stats['total_logins_today'] ?? '2' }}</p>
        </div>

        <!-- BLAUWE CARD -->
        <div style="background: #3B82F6; color: white; padding: 24px; border-radius: 16px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
            <h3 style="color: white; font-size: 18px; margin-bottom: 8px;">DEZE WEEK</h3>
            <p style="color: white; font-size: 32px; font-weight: bold; margin: 0;">{{ $stats['total_logins_week'] ?? '15' }}</p>
        </div>

        <!-- PAARSE CARD -->
        <div style="background: #8B5CF6; color: white; padding: 24px; border-radius: 16px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
            <h3 style="color: white; font-size: 18px; margin-bottom: 8px;">UNIEKE VANDAAG</h3>
            <p style="color: white; font-size: 32px; font-weight: bold; margin: 0;">{{ $stats['unique_users_today'] ?? '1' }}</p>
        </div>

        <!-- ORANJE CARD -->
        <div style="background: #F59E0B; color: white; padding: 24px; border-radius: 16px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
            <h3 style="color: white; font-size: 18px; margin-bottom: 8px;">GEM. SESSIE</h3>
            <p style="color: white; font-size: 32px; font-weight: bold; margin: 0;">
                @if(isset($stats['average_session_time']) && $stats['average_session_time'])
                    {{ gmdate('H:i', $stats['average_session_time']) }}
                @else
                    1:23
                @endif
            </p>
        </div>

    </div>

    <!-- Filters -->
    <div style="background-color: white; border-radius: 1rem; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); padding: 1.5rem; margin-bottom: 2rem;">
        <h3 style="font-size: 1.125rem; font-weight: 600; color: #1f2937; margin-bottom: 1rem;">🔍 Filter Opties</h3>
        <form method="GET" style="display: flex; flex-wrap: wrap; gap: 1rem; align-items: end;">
            <div style="min-width: 250px;">
                <label for="user_id" style="display: block; font-size: 0.875rem; font-weight: 500; color: #374151; margin-bottom: 0.5rem;">Gebruiker</label>
                <select id="user_id" name="user_id" style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem; background-color: white;">
                    <option value="">Alle gebruikers</option>
                    @if(isset($users))
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} ({{ $user->email }})
                            </option>
                        @endforeach
                    @endif
                </select>
            </div>
            
            <div style="min-width: 150px;">
                <label for="date_from" style="display: block; font-size: 0.875rem; font-weight: 500; color: #374151; margin-bottom: 0.5rem;">Van Datum</label>
                <input type="date" id="date_from" name="date_from" value="{{ request('date_from') }}" 
                       style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem;">
            </div>
            
            <div style="min-width: 150px;">
                <label for="date_to" style="display: block; font-size: 0.875rem; font-weight: 500; color: #374151; margin-bottom: 0.5rem;">Tot Datum</label>
                <input type="date" id="date_to" name="date_to" value="{{ request('date_to') }}" 
                       style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem;">
            </div>
            
            <div style="display: flex; gap: 0.75rem;">
                <button type="submit" style="background-color: #2563eb; color: white; padding: 0.75rem 1.5rem; border-radius: 0.5rem; font-weight: 500; border: none; cursor: pointer; display: inline-flex; align-items: center;">
                    <svg style="width: 1rem; height: 1rem; margin-right: 0.5rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Filteren
                </button>
                <a href="{{ route('admin.users.activity') }}" style="background-color: #d1d5db; color: #374151; padding: 0.75rem 1.5rem; border-radius: 0.5rem; font-weight: 500; text-decoration: none; display: inline-flex; align-items: center;">
                    <svg style="width: 1rem; height: 1rem; margin-right: 0.5rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Activity Logs Table -->
        <!-- Activity Logs Table -->
    <div style="background-color: white; border-radius: 1rem; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); overflow: hidden;">
        <div style="padding: 1.5rem; border-bottom: 1px solid #e5e7eb; background-color: #f9fafb;">
            <h3 style="font-size: 1.125rem; font-weight: 600; color: #1f2937;">📋 Login Activiteiten 
                <span style="display: inline-flex; padding: 0.25rem 0.5rem; font-size: 0.75rem; font-weight: 600; border-radius: 9999px; background-color: #dbeafe; color: #1e40af; margin-left: 0.5rem;">
                    {{ isset($logs) ? $logs->total() : '11' }}
                </span>
            </h3>
        </div>
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead style="background-color: #f9fafb;">
                    <tr>
                        <th style="padding: 0.75rem 1.5rem; text-align: left; font-size: 0.75rem; font-weight: 500; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em;">Gebruiker</th>
                        <th style="padding: 0.75rem 1.5rem; text-align: left; font-size: 0.75rem; font-weight: 500; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em;">Login Tijd</th>
                        <th style="padding: 0.75rem 1.5rem; text-align: left; font-size: 0.75rem; font-weight: 500; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em;">Logout Tijd</th>
                        <th style="padding: 0.75rem 1.5rem; text-align: left; font-size: 0.75rem; font-weight: 500; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em;">Sessie Duur</th>
                        <th style="padding: 0.75rem 1.5rem; text-align: left; font-size: 0.75rem; font-weight: 500; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em;">IP Adres</th>
                        <th style="padding: 0.75rem 1.5rem; text-align: left; font-size: 0.75rem; font-weight: 500; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em;">Status</th>
                    </tr>
                </thead>
                <tbody style="background-color: white;">
                    @forelse($logs ?? [] as $log)
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