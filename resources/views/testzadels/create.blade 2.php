@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 pt-32 pb-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6 mt-20" style="margin-top: 120px !important;">
        <div>
                <h1>Nieuwe Uitlening</h1>
            <p class="text-gray-600 mt-1">Registreer een nieuwe testzadel uitlening</p>
        </div>
        <a href="{{ route('testzadels.index') }}" 
           class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Terug naar overzicht
        </a>
    </div>

    <!-- Form -->
    <div class="bg-white shadow rounded-lg">
        <form action="{{ route('testzadels.store') }}" method="POST" enctype="multipart/form-data" class="p-6">
            @csrf
            
            <!-- Error Messages -->
            @if ($errors->any())
                <div class="mb-6 bg-red-50 border border-red-200 rounded-md p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Er zijn fouten opgetreden:</h3>
                            <div class="mt-2 text-sm text-red-700">
                                <ul class="list-disc list-inside space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Klant & Bikefit Sectie -->
                <div class="space-y-6">
                    <div>
                        <label for="klant_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Klant <span class="text-red-500">*</span>
                        </label>
                        <select name="klant_id" id="klant_id" required 
                                class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Selecteer een klant...</option>
                            @foreach($klanten as $klant)
                                <option value="{{ $klant->id }}" {{ old('klant_id') == $klant->id ? 'selected' : '' }}
                                        data-email="{{ $klant->email }}"
                                        data-telefoon="{{ $klant->telefoon }}">
                                    {{ $klant->voornaam }} {{ $klant->naam }} ({{ $klant->email }})
                                </option>
                            @endforeach
                        </select>
                        @error('klant_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="bikefit_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Gekoppelde Bikefit (optioneel)
                        </label>
                        <select name="bikefit_id" id="bikefit_id" 
                                class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Geen bikefit...</option>
                        </select>
                        <p class="mt-1 text-sm text-gray-500">Kies eerst een klant om bikefits te zien</p>
                        @error('bikefit_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Testzadel Details -->
                <div class="space-y-6">
                    <div>
                        <label for="onderdeel" class="block text-sm font-medium text-gray-700 mb-2">
                            Onderdeel <span class="text-red-500">*</span>
                        </label>
                        <select name="onderdeel" id="onderdeel" required
                                class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">-- Selecteer onderdeel --</option>
                            <option value="Testzadel" {{ old('onderdeel') == 'Testzadel' ? 'selected' : '' }}>Testzadel</option>
                            <option value="Nieuw zadel" {{ old('onderdeel') == 'Nieuw zadel' ? 'selected' : '' }}>Nieuw zadel</option>
                            <option value="Zooltjes" {{ old('onderdeel') == 'Zooltjes' ? 'selected' : '' }}>Zooltjes</option>
                            <option value="Lak schoenen" {{ old('onderdeel') == 'Lak schoenen' ? 'selected' : '' }}>Lak schoenen</option>
                        </select>
                        <p class="mt-1 text-sm text-gray-500">Registreer hier onderdelen die uitgeleend worden of nieuw besteld zijn voor deze klant.</p>
                        @error('onderdeel')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                            Status <span class="text-red-500">*</span>
                        </label>
                        <select name="status" id="status" required
                                class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">-- Selecteer status --</option>
                            <option value="uitgeleend" {{ old('status') == 'uitgeleend' ? 'selected' : '' }}>Uitgeleend</option>
                            <option value="beschikbaar" {{ old('status') == 'beschikbaar' ? 'selected' : '' }}>Beschikbaar</option>
                            <option value="teruggebracht" {{ old('status') == 'teruggebracht' ? 'selected' : '' }}>Teruggebracht</option>
                        </select>
                        @error('status')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" name="automatisch_herinneringsmailtje" id="automatisch_herinneringsmailtje" 
                               value="1" {{ old('automatisch_herinneringsmailtje') ? 'checked' : '' }}
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="automatisch_herinneringsmailtje" class="ml-2 block text-sm text-gray-900">
                            Automatisch herinneringsmailtje versturen
                        </label>
                    </div>
                    <p class="text-sm text-gray-500">Vink aan om automatische herinneringen te versturen voor dit onderdeel</p>
                </div>

                <!-- Zadel Informatie -->
                <div class="space-y-6" id="zadel-info-section" style="display: none;">
                    <h3 class="text-lg font-medium text-gray-900 border-b border-gray-200 pb-2">Zadel Informatie</h3>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="merk" class="block text-sm font-medium text-gray-700 mb-2">Merk</label>
                            <input type="text" name="merk" id="merk" value="{{ old('merk') }}"
                                   class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            @error('merk')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="model" class="block text-sm font-medium text-gray-700 mb-2">Model</label>
                            <input type="text" name="model" id="model" value="{{ old('model') }}"
                                   class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            @error('model')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="type" class="block text-sm font-medium text-gray-700 mb-2">Type</label>
                            <input type="text" name="type" id="type" value="{{ old('type') }}"
                                   class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            @error('type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="breedte" class="block text-sm font-medium text-gray-700 mb-2">Breedte (mm)</label>
                            <input type="number" name="breedte" id="breedte" value="{{ old('breedte') }}" 
                                   min="1" step="1"
                                   class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            @error('breedte')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="foto_zadel" class="block text-sm font-medium text-gray-700 mb-2">Foto van zadel</label>
                        <input type="file" name="foto_zadel" id="foto_zadel" 
                               accept="image/jpeg,image/png,image/jpg"
                               class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        <p class="mt-1 text-sm text-gray-500">Max 5MB - JPG, PNG</p>
                    </div>

                    <div>
                        <label for="beschrijving" class="block text-sm font-medium text-gray-700 mb-2">Beschrijving</label>
                        <textarea name="beschrijving" id="beschrijving" rows="3" 
                                  placeholder="Eventuele beschrijving van de testzadel..."
                                  class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">{{ old('beschrijving') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Datum Sectie -->
            <div class="mt-8 border-t border-gray-200 pt-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Uitlening Details</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="uitgeleend_op" class="block text-sm font-medium text-gray-700 mb-2">
                            Uitgeleend op <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="uitgeleend_op" id="uitgeleend_op" 
                               value="{{ old('uitgeleend_op', date('Y-m-d')) }}" required
                               class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        @error('uitgeleend_op')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="verwachte_terugbring_datum" class="block text-sm font-medium text-gray-700 mb-2">
                            Verwachte terugbring datum <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="verwachte_terugbring_datum" id="verwachte_terugbring_datum" 
                               value="{{ old('verwachte_terugbring_datum', date('Y-m-d', strtotime('+2 weeks'))) }}" required
                               class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        @error('verwachte_terugbring_datum')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-4">
                    <label for="opmerkingen" class="block text-sm font-medium text-gray-700 mb-2">Opmerkingen</label>
                    <textarea name="opmerkingen" id="opmerkingen" rows="3" 
                              placeholder="Eventuele opmerkingen bij de uitlening..."
                              class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">{{ old('opmerkingen') }}</textarea>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="mt-8 flex justify-end space-x-3 border-t border-gray-200 pt-6">
                <a href="{{ route('testzadels.index') }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    Annuleren
                </a>
                <button type="submit" 
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Testzadel uitlening registreren
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const onderdeelSelect = document.getElementById('onderdeel');
    const zadelInfoSection = document.getElementById('zadel-info-section');
    const merkLabel = document.querySelector('label[for="merk"]');
    const modelLabel = document.querySelector('label[for="model"]');
    const typeLabel = document.querySelector('label[for="type"]');
    const breedteLabel = document.querySelector('label[for="breedte"]');
    const fotoLabel = document.querySelector('label[for="foto_zadel"]');
    const beschrijvingTextarea = document.getElementById('beschrijving');
    const sectionTitle = zadelInfoSection.querySelector('h3');

    function updateFieldsBasedOnOnderdeel(selectedValue) {
        // Reset visibility
        zadelInfoSection.style.display = 'none';
        
        if (selectedValue === 'Testzadel' || selectedValue === 'Nieuw zadel') {
            // Show zadel information section
            zadelInfoSection.style.display = 'block';
            sectionTitle.textContent = selectedValue === 'Testzadel' ? 'Zadel Informatie' : 'Nieuw Zadel Informatie';
            
            // Update labels for zadel
            merkLabel.textContent = 'Merk';
            modelLabel.textContent = 'Model';
            typeLabel.textContent = 'Type';
            breedteLabel.textContent = 'Breedte (mm)';
            fotoLabel.textContent = selectedValue === 'Testzadel' ? 'Foto van zadel' : 'Foto van nieuw zadel';
            beschrijvingTextarea.placeholder = selectedValue === 'Testzadel' ? 'Eventuele beschrijving van de testzadel...' : 'Eventuele beschrijving van het nieuwe zadel...';
            
        } else if (selectedValue === 'Zooltjes') {
            // Show section with different labels for zooltjes
            zadelInfoSection.style.display = 'block';
            sectionTitle.textContent = 'Zooltjes Informatie';
            
            // Update labels for zooltjes
            merkLabel.textContent = 'Merk';
            modelLabel.textContent = 'Model';
            typeLabel.textContent = 'Type';
            breedteLabel.textContent = 'Maat';
            fotoLabel.textContent = 'Foto van zooltjes';
            beschrijvingTextarea.placeholder = 'Eventuele beschrijving van de zooltjes...';
            
        } else if (selectedValue === 'Lak schoenen') {
            // Show section with different labels for schoenen
            zadelInfoSection.style.display = 'block';
            sectionTitle.textContent = 'Lak Schoenen Informatie';
            
            // Update labels for schoenen
            merkLabel.textContent = 'Merk';
            modelLabel.textContent = 'Model';
            typeLabel.textContent = 'Type';
            breedteLabel.textContent = 'Maat';
            fotoLabel.textContent = 'Foto van schoenen';
            beschrijvingTextarea.placeholder = 'Eventuele beschrijving van de schoenen...';
        }
    }

    // Listen for changes in onderdeel selection
    onderdeelSelect.addEventListener('change', function() {
        updateFieldsBasedOnOnderdeel(this.value);
    });

    // Initialize on page load if there's an old value
    const currentValue = onderdeelSelect.value || '{{ old('onderdeel') }}';
    if (currentValue) {
        updateFieldsBasedOnOnderdeel(currentValue);
    }
});
</script>
@endsection