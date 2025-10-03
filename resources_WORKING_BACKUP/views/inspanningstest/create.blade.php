@extends('layouts.app')

@section('content')
<!-- Chart.js library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                                <option value="4 x 1600m en 1 x 600m" {{ old('protocol') == '4 x 1600m en 1 x 600m' ? 'selected' : '' }}>4 x 1600m en 1 x 600m</option>
                                <option value="4 x 2000m en 1 x 600m" {{ old('protocol') == '4 x 2000m en 1 x 600m' ? 'selected' : '' }}>4 x 2000m en 1 x 600m</option>
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
                            <label for="aerobe_drempel_vermogen" class="block text-sm font-medium text-gray-700 mb-2">Aërobe drempel - Vermogen (Watt)</label>
                            <input type="number" 
                                   name="aerobe_drempel_vermogen" 
                                   id="aerobe_drempel_vermogen"
                                   value="{{ old('aerobe_drempel_vermogen') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div class="mb-4">
                            <label for="aerobe_drempel_hartslag" class="block text-sm font-medium text-gray-700 mb-2">Aërobe drempel - Hartslag (bpm)</label>
                            <input type="number" 
                                   name="aerobe_drempel_hartslag" 
                                   id="aerobe_drempel_hartslag"
                                   value="{{ old('aerobe_drempel_hartslag') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div class="mb-4">
                            <label for="anaerobe_drempel_vermogen" class="block text-sm font-medium text-gray-700 mb-2">Anaërobe drempel - Vermogen (Watt)</label>
                            <input type="number" 
                                   name="anaerobe_drempel_vermogen" 
                                   id="anaerobe_drempel_vermogen"
                                   value="{{ old('anaerobe_drempel_vermogen') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div class="mb-4">
                            <label for="anaerobe_drempel_hartslag" class="block text-sm font-medium text-gray-700 mb-2">Anaërobe drempel - Hartslag (bpm)</label>
                            <input type="number" 
                                   name="anaerobe_drempel_hartslag" 
                                   id="anaerobe_drempel_hartslag"
                                   value="{{ old('anaerobe_drempel_hartslag') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="besluit_lichaamssamenstelling" class="block text-sm font-medium text-gray-700 mb-2">Besluit Lichaamssamenstelling</label>
                        <textarea name="besluit_lichaamssamenstelling" 
                                  id="besluit_lichaamssamenstelling"
                                  rows="3"
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                  placeholder="Conclusies over lichaamssamenstelling...">{{ old('besluit_lichaamssamenstelling') }}</textarea>
                    </div>

                    <div class="mb-4">
                        <label for="advies_aerobe_drempel" class="block text-sm font-medium text-gray-700 mb-2">Advies Aërobe Drempel</label>
                        <textarea name="advies_aerobe_drempel" 
                                  id="advies_aerobe_drempel"
                                  rows="3"
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                  placeholder="Trainingsadvies voor aërobe zone...">{{ old('advies_aerobe_drempel') }}</textarea>
                    </div>

                    <div class="mb-4">
                        <label for="advies_anaerobe_drempel" class="block text-sm font-medium text-gray-700 mb-2">Advies Anaërobe Drempel</label>
                        <textarea name="advies_anaerobe_drempel" 
                                  id="advies_anaerobe_drempel"
                                  rows="3"
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                  placeholder="Trainingsadvies voor anaërobe zone...">{{ old('advies_anaerobe_drempel') }}</textarea>
                    </div>

                    <!-- Debug informatie -->
                    <div class="mt-4 p-4 bg-gray-100 rounded" style="display: none;" id="debug-info">
                        <h4 class="font-bold">Debug Info:</h4>
                        <p>Datum: <span id="debug-datum"></span></p>
                        <p>Testtype: <span id="debug-testtype"></span></p>
                    </div>                    <!-- Submit buttons -->
                    <div class="mt-8 flex gap-3 justify-start">
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
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Counter voor nieuwe rijen
let rowCount = 0;
let currentTableType = '';

