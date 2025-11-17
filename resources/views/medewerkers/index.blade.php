@extends('layouts.app')

@section('content')
@if(session('success'))
    <div style="background:#d1fae5;color:#065f46;padding:1em;margin-bottom:1em;border-radius:5px;">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div style="background:#fef2f2;color:#dc2626;padding:1em;margin-bottom:1em;border-radius:5px;">
        {{ session('error') }}
    </div>
@endif

<!-- Header met titel -->
<div style="margin-bottom:2em;">
    <h2 class="text-2xl font-bold">Medewerkerslijst ({{ $medewerkers->count() }})</h2>
</div>

<!-- Actions: moved here from topbar -->
<div style="display:flex;flex-wrap:wrap;gap:0.7em;align-items:center;margin:1.2em 0;">
    <a href="{{ route('medewerkers.create') }}" style="background:#c8e1eb;color:#111;padding:0.5em 0.9em;border-radius:7px;text-decoration:none;font-weight:600;font-size:0.95em;box-shadow:0 1px 3px #e0e7ff;white-space:nowrap;">+ Medewerker toevoegen</a>
    <button type="button" 
            class="inline-flex items-center justify-center" 
            style="background:#c8e1eb;color:#111;padding:0.7em 0.9em;border-radius:7px;font-weight:600;font-size:0.95em;box-shadow:0 1px 3px #e0e7ff;border:none;cursor:pointer;"
            aria-label="Export Excel" 
            title="Export Excel">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 10l5 5 5-5"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15V3"/>
        </svg>
    </button>
    
    <!-- Responsive break: zoek, sorteer en kolommen op nieuwe regel op mobile -->
    <div style="display:flex;gap:0.7em;width:100%;flex-wrap:wrap;margin-top:0.5em;">
        <input 
            type="text" 
            id="searchMedewerkers" 
            placeholder="Zoek medewerker..." 
            value="{{ request('zoek') }}"
            style="padding:0.5em 0.9em;border:1.2px solid #d1d5db;border-radius:7px;font-size:0.95em;flex:1;min-width:180px;box-shadow:0 1px 3px #f3f4f6;" 
            autocomplete="off"
        />
        
        <select 
            id="sorteerMedewerkers" 
            style="padding:0.5em 0.9em;border:1.2px solid #d1d5db;border-radius:7px;font-size:0.95em;box-shadow:0 1px 3px #f3f4f6;background:#fff;cursor:pointer;flex:1;min-width:180px;"
        >
            <option value="naam-asc">Naam (A-Z)</option>
            <option value="naam-desc">Naam (Z-A)</option>
            <option value="voornaam-asc">Voornaam (A-Z)</option>
            <option value="voornaam-desc">Voornaam (Z-A)</option>
            <option value="rol-admin">Rol: Admin eerst</option>
            <option value="rol-medewerker">Rol: Medewerker eerst</option>
            <option value="status-actief">Status: Actief eerst</option>
            <option value="status-inactief">Status: Inactief eerst</option>
        </select>
        
        {{-- Kolom visibility toggle --}}
        <div style="position:relative;">
            <button id="kolom-toggle-btn" type="button" 
                    style="display:flex;align-items:center;gap:0.5em;padding:0.5em 0.9em;border:1.2px solid #d1d5db;border-radius:7px;background:#fff;cursor:pointer;font-size:0.95em;box-shadow:0 1px 3px #f3f4f6;white-space:nowrap;">
                <svg style="width:18px;height:18px;color:#4b5563;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                </svg>
                <span style="font-weight:600;color:#374151;">Kolommen</span>
            </button>
            
            <div id="kolom-toggle-dropdown" style="display:none;position:absolute;right:0;margin-top:0.5em;width:240px;background:#fff;border-radius:8px;box-shadow:0 4px 12px rgba(0,0,0,0.15);border:1px solid #e5e7eb;z-index:10;">
                <div style="padding:0.75em;">
                    <div style="font-size:0.85em;font-weight:600;color:#374151;margin-bottom:0.5em;">Toon kolommen:</div>
                    <div style="display:flex;flex-direction:column;gap:0.5em;">
                        <label style="display:flex;align-items:center;cursor:pointer;">
                            <input type="checkbox" class="kolom-toggle" data-kolom="naam" checked style="margin-right:0.5em;cursor:pointer;">
                            <span style="font-size:0.85em;color:#374151;">Naam</span>
                        </label>
                        <label style="display:flex;align-items:center;cursor:pointer;">
                            <input type="checkbox" class="kolom-toggle" data-kolom="voornaam" checked style="margin-right:0.5em;cursor:pointer;">
                            <span style="font-size:0.85em;color:#374151;">Voornaam</span>
                        </label>
                        <label style="display:flex;align-items:center;cursor:pointer;">
                            <input type="checkbox" class="kolom-toggle" data-kolom="email" checked style="margin-right:0.5em;cursor:pointer;">
                            <span style="font-size:0.85em;color:#374151;">E-mail</span>
                        </label>
                        <label style="display:flex;align-items:center;cursor:pointer;">
                            <input type="checkbox" class="kolom-toggle" data-kolom="rol" checked style="margin-right:0.5em;cursor:pointer;">
                            <span style="font-size:0.85em;color:#374151;">Rol</span>
                        </label>
                        <label style="display:flex;align-items:center;cursor:pointer;">
                            <input type="checkbox" class="kolom-toggle" data-kolom="status" checked style="margin-right:0.5em;cursor:pointer;">
                            <span style="font-size:0.85em;color:#374151;">Status</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

    <!-- Medewerkers Table -->
    <div class="overflow-x-auto bg-white/80 rounded-xl shadow border border-gray-100">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Naam</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Voornaam</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">E-mail</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Rol</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider"></th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100" id="medewerkersTableBody">
                    @forelse($medewerkers as $medewerker)
                        <tr class="hover:bg-gray-50 medewerker-row">
                            <!-- Naam met Avatar -->
                            <td class="px-6 py-4 whitespace-nowrap text-gray-900">
                                <div class="flex items-center gap-3">
                                    @php
                                        // AVATAR PATH - productie compatibel (EXACT ZELFDE ALS KLANTEN)
                                        $avatarPath = $medewerker->avatar_path ?? null;
                                        $cacheKey = optional($medewerker->updated_at)->timestamp ?? time();
                                        $avatarUrl = null;
                                        
                                        if ($avatarPath) {
                                            // Database bevat: 'avatars/medewerkers/filename.png'
                                            // Omzetten naar: 'uploads/avatars/medewerkers/filename.png'
                                            if (app()->environment('production')) {
                                                $avatarUrl = url(str_replace('avatars/', 'uploads/avatars/', $avatarPath));
                                            } else {
                                                $avatarUrl = asset('storage/' . $avatarPath);
                                            }
                                        }
                                    @endphp
                                    @if($avatarUrl)
                                        <img src="{{ $avatarUrl }}?t={{ $cacheKey }}" alt="{{ $medewerker->name }}" class="h-10 w-10 rounded-full object-cover">
                                    @else
                                        <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center text-gray-600 font-semibold">
                                            {{ strtoupper(substr($medewerker->voornaam, 0, 1)) }}
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
                            <td class="px-6 py-4">
                                @if($medewerker->role === 'admin')
                                    <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-700">
                                        👑 Administrator
                                    </span>
                                @elseif($medewerker->role === 'medewerker')
                                    <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">
                                        👤 Medewerker
                                    </span>
                                @elseif($medewerker->role === 'stagiair')
                                    <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                                        🎓 Stagiair
                                    </span>
                                @else
                                    <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">
                                        {{ ucfirst($medewerker->role) }}
                                    </span>
                                @endif
                            </td>

                            <!-- Status -->
                            <td class="px-6 py-4">
                                @php
                                    $status = $medewerker->status ?? 'Actief';
                                @endphp
                                
                                @if(strtolower($status) === 'actief' || strtolower($status) === 'active')
                                    <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">
                                        ✅ Actief
                                    </span>
                                @elseif(strtolower($status) === 'inactief' || strtolower($status) === 'inactive')
                                    <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-700">
                                        ❌ Inactief
                                    </span>
                                @elseif(strtolower($status) === 'verlof')
                                    <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700">
                                        🏖️ Verlof
                                    </span>
                                @elseif(strtolower($status) === 'ziek')
                                    <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700">
                                        🤒 Ziek
                                    </span>
                                @else
                                    <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">
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
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-full hover:bg-green-200 transition-colors" 
                                                style="background-color: #dff7e7; color: #15803d;"
                                                title="Uitnodiging versturen">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
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
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-full hover:bg-red-200 transition-colors" 
                                                style="background-color: #fbe2e2ff; color: #dc2626;"
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
    if (!kolomToggleDropdown.contains(e.target) && e.target !== kolomToggleBtn && !kolomToggleBtn.contains(e.target)) {
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
        'voornaam': 1,
        'email': 2,
        'rol': 3,
        'status': 4
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

// Sorteerfunctie
document.getElementById('sorteerMedewerkers').addEventListener('change', function(e) {
    const sorteerType = e.target.value;
    const tbody = document.querySelector('tbody');
    const rijen = Array.from(tbody.querySelectorAll('tr'));
    
    rijen.sort((a, b) => {
        let veldA, veldB;
        
        switch(sorteerType) {
            case 'naam-asc':
                veldA = a.cells[0].textContent.trim().toLowerCase();
                veldB = b.cells[0].textContent.trim().toLowerCase();
                return veldA.localeCompare(veldB);
            
            case 'naam-desc':
                veldA = a.cells[0].textContent.trim().toLowerCase();
                veldB = b.cells[0].textContent.trim().toLowerCase();
                return veldB.localeCompare(veldA);
            
            case 'voornaam-asc':
                veldA = a.cells[1].textContent.trim().toLowerCase();
                veldB = b.cells[1].textContent.trim().toLowerCase();
                return veldA.localeCompare(veldB);
            
            case 'voornaam-desc':
                veldA = a.cells[1].textContent.trim().toLowerCase();
                veldB = b.cells[1].textContent.trim().toLowerCase();
                return veldB.localeCompare(veldA);
            
            case 'rol-admin':
                veldA = a.cells[3].textContent.trim();
                veldB = b.cells[3].textContent.trim();
                return veldA.includes('Administrator') ? -1 : 1;
            
            case 'rol-medewerker':
                veldA = a.cells[3].textContent.trim();
                veldB = b.cells[3].textContent.trim();
                return veldA.includes('Medewerker') ? -1 : 1;
            
            case 'status-actief':
                veldA = a.cells[4].textContent.trim();
                veldB = b.cells[4].textContent.trim();
                return veldA.includes('Actief') ? -1 : 1;
            
            case 'status-inactief':
                veldA = a.cells[4].textContent.trim();
                veldB = b.cells[4].textContent.trim();
                return veldA.includes('Actief') ? 1 : -1;
        }
    });
    
    // Verwijder alle rijen
    rijen.forEach(rij => tbody.removeChild(rij));
    
    // Voeg gesorteerde rijen toe
    rijen.forEach(rij => tbody.appendChild(rij));
});
</script>
@endsection
