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
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-xs text-gray-600 mb-1">Aantal Prestaties</div>
            <div class="text-2xl font-bold text-gray-900">{{ $prestaties->count() }}</div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-xs text-gray-600 mb-1">Commissie %</div>
            <div class="text-2xl font-bold text-green-600">
                {{ $prestaties->count() > 0 ? number_format($prestaties->avg('commissie_percentage'), 1, ',', '.') : '0' }}%
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-xs text-gray-600 mb-1">Totale Commissie</div>
            <div class="text-2xl font-bold text-blue-600">
                €{{ number_format($totaleCommissie, 2, ',', '.') }}
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-xs text-gray-600 mb-1">Gemiddelde per Prestatie</div>
            <div class="text-2xl font-bold text-gray-900">
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
                    
                    {{-- Dienst & Prijs naast elkaar --}}
                    <div class="grid grid-cols-2 gap-3 mb-4">
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
                    
                    {{-- Commissie preview --}}
                    <div class="mb-4 p-3 bg-blue-50 rounded">
                        <div class="text-sm text-gray-600">Commissie</div>
                        <div class="text-xl font-bold text-blue-600" id="commissie-preview">€0,00</div>
                    </div>
                    
                    {{-- Klant en Opmerkingen naast elkaar --}}
                    <div class="grid grid-cols-2 gap-3 mb-4">
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
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Opmerkingen</label>
                            <textarea name="opmerkingen" rows="6" 
                                      class="w-full rounded border-gray-300 py-2 px-3"></textarea>
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
                    
                    {{-- Zoekveld --}}
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <input type="text" id="prestatie-zoek" placeholder="Zoek op dienst, klant of opmerking..." 
                               class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200" id="prestaties-tabel">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-20">
                                    Uitgevoerd
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Datum
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Dienst
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Klant
                                </th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Prijs
                                </th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Commissie
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Opmerkingen
                                </th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-20">
                                    Acties
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($prestaties as $prestatie)
                                <tr class="hover:bg-gray-50" data-searchable="{{ strtolower($prestatie->dienst->naam ?? '') }} {{ strtolower($prestatie->klant_naam ?? '') }} {{ strtolower($prestatie->opmerkingen ?? '') }}">
                                    <td class="px-4 py-4 text-center">
                                        <input type="checkbox" 
                                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 h-4 w-4 cursor-pointer"
                                               {{ $prestatie->is_uitgevoerd ? 'checked' : '' }}
                                               onchange="toggleUitgevoerd({{ $prestatie->id }}, this.checked)"
                                               title="Dienst uitgevoerd">
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $prestatie->datum_prestatie->format('d/m/Y') }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $prestatie->dienst->naam ?? 'Onbekend' }}</div>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                        @if($prestatie->klant)
                                            {{ $prestatie->klant->voornaam }} {{ $prestatie->klant->naam }}
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 text-right">
                                        €{{ number_format($prestatie->bruto_prijs, 2, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm font-semibold text-green-600 text-right">
                                        €{{ number_format($prestatie->commissie_bedrag, 2, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-4 text-sm text-gray-500">
                                        {{ $prestatie->opmerkingen ?? '-' }}
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        <form method="POST" action="{{ route('prestaties.destroy', $prestatie) }}" 
                                              onsubmit="return confirm('Weet je zeker dat je deze prestatie wilt verwijderen?')"
                                              class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700" title="Verwijderen">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-12 text-center text-gray-500">
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
                                    <td colspan="4" class="px-4 py-3 text-sm text-gray-900">Totaal</td>
                                    <td class="px-4 py-3 text-sm text-gray-900 text-right">
                                        €{{ number_format($prestaties->sum('bruto_prijs'), 2, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-green-600 text-right">
                                        €{{ number_format($totaleCommissie, 2, ',', '.') }}
                                    </td>
                                    <td colspan="2" class="px-4 py-3 text-sm text-gray-500"></td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Prestatie zoekfunctie in tabel
const prestatieZoek = document.getElementById('prestatie-zoek');
const prestatieTabel = document.getElementById('prestaties-tabel');

if (prestatieZoek && prestatieTabel) {
    const rows = prestatieTabel.querySelectorAll('tbody tr[data-searchable]');
    
    prestatieZoek.addEventListener('input', function() {
        const zoekterm = this.value.toLowerCase();
        
        rows.forEach(row => {
            const searchableText = row.getAttribute('data-searchable');
            if (searchableText.includes(zoekterm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
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
    const commissiePercentage = parseFloat(selectedOption.dataset.commissie || 0);
    
    // Als "Andere" geselecteerd is, maak prijs input bewerkbaar
    if (this.value === 'andere') {
        prijsInput.value = '';
        prijsInput.removeAttribute('readonly');
        prijsInput.focus();
        document.getElementById('commissie-preview').textContent = '€0,00';
    } else {
        // Voor gewone diensten: auto-fill en readonly
        prijsInput.setAttribute('readonly', 'readonly');
        prijsInput.value = prijs.toFixed(2);
        
        const commissieBedrag = (prijs * commissiePercentage) / 100;
        document.getElementById('commissie-preview').textContent = 
            '€' + commissieBedrag.toFixed(2).replace('.', ',');
    }
});

// Update commissie preview wanneer prijs handmatig wordt gewijzigd (bij "Andere")
document.getElementById('prijs-input').addEventListener('input', function() {
    const dienstSelect = document.getElementById('dienst-select');
    const selectedOption = dienstSelect.options[dienstSelect.selectedIndex];
    
    if (dienstSelect.value === 'andere') {
        const prijs = parseFloat(this.value || 0);
        const commissiePercentage = parseFloat(selectedOption.dataset.commissie || 0);
        const commissieBedrag = (prijs * commissiePercentage) / 100;
        
        document.getElementById('commissie-preview').textContent = 
            '€' + commissieBedrag.toFixed(2).replace('.', ',');
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
    console.log('Toggle uitgevoerd:', prestatieId, isChecked);
    
    fetch(`/prestaties/${prestatieId}/toggle-uitgevoerd`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ is_uitgevoerd: isChecked })
    })
    .then(response => {
        console.log('Response status:', response.status);
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
</script>
@endsection
