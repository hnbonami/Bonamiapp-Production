@extends('layouts.app')
@section('content')
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
            <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-6 mt-0">Inspanningstest bewerken — {{ $klant->voornaam }} {{ $klant->naam }}</h1>
        </div>
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <form method="POST" action="{{ route('inspanningstest.update', [$klant->id, $test->id]) }}">
                    @csrf
                    @method('PUT')
                    @include('inspanningstest._form', ['submitLabel' => 'Opslaan', 'inspanningstest' => $test])

                    <h3 class="text-xl font-bold mt-6 mb-2">Lichaamsmetingen</h3>
                    <div class="mb-2"><label>Lichaamslengte (cm):</label><input type="number" name="lichaamslengte_cm" class="border rounded w-full p-2" value="{{ old('lichaamslengte_cm', $test->lichaamslengte_cm) }}"></div>
                    <div class="mb-2"><label>Lichaamsgewicht (kg):</label><input type="number" step="0.1" name="lichaamsgewicht_kg" class="border rounded w-full p-2" value="{{ old('lichaamsgewicht_kg', $test->lichaamsgewicht_kg) }}"></div>
                    <div class="mb-2"><label>BMI:</label><input type="number" step="0.1" name="bmi" class="border rounded w-full p-2" value="{{ old('bmi', $test->bmi) }}"></div>
                    <div class="mb-2"><label>Hartslag in rust (bpm):</label><input type="number" name="hartslag_rust_bpm" class="border rounded w-full p-2" value="{{ old('hartslag_rust_bpm', $test->hartslag_rust_bpm) }}"></div>
                    <div class="mb-2"><label>Buikomtrek (cm):</label><input type="number" name="buikomtrek_cm" class="border rounded w-full p-2" value="{{ old('buikomtrek_cm', $test->buikomtrek_cm) }}"></div>

                    <h3 class="text-xl font-bold mt-6 mb-2">Protocol</h3>
                    <div class="mb-2"><label>Startwattage (watt):</label><input type="number" name="startwattage" class="border rounded w-full p-2" value="{{ old('startwattage', $test->startwattage) }}"></div>
                    <div class="mb-2"><label>Stappen (min):</label><input type="number" name="stappen_min" class="border rounded w-full p-2" value="{{ old('stappen_min', $test->stappen_min) }}"></div>

                    <h3 class="text-xl font-bold mt-6 mb-2">Testresultaten (Stappen)</h3>
                    <div id="testresultaten-table" class="mb-4">
                        <table class="w-full border mb-2">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="p-2">Tijd (min)</th>
                                    <th class="p-2">Vermogen (Watt)</th>
                                    <th class="p-2">Snelheid (km/u)</th>
                                    <th class="p-2">Lactaat (mmol/L)</th>
                                    <th class="p-2">Hartslag (bpm)</th>
                                    <th class="p-2">Borg</th>
                                    <th class="p-2"></th>
                                </tr>
                            </thead>
                            <tbody id="testresultaten-body">
                                @foreach($test->testresultaten ?? [] as $i => $row)
                                <tr>
                                    <td><input type="number" name="testresultaten[{{ $i }}][tijd]" class="border rounded p-1 w-full" value="{{ $row['tijd'] ?? '' }}"></td>
                                    <td><input type="number" name="testresultaten[{{ $i }}][vermogen]" class="border rounded p-1 w-full" value="{{ $row['vermogen'] ?? '' }}"></td>
                                    <td><input type="number" name="testresultaten[{{ $i }}][snelheid]" class="border rounded p-1 w-full" value="{{ $row['snelheid'] ?? '' }}"></td>
                                    <td><input type="number" step="0.1" name="testresultaten[{{ $i }}][lactaat]" class="border rounded p-1 w-full" value="{{ $row['lactaat'] ?? '' }}"></td>
                                    <td><input type="number" name="testresultaten[{{ $i }}][hartslag]" class="border rounded p-1 w-full" value="{{ $row['hartslag'] ?? '' }}"></td>
                                    <td><input type="number" name="testresultaten[{{ $i }}][borg]" class="border rounded p-1 w-full" value="{{ $row['borg'] ?? '' }}"></td>
                                    <td></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <button type="button" onclick="addRow()" class="bg-green-500 text-white px-4 py-1 rounded">Rij toevoegen</button>
                    </div>

                    <h3 class="text-xl font-bold mt-6 mb-2">Drempels en Besluiten</h3>
                    <div class="mb-2"><label>Aërobe drempel - Vermogen (Watt):</label><input type="number" name="aerobe_drempel_vermogen" class="border rounded w-full p-2" value="{{ old('aerobe_drempel_vermogen', $test->aerobe_drempel_vermogen) }}"></div>
                    <div class="mb-2"><label>Aërobe drempel - Hartslag (bpm):</label><input type="number" name="aerobe_drempel_hartslag" class="border rounded w-full p-2" value="{{ old('aerobe_drempel_hartslag', $test->aerobe_drempel_hartslag) }}"></div>
                    <div class="mb-2"><label>Anaërobe drempel - Vermogen (Watt):</label><input type="number" name="anaerobe_drempel_vermogen" class="border rounded w-full p-2" value="{{ old('anaerobe_drempel_vermogen', $test->anaerobe_drempel_vermogen) }}"></div>
                    <div class="mb-2"><label>Anaërobe drempel - Hartslag (bpm):</label><input type="number" name="anaerobe_drempel_hartslag" class="border rounded w-full p-2" value="{{ old('anaerobe_drempel_hartslag', $test->anaerobe_drempel_hartslag) }}"></div>
                    <div class="mb-2"><label>Besluit Lichaamssamenstelling:</label><textarea name="besluit_lichaamssamenstelling" class="border rounded w-full p-2">{{ old('besluit_lichaamssamenstelling', $test->besluit_lichaamssamenstelling) }}</textarea></div>
                    <div class="mb-2"><label>Advies Aërobe Drempel:</label><textarea name="advies_aerobe_drempel" class="border rounded w-full p-2">{{ old('advies_aerobe_drempel', $test->advies_aerobe_drempel) }}</textarea></div>
                    <div class="mb-2"><label>Advies Anaërobe Drempel:</label><textarea name="advies_anaerobe_drempel" class="border rounded w-full p-2">{{ old('advies_anaerobe_drempel', $test->advies_anaerobe_drempel) }}</textarea></div>

                    <div class="mt-6 flex gap-3 justify-end">
                        @php
                            use App\Helpers\SjabloonHelper;
                            $hasMatchingTemplate = SjabloonHelper::hasMatchingTemplate($test->testtype, 'inspanningstest');
                            $matchingTemplate = SjabloonHelper::findMatchingTemplate($test->testtype, 'inspanningstest');
                        @endphp
                        
                        @if($hasMatchingTemplate)
                            <a href="{{ route('inspanningstest.sjabloon-rapport', ['klant' => $klant->id, 'test' => $test->id]) }}" 
                               target="_blank"
                               class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium text-gray-700 hover:text-gray-900 transition-all duration-200"
                               style="background-color: #c8e1eb; border: 1px solid #a5c9d6;">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                📄 Rapport Preview ({{ $matchingTemplate->naam }})
                            </a>
                        @else
                            <div class="inline-flex items-center px-3 py-2 rounded-lg text-sm font-medium bg-yellow-100 border border-yellow-400 text-yellow-800">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.734-.833-2.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                                Geen sjabloon voor "{{ $test->testtype }}" 
                                <a href="{{ route('sjablonen.create') }}" class="ml-1 underline hover:no-underline">Maak aan</a>
                            </div>
                        @endif
                        
                        <a href="{{ route('klanten.show', $klant->id) }}" class="rounded-full px-4 py-1 bg-gray-100 text-gray-800 font-bold text-sm flex items-center justify-center">Terug</a>
                        <button type="submit" class="rounded-full px-4 py-1 bg-indigo-100 text-indigo-800 font-bold text-sm flex items-center justify-center">Test Opslaan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
