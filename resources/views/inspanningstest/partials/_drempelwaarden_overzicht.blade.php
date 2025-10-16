{{-- Drempelwaarden Overzicht Tabel voor Inspanningstest Results --}}
{{-- Variabele sleutel: {{INSPANNINGSTEST_DREMPELWAARDEN}} --}}

@php
    // Bepaal testtype
    $testtype = $inspanningstest->testtype ?? 'fietstest';
    $isLooptest = str_contains($testtype, 'loop') || str_contains($testtype, 'lopen');
    $isZwemtest = str_contains($testtype, 'zwem');
    
    // Haal testresultaten op via relatie of decode JSON
    $testresultaten = $inspanningstest->testresultaten ?? collect();
    
    // Check of testresultaten een string is (JSON) en decode indien nodig
    if (is_string($testresultaten)) {
        $testresultaten = json_decode($testresultaten, true) ?? [];
    }
    
    // Converteer naar array voor consistentie
    $testresultaten = is_array($testresultaten) ? $testresultaten : [];
    
    // BEREKEN snelheid voor veldtesten als die niet aanwezig is (exact zoals _testresultaten.blade.php)
    $isVeldtest = str_contains($testtype, 'veld');
    $testresultaten = array_map(function($stap) use ($isVeldtest, $isLooptest, $isZwemtest) {
        // Converteer naar array als het een object is
        $stap = is_array($stap) ? $stap : (array)$stap;
        
        // Als snelheid al aanwezig is, gebruik die
        if (isset($stap['snelheid']) && $stap['snelheid'] !== null && $stap['snelheid'] !== '') {
            return $stap;
        }
        
        // Voor veldtesten: bereken snelheid uit afstand en tijd
        if ($isVeldtest && ($isLooptest || $isZwemtest)) {
            $afstand = floatval($stap['afstand'] ?? 0); // in meters
            $tijdMin = floatval($stap['tijd_min'] ?? 0);
            $tijdSec = floatval($stap['tijd_sec'] ?? 0);
            $tijdUren = ($tijdMin + ($tijdSec / 60)) / 60; // converteer naar uren
            
            // Snelheid = afstand (km) / tijd (uren)
            if ($tijdUren > 0) {
                $stap['snelheid'] = ($afstand / 1000) / $tijdUren;
            }
        }
        
        return $stap;
    }, $testresultaten);
    
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
    
    // Bereken Watt/kg voor fietstesten
    $lt1WattPerKg = null;
    $lt2WattPerKg = null;
    $maxWattPerKg = null;
    
    if ($gewicht && $gewicht > 0) {
        if ($lt1Vermogen) {
            $lt1WattPerKg = $lt1Vermogen / $gewicht;
        }
        if ($lt2Vermogen) {
            $lt2WattPerKg = $lt2Vermogen / $gewicht;
        }
        if ($maxVermogen) {
            $maxWattPerKg = $maxVermogen / $gewicht;
        }
    }
    
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
                        @if($isLooptest)
                            Snelheid<br><span class="text-xs font-normal text-gray-600">%max</span>
                        @else
                            Vermogen<br><span class="text-xs font-normal text-gray-600">%max</span>
                        @endif
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
                            {{ $lt1WattPerKg ? number_format($lt1WattPerKg, 1) : '-' }}
                        </td>
                    @endif
                    
                    <td class="px-4 py-4 text-center font-semibold text-lg border-b border-gray-200" style="color: #dc2626;">
                        {{ $lt1Hartslag ? round($lt1Hartslag) : '-' }}
                    </td>
                    <td class="px-4 py-4 text-center font-semibold text-lg border-b border-gray-200" style="color: #16a34a;">
                        {{ $lt1Lactaat !== null ? number_format($lt1Lactaat, 1) : '-' }}
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
                            {{ $lt2WattPerKg ? number_format($lt2WattPerKg, 1) : '-' }}
                        </td>
                    @endif
                    
                    <td class="px-4 py-4 text-center font-semibold text-lg border-b border-gray-200" style="color: #dc2626;">
                        {{ $lt2Hartslag ? round($lt2Hartslag) : '-' }}
                    </td>
                    <td class="px-4 py-4 text-center font-semibold text-lg border-b border-gray-200" style="color: #16a34a;">
                        {{ $lt2Lactaat !== null ? number_format($lt2Lactaat, 1) : '-' }}
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
                                {{ $maxWattPerKg ? number_format($maxWattPerKg, 1) : '-' }}
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
    
    {{-- Uitgebreide Toelichting Drempelwaarden --}}
    <div class="mx-6 mb-6 mt-4 p-6" style="background-color: #fff8e1;">
        <div class="flex items-start">
            <div class="flex-shrink-0 mr-4 text-2xl">
                💡
            </div>
            <div class="flex-1">
                <h4 class="text-base font-bold text-gray-900 mb-3">Wat betekenen deze drempelwaarden?</h4>
                <div class="text-sm text-gray-700 space-y-3">
                    <p>
                        De drempelwaarden zijn de <strong>meest belangrijke uitkomsten</strong> van je inspanningstest. 
                        Ze geven de overgangsmomenten weer tussen verschillende energiesystemen in je lichaam en vormen de basis voor je trainingszones.
                    </p>
                    
                    <div class="mt-4 pt-4 border-t border-gray-300">
                        <p class="font-bold text-gray-900 mb-2">🔴 Aërobe drempel (LT1 - Lactate Threshold 1)</p>
                        <p>
                            Dit is het punt waarop je lichaam begint over te schakelen van pure <strong>aërobe energievoorziening</strong> (met zuurstof) 
                            naar een combinatie met anaërobe energieproductie. Bij deze intensiteit stijgt je lactaat licht boven het basisniveau, 
                            maar je lichaam kan het lactaat nog volledig afbreken.
                        </p>
                        <p class="mt-2">
                            <strong>Praktisch:</strong> 
                            @if($isLooptest)
                                Tot deze snelheid kun je <strong>zeer lang volhouden</strong> zonder moe te worden - denk aan lange duurlopen van meerdere uren. 
                                Deze snelheid voelt comfortabel aan en je kunt nog makkelijk een gesprek voeren tijdens het lopen.
                            @elseif($isZwemtest)
                                Tot dit tempo kun je <strong>zeer lang volhouden</strong> zonder moe te worden - denk aan lange duurtrainingen in het water. 
                                Dit tempo voelt comfortabel aan en je ademhaling blijft rustig en gecontroleerd.
                            @else
                                Tot dit vermogen kun je <strong>zeer lang volhouden</strong> zonder moe te worden - denk aan tochten van meerdere uren. 
                                Dit vermogen voelt comfortabel aan en je kunt nog makkelijk een gesprek voeren tijdens het fietsen.
                            @endif
                            Dit is je ideale intensiteit voor het opbouwen van je aerobe basis.
                        </p>
                    </div>
                    
                    <div class="mt-4 pt-4 border-t border-gray-300">
                        <p class="font-bold text-gray-900 mb-2">🟠 Anaërobe drempel (LT2 - Lactate Threshold 2)</p>
                        <p>
                            Dit is het punt waarop je lichaam <strong>meer lactaat produceert dan het kan afbreken</strong>. 
                            De lactaatwaarde stijgt hier snel. Boven deze drempel begin je te "verzuren" 
                            en kun je de intensiteit slechts beperkte tijd volhouden voordat je vertraagt door vermoeidheid.
                        </p>
                        <p class="mt-2">
                            <strong>Praktisch:</strong> 
                            @if($isLooptest)
                                Deze snelheid kun je ongeveer <strong>30-60 minuten volhouden</strong> bij een maximale inspanning. 
                                Het voelt "zwaar maar haalbaar" - denken aan een 10km wedstrijdtempo voor goed getrainde lopers, 
                                of een half marathon tempo voor zeer goed getrainde atleten. Je ademhaling is diep en regelmatig, 
                                maar praten wordt moeilijk.
                            @elseif($isZwemtest)
                                Dit tempo kun je ongeveer <strong>30-60 minuten volhouden</strong> bij een maximale inspanning. 
                                Het voelt "zwaar maar haalbaar" - je zwemtechniek blijft intact maar vergt meer concentratie. 
                                Je ademhaling is diep en krachtig.
                            @else
                                Dit vermogen kun je ongeveer <strong>30-60 minuten volhouden</strong> bij een maximale inspanning. 
                                Het voelt "zwaar maar haalbaar" - denk aan een tijdrit of bergrit tempo. 
                                Je ademhaling is diep en regelmatig, maar praten wordt moeilijk.
                            @endif
                            Dit is je maximale "steady state" intensiteit.
                        </p>
                    </div>
                    
                    <div class="mt-4 pt-4 border-t border-gray-300">
                        <p class="font-bold text-gray-900 mb-2">🔥 Maximum waarden</p>
                        <p>
                            Dit zijn de hoogste waarden die je tijdens de test hebt bereikt. 
                            @if($isLooptest)
                                Je maximale snelheid geeft aan wat je topsnelheid is in uitgeruste toestand.
                            @elseif($isZwemtest)
                                Je maximale tempo geeft aan wat je toptempo is in uitgeruste toestand.
                            @else
                                Je maximale vermogen geeft aan wat je piekprestatie is in uitgeruste toestand.
                            @endif
                            Het maximale lactaat toont hoe goed je lichaam met hoge lactaatwaarden kan omgaan.
                        </p>
                    </div>
                    
                    <div class="mt-4 pt-4 border-t border-gray-300">
                        <p class="font-bold text-gray-900 mb-2">📊 Kolom uitleg</p>
                        <ul class="list-disc pl-5 space-y-2 mt-2">
                            @if($isLooptest)
                                <li>
                                    <strong>Snelheid (km/h):</strong> Je loopsnelheid in kilometers per uur bij de drempel. 
                                    Dit is de meest directe waarde om je trainingssnelheden mee te bepalen.
                                </li>
                                <li>
                                    <strong>Tempo (min/km):</strong> Dezelfde snelheid uitgedrukt in minuten per kilometer - 
                                    dit is vaak praktischer voor lopers omdat het direct aangeeft hoeveel minuten je over een kilometer doet.
                                </li>
                            @elseif($isZwemtest)
                                <li>
                                    <strong>Snelheid (min/100m):</strong> Je zwemtempo in minuten per 100 meter. 
                                    Dit is de standaard manier om zwemsnelheid uit te drukken en direct bruikbaar voor je trainingen.
                                </li>
                            @else
                                <li>
                                    <strong>Vermogen (Watt):</strong> Het absolute vermogen dat je trapt bij de drempel. 
                                    Dit is de meest nauwkeurige maat voor je inspanning op de fiets.
                                </li>
                                <li>
                                    <strong>Vermogen (Watt/kg):</strong> Je vermogen gedeeld door je lichaamsgewicht. 
                                    Deze waarde is nuttig om jezelf te vergelijken met anderen of je eigen progressie te volgen bij gewichtsveranderingen. 
                                    Hoe hoger dit getal, hoe beter je klimvermogen bijvoorbeeld.
                                </li>
                            @endif
                            <li>
                                <strong>Hartslag (BPM):</strong> Je hartslag in slagen per minuut bij de drempel. 
                                Handig als je geen vermogensmeter
                                @if($isLooptest)
                                    of GPS-horloge hebt - je kunt dan trainen op hartslagzones.
                                @else
                                    hebt - je kunt dan trainen op hartslagzones.
                                @endif
                                Let op: hartslag kan variëren door warmte, vermoeidheid, stress en cafeïne.
                            </li>
                            <li>
                                <strong>Lactaat (mmol/L):</strong> De hoeveelheid lactaat (melkzuur) in je bloed bij de drempel, 
                                gemeten in millimol per liter. Dit getal vertelt hoe je lichaam reageert op de inspanning en 
                                is een objectieve maat voor je metabolisme.
                            </li>
                            <li>
                                <strong>%max:</strong> Het percentage van je maximale 
                                @if($isLooptest)
                                    snelheid of maximale hartslag.
                                @elseif($isZwemtest)
                                    tempo of maximale hartslag.
                                @else
                                    vermogen of maximale hartslag.
                                @endif
                                Dit helpt om te zien in welke verhouding je drempels tot je maximum staan. 
                                Bij goed getrainde atleten ligt LT2 vaak rond 85-95% van het maximum.
                            </li>
                        </ul>
                    </div>
                    
                    <p class="mt-4 pt-4 border-t-2 border-gray-400">
                        <strong>💪 Gebruik deze waarden:</strong> 
                        De drempelwaarden uit deze tabel zijn de basis voor je <strong>persoonlijke trainingszones</strong>. 
                        @if($isLooptest)
                            Gebruik de snelheid of hartslag om je looptrainingen correct in te stellen.
                        @elseif($isZwemtest)
                            Gebruik het tempo of hartslag om je zwemtrainingen correct in te stellen.
                        @else
                            Gebruik het vermogen of hartslag om je fietstrainingen correct in te stellen.
                        @endif
                        Vergelijk deze waarden bij een volgende test om je vooruitgang objectief te meten. 
                        Let vooral op verschuivingen van LT1 en LT2 - als deze hoger worden, word je fitter!
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
