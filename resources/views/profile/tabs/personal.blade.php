<h2 class="text-xl font-semibold text-gray-900 mb-6">Persoonlijke Gegevens</h2>

<!-- Avatar Section -->
<div class="mb-8">
    <div class="flex items-center space-x-6">
        <div class="relative">
            @php
                // GEBRUIK KLANT AVATAR - niet user avatar!
                $user = Auth::user();
                
                // Refresh user van DB om zeker te zijn dat we laatste avatar hebben
                $user->refresh();
                
                // Als user een klant is, haal avatar van klant record
                if ($user->role === 'klant' && $user->klant_id) {
                    $klant = \App\Models\Klant::find($user->klant_id);
                    $avatarPath = $klant ? $klant->avatar : null;
                    $cacheKey = $klant ? ($klant->updated_at ? $klant->updated_at->timestamp : time()) : time();
                } else {
                    // Voor beheerders/medewerkers: gebruik user avatar (avatar_path kolom)
                    $avatarPath = $user->avatar_path ?? $user->avatar;
                    $cacheKey = $user->updated_at ? $user->updated_at->timestamp : time();
                }
                
                $firstInitial = strtoupper(substr($user->name ?? 'U', 0, 1));
                
                \Log::info('🖼️ Avatar debug personal.blade', [
                    'user_id' => $user->id,
                    'user_role' => $user->role,
                    'klant_id' => $user->klant_id ?? 'geen',
                    'avatar_path' => $avatarPath ?? 'geen',
                    'avatar_in_db' => $user->avatar_path ?? 'geen fallback',
                    'cache_key' => $cacheKey
                ]);
            @endphp
            
            @if($avatarPath)
                <img class="user-avatar h-24 w-24 rounded-full object-cover border-4 border-gray-200" 
                     src="{{ asset('storage/' . $avatarPath) }}?t={{ $cacheKey }}" 
                     alt="Avatar"
                     id="profile-avatar-image">
            @else
                <div class="h-24 w-24 rounded-full bg-gray-200 flex items-center justify-center border-4 border-gray-200">
                    <span class="text-3xl font-semibold text-gray-600">{{ $firstInitial }}</span>
                </div>
            @endif
            
            <div class="absolute bottom-0 right-0 bg-blue-600 rounded-full p-2 cursor-pointer hover:bg-blue-700 transition-colors"
                 onclick="document.getElementById('avatar-upload').click()">
                <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
            </div>
        </div>
        <div>
            <h3 class="text-lg font-medium text-gray-900">Profielfoto</h3>
            <p class="text-sm text-gray-600">Upload een profielfoto. Maximaal 2MB, JPG, PNG of GIF.</p>
            
            @php
                // Voor ALLE gebruikers: gebruik profile.update.avatar route
                // Deze route werkt voor klanten, medewerkers én beheerders
                $uploadRoute = route('profile.update.avatar');
                
                \Log::info('🔧 Avatar upload route debug', [
                    'user_id' => $user->id,
                    'user_role' => $user->role ?? $user->rol ?? 'onbekend',
                    'klant_id' => $user->klant_id ?? 'geen',
                    'upload_route' => $uploadRoute
                ]);
            @endphp
            
            <form class="mt-2" action="{{ $uploadRoute }}" method="POST" enctype="multipart/form-data" id="avatar-upload-form">
                @csrf
                @method('POST')
                <input type="file" id="avatar-upload" name="avatar" accept="image/*" class="hidden" onchange="handleAvatarUpload(this)">
            </form>
        </div>
    </div>
</div>

<script>
// Avatar upload handler met force reload
function handleAvatarUpload(input) {
    if (input.files && input.files[0]) {
        console.log('📸 Avatar geselecteerd, uploading...');
        
        // Maak een FormData object
        const formData = new FormData(input.closest('form'));
        
        // Submit met fetch en dan force reload
        fetch(input.closest('form').action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).then(response => {
            console.log('✅ Upload complete, reloading page...');
            // Force complete page reload om cache te clearen
            window.location.href = window.location.href + '?refresh=' + Date.now();
        }).catch(error => {
            console.error('❌ Upload error:', error);
            alert('Upload mislukt. Probeer het opnieuw.');
        });
    }
}
</script>

