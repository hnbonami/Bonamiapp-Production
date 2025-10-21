{{-- Trainingszones Tabel voor Inspanningstest Results --}}
{{-- Variabele sleutel: {{INSPANNINGSTEST_TRAININGSZONES}} --}}

@php
    // Bepaal testtype
    $testtype = $inspanningstest->testtype ?? 'fietstest';
    $isLooptest = str_contains($testtype, 'loop') || str_contains($testtype, 'lopen');
    $isZwemtest = str_contains($testtype, 'zwem');
    
    // Haal trainingszones op via relatie of decode JSON
    $trainingszones = $inspanningstest->trainingszones_data ?? [];
    
    // Check of trainingszones een string is (JSON) en decode indien nodig
    if (is_string($trainingszones)) {
        $trainingszones = json_decode($trainingszones, true) ?? [];
    }
    
    // Decode trainingszones JSON - zorg dat het een array is
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
    
    // Helper functie om decimale minuten naar mm:ss te converteren
    function formatMinPerKmDisplay($decimalMinutes) {
        if ($decimalMinutes >= 999 || !is_numeric($decimalMinutes)) return '∞';
        
        $minutes = floor($decimalMinutes);
        $seconds = round(($decimalMinutes - $minutes) * 60);
        
        return sprintf('%d:%02d', $minutes, $seconds);
    }
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
                        
                        // DEBUG: Log de zone naam
                        \Log::info("Zone {$index} naam: " . ($zone['naam'] ?? 'GEEN NAAM'));
                        \Log::info("Zone {$index} beschrijving: " . ($zone['beschrijving'] ?? 'GEEN BESCHRIJVING'));
                        
                        // Gebruik de echte naam uit de database
                        $zoneNaam = $zone['naam'] ?? ('Zone ' . ($index + 1));
                        $zoneBeschrijving = $zone['beschrijving'] ?? '';
                    @endphp
                    <tr class="border-b border-gray-200 hover:bg-opacity-80 transition-colors duration-150" style="background-color: {{ $zoneKleur }};">
                        {{-- Zone naam en beschrijving --}}
                        <td class="px-4 py-3 border-r border-gray-200">
                            <div class="font-bold text-sm text-gray-900">{{ $zoneNaam }}</div>
                            @if(!empty($zoneBeschrijving))
                                <div class="text-xs text-gray-600 mt-1">{{ $zoneBeschrijving }}</div>
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
                            @if($isZwemtest)
                                {{ isset($zone['minVermogen']) ? formatMinPerKmDisplay($zone['minVermogen']) : '-' }}
                            @elseif($isLooptest)
                                {{ isset($zone['minVermogen']) ? number_format($zone['minVermogen'], 1) : '-' }}
                            @else
                                {{ isset($zone['minVermogen']) ? round($zone['minVermogen']) : '-' }}
                            @endif
                        </td>
                        <td class="px-2 py-3 text-center text-sm font-semibold border-r border-gray-200" style="color: #2563eb;">
                            @if($isZwemtest)
                                {{ isset($zone['maxVermogen']) ? formatMinPerKmDisplay($zone['maxVermogen']) : '-' }}
                            @elseif($isLooptest)
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
    
    {{-- Toelichting Trainingszones --}}
    <div class="mx-6 mb-6 mt-4 p-6" style="background-color: #fff8e1;">
        <div class="flex items-start">
            <div class="flex-shrink-0 mr-4 text-2xl">
                💡
            </div>
            <div class="flex-1">
                <h4 class="text-base font-bold text-gray-900 mb-4">Uitleg Trainingszones</h4>
                <div class="text-sm text-gray-700">
                    @foreach($trainingszones as $index => $zone)
                        <div class="mb-6 pb-5 {{ $index < count($trainingszones) - 1 ? 'border-b border-gray-300' : '' }}">
                            <h5 class="font-bold text-gray-900 mb-2 uppercase tracking-wide" style="color: #1f2937;">{{ $zone['naam'] ?? '' }}</h5>
                            @php
                                $zoneName = strtolower($zone['naam'] ?? '');
                            @endphp
                            
                            @if(str_contains($zoneName, 'herstel') || str_contains($zoneName, 'recuperatie'))
                                <p class="leading-relaxed">
                                    In deze zone, ook wel de <strong>actieve recuperatie</strong> genoemd, worden de hersteltrainingen uitgevoerd, meestal na een wedstrijd of zware trainingsdag. 
                                    @if($isLooptest)
                                        Deze rustige loopsessies
                                    @elseif($isZwemtest)
                                        Deze rustige zwemsessies
                                    @else
                                        Deze rustige fietstochten
                                    @endif
                                    bevorderen het herstel maar verder is de intensiteit te laag om enig trainingseffect te veroorzaken. Focus op soepele bewegingen en volledig herstel.
                                </p>
                            @elseif(str_contains($zoneName, 'lange') && str_contains($zoneName, 'duur'))
                                <p class="leading-relaxed">
                                    Dit is de intensiteit waaraan je de <strong>(zeer) lange rustige duurtrainingen</strong> afwerkt 
                                    @if($isLooptest)
                                        (tot wel vijf uur en meer). Deze trainingen stimuleren het aërobe prestatievermogen en dienen om je basisconditie en loopeconomie te verbeteren.
                                    @elseif($isZwemtest)
                                        (lange afstanden in het water). Deze trainingen stimuleren het aërobe prestatievermogen en dienen om je zwemtechniek en basisconditie te verbeteren.
                                    @else
                                        (tot wel vijf uur en meer). Deze trainingen stimuleren het aërobe prestatievermogen en dienen om je basisconditie te verbeteren.
                                    @endif
                                    Je bouwt hier je uithoudingsvermogen op.
                                </p>
                            @elseif(str_contains($zoneName, 'extensieve') || str_contains($zoneName, 'extensief'))
                                <p class="leading-relaxed">
                                    Aan deze intensiteit werk je de <strong>snellere duurtrainingen</strong> af. 
                                    @if($isLooptest)
                                        Je loopt aan een vlot tempo waardoor je uithoudingsvermogen goed gestimuleerd wordt.
                                    @elseif($isZwemtest)
                                        Je zwemt aan een vlot tempo waardoor je uithoudingsvermogen goed gestimuleerd wordt.
                                    @else
                                        Je fietst aan een vlot tempo waardoor je uithoudingsvermogen goed gestimuleerd wordt.
                                    @endif
                                    De vetverbranding wordt sterk aangesproken. Dit is vaak de zone waarin je het meest zult trainen voor lange wedstrijden.
                                </p>
                            @elseif(str_contains($zoneName, 'intensieve') || str_contains($zoneName, 'intensief'))
                                <p class="leading-relaxed">
                                    Deze zone situeert zich <strong>tussen de aërobe en anaërobe drempel</strong>. De inspanning kan relatief lang worden volgehouden, zolang er voldoende energiereserves (koolhydraten) beschikbaar zijn. 
                                    @if($isLooptest)
                                        Het looptempo voelt lastig maar is nog vol te houden voor langere periodes.
                                    @elseif($isZwemtest)
                                        Het zwemtempo voelt lastig maar is nog vol te houden voor langere sets.
                                    @else
                                        Het vermogen voelt lastig maar is nog vol te houden voor langere periodes.
                                    @endif
                                    Gevoel is lastig. Deze zone verbetert je wedstrijdsnelheid op middellange afstanden.
                                </p>
                            @elseif(str_contains($zoneName, 'tempo'))
                                <p class="leading-relaxed">
                                    Training in deze zone belast ons <strong>aeroob systeem maximaal</strong>, tevens met een belangrijke bijdrage van de anaerobe stofwisseling afhankelijk van de individuele verhouding van spiervezeltypes. 
                                    De bedoeling is om de VO2max zelf, maar vooral ook het rendement van inspanning op 95-100% van de maximale zuurstofopname te verbeteren. 
                                    Er treedt geen lactaatevenwicht meer op waardoor je lichaam gaat verzuren. Dit type trainingen wordt altijd <strong>opgesplitst in blokjes tussen de 3 en 10min</strong> met telkens een herstelperiode ertussen (van bijv. 1-3min). 
                                    Deze training is zeer belastend en wordt dan ook enkel uitgevoerd in volledig uitgeruste status.
                                </p>
                            @elseif(str_contains($zoneName, 'weerstand') || str_contains($zoneName, 'maximaal'))
                                <p class="leading-relaxed">
                                    Dit is de zone voor de <strong>puur anaerobe training</strong>, met zeer korte intervallen meestal tussen 15 sec en 2 min. 
                                    Deze trainingen hebben de bedoeling om de weerstand tegen 'verzuring' te verbeteren, maar zijn maximaal belastend voor ons lichaam en vergen een extra lange recuperatie. 
                                    @if($isLooptest)
                                        Je sprint hier op maximale snelheid met zeer korte duur.
                                    @elseif($isZwemtest)
                                        Je zwemt hier op maximale intensiteit met zeer korte afstanden.
                                    @else
                                        Je trapt hier op maximaal vermogen met zeer korte duur.
                                    @endif
                                    Gezien de intensiteit van de inspanningen en de vrij korte duur van de intervallen is de hartfrequentie absoluut geen valabele parameter meer.
                                </p>
                            @else
                                <p class="leading-relaxed">{{ $zone['beschrijving'] ?? 'Training in deze zone draagt bij aan je algehele fitheid en prestatievermogen.' }}</p>
                            @endif
                        </div>
                    @endforeach
                    
                    <div class="mt-6 pt-5 border-t-2 border-gray-400">
                        <p class="font-bold text-gray-900 mb-3 text-base">💪 Praktisch gebruik</p>
                        <p class="leading-relaxed">
                            Gebruik deze zones om je trainingen gestructureerd op te bouwen. Train <strong>niet altijd in dezelfde zone</strong> - variatie is de sleutel tot vooruitgang. 
                            Bouw je trainingsweek op met voornamelijk trainingen in de lagere zones (herstel, lange duur, extensief) en voeg beperkt intensievere sessies toe (tempo, weerstand).
                            @if($isLooptest)
                                Let bij hardlopen extra op je loopvorm - bij vermoeidheid kan je techniek verslechteren.
                            @elseif($isZwemtest)
                                Let bij zwemmen extra op je techniek - bij vermoeidheid kan je houding in het water verslechteren.
                            @else
                                Let bij fietsen op je traptechniek en houding - bij vermoeidheid kan je efficiëntie afnemen.
                            @endif
                        </p>
                    </div>
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
