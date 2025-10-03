<h2 class="text-xl font-semibold text-gray-900 mb-6">Persoonlijke Gegevens</h2>

<!-- Avatar Section -->
<div class="mb-8">
    <div class="flex items-center space-x-6">
        <div class="relative">
            <img class="user-avatar h-24 w-24 rounded-full object-cover border-4 border-gray-200" 
                 src="{{ $user->avatar_path ? asset('storage/' . $user->avatar_path) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&color=7F9CF5&background=EBF4FF' }}" 
                 alt="Avatar">
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
            <form class="ajax-form mt-2" action="{{ route('profile.update.avatar') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="file" id="avatar-upload" name="avatar" accept="image/*" class="hidden" onchange="this.form.submit()">
            </form>
        </div>
    </div>
</div>

<!-- Personal Information Form -->
<form class="ajax-form" action="{{ route('profile.update.personal') }}" method="POST">
    @csrf
    @method('PATCH')
    
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
        <!-- First Name -->
        <div>
            <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">
                Voornaam <span class="text-red-500">*</span>
            </label>
            <input type="text" name="first_name" id="first_name" 
                   value="{{ old('first_name', $user->first_name ?? '') }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                   required>
        </div>

        <!-- Last Name -->
        <div>
            <label for="last_name" class="block text-sm font-medium text-gray-700 mb-2">
                Achternaam <span class="text-red-500">*</span>
            </label>
            <input type="text" name="last_name" id="last_name" 
                   value="{{ old('last_name', $user->last_name ?? '') }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                   required>
        </div>

        <!-- Email -->
        <div class="sm:col-span-2">
            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                E-mailadres <span class="text-red-500">*</span>
            </label>
            <input type="email" name="email" id="email" 
                   value="{{ old('email', $user->email) }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                   required>
        </div>

        <!-- Phone -->
        <div>
            <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                Telefoonnummer
            </label>
            <input type="tel" name="phone" id="phone" 
                   value="{{ $user->telefoonnummer }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                   placeholder="+32 123 45 67 89">
        </div>

        <!-- Birth Date -->
        <div>
            <label for="birth_date" class="block text-sm font-medium text-gray-700 mb-2">
                Geboortedatum
            </label>
            <input type="date" name="birth_date" id="birth_date" 
                   value="{{ $user->geboortedatum ? $user->geboortedatum->format('Y-m-d') : '' }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
        </div>

        <!-- Address -->
        <div class="sm:col-span-2">
            <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
                Adres
            </label>
            <textarea name="address" id="address" rows="2"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                      placeholder="Straat en huisnummer">{{ $user->adres }}</textarea>
        </div>

        <!-- City -->
        <div>
            <label for="city" class="block text-sm font-medium text-gray-700 mb-2">
                Stad
            </label>
            <input type="text" name="city" id="city" 
                   value="{{ $user->stad }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
        </div>

        <!-- Postal Code -->
        <div>
            <label for="postal_code" class="block text-sm font-medium text-gray-700 mb-2">
                Postcode
            </label>
            <input type="text" name="postal_code" id="postal_code" 
                   value="{{ $user->postcode }}"
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
