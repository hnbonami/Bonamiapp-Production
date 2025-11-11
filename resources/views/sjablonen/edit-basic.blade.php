@extends('layouts.app')

@section('title', 'Sjabloon Informatie Bewerken')

@section('content')
    <div class="max-w-2xl mx-auto py-6">
        <!-- Header -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Sjabloon Informatie Bewerken</h1>
                        <p class="text-sm text-gray-600">Bewerk de basis informatie van je sjabloon</p>
                    </div>
                    <a href="{{ route('sjablonen.index') }}" 
                       class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium text-gray-700 hover:text-gray-900 transition-all duration-200"
                       style="background-color: #c8e1eb; border: 1px solid #a5c9d6;">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Terug naar Overzicht
                    </a>
                </div>
            </div>
        </div>

        <!-- Success Message -->
        @if(session('success'))
            <div class="mb-6">
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    {{ session('success') }}
                </div>
            </div>
        @endif

        <!-- Form -->
        <div class="bg-white shadow rounded-lg">
            <form method="POST" action="{{ route('sjablonen.update-basic', $sjabloon->id) }}" class="p-6">
                @csrf

                <!-- Naam -->
                <div class="mb-6">
                    <label for="naam" class="block text-sm font-medium text-gray-700 mb-2">
                        Sjabloon Naam *
                    </label>
                    <input type="text" 
                           id="naam" 
                           name="naam" 
                           value="{{ old('naam', $sjabloon->naam) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                           required>
                    @error('naam')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Categorie -->
                <div class="mb-6">
                    <label for="categorie" class="block text-sm font-medium text-gray-700 mb-2">
                        Categorie *
                    </label>
                    <select id="categorie" 
                            name="categorie"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                            required>
                        <option value="">Selecteer een categorie</option>
                        <option value="bikefit" {{ old('categorie', $sjabloon->categorie) == 'bikefit' ? 'selected' : '' }}>Bikefit</option>
                        <option value="inspanningstest" {{ old('categorie', $sjabloon->categorie) == 'inspanningstest' ? 'selected' : '' }}>Inspanningstest</option>
                        <option value="algemeen" {{ old('categorie', $sjabloon->categorie) == 'algemeen' ? 'selected' : '' }}>Algemeen</option>
                        <option value="rapport" {{ old('categorie', $sjabloon->categorie) == 'rapport' ? 'selected' : '' }}>Rapport</option>
                    </select>
                    @error('categorie')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Test Type -->
                <div class="mb-6">
                    <label for="testtype" class="block text-sm font-medium text-gray-700 mb-2">
                        Test Type (optioneel)
                    </label>
                    <input type="text" 
                           id="testtype" 
                           name="testtype" 
                           value="{{ old('testtype', $sjabloon->testtype) }}"
                           placeholder="Bijv. VO2max, Lactaat, etc."
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    @error('testtype')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Beschrijving -->
                            <!-- Beschrijving -->
            <div class="mt-6">
                <label for="beschrijving" class="block text-sm font-medium text-gray-700">Beschrijving (optioneel)</label>
                <textarea name="beschrijving" 
                          id="beschrijving" 
                          rows="4"
                          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">{{ old('beschrijving', $sjabloon->beschrijving) }}</textarea>
                @error('beschrijving')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            @if(auth()->user()->role === 'superadmin')
                <!-- Sjabloon Actief/Inactief Toggle (ONAFHANKELIJK systeem) -->
                <div class="mt-6 p-4 bg-blue-50 border-2 border-blue-200 rounded-lg">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" 
                               name="is_actief_checkbox" 
                               id="is_actief_checkbox" 
                               value="1"
                               {{ old('is_actief_checkbox', $sjabloon->is_actief == 1) ? 'checked' : '' }}
                               class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <span class="ml-3">
                            <span class="text-sm font-semibold text-blue-900">✓ Sjabloon Actief</span>
                            <span class="block text-xs text-blue-700 mt-1">
                                Inactieve sjablonen kunnen niet gebruikt worden voor rapporten (maar blijven zichtbaar in de lijst).
                            </span>
                        </span>
                    </label>
                </div>

                <!-- Superadmin: App Sjabloon Toggle (ONAFHANKELIJK systeem) -->
                <div class="mt-6 p-4 bg-purple-50 border-2 border-purple-200 rounded-lg">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" 
                               name="is_shared_template" 
                               id="is_shared_template" 
                               value="1"
                               {{ old('is_shared_template', $sjabloon->organisatie_id == 1 ? 1 : 0) ? 'checked' : '' }}
                               class="w-5 h-5 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                        <span class="ml-3">
                            <span class="text-sm font-semibold text-purple-900">✨ Maak dit een App Sjabloon (Shared)</span>
                            <span class="block text-xs text-purple-700 mt-1">
                                App sjablonen (organisatie_id = 1) zijn zichtbaar voor alle organisaties. Privé sjablonen (jouw organisatie) zijn alleen voor jou.
                            </span>
                        </span>
                    </label>
                </div>
            @else
                <!-- Normale organisaties: Gewone Actief/Inactief Toggle -->
                <div class="mt-6 p-4 bg-blue-50 border-2 border-blue-200 rounded-lg">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" 
                               name="is_actief_checkbox" 
                               id="is_actief_checkbox" 
                               value="1"
                               {{ old('is_actief_checkbox', $sjabloon->is_actief == 1 ? 1 : 0) ? 'checked' : '' }}
                               class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <span class="ml-3">
                            <span class="text-sm font-semibold text-blue-900">✓ Sjabloon Actief</span>
                            <span class="block text-xs text-blue-700 mt-1">
                                Inactieve sjablonen worden niet getoond in de sjablonen lijst en kunnen niet gebruikt worden.
                            </span>
                        </span>
                    </label>
                </div>
            @endif

                <!-- Buttons -->
                <div class="flex gap-4 pt-4">
                    <button type="submit" 
                            class="inline-flex items-center px-6 py-3 rounded-lg text-sm font-medium text-gray-700 hover:text-gray-900 transition-all duration-200"
                            style="background-color: #c8e1eb; border: 1px solid #a5c9d6;">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Opslaan en Inhoud Bewerken
                    </button>
                    
                    <a href="{{ route('sjablonen.index') }}" 
                       class="inline-flex items-center px-6 py-3 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-all duration-200">
                        Annuleren
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection