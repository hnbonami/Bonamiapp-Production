@extends('layouts.app')

@section('content')
<style>
/* Bonami Slider Styling */
.slider-bonami {
    -webkit-appearance: none;
    appearance: none;
    width: 100%;
    height: 8px;
    border-radius: 5px;
    background: linear-gradient(to right, #ef4444 0%, #f59e0b 50%, #10b981 100%);
    outline: none;
    opacity: 0.9;
    transition: opacity 0.2s;
}

.slider-bonami:hover {
    opacity: 1;
}

.slider-bonami::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: #3b82f6;
    cursor: pointer;
    border: 3px solid white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.slider-bonami::-moz-range-thumb {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: #3b82f6;
    cursor: pointer;
    border: 3px solid white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}
</style>

<!-- Chart.js library - versie zonder source maps voor geen console warnings -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<!-- CSRF Token voor AI AJAX calls -->
<meta name="csrf-token" content="{{ csrf_token() }}">
@if ($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded mb-6">
        <ul class="list-disc pl-5 m-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="py-2">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="p-8">
            <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-6 mt-0">Nieuwe Inspanningstest — {{ $klant->naam }}</h1>
        </div>
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <form method="POST" action="{{ route('inspanningstest.store', $klant->id) }}" id="inspanningstest-form">
                    @csrf
                    
                    <!-- Basis informatie -->
                    <h3 class="text-xl font-bold mb-4">Test Informatie</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="mb-4">
                            <label for="datum" class="block text-sm font-medium text-gray-700 mb-2">Testdatum</label>
                            <input type="date" 
                                   name="testdatum" 
                                   id="datum" 
                                   value="{{ old('testdatum', now()->format('Y-m-d')) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                   required>
                            <p class="text-xs text-gray-500 mt-1">Standaard ingesteld op vandaag, maar aanpasbaar</p>
                        </div>

                        <div class="mb-4">
                            <label for="testtype" class="block text-sm font-medium text-gray-700 mb-2">Testtype</label>
                            <select name="testtype" 
                                    id="testtype"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    required>
                                <option value="">Selecteer testtype</option>
                                <option value="looptest" {{ old('testtype') == 'looptest' ? 'selected' : '' }}>Inspanningstest Lopen</option>
                                <option value="fietstest" {{ old('testtype') == 'fietstest' ? 'selected' : '' }}>Inspanningstest Fietsen</option>
                                <option value="veldtest_lopen" {{ old('testtype') == 'veldtest_lopen' ? 'selected' : '' }}>Veldtest Lopen</option>
                                <option value="veldtest_fietsen" {{ old('testtype') == 'veldtest_fietsen' ? 'selected' : '' }}>Veldtest Fietsen</option>
                                <option value="veldtest_zwemmen" {{ old('testtype') == 'veldtest_zwemmen' ? 'selected' : '' }}>Veldtest Zwemmen</option>
                            </select>
                        </div>
                    </div>

                    <!-- Algemene informatie -->
                    <h3 class="text-xl font-bold mt-6 mb-4">Algemene Informatie</h3>
                    
                    <div class="mb-4">
                        <label for="specifieke_doelstellingen" class="block text-sm font-medium text-gray-700 mb-2">Specifieke doelstellingen</label>
                        <textarea name="specifieke_doelstellingen" 
                                  id="specifieke_doelstellingen"
                                  rows="3"
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                  placeholder="Bijv. verbetering van uithoudingsvermogen, gewichtsverlies, prestatieoptimalisatie...">{{ old('specifieke_doelstellingen') }}</textarea>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="mb-4">
                            <label for="lichaamslengte_cm" class="block text-sm font-medium text-gray-700 mb-2">Lengte (cm)</label>
                            <input type="number" 
                                   step="0.1"
                                   name="lichaamslengte_cm" 
                                   id="lichaamslengte_cm"
                                   value="{{ old('lichaamslengte_cm', $klant->lengte_cm) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div class="mb-4">
                            <label for="lichaamsgewicht_kg" class="block text-sm font-medium text-gray-700 mb-2">Gewicht (kg)</label>
                            <input type="number" 
                                   step="0.1"
                                   name="lichaamsgewicht_kg" 
                                   id="lichaamsgewicht_kg"
                                   value="{{ old('lichaamsgewicht_kg', $klant->gewicht_kg) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div class="mb-4">
                            <label for="bmi" class="block text-sm font-medium text-gray-700 mb-2">BMI</label>
                            <input type="number" 
                                   step="0.1"
                                   name="bmi" 
                                   id="bmi"
                                   value="{{ old('bmi') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div class="mb-4">
                            <label for="vetpercentage" class="block text-sm font-medium text-gray-700 mb-2">Vetpercentage (%)</label>
                            <input type="number" 
                                   step="0.1"
                                   name="vetpercentage" 
                                   id="vetpercentage"
                                   value="{{ old('vetpercentage') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div class="mb-4">
                            <label for="hartslag_rust_bpm" class="block text-sm font-medium text-gray-700 mb-2">Hartslag rust (bpm)</label>
                            <input type="number" 
                                   name="hartslag_rust_bpm" 
                                   id="hartslag_rust_bpm"
                                   value="{{ old('hartslag_rust_bpm') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div class="mb-4">
                            <label for="maximale_hartslag_bpm" class="block text-sm font-medium text-gray-700 mb-2">Hartslag max (bpm)</label>
                            <input type="number" 
                                   name="maximale_hartslag_bpm" 
                                   id="maximale_hartslag_bpm"
                                   value="{{ old('maximale_hartslag_bpm') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div class="mb-4">
                            <label for="buikomtrek_cm" class="block text-sm font-medium text-gray-700 mb-2">Buikomtrek (cm)</label>
                            <input type="number" 
                                   name="buikomtrek_cm" 
                                   id="buikomtrek_cm"
                                   value="{{ old('buikomtrek_cm') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>
                    <!-- Huidige Trainingstatus -->
<div class="bg-white p-6 rounded-lg shadow mb-6">
    <h3 class="text-lg font-semibold mb-4 text-gray-800">Huidige Trainingstatus</h3>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
        <!-- Slaapkwaliteit -->
        <div>
            <label for="slaapkwaliteit" class="block text-sm font-medium text-gray-700 mb-1">
                Slaapkwaliteit
                <span class="text-xs text-gray-500">(0 = slecht, 10 = perfect)</span>
            </label>
            <input type="range" 
                   id="slaapkwaliteit" 
                   name="slaapkwaliteit" 
                   min="0" 
                   max="10" 
                   value="{{ old('slaapkwaliteit', 5) }}"
                   class="w-full slider-bonami"
                   oninput="updateScoreDisplay('slaapkwaliteit')">
            <div class="flex justify-between text-xs text-gray-600 mt-1">
                <span>0 (slecht)</span>
                <span id="slaapkwaliteit_value" class="font-semibold">5</span>
                <span>10 (perfect)</span>
            </div>
        </div>

        <!-- Eetlust -->
        <div>
            <label for="eetlust" class="block text-sm font-medium text-gray-700 mb-1">
                Eetlust
                <span class="text-xs text-gray-500">(0 = slecht, 10 = perfect)</span>
            </label>
            <input type="range" 
                   id="eetlust" 
                   name="eetlust" 
                   min="0" 
                   max="10" 
                   value="{{ old('eetlust', 5) }}"
                   class="w-full slider-bonami"
                   oninput="updateScoreDisplay('eetlust')">
            <div class="flex justify-between text-xs text-gray-600 mt-1">
                <span>0 (slecht)</span>
                <span id="eetlust_value" class="font-semibold">5</span>
                <span>10 (perfect)</span>
            </div>
        </div>

        <!-- Gevoel op training -->
        <div>
            <label for="gevoel_op_training" class="block text-sm font-medium text-gray-700 mb-1">
                Gevoel op training
                <span class="text-xs text-gray-500">(0 = slecht, 10 = perfect)</span>
            </label>
            <input type="range" 
                   id="gevoel_op_training" 
                   name="gevoel_op_training" 
                   min="0" 
                   max="10" 
                   value="{{ old('gevoel_op_training', 5) }}"
                   class="w-full slider-bonami"
                   oninput="updateScoreDisplay('gevoel_op_training')">
            <div class="flex justify-between text-xs text-gray-600 mt-1">
                <span>0 (slecht)</span>
                <span id="gevoel_op_training_value" class="font-semibold">5</span>
                <span>10 (perfect)</span>
            </div>
        </div>

        <!-- Stressniveau -->
        <div>
            <label for="stressniveau" class="block text-sm font-medium text-gray-700 mb-1">
                Stressniveau
                <span class="text-xs text-gray-500">(0 = veel stress, 10 = geen stress)</span>
            </label>
            <input type="range" 
                   id="stressniveau" 
                   name="stressniveau" 
                   min="0" 
                   max="10" 
                   value="{{ old('stressniveau', 5) }}"
                   class="w-full slider-bonami"
                   oninput="updateScoreDisplay('stressniveau')">
            <div class="flex justify-between text-xs text-gray-600 mt-1">
                <span>0 (veel)</span>
                <span id="stressniveau_value" class="font-semibold">5</span>
                <span>10 (geen)</span>
            </div>
        </div>
    </div>

    <!-- Gemiddelde Score -->
    <div class="bg-blue-50 p-4 rounded-lg mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-2">Gemiddelde Score</label>
        <div class="text-3xl font-bold text-blue-600" id="gemiddelde_display">5.0</div>
        <input type="hidden" name="gemiddelde_trainingstatus" id="gemiddelde_trainingstatus" value="5.0">
        <p class="text-xs text-gray-500 mt-1">Automatisch berekend gemiddelde van bovenstaande scores</p>
    </div>

    <!-- Training dag voor test -->
    <div class="mb-4">
        <label for="training_dag_voor_test" class="block text-sm font-medium text-gray-700 mb-1">
            Training dag voor de test
        </label>
        <textarea id="training_dag_voor_test" 
                  name="training_dag_voor_test" 
                  rows="3" 
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="Beschrijf de training van 1 dag voor de test...">{{ old('training_dag_voor_test') }}</textarea>
    </div>

    <!-- Training 2 dagen voor test -->
    <div>
        <label for="training_2d_voor_test" class="block text-sm font-medium text-gray-700 mb-1">
            Training 2 dagen voor de test
        </label>
        <textarea id="training_2d_voor_test" 
                  name="training_2d_voor_test" 
                  rows="3" 
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="Beschrijf de training van 2 dagen voor de test...">{{ old('training_2d_voor_test') }}</textarea>
    </div>
</div>

<script>
// Update score display en bereken gemiddelde
function updateScoreDisplay(fieldId) {
    const slider = document.getElementById(fieldId);
    const display = document.getElementById(fieldId + '_value');
    display.textContent = slider.value;
    
    // Bereken gemiddelde
    berekenGemiddelde();
}

function berekenGemiddelde() {
    const slaap = parseInt(document.getElementById('slaapkwaliteit').value) || 0;
    const eetlust = parseInt(document.getElementById('eetlust').value) || 0;
    const gevoel = parseInt(document.getElementById('gevoel_op_training').value) || 0;
    const stress = parseInt(document.getElementById('stressniveau').value) || 0;
    
    const gemiddelde = ((slaap + eetlust + gevoel + stress) / 4).toFixed(1);
    
    document.getElementById('gemiddelde_display').textContent = gemiddelde;
    document.getElementById('gemiddelde_trainingstatus').value = gemiddelde;
}

// Initialiseer bij laden
document.addEventListener('DOMContentLoaded', function() {
    updateScoreDisplay('slaapkwaliteit');
    updateScoreDisplay('eetlust');
    updateScoreDisplay('gevoel_op_training');
    updateScoreDisplay('stressniveau');
});
</script>
                    <!-- Protocol -->
                    <h3 class="text-xl font-bold mt-6 mb-4">Test Protocol</h3>
                    
                    <!-- Alle protocol velden in één grid -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <!-- Testlocatie -->
                        <div class="mb-4">
                            <label for="testlocatie" class="block text-sm font-medium text-gray-700 mb-2">Testlocatie</label>
                            <input type="text" 
                                   name="testlocatie" 
                                   id="testlocatie"
                                   value="{{ old('testlocatie', 'Bonami sportmedisch centrum') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <!-- Protocol dropdown voor veldtest lopen -->
                        <div id="protocol-field-lopen" class="mb-4" style="display: none;">
                            <label for="protocol" class="block text-sm font-medium text-gray-700 mb-2">Protocol</label>
                            <select name="protocol" 
                                    id="protocol"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Selecteer protocol</option>
                                <option value="4 x 2000m en 1 x 600m" {{ old('protocol') == '4 x 2000m en 1 x 600m' ? 'selected' : '' }}>4 x 2000m en 1 x 600m</option>
                                <option value="4 x 1600m en 1 x 600m" {{ old('protocol') == '4 x 1600m en 1 x 600m' ? 'selected' : '' }}>4 x 1600m en 1 x 600m</option>
                                <option value="3 x 1600m en 1 x 600m" {{ old('protocol') == '3 x 1600m en 1 x 600m' ? 'selected' : '' }}>3 x 1600m en 1 x 600m</option>
                                <option value="4 x 1200m en 1 x 600m" {{ old('protocol') == '4 x 1200m en 1 x 600m' ? 'selected' : '' }}>4 x 1200m en 1 x 600m</option>
                                <option value="4 x 800m en 1 x 400m" {{ old('protocol') == '4 x 800m en 1 x 400m' ? 'selected' : '' }}>4 x 800m en 1 x 400m</option>
                            </select>
                        </div>

                        <!-- Protocol dropdown voor veldtest zwemmen -->
                        <div id="protocol-field-zwemmen" class="mb-4" style="display: none;">
                            <label for="protocol_zwemmen" class="block text-sm font-medium text-gray-700 mb-2">Protocol</label>
                            <select name="protocol" 
                                    id="protocol_zwemmen"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Selecteer protocol</option>
                                <option value="4 x 200m" {{ old('protocol') == '4 x 200m' ? 'selected' : '' }}>4 x 200m</option>
                                <option value="5 x 200m" {{ old('protocol') == '5 x 200m' ? 'selected' : '' }}>5 x 200m</option>
                                <option value="3 x 200m en 1 x 400m" {{ old('protocol') == '3 x 200m en 1 x 400m' ? 'selected' : '' }}>3 x 200m en 1 x 400m</option>
                            </select>
                        </div>
                        
                        <!-- Standaard protocol veld 1 -->
                        <div id="standard-protocol-field-1" class="mb-4">
                            <label for="startwattage" class="block text-sm font-medium text-gray-700 mb-2">Start (watt)</label>
                            <input type="number" 
                                   name="startwattage" 
                                   id="startwattage"
                                   value="{{ old('startwattage', 8) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <!-- Standaard protocol veld 2 -->
                        <div id="standard-protocol-field-2" class="mb-4">
                            <label for="stappen_min" class="block text-sm font-medium text-gray-700 mb-2">Stappen (minuten)</label>
                            <input type="number" 
                                   name="stappen_min" 
                                   id="stappen_min"
                                   value="{{ old('stappen_min', 3) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <!-- Standaard protocol veld 3 -->
                        <div id="standard-protocol-field-3" class="mb-4">
                            <label for="stappen_watt" class="block text-sm font-medium text-gray-700 mb-2">Stappen (watt)</label>
                            <input type="number" 
                                   name="stappen_watt" 
                                   id="stappen_watt"
                                   value="{{ old('stappen_watt', 1) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>

                    <!-- Weersomstandigheden veld voor veldtesten -->
                    <div id="weersomstandigheden-field" class="mb-4" style="display: none;">
                        <label for="weersomstandigheden" class="block text-sm font-medium text-gray-700 mb-2">Weersomstandigheden</label>
                        <input type="text" 
                               name="weersomstandigheden" 
                               id="weersomstandigheden"
                               value="{{ old('weersomstandigheden') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                               placeholder="Bijv. zonnig, 20°C, wind 2 Bft">
                    </div>

                    <!-- Testresultaten (Stappen) -->
                    <h3 class="text-xl font-bold mt-6 mb-4">Testresultaten</h3>
                    <div id="testresultaten-table" class="mb-6">
                        <table class="w-full border border-gray-300 mb-4">
                            <thead id="testresultaten-header">
                                <!-- Headers worden dynamisch tegengeeregend -->
                            </thead>
                            <tbody id="testresultaten-body">
                                <!-- Rijen worden dynamisch gegenereerd -->
                            </tbody>
                        </table>
                        <div class="flex gap-2">
                            <button type="button" onclick="addRow()" class="font-bold py-2 px-4 rounded border-2 border-gray-400 text-gray-800 hover:bg-gray-100 transition duration-200" style="background-color: #c8e1eb;">
                                Rij toevoegen
                            </button>
                            <button type="button" onclick="removeLastRow()" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                                Laatste rij verwijderen
                            </button>
                        </div>
                    </div>

                    <!-- Grafiek Analyse -->
                    <h3 class="text-xl font-bold mt-6 mb-4">Grafiek Analyse</h3>
                    
                    <div class="mb-6">
                        <div class="mb-4">
                            <label for="analyse_methode" class="block text-sm font-medium text-gray-700 mb-2">Analyse methode</label>
                            <select name="analyse_methode" 
                                    id="analyse_methode"
                                    class="mt-1 block w-48 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Selecteer methode</option>
                                <option value="dmax" {{ old('analyse_methode') == 'dmax' ? 'selected' : '' }}>D-max</option>
                                <option value="dmax_modified" {{ old('analyse_methode') == 'dmax_modified' ? 'selected' : '' }}>D-max Modified</option>
                                <option value="lactaat_steady_state" {{ old('analyse_methode') == 'lactaat_steady_state' ? 'selected' : '' }}>Lactaat Steady State</option>
                                <option value="hartslag_deflectie" {{ old('analyse_methode') == 'hartslag_deflectie' ? 'selected' : '' }}>Hartslagdeflectie</option>
                                <option value="handmatig" {{ old('analyse_methode') == 'handmatig' ? 'selected' : '' }}>Handmatig</option>
                            </select>
                        </div>
                        
                        <!-- D-max Modified configuratie veld -->
                        <div id="dmax-modified-config" class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg" style="display: none;">
                            <label for="dmax_modified_threshold" class="block text-sm font-medium text-blue-800 mb-2">
                                🔧 D-max Modified Drempelwaarde (mmol/L)
                            </label>
                            <div class="flex items-center gap-3">
                                <span class="text-sm text-gray-600">Baseline +</span>
                                <input type="number" 
                                       step="0.1" 
                                       min="0.1" 
                                       max="2.0" 
                                       name="dmax_modified_threshold" 
                                       id="dmax_modified_threshold"
                                       value="{{ old('dmax_modified_threshold', '0.4') }}"
                                       class="w-20 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-center">
                                <span class="text-sm text-gray-600">mmol/L</span>
                            </div>
                            <p class="text-xs text-blue-600 mt-1">
                                💡 Standaard: 0.4 mmol/L (aanpasbaar per 0.1 mmol/L)
                            </p>
                        </div>
                        
                        <!-- Grafiek container -->
                        <div id="grafiek-container" class="bg-gray-50 border border-gray-300 rounded-lg p-4 mb-4" style="height: 400px; display: none;">
                            <canvas id="hartslagLactaatGrafiek" width="800" height="350"></canvas>
                        </div>
                        
                        <!-- Grafiek instructies -->
                        <div id="grafiek-instructies" class="text-sm text-gray-600 mb-4" style="display: none;">
                            <p><strong>Grafiek Analyse:</strong></p>
                            <ul class="list-disc pl-5">
                                <li><span class="text-blue-600">Blauwe lijn:</span> Hartslag progressie</li>
                                <li><span class="text-green-600">Groene lijn:</span> Lactaat progressie</li>
                                <li><span class="text-red-600">Rode gestreepte lijn:</span> Aërobe drempel (versleepbaar)</li>
                                <li><span class="text-yellow-600">Oranje gestreepte lijn:</span> Anaërobe drempel (versleepbaar)</li>
                                <li>Sleep de drempellijnen om handmatig aan te passen</li>
                                <li>Waarden worden automatisch bijgewerkt in de velden hieronder</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Drempels en Besluiten -->
                    <h3 class="text-xl font-bold mt-6 mb-4">Drempels en Besluiten</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="mb-4">
                            <label for="aerobe_drempel_vermogen" id="label_aerobe_drempel_vermogen" class="block text-sm font-medium text-gray-700 mb-2">Aërobe drempel - Vermogen (Watt)</label>
                            <input type="number" 
                                   step="any"
                                   name="aerobe_drempel_vermogen" 
                                   id="aerobe_drempel_vermogen"
                                   value="{{ old('aerobe_drempel_vermogen') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                   placeholder="Automatisch berekend">
                        </div>

                        <div class="mb-4">
                            <label for="aerobe_drempel_hartslag" class="block text-sm font-medium text-gray-700 mb-2">Aërobe drempel - Hartslag (bpm)</label>
                            <input type="number" 
                                   step="1"
                                   name="aerobe_drempel_hartslag" 
                                   id="aerobe_drempel_hartslag"
                                   value="{{ old('aerobe_drempel_hartslag') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                   placeholder="Automatisch berekend">
                        </div>

                        <div class="mb-4">
                            <label for="anaerobe_drempel_vermogen" id="label_anaerobe_drempel_vermogen" class="block text-sm font-medium text-gray-700 mb-2">Anaërobe drempel - Vermogen (Watt)</label>
                            <input type="number" 
                                   step="any"
                                   name="anaerobe_drempel_vermogen" 
                                   id="anaerobe_drempel_vermogen"
                                   value="{{ old('anaerobe_drempel_vermogen') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                   placeholder="Automatisch berekend">
                        </div>

                        <div class="mb-4">
                            <label for="anaerobe_drempel_hartslag" class="block text-sm font-medium text-gray-700 mb-2">Anaërobe drempel - Hartslag (bpm)</label>
                            <input type="number" 
                                   step="1"
                                   name="anaerobe_drempel_hartslag" 
                                   id="anaerobe_drempel_hartslag"
                                   value="{{ old('anaerobe_drempel_hartslag') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                   placeholder="Automatisch berekend">
                        </div>
                    </div>




                    <!-- Complete AI Analyse Sectie -->
                    <div class="mb-6 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-center gap-3 mb-4">
                            <h4 class="text-lg font-bold text-blue-900">🧠 Complete AI Performance Analyse</h4>
                            <button type="button" 
                                    onclick="generateCompleteAIAnalysis()" 
                                    id="ai-complete-btn"
                                    class="inline-flex items-center px-4 py-2 border border-blue-400 rounded-md text-sm font-bold text-blue-800 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 transition duration-200 shadow-sm">
                                🤖 Genereer Complete Analyse
                            </button>
                        </div>
                        
                        <div class="mb-3">
                            <p class="text-sm text-blue-700 mb-2">
                                <strong>💡 Deze AI analyseert ALLE ingevulde parameters:</strong> doelstellingen, drempelwaarden, lichaamssamenstelling, hartslaggegevens, testtype, etc. en geeft uitgebreide populatievergelijkingen en specifieke trainingsadvies.
                            </p>
                        </div>
                        
                        <textarea name="complete_ai_analyse" 
                                  id="complete_ai_analyse"
                                  rows="20"
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm font-mono"
                                  placeholder="Klik op 'Genereer Complete Analyse' om een uitgebreide AI-analyse te krijgen van alle testparameters, inclusief populatievergelijkingen, prestatieclassificatie, fysiologische interpretatie en specifieke trainingsadvies op basis van je doelstellingen...">{{ old('complete_ai_analyse') }}</textarea>
                    </div>

                    <!-- Trainingszones Configuratie - NIEUWE SECTIE -->
                    <h3 class="text-xl font-bold mt-8 mb-4">Trainingszones Berekening</h3>
                    
                    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6">
                        <div class="flex items-center justify-between gap-4">
                            <p class="text-blue-800 text-sm flex-1">
                                🏃‍♂️ <strong>Automatische Zones:</strong> Kies een wetenschappelijke methode om trainingszones te berekenen op basis van je gemeten drempels.
                                De zones worden live bijgewerkt wanneer je de configuratie wijzigt.
                            </p>
                            <button type="button" 
                                    id="herbereken-zones-btn"
                                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium text-sm whitespace-nowrap flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Zones Updaten
                            </button>
                        </div>
                    </div>

                    <script>
                        // Veilige knop handler voor zones updaten
                        document.addEventListener('DOMContentLoaded', function() {
                            const herberekeBtn = document.getElementById('herbereken-zones-btn');
                            
                            if (herberekeBtn) {
                                herberekeBtn.addEventListener('click', function() {
                                    console.log('🔄 Zones Updaten knop geklikt');
                                    
                                    // Check of updateTrainingszones functie bestaat
                                    if (typeof updateTrainingszones === 'function') {
                                        console.log('✅ updateTrainingszones functie gevonden, uitvoeren...');
                                        
                                        // Voer bestaande functie uit
                                        updateTrainingszones();
                                        
                                        // Toon feedback
                                        herberekeBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg> Bijgewerkt!';
                                        herberekeBtn.classList.add('bg-green-600');
                                        herberekeBtn.classList.remove('bg-blue-600');
                                        
                                        // Reset na 2 seconden
                                        setTimeout(() => {
                                            herberekeBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg> Zones Updaten';
                                            herberekeBtn.classList.remove('bg-green-600');
                                            herberekeBtn.classList.add('bg-blue-600');
                                        }, 2000);
                                    } else {
                                        console.warn('⚠️ updateTrainingszones functie niet gevonden');
                                        alert('Selecteer eerst een berekenings methode voor de trainingszones.');
                                    }
                                });
                            }
                        });
                    </script>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div class="mb-4">
                            <label for="zones_methode" class="block text-sm font-medium text-gray-700 mb-2">Berekenings Methode</label>
                            <select name="zones_methode" 
                                    id="zones_methode"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Selecteer methode</option>
                                <option value="bonami" {{ old('zones_methode', 'bonami') == 'bonami' ? 'selected' : '' }}>Bonami Drempel Methode (6 zones)</option>
                                <option value="karvonen" {{ old('zones_methode') == 'karvonen' ? 'selected' : '' }}>Karvonen (Hartslagreserve)</option>
                                <option value="handmatig" {{ old('zones_methode') == 'handmatig' ? 'selected' : '' }}>Handmatig Aanpassen</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="zones_aantal" class="block text-sm font-medium text-gray-700 mb-2">Aantal Zones</label>
                            <select name="zones_aantal" 
                                    id="zones_aantal"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="3" {{ old('zones_aantal') == '3' ? 'selected' : '' }}>3 Zones (Basis)</option>
                                <option value="5" {{ old('zones_aantal') == '5' ? 'selected' : '' }}>5 Zones (Standaard)</option>
                                <option value="6" {{ old('zones_aantal', '6') == '6' ? 'selected' : '' }}>6 Zones (Bonami)</option>
                                <option value="7" {{ old('zones_aantal') == '7' ? 'selected' : '' }}>7 Zones (Uitgebreid)</option>
                                <option value="8" {{ old('zones_aantal') == '8' ? 'selected' : '' }}>8 Zones (Expert)</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="zones_eenheid" class="block text-sm font-medium text-gray-700 mb-2">Focus Eenheid</label>
                            <select name="zones_eenheid" 
                                    id="zones_eenheid"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="hartslag" {{ old('zones_eenheid', 'hartslag') == 'hartslag' ? 'selected' : '' }}>Hartslag (bpm)</option>
                                <option value="vermogen" {{ old('zones_eenheid') == 'vermogen' ? 'selected' : '' }}>Vermogen (Watt)</option>
                                <option value="snelheid" {{ old('zones_eenheid') == 'snelheid' ? 'selected' : '' }}>Snelheid (km/h)</option>
                                <option value="combinatie" {{ old('zones_eenheid') == 'combinatie' ? 'selected' : '' }}>Combinatie Alle</option>
                            </select>
                        </div>
                    </div>

                    <!-- Trainingszones Tabel Container - EXACT ZOALS EDIT.BLADE.PHP -->
                    <div id="trainingszones-container" class="mb-6" style="display: none;">
                        <div class="bg-white border-2 border-indigo-200 rounded-xl overflow-hidden shadow-lg">
                            <div class="bg-gradient-to-r from-indigo-50 via-blue-50 to-purple-50 px-6 py-4 border-b-2 border-indigo-200">
                                <h4 class="font-extrabold text-indigo-900 text-lg flex items-center">
                                    🎯 <span class="ml-2">Berekende Trainingszones</span>
                                    <span id="zones-methode-label" class="ml-3 text-sm font-semibold text-indigo-600 bg-white px-3 py-1 rounded-full"></span>
                                </h4>
                            </div>
                            
                            <div class="overflow-x-auto">
                                <table id="trainingszones-tabel" class="w-full border-collapse">
                                    <thead id="zones-header" class="bg-gradient-to-r from-gray-100 via-gray-50 to-gray-100">
                                        <!-- Headers worden dynamisch gegenereerd -->
                                    </thead>
                                    <tbody id="zones-body">
                                        <!-- Rijen worden dynamisch gegenereerd -->
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="bg-gradient-to-r from-gray-50 via-blue-50 to-gray-50 px-6 py-4 border-t-2 border-indigo-100">
                                <div class="flex justify-between items-center">
                                    <p class="text-xs text-gray-700 font-medium" id="zones-tip-text">
                                        💡 <strong>Tip:</strong> Deze zones zijn automatisch berekend. Bij 'Handmatig' kun je waarden aanpassen.
                                    </p>
                                    <button type="button" onclick="exportZonesData()" class="text-indigo-700 hover:text-indigo-900 text-sm font-bold bg-white px-4 py-2 rounded-lg border-2 border-indigo-200 hover:bg-indigo-50 transition-all duration-200 shadow-sm">
                                        📊 Exporteer Zones
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Hidden input voor zones data -->
                    <input type="hidden" name="trainingszones_data" id="trainingszones_data" value="{{ old('trainingszones_data') }}">


    </div>                    <!-- Sjabloon notificatie - EENVOUDIGE VERSIE -->
                    <div id="sjabloon-notificatie" class="mt-6 mb-6" style="background: #e3f2fd; border: 2px solid #2196f3; padding: 15px; border-radius: 8px;">
                        <strong style="color: #1976d2;">📋 Selecteer een testtype om te zien of er een sjabloon beschikbaar is voor rapportgeneratie.</strong>
                    </div>

                    <!-- Submit buttons -->
                    <div class="mt-8 flex gap-3 justify-start flex-wrap">
                        <div class="flex gap-3">
                            <a href="{{ route('klanten.show', $klant->id) }}" 
                               class="rounded-full px-6 py-2 text-gray-800 font-bold text-sm flex items-center justify-center hover:opacity-80 transition duration-200" 
                               style="background-color: #c8e1eb;">
                                Terug
                            </a>
                            <button type="submit" 
                                    class="rounded-full px-6 py-2 text-gray-800 font-bold text-sm flex items-center justify-center hover:opacity-80 transition duration-200" 
                                    style="background-color: #c8e1eb;">
                                Test Aanmaken
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Counter voor nieuwe rijen
let rowCount = 0;
let currentTableType = '';

// 🔥 BELANGRIJK: Voor CREATE mode is er GEEN bestaande data
const existingTestresultaten = []; // CREATE: geen bestaande data
console.log('📊 CREATE MODE: Geen bestaande testresultaten');

// Functie om huidige tabeldata te bewaren voordat tabel wordt gecleared
function preserveCurrentTableData() {
    const tbody = document.getElementById('testresultaten-body');
    const rows = tbody.getElementsByTagName('tr');
    const preservedData = [];
    
    for (let i = 0; i < rows.length; i++) {
        const inputs = rows[i].getElementsByTagName('input');
        const rowData = {};
        let hasData = false;
        
        for (let j = 0; j < inputs.length; j++) {
            const value = inputs[j].value;
            const name = inputs[j].name;
            if (value.trim() !== '') {
                hasData = true;
            }
            rowData[j] = value;
            rowData.fieldName = name;
        }
        
        if (hasData) {
            preservedData.push(rowData);
        }
    }
    
    return preservedData;
}

// Functie om inspanningstest rijen te genereren op basis van protocol
function generateInspanningstestRows(testType) {
    console.log('🏭 generateInspanningstestRows aangeroepen voor:', testType);
    
    const startValue = parseFloat(document.getElementById('startwattage').value) || 0;
    const stappenMin = parseFloat(document.getElementById('stappen_min').value) || 3;
    const stappenIncrement = parseFloat(document.getElementById('stappen_watt').value) || (testType === 'looptest' ? 1 : 40);
    
    console.log('📊 Protocol parameters:', {
        startValue,
        stappenMin,
        stappenIncrement,
        testType
    });
    
    if (startValue <= 0 || stappenIncrement <= 0) {
        console.log('❌ Ongeldige protocol parameters');
        return []; // Geen geldige waarden, return lege array
    }
    
    const rows = [];
    const maxStappen = 5; // Altijd precies 5 rijen genereren
    
    for (let i = 0; i < maxStappen; i++) {
        const currentTime = stappenMin * (i + 1);
        let currentValue = startValue + (stappenIncrement * i); // Eerste rij (i=0) = startValue + 0 = startValue ✅
        
        if (testType === 'fietstest') {
            console.log(`🚴 Fietstest rij ${i + 1}: ${currentTime}min, ${currentValue}W`);
            rows.push({
                tijd: currentTime,
                vermogen: currentValue,
                lactaat: '',
                hartslag: '',
                borg: ''
            });
        } else if (testType === 'looptest') {
            console.log(`🏃 Looptest rij ${i + 1}: ${currentTime}min, ${currentValue.toFixed(1)}km/h`);
            rows.push({
                tijd: currentTime,
                snelheid: currentValue.toFixed(1),
                lactaat: '',
                hartslag: '',
                borg: ''
            });
        }
    }
    
    console.log('✅ Gegenereerde rijen:', rows.length);
    return rows;
}

// Tabel configuraties per testtype
const tableConfigs = {
    'fietstest': { // Inspanningstest fietsen
        headers: ['Tijd (min)', 'Vermogen (Watt)', 'Lactaat (mmol/L)', 'Hartslag (bpm)', 'Borg'],
        fields: ['tijd', 'vermogen', 'lactaat', 'hartslag', 'borg'],
        inputTypes: ['number', 'number', 'number', 'number', 'number'],
        steps: ['', '', '0.1', '', '']
    },
    'looptest': { // Inspanningstest lopen - MET KOMMAGETALLEN
        headers: ['Tijd (min)', 'Snelheid (km/h)', 'Lactaat (mmol/L)', 'Hartslag (bpm)', 'Borg'],
        fields: ['tijd', 'snelheid', 'lactaat', 'hartslag', 'borg'],
        inputTypes: ['number', 'number', 'number', 'number', 'number'],
        steps: ['', '0.5', '0.1', '', ''] // 0.5 voor snelheid (8.0, 8.5, 9.0, etc.)
    },
    'veldtest_lopen': { // Veldtest lopen
        headers: ['Afstand (m)', 'Tijd (min)', 'Tijd (sec)', 'Lactaat (mmol/L)', 'Hartslag (bpm)', 'Borg'],
        fields: ['afstand', 'tijd_min', 'tijd_sec', 'lactaat', 'hartslag', 'borg'],
        inputTypes: ['number', 'number', 'number', 'number', 'number', 'number'],
        steps: ['', '', '', '0.1', '', '']
    },
    'veldtest_fietsen': { // Veldtest fietsen
        headers: ['Tijd (min)', 'Vermogen (Watt)', 'Lactaat (mmol/L)', 'Hartslag (bpm)', 'Borg'],
        fields: ['tijd', 'vermogen', 'lactaat', 'hartslag', 'borg'],
        inputTypes: ['number', 'number', 'number', 'number', 'number'],
        steps: ['', '', '0.1', '', '']
    },
    'veldtest_zwemmen': { // Veldtest zwemmen
        headers: ['Afstand (m)', 'Tijd (min)', 'Tijd (sec)', 'Lactaat (mmol/L)', 'Hartslag (bpm)', 'Borg'],
        fields: ['afstand', 'tijd_min', 'tijd_sec', 'lactaat', 'hartslag', 'borg'],
        inputTypes: ['number', 'number', 'number', 'number', 'number', 'number'],
        steps: ['', '', '', '0.1', '', '']
    }
};

// Protocol voorinstellingen voor veldtest lopen (chronologisch: lang naar kort)
const veldtestLopenProtocols = {
    '4 x 2000m en 1 x 600m': [
        {afstand: 2000, tijd_min: '', tijd_sec: '', lactaat: '', hartslag: '', borg: ''},
        {afstand: 2000, tijd_min: '', tijd_sec: '', lactaat: '', hartslag: '', borg: ''},
        {afstand: 2000, tijd_min: '', tijd_sec: '', lactaat: '', hartslag: '', borg: ''},
        {afstand: 2000, tijd_min: '', tijd_sec: '', lactaat: '', hartslag: '', borg: ''},
        {afstand: 600, tijd_min: '', tijd_sec: '', lactaat: '', hartslag: '', borg: ''}
    ],
    '4 x 1600m en 1 x 600m': [
        {afstand: 1600, tijd_min: '', tijd_sec: '', lactaat: '', hartslag: '', borg: ''},
        {afstand: 1600, tijd_min: '', tijd_sec: '', lactaat: '', hartslag: '', borg: ''},
        {afstand: 1600, tijd_min: '', tijd_sec: '', lactaat: '', hartslag: '', borg: ''},
        {afstand: 1600, tijd_min: '', tijd_sec: '', lactaat: '', hartslag: '', borg: ''},
        {afstand: 600, tijd_min: '', tijd_sec: '', lactaat: '', hartslag: '', borg: ''}
    ],
    '3 x 1600m en 1 x 600m': [
        {afstand: 1600, tijd_min: '', tijd_sec: '', lactaat: '', hartslag: '', borg: ''},
        {afstand: 1600, tijd_min: '', tijd_sec: '', lactaat: '', hartslag: '', borg: ''},
        {afstand: 1600, tijd_min: '', tijd_sec: '', lactaat: '', hartslag: '', borg: ''},
        {afstand: 600, tijd_min: '', tijd_sec: '', lactaat: '', hartslag: '', borg: ''}
    ],
    '4 x 1200m en 1 x 600m': [
        {afstand: 1200, tijd_min: '', tijd_sec: '', lactaat: '', hartslag: '', borg: ''},
        {afstand: 1200, tijd_min: '', tijd_sec: '', lactaat: '', hartslag: '', borg: ''},
        {afstand: 1200, tijd_min: '', tijd_sec: '', lactaat: '', hartslag: '', borg: ''},
        {afstand: 1200, tijd_min: '', tijd_sec: '', lactaat: '', hartslag: '', borg: ''},
        {afstand: 600, tijd_min: '', tijd_sec: '', lactaat: '', hartslag: '', borg: ''}
    ],
    '4 x 800m en 1 x 400m': [
        {afstand: 800, tijd_min: '', tijd_sec: '', lactaat: '', hartslag: '', borg: ''},
        {afstand: 800, tijd_min: '', tijd_sec: '', lactaat: '', hartslag: '', borg: ''},
        {afstand: 800, tijd_min: '', tijd_sec: '', lactaat: '', hartslag: '', borg: ''},
        {afstand: 800, tijd_min: '', tijd_sec: '', lactaat: '', hartslag: '', borg: ''},
        {afstand: 400, tijd_min: '', tijd_sec: '', lactaat: '', hartslag: '', borg: ''}
    ]
};

// Protocol voorinstellingen voor veldtest zwemmen
const veldtestZwemmenProtocols = {
    '4 x 200m': [
        {afstand: 200, tijd_min: '', tijd_sec: '', lactaat: '', hartslag: '', borg: ''},
        {afstand: 200, tijd_min: '', tijd_sec: '', lactaat: '', hartslag: '', borg: ''},
        {afstand: 200, tijd_min: '', tijd_sec: '', lactaat: '', hartslag: '', borg: ''},
        {afstand: 200, tijd_min: '', tijd_sec: '', lactaat: '', hartslag: '', borg: ''}
    ],
    '5 x 200m': [
        {afstand: 200, tijd_min: '', tijd_sec: '', lactaat: '', hartslag: '', borg: ''},
        {afstand: 200, tijd_min: '', tijd_sec: '', lactaat: '', hartslag: '', borg: ''},
        {afstand: 200, tijd_min: '', tijd_sec: '', lactaat: '', hartslag: '', borg: ''},
        {afstand: 200, tijd_min: '', tijd_sec: '', lactaat: '', hartslag: '', borg: ''},
        {afstand: 200, tijd_min: '', tijd_sec: '', lactaat: '', hartslag: '', borg: ''}
    ],
    '3 x 200m en 1 x 400m': [
        {afstand: 200, tijd_min: '', tijd_sec: '', lactaat: '', hartslag: '', borg: ''},
        {afstand: 200, tijd_min: '', tijd_sec: '', lactaat: '', hartslag: '', borg: ''},
        {afstand: 200, tijd_min: '', tijd_sec: '', lactaat: '', hartslag: '', borg: ''},
        {afstand: 400, tijd_min: '', tijd_sec: '', lactaat: '', hartslag: '', borg: ''}
    ]
};

// Functie om tabel header te genereren
function generateTableHeader(testType) {
    const config = tableConfigs[testType];
    if (!config) return '';
    
    let headerHtml = '<tr class="bg-gray-100">';
    config.headers.forEach(header => {
        headerHtml += `<th class="border border-gray-300 p-2 text-sm font-medium">${header}</th>`;
    });
    headerHtml += '</tr>';
    return headerHtml;
}

// Functie om tabel rij te genereren
function generateTableRow(testType, rowIndex, data = {}) {
    const config = tableConfigs[testType];
    if (!config) return '';
    
    let rowHtml = '<tr>';
    config.fields.forEach((field, index) => {
        const stepAttr = config.steps[index] ? `step="${config.steps[index]}"` : '';
        const value = data[field] !== undefined ? data[field] : '';
        rowHtml += `<td class="border border-gray-300 p-1">
            <input type="${config.inputTypes[index]}" 
                   name="testresultaten[${rowIndex}][${field}]" 
                   value="${value}"
                   class="w-full p-1 text-sm border-0" 
                   ${stepAttr}>
        </td>`;
    });
    rowHtml += '</tr>';
    return rowHtml;
}

// Functie om tabel te updaten - ALLEEN bij testtype wijziging
function updateTable(testType) {
    currentTableType = testType;
    const header = document.getElementById('testresultaten-header');
    const tbody = document.getElementById('testresultaten-body');
    
    // Bewaar huidige data voordat we clearen
    const currentData = preserveCurrentTableData();
    console.log('Bewaarde data voor testtype wijziging:', currentData);
    
    // Clear existing content
    header.innerHTML = '';
    tbody.innerHTML = '';
    rowCount = 0;
    
    if (!testType || !tableConfigs[testType]) return;
    
    // Set header
    header.innerHTML = generateTableHeader(testType);
    
    // Voor veldtest lopen met protocol, voeg vooringevulde rijen toe
    if (testType === 'veldtest_lopen') {
        const protocolSelect = document.getElementById('protocol');
        const selectedProtocol = protocolSelect ? protocolSelect.value : '';
        console.log('🏃 Veldtest lopen - geselecteerd protocol:', selectedProtocol);
        
        if (selectedProtocol && veldtestLopenProtocols[selectedProtocol]) {
            console.log('📊 Voorinvullen met protocol data:', veldtestLopenProtocols[selectedProtocol]);
            veldtestLopenProtocols[selectedProtocol].forEach(data => {
                tbody.innerHTML += generateTableRow(testType, rowCount, data);
                rowCount++;
            });
            return;
        }
    }
    
    // Voor veldtest zwemmen met protocol, voeg vooringevulde rijen toe
    if (testType === 'veldtest_zwemmen') {
        const protocolSelect = document.getElementById('protocol_zwemmen');
        const selectedProtocol = protocolSelect ? protocolSelect.value : '';
        console.log('🏊 Veldtest zwemmen - geselecteerd protocol:', selectedProtocol);
        
        if (selectedProtocol && veldtestZwemmenProtocols[selectedProtocol]) {
            console.log('📊 Voorinvullen zwemprotocol data:', veldtestZwemmenProtocols[selectedProtocol]);
            veldtestZwemmenProtocols[selectedProtocol].forEach(data => {
                tbody.innerHTML += generateTableRow(testType, rowCount, data);
                rowCount++;
            });
            return;
        }
    }
    
    // Voor inspanningstesten, genereer vooringevulde rijen op basis van protocol
    if (testType === 'fietstest' || testType === 'looptest') {
        const protocolRows = generateInspanningstestRows(testType);
        if (protocolRows.length > 0) {
            protocolRows.forEach(data => {
                tbody.innerHTML += generateTableRow(testType, rowCount, data);
                rowCount++;
            });
            return;
        }
    }
    
    // Voor veldtest fietsen, genereer ook 5 vooringevulde rijen
    if (testType === '4') {
        const startValue = parseFloat(document.getElementById('startwattage').value) || 100;
        const stappenMin = parseFloat(document.getElementById('stappen_min').value) || 3;
        const stappenIncrement = parseFloat(document.getElementById('stappen_watt').value) || 40;
        
        if (startValue > 0 && stappenIncrement > 0) {
            for (let i = 0; i < 5; i++) {
                const currentTime = stappenMin * (i + 1);
                const currentValue = startValue + (stappenIncrement * i);
                tbody.innerHTML += generateTableRow(testType, rowCount, {
                    tijd: currentTime,
                    vermogen: currentValue,
                    lactaat: '',
                    hartslag: '',
                    borg: ''
                });
                rowCount++;
            }
            return;
        }
    }
    
    // Voor andere testen, voeg één lege rij toe
    tbody.innerHTML += generateTableRow(testType, rowCount);
    rowCount++;
}

// Functie om nieuwe rij toe te voegen ZONDER bestaande data te wissen
function addRow() {
    console.log('🟢 addRow() gestart');
    console.log('currentTableType:', currentTableType);
    
    if (!currentTableType) {
        console.log('❌ Geen currentTableType - kan geen rij toevoegen');
        alert('Selecteer eerst een testtype voordat je rijen toevoegt');
        return;
    }
    
    const tbody = document.getElementById('testresultaten-body');
    if (!tbody) {
        console.log('❌ Geen tbody gevonden');
        return;
    }
    
    console.log('📊 Aantal rijen voor toevoegen:', tbody.getElementsByTagName('tr').length);
    
    // Tel bestaande data
    const existingInputs = tbody.querySelectorAll('input');
    let filledInputs = 0;
    existingInputs.forEach(input => {
        if (input.value.trim() !== '') filledInputs++;
    });
    console.log('📝 Bestaande ingevulde velden:', filledInputs);
    
    // Bereken automatische waarden voor de nieuwe rij op basis van protocol
    const autoValues = calculateNextRowValues(tbody.getElementsByTagName('tr').length);
    console.log('🧮 Berekende waarden voor nieuwe rij:', autoValues);
    
    // Maak nieuwe rij element aan met automatische waarden
    const newRow = document.createElement('tr');
    newRow.innerHTML = generateTableRowContent(currentTableType, rowCount, autoValues);
    
    // Voeg toe aan tbody zonder bestaande content te overschrijven
    tbody.appendChild(newRow);
    rowCount++;
    
    // Verifieer dat bestaande data er nog is
    const finalInputs = tbody.querySelectorAll('input');
    let finalFilledInputs = 0;
    finalInputs.forEach(input => {
        if (input.value.trim() !== '') finalFilledInputs++;
    });
    
    console.log('✅ Rij toegevoegd! Totaal rijen nu:', tbody.getElementsByTagName('tr').length);
    console.log('📝 Ingevulde velden na toevoegen:', finalFilledInputs);
    
    if (finalFilledInputs < filledInputs) {
        console.log('🚨 DATA VERLOREN! Voor:', filledInputs, 'Na:', finalFilledInputs);
    } else {
        console.log('✅ Alle data behouden!');
    }
}

// VEILIGE functie om alleen de eerste twee kolommen van bestaande rijen bij te werken
function updateTableSafely(testType) {
    console.log('🛡️ updateTableSafely aangeroepen voor:', testType);
    
    const tbody = document.getElementById('testresultaten-body');
    const rows = tbody.getElementsByTagName('tr');
    
    if (rows.length === 0) {
        console.log('Geen rijen om bij te werken');
        return;
    }
    
    // Haal protocol waarden op
    const startValue = parseFloat(document.getElementById('startwattage').value) || 0;
    const stappenMin = parseFloat(document.getElementById('stappen_min').value) || 3;
    const stappenIncrement = parseFloat(document.getElementById('stappen_watt').value) || 
                            (testType === 'looptest' ? 1 : 40);
    
    console.log('📊 Bijwerken met protocol:', { startValue, stappenMin, stappenIncrement });
    
    // Update alleen de eerste twee kolommen van bestaande rijen
    for (let i = 0; i < rows.length; i++) {
        const inputs = rows[i].getElementsByTagName('input');
        
        if (inputs.length >= 2) {
            const newTime = stappenMin * (i + 1);
            const newValue = startValue + (stappenIncrement * i);
            
            // Update alleen eerste twee velden (tijd en vermogen/snelheid)
            inputs[0].value = newTime; // Tijd
            
            if (testType === 'fietstest' || testType === 'veldtest_fietsen') {
                inputs[1].value = newValue; // Vermogen
                console.log(`📝 Rij ${i + 1} bijgewerkt: ${newTime}min, ${newValue}W`);
            } else if (testType === 'looptest') {
                inputs[1].value = newValue.toFixed(1); // Snelheid
                console.log(`📝 Rij ${i + 1} bijgewerkt: ${newTime}min, ${newValue.toFixed(1)}km/h`);
            }
            
            // Laat lactaat, hartslag, borg ongewijzigd!
        }
    }
    
    console.log('✅ Veilige update voltooid - handmatig ingevulde data behouden');
}

// Functie om automatische waarden te berekenen voor de volgende rij
function calculateNextRowValues(currentRowIndex) {
    const config = tableConfigs[currentTableType];
    if (!config) return {};
    
    console.log('🔢 Berekenen waarden voor rij', currentRowIndex, 'van type', currentTableType);
    
    // Voor inspanningstesten (fiets en loop): bereken op basis van protocol
    if (currentTableType === 'fietstest' || currentTableType === 'looptest') {
        const startValue = parseFloat(document.getElementById('startwattage').value) || 0;
        const stappenMin = parseFloat(document.getElementById('stappen_min').value) || 3;
        const stappenIncrement = parseFloat(document.getElementById('stappen_watt').value) || 
                                (currentTableType === 'looptest' ? 1 : 40);
        
        const nextTime = stappenMin * (currentRowIndex + 1);
        const nextValue = startValue + (stappenIncrement * currentRowIndex); // Rij 0 = start, rij 1 = start + increment
        
        console.log('📊 Protocol waarden voor ' + currentTableType + ':', {
            startValue,
            stappenMin, 
            stappenIncrement,
            nextTime,
            nextValue,
            currentTableType
        });
        
        if (currentTableType === 'fietstest') {
            return {
                tijd: nextTime,
                vermogen: nextValue,
                lactaat: '',
                hartslag: '',
                borg: ''
            };
        } else if (currentTableType === 'looptest') {
            return {
                tijd: nextTime,
                snelheid: nextValue.toFixed(1),
                lactaat: '',
                hartslag: '',
                borg: ''
            };
        }
    }
    
    // Voor veldtest fietsen: ook op basis van protocol
    if (currentTableType === 'veldtest_fietsen') {
        const startValue = parseFloat(document.getElementById('startwattage').value) || 100;
        const stappenMin = parseFloat(document.getElementById('stappen_min').value) || 3;
        const stappenIncrement = parseFloat(document.getElementById('stappen_watt').value) || 40;
        
        const nextTime = stappenMin * (currentRowIndex + 1);
        const nextValue = startValue + (stappenIncrement * currentRowIndex);
        
        return {
            tijd: nextTime,
            vermogen: nextValue,
            lactaat: '',
            hartslag: '',
            borg: ''
        };
    }
    
    // Voor veldtesten (lopen/zwemmen): lege waarden, gebruiker vult handmatig in
    if (currentTableType === 'veldtest_lopen' || currentTableType === 'veldtest_zwemmen') {
        return {
            afstand: '',
            tijd_min: '',
            tijd_sec: '',
            lactaat: '',
            hartslag: '',
            borg: ''
        };
    }
    
    // Fallback: lege waarden
    return {};
}

// Hulpfunctie om alleen de innerHTML van een rij te genereren
function generateTableRowContent(testType, rowIndex, data = {}) {
    const config = tableConfigs[testType];
    if (!config) return '';
    
    let content = '';
    config.fields.forEach((field, index) => {
        const stepAttr = config.steps[index] ? `step="${config.steps[index]}"` : '';
        const value = data[field] !== undefined ? data[field] : '';
        content += `<td class="border border-gray-300 p-1">
            <input type="${config.inputTypes[index]}" 
                   name="testresultaten[${rowIndex}][${field}]" 
                   value="${value}"
                   class="w-full p-1 text-sm border-0" 
                   ${stepAttr}>
        </td>`;
    });
    return content;
}

// Functie om laatste rij te verwijderen ZONDER andere data te beïnvloeden
function removeLastRow() {
    const tbody = document.getElementById('testresultaten-body');
    const rows = tbody.getElementsByTagName('tr');
    
    // Zorg dat er altijd minstens één rij blijft staan
    if (rows.length > 1) {
        // Verwijder alleen de laatste rij
        tbody.removeChild(rows[rows.length - 1]);
        rowCount = Math.max(0, rowCount - 1);
        
        console.log('Laatste rij verwijderd. Aantal rijen nu:', rows.length - 1);
    } else {
        console.log('Kan laatste rij niet verwijderen - minimaal 1 rij nodig');
    }
}



// Automatisch BMI berekenen
document.addEventListener('DOMContentLoaded', function() {
    const lengteInput = document.getElementById('lichaamslengte_cm');
    const gewichtInput = document.getElementById('lichaamsgewicht_kg');
    const bmiInput = document.getElementById('bmi');

    function calculateBMI() {
        const lengte = parseFloat(lengteInput.value);
        const gewicht = parseFloat(gewichtInput.value);
        
        if (lengte && gewicht && lengte > 0) {
            const lengteInMeters = lengte / 100;
            const bmi = gewicht / (lengteInMeters * lengteInMeters);
            bmiInput.value = bmi.toFixed(1);
        }
    }

    lengteInput.addEventListener('input', calculateBMI);
    gewichtInput.addEventListener('input', calculateBMI);
    
    // Bereken BMI bij het laden van de pagina als waardes al ingevuld zijn
    calculateBMI();

    // Dynamische velden op basis van testtype
    const testtypeSelect = document.getElementById('testtype');
    const startLabel = document.querySelector('label[for="startwattage"]');
    const stappenWattLabel = document.querySelector('label[for="stappen_watt"]');
    const protocolFieldLopen = document.getElementById('protocol-field-lopen');
    const protocolFieldZwemmen = document.getElementById('protocol-field-zwemmen');
    const weersomstandighedenField = document.getElementById('weersomstandigheden-field');
    const standardProtocolField1 = document.getElementById('standard-protocol-field-1');
    const standardProtocolField2 = document.getElementById('standard-protocol-field-2');
    const standardProtocolField3 = document.getElementById('standard-protocol-field-3');
    
    // Functie om sjabloon notificatie te updaten
    function updateSjabloonNotificatie() {
        const selectedType = testtypeSelect.value;
        const notificatieContainer = document.getElementById('sjabloon-notificatie');
        
        console.log('updateSjabloonNotificatie called with:', selectedType);
        
        if (!selectedType) {
            notificatieContainer.innerHTML = '<strong style="color: #1976d2;">📋 Selecteer een testtype om te zien of er een sjabloon beschikbaar is voor rapportgeneratie.</strong>';
            notificatieContainer.style.background = '#e3f2fd';
            notificatieContainer.style.borderColor = '#2196f3';
            return;
        }
        
        // Simpele update
        notificatieContainer.innerHTML = '<strong style="color: #2e7d32;">✅ Testtype "' + selectedType + '" geselecteerd - Na het aanmaken van de test kun je rapporten genereren!</strong>';
        notificatieContainer.style.background = '#e8f5e8';
        notificatieContainer.style.borderColor = '#4caf50';
    }
    
    function updateProtocolFields() {
        const selectedType = testtypeSelect.value;
        
        // Verberg eerst alle protocol velden
        protocolFieldLopen.style.display = 'none';
        protocolFieldZwemmen.style.display = 'none';
        weersomstandighedenField.style.display = 'none';
        standardProtocolField1.style.display = 'block';
        standardProtocolField2.style.display = 'block';
        standardProtocolField3.style.display = 'block';
        
        // 🔧 PAS STAPPEN VELD AAN voor looptesten - CRUCIALE FIX!
        const stappenInput = document.getElementById('stappen_watt');
        const stappenLabel = document.querySelector('label[for="stappen_watt"]');
        
        if (selectedType === 'looptest' || selectedType === 'veldtest_lopen') {
            // Voor looptesten: kommagetallen toestaan
            stappenInput.step = '0.5'; // Toestaan: 0.5, 1.0, 1.5, 2.0 km/h
            stappenLabel.textContent = 'Stappen (km/h)';
            console.log('✅ Looptest: stappen_watt.step = 0.5');
        } else {
            // Voor andere testen: hele getallen
            stappenInput.step = '1';
            stappenLabel.textContent = 'Stappen (Watt)';
            console.log('✅ Fietstest: stappen_watt.step = 1');
        }
        
        // Update tabel op basis van testtype
        updateTable(selectedType);
        
        // Update sjabloon notificatie
        updateSjabloonNotificatie();
        
        // Update drempelwaarden labels op basis van testtype
        updateDrempelwaardenLabels(selectedType);
        
        // 🎯 UPDATE TRAININGSZONES bij testtype wijziging
        setTimeout(() => {
            console.log('🔄 Zones updaten na testtype wijziging naar:', selectedType);
            updateTrainingszones();
        }, 200); // Kleine delay om ervoor te zorgen dat currentTableType is bijgewerkt
        
        console.log('🔍 updateProtocolFields - selectedType:', selectedType);
        console.log('🔍 currentTableType na updateTable:', currentTableType);
        
        if (selectedType === 'veldtest_lopen') {
            // Veldtest lopen: toon loop protocol dropdown, verberg standaard velden
            console.log('🏃 Veldtest lopen geselecteerd');
            protocolFieldLopen.style.display = 'block';
            weersomstandighedenField.style.display = 'block';
            standardProtocolField1.style.display = 'none';
            standardProtocolField2.style.display = 'none';
            standardProtocolField3.style.display = 'none';
            
            // Automatisch eerste protocol selecteren als er geen is geselecteerd
            const protocolSelect = document.getElementById('protocol');
            if (protocolSelect && !protocolSelect.value) {
                protocolSelect.value = '4 x 2000m en 1 x 600m'; // Standaard eerste protocol
                console.log('📋 Automatisch eerste protocol geselecteerd');
                
                // Update tabel met standaard protocol
                setTimeout(() => {
                    updateTable('veldtest_lopen');
                }, 100);
            }
        } else if (selectedType === 'veldtest_zwemmen') {
            // Veldtest zwemmen: toon zwem protocol dropdown, verberg standaard velden
            console.log('🏊 Veldtest zwemmen geselecteerd');
            protocolFieldZwemmen.style.display = 'block';
            weersomstandighedenField.style.display = 'block';
            standardProtocolField1.style.display = 'none';
            standardProtocolField2.style.display = 'none';
            standardProtocolField3.style.display = 'none';
            
            // Automatisch eerste protocol selecteren als er geen is geselecteerd
            const protocolZwemSelect = document.getElementById('protocol_zwemmen');
            if (protocolZwemSelect && !protocolZwemSelect.value) {
                protocolZwemSelect.value = '5 x 200m'; // Standaard eerste protocol
                console.log('📋 Automatisch eerste zwemprotocol geselecteerd');
                
                // Update tabel met standaard protocol
                setTimeout(() => {
                    updateTable('veldtest_zwemmen');
                }, 100);
            }
        } else if (selectedType === 'veldtest_fietsen') {
            // Veldtest fietsen: toon standaard velden + weersomstandigheden

            weersomstandighedenField.style.display = 'block';
            startLabel.textContent = 'Start wattage (watt)';
            stappenWattLabel.textContent = 'Stappen (watt)';
            document.getElementById('startwattage').value = 100;
            document.getElementById('stappen_watt').value = 40;
            
            // VELDTEST FIETS: Update tabel naar fietswattages na protocol instelling
            console.log('🔄 Updating tabel naar veldtest fietswattages...');
            setTimeout(() => {
                updateTableSafely('veldtest_fietsen');
            }, 100);
        } else {
            // Andere testen: toon alleen standaard velden
            console.log('🔧 Standaard protocol velden voor type:', selectedType);
            
            if (selectedType === 'fietstest') {
                // Fiets testen: wattage velden
                console.log('🚴 Fietstest protocol instellingen');
                startLabel.textContent = 'Start wattage (watt)';
                stappenWattLabel.textContent = 'Stappen (watt)';
                document.getElementById('startwattage').value = 100;
                document.getElementById('stappen_watt').value = 40;
                
                // CONSISTENTIE: Update tabel naar fietswattages na protocol instelling
                console.log('🔄 Updating tabel naar fietswattages...');
                setTimeout(() => {
                    updateTableSafely('fietstest');
                }, 100); // Kleine delay om ervoor te zorgen dat velden zijn bijgewerkt
            } else if (selectedType === 'looptest') {
                // Inspanningstest lopen: snelheid velden  
                console.log('🏃 Looptest protocol instellingen');
                startLabel.textContent = 'Start snelheid (km/h)';
                stappenWattLabel.textContent = 'Stappen (km/h)';
                document.getElementById('startwattage').value = 8;
                document.getElementById('stappen_watt').value = 1;
                
                // BELANGRIJKE TOEVOEGING: Update tabel naar loopsnelheden na protocol instelling
                console.log('🔄 Updating tabel naar loopsnelheden...');
                setTimeout(() => {
                    updateTableSafely('looptest');
                }, 100); // Kleine delay om ervoor te zorgen dat velden zijn bijgewerkt
            } else {
                // Default: wattage velden
                console.log('🔧 Default protocol instellingen');
                startLabel.textContent = 'Start (watt)';
                stappenWattLabel.textContent = 'Stappen (watt)';
                document.getElementById('startwattage').value = 100;
                document.getElementById('stappen_watt').value = 40;
            }
        }
    }
    
    // 🏷️ NIEUWE FUNCTIE: Update drempelwaarden labels op basis van testtype
    function updateDrempelwaardenLabels(selectedType) {
        console.log('🏷️ updateDrempelwaardenLabels aangeroepen voor:', selectedType);
        
        const aerobeVermogenLabel = document.getElementById('label_aerobe_drempel_vermogen');
        const anaerobeVermogenLabel = document.getElementById('label_anaerobe_drempel_vermogen');
        
        if (!aerobeVermogenLabel || !anaerobeVermogenLabel) {
            console.log('❌ Drempelwaarden labels niet gevonden');
            return;
        }
        
        // Bepaal het juiste label op basis van testtype
        let vermogenLabel = '';
        
        if (selectedType === 'looptest' || selectedType === 'veldtest_lopen') {
            vermogenLabel = 'Snelheid (km/h)';
            console.log('🏃 Looptest: labels naar snelheid (km/h)');
        } else if (selectedType === 'veldtest_zwemmen') {
            vermogenLabel = 'Snelheid (min/100m)';
            console.log('🏊 Zwemtest: labels naar snelheid (min/100m)');
        } else {
            // fietstest, veldtest_fietsen, of andere
            vermogenLabel = 'Vermogen (Watt)';
            console.log('🚴 Fietstest: labels naar vermogen (Watt)');
        }
        
        // Update de labels
        aerobeVermogenLabel.textContent = `Aërobe drempel - ${vermogenLabel}`;
        anaerobeVermogenLabel.textContent = `Anaërobe drempel - ${vermogenLabel}`;
        
        console.log('✅ Drempelwaarden labels bijgewerkt naar:', vermogenLabel);
    }
    
    // Event listeners voor protocol wijzigingen bij veldtesten - alleen bij echte protocol wijziging
    document.getElementById('protocol').addEventListener('change', function() {
        if (testtypeSelect.value === 'veldtest_lopen' && this.value !== '') {
            console.log('Protocol gewijzigd naar:', this.value, '- tabel wordt ververst');
            updateTable('veldtest_lopen');
        }
    });
    
    document.getElementById('protocol_zwemmen').addEventListener('change', function() {
        if (testtypeSelect.value === 'veldtest_zwemmen' && this.value !== '') {
            console.log('🏊 Zwemprotocol gewijzigd naar:', this.value, '- tabel wordt ververst');
            updateTable('veldtest_zwemmen');
        }
    });
    
    // Event listeners voor protocol veld wijzigingen - VEILIGE automatische update
    document.getElementById('startwattage').addEventListener('input', function() {
        const selectedType = testtypeSelect.value;
        console.log('Startwattage gewijzigd voor type:', selectedType);
        if (selectedType === 'fietstest' || selectedType === 'looptest' || selectedType === 'veldtest_fietsen') {
            console.log('🔄 Tabel wordt veilig bijgewerkt...');
            updateTableSafely(selectedType);
        }
    });
    
    document.getElementById('stappen_min').addEventListener('input', function() {
        const selectedType = testtypeSelect.value;
        console.log('Stappen minuten gewijzigd voor type:', selectedType);
        if (selectedType === 'fietstest' || selectedType === 'looptest' || selectedType === 'veldtest_fietsen') {
            console.log('🔄 Tabel wordt veilig bijgewerkt...');
            updateTableSafely(selectedType);
        }
    });
    
    document.getElementById('stappen_watt').addEventListener('input', function() {
        const selectedType = testtypeSelect.value;
        console.log('Stappen watt gewijzigd voor type:', selectedType);
        if (selectedType === 'fietstest' || selectedType === 'looptest' || selectedType === 'veldtest_fietsen') {
            console.log('🔄 Tabel wordt veilig bijgewerkt...');
            updateTableSafely(selectedType);
        }
    });
    
    // Event listener voor testtype wijzigingen
    testtypeSelect.addEventListener('change', updateProtocolFields);
    
    // Event listener voor analyse methode
    const analyseMethodeSelect = document.getElementById('analyse_methode');
    if (analyseMethodeSelect) {
        analyseMethodeSelect.addEventListener('change', handleAnalyseMethodeChange);
    }
    
    // Event listener voor D-max Modified drempelwaarde wijziging
    const dmaxThresholdInput = document.getElementById('dmax_modified_threshold');
    if (dmaxThresholdInput) {
        dmaxThresholdInput.addEventListener('input', function() {
            const selectedMethod = document.getElementById('analyse_methode').value;
            if (selectedMethod === 'dmax_modified') {
                console.log('🔧 D-max Modified drempelwaarde gewijzigd naar:', this.value, 'mmol/L');
                // Regenereer grafiek met nieuwe drempelwaarde
                generateChart();
            }
        });
    }
    
    // Initiële update bij het laden van de pagina
    updateProtocolFields();
    
    // 🎯 AUTOMATISCH BONAMI ZONES TONEN bij laden pagina
    setTimeout(() => {
        console.log('🚀 Automatisch Bonami zones laden...');
        updateTrainingszones();
    }, 500); // Kleine delay om ervoor te zorgen dat alles geladen is
    
    // NIEUWE FUNCTIONALITEIT: Event listeners voor trainingszones (VEILIG)
    const zonesMethodeSelect = document.getElementById('zones_methode');
    const zonesAantalSelect = document.getElementById('zones_aantal');
    const zonesEenheidSelect = document.getElementById('zones_eenheid');
    
    // Veilige event listeners die bestaande functionaliteit niet verstoren
    if (zonesMethodeSelect) {
        zonesMethodeSelect.addEventListener('change', function() {
            console.log('🎯 Zones methode gewijzigd naar:', this.value);
            updateTrainingszones();
        });
    }
    
    if (zonesAantalSelect) {
        zonesAantalSelect.addEventListener('change', function() {
            console.log('🔢 Zones aantal gewijzigd naar:', this.value);
            updateTrainingszones();
        });
    }
    
    if (zonesEenheidSelect) {
        zonesEenheidSelect.addEventListener('change', function() {
            console.log('📊 Zones eenheid gewijzigd naar:', this.value);
            updateTrainingszones();
        });
    }
    
    // 🔥 NIEUWE FUNCTIONALITEIT: Auto-update zones bij drempelwaarde wijzigingen
    const drempelInputs = document.querySelectorAll('.drempel-input');
    drempelInputs.forEach(input => {
        // Debounce timer om niet bij elke toetsaanslag te updaten
        let debounceTimer;
        
        input.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            
            // Wacht 800ms na laatste wijziging voordat zones worden bijgewerkt
            debounceTimer = setTimeout(() => {
                const methodeSelektor = document.getElementById('zones_methode');
                
                // Update zones alleen als er een methode geselecteerd is
                if (methodeSelektor && methodeSelektor.value) {
                    console.log('🔄 Drempelwaarde gewijzigd, zones worden automatisch bijgewerkt...');
                    
                    // Toon korte feedback
                    toonZonesUpdateFeedback();
                    
                    // Update zones
                    updateTrainingszones();
                }
            }, 800);
        });
    });
    
    console.log('✅ Auto-update voor drempelwaarden geactiveerd');
});

// Grafiek variabelen
let hartslagLactaatChart = null;
let aerobeThresholdLine = null;
let anaerobeThresholdLine = null;
let isDragging = false;
let draggedThreshold = null;

// Functie om analyse methode wijziging te behandelen
function handleAnalyseMethodeChange() {
    const selectedMethod = document.getElementById('analyse_methode').value;
    const grafiekContainer = document.getElementById('grafiek-container');
    const grafiekInstructies = document.getElementById('grafiek-instructies');
    const dmaxModifiedConfig = document.getElementById('dmax-modified-config');
    
    console.log('Analyse methode gewijzigd naar:', selectedMethod);
    
    // Toon/verberg D-max Modified configuratie
    if (selectedMethod === 'dmax_modified') {
        dmaxModifiedConfig.style.display = 'block';
    } else {
        dmaxModifiedConfig.style.display = 'none';
    }
    
    if (selectedMethod && selectedMethod !== '') {
        grafiekContainer.style.display = 'block';
        grafiekInstructies.style.display = 'block';
        generateChart();
    } else {
        grafiekContainer.style.display = 'none';
        grafiekInstructies.style.display = 'none';
        dmaxModifiedConfig.style.display = 'none';
        if (hartslagLactaatChart) {
            hartslagLactaatChart.destroy();
            hartslagLactaatChart = null;
        }
    }
}

