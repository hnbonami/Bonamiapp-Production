@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    {{-- Header --}}
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Diensten Beheer</h1>
            <p class="text-sm text-gray-600 mt-1">Beheer diensten en commissiepercentages</p>
        </div>
        <button onclick="window.location.href='{{ route('admin.prestaties.diensten.create') }}'" class="px-4 py-2 text-sm font-medium rounded-lg text-gray-900 hover:opacity-90 transition" style="background-color: #c8e1eb;">
            + Nieuwe Dienst
        </button>
    </div>

    {{-- Success Message --}}
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-500 text-green-800 rounded">
            ✅ {{ session('success') }}
        </div>
    @endif

    {{-- Diensten Tabel --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Dienst
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Prijs
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                BTW
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Commissie %
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Commissie Bedrag
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Acties
                            </th>
                        </tr>
                    </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($diensten as $dienst)
                        <tr class="hover:bg-gray-50">
                            <!-- DIENST NAAM + OMSCHRIJVING -->
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $dienst->naam }}
                                </div>
                                @if($dienst->omschrijving)
                                <div class="text-sm text-gray-500">
                                    {{ $dienst->omschrijving }}
                                </div>
                                @endif
                            </td>
                            
                            <!-- PRIJS -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-gray-900">
                                    €{{ number_format($dienst->prijs_incl_btw, 2, ',', '.') }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    Excl: €{{ number_format($dienst->prijs_excl_btw, 2, ',', '.') }}
                                </div>
                            </td>
                            
                            <!-- BTW -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ number_format($dienst->btw_percentage, 2) }}%
                            </td>
                            
                            <!-- COMMISSIE % -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ number_format($dienst->commissie_percentage, 2) }}%
                            </td>
                            
                            <!-- COMMISSIE BEDRAG -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 font-semibold">
                                €{{ number_format($dienst->berekenCommissieBedrag(), 2, ',', '.') }}
                            </td>
                            
                            <!-- STATUS -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($dienst->is_actief)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Actief
                                    </span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        Inactief
                                    </span>
                                @endif
                            </td>
                            
                            <!-- ACTIES -->
                            <td class="px-6 py-4 whitespace-nowrap text-right align-top">
                                <div class="action-buttons flex flex-row flex-nowrap items-center justify-end gap-2">
                                    <a href="{{ route('admin.prestaties.diensten.edit', $dienst) }}" 
                                       aria-label="Bewerk" 
                                       class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-orange-100 text-orange-800" 
                                       title="Bewerk">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-4 h-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.71 7.04a1 1 0 0 0 0-1.41l-2.34-2.34a1 1 0 0 0-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                                        </svg>
                                    </a>
                                    <form action="{{ route('admin.prestaties.diensten.destroy', $dienst) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                onclick="return confirm('Weet je zeker dat je deze dienst wilt verwijderen?')" 
                                                aria-label="Verwijderen" 
                                                class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-rose-100 text-rose-700" 
                                                style="margin-right:2px;"
                                                title="Verwijderen">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-4 h-4">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m-7 0a1 1 0 001 1h8a1 1 0 001-1m-10 0V6a2 2 0 012-2h4a2 2 0 012 2v1"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
        </table>
    </div>
</div>

