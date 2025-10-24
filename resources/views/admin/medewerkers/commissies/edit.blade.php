@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    {{-- Header --}}
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Commissie Factoren - {{ $medewerker->name }}</h1>
            <p class="text-sm text-gray-600 mt-1">Stel diploma, ervaring en anciënniteit bonussen in</p>
        </div>
        <a href="{{ route('admin.medewerkers.commissies.index') }}" 
           class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition text-sm font-medium">
            ← Terug
        </a>
    </div>

    {{-- Success/Error berichten --}}
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-500 text-green-800 rounded flex items-center justify-between">
            <span>✅ {{ session('success') }}</span>
            <button onclick="this.parentElement.remove()" class="text-green-600 hover:text-green-800">&times;</button>
        </div>
    @endif

    @if($errors->any())
        <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-800 rounded">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Algemene Factoren --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-bold mb-4">Algemene Commissie Factoren</h2>
            
            <form action="{{ route('admin.medewerkers.commissies.update', $medewerker) }}" method="POST">
                @csrf
                @method('PUT')
                
                {{-- Diploma Factor --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Diploma Bonus (%)
                        <span class="text-gray-500 text-xs">Bonus voor behaalde diploma's</span>
                    </label>
                    <input type="number" name="diploma_factor" step="0.01" min="0" max="100"
                           value="{{ old('diploma_factor', $algemeneFactoren->diploma_factor ?? 0) }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                {{-- Ervaring Factor --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Ervaring Bonus (%)
                        <span class="text-gray-500 text-xs">Bonus voor jaren ervaring</span>
                    </label>
                    <input type="number" name="ervaring_factor" step="0.01" min="0" max="100"
                           value="{{ old('ervaring_factor', $algemeneFactoren->ervaring_factor ?? 0) }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                {{-- Anciënniteit Factor --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Anciënniteit Bonus (%)
                        <span class="text-gray-500 text-xs">Bonus voor diensttijd bij Bonami</span>
                    </label>
                    <input type="number" name="ancienniteit_factor" step="0.01" min="0" max="100"
                           value="{{ old('ancienniteit_factor', $algemeneFactoren->ancienniteit_factor ?? 0) }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                {{-- Totale Bonus Preview --}}
                <div class="mb-4 p-4 bg-blue-50 rounded-lg">
                    <div class="text-sm text-gray-600 mb-1">Totale Bonus</div>
                    <div class="text-2xl font-bold text-blue-600" id="totale-bonus-preview">
                        +{{ number_format(($algemeneFactoren->diploma_factor ?? 0) + ($algemeneFactoren->ervaring_factor ?? 0) + ($algemeneFactoren->ancienniteit_factor ?? 0), 2) }}%
                    </div>
                </div>

                {{-- Opmerking --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Opmerking</label>
                    <textarea name="opmerking" rows="3"
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                              placeholder="Optionele notitie...">{{ old('opmerking', $algemeneFactoren->opmerking ?? '') }}</textarea>
                </div>

                <button type="submit" 
                        class="w-full py-3 px-4 rounded-lg font-medium hover:opacity-90 transition text-gray-900"
                        style="background-color: #c8e1eb;">
                    Algemene Factoren Opslaan
                </button>
            </form>
        </div>

        {{-- Dienst-Specifieke Commissies --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-bold mb-4">Dienst-Specifieke Commissies</h2>
            <p class="text-sm text-gray-600 mb-4">Overschrijf de standaard commissie voor specifieke diensten</p>
            
            <div class="space-y-4">
                @foreach($diensten as $dienstData)
                    @php
                        $dienst = $dienstData['dienst'];
                        $customFactor = $dienstData['custom_factor'];
                        $berekend = $dienstData['berekende_commissie'];
                    @endphp
                    
                    <div class="border rounded-lg p-4">
                        <div class="flex justify-between items-start mb-2">
                            <div class="flex-1">
                                <div class="font-medium text-gray-900">{{ $dienst->naam }}</div>
                                <div class="text-xs text-gray-500 mt-1">
                                    Standaard: {{ $dienst->commissie_percentage }}%
                                    @if($algemeneFactoren)
                                        + {{ number_format($algemeneFactoren->totale_bonus, 1) }}% bonus
                                    @endif
                                    = {{ number_format($berekend, 1) }}%
                                </div>
                            </div>
                        </div>

                        <form action="{{ route('admin.medewerkers.commissies.dienst.update', [$medewerker, $dienst]) }}" 
                              method="POST" class="mt-3">
                            @csrf
                            @method('PUT')
                            
                            <div class="flex gap-2">
                                <input type="number" name="custom_commissie_percentage" step="0.01" min="0" max="100"
                                       value="{{ $customFactor->custom_commissie_percentage ?? '' }}"
                                       placeholder="Custom % (leeg = standaard)"
                                       class="flex-1 border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                
                                <button type="submit" 
                                        class="px-4 py-2 rounded font-medium text-sm"
                                        style="background-color: #c8e1eb; color: #111;">
                                    {{ $customFactor ? 'Update' : 'Instellen' }}
                                </button>
                            </div>

                            @if($customFactor && $customFactor->custom_commissie_percentage)
                                <div class="mt-2 text-xs text-green-600 font-medium">
                                    ✓ Custom commissie actief: {{ number_format($customFactor->custom_commissie_percentage, 1) }}%
                                </div>
                            @endif
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<script>
// Live update totale bonus preview
document.querySelectorAll('input[name="diploma_factor"], input[name="ervaring_factor"], input[name="ancienniteit_factor"]').forEach(input => {
    input.addEventListener('input', function() {
        const diploma = parseFloat(document.querySelector('input[name="diploma_factor"]').value) || 0;
        const ervaring = parseFloat(document.querySelector('input[name="ervaring_factor"]').value) || 0;
        const ancienniteit = parseFloat(document.querySelector('input[name="ancienniteit_factor"]').value) || 0;
        const totaal = diploma + ervaring + ancienniteit;
        
        document.getElementById('totale-bonus-preview').textContent = '+' + totaal.toFixed(2) + '%';
    });
});
</script>
@endsection