// 🎯 NIEUWE FUNCTIE: Handmatig herberekenen van zones (voor de knop)
function herberekeTrainingszones() {
    console.log('🔄 Handmatige herberekening van zones gestart...');
    
    const methodeSelektor = document.getElementById('zones_methode');
    
    if (!methodeSelektor || !methodeSelektor.value) {
        alert('Selecteer eerst een berekenings methode voor de trainingszones.');
        return;
    }
    
    // Toon feedback
    toonZonesUpdateFeedback('🔄 Zones worden herberekend...');
    
    // Update zones
    setTimeout(() => {
        updateTrainingszones();
        toonZonesUpdateFeedback('✅ Zones succesvol herberekend!');
    }, 100);
}

// 🎨 HULPFUNCTIE: Toon korte feedback bij zones update
function toonZonesUpdateFeedback(message = '🔄 Zones bijgewerkt') {
    const container = document.getElementById('trainingszones-container');
    
    if (!container) return;
    
    // Maak feedback element
    const feedback = document.createElement('div');
    feedback.className = 'fixed top-20 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg z-50 transition-opacity duration-300';
    feedback.textContent = message;
    feedback.style.opacity = '0';
    
    document.body.appendChild(feedback);
    
    // Fade in
    setTimeout(() => {
        feedback.style.opacity = '1';
    }, 10);
    
    // Fade out en verwijder na 2 seconden
    setTimeout(() => {
        feedback.style.opacity = '0';
        setTimeout(() => {
            document.body.removeChild(feedback);
        }, 300);
    }, 2000);
}

