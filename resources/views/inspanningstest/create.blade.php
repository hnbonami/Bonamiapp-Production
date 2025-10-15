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

<!-- Chart.js library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                                   value="{{ old('testdatum', date('Y-m-d')) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                   required>
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
                                <!-- Headers worden dynamisch gegenereerd -->
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
                        <p class="text-blue-800 text-sm">
                            🏃‍♂️ <strong>Automatische Zones:</strong> Kies een wetenschappelijke methode om trainingszones te berekenen op basis van je gemeten drempels.
                            De zones worden live bijgewerkt wanneer je de configuratie wijzigt.
                        </p>
                    </div>
                    
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

                    <!-- Trainingszones Tabel Container -->
                    <div id="trainingszones-container" class="mb-6" style="display: none;">
                        <div class="bg-white border border-gray-300 rounded-lg overflow-hidden">
                            <div class="bg-gray-50 px-4 py-3 border-b border-gray-300">
                                <h4 class="font-bold text-gray-900 flex items-center">
                                    🎯 <span class="ml-2">Berekende Trainingszones</span>
                                    <span id="zones-methode-label" class="ml-2 text-sm text-gray-600"></span>
                                </h4>
                            </div>
                            
                            <div class="overflow-x-auto">
                                <table id="trainingszones-tabel" class="w-full">
                                    <thead id="zones-header" class="bg-gray-100">
                                        <!-- Headers worden dynamisch gegenereerd -->
                                    </thead>
                                    <tbody id="zones-body">
                                        <!-- Rijen worden dynamisch gegenereerd -->
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="bg-gray-50 px-4 py-3 border-t border-gray-300">
                                <div class="flex justify-between items-center">
                                    <p class="text-xs text-gray-600" id="zones-tip-text">
                                        💡 <strong>Tip:</strong> Deze zones zijn automatisch berekend. Bij 'Handmatig' kun je waarden aanpassen.
                                    </p>
                                    <button type="button" onclick="exportZonesData()" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
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
    'looptest': { // Inspanningstest lopen
        headers: ['Tijd (min)', 'Snelheid (km/h)', 'Lactaat (mmol/L)', 'Hartslag (bpm)', 'Borg'],
        fields: ['tijd', 'snelheid', 'lactaat', 'hartslag', 'borg'],
        inputTypes: ['number', 'number', 'number', 'number', 'number'],
        steps: ['', '0.1', '0.1', '', '']
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
    analyseMethodeSelect.addEventListener('change', handleAnalyseMethodeChange);
    
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
});

// Grafiek variabelen
let hartslagLactaatChart = null;
let aerobeThresholdLine = null;
let anaerobeThresholdLine = null;

// Functie om analyse methode wijziging te behandelen
function handleAnalyseMethodeChange() {
    const selectedMethod = document.getElementById('analyse_methode').value;
    const grafiekContainer = document.getElementById('grafiek-container');
    const grafiekInstructies = document.getElementById('grafiek-instructies');
    const dmaxModifiedConfig = document.getElementById('dmax-modified-config');
    
    console.log('Analyse methode gewijzigd naar:', selectedMethod);
    console.log('Container gevonden:', !!grafiekContainer);
    console.log('Instructies gevonden:', !!grafiekInstructies);
    
    // Toon/verberg D-max Modified configuratie
    if (selectedMethod === 'dmax_modified') {
        dmaxModifiedConfig.style.display = 'block';
        console.log('✅ D-max Modified configuratie getoond');
    } else {
        dmaxModifiedConfig.style.display = 'none';
        console.log('❌ D-max Modified configuratie verborgen');
    }
    
    if (selectedMethod && selectedMethod !== '') {
        grafiekContainer.style.display = 'block';
        grafiekInstructies.style.display = 'block';
        console.log('Container en instructies zichtbaar gemaakt voor methode:', selectedMethod);
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
        const afstandKm = rowData.afstand / 1000; // meter naar km
        const tijdMinuten = rowData.tijd_min + ((rowData.tijd_sec || 0) / 60); // totale tijd in minuten
        const tijdUur = tijdMinuten / 60; // minuten naar uur
        if (tijdUur > 0) {
            rowData.snelheid = afstandKm / tijdUur; // km/h
            console.log(`  🏃 Berekende snelheid: ${rowData.snelheid.toFixed(2)} km/h (${rowData.afstand}m in ${tijdMinuten.toFixed(2)}min)`);
        }
        // Zorg dat hartslag aanwezig is
        if (rowData.hartslag) {
            console.log(`  ❤️ Hartslag aanwezig: ${rowData.hartslag} bpm`);
        }
    }
    
    // SPECIALE BEHANDELING voor veldtest zwemmen: bereken snelheid (min/100m) EN voeg hartslag toe
    if (currentTableType === 'veldtest_zwemmen' && rowData.afstand && rowData.tijd_min) {
        // Bereken totale tijd in seconden
        const totaleTijdSec = (rowData.tijd_min * 60) + (rowData.tijd_sec || 0);
        if (totaleTijdSec > 0 && rowData.afstand > 0) {
            // Bereken min/100m (zwemnotatie)
            rowData.snelheid = (totaleTijdSec / 60) * (100 / rowData.afstand); // min/100m
            console.log(`  🏊 Berekende zwemsnelheid: ${rowData.snelheid.toFixed(2)} min/100m (${rowData.afstand}m in ${totaleTijdSec}sec)`);
        }
        // Zorg dat hartslag aanwezig is
        if (rowData.hartslag) {
            console.log(`  ❤️ Hartslag aanwezig: ${rowData.hartslag} bpm`);
        }
    }        console.log('RowData:', rowData);
        
        // Only add row if it has useful data
        if (Object.values(rowData).some(val => val !== null && val !== '')) {
            data.push(rowData);
            console.log('Rij toegevoegd aan data');
        } else {
            console.log('Rij NIET toegevoegd (geen nuttige data)');
        }
    }
    
    console.log('Finale data array:', data);
    return data;
}

// Functie om verschillende analyse methodes toe te passen
function calculateThresholds(data, method) {
    if (!data || data.length < 3) return null;
    
    // Filter data met geldige lactaat waarden
    const validData = data.filter(row => row.lactaat !== null && row.lactaat > 0);
    if (validData.length < 3) return null;
    
    // Bepaal X-as veld op basis van testtype
    const xField = currentTableType === 'fietstest' ? 'vermogen' : 
                   currentTableType === 'looptest' ? 'snelheid' : 
                   currentTableType === 'veldtest_lopen' ? 'snelheid' :
                   currentTableType === 'veldtest_fietsen' ? 'vermogen' : 
                   currentTableType === 'veldtest_zwemmen' ? 'snelheid' : 'tijd';
    
    validData.sort((a, b) => (a[xField] || 0) - (b[xField] || 0));
    
    switch(method) {
        case 'dmax':
            return calculateDmax(validData, xField);
        case 'dmax_modified':
            return calculateDmaxModified(validData, xField);
        case 'lactaat_steady_state':
            return calculateLactaatSteadyState(validData, xField);
        case 'hartslag_deflectie':
            return calculateHartslagDeflectie(validData, xField);
        default:
            return calculateDmax(validData, xField);
    }
}

// Functie om waarde te interpoleren op basis van curve
function interpolateValueOnCurve(validData, xField, targetValue, targetField) {
    // Sorteer data
    validData.sort((a, b) => (a[xField] || 0) - (b[xField] || 0));
    
    // Vind het punt waar targetField de targetValue kruist
    for (let i = 0; i < validData.length - 1; i++) {
        const p1 = validData[i];
        const p2 = validData[i + 1];
        
        if (p1[targetField] <= targetValue && p2[targetField] >= targetValue) {
            // Lineaire interpolatie tussen de twee punten
            const ratio = (targetValue - p1[targetField]) / (p2[targetField] - p1[targetField]);
            const interpolatedX = p1[xField] + ratio * (p2[xField] - p1[xField]);
            const interpolatedHartslag = p1.hartslag + ratio * (p2.hartslag - p1.hartslag);
            
            return {
                [xField]: interpolatedX,
                lactaat: targetValue,
                hartslag: interpolatedHartslag
            };
        }
    }
    
    return null;
}

// D-max methode - WISKUNDIGE BEREKENING
function calculateDmax(validData, xField) {
    console.log('🧮 === D-MAX WISKUNDIGE BEREKENING ===');
    
    validData.sort((a, b) => (a[xField] || 0) - (b[xField] || 0));
    console.log('Data punten:', validData.map(d => `${d[xField]}W: ${d.lactaat.toFixed(2)}mmol/L`));
    
    // STAP 1: Aërobe drempel = laagste lactaat + 0.4 mmol/L
    const minLactaat = Math.min(...validData.map(d => d.lactaat));
    const aerobeThreshold = minLactaat + 0.4;
    let aerobePoint = interpolatePointAtLactaat(validData, xField, aerobeThreshold);
    
    if (!aerobePoint) {
        aerobePoint = {
            [xField]: validData[0][xField],
            lactaat: aerobeThreshold,
            hartslag: validData[0].hartslag || 140
        };
    }
    
    console.log(`✅ Aërobe drempel: ${aerobePoint[xField].toFixed(1)}W bij ${aerobeThreshold.toFixed(2)}mmol/L`);
    
    // STAP 2: Bereken parabool coëfficiënten voor lactaatcurve
    const xArray = validData.map(d => d[xField]);
    const yArray = validData.map(d => d.lactaat);
    const paraboolCoeff = fitParabola(xArray, yArray);
    
    console.log(`📐 Parabool: y = ${paraboolCoeff.a.toFixed(6)}x² + ${paraboolCoeff.b.toFixed(6)}x + ${paraboolCoeff.c.toFixed(6)}`);
    
    // STAP 3: Bereken hulplijn (eerste naar laatste punt)
    const firstPoint = validData[0];
    const lastPoint = validData[validData.length - 1];
    const m = (lastPoint.lactaat - firstPoint.lactaat) / (lastPoint[xField] - firstPoint[xField]);
    const bLine = firstPoint.lactaat - m * firstPoint[xField];
    
    console.log(`📏 Hulplijn: y = ${m.toFixed(6)}x + ${bLine.toFixed(6)}`);
    console.log(`📏 Van punt: ${firstPoint[xField]}W, ${firstPoint.lactaat.toFixed(2)}mmol/L`);
    console.log(`📏 Naar punt: ${lastPoint[xField]}W, ${lastPoint.lactaat.toFixed(2)}mmol/L`);
    
    // STAP 4: Bereken D-max punt (maximum afstand parabool tot hulplijn)
    const dmaxX = computeDmax(xArray, yArray, paraboolCoeff.a, paraboolCoeff.b, paraboolCoeff.c, m, bLine);
    const dmaxLactaat = paraboolCoeff.a * (dmaxX * dmaxX) + paraboolCoeff.b * dmaxX + paraboolCoeff.c;
    const dmaxHartslag = interpolateLinear(validData, dmaxX, 'hartslag', xField) || 160;
    
    const anaerobePoint = {
        [xField]: dmaxX,
        lactaat: dmaxLactaat,
        hartslag: dmaxHartslag
    };
    
    console.log(`🎯 D-MAX punt: ${dmaxX.toFixed(1)}W, ${dmaxLactaat.toFixed(2)}mmol/L, ${dmaxHartslag.toFixed(0)}bpm`);
    
    const result = {
        aerobe: aerobePoint,
        anaerobe: anaerobePoint
    };
    
    console.log('✅ D-MAX RESULTAAT:');
    console.log('  Aërobe:', aerobePoint[xField].toFixed(1) + 'W,', aerobePoint.hartslag.toFixed(0) + 'bpm');
    console.log('  Anaërobe:', anaerobePoint[xField].toFixed(1) + 'W,', anaerobePoint.hartslag.toFixed(0) + 'bpm');
    
    return result;
}

// D-max Modified methode - WISKUNDIGE BEREKENING
function calculateDmaxModified(validData, xField) {
    console.log('🔥 === D-MAX MODIFIED WISKUNDIGE BEREKENING ===');
    
    validData.sort((a, b) => (a[xField] || 0) - (b[xField] || 0));
    console.log('Data punten:', validData.map(d => `${d[xField]}W: ${d.lactaat.toFixed(2)}mmol/L`));
    
    // SPECIALE BEHANDELING VOOR ZWEMMEN
    const isSwimming = currentTableType === 'veldtest_zwemmen';
    
    // STAP 1: Haal configureerbare drempelwaarde op
    const thresholdInput = document.getElementById('dmax_modified_threshold');
    const configurableThreshold = thresholdInput ? parseFloat(thresholdInput.value) || 0.4 : 0.4;
    
    console.log(`🔧 Configureerbare drempelwaarde: ${configurableThreshold.toFixed(1)} mmol/L`);
    
    // STAP 2: Aërobe drempel = laagste lactaat + configureerbare waarde
    const minLactaat = Math.min(...validData.map(d => d.lactaat));
    const aerobeThreshold = minLactaat + configurableThreshold;
    let aerobePoint = interpolatePointAtLactaat(validData, xField, aerobeThreshold);
    
    if (!aerobePoint) {
        aerobePoint = {
            [xField]: validData[0][xField],
            lactaat: aerobeThreshold,
            hartslag: validData[0].hartslag || 140
        };
    }
    
    console.log(`✅ Modified Aërobe drempel: ${aerobePoint[xField].toFixed(1)}W bij ${aerobeThreshold.toFixed(2)}mmol/L (baseline +${configurableThreshold.toFixed(1)})`);
    
    // STAP 3: Voor zwemmen, gebruik de andere richting voor de hulplijn
    let startPoint, endPoint;
    if (isSwimming) {
        // Voor zwemmen: van laatste punt (langzaamste = hoogste X) naar aërobe punt
        startPoint = validData[validData.length - 1]; // Langzaamste tijd (hoogste lactaat verwacht)
        endPoint = aerobePoint; // Aërobe punt
        console.log(`🏊 ZWEM: Hulplijn van laatste punt naar aërobe punt`);
    } else {
        // Voor andere sporten: van aërobe punt naar laatste punt
        startPoint = aerobePoint;
        endPoint = validData[validData.length - 1];
        console.log(`🚴 NORMAAL: Hulplijn van aërobe punt naar laatste punt`);
    }
    
    // STAP 4: Bereken parabool coëfficiënten voor lactaatcurve
    const xArray = validData.map(d => d[xField]);
    const yArray = validData.map(d => d.lactaat);
    const paraboolCoeff = fitParabola(xArray, yArray);
    
    console.log(`📐 Modified Parabool: y = ${paraboolCoeff.a.toFixed(6)}x² + ${paraboolCoeff.b.toFixed(6)}x + ${paraboolCoeff.c.toFixed(6)}`);
    
    // STAP 5: Bereken hulplijn tussen start en eind punt
    const m = (endPoint.lactaat - startPoint.lactaat) / (endPoint[xField] - startPoint[xField]);
    const bLine = startPoint.lactaat - m * startPoint[xField];
    
    console.log(`📏 Modified Hulplijn: y = ${m.toFixed(6)}x + ${bLine.toFixed(6)}`);
    console.log(`📏 Van punt: ${startPoint[xField].toFixed(2)}W, ${startPoint.lactaat.toFixed(2)}mmol/L`);
    console.log(`📏 Naar punt: ${endPoint[xField].toFixed(2)}W, ${endPoint.lactaat.toFixed(2)}mmol/L`);
    
    // STAP 6: Bereken D-max punt tussen de juiste grenzen
    const searchStartX = Math.min(startPoint[xField], endPoint[xField]);
    const searchEndX = Math.max(startPoint[xField], endPoint[xField]);
    
    const dmaxX = computeDmaxFromBaseline(xArray, yArray, paraboolCoeff.a, paraboolCoeff.b, paraboolCoeff.c, m, bLine, searchStartX, searchEndX);
    const dmaxLactaat = paraboolCoeff.a * (dmaxX * dmaxX) + paraboolCoeff.b * dmaxX + paraboolCoeff.c;
    const dmaxHartslag = interpolateLinear(validData, dmaxX, 'hartslag', xField) || 160;
    
    const anaerobePoint = {
        [xField]: dmaxX,
        lactaat: dmaxLactaat,
        hartslag: dmaxHartslag
    };
    
    console.log(`🎯 D-MAX Modified punt: ${dmaxX.toFixed(1)}W, ${dmaxLactaat.toFixed(2)}mmol/L, ${dmaxHartslag.toFixed(0)}bpm`);
    
    const result = {
        aerobe: aerobePoint,
        anaerobe: anaerobePoint
    };
    
    console.log('✅ D-MAX MODIFIED RESULTAAT:');
    console.log('  Aërobe:', aerobePoint[xField].toFixed(1) + 'W,', aerobePoint.hartslag.toFixed(0) + 'bpm');
    console.log('  Anaërobe:', anaerobePoint[xField].toFixed(1) + 'W,', anaerobePoint.hartslag.toFixed(0) + 'bpm');
    
    return result;
}

