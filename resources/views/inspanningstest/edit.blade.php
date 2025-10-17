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
            <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-6 mt-0">Inspanningstest Bewerken — {{ $klant->naam }}</h1>
        </div>
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <form method="POST" action="{{ route('inspanningstest.update', ['klant' => $klant->id, 'test' => $inspanningstest->id]) }}" id="inspanningstest-form">
                    @csrf
                    @method('PUT')
                    
                    <!-- Basis informatie -->
                    <h3 class="text-xl font-bold mb-4">Test Informatie</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="mb-4">
                            <label for="datum" class="block text-sm font-medium text-gray-700 mb-2">Testdatum</label>
                            <input type="date" 
                                   name="testdatum" 
                                   id="datum" 
                                   value="{{ old('testdatum', $inspanningstest->datum ? $inspanningstest->datum->format('Y-m-d') : now()->format('Y-m-d')) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                   required>
                            <p class="text-xs text-gray-500 mt-1">Datum aanpasbaar indien nodig</p>
                        </div>

                        <div class="mb-4">
                            <label for="testtype" class="block text-sm font-medium text-gray-700 mb-2">Testtype</label>
                            <select name="testtype" 
                                    id="testtype"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    required>
                                <option value="">Selecteer testtype</option>
                                <option value="looptest" {{ old('testtype', $inspanningstest->testtype) == 'looptest' ? 'selected' : '' }}>Inspanningstest Lopen</option>
                                <option value="fietstest" {{ old('testtype', $inspanningstest->testtype) == 'fietstest' ? 'selected' : '' }}>Inspanningstest Fietsen</option>
                                <option value="veldtest_lopen" {{ old('testtype', $inspanningstest->testtype) == 'veldtest_lopen' ? 'selected' : '' }}>Veldtest Lopen</option>
                                <option value="veldtest_fietsen" {{ old('testtype', $inspanningstest->testtype) == 'veldtest_fietsen' ? 'selected' : '' }}>Veldtest Fietsen</option>
                                <option value="veldtest_zwemmen" {{ old('testtype', $inspanningstest->testtype) == 'veldtest_zwemmen' ? 'selected' : '' }}>Veldtest Zwemmen</option>
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
                                  placeholder="Bijv. verbetering van uithoudingsvermogen, gewichtsverlies, prestatieoptimalisatie...">{{ old('specifieke_doelstellingen', $inspanningstest->specifieke_doelstellingen) }}</textarea>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="mb-4">
                            <label for="lichaamslengte_cm" class="block text-sm font-medium text-gray-700 mb-2">Lengte (cm)</label>
                            <input type="number" 
                                   step="0.1"
                                   name="lichaamslengte_cm" 
                                   id="lichaamslengte_cm"
                                   value="{{ old('lichaamslengte_cm', $inspanningstest->lichaamslengte_cm ?? $klant->lengte_cm) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div class="mb-4">
                            <label for="lichaamsgewicht_kg" class="block text-sm font-medium text-gray-700 mb-2">Gewicht (kg)</label>
                            <input type="number" 
                                   step="0.1"
                                   name="lichaamsgewicht_kg" 
                                   id="lichaamsgewicht_kg"
                                   value="{{ old('lichaamsgewicht_kg', $inspanningstest->lichaamsgewicht_kg ?? $klant->gewicht_kg) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div class="mb-4">
                            <label for="bmi" class="block text-sm font-medium text-gray-700 mb-2">BMI</label>
                            <input type="number" 
                                   step="0.1"
                                   name="bmi" 
                                   id="bmi"
                                   value="{{ old('bmi', $inspanningstest->bmi) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div class="mb-4">
                            <label for="vetpercentage" class="block text-sm font-medium text-gray-700 mb-2">Vetpercentage (%)</label>
                            <input type="number" 
                                   step="0.1"
                                   name="vetpercentage" 
                                   id="vetpercentage"
                                   value="{{ old('vetpercentage', $inspanningstest->vetpercentage) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div class="mb-4">
                            <label for="hartslag_rust_bpm" class="block text-sm font-medium text-gray-700 mb-2">Hartslag rust (bpm)</label>
                            <input type="number" 
                                   name="hartslag_rust_bpm" 
                                   id="hartslag_rust_bpm"
                                   value="{{ old('hartslag_rust_bpm', $inspanningstest->hartslag_rust_bpm) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div class="mb-4">
                            <label for="maximale_hartslag_bpm" class="block text-sm font-medium text-gray-700 mb-2">Hartslag max (bpm)</label>
                            <input type="number" 
                                   name="maximale_hartslag_bpm" 
                                   id="maximale_hartslag_bpm"
                                   value="{{ old('maximale_hartslag_bpm', $inspanningstest->maximale_hartslag_bpm) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div class="mb-4">
                            <label for="buikomtrek_cm" class="block text-sm font-medium text-gray-700 mb-2">Buikomtrek (cm)</label>
                            <input type="number" 
                                   name="buikomtrek_cm" 
                                   id="buikomtrek_cm"
                                   value="{{ old('buikomtrek_cm', $inspanningstest->buikomtrek_cm) }}"
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
                   value="{{ old('slaapkwaliteit', $inspanningstest->slaapkwaliteit ?? 5) }}"
                   class="w-full slider-bonami"
                   oninput="updateScoreDisplay('slaapkwaliteit')">
            <div class="flex justify-between text-xs text-gray-600 mt-1">
                <span>0 (slecht)</span>
                <span id="slaapkwaliteit_value" class="font-semibold">{{ old('slaapkwaliteit', $inspanningstest->slaapkwaliteit ?? 5) }}</span>
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
                   value="{{ old('eetlust', $inspanningstest->eetlust ?? 5) }}"
                   class="w-full slider-bonami"
                   oninput="updateScoreDisplay('eetlust')">
            <div class="flex justify-between text-xs text-gray-600 mt-1">
                <span>0 (slecht)</span>
                <span id="eetlust_value" class="font-semibold">{{ old('eetlust', $inspanningstest->eetlust ?? 5) }}</span>
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
                   value="{{ old('gevoel_op_training', $inspanningstest->gevoel_op_training ?? 5) }}"
                   class="w-full slider-bonami"
                   oninput="updateScoreDisplay('gevoel_op_training')">
            <div class="flex justify-between text-xs text-gray-600 mt-1">
                <span>0 (slecht)</span>
                <span id="gevoel_op_training_value" class="font-semibold">{{ old('gevoel_op_training', $inspanningstest->gevoel_op_training ?? 5) }}</span>
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
                   value="{{ old('stressniveau', $inspanningstest->stressniveau ?? 5) }}"
                   class="w-full slider-bonami"
                   oninput="updateScoreDisplay('stressniveau')">
            <div class="flex justify-between text-xs text-gray-600 mt-1">
                <span>0 (veel)</span>
                <span id="stressniveau_value" class="font-semibold">{{ old('stressniveau', $inspanningstest->stressniveau ?? 5) }}</span>
                <span>10 (geen)</span>
            </div>
        </div>
    </div>

    <!-- Gemiddelde Score -->
    <div class="bg-blue-50 p-4 rounded-lg mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-2">Gemiddelde Score</label>
        <div class="text-3xl font-bold text-blue-600" id="gemiddelde_display">{{ old('gemiddelde_trainingstatus', $inspanningstest->gemiddelde_trainingstatus ?? 5.0) }}</div>
        <input type="hidden" name="gemiddelde_trainingstatus" id="gemiddelde_trainingstatus" value="{{ old('gemiddelde_trainingstatus', $inspanningstest->gemiddelde_trainingstatus ?? 5.0) }}">
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
                  placeholder="Beschrijf de training van 1 dag voor de test...">{{ old('training_dag_voor_test', $inspanningstest->training_dag_voor_test) }}</textarea>
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
                  placeholder="Beschrijf de training van 2 dagen voor de test...">{{ old('training_2d_voor_test', $inspanningstest->training_2d_voor_test) }}</textarea>
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
                                   value="{{ old('testlocatie', $inspanningstest->testlocatie ?? 'Bonami sportmedisch centrum') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <!-- Protocol dropdown voor veldtest lopen -->
                        <div id="protocol-field-lopen" class="mb-4" style="display: none;">
                            <label for="protocol" class="block text-sm font-medium text-gray-700 mb-2">Protocol</label>
                            <select name="protocol" 
                                    id="protocol"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Selecteer protocol</option>
                                <option value="4 x 2000m en 1 x 600m" {{ old('protocol', $inspanningstest->protocol) == '4 x 2000m en 1 x 600m' ? 'selected' : '' }}>4 x 2000m en 1 x 600m</option>
                                <option value="4 x 1600m en 1 x 600m" {{ old('protocol', $inspanningstest->protocol) == '4 x 1600m en 1 x 600m' ? 'selected' : '' }}>4 x 1600m en 1 x 600m</option>
                                <option value="3 x 1600m en 1 x 600m" {{ old('protocol', $inspanningstest->protocol) == '3 x 1600m en 1 x 600m' ? 'selected' : '' }}>3 x 1600m en 1 x 600m</option>
                                <option value="4 x 1200m en 1 x 600m" {{ old('protocol', $inspanningstest->protocol) == '4 x 1200m en 1 x 600m' ? 'selected' : '' }}>4 x 1200m en 1 x 600m</option>
                                <option value="4 x 800m en 1 x 400m" {{ old('protocol', $inspanningstest->protocol) == '4 x 800m en 1 x 400m' ? 'selected' : '' }}>4 x 800m en 1 x 400m</option>
                            </select>
                        </div>

                        <!-- Protocol dropdown voor veldtest zwemmen -->
                        <div id="protocol-field-zwemmen" class="mb-4" style="display: none;">
                            <label for="protocol_zwemmen" class="block text-sm font-medium text-gray-700 mb-2">Protocol</label>
                            <select name="protocol" 
                                    id="protocol_zwemmen"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Selecteer protocol</option>
                                <option value="4 x 200m" {{ old('protocol', $inspanningstest->protocol) == '4 x 200m' ? 'selected' : '' }}>4 x 200m</option>
                                <option value="5 x 200m" {{ old('protocol', $inspanningstest->protocol) == '5 x 200m' ? 'selected' : '' }}>5 x 200m</option>
                                <option value="3 x 200m en 1 x 400m" {{ old('protocol', $inspanningstest->protocol) == '3 x 200m en 1 x 400m' ? 'selected' : '' }}>3 x 200m en 1 x 400m</option>
                            </select>
                        </div>
                        
                        <!-- Standaard protocol veld 1 -->
                        <div id="standard-protocol-field-1" class="mb-4">
                            <label for="startwattage" class="block text-sm font-medium text-gray-700 mb-2">Start (watt)</label>
                            <input type="number" 
                                   name="startwattage" 
                                   id="startwattage"
                                   value="{{ old('startwattage', $inspanningstest->startwattage ?? 8) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <!-- Standaard protocol veld 2 -->
                        <div id="standard-protocol-field-2" class="mb-4">
                            <label for="stappen_min" class="block text-sm font-medium text-gray-700 mb-2">Stappen (minuten)</label>
                            <input type="number" 
                                   name="stappen_min" 
                                   id="stappen_min"
                                   value="{{ old('stappen_min', $inspanningstest->stappen_min ?? 3) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <!-- Standaard protocol veld 3 -->
                        <div id="standard-protocol-field-3" class="mb-4">
                            <label for="stappen_watt" class="block text-sm font-medium text-gray-700 mb-2">Stappen (watt)</label>
                            <input type="number" 
                                   name="stappen_watt" 
                                   id="stappen_watt"
                                   value="{{ old('stappen_watt', $inspanningstest->stappen_watt ?? 1) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>

                    <!-- Weersomstandigheden veld voor veldtesten -->
                    <div id="weersomstandigheden-field" class="mb-4" style="display: none;">
                        <label for="weersomstandigheden" class="block text-sm font-medium text-gray-700 mb-2">Weersomstandigheden</label>
                        <input type="text" 
                               name="weersomstandigheden" 
                               id="weersomstandigheden"
                               value="{{ old('weersomstandigheden', $inspanningstest->weersomstandigheden) }}"
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
                                <option value="dmax" {{ old('analyse_methode', $inspanningstest->analyse_methode) == 'dmax' ? 'selected' : '' }}>D-max</option>
                                <option value="dmax_modified" {{ old('analyse_methode', $inspanningstest->analyse_methode) == 'dmax_modified' ? 'selected' : '' }}>D-max Modified</option>
                                <option value="lactaat_steady_state" {{ old('analyse_methode', $inspanningstest->analyse_methode) == 'lactaat_steady_state' ? 'selected' : '' }}>Lactaat Steady State</option>
                                <option value="hartslag_deflectie" {{ old('analyse_methode', $inspanningstest->analyse_methode) == 'hartslag_deflectie' ? 'selected' : '' }}>Hartslagdeflectie</option>
                                <option value="handmatig" {{ old('analyse_methode', $inspanningstest->analyse_methode) == 'handmatig' ? 'selected' : '' }}>Handmatig</option>
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
                                       value="{{ old('dmax_modified_threshold', $inspanningstest->dmax_modified_threshold ?? '0.4') }}"
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
                                   value="{{ old('aerobe_drempel_vermogen', $inspanningstest->aerobe_drempel_vermogen) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                   placeholder="Automatisch berekend">
                        </div>

                        <div class="mb-4">
                            <label for="aerobe_drempel_hartslag" class="block text-sm font-medium text-gray-700 mb-2">Aërobe drempel - Hartslag (bpm)</label>
                            <input type="number" 
                                   step="1"
                                   name="aerobe_drempel_hartslag" 
                                   id="aerobe_drempel_hartslag"
                                   value="{{ old('aerobe_drempel_hartslag', $inspanningstest->aerobe_drempel_hartslag) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                   placeholder="Automatisch berekend">
                        </div>

                        <div class="mb-4">
                            <label for="anaerobe_drempel_vermogen" id="label_anaerobe_drempel_vermogen" class="block text-sm font-medium text-gray-700 mb-2">Anaërobe drempel - Vermogen (Watt)</label>
                            <input type="number" 
                                   step="any"
                                   name="anaerobe_drempel_vermogen" 
                                   id="anaerobe_drempel_vermogen"
                                   value="{{ old('anaerobe_drempel_vermogen', $inspanningstest->anaerobe_drempel_vermogen) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                   placeholder="Automatisch berekend">
                        </div>

                        <div class="mb-4">
                            <label for="anaerobe_drempel_hartslag" class="block text-sm font-medium text-gray-700 mb-2">Anaërobe drempel - Hartslag (bpm)</label>
                            <input type="number" 
                                   step="1"
                                   name="anaerobe_drempel_hartslag" 
                                   id="anaerobe_drempel_hartslag"
                                   value="{{ old('anaerobe_drempel_hartslag', $inspanningstest->anaerobe_drempel_hartslag) }}"
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
                                  placeholder="Klik op 'Genereer Complete Analyse' om een uitgebreide AI-analyse te krijgen van alle testparameters, inclusief populatievergelijkingen, prestatieclassificatie, fysiologische interpretatie en specifieke trainingsadvies op basis van je doelstellingen...">{{ old('complete_ai_analyse', $inspanningstest->complete_ai_analyse) }}</textarea>
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
                                <option value="bonami" {{ old('zones_methode', $inspanningstest->zones_methode ?? 'bonami') == 'bonami' ? 'selected' : '' }}>Bonami Drempel Methode (6 zones)</option>
                                <option value="karvonen" {{ old('zones_methode', $inspanningstest->zones_methode) == 'karvonen' ? 'selected' : '' }}>Karvonen (Hartslagreserve)</option>
                                <option value="handmatig" {{ old('zones_methode', $inspanningstest->zones_methode) == 'handmatig' ? 'selected' : '' }}>Handmatig Aanpassen</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="zones_aantal" class="block text-sm font-medium text-gray-700 mb-2">Aantal Zones</label>
                            <select name="zones_aantal" 
                                    id="zones_aantal"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="3" {{ old('zones_aantal', $inspanningstest->zones_aantal) == '3' ? 'selected' : '' }}>3 Zones (Basis)</option>
                                <option value="5" {{ old('zones_aantal', $inspanningstest->zones_aantal) == '5' ? 'selected' : '' }}>5 Zones (Standaard)</option>
                                <option value="6" {{ old('zones_aantal', $inspanningstest->zones_aantal ?? '6') == '6' ? 'selected' : '' }}>6 Zones (Bonami)</option>
                                <option value="7" {{ old('zones_aantal', $inspanningstest->zones_aantal) == '7' ? 'selected' : '' }}>7 Zones (Uitgebreid)</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="zones_eenheid" class="block text-sm font-medium text-gray-700 mb-2">Focus Eenheid</label>
                            <select name="zones_eenheid" 
                                    id="zones_eenheid"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="hartslag" {{ old('zones_eenheid', $inspanningstest->zones_eenheid ?? 'hartslag') == 'hartslag' ? 'selected' : '' }}>Hartslag (bpm)</option>
                                <option value="vermogen" {{ old('zones_eenheid', $inspanningstest->zones_eenheid) == 'vermogen' ? 'selected' : '' }}>Vermogen (Watt)</option>
                                <option value="snelheid" {{ old('zones_eenheid', $inspanningstest->zones_eenheid) == 'snelheid' ? 'selected' : '' }}>Snelheid (km/h)</option>
                                <option value="combinatie" {{ old('zones_eenheid', $inspanningstest->zones_eenheid) == 'combinatie' ? 'selected' : '' }}>Combinatie Alle</option>
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
                                        <!-- Rijen worden dynamisch tegengevoerd -->
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
                    <input type="hidden" name="trainingszones_data" id="trainingszones_data" value="{{ old('trainingszones_data', $inspanningstest->trainingszones_data) }}">


    </div>                    <!-- Sjabloon notificatie - EENVOUDIGE VERSIE -->
                    <div id="sjabloon-notificatie" class="mt-6 mb-6" style="background: #e3f2fd; border: 2px solid #2196f3; padding: 15px; border-radius: 8px;">
                        <strong style="color: #1976d2;">📋 Selecteer een testtype om te zien of er een sjabloon beschikbaar is voor rapportgeneratie.</strong>
                    </div>

                    <!-- Submit buttons -->
                    <div class="mt-8 flex gap-3 justify-start flex-wrap">
                        <div class="flex gap-3">
                            <a href="{{ route('inspanningstest.show', ['klant' => $klant->id, 'test' => $inspanningstest->id]) }}" 
                               class="rounded-full px-6 py-2 text-gray-800 font-bold text-sm flex items-center justify-center hover:opacity-80 transition duration-200" 
                               style="background-color: #c8e1eb;">
                                Annuleren
                            </a>
                            <button type="submit" 
                                    class="rounded-full px-6 py-2 text-gray-800 font-bold text-sm flex items-center justify-center hover:opacity-80 transition duration-200" 
                                    style="background-color: #c8e1eb;">
                                Test Bijwerken
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
            if (typeof updateTrainingszones === 'function') {
                updateTrainingszones();
            } else {
                console.warn('⚠️ updateTrainingszones functie niet gevonden - skip zones update');
            }
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
    
    // Initialiseer protocol fields direct na laden
    updateProtocolFields();
    
    // 🚀 Automatisch Bonami zones laden...
    if (typeof updateTrainingszones === 'function') {
        const currentTesttype = document.getElementById('testtype').value;
        updateTrainingszones(currentTesttype);
    } else {
        console.warn('⚠️ updateTrainingszones functie niet beschikbaar bij laden');
    }
    
    // 🔧 LAAD BESTAANDE TESTRESULTATEN (EDIT MODE)
    @if(isset($inspanningstest) && $inspanningstest->testresultaten)
        setTimeout(() => {
            const bestaandeTestresultaten = @json($inspanningstest->testresultaten);
            let parsed = [];
            
            // Parse als string, anders gebruik direct
            if (typeof bestaandeTestresultaten === 'string' && bestaandeTestresultaten.length > 0) {
                try {
                    parsed = JSON.parse(bestaandeTestresultaten);
                } catch (e) {
                    console.error('❌ Fout bij parsen testresultaten:', e);
                    return;
                }
            } else if (Array.isArray(bestaandeTestresultaten)) {
                parsed = bestaandeTestresultaten;
            }
            
            console.log('📊 Bestaande testresultaten gevonden:', parsed);
            
            if (parsed && parsed.length > 0) {
                const tbody = document.getElementById('testresultaten-body');
                if (tbody) {
                    parsed.forEach((resultaat, index) => {
                        const row = tbody.rows[index];
                        if (row) {
                            Object.keys(resultaat).forEach(key => {
                                const input = row.querySelector(`input[name="testresultaten[${index}][${key}]"]`);
                                if (input && resultaat[key] !== null) {
                                    input.value = resultaat[key];
                                }
                            });
                        }
                    });
                    console.log('✅ Testresultaten geladen:', parsed.length, 'rijen');
                }
            }
        }, 1000); // Wacht tot tabel volledig gegenereerd is
    @endif
});