// 🔥 HULPFUNCTIE: Voeg nieuwe rij toe aan testresultaten tabel
function addRow() {
    const tbody = document.querySelector('#testresultaten-tabel tbody');
    if (!tbody) {
        console.error('❌ Testresultaten tabel body niet gevonden');
        return;
    }
    
    const rowCount = tbody.querySelectorAll('tr').length;
    const newRow = document.createElement('tr');
    newRow.className = 'hover:bg-gray-50';
    
    newRow.innerHTML = `
        <td class="px-4 py-2 border">
            <input type="number" name="testresultaten[${rowCount}][tijd_minuten]" 
                   class="w-full px-2 py-1 border rounded" placeholder="Min" min="0">
        </td>
        <td class="px-4 py-2 border">
            <input type="number" name="testresultaten[${rowCount}][vermogen_watt]" 
                   class="w-full px-2 py-1 border rounded" placeholder="Watt" min="0">
        </td>
        <td class="px-4 py-2 border">
            <input type="number" name="testresultaten[${rowCount}][hartslag_bpm]" 
                   class="w-full px-2 py-1 border rounded" placeholder="BPM" min="0">
        </td>
        <td class="px-4 py-2 border">
            <input type="number" name="testresultaten[${rowCount}][lactaat_mmol]" 
                   class="w-full px-2 py-1 border rounded" placeholder="mmol/L" min="0" step="0.1">
        </td>
        <td class="px-4 py-2 border">
            <input type="number" name="testresultaten[${rowCount}][borg_rpe]" 
                   class="w-full px-2 py-1 border rounded" placeholder="6-20" min="6" max="20">
        </td>
        <td class="px-4 py-2 border text-center">
            <button type="button" onclick="this.closest('tr').remove()" 
                    class="text-red-600 hover:text-red-800 font-bold">
                ✕
            </button>
        </td>
    `;
    
    tbody.appendChild(newRow);
    console.log('✅ Nieuwe testresultaten rij toegevoegd');
}

