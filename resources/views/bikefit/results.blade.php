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
    <h1 class="text-2xl font-bold mb-4">Bikefit berekende resultaten - {{ $klant->voornaam}} {{ $klant->naam }}</h1>

    <!-- Hidden form voor het opslaan van wijzigingen -->
    <form id="bikefit-form" method="POST" action="{{ route('bikefit.update', ['klant' => $bikefit->klant_id, 'bikefit' => $bikefit->id]) }}" style="display: none;">
        @csrf
        @method('PUT')
    </form>

    <!-- Prognose zitpositie -->
    <div class="bg-white rounded shadow p-4 md:p-6 mb-8">
        <h2 class="text-lg font-bold text-center mb-4">Prognose zitpositie</h2>
        @include('bikefit._results_section', ['results' => $results, 'bikefit' => $bikefit])
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
        <a href="{{ route('bikefit.edit', ['klant' => $bikefit->klant_id, 'bikefit' => $bikefit->id]) }}" 
           class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium text-gray-700 hover:text-gray-900 transition-all duration-200"
           style="background-color: #c8e1eb; border: 1px solid #a5c9d6;">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
            Bewerken
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
                Rapport Preview ({{ $matchingTemplate->naam }})
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
        
        <a href="{{ route('klanten.show', $bikefit->klant_id) }}" 
           class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium text-gray-700 hover:text-gray-900 transition-all duration-200"
           style="background-color: #c8e1eb; border: 1px solid #a5c9d6;">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Terug naar klant
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

@section('scripts')
<script>
    // Geef custom waarden door aan JavaScript
    window.bikefitCustomValues = @json($customValues ?? []);
    console.log('📊 Custom waarden geladen vanuit database:', window.bikefitCustomValues);
    
    // FORCEER custom waarden in de input velden zodra de pagina geladen is
    window.addEventListener('DOMContentLoaded', function() {
        setTimeout(function() {
            console.log('🔧 Toepassen custom waarden op input velden...');
            
            // Loop door alle contexten (prognose, voor, na)
            ['prognose', 'voor', 'na'].forEach(function(context) {
                const customValues = window.bikefitCustomValues[context];
                if (!customValues) return;
                
                console.log(`📝 Context: ${context}`, customValues);
                
                // Loop door alle velden in deze context
                Object.keys(customValues).forEach(function(fieldName) {
                    const customValue = customValues[fieldName];
                    if (customValue === null || customValue === undefined) return;
                    
                    // Zoek het input veld met deze naam in deze context
                    const inputs = document.querySelectorAll(`input[data-field="${fieldName}"]`);
                    inputs.forEach(function(input) {
                        // Check of dit input in de juiste context zit
                        const section = input.closest('[data-context]');
                        if (section && section.dataset.context === context) {
                            console.log(`✏️ Zet ${context}.${fieldName} = ${customValue} (was: ${input.value})`);
                            input.value = customValue;
                            input.style.backgroundColor = '#fffbeb'; // Licht geel om te tonen dat het custom is
                        }
                    });
                });
            });
            
            console.log('✅ Custom waarden toegepast op alle input velden');
        }, 500); // Wacht 500ms zodat editable-results.js eerst kan laden
    });
</script>
<script src="{{ asset('js/editable-results.js') }}"></script>
@endsection
