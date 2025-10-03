<h2 class="text-xl font-semibold text-gray-900 mb-6">Voorkeuren</h2>

<form class="ajax-form" action="{{ route('profile.update.preferences') }}" method="POST">
    @csrf
    @method('PATCH')
    
    <!-- Language Selection -->
    <div class="bg-gray-50 rounded-lg p-6 mb-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Taal & Regio</h3>
        
        <div class="space-y-4">
            <div>
                <label for="language" class="block text-sm font-medium text-gray-700 mb-2">
                    Interface taal
                </label>
                <select name="language" id="language" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    @foreach($languages as $code => $name)
                        <option value="{{ $code }}" {{ ($user->language ?? 'nl') == $code ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- Privacy Settings -->
    <div class="bg-gray-50 rounded-lg p-6 mb-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Privacy</h3>
        
        <div class="space-y-4">
            <div>
                <label for="profile_visibility" class="block text-sm font-medium text-gray-700 mb-2">
                    Profiel zichtbaarheid
                </label>
                <select name="profile_visibility" id="profile_visibility" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    <option value="private" {{ ($user->profile_visibility ?? 'private') == 'private' ? 'selected' : '' }}>
                        Privé - Alleen ik kan mijn profiel zien
                    </option>
                    <option value="staff_only" {{ ($user->profile_visibility ?? 'private') == 'staff_only' ? 'selected' : '' }}>
                        Staff Only - Alleen staff kan mijn profiel zien
                    </option>
                    <option value="public" {{ ($user->profile_visibility ?? 'private') == 'public' ? 'selected' : '' }}>
                        Openbaar - Iedereen kan mijn profiel zien
                    </option>
                </select>
            </div>
        </div>
    </div>

    <!-- Save Button -->
    <div class="flex justify-end">
        <button type="submit" 
                class="px-6 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
            Voorkeuren opslaan
        </button>
    </div>
</form>