// Functie om data uit de tabel te lezen
function getTableData() {
    const tbody = document.getElementById('testresultaten-body');
    const rows = tbody.getElementsByTagName('tr');
    const data = [];
    
    console.log('getTableData(): Aantal rijen gevonden:', rows.length);
    console.log('getTableData(): currentTableType:', currentTableType);
    
    for (let i = 0; i < rows.length; i++) {
        const inputs = rows[i].getElementsByTagName('input');
        const rowData = {};
        
        console.log(`Rij ${i}: Aantal inputs:`, inputs.length);
        
        // Get field names from current table type
        const config = tableConfigs[currentTableType];
        if (!config) {
            console.log('Geen config gevonden voor:', currentTableType);
            console.log('Beschikbare configs:', Object.keys(tableConfigs));
            continue;
        }
        
        config.fields.forEach((field, index) => {
            if (inputs[index]) {
                const value = parseFloat(inputs[index].value);
                console.log(`  ${field}:`, inputs[index].value, '→', value);
                rowData[field] = isNaN(value) || inputs[index].value === '' ? null : value;
            }
        });
        
        // SPECIALE BEHANDELING voor veldtest lopen: bereken snelheid EN voeg hartslag toe
        if (currentTableType === 'veldtest_lopen' && rowData.afstand && rowData.tijd_min) {
            const afstandKm = rowData.afstand / 1000;
            const tijdMinuten = rowData.tijd_min + ((rowData.tijd_sec || 0) / 60);
            const tijdUur = tijdMinuten / 60;
            if (tijdUur > 0) {
                rowData.snelheid = afstandKm / tijdUur;
                console.log(`  🏃 Berekende snelheid: ${rowData.snelheid.toFixed(2)} km/h`);
            }
        }
        
        // SPECIALE BEHANDELING voor veldtest zwemmen
        if (currentTableType === 'veldtest_zwemmen' && rowData.afstand && rowData.tijd_min) {
            const totaleTijdSec = (rowData.tijd_min * 60) + (rowData.tijd_sec || 0);
            if (totaleTijdSec > 0 && rowData.afstand > 0) {
                rowData.snelheid = (totaleTijdSec / 60) * (100 / rowData.afstand);
                console.log(`  🏊 Berekende zwemsnelheid: ${rowData.snelheid.toFixed(2)} min/100m`);
            }
        }
        
        // Only add row if it has useful data
        if (Object.values(rowData).some(val => val !== null && val !== '')) {
            data.push(rowData);
        }
    }
    
    console.log('Finale data array:', data);
    return data;
}

// === GRAFIEK GENERATIE FUNCTIONALITEIT ===

// Functie om drempelwaarden te berekenen op basis van geselecteerde methode
function calculateThresholds() {
    const selectedMethod = document.getElementById('analyse_methode').value;
    const tableData = getTableData();
    
    console.log('calculateThresholds aangeroepen met methode:', selectedMethod);
    console.log('Tabel data:', tableData);
    
    if (!selectedMethod || tableData.length === 0) {
        console.log('Geen methode geselecteerd of geen data');
        return null;
    }
    
    let result = null;
    
    switch(selectedMethod) {
        case 'dmax':
            result = calculateDmax(tableData);
            break;
        case 'dmax_modified':
            result = calculateDmaxModified(tableData);
            break;
        case 'lactaat_steady_state':
            result = calculateLactaatSteadyState(tableData);
            break;
        case 'hartslag_deflectie':
            result = calculateHartslagDeflectie(tableData);
            break;
        case 'handmatig':
            result = {
                aerobe: {
                    vermogen: parseFloat(document.getElementById('aerobe_drempel_vermogen').value) || null,
                    hartslag: parseFloat(document.getElementById('aerobe_drempel_hartslag').value) || null
                },
                anaerobe: {
                    vermogen: parseFloat(document.getElementById('anaerobe_drempel_vermogen').value) || null,
                    hartslag: parseFloat(document.getElementById('anaerobe_drempel_hartslag').value) || null
                }
            };
            break;
    }
    
    console.log('Berekende drempelwaarden:', result);
    return result;
}

// D-max methode (klassiek)
function calculateDmax(data) {
    console.log('🔬 D-max berekening gestart met data:', data);
    
    // Filter data met lactaat waarden
    const lactaatData = data.filter(d => d.lactaat !== null && d.lactaat !== undefined);
    
    if (lactaatData.length < 3) {
        console.log('❌ Onvoldoende lactaat data voor D-max');
        return null;
    }
    
    // Sorteer op vermogen/snelheid
    const xField = currentTableType === 'looptest' || currentTableType === 'veldtest_lopen' ? 'snelheid' : 
                   currentTableType === 'veldtest_zwemmen' ? 'snelheid' : 'vermogen';
    
    lactaatData.sort((a, b) => (a[xField] || 0) - (b[xField] || 0));
    
    // Fit polynoom door de punten
    const xValues = lactaatData.map(d => d[xField]);
    const yValues = lactaatData.map(d => d.lactaat);
    
    // Bereken rechte lijn tussen eerste en laatste punt
    const x1 = xValues[0];
    const y1 = yValues[0];
    const x2 = xValues[xValues.length - 1];
    const y2 = yValues[yValues.length - 1];
    
    // Vind punt met maximale afstand tot de lijn
    let maxDistance = 0;
    let maxIndex = 0;
    
    for (let i = 1; i < xValues.length - 1; i++) {
        const x = xValues[i];
        const y = yValues[i];
        
        // Bereken afstand tot lijn
        const lineY = y1 + (y2 - y1) * (x - x1) / (x2 - x1);
        const distance = Math.abs(y - lineY);
        
        if (distance > maxDistance) {
            maxDistance = distance;
            maxIndex = i;
        }
    }
    
    const dmaxPoint = lactaatData[maxIndex];
    console.log('✅ D-max punt gevonden:', dmaxPoint);
    
    // Anaërobe drempel = D-max punt
    // Aërobe drempel = 70% van anaërobe drempel
    const anaerobeVermogen = dmaxPoint[xField];
    const aerobeVermogen = anaerobeVermogen * 0.7;
    
    // Interpoleer hartslagen
    const anaerobeHartslag = interpolateLinear(xValues, lactaatData.map(d => d.hartslag), anaerobeVermogen);
    const aerobeHartslag = interpolateLinear(xValues, lactaatData.map(d => d.hartslag), aerobeVermogen);
    
    return {
        aerobe: {
            vermogen: aerobeVermogen,
            hartslag: aerobeHartslag,
            lactaat: interpolateLinear(xValues, yValues, aerobeVermogen)
        },
        anaerobe: {
            vermogen: anaerobeVermogen,
            hartslag: anaerobeHartslag,
            lactaat: dmaxPoint.lactaat
        }
    };
}

