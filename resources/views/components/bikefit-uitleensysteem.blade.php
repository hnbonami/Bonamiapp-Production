<!-- Uitgebreid Uitleensysteem voor Bikefit -->
@php
    // Check if we're editing and there's existing testzadel data
    $existingTestzadel = null;
    if (isset($bikefit) && $bikefit) {
        $existingTestzadel = \App\Models\Testzadel::where('bikefit_id', $bikefit->id)->first();
    }
@endphp

<div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mt-6">
    <h3 class="text-lg font-semibold text-amber-800 mb-4">
        <i class="fas fa-handshake mr-2"></i>Uitlenen/Nieuw
    </h3>
    <p class="text-sm text-amber-700 mb-4">
        Registreer hier onderdelen die uitgeleend worden of nieuw besteld zijn voor deze klant.
    </p>

    <!-- Onderdeel Type -->
    <div class="mb-4">
        <label for="onderdeel_type" class="block text-sm font-medium text-gray-700 mb-2">
            Onderdeel <span class="text-red-500">*</span>
        </label>
        <select name="onderdeel_type" id="onderdeel_type" 
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            <option value="">-- Selecteer onderdeel --</option>
            <option value="testzadel" {{ old('onderdeel_type', $existingTestzadel->onderdeel_type ?? '') == 'testzadel' ? 'selected' : '' }}>Testzadel</option>
            <option value="nieuw zadel" {{ old('onderdeel_type', $existingTestzadel->onderdeel_type ?? '') == 'nieuw zadel' ? 'selected' : '' }}>Nieuw zadel</option>
            <option value="zooltjes" {{ old('onderdeel_type', $existingTestzadel->onderdeel_type ?? '') == 'zooltjes' ? 'selected' : '' }}>Zooltjes</option>
            <option value="Lake schoenen" {{ old('onderdeel_type', $existingTestzadel->onderdeel_type ?? '') == 'Lake schoenen' ? 'selected' : '' }}>Lake schoenen</option>
        </select>
    </div>

    <!-- Status (verschijnt alleen als onderdeel_type is geselecteerd) -->
    <div id="status_container" class="mb-4" style="display: none;">
        <label for="onderdeel_status" class="block text-sm font-medium text-gray-700 mb-2">
            Status <span class="text-red-500">*</span>
        </label>
        <select name="onderdeel_status" id="onderdeel_status" 
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            <option value="">-- Selecteer status --</option>
            <option value="nieuw" {{ old('onderdeel_status', $existingTestzadel->onderdeel_status ?? '') == 'nieuw' ? 'selected' : '' }}>Nieuw</option>
            <option value="test" {{ old('onderdeel_status', $existingTestzadel->onderdeel_status ?? '') == 'test' ? 'selected' : '' }}>Test</option>
            <option value="besteld" {{ old('onderdeel_status', $existingTestzadel->onderdeel_status ?? '') == 'besteld' ? 'selected' : '' }}>Besteld</option>
        </select>
    </div>

    <!-- Automatisch mailtje -->
    <div id="mailtje_container" class="mb-4" style="display: none;">
        <div class="flex items-center">
            <input type="checkbox" name="automatisch_mailtje" id="automatisch_mailtje" 
                   class="mr-2 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                   {{ old('automatisch_mailtje', $existingTestzadel->automatisch_mailtje ?? false) ? 'checked' : '' }}>
            <label for="automatisch_mailtje" class="text-sm font-medium text-gray-700">
                Automatisch herinneringsmailtje versturen
            </label>
        </div>
        <p class="text-xs text-gray-500 mt-1">
            Vink aan om automatische herinneringen te versturen voor dit onderdeel
        </p>
    </div>

    <!-- Dynamische velden per onderdeel type -->
    
    <!-- Voor Testzadel en Nieuw zadel -->
    <div id="zadel_fields" class="space-y-4" style="display: none;">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="zadel_merk" class="block text-sm font-medium text-gray-700 mb-2">Merk</label>
                <input type="text" name="zadel_merk" id="zadel_merk" 
                       value="{{ old('zadel_merk', $existingTestzadel->zadel_merk ?? '') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label for="zadel_model" class="block text-sm font-medium text-gray-700 mb-2">Model</label>
                <input type="text" name="zadel_model" id="zadel_model" 
                       value="{{ old('zadel_model', $existingTestzadel->zadel_model ?? '') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="zadel_type" class="block text-sm font-medium text-gray-700 mb-2">Type</label>
                <input type="text" name="zadel_type" id="zadel_type" 
                       value="{{ old('zadel_type') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label for="zadel_breedte" class="block text-sm font-medium text-gray-700 mb-2">Breedte (mm)</label>
                <input type="number" name="zadel_breedte" id="zadel_breedte" 
                       value="{{ old('zadel_breedte') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
        </div>
    </div>

    <!-- Voor Zooltjes en Lake schoenen -->
    <div id="overig_fields" class="space-y-4" style="display: none;">
        <div>
            <label for="onderdeel_omschrijving" class="block text-sm font-medium text-gray-700 mb-2">Omschrijving</label>
            <input type="text" name="onderdeel_omschrijving" id="onderdeel_omschrijving" 
                   value="{{ old('onderdeel_omschrijving') }}"
                   placeholder="Beschrijving van het onderdeel..."
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div>
            <label for="overig_merk" class="block text-sm font-medium text-gray-700 mb-2">Merk (optioneel)</label>
            <input type="text" name="overig_merk" id="overig_merk" 
                   value="{{ old('overig_merk') }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>
    </div>

    <!-- Uitleendatum en verwachte retour (voor alle types) -->
    <div id="datum_fields" class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4" style="display: none;">
        <div>
            <label for="uitgeleend_op" class="block text-sm font-medium text-gray-700 mb-2">
                Uitgeleend op <span class="text-red-500">*</span>
            </label>
            <input type="date" name="uitgeleend_op" id="uitgeleend_op" 
                   value="{{ old('uitgeleend_op', $existingTestzadel ? ($existingTestzadel->uitleen_datum ? $existingTestzadel->uitleen_datum->format('Y-m-d') : date('Y-m-d')) : date('Y-m-d')) }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div>
            <label for="verwachte_terugbring_datum" class="block text-sm font-medium text-gray-700 mb-2">
                Verwachte retour <span class="text-red-500">*</span>
            </label>
            <input type="date" name="verwachte_terugbring_datum" id="verwachte_terugbring_datum" 
                   value="{{ old('verwachte_terugbring_datum', $existingTestzadel ? ($existingTestzadel->verwachte_retour_datum ? $existingTestzadel->verwachte_retour_datum->format('Y-m-d') : date('Y-m-d', strtotime('+2 weeks'))) : date('Y-m-d', strtotime('+2 weeks'))) }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>
    </div>

    <!-- Opmerkingen -->
    <div id="opmerkingen_field" class="mt-4" style="display: none;">
        <label for="onderdeel_opmerkingen" class="block text-sm font-medium text-gray-700 mb-2">Opmerkingen</label>
        <textarea name="onderdeel_opmerkingen" id="onderdeel_opmerkingen" rows="3"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  placeholder="Eventuele opmerkingen over dit onderdeel...">{{ old('onderdeel_opmerkingen') }}</textarea>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const onderdeelType = document.getElementById('onderdeel_type');
    const statusContainer = document.getElementById('status_container');
    const mailtjeContainer = document.getElementById('mailtje_container');
    const zadelFields = document.getElementById('zadel_fields');
    const overigFields = document.getElementById('overig_fields');
    const datumFields = document.getElementById('datum_fields');
    const opmerkingenField = document.getElementById('opmerkingen_field');

    function toggleFields() {
        const selectedType = onderdeelType.value;
        
        if (selectedType) {
            // Toon basis velden
            statusContainer.style.display = 'block';
            mailtjeContainer.style.display = 'block';
            datumFields.style.display = 'block';
            opmerkingenField.style.display = 'block';
            
            // Toon specifieke velden per type
            if (selectedType === 'testzadel' || selectedType === 'nieuw zadel') {
                zadelFields.style.display = 'block';
                overigFields.style.display = 'none';
            } else {
                zadelFields.style.display = 'none';
                overigFields.style.display = 'block';
            }
        } else {
            // Verberg alle velden
            statusContainer.style.display = 'none';
            mailtjeContainer.style.display = 'none';
            zadelFields.style.display = 'none';
            overigFields.style.display = 'none';
            datumFields.style.display = 'none';
            opmerkingenField.style.display = 'none';
        }
    }

    onderdeelType.addEventListener('change', toggleFields);
    
    // Bij page load - ook rekening houden met bestaande data
    toggleFields();
    
    // Als er bestaande data is, zorg dat de juiste velden getoond worden
    @if($existingTestzadel)
        // Trigger change event om velden te tonen
        const changeEvent = new Event('change');
        onderdeelType.dispatchEvent(changeEvent);
    @endif
});
</script>