@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.inspanningstesten.instellingen') }}" 
                   class="text-gray-600 hover:text-gray-900">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">✏️ Template Bewerken: {{ $template->naam }}</h1>
                    <p class="mt-2 text-gray-600">Pas de zones configuratie aan voor jouw organisatie</p>
                </div>
            </div>
        </div>

        <form action="{{ route('admin.inspanningstesten.update', $template->id) }}" method="POST" id="zone-template-form">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Linker kolom: Template Info -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Stap 1: Template Informatie -->
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-4">📋 Stap 1: Template Informatie</h2>
                        
                        <div class="space-y-4">
                            <!-- Template Naam -->
                            <div>
                                <label for="naam" class="block text-sm font-medium text-gray-700 mb-1">
                                    Template Naam <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       name="naam" 
                                       id="naam" 
                                       required
                                       value="{{ old('naam', $template->naam) }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                       placeholder="bijv. Mijn Custom Zones">
                                @error('naam')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Sport Type -->
                            <div>
                                <label for="sport_type" class="block text-sm font-medium text-gray-700 mb-1">
                                    Sport Type <span class="text-red-500">*</span>
                                </label>
                                <select name="sport_type" 
                                        id="sport_type" 
                                        required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="beide" {{ old('sport_type', $template->sport_type) == 'beide' ? 'selected' : '' }}>🏆 Beide (Fietsen & Lopen)</option>
                                    <option value="fietsen" {{ old('sport_type', $template->sport_type) == 'fietsen' ? 'selected' : '' }}>🚴 Fietsen</option>
                                    <option value="lopen" {{ old('sport_type', $template->sport_type) == 'lopen' ? 'selected' : '' }}>🏃 Lopen</option>
                                </select>
                            </div>

                            <!-- Berekening Basis -->
                            <div>
                                <label for="berekening_basis" class="block text-sm font-medium text-gray-700 mb-1">
                                    Berekening Basis <span class="text-red-500">*</span>
                                </label>
                                <select name="berekening_basis" 
                                        id="berekening_basis" 
                                        required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="lt2" {{ old('berekening_basis', $template->berekening_basis) == 'lt2' ? 'selected' : '' }}>LT2 (Anaërobe Drempel)</option>
                                    <option value="lt1" {{ old('berekening_basis', $template->berekening_basis) == 'lt1' ? 'selected' : '' }}>LT1 (Aërobe Drempel)</option>
                                    <option value="max" {{ old('berekening_basis', $template->berekening_basis) == 'max' ? 'selected' : '' }}>MAX (Maximaal Vermogen)</option>
                                    <option value="ftp" {{ old('berekening_basis', $template->berekening_basis) == 'ftp' ? 'selected' : '' }}>FTP (Functional Threshold Power)</option>
                                    <option value="custom" {{ old('berekening_basis', $template->berekening_basis) == 'custom' ? 'selected' : '' }}>Custom</option>
                                </select>
                            </div>

                            <!-- Beschrijving -->
                            <div>
                                <label for="beschrijving" class="block text-sm font-medium text-gray-700 mb-1">
                                    Beschrijving (optioneel)
                                </label>
                                <textarea name="beschrijving" 
                                          id="beschrijving" 
                                          rows="3"
                                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                          placeholder="Beschrijf wanneer deze template gebruikt moet worden...">{{ old('beschrijving', $template->beschrijving) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Stap 2: Zones Configuratie -->
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-xl font-bold text-gray-900">🎯 Stap 2: Zones Configuratie</h2>
                            <button type="button" 
                                    onclick="addZone()"
                                    class="px-4 py-2 text-sm font-medium rounded-lg transition"
                                    style="background-color: #c8e1eb; color: #1e3a8a;"
                                    onmouseover="this.style.backgroundColor='#b0d4e0'" 
                                    onmouseout="this.style.backgroundColor='#c8e1eb'">
                                ➕ Zone Toevoegen
                            </button>
                        </div>

                        <div id="zones-container" class="space-y-4">
                            <!-- Bestaande zones worden hier geladen -->
                        </div>

                        <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <p class="text-sm text-blue-800">
                                💡 <strong>Tip:</strong> Klik op de kleur om een color picker te openen. Zones worden automatisch gesorteerd op volgorde.
                            </p>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="flex gap-4">
                        <button type="submit" 
                                class="flex-1 px-6 py-3 font-medium rounded-lg transition"
                                style="background-color: #c8e1eb; color: #1e3a8a;"
                                onmouseover="this.style.backgroundColor='#b0d4e0'" 
                                onmouseout="this.style.backgroundColor='#c8e1eb'">
                            💾 Wijzigingen Opslaan
                        </button>
                        <a href="{{ route('admin.inspanningstesten.instellingen') }}" 
                           class="px-6 py-3 bg-gray-200 text-gray-700 font-medium rounded-lg hover:bg-gray-300 transition text-center">
                            ❌ Annuleren
                        </a>
                    </div>
                </div>

                <!-- Rechter kolom: Live Preview -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-xl shadow-md p-6 sticky top-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-4">👁️ Live Preview</h2>
                        
                        <!-- Visual Preview Bar -->
                        <div id="preview-bar" class="h-12 rounded-lg overflow-hidden border-2 border-gray-300 mb-4 flex">
                            <div class="flex-1 bg-gray-100 flex items-center justify-center text-gray-400 text-sm">
                                Voeg zones toe
                            </div>
                        </div>

                        <!-- Preview Percentages -->
                        <div class="flex justify-between text-xs text-gray-600 mb-4">
                            <span>0%</span>
                            <span>50%</span>
                            <span>100%</span>
                        </div>

                        <!-- Zone List Preview -->
                        <div id="preview-list" class="space-y-2">
                            <p class="text-sm text-gray-500 italic">Geen zones geconfigureerd</p>
                        </div>

                        <!-- Stats -->
                        <div class="mt-6 pt-4 border-t border-gray-200">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600">Totaal zones:</span>
                                <span id="zone-count" class="font-bold text-gray-900">0</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
let zoneCounter = 0;
const defaultColors = ['#E3F2FD', '#E8F5E8', '#F1F8E9', '#FFF3E0', '#FFEBEE', '#FFCDD2', '#FFE0E0', '#FFD0D0'];

// Bestaande zones uit database
const existingZones = @json($template->zones);

// Laad bestaande zones bij laden
document.addEventListener('DOMContentLoaded', function() {
    if (existingZones.length > 0) {
        existingZones.forEach(zone => {
            addZone(zone);
        });
        // Wacht even tot DOM updated is en update dan preview
        setTimeout(() => updatePreview(), 100);
    } else {
        addZone(); // Start met 1 lege zone
        updatePreview();
    }
});

function addZone(zoneData = null) {
    const container = document.getElementById('zones-container');
    const index = zoneCounter++;
    const color = zoneData ? zoneData.kleur : defaultColors[index % defaultColors.length];
    
    const zoneHtml = `
        <div class="zone-item border-2 border-gray-200 rounded-lg p-4 bg-gray-50" data-index="${index}">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-bold text-gray-900">Zone ${index + 1}</h3>
                <button type="button" 
                        onclick="removeZone(${index})"
                        class="text-red-600 hover:text-red-800 font-medium text-sm">
                    🗑️ Verwijder
                </button>
            </div>
            
            <div class="grid grid-cols-2 gap-3">
                <!-- Zone Naam -->
                <div class="col-span-2">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Zone Naam *</label>
                    <input type="text" 
                           name="zones[${index}][zone_naam]" 
                           required
                           value="${zoneData ? zoneData.zone_naam : ''}"
                           onchange="updatePreview()"
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                           placeholder="bijv. Herstel">
                </div>

                <!-- Min % -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Min %</label>
                    <input type="number" 
                           name="zones[${index}][min_percentage]" 
                           required
                           min="0"
                           max="200"
                           value="${zoneData ? zoneData.min_percentage : ''}"
                           onchange="updatePreview()"
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                           placeholder="60">
                </div>

                <!-- Max % -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Max %</label>
                    <input type="number" 
                           name="zones[${index}][max_percentage]" 
                           required
                           min="0"
                           max="200"
                           value="${zoneData ? zoneData.max_percentage : ''}"
                           onchange="updatePreview()"
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                           placeholder="75">
                </div>

                <!-- Kleur -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Kleur</label>
                    <input type="color" 
                           name="zones[${index}][kleur]" 
                           value="${color}"
                           onchange="updatePreview()"
                           class="w-full h-10 border border-gray-300 rounded-lg cursor-pointer">
                </div>

                <!-- Referentie -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Referentie</label>
                    <select name="zones[${index}][referentie_waarde]"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="" ${!zoneData || !zoneData.referentie_waarde ? 'selected' : ''}>Geen</option>
                        <option value="LT1" ${zoneData && zoneData.referentie_waarde === 'LT1' ? 'selected' : ''}>LT1</option>
                        <option value="LT2" ${zoneData && zoneData.referentie_waarde === 'LT2' ? 'selected' : ''}>LT2</option>
                        <option value="MAX" ${zoneData && zoneData.referentie_waarde === 'MAX' ? 'selected' : ''}>MAX</option>
                        <option value="FTP" ${zoneData && zoneData.referentie_waarde === 'FTP' ? 'selected' : ''}>FTP</option>
                        <option value="OBLA" ${zoneData && zoneData.referentie_waarde === 'OBLA' ? 'selected' : ''}>OBLA</option>
                    </select>
                </div>

                <!-- Beschrijving -->
                <div class="col-span-2">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Beschrijving</label>
                    <input type="text" 
                           name="zones[${index}][beschrijving]" 
                           value="${zoneData ? (zoneData.beschrijving || '') : ''}"
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                           placeholder="Optionele beschrijving...">
                </div>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', zoneHtml);
    updatePreview();
}

function removeZone(index) {
    const zoneItem = document.querySelector(`.zone-item[data-index="${index}"]`);
    if (zoneItem) {
        zoneItem.remove();
        updatePreview();
    }
}

function updatePreview() {
    const zones = [];
    const zoneItems = document.querySelectorAll('.zone-item');
    
    zoneItems.forEach(item => {
        const naam = item.querySelector('[name*="[zone_naam]"]')?.value || '';
        const min = parseInt(item.querySelector('[name*="[min_percentage]"]')?.value) || 0;
        const max = parseInt(item.querySelector('[name*="[max_percentage]"]')?.value) || 0;
        const kleur = item.querySelector('[name*="[kleur]"]')?.value || '#E3F2FD';
        
        if (naam) {
            zones.push({ naam, min, max, kleur });
        }
    });
    
    // Update preview bar
    const previewBar = document.getElementById('preview-bar');
    if (zones.length === 0) {
        previewBar.innerHTML = '<div class="flex-1 bg-gray-100 flex items-center justify-center text-gray-400 text-sm">Voeg zones toe</div>';
    } else {
        previewBar.innerHTML = zones.map(zone => 
            `<div style="background-color: ${zone.kleur}; flex: 1;" 
                  title="${zone.naam}: ${zone.min}-${zone.max}%"
                  class="hover:opacity-80 transition"></div>`
        ).join('');
    }
    
    // Update preview list
    const previewList = document.getElementById('preview-list');
    if (zones.length === 0) {
        previewList.innerHTML = '<p class="text-sm text-gray-500 italic">Geen zones geconfigureerd</p>';
    } else {
        previewList.innerHTML = zones.map(zone => `
            <div class="flex items-center text-sm">
                <div class="w-4 h-4 rounded-full mr-2" style="background-color: ${zone.kleur}"></div>
                <span class="font-medium text-gray-900">${zone.naam}</span>
                <span class="ml-auto text-gray-600">${zone.min}-${zone.max}%</span>
            </div>
        `).join('');
    }
    
    // Update count
    document.getElementById('zone-count').textContent = zones.length;
}
</script>
@endsection
