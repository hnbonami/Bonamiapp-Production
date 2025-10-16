{{-- Testresultaten Tabel - Rapport Versie (Print-ready) --}}
{{-- Variabele sleutel: {{INSPANNINGSTEST_TESTRESULTATEN}} --}}

<style>
    .rapport-testresultaten {
        font-family: Tahoma, Arial, sans-serif;
        font-size: 11px;
        line-height: 1.4;
        color: #0a152dff;
        margin: 20px 0;
        width: 120%;
    }
    
    .rapport-testresultaten h3 {
        font-size: 14px;
        font-weight: 700;
        color: #0a152dff;
        margin: 15px 0 10px 0;
        padding: 8px 10px;
        background-color: #c8e1eb;
        border-left: 4px solid #0f4c75;
    }
    
    .testresultaten-table {
        width: 100%;
        border-collapse: collapse;
        margin: 15px 0;
        background: white;
        border: 1px solid #d1d5db;
    }
    
    .testresultaten-table thead {
        background-color: #e3f2fd;
    }
    
    .testresultaten-table th {
        padding: 10px 12px;
        text-align: center;
        font-weight: 700;
        font-size: 11px;
        color: #0a152dff;
        border: 1px solid #c8e1eb;
    }
    
    .testresultaten-table td {
        padding: 8px 12px;
        font-size: 11px;
        color: #0a152dff;
        border: 1px solid #e5e7eb;
        text-align: center;
    }
    
    .testresultaten-table tbody tr:nth-child(even) {
        background-color: #f9fafb;
    }
    
    .testresultaten-table tbody tr:hover {
        background-color: #f3f4f6;
    }
    
    .stap-highlight {
        background-color: #fef3c7 !important;
        font-weight: 600;
    }
    
    .rapport-toelichting-box {
        margin: 15px 0;
        padding: 12px 15px;
        background: #fff8e1;
        border-left: 4px solid #f59e0b;
        font-size: 10px;
        line-height: 1.6;
        color: #78350f;
    }
    
    .rapport-toelichting-box h4 {
        font-size: 11px;
        font-weight: 700;
        color: #92400e;
        margin: 0 0 8px 0;
    }
</style>

@php
    // Bepaal testtype
    $testtype = $inspanningstest->testtype ?? 'fietstest';
    $isLooptest = str_contains($testtype, 'loop') || str_contains($testtype, 'lopen');
    $isZwemtest = str_contains($testtype, 'zwem');
    $isVeldtest = str_contains($testtype, 'veld');
    
    // Haal testresultaten op via relatie of decode JSON
    $resultaten = $inspanningstest->testresultaten ?? [];
    
    // Check of testresultaten een string is (JSON) en decode indien nodig
    if (is_string($resultaten)) {
        $resultaten = json_decode($resultaten, true) ?? [];
    }
    
    // BEREKEN snelheid voor veldtesten als die niet aanwezig is
    $resultaten = array_map(function($stap) use ($isVeldtest, $isLooptest, $isZwemtest) {
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
    }, $resultaten);
    
    // Bepaal kolomlabels op basis van testtype
    $vermogenLabel = $isLooptest ? 'Snelheid<br><span style="font-size: 9px; font-weight: normal;">(km/h)</span>' : 
                      ($isZwemtest ? 'Tempo<br><span style="font-size: 9px; font-weight: normal;">(mm:ss)</span>' : 
                       'Vermogen<br><span style="font-size: 9px; font-weight: normal;">(W)</span>');
@endphp

