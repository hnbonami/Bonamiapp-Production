@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Testzadel Bewerken</h1>
        <a href="{{ route('testzadels.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
            ← Terug naar overzicht
        </a>
    </div>

    @if(session('success'))
        <div style="background:#d1fae5;color:#065f46;padding:1em;margin-bottom:1em;border-radius:5px;">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div style="background:#fef2f2;color:#dc2626;padding:1em;margin-bottom:1em;border-radius:5px;">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
        <div class="p-6">
            <form method="POST" action="{{ route('testzadels.update', $testzadel) }}">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Klant -->
                    <div>
                        <label for="klant_id" class="block text-sm font-medium text-gray-700">Klant *</label>
                        <select id="klant_id" name="klant_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach($klanten as $klant)
                                <option value="{{ $klant->id }}" {{ $testzadel->klant_id == $klant->id ? 'selected' : '' }}>
                                    {{ $klant->voornaam }} {{ $klant->naam }} ({{ $klant->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Bikefit (optioneel) -->
                    <div>
                        <label for="bikefit_id" class="block text-sm font-medium text-gray-700">Gekoppelde Bikefit (optioneel)</label>
                        <select id="bikefit_id" name="bikefit_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Geen bikefit gekoppeld</option>
                            @foreach($bikefits as $bikefit)
                                <option value="{{ $bikefit->id }}" {{ $testzadel->bikefit_id == $bikefit->id ? 'selected' : '' }}>
                                    {{ $bikefit->klant->voornaam }} {{ $bikefit->klant->naam }} - {{ $bikefit->datum->format('d/m/Y') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Onderdeel Type -->
                <div class="mt-6">
                    <label for="onderdeel_type" class="block text-sm font-medium text-gray-700">Onderdeel Type *</label>
                    <select id="onderdeel_type" name="onderdeel_type" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="testzadel" {{ $testzadel->onderdeel_type == 'testzadel' ? 'selected' : '' }}>Testzadel</option>
                        <option value="zooltjes" {{ $testzadel->onderdeel_type == 'zooltjes' ? 'selected' : '' }}>Zooltjes</option>
                        <option value="cleats" {{ $testzadel->onderdeel_type == 'cleats' ? 'selected' : '' }}>Cleats</option>
                        <option value="stuurpen" {{ $testzadel->onderdeel_type == 'stuurpen' ? 'selected' : '' }}>Stuurpen</option>
                    </select>
                </div>

                <!-- Status - NIEUW VELD -->
                <div class="mt-6">
                    <label for="status" class="block text-sm font-medium text-gray-700">Status *</label>
                    <select id="status" name="status" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="uitgeleend" {{ $testzadel->status == 'uitgeleend' ? 'selected' : '' }}>Uitgeleend</option>
                        <option value="teruggegeven" {{ $testzadel->status == 'teruggegeven' ? 'selected' : '' }}>Teruggegeven</option>
                        <option value="gearchiveerd" {{ $testzadel->status == 'gearchiveerd' ? 'selected' : '' }}>Gearchiveerd</option>
                    </select>
                    <p class="mt-1 text-xs text-gray-500">
                        Status wijzigen naar "Teruggegeven" zet automatisch de retour datum.
                    </p>
                </div>

                <!-- Automatisch Mailtje -->
                <div class="mt-6">
                    <label class="flex items-center">
                        <input type="checkbox" name="automatisch_mailtje" value="1" 
                               {{ $testzadel->automatisch_mailtje ? 'checked' : '' }}
                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-gray-600">Automatisch herinneringsmailtje versturen</span>
                    </label>
                    <p class="mt-1 text-xs text-gray-500">
                        Vink aan om automatische herinneringen te versturen voor dit onderdeel.
                    </p>
                </div>

                <!-- Zadel Informatie -->
                <div class="mt-8">
                    <h3 class="text-lg font-bold mb-4 text-gray-900">Zadel Informatie</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="zadel_merk" class="block text-sm font-medium text-gray-700">Merk</label>
                            <input type="text" id="zadel_merk" name="zadel_merk" value="{{ $testzadel->zadel_merk }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label for="zadel_model" class="block text-sm font-medium text-gray-700">Model</label>
                            <input type="text" id="zadel_model" name="zadel_model" value="{{ $testzadel->zadel_model }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label for="zadel_type" class="block text-sm font-medium text-gray-700">Type</label>
                            <input type="text" id="zadel_type" name="zadel_type" value="{{ $testzadel->zadel_type }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label for="zadel_breedte" class="block text-sm font-medium text-gray-700">Breedte (mm)</label>
                            <input type="number" id="zadel_breedte" name="zadel_breedte" value="{{ $testzadel->zadel_breedte }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>
                </div>

                <!-- Datums -->
                <div class="mt-8">
                    <h3 class="text-lg font-bold mb-4 text-gray-900">Datums</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="uitleen_datum" class="block text-sm font-medium text-gray-700">Uitleen Datum *</label>
                            <input type="date" id="uitleen_datum" name="uitleen_datum" 
                                   value="{{ $testzadel->uitleen_datum ? $testzadel->uitleen_datum->format('Y-m-d') : '' }}" required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label for="verwachte_retour_datum" class="block text-sm font-medium text-gray-700">Verwachte Retour Datum *</label>
                            <input type="date" id="verwachte_retour_datum" name="verwachte_retour_datum" 
                                   value="{{ $testzadel->verwachte_retour_datum ? $testzadel->verwachte_retour_datum->format('Y-m-d') : '' }}" required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>

                    <!-- Werkelijke retour datum (readonly, automatisch gezet) -->
                    @if($testzadel->werkelijke_retour_datum)
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700">Werkelijke Retour Datum</label>
                        <input type="text" readonly value="{{ $testzadel->werkelijke_retour_datum->format('d/m/Y H:i') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm">
                        <p class="mt-1 text-xs text-gray-500">
                            Automatisch gezet bij status wijziging naar "Teruggegeven"
                        </p>
                    </div>
                    @endif
                </div>

                <!-- Opmerkingen -->
                <div class="mt-8">
                    <label for="opmerkingen" class="block text-sm font-medium text-gray-700">Opmerkingen</label>
                    <textarea id="opmerkingen" name="opmerkingen" rows="3"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ $testzadel->opmerkingen }}</textarea>
                </div>

                <!-- Submit Buttons -->
                <div class="mt-8 flex justify-end space-x-3">
                    <a href="{{ route('testzadels.index') }}" 
                       class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                        Annuleren
                    </a>
                    <button type="submit" 
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Bijwerken
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

