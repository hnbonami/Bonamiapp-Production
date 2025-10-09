@extends('layouts.app')

@section('title', 'Gebruiker Bewerken - Bonami Sportcoaching')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">✏️ Gebruiker Bewerken</h1>
            <p class="text-gray-600 mt-2">{{ $user->name }} ({{ $user->email }})</p>
        </div>
        
        <a href="{{ route('admin.users.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition-colors">
            ← Terug naar Overzicht
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- User Edit Form -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-6">👤 Gebruikersinformatie</h2>
                
                <form method="POST" action="{{ route('admin.users.update', $user) }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Naam</label>
                            <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   required>
                            @error('name')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                            <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   required>
                            @error('email')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="role" class="block text-sm font-medium text-gray-700 mb-2">Rol</label>
                            <select id="role" name="role" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Admin</option>
                                <option value="medewerker" {{ old('role', $user->role) === 'medewerker' ? 'selected' : '' }}>Medewerker</option>
                                <option value="klant" {{ old('role', $user->role) === 'klant' ? 'selected' : '' }}>Klant</option>
                            </select>
                            @error('role')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select id="status" name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="active" {{ old('status', $user->status ?? 'active') === 'active' ? 'selected' : '' }}>Actief</option>
                                <option value="inactive" {{ old('status', $user->status ?? 'active') === 'inactive' ? 'selected' : '' }}>Inactief</option>
                                <option value="suspended" {{ old('status', $user->status ?? 'active') === 'suspended' ? 'selected' : '' }}>Geschorst</option>
                            </select>
                            @error('status')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="mb-6">
                        <label for="admin_notes" class="block text-sm font-medium text-gray-700 mb-2">Admin Notities</label>
                        <textarea id="admin_notes" name="admin_notes" rows="4" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                  placeholder="Interne notities over deze gebruiker...">{{ old('admin_notes', $user->admin_notes) }}</textarea>
                        @error('admin_notes')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div class="flex gap-4">
                        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                            💾 Opslaan
                        </button>
                        <a href="{{ route('admin.users.index') }}" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400 transition-colors">
                            ↩️ Annuleren
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- User Stats & Recent Activity -->
        <div class="space-y-6">
            <!-- Stats Card -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">📊 Statistieken</h3>
                
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Laatste login:</span>
                        <span class="font-medium">
                            @if($user->last_login_at)
                                {{ $user->last_login_at->format('d/m/Y H:i') }}
                            @else
                                Nooit
                            @endif
                        </span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600">Totaal logins:</span>
                        <span class="font-medium">{{ $user->login_count ?? 0 }}x</span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600">Account aangemaakt:</span>
                        <span class="font-medium">{{ $user->created_at->format('d/m/Y') }}</span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600">Laatst gewijzigd:</span>
                        <span class="font-medium">{{ $user->updated_at->format('d/m/Y H:i') }}</span>
                    </div>
                </div>
            </div>
            
            <!-- Recent Login Activity -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">🕐 Recente Activiteit</h3>
                
                @if($recentLogins->count() > 0)
                    <div class="space-y-3">
                        @foreach($recentLogins as $login)
                            <div class="border-l-4 border-blue-200 pl-4 py-2">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $login->login_at->format('d/m/Y H:i') }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    @if($login->logout_at)
                                        Sessie: {{ $login->sessionDurationHuman }}
                                    @else
                                        <span class="text-green-600">Nog ingelogd</span>
                                    @endif
                                </div>
                                @if($login->ip_address)
                                    <div class="text-xs text-gray-400">IP: {{ $login->ip_address }}</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-sm">Geen recente login activiteit</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection