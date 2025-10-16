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
        
        {{-- Compacte uitleg per zone --}}
        <div class="rapport-zones-uitleg">
            <h4>💡 Korte Uitleg Trainingszones</h4>
            
            @foreach($trainingszones as $zone)
                @php
                    $zoneName = strtolower($zone['naam'] ?? '');
                @endphp
                <p style="margin: 6px 0;">
                    <strong>{{ $zone['naam'] ?? '' }}:</strong>
                    @if(str_contains($zoneName, 'herstel'))
                        Actieve recuperatie na zware trainingen. Bevordert herstel zonder trainingseffect.
                    @elseif(str_contains($zoneName, 'lange') && str_contains($zoneName, 'duur'))
                        Zeer lange rustige trainingen (tot 5u+). Bouwt basisconditie en uithoudingsvermogen op.
                    @elseif(str_contains($zoneName, 'extensieve') || str_contains($zoneName, 'extensief'))
                        Snellere duurtrainingen. Stimuleert uithoudingsvermogen en vetverbranding optimaal.
                    @elseif(str_contains($zoneName, 'intensieve') || str_contains($zoneName, 'intensief'))
                        Tussen aërobe en anaërobe drempel. Verbetert wedstrijdsnelheid op middellange afstand.
                    @elseif(str_contains($zoneName, 'tempo'))
                        Maximale aërobe belasting met anaërobe bijdrage. Intervallen van 3-10min. Zeer belastend.
                    @elseif(str_contains($zoneName, 'weerstand') || str_contains($zoneName, 'maximaal'))
                        Puur anaëroob (15sec-2min). Verbetert weerstand tegen verzuring. Maximaal belastend.
                    @else
                        {{ $zone['beschrijving'] ?? 'Training in deze zone verbetert je fitheid.' }}
                    @endif
                </p>
            @endforeach
            
            <p style="margin-top: 10px; padding-top: 8px; border-top: 1px solid #f59e0b;">
                <strong>💪 Praktisch:</strong> Train vooral in lagere zones (herstel, lange duur, extensief) en voeg beperkt intensieve sessies toe (tempo, maximaal). Variatie is de sleutel!
            </p>
        </div>
        
    @else
        <div style="text-align: center; padding: 20px; background: #fef3c7; border-radius: 4px;">
            <p style="color: #92400e; font-size: 10px;">⚠️ Geen trainingszones beschikbaar</p>
        </div>
    @endif
</div>