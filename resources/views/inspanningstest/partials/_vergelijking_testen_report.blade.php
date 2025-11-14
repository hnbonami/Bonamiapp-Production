{{-- Test Vergelijking Sectie - Rapport Versie (Print-ready) --}}
{{-- Variabele sleutel: {{INSPANNINGSTEST_VERGELIJKING}} --}}

@php
    // Haal alle vergelijkbare testen op (zelfde testtype, exclusief huidige test)
    $vergelijkbareTesten = \App\Models\Inspanningstest::where('klant_id', $klant->id)
        ->where('testtype', $inspanningstest->testtype)
        ->where('id', '!=', $inspanningstest->id)
        ->whereNotNull('testresultaten')
        ->orderBy('datum', 'desc')
        ->limit(4) // Max 4 extra testen (+ huidige = 5 totaal)
        ->get();
    
    // Toon alleen als er minimaal 1 vergelijkbare test is (= 2 testen totaal)
    $toonVergelijking = $vergelijkbareTesten->count() >= 1;
    
    // Bepaal testtype voor correcte X-as
    $testtype = $inspanningstest->testtype ?? 'fietstest';
    $isLooptest = str_contains(strtolower($testtype), 'loop') || str_contains(strtolower($testtype), 'lopen');
    $isZwemtest = str_contains(strtolower($testtype), 'zwem');
    
    // Voor rapport: neem automatisch de 3 meest recente testen
    $geselecteerdeTesten = $vergelijkbareTesten->take(3);
@endphp

