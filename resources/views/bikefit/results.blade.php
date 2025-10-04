@extends('layouts.app')

@section('content')
<!-- CSS om SVG tekst zwart en vet te maken -->
<style>
svg text {
    fill: #000000 !important;
    font-weight: bold !important;
    font-size: 14px !important;
}

svg tspan {
    fill: #000000 !important;
    font-weight: bold !important;
}
</style>

<!-- JavaScript om SVG tekst te forceren naar zwart -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        const svgTexts = document.querySelectorAll('svg text, svg tspan');
        svgTexts.forEach(function(textElement) {
            textElement.style.fill = '#000000';
            textElement.style.fontWeight = 'bold';
            textElement.setAttribute('fill', '#000000');
        });
        console.log('SVG tekst aangepast: ' + svgTexts.length + ' elementen');
    }, 1500);
});
</script>

<div class="w-full bg-white p-8 rounded shadow">
    <h1 class="text-2xl font-bold mb-4">Bikefit berekende resultaten</h1>

    <!-- Prognose zitpositie -->
    <div class="bg-white rounded shadow p-8 mb-8">
        <h2 class="text-xl font-bold text-center mb-6">Prognose zitpositie</h2>
        <div class="flex flex-col md:flex-row gap-8 items-center">
            @php
                $type = strtolower(trim($bikefit->type_fitting ?? ''));
                if (in_array($type, ['mtb', 'mountainbike'])) {
                    $img = '/images/bikefit-schema-mtb.png';
                } elseif (in_array($type, ['tijdritfiets', 'tt'])) {
                    $img = '/images/bikefit-schema-tt.png';
                } else {
                    $img = '/images/bikefit-schema.png';
                }
            @endphp
            <img src="{{ $img }}" alt="Bikefit schema" class="w-full md:w-1/2 max-w-md mx-auto">
            <div class="w-full md:w-1/2">
                <table class="w-full text-sm mb-4">
                    <tbody>
                        <tr>
                            <td class="font-bold text-blue-700">A</td>
                            <td>Zadelhoogte</td>
                            <td>
                                <input type="number" step="0.1" name="zadelhoogte" value="{{ old('zadelhoogte', $results['zadelhoogte'] ?? '') }}" class="px-2 py-1 w-24 text-right border border-gray-300 rounded-md" form="bikefit-report-form"> cm
                            </td>
                        </tr>
                        <tr>
                            <td class="font-bold text-blue-700">B</td>
                            <td>Zadelterugstand</td>
                            <td>
                                <input type="number" step="0.1" name="zadelterugstand" value="{{ $results['zadelterugstand'] ?? '' }}" class="px-2 py-1 w-24 text-right border border-gray-300 rounded-md" form="bikefit-report-form"> cm
                            </td>
                        </tr>
                        <tr>
                            <td class="font-bold text-blue-700">C</td>
                            <td>Zadelterugstand (top zadel)</td>
                            <td>
                                <input type="number" step="0.1" name="zadelterugstand_top" value="{{ $results['zadelterugstand_top'] ?? '' }}" class="px-2 py-1 w-24 text-right border border-gray-300 rounded-md" form="bikefit-report-form"> cm
                            </td>
                        </tr>
                        <tr>
                            <td class="font-bold text-blue-700">D</td>
                            <td>Horizontale reach</td>
                            <td>
                                <input type="number" step="0.1" name="reach" value="{{ old('reach', $results['reach'] ?? '') }}" class="px-2 py-1 w-24 text-right border border-gray-300 rounded-md" form="bikefit-report-form"> mm
                            </td>
                        </tr>
                        <tr>
                            <td class="font-bold text-blue-700">E</td>
                            <td>Reach</td>
                            <td>
                                <input type="number" step="0.1" name="directe_reach" value="{{ $results['directe_reach'] ?? '' }}" class="px-2 py-1 w-24 text-right border border-gray-300 rounded-md" form="bikefit-report-form"> mm
                            </td>
                        </tr>
                        <tr>
                            <td class="font-bold text-blue-700">F</td>
                            <td>Drop</td>
                            <td>
                                <input type="number" step="0.1" name="drop" value="{{ old('drop', $results['drop'] ?? '') }}" class="px-2 py-1 w-24 text-right border border-gray-300 rounded-md" form="bikefit-report-form"> mm
                            </td>
                        </tr>
                        <tr>
                            <td class="font-bold text-blue-700">G</td>
                            <td>Cranklengte</td>
                            <td>
                                <input type="number" step="0.1" name="cranklengte" value="{{ old('cranklengte', $results['cranklengte'] ?? '') }}" class="px-2 py-1 w-24 text-right border border-gray-300 rounded-md" form="bikefit-report-form"> mm
                            </td>
                        </tr>
                        <tr>
                            <td class="font-bold text-blue-700">H</td>
                            <td>Stuurbreedte</td>
                            <td>
                                <input type="number" step="0.1" name="stuurbreedte" value="{{ old('stuurbreedte', $results['stuurbreedte'] ?? '') }}" class="px-2 py-1 w-24 text-right border border-gray-300 rounded-md" form="bikefit-report-form"> mm
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Resultaten voor en na bikefit naast elkaar -->
    <div class="flex flex-col md:flex-row gap-8 mb-8">
        <div class="bg-white rounded shadow p-4 md:p-6 w-full md:w-1/2">
            <h2 class="text-lg font-bold text-center mb-4">Resultaten voor bikefit</h2>
            @include('bikefit._results_section', ['results' => $resultsVoor, 'bikefit' => $bikefitVoor])
        </div>
        <div class="bg-white rounded shadow p-4 md:p-6 w-full md:w-1/2">
            <h2 class="text-lg font-bold text-center mb-4">Resultaten na bikefit</h2>
            @include('bikefit._results_section', ['results' => $resultsNa, 'bikefit' => $bikefitNa])
        </div>
        </div>

                </div>

    <div class="bg-white rounded shadow p-4 md:p-6 w-full mb-8">
        @include('bikefit._mobility_results', ['bikefit' => $bikefitNa])
    </div>

    <div class="mt-6 flex gap-4">
        <a href="{{ route('bikefit.report.print.perfect', ['klant' => $bikefit->klant_id, 'bikefit' => $bikefit->id]) }}" target="_blank" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded inline-block">
            📋 Verslag genereren
        </a>
        
        @php
            use App\Helpers\SjabloonHelper;
            $hasMatchingTemplate = SjabloonHelper::hasMatchingTemplate($bikefit->testtype, 'bikefit');
            $matchingTemplate = SjabloonHelper::findMatchingTemplate($bikefit->testtype, 'bikefit');
        @endphp
        
        @if($hasMatchingTemplate)
            <a href="{{ route('bikefit.sjabloon-rapport', ['klant' => $bikefit->klant_id, 'bikefit' => $bikefit->id]) }}" 
               target="_blank"
               class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium text-gray-700 hover:text-gray-900 transition-all duration-200"
               style="background-color: #c8e1eb; border: 1px solid #a5c9d6;">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                📄 Rapport Preview ({{ $matchingTemplate->naam }})
            </a>
        @else
            <div class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium bg-yellow-100 border border-yellow-400 text-yellow-800">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.734-.833-2.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
                Geen sjabloon voor "{{ $bikefit->testtype }}" 
                <a href="{{ route('sjablonen.create') }}" class="ml-2 underline hover:no-underline">Maak aan</a>
            </div>
        @endif
        
        <a href="{{ route('klanten.show', $bikefit->klant_id) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded inline-block">
            ← Terug naar klant
        </a>
    </div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('input[type="number"]').forEach(function(input) {
        input.addEventListener('input', function(e) {
            if (input.value.includes(',')) {
                input.value = input.value.replace(',', '.');
            }
        });
    });
});
</script>
</div>
@endsection
