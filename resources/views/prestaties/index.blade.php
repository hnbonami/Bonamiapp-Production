@extends('layouts.app')

@section('content')
{{-- Success/Error Banners --}}
@if(session('success'))
    <div id="success-banner" style="background:#d1fae5;color:#065f46;padding:1em;margin-bottom:1em;border-radius:5px;display:flex;justify-content:space-between;align-items:center;">
        <span>{{ session('success') }}</span>
        <button onclick="this.parentElement.remove()" style="color:#065f46;font-size:1.5em;line-height:1;border:none;background:none;cursor:pointer;">&times;</button>
    </div>
@endif

@if(session('error'))
    <div id="error-banner" style="background:#fef2f2;color:#dc2626;padding:1em;margin-bottom:1em;border-radius:5px;display:flex;justify-content:space-between;align-items:center;">
        <span>{{ session('error') }}</span>
        <button onclick="this.parentElement.remove()" style="color:#dc2626;font-size:1.5em;line-height:1;border:none;background:none;cursor:pointer;">&times;</button>
    </div>
@endif

<div class="container mx-auto px-4 py-6">
    {{-- Header met jaar & kwartaal selectie --}}
    <div class="flex justify-between items-center mb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Mijn Prestaties {{ $huidigJaar }}</h1>
            <p class="text-sm text-gray-600 mt-1">Beheer je geleverde diensten en commissies - {{ $huidigKwartaal }} {{ $huidigJaar }}</p>
        </div>
        
        {{-- Jaar & Kwartaal filter --}}
        <div class="flex gap-2 items-center">
            <span class="text-sm text-gray-600">Periode:</span>
            
            {{-- Jaar navigatie met +/- knoppen --}}
            <div class="flex items-center gap-1">
                <a href="{{ route('prestaties.index') }}?jaar={{ $huidigJaar - 1 }}&kwartaal={{ $huidigKwartaal }}" 
                   class="px-2 py-1 text-sm bg-gray-100 hover:bg-gray-200 rounded transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <span class="text-sm font-semibold px-3 py-1 bg-blue-50 text-blue-700 rounded">
                    {{ $huidigJaar }}
                </span>
                <a href="{{ route('prestaties.index') }}?jaar={{ $huidigJaar + 1 }}&kwartaal={{ $huidigKwartaal }}" 
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

    {{-- Statistieken kaarten - compact --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-xs text-gray-600 mb-1">Aantal Prestaties</div>
            <div class="text-2xl font-bold text-gray-900">{{ $prestaties->count() }}</div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-xs text-gray-600 mb-1">Mijn Bonus</div>
            <div class="flex items-center gap-2">
                <div class="text-2xl font-bold text-green-600">
                    @php
                        // Haal de totale bonus op van de ingelogde medewerker
                        $medewerker = auth()->user();
                        $factoren = $medewerker->commissieFactoren->first();
                        $totaleBonus = $factoren ? $factoren->totale_bonus : 0;
                        $organisatieNaam = $medewerker->organisatie->naam ?? 'Organisatie';
                        
                        // Bereken gemiddeld effectief commissie percentage voor deze medewerker
                        $gemiddeldEffectiefPercentage = 0;
                        if ($prestaties->count() > 0) {
                            $gemiddeldEffectiefPercentage = $prestaties->avg('commissie_percentage');
                        }
                    @endphp
                    {{ $totaleBonus > 0 ? '+' : '' }}{{ number_format($totaleBonus, 1, ',', '.') }}%
                </div>
                <button onclick="openCommissieInfoModal()" 
                        class="flex items-center gap-1 text-blue-500 hover:text-blue-700 transition"
                        title="Hoe wordt mijn commissie berekend?">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"/>
                    </svg>
                    <span class="text-xs">info</span>
                </button>
            </div>
            @if($factoren)
                <div class="text-xs text-gray-500 mt-1">
                    {{ $factoren->bonus_richting === 'plus' ? '→ Extra inkomst voor jou' : '→ Gaat naar organisatie' }}
                </div>
                @if($gemiddeldEffectiefPercentage > 0)
                    <div class="text-xs font-medium text-gray-700 mt-2 p-2 bg-gray-50 rounded">
                        {{ $organisatieNaam }} krijgt gem. {{ number_format($gemiddeldEffectiefPercentage, 1) }}%
                    </div>
                @endif
            @endif
        </div>
        
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-xs text-gray-600 mb-1">Mijn Totale Inkomst</div>
            <div class="text-2xl font-bold text-green-600">
                @php
                    // Totale Netto Inkomst = Σ(Excl BTW - Commissie)
                    $totaleNettoInkomst = $prestaties->sum(function($p) {
                        $prijsExclBtw = $p->bruto_prijs / 1.21;
                        $commissieBedrag = $prijsExclBtw * ($p->commissie_percentage / 100);
                        return $prijsExclBtw - $commissieBedrag;
                    });
                @endphp
                €{{ number_format($totaleNettoInkomst, 2, ',', '.') }}
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-xs text-gray-600 mb-1">Gemiddelde Inkomst per Prestatie</div>
            <div class="text-2xl font-bold text-gray-900">
                @php
                    if ($prestaties->count() > 0) {
                        $totaleNettoInkomst = $prestaties->sum(function($p) {
                            $prijsExclBtw = $p->bruto_prijs / 1.21;
                            $commissieBedrag = $prijsExclBtw * ($p->commissie_percentage / 100);
                            return $prijsExclBtw - $commissieBedrag;
                        });
                        $gemiddelde = $totaleNettoInkomst / $prestaties->count();
                    } else {
                        $gemiddelde = 0;
                    }
                @endphp
                €{{ number_format($gemiddelde, 2, ',', '.') }}
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        {{-- Nieuwe prestatie toevoegen formulier - compact --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow p-4">
                <h2 class="text-lg font-bold mb-3">Nieuwe Prestatie</h2>
                
                <form id="prestatie-form" method="POST" action="{{ route('prestaties.store') }}">
                    @csrf
                    
                    {{-- Startdatum, Einddatum, Dienst & Prijs alle 4 naast elkaar --}}
                    <div class="grid grid-cols-4 gap-3 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Startdatum</label>
                            <input type="date" name="datum_prestatie" required
                                   class="w-full rounded border-gray-300 py-2 px-3"
                                   value="{{ date('Y-m-d') }}">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Einddatum (optioneel)</label>
                            <input type="date" name="einddatum_prestatie"
                                   class="w-full rounded border-gray-300 py-2 px-3">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Dienst</label>
                            <select name="dienst_id" id="dienst-select" required
                                    class="w-full rounded border-gray-300 py-2 px-3">
                                <option value="">-- Kies dienst --</option>
                                @foreach($beschikbareDiensten as $dienst)
                                    <option value="{{ $dienst->id }}" 
                                            data-prijs="{{ $dienst->standaard_prijs }}"
                                            data-commissie="{{ $dienst->commissie_percentage }}">
                                        {{ $dienst->naam }}
                                    </option>
                                @endforeach
                                <option value="andere" data-prijs="0" data-commissie="0">Andere</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Prijs</label>
                            <input type="number" name="prijs" id="prijs-input" step="0.01" required
                                   class="w-full rounded border-gray-300 py-2 px-3">
                        </div>
                    </div>
                    
                    {{-- Bedrag excl. BTW en netto inkomst preview --}}
                    <div class="mt-3 mb-6 p-3 bg-gray-50 rounded-md border border-gray-200">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm text-gray-600">Bedrag excl. BTW:</span>
                            <span id="bedrag-excl-btw-preview" class="text-lg font-bold text-gray-900">€0,00</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Jouw netto inkomst:</span>
                            <span id="commissie-preview" class="text-lg font-bold text-green-600">€0,00</span>
                        </div>
                    </div>
                    
                    {{-- Klant en Opmerkingen naast elkaar --}}
                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Klant (optioneel)</label>
                            <input type="text" id="klant-zoek" placeholder="Zoek klant..." 
                                   class="w-full rounded border-gray-300 py-1 px-2 mb-1 text-sm">
                            <select name="klant_id" id="klant-select" class="w-full rounded border-gray-300 py-1 px-2 text-sm" size="3">
                                <option value="">-- Geen klant --</option>
                                @foreach($klanten as $klant)
                                    <option value="{{ $klant['id'] }}" data-naam="{{ strtolower($klant['naam']) }}">{{ $klant['naam'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Opmerkingen</label>
                            <textarea name="opmerkingen" rows="4" 
                                      class="w-full rounded border-gray-300 py-1 px-2 text-sm"></textarea>
                        </div>
                    </div>
                    
                    {{-- Uitgevoerd checkbox --}}
                    <div class="mb-4">
                        <label class="flex items-center">
                            <input type="checkbox" name="is_uitgevoerd" value="1" 
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 h-4 w-4">
                            <span class="ml-2 text-sm text-gray-700">Dienst is uitgevoerd</span>
                        </label>
                    </div>
                    
                    <button type="submit" class="w-full py-3 px-4 rounded font-medium hover:opacity-90 transition text-gray-900" style="background-color: #c8e1eb;">
                        Prestatie Toevoegen
                    </button>
                </form>
            </div>
        </div>

        {{-- Prestaties tabel --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow">
                <div class="p-4 border-b">
                    <h2 class="text-lg font-bold mb-4">Prestaties {{ $huidigKwartaal }} {{ $huidigJaar }}</h2>
                    
                    {{-- Zoek en Filter velden - op één rij --}}
                    <div class="flex flex-wrap gap-3 mb-3">
                        <div class="relative flex-1 min-w-[200px]">
                            <input type="text" id="prestatie-zoek" placeholder="Zoek op dienst, klant of opmerking..." 
                                   class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                        </div>
                        
                        <select id="filter-dienst" class="flex-1 min-w-[180px] border border-gray-300 rounded-lg py-2 px-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Alle diensten</option>
                            @foreach($beschikbareDiensten as $dienst)
                                <option value="{{ $dienst->id }}">{{ $dienst->naam }}</option>
                            @endforeach
                        </select>
                        
                        <select id="filter-uitgevoerd" class="flex-1 min-w-[150px] border border-gray-300 rounded-lg py-2 px-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Alle statussen</option>
                            <option value="1">Uitgevoerd</option>
                            <option value="0">Niet uitgevoerd</option>
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
                                            <input type="checkbox" class="kolom-toggle rounded border-gray-300 text-blue-600" data-kolom="uitgevoerd" checked>
                                            <span class="ml-2 text-sm text-gray-700">Uitgevoerd</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="checkbox" class="kolom-toggle rounded border-gray-300 text-blue-600" data-kolom="datum" checked>
                                            <span class="ml-2 text-sm text-gray-700">Datum</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="checkbox" class="kolom-toggle rounded border-gray-300 text-blue-600" data-kolom="dienst" checked>
                                            <span class="ml-2 text-sm text-gray-700">Dienst</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="checkbox" class="kolom-toggle rounded border-gray-300 text-blue-600" data-kolom="klant" checked>
                                            <span class="ml-2 text-sm text-gray-700">Klant</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="checkbox" class="kolom-toggle rounded border-gray-300 text-blue-600" data-kolom="prijs" checked>
                                            <span class="ml-2 text-sm text-gray-700">Bruto Prijs (incl. BTW)</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="checkbox" class="kolom-toggle rounded border-gray-300 text-blue-600" data-kolom="prijs-excl">
                                            <span class="ml-2 text-sm text-gray-700">Prijs excl. BTW</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="checkbox" class="kolom-toggle rounded border-gray-300 text-blue-600" data-kolom="netto" checked>
                                            <span class="ml-2 text-sm text-gray-700">Netto Inkomst</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="checkbox" class="kolom-toggle rounded border-gray-300 text-blue-600" data-kolom="commissie">
                                            <span class="ml-2 text-sm text-gray-700">{{ auth()->user()->organisatie->naam ?? 'Organisatie' }} Commissie</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="checkbox" class="kolom-toggle rounded border-gray-300 text-blue-600" data-kolom="opmerkingen" checked>
                                            <span class="ml-2 text-sm text-gray-700">Opmerkingen</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200" id="prestaties-tabel">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-20 kolom-uitgevoerd">
                                    Uitgevoerd
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider kolom-datum">
                                    Datum
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider kolom-dienst">
                                    Dienst
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider kolom-klant">
                                    Klant
                                </th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider kolom-prijs">
                                    Bruto Prijs (incl. BTW)
                                </th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider kolom-prijs-excl">
                                    Prijs excl. BTW
                                </th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider kolom-netto">
                                    Netto Inkomst
                                </th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider kolom-commissie">
                                    {{ auth()->user()->organisatie->naam ?? 'Organisatie' }} Commissie
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider kolom-opmerkingen">
                                    Opmerkingen
                                </th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-20">
                                    Acties
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($prestaties as $prestatie)
                                <tr class="hover:bg-gray-50 prestatie-row" 
                                    data-searchable="{{ strtolower($prestatie->dienst->naam ?? '') }} {{ strtolower($prestatie->klant_naam ?? '') }} {{ strtolower($prestatie->opmerkingen ?? '') }}"
                                    data-dienst="{{ $prestatie->dienst_id }}"
                                    data-uitgevoerd="{{ $prestatie->is_uitgevoerd ? '1' : '0' }}"
                                    data-datum="{{ $prestatie->datum_prestatie->format('Y-m-d') }}"
                                    data-klant="{{ strtolower($prestatie->klant_naam ?? '') }}"
                                    data-prijs="{{ $prestatie->bruto_prijs }}">
                                    <td class="px-4 py-4 text-center kolom-uitgevoerd">
                                        <input type="checkbox" 
                                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 h-4 w-4 cursor-pointer"
                                               {{ $prestatie->is_uitgevoerd ? 'checked' : '' }}
                                               onchange="toggleUitgevoerd({{ $prestatie->id }}, this.checked)"
                                               title="Dienst uitgevoerd">
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900 kolom-datum">
                                        {{ $prestatie->datum_prestatie->format('d/m/Y') }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap kolom-dienst">
                                        <div class="text-sm font-medium text-gray-900">{{ $prestatie->dienst->naam ?? 'Onbekend' }}</div>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900 kolom-klant">
                                        @if($prestatie->klant)
                                            {{ $prestatie->klant->voornaam }} {{ $prestatie->klant->naam }}
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 text-right kolom-prijs">
                                        €{{ number_format($prestatie->bruto_prijs, 2, ',', '.') }}
                                    </td>
                                    @php
                                        // CORRECTE BEREKENING:
                                        // Stap 1: BTW aftrekken van bruto prijs
                                        $prijsExclBtw = $prestatie->bruto_prijs / 1.21;
                                        
                                        // Stap 2: Bonami commissie berekenen van excl BTW bedrag
                                        $bonamiCommissie = $prijsExclBtw * ($prestatie->commissie_percentage / 100);
                                        
                                        // Stap 3: Netto inkomst voor medewerker = Excl BTW - Bonami commissie
                                        $nettoInkomstMedewerker = $prijsExclBtw - $bonamiCommissie;
                                    @endphp
                                    <td class="px-4 py-4 whitespace-nowrap text-sm font-semibold text-gray-700 text-right kolom-prijs-excl">
                                        €{{ number_format($prijsExclBtw, 2, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm font-semibold text-green-600 text-right kolom-netto">
                                        €{{ number_format($nettoInkomstMedewerker, 2, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm font-semibold text-blue-600 text-right kolom-commissie">
                                        €{{ number_format($bonamiCommissie, 2, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-4 text-sm text-gray-500 kolom-opmerkingen">
                                        {{ $prestatie->opmerkingen ?? '-' }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-center">
                                        <div class="action-buttons flex flex-row flex-nowrap items-center justify-center gap-2">
                                            <button onclick="openEditModal({{ $prestatie->id }})" 
                                                    aria-label="Bewerk" 
                                                    class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-orange-100 text-orange-800" 
                                                    title="Bewerk">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-4 h-4">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.71 7.04a1 1 0 0 0 0-1.41l-2.34-2.34a1 1 0 0 0-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                                                </svg>
                                            </button>
                                            
                                            <button onclick="dupliceerPrestatie({{ $prestatie->id }})" 
                                                    aria-label="Dupliceren" 
                                                    class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-800" 
                                                    title="Dupliceren">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-4 h-4">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                                </svg>
                                            </button>
                                            
                                            <form method="POST" action="{{ route('prestaties.destroy', $prestatie) }}" 
                                                  onsubmit="return confirm('Weet je zeker dat je deze prestatie wilt verwijderen?')"
                                                  class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        aria-label="Verwijderen" 
                                                        class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-rose-100 text-rose-700" 
                                                        style="margin-right:2px;">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-4 h-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m-7 0a1 1 0 001 1h8a1 1 0 001-1m-10 0V6a2 2 0 012-2h4a2 2 0 012 2v1"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="px-6 py-12 text-center text-gray-500">
                                        <div class="flex flex-col items-center">
                                            <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                            <p class="text-lg font-medium">Geen prestaties gevonden</p>
                                            <p class="text-sm">Voeg een nieuwe prestatie toe om te beginnen</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($prestaties->count() > 0)
                            <tfoot class="bg-gray-50 font-semibold">
                                <tr>
                                    <td colspan="4" class="px-4 py-3 text-sm text-gray-900 kolom-uitgevoerd kolom-datum kolom-dienst kolom-klant">Totaal</td>
                                    <td class="px-4 py-3 text-sm text-gray-900 text-right kolom-prijs">
                                        €{{ number_format($prestaties->sum('bruto_prijs'), 2, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700 text-right kolom-prijs-excl">
                                        @php
                                            $totalePrijsExclBtw = $prestaties->sum(function($p) {
                                                return $p->bruto_prijs / 1.21;
                                            });
                                        @endphp
                                        €{{ number_format($totalePrijsExclBtw, 2, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-green-600 text-right kolom-netto">
                                        @php
                                            $totaleNettoInkomst = $prestaties->sum(function($p) {
                                                $prijsExclBtw = $p->bruto_prijs / 1.21;
                                                $commissieBedrag = $prijsExclBtw * ($p->commissie_percentage / 100);
                                                return $prijsExclBtw - $commissieBedrag;
                                            });
                                        @endphp
                                        €{{ number_format($totaleNettoInkomst, 2, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-blue-600 text-right kolom-commissie">
                                        @php
                                            $totaleCommissie = $prestaties->sum(function($p) {
                                                $prijsExclBtw = $p->bruto_prijs / 1.21;
                                                return $prijsExclBtw * ($p->commissie_percentage / 100);
                                            });
                                        @endphp
                                        €{{ number_format($totaleCommissie, 2, ',', '.') }}
                                    </td>
                                    <td colspan="2" class="px-4 py-3 text-sm text-gray-500 kolom-opmerkingen"></td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
            
            {{-- Berekeningensamenvatting onder de tabel --}}
            @if($prestaties->count() > 0)
                <div class="mt-4 p-4 bg-gradient-to-r from-blue-50 to-green-50 rounded-lg border border-blue-200">
                    <h3 class="text-sm font-bold text-gray-900 mb-3 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        Berekeningenoverzicht {{ $huidigKwartaal }} {{ $huidigJaar }}
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                        {{-- Bruto Totaal (incl. BTW) --}}
                        <div class="bg-white rounded-lg p-3 border-l-4 border-gray-500 shadow-sm">
                            <div class="text-xs text-gray-600 mb-1 flex items-center gap-1">
                                <span class="w-2 h-2 bg-gray-500 rounded-full"></span>
                                Bruto Totaal (incl. BTW)
                            </div>
                            <div class="text-xl font-bold text-gray-900">
                                €{{ number_format($prestaties->sum('bruto_prijs'), 2, ',', '.') }}
                            </div>
                            <div class="text-xs text-gray-500 mt-1">{{ $prestaties->count() }} prestaties</div>
                        </div>
                        
                        {{-- Totaal excl. BTW --}}
                        <div class="bg-white rounded-lg p-3 border-l-4 border-blue-500 shadow-sm">
                            <div class="text-xs text-gray-600 mb-1 flex items-center gap-1">
                                <span class="w-2 h-2 bg-blue-500 rounded-full"></span>
                                Totaal excl. BTW (÷ 1.21)
                            </div>
                            <div class="text-xl font-bold text-blue-700">
                                @php
                                    $totalePrijsExclBtw = $prestaties->sum(function($p) {
                                        return $p->bruto_prijs / 1.21;
                                    });
                                @endphp
                                €{{ number_format($totalePrijsExclBtw, 2, ',', '.') }}
                            </div>
                            <div class="text-xs text-gray-500 mt-1">Basis voor commissieberekening</div>
                        </div>
                        
                        {{-- Bonami Commissie --}}
                        <div class="bg-white rounded-lg p-3 border-l-4 border-orange-500 shadow-sm">
                            <div class="text-xs text-gray-600 mb-1 flex items-center gap-1">
                                <span class="w-2 h-2 bg-orange-500 rounded-full"></span>
                                {{ auth()->user()->organisatie->naam ?? 'Organisatie' }} Commissie
                            </div>
                            <div class="text-xl font-bold text-orange-600">
                                @php
                                    $totaleCommissie = $prestaties->sum(function($p) {
                                        $prijsExclBtw = $p->bruto_prijs / 1.21;
                                        return $prijsExclBtw * ($p->commissie_percentage / 100);
                                    });
                                    
                                    // Bereken gemiddeld commissie percentage
                                    $gemiddeldCommissiePercentage = 0;
                                    if ($prestaties->count() > 0) {
                                        $gemiddeldCommissiePercentage = $prestaties->avg('commissie_percentage');
                                    }
                                @endphp
                                €{{ number_format($totaleCommissie, 2, ',', '.') }}
                            </div>
                            <div class="text-xs text-gray-500 mt-1">
                                Gaat naar {{ auth()->user()->organisatie->naam ?? 'organisatie' }} 
                                @if($gemiddeldCommissiePercentage > 0)
                                    ({{ number_format($gemiddeldCommissiePercentage, 1) }}%)
                                @endif
                            </div>
                        </div>
                        
                        {{-- Jouw Netto Inkomst --}}
                        <div class="bg-white rounded-lg p-3 border-l-4 border-green-500 shadow-sm">
                            <div class="text-xs text-gray-600 mb-1 flex items-center gap-1">
                                <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                                Jouw Netto Inkomst
                            </div>
                            <div class="text-xl font-bold text-green-600">
                                @php
                                    $totaleNettoInkomst = $prestaties->sum(function($p) {
                                        $prijsExclBtw = $p->bruto_prijs / 1.21;
                                        $commissieBedrag = $prijsExclBtw * ($p->commissie_percentage / 100);
                                        return $prijsExclBtw - $commissieBedrag;
                                    });
                                @endphp
                                €{{ number_format($totaleNettoInkomst, 2, ',', '.') }}
                            </div>
                            <div class="text-xs text-gray-500 mt-1">Excl. BTW - Commissie</div>
                        </div>
                    </div>
                    
                    {{-- Berekeningsformule uitleg --}}
                    <div class="mt-3 p-3 bg-white rounded-lg border border-gray-200">
                        <div class="text-xs text-gray-700 flex items-center gap-2 flex-wrap">
                            <span class="font-semibold">Berekening:</span>
                            <span class="px-2 py-1 bg-gray-100 rounded">€{{ number_format($prestaties->sum('bruto_prijs'), 2, ',', '.') }}</span>
                            <span>÷ 1.21 =</span>
                            <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded font-semibold">€{{ number_format($totalePrijsExclBtw, 2, ',', '.') }}</span>
                            <span>−</span>
                            <span class="px-2 py-1 bg-orange-100 text-orange-700 rounded font-semibold">€{{ number_format($totaleCommissie, 2, ',', '.') }}</span>
                            <span>=</span>
                            <span class="px-2 py-1 bg-green-100 text-green-700 rounded font-bold">€{{ number_format($totaleNettoInkomst, 2, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Commissie Info Modal --}}
<div id="commissie-info-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            {{-- Header --}}
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">💰 Jouw Commissie Berekening</h2>
                    <p class="text-sm text-gray-600 mt-1">Zo wordt je commissie percentage bepaald</p>
                </div>
                <button onclick="closeCommissieInfoModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            @php
                $user = auth()->user();
                $algemeneFactoren = $user->commissieFactoren()->actief()->first();
            @endphp

            {{-- Algemene Commissie Factoren --}}
            <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                <h3 class="font-bold text-gray-900 mb-3">Algemene Bonussen</h3>
                
                @if($algemeneFactoren)
                    <div class="space-y-2 mb-4">
                        @if($algemeneFactoren->diploma_factor > 0)
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-700">🎓 Diploma Bonus</span>
                                <span class="font-semibold text-green-600">{{ $algemeneFactoren->bonus_richting === 'plus' ? '+' : '-' }}{{ number_format($algemeneFactoren->diploma_factor, 1) }}%</span>
                            </div>
                        @endif
                        
                        @if($algemeneFactoren->ervaring_factor > 0)
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-700">💼 Ervaring Bonus</span>
                                <span class="font-semibold text-green-600">{{ $algemeneFactoren->bonus_richting === 'plus' ? '+' : '-' }}{{ number_format($algemeneFactoren->ervaring_factor, 1) }}%</span>
                            </div>
                        @endif
                        
                        @if($algemeneFactoren->ancienniteit_factor > 0)
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-700">⏳ Anciënniteit Bonus</span>
                                <span class="font-semibold text-green-600">{{ $algemeneFactoren->bonus_richting === 'plus' ? '+' : '-' }}{{ number_format($algemeneFactoren->ancienniteit_factor, 1) }}%</span>
                            </div>
                        @endif
                    </div>

                    <div class="pt-3 border-t border-gray-200">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-semibold text-gray-900">Totale Bonus</span>
                            <span class="text-lg font-bold {{ $algemeneFactoren->bonus_richting === 'plus' ? 'text-green-600' : 'text-red-600' }}">
                                {{ $algemeneFactoren->bonus_richting === 'plus' ? '+' : '-' }}{{ number_format($algemeneFactoren->totale_bonus, 1) }}%
                            </span>
                        </div>
                        <div class="text-xs text-gray-500 mt-1">
                            @if($algemeneFactoren->bonus_richting === 'plus')
                                ✅ Jij krijgt meer commissie
                            @else
                                ⚠️ Organisatie krijgt meer commissie
                            @endif
                        </div>
                    </div>
                @else
                    <p class="text-sm text-gray-600 italic">Geen algemene bonussen ingesteld</p>
                @endif
            </div>

            {{-- Dienst-Specifieke Commissies --}}
            <div class="mb-6">
                <h3 class="font-bold text-gray-900 mb-3">Commissie per Dienst</h3>
                
                <div class="space-y-2">
                    @foreach($beschikbareDiensten as $dienst)
                        @php
                            $customFactor = $user->commissieFactoren()
                                ->where('dienst_id', $dienst->id)
                                ->actief()
                                ->first();
                            
                            // Bereken medewerker commissie percentage
                            $berekendPercentage = $user->getCommissiePercentageVoorDienst($dienst);
                            
                            // Bereken organisatie commissie percentage voor weergave
                            if ($algemeneFactoren) {
                                if ($algemeneFactoren->bonus_richting === 'plus') {
                                    // + Mode: organisatie - bonus = minder voor organisatie, meer voor medewerker
                                    $organisatieCommissie = $dienst->commissie_percentage - $algemeneFactoren->totale_bonus;
                                } else {
                                    // - Mode: organisatie + bonus = meer voor organisatie, minder voor medewerker
                                    $organisatieCommissie = $dienst->commissie_percentage + $algemeneFactoren->totale_bonus;
                                }
                            } else {
                                $organisatieCommissie = $dienst->commissie_percentage;
                            }
                            
                            // Medewerker krijgt het omgekeerde (100% - organisatie commissie)
                            $medewerkerCommissie = 100 - $organisatieCommissie;
                        @endphp
                        
                        <div class="p-3 bg-white border border-gray-200 rounded-lg">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <div class="font-medium text-gray-900">{{ $dienst->naam }}</div>
                                    <div class="text-xs text-gray-600 mt-1">
                                        Basis organisatie: {{ number_format($dienst->commissie_percentage, 1) }}%
                                        @if($algemeneFactoren && $algemeneFactoren->totale_bonus > 0)
                                            {{ $algemeneFactoren->bonus_richting === 'plus' ? '-' : '+' }} {{ number_format($algemeneFactoren->totale_bonus, 1) }}% = {{ number_format($organisatieCommissie, 1) }}%
                                        @endif
                                    </div>
                                </div>
                                <div class="text-right">
                                    @if($customFactor && $customFactor->custom_commissie_percentage !== null)
                                        <div class="text-lg font-bold text-blue-600">
                                            {{ number_format($customFactor->custom_commissie_percentage, 1) }}%
                                        </div>
                                        <div class="text-xs text-blue-600">Custom medewerker</div>
                                    @else
                                        <div class="text-lg font-bold text-green-600">
                                            {{ number_format($medewerkerCommissie, 1) }}%
                                        </div>
                                        <div class="text-xs text-gray-500">Jouw inkomst</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Opmerking --}}
            @if($algemeneFactoren && $algemeneFactoren->opmerking)
                <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="text-xs font-semibold text-blue-900 mb-1">📝 Opmerking van beheerder:</div>
                    <div class="text-sm text-blue-800">{{ $algemeneFactoren->opmerking }}</div>
                </div>
            @endif

            {{-- Sluiten knop --}}
            <div class="mt-6 flex justify-end">
                <button onclick="closeCommissieInfoModal()" 
                        class="px-6 py-2 rounded-lg font-medium text-gray-900 hover:opacity-90 transition"
                        style="background-color: #c8e1eb;">
                    Sluiten
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Edit Prestatie Modal --}}
<div id="edit-prestatie-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            {{-- Header --}}
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">✏️ Prestatie Bewerken</h2>
                    <p class="text-sm text-gray-600 mt-1">Wijzig de gegevens van deze prestatie</p>
                </div>
                <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Edit Form --}}
            <form id="edit-prestatie-form" method="POST">
                @csrf
                @method('PUT')
                
                {{-- Datum range --}}
                <div class="grid grid-cols-2 gap-3 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Startdatum</label>
                        <input type="date" name="datum_prestatie" id="edit-datum" required
                               class="w-full rounded border-gray-300 py-2 px-3">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Einddatum (optioneel)</label>
                        <input type="date" name="einddatum_prestatie" id="edit-einddatum"
                               class="w-full rounded border-gray-300 py-2 px-3">
                    </div>
                </div>
                
                {{-- Dienst & Prijs --}}
                <div class="grid grid-cols-2 gap-3 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Dienst</label>
                        <select name="dienst_id" id="edit-dienst" required
                                class="w-full rounded border-gray-300 py-2 px-3">
                            <option value="">-- Kies dienst --</option>
                            @foreach($beschikbareDiensten as $dienst)
                                <option value="{{ $dienst->id }}" 
                                        data-prijs="{{ $dienst->standaard_prijs }}"
                                        data-commissie="{{ $dienst->commissie_percentage }}">
                                    {{ $dienst->naam }}
                                </option>
                            @endforeach
                            <option value="andere" data-prijs="0" data-commissie="0">Andere</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Prijs (incl. BTW)</label>
                        <input type="number" name="prijs" id="edit-prijs" step="0.01" required
                               class="w-full rounded border-gray-300 py-2 px-3">
                    </div>
                </div>
                
                {{-- Bedrag excl. BTW en netto inkomst preview in Edit modal --}}
                <div class="mt-3 p-3 bg-gray-50 rounded-md border border-gray-200">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm text-gray-600">Bedrag excl. BTW:</span>
                        <span id="edit-bedrag-excl-btw-preview" class="text-lg font-bold text-gray-900">€0,00</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Jouw netto inkomst:</span>
                        <span id="edit-commissie-preview" class="text-lg font-bold text-green-600">€0,00</span>
                    </div>
                </div>
                
                {{-- Klant --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Klant (optioneel)</label>
                    <input type="text" id="edit-klant-zoek" placeholder="Zoek klant..." 
                           class="w-full rounded border-gray-300 py-2 px-3 mb-1">
                    <select name="klant_id" id="edit-klant-select" class="w-full rounded border-gray-300 py-2 px-3" size="4">
                        <option value="">-- Geen klant --</option>
                        @foreach($klanten as $klant)
                            <option value="{{ $klant['id'] }}" data-naam="{{ strtolower($klant['naam']) }}">{{ $klant['naam'] }}</option>
                        @endforeach
                    </select>
                </div>
                
                {{-- Opmerkingen --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Opmerkingen</label>
                    <textarea name="opmerkingen" id="edit-opmerkingen" rows="4" 
                              class="w-full rounded border-gray-300 py-2 px-3"></textarea>
                </div>
                
                {{-- Uitgevoerd checkbox --}}
                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_uitgevoerd" id="edit-uitgevoerd" value="1" 
                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 h-4 w-4">
                        <span class="ml-2 text-sm text-gray-700">Dienst is uitgevoerd</span>
                    </label>
                </div>
                
                {{-- Buttons --}}
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="closeEditModal()" 
                            class="px-6 py-2 rounded-lg font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 transition">
                        Annuleren
                    </button>
                    <button type="submit" 
                            class="px-6 py-2 rounded-lg font-medium text-gray-900 hover:opacity-90 transition"
                            style="background-color: #c8e1eb;">
                        Opslaan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Toon success/error banner met auto-dismiss na 5 seconden
document.addEventListener('DOMContentLoaded', function() {
    const successBanner = document.getElementById('success-banner');
    const errorBanner = document.getElementById('error-banner');
    
    if (successBanner) {
        setTimeout(() => {
            successBanner.style.opacity = '0';
            successBanner.style.transition = 'opacity 0.5s ease';
            setTimeout(() => successBanner.remove(), 500);
        }, 5000);
    }
    
    if (errorBanner) {
        setTimeout(() => {
            errorBanner.style.opacity = '0';
            errorBanner.style.transition = 'opacity 0.5s ease';
            setTimeout(() => errorBanner.remove(), 500);
        }, 5000);
    }
});

// Helper functie om JavaScript success banner te tonen
function toonSuccessBanner(bericht) {
    const existingBanner = document.getElementById('js-success-banner');
    if (existingBanner) existingBanner.remove();
    
    const banner = document.createElement('div');
    banner.id = 'js-success-banner';
    banner.style.cssText = 'background:#d1fae5;color:#065f46;padding:1em;margin-bottom:1em;border-radius:5px;display:flex;justify-content:space-between;align-items:center;';
    banner.innerHTML = `
        <span>${bericht}</span>
        <button onclick="this.parentElement.remove()" style="color:#065f46;font-size:1.5em;line-height:1;border:none;background:none;cursor:pointer;">&times;</button>
    `;
    
    document.querySelector('.container').insertBefore(banner, document.querySelector('.container').firstChild);
    
    setTimeout(() => {
        banner.style.opacity = '0';
        banner.style.transition = 'opacity 0.5s ease';
        setTimeout(() => banner.remove(), 500);
    }, 5000);
}

// Helper functie om JavaScript error banner te tonen
function toonErrorBanner(bericht) {
    const existingBanner = document.getElementById('js-error-banner');
    if (existingBanner) existingBanner.remove();
    
    const banner = document.createElement('div');
    banner.id = 'js-error-banner';
    banner.style.cssText = 'background:#fef2f2;color:#dc2626;padding:1em;margin-bottom:1em;border-radius:5px;display:flex;justify-content:space-between;align-items:center;';
    banner.innerHTML = `
        <span>${bericht}</span>
        <button onclick="this.parentElement.remove()" style="color:#dc2626;font-size:1.5em;line-height:1;border:none;background:none;cursor:pointer;">&times;</button>
    `;
    
    document.querySelector('.container').insertBefore(banner, document.querySelector('.container').firstChild);
    
    setTimeout(() => {
        banner.style.opacity = '0';
        banner.style.transition = 'opacity 0.5s ease';
        setTimeout(() => banner.remove(), 500);
    }, 5000);
}

// Commissie Info Modal functies
function openCommissieInfoModal() {
    document.getElementById('commissie-info-modal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeCommissieInfoModal() {
    document.getElementById('commissie-info-modal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Sluit modal bij klikken buiten de modal
document.getElementById('commissie-info-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeCommissieInfoModal();
    }
});

// Sluit modal met Escape toets
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeCommissieInfoModal();
    }
});

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
    const voorkeuren = JSON.parse(localStorage.getItem('prestatieKolomVoorkeuren') || '{}');
    
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
    localStorage.setItem('prestatieKolomVoorkeuren', JSON.stringify(voorkeuren));
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

// Prestatie zoek en filter functionaliteit
const prestatieZoek = document.getElementById('prestatie-zoek');
const filterDienst = document.getElementById('filter-dienst');
const filterUitgevoerd = document.getElementById('filter-uitgevoerd');
const sorteerSelect = document.getElementById('sorteer');
const prestatieTabel = document.getElementById('prestaties-tabel');

function filterEnSorteerPrestaties() {
    if (!prestatieTabel) return;
    
    const rows = prestatieTabel.querySelectorAll('tbody tr.prestatie-row');
    const zoekterm = prestatieZoek.value.toLowerCase();
    const dienstId = filterDienst.value;
    const uitgevoerdStatus = filterUitgevoerd.value;
    
    // Filter rows
    let zichtbareRows = Array.from(rows).filter(row => {
        // Zoekfilter
        const searchableText = row.getAttribute('data-searchable');
        const matchZoek = searchableText.includes(zoekterm);
        
        // Dienst filter
        const rowDienst = row.getAttribute('data-dienst');
        const matchDienst = !dienstId || rowDienst === dienstId;
        
        // Uitgevoerd filter
        const rowUitgevoerd = row.getAttribute('data-uitgevoerd');
        const matchUitgevoerd = !uitgevoerdStatus || rowUitgevoerd === uitgevoerdStatus;
        
        const isZichtbaar = matchZoek && matchDienst && matchUitgevoerd;
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
    const tbody = prestatieTabel.querySelector('tbody');
    zichtbareRows.forEach(row => tbody.appendChild(row));
}

// Event listeners voor filters
if (prestatieZoek && filterDienst && filterUitgevoerd && sorteerSelect) {
    prestatieZoek.addEventListener('input', filterEnSorteerPrestaties);
    filterDienst.addEventListener('change', filterEnSorteerPrestaties);
    filterUitgevoerd.addEventListener('change', filterEnSorteerPrestaties);
    sorteerSelect.addEventListener('change', filterEnSorteerPrestaties);
}

// Klant zoekfunctie
const klantZoek = document.getElementById('klant-zoek');
const klantSelect = document.getElementById('klant-select');

// Filter klanten bij typen in zoekveld
klantZoek.addEventListener('input', function() {
    const zoekterm = this.value.toLowerCase();
    const options = klantSelect.querySelectorAll('option');
    
    options.forEach(option => {
        if (option.value === '') {
            option.style.display = '';
            return;
        }
        
        const naam = option.getAttribute('data-naam') || '';
        if (naam.includes(zoekterm)) {
            option.style.display = '';
        } else {
            option.style.display = 'none';
        }
    });
    
    // Auto-selecteer eerste zichtbare optie bij typen
    if (zoekterm) {
        const eersteZichtbaar = Array.from(options).find(opt => opt.style.display !== 'none' && opt.value !== '');
        if (eersteZichtbaar) {
            klantSelect.value = eersteZichtbaar.value;
        }
    }
});

// Wanneer klant geselecteerd wordt in dropdown, toon naam in zoekveld
klantSelect.addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    
    if (this.value && this.value !== '') {
        klantZoek.value = selectedOption.textContent.trim();
    } else {
        klantZoek.value = '';
    }
});

// Ook bij click op een optie
klantSelect.addEventListener('click', function() {
    const selectedOption = this.options[this.selectedIndex];
    if (this.value && this.value !== '') {
        klantZoek.value = selectedOption.textContent.trim();
    }
});

// Auto-fill prijs en commissie bij dienst selectie
document.getElementById('dienst-select').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const prijsInput = document.getElementById('prijs-input');
    const prijs = parseFloat(selectedOption.dataset.prijs || 0);
    const dienstId = this.value;
    
    // Als "Andere" geselecteerd is, maak prijs input bewerkbaar
    if (this.value === 'andere') {
        prijsInput.value = '';
        prijsInput.removeAttribute('readonly');
        prijsInput.focus();
        document.getElementById('bedrag-excl-btw-preview').textContent = '€0,00';
        document.getElementById('commissie-preview').textContent = '€0,00';
    } else {
        // Voor gewone diensten: auto-fill en readonly
        prijsInput.setAttribute('readonly', 'readonly');
        prijsInput.value = prijs.toFixed(2);
        
        // Haal de JUISTE commissie voor deze medewerker op via AJAX
        fetch(`/api/commissie-percentage?dienst_id=${dienstId}`)
            .then(response => response.json())
            .then(data => {
                const commissiePercentage = data.commissie_percentage || 0;
                
                // Correcte berekening: Excl BTW - (Excl BTW × Commissie%)
                const bedragExclBtw = prijs / 1.21;
                const commissieBedrag = bedragExclBtw * (commissiePercentage / 100);
                const nettoInkomst = bedragExclBtw - commissieBedrag;
                
                document.getElementById('bedrag-excl-btw-preview').textContent = 
                    '€' + bedragExclBtw.toFixed(2).replace('.', ',');
                document.getElementById('commissie-preview').textContent = 
                    '€' + nettoInkomst.toFixed(2).replace('.', ',');
            })
            .catch(error => {
                console.error('Error fetching commissie:', error);
                // Fallback naar 0% commissie
                const bedragExclBtw = prijs / 1.21;
                document.getElementById('bedrag-excl-btw-preview').textContent = 
                    '€' + bedragExclBtw.toFixed(2).replace('.', ',');
                document.getElementById('commissie-preview').textContent = 
                    '€' + bedragExclBtw.toFixed(2).replace('.', ',');
            });
    }
});

// Update commissie preview wanneer prijs handmatig wordt gewijzigd (bij "Andere")
document.getElementById('prijs-input').addEventListener('input', function() {
    const dienstSelect = document.getElementById('dienst-select');
    const dienstId = dienstSelect.value;
    
    if (dienstSelect.value === 'andere') {
        const prijs = parseFloat(this.value || 0);
        
        // Haal commissie percentage op via AJAX
        fetch(`/api/commissie-percentage?dienst_id=${dienstId}`)
            .then(response => response.json())
            .then(data => {
                const commissiePercentage = data.commissie_percentage || 0;
                
                const bedragExclBtw = prijs / 1.21;
                const commissieBedrag = bedragExclBtw * (commissiePercentage / 100);
                const nettoInkomst = bedragExclBtw - commissieBedrag;
                
                document.getElementById('bedrag-excl-btw-preview').textContent = 
                    '€' + bedragExclBtw.toFixed(2).replace('.', ',');
                document.getElementById('commissie-preview').textContent = 
                    '€' + nettoInkomst.toFixed(2).replace('.', ',');
            });
    }
});

// Kwartaal filter - redirect bij wijziging
const kwartaalFilter = document.getElementById('kwartaal-filter');
if (kwartaalFilter) {
    kwartaalFilter.addEventListener('change', function() {
        const jaar = {{ $huidigJaar }};
        const kwartaal = this.value;
        window.location.href = `/prestaties?jaar=${jaar}&kwartaal=${kwartaal}`;
    });
}

// Toggle uitgevoerd status
function toggleUitgevoerd(prestatieId, isChecked) {
    fetch(`/prestaties/${prestatieId}/toggle-uitgevoerd`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ is_uitgevoerd: isChecked })
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
        if (data.success) {
            toonSuccessBanner(isChecked ? '✅ Prestatie gemarkeerd als uitgevoerd' : '⏳ Prestatie gemarkeerd als niet uitgevoerd');
        } else {
            toonErrorBanner('❌ Er ging iets mis bij het opslaan');
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error details:', error);
        toonErrorBanner('❌ Er ging iets mis bij het opslaan: ' + error.message);
        location.reload();
    });
}

// Edit Prestatie Modal functies
function openEditModal(prestatieId) {
    // Reset form en modal staat
    const form = document.getElementById('edit-prestatie-form');
    form.reset();
    document.getElementById('edit-commissie-preview').textContent = '€0,00';
    form.action = `/prestaties/${prestatieId}`;
    
    // Haal prestatie gegevens op
    fetch(`/prestaties/${prestatieId}/edit`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const prestatie = data.prestatie;
                
                // Vul form met gegevens
                document.getElementById('edit-datum').value = prestatie.datum_prestatie.split('T')[0];
                document.getElementById('edit-einddatum').value = prestatie.einddatum_prestatie ? prestatie.einddatum_prestatie.split('T')[0] : '';
                document.getElementById('edit-prijs').value = prestatie.bruto_prijs;
                document.getElementById('edit-opmerkingen').value = prestatie.opmerkingen || '';
                document.getElementById('edit-uitgevoerd').checked = prestatie.is_uitgevoerd;
                
                // Dienst selecteren
                const dienstSelect = document.getElementById('edit-dienst');
                dienstSelect.value = prestatie.dienst_id;
                
                // Trigger change event om prijs en commissie te updaten
                const event = new Event('change');
                dienstSelect.dispatchEvent(event);
                
                // Klant selecteren
                const klantSelect = document.getElementById('edit-klant-select');
                klantSelect.value = prestatie.klant_id || '';
                
                // Trigger change event voor klant zoekfunctie
                const klantZoek = document.getElementById('edit-klant-zoek');
                if (prestatie.klant) {
                    klantZoek.value = `${prestatie.klant.voornaam} ${prestatie.klant.naam}`;
                } else {
                    klantZoek.value = '';
                }
                
                // Toon modal
                document.getElementById('edit-prestatie-modal').classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            } else {
                alert('Kon prestatie gegevens niet ophalen');
            }
        })
        .catch(error => {
            console.error('Error fetching prestatie:', error);
            alert('Er ging iets mis bij het ophalen van de prestatiegegevens');
        });
}

function closeEditModal() {
    document.getElementById('edit-prestatie-modal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Dupliceer prestatie functie
function dupliceerPrestatie(prestatieId) {
    if (!confirm('Weet je zeker dat je deze prestatie wilt dupliceren?')) {
        return;
    }
    
    fetch(`/prestaties/${prestatieId}/duplicate`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            toonSuccessBanner('📋 Prestatie succesvol gedupliceerd!');
            setTimeout(() => location.reload(), 1000);
        } else {
            toonErrorBanner('❌ Er ging iets mis bij het dupliceren');
        }
    })
    .catch(error => {
        console.error('Error duplicating prestatie:', error);
        toonErrorBanner('❌ Er ging iets mis bij het dupliceren: ' + error.message);
    });
}

// Sluit modal bij klikken buiten de modal
document.getElementById('edit-prestatie-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeEditModal();
    }
});

// Sluit modal met Escape toets
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeEditModal();
    }
});

// Klant zoekfunctie voor Edit modal
const editKlantZoek = document.getElementById('edit-klant-zoek');
const editKlantSelect = document.getElementById('edit-klant-select');

// Filter klanten bij typen in zoekveld
editKlantZoek.addEventListener('input', function() {
    const zoekterm = this.value.toLowerCase();
    const options = editKlantSelect.querySelectorAll('option');
    
    options.forEach(option => {
        if (option.value === '') {
            option.style.display = '';
            return;
        }
        
        const naam = option.getAttribute('data-naam') || '';
        if (naam.includes(zoekterm)) {
            option.style.display = '';
        } else {
            option.style.display = 'none';
        }
    });
    
    // Auto-selecteer eerste zichtbare optie bij typen
    if (zoekterm) {
        const eersteZichtbaar = Array.from(options).find(opt => opt.style.display !== 'none' && opt.value !== '');
        if (eersteZichtbaar) {
            editKlantSelect.value = eersteZichtbaar.value;
        }
    }
});

// Wanneer klant geselecteerd wordt in dropdown, toon naam in zoekveld
editKlantSelect.addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    
    if (this.value && this.value !== '') {
        editKlantZoek.value = selectedOption.textContent.trim();
    } else {
        editKlantZoek.value = '';
    }
});

// Ook bij click op een optie
editKlantSelect.addEventListener('click', function() {
    const selectedOption = this.options[this.selectedIndex];
    if (this.value && this.value !== '') {
        editKlantZoek.value = selectedOption.textContent.trim();
    }
});

// Auto-fill prijs en commissie bij dienst selectie in Edit modal
document.getElementById('edit-dienst').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const prijsInput = document.getElementById('edit-prijs');
    const prijs = parseFloat(selectedOption.dataset.prijs || 0);
    const dienstId = this.value;
    
    // Als "Andere" geselecteerd is, maak prijs input bewerkbaar
    if (this.value === 'andere') {
        prijsInput.removeAttribute('readonly');
        prijsInput.focus();
        
        const huidigePrijs = parseFloat(prijsInput.value || 0);
        
        // Haal commissie percentage op
        fetch(`/api/commissie-percentage?dienst_id=${dienstId}`)
            .then(response => response.json())
            .then(data => {
                const commissiePercentage = data.commissie_percentage || 0;
                const bedragExclBtw = huidigePrijs / 1.21;
                const commissieBedrag = bedragExclBtw * (commissiePercentage / 100);
                const nettoInkomst = bedragExclBtw - commissieBedrag;
                
                document.getElementById('edit-bedrag-excl-btw-preview').textContent = 
                    '€' + bedragExclBtw.toFixed(2).replace('.', ',');
                document.getElementById('edit-commissie-preview').textContent = 
                    '€' + nettoInkomst.toFixed(2).replace('.', ',');
            });
    } else {
        // Voor gewone diensten: auto-fill en readonly
        prijsInput.setAttribute('readonly', 'readonly');
        prijsInput.value = prijs.toFixed(2);
        
        // Haal de JUISTE commissie voor deze medewerker op
        fetch(`/api/commissie-percentage?dienst_id=${dienstId}`)
            .then(response => response.json())
            .then(data => {
                const commissiePercentage = data.commissie_percentage || 0;
                const bedragExclBtw = prijs / 1.21;
                const commissieBedrag = bedragExclBtw * (commissiePercentage / 100);
                const nettoInkomst = bedragExclBtw - commissieBedrag;
                
                document.getElementById('edit-bedrag-excl-btw-preview').textContent = 
                    '€' + bedragExclBtw.toFixed(2).replace('.', ',');
                document.getElementById('edit-commissie-preview').textContent = 
                    '€' + nettoInkomst.toFixed(2).replace('.', ',');
            });
    }
});

// Update commissie preview wanneer prijs handmatig wordt gewijzigd (bij "Andere") in Edit modal
document.getElementById('edit-prijs').addEventListener('input', function() {
    const dienstSelect = document.getElementById('edit-dienst');
    const dienstId = dienstSelect.value;
    
    if (dienstSelect.value === 'andere' || !dienstSelect.hasAttribute('readonly')) {
        const prijs = parseFloat(this.value || 0);
        
        // Haal commissie percentage op
        fetch(`/api/commissie-percentage?dienst_id=${dienstId}`)
            .then(response => response.json())
            .then(data => {
                const commissiePercentage = data.commissie_percentage || 0;
                const bedragExclBtw = prijs / 1.21;
                const commissieBedrag = bedragExclBtw * (commissiePercentage / 100);
                const nettoInkomst = bedragExclBtw - commissieBedrag;
                
                document.getElementById('edit-bedrag-excl-btw-preview').textContent = 
                    '€' + bedragExclBtw.toFixed(2).replace('.', ',');
                document.getElementById('edit-commissie-preview').textContent = 
                    '€' + nettoInkomst.toFixed(2).replace('.', ',');
            });
    }
});
</script>
@endsection
