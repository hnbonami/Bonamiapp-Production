@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    {{-- Header --}}
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Dienst Bewerken</h1>
            <p class="text-sm text-gray-600 mt-1">{{ $dienst->naam }}</p>
        </div>
        <a href="{{ route('admin.prestaties.diensten.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
            ← Terug
        </a>
    </div>

    {{-- Formulier --}}
    <div class="bg-white rounded-lg shadow p-6 max-w-2xl">
        <form action="{{ route('admin.prestaties.diensten.update', $dienst) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Naam *</label>
                <input type="text" name="naam" value="{{ old('naam', $dienst->naam) }}" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 @error('naam') border-red-500 @enderror">
                @error('naam')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Omschrijving</label>
                <textarea name="omschrijving" rows="3"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">{{ old('omschrijving', $dienst->omschrijving) }}</textarea>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Prijs (incl. BTW) *</label>
                <input type="number" name="prijs" step="0.01" value="{{ old('prijs', $dienst->standaard_prijs) }}" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 @error('prijs') border-red-500 @enderror">
                @error('prijs')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
                <p class="text-xs text-gray-500 mt-1">
                    Excl. BTW: €{{ number_format($dienst->prijs_excl_btw ?? $dienst->standaard_prijs / 1.21, 2, ',', '.') }}
                </p>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Commissie Percentage *</label>
                <input type="number" name="commissie_percentage" step="0.01" min="0" max="100" value="{{ old('commissie_percentage', $dienst->commissie_percentage) }}" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 @error('commissie_percentage') border-red-500 @enderror">
                @error('commissie_percentage')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label class="flex items-center">
                    <input type="checkbox" name="actief" value="1" {{ old('actief', $dienst->is_actief) ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600">
                    <span class="ml-2 text-sm text-gray-700">Actief</span>
                </label>
            </div>

            <div class="flex gap-2">
                <a href="{{ route('admin.prestaties.diensten.index') }}" class="flex-1 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-center">
                    Annuleren
                </a>
                <button type="submit" class="flex-1 px-4 py-2 rounded-lg font-medium hover:opacity-90 text-gray-900" style="background-color: #c8e1eb;">
                    Bijwerken
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
