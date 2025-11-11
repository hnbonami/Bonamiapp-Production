@extends('layouts.app')

@section('title', 'Sjabloon Bewerken - ' . $sjabloon->naam)

@section('content')
    <!-- Header -->
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-6 py-4">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Sjabloon Bewerken</h1>
                    <p class="text-sm text-gray-600">{{ $sjabloon->naam }}</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('sjablonen.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Terug naar Overzicht
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="bg-white shadow rounded-lg p-6">
        <form method="POST" action="{{ route('sjablonen.update-basic', $sjabloon->id) }}">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Naam -->
                <div>
                    <label for="naam" class="block text-sm font-medium text-gray-700">Sjabloon Naam</label>
                    <input type="text" 
                           name="naam" 
                           id="naam" 
                           value="{{ old('naam', $sjabloon->naam) }}"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                           required>
                    @error('naam')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Categorie -->
                <div>
                    <label for="categorie" class="block text-sm font-medium text-gray-700">Categorie</label>
                    <select name="categorie" 
                            id="categorie" 
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                            required>
                        <option value="">Selecteer categorie</option>
                        <option value="bikefit" {{ old('categorie', $sjabloon->categorie) == 'bikefit' ? 'selected' : '' }}>Bikefit</option>
                        <option value="inspanningstest" {{ old('categorie', $sjabloon->categorie) == 'inspanningstest' ? 'selected' : '' }}>Inspanningstest</option>
                        <option value="rapport" {{ old('categorie', $sjabloon->categorie) == 'rapport' ? 'selected' : '' }}>Rapport</option>
                        <option value="algemeen" {{ old('categorie', $sjabloon->categorie) == 'algemeen' ? 'selected' : '' }}>Algemeen</option>
                    </select>
                    @error('categorie')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Test Type -->
                <div>
                    <label for="testtype" class="block text-sm font-medium text-gray-700">Test Type (optioneel)</label>
                    <select name="testtype" 
                            id="testtype" 
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Selecteer test type (optioneel)</option>
                        <option value="professionele bikefit" {{ old('testtype', $sjabloon->testtype) == 'professionele bikefit' ? 'selected' : '' }}>Professionele Bikefit</option>
                        <option value="standaard bikefit" {{ old('testtype', $sjabloon->testtype) == 'standaard bikefit' ? 'selected' : '' }}>Standaard Bikefit</option>
                        <option value="Inspanningstest Fietsen" {{ old('testtype', $sjabloon->testtype) == 'Inspanningstest Fietsen' ? 'selected' : '' }}>Inspanningstest Fietsen</option>
                        <option value="Inspanningstest Lopen" {{ old('testtype', $sjabloon->testtype) == 'Inspanningstest Lopen' ? 'selected' : '' }}>Inspanningstest Lopen</option>
                    </select>
                    @error('testtype')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Beschrijving -->
            <div class="mt-6">
                <label for="beschrijving" class="block text-sm font-medium text-gray-700">Beschrijving (optioneel)</label>
                <textarea name="beschrijving" 
                          id="beschrijving"
                          rows="3"
                          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">{{ old('beschrijving', $sjabloon->beschrijving) }}</textarea>
                @error('beschrijving')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            @if(auth()->user()->role === 'superadmin')
                <!-- Superadmin: App Sjabloon Toggle -->
                <div class="mb-4">
                    <label class="flex items-center">
                        {{-- ✅ HIDDEN FIELD TRICK: Checkbox uitgevinkt = "0" versturen, aangevinkt = "1" overschrijft --}}
                        <input type="hidden" name="is_shared_template" value="0">
                        <input type="checkbox" 
                               name="is_shared_template" 
                               value="1" 
                               {{ old('is_shared_template', $sjabloon->is_app_sjabloon ?? 0) == 1 ? 'checked' : '' }}
                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <span class="ml-2 text-sm text-gray-600">
                            <strong>App Sjabloon</strong> - Zichtbaar voor ALLE organisaties
                        </span>
                    </label>
                    <p class="mt-1 text-xs text-gray-500">
                        ✅ Aangevinkt = App Sjabloon (alle organisaties kunnen dit sjabloon gebruiken)<br>
                        ❌ Uitgevinkt = Privé Sjabloon (alleen Performance Pulse kan dit sjabloon gebruiken)
                    </p>
                </div>
            @endif
            
            <!-- Submit Buttons -->
            <div class="mt-6 flex justify-end space-x-3">
                <a href="{{ route('sjablonen.index') }}" 
                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Annuleren
                </a>
                <button type="submit" 
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Opslaan
                </button>
            </div>
        </form>
    </div>
@endsection