// Lineaire interpolatie helper functie - GEFIXEERD VOOR VELDTESTEN
function interpolateLinear(points, targetX, targetField, xField) {
    // Voor veldtesten: zorg dat snelheid en hartslag beschikbaar zijn
    const validPoints = points.filter(p => {
        const hasXValue = p[xField] !== null && p[xField] !== undefined && !isNaN(p[xField]);
        const hasTargetValue = p[targetField] !== null && p[targetField] !== undefined && !isNaN(p[targetField]);
        return hasXValue && hasTargetValue;
    });
    
    if (validPoints.length === 0) {
        console.log('❌ interpolateLinear: Geen valide punten met', xField, 'en', targetField);
        return targetField === 'hartslag' ? 140 : 0; // Fallback waarde
    }
    
    if (validPoints.length === 1) {
        console.log('⚠️ interpolateLinear: Slechts 1 punt, gebruik deze waarde');
        return validPoints[0][targetField];
    }
    
    validPoints.sort((a, b) => (a[xField] || 0) - (b[xField] || 0));
    
    console.log(`🔍 interpolateLinear: targetX=${targetX.toFixed(2)}, targetField=${targetField}, xField=${xField}`);
    console.log(`📊 Valide punten (${validPoints.length}):`, validPoints.map(p => `${p[xField].toFixed(2)}${xField}: ${p[targetField]}`));
    
    for (let i = 0; i < validPoints.length - 1; i++) {
        const p1 = validPoints[i];
        const p2 = validPoints[i + 1];
        
        if (p1[xField] <= targetX && p2[xField] >= targetX) {
            const ratio = (targetX - p1[xField]) / (p2[xField] - p1[xField]);
            const interpolatedValue = p1[targetField] + ratio * (p2[targetField] - p1[targetField]);
            console.log(`✅ Interpolatie tussen ${p1[xField].toFixed(2)} (${p1[targetField]}) en ${p2[xField].toFixed(2)} (${p2[targetField]}): ${interpolatedValue.toFixed(1)}`);
            return interpolatedValue;
        }
    }
    
    // Extrapolatie: gebruik dichtstbijzijnde punt
    if (targetX < validPoints[0][xField]) {
        console.log(`⬅️ Extrapolatie links: gebruik eerste punt ${validPoints[0][targetField]}`);
        return validPoints[0][targetField];
    } else {
        console.log(`➡️ Extrapolatie rechts: gebruik laatste punt ${validPoints[validPoints.length - 1][targetField]}`);
        return validPoints[validPoints.length - 1][targetField];
    }
}

// Lactaat Steady State methode met interpolatie
function calculateLactaatSteadyState(validData, xField) {
    // Aërobe drempel: 2 mmol/L lactaat (geïnterpoleerd)
    let aerobePoint = interpolateValueOnCurve(validData, xField, 2.0, 'lactaat');
    if (!aerobePoint) {
        aerobePoint = validData.find(d => d.lactaat >= 2.0) || validData[0];
    }
    
    // Anaërobe drempel: 4 mmol/L lactaat (geïnterpoleerd)
    let anaerobePoint = interpolateValueOnCurve(validData, xField, 4.0, 'lactaat');
    if (!anaerobePoint) {
        anaerobePoint = validData.find(d => d.lactaat >= 4.0) || validData[Math.floor(validData.length / 2)];
    }
    
    return {
        aerobe: aerobePoint,
        anaerobe: anaerobePoint
    };
}

// Hartslagdeflectie methode met curve analyse
function calculateHartslagDeflectie(validData, xField) {
    // Filter data met geldige hartslag waarden
    const hartslagData = validData.filter(d => d.hartslag && d.hartslag > 0);
    if (hartslagData.length < 3) return null;
    
    hartslagData.sort((a, b) => (a[xField] || 0) - (b[xField] || 0));
    
    // Genereer meer punten voor nauwkeurigere deflectie analyse
    const minX = Math.min(...hartslagData.map(d => d[xField]));
    const maxX = Math.max(...hartslagData.map(d => d[xField]));
    const curvePoints = [];
    
    for (let i = 0; i <= 30; i++) {
        const x = minX + (maxX - minX) * (i / 30);
        const hartslag = interpolateLinear(hartslagData, x, 'hartslag', xField);
        const lactaat = interpolateLinear(validData, x, 'lactaat', xField);
        
        curvePoints.push({
            [xField]: x,
            hartslag: hartslag,
            lactaat: lactaat
        });
    }
    
    // Zoek deflectiepunten in de vloeiende curve
    let maxDeflectie = 0;
    let deflectiePoint = null;
    let aerobeDeflectiePoint = null;
    
    for (let i = 2; i < curvePoints.length - 2; i++) {
        const p1 = curvePoints[i - 2];
        const p2 = curvePoints[i - 1];
        const p3 = curvePoints[i];
        const p4 = curvePoints[i + 1];
        const p5 = curvePoints[i + 2];
        
        // Bereken tweede afgeleide (curvature) voor deflectie
        const slope1 = (p2.hartslag - p1.hartslag) / (p2[xField] - p1[xField]);
        const slope2 = (p3.hartslag - p2.hartslag) / (p3[xField] - p2[xField]);
        const slope3 = (p4.hartslag - p3.hartslag) / (p4[xField] - p3[xField]);
        const slope4 = (p5.hartslag - p4.hartslag) / (p5[xField] - p4[xField]);
        
        const curvature = Math.abs((slope3 - slope2) - (slope2 - slope1));
        
        // Eerste significante deflectie = aërobe drempel
        if (!aerobeDeflectiePoint && curvature > 0.5) {
            aerobeDeflectiePoint = p3;
        }
        
        // Maximale deflectie = anaërobe drempel
        if (curvature > maxDeflectie) {
            maxDeflectie = curvature;
            deflectiePoint = p3;
        }
    }
    
    return {
        aerobe: aerobeDeflectiePoint || hartslagData[Math.floor(hartslagData.length / 3)],
        anaerobe: deflectiePoint || hartslagData[Math.floor(hartslagData.length * 2 / 3)]
    };
}

// === WISKUNDIGE HULPFUNCTIES ===

// Functie om lijn tussen twee punten te berekenen
function lineBetweenPoints(x1, y1, x2, y2, x) {
    if (x2 === x1) return y1; // Verticale lijn
    const m = (y2 - y1) / (x2 - x1);
    const b = y1 - m * x1;
    return m * x + b;
}

// Functie om parabool door punten te fitten (least squares)
function fitParabola(xArray, yArray) {
    const n = xArray.length;
    if (n < 3) {
        console.warn('Te weinig punten voor parabool fit, gebruik lineaire benadering');
        return { a: 0, b: (yArray[yArray.length-1] - yArray[0]) / (xArray[xArray.length-1] - xArray[0]), c: yArray[0] };
    }
    
    // Stel matrix systeem op voor ax² + bx + c
    let sumX = 0, sumX2 = 0, sumX3 = 0, sumX4 = 0;
    let sumY = 0, sumXY = 0, sumX2Y = 0;
    
    for (let i = 0; i < n; i++) {
        const x = xArray[i];
        const y = yArray[i];
        const x2 = x * x;
        const x3 = x2 * x;
        const x4 = x3 * x;
        
        sumX += x;
        sumX2 += x2;
        sumX3 += x3;
        sumX4 += x4;
        sumY += y;
        sumXY += x * y;
        sumX2Y += x2 * y;
    }
    
    // Los matrix systeem op met Cramer's regel
    const det = sumX4 * (sumX2 * n - sumX * sumX) - 
                sumX3 * (sumX3 * n - sumX * sumX2) + 
                sumX2 * (sumX3 * sumX - sumX2 * sumX2);
                
    if (Math.abs(det) < 1e-10) {
        console.warn('Determinant te klein voor parabool fit');
        return { a: 0.001, b: (yArray[yArray.length-1] - yArray[0]) / (xArray[xArray.length-1] - xArray[0]), c: yArray[0] };
    }
    
    const detA = sumX2Y * (sumX2 * n - sumX * sumX) - 
                 sumXY * (sumX3 * n - sumX * sumX2) + 
                 sumY * (sumX3 * sumX - sumX2 * sumX2);
                 
    const detB = sumX4 * (sumXY * n - sumY * sumX) - 
                 sumX3 * (sumX2Y * n - sumY * sumX2) + 
                 sumX2 * (sumX2Y * sumX - sumXY * sumX2);
                 
    const detC = sumX4 * (sumX2 * sumY - sumX * sumXY) - 
                 sumX3 * (sumX3 * sumY - sumX * sumX2Y) + 
                 sumX2 * (sumX3 * sumXY - sumX2 * sumX2Y);
    
    return {
        a: detA / det,
        b: detB / det,
        c: detC / det
    };
}

// Functie om D-max punt te berekenen (maximum afstand parabool tot lijn)
function computeDmax(xArray, yArray, a, b, c, m, bLine) {
    const minX = Math.min(...xArray);
    const maxX = Math.max(...xArray);
    
    // Zoek maximum afstand tussen parabool en lijn
    let maxDistance = 0;
    let dmaxX = minX + (maxX - minX) * 0.5; // Start in het midden
    
    const testPoints = 200; // Test 200 punten voor nauwkeurigheid
    for (let i = 1; i < testPoints - 1; i++) { // Skip eerste en laatste punt
        const x = minX + (maxX - minX) * (i / (testPoints - 1));
        const parabolaY = a * x * x + b * x + c;
        const lineY = m * x + bLine;
        const distance = Math.abs(parabolaY - lineY);
        
        if (distance > maxDistance) {
            maxDistance = distance;
            dmaxX = x;
        }
    }
    
    console.log(`🎯 D-max berekend op ${dmaxX.toFixed(1)}W met afstand ${maxDistance.toFixed(4)}`);
    return dmaxX;
}

// Functie om D-max Modified punt te berekenen (van baseline punt naar laatste punt)
function computeDmaxFromBaseline(xArray, yArray, a, b, c, m, bLine, startX, endX) {
    // Zoek maximum afstand tussen parabool en lijn van startX naar endX
    let maxDistance = 0;
    let dmaxX = startX + (endX - startX) * 0.6; // Start bij 60% van bereik
    
    const testPoints = 100; // Test 100 punten in dit bereik
    for (let i = 1; i < testPoints - 1; i++) { // Skip eerste en laatste punt
        const x = startX + (endX - startX) * (i / (testPoints - 1));
        const parabolaY = a * x * x + b * x + c;
        const lineY = m * x + bLine;
        const distance = Math.abs(parabolaY - lineY);
        
        if (distance > maxDistance) {
            maxDistance = distance;
            dmaxX = x;
        }
    }
    
    console.log(`🎯 D-max Modified berekend op ${dmaxX.toFixed(1)}W met afstand ${maxDistance.toFixed(4)}`);
    return dmaxX;
}

// Interpoleer punt op specifieke lactaat waarde
function interpolatePointAtLactaat(validData, xField, targetLactaat) {
    if (!validData || validData.length === 0) {
        console.error('❌ interpolatePointAtLactaat: Geen valide data');
        return null;
    }
    
    if (validData.length === 1) {
        return {
            [xField]: validData[0][xField],
            lactaat: targetLactaat,
            hartslag: validData[0].hartslag || 140
        };
    }
    
    for (let i = 0; i < validData.length - 1; i++) {
        const p1 = validData[i];
        const p2 = validData[i + 1];
        
        if (p1.lactaat <= targetLactaat && p2.lactaat >= targetLactaat) {
            const ratio = (targetLactaat - p1.lactaat) / (p2.lactaat - p1.lactaat);
            const interpolatedPoint = {
                [xField]: p1[xField] + ratio * (p2[xField] - p1[xField]),
                lactaat: targetLactaat,
                hartslag: (p1.hartslag || 140) + ratio * ((p2.hartslag || 160) - (p1.hartslag || 140))
            };
            console.log(`✅ Geïnterpoleerd punt op ${targetLactaat}mmol/L: ${interpolatedPoint[xField].toFixed(1)}W`);
            return interpolatedPoint;
        }
    }
    
    // Fallback: dichtstbijzijnde punt
    const closest = validData.reduce((closest, current) => 
        Math.abs(current.lactaat - targetLactaat) < Math.abs(closest.lactaat - targetLactaat) 
            ? current : closest
    );
    
    console.log(`⚠️ Fallback naar dichtstbijzijnde punt voor ${targetLactaat}mmol/L: ${closest[xField]}W`);
    return {
        [xField]: closest[xField],
        lactaat: targetLactaat, // Gebruik gewenste lactaat, niet originele
        hartslag: closest.hartslag || 140
    };
}

// Functie om afstand van punt tot lijn te berekenen (LEGACY FUNCTIE - blijft voor compatibiliteit)
function calculatePointToLineDistance(px, py, x1, y1, x2, y2) {
    const A = y2 - y1;
    const B = x1 - x2;
    const C = x2 * y1 - x1 * y2;
    
    return Math.abs(A * px + B * py + C) / Math.sqrt(A * A + B * B);
}

