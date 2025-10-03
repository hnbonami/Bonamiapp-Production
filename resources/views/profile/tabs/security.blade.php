<h2 class="text-xl font-semibold text-gray-900 mb-6">Account & Beveiliging</h2>

<!-- Password Change Section -->
<div class="bg-gray-50 rounded-lg p-6 mb-6">
    <h3 class="text-lg font-medium text-gray-900 mb-4">Wachtwoord wijzigen</h3>
    
    <form class="ajax-form" action="{{ route('profile.update.password') }}" method="POST">
        @csrf
        @method('PATCH')
        
        <div class="space-y-4">
            <!-- Current Password -->
            <div>
                <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">
                    Huidig wachtwoord <span class="text-red-500">*</span>
                </label>
                <input type="password" name="current_password" id="current_password" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                       required>
            </div>

            <!-- New Password -->
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                    Nieuw wachtwoord <span class="text-red-500">*</span>
                </label>
                <input type="password" name="password" id="password" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                       required>
            </div>

            <!-- Confirm Password -->
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                    Bevestig nieuw wachtwoord <span class="text-red-500">*</span>
                </label>
                <input type="password" name="password_confirmation" id="password_confirmation" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                       required>
            </div>
        </div>

        <div class="mt-4 flex justify-end">
            <button type="submit" 
                    class="px-6 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                Wachtwoord wijzigen
            </button>
        </div>
    </form>
</div>
