@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    {{-- Header --}}
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Diensten Beheer</h1>
            <p class="text-sm text-gray-600 mt-1">Beheer diensten en commissiepercentages</p>
        </div>
        
        <button onclick="openNieuweDienstModal()" style="background:#c8e1eb;color:#111;padding:0.5em 1em;border-radius:7px;font-weight:600;font-size:0.95em;box-shadow:0 1px 3px #e0e7ff;display:flex;align-items:center;gap:0.5em;border:none;cursor:pointer;">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Nieuwe Dienst
        </button>
    </div>

    {{-- Diensten Tabel --}}
    <div class="bg-white rounded-lg shadow">
        <div class="overflow-x-auto">
            <table class="w-full">
                                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dienst</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prijs</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">BTW</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Commissie %</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Commissie Bedrag</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acties</th>
                        </tr>
                    </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($diensten as $dienst)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $dienst->naam }}</div>
                                @if($dienst->omschrijving)
                                    <div class="text-sm text-gray-500">{{ $dienst->omschrijving }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-semibold text-gray-900">€{{ number_format($dienst->standaard_prijs, 2, ',', '.') }}</div>
                                <div class="text-xs text-gray-500">Excl: €{{ number_format($dienst->prijs_excl_btw ?? $dienst->standaard_prijs / 1.21, 2, ',', '.') }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $dienst->btw_percentage ?? 21 }}%
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ number_format($dienst->commissie_percentage, 2) }}%
                            </td>
                            <td class="px-6 py-4 text-sm font-medium text-green-600">
                                €{{ number_format(($dienst->standaard_prijs * $dienst->commissie_percentage) / 100, 2, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($dienst->is_actief)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Actief</span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Inactief</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="action-buttons flex flex-row flex-nowrap items-center justify-end gap-2">
                                    <button onclick="editDienst(this)" 
                                            data-id="{{ $dienst->id }}"
                                            data-naam="{{ $dienst->naam }}"
                                            data-omschrijving="{{ $dienst->beschrijving ?? '' }}"
                                            data-prijs="{{ $dienst->standaard_prijs }}"
                                            data-commissie="{{ $dienst->commissie_percentage }}"
                                            data-actief="{{ $dienst->is_actief ? '1' : '0' }}"
                                            aria-label="Bewerk" 
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-orange-100 text-orange-800" 
                                            title="Bewerk dienst">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-4 h-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.71 7.04a1 1 0 0 0 0-1.41l-2.34-2.34a1 1 0 0 0-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                                        </svg>
                                    </button>
                                    <form action="{{ route('admin.prestaties.diensten.destroy', $dienst) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                onclick="return confirm('Weet je zeker dat je deze dienst wilt verwijderen?')"
                                                aria-label="Verwijderen" 
                                                class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-rose-100 text-rose-700" 
                                                title="Verwijder dienst"
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
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    <p class="text-gray-500">Nog geen diensten aangemaakt</p>
                                    <button onclick="openNieuweDienstModal()" class="mt-4 text-blue-600 hover:text-blue-800">
                                        Maak je eerste dienst aan →
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal voor Bewerk Dienst --}}
<div id="editDienstModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto border w-full max-w-md shadow-lg rounded-md bg-white" style="padding:2rem;">
        <div class="flex justify-between items-center pb-3 border-b">
            <h3 class="text-lg font-semibold text-gray-900">Dienst Bewerken</h3>
            <button onclick="closeEditDienstModal()" class="text-gray-400 hover:text-gray-500">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        <form id="editDienstForm" method="POST" class="mt-4">
            @csrf
            @method('PUT')
            
            {{-- Naam --}}
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Naam <span class="text-red-500">*</span>
                </label>
                <input type="text" name="naam" id="edit-naam" required 
                       placeholder="Bikefit, Inspanningstest, etc."
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            {{-- Omschrijving --}}
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Omschrijving</label>
                <textarea name="omschrijving" id="edit-omschrijving" rows="2"
                          placeholder="Korte beschrijving van de dienst..."
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
            </div>
            
            {{-- Prijs veld --}}
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Prijs (incl. BTW)</label>
                <input type="number" name="prijs" id="edit-prijs" step="0.01" required 
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
            </div>

            {{-- BTW Info --}}
            <div class="mb-4 p-3 bg-gray-50 rounded-lg">
                <div class="text-xs text-gray-600 mb-1">BTW Berekening (automatisch)</div>
                <div class="grid grid-cols-2 gap-2 text-sm">
                    <div>
                        <span class="text-gray-500">BTW %:</span>
                        <span class="font-medium">21%</span>
                    </div>
                    <div>
                        <span class="text-gray-500">Excl. BTW:</span>
                        <span class="font-medium" id="prijs-excl-btw">€0,00</span>
                    </div>
                </div>
            </div>
            
            {{-- Commissie percentage --}}
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Commissie Percentage</label>
                <input type="number" name="commissie_percentage" id="edit-commissie_percentage" step="0.01" required 
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
            </div>
            
            {{-- Actief checkbox --}}
            <div class="mb-4">
                <label class="inline-flex items-center">
                    <input type="checkbox" name="actief" id="edit-actief" value="1"
                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <span class="ml-2 text-sm text-gray-700">Actief</span>
                </label>
            </div>
            
            {{-- Actie knoppen --}}
            <div class="flex justify-end gap-3 pt-4 border-t mt-4">
                <button type="button" onclick="closeEditDienstModal()" 
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition text-sm font-medium">
                    Annuleren
                </button>
                <button type="submit" 
                        style="background:#c8e1eb;color:#111;padding:0.5em 1em;border-radius:0.5em;font-weight:600;font-size:0.875rem;border:none;cursor:pointer;">
                    Opslaan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal voor Nieuwe/Bewerk Dienst --}}