// Functie om exponentiële curve te genereren voor lactaat
function generateSmoothLactaatCurve(lactaatData) {
    console.log('🏊 === ZWEM DEBUG === generateSmoothLactaatCurve aangeroepen voor testtype:', currentTableType);
    console.log('🏊 Raw lactaat data:', lactaatData);
    
    if (!lactaatData || lactaatData.length < 2) {
        console.log('Niet genoeg lactaat data voor curve generatie');
        return lactaatData || [];
    }
    
    // ZWEM DEBUG: Check data voor en na sortering
    console.log('🏊 Data VOOR sortering:', lactaatData.map(d => `${d.x.toFixed(2)} min/100m: ${d.y.toFixed(2)} mmol/L`));
    
    // Sorteer data op X-waarde
    const sortedData = [...lactaatData].sort((a, b) => a.x - b.x);
    console.log('🏊 Data NA sortering:', sortedData.map(d => `${d.x.toFixed(2)} min/100m: ${d.y.toFixed(2)} mmol/L`));
    
    const smoothData = [];
    const minX = sortedData[0].x;
    const maxX = sortedData[sortedData.length - 1].x;
    const steps = 100; // 100 punten voor zeer vloeiende exponentiële curve
    
    console.log('Curve bereik:', minX, 'tot', maxX, 'met', steps, 'stappen');
    
    for (let i = 0; i <= steps; i++) {
        const x = minX + (maxX - minX) * (i / steps);
        
        // SPECIALE BEHANDELING VOOR ZWEMMEN: gebruik aangepaste interpolatie
        let y;
        if (currentTableType === 'veldtest_zwemmen') {
            y = interpolateExponentialForSwimming(sortedData, x);
            console.log(`🏊 ZWEM curve punt ${i}: ${x.toFixed(2)} min/100m -> ${y.toFixed(2)} mmol/L`);
        } else {
            // Gebruik normale exponentiële interpolatie voor andere testen
            y = interpolateExponentialForCurve(sortedData, x);
        }
        
        // Debug eerste paar punten voor niet-zwem testen
        if (i < 5 && currentTableType !== 'veldtest_zwemmen') {
            console.log(`Curve punt ${i}: ${x.toFixed(1)}W -> ${y.toFixed(2)}mmol/L`);
        }
        
        smoothData.push({ x: x, y: y });
    }
    
    console.log('Smooth data gegenereerd:', smoothData.length, 'punten');
    return smoothData;
}

// Exponentiële interpolatie voor fysiologisch realistische lactaatcurve
function interpolateExponentialForCurve(points, targetX) {
    if (points.length === 0) return 1.0;
    if (points.length === 1) return points[0].y;
    
    // Sorteer punten op X-waarde
    const sortedPoints = [...points].sort((a, b) => a.x - b.x);
    
    // Maak exponentiële curve door alle punten
    // Lactaat volgt ongeveer: y = a * exp(b * (x - x0))
    
    const minX = sortedPoints[0].x;
    const maxX = sortedPoints[sortedPoints.length - 1].x;
    const minY = sortedPoints[0].y;
    const maxY = sortedPoints[sortedPoints.length - 1].y;
    
    // Normaliseer X-waarde tussen 0 en 1
    const normalizedX = (targetX - minX) / (maxX - minX);
    
    // Exponentiële functie: start vlak, wordt exponentieel steiler
    // Voor lactaat: y = minY + (maxY - minY) * exp(k * x) / exp(k)
    // waar k bepaalt hoe exponentieel de curve is
    
    let exponentialValue;
    if (maxY > 6) {
        // Hoge lactaatwaarden: sterke exponentiële groei
        const k = 2.5; // Exponentiële factor
        exponentialValue = minY + (maxY - minY) * (Math.exp(k * normalizedX) - 1) / (Math.exp(k) - 1);
    } else if (maxY > 3) {
        // Matige lactaatwaarden: matige exponentiële groei
        const k = 1.8;
        exponentialValue = minY + (maxY - minY) * (Math.exp(k * normalizedX) - 1) / (Math.exp(k) - 1);
    } else {
        // Lage lactaatwaarden: bijna lineair
        const k = 1.2;
        exponentialValue = minY + (maxY - minY) * (Math.exp(k * normalizedX) - 1) / (Math.exp(k) - 1);
    }
    
    // Interpoleer tussen omliggende meetpunten voor lokalere precisie
    let i = 0;
    while (i < sortedPoints.length - 1 && sortedPoints[i + 1].x < targetX) {
        i++;
    }
    
    let localValue = exponentialValue;
    if (i < sortedPoints.length - 1) {
        const p1 = sortedPoints[i];
        const p2 = sortedPoints[i + 1];
        const t = (targetX - p1.x) / (p2.x - p1.x);
        
        // Mix tussen lokale lineaire interpolatie en globale exponentiële curve
        const linearLocal = p1.y + t * (p2.y - p1.y);
        localValue = 0.3 * linearLocal + 0.7 * exponentialValue; // 70% exponentieel, 30% lokaal
    }
    
    return Math.max(localValue, 0.8); // Minimum lactaat
}

// NIEUWE FUNCTIE: Speciale exponentiële interpolatie voor zwemmen
function interpolateExponentialForSwimming(points, targetX) {
    console.log(`🏊 === ZWEM INTERPOLATIE === voor X=${targetX.toFixed(2)} min/100m`);
    
    if (points.length === 0) return 1.0;
    if (points.length === 1) return points[0].y;
    
    // Sorteer punten op X-waarde (al gesorteerd maar zeker weten)
    const sortedPoints = [...points].sort((a, b) => a.x - b.x);
    console.log('🏊 Gesorteerde punten:', sortedPoints.map(p => `${p.x.toFixed(2)}min: ${p.y.toFixed(2)}mmol`));
    
    // ZWEMMEN: EENVOUDIGE LINEAIRE INTERPOLATIE die garanteerd door meetpunten gaat
    
    // Vind de twee omliggende punten
    let i = 0;
    while (i < sortedPoints.length - 1 && sortedPoints[i + 1].x < targetX) {
        i++;
    }
    
    // Als we exact op een meetpunt zitten
    if (i < sortedPoints.length && Math.abs(sortedPoints[i].x - targetX) < 0.001) {
        console.log(`🏊 Exact meetpunt gevonden: ${sortedPoints[i].y.toFixed(2)} mmol/L`);
        return sortedPoints[i].y;
    }
    
    // Extrapolatie aan de randen
    if (targetX <= sortedPoints[0].x) {
        console.log(`🏊 Extrapolatie links: ${sortedPoints[0].y.toFixed(2)} mmol/L`);
        return sortedPoints[0].y;
    }
    
    if (targetX >= sortedPoints[sortedPoints.length - 1].x) {
        console.log(`🏊 Extrapolatie rechts: ${sortedPoints[sortedPoints.length - 1].y.toFixed(2)} mmol/L`);
        return sortedPoints[sortedPoints.length - 1].y;
    }
    
    // Lineaire interpolatie tussen twee punten
    if (i < sortedPoints.length - 1) {
        const p1 = sortedPoints[i];
        const p2 = sortedPoints[i + 1];
        const t = (targetX - p1.x) / (p2.x - p1.x);
        const interpolatedValue = p1.y + t * (p2.y - p1.y);
        
        console.log(`🏊 Lineaire interpolatie tussen ${p1.x.toFixed(2)}min (${p1.y.toFixed(2)}mmol) en ${p2.x.toFixed(2)}min (${p2.y.toFixed(2)}mmol)`);
        console.log(`🏊 t=${t.toFixed(3)}, resultaat=${interpolatedValue.toFixed(2)} mmol/L`);
        
        return Math.max(interpolatedValue, 0.8); // Minimum lactaat
    }
    
    // Fallback
    return sortedPoints[0].y;
}

// Functie om drag event listeners in te stellen
function setupDragEventListeners(canvas) {
    console.log('setupDragEventListeners aangeroepen voor canvas:', canvas);
    
    canvas.addEventListener('mousedown', function(event) {
        console.log('mousedown event getriggerd');
        console.log('currentThresholds:', currentThresholds);
        console.log('currentXField:', currentXField);
        
        if (!currentThresholds || !currentXField) {
            console.log('Geen thresholds of xField, geen drag mogelijk');
            return;
        }
        
        const rect = canvas.getBoundingClientRect();
        const x = event.clientX - rect.left;
        const y = event.clientY - rect.top;
        
        console.log('Klik positie:', x, y);
        
        // Controleer of we op een drempellijn klikken (verticale lijnen)
        const chart = hartslagLactaatChart;
        const chartArea = chart.chartArea;
        
        console.log('Chart area:', chartArea);
        
        if (currentThresholds.aerobe) {
            const aerobeX = chart.scales.x.getPixelForValue(currentThresholds.aerobe[currentXField]);
            console.log('Aërobe lijn X positie:', aerobeX, 'Afstand tot klik:', Math.abs(x - aerobeX));
            
            if (Math.abs(x - aerobeX) < 15 && y >= chartArea.top - 10 && y <= chartArea.bottom + 10) {
                console.log('Aërobe drempel geselecteerd voor drag');
                isDragging = true;
                dragTarget = 'aerobe';
                canvas.style.cursor = 'ew-resize';
                event.preventDefault();
                return;
            }
        }
        
        if (currentThresholds.anaerobe) {
            const anaerobeX = chart.scales.x.getPixelForValue(currentThresholds.anaerobe[currentXField]);
            console.log('Anaërobe lijn X positie:', anaerobeX, 'Afstand tot klik:', Math.abs(x - anaerobeX));
            
            if (Math.abs(x - anaerobeX) < 15 && y >= chartArea.top - 10 && y <= chartArea.bottom + 10) {
                console.log('Anaërobe drempel geselecteerd voor drag');
                isDragging = true;
                dragTarget = 'anaerobe';
                canvas.style.cursor = 'ew-resize';
                event.preventDefault();
                return;
            }
        }
        
        console.log('Geen drempel geselecteerd');
    });
    
    canvas.addEventListener('mousemove', function(event) {
        const rect = canvas.getBoundingClientRect();
        const x = event.clientX - rect.left;
        const y = event.clientY - rect.top;
        
        if (isDragging && dragTarget) {
            // Update drempel positie
            const chart = hartslagLactaatChart;
            const newXValue = chart.scales.x.getValueForPixel(x);
            
            if (dragTarget === 'aerobe' && currentThresholds.aerobe) {
                currentThresholds.aerobe[currentXField] = newXValue;
                currentThresholds.aerobe.hartslag = interpolateLinear(getTableData(), newXValue, 'hartslag', currentXField);
            } else if (dragTarget === 'anaerobe' && currentThresholds.anaerobe) {
                currentThresholds.anaerobe[currentXField] = newXValue;
                currentThresholds.anaerobe.hartslag = interpolateLinear(getTableData(), newXValue, 'hartslag', currentXField);
            }
            
            // Update input velden
            updateThresholdValues(currentThresholds, currentXField);
            
            // Herteken grafiek
            chart.update('none');
        } else {
            // Verander cursor als we over een drempel hoveren
            if (!currentThresholds || !currentXField) return;
            
            const chart = hartslagLactaatChart;
            const chartArea = chart.chartArea;
            let overThreshold = false;
            
            if (currentThresholds.aerobe) {
                const aerobeX = chart.scales.x.getPixelForValue(currentThresholds.aerobe[currentXField]);
                if (Math.abs(x - aerobeX) < 15 && y >= chartArea.top - 10 && y <= chartArea.bottom + 10) {
                    overThreshold = true;
                }
            }
            
            if (currentThresholds.anaerobe) {
                const anaerobeX = chart.scales.x.getPixelForValue(currentThresholds.anaerobe[currentXField]);
                if (Math.abs(x - anaerobeX) < 15 && y >= chartArea.top - 10 && y <= chartArea.bottom + 10) {
                    overThreshold = true;
                }
            }
            
            canvas.style.cursor = overThreshold ? 'ew-resize' : 'default';
        }
    });
    
    canvas.addEventListener('mouseup', function() {
        isDragging = false;
        dragTarget = null;
        canvas.style.cursor = 'default';
    });
    
    canvas.addEventListener('mouseleave', function() {
        isDragging = false;
        dragTarget = null;
        canvas.style.cursor = 'default';
    });
}

// Eenvoudige lineaire interpolatie voor debugging (behouden)
function interpolateSimple(points, targetX) {
    if (points.length === 0) return 0;
    if (points.length === 1) return points[0].y;
    
    // Vind omliggende punten
    for (let i = 0; i < points.length - 1; i++) {
        if (targetX >= points[i].x && targetX <= points[i + 1].x) {
            const ratio = (targetX - points[i].x) / (points[i + 1].x - points[i].x);
            return points[i].y + ratio * (points[i + 1].y - points[i].y);
        }
    }
    
    // Extrapolatie
    if (targetX < points[0].x) return points[0].y;
    if (targetX > points[points.length - 1].x) return points[points.length - 1].y;
    
    return points[0].y;
}

// Exponentiële interpolatie voor lactaatdata
function interpolateExponential(points, x) {
    // Sorteer punten op X-waarde voor het vinden van omliggende punten
    const sortedPoints = [...points].sort((a, b) => a[Object.keys(a).find(k => k !== 'lactaat' && k !== 'hartslag')] - b[Object.keys(b).find(k => k !== 'lactaat' && k !== 'hartslag')]);
    
    // Bepaal het X-veld (vermogen, snelheid, of tijd)
    const xField = Object.keys(sortedPoints[0]).find(k => k !== 'lactaat' && k !== 'hartslag');
    
    // Vind de twee punten rondom x
    let i = 0;
    while (i < sortedPoints.length - 1 && sortedPoints[i + 1][xField] < x) {
        i++;
    }
    
    if (i === 0 && x < sortedPoints[0][xField]) return sortedPoints[0].lactaat;
    if (i === sortedPoints.length - 1) return sortedPoints[sortedPoints.length - 1].lactaat;
    
    const p1 = sortedPoints[i];
    const p2 = sortedPoints[i + 1];
    
    // Exponentiële interpolatie voor realistische lactaat curve
    const t = (x - p1[xField]) / (p2[xField] - p1[xField]);
    
    // Pas exponentiële groei toe voor lactaat (lactaat stijgt exponentieel)
    let exponentialT;
    if (p2.lactaat > p1.lactaat && p2.lactaat > 4) {
        // Bij hoge lactaatwaarden: sterke exponentiële groei
        exponentialT = Math.pow(t, 0.7); // Minder steep voor meer realisme
    } else {
        // Bij lage lactaatwaarden: meer lineaire interpolatie
        exponentialT = t;
    }
    
    const interpolatedValue = p1.lactaat + exponentialT * (p2.lactaat - p1.lactaat);
    
    return Math.max(interpolatedValue, 0.5); // Minimum lactaat waarde
}