// === COMPLETE AI ANALYSE FUNCTIE (IDENTIEK AAN CREATE) ===
async function generateCompleteAIAnalysis() {
    console.log('🤖 generateCompleteAIAnalysis() gestart');
    
    const btn = document.getElementById('ai-complete-btn');
    const textarea = document.getElementById('complete_ai_analyse');
    
    if (!btn || !textarea) {
        console.log('❌ AI button of textarea niet gevonden');
        return;
    }
    
    // Disable button tijdens generatie
    btn.disabled = true;
    btn.innerHTML = '⏳ AI aan het werk...';
    textarea.value = '⏳ AI analyseert alle testdata...';
    
    try {
        // Verzamel ALLE testparameters
        const testData = {
            testtype: document.getElementById('testtype')?.value || '',
            datum: document.getElementById('datum')?.value || '',
            specifieke_doelstellingen: document.getElementById('specifieke_doelstellingen')?.value || '',
            lichaamslengte_cm: document.getElementById('lichaamslengte_cm')?.value || '',
            lichaamsgewicht_kg: document.getElementById('lichaamsgewicht_kg')?.value || '',
            bmi: document.getElementById('bmi')?.value || '',
            vetpercentage: document.getElementById('vetpercentage')?.value || '',
            hartslag_rust_bpm: document.getElementById('hartslag_rust_bpm')?.value || '',
            maximale_hartslag_bpm: document.getElementById('maximale_hartslag_bpm')?.value || '',
            slaapkwaliteit: document.getElementById('slaapkwaliteit')?.value || '',
            eetlust: document.getElementById('eetlust')?.value || '',
            gevoel_op_training: document.getElementById('gevoel_op_training')?.value || '',
            stressniveau: document.getElementById('stressniveau')?.value || '',
            gemiddelde_trainingstatus: document.getElementById('gemiddelde_trainingstatus')?.value || '',
            testlocatie: document.getElementById('testlocatie')?.value || '',
            startwattage: document.getElementById('startwattage')?.value || '',
            stappen_min: document.getElementById('stappen_min')?.value || '',
            stappen_watt: document.getElementById('stappen_watt')?.value || '',
            aerobe_drempel_vermogen: document.getElementById('aerobe_drempel_vermogen')?.value || '',
            aerobe_drempel_hartslag: document.getElementById('aerobe_drempel_hartslag')?.value || '',
            anaerobe_drempel_vermogen: document.getElementById('anaerobe_drempel_vermogen')?.value || '',
            anaerobe_drempel_hartslag: document.getElementById('anaerobe_drempel_hartslag')?.value || '',
            analyse_methode: document.getElementById('analyse_methode')?.value || ''
        };
        
        console.log('📊 Test data verzameld:', testData);
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const response = await fetch('{{ route("inspanningstest.ai-complete-analysis") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify(testData)
        });
        
        console.log('📡 API response status:', response.status);
        
        if (!response.ok) {
            const errorText = await response.text();
            throw new Error(`API fout: ${response.status} - ${errorText}`);
        }
        
        const result = await response.json();
        console.log('✅ AI analyse ontvangen:', result);
        
        // 🔧 KRITIEKE FIX: API geeft 'analysis' terug (Engels), niet 'analyse' (Nederlands)
        const analyseText = result.analyse || result.analysis;
        
        if (result.success && analyseText) {
            textarea.value = analyseText;
            console.log('✅ Complete AI analyse succesvol gegenereerd');
        } else {
            throw new Error(result.message || 'Onbekende fout');
        }
        
    } catch (error) {
        console.error('❌ AI analyse fout:', error);
        textarea.value = `❌ Fout bij genereren AI analyse:\n\n${error.message}\n\nProbeer het opnieuw of vul handmatig in.`;
    } finally {
        btn.disabled = false;
        btn.innerHTML = '🤖 Genereer Complete Analyse';
    }
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

