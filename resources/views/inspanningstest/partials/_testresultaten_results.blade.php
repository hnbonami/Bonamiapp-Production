{{-- Testresultaten Tabel voor Inspanningstest Results --}}
{{-- Variabele sleutel: {{INSPANNINGSTEST_TESTRESULTATEN}} --}}

@php
    // Bepaal testtype en relevante velden
    $testtype = $inspanningstest->testtype ?? 'fietstest';
    $isLooptest = str_contains($testtype, 'loop') || str_contains($testtype, 'lopen');
    $isZwemtest = str_contains($testtype, 'zwem');
    $isVeldtest = str_contains($testtype, 'veld');
    
    // Decode testresultaten JSON
    $testresultaten = is_array($testresultaten) ? $testresultaten : [];
    
    // Bepaal kolom headers op basis van testtype
    $headers = [];
    if ($isVeldtest && $isLooptest) {
        $headers = ['Afstand (m)', 'Snelheid (km/h)', 'Lactaat (mmol/L)', 'Hartslag (bpm)', 'Borg'];
    } elseif ($isVeldtest && $isZwemtest) {
        $headers = ['Afstand (m)', 'Tijd (min)', 'Tijd (sec)', 'Lactaat (mmol/L)', 'Hartslag (bpm)', 'Borg'];
    } elseif ($isLooptest) {
        $headers = ['Tijd (min)', 'Snelheid (km/h)', 'Lactaat (mmol/L)', 'Hartslag (bpm)', 'Borg'];
    } else {
        // Fietstest of veldtest fietsen
        $headers = ['Tijd (min)', 'Vermogen (Watt)', 'Lactaat (mmol/L)', 'Hartslag (bpm)', 'Borg'];
    }
@endphp

