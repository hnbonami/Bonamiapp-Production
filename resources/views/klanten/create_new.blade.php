@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
        <div class="p-6">
            <h1 class="text-2xl font-bold text-gray-900 mb-6">Nieuwe klant toevoegen</h1>
            
            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-md">
                    <div class="text-red-600">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('klanten.store') }}" enctype="multipart/form-data">
                @csrf
                
                <!-- Persoonlijke Informatie -->
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Persoonlijke Informatie</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="voornaam" class="block text-sm font-medium text-gray-700">Voornaam *</label>
                        <input type="text" name="voornaam" id="voornaam" value="{{ old('voornaam') }}" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    
                    <div>
                        <label for="naam" class="block text-sm font-medium text-gray-700">Naam *</label>
                        <input type="text" name="naam" id="naam" value="{{ old('naam') }}" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">E-mailadres</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    
                    <div>
                        <label for="telefoonnummer" class="block text-sm font-medium text-gray-700">Telefoonnummer</label>
                        <input type="text" name="telefoonnummer" id="telefoonnummer" value="{{ old('telefoonnummer') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    
                    <div>
                        <label for="geboortedatum" class="block text-sm font-medium text-gray-700">Geboortedatum</label>
                        <input type="date" name="geboortedatum" id="geboortedatum" value="{{ old('geboortedatum') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    
                    <div>
                        <label for="geslacht" class="block text-sm font-medium text-gray-700">Geslacht *</label>
                        <select name="geslacht" id="geslacht" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Selecteer...</option>
                            <option value="man" {{ old('geslacht') == 'man' ? 'selected' : '' }}>Man</option>
                            <option value="vrouw" {{ old('geslacht') == 'vrouw' ? 'selected' : '' }}>Vrouw</option>
                            <option value="anders" {{ old('geslacht') == 'anders' ? 'selected' : '' }}>Anders</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">Status *</label>
                        <select name="status" id="status" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Selecteer...</option>
                            <option value="actief" {{ old('status') == 'actief' ? 'selected' : '' }}>Actief</option>
                            <option value="inactief" {{ old('status') == 'inactief' ? 'selected' : '' }}>Inactief</option>
                            <option value="potentieel" {{ old('status') == 'potentieel' ? 'selected' : '' }}>Potentieel</option>
                        </select>
                    </div>
                </div>

                <!-- Adresgegevens -->
                <h2 class="text-xl font-semibold text-gray-800 mb-4 mt-8">Adresgegevens</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="straatnaam" class="block text-sm font-medium text-gray-700">Straatnaam</label>
                        <input type="text" name="straatnaam" id="straatnaam" value="{{ old('straatnaam') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    
                    <div>
                        <label for="huisnummer" class="block text-sm font-medium text-gray-700">Huisnummer</label>
                        <input type="text" name="huisnummer" id="huisnummer" value="{{ old('huisnummer') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    
                    <div>
                        <label for="postcode" class="block text-sm font-medium text-gray-700">Postcode</label>
                        <input type="text" name="postcode" id="postcode" value="{{ old('postcode') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    
                    <div>
                        <label for="stad" class="block text-sm font-medium text-gray-700">Stad</label>
                        <input type="text" name="stad" id="stad" value="{{ old('stad') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>

                <!-- Doorverwijzing Sectie -->
                <div class="mt-8 p-6 bg-blue-50 rounded-lg border border-blue-200">
                    <h3 class="text-lg font-semibold text-blue-900 mb-4">🤝 Hoe bent u bij ons terechtgekomen?</h3>
                    
                    <div class="mb-4">
                        <label for="doorverwijzing_type" class="block text-sm font-medium text-gray-700 mb-2">
                            Doorverwijzing type
                        </label>
                        <select name="doorverwijzing_type" id="doorverwijzing_type" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Selecteer...</option>
                            @foreach(\App\Models\Klant::getDoorverwijzingTypes() as $key => $label)
                                <option value="{{ $key }}" {{ old('doorverwijzing_type') == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Klant selectie voor mond-aan-mond -->
                    <div id="referral-customer-section" class="mb-4" style="display: none;">
                        <label for="doorverwijzing_klant_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Van welke klant komt de doorverwijzing?
                        </label>
                        <select name="doorverwijzing_klant_id" id="doorverwijzing_klant_id" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Selecteer een klant...</option>
                            @foreach(\App\Models\Klant::orderBy('voornaam')->orderBy('naam')->get() as $bestaandeKlant)
                                <option value="{{ $bestaandeKlant->id }}" {{ old('doorverwijzing_klant_id') == $bestaandeKlant->id ? 'selected' : '' }}>
                                    {{ $bestaandeKlant->voornaam }} {{ $bestaandeKlant->naam }} ({{ $bestaandeKlant->email }})
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-sm text-blue-600">
                            💡 Deze klant ontvangt automatisch een bedankingsmail voor de doorverwijzing
                        </p>
                    </div>
                    
                    <!-- Toelichting veld voor overige opties -->
                    <div id="other-referral-section" class="mb-4" style="display: none;">
                        <label for="hoe_bent_u_bij_ons_terechtgekomen" class="block text-sm font-medium text-gray-700 mb-2">
                            Toelichting (optioneel)
                        </label>
                        <input type="text" name="hoe_bent_u_bij_ons_terechtgekomen" id="hoe_bent_u_bij_ons_terechtgekomen" 
                               value="{{ old('hoe_bent_u_bij_ons_terechtgekomen') }}" 
                               placeholder="Bijv. welke website, welk evenement, via welk medium, etc."
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>

                <!-- Profielfoto -->
                <div class="mt-8">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Profielfoto</h3>
                    <div class="flex items-center space-x-4">
                        <div class="w-20 h-20 bg-gray-200 rounded-full flex items-center justify-center">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <div>
                            <input type="file" name="profielfoto" id="profielfoto" accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            <p class="text-xs text-gray-500 mt-1">JPG, PNG, GIF tot 2MB</p>
                        </div>
                    </div>
                </div>

                <!-- Submit buttons -->
                <div class="flex justify-between mt-8">
                    <a href="{{ route('klanten.index') }}" class="px-6 py-3 bg-gray-300 text-black rounded font-semibold hover:bg-gray-400">
                        Terug
                    </a>
                    <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded font-semibold hover:bg-blue-700">
                        Klant Aanmaken
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Doorverwijzing type toggle functionaliteit
    const doorverwijzingSelect = document.querySelector('select[name="doorverwijzing_type"]');
    const referralCustomerSection = document.getElementById('referral-customer-section');
    const otherReferralSection = document.getElementById('other-referral-section');
    
    if (doorverwijzingSelect) {
        function toggleReferralSections() {
            if (doorverwijzingSelect.value === 'mond_aan_mond') {
                if (referralCustomerSection) referralCustomerSection.style.display = 'block';
                if (otherReferralSection) otherReferralSection.style.display = 'none';
            } else if (doorverwijzingSelect.value && doorverwijzingSelect.value !== '') {
                if (referralCustomerSection) referralCustomerSection.style.display = 'none';
                if (otherReferralSection) otherReferralSection.style.display = 'block';
            } else {
                if (referralCustomerSection) referralCustomerSection.style.display = 'none';
                if (otherReferralSection) otherReferralSection.style.display = 'none';
            }
            
            // Reset selecties wanneer we van type switchen
            if (doorverwijzingSelect.value !== 'mond_aan_mond') {
                const customerSelect = document.querySelector('select[name="doorverwijzing_klant_id"]');
                if (customerSelect) customerSelect.value = '';
            }
            if (doorverwijzingSelect.value === 'mond_aan_mond') {
                const textInput = document.querySelector('input[name="hoe_bent_u_bij_ons_terechtgekomen"]');
                if (textInput) textInput.value = '';
            }
        }
        
        doorverwijzingSelect.addEventListener('change', toggleReferralSections);
        toggleReferralSections(); // Initial state
    }
});
</script>
@endsection