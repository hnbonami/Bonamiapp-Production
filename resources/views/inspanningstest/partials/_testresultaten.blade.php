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
        border-collapse: separate;
        border-spacing: 0;
        margin: 15px 0;
        background: white;
        border: 3px solid #c8e1eb;
        border-radius: 8px;
        overflow: hidden;
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
        border-bottom: 2px solid #c8e1eb;
        border-right: 1px solid #e5e7eb;
    }
    
    .testresultaten-table th:last-child {
        border-right: none;
    }
    
    .testresultaten-table td {
        padding: 8px 12px;
        font-size: 11px;
        color: #0a152dff;
        border-bottom: 1px solid #e5e7eb;
        border-right: 1px solid #e5e7eb;
        text-align: center;
    }
    
    .testresultaten-table td:last-child {
        border-right: none;
    }
    
    .testresultaten-table tbody tr:last-child td {
        border-bottom: none;
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
        background: #f4e8c0ff;
        border-left: 4px solid #f5b444ff;
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
    
    // Bereken cumulatieve tijd voor veldtesten als 'tijd' veld niet bestaat
    if ($isVeldtest) {
        $cumulatiefTijd = 0;
        $resultaten = array_map(function($stap) use (&$cumulatiefTijd) {
            // Als 'tijd' al bestaat, gebruik die
            if (!isset($stap['tijd']) || $stap['tijd'] === null || $stap['tijd'] === '') {
                $tijdMin = floatval($stap['tijd_min'] ?? 0);
                $tijdSec = floatval($stap['tijd_sec'] ?? 0);
                $cumulatiefTijd += $tijdMin + ($tijdSec / 60);
                $stap['tijd'] = round($cumulatiefTijd);
            }
            return $stap;
        }, $resultaten);
    }
    
    // Bepaal kolomlabels op basis van testtype
    $vermogenLabel = $isLooptest ? 'Snelheid<br><span style="font-size: 9px; font-weight: normal;">(km/h)</span>' : 
                      ($isZwemtest ? 'Tempo<br><span style="font-size: 9px; font-weight: normal;">(mm:ss)</span>' : 
                       'Vermogen<br><span style="font-size: 9px; font-weight: normal;">(W)</span>');
    
    // Voor veldtest lopen: toon afstand in plaats van tijd
    $eersteKolomLabel = ($isVeldtest && $isLooptest) ? 'Afstand<br><span style="font-size: 9px; font-weight: normal;">(m)</span>' : 
                        'Tijd<br><span style="font-size: 9px; font-weight: normal;">(min)</span>';
@endphp

<div class="rapport-testresultaten">
    <h3>📋 Testresultaten</h3>
    <p style="font-size: 10px; color: #6b7280; margin: 8px 0;">Gemeten waarden per stap tijdens de inspanningstest. Elk blok vertegenwoordigt een intensiteitsniveau.</p>
    
    @if(is_array($resultaten) && count($resultaten) > 0)
        <table class="testresultaten-table">
            <thead>
                <tr>
                    <th>{!! $eersteKolomLabel !!}</th>
                    <th>{!! $vermogenLabel !!}</th>
                    <th>Hartslag<br><span style="font-size: 9px; font-weight: normal;">(bpm)</span></th>
                    <th>Lactaat<br><span style="font-size: 9px; font-weight: normal;">(mmol/L)</span></th>
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
                        <td>{{ ($isVeldtest && $isLooptest) ? ($stap['afstand'] ?? '-') : ($stap['tijd'] ?? '-') }}</td>
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
            <p style="margin-top: 8px;">
                <strong>Hartslag:</strong> De hartslag (in slagen per minuut) geeft aan hoe hard je hart werkt bij elke intensiteit.
            </p>
            <p>
                <strong>Lactaat:</strong> Het lactaatgehalte (in mmol/L) toont hoeveel melkzuur er in je bloed aanwezig is bij bepaalde belasting. 
                @if($isLooptest)
                    Bij lage snelheden blijft lactaat laag (aëroob). Bij hoge snelheden stijgt lactaat snel (anaëroob).
                @elseif($isZwemtest)
                    Bij lage intensiteit blijft lactaat laag (aëroob). Bij hoge intensiteit stijgt lactaat snel (anaëroob).
                @else
                    Bij laag vermogen blijft lactaat laag (aëroob). Bij hoog vermogen stijgt lactaat snel (anaëroob).
                @endif
            </p>
            <p style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #fbbf24;">
                <strong>Doel van de test:</strong> Het bepalen van de aërobe en anaërobe drempels. 
                @if($isLooptest)
                    Door de combinatie van snelheid, hartslag en melkzuurproductie kunnen we je optimale trainingssnelheden bepalen.
                @elseif($isZwemtest)
                    Door de combinatie van zwemtijden, hartslag en lactaatniveaus kunnen we je optimale trainingsintensiteiten bepalen.
                @else
                    Door de combinatie van vermogen, hartslag en melkzuurproductie kunnen we je optimale trainingszones bepalen.
                @endif
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