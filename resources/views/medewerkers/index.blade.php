@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Medewerkerslijst</h1>
            <div class="flex items-center space-x-2 mt-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" style="background-color: #fef3c7; color: #d97706;">
                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                    </svg>
                    Aantal medewerkers: {{ $medewerkers->count() }}
                </span>
            </div>
        </div>
        
        <div class="flex items-center space-x-3">
            <a href="{{ route('medewerkers.create') }}" 
               class="rounded-full px-6 py-2 text-gray-800 font-bold text-sm flex items-center justify-center hover:opacity-80 transition duration-200" 
               style="background-color: #c8e1eb;">
                + Medewerker toevoegen
            </a>
            <button type="button" 
                    class="rounded-full px-6 py-2 text-gray-800 font-bold text-sm flex items-center justify-center hover:opacity-80 transition duration-200" 
                    style="background-color: #c8e1eb;">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            {{ session('error') }}
        </div>
    @endif

    <!-- Search Bar - compact zoals klanten -->
    <div style="display:flex;gap:0.7em;align-items:center;margin:1.2em 0;">
        <input 
            type="text" 
            id="searchMedewerkers" 
            placeholder="Zoek medewerker..." 
            value="{{ request('zoek') }}"
            style="padding:0.25em 0.9em;border:1.2px solid #d1d5db;border-radius:7px;font-size:0.95em;width:180px;box-shadow:0 1px 3px #f3f4f6;margin-left:auto;" 
            autocomplete="off"
        />
    </div>
    
    <script>
    // Simple search - gewoon client-side filtering
    document.getElementById('searchMedewerkers').addEventListener('input', function(e) {
        const zoekterm = e.target.value.toLowerCase();
        const rijen = document.querySelectorAll('#medewerkersTableBody tr');
        
        rijen.forEach(rij => {
            const tekst = rij.textContent.toLowerCase();
            rij.style.display = tekst.includes(zoekterm) ? '' : 'none';
        });
    });
    </script>

    <!-- Medewerkers Table -->
    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Naam</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Voornaam</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">E-mail</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rol</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acties</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" id="medewerkersTableBody">
                    @forelse($medewerkers as $medewerker)
                        <tr class="hover:bg-gray-50 medewerker-row">
                            <!-- Naam -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        @if($medewerker->avatar)
                                            <img class="h-10 w-10 rounded-full object-cover" 
                                                 src="{{ Storage::url($medewerker->avatar) }}" 
                                                 alt="{{ $medewerker->name }}">
                                        @else
                                            <div class="h-10 w-10 rounded-full flex items-center justify-center text-white font-medium" 
                                                 style="background-color: #c8e1eb; color: #1f2937;">
                                                {{ strtoupper(substr($medewerker->voornaam ?? $medewerker->name, 0, 1)) }}
                                            </div>
                                        @endif
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900 medewerker-naam">
                                            {{ $medewerker->name }}
                                        </div>
                                        @if($medewerker->telefoon)
                                            <div class="text-sm text-gray-500">
                                                📞 {{ $medewerker->telefoon }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- Voornaam -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 medewerker-voornaam">
                                {{ $medewerker->voornaam }}
                            </td>

                            <!-- E-mail -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 medewerker-email">
                                {{ $medewerker->email }}
                            </td>

                            <!-- Rol -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($medewerker->role === 'admin')
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                                        👑 Administrator
                                    </span>
                                @elseif($medewerker->role === 'medewerker')
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                        👤 Medewerker
                                    </span>
                                @elseif($medewerker->role === 'manager')
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800">
                                        👔 Manager
                                    </span>
                                @else
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                        {{ ucfirst($medewerker->role) }}
                                    </span>
                                @endif
                            </td>

                            <!-- Status -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $status = $medewerker->status ?? 'Actief';
                                @endphp
                                
                                @if(strtolower($status) === 'actief' || strtolower($status) === 'active')
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        ✅ Actief
                                    </span>
                                @elseif(strtolower($status) === 'inactief' || strtolower($status) === 'inactive')
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                        ❌ Inactief
                                    </span>
                                @elseif(strtolower($status) === 'verlof')
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        🏖️ Verlof
                                    </span>
                                @elseif(strtolower($status) === 'ziek')
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                        🤒 Ziek
                                    </span>
                                @else
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        ✅ Actief
                                    </span>
                                @endif
                            </td>

                            <!-- Acties -->
                            <td class="px-6 py-4 whitespace-nowrap text-right align-top">
                                <div class="action-buttons flex flex-row flex-nowrap items-center justify-end gap-2">
                                    <!-- Bewerken -->
                                    <a href="{{ route('medewerkers.edit', $medewerker) }}" aria-label="Bewerk" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-orange-100 text-orange-800" title="Bewerk">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.71 7.04a1 1 0 0 0 0-1.41l-2.34-2.34a1 1 0 0 0-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                                    </a>
                                    
                                    <!-- Bekijken -->
                                    <a href="{{ route('medewerkers.show', $medewerker) }}" aria-label="Profiel" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-800" title="Profiel">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 12c2.761 0 5-2.239 5-5s-2.239-5-5-5-5 2.239-5 5 2.239 5 5 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2 21c0-4 4-7 10-7s10 3 10 7"/></svg>
                                    </a>

                                    <!-- Uitnodigen -->
                                    <form method="POST" action="{{ route('medewerkers.invite', $medewerker) }}" class="inline" onsubmit="return confirm('Uitnodiging versturen naar {{ $medewerker->email }}?')">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-emerald-100 text-emerald-800" aria-label="Uitnodigen" title="Uitnodigen">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l9 6 9-6"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 6v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6"/></svg>
                                        </button>
                                    </form>

                                    <!-- Verwijderen (alleen voor admins, niet jezelf) -->
                                    @if(auth()->user()->role === 'admin' && $medewerker->id !== auth()->id())
                                        <form method="POST" action="{{ route('medewerkers.destroy', $medewerker) }}" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" onclick="return confirm('Weet je zeker dat je deze medewerker wilt verwijderen?')" aria-label="Verwijderen" class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-rose-100 text-rose-700" style="margin-right:2px;">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m-7 0a1 1 0 001 1h8a1 1 0 001-1m-10 0V6a2 2 0 012-2h4a2 2 0 012 2v1"/></svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                    <p class="text-lg font-medium">Geen medewerkers gevonden</p>
                                    <p class="text-sm">Voeg de eerste medewerker toe</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- JavaScript voor zoekfunctionaliteit - exact zoals klanten -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const zoekInput = document.getElementById('zoekInputMedewerkers');
    const zoekForm = document.getElementById('zoekFormMedewerkers');
    let timeout = null;
    
    console.log('🔍 Medewerkers zoekfunctie actief', {
        input: zoekInput ? 'found' : 'NOT FOUND',
        form: zoekForm ? 'found' : 'NOT FOUND',
        formAction: zoekForm ? zoekForm.action : 'N/A'
    });
    
    if (zoekInput && zoekForm) {
        zoekInput.addEventListener('input', function(e) {
            clearTimeout(timeout);
            const zoekwaarde = this.value.trim();
            console.log('⌨️ Input event fired:', zoekwaarde);
            
            timeout = setTimeout(function() {
                console.log('📤 Submitting form to:', zoekForm.action);
                console.log('📝 Form method:', zoekForm.method);
                console.log('🔑 Search value:', zoekwaarde);
                
                // Force submit
                zoekForm.submit();
                console.log('✅ Submit called');
            }, 400);
        });
        
        // Test om te zien of form submit überhaupt werkt
        zoekForm.addEventListener('submit', function(e) {
            console.log('🚀 Form submit event triggered!');
        });
    } else {
        console.error('❌ Zoek elementen niet gevonden!', {
            inputExists: !!zoekInput,
            formExists: !!zoekForm
        });
    }
});
</script>
@endsection
