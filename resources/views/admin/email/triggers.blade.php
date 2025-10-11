@extends('layouts.app')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">🚀 Trigger Beheer</h1>
            <p class="text-gray-600 mt-2">Test alle automatische triggers en verstuur emails indien nodig. Veilig om te gebruiken - er worden alleen emails verstuurd aan mensen die ze daadwerkelijk nodig hebben.</p>
        </div>
        <a href="{{ route('admin.email.index') }}" 
           class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Terug naar Email Beheer
        </a>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Totaal Verstuurd</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $stats['total_sent'] ?? 0 }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Vandaag</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $stats['today_sent'] ?? 0 }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Mislukt</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $stats['failed'] ?? 0 }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v4"/>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Open Rate</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $stats['open_rate'] ?? '0%' }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Triggers Table -->
    <div class="bg-white shadow overflow-hidden sm:rounded-md mb-8">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Geconfigureerde Triggers</h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">Overzicht van alle email triggers en hun status</p>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trigger</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Emails Sent</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Run</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acties</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($triggers as $trigger)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-8 w-8">
                                    @if($trigger->type === 'testzadel_reminder')
                                        <div class="h-8 w-8 bg-orange-100 rounded-full flex items-center justify-center">
                                            <span class="text-orange-600 text-sm">🚴‍♀️</span>
                                        </div>
                                    @elseif($trigger->type === 'welcome_customer')
                                        <div class="h-8 w-8 bg-green-100 rounded-full flex items-center justify-center">
                                            <span class="text-green-600 text-sm">👋</span>
                                        </div>
                                    @elseif($trigger->type === 'birthday')
                                        <div class="h-8 w-8 bg-purple-100 rounded-full flex items-center justify-center">
                                            <span class="text-purple-600 text-sm">🎉</span>
                                        </div>
                                    @else
                                        <div class="h-8 w-8 bg-gray-100 rounded-full flex items-center justify-center">
                                            <span class="text-gray-600 text-sm">📧</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $trigger->name }}</div>
                                    <div class="text-sm text-gray-500">
                                        {{ $trigger->template_name ?? 'Handmatig verstuurd' }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ ucfirst(str_replace('_', ' ', $trigger->type)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($trigger->is_active)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Actief
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    Inactief
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ \DB::table('email_logs')->where('trigger_name', $trigger->type)->count() }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $trigger->last_run_at ? \Carbon\Carbon::parse($trigger->last_run_at)->diffForHumans() : 'Nog nooit' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            @if($trigger->id)
                                <a href="{{ route('admin.email.triggers.edit', $trigger->id) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Bewerken</a>
                                <a href="/admin/email" class="text-blue-600 hover:text-blue-900 mr-3">Email Templates</a>
                                <button onclick="runTrigger('{{ $trigger->type }}')" class="text-green-600 hover:text-green-900">Test Run</button>
                            @else
                                <a href="/admin/email" class="text-blue-600 hover:text-blue-900">Email Templates</a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                            Geen triggers gevonden. Verstuur eerst een email om triggers te zien.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Manual Trigger Section -->
    <div class="bg-white shadow sm:rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">🔧 Email Template Beheer</h3>
        <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">Email Templates Beheren</h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <p><strong>Email Template Module:</strong> <a href="/admin/email" class="underline">Ga naar Email Beheer (/admin/email)</a></p>
                        <p><strong>Template Types:</strong> welcome_customer, testzadel_reminder, birthday, welcome_employee</p>
                        <p><strong>Variabelen:</strong> Gebruik @{{voornaam}}, @{{naam}}, @{{email}}, @{{temporary_password}}, @{{bedrijf_naam}}</p>
                        <p><strong>Automatisering:</strong> Triggers zoeken automatisch naar actieve templates met de juiste types</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function runTrigger(triggerType) {
    if (confirm('Weet je zeker dat je deze trigger wilt uitvoeren? Dit kan emails versturen.')) {
        fetch(`/admin/email/triggers/run/${triggerType}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(`Trigger uitgevoerd! ${data.emails_sent} emails verstuurd.`);
                location.reload();
            } else {
                alert('Er is een fout opgetreden: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Er is een fout opgetreden bij het uitvoeren van de trigger.');
        });
    }
}
</script>
@endsection