// Functie om grafiek te genereren
function generateChart() {
    console.log('generateChart() wordt uitgevoerd');
    console.log('currentTableType:', currentTableType);
    
    const data = getTableData();
    console.log('Gevonden data:', data);
    
    if (!data || data.length < 2) {
        console.log('Niet genoeg data voor grafiek. Data length:', data ? data.length : 'null');
        return;
    }
    
    // Bepaal X-as veld op basis van testtype
    const xField = currentTableType === 'fietstest' ? 'vermogen' : 
                   currentTableType === 'looptest' ? 'snelheid' : 
                   currentTableType === 'veldtest_lopen' ? 'snelheid' :
                   currentTableType === 'veldtest_fietsen' ? 'vermogen' : 
                   currentTableType === 'veldtest_zwemmen' ? 'snelheid' : 'tijd';
    const xLabel = currentTableType === 'fietstest' ? 'Vermogen (Watt)' : 
                   currentTableType === 'looptest' ? 'Snelheid (km/h)' : 
                   currentTableType === 'veldtest_lopen' ? 'Snelheid (km/h)' :
                   currentTableType === 'veldtest_fietsen' ? 'Vermogen (Watt)' : 
                   currentTableType === 'veldtest_zwemmen' ? 'Snelheid (min/100m)' : 'Tijd (min)';
    
    // Bereken drempels met geselecteerde methode
    const selectedMethod = document.getElementById('analyse_methode').value;
    const thresholds = calculateThresholds(data, selectedMethod);
    
    // Prepareer data voor grafiek
    const hartslagData = data.filter(d => d.hartslag && d[xField]).map(d => ({
        x: d[xField],
        y: d.hartslag
    }));
    
    const rawLactaatData = data.filter(d => d.lactaat && d[xField]).map(d => ({
        x: d[xField],
        y: d.lactaat
    }));
    
    // Genereer vloeiende lactaatcurve
    const lactaatData = generateSmoothLactaatCurve(rawLactaatData);
    
    console.log('Raw lactaat data:', rawLactaatData);
    console.log('Smooth lactaat data (eerste 5):', lactaatData.slice(0, 5));
    console.log('Smooth lactaat data length:', lactaatData.length);
    
    console.log('xField:', xField);
    console.log('Hartslag data:', hartslagData);
    console.log('Lactaat data:', lactaatData);
    
    const ctx = document.getElementById('hartslagLactaatGrafiek').getContext('2d');
    
    // Destroy existing chart
    if (hartslagLactaatChart) {
        hartslagLactaatChart.destroy();
    }
    
    // Bereken min en max waarden voor drempellijnen
    const allXValues = [...hartslagData, ...lactaatData].map(d => d.x);
    const minX = Math.min(...allXValues);
    const maxX = Math.max(...allXValues);
    
    // Bereken Y-as bereik voor verticale drempellijnen
    const allHartslagValues = hartslagData.map(d => d.y);
    const minHartslag = Math.min(...allHartslagValues);
    const maxHartslag = Math.max(...allHartslagValues);
    
    // Voeg datasets toe
    const datasets = [{
        label: 'Hartslag (bpm)',
        data: hartslagData,
        borderColor: 'rgb(59, 130, 246)',
        backgroundColor: 'rgba(59, 130, 246, 0.1)',
        yAxisID: 'y',
        pointRadius: 4,
        tension: 0.3, // Lichte curve voor hartslag
        fill: false,
        cubicInterpolationMode: 'monotone' // Vloeiende curve door punten
    }];
    
    // Voeg lactaat curve toe als er data is
    if (lactaatData && lactaatData.length > 0) {
        datasets.push({
            label: 'Lactaat Curve (mmol/L)',
            data: lactaatData,
            borderColor: 'rgb(16, 185, 129)',
            backgroundColor: 'rgba(16, 185, 129, 0.1)',
            yAxisID: 'y1',
            pointRadius: 0,
            tension: 0, // Geen extra tension, onze interpolatie is al exponentieel
            fill: false,
            borderWidth: 3,
            showLine: true,
            stepped: false
        });
        console.log('Lactaat curve dataset toegevoegd met', lactaatData.length, 'punten');
    }
    
    // Voeg lactaat meetpunten toe als er data is
    if (rawLactaatData && rawLactaatData.length > 0) {
        datasets.push({
            label: 'Lactaat Meetpunten',
            data: rawLactaatData,
            borderColor: 'rgb(16, 185, 129)',
            backgroundColor: 'rgb(16, 185, 129)',
            yAxisID: 'y1',
            pointRadius: 5,
            showLine: false,
            pointStyle: 'circle'
        });
        console.log('Lactaat meetpunten dataset toegevoegd met', rawLactaatData.length, 'punten');
    }
    
    console.log('Totaal datasets:', datasets.length);
    
    // Voor D-max methode: voeg hulplijn en D-max punt toe (eerste naar laatste punt van DATA)
    if (selectedMethod === 'dmax' && rawLactaatData && rawLactaatData.length > 2 && thresholds && thresholds.anaerobe) {
        const firstDataPoint = rawLactaatData[0];
        const lastDataPoint = rawLactaatData[rawLactaatData.length - 1];
        
        // Hulplijn tussen eerste en laatste DATAPUNT (niet curve punt)
        datasets.push({
            label: 'D-max Hulplijn (Eerste → Laatste)',
            data: [
                {x: firstDataPoint.x, y: firstDataPoint.y},
                {x: lastDataPoint.x, y: lastDataPoint.y}
            ],
            borderColor: 'rgba(107, 114, 128, 0.7)',
            backgroundColor: 'rgba(107, 114, 128, 0.1)',
            borderWidth: 2,
            borderDash: [3, 3],
            pointRadius: 3,
            pointBackgroundColor: 'rgb(107, 114, 128)',
            yAxisID: 'y1',
            showLine: true,
            fill: false
        });
        
        // D-max punt markeren (anaërobe drempel)
        const dmaxX = thresholds.anaerobe[xField];
        const dmaxLactaat = thresholds.anaerobe.lactaat;
        
        datasets.push({
            label: 'D-max Punt (Wiskundig Berekend)',
            data: [{x: dmaxX, y: dmaxLactaat}],
            borderColor: 'rgb(245, 158, 11)',
            backgroundColor: 'rgb(245, 158, 11)',
            pointRadius: 12,
            pointHoverRadius: 14,
            pointStyle: 'star',
            yAxisID: 'y1',
            showLine: false
        });
        
        // LOODLIJN: Verticale lijn van D-max punt naar hulplijn
        // Bereken hulplijn Y-waarde op dmaxX positie
        const m = (lastDataPoint.y - firstDataPoint.y) / (lastDataPoint.x - firstDataPoint.x);
        const b = firstDataPoint.y - m * firstDataPoint.x;
        const hulplijnY = m * dmaxX + b;
        
        datasets.push({
            label: 'D-max Loodlijn (Maximale Afstand)',
            data: [
                {x: dmaxX, y: dmaxLactaat},
                {x: dmaxX, y: hulplijnY}
            ],
            borderColor: 'rgb(220, 38, 127)',
            backgroundColor: 'rgb(220, 38, 127)',
            borderWidth: 4,
            pointRadius: 4,
            pointBackgroundColor: 'rgb(220, 38, 127)',
            yAxisID: 'y1',
            showLine: true,
            fill: false
        });
        
        // Punt op hulplijn markeren
        datasets.push({
            label: 'Hulplijn Punt',
            data: [{x: dmaxX, y: hulplijnY}],
            borderColor: 'rgb(107, 114, 128)',
            backgroundColor: 'rgb(107, 114, 128)',
            pointRadius: 6,
            yAxisID: 'y1',
            showLine: false
        });
        
        console.log('📏 D-max Loodlijn:', `${dmaxX.toFixed(1)}W: curve=${dmaxLactaat.toFixed(2)} → lijn=${hulplijnY.toFixed(2)} (afstand=${Math.abs(dmaxLactaat - hulplijnY).toFixed(4)})`);
    }
    
    // Voor D-max Modified methode: voeg hulplijn en D-max punt toe (aërobe punt naar laatste datapunt)
    if (selectedMethod === 'dmax_modified' && rawLactaatData && rawLactaatData.length > 2 && thresholds && thresholds.anaerobe) {
        // SPECIALE BEHANDELING VOOR ZWEMMEN: gebruik correcte hulplijn richting
        const isSwimming = currentTableType === 'veldtest_zwemmen';
        
        let startPoint, endPoint;
        if (isSwimming) {
            // Voor zwemmen: hulplijn van laatste punt (langzaamste) naar aërobe punt (snellere baseline)
            startPoint = rawLactaatData[rawLactaatData.length - 1]; // Laatste = langzaamste
            endPoint = thresholds.aerobe; // Aërobe = snellere baseline
        } else {
            // Voor andere sporten: normale richting
            startPoint = thresholds.aerobe;
            endPoint = rawLactaatData[rawLactaatData.length - 1];
        }
        
        // Hulplijn tussen correcte punten
        datasets.push({
            label: isSwimming ? 'D-max Modified Hulplijn (Laatste → Aërobe)' : 'D-max Modified Hulplijn (Aërobe → Laatste)',
            data: [
                {x: startPoint.x || startPoint[xField], y: startPoint.y || startPoint.lactaat},
                {x: endPoint.x || endPoint[xField], y: endPoint.y || endPoint.lactaat}
            ],
            borderColor: 'rgba(107, 114, 128, 0.7)',
            backgroundColor: 'rgba(107, 114, 128, 0.1)',
            borderWidth: 2,
            borderDash: [3, 3],
            pointRadius: 3,
            pointBackgroundColor: 'rgb(107, 114, 128)',
            yAxisID: 'y1',
            showLine: true,
            fill: false
        });
        
        // D-max punt markeren (anaërobe drempel)
        const dmaxX = thresholds.anaerobe[xField];
        const dmaxLactaat = thresholds.anaerobe.lactaat;
        
        datasets.push({
            label: 'D-max Modified Punt (Wiskundig)',
            data: [{x: dmaxX, y: dmaxLactaat}],
            borderColor: 'rgb(245, 158, 11)',
            backgroundColor: 'rgb(245, 158, 11)',
            pointRadius: 12,
            pointHoverRadius: 14,
            pointStyle: 'star',
            yAxisID: 'y1',
            showLine: false
        });
        
        // LOODLIJN: Verticale lijn van D-max punt naar hulplijn
        // Bereken hulplijn Y-waarde op dmaxX positie (lineaire interpolatie tussen start en eind punt)
        const startX = startPoint.x || startPoint[xField];
        const startY = startPoint.y || startPoint.lactaat;
        const endX = endPoint.x || endPoint[xField];
        const endY = endPoint.y || endPoint.lactaat;
        
        const m = (endY - startY) / (endX - startX);
        const b = startY - m * startX;
        const hulplijnY = m * dmaxX + b;
        
        datasets.push({
            label: 'D-max Modified Loodlijn',
            data: [
                {x: dmaxX, y: dmaxLactaat},
                {x: dmaxX, y: hulplijnY}
            ],
            borderColor: 'rgb(220, 38, 127)',
            backgroundColor: 'rgb(220, 38, 127)',
            borderWidth: 4,
            pointRadius: 4,
            pointBackgroundColor: 'rgb(220, 38, 127)',
            yAxisID: 'y1',
            showLine: true,
            fill: false
        });
        
        // Punt op hulplijn markeren
        datasets.push({
            label: 'Modified Hulplijn Punt',
            data: [{x: dmaxX, y: hulplijnY}],
            borderColor: 'rgb(107, 114, 128)',
            backgroundColor: 'rgb(107, 114, 128)',
            pointRadius: 6,
            yAxisID: 'y1',
            showLine: false
        });
        
        console.log('📏 D-max Modified Loodlijn:', `${dmaxX.toFixed(1)}W: curve=${dmaxLactaat.toFixed(2)} → lijn=${hulplijnY.toFixed(2)} (afstand=${Math.abs(dmaxLactaat - hulplijnY).toFixed(4)})`);
    }
    
    // Voeg drempellijnen toe als ze berekend zijn (verticale lijnen)
    if (thresholds && thresholds.aerobe) {
        const aerobeX = thresholds.aerobe[xField];
        if (aerobeX) {
            // VERBETERDE AËROBE DREMPELLIJN - ZEER ZICHTBAAR
            datasets.push({
                label: 'Aërobe Drempel (Rode Lijn)',
                data: [{x: aerobeX, y: minHartslag - 20}, {x: aerobeX, y: maxHartslag + 20}],
                borderColor: 'rgb(220, 38, 38)', // Helderrood
                backgroundColor: 'rgba(220, 38, 38, 0.3)',
                borderWidth: 5, // Veel dikker
                borderDash: [8, 4], // Duidelijke streepjes
                pointRadius: 8,
                pointBackgroundColor: 'rgb(220, 38, 38)',
                pointBorderColor: 'white',
                pointBorderWidth: 2,
                yAxisID: 'y',
                showLine: true,
                fill: false,
                tension: 0
            });
            
            console.log('✅ Aërobe drempellijn toegevoegd op X:', aerobeX.toFixed(1), 'W');
        }
    }
    
    if (thresholds && thresholds.anaerobe) {
        const anaerobeX = thresholds.anaerobe[xField];
        if (anaerobeX) {
            // VERBETERDE ANAËROBE DREMPELLIJN - ZEER ZICHTBAAR
            datasets.push({
                label: 'Anaërobe Drempel (Oranje Lijn)',
                data: [{x: anaerobeX, y: minHartslag - 20}, {x: anaerobeX, y: maxHartslag + 20}],
                borderColor: 'rgb(245, 158, 11)', // Helder oranje
                backgroundColor: 'rgba(245, 158, 11, 0.3)',
                borderWidth: 5, // Veel dikker
                borderDash: [12, 6], // Duidelijke streepjes
                pointRadius: 8,
                pointBackgroundColor: 'rgb(245, 158, 11)',
                pointBorderColor: 'white',
                pointBorderWidth: 2,
                yAxisID: 'y',
                showLine: true,
                fill: false,
                tension: 0
            });
            
            console.log('✅ Anaërobe drempellijn toegevoegd op X:', anaerobeX.toFixed(1), 'W');
        }
    }
    
    hartslagLactaatChart = new Chart(ctx, {
        type: 'line',
        data: {
            datasets: datasets
        },
        plugins: [{
            id: 'draggableThresholds',
            afterDatasetsDraw: function(chart) {
                drawDraggableThresholds(chart);
            }
        }],
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index'
            },
            plugins: {
                title: {
                    display: true,
                    text: 'Hartslag en Lactaat Analyse',
                    font: {
                        size: 16,
                        weight: 'bold'
                    }
                },
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: 'white',
                    bodyColor: 'white',
                    borderColor: 'rgba(255, 255, 255, 0.1)',
                    borderWidth: 1,
                    callbacks: {
                        title: function(context) {
                            const value = context[0].parsed.x;
                            // SPECIALE FORMATTING VOOR ZWEMMEN TOOLTIP
                            if (currentTableType === 'veldtest_zwemmen') {
                                const minuten = Math.floor(value);
                                const seconden = Math.round((value - minuten) * 60);
                                return `Snelheid: ${minuten}:${seconden.toString().padStart(2, '0')} min/100m`;
                            }
                            return context[0].label;
                        },
                        afterBody: function(context) {
                            if (selectedMethod === 'dmax') {
                                const point = context[0];
                                if (point.dataset.label === 'D-max Punt') {
                                    return ['', 'Dit punt heeft de maximale', 'afstand tot de hulplijn', '(D-max methode: eerste → laatste)'];
                                } else if (point.dataset.label === 'D-max Hulplijn (Eerste → Laatste)') {
                                    return ['', 'Hulplijn voor D-max berekening', 'tussen eerste en laatste punt'];
                                }
                            } else if (selectedMethod === 'dmax_modified') {
                                const point = context[0];
                                if (point.dataset.label === 'D-max Modified Punt') {
                                    return ['', 'Dit punt heeft de maximale', 'afstand tot de baseline hulplijn', '(D-max Modified: baseline +0.4 → laatste)'];
                                } else if (point.dataset.label === 'D-max Modified Hulplijn (Baseline +0.4)') {
                                    return ['', 'Hulplijn voor D-max Modified', 'van baseline +0.4 naar laatste punt'];
                                }
                            }
                            return '';
                        }
                    }
                }
            },
            elements: {
                line: {
                    tension: 0.4
                },
                point: {
                    radius: 4,
                    hoverRadius: 6,
                    backgroundColor: 'white',
                    borderWidth: 2
                }
            },
            scales: {
                x: {
                    type: 'linear',
                    display: true,
                    title: {
                        display: true,
                        text: xLabel,
                        font: {
                            size: 14,
                            weight: 'bold'
                        }
                    },
                    ticks: {
                        stepSize: currentTableType === 'fietstest' ? 20 : 
                                 currentTableType === 'veldtest_fietsen' ? 20 :
                                 currentTableType === 'veldtest_lopen' ? 1 : 1,
                        callback: function(value) {
                            // SPECIALE FORMATTING VOOR ZWEMMEN: mm:ss
                            if (currentTableType === 'veldtest_zwemmen') {
                                const minuten = Math.floor(value);
                                const seconden = Math.round((value - minuten) * 60);
                                return `${minuten}:${seconden.toString().padStart(2, '0')}`;
                            }
                            return Math.round(value);
                        }
                    },
                    // OMKEREN X-AS VOOR ZWEMMEN (snel naar langzaam)
                    reverse: currentTableType === 'veldtest_zwemmen',
                    min: minX - (maxX - minX) * 0.05,
                    max: maxX + (maxX - minX) * 0.05
                },
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Hartslag (bpm)'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Lactaat (mmol/L)'
                    },
                    grid: {
                        drawOnChartArea: false,
                    },
                }
            }
        }
    });
    
    // Voeg drempellijnen toe en update input velden
    if (thresholds) {
        updateThresholdValues(thresholds, xField);
        console.log('Drempels berekend:', thresholds);
        console.log('xField:', xField);
        console.log('Aërobe drempel X-waarde:', thresholds.aerobe ? thresholds.aerobe[xField] : 'geen');
        console.log('Anaërobe drempel X-waarde:', thresholds.anaerobe ? thresholds.anaerobe[xField] : 'geen');
    }
    
    console.log('Grafiek gegenereerd met', data.length, 'datapunten');
    console.log('X-as bereik:', minX, 'tot', maxX);
    console.log('Y-as bereik (hartslag):', minHartslag, 'tot', maxX);
    
    // Voeg drag event listeners toe
    setupDragEventListeners(hartslagLactaatChart.canvas);
}

