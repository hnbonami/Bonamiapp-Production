@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Nieuwe medewerker toevoegen</h1>
    </div>

    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
        <div class="p-6">
            @if ($errors->any())
                <div class="mb-4 text-red-600">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            <form method="POST" action="{{ route('medewerkers.store') }}" enctype="multipart/form-data">
                @csrf
                
                <!-- Persoonlijke Informatie -->
                <h3 class="text-xl font-bold mb-4">Persoonlijke Informatie</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                    <div class="mb-4">
                        <label for="voornaam" class="block text-sm font-medium text-gray-700 mb-2">Voornaam *</label>
                        <input type="text" 
                               name="voornaam" 
                               id="voornaam" 
                               value="{{ old('voornaam') }}" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                               required>
                    </div>
                    
                    <div class="mb-4">
                        <label for="achternaam" class="block text-sm font-medium text-gray-700 mb-2">Achternaam *</label>
                        <input type="text" 
                               name="achternaam" 
                               id="achternaam" 
                               value="{{ old('achternaam') }}" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                               required>
                    </div>

                    <div class="mb-4">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">E-mailadres *</label>
                        <input type="email" 
                               name="email" 
                               id="email" 
                               value="{{ old('email') }}" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                               required>
                    </div>
                    
                    <div class="mb-4">
                        <label for="telefoonnummer" class="block text-sm font-medium text-gray-700 mb-2">Telefoonnummer</label>
                        <input type="tel" 
                               name="telefoonnummer" 
                               id="telefoonnummer" 
                               value="{{ old('telefoonnummer') }}" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div class="mb-4">
                        <label for="geboortedatum" class="block text-sm font-medium text-gray-700 mb-2">Geboortedatum</label>
                        <input type="date" 
                               name="geboortedatum" 
                               id="geboortedatum" 
                               value="{{ old('geboortedatum') }}" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div class="mb-4">
                        <label for="geslacht" class="block text-sm font-medium text-gray-700 mb-2">Geslacht</label>
                        <select name="geslacht" 
                                id="geslacht" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Selecteer geslacht</option>
                            <option value="Man" {{ old('geslacht') == 'Man' ? 'selected' : '' }}>Man</option>
                            <option value="Vrouw" {{ old('geslacht') == 'Vrouw' ? 'selected' : '' }}>Vrouw</option>
                            <option value="Anders" {{ old('geslacht') == 'Anders' ? 'selected' : '' }}>Anders</option>
                        </select>
                    </div>
                </div>

                <!-- Adresgegevens -->
                <h3 class="text-xl font-bold mt-6 mb-4">Adresgegevens</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <div class="mb-4 lg:col-span-2">
                        <label for="straatnaam" class="block text-sm font-medium text-gray-700 mb-2">Straatnaam</label>
                        <input type="text" 
                               name="straatnaam" 
                               id="straatnaam" 
                               value="{{ old('straatnaam') }}" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                               placeholder="Begin te typen voor suggesties...">
                    </div>

                    <div class="mb-4">
                        <label for="huisnummer" class="block text-sm font-medium text-gray-700 mb-2">Huisnummer</label>
                        <input type="text" 
                               name="huisnummer" 
                               id="huisnummer" 
                               value="{{ old('huisnummer') }}" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div class="mb-4">
                        <label for="postcode" class="block text-sm font-medium text-gray-700 mb-2">Postcode</label>
                        <input type="text" 
                               name="postcode" 
                               id="postcode" 
                               value="{{ old('postcode') }}" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                               placeholder="9000">
                    </div>

                    <div class="mb-4 lg:col-span-3">
                        <label for="stad" class="block text-sm font-medium text-gray-700 mb-2">Stad</label>
                        <input type="text" 
                               name="stad" 
                               id="stad" 
                               value="{{ old('stad') }}" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                               readonly>
                    </div>
                </div>

                <!-- Werkgerelateerde Informatie -->
                <h3 class="text-xl font-bold mt-6 mb-4">Werkgerelateerde Informatie</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                    <div class="mb-4">
                        <label for="functie" class="block text-sm font-medium text-gray-700 mb-2">Functie</label>
                        <input type="text" 
                               name="functie" 
                               id="functie" 
                               value="{{ old('functie') }}" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div class="mb-4">
                        <label for="rol" class="block text-sm font-medium text-gray-700 mb-2">Rol</label>
                        <select name="rol" 
                                id="rol" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Selecteer rol</option>
                            <option value="admin" {{ old('rol') == 'admin' ? 'selected' : '' }}>Administrator</option>
                            <option value="manager" {{ old('rol') == 'manager' ? 'selected' : '' }}>Manager</option>
                            <option value="medewerker" {{ old('rol', 'medewerker') == 'medewerker' ? 'selected' : '' }}>Medewerker</option>
                            <option value="stagiair" {{ old('rol') == 'stagiair' ? 'selected' : '' }}>Stagiair</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="startdatum" class="block text-sm font-medium text-gray-700 mb-2">Startdatum</label>
                        <input type="date" 
                               name="startdatum" 
                               id="startdatum" 
                               value="{{ old('startdatum') }}" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div class="mb-4">
                        <label for="contract_type" class="block text-sm font-medium text-gray-700 mb-2">Contract Type</label>
                        <select name="contract_type" 
                                id="contract_type" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Selecteer contract type</option>
                            <option value="Vast" {{ old('contract_type') == 'Vast' ? 'selected' : '' }}>Vast contract</option>
                            <option value="Tijdelijk" {{ old('contract_type') == 'Tijdelijk' ? 'selected' : '' }}>Tijdelijk contract</option>
                            <option value="Freelance" {{ old('contract_type') == 'Freelance' ? 'selected' : '' }}>Freelance</option>
                            <option value="Stage" {{ old('contract_type') == 'Stage' ? 'selected' : '' }}>Stage</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status *</label>
                        <select name="status" 
                                id="status" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                                required>
                            <option value="Actief" {{ old('status') == 'Actief' ? 'selected' : '' }}>Actief</option>
                            <option value="Inactief" {{ old('status') == 'Inactief' ? 'selected' : '' }}>Inactief</option>
                            <option value="Verlof" {{ old('status') == 'Verlof' ? 'selected' : '' }}>Verlof</option>
                            <option value="Ziek" {{ old('status') == 'Ziek' ? 'selected' : '' }}>Ziek</option>
                        </select>
                    </div>
                </div>

                <!-- Rechten en Toegang -->
                <h3 class="text-xl font-bold mt-6 mb-4">Rechten en Toegang</h3>
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">Welke functies mag deze medewerker uitvoeren?</label>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="flex items-center">
                            <input type="checkbox" 
                                   name="bikefit" 
                                   value="1" 
                                   id="recht_bikefit"
                                   {{ old('bikefit') ? 'checked' : '' }}
                                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            <label for="recht_bikefit" class="ml-3 block text-sm font-medium text-gray-700">
                                Bikefit
                                <span class="block text-xs text-gray-500">Kan bikefit sessies aanmaken en beheren</span>
                            </label>
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox" 
                                   name="inspanningstest" 
                                   value="1" 
                                   id="recht_inspanningstest"
                                   {{ old('inspanningstest') ? 'checked' : '' }}
                                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            <label for="recht_inspanningstest" class="ml-3 block text-sm font-medium text-gray-700">
                                Inspanningstest
                                <span class="block text-xs text-gray-500">Kan inspanningstests aanmaken en beheren</span>
                            </label>
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox" 
                                   name="upload_documenten" 
                                   value="1" 
                                   id="recht_upload_documenten"
                                   {{ old('upload_documenten') ? 'checked' : '' }}
                                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            <label for="recht_upload_documenten" class="ml-3 block text-sm font-medium text-gray-700">
                                Upload documenten
                                <span class="block text-xs text-gray-500">Kan documenten uploaden en bewerken</span>
                            </label>
                        </div>
                    </div>
                    <p class="mt-2 text-sm text-gray-500">
                        <strong>Let op:</strong> Administrators hebben automatisch alle rechten. Deze instellingen gelden voor medewerkers en managers.
                    </p>
                </div>

                <!-- Opmerkingen -->
                <div class="mb-6">
                    <label for="notities" class="block text-sm font-medium text-gray-700 mb-2">Opmerkingen</label>
                    <textarea name="notities" 
                              id="notities" 
                              rows="4"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                              placeholder="Eventuele opmerkingen over deze medewerker...">{{ old('notities') }}</textarea>
                </div>

                <!-- Profielfoto -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Profielfoto</label>
                    <div class="flex items-center gap-4">
                        <!-- Avatar Preview -->
                        <div class="relative flex-shrink-0" style="width:80px;height:80px;">
                            <label for="avatarInput" style="cursor:pointer;display:block;position:relative;">
                                <div id="avatarPreviewContainer" class="rounded-lg bg-gray-200 flex items-center justify-center text-gray-600 font-semibold" style="width:80px;height:80px;font-size:32px;">
                                    ?
                                </div>
                                <!-- Camera overlay icon -->
                                <div style="position:absolute;bottom:4px;right:4px;background:rgba(200,225,235,0.95);border-radius:50%;width:28px;height:28px;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 4px rgba(0,0,0,0.1);">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#333" stroke-width="2">
                                        <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
                                        <circle cx="12" cy="13" r="4"></circle>
                                    </svg>
                                </div>
                            </label>
                            <input type="file" name="avatar" id="avatarInput" accept="image/*" capture="environment" style="display:none;">
                        </div>
                        
                        <!-- File info -->
                        <div class="flex-1">
                            <span id="avatarFileName" class="text-sm text-gray-600">Geen bestand gekozen</span>
                            <p class="text-xs text-gray-500 mt-1">Klik op de avatar om een foto te maken of te kiezen</p>
                        </div>
                    </div>
                </div>

                <!-- Submit buttons -->
                <div class="mt-8 flex gap-3 justify-start">
                    <a href="{{ route('medewerkers.index') }}" 
                       class="px-6 py-2 text-gray-800 font-bold text-sm flex items-center justify-center hover:opacity-80 transition duration-200 rounded-lg" 
                       style="background-color: #c8e1eb;">
                        Terug
                    </a>
                    <button type="submit" 
                            class="px-6 py-2 text-gray-800 font-bold text-sm flex items-center justify-center hover:opacity-80 transition duration-200 rounded-lg" 
                            style="background-color: #c8e1eb;">
                        Medewerker Aanmaken
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('avatarInput');
    const nameEl = document.getElementById('avatarFileName');
    const previewContainer = document.getElementById('avatarPreviewContainer');
    const voornaamInput = document.getElementById('voornaam');
    
    // Update preview placeholder met voornaam
    if (voornaamInput) {
        voornaamInput.addEventListener('input', function() {
            const voornaam = this.value.trim();
            if (voornaam && !input.files.length) {
                previewContainer.textContent = voornaam.charAt(0).toUpperCase();
            }
        });
    }
    
    // Handle file selection
    if (input) {
        input.addEventListener('change', function() {
            const file = this.files && this.files[0];
            if (file) {
                nameEl.textContent = file.name;
                
                // Show image preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewContainer.innerHTML = `<img src="${e.target.result}" alt="Preview" class="rounded-lg object-cover" style="width:80px;height:80px;" />`;
                };
                reader.readAsDataURL(file);
            } else {
                nameEl.textContent = 'Geen bestand gekozen';
                const voornaam = voornaamInput ? voornaamInput.value.trim() : '';
                previewContainer.innerHTML = voornaam ? voornaam.charAt(0).toUpperCase() : '?';
                previewContainer.className = 'rounded-lg bg-gray-200 flex items-center justify-center text-gray-600 font-semibold';
                previewContainer.style.cssText = 'width:80px;height:80px;font-size:32px;';
            }
        });
    }

    // Postcode automatische invulling
    const postcodeInput = document.getElementById('postcode');
    const stadInput = document.getElementById('stad');
    
    if (postcodeInput && stadInput) {
        postcodeInput.addEventListener('input', async function() {
            const postcode = this.value.replace(/\s/g, '');
            if (postcode.length === 4 && /^\d{4}$/.test(postcode)) {
                // Uitgebreide Belgische postcodes database
                const belgianPostcodes = {
                    // Provincie Antwerpen
                    '2000': 'Antwerpen',
                    '2018': 'Antwerpen',
                    '2020': 'Antwerpen',
                    '2030': 'Antwerpen',
                    '2040': 'Antwerpen',
                    '2050': 'Antwerpen',
                    '2060': 'Antwerpen',
                    '2100': 'Deurne',
                    '2140': 'Borgerhout',
                    '2150': 'Borsbeek',
                    '2160': 'Wommelgem',
                    '2170': 'Merksem',
                    '2180': 'Ekeren',
                    '2200': 'Herentals',
                    '2220': 'Heist-op-den-Berg',
                    '2230': 'Herselt',
                    '2240': 'Zandhoven',
                    '2250': 'Olen',
                    '2260': 'Westerlo',
                    '2270': 'Herenthout',
                    '2280': 'Grobbendonk',
                    '2290': 'Vorselaar',
                    '2300': 'Turnhout',
                    '2310': 'Rijkevorsel',
                    '2320': 'Hoogstraten',
                    '2330': 'Merksplas',
                    '2340': 'Beerse',
                    '2350': 'Vosselaar',
                    '2360': 'Oud-Turnhout',
                    '2370': 'Arendonk',
                    '2380': 'Ravels',
                    '2390': 'Westmalle',
                    '2400': 'Mol',
                    '2440': 'Geel',
                    '2450': 'Meerhout',
                    '2460': 'Kasterlee',
                    '2470': 'Retie',
                    '2480': 'Dessel',
                    '2490': 'Balen',
                    '2500': 'Lier',
                    '2520': 'Ranst',
                    '2530': 'Boechout',
                    '2540': 'Hove',
                    '2550': 'Kontich',
                    '2560': 'Nijlen',
                    '2570': 'Duffel',
                    '2580': 'Putte',
                    '2590': 'Berlaar',
                    '2600': 'Berchem',
                    '2610': 'Wilrijk',
                    '2620': 'Hemiksem',
                    '2630': 'Aartselaar',
                    '2640': 'Mortsel',
                    '2650': 'Edegem',
                    '2660': 'Hoboken',
                    '2670': 'Puurs',
                    '2800': 'Mechelen',
                    '2820': 'Bonheiden',
                    '2830': 'Willebroek',
                    '2840': 'Rumst',
                    '2845': 'Niel',
                    '2850': 'Boom',
                    '2860': 'Sint-Katelijne-Waver',
                    '2870': 'Puurs-Sint-Amands',
                    '2880': 'Bornem',
                    '2890': 'Oppuurs',

                    // Provincie Oost-Vlaanderen
                    '9000': 'Gent',
                    '9030': 'Mariakerke',
                    '9031': 'Drongen',
                    '9032': 'Wondelgem',
                    '9040': 'Sint-Amandsberg',
                    '9041': 'Oostakker',
                    '9042': 'Desteldonk',
                    '9050': 'Gentbrugge',
                    '9051': 'Sint-Denijs-Westrem',
                    '9052': 'Zwijnaarde',
                    '9060': 'Zelzate',
                    '9070': 'Destelbergen',
                    '9080': 'Lochristi',
                    '9090': 'Melle',
                    '9100': 'Sint-Niklaas',
                    '9111': 'Belsele',
                    '9112': 'Sinaai',
                    '9120': 'Beveren',
                    '9130': 'Kallo',
                    '9140': 'Temse',
                    '9150': 'Kruibeke',
                    '9160': 'Lokeren',
                    '9170': 'Sint-Gillis-Waas',
                    '9180': 'Moerbeke',
                    '9185': 'Wachtebeke',
                    '9190': 'Stekene',
                    '9200': 'Dendermonde',
                    '9220': 'Hamme',
                    '9230': 'Wetteren',
                    '9240': 'Zele',
                    '9250': 'Waasmunster',
                    '9260': 'Wichelen',
                    '9270': 'Laarne',
                    '9280': 'Lebbeke',
                    '9290': 'Berlare',
                    '9300': 'Aalst',
                    '9310': 'Moorsel',
                    '9320': 'Erembodegem',
                    '9340': 'Lede',
                    '9400': 'Ninove',
                    '9450': 'Haaltert',
                    '9500': 'Geraardsbergen',
                    '9506': 'Geraardsbergen',
                    '9520': 'Sint-Lievens-Houtem',
                    '9521': 'Sint-Lievens-Houtem',
                    '9540': 'Herzele',
                    '9550': 'Herzele',
                    '9560': 'Herzele',
                    '9570': 'Lierde',
                    '9571': 'Lierde',
                    '9572': 'Lierde',
                    '9600': 'Ronse',
                    '9620': 'Zottegem',
                    '9630': 'Zwalm',
                    '9636': 'Zwalm',
                    '9660': 'Brakel',
                    '9661': 'Brakel',
                    '9667': 'Horebeke',
                    '9680': 'Maarkedal',
                    '9681': 'Maarkedal',
                    '9690': 'Kluisbergen',
                    '9700': 'Oudenaarde',
                    '9750': 'Kruisem',
                    '9770': 'Kruisem',
                    '9790': 'Wortegem-Petegem',
                    '9800': 'Deinze',
                    '9820': 'Merelbeke',
                    '9830': 'Sint-Martens-Latem',
                    '9840': 'De Pinte',
                    '9850': 'Nevele',
                    '9860': 'Oosterzele',
                    '9870': 'Zulte',
                    '9880': 'Aalter',
                    '9890': 'Gavere',
                    '9900': 'Eeklo',
                    '9910': 'Knesselare',
                    '9920': 'Lovendegem',
                    '9930': 'Zomergem',
                    '9940': 'Evergem',
                    '9950': 'Waarschoot',
                    '9960': 'Assenede',
                    '9970': 'Kaprijke',
                    '9980': 'Sint-Laureins',
                    '9990': 'Maldegem',
                    '9991': 'Adegem',
                    '9992': 'Middelburg',

                    // Brussels Hoofdstedelijk Gewest
                    '1000': 'Brussel',
                    '1020': 'Laken',
                    '1030': 'Schaarbeek',
                    '1040': 'Etterbeek',
                    '1050': 'Elsene',
                    '1060': 'Sint-Gillis',
                    '1070': 'Anderlecht',
                    '1080': 'Sint-Jans-Molenbeek',
                    '1081': 'Koekelberg',
                    '1082': 'Sint-Agatha-Berchem',
                    '1083': 'Ganshoren',
                    '1090': 'Jette',
                    '1120': 'Neder-Over-Heembeek',
                    '1130': 'Haren',
                    '1140': 'Evere',
                    '1150': 'Sint-Pieters-Woluwe',
                    '1160': 'Oudergem',
                    '1170': 'Watermaal-Bosvoorde',
                    '1180': 'Ukkel',
                    '1190': 'Vorst',
                    '1200': 'Sint-Lambrechts-Woluwe',
                    '1210': 'Sint-Joost-ten-Node',

                    // Provincie West-Vlaanderen
                    '8000': 'Brugge',
                    '8200': 'Sint-Michiels',
                    '8210': 'Zedelgem',
                    '8300': 'Knokke-Heist',
                    '8310': 'Sint-Kruis',
                    '8340': 'Damme',
                    '8370': 'Blankenberge',
                    '8380': 'Zeebrugge',
                    '8400': 'Oostende',
                    '8420': 'De Haan',
                    '8430': 'Middelkerke',
                    '8450': 'Bredene',
                    '8460': 'Oudenburg',
                    '8470': 'Gistel',
                    '8480': 'Bekegem',
                    '8490': 'Jabbeke',
                    '8500': 'Kortrijk',
                    '8510': 'Marke',
                    '8520': 'Kuurne',
                    '8530': 'Harelbeke',
                    '8540': 'Deerlijk',
                    '8550': 'Zwevegem',
                    '8560': 'Wevelgem',
                    '8570': 'Anzegem',
                    '8580': 'Avelgem',
                    '8590': 'Waregem',
                    '8600': 'Diksmuide',
                    '8610': 'Kortemark',
                    '8620': 'Nieuwpoort',
                    '8630': 'Veurne',
                    '8640': 'Vleteren',
                    '8650': 'Houthulst',
                    '8660': 'De Panne',
                    '8670': 'Koksijde',
                    '8680': 'Koekelare',
                    '8690': 'Alveringem',
                    '8700': 'Tielt',
                    '8710': 'Wielsbeke',
                    '8720': 'Dentergem',
                    '8730': 'Beernem',
                    '8740': 'Pittem',
                    '8750': 'Wingene',
                    '8760': 'Meulebeke',
                    '8770': 'Ingelmunster',
                    '8780': 'Oostrozebeke',
                    '8790': 'Waregem',
                    '8800': 'Roeselare',
                    '8810': 'Lichtervelde',
                    '8820': 'Torhout',
                    '8830': 'Hooglede',
                    '8840': 'Staden',
                    '8850': 'Ardooie',
                    '8860': 'Lendelede',
                    '8870': 'Izegem',
                    '8880': 'Ledegem',
                    '8890': 'Moorslede',
                    '8900': 'Ieper',
                    '8902': 'Zillebeke',
                    '8904': 'Voormezele',
                    '8906': 'Elverdinge',
                    '8908': 'Vlamertinge',
                    '8920': 'Langemark-Poelkapelle',
                    '8930': 'Menen',
                    '8940': 'Wervik',
                    '8950': 'Heuvelland',
                    '8956': 'Kemmel',
                    '8957': 'Mesen',
                    '8958': 'Loker',
                    '8970': 'Poperinge',
                    '8972': 'Proven',
                    '8978': 'Watou',
                    '8980': 'Zonnebeke',

                    // Provincie Vlaams-Brabant
                    '1500': 'Halle',
                    '1502': 'Lembeek',
                    '1540': 'Herne',
                    '1547': 'Bever',
                    '1560': 'Hoeilaart',
                    '1570': 'Galmaarden',
                    '1600': 'Sint-Pieters-Leeuw',
                    '1601': 'Ruisbroek',
                    '1620': 'Drogenbos',
                    '1630': 'Linkebeek',
                    '1640': 'Sint-Genesius-Rode',
                    '1650': 'Beersel',
                    '1651': 'Lot',
                    '1652': 'Alsemberg',
                    '1653': 'Dworp',
                    '1654': 'Huizingen',
                    '1670': 'Heikruis',
                    '1671': 'Eizeringen',
                    '1673': 'Heikruis',
                    '1674': 'Heikruis',
                    '1700': 'Dilbeek',
                    '1701': 'Itterbeek',
                    '1702': 'Groot-Bijgaarden',
                    '1703': 'Schepdaal',
                    '1730': 'Asse',
                    '1731': 'Zellik',
                    '1740': 'Ternat',
                    '1741': 'Wambeek',
                    '1742': 'Ternat',
                    '1745': 'Opwijk',
                    '1750': 'Lennik',
                    '1755': 'Gooik',
                    '1760': 'Roosdaal',
                    '1761': 'Borchtlombeek',
                    '1770': 'Liedekerke',
                    '1780': 'Wemmel',
                    '1785': 'Merchtem',
                    '1790': 'Affligem',
                    '1800': 'Vilvoorde',
                    '1804': 'Cargovil',
                    '1820': 'Steenokkerzeel',
                    '1830': 'Machelen',
                    '1831': 'Diegem',
                    '1840': 'Londerzeel',
                    '1850': 'Grimbergen',
                    '1860': 'Meise',
                    '1870': 'Zemst',
                    '1880': 'Kapelle-op-den-Bos',
                    '1910': 'Kampenhout',
                    '1930': 'Zaventem',
                    '1931': 'Brucargo',
                    '1932': 'Sint-Stevens-Woluwe',
                    '1933': 'Sterrebeek',
                    '1950': 'Kraainem',
                    '1970': 'Wezembeek-Oppem',
                    '2270': 'Herenthout',
                    '3000': 'Leuven',
                    '3001': 'Heverlee',
                    '3010': 'Kessel-Lo',
                    '3012': 'Wilsele',
                    '3018': 'Wijgmaal',
                    '3020': 'Herent',
                    '3040': 'Huldenberg',
                    '3050': 'Oud-Heverlee',
                    '3051': 'Sint-Joris-Weert',
                    '3052': 'Blanden',
                    '3053': 'Haasrode',
                    '3054': 'Vaalbeek',
                    '3060': 'Bertem',
                    '3061': 'Leefdaal',
                    '3070': 'Kortenberg',
                    '3071': 'Erps-Kwerps',
                    '3078': 'Everberg',
                    '3080': 'Tervuren',
                    '3090': 'Overijse',
                    '3110': 'Rotselaar',
                    '3118': 'Werchter',
                    '3120': 'Tremelo',
                    '3128': 'Baal',
                    '3130': 'Begijnendijk',
                    '3140': 'Keerbergen',
                    '3150': 'Haacht',
                    '3190': 'Boortmeerbeek',
                    '3191': 'Hever',
                    '3200': 'Aarschot',
                    '3201': 'Langdorp',
                    '3202': 'Rillaar',
                    '3210': 'Lubbeek',
                    '3211': 'Binkom',
                    '3212': 'Pellenberg',
                    '3220': 'Holsbeek',
                    '3221': 'Nieuwrode',
                    '3270': 'Scherpenheuvel-Zichem',
                    '3271': 'Averbode',
                    '3272': 'Messelbroek',
                    '3290': 'Diest',
                    '3293': 'Deurne',
                    '3294': 'Molenstede',
                    '3295': 'Kaggevinne',
                    '3296': 'Webbekom'
                };
                
                if (belgianPostcodes[postcode]) {
                    stadInput.value = belgianPostcodes[postcode];
                }
            }
        });
    }

    // Straatnaam autocomplete basis
    const straatnaamInput = document.getElementById('straatnaam');
    if (straatnaamInput) {
        let timeout;
        straatnaamInput.addEventListener('input', function() {
            const query = this.value;
            if (query.length > 2) {
                clearTimeout(timeout);
                timeout = setTimeout(() => {
                    console.log('Zoeken naar straatnamen met:', query);
                }, 300);
            }
        });
    }
});
</script>
@endsection
