{{-- Testresultaten Tabel - Rapport Versie (Print-ready) --}}
{{-- Variabele sleutel: {{INSPANNINGSTEST_TESTRESULTATEN}} --}}

<style>
    .rapport-testresultaten {
        font-family: Tahoma, Arial, sans-serif;
        font-size: 11px;
        line-height: 1.4;
        color: #1f2937;
        margin: 20px 0;
        width: 120%;
    }
    
    .rapport-testresultaten h3 {
        font-size: 14px;
        font-weight: 700;
        color: #0f4c75;
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
        border: 1px solid #e5e7eb;
    }
    
    .testresultaten-table thead {
        background-color: #f3f4f6;
    }
    
    .testresultaten-table th {
        padding: 8px 10px;
        text-align: left;
        font-weight: 700;
        font-size: 10px;
        color: #374151;
        border-bottom: 2px solid #d1d5db;
    }
    
    .testresultaten-table td {
        padding: 6px 10px;
        font-size: 10px;
        color: #1f2937;
        border-bottom: 1px solid #e5e7eb;
    }
    
    .testresultaten-table tbody tr:hover {
        background-color: #f9fafb;
    }
    
    .testresultaten-table tbody tr:last-child td {
        border-bottom: none;
    }
    
    .testresultaten-table .text-right {
        text-align: right;
    }
    
    .testresultaten-table .text-center {
        text-align: center;
    }
    
    .rapport-testresultaten-toelichting {
        margin: 15px 0;
        padding: 12px 15px;
        background: #f0f9ff;
        border-left: 4px solid #3b82f6;
        font-size: 10px;
        line-height: 1.6;
        color: #1e40af;
    }
    
    .rapport-testresultaten-toelichting h4 {
        font-size: 11px;
        font-weight: 700;
        color: #1e3a8a;
        margin: 0 0 8px 0;
    }
    
    .rapport-geen-data {
        text-align: center;
        padding: 30px 20px;
        color: #9ca3af;
        font-style: italic;
    }
    
    .stap-highlight {
        background-color: #fef3c7 !important;
        font-weight: 600;
    }
</style>

@php
    // Bepaal testtype
    $testtype = $inspanningstest->testtype ?? 'fietstest';
    $isLooptest = str_contains($testtype, 'loop') || str_contains($testtype, 'lopen');
    $isZwemtest = str_contains($testtype, 'zwem');
    $isVeldtest = str_contains($testtype, 'veld');
    
    // Haal testresultaten op via relatie of decode JSON
    $resultaten = $inspanningstest->testresultaten ?? collect();
    
    // Check of testresultaten een string is (JSON) en decode indien nodig
    if (is_string($resultaten)) {
        $resultaten = json_decode($resultaten) ?? [];
    }
    
    // Converteer naar collection voor consistentie
    $resultaten = collect($resultaten);
    
    // Bepaal kolomlabels op basis van testtype
    $vermogenLabel = $isLooptest ? 'Snelheid (km/h)' : ($isZwemtest ? 'Tempo (mm:ss)' : 'Vermogen (W)');
@endphp