<!-- Personal Information Form -->
<form class="ajax-form" action="{{ route('profile.update.personal') }}" method="POST">
    @csrf
    @method('PATCH')
    
    @php
        // Voor ALLE gebruikers: haal gegevens van klant record als die bestaat
        // Anders gebruik user gegevens als fallback
        $klantData = null;
        
        if ($user->klant_id) {
            $klantData = \App\Models\Klant::find($user->klant_id);
        }
        
        if ($klantData) {
            // Gebruik klant gegevens (voor klanten, medewerkers én beheerders met klant_id)
            $voornaamValue = $klantData->voornaam ?? '';
            $achternaamValue = $klantData->naam ?? '';
            $emailValue = $klantData->email ?? $user->email;
            $telefoonValue = $klantData->telefoonnummer ?? $klantData->telefoon ?? '';
            $geboortedatumValue = $klantData->geboortedatum ?? null;
            $adresValue = $klantData->adres ?? '';
            $stadValue = $klantData->stad ?? '';
            $postcodeValue = $klantData->postcode ?? '';
            
            \Log::info('📋 Personal tab - Data van klant record', [
                'user_id' => $user->id,
                'klant_id' => $klantData->id,
                'voornaam' => $voornaamValue,
                'achternaam' => $achternaamValue
            ]);
        } else {
            // Fallback: gebruik user gegevens (voor users zonder klant_id)
            // Split name kolom in voornaam en achternaam
            $nameParts = explode(' ', $user->name ?? '', 2);
            $voornaamValue = $nameParts[0] ?? '';
            $achternaamValue = $nameParts[1] ?? '';
            
            $emailValue = $user->email;
            $telefoonValue = $user->telefoonnummer ?? $user->telefoon ?? '';
            $geboortedatumValue = $user->geboortedatum ?? null;
            $adresValue = $user->adres ?? '';
            $stadValue = $user->stad ?? '';
            $postcodeValue = $user->postcode ?? '';
            
            \Log::info('📋 Personal tab - Data van user record (geen klant gekoppeld)', [
                'user_id' => $user->id,
                'email' => $emailValue,
                'name_in_db' => $user->name,
                'voornaam_split' => $voornaamValue,
                'achternaam_split' => $achternaamValue
            ]);
        }
    @endphp
    
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
        <!-- First Name -->
        <div>
            <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">
                Voornaam <span class="text-red-500">*</span>
            </label>
            <input type="text" name="first_name" id="first_name" 
                   value="{{ old('first_name', $voornaamValue) }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                   required>
        </div>

        <!-- Last Name -->
        <div>
            <label for="last_name" class="block text-sm font-medium text-gray-700 mb-2">
                Achternaam <span class="text-red-500">*</span>
            </label>
            <input type="text" name="last_name" id="last_name" 
                   value="{{ old('last_name', $achternaamValue) }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                   required>
        </div>

        <!-- Email -->
        <div class="sm:col-span-2">
            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                E-mailadres <span class="text-red-500">*</span>
            </label>
            <input type="email" name="email" id="email" 
                   value="{{ old('email', $emailValue) }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                   required>
        </div>

        <!-- Phone -->
        <div>
            <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                Telefoonnummer
            </label>
            <input type="tel" name="phone" id="phone" 
                   value="{{ old('phone', $telefoonValue) }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                   placeholder="+32 123 45 67 89">
        </div>

        <!-- Birth Date -->
        <div>
            <label for="birth_date" class="block text-sm font-medium text-gray-700 mb-2">
                Geboortedatum
            </label>
            <input type="date" 
                   id="geboortedatum" 
                   name="geboortedatum" 
                   value="{{ old('geboortedatum', $geboortedatumValue ? (is_string($geboortedatumValue) ? $geboortedatumValue : $geboortedatumValue->format('Y-m-d')) : '') }}" 
                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
        </div>

        <!-- Address -->
        <div class="sm:col-span-2">
            <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
                Adres
            </label>
            <textarea name="address" id="address" rows="2"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                      placeholder="Straat en huisnummer">{{ old('address', $adresValue) }}</textarea>
        </div>

        <!-- City -->
        <div>
            <label for="city" class="block text-sm font-medium text-gray-700 mb-2">
                Stad
            </label>
            <input type="text" name="city" id="city" 
                   value="{{ old('city', $stadValue) }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
        </div>

        <!-- Postal Code -->
        <div>
            <label for="postal_code" class="block text-sm font-medium text-gray-700 mb-2">
                Postcode
            </label>
            <input type="text" name="postal_code" id="postal_code" 
                   value="{{ old('postal_code', $postcodeValue) }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                   placeholder="1000">
        </div>
    </div>

    <!-- Save Button -->
    <div class="mt-6 flex justify-end">
        <button type="submit" 
                class="px-6 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
            Opslaan
        </button>
    </div>
</form>
