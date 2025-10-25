@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    {{-- Header met periode selectie --}}
    <div class="flex justify-between items-center mb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Prestaties Overzicht</h1>
            <p class="text-sm text-gray-600 mt-1">Bekijk alle prestaties per medewerker - {{ $huidigKwartaal }} {{ $huidigJaar }}</p>
        </div>
        
        {{-- Jaar & Kwartaal filter --}}
        <div class="flex gap-2 items-center">
            <span class="text-sm text-gray-600">Periode:</span>
            
            {{-- Jaar navigatie met +/- knoppen --}}
            <div class="flex items-center gap-1">
                <a href="{{ route('admin.prestaties.overzicht') }}?jaar={{ $huidigJaar - 1 }}&kwartaal={{ $huidigKwartaal }}" 
                   class="px-2 py-1 text-sm bg-gray-100 hover:bg-gray-200 rounded transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <span class="text-sm font-semibold px-3 py-1 bg-blue-50 text-blue-700 rounded">
                    {{ $huidigJaar }}
                </span>
                <a href="{{ route('admin.prestaties.overzicht') }}?jaar={{ $huidigJaar + 1 }}&kwartaal={{ $huidigKwartaal }}" 
                   class="px-2 py-1 text-sm bg-gray-100 hover:bg-gray-200 rounded transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
            
            <select id="kwartaal-filter" class="text-sm rounded border-gray-300 py-1 px-2">
                <option value="Q1" {{ $huidigKwartaal == 'Q1' ? 'selected' : '' }}>Q1</option>
                <option value="Q2" {{ $huidigKwartaal == 'Q2' ? 'selected' : '' }}>Q2</option>
                <option value="Q3" {{ $huidigKwartaal == 'Q3' ? 'selected' : '' }}>Q3</option>
                <option value="Q4" {{ $huidigKwartaal == 'Q4' ? 'selected' : '' }}>Q4</option>
            </select>
        </div>
    </div>

    {{-- Totaal Overzicht Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Totaal Prestaties</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $totaalPrestaties }}</p>
                </div>
                <div class="bg-blue-100 rounded-full p-3">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Totale Commissie</p>
                    <p class="text-3xl font-bold text-green-600">€{{ number_format($totaleCommissie, 2, ',', '.') }}</p>
                </div>
                <div class="bg-green-100 rounded-full p-3">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Gemiddelde per Prestatie</p>
                    <p class="text-3xl font-bold text-blue-600">€{{ $totaalPrestaties > 0 ? number_format($totaleCommissie / $totaalPrestaties, 2, ',', '.') : '0,00' }}</p>
                </div>
                <div class="bg-blue-100 rounded-full p-3">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Alle Prestaties Tabel --}}
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Alle Prestaties</h2>
            
            {{-- Zoek en Filter velden - op één rij --}}
            <div class="flex flex-wrap gap-3 mb-3">
                <div class="relative flex-1 min-w-[200px]">
                    <input type="text" id="zoek-klant" placeholder="Zoek klant..." 
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </div>
                
                <select id="filter-medewerker" class="flex-1 min-w-[180px] border border-gray-300 rounded-lg py-2 px-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Alle medewerkers</option>
                    @foreach($medewerkerStats as $stat)
                        <option value="{{ $stat->id }}">{{ $stat->name }}</option>
                    @endforeach
                </select>
                
                <select id="filter-uitgevoerd" class="flex-1 min-w-[150px] border border-gray-300 rounded-lg py-2 px-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Alle statussen</option>
                    <option value="1">Uitgevoerd</option>
                    <option value="0">Niet uitgevoerd</option>
                </select>
                
                <select id="filter-factuur" class="flex-1 min-w-[180px] border border-gray-300 rounded-lg py-2 px-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Alle facturen</option>
                    <option value="1">Factuur verstuurd</option>
                    <option value="0">Geen factuur</option>
                </select>
                
                <select id="sorteer" class="flex-1 min-w-[160px] border border-gray-300 rounded-lg py-2 px-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="datum-desc">Nieuwste eerst</option>
                    <option value="datum-asc">Oudste eerst</option>
                    <option value="klant-asc">Klant A-Z</option>
                    <option value="klant-desc">Klant Z-A</option>
                    <option value="prijs-desc">Prijs hoog-laag</option>
                    <option value="prijs-asc">Prijs laag-hoog</option>
                </select>
                
                {{-- Kolom visibility toggle --}}
                <div class="relative">
                    <button id="kolom-toggle-btn" type="button" 
                            class="flex items-center gap-2 px-4 py-2 border border-gray-300 rounded-lg bg-white hover:bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                        </svg>
                        <span class="text-sm font-medium text-gray-700">Kolommen</span>
                    </button>
                    
                    <div id="kolom-toggle-dropdown" class="hidden absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-lg border border-gray-200 z-10">
                        <div class="p-3">
                            <div class="text-sm font-semibold text-gray-700 mb-2">Toon kolommen:</div>
                            <div class="space-y-2">
                                <label class="flex items-center">
                                    <input type="checkbox" class="kolom-toggle rounded border-gray-300 text-blue-600" data-kolom="klant" checked>
                                    <span class="ml-2 text-sm text-gray-700">Klant</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" class="kolom-toggle rounded border-gray-300 text-blue-600" data-kolom="medewerker" checked>
                                    <span class="ml-2 text-sm text-gray-700">Medewerker</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" class="kolom-toggle rounded border-gray-300 text-blue-600" data-kolom="dienst" checked>
                                    <span class="ml-2 text-sm text-gray-700">Dienst</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" class="kolom-toggle rounded border-gray-300 text-blue-600" data-kolom="prijs">
                                    <span class="ml-2 text-sm text-gray-700">Prijs incl BTW</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" class="kolom-toggle rounded border-gray-300 text-blue-600" data-kolom="commissie" checked>
                                    <span class="ml-2 text-sm text-gray-700">Commissie</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" class="kolom-toggle rounded border-gray-300 text-blue-600" data-kolom="uitgevoerd" checked>
                                    <span class="ml-2 text-sm text-gray-700">Uitgevoerd</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" class="kolom-toggle rounded border-gray-300 text-blue-600" data-kolom="factuur" checked>
                                    <span class="ml-2 text-sm text-gray-700">Factuur Klant</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" class="kolom-toggle rounded border-gray-300 text-blue-600" data-kolom="startdatum" checked>
                                    <span class="ml-2 text-sm text-gray-700">Startdatum</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" class="kolom-toggle rounded border-gray-300 text-blue-600" data-kolom="einddatum">
                                    <span class="ml-2 text-sm text-gray-700">Einddatum</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" class="kolom-toggle rounded border-gray-300 text-blue-600" data-kolom="opmerkingen">
                                    <span class="ml-2 text-sm text-gray-700">Opmerkingen</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full" id="alle-prestaties-tabel">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider kolom-klant">Klant</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider kolom-medewerker">Medewerker</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider kolom-dienst">Dienst</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider kolom-prijs">Prijs incl BTW</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider kolom-commissie">Commissie</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider kolom-uitgevoerd">Uitgevoerd</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider kolom-factuur">Factuur Klant</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider kolom-startdatum">Startdatum</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider kolom-einddatum">Einddatum</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider kolom-opmerkingen">Opmerkingen</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @php
                        // Haal alleen prestaties op van de ingelogde gebruiker
                        $allePrestaties = \App\Models\Prestatie::where('jaar', $huidigJaar)
                            ->where('kwartaal', $huidigKwartaal)
                            ->where('user_id', auth()->id()) // Filter op ingelogde gebruiker
                            ->with(['user', 'dienst', 'klant'])
                            ->orderBy('datum_prestatie', 'desc')
                            ->get();
                    @endphp
                    
                    @forelse($allePrestaties as $prestatie)
                        <tr class="hover:bg-gray-50 prestatie-row" 
                            data-klant="{{ strtolower($prestatie->klant_naam ?? '') }}"
                            data-medewerker="{{ $prestatie->user_id }}"
                            data-uitgevoerd="{{ $prestatie->is_uitgevoerd ? '1' : '0' }}"
                            data-factuur="{{ $prestatie->factuur_naar_klant ? '1' : '0' }}"
                            data-datum="{{ $prestatie->datum_prestatie->format('Y-m-d') }}"
                            data-prijs="{{ $prestatie->bruto_prijs }}">
                            
                            <td class="px-4 py-4 whitespace-nowrap kolom-klant">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $prestatie->klant_naam ?? 'Geen klant' }}
                                </div>
                            </td>
                            
                            <td class="px-4 py-4 whitespace-nowrap kolom-medewerker">
                                <div class="text-sm text-gray-900">{{ $prestatie->user->name ?? '-' }}</div>
                            </td>
                            
                            <td class="px-4 py-4 kolom-dienst">
                                <div class="text-sm text-gray-900">{{ $prestatie->dienst->naam ?? 'Andere' }}</div>
                            </td>
                            
                            <td class="px-4 py-4 whitespace-nowrap text-right kolom-prijs">
                                <span class="text-sm font-semibold text-gray-900">
                                    €{{ number_format($prestatie->bruto_prijs, 2, ',', '.') }}
                                </span>
                            </td>
                            
                            <td class="px-4 py-4 whitespace-nowrap text-right kolom-commissie">
                                <span class="text-sm font-semibold text-green-600">
                                    €{{ number_format($prestatie->commissie_bedrag, 2, ',', '.') }}
                                </span>
                            </td>
                            
                            <td class="px-4 py-4 text-center kolom-uitgevoerd">
                                @if($prestatie->is_uitgevoerd)
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        ✓ Ja
                                    </span>
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                        - Nee
                                    </span>
                                @endif
                            </td>
                            
                            <td class="px-4 py-4 text-center kolom-factuur">
                                <input type="checkbox" 
                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 h-4 w-4 cursor-pointer"
                                       {{ $prestatie->factuur_naar_klant ? 'checked' : '' }}
                                       onchange="toggleFactuurNaarKlant({{ $prestatie->id }}, this.checked)"
                                       title="Factuur naar klant verstuurd">
                            </td>
                            
                            <td class="px-4 py-4 whitespace-nowrap kolom-startdatum">
                                <span class="text-sm text-gray-900">
                                    {{ $prestatie->datum_prestatie ? $prestatie->datum_prestatie->format('d/m/Y') : '-' }}
                                </span>
                            </td>
                            
                            <td class="px-4 py-4 whitespace-nowrap kolom-einddatum">
                                <span class="text-sm text-gray-900">
                                    @if($prestatie->einddatum_prestatie)
                                        {{ \Carbon\Carbon::parse($prestatie->einddatum_prestatie)->format('d/m/Y') }}
                                    @else
                                        -
                                    @endif
                                </span>
                            </td>
                            
                            <td class="px-4 py-4 kolom-opmerkingen">
                                <div class="text-sm text-gray-500 max-w-xs truncate" title="{{ $prestatie->opmerkingen }}">
                                    {{ $prestatie->opmerkingen ?? '-' }}
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-6 py-12 text-center">
                                <p class="text-gray-500">Geen prestaties gevonden in deze periode</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Medewerkers Overzicht --}}
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Prestaties per Medewerker</h2>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Medewerker</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aantal Prestaties</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Totale Commissie</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gemiddelde</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acties</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($medewerkerStats as $stat)
                        @if($stat->id === auth()->id())
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                            <span class="text-blue-600 font-semibold">{{ substr($stat->name, 0, 1) }}</span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $stat->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $stat->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-semibold text-gray-900">{{ $stat->aantal_prestaties }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-semibold text-green-600">€{{ number_format($stat->totale_commissie, 2, ',', '.') }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm text-gray-900">€{{ number_format($stat->gemiddelde_commissie, 2, ',', '.') }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('admin.prestaties.coach.detail', $stat->id) }}?jaar={{ $huidigJaar }}&kwartaal={{ $huidigKwartaal }}" 
                                   class="text-blue-600 hover:text-blue-900">
                                    Details →
                                </a>
                            </td>
                        </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <p class="text-gray-500">Nog geen prestaties in deze periode</p>
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
    kolomToggleDropdown.classList.toggle('hidden');
});