// Globale variabelen voor versleepbare drempels
let currentThresholds = null;
let currentXField = null;
let isDragging = false;
let dragTarget = null;

// Functie om drempelwaarden in de input velden te zetten
function updateThresholdValues(thresholds, xField) {
    console.log('updateThresholdValues aangeroepen met:', thresholds, xField);
    
    // Sla huidige drempels op voor drag functionaliteit
    currentThresholds = thresholds;
    currentXField = xField;
    
    if (thresholds.aerobe) {
        const aerobeVermogen = thresholds.aerobe[xField];
        const aerobeHartslag = thresholds.aerobe.hartslag;
        
        console.log('Aërobe drempel:', aerobeVermogen, 'eenheid,', aerobeHartslag, 'bpm');
        
        if (aerobeVermogen) {
            // 🏊 ZWEM DEBUG: Check of we een zwemtest hebben
            if (currentTableType === 'veldtest_zwemmen') {
                console.log('🏊 ZWEM: Aërobe waarde voor input:', aerobeVermogen.toFixed(3));
                // Voor zwemmen: gebruik decimale waarde in input, maar toon later als mm:ss
                document.getElementById('aerobe_drempel_vermogen').value = parseFloat(aerobeVermogen.toFixed(3));
            } else {
                // Voor andere testen: normale decimale waarde
                document.getElementById('aerobe_drempel_vermogen').value = parseFloat(aerobeVermogen.toFixed(1));
            }
        }
        if (aerobeHartslag) document.getElementById('aerobe_drempel_hartslag').value = Math.round(aerobeHartslag);
    }
    
    if (thresholds.anaerobe) {
        const anaerobeVermogen = thresholds.anaerobe[xField];
        const anaerobeHartslag = thresholds.anaerobe.hartslag;
        
        console.log('Anaërobe drempel:', anaerobeVermogen, 'eenheid,', anaerobeHartslag, 'bpm');
        
        if (anaerobeVermogen) {
            // 🏊 ZWEM DEBUG: Check of we een zwemtest hebben
            if (currentTableType === 'veldtest_zwemmen') {
                console.log('🏊 ZWEM: Anaërobe waarde voor input:', anaerobeVermogen.toFixed(3));
                // Voor zwemmen: gebruik decimale waarde in input, maar toon later als mm:ss
                document.getElementById('anaerobe_drempel_vermogen').value = parseFloat(anaerobeVermogen.toFixed(3));
            } else {
                // Voor andere testen: normale decimale waarde
                document.getElementById('anaerobe_drempel_vermogen').value = parseFloat(anaerobeVermogen.toFixed(1));
            }
        }
        if (anaerobeHartslag) document.getElementById('anaerobe_drempel_hartslag').value = Math.round(anaerobeHartslag);
    }
}

// Functie om versleepbare drempellijnen te tekenen
function drawDraggableThresholds(chart) {
    if (!currentThresholds || !currentXField) return;
    
    const ctx = chart.ctx;
    const chartArea = chart.chartArea;
    
    // Teken aërobe drempel
    if (currentThresholds.aerobe) {
        const xValue = currentThresholds.aerobe[currentXField];
        const xPixel = chart.scales.x.getPixelForValue(xValue);
        
        ctx.save();
        ctx.strokeStyle = 'rgba(239, 68, 68, 0.8)';
        ctx.lineWidth = 3;
        ctx.setLineDash([5, 5]);
        ctx.beginPath();
        ctx.moveTo(xPixel, chartArea.top);
        ctx.lineTo(xPixel, chartArea.bottom);
        ctx.stroke();
        
        // Voeg draggable indicator toe
        ctx.fillStyle = 'rgba(239, 68, 68, 1)';
        ctx.fillRect(xPixel - 5, chartArea.top - 10, 10, 20);
        ctx.restore();
    }
    
    // Teken anaërobe drempel
    if (currentThresholds.anaerobe) {
        const xValue = currentThresholds.anaerobe[currentXField];
        const xPixel = chart.scales.x.getPixelForValue(xValue);
        
        ctx.save();
        ctx.strokeStyle = 'rgba(245, 158, 11, 0.8)';
        ctx.lineWidth = 3;
        ctx.setLineDash([10, 5]);
        ctx.beginPath();
        ctx.moveTo(xPixel, chartArea.top);
        ctx.lineTo(xPixel, chartArea.bottom);
        ctx.stroke();
        
        // Voeg draggable indicator toe
        ctx.fillStyle = 'rgba(245, 158, 11, 1)';
        ctx.fillRect(xPixel - 5, chartArea.top - 10, 10, 20);
        ctx.restore();
    }
}

// === TEST FUNCTIE VOOR DEBUGGING ===
function testJavaScript() {
    console.log('🔧 JavaScript test gestart...');
    
    // Test basisselectors
    const aerobeBtn = document.getElementById('ai-aerobe-btn');
    const anaerobeBtn = document.getElementById('ai-anaerobe-btn');
    const aerobeTextarea = document.getElementById('advies_aerobe_drempel');
    const anaerobeTextarea = document.getElementById('advies_anaerobe_drempel');
    
    console.log('Elements gevonden:');
    console.log('- Aërobe knop:', !!aerobeBtn, aerobeBtn);
    console.log('- Anaërobe knop:', !!anaerobeBtn, anaerobeBtn);
    console.log('- Aërobe textarea:', !!aerobeTextarea, aerobeTextarea);
    console.log('- Anaërobe textarea:', !!anaerobeTextarea, anaerobeTextarea);
    
    // Test CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    console.log('- CSRF token:', !!csrfToken, csrfToken ? csrfToken.substring(0, 10) + '...' : 'GEEN');
    
    // Test fetch functionaliteit
    console.log('🌐 Test fetch functionaliteit...');
    
    fetch('/api/ai-advice', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken || '',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            type: 'test',
            testtype: 'fietstest'
        })
    })
    .then(response => {
        console.log('📡 Test response:', response.status, response.statusText);
        return response.text();
    })
    .then(text => {
        console.log('📄 Response body:', text);
        alert('JavaScript test voltooid! Check console voor details.');
    })
    .catch(error => {
        console.error('❌ Test fetch error:', error);
        alert('Fetch test gefaald: ' + error.message);
    });
    
    alert('JavaScript functionaliteit test gestart! Check console (F12) voor details.');
}

// Debug functie
function debugForm() {
    const debugInfo = document.getElementById('debug-info');
    const datum = document.getElementById('datum').value;
    const testtype = document.getElementById('testtype').value;
    
    document.getElementById('debug-datum').textContent = datum || 'LEEG';
    document.getElementById('debug-testtype').textContent = testtype || 'LEEG';
    
    debugInfo.style.display = 'block';
    
    console.log('Form Debug:');
    console.log('Datum:', datum);
    console.log('Testtype:', testtype);
    console.log('Form element:', document.querySelector('#inspanningstest-form'));
    
    // Check all form elements
    const formData = new FormData(document.querySelector('#inspanningstest-form'));
    for (let [key, value] of formData.entries()) {
        console.log(key + ':', value);
    }
}

// === AI ADVIES FUNCTIONALITEIT ===

// === COMPLETE AI ANALYSE FUNCTIONALITEIT ===

/**
 * Genereer complete AI analyse van alle testparameters
 */
function generateCompleteAIAnalysis() {
    console.log('� Genereren complete AI analyse...');
    
    const button = document.getElementById('ai-complete-btn');
    const textarea = document.getElementById('complete_ai_analyse');
    
    if (!button || !textarea) {
        console.error('AI knop of textarea niet gevonden');
        return;
    }
    
    // Update knop status
    const originalText = button.innerHTML;
    button.innerHTML = '🔄 Analyseren...';
    button.disabled = true;
    
    // Verzamel ALLE beschikbare testdata
    const completeTestData = collectCompleteTestData();
    
    console.log('📊 Complete testdata verzameld:', completeTestData);
    
    // Verstuur naar complete AI analyse endpoint
    fetch('/api/ai-advice', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            'Accept': 'application/json'
        },
        body: JSON.stringify(completeTestData)
    })
    .then(response => {
        console.log('📡 Response ontvangen:', response.status);
        
        if (!response.ok) {
            return response.text().then(text => {
                console.error('Response body:', text);
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            });
        }
        return response.json();
    })
    .then(data => {
        console.log('✅ Complete analyse ontvangen:', data);
        
        if (data.success) {
            // Update textarea met complete analyse
            textarea.value = data.analysis;
            
            // Success feedback
            button.innerHTML = '✅ Analyse Compleet';
            button.style.backgroundColor = '#dcfce7';
            button.style.borderColor = '#16a34a';
            button.style.color = '#16a34a';
            
            console.log('✅ Complete AI analyse succesvol gegenereerd');
            
            // Reset knop na 5 seconden
            setTimeout(() => {
                button.innerHTML = originalText;
                button.style.backgroundColor = '';
                button.style.borderColor = '';
                button.style.color = '';
                button.disabled = false;
            }, 5000);
            
        } else {
            throw new Error(data.message || 'Onbekende fout');
        }
    })
    .catch(error => {
        console.error('❌ Fout bij complete AI analyse:', error);
        
        // Error feedback
        button.innerHTML = '❌ Fout';
        button.style.backgroundColor = '#fef2f2';
        button.style.borderColor = '#dc2626';
        button.style.color = '#dc2626';
        
        // Toon fallback analyse
        textarea.value = getCompleteAnalysisFallback(completeTestData);
        
        // Reset knop na 5 seconden
        setTimeout(() => {
            button.innerHTML = originalText;
            button.style.backgroundColor = '';
            button.style.borderColor = '';
            button.style.color = '';
            button.disabled = false;
        }, 5000);
        
        alert(`Er ging iets mis bij het genereren van de AI analyse: ${error.message}\n\nEr is een standaard analyse ingevuld.`);
    });
}

/**
 * Verzamel ALLE beschikbare testdata voor complete analyse
 */
function collectCompleteTestData() {
    // Bepaal leeftijd (fallback van 35 jaar indien niet beschikbaar)
    let leeftijd = 35;
    
    return {
        // Testtype en protocol
        testtype: document.getElementById('testtype')?.value || 'fietstest',
        analyse_methode: document.getElementById('analyse_methode')?.value || '',
        testlocatie: document.getElementById('testlocatie')?.value || '',
        
        // Doelstellingen - ZEER BELANGRIJK voor AI
        specifieke_doelstellingen: document.getElementById('specifieke_doelstellingen')?.value || 'Algemene fitheid verbetering',
        
        // Persoonlijke gegevens
        leeftijd: leeftijd,
        lichaamsgewicht_kg: parseFloat(document.getElementById('lichaamsgewicht_kg')?.value) || null,
        lichaamslengte_cm: parseFloat(document.getElementById('lichaamslengte_cm')?.value) || null,
        bmi: parseFloat(document.getElementById('bmi')?.value) || null,
        vetpercentage: parseFloat(document.getElementById('vetpercentage')?.value) || null,
        buikomtrek_cm: parseFloat(document.getElementById('buikomtrek_cm')?.value) || null,
        
        // Drempelwaarden - KERN van de analyse
        aerobe_drempel_vermogen: parseFloat(document.getElementById('aerobe_drempel_vermogen')?.value) || null,
        aerobe_drempel_hartslag: parseFloat(document.getElementById('aerobe_drempel_hartslag')?.value) || null,
        anaerobe_drempel_vermogen: parseFloat(document.getElementById('anaerobe_drempel_vermogen')?.value) || null,
        anaerobe_drempel_hartslag: parseFloat(document.getElementById('anaerobe_drempel_hartslag')?.value) || null,
        
        // Hartslaggegevens
        maximale_hartslag_bpm: parseFloat(document.getElementById('maximale_hartslag_bpm')?.value) || null,
        hartslag_rust_bpm: parseFloat(document.getElementById('hartslag_rust_bpm')?.value) || null,
        
        // Trainingstatus velden - BELANGRIJK voor herstel en belastbaarheid analyse
        slaapkwaliteit: parseInt(document.getElementById('slaapkwaliteit')?.value) || null,
        eetlust: parseInt(document.getElementById('eetlust')?.value) || null,
        gevoel_op_training: parseInt(document.getElementById('gevoel_op_training')?.value) || null,
        stressniveau: parseInt(document.getElementById('stressniveau')?.value) || null,
        gemiddelde_trainingstatus: parseFloat(document.getElementById('gemiddelde_trainingstatus')?.value) || null,
        training_dag_voor_test: document.getElementById('training_dag_voor_test')?.value || '',
        training_2d_voor_test: document.getElementById('training_2d_voor_test')?.value || '',
        
        // Besluit velden (indien ingevuld)
        besluit_lichaamssamenstelling: document.getElementById('besluit_lichaamssamenstelling')?.value || ''
    };
}

