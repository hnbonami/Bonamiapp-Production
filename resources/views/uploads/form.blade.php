@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto p-6">
    <h2 class="text-xl font-bold mb-4">Document Uploaden</h2>
    <form action="/uploads" method="post" enctype="multipart/form-data">
        @csrf
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Selecteer bestand *</label>
            <input type="file" name="file" required class="mt-1 block w-full text-sm text-gray-500
                file:mr-4 file:py-2 file:px-4
                file:rounded file:border-0
                file:text-sm file:font-semibold
                file:bg-blue-50 file:text-blue-700
                hover:file:bg-blue-100" />
            @error('file')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Naam (optioneel)</label>
            <input type="text" name="naam" class="mt-1 w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Document naam..." />
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Beschrijving (optioneel)</label>
            <textarea name="beschrijving" rows="3" class="mt-1 w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Korte beschrijving..."></textarea>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Toegang *</label>
            <select name="toegang" required class="mt-1 w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="alle_medewerkers" selected>Alle medewerkers</option>
                <option value="alleen_mezelf">Alleen mezelf</option>
                <option value="klant">Klant + mezelf</option>
                <option value="iedereen">Iedereen</option>
            </select>
            <p class="text-xs text-gray-500 mt-1">
                <strong>Alleen mezelf:</strong> Alleen jij hebt toegang<br>
                <strong>Klant + mezelf:</strong> De gekoppelde klant en jij<br>
                <strong>Alle medewerkers:</strong> Alle medewerkers en admins<br>
                <strong>Iedereen:</strong> Publiek toegankelijk
            </p>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Koppel aan bikefit id (optioneel)</label>
            <input type="text" name="bikefit_id" class="mt-1 w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="bv. 123" />
        </div>

        <div class="mb-4">
            <label class="inline-flex items-center">
                <input type="checkbox" name="is_cover" value="1" class="mr-2 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" />
                <span class="text-sm text-gray-700">Markeer als cover afbeelding</span>
            </label>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t">
            <button type="button" onclick="window.history.back()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition">
                Annuleren
            </button>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                Uploaden
            </button>
        </div>
    </form>
</div>
@endsection