<div class="rapport-testresultaten">
    <h3>📋 Testresultaten</h3>
    <p style="font-size: 10px; color: #6b7280; margin: 8px 0;">Gemeten waarden per stap tijdens de inspanningstest. Elk blok vertegenwoordigt een intensiteitsniveau.</p>
    
    @if(is_array($resultaten) && count($resultaten) > 0)
        <table class="testresultaten-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Tijd<br><span style="font-size: 9px; font-weight: normal;">(min)</span></th>
                    <th>{!! $vermogenLabel !!}</th>
                    <th>Hartslag<br><span style="font-size: 9px; font-weight: normal;">(bpm)</span></th>
                    <th>Lactaat<br><span style="font-size: 9px; font-weight: normal;">(mmol/L)</span></th>
                    @if(!$isLooptest && !$isZwemtest)
                        <th>RPM</th>
                    @endif
                    <th>Borg</th>
                </tr>
            </thead>
            <tbody>
                @foreach($resultaten as $index => $resultaat)
                    @php
                        // Converteer naar array als het een object is
                        $stap = is_array($resultaat) ? $resultaat : (array)$resultaat;
                        
                        // Check of deze stap een drempelwaarde bevat
                        $isLT1Stap = false;
                        $isLT2Stap = false;
                        
                        if ($isLooptest || $isZwemtest) {
                            $stapWaarde = $stap['snelheid'] ?? 0;
                            $isLT1Stap = abs($stapWaarde - ($inspanningstest->aerobe_drempel_snelheid ?? 0)) < 0.1;
                            $isLT2Stap = abs($stapWaarde - ($inspanningstest->anaerobe_drempel_snelheid ?? 0)) < 0.1;
                        } else {
                            $stapWaarde = $stap['vermogen'] ?? 0;
                            $isLT1Stap = abs($stapWaarde - ($inspanningstest->aerobe_drempel_vermogen ?? 0)) < 1;
                            $isLT2Stap = abs($stapWaarde - ($inspanningstest->anaerobe_drempel_vermogen ?? 0)) < 1;
                        }
                        
                        $highlightClass = ($isLT1Stap || $isLT2Stap) ? 'stap-highlight' : '';
                    @endphp
                    <tr class="{{ $highlightClass }}">
                        <td><strong>{{ $index + 1 }}</strong></td>
                        <td>{{ $stap['tijd'] ?? '-' }}</td>
                        <td style="color: #2563eb; font-weight: 600;">
                            @if($isLooptest)
                                {{ number_format($stap['snelheid'] ?? 0, 1, ',', '.') }}
                            @elseif($isZwemtest)
                                @php
                                    $tempo = $stap['snelheid'] ?? 0;
                                    $minuten = floor($tempo);
                                    $seconden = round(($tempo - $minuten) * 60);
                                @endphp
                                {{ $minuten }}:{{ str_pad($seconden, 2, '0', STR_PAD_LEFT) }}
                            @else
                                {{ number_format($stap['vermogen'] ?? 0, 0, ',', '.') }}
                            @endif
                        </td>
                        <td style="color: #dc2626; font-weight: 600;">{{ $stap['hartslag'] ?? '-' }}</td>
                        <td style="color: #16a34a; font-weight: 600;">{{ number_format($stap['lactaat'] ?? 0, 1, ',', '.') }}</td>
                        @if(!$isLooptest && !$isZwemtest)
                            <td>{{ $stap['rpm'] ?? '-' }}</td>
                        @endif
                        <td>
                            @if($isLT1Stap)
                                <span style="color: #dc2626; font-weight: 700;">🔴 LT1 (Aëroob)</span>
                            @elseif($isLT2Stap)
                                <span style="color: #f59e0b; font-weight: 700;">🟠 LT2 (Anaëroob)</span>
                            @else
                                {{ $stap['borg'] ?? '-' }}
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        
        <div style="text-align: right; font-size: 10px; color: #6b7280; margin: 10px 0;">
            <strong>Totaal aantal metingen:</strong> {{ count($resultaten) }}
        </div>
        
        {{-- Toelichting --}}
        <div class="rapport-toelichting-box">
            <h4>💡 Hoe lees je deze tabel?</h4>
            <p>
                Tijdens de looptest wordt de snelheid geleidelijk verhoogd per stap. Aan het einde van elke stap worden hartslag, snelheid en melkzuurproductie geregistreerd.
            </p>
            <p style="margin-top: 8px;">
                <strong>Hartslag:</strong> De hartslag (in slagen per minuut) geeft aan hoe hard je hart werkt bij elke intensiteit.
            </p>
            <p>
                <strong>Lactaat:</strong> Het lactaatgehalte (in mmol/L) toont hoeveel melkzuur er in je bloed aanwezig is bij bepaalde belasting. 
                Bij lage snelheden blijft lactaat laag (aëroob). Bij hoge snelheden stijgt lactaat snel (anaëroob).
            </p>
            <p style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #fbbf24;">
                <strong>Doel van de test:</strong> Het bepalen van de aërobe en anaërobe drempels. Door de combinatie van snelheid, hartslag en melkzuurproductie kunnen we je optimale trainingszones bepalen.
            </p>
        </div>
    @else
        <div style="text-align: center; padding: 30px 20px; background: #f9fafb; border-radius: 8px; border: 1px dashed #d1d5db;">
            <div style="font-size: 32px; margin-bottom: 10px;">📋</div>
            <p style="color: #6b7280; font-size: 11px; font-weight: 600;">Geen testresultaten beschikbaar</p>
            <p style="color: #9ca3af; font-size: 9px; margin-top: 5px;">Er zijn nog geen meetwaarden ingevoerd voor deze inspanningstest.</p>
        </div>
    @endif
</div>