{{-- Drempelwaarden Overzicht Tabel voor Inspanningstest Results --}}
{{-- Variabele sleutel: {{INSPANNINGSTEST_DREMPELWAARDEN}} --}}

@php
    // Bepaal testtype
    $testtype = $inspanningstest->testtype ?? 'fietstest';
    $isLooptest = str_contains($testtype, 'loop');
    $isZwemtest = str_contains($testtype, 'zwem');
    
    // Haal drempelwaarden op
    $lt1Vermogen = $inspanningstest->aerobe_drempel_vermogen ?? null;
    $lt1Hartslag = $inspanningstest->aerobe_drempel_hartslag ?? null;
    $lt2Vermogen = $inspanningstest->anaerobe_drempel_vermogen ?? null;
    $lt2Hartslag = $inspanningstest->anaerobe_drempel_hartslag ?? null;
    
    // Maximum waarden (ALTIJD uit laatste testresultaat halen)
    $maxVermogen = null;
    $maxLactaat = null;
    $maxHartslag = null;
    
    if (count($testresultaten) > 0) {
        $laatsteStap = end($testresultaten);
        $maxVermogen = $laatsteStap['vermogen'] ?? $laatsteStap['snelheid'] ?? null;
        $maxLactaat = $laatsteStap['lactaat'] ?? null;
        $maxHartslag = $laatsteStap['hartslag'] ?? null; // GEFIXEERD: uit testresultaten!
    }
    
    // Fallback naar ingevuld max hartslag veld
    if (!$maxHartslag) {
        $maxHartslag = $inspanningstest->maximale_hartslag_bpm ?? null;
    }
    
    // Lichaamsgewicht voor Watt/kg berekening
    $gewicht = $inspanningstest->lichaamsgewicht_kg ?? null;
    
    // Bereken percentages van max
    function berekenPercentage($waarde, $max) {
        if (!$waarde || !$max || $max == 0) return null;
        return round(($waarde / $max) * 100);
    }
    
    $lt1Percentage = berekenPercentage($lt1Hartslag, $maxHartslag);
    $lt2Percentage = berekenPercentage($lt2Hartslag, $maxHartslag);
    
    // GEFIXEERD: Haal WERKELIJKE lactaat waarden op bij drempels uit testresultaten
    $lt1Lactaat = null;
    $lt2Lactaat = null;
    
    // Zoek dichtstbijzijnde testresultaat voor LT1
    if ($lt1Vermogen && count($testresultaten) > 0) {
        $closestDiff = PHP_INT_MAX;
        foreach ($testresultaten as $stap) {
            $stapVermogen = $stap['vermogen'] ?? $stap['snelheid'] ?? null;
            if ($stapVermogen) {
                $diff = abs($stapVermogen - $lt1Vermogen);
                if ($diff < $closestDiff) {
                    $closestDiff = $diff;
                    $lt1Lactaat = $stap['lactaat'] ?? null;
                }
            }
        }
    }
    
    // Zoek dichtstbijzijnde testresultaat voor LT2
    if ($lt2Vermogen && count($testresultaten) > 0) {
        $closestDiff = PHP_INT_MAX;
        foreach ($testresultaten as $stap) {
            $stapVermogen = $stap['vermogen'] ?? $stap['snelheid'] ?? null;
            if ($stapVermogen) {
                $diff = abs($stapVermogen - $lt2Vermogen);
                if ($diff < $closestDiff) {
                    $closestDiff = $diff;
                    $lt2Lactaat = $stap['lactaat'] ?? null;
                }
            }
        }
    }
    
    // Voor zwemmen: converteer decimale minuten naar mm:ss
    function formatZwemTijd($decimaleMinuten) {
        if (!$decimaleMinuten) return null;
        $totalSeconds = $decimaleMinuten * 60;
        $min = floor($totalSeconds / 60);
        $sec = round($totalSeconds % 60);
        return sprintf('%d:%02d', $min, $sec);
    }
    
    // Voor looptesten: bereken min/km uit km/h en formatteer als mm:ss
    function berekenMinPerKm($kmh) {
        if (!$kmh || $kmh == 0) return null;
        $decimalMinutes = 60 / $kmh;
        
        // Converteer naar mm:ss formaat
        $minutes = floor($decimalMinutes);
        $seconds = round(($decimalMinutes - $minutes) * 60);
        
        return sprintf('%d:%02d', $minutes, $seconds);
    }
@endphp

