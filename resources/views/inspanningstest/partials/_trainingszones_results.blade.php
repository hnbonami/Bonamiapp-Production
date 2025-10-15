{{-- Trainingszones Tabel voor Inspanningstest Results --}}
{{-- Variabele sleutel: {{INSPANNINGSTEST_TRAININGSZONES}} --}}

@php
    // Bepaal testtype
    $testtype = $inspanningstest->testtype ?? 'fietstest';
    $isLooptest = str_contains($testtype, 'loop');
    $isZwemtest = str_contains($testtype, 'zwem');
    
    // Decode trainingszones JSON
    $trainingszones = is_array($trainingszones) ? $trainingszones : [];
    
    // Bepaal eenheid label
    $eenheidLabel = 'Watt';
    if ($isLooptest) {
        $eenheidLabel = 'km/h';
    } elseif ($isZwemtest) {
        $eenheidLabel = 'min/100m';
    }
    
    // Bepaal zones methode
    $zonesMethode = $inspanningstest->zones_methode ?? 'bonami';
    $zonesMethodeLabel = ucfirst($zonesMethode);
@endphp

{{-- Trainingszones Results Partial --}}
{{-- Variabele sleutel: {{INSPANNINGSTEST_TRAININGSZONES}} --}}

@php
    // Helper functie om decimale minuten naar mm:ss te converteren
    function formatMinPerKmDisplay($decimalMinutes) {
        if ($decimalMinutes >= 999 || !is_numeric($decimalMinutes)) return '∞';
        
        $minutes = floor($decimalMinutes);
        $seconds = round(($decimalMinutes - $minutes) * 60);
        
        return sprintf('%d:%02d', $minutes, $seconds);
    }
    
    // Bepaal of dit een looptest is voor min/km kolom
    $isLooptest = in_array($inspanningstest->testtype, ['looptest', 'veldtest_lopen']);
@endphp

