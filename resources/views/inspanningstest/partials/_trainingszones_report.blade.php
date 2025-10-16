{{-- Trainingszones - Rapport Versie (Print-ready) --}}
{{-- Variabele sleutel: {{INSPANNINGSTEST_TRAININGSZONES}} --}}

<style>
    .rapport-trainingszones {
        font-family: Tahoma, Arial, sans-serif;
        font-size: 11px;
        line-height: 1.4;
        color: #1f2937;
        margin: 20px 0;
        width: 120%;
    }
    
    .rapport-trainingszones h3 {
        font-size: 14px;
        font-weight: 700;
        color: #0f4c75;
        margin: 15px 0 10px 0;
        padding: 8px 10px;
        background-color: #c8e1eb;
        border-left: 4px solid #0f4c75;
    }
    
    .trainingszones-table {
        width: 100%;
        border-collapse: collapse;
        margin: 10px 0;
        background: white;
    }
    
    .trainingszones-table thead {
        background-color: #e3f2fd;
    }
    
    .trainingszones-table th {
        padding: 5px 6px;
        text-align: center;
        font-weight: 700;
        font-size: 9px;
        color: #374151;
        border-bottom: 2px solid #c8e1eb;
    }
    
    .trainingszones-table td {
        padding: 4px 6px;
        font-size: 10px;
        color: #1f2937;
        border-bottom: 1px solid #e5e7eb;
        text-align: center;
    }
    
    .zone-naam-cel {
        text-align: left !important;
        font-weight: 700;
        padding-right: 8px;
    }
    
    .rapport-zones-uitleg {
        margin: 15px 0;
        padding: 10px 12px;
        background: #fff8e1;
        border-left: 4px solid #f59e0b;
        font-size: 9px;
        line-height: 1.5;
        color: #78350f;
    }
    
    .rapport-zones-uitleg h4 {
        font-size: 10px;
        font-weight: 700;
        color: #92400e;
        margin: 0 0 6px 0;
    }
    
    .rapport-zones-uitleg p {
        margin: 5px 0;
        color: #78350f;
    }
</style>

@php
    // Bepaal testtype
    $testtype = $inspanningstest->testtype ?? 'fietstest';
    $isLooptest = str_contains($testtype, 'loop') || str_contains($testtype, 'lopen');
    $isZwemtest = str_contains($testtype, 'zwem');
    
    // Haal trainingszones op
    $trainingszones = $inspanningstest->trainingszones_data ?? [];
    if (is_string($trainingszones)) {
        $trainingszones = json_decode($trainingszones, true) ?? [];
    }
    $trainingszones = is_array($trainingszones) ? $trainingszones : [];
    
    // Bepaal eenheid label
    $eenheidLabel = 'Watt';
    if ($isLooptest) {
        $eenheidLabel = 'km/h';
    } elseif ($isZwemtest) {
        $eenheidLabel = 'min/100m';
    }
    
    // Helper functie voor min/km formattering
    function formatMinPerKmCompact($decimalMinutes) {
        if ($decimalMinutes >= 999 || !is_numeric($decimalMinutes)) return '∞';
        $minutes = floor($decimalMinutes);
        $seconds = round(($decimalMinutes - $minutes) * 60);
        return sprintf('%d:%02d', $minutes, $seconds);
    }
@endphp

