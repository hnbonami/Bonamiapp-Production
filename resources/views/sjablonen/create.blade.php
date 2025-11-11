@extends('layouts.app')

@section('title', 'Nieuw Sjabloon - Sjablonen Manager')

@section('content')
    <!-- Sjablonen Button Enhancements -->
    <link rel="stylesheet" href="/css/sjablonen-editor-buttons.css">

    <!-- Header -->
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-6 py-4">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Nieuw Sjabloon</h1>
                    <p class="text-sm text-gray-600">Maak een nieuw sjabloon aan</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('sjablonen.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Terug naar Overzicht
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if(auth()->user()->role === 'superadmin')
        <!-- Superadmin Info Box -->
        <div class="bg-gradient-to-r from-purple-50 to-pink-50 border-l-4 border-purple-500 p-4 mb-6 rounded-lg">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-purple-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-purple-700 font-semibold">
                        💡 Superadmin Tip: Als je dit sjabloon wilt delen met alle organisaties (standaard sjabloon), kies dan organisatie ID 1 of laat organisatie_id leeg.
                    </p>
                    <p class="text-xs text-purple-600 mt-1">
                        Standaard sjablonen krijgen een speciale badge en zijn zichtbaar voor alle organisaties zonder "rapporten_opmaken" feature.
                    </p>
                </div>
            </div>
        </div>
    @endif

    <!-- Main Content -->
    <div class="bg-white shadow rounded-lg p-6">
        <form method="POST" action="{{ route('sjablonen.store') }}">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Naam -->
                <div>
                    <label for="naam" class="block text-sm font-medium text-gray-700">Sjabloon Naam</label>
                    <input type="text" 
                           name="naam" 
                           id="naam" 
                           value="{{ old('naam') }}"
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
                        <option value="bikefit" {{ old('categorie') == 'bikefit' ? 'selected' : '' }}>Bikefit</option>
                        <option value="inspanningstest" {{ old('categorie') == 'inspanningstest' ? 'selected' : '' }}>Inspanningstest</option>
                        <option value="rapport" {{ old('categorie') == 'rapport' ? 'selected' : '' }}>Rapport</option>
                        <option value="algemeen" {{ old('categorie') == 'algemeen' ? 'selected' : '' }}>Algemeen</option>
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
                        <option value="professionele bikefit" {{ old('testtype') == 'professionele bikefit' ? 'selected' : '' }}>Professionele Bikefit</option>
                        <option value="standaard bikefit" {{ old('testtype') == 'standaard bikefit' ? 'selected' : '' }}>Standaard Bikefit</option>
                        <option value="Inspanningstest Fietsen" {{ old('testtype') == 'Inspanningstest Fietsen' ? 'selected' : '' }}>Inspanningstest Fietsen</option>
                        <option value="Inspanningstest Lopen" {{ old('testtype') == 'Inspanningstest Lopen' ? 'selected' : '' }}>Inspanningstest Lopen</option>
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Belangrijk: Dit koppelt het sjabloon aan het juiste type verslag</p>
                    @error('testtype')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Beschrijving -->
                        <!-- Beschrijving -->
            <div class="mt-6">
                <label for="beschrijving" class="block text-sm font-medium text-gray-700">Beschrijving (optioneel)</label>
                <textarea name="beschrijving" 
                          id="beschrijving" 
                          rows="3" 
                          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">{{ old('beschrijving') }}</textarea>
                @error('beschrijving')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            @if(auth()->user()->role === 'superadmin')
                <!-- Superadmin: App Sjabloon Toggle -->
                <div class="mt-6 p-4 bg-purple-50 border-2 border-purple-200 rounded-lg">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" 
                               name="is_shared_template" 
                               id="is_shared_template" 
                               value="1"
                               class="w-5 h-5 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                        <span class="ml-3">
                            <span class="text-sm font-semibold text-purple-900">✨ Maak dit een App Sjabloon</span>
                            <span class="block text-xs text-purple-700 mt-1">
                                App sjablonen zijn zichtbaar voor alle organisaties. Als je dit NIET aanvinkt, wordt het een privé sjabloon voor jouw organisatie.
                            </span>
                        </span>
                    </label>
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
                    Sjabloon Aanmaken
                </button>
            </div>
        </form>
    </div>
@endsection