@if(count($trainingszones) > 0)
<div class="bg-white rounded-lg shadow-md overflow-hidden mb-6" style="border: 2px solid #c8e1eb;">
    {{-- Header --}}
    <div class="px-6 py-4" style="background-color: #c8e1eb; border-bottom: 2px solid #a8c1cb;">
        <div class="flex justify-between items-center">
            <div>
                <h3 class="text-xl font-bold text-gray-900">🎯 Trainingszones</h3>
                <p class="text-sm text-gray-700 mt-1">Persoonlijke trainingszones op basis van {{ $zonesMethodeLabel }} methode</p>
            </div>
            <div class="text-right">
                <span class="text-xs font-semibold text-gray-700 bg-white px-3 py-1 rounded-full border-2" style="border-color: #a8c1cb;">
                    {{ count($trainingszones) }} zones
                </span>
            </div>
        </div>
    </div>
    
    {{-- Tabel --}}
    <div class="overflow-x-auto">
        <table class="w-full">
            {{-- Table Header --}}
            <thead style="background-color: #e3f2fd;">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b-2" style="border-color: #c8e1eb;">
                        Zone
                    </th>
                    <th class="px-4 py-3 text-center text-xs font-bold text-gray-700 uppercase tracking-wider border-b-2" style="border-color: #c8e1eb;" colspan="2">
                        Hartslag (bpm)
                    </th>
                    <th class="px-4 py-3 text-center text-xs font-bold text-gray-700 uppercase tracking-wider border-b-2" style="border-color: #c8e1eb;" colspan="2">
                        {{ $eenheidLabel }}
                    </th>
                    @if($isLooptest)
                        <th class="px-4 py-3 text-center text-xs font-bold text-gray-700 uppercase tracking-wider border-b-2" style="border-color: #c8e1eb;" colspan="2">
                            min/km
                        </th>
                    @endif
                    <th class="px-4 py-3 text-center text-xs font-bold text-gray-700 uppercase tracking-wider border-b-2" style="border-color: #c8e1eb;">
                        Borg
                    </th>
                </tr>
                <tr style="background-color: #f0f9ff;">
                    <th class="px-4 py-2 border-b" style="border-color: #c8e1eb;"></th>
                    <th class="px-2 py-2 text-xs text-gray-600 border-b" style="border-color: #c8e1eb;">min</th>
                    <th class="px-2 py-2 text-xs text-gray-600 border-b" style="border-color: #c8e1eb;">max</th>
                    <th class="px-2 py-2 text-xs text-gray-600 border-b" style="border-color: #c8e1eb;">min</th>
                    <th class="px-2 py-2 text-xs text-gray-600 border-b" style="border-color: #c8e1eb;">max</th>
                    @if($isLooptest)
                        <th class="px-2 py-2 text-xs text-gray-600 border-b" style="border-color: #c8e1eb;">min</th>
                        <th class="px-2 py-2 text-xs text-gray-600 border-b" style="border-color: #c8e1eb;">max</th>
                    @endif
                    <th class="px-2 py-2 text-xs text-gray-600 border-b" style="border-color: #c8e1eb;">schaal</th>
                </tr>
            </thead>
            
            {{-- Table Body --}}
            <tbody>
                @foreach($trainingszones as $index => $zone)
                    @php
                        // Bereken min/km voor looptesten
                        $minMinPerKm = null;
                        $maxMinPerKm = null;
                        if ($isLooptest && isset($zone['maxVermogen']) && isset($zone['minVermogen'])) {
                            $minMinPerKm = $zone['maxVermogen'] > 0 ? (60 / $zone['maxVermogen']) : null;
                            $maxMinPerKm = $zone['minVermogen'] > 0 ? (60 / $zone['minVermogen']) : null;
                        }
                        
                        // Borg tekst
                        $borgText = '';
                        if (isset($zone['borgMin']) && isset($zone['borgMax'])) {
                            $borgText = $zone['borgMin'] . ' - ' . $zone['borgMax'];
                        }
                        
                        // Zone kleur (uit berekening)
                        $zoneKleur = $zone['kleur'] ?? '#FFFFFF';
                    @endphp
                    <tr class="border-b border-gray-200 hover:bg-opacity-80 transition-colors duration-150" style="background-color: {{ $zoneKleur }};">
                        {{-- Zone naam en beschrijving --}}
                        <td class="px-4 py-3 border-r border-gray-200">
                            <div class="font-bold text-sm text-gray-900">{{ $zone['naam'] ?? 'Zone ' . ($index + 1) }}</div>
                            @if(isset($zone['beschrijving']))
                                <div class="text-xs text-gray-600 mt-1">{{ $zone['beschrijving'] }}</div>
                            @endif
                        </td>
                        
                        {{-- Hartslag min/max --}}
                        <td class="px-2 py-3 text-center text-sm font-semibold border-r border-gray-200" style="color: #dc2626;">
                            {{ isset($zone['minHartslag']) ? round($zone['minHartslag']) : '-' }}
                        </td>
                        <td class="px-2 py-3 text-center text-sm font-semibold border-r border-gray-200" style="color: #dc2626;">
                            {{ isset($zone['maxHartslag']) ? round($zone['maxHartslag']) : '-' }}
                        </td>
                        
                        {{-- Vermogen/Snelheid min/max --}}
                        <td class="px-2 py-3 text-center text-sm font-semibold border-r border-gray-200" style="color: #2563eb;">
                            @if($isLooptest)
                                {{ isset($zone['minVermogen']) ? number_format($zone['minVermogen'], 1) : '-' }}
                            @else
                                {{ isset($zone['minVermogen']) ? round($zone['minVermogen']) : '-' }}
                            @endif
                        </td>
                        <td class="px-2 py-3 text-center text-sm font-semibold border-r border-gray-200" style="color: #2563eb;">
                            @if($isLooptest)
                                {{ isset($zone['maxVermogen']) ? number_format($zone['maxVermogen'], 1) : '-' }}
                            @else
                                {{ isset($zone['maxVermogen']) ? round($zone['maxVermogen']) : '-' }}
                            @endif
                        </td>
                        
                        {{-- Min/km kolommen (alleen voor looptesten) - mm:ss formaat --}}
                        @if($isLooptest)
                            <td class="px-2 py-3 text-center text-sm text-gray-700 border-r border-gray-200">
                                {{ $minMinPerKm !== null ? formatMinPerKmDisplay($minMinPerKm) : '-' }}
                            </td>
                            <td class="px-2 py-3 text-center text-sm text-gray-700 border-r border-gray-200">
                                {{ $maxMinPerKm !== null ? formatMinPerKmDisplay($maxMinPerKm) : '-' }}
                            </td>
                        @endif
                        
                        {{-- Borg schaal --}}
                        <td class="px-2 py-3 text-center text-sm text-gray-700">
                            {{ $borgText }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    {{-- Footer met legenda --}}
    <div class="px-6 py-4 bg-gray-50 border-t-2" style="border-color: #c8e1eb;">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- Kleuren legenda --}}
            <div>
                <p class="text-xs font-semibold text-gray-700 mb-2">💡 Zone Kleuren:</p>
                <div class="flex flex-wrap gap-2">
                    <span class="text-xs px-2 py-1 rounded" style="background-color: #E3F2FD;">Herstel</span>
                    <span class="text-xs px-2 py-1 rounded" style="background-color: #E8F5E8;">Lange Duur</span>
                    <span class="text-xs px-2 py-1 rounded" style="background-color: #F1F8E9;">Extensief</span>
                    <span class="text-xs px-2 py-1 rounded" style="background-color: #FFF3E0;">Intensief</span>
                    <span class="text-xs px-2 py-1 rounded" style="background-color: #FFEBEE;">Tempo</span>
                    <span class="text-xs px-2 py-1 rounded" style="background-color: #FFCDD2;">Maximaal</span>
                </div>
            </div>
            
            {{-- Waarden legenda --}}
            <div class="text-right">
                <p class="text-xs font-semibold text-gray-700 mb-2">📊 Waarden:</p>
                <div class="flex justify-end gap-4 text-xs">
                    <div><span class="inline-block w-3 h-3 rounded-full mr-1" style="background-color: #2563eb;"></span> {{ $eenheidLabel }}</div>
                    <div><span class="inline-block w-3 h-3 rounded-full mr-1" style="background-color: #dc2626;"></span> Hartslag</div>
                </div>
            </div>
        </div>
    </div>
</div>
@else
<div class="bg-yellow-50 rounded-lg p-6 mb-6" style="border: 2px solid #fbbf24;">
    <p class="text-yellow-800 text-center">
        ⚠️ Geen trainingszones beschikbaar. Zones worden berekend op basis van drempelwaarden.
    </p>
</div>
@endif