<div class="rapport-testresultaten">
    <h3>📋 Testresultaten per Stap</h3>
    
    @if($resultaten && count($resultaten) > 0)
        <table class="testresultaten-table">
            <thead>
                <tr>
                    <th class="text-center">Stap</th>
                    <th class="text-right">{{ $vermogenLabel }}</th>
                    <th class="text-right">Hartslag (bpm)</th>
                    <th class="text-right">Lactaat (mmol/L)</th>
                    @if(!$isLooptest && !$isZwemtest)
                        <th class="text-right">RPM</th>
                    @endif
                    <th>Opmerkingen</th>
                </tr>
            </thead>
            <tbody>
                @foreach($resultaten as $index => $resultaat)
                    @php
                        // Check of deze stap een drempelwaarde bevat
                        $isLT1Stap = false;
                        $isLT2Stap = false;
                        
                        if ($isLooptest || $isZwemtest) {
                            $isLT1Stap = abs(($resultaat->snelheid ?? 0) - ($inspanningstest->aerobe_drempel_snelheid ?? 0)) < 0.1;
                            $isLT2Stap = abs(($resultaat->snelheid ?? 0) - ($inspanningstest->anaerobe_drempel_snelheid ?? 0)) < 0.1;
                        } else {
                            $isLT1Stap = abs(($resultaat->vermogen ?? 0) - ($inspanningstest->aerobe_drempel_vermogen ?? 0)) < 1;
                            $isLT2Stap = abs(($resultaat->vermogen ?? 0) - ($inspanningstest->anaerobe_drempel_vermogen ?? 0)) < 1;
                        }
                        
                        $highlightClass = ($isLT1Stap || $isLT2Stap) ? 'stap-highlight' : '';
                    @endphp
                    <tr class="{{ $highlightClass }}">
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="text-right">
                            @if($isLooptest)
                                {{ number_format($resultaat->snelheid ?? $resultaat->vermogen ?? 0, 1, ',', '.') }}
                            @elseif($isZwemtest)
                                @php
                                    $tempo = $resultaat->snelheid ?? $resultaat->vermogen ?? 0;
                                    $minuten = floor($tempo);
                                    $seconden = round(($tempo - $minuten) * 60);
                                @endphp
                                {{ $minuten }}:{{ str_pad($seconden, 2, '0', STR_PAD_LEFT) }}
                            @else
                                {{ number_format($resultaat->vermogen ?? 0, 0, ',', '.') }}
                            @endif
                        </td>
                        <td class="text-right">{{ $resultaat->hartslag ?? '-' }}</td>
                        <td class="text-right">{{ number_format($resultaat->lactaat ?? 0, 1, ',', '.') }}</td>
                        @if(!$isLooptest && !$isZwemtest)
                            <td class="text-right">{{ $resultaat->rpm ?? '-' }}</td>
                        @endif
                        <td>
                            @if($isLT1Stap)
                                <span style="color: #dc2626; font-weight: 600;">🔴 LT1 (Aëroob)</span>
                            @endif
                            @if($isLT2Stap)
                                <span style="color: #f59e0b; font-weight: 600;">🟠 LT2 (Anaëroob)</span>
                            @endif
                            {{ $resultaat->opmerkingen ?? '' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        
        {{-- Toelichting --}}
        <div class="rapport-testresultaten-toelichting">
            <h4>📊 Leeswijzer Testresultaten</h4>
            <p>
                Deze tabel toont de <strong>gemeten waarden per teststap</strong>. Elk blok vertegenwoordigt een intensiteitsniveau tijdens de test.
            </p>
            <p>
                <strong>Hartslag:</strong> De hartslag (in slagen per minuut) geeft aan hoe hard je cardiovasculaire systeem werkt bij elke intensiteit.
            </p>
            <p>
                <strong>Lactaat:</strong> Het lactaatgehalte (in mmol/L) toont hoeveel melkzuur er in je bloed aanwezig is. 
                Lage waarden (&lt; 2 mmol/L) duiden op aëroob werk, hogere waarden op anaëroob werk.
            </p>
            @if(!$isLooptest && !$isZwemtest)
            <p>
                <strong>RPM:</strong> Het aantal rotaties per minuut (cadans) waarmee je trapt. Dit helpt bij het evalueren van je trapefficiëntie.
            </p>
            @endif
            <p style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #93c5fd;">
                <strong style="background-color: #fef3c7; padding: 2px 6px; border-radius: 3px;">Geel gemarkeerde rijen</strong> 
                tonen de stappen waar je drempelwaarden (LT1 of LT2) zijn bereikt.
            </p>
        </div>
        
    @else
        {{-- Geen testresultaten --}}
        <div class="rapport-geen-data">
            <div style="font-size: 32px; margin-bottom: 10px;">📋</div>
            <p style="color: #6b7280; font-size: 11px;">Geen testresultaten beschikbaar</p>
            <p style="color: #9ca3af; font-size: 9px; margin-top: 5px;">Er zijn nog geen meetwaarden ingevoerd voor deze test</p>
        </div>
    @endif
</div>