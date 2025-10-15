{{-- Testresultaten Tabel voor Inspanningstest Results --}}
{{-- Variabele sleutel: {{INSPANNINGSTEST_TESTRESULTATEN}} --}}

@php
    // Bepaal testtype en relevante velden
    $testtype = $inspanningstest->testtype ?? 'fietstest';
    $isLooptest = str_contains($testtype, 'loop');
    $isZwemtest = str_contains($testtype, 'zwem');
    $isVeldtest = str_contains($testtype, 'veld');
    
    // Decode testresultaten JSON
    $testresultaten = is_array($testresultaten) ? $testresultaten : [];
    
    // Bepaal kolom headers op basis van testtype
    $headers = [];
    if ($isVeldtest && $isLooptest) {
        $headers = ['Afstand (m)', 'Tijd (min)', 'Tijd (sec)', 'Lactaat (mmol/L)', 'Hartslag (bpm)', 'Borg'];
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
                        @if($isVeldtest && ($isLooptest || $isZwemtest))
                            {{-- Veldtest lopen/zwemmen: Afstand, Tijd (min), Tijd (sec), Lactaat, Hartslag, Borg --}}
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
</div>
@else
<div class="bg-yellow-50 rounded-lg p-6 mb-6" style="border: 2px solid #fbbf24;">
    <p class="text-yellow-800 text-center">
        ⚠️ Geen testresultaten beschikbaar voor deze inspanningstest.
    </p>
</div>
@endif