// Functie om inspanningstest rijen te genereren op basis van protocol
function generateInspanningstestRows(testType) {
    const startValue = parseFloat(document.getElementById('startwattage').value) || 0;
    const stappenMin = parseFloat(document.getElementById('stappen_min').value) || 3;
    const stappenIncrement = parseFloat(document.getElementById('stappen_watt').value) || (testType === 'looptest' ? 1 : 40);
    
    if (startValue <= 0 || stappenIncrement <= 0) {
        return []; // Geen geldige waarden, return lege array
    }
    
    const rows = [];
    const maxStappen = 5; // Altijd precies 5 rijen genereren
    
    for (let i = 0; i < maxStappen; i++) {
        const currentTime = stappenMin * (i + 1);
        let currentValue = startValue + (stappenIncrement * i);
        
        if (testType === 'fietstest') {
            rows.push({
                tijd: currentTime,
                vermogen: currentValue,
                lactaat: '',
                hartslag: '',
                borg: ''
            });
        } else if (testType === 'looptest') {
            rows.push({
                tijd: currentTime,
                snelheid: currentValue.toFixed(1),
                lactaat: '',
                hartslag: '',
                borg: ''
            });
        }
    }
    
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
        headers: ['Afstand (m)', 'Tijd (min)', 'Tijd (sec)', 'Hartslag (bpm)', 'Borg'],
        fields: ['afstand', 'tijd_min', 'tijd_sec', 'hartslag', 'borg'],
        inputTypes: ['number', 'number', 'number', 'number', 'number'],
        steps: ['', '', '', '', '']
    },
    'veldtest_fietsen': { // Veldtest fietsen
        headers: ['Tijd (min)', 'Vermogen (Watt)', 'Lactaat (mmol/L)', 'Hartslag (bpm)', 'Borg'],
        fields: ['tijd', 'vermogen', 'lactaat', 'hartslag', 'borg'],
        inputTypes: ['number', 'number', 'number', 'number', 'number'],
        steps: ['', '', '0.1', '', '']
    },
    'veldtest_zwemmen': { // Veldtest zwemmen
        headers: ['Afstand (m)', 'Tijd (min)', 'Tijd (sec)', 'Hartslag (bpm)', 'Borg'],
        fields: ['afstand', 'tijd_min', 'tijd_sec', 'hartslag', 'borg'],
        inputTypes: ['number', 'number', 'number', 'number', 'number'],
        steps: ['', '', '', '', '']
    }
};

// Protocol voorinstellingen voor veldtest lopen
const veldtestLopenProtocols = {
    '4 x 1600m en 1 x 600m': [
        {afstand: 1600, tijd_min: '', tijd_sec: '', hartslag: '', borg: ''},
        {afstand: 1600, tijd_min: '', tijd_sec: '', hartslag: '', borg: ''},
        {afstand: 1600, tijd_min: '', tijd_sec: '', hartslag: '', borg: ''},
        {afstand: 1600, tijd_min: '', tijd_sec: '', hartslag: '', borg: ''},
        {afstand: 600, tijd_min: '', tijd_sec: '', hartslag: '', borg: ''}
    ],
    '4 x 2000m en 1 x 600m': [
        {afstand: 2000, tijd_min: '', tijd_sec: '', hartslag: '', borg: ''},
        {afstand: 2000, tijd_min: '', tijd_sec: '', hartslag: '', borg: ''},
        {afstand: 2000, tijd_min: '', tijd_sec: '', hartslag: '', borg: ''},
        {afstand: 2000, tijd_min: '', tijd_sec: '', hartslag: '', borg: ''},
        {afstand: 600, tijd_min: '', tijd_sec: '', hartslag: '', borg: ''}
    ]
};

