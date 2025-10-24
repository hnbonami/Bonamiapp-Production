@extends('layouts.app')

@section('content')
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
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-xs text-gray-600 mb-1">Aantal Prestaties</div>
            <div class="text-2xl font-bold text-gray-900">{{ $prestaties->count() }}</div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-xs text-gray-600 mb-1">Totale Commissie</div>
            <div class="text-2xl font-bold text-green-600">
                €{{ number_format($totaleCommissie, 2, ',', '.') }}
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-xs text-gray-600 mb-1">Gemiddelde per Prestatie</div>
            <div class="text-2xl font-bold text-blue-600">
                €{{ $prestaties->count() > 0 ? number_format($totaleCommissie / $prestaties->count(), 2, ',', '.') : '0,00' }}
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
                    
                    {{-- Datum range --}}
                    <div class="grid grid-cols-2 gap-3 mb-4">
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
                    </div>
                    
                    {{-- Dienst & Klant naast elkaar --}}
                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Dienst</label>
                            <select name="dienst_id" id="dienst-select" required
                                    class="w-full rounded border-gray-300 py-2 px-3">
                                <option value="">-- Kies dienst --</option>
                                @foreach($beschikbareDiensten as $dienst)
                                    <option value="{{ $dienst->id }}" 
                                            data-prijs="{{ $dienst->pivot->custom_prijs ?? $dienst->standaard_prijs }}"
                                            data-commissie="{{ $dienst->pivot->commissie_percentage ?? $dienst->commissie_percentage }}">
                                        {{ $dienst->naam }}
                                    </option>
                                @endforeach
                            </select>
                            
                            {{-- Prijs & Commissie onder dienst --}}
                            <div class="mt-3 space-y-2">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Prijs</label>
                                    <input type="number" name="prijs" id="prijs-input" step="0.01" required
                                           class="w-full rounded border-gray-300 py-2 px-3" readonly>
                                </div>
                                
                                <div class="p-3 bg-blue-50 rounded">
                                    <div class="text-sm text-gray-600">Commissie</div>
                                    <div class="text-xl font-bold text-blue-600" id="commissie-preview">€0,00</div>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Klant (optioneel)</label>
                            <input type="text" id="klant-zoek" placeholder="Zoek klant..." 
                                   class="w-full rounded border-gray-300 py-2 px-3 mb-1">
                            <select name="klant_id" id="klant-select" class="w-full rounded border-gray-300 py-2 px-3" size="4">
                                <option value="">-- Geen klant --</option>
                                @foreach($klanten as $klant)
                                    <option value="{{ $klant['id'] }}" data-naam="{{ strtolower($klant['naam']) }}">{{ $klant['naam'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    {{-- Opmerkingen --}}
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Opmerkingen</label>
                        <textarea name="opmerkingen" rows="3" 
                                  class="w-full rounded border-gray-300 py-2 px-3"></textarea>
                    </div>
                    
                    <button type="submit" class="w-full py-3 px-4 rounded font-medium hover:opacity-90 transition text-gray-900" style="background-color: #c8e1eb;">
                        Prestatie Toevoegen
                    </button>
                </form>
            </div>
        </div>

        {{-- Prestaties lijst - compact --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow">
                <div class="p-4 border-b">
                    <h2 class="text-lg font-bold">Prestaties {{ $huidigKwartaal }} {{ $huidigJaar }}</h2>
                </div>
                
                <div class="p-4">
                    @if($prestaties->isEmpty())
                        <div class="text-center py-8">
                            <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="text-sm text-gray-500">Nog geen prestaties toegevoegd voor dit kwartaal</p>
                        </div>
                    @else
                        <div class="space-y-2">
                            @foreach($prestaties as $prestatie)
                                <div class="border rounded p-3 hover:bg-gray-50 transition">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <div class="font-medium text-sm text-gray-900">{{ $prestatie->dienst->naam }}</div>
                                            <div class="text-xs text-gray-600 mt-1">
                                                {{ $prestatie->datum_prestatie->format('d/m/Y') }}
                                                @if($prestatie->klant)
                                                    • {{ $prestatie->klant->voornaam }} {{ $prestatie->klant->naam }}
                                                @endif
                                            </div>
                                            @if($prestatie->opmerkingen)
                                                <div class="text-xs text-gray-500 mt-1">{{ $prestatie->opmerkingen }}</div>
                                            @endif
                                        </div>
                                        
                                        <div class="text-right ml-3">
                                            <div class="text-base font-bold text-gray-900">
                                                €{{ number_format($prestatie->bruto_prijs, 2, ',', '.') }}
                                            </div>
                                            <div class="text-xs text-green-600">
                                                +€{{ number_format($prestatie->commissie_bedrag, 2, ',', '.') }}
                                            </div>
                                        </div>
                                        
                                        <form method="POST" action="{{ route('prestaties.destroy', $prestatie) }}" 
                                              onsubmit="return confirm('Weet je zeker dat je deze prestatie wilt verwijderen?')"
                                              class="ml-2">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
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
    const prijs = parseFloat(selectedOption.dataset.prijs || 0);
    const commissiePercentage = parseFloat(selectedOption.dataset.commissie || 0);
    const commissieBedrag = (prijs * commissiePercentage) / 100;
    
    document.getElementById('prijs-input').value = prijs.toFixed(2);
    document.getElementById('commissie-preview').textContent = 
        '€' + commissieBedrag.toFixed(2).replace('.', ',');
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
</script>
@endsection