// D-max Modified methode - EXACT zoals edit.blade.php
function calculateDmaxModified(data) {
    console.log('🔬 D-max Modified berekening gestart');
    
    const lactaatData = data.filter(d => d.lactaat !== null && d.lactaat !== undefined);
    
    if (lactaatData.length < 3) {
        console.log('❌ Onvoldoende lactaat data voor D-max Modified');
        return null;
    }
    
    // Haal baseline + drempelwaarde op
    const thresholdInput = document.getElementById('dmax_modified_threshold');
    const thresholdIncrement = parseFloat(thresholdInput?.value) || 0.4;
    
    console.log('🔧 D-max Modified threshold increment:', thresholdIncrement);
    
    const xField = currentTableType === 'looptest' || currentTableType === 'veldtest_lopen' ? 'snelheid' : 
                   currentTableType === 'veldtest_zwemmen' ? 'snelheid' : 'vermogen';
    
    lactaatData.sort((a, b) => (a[xField] || 0) - (b[xField] || 0));
    
    // Baseline = GEMIDDELDE van eerste 2 lactaatwaarden (stabielere baseline)
    const baseline = lactaatData.length >= 2 
        ? (lactaatData[0].lactaat + lactaatData[1].lactaat) / 2 
        : lactaatData[0].lactaat;
    
    const threshold = baseline + thresholdIncrement;
    
    console.log(`📊 Baseline: ${baseline.toFixed(2)} mmol/L (avg eerste 2 punten)`);
    console.log(`📊 Drempel: ${threshold.toFixed(2)} mmol/L (baseline + ${thresholdIncrement})`);
    
    // Vind punt waar lactaat de drempel overschrijdt
    let anaerobePoint = null;
    
    for (let i = 0; i < lactaatData.length - 1; i++) {
        const currentLactaat = lactaatData[i].lactaat;
        const nextLactaat = lactaatData[i + 1].lactaat;
        
        console.log(`🔍 Punt ${i}: ${currentLactaat.toFixed(2)} mmol/L, Punt ${i+1}: ${nextLactaat.toFixed(2)} mmol/L`);
        
        if (currentLactaat < threshold && nextLactaat >= threshold) {
            // Interpoleer exact kruispunt
            const x1 = lactaatData[i][xField];
            const x2 = lactaatData[i + 1][xField];
            const y1 = currentLactaat;
            const y2 = nextLactaat;
            
            const ratio = (threshold - y1) / (y2 - y1);
            const xThreshold = x1 + ratio * (x2 - x1);
            
            anaerobePoint = xThreshold;
            
            console.log(`✅ Drempel gevonden tussen punt ${i} en ${i+1}`);
            console.log(`   Interpolatie: ${x1.toFixed(1)} + ${ratio.toFixed(3)} * (${x2.toFixed(1)} - ${x1.toFixed(1)}) = ${xThreshold.toFixed(1)}`);
            break;
        }
    }
    
    if (!anaerobePoint) {
        console.log('❌ Geen drempelpunt gevonden - lactaat blijft onder threshold');
        // Fallback: gebruik hoogste punt
        anaerobePoint = lactaatData[lactaatData.length - 1][xField];
        console.log(`⚠️ Fallback naar hoogste punt: ${anaerobePoint.toFixed(1)}`);
    }
    
    // Aërobe drempel = 70% van anaërobe
    const aerobePoint = anaerobePoint * 0.7;
    
    console.log(`📈 Anaërobe drempel (LT2): ${anaerobePoint.toFixed(1)}`);
    console.log(`📈 Aërobe drempel (LT1): ${aerobePoint.toFixed(1)} (70% van LT2)`);
    
    const xValues = lactaatData.map(d => d[xField]);
    const hartslagValues = lactaatData.map(d => d.hartslag);
    
    const anaerobeHartslag = interpolateLinear(xValues, hartslagValues, anaerobePoint);
    const aerobeHartslag = interpolateLinear(xValues, hartslagValues, aerobePoint);
    
    console.log(`💓 Anaërobe hartslag: ${anaerobeHartslag ? anaerobeHartslag.toFixed(0) : 'N/A'} bpm`);
    console.log(`💓 Aërobe hartslag: ${aerobeHartslag ? aerobeHartslag.toFixed(0) : 'N/A'} bpm`);
    
    return {
        aerobe: {
            vermogen: aerobePoint,
            hartslag: aerobeHartslag,
            lactaat: interpolateLinear(xValues, lactaatData.map(d => d.lactaat), aerobePoint)
        },
        anaerobe: {
            vermogen: anaerobePoint,
            hartslag: anaerobeHartslag,
            lactaat: threshold
        }
    };
}

// Lactaat Steady State methode
function calculateLactaatSteadyState(data) {
    console.log('🔬 Lactaat Steady State berekening');
    
    const lactaatData = data.filter(d => d.lactaat !== null);
    if (lactaatData.length < 3) return null;
    
    const xField = currentTableType === 'looptest' || currentTableType === 'veldtest_lopen' ? 'snelheid' : 
                   currentTableType === 'veldtest_zwemmen' ? 'snelheid' : 'vermogen';
    
    // Zoek punt waar lactaat rond 4 mmol/L ligt (anaërobe drempel)
    let anaerobePoint = null;
    
    for (let i = 0; i < lactaatData.length - 1; i++) {
        if (lactaatData[i].lactaat < 4.0 && lactaatData[i + 1].lactaat >= 4.0) {
            const x1 = lactaatData[i][xField];
            const x2 = lactaatData[i + 1][xField];
            const y1 = lactaatData[i].lactaat;
            const y2 = lactaatData[i + 1].lactaat;
            
            const ratio = (4.0 - y1) / (y2 - y1);
            anaerobePoint = x1 + ratio * (x2 - x1);
            break;
        }
    }
    
    if (!anaerobePoint) {
        anaerobePoint = lactaatData[lactaatData.length - 1][xField];
    }
    
    const aerobePoint = anaerobePoint * 0.75;
    
    const xValues = lactaatData.map(d => d[xField]);
    
    return {
        aerobe: {
            vermogen: aerobePoint,
            hartslag: interpolateLinear(xValues, lactaatData.map(d => d.hartslag), aerobePoint),
            lactaat: 2.0
        },
        anaerobe: {
            vermogen: anaerobePoint,
            hartslag: interpolateLinear(xValues, lactaatData.map(d => d.hartslag), anaerobePoint),
            lactaat: 4.0
        }
    };
}

// Hartslagdeflectie methode
function calculateHartslagDeflectie(data) {
    console.log('🔬 Hartslagdeflectie berekening');
    
    const hartslagData = data.filter(d => d.hartslag !== null);
    if (hartslagData.length < 3) return null;
    
    const xField = currentTableType === 'looptest' || currentTableType === 'veldtest_lopen' ? 'snelheid' : 
                   currentTableType === 'veldtest_zwemmen' ? 'snelheid' : 'vermogen';
    
    hartslagData.sort((a, b) => (a[xField] || 0) - (b[xField] || 0));
    
    // Vind deflectiepunt in hartslag curve
    let maxCurvature = 0;
    let deflectionIndex = Math.floor(hartslagData.length / 2);
    
    for (let i = 1; i < hartslagData.length - 1; i++) {
        const x1 = hartslagData[i - 1][xField];
        const x2 = hartslagData[i][xField];
        const x3 = hartslagData[i + 1][xField];
        const y1 = hartslagData[i - 1].hartslag;
        const y2 = hartslagData[i].hartslag;
        const y3 = hartslagData[i + 1].hartslag;
        
        const slope1 = (y2 - y1) / (x2 - x1);
        const slope2 = (y3 - y2) / (x3 - x2);
        const curvature = Math.abs(slope2 - slope1);
        
        if (curvature > maxCurvature) {
            maxCurvature = curvature;
            deflectionIndex = i;
        }
    }
    
    const anaerobeVermogen = hartslagData[deflectionIndex][xField];
    const aerobeVermogen = anaerobeVermogen * 0.75;
    
    const xValues = hartslagData.map(d => d[xField]);
    
    return {
        aerobe: {
            vermogen: aerobeVermogen,
            hartslag: interpolateLinear(xValues, hartslagData.map(d => d.hartslag), aerobeVermogen)
        },
        anaerobe: {
            vermogen: anaerobeVermogen,
            hartslag: hartslagData[deflectionIndex].hartslag
        }
    };
}

// Lineaire interpolatie helper
function interpolateLinear(xValues, yValues, xTarget) {
    if (xValues.length !== yValues.length) return null;
    
    for (let i = 0; i < xValues.length - 1; i++) {
        if (xTarget >= xValues[i] && xTarget <= xValues[i + 1]) {
            const x1 = xValues[i];
            const x2 = xValues[i + 1];
            const y1 = yValues[i];
            const y2 = yValues[i + 1];
            
            if (y1 === null || y2 === null) continue;
            
            const ratio = (xTarget - x1) / (x2 - x1);
            return y1 + ratio * (y2 - y1);
        }
    }
    
    return null;
}

// Grafiek genereren
function generateChart() {
    console.log('📊 Generating chart...');
    
    const canvas = document.getElementById('hartslagLactaatGrafiek');
    if (!canvas) {
        console.log('❌ Canvas element niet gevonden');
        return;
    }
    
    const ctx = canvas.getContext('2d');
    
    // Destroy oude grafiek
    if (hartslagLactaatChart) {
        hartslagLactaatChart.destroy();
    }
    
    const tableData = getTableData();
    console.log('Tabel data voor grafiek:', tableData);
    
    if (tableData.length === 0) {
        console.log('Geen data voor grafiek');
        return;
    }
    
    // Bereken drempels
    const thresholds = calculateThresholds();
    
    // Update drempelwaarden velden
    if (thresholds) {
        if (thresholds.aerobe) {
            document.getElementById('aerobe_drempel_vermogen').value = thresholds.aerobe.vermogen?.toFixed(1) || '';
            document.getElementById('aerobe_drempel_hartslag').value = thresholds.aerobe.hartslag?.toFixed(0) || '';
        }
        if (thresholds.anaerobe) {
            document.getElementById('anaerobe_drempel_vermogen').value = thresholds.anaerobe.vermogen?.toFixed(1) || '';
            document.getElementById('anaerobe_drempel_hartslag').value = thresholds.anaerobe.hartslag?.toFixed(0) || '';
        }
        
        // 🎯 TRIGGER ZONES UPDATE na drempelwaarden berekening
        setTimeout(() => {
            console.log('🔄 Auto-update zones na drempelberekening');
            updateTrainingszones();
        }, 300);
    }
    
    // Bepaal X-as veld
    const xField = currentTableType === 'looptest' || currentTableType === 'veldtest_lopen' ? 'snelheid' : 
                   currentTableType === 'veldtest_zwemmen' ? 'snelheid' : 'vermogen';
    
    const xLabel = currentTableType === 'looptest' || currentTableType === 'veldtest_lopen' ? 'Snelheid (km/h)' :
                   currentTableType === 'veldtest_zwemmen' ? 'Snelheid (min/100m)' : 'Vermogen (Watt)';
    
    // Sorteer data
    tableData.sort((a, b) => (a[xField] || 0) - (b[xField] || 0));
    
    const xValues = tableData.map(d => d[xField]);
    const hartslagValues = tableData.map(d => d.hartslag);
    const lactaatValues = tableData.map(d => d.lactaat);
    
    const datasets = [];
    
    // Hartslag lijn
    if (hartslagValues.some(v => v !== null)) {
        datasets.push({
            label: 'Hartslag (bpm)',
            data: xValues.map((x, i) => ({x: x, y: hartslagValues[i]})),
            borderColor: 'rgb(59, 130, 246)',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            yAxisID: 'y',
            tension: 0.4
        });
    }
    
    // Lactaat lijn
    if (lactaatValues.some(v => v !== null)) {
        datasets.push({
            label: 'Lactaat (mmol/L)',
            data: xValues.map((x, i) => ({x: x, y: lactaatValues[i]})),
            borderColor: 'rgb(34, 197, 94)',
            backgroundColor: 'rgba(34, 197, 94, 0.1)',
            yAxisID: 'y1',
            tension: 0.4
        });
    }
    
    // Voeg verticale drempellijnen toe als er drempels zijn
    if (thresholds && thresholds.aerobe && thresholds.aerobe.vermogen) {
        const minY = Math.min(...hartslagValues.filter(v => v !== null));
        const maxY = Math.max(...hartslagValues.filter(v => v !== null));
        
        // Aërobe drempel lijn (rood)
        datasets.push({
            label: 'Aërobe Drempel (LT1)',
            data: [
                {x: thresholds.aerobe.vermogen, y: minY},
                {x: thresholds.aerobe.vermogen, y: maxY}
            ],
            borderColor: 'rgba(239, 68, 68, 0.8)',
            borderWidth: 3,
            borderDash: [10, 5],
            pointRadius: 6,
            pointHoverRadius: 8,
            pointBackgroundColor: 'rgba(239, 68, 68, 1)',
            yAxisID: 'y',
            showLine: true
        });
    }
    
    if (thresholds && thresholds.anaerobe && thresholds.anaerobe.vermogen) {
        const minY = Math.min(...hartslagValues.filter(v => v !== null));
        const maxY = Math.max(...hartslagValues.filter(v => v !== null));
        
        // Anaërobe drempel lijn (oranje)
        datasets.push({
            label: 'Anaërobe Drempel (LT2)',
            data: [
                {x: thresholds.anaerobe.vermogen, y: minY},
                {x: thresholds.anaerobe.vermogen, y: maxY}
            ],
            borderColor: 'rgba(251, 146, 60, 0.8)',
            borderWidth: 3,
            borderDash: [10, 5],
            pointRadius: 6,
            pointHoverRadius: 8,
            pointBackgroundColor: 'rgba(251, 146, 60, 1)',
            yAxisID: 'y',
            showLine: true
        });
    }
    
    // Maak grafiek met drag functionaliteit
    hartslagLactaatChart = new Chart(ctx, {
        type: 'line',
        data: { datasets },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'nearest',
                intersect: false
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    enabled: true,
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                label += Math.round(context.parsed.y * 100) / 100;
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                x: {
                    type: 'linear',
                    title: {
                        display: true,
                        text: xLabel
                    }
                },
                y: {
                    type: 'linear',
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Hartslag (bpm)'
                    }
                },
                y1: {
                    type: 'linear',
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Lactaat (mmol/L)'
                    },
                    grid: {
                        drawOnChartArea: false
                    }
                }
            },
            onHover: (event, activeElements) => {
                if (activeElements.length > 0) {
                    const element = activeElements[0];
                    const dataset = hartslagLactaatChart.data.datasets[element.datasetIndex];
                    if (dataset.label.includes('Drempel')) {
                        event.native.target.style.cursor = 'grab';
                    } else {
                        event.native.target.style.cursor = 'default';
                    }
                } else {
                    event.native.target.style.cursor = 'default';
                }
            }
        }
    });
    
    // Voeg drag event listeners toe
    canvas.addEventListener('mousedown', handleMouseDown);
    canvas.addEventListener('mousemove', handleMouseMove);
    canvas.addEventListener('mouseup', handleMouseUp);
    canvas.addEventListener('mouseleave', handleMouseUp);
    
    console.log('✅ Chart gegenereerd met dragbare drempellijnen');
}

// Drag functionaliteit voor drempellijnen
function handleMouseDown(event) {
    const points = hartslagLactaatChart.getElementsAtEventForMode(event, 'nearest', {intersect: false}, false);
    
    if (points.length > 0) {
        const point = points[0];
        const dataset = hartslagLactaatChart.data.datasets[point.datasetIndex];
        
        if (dataset.label.includes('Aërobe Drempel')) {
            isDragging = true;
            draggedThreshold = 'aerobe';
            event.target.style.cursor = 'grabbing';
        } else if (dataset.label.includes('Anaërobe Drempel')) {
            isDragging = true;
            draggedThreshold = 'anaerobe';
            event.target.style.cursor = 'grabbing';
        }
    }
}

