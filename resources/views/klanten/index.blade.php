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

<!-- Header met titel en tegel -->
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:2em;">
    <h2 class="text-2xl font-bold">Klantenlijst</h2>
    
    <!-- Aantal klanten tegel -->
    <div style="background:#fff;border-radius:18px;box-shadow:0 2px 8px #e5e7eb;padding:1.1em 1.2em;display:flex;align-items:center;gap:0.6em;">
        <span style="background:#fef3e2;border-radius:50%;width:40px;height:40px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <svg width="22" height="22" fill="none" viewBox="0 0 16 16">
                <circle cx="8" cy="8" r="8" fill="#fef3e2"/>
                <g>
                    <circle cx="5.75" cy="7" r="1.1" fill="#ea580c"/>
                    <circle cx="10.25" cy="7" r="1.1" fill="#ea580c"/>
                    <circle cx="8" cy="5.75" r="1.5" fill="#ea580c"/>
                    <path d="M4.25 11c0-1.05 1.75-1.75 3.75-1.75s3.75 0.7 3.75 1.75v0.7a0.7 0.7 0 0 1-0.7 0.7H4.95a0.7 0.7 0 0 1-0.7-0.7V11z" fill="#fdba74"/>
                </g>
            </svg>
        </span>
        <div style="color:#222;font-size:1.5em;font-weight:700;letter-spacing:-0.5px;line-height:1;">{{ $klanten->count() }}</div>
    </div>
</div>

<!-- Actions: moved here from topbar -->
<div style="display:flex;gap:0.7em;align-items:center;margin:1.2em 0;">
    <a href="{{ route('klanten.create') }}" style="background:#c8e1eb;color:#111;padding:0.25em 0.9em;border-radius:7px;text-decoration:none;font-weight:600;font-size:0.95em;box-shadow:0 1px 3px #e0e7ff;">+ Klant toevoegen</a>
    <a href="{{ route('klanten.export') }}" 
       class="inline-flex items-center justify-center" 
       style="background:#c8e1eb;color:#111;padding:0.25em 0.9em;border-radius:7px;font-weight:600;font-size:0.95em;box-shadow:0 1px 3px #e0e7ff;text-decoration:none;"
       aria-label="Export Excel" 
       title="Export Excel">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 10l5 5 5-5"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15V3"/>
        </svg>
    </a>
    <select 
        id="sorteerKlanten" 
        style="padding:0.25em 0.9em;border:1.2px solid #d1d5db;border-radius:7px;font-size:0.95em;box-shadow:0 1px 3px #f3f4f6;background:#fff;cursor:pointer;margin-left:auto;"
    >
        <option value="naam-asc">Naam (A-Z)</option>
        <option value="naam-desc">Naam (Z-A)</option>
        <option value="voornaam-asc">Voornaam (A-Z)</option>
        <option value="voornaam-desc">Voornaam (Z-A)</option>
        <option value="datum-nieuw" selected>Nieuwste eerst</option>
        <option value="datum-oud">Oudste eerst</option>
        <option value="status-actief">Status: Actief eerst</option>
        <option value="status-inactief">Status: Inactief eerst</option>
    </select>
    <input 
        type="text" 
        id="searchKlanten" 
        placeholder="Zoek klant..." 
        value="{{ request('zoek') }}"
        style="padding:0.25em 0.9em;border:1.2px solid #d1d5db;border-radius:7px;font-size:0.95em;width:180px;box-shadow:0 1px 3px #f3f4f6;" 
        autocomplete="off"
    />
    
    {{-- Kolom visibility toggle --}}
    <div style="position:relative;">
        <button id="kolom-toggle-btn" type="button" 
                style="display:flex;align-items:center;gap:0.5em;padding:0.25em 0.9em;border:1.2px solid #d1d5db;border-radius:7px;background:#fff;cursor:pointer;font-size:0.95em;box-shadow:0 1px 3px #f3f4f6;">
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
                        <span style="font-size:0.85em;color:#374151;">E-mailadres</span>
                    </label>
                    <label style="display:flex;align-items:center;cursor:pointer;">
                        <input type="checkbox" class="kolom-toggle" data-kolom="datum" style="margin-right:0.5em;cursor:pointer;">
                        <span style="font-size:0.85em;color:#374151;">Datum toegevoegd</span>
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