// Sluit dropdown bij klik buiten
document.addEventListener('click', function(e) {
    if (!kolomToggleDropdown.contains(e.target) && e.target !== kolomToggleBtn) {
        kolomToggleDropdown.classList.add('hidden');
    }
});

// Laad opgeslagen kolom voorkeuren uit localStorage
function laadKolomVoorkeuren() {
    const voorkeuren = JSON.parse(localStorage.getItem('overzichtKolomVoorkeuren') || '{}');
    
    kolomToggles.forEach(toggle => {
        const kolomNaam = toggle.dataset.kolom;
        const isZichtbaar = voorkeuren[kolomNaam] !== undefined ? voorkeuren[kolomNaam] : toggle.checked;
        
        toggle.checked = isZichtbaar;
        toggleKolom(kolomNaam, isZichtbaar);
    });
}

// Toggle kolom zichtbaarheid
function toggleKolom(kolomNaam, isZichtbaar) {
    const headers = document.querySelectorAll(`.kolom-${kolomNaam}`);
    headers.forEach(element => {
        element.style.display = isZichtbaar ? '' : 'none';
    });
}

// Sla kolom voorkeuren op
function slaKolomVoorkeurenOp() {
    const voorkeuren = {};
    kolomToggles.forEach(toggle => {
        voorkeuren[toggle.dataset.kolom] = toggle.checked;
    });
    localStorage.setItem('overzichtKolomVoorkeuren', JSON.stringify(voorkeuren));
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

// Filter en zoek functionaliteit voor alle prestaties tabel
const zoekKlant = document.getElementById('zoek-klant');
const filterMedewerker = document.getElementById('filter-medewerker');
const filterUitgevoerd = document.getElementById('filter-uitgevoerd');
const filterFactuur = document.getElementById('filter-factuur');
const sorteerSelect = document.getElementById('sorteer');
const tabelRows = document.querySelectorAll('.prestatie-row');

function filterEnSorteerPrestaties() {
    const zoekterm = zoekKlant.value.toLowerCase();
    const medewerkerId = filterMedewerker.value;
    const uitgevoerdStatus = filterUitgevoerd.value;
    const factuurStatus = filterFactuur.value;
    
    // Filter rows
    let zichtbareRows = Array.from(tabelRows).filter(row => {
        // Klant zoekfilter
        const klantNaam = row.getAttribute('data-klant');
        const matchZoek = klantNaam.includes(zoekterm);
        
        // Medewerker filter
        const rowMedewerker = row.getAttribute('data-medewerker');
        const matchMedewerker = !medewerkerId || rowMedewerker === medewerkerId;
        
        // Uitgevoerd filter
        const rowUitgevoerd = row.getAttribute('data-uitgevoerd');
        const matchUitgevoerd = !uitgevoerdStatus || rowUitgevoerd === uitgevoerdStatus;
        
        // Factuur filter
        const rowFactuur = row.getAttribute('data-factuur');
        const matchFactuur = !factuurStatus || rowFactuur === factuurStatus;
        
        const isZichtbaar = matchZoek && matchMedewerker && matchUitgevoerd && matchFactuur;
        row.style.display = isZichtbaar ? '' : 'none';
        
        return isZichtbaar;
    });
    
    // Sorteer zichtbare rows
    const sorteerWaarde = sorteerSelect.value;
    zichtbareRows.sort((a, b) => {
        switch(sorteerWaarde) {
            case 'datum-desc':
                return b.getAttribute('data-datum').localeCompare(a.getAttribute('data-datum'));
            case 'datum-asc':
                return a.getAttribute('data-datum').localeCompare(b.getAttribute('data-datum'));
            case 'klant-asc':
                return a.getAttribute('data-klant').localeCompare(b.getAttribute('data-klant'));
            case 'klant-desc':
                return b.getAttribute('data-klant').localeCompare(a.getAttribute('data-klant'));
            case 'prijs-desc':
                return parseFloat(b.getAttribute('data-prijs')) - parseFloat(a.getAttribute('data-prijs'));
            case 'prijs-asc':
                return parseFloat(a.getAttribute('data-prijs')) - parseFloat(b.getAttribute('data-prijs'));
            default:
                return 0;
        }
    });
    
    // Herorden de DOM
    const tbody = document.querySelector('#alle-prestaties-tabel tbody');
    zichtbareRows.forEach(row => tbody.appendChild(row));
}

// Event listeners
zoekKlant.addEventListener('input', filterEnSorteerPrestaties);
filterMedewerker.addEventListener('change', filterEnSorteerPrestaties);
filterUitgevoerd.addEventListener('change', filterEnSorteerPrestaties);
filterFactuur.addEventListener('change', filterEnSorteerPrestaties);
sorteerSelect.addEventListener('change', filterEnSorteerPrestaties);

// Toggle factuur naar klant status
function toggleFactuurNaarKlant(prestatieId, isChecked) {
    console.log('Toggle factuur naar klant:', prestatieId, isChecked);
    
    // Gebruik directe URL constructie omdat de route nog niet bestaat
    const url = `/admin/prestaties/${prestatieId}/toggle-factuur`;
    
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ factuur_naar_klant: isChecked })
    })
    .then(response => {
        if (!response.ok) {
            return response.text().then(text => {
                console.error('Server error response:', text);
                throw new Error('Server error: ' + response.status);
            });
        }
        return response.json();
    })
    .then(data => {
        console.log('Success:', data);
        if (!data.success) {
            alert('Er ging iets mis bij het opslaan');
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error details:', error);
        alert('Er ging iets mis bij het opslaan: ' + error.message);
        location.reload();
    });
}

// Kwartaal filter - redirect bij wijziging
document.getElementById('kwartaal-filter').addEventListener('change', function() {
    const jaar = {{ $huidigJaar }};
    const kwartaal = this.value;
    window.location.href = `/admin/prestaties/overzicht?jaar=${jaar}&kwartaal=${kwartaal}`;
});
</script>
@endsection