{{-- Modal --}}
<div id="dienstModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-6 border max-w-md shadow-lg rounded-lg bg-white" style="width: 90%; max-width: 420px;">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold" id="modalTitle">Nieuwe Dienst</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form id="dienstForm" method="POST" action="{{ route('admin.prestaties.diensten.store') }}">
            @csrf
            <input type="hidden" name="_method" value="POST" id="formMethod">

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Naam</label>
                <input type="text" name="naam" id="dienst_naam" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Omschrijving</label>
                <textarea name="omschrijving" id="dienst_omschrijving" rows="3"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500"></textarea>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Prijs (incl. BTW)</label>
                <input type="number" name="prijs" id="dienst_prijs" step="0.01" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
            </div>

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

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Commissie Percentage</label>
                <input type="number" name="commissie_percentage" id="dienst_commissie" step="0.01" min="0" max="100" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="mb-4">
                <label class="flex items-center">
                    <input type="checkbox" name="actief" id="dienst_actief" checked class="rounded border-gray-300 text-blue-600">
                    <span class="ml-2 text-sm text-gray-700">Actief</span>
                </label>
            </div>

            <div class="flex gap-2">
                <button type="button" onclick="closeModal()" class="flex-1 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                    Annuleren
                </button>
                <button type="submit" class="flex-1 px-4 py-2 rounded-lg font-medium hover:opacity-90 text-gray-900" style="background-color: #c8e1eb;">
                    Opslaan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// BTW berekening
document.addEventListener('DOMContentLoaded', function() {
    const prijsInput = document.getElementById('dienst_prijs');
    const prijsExclBtwElement = document.getElementById('prijs-excl-btw');
    
    if (prijsInput) {
        prijsInput.addEventListener('input', function() {
            const prijsInclBtw = parseFloat(this.value) || 0;
            const prijsExclBtw = prijsInclBtw / 1.21;
            prijsExclBtwElement.textContent = '€' + prijsExclBtw.toFixed(2).replace('.', ',');
        });
    }
});

function openModal() {
    const form = document.getElementById('dienstForm');
    const modal = document.getElementById('dienstModal');
    
    document.getElementById('modalTitle').textContent = 'Nieuwe Dienst';
    form.action = '{{ route("admin.prestaties.diensten.store") }}';
    document.getElementById('formMethod').value = 'POST';
    form.reset();
    document.getElementById('prijs-excl-btw').textContent = '€0,00';
    modal.classList.remove('hidden');
    
    console.log('✅ Modal opened for create', form.action);
}

function closeModal() {
    document.getElementById('dienstModal').classList.add('hidden');
}

function openEditModal(id) {
    console.log('🔧 Opening edit modal for dienst:', id);
    
    // Haal dienst data op
    fetch(`/admin/prestaties/diensten/${id}`)
        .then(response => {
            console.log('📡 Response status:', response.status);
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(dienst => {
            console.log('✅ Dienst data:', dienst);
            
            const form = document.getElementById('dienstForm');
            const modal = document.getElementById('dienstModal');
            
            document.getElementById('modalTitle').textContent = 'Dienst Bewerken';
            form.action = `/admin/prestaties/diensten/${id}`;
            document.getElementById('formMethod').value = 'PUT';
            
            document.getElementById('dienst_naam').value = dienst.naam;
            document.getElementById('dienst_omschrijving').value = dienst.omschrijving || '';
            document.getElementById('dienst_prijs').value = dienst.standaard_prijs;
            document.getElementById('dienst_commissie').value = dienst.commissie_percentage;
            document.getElementById('dienst_actief').checked = dienst.is_actief == 1;
            
            // Update BTW preview
            const prijsExclBtw = dienst.standaard_prijs / 1.21;
            document.getElementById('prijs-excl-btw').textContent = '€' + prijsExclBtw.toFixed(2).replace('.', ',');
            
            modal.classList.remove('hidden');
            console.log('✅ Modal opened for edit', form.action);
        })
        .catch(error => {
            console.error('❌ Error:', error);
            alert('Fout bij ophalen dienst gegevens: ' + error.message);
        });
}

function deleteDienst(id) {
    if (confirm('Weet je zeker dat je deze dienst wilt verwijderen?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/prestaties/diensten/${id}`;
        form.innerHTML = `
            @csrf
            @method('DELETE')
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Close modal on escape
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeModal();
    }
});

// Close modal on outside click
document.getElementById('dienstModal')?.addEventListener('click', function(event) {
    if (event.target === this) {
        closeModal();
    }
});
</script>
@endsection