/**
 * Fallback complete analyse bij fouten
 */
function getCompleteAnalysisFallback(testData) {
    const testtype = testData.testtype || 'fietstest';
    const doelstellingen = testData.specifieke_doelstellingen || 'algemene fitheid';
    const lt1 = testData.aerobe_drempel_vermogen || 'niet gemeten';
    const lt2 = testData.anaerobe_drempel_vermogen || 'niet gemeten';
    const trainingstatus = testData.gemiddelde_trainingstatus || 'niet ingevuld';
    
    // Bepaal trainingstatus interpretatie
    let statusInterpretatie = '';
    if (trainingstatus !== 'niet ingevuld') {
        if (trainingstatus >= 8) {
            statusInterpretatie = '✅ Uitstekend - Optimaal voor intensieve training';
        } else if (trainingstatus >= 6) {
            statusInterpretatie = '👍 Goed - Geschikt voor normale training';
        } else if (trainingstatus >= 4) {
            statusInterpretatie = '⚠️ Matig - Focus op herstel en lichte training';
        } else {
            statusInterpretatie = '🚨 Laag - Herstel prioriteit, geen intensieve training';
        }
    }
    
    return `COMPLETE INSPANNINGSTEST ANALYSE

🎯 DOELSTELLINGEN: ${doelstellingen}

📊 GEMETEN DREMPELS:
• Aërobe drempel (LT1): ${lt1} Watt
• Anaërobe drempel (LT2): ${lt2} Watt

💤 TRAININGSTATUS:
• Gemiddelde score: ${trainingstatus}/10
${statusInterpretatie ? '• Status: ' + statusInterpretatie : ''}
${testData.slaapkwaliteit ? '• Slaapkwaliteit: ' + testData.slaapkwaliteit + '/10' : ''}
${testData.eetlust ? '• Eetlust: ' + testData.eetlust + '/10' : ''}
${testData.gevoel_op_training ? '• Gevoel op training: ' + testData.gevoel_op_training + '/10' : ''}
${testData.stressniveau ? '• Stressniveau: ' + testData.stressniveau + '/10 (10 = geen stress)' : ''}

🏆 PRESTATIECLASSIFICATIE:
Uw drempelwaardes worden geanalyseerd in de context van uw specifieke doelstellingen voor ${testtype}.

💪 BELANGRIJKE BEVINDINGEN:
• Uw aërobe basis vormt de foundation voor langdurige prestaties
• De anaërobe drempel bepaalt uw tempo-capaciteit
• Training moet afgestemd worden op uw gestelde doelen
${trainingstatus < 6 ? '• LET OP: Trainingstatus is matig/laag - prioriteer herstel' : ''}

🎯 TRAININGSAANBEVELINGEN:
1. Focus op 80% training onder LT1 voor aerobe ontwikkeling
2. Voeg specifieke intervaltraining toe rond LT2
3. Periodiseer training naar uw doelstellingen toe
4. Monitor progressie met regelmatige hertesten
${trainingstatus < 6 ? '5. BELANGRIJK: Verbeter herstel (slaap, voeding, stress management)' : ''}

Voor een uitgebreidere AI-analyse probeer het opnieuw wanneer de verbinding hersteld is.`;
}

/**
 * Verzamel alle relevante testdata voor AI analyse
 */
function collectTestDataForAI(type) {
    // Probeer leeftijd uit geboortedatum te berekenen (uit klant context indien beschikbaar)
    const today = new Date();
    let leeftijd = 45; // Default fallback
    
    // Probeer uit pagina context te halen (bijv. uit hidden fields of klant info)
    const klantLeeftijdElement = document.querySelector('[data-klant-leeftijd]');
    if (klantLeeftijdElement) {
        leeftijd = parseInt(klantLeeftijdElement.getAttribute('data-klant-leeftijd')) || 45;
    }
    
    // Verzamel alle beschikbare data
    const testData = {
        type: type,
        testtype: document.getElementById('testtype')?.value || 'fietstest',
        
        // Drempelwaarden
        aerobe_drempel_vermogen: parseFloat(document.getElementById('aerobe_drempel_vermogen')?.value) || null,
        aerobe_drempel_hartslag: parseFloat(document.getElementById('aerobe_drempel_hartslag')?.value) || null,
        anaerobe_drempel_vermogen: parseFloat(document.getElementById('anaerobe_drempel_vermogen')?.value) || null,
        anaerobe_drempel_hartslag: parseFloat(document.getElementById('anaerobe_drempel_hartslag')?.value) || null,
        
        // Persoonlijke gegevens
        specifieke_doelstellingen: document.getElementById('specifieke_doelstellingen')?.value || 'Algemene fitheid verbetering',
        lichaamsgewicht_kg: parseFloat(document.getElementById('lichaamsgewicht_kg')?.value) || null,
        lichaamslengte_cm: parseFloat(document.getElementById('lichaamslengte_cm')?.value) || null,
        leeftijd: leeftijd,
        
        // Extra context
        maximale_hartslag_bpm: parseFloat(document.getElementById('maximale_hartslag_bpm')?.value) || null,
        hartslag_rust_bpm: parseFloat(document.getElementById('hartslag_rust_bpm')?.value) || null,
        bmi: parseFloat(document.getElementById('bmi')?.value) || null
    };
    
    console.log('🔍 Verzamelde testdata voor AI:', testData);
    
    return testData;
}

/**
 * Fallback advies bij AI fouten
 */
function getFallbackAdvice(type, testData) {
    const testtype = testData.testtype || 'fietstest';
    
    if (type === 'aerobe') {
        const vermogen = testData.aerobe_drempel_vermogen;
        
        if (testtype.includes('loop')) {
            return `Uw aërobe drempel ligt op ${vermogen || 'uw gemeten'} km/h. Train voornamelijk onder deze intensiteit voor optimale vetverbranding en basisuithoudingsvermogen. Bouw geleidelijk op in volume en frequentie.`;
        }
        
        if (testtype.includes('zwem')) {
            return `Uw aërobe drempel is bepaald op ${vermogen || 'uw gemeten'} min/100m. Focus op lange, gestage zwemsessies net onder dit tempo voor aerobe ontwikkeling.`;
        }
        
        return `Uw aërobe drempel bedraagt ${vermogen || 'uw gemeten'} Watt. Train 80% van de tijd onder deze intensiteit voor optimale vetverbranding en basisfitheid.`;
    }
    
    if (type === 'anaerobe') {
        const vermogen = testData.anaerobe_drempel_vermogen;
        
        if (testtype.includes('loop')) {
            return `Uw anaërobe drempel is ${vermogen || 'uw gemeten'} km/h. Gebruik dit als basis voor intervaltraining en tempowerk. Limiteer hoogintensieve training tot 20% van totale trainingstijd.`;
        }
        
        if (testtype.includes('zwem')) {
            return `Uw anaërobe drempel ligt op ${vermogen || 'uw gemeten'} min/100m. Train intervallen rond dit tempo voor snelheidsverbetering.`;
        }
        
        return `Uw anaërobe drempel is ${vermogen || 'uw gemeten'} Watt. Gebruik dit vermogen voor intervaltraining en tempowerk ter voorbereiding op wedstrijden.`;
    }
    
    return 'Advies kon niet worden gegenereerd. Consulteer uw trainer voor specifieke aanbevelingen.';
}

// Toon alle form data zonder te submitten
function showFormData() {
    const form = document.querySelector('#inspanningstest-form');
    
    let output = '<div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded"><h4 class="font-bold text-blue-800 mb-2">DIAGNOSE RESULTAAT:</h4>';
    
    // Check of form gevonden wordt
    output += `<p><strong>Form element gevonden:</strong> ${!!form}</p>`;
    
    if (!form) {
        output += '<p class="text-red-600">FORM NIET GEVONDEN! Check HTML structuur.</p>';
        const debugInfo = document.getElementById('debug-info');
        debugInfo.innerHTML = output + '</div>';
        debugInfo.style.display = 'block';
        return;
    }
    
    // Check of input velden IN de form zitten
    const datumInput = document.getElementById('datum');
    const testtypeInput = document.getElementById('testtype');
    
    output += `<p><strong>Datum input bestaat:</strong> ${!!datumInput}</p>`;
    if (datumInput) {
        output += `<p><strong>Datum zit IN form:</strong> ${form.contains(datumInput)}</p>`;
        output += `<p><strong>Datum waarde:</strong> ${datumInput.value}</p>`;
    }
    
    output += `<p><strong>Testtype select bestaat:</strong> ${!!testtypeInput}</p>`;
    if (testtypeInput) {
        output += `<p><strong>Testtype zit IN form:</strong> ${form.contains(testtypeInput)}</p>`;
        output += `<p><strong>Testtype waarde:</strong> ${testtypeInput.value}</p>`;
    }
    
    // Tel alle inputs op de pagina vs in de form
    const allPageInputs = document.querySelectorAll('input, select, textarea');
    const allFormInputs = form.querySelectorAll('input, select, textarea');
    
    output += `<hr class="my-2">`;
    output += `<p><strong>Totaal inputs op pagina:</strong> ${allPageInputs.length}</p>`;
    output += `<p><strong>Inputs IN de form:</strong> ${allFormInputs.length}</p>`;
    
    if (allFormInputs.length < 10) {
        output += '<p class="text-red-600">TE WEINIG INPUTS IN FORM! HTML structuur probleem.</p>';
    }
    
    // Toon FormData
    const formData = new FormData(form);
    output += `<hr class="my-2"><p class="text-sm text-gray-600"><strong>FormData entries:</strong></p>`;
    
    let entryCount = 0;
    for (let [key, value] of formData.entries()) {
        output += `<p class="text-sm">${key}: ${value || 'LEEG'}</p>`;
        entryCount++;
    }
    
    output += `<p><strong>Totaal FormData entries:</strong> ${entryCount}</p>`;
    
    if (entryCount < 5) {
        output += '<p class="text-red-600">TE WEINIG FORM DATA! Inputs niet gekoppeld aan form.</p>';
        output += '<p class="text-blue-600"><strong>OPLOSSING:</strong> Check of alle inputs binnen de form tags staan!</p>';
    }
    
    output += '</div>';
    
    // Voeg output toe na de debug info
    const debugInfo = document.getElementById('debug-info');
    debugInfo.innerHTML = output;
    debugInfo.style.display = 'block';
    
    console.log('DIAGNOSE: Form gevonden:', !!form);
    console.log('DIAGNOSE: Inputs op pagina:', allPageInputs.length);
    console.log('DIAGNOSE: Inputs in form:', allFormInputs.length);
    console.log('DIAGNOSE: FormData entries:', entryCount);
}

// === TRAININGSZONES EXPORT FUNCTIE (VEILIG) ===
function exportZonesData() {
    console.log('📊 exportZonesData() aangeroepen');
    alert('Export functionaliteit komt in de volgende stap!');
}

// 🔧 NIEUWE FUNCTIE: Update handmatige zone waarden
function updateHandmatigeZone(zoneIndex, field, value) {
    console.log(`🔧 updateHandmatigeZone: Zone ${zoneIndex}, veld ${field} = ${value}`);
    
    if (!huidigeZonesData || !huidigeZonesData[zoneIndex]) {
        console.log('❌ Geen zones data beschikbaar voor update');
        return;
    }
    
    // Update de waarde in de zones data
    huidigeZonesData[zoneIndex][field] = parseFloat(value) || 0;
    
    console.log(`✅ Zone ${zoneIndex} bijgewerkt:`, huidigeZonesData[zoneIndex]);
    
    // Update de hidden input met de nieuwe data
    document.getElementById('trainingszones_data').value = JSON.stringify(huidigeZonesData);
    
    // Herbereken min/km voor looptesten als vermogen wijzigt
    if (currentTableType === 'looptest' && (field === 'minVermogen' || field === 'maxVermogen')) {
        console.log('🏃 Herbereken min/km voor looptest na vermogen wijziging');
        updateMinKmDisplay(zoneIndex);
    }
}

// 🏃 HULPFUNCTIE: Update min/km display voor looptesten
function updateMinKmDisplay(zoneIndex) {
    if (currentTableType !== 'looptest' || !huidigeZonesData[zoneIndex]) return;
    
    const zone = huidigeZonesData[zoneIndex];
    const minMinPerKmDecimal = zone.maxVermogen > 0 ? (60 / zone.maxVermogen) : 999;
    const maxMinPerKmDecimal = zone.minVermogen > 0 ? (60 / zone.minVermogen) : 999;
    
    // Converteer naar mm:ss formaat
    const minMinPerKm = formatMinPerKm(minMinPerKmDecimal);
    const maxMinPerKm = formatMinPerKm(maxMinPerKmDecimal);
    
    // Update de min/km cellen in de tabel (als ze bestaan)
    const rows = document.getElementById('zones-body').getElementsByTagName('tr');
    if (rows[zoneIndex]) {
        const cells = rows[zoneIndex].getElementsByTagName('td');
        if (cells.length >= 7) { // Controleer of min/km kolommen bestaan
            cells[5].textContent = minMinPerKm; // min min/km
            cells[6].textContent = maxMinPerKm; // max min/km
        }
    }
    
    console.log(`🔄 Min/km bijgewerkt voor zone ${zoneIndex}: ${minMinPerKm} - ${maxMinPerKm}`);
}

// === TRAININGSZONES BEREKENING FUNCTIONALITEIT ===

// Globale variabele voor huidige zones data
let huidigeZonesData = null;

// 🏃 HULPFUNCTIE: Converteer decimale minuten naar mm:ss formaat
function formatMinPerKm(decimalMinutes) {
    if (decimalMinutes >= 999 || isNaN(decimalMinutes)) return '∞';
    
    const minutes = Math.floor(decimalMinutes);
    const seconds = Math.round((decimalMinutes - minutes) * 60);
    
    return `${minutes}:${seconds.toString().padStart(2, '0')}`;
}