// Protocol voorinstellingen voor veldtest zwemmen
const veldtestZwemmenProtocols = {
    '4 x 200m': [
        {afstand: 200, tijd_min: '', tijd_sec: '', hartslag: '', borg: ''},
        {afstand: 200, tijd_min: '', tijd_sec: '', hartslag: '', borg: ''},
        {afstand: 200, tijd_min: '', tijd_sec: '', hartslag: '', borg: ''},
        {afstand: 200, tijd_min: '', tijd_sec: '', hartslag: '', borg: ''}
    ],
    '5 x 200m': [
        {afstand: 200, tijd_min: '', tijd_sec: '', hartslag: '', borg: ''},
        {afstand: 200, tijd_min: '', tijd_sec: '', hartslag: '', borg: ''},
        {afstand: 200, tijd_min: '', tijd_sec: '', hartslag: '', borg: ''},
        {afstand: 200, tijd_min: '', tijd_sec: '', hartslag: '', borg: ''},
        {afstand: 200, tijd_min: '', tijd_sec: '', hartslag: '', borg: ''}
    ],
    '3 x 200m en 1 x 400m': [
        {afstand: 200, tijd_min: '', tijd_sec: '', hartslag: '', borg: ''},
        {afstand: 200, tijd_min: '', tijd_sec: '', hartslag: '', borg: ''},
        {afstand: 200, tijd_min: '', tijd_sec: '', hartslag: '', borg: ''},
        {afstand: 400, tijd_min: '', tijd_sec: '', hartslag: '', borg: ''}
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

// Functie om tabel te updaten
function updateTable(testType) {
    currentTableType = testType;
    const header = document.getElementById('testresultaten-header');
    const tbody = document.getElementById('testresultaten-body');
    
    // Clear existing content
    header.innerHTML = '';
    tbody.innerHTML = '';
    rowCount = 0;
    
    if (!testType || !tableConfigs[testType]) return;
    
    // Set header
    header.innerHTML = generateTableHeader(testType);
    
    // Voor veldtesten met protocol, voeg vooringevulde rijen toe
    if (testType === '3') {
        const protocolSelect = document.getElementById('protocol');
        const selectedProtocol = protocolSelect ? protocolSelect.value : '';
        if (selectedProtocol && veldtestLopenProtocols[selectedProtocol]) {
            veldtestLopenProtocols[selectedProtocol].forEach(data => {
                tbody.innerHTML += generateTableRow(testType, rowCount, data);
                rowCount++;
            });
            return;
        }
    }
    
    if (testType === '5') {
        const protocolSelect = document.getElementById('protocol_zwemmen');
        const selectedProtocol = protocolSelect ? protocolSelect.value : '';
        if (selectedProtocol && veldtestZwemmenProtocols[selectedProtocol]) {
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

// Functie om nieuwe rij toe te voegen
function addRow() {
    if (!currentTableType) return;
    
    const tbody = document.getElementById('testresultaten-body');
    tbody.innerHTML += generateTableRow(currentTableType, rowCount);
    rowCount++;
}

// Functie om laatste rij te verwijderen
function removeLastRow() {
    const tbody = document.getElementById('testresultaten-body');
    const rows = tbody.getElementsByTagName('tr');
    if (rows.length > 1) {
        tbody.removeChild(rows[rows.length - 1]);
        rowCount--;
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
        
        if (selectedType === 'veldtest_lopen') {
            // Veldtest lopen: toon loop protocol dropdown, verberg standaard velden
            protocolFieldLopen.style.display = 'block';
            weersomstandighedenField.style.display = 'block';
            standardProtocolField1.style.display = 'none';
            standardProtocolField2.style.display = 'none';
            standardProtocolField3.style.display = 'none';
        } else if (selectedType === 'veldtest_zwemmen') {
            // Veldtest zwemmen: toon zwem protocol dropdown, verberg standaard velden
            protocolFieldZwemmen.style.display = 'block';
            weersomstandighedenField.style.display = 'block';
            standardProtocolField1.style.display = 'none';
            standardProtocolField2.style.display = 'none';
            standardProtocolField3.style.display = 'none';
        } else if (selectedType === 'veldtest_fietsen') {
            // Veldtest fietsen: toon standaard velden + weersomstandigheden
            weersomstandighedenField.style.display = 'block';
            startLabel.textContent = 'Start wattage (watt)';
            stappenWattLabel.textContent = 'Stappen (watt)';
            document.getElementById('startwattage').value = 100;
            document.getElementById('stappen_watt').value = 40;
        } else {
            // Andere testen: toon alleen standaard velden
            if (selectedType === 'fietstest') {
                // Fiets testen: wattage velden
                startLabel.textContent = 'Start wattage (watt)';
                stappenWattLabel.textContent = 'Stappen (watt)';
                document.getElementById('startwattage').value = 100;
                document.getElementById('stappen_watt').value = 40;
            } else if (selectedType === 'looptest') {
                // Inspanningstest lopen: snelheid velden
                startLabel.textContent = 'Start snelheid (km/h)';
                stappenWattLabel.textContent = 'Stappen (km/h)';
                document.getElementById('startwattage').value = 8;
                document.getElementById('stappen_watt').value = 1;
            } else {
                // Default: wattage velden
                startLabel.textContent = 'Start (watt)';
                stappenWattLabel.textContent = 'Stappen (watt)';
                document.getElementById('startwattage').value = 100;
                document.getElementById('stappen_watt').value = 40;
            }
        }
    }
    
    // Event listeners voor protocol wijzigingen bij veldtesten
    document.getElementById('protocol').addEventListener('change', function() {
        if (testtypeSelect.value === 'veldtest_lopen') {
            updateTable('veldtest_lopen');
        }
    });
    
    document.getElementById('protocol_zwemmen').addEventListener('change', function() {
        if (testtypeSelect.value === 'veldtest_zwemmen') {
            updateTable('veldtest_zwemmen');
        }
    });
    
    // Event listeners voor protocol veld wijzigingen (inspanningstesten en veldtest fietsen)
    document.getElementById('startwattage').addEventListener('input', function() {
        if (testtypeSelect.value === 'fietstest' || testtypeSelect.value === 'looptest' || testtypeSelect.value === 'veldtest_fietsen') {
            updateTable(testtypeSelect.value);
        }
    });
    
    document.getElementById('stappen_min').addEventListener('input', function() {
        if (testtypeSelect.value === 'fietstest' || testtypeSelect.value === 'looptest' || testtypeSelect.value === 'veldtest_fietsen') {
            updateTable(testtypeSelect.value);
        }
    });
    
    document.getElementById('stappen_watt').addEventListener('input', function() {
        if (testtypeSelect.value === 'fietstest' || testtypeSelect.value === 'looptest' || testtypeSelect.value === 'veldtest_fietsen') {
            updateTable(testtypeSelect.value);
        }
    });
    
    // Event listener voor testtype wijzigingen
    testtypeSelect.addEventListener('change', updateProtocolFields);
    
    // Event listener voor analyse methode
    const analyseMethodeSelect = document.getElementById('analyse_methode');
    analyseMethodeSelect.addEventListener('change', handleAnalyseMethodeChange);
    
    // Initiële update bij het laden van de pagina
    updateProtocolFields();
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
    
    console.log('Analyse methode gewijzigd naar:', selectedMethod);
    console.log('Container gevonden:', !!grafiekContainer);
    console.log('Instructies gevonden:', !!grafiekInstructies);
    
    if (selectedMethod && selectedMethod !== '') {
        grafiekContainer.style.display = 'block';
        grafiekInstructies.style.display = 'block';
        console.log('Container en instructies zichtbaar gemaakt voor methode:', selectedMethod);
        generateChart();
    } else {
        grafiekContainer.style.display = 'none';
        grafiekInstructies.style.display = 'none';
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
        
        console.log('RowData:', rowData);
        
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
    
    // Sorteer op basis van tijd of vermogen/snelheid
    const xField = currentTableType === 'fietstest' ? 'vermogen' : 
                   currentTableType === 'looptest' ? 'snelheid' : 'tijd';
    
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
    
    console.log(`✅ Modified Aërobe drempel: ${aerobePoint[xField].toFixed(1)}W bij ${aerobeThreshold.toFixed(2)}mmol/L`);
    
    // STAP 2: Bereken parabool coëfficiënten voor lactaatcurve
    const xArray = validData.map(d => d[xField]);
    const yArray = validData.map(d => d.lactaat);
    const paraboolCoeff = fitParabola(xArray, yArray);
    
    console.log(`📐 Modified Parabool: y = ${paraboolCoeff.a.toFixed(6)}x² + ${paraboolCoeff.b.toFixed(6)}x + ${paraboolCoeff.c.toFixed(6)}`);
    
    // STAP 3: Bereken hulplijn (van aërobe punt naar laatste punt)
    const lastPoint = validData[validData.length - 1];
    const m = (lastPoint.lactaat - aerobePoint.lactaat) / (lastPoint[xField] - aerobePoint[xField]);
    const bLine = aerobePoint.lactaat - m * aerobePoint[xField];
    
    console.log(`📏 Modified Hulplijn: y = ${m.toFixed(6)}x + ${bLine.toFixed(6)}`);
    console.log(`📏 Van aërobe punt: ${aerobePoint[xField].toFixed(1)}W, ${aerobePoint.lactaat.toFixed(2)}mmol/L`);
    console.log(`📏 Naar laatste punt: ${lastPoint[xField]}W, ${lastPoint.lactaat.toFixed(2)}mmol/L`);
    
    // STAP 4: Bereken D-max punt (maximum afstand parabool tot hulplijn van aërobe naar laatste punt)
    const dmaxX = computeDmaxFromBaseline(xArray, yArray, paraboolCoeff.a, paraboolCoeff.b, paraboolCoeff.c, m, bLine, aerobePoint[xField], lastPoint[xField]);
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

// Lineaire interpolatie helper functie
function interpolateLinear(points, targetX, targetField, xField) {
    points.sort((a, b) => (a[xField] || 0) - (b[xField] || 0));
    
    for (let i = 0; i < points.length - 1; i++) {
        const p1 = points[i];
        const p2 = points[i + 1];
        
        if (p1[xField] <= targetX && p2[xField] >= targetX) {
            const ratio = (targetX - p1[xField]) / (p2[xField] - p1[xField]);
            return p1[targetField] + ratio * (p2[targetField] - p1[targetField]);
        }
    }
    
    return points[0][targetField]; // Fallback
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
    console.log('generateSmoothLactaatCurve aangeroepen met:', lactaatData);
    
    if (!lactaatData || lactaatData.length < 2) {
        console.log('Niet genoeg lactaat data voor curve generatie');
        return lactaatData || [];
    }
    
    // Sorteer data op X-waarde
    const sortedData = [...lactaatData].sort((a, b) => a.x - b.x);
    console.log('Gesorteerde data:', sortedData);
    
    const smoothData = [];
    const minX = sortedData[0].x;
    const maxX = sortedData[sortedData.length - 1].x;
    const steps = 100; // 100 punten voor zeer vloeiende exponentiële curve
    
    console.log('Curve bereik:', minX, 'tot', maxX, 'met', steps, 'stappen');
    
    for (let i = 0; i <= steps; i++) {
        const x = minX + (maxX - minX) * (i / steps);
        
        // Gebruik exponentiële interpolatie voor realistische lactaatcurve
        let y = interpolateExponentialForCurve(sortedData, x);
        
        // Debug eerste paar punten
        if (i < 5) {
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
                   currentTableType === 'looptest' ? 'snelheid' : 'tijd';
    const xLabel = currentTableType === 'fietstest' ? 'Vermogen (Watt)' : 
                   currentTableType === 'looptest' ? 'Snelheid (km/h)' : 'Tijd (min)';
    
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
        // Gebruik de aërobe punt berekening uit thresholds
        const aerobePoint = thresholds.aerobe;
        const lastDataPoint = rawLactaatData[rawLactaatData.length - 1];
        
        // Hulplijn tussen aërobe punt en laatste datapunt
        datasets.push({
            label: 'D-max Modified Hulplijn (Aërobe → Laatste)',
            data: [
                {x: aerobePoint[xField], y: aerobePoint.lactaat},
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
        // Bereken hulplijn Y-waarde op dmaxX positie (lineaire interpolatie tussen aërobe en laatste punt)
        const m = (lastDataPoint.y - aerobePoint.lactaat) / (lastDataPoint.x - aerobePoint[xField]);
        const b = aerobePoint.lactaat - m * aerobePoint[xField];
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
                        stepSize: currentTableType === 'fietstest' ? 20 : 1,
                        callback: function(value) {
                            return Math.round(value);
                        }
                    },
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
        
        console.log('Aërobe drempel:', aerobeVermogen, 'W,', aerobeHartslag, 'bpm');
        
        if (aerobeVermogen) document.getElementById('aerobe_drempel_vermogen').value = parseFloat(aerobeVermogen.toFixed(1)); // 1 decimaal
        if (aerobeHartslag) document.getElementById('aerobe_drempel_hartslag').value = Math.round(aerobeHartslag);
    }
    
    if (thresholds.anaerobe) {
        const anaerobeVermogen = thresholds.anaerobe[xField];
        const anaerobeHartslag = thresholds.anaerobe.hartslag;
        
        console.log('Anaërobe drempel:', anaerobeVermogen, 'W,', anaerobeHartslag, 'bpm');
        
        if (anaerobeVermogen) document.getElementById('anaerobe_drempel_vermogen').value = parseFloat(anaerobeVermogen.toFixed(1)); // 1 decimaal
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
</script>
@endsection