function handleMouseMove(event) {
    if (!isDragging || !draggedThreshold) return;
    
    const canvasPosition = Chart.helpers.getRelativePosition(event, hartslagLactaatChart);
    const dataX = hartslagLactaatChart.scales.x.getValueForPixel(canvasPosition.x);
    
    if (dataX) {
        // Update de drempellijn positie
        const thresholdDatasetIndex = draggedThreshold === 'aerobe' ? 
            hartslagLactaatChart.data.datasets.findIndex(ds => ds.label.includes('Aërobe Drempel')) :
            hartslagLactaatChart.data.datasets.findIndex(ds => ds.label.includes('Anaërobe Drempel'));
        
        if (thresholdDatasetIndex !== -1) {
            hartslagLactaatChart.data.datasets[thresholdDatasetIndex].data.forEach(point => {
                point.x = dataX;
            });
            
            // Update input veld
            const inputId = draggedThreshold === 'aerobe' ? 'aerobe_drempel_vermogen' : 'anaerobe_drempel_vermogen';
            document.getElementById(inputId).value = dataX.toFixed(1);
            
            // Interpoleer hartslag
            const tableData = getTableData();
            const xField = currentTableType === 'looptest' || currentTableType === 'veldtest_lopen' ? 'snelheid' : 
                           currentTableType === 'veldtest_zwemmen' ? 'snelheid' : 'vermogen';
            const xValues = tableData.map(d => d[xField]);
            const hartslagValues = tableData.map(d => d.hartslag);
            
            const interpolatedHartslag = interpolateLinear(xValues, hartslagValues, dataX);
            if (interpolatedHartslag) {
                const hartslagInputId = draggedThreshold === 'aerobe' ? 'aerobe_drempel_hartslag' : 'anaerobe_drempel_hartslag';
                document.getElementById(hartslagInputId).value = Math.round(interpolatedHartslag);
            }
            
            hartslagLactaatChart.update('none');
        }
    }
}

function handleMouseUp(event) {
    if (isDragging) {
        isDragging = false;
        event.target.style.cursor = 'default';
        
        // Trigger zones update na drag
        setTimeout(() => {
            updateTrainingszones();
        }, 100);
    }
    draggedThreshold = null;
}

// === AI ADVIES FUNCTIONALITEIT ===

function generateCompleteAIAnalysis() {
    console.log('🧠 Genereren complete AI analyse...');
    
    const button = document.getElementById('ai-complete-btn');
    const textarea = document.getElementById('complete_ai_analyse');
    
    if (!button || !textarea) {
        console.error('AI knop of textarea niet gevonden');
        return;
    }
    
    const originalText = button.innerHTML;
    button.innerHTML = '🔄 Analyseren...';
    button.disabled = true;
    
    const completeTestData = collectCompleteTestData();
    
    fetch('/api/ai-advice', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            'Accept': 'application/json'
        },
        body: JSON.stringify(completeTestData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            textarea.value = data.analysis;
            button.innerHTML = '✅ Analyse Compleet';
            button.style.backgroundColor = '#dcfce7';
            
            setTimeout(() => {
                button.innerHTML = originalText;
                button.style.backgroundColor = '';
                button.disabled = false;
            }, 5000);
        }
    })
    .catch(error => {
        console.error('❌ AI analyse fout:', error);
        button.innerHTML = '❌ Fout';
        textarea.value = getCompleteAnalysisFallback(completeTestData);
        
        setTimeout(() => {
            button.innerHTML = originalText;
            button.disabled = false;
        }, 5000);
    });
}

function collectCompleteTestData() {
    return {
        testtype: document.getElementById('testtype')?.value || 'fietstest',
        analyse_methode: document.getElementById('analyse_methode')?.value || '',
        specifieke_doelstellingen: document.getElementById('specifieke_doelstellingen')?.value || '',
        lichaamsgewicht_kg: parseFloat(document.getElementById('lichaamsgewicht_kg')?.value) || null,
        aerobe_drempel_vermogen: parseFloat(document.getElementById('aerobe_drempel_vermogen')?.value) || null,
        aerobe_drempel_hartslag: parseFloat(document.getElementById('aerobe_drempel_hartslag')?.value) || null,
        anaerobe_drempel_vermogen: parseFloat(document.getElementById('anaerobe_drempel_vermogen')?.value) || null,
        anaerobe_drempel_hartslag: parseFloat(document.getElementById('anaerobe_drempel_hartslag')?.value) || null,
        slaapkwaliteit: parseInt(document.getElementById('slaapkwaliteit')?.value) || null,
        eetlust: parseInt(document.getElementById('eetlust')?.value) || null
    };
}

function getCompleteAnalysisFallback(testData) {
    return `COMPLETE INSPANNINGSTEST ANALYSE

🎯 DOELSTELLINGEN: ${testData.specifieke_doelstellingen}

📊 GEMETEN DREMPELS:
• Aërobe drempel (LT1): ${testData.aerobe_drempel_vermogen || 'niet gemeten'} Watt
• Anaërobe drempel (LT2): ${testData.anaerobe_drempel_vermogen || 'niet gemeten'} Watt

💪 TRAININGSAANBEVELINGEN:
1. Focus op 80% training onder LT1 voor aerobe ontwikkeling
2. Voeg specifieke intervaltraining toe rond LT2
3. Monitor progressie met regelmatige hertesten`;
}

// === TRAININGSZONES EXPORT FUNCTIE ===
function exportZonesData() {
    console.log('📊 exportZonesData() aangeroepen');
    alert('Export functionaliteit komt in de volgende stap!');
}

// === TRAININGSZONES BEREKENING FUNCTIONALITEIT - EXACT KOPIE VAN EDIT.BLADE.PHP ===

let huidigeZonesData = null;

function updateTrainingszones() {
    console.log('🎯 updateTrainingszones() gestart');
    
    const methodeSelektor = document.getElementById('zones_methode');
    const aantalSelektor = document.getElementById('zones_aantal');
    const eenheidSelektor = document.getElementById('zones_eenheid');
    
    if (!methodeSelektor || !aantalSelektor || !eenheidSelektor) {
        console.log('❌ Zone selektoren niet gevonden');
        return;
    }
    
    const methode = methodeSelektor.value;
    const aantal = parseInt(aantalSelektor.value) || 6; // Default 6 voor Bonami
    const eenheid = eenheidSelektor.value;
    
    console.log('⚙️ Zones configuratie:', { methode, aantal, eenheid });
    
    const container = document.getElementById('trainingszones-container');
    const methodeLabel = document.getElementById('zones-methode-label');
    
    if (!methode) {
        console.log('⚠️ Geen methode geselecteerd');
        container.style.display = 'none';
        return;
    }
    
    container.style.display = 'block';
    
    let zonesData = null;
    let methodeLabelText = '';
    
    if (methode === 'bonami') {
        zonesData = berekenBonamiZones(aantal, eenheid);
        methodeLabelText = '(Bonami Drempel Methode)';
    } else if (methode === 'karvonen') {
        zonesData = berekenKarvonenZones(aantal, eenheid);
        methodeLabelText = '(Karvonen Hartslagreserve)';
    } else if (methode === 'handmatig') {
        zonesData = createHandmatigeZones(aantal, eenheid);
        methodeLabelText = '(Handmatig aanpasbaar)';
    }
    
    if (methodeLabel) {
        methodeLabel.textContent = methodeLabelText;
    }
    
    if (zonesData) {
        genereerZonesTabel(zonesData, eenheid);
        huidigeZonesData = zonesData;
        
        // Save naar hidden input voor form submit
        const hiddenInput = document.getElementById('trainingszones_data');
        if (hiddenInput) {
            hiddenInput.value = JSON.stringify(zonesData);
            console.log('💾 Zones data opgeslagen in hidden input');
        }
        
        console.log('✅ Trainingszones succesvol berekend:', zonesData.length, 'zones');
    } else {
        console.log('⚠️ Geen zones data gegenereerd');
    }
}

// 🏆 BONAMI ZONES METHODE - EXACT ZOALS EDIT.BLADE.PHP
function berekenBonamiZones(aantal, eenheid) {
    console.log('🎯 Bonami zones berekenen:', { aantal, eenheid });
    
    // Haal drempelwaarden op
    const aerobeVermogen = parseFloat(document.getElementById('aerobe_drempel_vermogen')?.value);
    const aerobeHartslag = parseFloat(document.getElementById('aerobe_drempel_hartslag')?.value);
    const anaerobeVermogen = parseFloat(document.getElementById('anaerobe_drempel_vermogen')?.value);
    const anaerobeHartslag = parseFloat(document.getElementById('anaerobe_drempel_hartslag')?.value);
    const maxHartslag = parseFloat(document.getElementById('maximale_hartslag_bpm')?.value);
    const rustHartslag = parseFloat(document.getElementById('hartslag_rust_bpm')?.value) || 60;
    
    console.log('📊 Drempelwaarden:', { 
        aerobeVermogen, 
        aerobeHartslag, 
        anaerobeVermogen, 
        anaerobeHartslag, 
        maxHartslag,
        rustHartslag 
    });
    
    if (!aerobeVermogen || !anaerobeVermogen) {
        console.log('⚠️ Geen drempelwaarden beschikbaar - kan zones niet berekenen');
        alert('⚠️ Bereken eerst de drempelwaarden (LT1 en LT2) via grafiek analyse voordat je trainingszones kunt genereren.');
        return null;
    }
    
    // Bonami 6-zones systeem (gebaseerd op LT1 en LT2) - EXACT zoals edit.blade.php
    const bonamiZones = [
        {
            zone: 'Zone 1',
            naam: 'Herstel',
            doel: 'Actief herstel, zeer lage intensiteit',
            percentage_lt1: '< 75%',
            percentage_lt2: '< 55%',
            multiplier_lt1_min: 0,
            multiplier_lt1_max: 0.75,
            multiplier_lt2_min: 0,
            multiplier_lt2_max: 0.55
        },
        {
            zone: 'Zone 2',
            naam: 'Duurtraining',
            doel: 'Aerobe basis, vetverbranding',
            percentage_lt1: '75-95%',
            percentage_lt2: '55-75%',
            multiplier_lt1_min: 0.75,
            multiplier_lt1_max: 0.95,
            multiplier_lt2_min: 0.55,
            multiplier_lt2_max: 0.75
        },
        {
            zone: 'Zone 3',
            naam: 'Tempo',
            doel: 'Aerobe ontwikkeling, net onder LT1',
            percentage_lt1: '95-105%',
            percentage_lt2: '75-85%',
            multiplier_lt1_min: 0.95,
            multiplier_lt1_max: 1.05,
            multiplier_lt2_min: 0.75,
            multiplier_lt2_max: 0.85
        },
        {
            zone: 'Zone 4',
            naam: 'Drempel',
            doel: 'Lactaatdrempel training, tussen LT1 en LT2',
            percentage_lt1: '105-125%',
            percentage_lt2: '85-100%',
            multiplier_lt1_min: 1.05,
            multiplier_lt1_max: 1.25,
            multiplier_lt2_min: 0.85,
            multiplier_lt2_max: 1.00
        },
        {
            zone: 'Zone 5',
            naam: 'VO2max',
            doel: 'Maximale aerobe capaciteit, boven LT2',
            percentage_lt1: '125-150%',
            percentage_lt2: '100-120%',
            multiplier_lt1_min: 1.25,
            multiplier_lt1_max: 1.50,
            multiplier_lt2_min: 1.00,
            multiplier_lt2_max: 1.20
        },
        {
            zone: 'Zone 6',
            naam: 'Anaeroob',
            doel: 'Maximale inspanning, sprint power',
            percentage_lt1: '> 150%',
            percentage_lt2: '> 120%',
            multiplier_lt1_min: 1.50,
            multiplier_lt1_max: 2.00,
            multiplier_lt2_min: 1.20,
            multiplier_lt2_max: 1.50
        }
    ];
    
    // Bereken concrete waarden per zone - EXACT zoals edit.blade.php
    const zonesMetWaarden = bonamiZones.slice(0, aantal).map((zone, index) => {
        let vermogenMin, vermogenMax, hartslagMin, hartslagMax;
        
        // Gebruik LT1 (aërobe drempel) als primaire basis
        vermogenMin = Math.round(aerobeVermogen * zone.multiplier_lt1_min);
        vermogenMax = Math.round(aerobeVermogen * zone.multiplier_lt1_max);
        
        // Hartslag berekening
        if (aerobeHartslag) {
            hartslagMin = Math.round(rustHartslag + ((aerobeHartslag - rustHartslag) * zone.multiplier_lt1_min));
            hartslagMax = Math.round(rustHartslag + ((aerobeHartslag - rustHartslag) * zone.multiplier_lt1_max));
            
            // Voor hogere zones: gebruik anaërobe hartslag als referentie
            if (index >= 4 && anaerobeHartslag) {
                hartslagMin = Math.round(anaerobeHartslag * zone.multiplier_lt2_min);
                hartslagMax = Math.round(anaerobeHartslag * zone.multiplier_lt2_max);
            }
            
            // Cap op max hartslag indien beschikbaar
            if (maxHartslag) {
                hartslagMax = Math.min(hartslagMax, maxHartslag);
            }
        } else {
            hartslagMin = null;
            hartslagMax = null;
        }
        
        console.log(`📊 Zone ${zone.zone}: Vermogen ${vermogenMin}-${vermogenMax}W, Hartslag ${hartslagMin}-${hartslagMax} bpm`);
        
        return {
            ...zone,
            vermogen_min: vermogenMin,
            vermogen_max: vermogenMax,
            hartslag_min: hartslagMin,
            hartslag_max: hartslagMax
        };
    });
    
    console.log('✅ Bonami zones berekend:', zonesMetWaarden);
    return zonesMetWaarden;
}

// 🧮 KARVONEN METHODE - EXACT zoals edit.blade.php
function berekenKarvonenZones(aantal, eenheid) {
    console.log('🎯 Karvonen zones berekenen:', { aantal, eenheid });
    
    const rustHartslag = parseFloat(document.getElementById('hartslag_rust_bpm')?.value);
    const maxHartslag = parseFloat(document.getElementById('maximale_hartslag_bpm')?.value);
    
    if (!rustHartslag || !maxHartslag) {
        console.log('⚠️ Rust of max hartslag ontbreekt');
        alert('⚠️ Voor Karvonen methode zijn zowel rust hartslag als maximale hartslag nodig.');
        return null;
    }
    
    const reserve = maxHartslag - rustHartslag;
    console.log('📊 Hartslagreserve:', reserve, '(', maxHartslag, '-', rustHartslag, ')');
    
    // Karvonen zones op basis van % hartslagreserve - EXACT zoals edit.blade.php
    const percentageSchema = {
        3: [[50, 70], [70, 85], [85, 100]],
        5: [[50, 60], [60, 70], [70, 80], [80, 90], [90, 100]],
        6: [[50, 60], [60, 70], [70, 80], [80, 85], [85, 95], [95, 100]],
        7: [[50, 60], [60, 70], [70, 76], [76, 82], [82, 89], [89, 94], [94, 100]],
        8: [[50, 60], [60, 65], [65, 70], [70, 76], [76, 82], [82, 89], [89, 94], [94, 100]]
    };
    
    const percentages = percentageSchema[aantal] || percentageSchema[5];
    
    const zoneNamen = ['Herstel', 'Duurtraining', 'Tempo', 'Drempel', 'VO2max', 'Anaeroob', 'Maximaal', 'Sprint'];
    
    const zones = percentages.map((range, index) => {
        const minHR = Math.round(rustHartslag + (reserve * range[0] / 100));
        const maxHR = Math.round(rustHartslag + (reserve * range[1] / 100));
        
        return {
            zone: `Zone ${index + 1}`,
            naam: zoneNamen[index] || `Intensiteit ${index + 1}`,
            doel: `${range[0]}-${range[1]}% van hartslagreserve`,
            hartslag_min: minHR,
            hartslag_max: maxHR,
            vermogen_min: null,
            vermogen_max: null,
            percentage_lt1: '-',
            percentage_lt2: '-'
        };
    });
    
    console.log('✅ Karvonen zones berekend:', zones);
    return zones;
}

// ✋ HANDMATIGE ZONES - EXACT zoals edit.blade.php
function createHandmatigeZones(aantal, eenheid) {
    console.log('🎯 Handmatige zones template maken:', { aantal, eenheid });
    
    const zoneNamen = ['Herstel', 'Duurtraining', 'Tempo', 'Drempel', 'VO2max', 'Anaeroob', 'Maximaal', 'Sprint'];
    const zones = [];
    
    for (let i = 1; i <= aantal; i++) {
        zones.push({
            zone: `Zone ${i}`,
            naam: zoneNamen[i - 1] || `Zone ${i}`,
            doel: 'Handmatig in te vullen',
            vermogen_min: null,
            vermogen_max: null,
            hartslag_min: null,
            hartslag_max: null,
            percentage_lt1: '-',
            percentage_lt2: '-',
            editable: true
        });
    }
    
    console.log('✅ Handmatige zones template gemaakt');
    return zones;
}

