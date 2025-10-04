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
                    <input type="text" 
                           name="testtype" 
                           id="testtype" 
                           value="{{ old('testtype') }}"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
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
                          rows="4"
                          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">{{ old('beschrijving') }}</textarea>
                @error('beschrijving')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

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