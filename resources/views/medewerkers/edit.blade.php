@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Medewerker bewerken: {{ $medewerker->voornaam }} {{ $medewerker->naam }}</h1>
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
            
            <form method="POST" action="{{ route('medewerkers.update', $medewerker) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <!-- Persoonlijke Informatie -->
                <h3 class="text-xl font-bold mb-4">Persoonlijke Informatie</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                    <div class="mb-4">
                        <label for="voornaam" class="block text-sm font-medium text-gray-700 mb-2">Voornaam *</label>
                        <input type="text" 
                               name="voornaam" 
                               id="voornaam" 
                               value="{{ old('voornaam', $medewerker->voornaam) }}" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                               required>
                    </div>
                    
                    <div class="mb-4">
                        <label for="achternaam" class="block text-sm font-medium text-gray-700 mb-2">Achternaam *</label>
                        <input type="text" 
                               name="achternaam" 
                               id="achternaam" 
                               value="{{ old('achternaam', $medewerker->achternaam) }}" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                               required>
                    </div>

                    <div class="mb-4">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">E-mailadres *</label>
                        <input type="email" 
                               name="email" 
                               id="email" 
                               value="{{ old('email', $medewerker->email) }}" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                               required>
                    </div>
                    
                    <div class="mb-4">
                        <label for="telefoonnummer" class="block text-sm font-medium text-gray-700 mb-2">Telefoonnummer</label>
                        <input type="tel" 
                               name="telefoonnummer" 
                               id="telefoonnummer" 
                               value="{{ old('telefoonnummer', $medewerker->telefoonnummer) }}" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                <div class="mb-6">
                    <label for="geboortedatum" class="block text-sm font-medium text-gray-700 mb-2">Geboortedatum</label>
                    <input type="date" 
                           name="geboortedatum" 
                           id="geboortedatum"
                           value="{{ old('geboortedatum', $medewerker->geboortedatum ? (is_string($medewerker->geboortedatum) ? \Carbon\Carbon::parse($medewerker->geboortedatum)->format('Y-m-d') : $medewerker->geboortedatum->format('Y-m-d')) : '') }}"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>                    <div class="mb-4">
                        <label for="geslacht" class="block text-sm font-medium text-gray-700 mb-2">Geslacht</label>
                        <select name="geslacht" 
                                id="geslacht" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Selecteer geslacht</option>
                            <option value="Man" {{ old('geslacht', $medewerker->geslacht) == 'Man' ? 'selected' : '' }}>Man</option>
                            <option value="Vrouw" {{ old('geslacht', $medewerker->geslacht) == 'Vrouw' ? 'selected' : '' }}>Vrouw</option>
                            <option value="Anders" {{ old('geslacht', $medewerker->geslacht) == 'Anders' ? 'selected' : '' }}>Anders</option>
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
                               value="{{ old('straatnaam', $medewerker->straatnaam) }}" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                               placeholder="Begin te typen voor suggesties...">
                    </div>

                    <div class="mb-4">
                        <label for="huisnummer" class="block text-sm font-medium text-gray-700 mb-2">Huisnummer</label>
                        <input type="text" 
                               name="huisnummer" 
                               id="huisnummer" 
                               value="{{ old('huisnummer', $medewerker->huisnummer) }}" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div class="mb-4">
                        <label for="postcode" class="block text-sm font-medium text-gray-700 mb-2">Postcode</label>
                        <input type="text" 
                               name="postcode" 
                               id="postcode" 
                               value="{{ old('postcode', $medewerker->postcode) }}" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                               placeholder="9000">
                    </div>

                    <div class="mb-4 lg:col-span-3">
                        <label for="stad" class="block text-sm font-medium text-gray-700 mb-2">Stad</label>
                        <input type="text" 
                               name="stad" 
                               id="stad" 
                               value="{{ old('stad', $medewerker->stad) }}" 
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
                               value="{{ old('functie', $medewerker->functie) }}" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div class="mb-4">
                        <label for="rol" class="block text-sm font-medium text-gray-700 mb-2">Rol</label>
                        <select name="rol" 
                                id="rol" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Selecteer rol</option>
                            <option value="admin" {{ old('rol', $medewerker->rol) == 'admin' ? 'selected' : '' }}>Administrator</option>
                            <option value="manager" {{ old('rol', $medewerker->rol) == 'manager' ? 'selected' : '' }}>Manager</option>
                            <option value="medewerker" {{ old('rol', $medewerker->rol) == 'medewerker' ? 'selected' : '' }}>Medewerker</option>
                            <option value="stagiair" {{ old('rol', $medewerker->rol) == 'stagiair' ? 'selected' : '' }}>Stagiair</option>
                        </select>
                    </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="mb-6">
                    <label for="startdatum" class="block text-sm font-medium text-gray-700 mb-2">Startdatum</label>
                    <input type="date" 
                           name="startdatum" 
                           id="startdatum"
                           value="{{ old('startdatum', $medewerker->startdatum ? (is_string($medewerker->startdatum) ? \Carbon\Carbon::parse($medewerker->startdatum)->format('Y-m-d') : $medewerker->startdatum->format('Y-m-d')) : '') }}"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>                    <div class="mb-4">
                        <label for="contract_type" class="block text-sm font-medium text-gray-700 mb-2">Contract Type</label>
                        <select name="contract_type" 
                                id="contract_type" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Selecteer contract type</option>
                            <option value="Vast" {{ old('contract_type', $medewerker->contract_type) == 'Vast' ? 'selected' : '' }}>Vast contract</option>
                            <option value="Tijdelijk" {{ old('contract_type', $medewerker->contract_type) == 'Tijdelijk' ? 'selected' : '' }}>Tijdelijk contract</option>
                            <option value="Freelance" {{ old('contract_type', $medewerker->contract_type) == 'Freelance' ? 'selected' : '' }}>Freelance</option>
                            <option value="Stage" {{ old('contract_type', $medewerker->contract_type) == 'Stage' ? 'selected' : '' }}>Stage</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status *</label>
                        <select name="status" 
                                id="status" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                                required>
                            <option value="Actief" {{ old('status', $medewerker->status) == 'Actief' ? 'selected' : '' }}>Actief</option>
                            <option value="Inactief" {{ old('status', $medewerker->status) == 'Inactief' ? 'selected' : '' }}>Inactief</option>
                            <option value="Verlof" {{ old('status', $medewerker->status) == 'Verlof' ? 'selected' : '' }}>Verlof</option>
                            <option value="Ziek" {{ old('status', $medewerker->status) == 'Ziek' ? 'selected' : '' }}>Ziek</option>
                        </select>
                    </div>
                </div>

                <!-- Rechten en Toegang -->
                <h3 class="text-xl font-bold mt-6 mb-4">Rechten en Toegang</h3>
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">Welke functies mag deze medewerker uitvoeren?</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="flex items-center">
                            <input type="checkbox" 
                                   name="bikefit" 
                                   value="1" 
                                   id="recht_bikefit"
                                   {{ old('bikefit', $medewerker->bikefit) ? 'checked' : '' }}
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
                                   {{ old('inspanningstest', $medewerker->inspanningstest) ? 'checked' : '' }}
                                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            <label for="recht_inspanningstest" class="ml-3 block text-sm font-medium text-gray-700">
                                Inspanningstest
                                <span class="block text-xs text-gray-500">Kan inspanningstests aanmaken en beheren</span>
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
                              placeholder="Eventuele opmerkingen over deze medewerker...">{{ old('notities', $medewerker->notities) }}</textarea>
                </div>

                <!-- Huidige Profielfoto -->
                @if($medewerker->avatar_path)
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Huidige Profielfoto</label>
                        <img src="{{ asset('storage/' . $medewerker->avatar_path) }}" alt="Huidige avatar" class="w-20 h-20 rounded-full object-cover">
                    </div>
                @endif

                <!-- Nieuwe Profielfoto -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ $medewerker->avatar_path ? 'Nieuwe profielfoto' : 'Profielfoto' }}</label>
                    <input type="file" name="avatar" id="avatarInput" accept="image/*" style="display:none">
                    <div style="display:flex;align-items:center;gap:0.6em;margin-top:0.5em;">
                        <button type="button" id="avatarCamBtn" aria-label="Maak foto" title="Maak foto" style="width:40px;height:40px;border-radius:9999px;background:#c8e1eb;border:none;display:inline-flex;align-items:center;justify-content:center;box-shadow:0 1px 3px #e0e7ff;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M7 7h2l1.2-1.6A2 2 0 0 1 12 4h0a2 2 0 0 1 1.8 1.4L15 7h2a3 3 0 0 1 3 3v7a3 3 0 0 1-3 3H7a3 3 0 0 1-3-3v-7a3 3 0 0 1 3-3Z" stroke="#111" stroke-width="1.5"/>
                                <circle cx="12" cy="13" r="3.5" stroke="#111" stroke-width="1.5"/>
                                <path d="M19 5v4M17 7h4" stroke="#111" stroke-width="1.5" stroke-linecap="round"/>
                            </svg>
                        </button>
                        <button type="button" id="avatarBtn" style="background:#c8e1eb;color:#111;padding:0.25em 0.9em;border-radius:7px;text-decoration:none;font-weight:600;font-size:0.95em;box-shadow:0 1px 3px #e0e7ff;border:none;">+ Profielfoto kiezen</button>
                        <span id="avatarFileName" style="color:#6b7280;font-size:0.9em;">Geen bestand gekozen</span>
                        <img id="avatarPreview" src="" alt="Voorbeeld" style="width:36px;height:36px;border-radius:50%;object-fit:cover;display:none;" />
                    </div>
                </div>

                <!-- Submit buttons -->
                <div class="mt-8 flex gap-3 justify-start">
                    <a href="{{ route('medewerkers.index') }}" 
                       class="rounded-full px-6 py-2 text-gray-800 font-bold text-sm flex items-center justify-center hover:opacity-80 transition duration-200" 
                       style="background-color: #c8e1eb;">
                        Terug
                    </a>
                    <button type="submit" 
                            class="rounded-full px-6 py-2 text-gray-800 font-bold text-sm flex items-center justify-center hover:opacity-80 transition duration-200" 
                            style="background-color: #c8e1eb;">
                        Wijzigingen Opslaan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('avatarInput');
    const btn = document.getElementById('avatarBtn');
    const camBtn = document.getElementById('avatarCamBtn');
    const nameEl = document.getElementById('avatarFileName');
    const preview = document.getElementById('avatarPreview');
    
    if (btn && input) {
        btn.addEventListener('click', function(){
            input.removeAttribute('capture');
            input.click();
        });
    }
    
    if (camBtn && input) {
        camBtn.addEventListener('click', function(){
            input.setAttribute('capture', 'environment');
            input.click();
        });
    }
    
    input?.addEventListener('change', function(){
        const file = input.files && input.files[0];
        if (file) {
            nameEl.textContent = file.name;
            const url = URL.createObjectURL(file);
            preview.src = url;
            preview.style.display = 'inline-block';
        } else {
            nameEl.textContent = 'Geen bestand gekozen';
            preview.src = '';
            preview.style.display = 'none';
        }
    });

    // Postcode automatische invulling
    const postcodeInput = document.getElementById('postcode');
    const stadInput = document.getElementById('stad');
    
    if (postcodeInput && stadInput) {
        postcodeInput.addEventListener('input', async function() {
            const postcode = this.value.replace(/\s/g, '');
            if (postcode.length === 4 && /^\d{4}$/.test(postcode)) {
                // Uitgebreide Belgische postcodes database - belangrijkste postcodes
                const belgianPostcodes = {
                    '9000': 'Gent', '9030': 'Mariakerke', '9031': 'Drongen', '9040': 'Sint-Amandsberg', '9050': 'Gentbrugge', '9100': 'Sint-Niklaas', '9200': 'Dendermonde', '9300': 'Aalst', '9400': 'Ninove', '9500': 'Geraardsbergen', '9600': 'Ronse', '9620': 'Zottegem', '9700': 'Oudenaarde', '9800': 'Deinze', '9900': 'Eeklo',
                    '2000': 'Antwerpen', '2100': 'Deurne', '2140': 'Borgerhout', '2170': 'Merksem', '2180': 'Ekeren', '2200': 'Herentals', '2300': 'Turnhout', '2400': 'Mol', '2440': 'Geel', '2500': 'Lier', '2800': 'Mechelen',
                    '1000': 'Brussel', '1020': 'Laken', '1030': 'Schaarbeek', '1040': 'Etterbeek', '1050': 'Elsene', '1070': 'Anderlecht', '1080': 'Sint-Jans-Molenbeek', '1090': 'Jette', '1140': 'Evere', '1150': 'Sint-Pieters-Woluwe', '1180': 'Ukkel', '1200': 'Sint-Lambrechts-Woluwe',
                    '8000': 'Brugge', '8300': 'Knokke-Heist', '8370': 'Blankenberge', '8400': 'Oostende', '8500': 'Kortrijk', '8600': 'Diksmuide', '8700': 'Tielt', '8800': 'Roeselare', '8900': 'Ieper', '8930': 'Menen',
                    '3000': 'Leuven', '3200': 'Aarschot', '3290': 'Diest', '3500': 'Hasselt', '3600': 'Genk', '3700': 'Tongeren', '3800': 'Sint-Truiden', '3900': 'Overpelt',
                    '1500': 'Halle', '1600': 'Sint-Pieters-Leeuw', '1700': 'Dilbeek', '1730': 'Asse', '1800': 'Vilvoorde', '1850': 'Grimbergen', '1930': 'Zaventem'
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