// Bonami specifieke zones berekening
function berekenBonamiZones(aantal, eenheid) {
    console.log('🚀 berekenBonamiZones() - aantal:', aantal, 'eenheid:', eenheid);
    console.log('🏃 Huidige testtype voor zones:', currentTableType);
    
    const LT1 = parseFloat(document.getElementById('aerobe_drempel_vermogen').value) || 0;
    const LT2 = parseFloat(document.getElementById('anaerobe_drempel_vermogen').value) || 0;
    const LT1_HR = parseFloat(document.getElementById('aerobe_drempel_hartslag').value) || 0;
    const LT2_HR = parseFloat(document.getElementById('anaerobe_drempel_hartslag').value) || 0;
    const HRmax = parseFloat(document.getElementById('maximale_hartslag_bpm').value) || 190;
    
    console.log('📊 Drempel waarden:', { LT1, LT2, LT1_HR, LT2_HR, HRmax });
    
    const isLooptest = currentTableType === 'looptest' || currentTableType === 'veldtest_lopen';
    const isZwemtest = currentTableType === 'veldtest_zwemmen';
    
    if (LT1 === 0 || LT2 === 0) {
        console.log('⚠️ Geen drempel data - genereer voorbeeldzones voor:', isZwemtest ? 'ZWEM' : isLooptest ? 'LOOP' : 'FIETS');
        return createVoorbeeldBonamiZones(aantal, isLooptest, isZwemtest);
    }
    
    if (isZwemtest) {
        console.log('🏊 ZWEM ZONES BEREKENING met LT1:', LT1, 'LT2:', LT2);
        
        const bonamiZwemZones = [
            { naam: 'HERSTEL', minVermogen: LT1 * 1.25, maxVermogen: LT1 * 1.10, minHartslag: Math.round(LT1_HR * 0.75), maxHartslag: Math.round(LT1_HR * 0.85), beschrijving: 'Herstel en regeneratie', kleur: '#E3F2FD', borgMin: 6, borgMax: 8 },
            { naam: 'LANGE DUUR', minVermogen: LT1 * 1.10, maxVermogen: LT1 * 1.05, minHartslag: Math.round(LT1_HR * 0.85), maxHartslag: Math.round(LT1_HR * 0.92), beschrijving: 'Lange duur training', kleur: '#E8F5E8', borgMin: 8, borgMax: 10 },
            { naam: 'EXTENSIEF', minVermogen: LT1 * 1.05, maxVermogen: LT1, minHartslag: Math.round(LT1_HR * 0.92), maxHartslag: Math.round(LT1_HR), beschrijving: 'Extensieve duur training', kleur: '#F1F8E9', borgMin: 10, borgMax: 12 },
            { naam: 'INTENSIEF', minVermogen: LT1, maxVermogen: LT2, minHartslag: Math.round(LT1_HR), maxHartslag: Math.round(LT2_HR), beschrijving: 'Intensieve duur training', kleur: '#FFF3E0', borgMin: 12, borgMax: 15 },
            { naam: 'TEMPO', minVermogen: LT2, maxVermogen: LT2 * 0.90, minHartslag: Math.round(LT2_HR), maxHartslag: Math.round(HRmax * 0.95), beschrijving: 'Tempo training', kleur: '#FFEBEE', borgMin: 15, borgMax: 18 },
            { naam: 'MAXIMAAL', minVermogen: LT2 * 0.90, maxVermogen: LT2 * 0.75, minHartslag: Math.round(HRmax * 0.95), maxHartslag: Math.round(HRmax), beschrijving: 'Maximale training', kleur: '#FFCDD2', borgMin: 18, borgMax: 20 }
        ];
        
        console.log('✅ Zwem zones berekend:', bonamiZwemZones.length, 'zones');
        return bonamiZwemZones.slice(0, aantal);
    }
    
    const bonamiZones = [
        { naam: 'HERSTEL', minVermogen: LT1 * 0.60, maxVermogen: LT1 * 0.80, minHartslag: Math.round(LT1_HR * 0.75), maxHartslag: Math.round(LT1_HR * 0.85), beschrijving: 'Herstel en regeneratie', kleur: '#E3F2FD', borgMin: 6, borgMax: 8 },
        { naam: 'LANGE DUUR', minVermogen: LT1 * 0.80, maxVermogen: LT1 * 0.90, minHartslag: Math.round(LT1_HR * 0.85), maxHartslag: Math.round(LT1_HR * 0.92), beschrijving: 'Lange duur training', kleur: '#E8F5E8', borgMin: 8, borgMax: 10 },
        { naam: 'EXTENSIEF', minVermogen: LT1 * 0.90, maxVermogen: LT1, minHartslag: Math.round(LT1_HR * 0.92), maxHartslag: Math.round(LT1_HR), beschrijving: 'Extensieve duur training', kleur: '#F1F8E9', borgMin: 10, borgMax: 12 },
        { naam: 'INTENSIEF', minVermogen: LT1, maxVermogen: LT2, minHartslag: Math.round(LT1_HR), maxHartslag: Math.round(LT2_HR), beschrijving: 'Intensieve duur training', kleur: '#FFF3E0', borgMin: 12, borgMax: 15 },
        { naam: 'TEMPO', minVermogen: LT2, maxVermogen: LT2 * 1.15, minHartslag: Math.round(LT2_HR), maxHartslag: Math.round(HRmax * 0.95), beschrijving: 'Tempo training', kleur: '#FFEBEE', borgMin: 15, borgMax: 18 },
        { naam: 'MAXIMAAL', minVermogen: LT2 * 1.15, maxVermogen: LT2 * 1.40, minHartslag: Math.round(HRmax * 0.95), maxHartslag: Math.round(HRmax), beschrijving: 'Maximale training', kleur: '#FFCDD2', borgMin: 18, borgMax: 20 }
    ];
    
    console.log('✅ Bonami zones berekend voor:', isLooptest ? 'LOOPTEST' : 'FIETSTEST', bonamiZones.length, 'zones');
    
    if (aantal !== 6) {
        return pasBonamiZonesAan(bonamiZones, aantal);
    }
    
    return bonamiZones;
}