// Hoofdfunctie om trainingszones bij te werken
function updateTrainingszones() {
    console.log('🎯 updateTrainingszones() gestart');
    
    const methodeSelektor = document.getElementById('zones_methode');
    const aantalSelektor = document.getElementById('zones_aantal');
    const eenheidSelektor = document.getElementById('zones_eenheid');
    
    if (!methodeSelektor || !aantalSelektor || !eenheidSelektor) {
        console.log('❌ Zone selektoren niet gevonden');
        return;
    }
    
    // 🏃 AUTOMATISCHE EENHEID AANPASSING voor looptesten
    const isLooptest = currentTableType === 'looptest' || currentTableType === 'veldtest_lopen';
    if (isLooptest && eenheidSelektor.value !== 'snelheid') {
        console.log('🏃 Automatisch snelheid selecteren voor looptest');
        eenheidSelektor.value = 'snelheid';
    } else if (!isLooptest && eenheidSelektor.value === 'snelheid') {
        console.log('🚴 Automatisch hartslag selecteren voor fietstest');
        eenheidSelektor.value = 'hartslag';
    }
    
    const methode = methodeSelektor.value;
    const aantal = parseInt(aantalSelektor.value) || 5;
    const eenheid = eenheidSelektor.value;
    
    console.log('🔧 Zone configuratie:', { methode, aantal, eenheid, isLooptest, testType: currentTableType });
    
    // Toon/verberg container
    const container = document.getElementById('trainingszones-container');
    if (!methode) {
        container.style.display = 'none';
        return;
    }
    
    container.style.display = 'block';
    
    // Update methode label
    const methodeLabel = document.getElementById('zones-methode-label');
    methodeLabel.textContent = `(${methode.charAt(0).toUpperCase() + methode.slice(1)})`;
    
    // Bereken zones op basis van methode
    let zonesData = null;
    
    if (methode === 'bonami') {
        zonesData = berekenBonamiZones(aantal, eenheid);
    } else if (methode === 'karvonen') {
        zonesData = berekenKarvonenZones(aantal, eenheid);
    } else if (methode === 'handmatig') {
        zonesData = createHandmatigeZones(aantal, eenheid);
    }
    
    if (zonesData) {
        genereerZonesTabel(zonesData, eenheid);
        huidigeZonesData = zonesData;
        
        // Sla zones data op in hidden input
        document.getElementById('trainingszones_data').value = JSON.stringify(zonesData);
        
        console.log('✅ Trainingszones succesvol berekend en weergegeven');
    } else {
        console.log('❌ Kan geen zones berekenen - ontbrekende drempel data');
        toonZonesError('Geen drempel data beschikbaar. Voer eerst testresultaten in en bereken drempels.');
    }
}

// Bonami specifieke zones berekening - VEREENVOUDIGD VOOR STAP 2
function berekenBonamiZones(aantal, eenheid) {
    console.log('🚀 berekenBonamiZones() - aantal:', aantal, 'eenheid:', eenheid);
    console.log('🏃 Huidige testtype voor zones:', currentTableType);
    
    // Controleer of er drempel data beschikbaar is
    const LT1 = parseFloat(document.getElementById('aerobe_drempel_vermogen').value) || 0;
    const LT2 = parseFloat(document.getElementById('anaerobe_drempel_vermogen').value) || 0;
    const LT1_HR = parseFloat(document.getElementById('aerobe_drempel_hartslag').value) || 0;
    const LT2_HR = parseFloat(document.getElementById('anaerobe_drempel_hartslag').value) || 0;
    const HRmax = parseFloat(document.getElementById('maximale_hartslag_bpm').value) || 190;
    
    console.log('📊 Drempel waarden:', { LT1, LT2, LT1_HR, LT2_HR, HRmax });
    
    // 🏃 SPECIALE BEHANDELING VOOR LOOPTESTEN: Gebruik snelheden in plaats van wattages
    const isLooptest = currentTableType === 'looptest' || currentTableType === 'veldtest_lopen';
    const isZwemtest = currentTableType === 'veldtest_zwemmen';
    
    // Als er geen drempel data is, maak voorbeeldzones
    if (LT1 === 0 || LT2 === 0) {
        console.log('⚠️ Geen drempel data - genereer voorbeeldzones voor:', isZwemtest ? 'ZWEM' : isLooptest ? 'LOOP' : 'FIETS');
        return createVoorbeeldBonamiZones(aantal, isLooptest, isZwemtest);
    }
    
    // 🏊 SPECIALE ZWEM ZONES BEREKENING
    if (isZwemtest) {
        console.log('🏊 ZWEM ZONES BEREKENING met LT1:', LT1, 'LT2:', LT2);
        
        // Voor zwemmen: LANGZAMERE tijd = LAGERE intensiteit
        // We maken zones van LANGZAAM (hoge waarde) naar SNEL (lage waarde)
        const bonamiZwemZones = [
            {
                naam: 'HERSTEL',
                minVermogen: LT1 * 1.25, // Langzamer dan LT1 (lagere intensiteit)
                maxVermogen: LT1 * 1.10,
                minHartslag: Math.round(LT1_HR * 0.75),
                maxHartslag: Math.round(LT1_HR * 0.85),
                beschrijving: 'Herstel en regeneratie',
                kleur: '#E3F2FD',
                borgMin: 6,
                borgMax: 8
            },
            {
                naam: 'LANGE DUUR',
                minVermogen: LT1 * 1.10, // Iets langzamer dan LT1
                maxVermogen: LT1 * 1.05,
                minHartslag: Math.round(LT1_HR * 0.85),
                maxHartslag: Math.round(LT1_HR * 0.92),
                beschrijving: 'Lange duur training',
                kleur: '#E8F5E8',
                borgMin: 8,
                borgMax: 10
            },
            {
                naam: 'EXTENSIEF',
                minVermogen: LT1 * 1.05, // Net onder LT1
                maxVermogen: LT1,
                minHartslag: Math.round(LT1_HR * 0.92),
                maxHartslag: Math.round(LT1_HR),
                beschrijving: 'Extensieve duur training',
                kleur: '#F1F8E9',
                borgMin: 10,
                borgMax: 12
            },
            {
                naam: 'INTENSIEF',
                minVermogen: LT1, // Van LT1 naar LT2
                maxVermogen: LT2,
                minHartslag: Math.round(LT1_HR),
                maxHartslag: Math.round(LT2_HR),
                beschrijving: 'Intensieve duur training',
                kleur: '#FFF3E0',
                borgMin: 12,
                borgMax: 15
            },
            {
                naam: 'TEMPO',
                minVermogen: LT2, // Van LT2 naar sneller
                maxVermogen: LT2 * 0.90, // Sneller dan LT2 (lagere tijd)
                minHartslag: Math.round(LT2_HR),
                maxHartslag: Math.round(HRmax * 0.95),
                beschrijving: 'Tempo training',
                kleur: '#FFEBEE',
                borgMin: 15,
                borgMax: 18
            },
            {
                naam: 'MAXIMAAL',
                minVermogen: LT2 * 0.90, // Sneller dan tempo
                maxVermogen: LT2 * 0.75, // Maximaal snelle tijd
                minHartslag: Math.round(HRmax * 0.95),
                maxHartslag: Math.round(HRmax),
                beschrijving: 'Maximale training',
                kleur: '#FFCDD2',
                borgMin: 18,
                borgMax: 20
            }
        ];
        
        console.log('✅ Zwem zones berekend:', bonamiZwemZones.length, 'zones');
        return bonamiZwemZones.slice(0, aantal);
    }
    
    // Normale Bonami zones berekening voor fiets/loop
    const bonamiZones = [
        {
            naam: 'HERSTEL',
            minVermogen: LT1 * 0.60,
            maxVermogen: LT1 * 0.80,
            minHartslag: Math.round(LT1_HR * 0.75),
            maxHartslag: Math.round(LT1_HR * 0.85),
            beschrijving: 'Herstel en regeneratie',
            kleur: '#E3F2FD',
            borgMin: 6,
            borgMax: 8
        },
        {
            naam: 'LANGE DUUR',
            minVermogen: LT1 * 0.80,
            maxVermogen: LT1 * 0.90,
            minHartslag: Math.round(LT1_HR * 0.85),
            maxHartslag: Math.round(LT1_HR * 0.92),
            beschrijving: 'Lange duur training',
            kleur: '#E8F5E8',
            borgMin: 8,
            borgMax: 10
        },
        {
            naam: 'EXTENSIEF',
            minVermogen: LT1 * 0.90,
            maxVermogen: LT1,
            minHartslag: Math.round(LT1_HR * 0.92),
            maxHartslag: Math.round(LT1_HR),
            beschrijving: 'Extensieve duur training',
            kleur: '#F1F8E9',
            borgMin: 10,
            borgMax: 12
        },
        {
            naam: 'INTENSIEF',
            minVermogen: LT1,
            maxVermogen: LT2,
            minHartslag: Math.round(LT1_HR),
            maxHartslag: Math.round(LT2_HR),
            beschrijving: 'Intensieve duur training',
            kleur: '#FFF3E0',
            borgMin: 12,
            borgMax: 15
        },
        {
            naam: 'TEMPO',
            minVermogen: LT2,
            maxVermogen: LT2 * 1.15,
            minHartslag: Math.round(LT2_HR),
            maxHartslag: Math.round(HRmax * 0.95),
            beschrijving: 'Tempo training',
            kleur: '#FFEBEE',
            borgMin: 15,
            borgMax: 18
        },
        {
            naam: 'MAXIMAAL',
            minVermogen: LT2 * 1.15,
            maxVermogen: LT2 * 1.40,
            minHartslag: Math.round(HRmax * 0.95),
            maxHartslag: Math.round(HRmax),
            beschrijving: 'Maximale training',
            kleur: '#FFCDD2',
            borgMin: 18,
            borgMax: 20
        }
    ];
    
    console.log('✅ Bonami zones berekend voor:', isLooptest ? 'LOOPTEST' : 'FIETSTEST', bonamiZones.length, 'zones');
    
    // Pas aan naar gewenst aantal zones
    if (aantal !== 6) {
        return pasBonamiZonesAan(bonamiZones, aantal);
    }
    
    return bonamiZones;
}

// Hulpfunctie om voorbeeldzones te maken als er geen drempel data is
function createVoorbeeldBonamiZones(aantal, isLooptest = false, isZwemtest = false) {
    console.log('🎯 createVoorbeeldBonamiZones voor:', isZwemtest ? 'ZWEMTEST' : isLooptest ? 'LOOPTEST' : 'FIETSTEST');
    
    let voorbeeldZones;
    
    if (isZwemtest) {
        // 🏊 ZWEMTEST VOORBEELDEN: Gebruik tijden in min/100m (langzaam naar snel)
        voorbeeldZones = [
            { naam: 'HERSTEL', minVermogen: 2.50, maxVermogen: 2.20, minHartslag: 110, maxHartslag: 130, beschrijving: 'Herstel (voorbeeld)', kleur: '#E3F2FD', borgMin: 6, borgMax: 8 },
            { naam: 'LANGE DUUR', minVermogen: 2.20, maxVermogen: 2.00, minHartslag: 131, maxHartslag: 145, beschrijving: 'Lange duur training (voorbeeld)', kleur: '#E8F5E8', borgMin: 8, borgMax: 10 },
            { naam: 'EXTENSIEF', minVermogen: 2.00, maxVermogen: 1.85, minHartslag: 146, maxHartslag: 160, beschrijving: 'Extensieve duur training (voorbeeld)', kleur: '#F1F8E9', borgMin: 10, borgMax: 12 },
            { naam: 'INTENSIEF', minVermogen: 1.85, maxVermogen: 1.65, minHartslag: 161, maxHartslag: 175, beschrijving: 'Intensieve duur training (voorbeeld)', kleur: '#FFF3E0', borgMin: 12, borgMax: 15 },
            { naam: 'TEMPO', minVermogen: 1.65, maxVermogen: 1.50, minHartslag: 176, maxHartslag: 185, beschrijving: 'Tempo training (voorbeeld)', kleur: '#FFEBEE', borgMin: 15, borgMax: 18 },
            { naam: 'MAXIMAAL', minVermogen: 1.50, maxVermogen: 1.30, minHartslag: 186, maxHartslag: 200, beschrijving: 'Maximale training (voorbeeld)', kleur: '#FFCDD2', borgMin: 18, borgMax: 20 }
        ];
    } else if (isLooptest) {
        // 🏃 LOOPTEST VOORBEELDEN: Gebruik snelheden in km/h
        voorbeeldZones = [
            { naam: 'HERSTEL', minVermogen: 8.0, maxVermogen: 10.0, minHartslag: 110, maxHartslag: 130, beschrijving: 'Herstel (voorbeeld)', kleur: '#E3F2FD', borgMin: 6, borgMax: 8 },
            { naam: 'LANGE DUUR', minVermogen: 10.0, maxVermogen: 11.5, minHartslag: 131, maxHartslag: 145, beschrijving: 'Lange duur training (voorbeeld)', kleur: '#E8F5E8', borgMin: 8, borgMax: 10 },
            { naam: 'EXTENSIEF', minVermogen: 11.5, maxVermogen: 13.0, minHartslag: 146, maxHartslag: 160, beschrijving: 'Extensieve duur training (voorbeeld)', kleur: '#F1F8E9', borgMin: 10, borgMax: 12 },
            { naam: 'INTENSIEF', minVermogen: 13.0, maxVermogen: 15.0, minHartslag: 161, maxHartslag: 175, beschrijving: 'Intensieve duur training (voorbeeld)', kleur: '#FFF3E0', borgMin: 12, borgMax: 15 },
            { naam: 'TEMPO', minVermogen: 15.0, maxVermogen: 17.0, minHartslag: 176, maxHartslag: 185, beschrijving: 'Tempo training (voorbeeld)', kleur: '#FFEBEE', borgMin: 15, borgMax: 18 },
            { naam: 'MAXIMAAL', minVermogen: 17.0, maxVermogen: 20.0, minHartslag: 186, maxHartslag: 200, beschrijving: 'Maximale training (voorbeeld)', kleur: '#FFCDD2', borgMin: 18, borgMax: 20 }
        ];
    } else {
        // 🚴 FIETSTEST VOORBEELDEN: Gebruik wattages
        voorbeeldZones = [
            { naam: 'HERSTEL', minVermogen: 120, maxVermogen: 160, minHartslag: 110, maxHartslag: 130, beschrijving: 'Herstel (voorbeeld)', kleur: '#E3F2FD', borgMin: 6, borgMax: 8 },
            { naam: 'LANGE DUUR', minVermogen: 161, maxVermogen: 185, minHartslag: 131, maxHartslag: 145, beschrijving: 'Lange duur training (voorbeeld)', kleur: '#E8F5E8', borgMin: 8, borgMax: 10 },
            { naam: 'EXTENSIEF', minVermogen: 186, maxVermogen: 210, minHartslag: 146, maxHartslag: 160, beschrijving: 'Extensieve duur training (voorbeeld)', kleur: '#F1F8E9', borgMin: 10, borgMax: 12 },
            { naam: 'INTENSIEF', minVermogen: 211, maxVermogen: 250, minHartslag: 161, maxHartslag: 175, beschrijving: 'Intensieve duur training (voorbeeld)', kleur: '#FFF3E0', borgMin: 12, borgMax: 15 },
            { naam: 'TEMPO', minVermogen: 251, maxVermogen: 290, minHartslag: 176, maxHartslag: 185, beschrijving: 'Tempo training (voorbeeld)', kleur: '#FFEBEE', borgMin: 15, borgMax: 18 },
            { naam: 'MAXIMAAL', minVermogen: 291, maxVermogen: 350, minHartslag: 186, maxHartslag: 200, beschrijving: 'Maximale training (voorbeeld)', kleur: '#FFCDD2', borgMin: 18, borgMax: 20 }
        ];
    }
    
    console.log('✅ Voorbeeldzones gemaakt voor:', isZwemtest ? 'ZWEM (min/100m)' : isLooptest ? 'LOOP (km/h)' : 'FIETS (Watt)');
    return voorbeeldZones.slice(0, aantal);
}