<div class="rapport-trainingszones">
    <h3>🎯 Trainingszones</h3>
    <p style="font-size: 10px; color: #6b7280; margin: 8px 0;">Persoonlijke trainingszones op basis van gemeten drempelwaarden</p>
    
    @if(count($trainingszones) > 0)
        <table class="trainingszones-table">
            <thead>
                <tr>
                    <th rowspan="2" style="border-right: 1px solid #c8e1eb;">Zone</th>
                    <th colspan="2" style="border-right: 1px solid #c8e1eb;">Hartslag (bpm)</th>
                    <th colspan="2" style="border-right: 1px solid #c8e1eb;">{{ $eenheidLabel }}</th>
                    @if($isLooptest)
                        <th colspan="2" style="border-right: 1px solid #c8e1eb;">min/km</th>
                    @endif
                    <th rowspan="2">Borg</th>
                </tr>
                <tr style="background-color: #f0f9ff;">
                    <th style="border-right: 1px solid #e5e7eb;">min</th>
                    <th style="border-right: 1px solid #c8e1eb;">max</th>
                    <th style="border-right: 1px solid #e5e7eb;">min</th>
                    <th style="border-right: 1px solid #c8e1eb;">max</th>
                    @if($isLooptest)
                        <th style="border-right: 1px solid #e5e7eb;">min</th>
                        <th style="border-right: 1px solid #c8e1eb;">max</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($trainingszones as $zone)
                    @php
                        $minMinPerKm = null;
                        $maxMinPerKm = null;
                        if ($isLooptest && isset($zone['maxVermogen']) && isset($zone['minVermogen'])) {
                            $minMinPerKm = $zone['maxVermogen'] > 0 ? (60 / $zone['maxVermogen']) : null;
                            $maxMinPerKm = $zone['minVermogen'] > 0 ? (60 / $zone['minVermogen']) : null;
                        }
                        
                        $borgText = isset($zone['borgMin']) && isset($zone['borgMax']) 
                            ? $zone['borgMin'] . '-' . $zone['borgMax'] 
                            : '-';
                        
                        $zoneKleur = $zone['kleur'] ?? '#FFFFFF';
                    @endphp
                    <tr style="background-color: {{ $zoneKleur }};">
                        <td class="zone-naam-cel">{{ $zone['naam'] ?? '-' }}</td>
                        <td style="color: #dc2626; font-weight: 600; border-right: 1px solid #e5e7eb;">
                            {{ isset($zone['minHartslag']) ? round($zone['minHartslag']) : '-' }}
                        </td>
                        <td style="color: #dc2626; font-weight: 600; border-right: 1px solid #c8e1eb;">
                            {{ isset($zone['maxHartslag']) ? round($zone['maxHartslag']) : '-' }}
                        </td>
                        <td style="color: #2563eb; font-weight: 600; border-right: 1px solid #e5e7eb;">
                            @if($isZwemtest)
                                {{ isset($zone['minVermogen']) ? formatMinPerKmCompact($zone['minVermogen']) : '-' }}
                            @elseif($isLooptest)
                                {{ isset($zone['minVermogen']) ? number_format($zone['minVermogen'], 1) : '-' }}
                            @else
                                {{ isset($zone['minVermogen']) ? round($zone['minVermogen']) : '-' }}
                            @endif
                        </td>
                        <td style="color: #2563eb; font-weight: 600; border-right: 1px solid #c8e1eb;">
                            @if($isZwemtest)
                                {{ isset($zone['maxVermogen']) ? formatMinPerKmCompact($zone['maxVermogen']) : '-' }}
                            @elseif($isLooptest)
                                {{ isset($zone['maxVermogen']) ? number_format($zone['maxVermogen'], 1) : '-' }}
                            @else
                                {{ isset($zone['maxVermogen']) ? round($zone['maxVermogen']) : '-' }}
                            @endif
                        </td>
                        @if($isLooptest)
                            <td style="color: #6b7280; border-right: 1px solid #e5e7eb;">
                                {{ $minMinPerKm !== null ? formatMinPerKmCompact($minMinPerKm) : '-' }}
                            </td>
                            <td style="color: #6b7280; border-right: 1px solid #c8e1eb;">
                                {{ $maxMinPerKm !== null ? formatMinPerKmCompact($maxMinPerKm) : '-' }}
                            </td>
                        @endif
                        <td style="color: #6b7280;">{{ $borgText }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        
        {{-- Uitgebreide uitleg per zone --}}
        <div class="rapport-zones-uitleg">
            <h4>💡 Uitleg Trainingszones</h4>
            
            @foreach($trainingszones as $index => $zone)
                @php
                    $zoneName = strtolower($zone['naam'] ?? '');
                @endphp
                <div style="margin: 8px 0; padding-bottom: 8px; {{ $index < count($trainingszones) - 1 ? 'border-bottom: 1px solid #e5e7eb;' : '' }}">
                    <p style="margin: 0 0 4px 0;"><strong style="text-transform: uppercase;">{{ $zone['naam'] ?? '' }}</strong></p>
                    
                    @if(str_contains($zoneName, 'herstel') || str_contains($zoneName, 'recuperatie'))
                        <p style="margin: 4px 0;">
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
                        <p style="margin: 4px 0;">
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
                        <p style="margin: 4px 0;">
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
                        <p style="margin: 4px 0;">
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
                        <p style="margin: 4px 0;">
                            Training in deze zone belast ons <strong>aeroob systeem maximaal</strong>, tevens met een belangrijke bijdrage van de anaerobe stofwisseling afhankelijk van de individuele verhouding van spiervezeltypes. 
                            De bedoeling is om de VO2max zelf, maar vooral ook het rendement van inspanning op 95-100% van de maximale zuurstofopname te verbeteren. 
                            Er treedt geen lactaatevenwicht meer op waardoor je lichaam gaat verzuren. Dit type trainingen wordt altijd <strong>opgesplitst in blokjes tussen de 3 en 10min</strong> met telkens een herstelperiode ertussen (van bijv. 1-3min). 
                            Deze training is zeer belastend en wordt dan ook enkel uitgevoerd in volledig uitgeruste status.
                        </p>
                    @elseif(str_contains($zoneName, 'weerstand') || str_contains($zoneName, 'maximaal'))
                        <p style="margin: 4px 0;">
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
                        <p style="margin: 4px 0;">{{ $zone['beschrijving'] ?? 'Training in deze zone draagt bij aan je algehele fitheid en prestatievermogen.' }}</p>
                    @endif
                </div>
            @endforeach
            
            <p style="margin-top: 10px; padding-top: 8px; border-top: 1px solid #f59e0b; font-weight: 600;">
                💪 Praktisch gebruik
            </p>
            <p style="margin: 4px 0;">
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
        
    @else
        <div style="text-align: center; padding: 20px; background: #fef3c7; border-radius: 4px;">
            <p style="color: #92400e; font-size: 10px;">⚠️ Geen trainingszones beschikbaar</p>
        </div>
    @endif
</div>