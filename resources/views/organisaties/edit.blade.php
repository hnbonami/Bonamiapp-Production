@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('organisaties.show', $organisatie) }}" class="inline-flex items-center text-gray-600 hover:text-gray-900">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Terug naar details
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Organisatie Bewerken</h1>

        <form action="{{ route('organisaties.update', $organisatie) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Naam -->
                <div class="md:col-span-2">
                    <label for="naam" class="block text-sm font-semibold text-gray-700 mb-2">Organisatie Naam *</label>
                    <input type="text" name="naam" id="naam" required value="{{ old('naam', $organisatie->naam) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    @error('naam')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Email *</label>
                    <input type="email" name="email" id="email" required value="{{ old('email', $organisatie->email) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Telefoon -->
                <div>
                    <label for="telefoon" class="block text-sm font-semibold text-gray-700 mb-2">Telefoon</label>
                    <input type="text" name="telefoon" id="telefoon" value="{{ old('telefoon', $organisatie->telefoon) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <!-- Adres -->
                <div class="md:col-span-2">
                    <label for="adres" class="block text-sm font-semibold text-gray-700 mb-2">Adres</label>
                    <input type="text" name="adres" id="adres" value="{{ old('adres', $organisatie->adres) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <!-- Postcode -->
                <div>
                    <label for="postcode" class="block text-sm font-semibold text-gray-700 mb-2">Postcode</label>
                    <input type="text" name="postcode" id="postcode" value="{{ old('postcode', $organisatie->postcode) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <!-- Plaats -->
                <div>
                    <label for="plaats" class="block text-sm font-semibold text-gray-700 mb-2">Plaats</label>
                    <input type="text" name="plaats" id="plaats" value="{{ old('plaats', $organisatie->plaats) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <!-- BTW Nummer -->
                <div>
                    <label for="btw_nummer" class="block text-sm font-semibold text-gray-700 mb-2">BTW Nummer</label>
                    <input type="text" name="btw_nummer" id="btw_nummer" value="{{ old('btw_nummer', $organisatie->btw_nummer) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <!-- Status -->
                <div>
                    <label for="status" class="block text-sm font-semibold text-gray-700 mb-2">Status *</label>
                    <select name="status" id="status" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="trial" {{ old('status', $organisatie->status) === 'trial' ? 'selected' : '' }}>Trial</option>
                        <option value="actief" {{ old('status', $organisatie->status) === 'actief' ? 'selected' : '' }}>Actief</option>
                        <option value="inactief" {{ old('status', $organisatie->status) === 'inactief' ? 'selected' : '' }}>Inactief</option>
                    </select>
                    @error('status')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Trial Eindigt Op -->
                <div>
                    <label for="trial_eindigt_op" class="block text-sm font-semibold text-gray-700 mb-2">Trial Eindigt Op</label>
                    <input type="date" name="trial_eindigt_op" id="trial_eindigt_op" value="{{ old('trial_eindigt_op', $organisatie->trial_eindigt_op?->format('Y-m-d')) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <!-- Maandelijkse Prijs -->
                <div>
                    <label for="maandelijkse_prijs" class="block text-sm font-semibold text-gray-700 mb-2">Maandelijkse Prijs (€)</label>
                    <input type="number" step="0.01" name="maandelijkse_prijs" id="maandelijkse_prijs" value="{{ old('maandelijkse_prijs', $organisatie->maandelijkse_prijs) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <!-- Notities -->
                <div class="md:col-span-2">
                    <label for="notities" class="block text-sm font-semibold text-gray-700 mb-2">Notities</label>
                    <textarea name="notities" id="notities" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('notities', $organisatie->notities) }}</textarea>
                </div>
            </div>

            <!-- Buttons -->
            <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-200">
                <a href="{{ route('organisaties.show', $organisatie) }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 font-semibold hover:bg-gray-50 transition">
                    Annuleren
                </a>
                <button type="submit" class="px-6 py-2 text-gray-900 font-semibold rounded-lg transition" style="background-color: #c8e1eb;">
                    Opslaan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
