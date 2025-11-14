@extends('layouts.app')

@section('content')
<div class="container" style="max-width:800px;margin:2em auto;padding:2em;">
    <div style="margin-bottom:2em;">
        <a href="{{ route('dashboard.index') }}" style="color:#3b82f6;text-decoration:none;display:inline-flex;align-items:center;gap:0.5em;">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Terug naar dashboard
        </a>
    </div>

    <h1 class="text-3xl font-bold text-gray-900" style="margin-bottom:1.5em;">Widget Toevoegen</h1>

    @if($errors->any())
        <div style="background:#fef2f2;color:#dc2626;padding:1em;margin-bottom:1em;border-radius:5px;">
            <ul style="margin:0;padding-left:1.5em;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('dashboard.widgets.store') }}" method="POST" enctype="multipart/form-data" style="background:#fff;padding:2em;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,0.1);">
        @csrf

        <!-- Widget Type Selector -->
        <div style="margin-bottom:1.5em;">
            <label for="type" style="display:block;font-weight:600;margin-bottom:0.5em;">Widget Type *</label>
            <select name="type" id="type" required style="width:100%;padding:0.8em;border:1px solid #d1d5db;border-radius:7px;">
                <option value="text" {{ old('type', $type) === 'text' ? 'selected' : '' }}>📝 Tekst</option>
                <option value="metric" {{ old('type', $type) === 'metric' ? 'selected' : '' }}>📈 Metric (KPI)</option>
                <option value="image" {{ old('type', $type) === 'image' ? 'selected' : '' }}>🖼️ Afbeelding</option>
                <option value="button" {{ old('type', $type) === 'button' ? 'selected' : '' }}>🔘 Knop</option>
                <option value="chart" {{ old('type', $type) === 'chart' ? 'selected' : '' }}>📊 Grafiek</option>
            </select>
        </div>

        <!-- Titel -->
        <div style="margin-bottom:1.5em;">
            <label for="title" style="display:block;font-weight:600;margin-bottom:0.5em;">Titel *</label>
            <input type="text" name="title" id="title" value="{{ old('title') }}" required style="width:100%;padding:0.8em;border:1px solid #d1d5db;border-radius:7px;" placeholder="Bijvoorbeeld: Totaal Klanten">
        </div>

        <!-- Text Fields -->
        <div id="text-fields" style="display:none;">
            <div style="margin-bottom:1.5em;">
                <label for="text-content" style="display:block;font-weight:600;margin-bottom:0.5em;">Tekst *</label>
                <textarea name="content" id="text-content" rows="6" style="width:100%;padding:0.8em;border:1px solid #d1d5db;border-radius:7px;" placeholder="Voer je tekst in...">{{ old('content') }}</textarea>
            </div>
        </div>

        {{-- Metric Widget Specifieke Velden --}}
        <div id="metric-fields" class="space-y-4" style="display: none;">
            <div>
                <label for="metric_type" class="block text-sm font-medium text-gray-700 mb-2">
                    Metric Type *
                </label>
                <select name="metric_type" id="metric_type" class="w-full border-gray-300 rounded-lg">
                    <option value="custom">🔢 Custom Waarde (Typ zelf in)</option>
                    <optgroup label="📊 Mijn Statistieken" id="medewerker-metrics" style="display: none;">
                        <option value="mijn_bikefits">🚴 Mijn Bikefits</option>
                        <option value="mijn_inspanningstests">💪 Mijn Inspanningstests</option>
                        <option value="mijn_klanten">👥 Mijn Klanten</option>
                        <option value="mijn_omzet_maand">💰 Mijn Omzet (Deze Maand)</option>
                        <option value="mijn_omzet_kwartaal">📊 Mijn Omzet (Dit Kwartaal)</option>
                    </optgroup>
                    <optgroup label="🏢 Organisatie Statistieken" id="admin-metrics" style="display: none;">
                        <option value="totaal_klanten">👥 Totaal Klanten</option>
                        <option value="totaal_bikefits">🚴 Totaal Bikefits</option>
                        <option value="nieuwe_klanten_maand">✨ Nieuwe Klanten (Deze Maand)</option>
                        <option value="omzet_organisatie_maand">💰 Organisatie Omzet (Deze Maand)</option>
                        <option value="omzet_organisatie_kwartaal">📈 Organisatie Omzet (Dit Kwartaal)</option>
                        <option value="actieve_medewerkers">👨‍💼 Actieve Medewerkers</option>
                    </optgroup>
                </select>
                <p class="mt-1 text-sm text-gray-500">
                    Kies een automatische metric of typ een custom waarde hieronder
                </p>
            </div>
            
            <div id="manual-value-field">
                <label for="content" class="block text-sm font-medium text-gray-700 mb-2">
                    Waarde *
                </label>
                <input 
                    type="text" 
                    name="content" 
                    id="metric-content"
                    placeholder="Bijvoorbeeld: 150"
                    class="w-full border-gray-300 rounded-lg"
                >
                <p class="mt-1 text-sm text-gray-500">
                    Tip: Deze waarde kan je later dynamisch maken
                </p>
            </div>
            
            <div id="auto-value-preview" style="display: none;" class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <p class="text-sm font-medium text-blue-900 mb-2">📊 Live Preview:</p>
                <p id="metric-preview-value" class="text-3xl font-bold text-blue-600">...</p>
                <p class="text-xs text-blue-600 mt-1">Deze waarde wordt automatisch bijgewerkt</p>
            </div>
        </div>

        <!-- Image Fields -->
        <div id="image-fields" style="display:none;">
            <div style="margin-bottom:1.5em;">
                <label for="image" style="display:block;font-weight:600;margin-bottom:0.5em;">Afbeelding *</label>
                <input type="file" name="image" id="image" accept="image/*" style="width:100%;padding:0.8em;border:1px solid #d1d5db;border-radius:7px;">
                <small style="color:#6b7280;display:block;margin-top:0.5em;">Max 2MB - JPG, PNG, GIF</small>
            </div>
        </div>

        <!-- Button Fields -->
        <div id="button-fields" style="display:none;">
            <div style="margin-bottom:1.5em;">
                <label for="button-text" style="display:block;font-weight:600;margin-bottom:0.5em;">Knop Tekst *</label>
                <input type="text" name="button_text" id="button-text" value="{{ old('button_text') }}" style="width:100%;padding:0.8em;border:1px solid #d1d5db;border-radius:7px;" placeholder="Bijvoorbeeld: Nieuwe Klant">
            </div>
            <div style="margin-bottom:1.5em;">
                <label for="button-url" style="display:block;font-weight:600;margin-bottom:0.5em;">URL *</label>
                <select name="button_url_select" id="button-url-select" style="width:100%;padding:0.8em;border:1px solid #d1d5db;border-radius:7px;">
                    <option value="">-- Kies een pagina --</option>
                    <option value="/klanten">Klanten Overzicht</option>
                    <option value="/klanten/create">Nieuwe Klant</option>
                    <option value="/bikefits">Bikefits Overzicht</option>
                    <option value="/inspanningstesten">Inspanningstesten</option>
                    <option value="/testzadels">Testzadels</option>
                    <option value="custom">✏️ Custom URL (typ zelf in)</option>
                </select>
            </div>
            <div id="custom-url-field" style="display:none;margin-bottom:1.5em;">
                <label for="button-url-custom" style="display:block;font-weight:600;margin-bottom:0.5em;">Custom URL *</label>
                <input type="text" name="button_url" id="button-url-custom" value="{{ old('button_url') }}" style="width:100%;padding:0.8em;border:1px solid #d1d5db;border-radius:7px;" placeholder="/mijn-custom-pagina">
                <small style="color:#6b7280;display:block;margin-top:0.5em;">Begin met / voor interne links, of https:// voor externe links</small>
            </div>
            <!-- Hidden input voor pre-defined URLs -->
            <input type="hidden" name="button_url" id="button-url-hidden" value="{{ old('button_url') }}">
            <div style="margin-bottom:1.5em;">
                <label for="button_color" style="display:block;font-weight:600;margin-bottom:0.5em;">Knop Kleur</label>
                <input type="color" name="button_color" id="button_color" value="{{ old('button_color', '#c8e1eb') }}" style="width:100%;height:45px;border:1px solid #d1d5db;border-radius:7px;">
                <small style="color:#6b7280;display:block;margin-top:0.5em;">Standaard: #c8e1eb (lichtblauw)</small>
            </div>
        </div>

        <!-- Chart Fields -->
        <div id="chart-fields" style="display:none;">
            <div style="margin-bottom:1.5em;">
                <label for="chart-type" style="display:block;font-weight:600;margin-bottom:0.5em;">Grafiek Type *</label>
                <select name="chart_type" id="chart-type" style="width:100%;padding:0.8em;border:1px solid #d1d5db;border-radius:7px;">
                    <option value="">-- Kies een grafiek --</option>
                    <option value="diensten">📊 Diensten Verdeling (Doughnut)</option>
                    <option value="status">✅ Status Verdeling (Doughnut)</option>
                    <option value="omzet">💰 Omzet Trend (Lijn)</option>
                    <option value="medewerker">👤 Prestaties per Medewerker (Bar)</option>
                    <option value="commissie">💵 Commissie Verdeling (Bar)</option>
                    <option value="bikefits-totaal">🚴 Bikefits Totaal (Lijn)</option>
                    <option value="bikefits-medewerker">🚴 Bikefits per Medewerker (Bar)</option>
                    <option value="testen-totaal">⚡ Inspanningstesten Totaal (Lijn)</option>
                    <option value="testen-medewerker">⚡ Testen per Medewerker (Bar)</option>
                    @if(auth()->user()->role === 'superadmin')
                    <option value="organisaties">🏢 Omzet per Organisatie (Bar)</option>
                    @endif
                </select>
            </div>
        </div>

        <!-- Kleuren -->
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1em;margin-bottom:1.5em;">
            <div>
                <label for="background_color" style="display:block;font-weight:600;margin-bottom:0.5em;">Achtergrond Kleur</label>
                <input type="color" name="background_color" id="background_color" value="{{ old('background_color', '#ffffff') }}" style="width:100%;height:45px;border:1px solid #d1d5db;border-radius:7px;">
            </div>
            <div>
                <label for="text_color" style="display:block;font-weight:600;margin-bottom:0.5em;">Tekst Kleur</label>
                <input type="color" name="text_color" id="text_color" value="{{ old('text_color', '#111111') }}" style="width:100%;height:45px;border:1px solid #d1d5db;border-radius:7px;">
            </div>
        </div>

        <!-- Widget Grootte -->
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1em;margin-bottom:1.5em;">
            <div>
                <label for="grid_width" style="display:block;font-weight:600;margin-bottom:0.5em;">Breedte (1-12)</label>
                <input type="number" name="grid_width" id="grid_width" value="{{ old('grid_width', 4) }}" min="1" max="12" style="width:100%;padding:0.8em;border:1px solid #d1d5db;border-radius:7px;">
            </div>
            <div>
                <label for="grid_height" style="display:block;font-weight:600;margin-bottom:0.5em;">Hoogte (1-10)</label>
                <input type="number" name="grid_height" id="grid_height" value="{{ old('grid_height', 3) }}" min="1" max="10" style="width:100%;padding:0.8em;border:1px solid #d1d5db;border-radius:7px;">
            </div>
        </div>

        <!-- Visibility -->
        <div style="margin-bottom:1.5em;">
            <label for="visibility" style="display:block;font-weight:600;margin-bottom:0.5em;">Zichtbaarheid</label>
            <select name="visibility" id="visibility" required style="width:100%;padding:0.8em;border:1px solid #d1d5db;border-radius:7px;">
                @if(in_array(auth()->user()->role, ['admin', 'organisatie_admin', 'superadmin', 'super_admin']))
                    <option value="everyone" {{ old('visibility', 'medewerkers') === 'everyone' ? 'selected' : '' }}>👥 Iedereen (incl. klanten)</option>
                    <option value="medewerkers" {{ old('visibility', 'medewerkers') === 'medewerkers' ? 'selected' : '' }}>👔 Alleen Medewerkers</option>
                    <option value="only_me" {{ old('visibility') === 'only_me' ? 'selected' : '' }}>🔒 Alleen Ik</option>
                @else
                    <option value="medewerkers" {{ old('visibility', 'medewerkers') === 'medewerkers' ? 'selected' : '' }}>👔 Alleen Medewerkers</option>
                    <option value="only_me" {{ old('visibility') === 'only_me' ? 'selected' : '' }}>🔒 Alleen Ik</option>
                @endif
            </select>
            <small style="color:#6b7280;display:block;margin-top:0.5em;">
                @if(in_array(auth()->user()->role, ['admin', 'organisatie_admin', 'superadmin', 'super_admin']))
                    Als admin kan je kiezen wie de widget kan zien
                @else
                    Als medewerker kan je de widget delen met collega's of privé houden
                @endif
            </small>
        </div>

        <!-- Submit -->
        <div style="display:flex;gap:1em;">
            <button type="submit" style="background:#c8e1eb;color:#111;padding:0.8em 2em;border-radius:7px;font-weight:600;border:none;cursor:pointer;">
                Widget Toevoegen
            </button>
            <a href="{{ route('dashboard.index') }}" style="background:#e5e7eb;color:#111;padding:0.8em 2em;border-radius:7px;font-weight:600;text-decoration:none;display:inline-block;">
                Annuleren
            </a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('type');
    const textFields = document.getElementById('text-fields');
    const metricFields = document.getElementById('metric-fields');
    const imageFields = document.getElementById('image-fields');
    const buttonFields = document.getElementById('button-fields');
    const chartFields = document.getElementById('chart-fields');

    // Toggle fields op basis van type
    function toggleTypeFields() {
        const selectedType = typeSelect.value;
        
        // Verberg alle fields
        textFields.style.display = 'none';
        metricFields.style.display = 'none';
        imageFields.style.display = 'none';
        buttonFields.style.display = 'none';
        chartFields.style.display = 'none';

        // Toon relevante fields
        switch(selectedType) {
            case 'text':
                textFields.style.display = 'block';
                break;
            case 'metric':
                metricFields.style.display = 'block';
                break;
            case 'image':
                imageFields.style.display = 'block';
                break;
            case 'button':
                buttonFields.style.display = 'block';
                break;
            case 'chart':
                chartFields.style.display = 'block';
                break;
        }

        console.log('📝 Type changed to:', selectedType);
    }

    // Event listener voor type change
    typeSelect.addEventListener('change', toggleTypeFields);

    // Initialize op basis van huidige selectie
    toggleTypeFields();
    
    // Button URL toggle tussen pre-defined en custom
    const buttonUrlSelect = document.getElementById('button-url-select');
    const customUrlField = document.getElementById('custom-url-field');
    const customUrlInput = document.getElementById('button-url-custom');
    const hiddenUrlInput = document.getElementById('button-url-hidden');
    
    if (buttonUrlSelect && customUrlField) {
        buttonUrlSelect.addEventListener('change', function() {
            if (this.value === 'custom') {
                // Toon custom URL veld
                customUrlField.style.display = 'block';
                customUrlInput.required = true;
                hiddenUrlInput.disabled = true;
                customUrlInput.disabled = false;
            } else {
                // Verberg custom URL veld en gebruik pre-defined URL
                customUrlField.style.display = 'none';
                customUrlInput.required = false;
                customUrlInput.disabled = true;
                hiddenUrlInput.disabled = false;
                hiddenUrlInput.value = this.value;
            }
        });
    }
});

