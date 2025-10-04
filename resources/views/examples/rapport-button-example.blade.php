{{-- Voorbeeld van hoe je een rapport knop kunt toevoegen aan je bikefit show/edit pagina --}}

@php
    use App\Helpers\SjabloonHelper;
    $hasMatchingTemplate = SjabloonHelper::hasMatchingTemplate($bikefit->testtype, 'bikefit');
@endphp

<div class="mt-6">
    @if($hasMatchingTemplate)
        <a href="{{ route('sjablonen.bikefit-rapport', $bikefit->id) }}" 
           class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium text-gray-700 hover:text-gray-900 transition-all duration-200"
           style="background-color: #c8e1eb; border: 1px solid #a5c9d6;">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            Genereer Rapport
        </a>
        <p class="text-sm text-gray-600 mt-2">
            Rapport wordt gegenereerd met sjabloon voor: {{ $bikefit->testtype }}
        </p>
    @else
        <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded">
            <strong>Let op:</strong> Geen sjabloon gevonden voor testtype "{{ $bikefit->testtype }}". 
            <a href="{{ route('sjablonen.create') }}" class="underline">Maak een sjabloon aan</a> 
            met testtype "{{ $bikefit->testtype }}" om rapporten te kunnen genereren.
        </div>
    @endif
</div>

{{-- Voor Inspanningstest zou dit hetzelfde zijn, maar dan met: --}}
{{-- route('sjablonen.inspanningstest-rapport', $inspanningstest->id) --}}
{{-- en categorie 'inspanningstest' --}}