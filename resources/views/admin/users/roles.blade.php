@extends('layouts.app')

@section('title', 'Rollen Beheer - Bonami Sportcoaching')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">🔐 Rollen & Rechten Beheer</h1>
            <p class="text-gray-600 mt-2">Configureer welke tabbladen en tests elke rol kan gebruiken</p>
        </div>
        
        <a href="{{ route('admin.users.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition-colors">
            ← Terug naar Gebruikers
        </a>
    </div>

    <!-- Role Tabs -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-8">
        <div class="border-b border-gray-200">
            <nav class="flex">
                @foreach($roles as $index => $role)
                    <button onclick="showRole('{{ $role }}')" 
                            id="tab-{{ $role }}"
                            class="role-tab px-6 py-4 font-medium text-sm {{ $index === 0 ? 'bg-blue-50 text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700' }}">
                        {{ ucfirst($role) }}
                        @if($role === 'admin')
                            👑
                        @elseif($role === 'medewerker')
                            👔
                        @else
                            👤
                        @endif
                    </button>
                @endforeach
            </nav>
        </div>

        @foreach($roles as $index => $role)
            <div id="role-{{ $role }}" class="role-content p-6 {{ $index !== 0 ? 'hidden' : '' }}">
                <form method="POST" action="{{ route('admin.users.roles.update') }}">
                    @csrf
                    <input type="hidden" name="role" value="{{ $role }}">
                    
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Tab Permissions -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">📋 Tabblad Toegang</h3>
                            <div class="space-y-3">
                                @foreach($availableTabs as $tabKey => $tabName)
                                    <label class="flex items-center">
                                        <input type="checkbox" 
                                               name="tab_permissions[]" 
                                               value="{{ $tabKey }}"
                                               {{ in_array($tabKey, $rolePermissions[$role] ?? []) ? 'checked' : '' }}
                                               class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                        <span class="ml-3 text-gray-700">{{ $tabName }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        
                        <!-- Test Permissions -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">🧪 Test Rechten</h3>
                            <div class="space-y-4">
                                @foreach($availableTests as $testKey => $testName)
                                    @php
                                        $testPerms = $roleTestPermissions[$role][$testKey] ?? null;
                                    @endphp
                                    <div class="border border-gray-200 rounded-lg p-4">
                                        <h4 class="font-medium text-gray-800 mb-3">{{ $testName }}</h4>
                                        <div class="space-y-2">
                                            <label class="flex items-center">
                                                <input type="checkbox" 
                                                       name="test_permissions[{{ $testKey }}][]" 
                                                       value="access"
                                                       {{ $testPerms && $testPerms->can_access ? 'checked' : '' }}
                                                       class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                                <span class="ml-2 text-sm text-gray-600">👁️ Bekijken</span>
                                            </label>
                                            
                                            <label class="flex items-center">
                                                <input type="checkbox" 
                                                       name="test_permissions[{{ $testKey }}][]" 
                                                       value="create"
                                                       {{ $testPerms && $testPerms->can_create ? 'checked' : '' }}
                                                       class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500">
                                                <span class="ml-2 text-sm text-gray-600">➕ Aanmaken</span>
                                            </label>
                                            
                                            <label class="flex items-center">
                                                <input type="checkbox" 
                                                       name="test_permissions[{{ $testKey }}][]" 
                                                       value="edit"
                                                       {{ $testPerms && $testPerms->can_edit ? 'checked' : '' }}
                                                       class="w-4 h-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500">
                                                <span class="ml-2 text-sm text-gray-600">✏️ Bewerken</span>
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                            💾 Rechten Opslaan voor {{ ucfirst($role) }}
                        </button>
                    </div>
                </form>
            </div>
        @endforeach
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl shadow-lg p-6 text-center">
            <div class="bg-green-100 p-3 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-800 mb-2">✅ Alles Toekennen</h3>
            <p class="text-gray-600 text-sm mb-4">Geef een rol toegang tot alle functies</p>
            <button onclick="grantAllPermissions()" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors text-sm">
                Alle Rechten Geven
            </button>
        </div>
        
        <div class="bg-white rounded-xl shadow-lg p-6 text-center">
            <div class="bg-red-100 p-3 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-800 mb-2">❌ Alles Intrekken</h3>
            <p class="text-gray-600 text-sm mb-4">Verwijder alle rechten van een rol</p>
            <button onclick="revokeAllPermissions()" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors text-sm">
                Alle Rechten Intrekken
            </button>
        </div>
        
        <div class="bg-white rounded-xl shadow-lg p-6 text-center">
            <div class="bg-blue-100 p-3 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-800 mb-2">📋 Kopiëren</h3>
            <p class="text-gray-600 text-sm mb-4">Kopieer rechten van een andere rol</p>
            <button onclick="copyPermissions()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors text-sm">
                Rechten Kopiëren
            </button>
        </div>
    </div>
</div>

<script>
// Role tab switching
function showRole(role) {
    // Hide all content
    document.querySelectorAll('.role-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Remove active state from all tabs
    document.querySelectorAll('.role-tab').forEach(tab => {
        tab.classList.remove('bg-blue-50', 'text-blue-600', 'border-b-2', 'border-blue-600');
        tab.classList.add('text-gray-500', 'hover:text-gray-700');
    });
    
    // Show selected content
    document.getElementById('role-' + role).classList.remove('hidden');
    
    // Add active state to selected tab
    const activeTab = document.getElementById('tab-' + role);
    activeTab.classList.add('bg-blue-50', 'text-blue-600', 'border-b-2', 'border-blue-600');
    activeTab.classList.remove('text-gray-500', 'hover:text-gray-700');
}

// Grant all permissions for current visible role
function grantAllPermissions() {
    const activeContent = document.querySelector('.role-content:not(.hidden)');
    activeContent.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
        checkbox.checked = true;
    });
}

// Revoke all permissions for current visible role
function revokeAllPermissions() {
    const activeContent = document.querySelector('.role-content:not(.hidden)');
    activeContent.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
        checkbox.checked = false;
    });
}

// Copy permissions functionality
function copyPermissions() {
    alert('Feature komt binnenkort! Je kunt nu handmatig de checkboxes aanvinken.');
}
</script>
@endsection