// 📊 GENEREER ZONES TABEL - EXACT zoals edit.blade.php
function genereerZonesTabel(zonesData, eenheid) {
    console.log('📊 Zones tabel genereren voor eenheid:', eenheid);
    console.log('📊 Zones data:', zonesData);
    
    const zonesHeader = document.getElementById('zones-header');
    const zonesBody = document.getElementById('zones-body');
    
    if (!zonesHeader || !zonesBody) {
        console.error('❌ Zones tabel elementen niet gevonden');
        return;
    }
    
    // Clear bestaande content
    zonesHeader.innerHTML = '';
    zonesBody.innerHTML = '';
    
    // Bouw header op basis van eenheid - EXACT zoals edit.blade.php
    let headerHTML = `
        <tr class="bg-gradient-to-r from-blue-100 via-indigo-100 to-purple-100">
            <th class="px-4 py-3 text-left text-xs font-bold text-gray-800 uppercase tracking-wider border-r border-gray-300">Zone</th>
            <th class="px-4 py-3 text-left text-xs font-bold text-gray-800 uppercase tracking-wider border-r border-gray-300">Naam</th>
            <th class="px-4 py-3 text-left text-xs font-bold text-gray-800 uppercase tracking-wider border-r border-gray-300">Trainingsdoel</th>
    `;
    
    if (eenheid === 'hartslag' || eenheid === 'combinatie') {
        headerHTML += `<th class="px-4 py-3 text-center text-xs font-bold text-gray-800 uppercase tracking-wider border-r border-gray-300">💓 Hartslag (bpm)</th>`;
    }
    
    if (eenheid === 'vermogen' || eenheid === 'combinatie') {
        headerHTML += `<th class="px-4 py-3 text-center text-xs font-bold text-gray-800 uppercase tracking-wider border-r border-gray-300">⚡ Vermogen (W)</th>`;
    }
    
    if (eenheid === 'snelheid' || eenheid === 'combinatie') {
        const snelheidLabel = currentTableType === 'veldtest_zwemmen' ? '🏊 Snelheid (min/100m)' : '🏃 Snelheid (km/h)';
        headerHTML += `<th class="px-4 py-3 text-center text-xs font-bold text-gray-800 uppercase tracking-wider border-r border-gray-300">${snelheidLabel}</th>`;
    }
    
    headerHTML += `
            <th class="px-4 py-3 text-center text-xs font-bold text-gray-800 uppercase tracking-wider border-r border-gray-300">% LT1</th>
            <th class="px-4 py-3 text-center text-xs font-bold text-gray-800 uppercase tracking-wider">% LT2</th>
        </tr>
    `;
    
    zonesHeader.innerHTML = headerHTML;
    
    // Kleurenschema per zone - EXACT zoals edit.blade.php
    const zoneKleuren = [
        { bg: 'bg-blue-50', text: 'text-blue-900', border: 'border-blue-200', icon: '😌' },        // Zone 1: Herstel
        { bg: 'bg-green-50', text: 'text-green-900', border: 'border-green-200', icon: '🚴' },     // Zone 2: Duurtraining
        { bg: 'bg-yellow-50', text: 'text-yellow-900', border: 'border-yellow-200', icon: '⚡' },  // Zone 3: Tempo
        { bg: 'bg-orange-50', text: 'text-orange-900', border: 'border-orange-200', icon: '🔥' },  // Zone 4: Drempel
        { bg: 'bg-red-50', text: 'text-red-900', border: 'border-red-200', icon: '💪' },          // Zone 5: VO2max
        { bg: 'bg-purple-50', text: 'text-purple-900', border: 'border-purple-200', icon: '🚀' }, // Zone 6: Anaeroob
        { bg: 'bg-pink-50', text: 'text-pink-900', border: 'border-pink-200', icon: '⚡' },       // Zone 7: Maximaal
        { bg: 'bg-red-100', text: 'text-red-900', border: 'border-red-300', icon: '💥' }         // Zone 8: Sprint
    ];
    
    // Bouw body rows - EXACT zoals edit.blade.php
    let bodyHTML = '';
    zonesData.forEach((zone, index) => {
        const kleuren = zoneKleuren[index] || zoneKleuren[0];
        
        bodyHTML += `
            <tr class="${kleuren.bg} hover:shadow-md hover:scale-[1.01] transition-all duration-200 border-b ${kleuren.border}">
                <td class="px-4 py-3 text-sm font-extrabold ${kleuren.text} border-r border-gray-200">
                    ${kleuren.icon} ${zone.zone}
                </td>
                <td class="px-4 py-3 text-sm font-bold ${kleuren.text} border-r border-gray-200">${zone.naam}</td>
                <td class="px-4 py-3 text-xs text-gray-700 border-r border-gray-200">${zone.doel}</td>
        `;
        
        if (eenheid === 'hartslag' || eenheid === 'combinatie') {
            const hsMin = zone.hartslag_min || '-';
            const hsMax = zone.hartslag_max || '-';
            bodyHTML += `<td class="px-4 py-3 text-sm text-center font-bold border-r border-gray-200" style="color: #dc2626;">💓 ${hsMin} - ${hsMax}</td>`;
        }
        
        if (eenheid === 'vermogen' || eenheid === 'combinatie') {
            const vmMin = zone.vermogen_min || '-';
            const vmMax = zone.vermogen_max || '-';
            bodyHTML += `<td class="px-4 py-3 text-sm text-center font-bold border-r border-gray-200" style="color: #059669;">⚡ ${vmMin} - ${vmMax}</td>`;
        }
        
        if (eenheid === 'snelheid' || eenheid === 'combinatie') {
            const snelheidIcon = currentTableType === 'veldtest_zwemmen' ? '🏊' : '🏃';
            const snMin = zone.snelheid_min || '-';
            const snMax = zone.snelheid_max || '-';
            bodyHTML += `<td class="px-4 py-3 text-sm text-center font-bold border-r border-gray-200" style="color: #7c3aed;">${snelheidIcon} ${snMin} - ${snMax}</td>`;
        }
        
        bodyHTML += `
                <td class="px-4 py-3 text-xs text-center text-gray-600 font-semibold border-r border-gray-200">${zone.percentage_lt1}</td>
                <td class="px-4 py-3 text-xs text-center text-gray-600 font-semibold">${zone.percentage_lt2}</td>
            </tr>
        `;
    });
    
    zonesBody.innerHTML = bodyHTML;
    
    console.log('✅ Zones tabel gegenereerd met', zonesData.length, 'zones en visuele styling');
}

// === HERBEREKEN ZONES KNOP ===
document.addEventListener('DOMContentLoaded', function() {
    const herberekeBtn = document.getElementById('herbereken-zones-btn');
    if (herberekeBtn) {
        herberekeBtn.addEventListener('click', function() {
            console.log('🔄 Herbereken zones knop geklikt');
            herberekeTrainingszones();
        });
    }
});
</script>
@endsection

@section('scripts')
<script>
console.log('🚀 AUTO-SAVE SCRIPT LOADED FOR INSPANNINGSTEST CREATE!');

// Auto-save functionaliteit voor inspanningstest formulieren (IDENTIEK AAN BIKEFIT)
class InspanningstestAutoSave {
    constructor() {
        this.form = null;
        this.saveTimeout = null;
        this.lastSaved = null;
        this.isEdit = false;
        this.klantId = null;
        this.testId = null;
        this.statusElement = null;
        
        this.init();
    }
    
    init() {
        console.log('🔧 InspanningstestAutoSave initializing...');
        console.log('📍 Current URL:', window.location.pathname);
        
        // Detecteer of we op een inspanningstest pagina zijn
        const path = window.location.pathname;
        const testMatch = path.match(/\/klanten\/(\d+)\/inspanningstest(?:\/(\d+))?/);
        
        if (!testMatch) {
            console.log('❌ Not on an inspanningstest page, auto-save disabled');
            return;
        }
        
        this.klantId = testMatch[1];
        this.testId = testMatch[2] || null;
        this.isEdit = !!this.testId;
        
        console.log('✅ Inspanningstest page detected:', {
            klantId: this.klantId,
            testId: this.testId,
            isEdit: this.isEdit
        });
        
        // Vind het formulier - zoek specifiek naar de inspanningstest form
        this.form = document.querySelector('form[action*="inspanningstest"]') || 
                   document.querySelector('form:not([action*="logout"])') ||
                   document.querySelector('form[method="POST"]');
        
        if (!this.form) {
            console.log('❌ No inspanningstest form found on page');
            return;
        }
        
        console.log('✅ Form found:', this.form);
        console.log('📋 Form action:', this.form.action);
        
        // Voeg status indicator toe
        this.addStatusIndicator();
        
        // Luister naar form changes
        this.attachEventListeners();
        
        console.log('🚀 Auto-save activated for', this.isEdit ? 'EDIT' : 'CREATE', 'mode');
    }
    
    addStatusIndicator() {
        // Voeg een subtiele status indicator toe
        const statusDiv = document.createElement('div');
        statusDiv.id = 'auto-save-status';
        statusDiv.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 10px 15px;
            font-size: 13px;
            color: #6c757d;
            z-index: 9999;
            opacity: 1;
            transition: opacity 0.3s ease;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            font-family: -apple-system, BlinkMacSystemFont, sans-serif;
        `;
        statusDiv.innerHTML = '💾 Auto-save ready';
        document.body.appendChild(statusDiv);
        this.statusElement = statusDiv;
        
        console.log('✅ Status indicator added');
        
        // Verberg na 3 seconden
        setTimeout(() => this.hideStatus(), 3000);
    }
    
    attachEventListeners() {
        // Luister naar alle form inputs
        const inputs = this.form.querySelectorAll('input, select, textarea');
        
        console.log(`📝 Found ${inputs.length} form inputs to monitor`);
        
        inputs.forEach((input, index) => {
            input.addEventListener('input', () => {
                console.log(`📝 Input changed: ${input.name || input.id || 'unnamed'}`);
                this.scheduleAutoSave();
            });
            input.addEventListener('change', () => {
                console.log(`🔄 Change event: ${input.name || input.id || 'unnamed'}`);
                this.scheduleAutoSave();
            });
        });
    }
    
    scheduleAutoSave() {
        console.log('⏰ Scheduling auto-save...');
        
        // Cancel bestaande timeout
        if (this.saveTimeout) {
            clearTimeout(this.saveTimeout);
            console.log('⏰ Cancelled previous save timeout');
        }
        
        // Schedule nieuwe save na 3 seconden
        this.saveTimeout = setTimeout(() => {
            this.performAutoSave();
        }, 3000);
        
        this.showStatus('⏳ Auto-save in 3 seconds...', '#ffc107');
    }
    
    async performAutoSave() {
        console.log('💾 Starting auto-save...');
        
        try {
            this.showStatus('💾 Saving...', '#007bff');
            
            // Maak een NIEUWE FormData object met alle velden expliciet
            const formData = new FormData();
            
            // Voeg CSRF token toe
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                             document.querySelector('input[name="_token"]')?.value;
            formData.append('_token', csrfToken);
            
            // KRITIEKE VELDEN: testtype en datum
            const testtypeInput = document.getElementById('testtype');
            const datumInput = document.getElementById('datum');
            
            if (!testtypeInput || !testtypeInput.value) {
                console.warn('⚠️ Testtype is leeg - skip auto-save');
                this.showStatus('⏭️ Selecteer eerst een testtype', '#ffc107');
                setTimeout(() => this.hideStatus(), 3000);
                return;
            }
            
            formData.append('testtype', testtypeInput.value);
            formData.append('testdatum', datumInput?.value || new Date().toISOString().split('T')[0]);
            
            console.log('✅ Kritieke velden:', {
                testtype: testtypeInput.value,
                datum: datumInput?.value
            });
            
            // Verzamel ALLE form inputs
            const inputs = this.form.querySelectorAll('input:not([type="file"]):not([name="_method"]), select, textarea');
            
            inputs.forEach(input => {
                const name = input.name;
                if (!name || name === '_token' || name === 'testtype' || name === 'testdatum') return;
                
                if (input.type === 'checkbox') {
                    if (input.checked) formData.append(name, input.value || '1');
                } else if (input.type === 'radio') {
                    if (input.checked) formData.append(name, input.value);
                } else {
                    const value = input.value;
                    if (value !== null && value !== undefined) {
                        formData.append(name, value);
                    }
                }
            });
            
            // 🔥 BELANGRIJKE FIX: Voeg testresultaten data expliciet toe
            const tbody = document.getElementById('testresultaten-body');
            if (tbody) {
                const rows = tbody.getElementsByTagName('tr');
                console.log(`📊 Testresultaten rijen gevonden: ${rows.length}`);
                
                for (let i = 0; i < rows.length; i++) {
                    const rowInputs = rows[i].getElementsByTagName('input');
                    console.log(`  Rij ${i}: ${rowInputs.length} inputs`);
                    
                    for (let j = 0; j < rowInputs.length; j++) {
                        const input = rowInputs[j];
                        if (input.name && input.value) {
                            formData.append(input.name, input.value);
                            console.log(`    ✅ ${input.name}: ${input.value}`);
                        }
                    }
                }
            } else {
                console.warn('⚠️ Testresultaten tbody niet gevonden');
            }
            
            // Voeg extra debug info toe
            console.log('📦 FormData contents:');
            for (let [key, value] of formData.entries()) {
                console.log(`  ${key}: ${value}`);
            }
            
            // Bepaal de juiste URL
            const url = this.isEdit 
                ? `/klanten/${this.klantId}/inspanningstest/${this.testId}/auto-save`
                : `/klanten/${this.klantId}/inspanningstest/auto-save`;
            
            console.log('🌐 Sending to URL:', url);
            
            // Verstuur de request
            const response = await fetch(url, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });
            
            console.log('📡 Response status:', response.status);
            console.log('📡 Response headers:', Object.fromEntries(response.headers.entries()));
            
            const responseText = await response.text();
            console.log('📡 Raw response:', responseText);
            
            let result;
            try {
                result = JSON.parse(responseText);
            } catch (e) {
                throw new Error(`Invalid JSON response: ${responseText}`);
            }
            
            if (response.ok && result.success) {
                this.lastSaved = new Date();
                console.log('✅ Auto-save successful:', result);
                this.showStatus('✅ ' + result.message, '#28a745');
                
                // 🔄 BELANGRIJKE FIX: Schakel over naar EDIT mode na eerste save EN update URL
                if (!this.isEdit && result.test_id) {
                    console.log('🔄 Switching to EDIT mode with test_id:', result.test_id);
                    this.testId = result.test_id;
                    this.isEdit = true;
                    
                    // Update URL naar edit route zodat refresh werkt
                    const newUrl = `/klanten/${this.klantId}/inspanningstest/${this.testId}/bewerken`;
                    window.history.replaceState({}, '', newUrl);
                    console.log('✅ URL updated naar:', newUrl);
                }
                
                setTimeout(() => this.hideStatus(), 4000);
            } else {
                console.error('❌ Auto-save failed:', result);
                this.showStatus('❌ ' + (result.message || 'Save failed'), '#dc3545');
                setTimeout(() => this.hideStatus(), 6000);
            }
            
        } catch (error) {
            console.error('💥 Auto-save error:', error);
            this.showStatus('❌ Connection error: ' + error.message, '#dc3545');
            setTimeout(() => this.hideStatus(), 6000);
        }
    }
    
    showStatus(message, color = '#6c757d') {
        if (!this.statusElement) return;
        
        this.statusElement.innerHTML = message;
        this.statusElement.style.borderColor = color;
        this.statusElement.style.color = color;
        this.statusElement.style.opacity = '1';
        
        console.log('📢 Status:', message);
    }
    
    hideStatus() {
        if (!this.statusElement) return;
        this.statusElement.style.opacity = '0';
    }
}

// Start auto-save
console.log('🎯 Starting inspanningstest auto-save initialization...');
new InspanningstestAutoSave();
</script>
@endsection
        // Bereid datasets voor    const datasets = [];        // Hartslag lijn    if (hartslagValues.length > 0) {        datasets.push({            label: 'Hartslag (bpm)',