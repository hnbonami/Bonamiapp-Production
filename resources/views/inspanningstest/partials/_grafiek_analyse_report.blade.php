{{-- Grafiek Analyse - Rapport Versie (Print-ready) --}}
{{-- Variabele sleutel: {{INSPANNINGSTEST_GRAFIEK}} --}}

<style>
    .rapport-grafiek {
        font-family: Tahoma, Arial, sans-serif;
        font-size: 11px;
        line-height: 1.4;
        color: #0a152dff;
        margin: 20px 0;
        width: 120%;
    }
    
    .rapport-grafiek h3 {
        font-size: 14px;
        font-weight: 700;
        color: #0a152dff;
        margin: 15px 0 10px 0;
        padding: 8px 10px;
        background-color: #c8e1eb;
        border-left: 4px solid #0a152dff;
    }
    
    .grafiek-container {
        margin: 15px 0;
        padding: 15px;
        background: white;
        border: 1px solid #e5e7eb;
        height: 450px !important;
        min-height: 450px !important;
        position: relative;
    }
    
    .grafiek-container canvas {
        height: 100% !important;
        width: 100% !important;
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
    
    .rapport-toelichting-box p {
        margin: 8px 0;
        color: #78350f;
    }
    
    .rapport-analyse-methode {
        display: inline-block;
        padding: 4px 10px;
        background: white;
        border: 1px solid #d1d5db;
        border-radius: 12px;
        font-size: 9px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 10px;
    }
    
    .rapport-geen-data {
        text-align: center;
        padding: 30px 20px;
        color: #9ca3af;
        font-style: italic;
    }
</style>

@php
    // Bepaal testtype
    $testtype = $inspanningstest->testtype ?? 'fietstest';
    $isLooptest = str_contains($testtype, 'loop') || str_contains($testtype, 'lopen');
    $isZwemtest = str_contains($testtype, 'zwem');
    
    // Haal testresultaten op
    $testresultaten = $inspanningstest->testresultaten ?? collect();
    if (is_string($testresultaten)) {
        $testresultaten = json_decode($testresultaten) ?? [];
    }
    $testresultaten = collect($testresultaten);
    
    // Haal analyse methode op
    $analyseMethode = $inspanningstest->analyse_methode ?? null;
    $analyseMethodeLabel = match($analyseMethode) {
        'dmax' => 'D-max Methode',
        'dmax_modified' => 'D-max Modified Methode',
        'lactaat_steady_state' => 'Lactaat Steady State',
        'hartslag_deflectie' => 'Hartslagdeflectie',
        'handmatig' => 'Handmatig Bepaald',
        default => 'Niet gespecificeerd'
    };
    
    // Check of er drempelwaarden zijn
    $heeftDrempels = ($inspanningstest->aerobe_drempel_vermogen || $inspanningstest->aerobe_drempel_snelheid) 
                  && ($inspanningstest->anaerobe_drempel_vermogen || $inspanningstest->anaerobe_drempel_snelheid);
@endphp

<div class="rapport-grafiek">
    @if($analyseMethode)
        <span class="rapport-analyse-methode">{{ $analyseMethodeLabel }}</span>
    @endif
    
    @if($heeftDrempels && count($testresultaten) > 0)
        {{-- Grafiek Container met Chart.js --}}
        <div class="grafiek-container">
            <canvas id="testResultatenGrafiek"></canvas>
        </div>
        
        {{-- Chart.js Script - EXACT KOPIE VAN WERKENDE RESULTS PAGINA --}}
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Haal testresultaten op
                const testresultaten = @json($testresultaten);
                
                // Bereid grafiek data voor - gebruik juiste veld op basis van testtype
                const testtype = '{{ $inspanningstest->testtype }}';
                const isLooptest = testtype.includes('loop') || testtype.includes('lopen');
                const isZwemtest = testtype.includes('zwem');
                
                // DEBUG: Log eerste stap om structuur te zien
                console.log('🔍 Eerste teststap structuur:', testresultaten[0]);
                
                // BEREKEN snelheid uit afstand en tijd voor veldtesten!
                const testresultatenMetSnelheid = testresultaten.map(stap => {
                    // Als snelheid al aanwezig is, gebruik die
                    if (stap.snelheid) {
                        return { ...stap, berekende_snelheid: parseFloat(stap.snelheid) };
                    }
                    
                    // Anders bereken uit afstand en tijd
                    const afstand = parseFloat(stap.afstand) || 0; // in meters
                    const tijdMin = parseFloat(stap.tijd_min) || 0;
                    const tijdSec = parseFloat(stap.tijd_sec) || 0;
                    const tijdUren = (tijdMin + (tijdSec / 60)) / 60; // converteer naar uren
                    
                    // Snelheid = afstand (km) / tijd (uren)
                    const snelheidKmh = tijdUren > 0 ? (afstand / 1000) / tijdUren : 0;
                    
                    return { ...stap, berekende_snelheid: snelheidKmh };
                });
                
                console.log('🔍 Testresultaten met berekende snelheid:', testresultatenMetSnelheid);
                
                // Voor X-as: gebruik berekende snelheid voor loop, vermogen voor fiets
                const xValues = testresultatenMetSnelheid.map(stap => {
                    if (isLooptest || isZwemtest) {
                        return stap.berekende_snelheid || 0;
                    }
                    return parseFloat(stap.vermogen) || 0;
                });
                
                const hartslagData = testresultatenMetSnelheid.map(stap => {
                    const xVal = isLooptest || isZwemtest 
                        ? stap.berekende_snelheid || 0
                        : parseFloat(stap.vermogen) || 0;
                    return {
                        x: xVal,
                        y: parseFloat(stap.hartslag) || 0
                    };
                });
                
                const lactaatData = testresultatenMetSnelheid.map(stap => {
                    const xVal = isLooptest || isZwemtest 
                        ? stap.berekende_snelheid || 0
                        : parseFloat(stap.vermogen) || 0;
                    return {
                        x: xVal,
                        y: parseFloat(stap.lactaat) || 0
                    };
                });
                
                console.log('📊 PDF Grafiek data:', {
                    testtype: testtype,
                    isLooptest,
                    xValues,
                    hartslagData,
                    lactaatData
                });
                
                // Haal drempelwaarden op uit database (niet van window object!)
                const lt1Vermogen = {{ $inspanningstest->aerobe_drempel_vermogen ?? 'null' }};
                const lt2Vermogen = {{ $inspanningstest->anaerobe_drempel_vermogen ?? 'null' }};
                const lt1Snelheid = {{ $inspanningstest->aerobe_drempel_snelheid ?? 'null' }};
                const lt2Snelheid = {{ $inspanningstest->anaerobe_drempel_snelheid ?? 'null' }};
                
                // FALLBACK: Voor looptesten, als snelheid null is maar vermogen bestaat, gebruik dan vermogen!
                // (Dit lost het probleem op dat drempelwaarden verkeerd zijn opgeslagen)
                let lt1Value = null;
                let lt2Value = null;
                
                if (isLooptest || isZwemtest) {
                    // Probeer eerst snelheid, fallback naar vermogen
                    lt1Value = lt1Snelheid !== null ? lt1Snelheid : lt1Vermogen;
                    lt2Value = lt2Snelheid !== null ? lt2Snelheid : lt2Vermogen;
                } else {
                    lt1Value = lt1Vermogen;
                    lt2Value = lt2Vermogen;
                }
                
                console.log('🎯 Drempelwaarden:', {
                    lt1Vermogen,
                    lt2Vermogen,
                    lt1Snelheid,
                    lt2Snelheid,
                    lt1Value,
                    lt2Value,
                    gebruiktSnelheid: isLooptest || isZwemtest,
                    gebruiktFallback: (isLooptest && lt1Snelheid === null && lt1Vermogen !== null)
                });
                
                // Bereid X-as label voor
                let xAxisLabel = 'Vermogen (Watt)';
                
                if (isLooptest) {
                    xAxisLabel = 'Snelheid (km/h)';
                } else if (isZwemtest) {
                    xAxisLabel = 'Tempo (mm:ss/100m)';
                }
                
                // Configureer Chart.js
                const ctx = document.getElementById('testResultatenGrafiek').getContext('2d');
                
                // Maak datasets array
                const datasets = [
                    {
                        label: 'Hartslag (bpm)',
                        data: hartslagData,
                        borderColor: '#0a152dff',
                        backgroundColor: 'rgba(37, 99, 235, 0.1)',
                        borderWidth: 2,
                        tension: 0.4,
                        yAxisID: 'y',
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        showLine: true
                    },
                    {
                        label: 'Lactaat (mmol/L)',
                        data: lactaatData,
                        borderColor: '#84bfd6ff',
                        backgroundColor: 'rgba(22, 163, 74, 0.1)',
                        borderWidth: 2,
                        tension: 0.4,
                        yAxisID: 'y1',
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        showLine: true
                    }
                ];
                
                // Voeg LT1 drempellijn toe
                if (lt1Value !== null && !isNaN(lt1Value) && hartslagData.length > 0) {
                    const minY = Math.min(...hartslagData.map(d => d.y)) - 10;
                    const maxY = Math.max(...hartslagData.map(d => d.y)) + 10;
                    datasets.push({
                        label: 'LT1 (Aëroob)',
                        data: [{ x: lt1Value, y: minY }, { x: lt1Value, y: maxY }],
                        borderColor: '#f1a8a8ff',
                        borderWidth: 3,
                        borderDash: [10, 5],
                        pointRadius: 0,
                        showLine: true,
                        yAxisID: 'y'
                    });
                    console.log('✅ LT1 lijn toegevoegd op x=' + lt1Value);
                }
                
                // Voeg LT2 drempellijn toe
                if (lt2Value !== null && !isNaN(lt2Value) && hartslagData.length > 0) {
                    const minY = Math.min(...hartslagData.map(d => d.y)) - 10;
                    const maxY = Math.max(...hartslagData.map(d => d.y)) + 10;
                    datasets.push({
                        label: 'LT2 (Anaëroob)',
                        data: [{ x: lt2Value, y: minY }, { x: lt2Value, y: maxY }],
                        borderColor: '#f5bc59ff',
                        borderWidth: 3,
                        borderDash: [10, 5],
                        pointRadius: 0,
                        showLine: true,
                        yAxisID: 'y'
                    });
                    console.log('✅ LT2 lijn toegevoegd op x=' + lt2Value);
                }
                
                // Custom plugin om labels naast drempellijnen te tonen
                const drempelLabelsPlugin = {
                    id: 'drempelLabels',
                    afterDatasetsDraw(chart) {
                        const ctx = chart.ctx;
                        const xScale = chart.scales.x;
                        const yScale = chart.scales.y;
                        
                        ctx.save();
                        ctx.font = 'bold 11px Tahoma, Arial, sans-serif';
                        ctx.textAlign = 'left';
                        ctx.textBaseline = 'middle';
                        
                        // Teken LT1 label
                        if (lt1Value !== null && !isNaN(lt1Value)) {
                            const xPos = xScale.getPixelForValue(lt1Value);
                            const yPos = yScale.top + 30;
                            
                            // Achtergrond rechthoek
                            const labelText = `LT1: ${lt1Value.toFixed(1)}`;
                            const textWidth = ctx.measureText(labelText).width;
                            ctx.fillStyle = '#f1a8a8ff';
                            ctx.fillRect(xPos + 5, yPos - 9, textWidth + 8, 18);
                            
                            // Tekst
                            ctx.fillStyle = '#ffffff';
                            ctx.fillText(labelText, xPos + 9, yPos);
                        }
                        
                        // Teken LT2 label
                        if (lt2Value !== null && !isNaN(lt2Value)) {
                            const xPos = xScale.getPixelForValue(lt2Value);
                            const yPos = yScale.top + 55;
                            
                            // Achtergrond rechthoek
                            const labelText = `LT2: ${lt2Value.toFixed(1)}`;
                            const textWidth = ctx.measureText(labelText).width;
                            ctx.fillStyle = '#f5bc59ff';
                            ctx.fillRect(xPos + 5, yPos - 9, textWidth + 8, 18);
                            
                            // Tekst
                            ctx.fillStyle = '#ffffff';
                            ctx.fillText(labelText, xPos + 9, yPos);
                        }
                        
                        ctx.restore();
                    }
                };
                
                const chart = new Chart(ctx, {
                    type: 'scatter',
                    data: { datasets: datasets },
                    plugins: [drempelLabelsPlugin],
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false,
                        },
                        plugins: {
                            legend: { display: true, position: 'top' },
                            title: {
                                display: true,
                                text: 'Hartslag & Lactaat Progressie met Drempels',
                                font: { size: 12, weight: 'bold' }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        let label = context.dataset.label || '';
                                        if (label) label += ': ';
                                        if (context.parsed.y !== null) {
                                            label += context.parsed.y.toFixed(1);
                                        }
                                        return label;
                                    }
                                }
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
                                    stepSize: isLooptest ? 0.5 : (isZwemtest ? 0.1 : 10),
                                    font: { size: 9 },
                                    callback: function(value) {
                                        if (isLooptest) return value.toFixed(1);
                                        if (isZwemtest) {
                                            const minuten = Math.floor(value);
                                            const seconden = Math.round((value - minuten) * 60);
                                            return `${minuten}:${seconden.toString().padStart(2, '0')}`;
                                        }
                                        return Math.round(value);
                                    }
                                }
                            },
                            y: {
                                type: 'linear',
                                display: true,
                                position: 'left',
                                title: {
                                    display: true,
                                    text: 'Hartslag (bpm)',
                                    color: '#0a152dff',
                                    font: { weight: 'bold', size: 10 }
                                },
                                ticks: { color: '#0a152dff', font: { size: 9 } }
                            },
                            y1: {
                                type: 'linear',
                                display: true,
                                position: 'right',
                                title: {
                                    display: true,
                                    text: 'Lactaat (mmol/L)',
                                    color: '#84bfd6ff',
                                    font: { weight: 'bold', size: 10 }
                                },
                                ticks: { color: '#84bfd6ff', font: { size: 9 } },
                                grid: { drawOnChartArea: false }
                            }
                        }
                    }
                });
                
                console.log('📊 PDF Grafiek geladen met ' + datasets.length + ' datasets', {
                    lt1Value,
                    lt2Value,
                    heeftDrempels: datasets.length > 2
                });
            });
        </script>
        
        {{-- Toelichting Grafiek Analyse --}}
        <div class="rapport-toelichting-box">
            <h4>💡 Hoe interpreteer je deze grafiek?</h4>
            <p>Deze grafiek toont de progressie van hartslag en lactaat tijdens je inspanningstest.</p>
            <p><strong>Hartslag (zwarte lijn):</strong> Stijgt geleidelijk naarmate je harder werkt. Bij de drempelwaarden versnelt de stijging vaak.</p>
            <p><strong>Lactaat (blauwe lijn):</strong> Bij lage intensiteiten blijft lactaat laag en stabiel (aëroob). Vanaf een bepaald punt stijgt de curve steiler.</p>
            
            @if($analyseMethode)
            <p style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #fbbf24;">
                <strong>🔬 Toegepaste methode: {{ $analyseMethodeLabel }}</strong>
            </p>
            @endif
        </div>
        
    @else
        {{-- Geen drempelwaarden of testresultaten --}}
        <div class="rapport-geen-data">
            <div style="font-size: 32px; margin-bottom: 10px;">📊</div>
            <p style="color: #6b7280; font-size: 11px;">Geen grafiekdata beschikbaar</p>
            <p style="color: #9ca3af; font-size: 9px; margin-top: 5px;">Drempelwaarden en testresultaten zijn vereist voor grafiekanalyse</p>
        </div>
    @endif
</div>