// Hulpfunctie om voorbeeldzones te maken
function createVoorbeeldBonamiZones(aantal, isLooptest = false, isZwemtest = false) {
    console.log('🎯 createVoorbeeldBonamiZones voor:', isZwemtest ? 'ZWEMTEST' : isLooptest ? 'LOOPTEST' : 'FIETSTEST');
    
    let voorbeeldZones;
    
    if (isZwemtest) {
        voorbeeldZones = [
            { naam: 'HERSTEL', minVermogen: 2.50, maxVermogen: 2.20, minHartslag: 110, maxHartslag: 130, beschrijving: 'Herstel (voorbeeld)', kleur: '#E3F2FD', borgMin: 6, borgMax: 8 },
            { naam: 'LANGE DUUR', minVermogen: 2.20, maxVermogen: 2.00, minHartslag: 131, maxHartslag: 145, beschrijving: 'Lange duur training (voorbeeld)', kleur: '#E8F5E8', borgMin: 8, borgMax: 10 },
            { naam: 'EXTENSIEF', minVermogen: 2.00, maxVermogen: 1.85, minHartslag: 146, maxHartslag: 160, beschrijving: 'Extensieve duur training (voorbeeld)', kleur: '#F1F8E9', borgMin: 10, borgMax: 12 },
            { naam: 'INTENSIEF', minVermogen: 1.85, maxVermogen: 1.65, minHartslag: 161, maxHartslag: 175, beschrijving: 'Intensieve duur training (voorbeeld)', kleur: '#FFF3E0', borgMin: 12, borgMax: 15 },
            { naam: 'TEMPO', minVermogen: 1.65, maxVermogen: 1.50, minHartslag: 176, maxHartslag: 185, beschrijving: 'Tempo training (voorbeeld)', kleur: '#FFEBEE', borgMin: 15, borgMax: 18 },
            { naam: 'MAXIMAAL', minVermogen: 1.50, maxVermogen: 1.30, minHartslag: 186, maxHartslag: 200, beschrijving: 'Maximale training (voorbeeld)', kleur: '#FFCDD2', borgMin: 18, borgMax: 20 }
        ];
    } else if (isLooptest) {
        voorbeeldZones = [
            { naam: 'HERSTEL', minVermogen: 8.0, maxVermogen: 10.0, minHartslag: 110, maxHartslag: 130, beschrijving: 'Herstel (voorbeeld)', kleur: '#E3F2FD', borgMin: 6, borgMax: 8 },
            { naam: 'LANGE DUUR', minVermogen: 10.0, maxVermogen: 11.5, minHartslag: 131, maxHartslag: 145, beschrijving: 'Lange duur training (voorbeeld)', kleur: '#E8F5E8', borgMin: 8, borgMax: 10 },
            { naam: 'EXTENSIEF', minVermogen: 11.5, maxVermogen: 13.0, minHartslag: 146, maxHartslag: 160, beschrijving: 'Extensieve duur training (voorbeeld)', kleur: '#F1F8E9', borgMin: 10, borgMax: 12 },
            { naam: 'INTENSIEF', minVermogen: 13.0, maxVermogen: 15.0, minHartslag: 161, maxHartslag: 175, beschrijving: 'Intensieve duur training (voorbeeld)', kleur: '#FFF3E0', borgMin: 12, borgMax: 15 },
            { naam: 'TEMPO', minVermogen: 15.0, maxVermogen: 17.0, minHartslag: 176, maxHartslag: 185, beschrijving: 'Tempo training (voorbeeld)', kleur: '#FFEBEE', borgMin: 15, borgMax: 18 },
            { naam: 'MAXIMAAL', minVermogen: 17.0, maxVermogen: 20.0, minHartslag: 186, maxHartslag: 200, beschrijving: 'Maximale training (voorbeeld)', kleur: '#FFCDD2', borgMin: 18, borgMax: 20 }
        ];
    } else {
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

function pasBonamiZonesAan(zones, aantal) {
    if (aantal >= zones.length) return zones;
    return zones.slice(0, aantal);
}

function berekenKarvonenZones(aantal, eenheid) {
    console.log('⚠️ Karvonen zones - placeholder functie');
    const HRmax = parseFloat(document.getElementById('maximale_hartslag_bpm').value) || 190;
    const HRrust = parseFloat(document.getElementById('hartslag_rust_bpm').value) || 60;
    
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
    console.log('🔧 Handmatige zones - gebruik Bonami als basis');
    console.log('🏃 Testtype voor handmatige zones:', currentTableType);
    
    const isLooptest = currentTableType === 'looptest' || currentTableType === 'veldtest_lopen';
    const isZwemtest = currentTableType === 'veldtest_zwemmen';
    
    let baseZones = berekenBonamiZones(6, eenheid);
    
    if (aantal < baseZones.length) {
        baseZones = baseZones.slice(0, aantal);
    }
    
    const handmatigeZones = baseZones.map((zone, index) => ({
        ...zone,
        naam: zone.naam,
        beschrijving: `${zone.beschrijving} (aanpasbaar)`,
        bewerkbaar: true
    }));
    
    if (aantal > baseZones.length) {
        for (let i = baseZones.length; i < aantal; i++) {
            let extraZone;
            
            if (isZwemtest) {
                extraZone = {
                    naam: `Zone ${i + 1}`,
                    minVermogen: 2.5 - (i * 0.2),
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
    
    console.log('✅ Handmatige zones voorbereid');
    return handmatigeZones;
}

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
    
    function formatZwemTijdVoorZones(minuten) {
        if (!isZwemtest) return minuten;
        
        const totalSecondenPer100m = minuten * 60;
        const min = Math.floor(totalSecondenPer100m / 60);
        const sec = Math.round(totalSecondenPer100m % 60);
        return `${min}:${sec.toString().padStart(2, '0')}`;
    }
    
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
        
        let extraKolomCellen = '';
        if (isLooptest) {
            const minPerKmMin = zone.maxVermogen > 0 ? (60 / zone.maxVermogen) : 999;
            const maxPerKmMin = zone.minVermogen > 0 ? (60 / zone.minVermogen) : 999;
            
            const minMinKmFormatted = formatMinPerKm(minPerKmMin);
            const maxMinKmFormatted = formatMinPerKm(maxPerKmMin);
            
            extraKolomCellen = `
                <td class="px-2 py-3 text-center text-sm border-r border-gray-200">${minMinKmFormatted}</td>
                <td class="px-2 py-3 text-center text-sm border-r border-gray-200">${maxMinKmFormatted}</td>
            `;
        }
        
        let hartslagMinCel, hartslagMaxCel, vermogenMinCel, vermogenMaxCel;
        
        if (isHandmatig) {
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
            hartslagMinCel = `<td class="px-2 py-3 text-center text-sm border-r border-gray-200">${zone.minHartslag}</td>`;
            hartslagMaxCel = `<td class="px-2 py-3 text-center text-sm border-r border-gray-200">${zone.maxHartslag}</td>`;
            
            if (isZwemtest) {
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
        // Luister naar alle form inputs - INCLUSIEF DYNAMISCHE testresultaten inputs
        const attachToInputs = () => {
            const inputs = this.form.querySelectorAll('input, select, textarea');
            console.log(`📝 Found ${inputs.length} form inputs to monitor`);
            
            inputs.forEach((input) => {
                // Skip als al event listener heeft
                if (input.dataset.autoSaveAttached === 'true') return;
                
                input.addEventListener('input', () => {
                    console.log(`📝 Input changed: ${input.name || input.id || 'unnamed'}`);
                    this.scheduleAutoSave();
                });
                input.addEventListener('change', () => {
                    console.log(`🔄 Change event: ${input.name || input.id || 'unnamed'}`);
                    this.scheduleAutoSave();
                });
                
                // Mark als attached
                input.dataset.autoSaveAttached = 'true';
            });
        };
        
        // Initiële attach
        attachToInputs();
        
        // 🔧 OBSERVER voor dynamische testresultaten inputs
        const tbody = document.getElementById('testresultaten-body');
        if (tbody) {
            const observer = new MutationObserver(() => {
                console.log('🔄 Testresultaten tabel gewijzigd - her-attach event listeners');
                attachToInputs();
            });
            
            observer.observe(tbody, {
                childList: true,
                subtree: true
            });
            
            console.log('👁️ MutationObserver gestart voor testresultaten tabel');
        }
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
            
            // 🔧 SPECIALE BEHANDELING VOOR TESTRESULTATEN ARRAYS
            // Verzamel testresultaten tabel data (indien aanwezig)
            const testresultatenBody = document.getElementById('testresultaten-body');
            if (testresultatenBody) {
                const testRows = testresultatenBody.getElementsByTagName('tr');
                console.log(`📊 Gevonden ${testRows.length} testresultaten rijen`);
                
                for (let i = 0; i < testRows.length; i++) {
                    const rowInputs = testRows[i].getElementsByTagName('input');
                    console.log(`  Rij ${i}: ${rowInputs.length} inputs`);
                    
                    // Voeg alle inputs van deze rij toe aan formData
                    for (let j = 0; j < rowInputs.length; j++) {
                        const input = rowInputs[j];
                        const name = input.name;
                        if (name && input.value !== '') {
                            formData.append(name, input.value);
                            console.log(`    ✅ ${name} = ${input.value}`);
                        }
                    }
                }
            }
            
            // Verzamel ALLE andere form inputs
            const inputs = this.form.querySelectorAll('input:not([type="file"]):not([name="_method"]), select, textarea');
            
            inputs.forEach(input => {
                const name = input.name;
                
                // Skip als geen naam, token, of al verzameld (testresultaten)
                if (!name || name === '_token' || name === 'testtype' || name === 'testdatum') return;
                if (name.startsWith('testresultaten[')) return; // Al verzameld hierboven
                
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
            
            // Voeg extra debug info toe
            console.log('📦 FormData contents:');
            for (let [key, value] of formData.entries()) {
                console.log(`  ${key}: ${value}`);
            }
            
            // Bepaal de juiste URL - FORCE EDIT MODE voor bestaande tests
            const url = this.testId 
                ? `/klanten/${this.klantId}/inspanningstest/${this.testId}/auto-save`
                : `/klanten/${this.klantId}/inspanningstest/auto-save`;
            
            console.log('🌐 Sending to URL:', url);
            console.log('🔍 Mode check:', {
                isEdit: this.isEdit,
                testId: this.testId,
                klantId: this.klantId,
                expectedRoute: this.testId ? 'EDIT (autoSaveEdit)' : 'CREATE (autoSave)'
            });
            
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
