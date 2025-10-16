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
        
        {{-- Chart.js Script --}}
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                if (typeof Chart === 'undefined') {
                    console.error('❌ Chart.js niet geladen!');
                    return;
                }
                
                const testresultaten = @json($testresultaten);
                const testtype = '{{ $inspanningstest->testtype }}';
                const isLooptest = testtype.includes('loop') || testtype.includes('lopen');
                const isZwemtest = testtype.includes('zwem');
                
                const lt1Value = {{ $inspanningstest->aerobe_drempel_snelheid ?? $inspanningstest->aerobe_drempel_vermogen ?? 'null' }};
                const lt2Value = {{ $inspanningstest->anaerobe_drempel_snelheid ?? $inspanningstest->anaerobe_drempel_vermogen ?? 'null' }};
                
                const hartslagData = testresultaten.map(stap => ({
                    x: stap.snelheid || stap.vermogen || 0,
                    y: stap.hartslag
                }));
                
                const lactaatData = testresultaten.map(stap => ({
                    x: stap.snelheid || stap.vermogen || 0,
                    y: stap.lactaat
                }));
                
                let xAxisLabel = 'Vermogen (Watt)';
                if (isLooptest) xAxisLabel = 'Snelheid (km/h)';
                else if (isZwemtest) xAxisLabel = 'Tempo (mm:ss/100m)';
                
                const ctx = document.getElementById('testResultatenGrafiek');
                if (!ctx) return;
                
                const datasets = [
                    {
                        label: 'Hartslag (bpm)',
                        data: hartslagData,
                        borderColor: '#0a152dff',
                        backgroundColor: 'rgba(37, 99, 235, 0.1)',
                        borderWidth: 2,
                        tension: 0.3,
                        yAxisID: 'y',
                        pointRadius: 4,
                        showLine: true
                    },
                    {
                        label: 'Lactaat (mmol/L)',
                        data: lactaatData,
                        borderColor: '#84bfd6ff',
                        backgroundColor: 'rgba(22, 163, 74, 0.1)',
                        borderWidth: 2,
                        tension: 0.3,
                        yAxisID: 'y1',
                        pointRadius: 4,
                        showLine: true
                    }
                ];
                
                // Voeg LT1 drempellijn toe
                if (lt1Value !== null && !isNaN(lt1Value)) {
                    const minY = Math.min(...hartslagData.map(d => d.y)) - 10;
                    const maxY = Math.max(...hartslagData.map(d => d.y)) + 10;
                    datasets.push({
                        label: 'LT1 (Aëroob)',
                        data: [{ x: lt1Value, y: minY }, { x: lt1Value, y: maxY }],
                        borderColor: '#f1a8a8ff',
                        borderWidth: 3,
                        borderDash: [8, 4],
                        pointRadius: 0,
                        showLine: true,
                        yAxisID: 'y',
                        fill: false,
                        tension: 0
                    });
                }
                
                // Voeg LT2 drempellijn toe
                if (lt2Value !== null && !isNaN(lt2Value)) {
                    const minY = Math.min(...hartslagData.map(d => d.y)) - 10;
                    const maxY = Math.max(...hartslagData.map(d => d.y)) + 10;
                    datasets.push({
                        label: 'LT2 (Anaëroob)',
                        data: [{ x: lt2Value, y: minY }, { x: lt2Value, y: maxY }],
                        borderColor: '#f5bc59ff',
                        borderWidth: 3,
                        borderDash: [8, 4],
                        pointRadius: 0,
                        showLine: true,
                        yAxisID: 'y',
                        fill: false,
                        tension: 0
                    });
                }
                
                // Custom plugin voor drempel labels
                const drempelLabelsPlugin = {
                    id: 'drempelLabels',
                    afterDatasetsDraw(chart) {
                        const ctx = chart.ctx;
                        const xScale = chart.scales.x;
                        const yScale = chart.scales.y;
                        
                        ctx.save();
                        ctx.font = 'bold 11px Tahoma, Arial, sans-serif';
                        ctx.textAlign = 'left';
                        ctx.textBaseline = 'top';
                        
                        if (lt1Value !== null && !isNaN(lt1Value)) {
                            const xPos = xScale.getPixelForValue(lt1Value);
                            const yPos = yScale.top + 5;
                            ctx.fillStyle = '#f1a8a8ff';
                            const labelText = `LT1: ${lt1Value}`;
                            const textWidth = ctx.measureText(labelText).width;
                            ctx.fillRect(xPos + 5, yPos, textWidth + 8, 18);
                            ctx.fillStyle = 'white';
                            ctx.fillText(labelText, xPos + 9, yPos + 3);
                        }
                        
                        if (lt2Value !== null && !isNaN(lt2Value)) {
                            const xPos = xScale.getPixelForValue(lt2Value);
                            const yPos = yScale.top + 30;
                            ctx.fillStyle = '#f5bc59ff';
                            const labelText = `LT2: ${lt2Value}`;
                            const textWidth = ctx.measureText(labelText).width;
                            ctx.fillRect(xPos + 5, yPos, textWidth + 8, 18);
                            ctx.fillStyle = 'white';
                            ctx.fillText(labelText, xPos + 9, yPos + 3);
                        }
                        
                        ctx.restore();
                    }
                };
                
                new Chart(ctx, {
                    type: 'scatter',
                    data: { datasets: datasets },
                    plugins: [drempelLabelsPlugin],
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: true, position: 'top', labels: { font: { size: 10 } } },
                            title: { display: true, text: 'Hartslag & Lactaat Progressie', font: { size: 12, weight: 'bold' } }
                        },
                        scales: {
                            x: {
                                display: true,
                                reverse: isZwemtest,
                                title: { display: true, text: xAxisLabel, font: { size: 10, weight: 'bold' } },
                                ticks: { font: { size: 9 } }
                            },
                            y: {
                                type: 'linear',
                                position: 'left',
                                title: { display: true, text: 'Hartslag (bpm)', color: '#0a152dff', font: { size: 10, weight: 'bold' } },
                                ticks: { color: '#0a152dff', font: { size: 9 } }
                            },
                            y1: {
                                type: 'linear',
                                position: 'right',
                                title: { display: true, text: 'Lactaat (mmol/L)', color: '#84bfd6ff', font: { size: 10, weight: 'bold' } },
                                ticks: { color: '#84bfd6ff', font: { size: 9 } },
                                grid: { drawOnChartArea: false }
                            }
                        }
                    }
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