<div id="dienstModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto border w-full max-w-md shadow-lg rounded-md bg-white" style="padding:2rem;">
        <div class="flex justify-between items-center pb-3 border-b">
            <h3 class="text-lg font-semibold text-gray-900" id="modalTitle">Nieuwe Dienst</h3>
            <button onclick="closeDienstModal()" class="text-gray-400 hover:text-gray-500">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        <form id="dienstForm" action="{{ route('admin.prestaties.diensten.store') }}" method="POST" class="mt-4">
            @csrf
            <input type="hidden" id="dienstId" name="dienst_id">
            <input type="hidden" id="formMethod" name="_method" value="POST">
            
            {{-- Naam --}}
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Naam <span class="text-red-500">*</span>
                </label>
                <input type="text" name="naam" id="naam" required 
                       placeholder="Bikefit, Inspanningstest, etc."
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            {{-- Omschrijving --}}
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Omschrijving</label>
                <textarea name="omschrijving" id="omschrijving" rows="2"
                          placeholder="Korte beschrijving van de dienst..."
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
            </div>
            
            {{-- Prijs veld --}}
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Prijs (incl. BTW)</label>
                <input type="number" name="prijs" id="prijs" step="0.01" required 
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
            </div>

            {{-- BTW Info --}}
            <div class="mb-4 p-3 bg-gray-50 rounded-lg">
                <div class="text-xs text-gray-600 mb-1">BTW Berekening (automatisch)</div>
                <div class="grid grid-cols-2 gap-2 text-sm">
                    <div>
                        <span class="text-gray-500">BTW %:</span>
                        <span class="font-medium">21%</span>
                    </div>
                    <div>
                        <span class="text-gray-500">Excl. BTW:</span>
                        <span class="font-medium" id="prijs-excl-btw">€0,00</span>
                    </div>
                </div>
            </div>
            
            {{-- Commissie percentage --}}
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Commissie Percentage</label>
                <input type="number" name="commissie_percentage" id="commissie_percentage" step="0.01" required 
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
            </div>
            
            {{-- Actief checkbox --}}
            <div class="mb-4">
                <label class="inline-flex items-center">
                    <input type="checkbox" name="actief" id="actief" value="1" checked
                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <span class="ml-2 text-sm text-gray-700">Actief</span>
                </label>
            </div>
            
            {{-- Actie knoppen --}}
            <div class="flex justify-end gap-3 pt-4 border-t mt-4">
                <button type="button" onclick="closeDienstModal()" 
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition text-sm font-medium">
                    Annuleren
                </button>
                <button type="submit" 
                        style="background:#c8e1eb;color:#111;padding:0.5em 1em;border-radius:0.5em;font-weight:600;font-size:0.875rem;border:none;cursor:pointer;">
                    Opslaan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Nieuwe dienst modal
function openNieuweDienstModal() {
    document.getElementById('dienstModal').classList.remove('hidden');
    document.getElementById('modalTitle').textContent = 'Nieuwe Dienst';
    document.getElementById('dienstForm').action = '{{ route("admin.prestaties.diensten.store") }}';
    document.getElementById('formMethod').value = 'POST';
    document.getElementById('dienstForm').reset();
}

function closeDienstModal() {
    document.getElementById('dienstModal').classList.add('hidden');
}

// Edit dienst modal
function editDienst(button) {
    const modal = document.getElementById('editDienstModal');
    const form = document.getElementById('editDienstForm');
    
    // Haal data van button
    const id = button.dataset.id;
    const naam = button.dataset.naam;
    const omschrijving = button.dataset.omschrijving;
    const prijs = button.dataset.prijs;
    const commissie = button.dataset.commissie;
    const actief = button.dataset.actief === '1';
    
    // Stel form action in
    form.action = `/admin/prestaties/diensten/${id}`;
    
    // Vul velden
    document.getElementById('edit-naam').value = naam;
    document.getElementById('edit-omschrijving').value = omschrijving;
    document.getElementById('edit-prijs').value = prijs;
    document.getElementById('edit-commissie_percentage').value = commissie;
    document.getElementById('edit-actief').checked = actief;
    
    // Toon modal
    modal.classList.remove('hidden');
}

function closeEditDienstModal() {
    document.getElementById('editDienstModal').classList.add('hidden');
}

// Sluit modals bij klik buiten
document.getElementById('dienstModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeDienstModal();
});

document.getElementById('editDienstModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeEditDienstModal();
});

// Sluit modals met ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeDienstModal();
        closeEditDienstModal();
    }
});

// BTW berekening in real-time
document.addEventListener('DOMContentLoaded', function() {
    const prijsInput = document.querySelector('input[name="prijs"]');
    const prijsExclBtwElement = document.getElementById('prijs-excl-btw');
    
    if (prijsInput && prijsExclBtwElement) {
        prijsInput.addEventListener('input', function() {
            const prijsInclBtw = parseFloat(this.value) || 0;
            const prijsExclBtw = prijsInclBtw / 1.21;
            prijsExclBtwElement.textContent = '€' + prijsExclBtw.toFixed(2).replace('.', ',');
        });
    }
});
</script>
@endsection