<style>
    .rapport-vergelijking {
        font-family: Tahoma, Arial, sans-serif;
        font-size: 11px;
        line-height: 1.4;
        color: #0a152dff;
        margin: 20px 0;
        page-break-inside: avoid;
    }
    
    .rapport-vergelijking h3 {
        font-size: 14px;
        font-weight: 700;
        color: #0a152dff;
        margin: 15px 0 10px 0;
        padding: 8px 10px;
        background: linear-gradient(135deg, #c8e1eb 0%, #84bfd6ff 100%);
        border-left: 4px solid #0a152dff;
    }
    
    .rapport-vergelijking-grafiek {
        margin: 15px 0;
        padding: 15px;
        background: white;
        border: 1px solid #e5e7eb;
        height: 400px !important;
        position: relative;
        page-break-inside: avoid;
    }
    
    .rapport-vergelijking-grafiek canvas {
        height: 100% !important;
        width: 100% !important;
    }
    
    .rapport-progressie-tabel {
        width: 100%;
        border-collapse: collapse;
        margin: 15px 0;
        font-size: 10px;
        page-break-inside: avoid;
    }
    
    .rapport-progressie-tabel th {
        background-color: #c8e1eb;
        color: #0a152dff;
        font-weight: 700;
        padding: 8px 6px;
        text-align: center;
        border: 1px solid #84bfd6ff;
        font-size: 9px;
    }
    
    .rapport-progressie-tabel td {
        padding: 6px;
        border: 1px solid #e5e7eb;
        text-align: center;
    }
    
    .rapport-progressie-tabel tr:nth-child(even) {
        background-color: #f9fafb;
    }
    
    .rapport-progressie-tabel tr:hover {
        background-color: #f3f4f6;
    }
    
    .rapport-delta-positief {
        color: #059669;
        font-weight: 700;
    }
    
    .rapport-delta-negatief {
        color: #dc2626;
        font-weight: 700;
    }
    
    .rapport-delta-neutraal {
        color: #6b7280;
    }
    
    .rapport-interpretatie {
        margin: 15px 0;
        padding: 12px 15px;
        background: #fef3c7;
        border-left: 4px solid #f59e0b;
        font-size: 10px;
        line-height: 1.6;
        page-break-inside: avoid;
    }
    
    .rapport-interpretatie h4 {
        font-size: 11px;
        font-weight: 700;
        color: #92400e;
        margin: 0 0 8px 0;
    }
    
    .rapport-interpretatie p {
        margin: 6px 0;
        color: #78350f;
    }
    
    .rapport-legenda {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin: 10px 0;
        font-size: 9px;
    }
    
    .rapport-legenda-item {
        display: flex;
        align-items: center;
        gap: 5px;
    }
    
    .rapport-legenda-kleur {
        width: 16px;
        height: 3px;
        border-radius: 2px;
    }
    
    @media print {
        .rapport-vergelijking {
            page-break-inside: avoid;
        }
        .rapport-vergelijking-grafiek {
            page-break-inside: avoid;
        }
    }
</style>

@if($toonVergelijking)
<div class="rapport-vergelijking">
    <h3>🔄 Test Vergelijking - {{ $inspanningstest->testtype }}</h3>
    
    <p style="margin: 10px 0; font-size: 10px; color: #6b7280;">
        Deze sectie vergelijkt je huidige test met eerdere testen van hetzelfde type. Een rechtsverschuiving van de curves duidt op verbeterde prestaties.
    </p>

    {{-- Legenda --}}
    <div class="rapport-legenda">
        <div class="rapport-legenda-item">
            <div class="rapport-legenda-kleur" style="background-color: #8b5cf6; height: 4px;"></div>
            <span><strong>{{ \Carbon\Carbon::parse($inspanningstest->datum)->format('d-m-Y') }}</strong> (Huidige test)</span>
        </div>
        @foreach($geselecteerdeTesten as $index => $test)
            @php
                $kleurIndex = $index + 1;
                $kleuren = [1 => '#06b6d4', 2 => '#10b981', 3 => '#f59e0b'];
                $kleur = $kleuren[$kleurIndex] ?? '#94a3b8';
            @endphp
            <div class="rapport-legenda-item">
                <div class="rapport-legenda-kleur" style="background-color: {{ $kleur }};"></div>
                <span>{{ \Carbon\Carbon::parse($test->datum)->format('d-m-Y') }}</span>
            </div>
        @endforeach
    </div>

    {{-- Grafiek Container --}}
    <div class="rapport-vergelijking-grafiek">
        <canvas id="rapportVergelijkingGrafiek"></canvas>
    </div>

    {{-- Progressie Analyse Tabel --}}
    <table class="rapport-progressie-tabel">
        <thead>
            <tr>
                <th style="text-align: left;">Metric</th>
                @foreach($geselecteerdeTesten->reverse() as $test)
                    <th>{{ \Carbon\Carbon::parse($test->datum)->format('d-m-Y') }}</th>
                @endforeach
                <th style="background-color: #ddd6fe; color: #5b21b6;">
                    <strong>Huidige</strong><br>
                    {{ \Carbon\Carbon::parse($inspanningstest->datum)->format('d-m-Y') }}
                </th>
                <th>Δ (%)</th>
                <th>Trend</th>
            </tr>
        </thead>
        <tbody>
            {{-- LT1 Vermogen/Snelheid --}}
            @php
                $oudsteLT1 = $geselecteerdeTesten->last() ? 
                    ($isLooptest || $isZwemtest ? $geselecteerdeTesten->last()->aerobe_drempel_snelheid : $geselecteerdeTesten->last()->aerobe_drempel_vermogen) : null;
                $huidigeLT1 = $isLooptest || $isZwemtest ? $inspanningstest->aerobe_drempel_snelheid : $inspanningstest->aerobe_drempel_vermogen;
                $deltaLT1 = ($oudsteLT1 && $huidigeLT1) ? (($huidigeLT1 - $oudsteLT1) / $oudsteLT1) * 100 : null;
            @endphp
            <tr>
                <td style="text-align: left; font-weight: 600;">LT1 {{ $isLooptest || $isZwemtest ? 'Snelheid' : 'Vermogen' }}</td>
                @foreach($geselecteerdeTesten->reverse() as $test)
                    @php
                        $waarde = $isLooptest || $isZwemtest ? $test->aerobe_drempel_snelheid : $test->aerobe_drempel_vermogen;
                    @endphp
                    <td>{{ $waarde ? ($isLooptest || $isZwemtest ? number_format($waarde, 1) . ' km/h' : $waarde . 'W') : '-' }}</td>
                @endforeach
                <td style="background-color: #ede9fe; font-weight: 700; color: #5b21b6;">
                    {{ $huidigeLT1 ? ($isLooptest || $isZwemtest ? number_format($huidigeLT1, 1) . ' km/h' : $huidigeLT1 . 'W') : '-' }}
                </td>
                <td class="{{ $deltaLT1 > 0 ? 'rapport-delta-positief' : ($deltaLT1 < 0 ? 'rapport-delta-negatief' : 'rapport-delta-neutraal') }}">
                    {{ $deltaLT1 !== null ? ($deltaLT1 > 0 ? '+' : '') . number_format($deltaLT1, 1) . '%' : '-' }}
                </td>
                <td style="font-size: 14px;">
                    @if($deltaLT1 > 5) 📈
                    @elseif($deltaLT1 > 0) ↗️
                    @elseif($deltaLT1 < -5) 📉
                    @elseif($deltaLT1 < 0) ↘️
                    @else →
                    @endif
                </td>
            </tr>

            {{-- LT1 Hartslag --}}
            @php
                $oudsteLT1HR = $geselecteerdeTesten->last() ? $geselecteerdeTesten->last()->aerobe_drempel_hartslag : null;
                $huidigeLT1HR = $inspanningstest->aerobe_drempel_hartslag;
                $deltaLT1HR = ($oudsteLT1HR && $huidigeLT1HR) ? (($huidigeLT1HR - $oudsteLT1HR) / $oudsteLT1HR) * 100 : null;
            @endphp
            <tr>
                <td style="text-align: left; font-weight: 600;">LT1 Hartslag</td>
                @foreach($geselecteerdeTesten->reverse() as $test)
                    <td>{{ $test->aerobe_drempel_hartslag ? $test->aerobe_drempel_hartslag . ' bpm' : '-' }}</td>
                @endforeach
                <td style="background-color: #ede9fe; font-weight: 700; color: #5b21b6;">
                    {{ $huidigeLT1HR ? $huidigeLT1HR . ' bpm' : '-' }}
                </td>
                <td class="{{ $deltaLT1HR > 0 ? 'rapport-delta-positief' : ($deltaLT1HR < 0 ? 'rapport-delta-negatief' : 'rapport-delta-neutraal') }}">
                    {{ $deltaLT1HR !== null ? ($deltaLT1HR > 0 ? '+' : '') . number_format($deltaLT1HR, 1) . '%' : '-' }}
                </td>
                <td style="font-size: 14px;">
                    @if($deltaLT1HR > 3) 📈
                    @elseif($deltaLT1HR > 0) ↗️
                    @elseif($deltaLT1HR < -3) 📉
                    @elseif($deltaLT1HR < 0) ↘️
                    @else →
                    @endif
                </td>
            </tr>

            {{-- LT2 Vermogen/Snelheid --}}
            @php
                $oudsteLT2 = $geselecteerdeTesten->last() ? 
                    ($isLooptest || $isZwemtest ? $geselecteerdeTesten->last()->anaerobe_drempel_snelheid : $geselecteerdeTesten->last()->anaerobe_drempel_vermogen) : null;
                $huidigeLT2 = $isLooptest || $isZwemtest ? $inspanningstest->anaerobe_drempel_snelheid : $inspanningstest->anaerobe_drempel_vermogen;
                $deltaLT2 = ($oudsteLT2 && $huidigeLT2) ? (($huidigeLT2 - $oudsteLT2) / $oudsteLT2) * 100 : null;
            @endphp
            <tr>
                <td style="text-align: left; font-weight: 600;">LT2 {{ $isLooptest || $isZwemtest ? 'Snelheid' : 'Vermogen' }}</td>
                @foreach($geselecteerdeTesten->reverse() as $test)
                    @php
                        $waarde = $isLooptest || $isZwemtest ? $test->anaerobe_drempel_snelheid : $test->anaerobe_drempel_vermogen;
                    @endphp
                    <td>{{ $waarde ? ($isLooptest || $isZwemtest ? number_format($waarde, 1) . ' km/h' : $waarde . 'W') : '-' }}</td>
                @endforeach
                <td style="background-color: #ede9fe; font-weight: 700; color: #5b21b6;">
                    {{ $huidigeLT2 ? ($isLooptest || $isZwemtest ? number_format($huidigeLT2, 1) . ' km/h' : $huidigeLT2 . 'W') : '-' }}
                </td>
                <td class="{{ $deltaLT2 > 0 ? 'rapport-delta-positief' : ($deltaLT2 < 0 ? 'rapport-delta-negatief' : 'rapport-delta-neutraal') }}">
                    {{ $deltaLT2 !== null ? ($deltaLT2 > 0 ? '+' : '') . number_format($deltaLT2, 1) . '%' : '-' }}
                </td>
                <td style="font-size: 14px;">
                    @if($deltaLT2 > 5) 📈
                    @elseif($deltaLT2 > 0) ↗️
                    @elseif($deltaLT2 < -5) 📉
                    @elseif($deltaLT2 < 0) ↘️
                    @else →
                    @endif
                </td>
            </tr>

            {{-- LT2 Hartslag --}}
            @php
                $oudsteLT2HR = $geselecteerdeTesten->last() ? $geselecteerdeTesten->last()->anaerobe_drempel_hartslag : null;
                $huidigeLT2HR = $inspanningstest->anaerobe_drempel_hartslag;
                $deltaLT2HR = ($oudsteLT2HR && $huidigeLT2HR) ? (($huidigeLT2HR - $oudsteLT2HR) / $oudsteLT2HR) * 100 : null;
            @endphp
            <tr>
                <td style="text-align: left; font-weight: 600;">LT2 Hartslag</td>
                @foreach($geselecteerdeTesten->reverse() as $test)
                    <td>{{ $test->anaerobe_drempel_hartslag ? $test->anaerobe_drempel_hartslag . ' bpm' : '-' }}</td>
                @endforeach
                <td style="background-color: #ede9fe; font-weight: 700; color: #5b21b6;">
                    {{ $huidigeLT2HR ? $huidigeLT2HR . ' bpm' : '-' }}
                </td>
                <td class="{{ $deltaLT2HR > 0 ? 'rapport-delta-positief' : ($deltaLT2HR < 0 ? 'rapport-delta-negatief' : 'rapport-delta-neutraal') }}">
                    {{ $deltaLT2HR !== null ? ($deltaLT2HR > 0 ? '+' : '') . number_format($deltaLT2HR, 1) . '%' : '-' }}
                </td>
                <td style="font-size: 14px;">
                    @if($deltaLT2HR > 3) 📈
                    @elseif($deltaLT2HR > 0) ↗️
                    @elseif($deltaLT2HR < -3) 📉
                    @elseif($deltaLT2HR < 0) ↘️
                    @else →
                    @endif
                </td>
            </tr>

            {{-- Max Vermogen/Snelheid (uit laatste testresultaat, zoals drempelwaarden partial) --}}
            @php
                // Functie om max vermogen/snelheid uit testresultaten te halen
                function getMaxVermogenFromTestReport($test, $isLooptest, $isZwemtest) {
                    $testresultaten = $test->testresultaten ?? [];
                    
                    // Check of testresultaten een string is (JSON) en decode indien nodig
                    if (is_string($testresultaten)) {
                        $testresultaten = json_decode($testresultaten, true) ?? [];
                    }
                    
                    // Converteer naar array
                    $testresultaten = is_array($testresultaten) ? $testresultaten : [];
                    
                    // Haal max vermogen/snelheid uit laatste testresultaat
                    if (count($testresultaten) > 0) {
                        $laatsteStap = end($testresultaten);
                        
                        // Voor looptesten/zwemtesten: gebruik snelheid
                        if ($isLooptest || $isZwemtest) {
                            return $laatsteStap['snelheid'] ?? $laatsteStap['vermogen'] ?? null;
                        } else {
                            // Voor fietstesten: gebruik vermogen
                            return $laatsteStap['vermogen'] ?? null;
                        }
                    }
                    
                    return null;
                }
                
                // Haal max waarden op voor oudste en huidige test
                $oudsteMax = $geselecteerdeTesten->last() ? getMaxVermogenFromTestReport($geselecteerdeTesten->last(), $isLooptest, $isZwemtest) : null;
                $huidigeMax = getMaxVermogenFromTestReport($inspanningstest, $isLooptest, $isZwemtest);
                $deltaMax = ($oudsteMax && $huidigeMax) ? (($huidigeMax - $oudsteMax) / $oudsteMax) * 100 : null;
            @endphp
            <tr>
                <td style="text-align: left; font-weight: 600;">Max {{ $isLooptest || $isZwemtest ? 'Snelheid' : 'Vermogen' }}</td>
                @foreach($geselecteerdeTesten->reverse() as $test)
                    <td>
                        @php
                            $maxWaarde = getMaxVermogenFromTestReport($test, $isLooptest, $isZwemtest);
                        @endphp
                        {{ $maxWaarde ? ($isLooptest || $isZwemtest ? number_format($maxWaarde, 1) . ' km/h' : number_format($maxWaarde, 0) . 'W') : '-' }}
                    </td>
                @endforeach
                <td style="background-color: #ede9fe; font-weight: 700; color: #5b21b6;">
                    {{ $huidigeMax ? ($isLooptest || $isZwemtest ? number_format($huidigeMax, 1) . ' km/h' : number_format($huidigeMax, 0) . 'W') : '-' }}
                </td>
                <td class="{{ $deltaMax > 0 ? 'rapport-delta-positief' : ($deltaMax < 0 ? 'rapport-delta-negatief' : 'rapport-delta-neutraal') }}">
                    {{ $deltaMax !== null ? ($deltaMax > 0 ? '+' : '') . number_format($deltaMax, 1) . '%' : '-' }}
                </td>
                <td style="font-size: 14px;">
                    @if($deltaMax > 5) 📈
                    @elseif($deltaMax > 0) ↗️
                    @elseif($deltaMax < -5) 📉
                    @elseif($deltaMax < 0) ↘️
                    @else →
                    @endif
                </td>
            </tr>
        </tbody>
    </table>

    {{-- Interpretatie --}}
    <div class="rapport-interpretatie">
        <h4>💡 Progressie Interpretatie</h4>
        @if($deltaLT2 !== null && $deltaLT2 > 5)
            <p><strong style="color: #059669;">✓ Uitstekende progressie!</strong> Je anaerobe drempel is met <strong>{{ number_format($deltaLT2, 1) }}%</strong> gestegen sinds {{ \Carbon\Carbon::parse($geselecteerdeTesten->last()->datum)->format('d-m-Y') }}. Dit betekent dat je lichaam efficiënter is geworden in het verwerken van lactaat bij hogere intensiteiten.</p>
        @elseif($deltaLT2 !== null && $deltaLT2 > 0)
            <p><strong style="color: #0284c7;">→ Positieve ontwikkeling:</strong> Je drempelwaarden zijn licht gestegen (+{{ number_format($deltaLT2, 1) }}%). Blijf doortrainen met focus op je zwakke zones voor verdere verbetering.</p>
        @elseif($deltaLT2 !== null && $deltaLT2 < 0)
            <p><strong style="color: #dc2626;">⚠ Aandachtspunt:</strong> Je drempelwaarden zijn gedaald ({{ number_format($deltaLT2, 1) }}%). Dit kan wijzen op overtraining, onderherstel of ziekte. Overweeg rustdagen of een trainingsaanpassing.</p>
        @else
            <p>Onvoldoende data voor vergelijking. Blijf regelmatig testen om je progressie te monitoren.</p>
        @endif
        
        <p style="margin-top: 10px; padding-top: 8px; border-top: 1px solid #fbbf24; font-size: 9px;">
            <strong>Leeswijzer:</strong> Een curve die naar rechts verschuift (bij zelfde hartslag/lactaat kun je harder werken) is een duidelijk teken van vooruitgang. De percentages geven het verschil weer tussen je oudste en meest recente test.
        </p>
    </div>

    {{-- Chart.js Script voor Rapport Grafiek --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Data voor alle geselecteerde testen (inclusief huidige test)
        const rapportTesten = [
            // Huidige test (index 0, altijd paars)
            {
                id: {{ $inspanningstest->id }},
                datum: '{{ \Carbon\Carbon::parse($inspanningstest->datum)->format('d-m-Y') }}',
                kleur: '#8b5cf6',
                testresultaten: @json($inspanningstest->testresultaten ?? []),
                label: '{{ \Carbon\Carbon::parse($inspanningstest->datum)->format('d-m-Y') }} (Huidige)',
                lt1_vermogen: {{ $inspanningstest->aerobe_drempel_vermogen ?? 'null' }},
                lt2_vermogen: {{ $inspanningstest->anaerobe_drempel_vermogen ?? 'null' }},
                lt1_snelheid: {{ $inspanningstest->aerobe_drempel_snelheid ?? 'null' }},
                lt2_snelheid: {{ $inspanningstest->anaerobe_drempel_snelheid ?? 'null' }}
            },
            @foreach($geselecteerdeTesten as $index => $test)
                @php
                    $kleurIndex = $index + 1;
                    $kleuren = [1 => '#06b6d4', 2 => '#10b981', 3 => '#f59e0b'];
                    $kleur = $kleuren[$kleurIndex] ?? '#94a3b8';
                @endphp
                {
                    id: {{ $test->id }},
                    datum: '{{ \Carbon\Carbon::parse($test->datum)->format('d-m-Y') }}',
                    kleur: '{{ $kleur }}',
                    testresultaten: @json($test->testresultaten ?? []),
                    label: '{{ \Carbon\Carbon::parse($test->datum)->format('d-m-Y') }}',
                    lt1_vermogen: {{ $test->aerobe_drempel_vermogen ?? 'null' }},
                    lt2_vermogen: {{ $test->anaerobe_drempel_vermogen ?? 'null' }},
                    lt1_snelheid: {{ $test->aerobe_drempel_snelheid ?? 'null' }},
                    lt2_snelheid: {{ $test->anaerobe_drempel_snelheid ?? 'null' }}
                }{{ $loop->last ? '' : ',' }}
            @endforeach
        ];

        console.log('📄 Rapport vergelijkingstesten geladen:', rapportTesten.length);

        const testtype = '{{ $testtype }}';
        const isLooptest = {{ $isLooptest ? 'true' : 'false' }};
        const isZwemtest = {{ $isZwemtest ? 'true' : 'false' }};

        // Genereer datasets voor alle geselecteerde testen
        const datasets = [];

        rapportTesten.forEach((test, testIndex) => {
            let testresultaten = test.testresultaten;
            if (typeof testresultaten === 'string') {
                testresultaten = JSON.parse(testresultaten);
            }
            testresultaten = Array.isArray(testresultaten) ? testresultaten : [];

            // Bereken snelheid voor looptesten
            const testresultatenMetSnelheid = testresultaten.map(stap => {
                if (stap.snelheid) {
                    return { ...stap, berekende_snelheid: parseFloat(stap.snelheid) };
                }
                const afstand = parseFloat(stap.afstand) || 0;
                const tijdMin = parseFloat(stap.tijd_min) || 0;
                const tijdSec = parseFloat(stap.tijd_sec) || 0;
                const tijdUren = (tijdMin + (tijdSec / 60)) / 60;
                const snelheidKmh = tijdUren > 0 ? (afstand / 1000) / tijdUren : 0;
                return { ...stap, berekende_snelheid: snelheidKmh };
            });

            // Hartslag dataset
            const hartslagData = testresultatenMetSnelheid.map(stap => {
                const xVal = isLooptest || isZwemtest 
                    ? stap.berekende_snelheid || 0
                    : parseFloat(stap.vermogen) || 0;
                return {
                    x: xVal,
                    y: parseFloat(stap.hartslag) || 0
                };
            });

            // Lactaat dataset
            const lactaatData = testresultatenMetSnelheid.map(stap => {
                const xVal = isLooptest || isZwemtest 
                    ? stap.berekende_snelheid || 0
                    : parseFloat(stap.vermogen) || 0;
                return {
                    x: xVal,
                    y: parseFloat(stap.lactaat) || 0
                };
            });

            // Voeg hartslag dataset toe (doorgetrokken lijn)
            datasets.push({
                label: test.label + ' - Hartslag',
                data: hartslagData,
                borderColor: test.kleur,
                backgroundColor: 'transparent',
                borderWidth: testIndex === 0 ? 3 : 2,
                tension: 0.4,
                yAxisID: 'y',
                pointRadius: testIndex === 0 ? 4 : 2,
                showLine: true,
                borderDash: testIndex === 0 ? [] : [5, 3]
            });

            // Voeg lactaat dataset toe (stippellijn)
            datasets.push({
                label: test.label + ' - Lactaat',
                data: lactaatData,
                borderColor: test.kleur,
                backgroundColor: 'transparent',
                borderWidth: testIndex === 0 ? 3 : 2,
                tension: 0.4,
                yAxisID: 'y1',
                pointRadius: testIndex === 0 ? 4 : 2,
                showLine: true,
                borderDash: [2, 2]
            });
        });

        // Voeg drempellijnen toe voor alle testen
        rapportTesten.forEach((test, testIndex) => {
            // Haal drempelwaarden op voor deze test
            const lt1Value = isLooptest || isZwemtest ? test.lt1_snelheid : test.lt1_vermogen;
            const lt2Value = isLooptest || isZwemtest ? test.lt2_snelheid : test.lt2_vermogen;

            // Bepaal Y-as range voor drempellijnen
            const minY = 40; // Minimale hartslag
            const maxY = 200; // Maximale hartslag

            // LT1 drempellijn (gestippeld)
            if (lt1Value !== null && !isNaN(lt1Value)) {
                datasets.push({
                    label: test.label + ' - LT1',
                    data: [
                        { x: lt1Value, y: minY },
                        { x: lt1Value, y: maxY }
                    ],
                    borderColor: test.kleur,
                    backgroundColor: 'transparent',
                    borderWidth: 1.5,
                    borderDash: [6, 3],
                    pointRadius: 0,
                    showLine: true,
                    yAxisID: 'y',
                    fill: false
                });
            }

            // LT2 drempellijn (kleinere stippels)
            if (lt2Value !== null && !isNaN(lt2Value)) {
                datasets.push({
                    label: test.label + ' - LT2',
                    data: [
                        { x: lt2Value, y: minY },
                        { x: lt2Value, y: maxY }
                    ],
                    borderColor: test.kleur,
                    backgroundColor: 'transparent',
                    borderWidth: 1.5,
                    borderDash: [3, 3],
                    pointRadius: 0,
                    showLine: true,
                    yAxisID: 'y',
                    fill: false
                });
            }
        });

        // X-as label
        let xAxisLabel = 'Vermogen (Watt)';
        if (isLooptest) {
            xAxisLabel = 'Snelheid (km/h)';
        } else if (isZwemtest) {
            xAxisLabel = 'Tempo (mm:ss/100m)';
        }

        // Maak grafiek
        const ctx = document.getElementById('rapportVergelijkingGrafiek').getContext('2d');
        new Chart(ctx, {
            type: 'scatter',
            data: { datasets: datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: { 
                        display: true, 
                        position: 'bottom',
                        labels: {
                            font: { size: 8 },
                            usePointStyle: true,
                            boxWidth: 6,
                            padding: 8
                        }
                    },
                    title: {
                        display: true,
                        text: 'Progressie Vergelijking: Hartslag & Lactaat',
                        font: { size: 11, weight: 'bold' }
                    }
                },
                scales: {
                    x: {
                        display: true,
                        reverse: isZwemtest,
                        title: {
                            display: true,
                            text: xAxisLabel,
                            font: { weight: 'bold', size: 10 }
                        },
                        ticks: {
                            font: { size: 8 }
                        }
                    },
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Hartslag (bpm)',
                            color: '#374151',
                            font: { weight: 'bold', size: 10 }
                        },
                        ticks: { 
                            color: '#374151', 
                            font: { size: 8 }
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Lactaat (mmol/L)',
                            color: '#06b6d4',
                            font: { weight: 'bold', size: 10 }
                        },
                        ticks: { 
                            color: '#06b6d4', 
                            font: { size: 8 }
                        },
                        grid: { drawOnChartArea: false }
                    }
                }
            }
        });

        console.log('✅ Rapport vergelijkingsgrafiek geladen');
    });
    </script>
</div>
@endif
