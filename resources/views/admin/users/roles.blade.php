@extends('layouts.app')

@section('title', 'Rollen Beheer - Bonami Sportcoaching')

@section('content')
<style>
    /* Toggle switch styling - identiek aan organisaties */
    .toggle-switch {
        position: relative;
        display: inline-block;
        width: 48px;
        height: 24px;
    }

    .toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .toggle-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #cbd5e0;
        transition: .3s;
        border-radius: 24px;
    }

    .toggle-slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .3s;
        border-radius: 50%;
    }

    input:checked + .toggle-slider {
        background-color: #3b82f6;
    }

    input:checked + .toggle-slider:before {
        transform: translateX(24px);
    }
</style>
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">🔐 Rollen Beheer</h1>
            <p class="text-gray-600 mt-2">Overzicht van gebruikersrollen en rechten</p>
        </div>
        
        <div class="flex gap-4">
            <a href="{{ route('admin.users.index') }}" class="text-white px-4 py-2 rounded-lg transition-colors" style="background-color: #c8e1eb; color: #1f2937;" onmouseover="this.style.backgroundColor='#b0d4e0'" onmouseout="this.style.backgroundColor='#c8e1eb'">
                ← Terug naar Gebruikers
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-3 md:grid-cols-5 gap-3 mb-8">
        <div class="bg-white rounded-xl shadow-lg p-3">
            <div class="flex flex-col items-center text-center">
                <div class="bg-blue-100 p-2 rounded-lg mb-2">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                    </svg>
                </div>
                <p class="text-xs font-medium text-gray-500 uppercase mb-1">Totaal</p>
                <p class="text-2xl font-bold text-gray-900">{{ $roleStats['total'] ?? 0 }}</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-3">
            <div class="flex flex-col items-center text-center">
                <div class="bg-red-100 p-2 rounded-lg mb-2">
                    <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                    </svg>
                </div>
                <p class="text-xs font-medium text-gray-500 uppercase mb-1">Superadmin</p>
                <p class="text-2xl font-bold text-gray-900">{{ $roleStats['superadmin'] ?? 0 }}</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-3">
            <div class="flex flex-col items-center text-center">
                <div class="bg-purple-100 p-2 rounded-lg mb-2">
                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <p class="text-xs font-medium text-gray-500 uppercase mb-1">Admins</p>
                <p class="text-2xl font-bold text-gray-900">{{ $roleStats['admin'] ?? 0 }}</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-3">
            <div class="flex flex-col items-center text-center">
                <div class="bg-orange-100 p-2 rounded-lg mb-2">
                    <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m8 0V6a2 2 0 012 2v6a2 2 0 01-2 2H8a2 2 0 01-2-2V8a2 2 0 012-2V6"/>
                    </svg>
                </div>
                <p class="text-xs font-medium text-gray-500 uppercase mb-1">Medewerkers</p>
                <p class="text-2xl font-bold text-gray-900">{{ $roleStats['medewerker'] ?? 0 }}</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-3">
            <div class="flex flex-col items-center text-center">
                <div class="bg-cyan-100 p-2 rounded-lg mb-2">
                    <svg class="w-4 h-4 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <p class="text-xs font-medium text-gray-500 uppercase mb-1">Klanten</p>
                <p class="text-2xl font-bold text-gray-900">{{ $roleStats['klant'] ?? 0 }}</p>
            </div>
        </div>
    </div>

    <!-- Roles Overview -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Beschikbare Rollen</h2>
                @if(auth()->user()->role !== 'superadmin')
                    <span class="text-sm text-gray-500 bg-blue-50 px-3 py-1 rounded-full">
                        {{ $totalFeatures ?? 0 }} features beschikbaar voor uw organisatie
                    </span>
                @endif
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rol</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Beschrijving</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rechten</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aantal Gebruikers</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Acties</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($roles as $role)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                    {{ $role['color'] === 'purple' ? 'bg-purple-100 text-purple-800' : '' }}
                                    {{ $role['color'] === 'orange' ? 'bg-orange-100 text-orange-800' : '' }}
                                    {{ $role['color'] === 'cyan' ? 'bg-cyan-100 text-cyan-800' : '' }}">
                                    {{ $role['name'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">{{ $role['description'] }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-500">{{ $role['permissions'] }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-semibold text-gray-900">{{ $roleStats[$role['key']] ?? 0 }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <button 
                                    onclick="openFeatureModal('{{ $role['key'] }}', '{{ $role['name'] }}')"
                                    class="inline-flex items-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                                    </svg>
                                    Features Beheren
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Feature Management Modal --}}
    <div id="featureModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-lg bg-white">
            <div class="flex justify-between items-center mb-4 pb-3 border-b">
                <h3 class="text-xl font-semibold text-gray-900">
                    Features beheren voor: <span id="modalRoleName" class="text-blue-600"></span>
                </h3>
                <button onclick="closeFeatureModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="mb-4">
                <p class="text-sm text-gray-600">
                    Selecteer welke features beschikbaar zijn voor gebruikers met deze rol.
                </p>
            </div>

            <div id="featureList" class="grid grid-cols-1 md:grid-cols-2 gap-4 max-h-96 overflow-y-auto">
                {{-- Features worden hier dynamisch geladen --}}
            </div>

            <div class="mt-6 flex justify-end gap-3 pt-3 border-t">
                <button onclick="closeFeatureModal()" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg transition">
                    Sluiten
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// CSRF Token
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
let currentRole = null;

// Features data - gefilterd op organisatie voor admins
const featuresData = @json($features ?? []);

// Haal role features mapping op
const roleFeatures = @json($roleFeatures ?? []);

// Open feature modal
function openFeatureModal(roleKey, roleName) {
    console.log('🔓 Opening modal for role:', roleKey);
    currentRole = roleKey;
    
    document.getElementById('modalRoleName').textContent = roleName;
    document.getElementById('featureModal').classList.remove('hidden');
    
    loadFeaturesForRole(roleKey);
}

// Sluit modal
function closeFeatureModal() {
    console.log('🔒 Closing modal');
    document.getElementById('featureModal').classList.add('hidden');
    currentRole = null;
}

// Laad features voor specifieke rol
function loadFeaturesForRole(roleKey) {
    const featureList = document.getElementById('featureList');
    featureList.innerHTML = '';
    
    console.log('📋 Loading features for role:', roleKey);
    console.log('Available features:', featuresData);
    console.log('Role features mapping:', roleFeatures);
    
    featuresData.forEach(feature => {
        // Check of deze rol deze feature heeft
        const isEnabled = roleFeatures[roleKey] && roleFeatures[roleKey].includes(feature.id);
        
        console.log(`Feature ${feature.naam}: ${isEnabled ? 'enabled' : 'disabled'}`);
        
        const featureCard = `
            <div class="border rounded-lg p-4 ${isEnabled ? 'bg-blue-50 border-blue-200' : 'bg-gray-50 border-gray-200'}">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <svg class="w-5 h-5 ${isEnabled ? 'text-blue-600' : 'text-gray-400'}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <h4 class="font-medium ${isEnabled ? 'text-blue-900' : 'text-gray-700'}">
                                ${feature.naam}
                            </h4>
                            ${feature.is_premium ? '<span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Premium</span>' : ''}
                        </div>
                        
                        <p class="text-sm ${isEnabled ? 'text-blue-700' : 'text-gray-500'} mb-2">
                            ${feature.beschrijving}
                        </p>
                        
                        <div class="flex items-center gap-3 text-xs">
                            <span class="text-gray-500">
                                <span class="font-medium">Categorie:</span> ${feature.categorie.charAt(0).toUpperCase() + feature.categorie.slice(1)}
                            </span>
                        </div>
                    </div>

                    <div class="ml-4">
                        <label class="toggle-switch">
                            <input 
                                type="checkbox" 
                                class="role-feature-toggle"
                                data-feature-id="${feature.id}"
                                data-role-key="${roleKey}"
                                ${isEnabled ? 'checked' : ''}
                                onchange="toggleRoleFeature(this)"
                            >
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>
            </div>
        `;
        
        featureList.innerHTML += featureCard;
    });
}

// Toggle feature voor rol
function toggleRoleFeature(toggle) {
    const featureId = toggle.dataset.featureId;
    const roleKey = toggle.dataset.roleKey;
    const isActive = toggle.checked;
    
    console.log(`🔄 Toggling feature ${featureId} for role ${roleKey}: ${isActive}`);
    
    // Verstuur POST request
    fetch(`/admin/roles/${roleKey}/features/${featureId}/toggle`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            is_active: isActive
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log('✅ Response:', data);
        if (data.success) {
            console.log('✅ Role feature updated successfully!');
            
            // Update de roleFeatures mapping in geheugen
            if (!roleFeatures[roleKey]) {
                roleFeatures[roleKey] = [];
            }
            
            if (isActive) {
                if (!roleFeatures[roleKey].includes(parseInt(featureId))) {
                    roleFeatures[roleKey].push(parseInt(featureId));
                }
            } else {
                roleFeatures[roleKey] = roleFeatures[roleKey].filter(id => id !== parseInt(featureId));
            }
            
            // Update de UI styling
            const card = toggle.closest('.border');
            if (isActive) {
                card.classList.remove('bg-gray-50', 'border-gray-200');
                card.classList.add('bg-blue-50', 'border-blue-200');
            } else {
                card.classList.remove('bg-blue-50', 'border-blue-200');
                card.classList.add('bg-gray-50', 'border-gray-200');
            }
            
        } else {
            console.error('❌ Error:', data.message);
            toggle.checked = !isActive;
            alert('Fout: ' + (data.message || 'Er ging iets mis'));
        }
    })
    .catch(error => {
        console.error('❌ Network error:', error);
        toggle.checked = !isActive;
        alert('Er is een fout opgetreden: ' + error.message);
    });
}

// Sluit modal bij klik buiten
window.onclick = function(event) {
    const modal = document.getElementById('featureModal');
    if (event.target === modal) {
        closeFeatureModal();
    }
}
</script>
@endsection