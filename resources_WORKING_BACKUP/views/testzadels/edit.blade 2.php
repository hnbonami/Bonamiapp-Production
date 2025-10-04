@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Testzadel Bewerken</h1>
        <div class="flex gap-3">
            <a href="{{ route('testzadels.show', $testzadel) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                👁️ Bekijken
            </a>
            <a href="{{ route('testzadels.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                ← Terug naar overzicht
            </a>
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
        <div class="p-6">
            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded">
                    <h3 class="font-medium text-red-800">Er zijn fouten opgetreden:</h3>
                    <ul class="mt-2 text-sm text-red-600">
                        @foreach ($errors->all() as $error)
                            <li>• {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('testzadels.update', $testzadel) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <!-- Status indicator -->
                <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Status: {{ ucfirst($testzadel->status) }}</h3>
                            <p class="text-sm text-gray-600">{{ $testzadel->dagen_uitgeleend }} dagen uitgeleend</p>
                        </div>
                        <span class="text-3xl">{{ $testzadel->status_icon }}</span>
                    </div>
                </div>

                <!-- Klant selectie -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="klant_id" class="block text-sm font-medium text-gray-700 mb-2">Klant *</label>
                        <select name="klant_id" id="klant_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            <option value="">Selecteer klant...</option>
                            @foreach($klanten as $klant)
                                <option value="{{ $klant->id }}" {{ old('klant_id', $testzadel->klant_id) == $klant->id ? 'selected' : '' }}>
                                    {{ $klant->voornaam }} {{ $klant->naam }} ({{ $klant->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="bikefit_id" class="block text-sm font-medium text-gray-700 mb-2">Gekoppelde Bikefit (optioneel)</label>
                        <select name="bikefit_id" id="bikefit_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Geen bikefit...</option>
                            @foreach($bikefits as $bikefit)
                                <option value="{{ $bikefit->id }}" {{ old('bikefit_id', $testzadel->bikefit_id) == $bikefit->id ? 'selected' : '' }}>
                                    {{ $bikefit->klant->voornaam }} {{ $bikefit->klant->naam }} - {{ $bikefit->datum->format('d/m/Y') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Status wijzigen -->
                <div class="mb-6">
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status *</label>
                    <select name="status" id="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        <option value="uitgeleend" {{ old('status', $testzadel->status) == 'uitgeleend' ? 'selected' : '' }}>Uitgeleend</option>
                        <option value="teruggebracht" {{ old('status', $testzadel->status) == 'teruggebracht' ? 'selected' : '' }}>Teruggebracht</option>
                        <option value="gearchiveerd" {{ old('status', $testzadel->status) == 'gearchiveerd' ? 'selected' : '' }}>Gearchiveerd</option>
                    </select>
                </div>

                <!-- Zadel informatie -->
                <h3 class="text-xl font-bold mb-4 text-gray-900">Zadel Informatie</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div>
                        <label for="zadel_merk" class="block text-sm font-medium text-gray-700 mb-2">Merk *</label>
                        <input type="text" name="zadel_merk" id="zadel_merk" value="{{ old('zadel_merk', $testzadel->zadel_merk) }}" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    </div>

                    <div>
                        <label for="zadel_model" class="block text-sm font-medium text-gray-700 mb-2">Model *</label>
                        <input type="text" name="zadel_model" id="zadel_model" value="{{ old('zadel_model', $testzadel->zadel_model) }}" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    </div>

                    <div>
                        <label for="zadel_type" class="block text-sm font-medium text-gray-700 mb-2">Type</label>
                        <select name="zadel_type" id="zadel_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Selecteer type...</option>
                            <option value="Road" {{ old('zadel_type', $testzadel->zadel_type) == 'Road' ? 'selected' : '' }}>Road</option>
                            <option value="MTB" {{ old('zadel_type', $testzadel->zadel_type) == 'MTB' ? 'selected' : '' }}>MTB</option>
                            <option value="Gravel" {{ old('zadel_type', $testzadel->zadel_type) == 'Gravel' ? 'selected' : '' }}>Gravel</option>
                            <option value="Triathlon" {{ old('zadel_type', $testzadel->zadel_type) == 'Triathlon' ? 'selected' : '' }}>Triathlon</option>
                            <option value="Comfort" {{ old('zadel_type', $testzadel->zadel_type) == 'Comfort' ? 'selected' : '' }}>Comfort</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="zadel_breedte" class="block text-sm font-medium text-gray-700 mb-2">Breedte (mm)</label>
                        <input type="number" name="zadel_breedte" id="zadel_breedte" value="{{ old('zadel_breedte', $testzadel->zadel_breedte) }}" 
                               min="100" max="300" step="1"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label for="foto" class="block text-sm font-medium text-gray-700 mb-2">Foto van zadel</label>
                        @if($testzadel->foto_path)
                            <div class="mb-2">
                                <img src="{{ asset('storage/' . $testzadel->foto_path) }}" alt="Huidige foto" class="w-20 h-20 object-cover rounded">
                                <p class="text-xs text-gray-500">Huidige foto</p>
                            </div>
                        @endif
                        <input type="file" name="foto" id="foto" accept="image/*"
                               class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        <p class="text-xs text-gray-500 mt-1">Max 5MB - JPG, PNG (optioneel - laat leeg om huidige foto te behouden)</p>
                    </div>
                </div>

                <div class="mb-6">
                    <label for="zadel_beschrijving" class="block text-sm font-medium text-gray-700 mb-2">Beschrijving</label>
                    <textarea name="zadel_beschrijving" id="zadel_beschrijving" rows="3" 
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('zadel_beschrijving', $testzadel->zadel_beschrijving) }}</textarea>
                </div>

                <!-- Uitlening details -->
                <h3 class="text-xl font-bold mb-4 text-gray-900">Uitlening Details</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div>
                        <label for="uitleen_datum" class="block text-sm font-medium text-gray-700 mb-2">Uitleen Datum *</label>
                        <input type="date" name="uitleen_datum" id="uitleen_datum" value="{{ old('uitleen_datum', $testzadel->uitleen_datum->format('Y-m-d')) }}" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    </div>

                    <div>
                        <label for="verwachte_retour_datum" class="block text-sm font-medium text-gray-700 mb-2">Verwachte Retour Datum</label>
                        <input type="date" name="verwachte_retour_datum" id="verwachte_retour_datum" 
                               value="{{ old('verwachte_retour_datum', $testzadel->verwachte_retour_datum?->format('Y-m-d')) }}" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label for="werkelijke_retour_datum" class="block text-sm font-medium text-gray-700 mb-2">Werkelijke Retour Datum</label>
                        <input type="date" name="werkelijke_retour_datum" id="werkelijke_retour_datum" 
                               value="{{ old('werkelijke_retour_datum', $testzadel->werkelijke_retour_datum?->format('Y-m-d')) }}" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>

                <div class="mb-6">
                    <label for="opmerkingen" class="block text-sm font-medium text-gray-700 mb-2">Opmerkingen</label>
                    <textarea name="opmerkingen" id="opmerkingen" rows="3" 
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('opmerkingen', $testzadel->opmerkingen) }}</textarea>
                </div>

                <!-- Submit buttons -->
                <div class="flex justify-between">
                    <div class="flex gap-3">
                        <a href="{{ route('testzadels.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                            Annuleren
                        </a>
                        
                        @if($testzadel->status !== 'gearchiveerd')
                        <form method="POST" action="{{ route('testzadels.destroy', $testzadel) }}" class="inline" 
                              onsubmit="return confirm('Testzadel definitief verwijderen?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                                🗑️ Verwijderen
                            </button>
                        </form>
                        @endif
                    </div>
                    
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Wijzigingen Opslaan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-fill werkelijke retour datum wanneer status wijzigt naar teruggebracht
    const statusSelect = document.getElementById('status');
    const werkelijkeRetour = document.getElementById('werkelijke_retour_datum');
    
    statusSelect.addEventListener('change', function() {
        if (this.value === 'teruggebracht' && !werkelijkeRetour.value) {
            werkelijkeRetour.value = new Date().toISOString().split('T')[0];
        }
    });
});
</script>
@endsection