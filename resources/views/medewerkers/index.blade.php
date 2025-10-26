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

    <!-- Filters - exact zoals klanten -->
    <div style="display:flex;gap:0.7em;align-items:center;margin:1.2em 0;">
        <input 
            type="text" 
            id="searchMedewerkers" 
            placeholder="Zoek medewerker..." 
            style="padding:0.25em 0.9em;border:1.2px solid #d1d5db;border-radius:7px;font-size:0.95em;width:180px;box-shadow:0 1px 3px #f3f4f6;margin-left:auto;" 
            autocomplete="off"
        />
        
        {{-- Kolom visibility toggle --}}
        <div style="position:relative;">
            <button type="button" id="kolom-toggle-btn" 
                    style="padding:0.25em 0.9em;border:1.2px solid #d1d5db;border-radius:7px;font-size:0.95em;background:#fff;cursor:pointer;display:flex;align-items:center;gap:0.5em;box-shadow:0 1px 3px #f3f4f6;">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 0v10m0-10a2 2 0 012 2v6a2 2 0 01-2 2m0 0a2 2 0 01-2-2"/>
                </svg>
                <span>Kolommen</span>
            </button>
            
            <div id="kolom-toggle-dropdown" style="display:none;position:absolute;right:0;top:calc(100% + 0.5em);background:#fff;border:1px solid #d1d5db;border-radius:7px;padding:0.8em;min-width:200px;box-shadow:0 4px 12px rgba(0,0,0,0.1);z-index:10;">
                <div style="font-weight:600;font-size:0.9em;margin-bottom:0.6em;color:#374151;">Toon kolommen:</div>
                <div style="display:flex;flex-direction:column;gap:0.5em;">
                    <label style="display:flex;align-items:center;gap:0.5em;cursor:pointer;font-size:0.9em;">
                        <input type="checkbox" class="kolom-toggle" data-kolom="naam" checked style="cursor:pointer;">
                        <span>Naam</span>
                    </label>
                    <label style="display:flex;align-items:center;gap:0.5em;cursor:pointer;font-size:0.9em;">
                        <input type="checkbox" class="kolom-toggle" data-kolom="email" checked style="cursor:pointer;">
                        <span>Email</span>
                    </label>
                    <label style="display:flex;align-items:center;gap:0.5em;cursor:pointer;font-size:0.9em;">
                        <input type="checkbox" class="kolom-toggle" data-kolom="rol" checked style="cursor:pointer;">
                        <span>Rol</span>
                    </label>
                    <label style="display:flex;align-items:center;gap:0.5em;cursor:pointer;font-size:0.9em;">
                        <input type="checkbox" class="kolom-toggle" data-kolom="organisatie" checked style="cursor:pointer;">
                        <span>Organisatie</span>
                    </label>
                    <label style="display:flex;align-items:center;gap:0.5em;cursor:pointer;font-size:0.9em;">
                        <input type="checkbox" class="kolom-toggle" data-kolom="prestaties" checked style="cursor:pointer;">
                        <span>Prestaties</span>
                    </label>
                    <label style="display:flex;align-items:center;gap:0.5em;cursor:pointer;font-size:0.9em;">
                        <input type="checkbox" class="kolom-toggle" data-kolom="laatst" checked style="cursor:pointer;">
                        <span>Laatst ingelogd</span>
                    </label>
                    <label style="display:flex;align-items:center;gap:0.5em;cursor:pointer;font-size:0.9em;">
                        <input type="checkbox" class="kolom-toggle" data-kolom="acties" checked style="cursor:pointer;">
                        <span>Acties</span>
                    </label>
                </div>
            </div>
        </div>
    </div>

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
                            <!-- Naam met Avatar -->
                            <td class="px-6 py-4 whitespace-nowrap text-gray-900">
                                <div class="flex items-center gap-3">
                                    @if($medewerker->avatar_path)
                                        <img src="{{ asset('storage/' . $medewerker->avatar_path) }}" alt="Avatar" class="w-9 h-9 rounded-full object-cover flex-none" style="aspect-ratio:1/1;" />
                                    @else
                                        <div class="w-9 h-9 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 font-semibold flex-none" style="aspect-ratio:1/1;">
                                            {{ strtoupper(substr($medewerker->voornaam ?? $medewerker->name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <a href="{{ route('medewerkers.show', $medewerker) }}" class="font-semibold text-blue-700 hover:underline medewerker-naam" title="Bekijk profiel">
                                        {{ $medewerker->name }}
                                    </a>
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

                                    <!-- Uitnodiging versturen -->
                                    <form action="{{ route('medewerkers.send-invitation', $medewerker) }}" 
                                          method="POST" 
                                          class="inline"
                                          onsubmit="return confirm('Uitnodiging versturen naar {{ $medewerker->email }}? Dit genereert een nieuw tijdelijk wachtwoord.');">
                                        @csrf
                                        <button type="submit" 
                                                aria-label="Uitnodiging versturen" 
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-emerald-100 text-emerald-800 hover:bg-emerald-200" 
                                                title="Uitnodiging versturen">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l9 6 9-6"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 6v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6"/></svg>
                                        </button>
                                    </form>

                                    <!-- Verwijderen -->
                                    <form action="{{ route('medewerkers.destroy', $medewerker->id) }}" 
                                          method="POST" 
                                          class="inline"
                                          onsubmit="return confirm('Weet je zeker dat je {{ $medewerker->name }} wilt verwijderen?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                aria-label="Verwijderen" 
                                                class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-rose-100 text-rose-700" 
                                                style="margin-right:2px;"
                                                title="Verwijderen">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m-7 0a1 1 0 001 1h8a1 1 0 001-1m-10 0V6a2 2 0 012-2h4a2 2 0 012 2v1"/></svg>
                                        </button>
                                    </form>
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

<script>
// Kolom visibility toggle functionaliteit
const kolomToggleBtn = document.getElementById('kolom-toggle-btn');
const kolomToggleDropdown = document.getElementById('kolom-toggle-dropdown');
const kolomToggles = document.querySelectorAll('.kolom-toggle');

// Toggle dropdown
kolomToggleBtn.addEventListener('click', function(e) {
    e.stopPropagation();
    const isHidden = kolomToggleDropdown.style.display === 'none';
    kolomToggleDropdown.style.display = isHidden ? 'block' : 'none';
});

// Sluit dropdown bij klik buiten
document.addEventListener('click', function(e) {
    if (!kolomToggleBtn.contains(e.target) && !kolomToggleDropdown.contains(e.target)) {
        kolomToggleDropdown.style.display = 'none';
    }
});

// Laad opgeslagen kolom voorkeuren uit localStorage
function laadKolomVoorkeuren() {
    const voorkeuren = JSON.parse(localStorage.getItem('medewerkersKolomVoorkeuren') || '{}');
    
    kolomToggles.forEach(toggle => {
        const kolomNaam = toggle.dataset.kolom;
        const isZichtbaar = voorkeuren[kolomNaam] !== undefined ? voorkeuren[kolomNaam] : toggle.checked;
        
        toggle.checked = isZichtbaar;
        toggleKolom(kolomNaam, isZichtbaar);
    });
}

// Toggle kolom zichtbaarheid
function toggleKolom(kolomNaam, isZichtbaar) {
    const table = document.querySelector('table');
    if (!table) return;
    
    const kolomIndex = getKolomIndex(kolomNaam);
    if (kolomIndex === -1) return;
    
    // Toggle header
    const headers = table.querySelectorAll('thead th');
    if (headers[kolomIndex]) {
        headers[kolomIndex].style.display = isZichtbaar ? '' : 'none';
    }
    
    // Toggle cells in alle rijen
    const rows = table.querySelectorAll('tbody tr');
    rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        if (cells[kolomIndex]) {
            cells[kolomIndex].style.display = isZichtbaar ? '' : 'none';
        }
    });
}

// Helper functie om kolom index te krijgen
function getKolomIndex(kolomNaam) {
    const mapping = {
        'naam': 0,
        'email': 1,
        'rol': 2,
        'organisatie': 3,
        'prestaties': 4,
        'laatst': 5,
        'acties': 6
    };
    return mapping[kolomNaam] ?? -1;
}

// Sla kolom voorkeuren op
function slaKolomVoorkeurenOp() {
    const voorkeuren = {};
    kolomToggles.forEach(toggle => {
        voorkeuren[toggle.dataset.kolom] = toggle.checked;
    });
    localStorage.setItem('medewerkersKolomVoorkeuren', JSON.stringify(voorkeuren));
}

// Event listeners voor kolom toggles
kolomToggles.forEach(toggle => {
    toggle.addEventListener('change', function() {
        toggleKolom(this.dataset.kolom, this.checked);
        slaKolomVoorkeurenOp();
    });
});

// Laad voorkeuren bij pagina load
laadKolomVoorkeuren();

// Zoekfunctie - client-side filtering
document.getElementById('searchMedewerkers').addEventListener('input', function(e) {
    const zoekterm = e.target.value.toLowerCase();
    const tbody = document.querySelector('tbody');
    const rijen = tbody.querySelectorAll('tr');
    
    rijen.forEach(rij => {
        const tekst = rij.textContent.toLowerCase();
        rij.style.display = tekst.includes(zoekterm) ? '' : 'none';
    });
});
</script>
@endsection