@if(count($testresultaten) > 0)
<div class="bg-white rounded-lg shadow-md overflow-hidden mb-6" style="border: 2px solid #c8e1eb;">
    {{-- Header --}}
    <div class="px-6 py-4" style="background-color: #c8e1eb; border-bottom: 2px solid #a8c1cb;">
        <h3 class="text-xl font-bold text-gray-900">📊 Testresultaten</h3>
        <p class="text-sm text-gray-700 mt-1">Gemeten waarden per stap tijdens de inspanningstest</p>
    </div>
    
    {{-- Tabel --}}
    <div class="overflow-x-auto">
        <table class="w-full">
            {{-- Table Header --}}
            <thead style="background-color: #e3f2fd;">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b-2" style="border-color: #c8e1eb;">
                        #
                    </th>
                    @foreach($headers as $header)
                        <th class="px-4 py-3 text-center text-xs font-bold text-gray-700 uppercase tracking-wider border-b-2" style="border-color: #c8e1eb;">
                            {{ $header }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            
            {{-- Table Body --}}
            <tbody>
                @foreach($testresultaten as $index => $rij)
                    <tr class="{{ $index % 2 === 0 ? 'bg-white' : 'bg-gray-50' }} hover:bg-blue-50 transition-colors duration-150">
                        {{-- Rij nummer --}}
                        <td class="px-4 py-3 text-center font-bold text-gray-900 border-b border-gray-200">
                            {{ $index + 1 }}
                        </td>
                        
                        {{-- Dynamische kolommen op basis van testtype --}}
                        @if($isVeldtest && $isLooptest)
                            {{-- Veldtest lopen: Afstand, Snelheid, Lactaat, Hartslag, Borg --}}
                            <td class="px-4 py-3 text-center text-sm text-gray-900 border-b border-gray-200">
                                {{ $rij['afstand'] ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-center text-sm font-semibold border-b border-gray-200" style="color: #2563eb;">
                                {{ isset($rij['snelheid']) ? number_format($rij['snelheid'], 1) : '-' }}
                            </td>
                            <td class="px-4 py-3 text-center text-sm font-semibold border-b border-gray-200" style="color: #16a34a;">
                                {{ isset($rij['lactaat']) ? number_format($rij['lactaat'], 1) : '-' }}
                            </td>
                            <td class="px-4 py-3 text-center text-sm font-semibold border-b border-gray-200" style="color: #dc2626;">
                                {{ $rij['hartslag'] ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-center text-sm text-gray-700 border-b border-gray-200">
                                {{ $rij['borg'] ?? '-' }}
                            </td>
                        @elseif($isVeldtest && $isZwemtest)
                            {{-- Veldtest zwemmen: Afstand, Tijd (min), Tijd (sec), Lactaat, Hartslag, Borg --}}
                            <td class="px-4 py-3 text-center text-sm text-gray-900 border-b border-gray-200">
                                {{ $rij['afstand'] ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-center text-sm text-gray-900 border-b border-gray-200">
                                {{ $rij['tijd_min'] ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-center text-sm text-gray-900 border-b border-gray-200">
                                {{ $rij['tijd_sec'] ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-center text-sm font-semibold border-b border-gray-200" style="color: #16a34a;">
                                {{ isset($rij['lactaat']) ? number_format($rij['lactaat'], 1) : '-' }}
                            </td>
                            <td class="px-4 py-3 text-center text-sm font-semibold border-b border-gray-200" style="color: #dc2626;">
                                {{ $rij['hartslag'] ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-center text-sm text-gray-700 border-b border-gray-200">
                                {{ $rij['borg'] ?? '-' }}
                            </td>
                        @elseif($isLooptest)
                            {{-- Inspanningstest lopen: Tijd, Snelheid, Lactaat, Hartslag, Borg --}}
                            <td class="px-4 py-3 text-center text-sm text-gray-900 border-b border-gray-200">
                                {{ $rij['tijd'] ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-center text-sm font-semibold border-b border-gray-200" style="color: #2563eb;">
                                {{ isset($rij['snelheid']) ? number_format($rij['snelheid'], 1) : '-' }}
                            </td>
                            <td class="px-4 py-3 text-center text-sm font-semibold border-b border-gray-200" style="color: #16a34a;">
                                {{ isset($rij['lactaat']) ? number_format($rij['lactaat'], 1) : '-' }}
                            </td>
                            <td class="px-4 py-3 text-center text-sm font-semibold border-b border-gray-200" style="color: #dc2626;">
                                {{ $rij['hartslag'] ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-center text-sm text-gray-700 border-b border-gray-200">
                                {{ $rij['borg'] ?? '-' }}
                            </td>
                        @else
                            {{-- Fietstest / Veldtest fietsen: Tijd, Vermogen, Lactaat, Hartslag, Borg --}}
                            <td class="px-4 py-3 text-center text-sm text-gray-900 border-b border-gray-200">
                                {{ $rij['tijd'] ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-center text-sm font-semibold border-b border-gray-200" style="color: #2563eb;">
                                {{ $rij['vermogen'] ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-center text-sm font-semibold border-b border-gray-200" style="color: #16a34a;">
                                {{ isset($rij['lactaat']) ? number_format($rij['lactaat'], 1) : '-' }}
                            </td>
                            <td class="px-4 py-3 text-center text-sm font-semibold border-b border-gray-200" style="color: #dc2626;">
                                {{ $rij['hartslag'] ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-center text-sm text-gray-700 border-b border-gray-200">
                                {{ $rij['borg'] ?? '-' }}
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    {{-- Footer met samenvatting --}}
    <div class="px-6 py-4 bg-gray-50 border-t-2" style="border-color: #c8e1eb;">
        <div class="flex justify-between items-center text-sm text-gray-600">
            <div>
                <span class="font-semibold">Totaal aantal metingen:</span> {{ count($testresultaten) }}
            </div>
            <div class="flex gap-4">
                <div><span class="inline-block w-3 h-3 rounded-full mr-1" style="background-color: #2563eb;"></span> Vermogen/Snelheid</div>
                <div><span class="inline-block w-3 h-3 rounded-full mr-1" style="background-color: #16a34a;"></span> Lactaat</div>
                <div><span class="inline-block w-3 h-3 rounded-full mr-1" style="background-color: #dc2626;"></span> Hartslag</div>
            </div>
        </div>
    </div>
    
    {{-- Toelichting Testresultaten --}}
    <div class="mx-6 mb-6 mt-4 p-6" style="background-color: #fff8e1;">
        <div class="flex items-start">
            <div class="flex-shrink-0 mr-4 text-2xl">
                💡
            </div>
            <div class="flex-1">
                <h4 class="text-base font-bold text-gray-900 mb-3">Wat wordt er gemeten?</h4>
                <div class="text-sm text-gray-700 space-y-3">
                    <p>
                        @if($isLooptest)
                            Tijdens de looptest wordt de snelheid geleidelijk verhoogd per stap. 
                            @if($isVeldtest)
                                Na elke inspanning wordt een bloedstaal genomen en worden de hartslag en tijd genoteerd.
                            @else
                                Aan het einde van elke stap worden hartslag, snelheid en melkzuurproductie gemeten en geregistreerd.
                            @endif
                        @elseif($isZwemtest)
                            Tijdens de zwemtest worden verschillende afstanden gezwommen met toenemende intensiteit. 
                            Na elke inspanning wordt een bloedstaal genomen en worden de hartslag en tijd genoteerd.
                        @else
                            Tijdens de fietstest wordt het vermogen (Watt) geleidelijk verhoogd per stap. 
                            Aan het einde van elke stap worden hartslag, vermogen en melkzuurproductie gemeten en geregistreerd.
                        @endif
                    </p>
                    <p>
                        <strong>Doel van de test:</strong> Het bepalen van de aërobe en anaërobe getraindheid. 
                        @if($isLooptest)
                            Door de combinatie van snelheid, hartslag en lactaatniveaus kunnen we je optimale trainingssnelheden bepalen.
                        @elseif($isZwemtest)
                            Door de combinatie van zwemtijden, hartslag en lactaatniveaus kunnen we je optimale trainingsintensiteiten bepalen.
                        @else
                            Door de combinatie van vermogen, hartslag en lactaatniveaus kunnen we je optimale trainingszones bepalen.
                        @endif
                    </p>
                    <p>
                        <strong>Lactaat (melkzuur):</strong> De lactaatwaarde geeft aan hoeveel afvalstoffen je spieren produceren bij een bepaalde intensiteit. 
                        @if($isLooptest)
                            Bij lage snelheden blijft het lactaat laag (aëroob), bij hoge snelheden stijgt het lactaat snel (anaëroob).
                        @elseif($isZwemtest)
                            Bij lage intensiteit blijft het lactaat laag (aëroob), bij hoge intensiteit stijgt het lactaat snel (anaëroob).
                        @else
                            Bij laag vermogen blijft het lactaat laag (aëroob), bij hoog vermogen stijgt het lactaat snel (anaëroob).
                        @endif
                        De overgang tussen beide zones is cruciaal voor je trainingsopbouw.
                    </p>
                    <p>
                        <strong>Borg schaal:</strong> Dit is je persoonlijke inspanningsbeleving op een schaal van 6-20. 
                        Dit helpt om subjectieve inspanning te koppelen aan objectieve metingen en is nuttig voor het sturen van je training op gevoel.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@else
<div class="bg-yellow-50 rounded-lg p-6 mb-6" style="border: 2px solid #fbbf24;">
    <p class="text-yellow-800 text-center">
        ⚠️ Geen testresultaten beschikbaar voor deze inspanningstest.
    </p>
</div>
@endif