document.addEventListener('DOMContentLoaded', function() {
    const metricTypeSelect = document.getElementById('metric_type');
    const manualValueField = document.getElementById('manual-value-field');
    const autoValuePreview = document.getElementById('auto-value-preview');
    const metricPreviewValue = document.getElementById('metric-preview-value');
    
    // Toon juiste metrics op basis van user rol
    @if(auth()->user()->isMedewerker() && !auth()->user()->isBeheerder())
        // Medewerker (geen admin) - alleen eigen statistieken
        document.getElementById('medewerker-metrics').style.display = 'block';
        document.getElementById('admin-metrics').style.display = 'none';
    @elseif(auth()->user()->isBeheerder())
        // Admin - beide groepen zichtbaar
        document.getElementById('medewerker-metrics').style.display = 'block';
        document.getElementById('admin-metrics').style.display = 'block';
    @endif
    
    // Toggle tussen manual en auto value
    metricTypeSelect.addEventListener('change', function() {
        const isCustom = this.value === 'custom';
        
        manualValueField.style.display = isCustom ? 'block' : 'none';
        autoValuePreview.style.display = isCustom ? 'none' : 'block';
        
        if (!isCustom) {
            // Haal live data op voor geselecteerde metric
            fetchMetricValue(this.value);
        }
    });
    
    // Haal metric waarde op via AJAX
    function fetchMetricValue(metricType) {
        metricPreviewValue.textContent = 'Laden...';
        
        fetch('/dashboard/stats/metric', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ metric_type: metricType })
        })
        .then(response => response.json())
        .then(data => {
            metricPreviewValue.textContent = data.formatted;
            // Sla de waarde op in een hidden field voor het formulier
            document.getElementById('metric-content').value = data.formatted;
        })
        .catch(error => {
            console.error('Error fetching metric:', error);
            metricPreviewValue.textContent = 'Fout bij laden';
        });
    }
});
</script>
@endsection