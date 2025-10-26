@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Widget aanmaken</h1>
    </div>

    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
        <div class="p-6">
            <form method="POST" action="{{ route('dashboard.widgets.store') }}" enctype="multipart/form-data">
                @csrf
                
                <!-- Widget Type -->
                <div class="mb-6">
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-2">Widget Type *</label>
                    <select name="type" id="type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required onchange="toggleTypeFields(this.value)">
                        <option value="">Selecteer type...</option>
                        <option value="text" {{ request('type') === 'text' ? 'selected' : '' }}>📝 Tekst</option>
                        <option value="metric" {{ request('type') === 'metric' ? 'selected' : '' }}>📈 Metric (Getal)</option>
                        <option value="image" {{ request('type') === 'image' ? 'selected' : '' }}>🖼️ Afbeelding</option>
                        <option value="button" {{ request('type') === 'button' ? 'selected' : '' }}>🔘 Knop</option>
                        <option value="chart" {{ request('type') === 'chart' ? 'selected' : '' }}>📊 Grafiek</option>
                    </select>
                </div>

                <!-- Titel -->
                <div class="mb-6">
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Titel</label>
                    <input type="text" name="title" id="title" value="{{ old('title') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Bijvoorbeeld: Welkom bericht">
                </div>

                <!-- Text Content (voor text en metric types) -->
                <div id="text-fields" class="mb-6" style="display:none;">
                    <label for="content" class="block text-sm font-medium text-gray-700 mb-2">Tekst / Waarde</label>
                    <textarea name="content" id="content" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Voor tekst: Vul hier je bericht in...&#10;Voor metric: Vul een getal of waarde in (bijv. 42)">{{ old('content') }}</textarea>
                </div>

                <!-- Image Upload -->
                <div id="image-fields" class="mb-6" style="display:none;">
                    <label for="image" class="block text-sm font-medium text-gray-700 mb-2">Afbeelding</label>
                    <input type="file" name="image" id="image" accept="image/*" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <p class="mt-1 text-sm text-gray-500">Max 2MB, JPG/PNG</p>
                </div>

                <!-- Button Fields -->
                <div id="button-fields" style="display:none;">
                    <div class="mb-6">
                        <label for="button_text" class="block text-sm font-medium text-gray-700 mb-2">Knop Tekst</label>
                        <input type="text" name="button_text" id="button_text" value="{{ old('button_text') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Bijvoorbeeld: Ga naar klanten">
                    </div>
                    <div class="mb-6">
                        <label for="button_url" class="block text-sm font-medium text-gray-700 mb-2">Link / Route</label>
                        <select name="button_url" id="button_url" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Selecteer bestemming...</option>
                            <optgroup label="Klanten">
                                <option value="/klanten">Klantenlijst</option>
                                <option value="/klanten/create">Klant toevoegen</option>
                            </optgroup>
                            <optgroup label="Medewerkers">
                                <option value="/medewerkers">Medewerkerslijst</option>
                                <option value="/medewerkers/create">Medewerker toevoegen</option>
                            </optgroup>
                            <optgroup label="Overig">
                                <option value="/analytics">Analytics</option>
                                <option value="/sjablonen">Sjablonen</option>
                                <option value="/instellingen">Instellingen</option>
                            </optgroup>
                        </select>
                    </div>
                </div>

                <!-- Chart Type (voor chart type) -->
                        @if($type === 'chart')
        <!-- Grafiek Type selectie -->
        <div style="margin-bottom:1.5em;">
            <label style="display:block;font-weight:600;margin-bottom:0.5em;">📊 Grafiek Type *</label>
            <select name="chart_type" id="chart_type" required style="width:100%;padding:0.8em;border:1px solid #ddd;border-radius:7px;">
                <option value="">-- Selecteer grafiek type --</option>
                <option value="diensten">🎯 Diensten Verdeling</option>
                <option value="status">✅ Prestatie Status</option>
                <option value="omzet">📈 Omzet Trend</option>
                <option value="medewerker">🏆 Top Medewerkers</option>
                <option value="commissie">💰 Commissie Trend</option>
                <option value="bikefits-totaal">🚴 Totaal Bikefits</option>
                <option value="bikefits-medewerker">🚴 Bikefits per Medewerker</option>
                <option value="testen-totaal">🏃 Totaal Inspanningstesten</option>
                <option value="testen-medewerker">🏃 Testen per Medewerker</option>
                @if(auth()->user()->is_super_admin || in_array(auth()->user()->email, ['info@bonami-sportcoaching.be', 'admin@bonami-sportcoaching.be']))
                <option value="organisaties">🏢 Omzet per Organisatie</option>
                @endif
            </select>
            <small style="color:#666;display:block;margin-top:0.5em;">💡 Tip: De grafiek toont realtime data op basis van je permissies</small>
        </div>

        <!-- Data scope selectie -->
        <div style="margin-bottom:1.5em;">
            <label style="display:block;font-weight:600;margin-bottom:0.5em;">🔍 Data Scope *</label>
            <select name="chart_scope" id="chart_scope" required style="width:100%;padding:0.8em;border:1px solid #ddd;border-radius:7px;">
                @if(auth()->user()->is_super_admin || in_array(auth()->user()->email, ['info@bonami-sportcoaching.be', 'admin@bonami-sportcoaching.be']))
                    <option value="auto">Automatisch</option>
                    <option value="organisatie">Mijn Organisatie</option>
                    <option value="medewerker">Alleen Ik</option>
                    <option value="all">Alle Organisaties</option>
                @elseif(auth()->user()->organisatie_id && !auth()->user()->is_medewerker)
                    <option value="organisatie" selected>Mijn Organisatie</option>
                    <option value="medewerker">Alleen Ik</option>
                @else
                    <option value="medewerker" selected>Alleen Ik</option>
                @endif
            </select>
            <small style="color:#666;display:block;margin-top:0.5em;">📊 Bepaalt welke data getoond wordt in de grafiek</small>
        </div>

        <!-- Periode selectie -->
        <div style="margin-bottom:1.5em;">
            <label style="display:block;font-weight:600;margin-bottom:0.5em;">� Standaard Periode *</label>
            <select name="chart_periode" id="chart_periode" required style="width:100%;padding:0.8em;border:1px solid #ddd;border-radius:7px;">
                <option value="laatste-7-dagen">Laatste 7 dagen</option>
                <option value="laatste-30-dagen" selected>Laatste 30 dagen</option>
                <option value="laatste-90-dagen">Laatste 90 dagen</option>
                <option value="deze-week">Deze week</option>
                <option value="deze-maand">Deze maand</option>
                <option value="dit-kwartaal">Dit kwartaal</option>
                <option value="dit-jaar">Dit jaar</option>
            </select>
            <small style="color:#666;display:block;margin-top:0.5em;">⏱️ Data wordt automatisch ververst</small>
        </div>
        @endif

                <!-- Kleuren -->
                <h3 class="text-lg font-semibold mt-8 mb-4">Styling</h3>
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div>
                        <label for="background_color" class="block text-sm font-medium text-gray-700 mb-2">Achtergrondkleur</label>
                        <input type="color" name="background_color" id="background_color" value="#ffffff" class="mt-1 block w-full h-10 rounded-md border-gray-300 shadow-sm">
                    </div>
                    <div>
                        <label for="text_color" class="block text-sm font-medium text-gray-700 mb-2">Tekstkleur</label>
                        <input type="color" name="text_color" id="text_color" value="#000000" class="mt-1 block w-full h-10 rounded-md border-gray-300 shadow-sm">
                    </div>
                </div>

                <!-- Grootte -->
                <h3 class="text-lg font-semibold mt-8 mb-4">Afmetingen</h3>
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div>
                        <label for="grid_width" class="block text-sm font-medium text-gray-700 mb-2">Breedte (1-12) *</label>
                        <input type="number" name="grid_width" id="grid_width" min="1" max="12" value="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        <p class="mt-1 text-sm text-gray-500">12 = volle breedte, 6 = halve breedte, 4 = 1/3 breedte</p>
                    </div>
                    <div>
                        <label for="grid_height" class="block text-sm font-medium text-gray-700 mb-2">Hoogte (1-12) *</label>
                        <input type="number" name="grid_height" id="grid_height" min="1" max="12" value="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        <p class="mt-1 text-sm text-gray-500">Hogere waarde = grotere widget</p>
                    </div>
                </div>

                <!-- Zichtbaarheid -->
                <h3 class="text-lg font-semibold mt-8 mb-4">Wie mag deze widget zien?</h3>
                <div class="mb-6">
                    <select name="visibility" id="visibility" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        <option value="everyone">👥 Iedereen</option>
                        <option value="medewerkers">👔 Alleen medewerkers (en admins)</option>
                        <option value="only_me">🔒 Alleen ik</option>
                    </select>
                </div>

                <!-- Submit buttons -->
                <div class="mt-8 flex gap-3 justify-start">
                    <a href="{{ route('dashboard.index') }}" class="rounded-full px-6 py-2 text-gray-800 font-bold text-sm flex items-center justify-center hover:opacity-80 transition duration-200" style="background-color: #c8e1eb;">
                        Annuleren
                    </a>
                    <button type="submit" class="rounded-full px-6 py-2 text-gray-800 font-bold text-sm flex items-center justify-center hover:opacity-80 transition duration-200" style="background-color: #c8e1eb;">
                        Widget Aanmaken
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleTypeFields(type) {
    // Hide all type-specific fields
    document.getElementById('text-fields').style.display = 'none';
    document.getElementById('image-fields').style.display = 'none';
    document.getElementById('button-fields').style.display = 'none';
    document.getElementById('chart-fields').style.display = 'none';
    
    // Show relevant fields based on type
    if (type === 'text' || type === 'metric') {
        document.getElementById('text-fields').style.display = 'block';
    } else if (type === 'image') {
        document.getElementById('image-fields').style.display = 'block';
    } else if (type === 'button') {
        document.getElementById('button-fields').style.display = 'block';
    } else if (type === 'chart') {
        document.getElementById('chart-fields').style.display = 'block';
    }
}

// Trigger op pagina load als type is geselecteerd
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('type');
    if (typeSelect.value) {
        toggleTypeFields(typeSelect.value);
    }
});
</script>
@endsection