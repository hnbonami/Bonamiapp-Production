@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Klant bewerken: {{ $klant->voornaam }} {{ $klant->naam }}</h1>
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
            
            <form method="POST" action="{{ route('klanten.update', $klant) }}" enctype="multipart/form-data">
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
                               value="{{ old('voornaam', $klant->voornaam ?? '') }}" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                               required>
                    </div>
                    
                    <div class="mb-4">
                        <label for="naam" class="block text-sm font-medium text-gray-700 mb-2">Naam *</label>
                        <input type="text" 
                               name="naam" 
                               id="naam" 
                               value="{{ old('naam', $klant->naam ?? '') }}" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                               required>
                    </div>

                    <div class="mb-4">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">E-mailadres</label>
                        <input type="email" 
                               name="email" 
                               id="email" 
                               value="{{ old('email', $klant->email ?? '') }}" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div class="mb-4">
                        <label for="telefoonnummer" class="block text-sm font-medium text-gray-700 mb-2">Telefoonnummer</label>
                        <input type="tel" 
                               name="telefoonnummer" 
                               id="telefoonnummer" 
                               value="{{ old('telefoonnummer', $klant->telefoonnummer ?? $klant->telefoon ?? '') }}" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    
                    <div class="mb-4">
                        <label for="geboortedatum" class="block text-sm font-medium text-gray-700 mb-2">Geboortedatum</label>
                        <input type="date" 
                               name="geboortedatum" 
                               id="geboortedatum" 
                               value="{{ old('geboortedatum', $klant->geboortedatum && $klant->geboortedatum instanceof \Carbon\Carbon ? $klant->geboortedatum->format('Y-m-d') : $klant->geboortedatum) }}" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div class="mb-4">
                        <label for="geslacht" class="block text-sm font-medium text-gray-700 mb-2">Geslacht *</label>
                        <select name="geslacht" 
                                id="geslacht" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                                required>
                            <option value="">Selecteer geslacht</option>
                            <option value="Man" {{ old('geslacht', $klant->geslacht ?? '') == 'Man' ? 'selected' : '' }}>Man</option>
                            <option value="Vrouw" {{ old('geslacht', $klant->geslacht ?? '') == 'Vrouw' ? 'selected' : '' }}>Vrouw</option>
                            <option value="Anders" {{ old('geslacht', $klant->geslacht ?? '') == 'Anders' ? 'selected' : '' }}>Anders</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status *</label>
                        <select name="status" 
                                id="status" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                                required>
                            <option value="Actief" {{ old('status', $klant->status ?? 'Actief') == 'Actief' ? 'selected' : '' }}>Actief</option>
                            <option value="Inactief" {{ old('status', $klant->status ?? 'Actief') == 'Inactief' ? 'selected' : '' }}>Inactief</option>
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
                               value="{{ old('straatnaam', $klant->straatnaam ?? $klant->adres ?? '') }}" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                               placeholder="Begin te typen voor suggesties..."
                               autocomplete="off">
                        <div id="straatnaam-suggesties" class="hidden absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm"></div>
                    </div>

                    <div class="mb-4">
                        <label for="huisnummer" class="block text-sm font-medium text-gray-700 mb-2">Huisnummer</label>
                        <input type="text" 
                               name="huisnummer" 
                               id="huisnummer" 
                               value="{{ old('huisnummer', $klant->huisnummer ?? '') }}" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div class="mb-4">
                        <label for="postcode" class="block text-sm font-medium text-gray-700 mb-2">Postcode</label>
                        <input type="text" 
                               name="postcode" 
                               id="postcode" 
                               value="{{ old('postcode', $klant->postcode ?? '') }}" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                               placeholder="9000">
                    </div>

                    <div class="mb-4 lg:col-span-2">
                        <label for="stad" class="block text-sm font-medium text-gray-700 mb-2">Stad</label>
                        <input type="text" 
                               name="stad" 
                               id="stad" 
                               value="{{ old('stad', $klant->stad ?? '') }}" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div class="mb-4 lg:col-span-2">
                        <label for="land" class="block text-sm font-medium text-gray-700 mb-2">Land</label>
                        <select name="land" 
                                id="land" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="België" {{ old('land', $klant->land ?? 'België') == 'België' ? 'selected' : '' }}>België</option>
                            <option value="Nederland" {{ old('land', $klant->land ?? '') == 'Nederland' ? 'selected' : '' }}>Nederland</option>
                            <option value="Duitsland" {{ old('land', $klant->land ?? '') == 'Duitsland' ? 'selected' : '' }}>Duitsland</option>
                            <option value="Frankrijk" {{ old('land', $klant->land ?? '') == 'Frankrijk' ? 'selected' : '' }}>Frankrijk</option>
                            <option value="Luxemburg" {{ old('land', $klant->land ?? '') == 'Luxemburg' ? 'selected' : '' }}>Luxemburg</option>
                            <option value="Verenigd Koninkrijk" {{ old('land', $klant->land ?? '') == 'Verenigd Koninkrijk' ? 'selected' : '' }}>Verenigd Koninkrijk</option>
                            <option value="Spanje" {{ old('land', $klant->land ?? '') == 'Spanje' ? 'selected' : '' }}>Spanje</option>
                            <option value="Italië" {{ old('land', $klant->land ?? '') == 'Italië' ? 'selected' : '' }}>Italië</option>
                            <option value="Portugal" {{ old('land', $klant->land ?? '') == 'Portugal' ? 'selected' : '' }}>Portugal</option>
                            <option value="Zwitserland" {{ old('land', $klant->land ?? '') == 'Zwitserland' ? 'selected' : '' }}>Zwitserland</option>
                            <option value="Oostenrijk" {{ old('land', $klant->land ?? '') == 'Oostenrijk' ? 'selected' : '' }}>Oostenrijk</option>
                            <option value="Polen" {{ old('land', $klant->land ?? '') == 'Polen' ? 'selected' : '' }}>Polen</option>
                            <option value="Tsjechië" {{ old('land', $klant->land ?? '') == 'Tsjechië' ? 'selected' : '' }}>Tsjechië</option>
                            <option value="Denemarken" {{ old('land', $klant->land ?? '') == 'Denemarken' ? 'selected' : '' }}>Denemarken</option>
                            <option value="Zweden" {{ old('land', $klant->land ?? '') == 'Zweden' ? 'selected' : '' }}>Zweden</option>
                            <option value="Noorwegen" {{ old('land', $klant->land ?? '') == 'Noorwegen' ? 'selected' : '' }}>Noorwegen</option>
                            <option value="Finland" {{ old('land', $klant->land ?? '') == 'Finland' ? 'selected' : '' }}>Finland</option>
                            <option value="Ierland" {{ old('land', $klant->land ?? '') == 'Ierland' ? 'selected' : '' }}>Ierland</option>
                            <option value="Griekenland" {{ old('land', $klant->land ?? '') == 'Griekenland' ? 'selected' : '' }}>Griekenland</option>
                            <option value="Kroatië" {{ old('land', $klant->land ?? '') == 'Kroatië' ? 'selected' : '' }}>Kroatië</option>
                            <option value="Slovenië" {{ old('land', $klant->land ?? '') == 'Slovenië' ? 'selected' : '' }}>Slovenië</option>
                            <option value="Roemenië" {{ old('land', $klant->land ?? '') == 'Roemenië' ? 'selected' : '' }}>Roemenië</option>
                            <option value="Bulgarije" {{ old('land', $klant->land ?? '') == 'Bulgarije' ? 'selected' : '' }}>Bulgarije</option>
                            <option value="Hongarije" {{ old('land', $klant->land ?? '') == 'Hongarije' ? 'selected' : '' }}>Hongarije</option>
                            <option value="Slowakije" {{ old('land', $klant->land ?? '') == 'Slowakije' ? 'selected' : '' }}>Slowakije</option>
                            <option value="Estland" {{ old('land', $klant->land ?? '') == 'Estland' ? 'selected' : '' }}>Estland</option>
                            <option value="Letland" {{ old('land', $klant->land ?? '') == 'Letland' ? 'selected' : '' }}>Letland</option>
                            <option value="Litouwen" {{ old('land', $klant->land ?? '') == 'Litouwen' ? 'selected' : '' }}>Litouwen</option>
                            <option value="Cyprus" {{ old('land', $klant->land ?? '') == 'Cyprus' ? 'selected' : '' }}>Cyprus</option>
                            <option value="Malta" {{ old('land', $klant->land ?? '') == 'Malta' ? 'selected' : '' }}>Malta</option>
                            <option value="Verenigde Staten" {{ old('land', $klant->land ?? '') == 'Verenigde Staten' ? 'selected' : '' }}>Verenigde Staten</option>
                            <option value="Canada" {{ old('land', $klant->land ?? '') == 'Canada' ? 'selected' : '' }}>Canada</option>
                            <option value="Australië" {{ old('land', $klant->land ?? '') == 'Australië' ? 'selected' : '' }}>Australië</option>
                            <option value="Nieuw-Zeeland" {{ old('land', $klant->land ?? '') == 'Nieuw-Zeeland' ? 'selected' : '' }}>Nieuw-Zeeland</option>
                            <option value="Japan" {{ old('land', $klant->land ?? '') == 'Japan' ? 'selected' : '' }}>Japan</option>
                            <option value="Zuid-Korea" {{ old('land', $klant->land ?? '') == 'Zuid-Korea' ? 'selected' : '' }}>Zuid-Korea</option>
                            <option value="China" {{ old('land', $klant->land ?? '') == 'China' ? 'selected' : '' }}>China</option>
                            <option value="Anders" {{ old('land', $klant->land ?? '') == 'Anders' ? 'selected' : '' }}>Anders</option>
                        </select>
                    </div>
                </div>

                <!-- Sport Informatie -->
                <h3 class="text-xl font-bold mt-6 mb-4">Sport Informatie</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                    <div class="mb-4">
                        <label for="sport" class="block text-sm font-medium text-gray-700 mb-2">Sport</label>
                        <input type="text" 
                               name="sport" 
                               id="sport" 
                               value="{{ old('sport', $klant->sport ?? '') }}" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div class="mb-4">
                        <label for="niveau" class="block text-sm font-medium text-gray-700 mb-2">Niveau</label>
                        <input type="text" 
                               name="niveau" 
                               id="niveau" 
                               value="{{ old('niveau', $klant->niveau ?? '') }}" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div class="mb-4">
                        <label for="club" class="block text-sm font-medium text-gray-700 mb-2">Club / Ploeg</label>
                        <input type="text" 
                               name="club" 
                               id="club" 
                               value="{{ old('club', $klant->club ?? '') }}" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>

                <!-- Overige Informatie -->
                <h3 class="text-xl font-bold mt-6 mb-4">Overige Informatie</h3>
                
                <div class="mb-6">
                    <label for="herkomst" class="block text-sm font-medium text-gray-700 mb-2">Hoe bent u bij ons terechtgekomen?</label>
                    <input type="text" 
                           name="herkomst" 
                           id="herkomst" 
                           value="{{ old('herkomst', $klant->herkomst ?? '') }}" 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <!-- Profielfoto -->
                <div class="mb-6">
                    @php
                        // AVATAR PATH - EXACT ZELFDE ALS SHOW.BLADE.PHP
                        $currentAvatar = $klant->avatar;
                        $cacheKey = $klant->updated_at ? $klant->updated_at->timestamp : time();
                        
                        // Genereer correcte avatar URL op basis van environment
                        if ($currentAvatar) {
                            $avatarUrl = app()->environment('production') 
                                ? asset('uploads/' . $currentAvatar)
                                : asset('storage/' . $currentAvatar);
                        } else {
                            $avatarUrl = null;
                        }
                    @endphp
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ $currentAvatar ? 'Nieuwe profielfoto' : 'Profielfoto' }}</label>
                    <div class="flex items-center gap-4">
                        <!-- Avatar Preview met bestaande foto -->
                        <div class="relative flex-shrink-0" style="width:80px;height:80px;">
                            <label for="avatarInput" style="cursor:pointer;display:block;position:relative;">
                                @if($avatarUrl)
                                    <img id="avatarPreviewImg" src="{{ $avatarUrl }}?t={{ $cacheKey }}" alt="Avatar" class="rounded-lg object-cover" style="width:80px;height:80px;" />
                                @else
                                    <div id="avatarPreviewContainer" class="rounded-lg bg-gray-200 flex items-center justify-center text-gray-600 font-semibold" style="width:80px;height:80px;font-size:32px;">
                                        {{ strtoupper(substr($klant->voornaam ?? '?', 0, 1)) }}
                                    </div>
                                @endif
                                <!-- Camera overlay icon -->
                                <div style="position:absolute;bottom:4px;right:4px;background:rgba(200,225,235,0.95);border-radius:50%;width:28px;height:28px;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 4px rgba(0,0,0,0.1);">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#333" stroke-width="2">
                                        <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
                                        <circle cx="12" cy="13" r="4"></circle>
                                    </svg>
                                </div>
                            </label>
                            <input type="file" name="avatar" id="avatarInput" accept="image/*" style="display:none;">
                        </div>
                        
                        <!-- File info -->
                        <div class="flex-1">
                            <span id="avatarFileName" class="text-sm text-gray-600">
                                {{ $currentAvatar ? 'Huidige foto' : 'Geen foto' }}
                            </span>
                            <p class="text-xs text-gray-500 mt-1">Klik op de avatar om een nieuwe foto te kiezen</p>
                        </div>
                    </div>
                </div>

                <!-- Submit buttons -->
                <div class="mt-8 flex gap-3 justify-start">
                    <a href="{{ route('klanten.show', $klant) }}" 
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
    const nameEl = document.getElementById('avatarFileName');
    
    // Handle file selection
    if (input) {
        input.addEventListener('change', function() {
            const file = this.files && this.files[0];
            if (file) {
                nameEl.textContent = file.name;
                
                // Show image preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    // Check of er al een preview img bestaat
                    let previewImg = document.getElementById('avatarPreviewImg');
                    const previewContainer = document.getElementById('avatarPreviewContainer');
                    const label = input.parentElement;
                    
                    if (previewContainer) {
                        // Vervang placeholder door image
                        previewContainer.remove();
                        const img = document.createElement('img');
                        img.id = 'avatarPreviewImg';
                        img.src = e.target.result;
                        img.alt = 'Preview';
                        img.className = 'rounded-lg object-cover';
                        img.style.cssText = 'width:80px;height:80px;';
                        label.insertBefore(img, label.firstChild);
                    } else if (previewImg) {
                        // Update bestaande image
                        previewImg.src = e.target.result;
                    }
                };
                reader.readAsDataURL(file);
            } else {
                nameEl.textContent = '{{ $currentAvatar ? "Huidige foto" : "Geen foto" }}';
            }
        });
    }

    // Straatnaam autocomplete
    const straatnaamInput = document.getElementById('straatnaam');
    const straatnaamSuggesties = document.getElementById('straatnaam-suggesties');
    
    if (straatnaamInput && straatnaamSuggesties) {
        let timeout;
        let selectedIndex = -1;
        
        straatnaamInput.addEventListener('input', function() {
            const query = this.value.trim();
            const postcode = postcodeInput ? postcodeInput.value.trim() : '';
            
            if (query.length > 2) {
                clearTimeout(timeout);
                timeout = setTimeout(() => {
                    fetchStraatnamen(query, postcode);
                }, 300);
            } else {
                straatnaamSuggesties.classList.add('hidden');
            }
        });
        
        // Keyboard navigation
        straatnaamInput.addEventListener('keydown', function(e) {
            const items = straatnaamSuggesties.querySelectorAll('.suggestie-item');
            
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                selectedIndex = Math.min(selectedIndex + 1, items.length - 1);
                updateSelection(items);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                selectedIndex = Math.max(selectedIndex - 1, -1);
                updateSelection(items);
            } else if (e.key === 'Enter' && selectedIndex >= 0) {
                e.preventDefault();
                items[selectedIndex].click();
            } else if (e.key === 'Escape') {
                straatnaamSuggesties.classList.add('hidden');
            }
        });
        
        function updateSelection(items) {
            items.forEach((item, index) => {
                if (index === selectedIndex) {
                    item.classList.add('bg-indigo-100');
                } else {
                    item.classList.remove('bg-indigo-100');
                }
            });
        }
        
        function fetchStraatnamen(query, postcode) {
            // Belgische straten per postcode
            const belgischeStraten = {
                '9000': ['Korenmarkt', 'Veldstraat', 'Kouter', 'Graslei', 'Korenlei', 'Sint-Baafsplein', 'Vrijdagmarkt', 'Nederkouter', 'Woodrow Wilsonplein', 'Zuid', 'Brabantdam', 'Coupure', 'Ketelvest'],
                '2000': ['Meir', 'Groenplaats', 'Leysstraat', 'Schuttershofstraat', 'Kloosterstraat', 'Nationalestraat', 'De Keyserlei', 'Huidevettersstraat'],
                '1000': ['Wetstraat', 'Louizalaan', 'Koninginnelaan', 'Europaplein', 'Belliardstraat', 'Louizaplein', 'Kruidtuinlaan'],
                '8000': ['Markt', 'Steenstraat', 'Wollestraat', 'Noordzandstraat', 'Simon Stevinplein', 'Katelijnestraat', 'Hoogstraat'],
                '3000': ['Bondgenotenlaan', 'Tiensestraat', 'Naamsestraat', 'Grote Markt', 'Oude Markt', 'Muntstraat', 'Diestsestraat']
            };
            
            const straten = belgischeStraten[postcode] || [];
            const gefilterd = straten.filter(straat => 
                straat.toLowerCase().includes(query.toLowerCase())
            );
            
            if (gefilterd.length > 0) {
                showSuggesties(gefilterd);
            } else {
                straatnaamSuggesties.classList.add('hidden');
            }
        }
        
        function showSuggesties(suggesties) {
            straatnaamSuggesties.innerHTML = '';
            selectedIndex = -1;
            
            suggesties.forEach(suggestie => {
                const div = document.createElement('div');
                div.className = 'suggestie-item px-4 py-2 cursor-pointer hover:bg-indigo-100';
                div.textContent = suggestie;
                div.addEventListener('click', function() {
                    straatnaamInput.value = suggestie;
                    straatnaamSuggesties.classList.add('hidden');
                });
                straatnaamSuggesties.appendChild(div);
            });
            
            straatnaamSuggesties.classList.remove('hidden');
        }
        
        // Sluit suggesties bij klik buiten
        document.addEventListener('click', function(e) {
            if (!straatnaamInput.contains(e.target) && !straatnaamSuggesties.contains(e.target)) {
                straatnaamSuggesties.classList.add('hidden');
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
                try {
                    // Uitgebreide Belgische postcodes database
                    const belgianPostcodes = {
                        '9000': 'Gent', '9030': 'Mariakerke', '9031': 'Drongen', '9032': 'Wondelgem', '9040': 'Sint-Amandsberg', '9041': 'Oostakker', '9042': 'Desteldonk', '9050': 'Gentbrugge', '9051': 'Sint-Denijs-Westrem', '9052': 'Zwijnaarde', '9060': 'Zelzate', '9070': 'Destelbergen', '9080': 'Lochristi', '9090': 'Melle',
                        '9100': 'Sint-Niklaas', '9111': 'Belsele', '9112': 'Sinaai', '9120': 'Beveren', '9130': 'Kallo', '9140': 'Temse', '9150': 'Kruibeke', '9160': 'Lokeren', '9170': 'Sint-Gillis-Waas', '9180': 'Moerbeke', '9185': 'Wachtebeke', '9190': 'Stekene',
                        '9200': 'Dendermonde', '9220': 'Hamme', '9230': 'Wetteren', '9240': 'Zele', '9250': 'Waasmunster', '9260': 'Wichelen', '9270': 'Laarne', '9280': 'Lebbeke', '9290': 'Berlare',
                        '9300': 'Aalst', '9310': 'Moorsel', '9320': 'Erembodegem', '9340': 'Lede', '9400': 'Ninove', '9450': 'Haaltert',
                        '9500': 'Geraardsbergen', '9506': 'Geraardsbergen', '9520': 'Sint-Lievens-Houtem', '9521': 'Sint-Lievens-Houtem', '9540': 'Herzele', '9550': 'Herzele', '9560': 'Herzele', '9570': 'Lierde', '9571': 'Lierde', '9572': 'Lierde',
                        '9600': 'Ronse', '9620': 'Zottegem', '9630': 'Zwalm', '9636': 'Zwalm', '9660': 'Brakel', '9661': 'Brakel', '9667': 'Horebeke', '9680': 'Maarkedal', '9681': 'Maarkedal', '9690': 'Kluisbergen',
                        '9700': 'Oudenaarde', '9750': 'Kruisem', '9770': 'Kruisem', '9790': 'Wortegem-Petegem',
                        '9800': 'Deinze', '9820': 'Merelbeke', '9830': 'Sint-Martens-Latem', '9840': 'De Pinte', '9850': 'Nevele', '9860': 'Oosterzele', '9870': 'Zulte', '9880': 'Aalter', '9890': 'Gavere',
                        '9900': 'Eeklo', '9910': 'Knesselare', '9920': 'Lovendegem', '9930': 'Zomergem', '9940': 'Evergem', '9950': 'Waarschoot', '9960': 'Assenede', '9970': 'Kaprijke', '9980': 'Sint-Laureins', '9990': 'Maldegem', '9991': 'Adegem', '9992': 'Middelburg',
                        '2000': 'Antwerpen', '2018': 'Antwerpen', '2020': 'Antwerpen', '2030': 'Antwerpen', '2040': 'Antwerpen', '2050': 'Antwerpen', '2060': 'Antwerpen', '2100': 'Deurne', '2140': 'Borgerhout', '2150': 'Borsbeek', '2160': 'Wommelgem', '2170': 'Merksem', '2180': 'Ekeren',
                        '8000': 'Brugge', '8200': 'Sint-Michiels', '8210': 'Zedelgem', '8300': 'Knokke-Heist', '8310': 'Sint-Kruis', '8340': 'Damme', '8370': 'Blankenberge', '8380': 'Zeebrugge', '8400': 'Oostende', '8420': 'De Haan', '8430': 'Middelkerke', '8450': 'Bredene', '8460': 'Oudenburg', '8470': 'Gistel',
                        '8500': 'Kortrijk', '8510': 'Marke', '8520': 'Kuurne', '8530': 'Harelbeke', '8540': 'Deerlijk', '8550': 'Zwevegem', '8560': 'Wevelgem', '8570': 'Anzegem', '8580': 'Avelgem', '8590': 'Waregem'
                    };
                    
                    if (belgianPostcodes[postcode]) {
                        stadInput.value = belgianPostcodes[postcode];
                    }
                } catch (error) {
                    console.log('Postcode lookup failed');
                }
            }
        });
    }
});
</script>
@endsection