// Hulpfunctie om Bonami zones aan te passen naar gewenst aantal
function pasBonamiZonesAan(zones, aantal) {
    if (aantal >= zones.length) return zones;
    
    // Voor nu: simpel de eerste X zones teruggeven
    return zones.slice(0, aantal);
}

// Placeholder functies voor andere methodes
function berekenKarvonenZones(aantal, eenheid) {
    console.log('⚠️ Karvonen zones - placeholder functie');
    const HRmax = parseFloat(document.getElementById('maximale_hartslag_bpm').value) || 190;
    const HRrust = parseFloat(document.getElementById('hartslag_rust_bpm').value) || 60;
    
    // Simpele Karvonen zones (HRR methode)
    const zones = [];
    for (let i = 0; i < aantal; i++) {
        const intensiteit = (i + 1) / aantal;
        const targetHR = HRrust + (intensiteit * (HRmax - HRrust));
        
        zones.push({
            naam: `Zone ${i + 1}`,
            minVermogen: Math.round(100 + (i * 40)),
            maxVermogen: Math.round(140 + (i * 40)),
            minHartslag: Math.round(targetHR - 10),
            maxHartslag: Math.round(targetHR + 10),
            beschrijving: `Karvonen zone ${i + 1}`,
            kleur: '#F5F5F5',
            borgMin: 6 + i,
            borgMax: 8 + i
        });
    }
    
    return zones;
}

function createHandmatigeZones(aantal, eenheid) {
    console.log('🔧 Handmatige zones - gebruik Bonami als basis en maak bewerkbaar');
    console.log('🏃 Testtype voor handmatige zones:', currentTableType);
    
    // Bepaal of het een looptest of zwemtest is
    const isLooptest = currentTableType === 'looptest' || currentTableType === 'veldtest_lopen';
    const isZwemtest = currentTableType === 'veldtest_zwemmen';
    
    // Gebruik Bonami zones als basis voor handmatige aanpassing
    let baseZones = berekenBonamiZones(6, eenheid); // Altijd start met 6 Bonami zones
    
    // Als er minder zones gewenst zijn, neem de eerste X
    if (aantal < baseZones.length) {
        baseZones = baseZones.slice(0, aantal);
    }
    
    // Maak zones bewerkbaar door naam aan te passen
    const handmatigeZones = baseZones.map((zone, index) => ({
        ...zone,
        naam: zone.naam, // Behoud originele Bonami namen
        beschrijving: `${zone.beschrijving} (aanpasbaar)`,
        bewerkbaar: true // Flag voor bewerkbare zones
    }));
    
    // Als er meer zones gewenst zijn dan Bonami basis, voeg extra zones toe
    if (aantal > baseZones.length) {
        for (let i = baseZones.length; i < aantal; i++) {
            let extraZone;
            
            if (isZwemtest) {
                // 🏊 Zwemtest: gebruik zwemtijden (min/100m)
                extraZone = {
                    naam: `Zone ${i + 1}`,
                    minVermogen: 2.5 - (i * 0.2), // Langzaam naar snel
                    maxVermogen: 2.3 - (i * 0.2),
                    minHartslag: 120 + (i * 15),
                    maxHartslag: 135 + (i * 15),
                    beschrijving: `Handmatige zone ${i + 1} (aanpasbaar)`,
                    kleur: '#F8F9FA',
                    borgMin: 6 + i,
                    borgMax: 8 + i,
                    bewerkbaar: true
                };
            } else if (isLooptest) {
                // 🏃 Looptest: gebruik snelheden
                extraZone = {
                    naam: `Zone ${i + 1}`,
                    minVermogen: 8.0 + (i * 2.0),
                    maxVermogen: 10.0 + (i * 2.0),
                    minHartslag: 120 + (i * 15),
                    maxHartslag: 135 + (i * 15),
                    beschrijving: `Handmatige zone ${i + 1} (aanpasbaar)`,
                    kleur: '#F8F9FA',
                    borgMin: 6 + i,
                    borgMax: 8 + i,
                    bewerkbaar: true
                };
            } else {
                // 🚴 Fietstest: gebruik wattages
                extraZone = {
                    naam: `Zone ${i + 1}`,
                    minVermogen: 100 + (i * 50),
                    maxVermogen: 150 + (i * 50),
                    minHartslag: 120 + (i * 15),
                    maxHartslag: 135 + (i * 15),
                    beschrijving: `Handmatige zone ${i + 1} (aanpasbaar)`,
                    kleur: '#F8F9FA',
                    borgMin: 6 + i,
                    borgMax: 8 + i,
                    bewerkbaar: true
                };
            }
            
            handmatigeZones.push(extraZone);
        }
    }
    
    console.log('✅ Handmatige zones voorbereid met Bonami kleuren voor:', isZwemtest ? 'ZWEM' : isLooptest ? 'LOOP' : 'FIETS', handmatigeZones);
    return handmatigeZones;
}

// Functie om zones tabel te genereren
function genereerZonesTabel(zonesData, eenheid) {
    console.log('📊 genereerZonesTabel() met', zonesData.length, 'zones');
    console.log('🏃 Huidige testtype:', currentTableType);
    
    const tabel = document.getElementById('trainingszones-tabel');
    const header = document.getElementById('zones-header');
    const body = document.getElementById('zones-body');
    
    if (!tabel || !header || !body) {
        console.log('❌ Zones tabel elementen niet gevonden');
        return;
    }
    
    // 🏃 DYNAMISCHE EENHEID DETECTIE op basis van testtype
    const isLooptest = currentTableType === 'looptest' || currentTableType === 'veldtest_lopen';
    const isZwemtest = currentTableType === 'veldtest_zwemmen';
    
    let eenheidLabel = 'Watt';
    if (isLooptest) {
        eenheidLabel = 'km/h';
    } else if (isZwemtest) {
        eenheidLabel = 'min/100m';
    }
    
    const isHandmatig = document.getElementById('zones_methode').value === 'handmatig';
    
    console.log('🔍 Eenheid bepaling:', { isLooptest, isZwemtest, eenheidLabel, testType: currentTableType });
    
    // 🏊 Hulpfunctie om minuten naar mm:ss formaat te converteren voor zwemmen
    function formatZwemTijdVoorZones(minuten) {
        if (!isZwemtest) return minuten;
        
        const totalSecondenPer100m = minuten * 60;
        const min = Math.floor(totalSecondenPer100m / 60);
        const sec = Math.round(totalSecondenPer100m % 60);
        return `${min}:${sec.toString().padStart(2, '0')}`;
    }
    
    // Voor looptesten: voeg extra kolom toe voor min/km
    const extraKolomHeaders = isLooptest ? '<th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-200" colspan="2">min/km</th>' : '';
    const extraKolomSubHeaders = isLooptest ? '<th class="px-2 py-2 text-xs text-gray-600 border-r border-gray-200">min</th><th class="px-2 py-2 text-xs text-gray-600 border-r border-gray-200">max</th>' : '';
    
    header.innerHTML = `
        <tr>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-200">Zone</th>
            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-200" colspan="2">Hartslag (bpm)</th>
            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-200" colspan="2">${eenheidLabel}</th>
            ${extraKolomHeaders}
            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Borg</th>
        </tr>
        <tr class="bg-gray-50">
            <th class="px-4 py-2 border-r border-gray-200"></th>
            <th class="px-2 py-2 text-xs text-gray-600 border-r border-gray-200">min</th>
            <th class="px-2 py-2 text-xs text-gray-600 border-r border-gray-200">max</th>
            <th class="px-2 py-2 text-xs text-gray-600 border-r border-gray-200">min</th>
            <th class="px-2 py-2 text-xs text-gray-600 border-r border-gray-200">max</th>
            ${extraKolomSubHeaders}
            <th class="px-2 py-2 text-xs text-gray-600">schaal</th>
        </tr>
    `;
    
    console.log('✅ Header gegenereerd met eenheid:', eenheidLabel);
    
    body.innerHTML = '';
    
    zonesData.forEach((zone, index) => {
        const row = document.createElement('tr');
        row.style.backgroundColor = zone.kleur || '#FFFFFF';
        row.className = 'border-b border-gray-200 hover:bg-opacity-80';
        
        let borgText = '';
        if (zone.borgMin === null) {
            borgText = `> ${zone.borgMax}`;
        } else if (zone.borgMax === null) {
            borgText = `${zone.borgMin} <`;
        } else {
            borgText = `${zone.borgMin} - ${zone.borgMax}`;
        }
        
        // Bereken min/km voor looptesten (60 / snelheid_kmh = min_per_km)
        let extraKolomCellen = '';
        if (isLooptest) {
            // Bereken min/km en converteer naar mm:ss formaat
            const minPerKmMin = zone.maxVermogen > 0 ? (60 / zone.maxVermogen) : 999;
            const maxPerKmMin = zone.minVermogen > 0 ? (60 / zone.minVermogen) : 999;
            
            // Converteer naar mm:ss formaat
            const minMinKmFormatted = formatMinPerKm(minPerKmMin);
            const maxMinKmFormatted = formatMinPerKm(maxPerKmMin);
            
            extraKolomCellen = `
                <td class="px-2 py-3 text-center text-sm border-r border-gray-200">${minMinKmFormatted}</td>
                <td class="px-2 py-3 text-center text-sm border-r border-gray-200">${maxMinKmFormatted}</td>
            `;
        }
        
        // 🔧 HANDMATIGE ZONES: Maak velden bewerkbaar
        let hartslagMinCel, hartslagMaxCel, vermogenMinCel, vermogenMaxCel;
        
        if (isHandmatig) {
            // Bewerkbare inputvelden voor handmatige aanpassing
            hartslagMinCel = `<td class="px-2 py-3 text-center text-sm border-r border-gray-200">
                <input type="number" value="${zone.minHartslag}" 
                       class="w-16 text-center text-sm border border-gray-300 rounded px-1 py-1"
                       onchange="updateHandmatigeZone(${index}, 'minHartslag', this.value)"
                       min="50" max="220">
            </td>`;
            
            hartslagMaxCel = `<td class="px-2 py-3 text-center text-sm border-r border-gray-200">
                <input type="number" value="${zone.maxHartslag}" 
                       class="w-16 text-center text-sm border border-gray-300 rounded px-1 py-1"
                       onchange="updateHandmatigeZone(${index}, 'maxHartslag', this.value)"
                       min="50" max="220">
            </td>`;
            
            if (isZwemtest) {
                // Voor zwemtesten: toon mm:ss formaat maar bewaar decimale waarden intern
                vermogenMinCel = `<td class="px-2 py-3 text-center text-sm border-r border-gray-200">
                    <input type="text" value="${formatZwemTijdVoorZones(zone.minVermogen)}" 
                           class="w-20 text-center text-sm border border-gray-300 rounded px-1 py-1"
                           onchange="updateHandmatigeZone(${index}, 'minVermogen', this.value)"
                           placeholder="mm:ss">
                </td>`;
                
                vermogenMaxCel = `<td class="px-2 py-3 text-center text-sm border-r border-gray-200">
                    <input type="text" value="${formatZwemTijdVoorZones(zone.maxVermogen)}" 
                           class="w-20 text-center text-sm border border-gray-300 rounded px-1 py-1"
                           onchange="updateHandmatigeZone(${index}, 'maxVermogen', this.value)"
                           placeholder="mm:ss">
                </td>`;
            } else {
                vermogenMinCel = `<td class="px-2 py-3 text-center text-sm border-r border-gray-200">
                    <input type="number" value="${isLooptest ? zone.minVermogen.toFixed(1) : Math.round(zone.minVermogen)}" 
                           class="w-16 text-center text-sm border border-gray-300 rounded px-1 py-1"
                           onchange="updateHandmatigeZone(${index}, 'minVermogen', this.value)"
                           min="0" step="${isLooptest ? '0.1' : '1'}">
                </td>`;
                
                vermogenMaxCel = `<td class="px-2 py-3 text-center text-sm border-r border-gray-200">
                    <input type="number" value="${isLooptest ? zone.maxVermogen.toFixed(1) : Math.round(zone.maxVermogen)}" 
                           class="w-16 text-center text-sm border border-gray-300 rounded px-1 py-1"
                           onchange="updateHandmatigeZone(${index}, 'maxVermogen', this.value)"
                           min="0" step="${isLooptest ? '0.1' : '1'}">
                </td>`;
            }
        } else {
            // Normale statische cellen voor andere methodes
            hartslagMinCel = `<td class="px-2 py-3 text-center text-sm border-r border-gray-200">${zone.minHartslag}</td>`;
            hartslagMaxCel = `<td class="px-2 py-3 text-center text-sm border-r border-gray-200">${zone.maxHartslag}</td>`;
            
            if (isZwemtest) {
                // Voor zwemtesten: toon mm:ss formaat
                vermogenMinCel = `<td class="px-2 py-3 text-center text-sm border-r border-gray-200">${formatZwemTijdVoorZones(zone.minVermogen)}</td>`;
                vermogenMaxCel = `<td class="px-2 py-3 text-center text-sm border-r border-gray-200">${formatZwemTijdVoorZones(zone.maxVermogen)}</td>`;
            } else {
                vermogenMinCel = `<td class="px-2 py-3 text-center text-sm border-r border-gray-200">${isLooptest ? zone.minVermogen.toFixed(1) : Math.round(zone.minVermogen)}</td>`;
                vermogenMaxCel = `<td class="px-2 py-3 text-center text-sm border-r border-gray-200">${isLooptest ? zone.maxVermogen.toFixed(1) : Math.round(zone.maxVermogen)}</td>`;
            }
        }
        
        row.innerHTML = `
            <td class="px-4 py-3 border-r border-gray-200">
                <div class="font-bold text-sm text-gray-900">${zone.naam}</div>
                <div class="text-xs text-gray-600 mt-1">${zone.beschrijving}</div>
            </td>
            ${hartslagMinCel}
            ${hartslagMaxCel}
            ${vermogenMinCel}
            ${vermogenMaxCel}
            ${extraKolomCellen}
            <td class="px-2 py-3 text-center text-sm">${borgText}</td>
        `;
        
        body.appendChild(row);
    });
    
    // 💡 Update tip tekst op basis van methode
    const tipElement = document.getElementById('zones-tip-text');
    if (tipElement) {
        if (isHandmatig) {
            tipElement.innerHTML = '🔧 <strong>Handmatig:</strong> Klik in de velden om waarden aan te passen. Wijzigingen worden automatisch opgeslagen.';
        } else {
            tipElement.innerHTML = '💡 <strong>Tip:</strong> Deze zones zijn automatisch berekend. Bij \'Handmatig\' kun je waarden aanpassen.';
        }
    }
    
    console.log('✅ Zones tabel succesvol gegenereerd' + (isHandmatig ? ' (BEWERKBAAR)' : '') + (isZwemtest ? ' (ZWEM mm:ss FORMAAT)' : ''));
}

// Error functie
function toonZonesError(bericht) {
    const container = document.getElementById('trainingszones-container');
    container.style.display = 'block';
    container.innerHTML = `
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex">
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">
                        Kan trainingszones niet berekenen
                    </h3>
                    <div class="mt-2 text-sm text-red-700">
                        <p>${bericht}</p>
                    </div>
                </div>
            </div>
        </div>
    `;
}
</script>
@endsection
