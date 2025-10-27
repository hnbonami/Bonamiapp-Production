@extends('layouts.app')

@section('content')
<style>
    /* EENVOUDIGE Toggle switch */
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
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="mb-6">
            <h1 class="text-3xl font-bold">{{ $organisatie->naam }}</h1>
            <p class="text-gray-600 mt-2">{{ $organisatie->email }}</p>
        </div>

        {{-- NIEUWE SECTIE: Features Beheer --}}
        <div class="bg-white rounded-lg shadow-sm p-6 mt-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900">Beschikbare Features</h3>
                <span class="text-sm text-gray-500">{{ $organisatie->features()->wherePivot('is_actief', true)->count() }} van {{ $totalFeatures }} features actief</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach(\App\Models\Feature::orderBy('categorie')->orderBy('sorteer_volgorde')->get() as $feature)
                    @php
                        // Check of organisatie deze feature heeft EN of deze actief is
                        $pivot = $organisatie->features()->where('feature_id', $feature->id)->first();
                        $isEnabled = $pivot ? $pivot->pivot->is_actief : false;
                    @endphp
                    
                    <div class="border rounded-lg p-4 {{ $isEnabled ? 'bg-blue-50 border-blue-200' : 'bg-gray-50 border-gray-200' }}">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <svg class="w-5 h-5 {{ $isEnabled ? 'text-blue-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <h4 class="font-medium {{ $isEnabled ? 'text-blue-900' : 'text-gray-700' }}">
                                        {{ $feature->naam }}
                                    </h4>
                                    @if($feature->is_premium)
                                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            Premium
                                        </span>
                                    @endif
                                </div>
                                
                                <p class="text-sm {{ $isEnabled ? 'text-blue-700' : 'text-gray-500' }} mb-2">
                                    {{ $feature->beschrijving }}
                                </p>
                                
                                <div class="flex items-center gap-3 text-xs mb-3">
                                    <span class="text-gray-500">
                                        <span class="font-medium">Categorie:</span> {{ ucfirst($feature->categorie) }}
                                    </span>
                                    @if($feature->is_premium && $feature->prijs_per_maand)
                                        <span class="text-gray-500">
                                            <span class="font-medium">Prijs:</span> €{{ number_format($feature->prijs_per_maand, 2) }}/mnd
                                        </span>
                                    @endif
                                </div>
                                
                                {{-- EXTRA: Custom Branding beheer knop --}}
                                @if($feature->key === 'custom_branding' && $isEnabled && auth()->user()->isAdminOfOrganisatie($organisatie->id))
                                    <a href="{{ route('branding.index') }}" class="inline-flex items-center gap-1 text-xs bg-purple-600 hover:bg-purple-700 text-white px-3 py-1.5 rounded-lg font-medium transition">
                                        <span>⚙️</span>
                                        <span>Branding Beheren</span>
                                    </a>
                                @endif
                            </div>

                            {{-- Toggle Switch --}}
                            <div class="ml-4">
                                <label class="toggle-switch">
                                    <input 
                                        type="checkbox" 
                                        class="feature-toggle"
                                        data-feature-id="{{ $feature->id }}"
                                        data-org-id="{{ $organisatie->id }}"
                                        {{ $isEnabled ? 'checked' : '' }}
                                    >
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Terug knop -->
        <div class="mt-6">
            <a href="{{ route('organisaties.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Terug naar overzicht
            </a>
        </div>
    </div>
</div>

<script>
// ✅ EENVOUDIGE, WERKENDE JAVASCRIPT
document.addEventListener('DOMContentLoaded', function() {
    console.log('🎯 Feature toggle script loaded');
    
    // Haal CSRF token op
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    
    // Vind alle toggles
    const toggles = document.querySelectorAll('.feature-toggle');
    console.log(`Found ${toggles.length} toggles`);
    
    // Voeg click handler toe aan ELKE toggle
    toggles.forEach(function(toggle) {
        toggle.addEventListener('change', function() {
            const featureId = this.dataset.featureId;
            const orgId = this.dataset.orgId;
            const isActive = this.checked;
            
            console.log(`🔄 Toggle clicked! Feature ${featureId}, Active: ${isActive}`);
            
            // Verstuur POST request
            fetch(`/organisaties/${orgId}/features/${featureId}/toggle`, {
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
                    console.log('✅ Feature updated successfully!');
                } else {
                    console.error('❌ Error:', data.message);
                    // Zet toggle terug bij fout
                    toggle.checked = !isActive;
                    alert('Fout: ' + data.message);
                }
            })
            .catch(error => {
                console.error('❌ Network error:', error);
                // Zet toggle terug bij fout
                toggle.checked = !isActive;
                alert('Er is een fout opgetreden');
            });
        });
    });
});
</script>

@endsection