let rowCount = {{ count($test->testresultaten ?? []) }};
function addRow() {
    const tbody = document.getElementById('testresultaten-body');
    const row = document.createElement('tr');
    row.innerHTML = `
        <td><input type="number" name="testresultaten[${rowCount}][tijd]" class="border rounded p-1 w-full"></td>
        <td><input type="number" name="testresultaten[${rowCount}][vermogen]" class="border rounded p-1 w-full"></td>
        <td><input type="number" name="testresultaten[${rowCount}][snelheid]" class="border rounded p-1 w-full"></td>
        <td><input type="number" step="0.1" name="testresultaten[${rowCount}][lactaat]" class="border rounded p-1 w-full"></td>
        <td><input type="number" name="testresultaten[${rowCount}][hartslag]" class="border rounded p-1 w-full"></td>
        <td><input type="number" name="testresultaten[${rowCount}][borg]" class="border rounded p-1 w-full"></td>
        <td></td>
    `;
    tbody.appendChild(row);
    rowCount++;
}
</script>
<script>
document.addEventListener('DOMContentLoaded', function(){
    const form = document.querySelector('form[action^="{{ route('inspanningstest.update', [$klant->id, $test->id]) }}"]');
    if (form) {
        form.addEventListener('submit', function(e){
            console.log('Inspanningstest edit form submitting');
            const btn = form.querySelector('button[type=submit]');
            if (btn) btn.disabled = true;
        });
    }
});
</script>
@endsection
