@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    {{-- Debug Info - verwijder later --}}
    <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-4">
        <strong>Debug Info:</strong> Organisatie ID van ingelogde gebruiker: {{ auth()->user()->organisatie_id }}
    </div>

    {{-- Header --}}
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Nieuwe Dienst</h1>
            <p class="text-sm text-gray-600 mt-1">Voeg een nieuwe dienst toe met commissiepercentage</p>
        </div>
        <a href="{{ route('admin.prestaties.diensten.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
            ← Terug
        </a>
    </div>

    {{-- Formulier --}}
    <div class="bg-white rounded-lg shadow p-6 max-w-2xl">
        <form action="{{ route('admin.prestaties.diensten.store') }}" method="POST">
            @csrf

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Naam *</label>
                <input type="text" name="naam" value="{{ old('naam') }}" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 @error('naam') border-red-500 @enderror">
                @error('naam')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Omschrijving</label>
                <textarea name="omschrijving" rows="3"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">{{ old('omschrijving') }}</textarea>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Prijs (incl. BTW) *</label>
                <input type="number" name="prijs" step="0.01" value="{{ old('prijs') }}" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 @error('prijs') border-red-500 @enderror">
                @error('prijs')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
                <p class="text-xs text-gray-500 mt-1">BTW wordt automatisch berekend (21%)</p>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Commissie Percentage *</label>
                <input type="number" name="commissie_percentage" step="0.01" min="0" max="100" value="{{ old('commissie_percentage') }}" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 @error('commissie_percentage') border-red-500 @enderror">
                @error('commissie_percentage')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label class="flex items-center">
                    <input type="checkbox" name="actief" value="1" {{ old('actief', true) ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600">
                    <span class="ml-2 text-sm text-gray-700">Actief</span>
                </label>
            </div>

            <div class="flex gap-2">
                <a href="{{ route('admin.prestaties.diensten.index') }}" class="flex-1 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-center">
                    Annuleren
                </a>
                <button type="submit" class="flex-1 px-4 py-2 rounded-lg font-medium hover:opacity-90 text-gray-900" style="background-color: #c8e1eb;">
                    Opslaan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