<div class="bg-white rounded-lg shadow-md overflow-hidden mb-6" style="border: 2px solid #c8e1eb;">
    {{-- Header --}}
    <div class="px-6 py-4" style="background-color: #c8e1eb; border-bottom: 2px solid #a8c1cb;">
        <h3 class="text-xl font-bold text-gray-900">📈 Drempelwaarden Overzicht</h3>
        <p class="text-sm text-gray-700 mt-1">Samenvatting van gemeten prestatieparameters</p>
    </div>
    
    {{-- Tabel --}}
    <div class="overflow-x-auto">
        <table class="w-full">
            {{-- Table Header --}}
            <thead style="background-color: #e3f2fd;">
                <tr>
                    <th class="px-6 py-3 text-left text-sm font-bold text-gray-800 uppercase tracking-wider border-b-2" style="border-color: #c8e1eb;">
                        Drempels
                    </th>
                    
                    @if($isZwemtest)
                        {{-- Zwemtest kolommen --}}
                        <th class="px-4 py-3 text-center text-sm font-bold text-gray-800 border-b-2" style="border-color: #c8e1eb;">
                            Snelheid<br><span class="text-xs font-normal text-gray-600">min/100m</span>
                        </th>
                    @elseif($isLooptest)
                        {{-- Looptest kolommen --}}
                        <th class="px-4 py-3 text-center text-sm font-bold text-gray-800 border-b-2" style="border-color: #c8e1eb;">
                            Snelheid<br><span class="text-xs font-normal text-gray-600">km/h</span>
                        </th>
                        <th class="px-4 py-3 text-center text-sm font-bold text-gray-800 border-b-2" style="border-color: #c8e1eb;">
                            Tempo<br><span class="text-xs font-normal text-gray-600">min/km</span>
                        </th>
                    @else
                        {{-- Fietstest kolommen --}}
                        <th class="px-4 py-3 text-center text-sm font-bold text-gray-800 border-b-2" style="border-color: #c8e1eb;">
                            Vermogen<br><span class="text-xs font-normal text-gray-600">Watt</span>
                        </th>
                        <th class="px-4 py-3 text-center text-sm font-bold text-gray-800 border-b-2" style="border-color: #c8e1eb;">
                            Vermogen<br><span class="text-xs font-normal text-gray-600">Watt/kg</span>
                        </th>
                    @endif
                    
                    <th class="px-4 py-3 text-center text-sm font-bold text-gray-800 border-b-2" style="border-color: #c8e1eb;">
                        Hartslag<br><span class="text-xs font-normal text-gray-600">BPM</span>
                    </th>
                    <th class="px-4 py-3 text-center text-sm font-bold text-gray-800 border-b-2" style="border-color: #c8e1eb;">
                        Lactaat<br><span class="text-xs font-normal text-gray-600">mmol/L</span>
                    </th>
                    <th class="px-4 py-3 text-center text-sm font-bold text-gray-800 border-b-2" style="border-color: #c8e1eb;">
                        Vermogen<br><span class="text-xs font-normal text-gray-600">%max</span>
                    </th>
                </tr>
            </thead>
            
            {{-- Table Body --}}
            <tbody>
                {{-- Aërobe Drempel (LT1) --}}
                <tr class="bg-white hover:bg-red-50 transition-colors">
                    <td class="px-6 py-4 font-bold text-gray-900 border-b border-gray-200">
                        🔴 Aërobe drempel
                    </td>
                    
                    @if($isZwemtest)
                        <td class="px-4 py-4 text-center font-semibold text-lg border-b border-gray-200" style="color: #2563eb;">
                            {{ $lt1Vermogen ? formatZwemTijd($lt1Vermogen) : '-' }}
                        </td>
                    @elseif($isLooptest)
                        <td class="px-4 py-4 text-center font-semibold text-lg border-b border-gray-200" style="color: #2563eb;">
                            {{ $lt1Vermogen ? number_format($lt1Vermogen, 1) : '-' }}
                        </td>
                        <td class="px-4 py-4 text-center font-semibold text-lg border-b border-gray-200" style="color: #2563eb;">
                            {{ $lt1Vermogen ? berekenMinPerKm($lt1Vermogen) : '-' }}
                        </td>
                    @else
                        <td class="px-4 py-4 text-center font-semibold text-lg border-b border-gray-200" style="color: #2563eb;">
                            {{ $lt1Vermogen ? round($lt1Vermogen) : '-' }}
                        </td>
                        <td class="px-4 py-4 text-center font-semibold text-lg border-b border-gray-200" style="color: #2563eb;">
                            {{ ($lt1Vermogen && $gewicht) ? number_format($lt1Vermogen / $gewicht, 1) : '-' }}
                        </td>
                    @endif
                    
                    <td class="px-4 py-4 text-center font-semibold text-lg border-b border-gray-200" style="color: #dc2626;">
                        {{ $lt1Hartslag ? round($lt1Hartslag) : '-' }}
                    </td>
                    <td class="px-4 py-4 text-center font-semibold text-lg border-b border-gray-200" style="color: #16a34a;">
                        {{ $lt1Lactaat ? number_format($lt1Lactaat, 1) : '~2.0' }}
                    </td>
                    <td class="px-4 py-4 text-center font-semibold text-lg border-b border-gray-200" style="color: #6b7280;">
                        {{ $lt1Percentage ? $lt1Percentage . '%' : '-' }}
                    </td>
                </tr>
                
                {{-- Anaërobe Drempel (LT2) --}}
                <tr class="bg-gray-50 hover:bg-orange-50 transition-colors">
                    <td class="px-6 py-4 font-bold text-gray-900 border-b border-gray-200">
                        🟠 Anaërobe drempel
                    </td>
                    
                    @if($isZwemtest)
                        <td class="px-4 py-4 text-center font-semibold text-lg border-b border-gray-200" style="color: #2563eb;">
                            {{ $lt2Vermogen ? formatZwemTijd($lt2Vermogen) : '-' }}
                        </td>
                    @elseif($isLooptest)
                        <td class="px-4 py-4 text-center font-semibold text-lg border-b border-gray-200" style="color: #2563eb;">
                            {{ $lt2Vermogen ? number_format($lt2Vermogen, 1) : '-' }}
                        </td>
                        <td class="px-4 py-4 text-center font-semibold text-lg border-b border-gray-200" style="color: #2563eb;">
                            {{ $lt2Vermogen ? berekenMinPerKm($lt2Vermogen) : '-' }}
                        </td>
                    @else
                        <td class="px-4 py-4 text-center font-semibold text-lg border-b border-gray-200" style="color: #2563eb;">
                            {{ $lt2Vermogen ? round($lt2Vermogen) : '-' }}
                        </td>
                        <td class="px-4 py-4 text-center font-semibold text-lg border-b border-gray-200" style="color: #2563eb;">
                            {{ ($lt2Vermogen && $gewicht) ? number_format($lt2Vermogen / $gewicht, 1) : '-' }}
                        </td>
                    @endif
                    
                    <td class="px-4 py-4 text-center font-semibold text-lg border-b border-gray-200" style="color: #dc2626;">
                        {{ $lt2Hartslag ? round($lt2Hartslag) : '-' }}
                    </td>
                    <td class="px-4 py-4 text-center font-semibold text-lg border-b border-gray-200" style="color: #16a34a;">
                        {{ $lt2Lactaat ? number_format($lt2Lactaat, 1) : '~4.0' }}
                    </td>
                    <td class="px-4 py-4 text-center font-semibold text-lg border-b border-gray-200" style="color: #6b7280;">
                        {{ $lt2Percentage ? $lt2Percentage . '%' : '-' }}
                    </td>
                </tr>
                
                {{-- Maximum --}}
                @if($maxVermogen || $maxHartslag || $maxLactaat)
                    <tr class="bg-white hover:bg-red-100 transition-colors">
                        <td class="px-6 py-4 font-bold text-gray-900 border-b border-gray-200">
                            🔥 Maximum
                        </td>
                        
                        @if($isZwemtest)
                            <td class="px-4 py-4 text-center font-semibold text-lg border-b border-gray-200" style="color: #2563eb;">
                                {{ $maxVermogen ? formatZwemTijd($maxVermogen) : '-' }}
                            </td>
                        @elseif($isLooptest)
                            <td class="px-4 py-4 text-center font-semibold text-lg border-b border-gray-200" style="color: #2563eb;">
                                {{ $maxVermogen ? number_format($maxVermogen, 1) : '-' }}
                            </td>
                            <td class="px-4 py-4 text-center font-semibold text-lg border-b border-gray-200" style="color: #2563eb;">
                                {{ $maxVermogen ? berekenMinPerKm($maxVermogen) : '-' }}
                            </td>
                        @else
                            <td class="px-4 py-4 text-center font-semibold text-lg border-b border-gray-200" style="color: #2563eb;">
                                {{ $maxVermogen ? round($maxVermogen) : '-' }}
                            </td>
                            <td class="px-4 py-4 text-center font-semibold text-lg border-b border-gray-200" style="color: #2563eb;">
                                {{ ($maxVermogen && $gewicht) ? number_format($maxVermogen / $gewicht, 1) : '-' }}
                            </td>
                        @endif
                        
                        <td class="px-4 py-4 text-center font-semibold text-lg border-b border-gray-200" style="color: #dc2626;">
                            {{ $maxHartslag ? round($maxHartslag) : '-' }}
                        </td>
                        <td class="px-4 py-4 text-center font-semibold text-lg border-b border-gray-200" style="color: #16a34a;">
                            {{ $maxLactaat ? number_format($maxLactaat, 1) : '-' }}
                        </td>
                        <td class="px-4 py-4 text-center font-semibold text-lg border-b border-gray-200" style="color: #6b7280;">
                            100%
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
    
    {{-- Footer met uitleg --}}
    <div class="px-6 py-4 bg-gray-50 border-t-2" style="border-color: #c8e1eb;">
        <div class="text-xs text-gray-600">
            <p><strong>💡 Toelichting:</strong></p>
            <ul class="list-disc pl-5 mt-2 space-y-1">
                <li><strong>Aërobe drempel (LT1):</strong> Begin van lactaatophoping (~2 mmol/L), basis voor duurtraining</li>
                <li><strong>Anaërobe drempel (LT2):</strong> Snelle lactaatophoping (~4 mmol/L), maximaal steady-state tempo</li>
                <li><strong>%max:</strong> Percentage van maximale hartslag voor training intensiteit</li>
            </ul>
        </div>
    </div>
</div>