<div class="overflow-x-auto bg-white/80 rounded-xl shadow border border-gray-100">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Naam</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Voornaam</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">E-mailadres</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Datum toegevoegd</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider"></th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-100">
            @foreach($klanten as $klant)
            <tr>
                <td class="px-6 py-4 whitespace-nowrap text-gray-900">
                    <div class="flex items-center gap-3">
                        @php
                            // Gebruik avatar kolom met cache-busting timestamp
                            $avatarPath = $klant->avatar ?? null;
                            $cacheKey = $klant->updated_at ? $klant->updated_at->timestamp : time();
                            $avatarUrl = $avatarPath ? asset('storage/' . $avatarPath) . '?v=' . $cacheKey : null;
                        @endphp
                        
                        @if($avatarUrl)
                            <img src="{{ $avatarUrl }}" alt="Avatar" class="w-9 h-9 rounded-full object-cover flex-none" style="aspect-ratio:1/1;" />
                        @else
                            <div class="w-9 h-9 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 font-semibold flex-none" style="aspect-ratio:1/1;">
                                {{ strtoupper(substr($klant->voornaam,0,1)) }}
                            </div>
                        @endif
                        <a href="{{ route('klanten.show', $klant) }}" class="font-semibold text-blue-700 hover:underline" title="Bekijk profiel">{{ $klant->naam }}</a>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-gray-700">{{ $klant->voornaam }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-gray-900">{{ $klant->email }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-gray-700">{{ $klant->created_at->format('d/m/Y') }}</td>
                <td class="px-6 py-4">
                    <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold {{ $klant->status === 'Actief' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                        {{ $klant->status }}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right align-top">
                    <div class="action-buttons flex flex-row flex-nowrap items-center justify-end gap-2">
                        <a href="{{ route('klanten.edit', $klant) }}" aria-label="Bewerk" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-orange-100 text-orange-800" title="Bewerk">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.71 7.04a1 1 0 0 0 0-1.41l-2.34-2.34a1 1 0 0 0-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                        </a>
                        <a href="{{ route('klanten.show', $klant) }}" aria-label="Profiel" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-800" title="Profiel">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 12c2.761 0 5-2.239 5-5s-2.239-5-5-5-5 2.239-5 5 2.239 5 5 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2 21c0-4 4-7 10-7s10 3 10 7"/></svg>
                        </a>
                        <form action="{{ route('klanten.invite', $klant) }}" method="POST" class="inline" onsubmit="return confirm('Uitnodiging versturen naar {{ $klant->email }}?')">
                            @csrf
                            <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-full hover:bg-green-200 transition-colors" style="background-color: #dff7e7ff; color: #15803d;" aria-label="Uitnodigen" title="Uitnodigen">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </button>
                        </form>
                        <form action="{{ route('klanten.verwijderViaPost', $klant) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" onclick="return confirm('Weet je zeker dat je deze klant wilt verwijderen?')" aria-label="Verwijderen" class="inline-flex items-center justify-center w-8 h-8 rounded-full hover:bg-red-200 transition-colors" style="background-color: #fbe2e2ff; color: #dc2626;">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m-7 0a1 1 0 001 1h8a1 1 0 001-1m-10 0V6a2 2 0 012-2h4a2 2 0 012 2v1"/></svg>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
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
    const voorkeuren = JSON.parse(localStorage.getItem('klantenKolomVoorkeuren') || '{}');
    
    kolomToggles.forEach(toggle => {
        const kolomNaam = toggle.dataset.kolom;
        const isZichtbaar = voorkeuren[kolomNaam] !== undefined ? voorkeuren[kolomNaam] : toggle.checked;
        
        toggle.checked = isZichtbaar;
        toggleKolom(kolomNaam, isZichtbaar);
    });
}

// Toggle kolom zichtbaarheid
function toggleKolom(kolomNaam, isZichtbaar) {
    const tabel = document.querySelector('table');
    const kolomIndex = getKolomIndex(kolomNaam);
    
    if (kolomIndex === -1) return;
    
    // Toggle header
    const headers = tabel.querySelectorAll('thead th');
    if (headers[kolomIndex]) {
        headers[kolomIndex].style.display = isZichtbaar ? '' : 'none';
    }
    
    // Toggle cells
    const rows = tabel.querySelectorAll('tbody tr');
    rows.forEach(row => {
        if (row.cells[kolomIndex]) {
            row.cells[kolomIndex].style.display = isZichtbaar ? '' : 'none';
        }
    });
}

// Helper functie om kolom index te krijgen
function getKolomIndex(kolomNaam) {
    const kolomMap = {
        'naam': 0,
        'voornaam': 1,
        'email': 2,
        'datum': 3,
        'status': 4
    };
    return kolomMap[kolomNaam] !== undefined ? kolomMap[kolomNaam] : -1;
}

// Sla kolom voorkeuren op
function slaKolomVoorkeurenOp() {
    const voorkeuren = {};
    kolomToggles.forEach(toggle => {
        voorkeuren[toggle.dataset.kolom] = toggle.checked;
    });
    localStorage.setItem('klantenKolomVoorkeuren', JSON.stringify(voorkeuren));
}

// Event listeners voor kolom toggles
kolomToggles.forEach(toggle => {
    toggle.addEventListener('change', function() {
        toggleKolom(this.dataset.kolom, this.checked);
        slaKolomVoorkeurenOp();
    });
});

// Initialize
laadKolomVoorkeuren();

// Zet standaard sortering op "nieuwste eerst" bij laden
document.addEventListener('DOMContentLoaded', function() {
    const sorteerSelect = document.getElementById('sorteerKlanten');
    
    // Trigger de sortering om de tabel direct te sorteren
    const event = new Event('change');
    sorteerSelect.dispatchEvent(event);
});

// Zoekfunctie
document.getElementById('searchKlanten').addEventListener('input', function(e) {
    const zoekterm = e.target.value.toLowerCase();
    const rijen = Array.from(document.querySelectorAll('tbody tr'));
    
    rijen.forEach(rij => {
        const tekst = rij.textContent.toLowerCase();
        rij.style.display = tekst.includes(zoekterm) ? '' : 'none';
    });
});

// Sorteerfunctie
document.getElementById('sorteerKlanten').addEventListener('change', function(e) {
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
            
            case 'datum-nieuw':
                veldA = a.cells[3].textContent.trim();
                veldB = b.cells[3].textContent.trim();
                return parseDatum(veldB) - parseDatum(veldA);
            
            case 'datum-oud':
                veldA = a.cells[3].textContent.trim();
                veldB = b.cells[3].textContent.trim();
                return parseDatum(veldA) - parseDatum(veldB);
            
            case 'status-actief':
                veldA = a.cells[4].textContent.trim();
                veldB = b.cells[4].textContent.trim();
                return veldA === 'Actief' ? -1 : 1;
            
            case 'status-inactief':
                veldA = a.cells[4].textContent.trim();
                veldB = b.cells[4].textContent.trim();
                return veldA === 'Actief' ? 1 : -1;
        }
    });
    
    // Verwijder alle rijen
    rijen.forEach(rij => tbody.removeChild(rij));
    
    // Voeg gesorteerde rijen toe
    rijen.forEach(rij => tbody.appendChild(rij));
});

// Helper functie voor datum parsing (dd/mm/yyyy)
function parseDatum(datumString) {
    const delen = datumString.split('/');
    return new Date(delen[2], delen[1] - 1, delen[0]);
}
</script>

@endsection
