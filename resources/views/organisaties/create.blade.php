@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('organisaties.index') }}" class="inline-flex items-center text-gray-600 hover:text-gray-900">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Terug naar overzicht
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Nieuwe Organisatie Aanmaken</h1>

        <form action="{{ route('organisaties.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Naam -->
                <div class="md:col-span-2">
                    <label for="naam" class="block text-sm font-semibold text-gray-700 mb-2">Organisatie Naam *</label>
                    <input type="text" name="naam" id="naam" required value="{{ old('naam') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Bijv. Sportcoaching Pro">
                    @error('naam')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Email *</label>
                    <input type="email" name="email" id="email" required value="{{ old('email') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="info@organisatie.nl">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Telefoon -->
                <div>
                    <label for="telefoon" class="block text-sm font-semibold text-gray-700 mb-2">Telefoon</label>
                    <input type="text" name="telefoon" id="telefoon" value="{{ old('telefoon') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="+32 123 45 67 89">
                </div>

                <!-- Adres -->
                <div class="md:col-span-2">
                    <label for="adres" class="block text-sm font-semibold text-gray-700 mb-2">Adres</label>
                    <input type="text" name="adres" id="adres" value="{{ old('adres') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Straatnaam 123">
                </div>

                <!-- Postcode -->
                <div>
                    <label for="postcode" class="block text-sm font-semibold text-gray-700 mb-2">Postcode</label>
                    <input type="text" name="postcode" id="postcode" value="{{ old('postcode') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="1000">
                </div>

                <!-- Plaats -->
                <div>
                    <label for="plaats" class="block text-sm font-semibold text-gray-700 mb-2">Plaats</label>
                    <input type="text" name="plaats" id="plaats" value="{{ old('plaats') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Brussel">
                </div>

                <!-- BTW Nummer -->
                <div>
                    <label for="btw_nummer" class="block text-sm font-semibold text-gray-700 mb-2">BTW Nummer</label>
                    <input type="text" name="btw_nummer" id="btw_nummer" value="{{ old('btw_nummer') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="BE0123456789">
                </div>

                <!-- Status -->
                <div>
                    <label for="status" class="block text-sm font-semibold text-gray-700 mb-2">Status *</label>
                    <select name="status" id="status" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="trial" {{ old('status') === 'trial' ? 'selected' : '' }}>Trial</option>
                        <option value="actief" {{ old('status') === 'actief' ? 'selected' : '' }}>Actief</option>
                        <option value="inactief" {{ old('status') === 'inactief' ? 'selected' : '' }}>Inactief</option>
                    </select>
                    @error('status')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Trial Eindigt Op -->
                <div>
                    <label for="trial_eindigt_op" class="block text-sm font-semibold text-gray-700 mb-2">Trial Eindigt Op</label>
                    <input type="date" name="trial_eindigt_op" id="trial_eindigt_op" value="{{ old('trial_eindigt_op') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <!-- Maandelijkse Prijs -->
                <div>
                    <label for="maandelijkse_prijs" class="block text-sm font-semibold text-gray-700 mb-2">Maandelijkse Prijs (€)</label>
                    <input type="number" step="0.01" name="maandelijkse_prijs" id="maandelijkse_prijs" value="{{ old('maandelijkse_prijs', 0) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="99.00">
                </div>

                <!-- Notities -->
                <div class="md:col-span-2">
                    <label for="notities" class="block text-sm font-semibold text-gray-700 mb-2">Notities</label>
                    <textarea name="notities" id="notities" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Eventuele opmerkingen...">{{ old('notities') }}</textarea>
                </div>
            </div>

            <!-- Buttons -->
            <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-200">
                <a href="{{ route('organisaties.index') }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 font-semibold hover:bg-gray-50 transition">
                    Annuleren
                </a>
                <button type="submit" class="px-6 py-2 text-gray-900 font-semibold rounded-lg transition" style="background-color: #c8e1eb;">
                    Organisatie Aanmaken
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
