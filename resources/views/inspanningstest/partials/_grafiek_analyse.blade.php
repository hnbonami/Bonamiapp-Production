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
    
    .grafiek-interpretatie-box {
        margin: 10px 0;
        padding: 10px 12px;
        background: #f0f9ff;
        border-left: 3px solid #c8e1eb;
        font-size: 10px;
        line-height: 1.5;
    }
    
    .grafiek-interpretatie-box ul {
        margin: 5px 0 0 0;
        padding-left: 0;
        list-style: none;
    }
    
    .grafiek-interpretatie-box li {
        padding: 3px 0;
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
    $isVeldtest = str_contains($testtype, 'veld');
    
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
                // Wacht tot Chart.js volledig geladen is
                if (typeof Chart === 'undefined') {
                    console.error('❌ Chart.js niet geladen!');
                    return;
                }
                
                console.log('✅ Chart.js geladen, versie:', Chart.version);
                
                const testresultaten = @json($testresultaten);
                const testtype = '{{ $inspanningstest->testtype }}';
                const isLooptest = testtype.includes('loop') || testtype.includes('lopen');
                const isZwemtest = testtype.includes('zwem');
                
                // Drempelwaarden ophalen
                const lt1Vermogen = {{ $inspanningstest->aerobe_drempel_vermogen ?? 'null' }};
                const lt2Vermogen = {{ $inspanningstest->anaerobe_drempel_vermogen ?? 'null' }};
                const lt1Snelheid = {{ $inspanningstest->aerobe_drempel_snelheid ?? 'null' }};
                const lt2Snelheid = {{ $inspanningstest->anaerobe_drempel_snelheid ?? 'null' }};
                
                console.log('🔍 Debug info:', {
                    testtype,
                    isLooptest,
                    isZwemtest,
                    lt1Vermogen,
                    lt2Vermogen,
                    lt1Snelheid,
                    lt2Snelheid,
                    testresultaten
                });
                
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
                
                // Bepaal drempelwaarden - gebruik ALTIJD vermogen kolommen want daar staan de waarden
                // Voor looptesten bevat 'vermogen' eigenlijk de snelheid in km/h
                const lt1Value = lt1Snelheid || lt1Vermogen;
                const lt2Value = lt2Snelheid || lt2Vermogen;
                
                console.log('📊 Drempelwaarden:', { 
                    lt1Value, 
                    lt2Value, 
                    isLooptest, 
                    isZwemtest,
                    'lt1Value type': typeof lt1Value,
                    'lt2Value type': typeof lt2Value,
                    'lt1Value === null': lt1Value === null,
                    'lt2Value === null': lt2Value === null,
                    'lt1Value !== null': lt1Value !== null,
                    'lt2Value !== null': lt2Value !== null,
                    'isNaN(lt1Value)': isNaN(lt1Value),
                    'isNaN(lt2Value)': isNaN(lt2Value)
                });
                
                const ctx = document.getElementById('testResultatenGrafiek');
                if (!ctx) {
                    console.error('Canvas element niet gevonden!');
                    return;
                }
                
                // Voeg drempellijnen toe als extra datasets (verticale lijnen)
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
                
                // Voeg LT1 drempellijn toe als verticale lijn dataset
                if (lt1Value !== null && lt1Value !== undefined && !isNaN(lt1Value)) {
                    // Gebruik een breed bereik voor Y-waarden zodat de lijn altijd zichtbaar is
                    const minY = Math.min(...hartslagData.map(d => d.y)) - 10;
                    const maxY = Math.max(...hartslagData.map(d => d.y)) + 10;
                    datasets.push({
                        label: 'LT1 (Aëroob)',
                        data: [
                            { x: lt1Value, y: minY },
                            { x: lt1Value, y: maxY }
                        ],
                        borderColor: '#f1a8a8ff',
                        borderWidth: 3,
                        borderDash: [8, 4],
                        pointRadius: 0,
                        pointHitRadius: 0,
                        showLine: true,
                        yAxisID: 'y',
                        fill: false,
                        tension: 0
                    });
                    console.log('✅ LT1 verticale lijn toegevoegd bij x=' + lt1Value, 'y-range:', minY, '-', maxY);
                }
                
                // Voeg LT2 drempellijn toe als verticale lijn dataset  
                if (lt2Value !== null && lt2Value !== undefined && !isNaN(lt2Value)) {
                    // Gebruik een breed bereik voor Y-waarden zodat de lijn altijd zichtbaar is
                    const minY = Math.min(...hartslagData.map(d => d.y)) - 10;
                    const maxY = Math.max(...hartslagData.map(d => d.y)) + 10;
                    datasets.push({
                        label: 'LT2 (Anaëroob)',
                        data: [
                            { x: lt2Value, y: minY },
                            { x: lt2Value, y: maxY }
                        ],
                        borderColor: '#f5bc59ff',
                        borderWidth: 3,
                        borderDash: [8, 4],
                        pointRadius: 0,
                        pointHitRadius: 0,
                        showLine: true,
                        yAxisID: 'y',
                        fill: false,
                        tension: 0
                    });
                    console.log('✅ LT2 verticale lijn toegevoegd bij x=' + lt2Value, 'y-range:', minY, '-', maxY);
                }
                
                console.log('📊 Totaal aantal datasets:', datasets.length);
                console.log('📊 Alle datasets:', datasets);
                
                // Custom plugin om labels aan de drempellijnen toe te voegen
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
                        
                        // Teken LT1 label
                        if (lt1Value !== null && lt1Value !== undefined && !isNaN(lt1Value)) {
                            const xPos = xScale.getPixelForValue(lt1Value);
                            const yPos = yScale.top + 5;
                            
                            // Achtergrond voor label
                            ctx.fillStyle = '#f1a8a8ff';
                            const labelText = `LT1: ${lt1Value}`;
                            const textWidth = ctx.measureText(labelText).width;
                            ctx.fillRect(xPos + 5, yPos, textWidth + 8, 18);
                            
                            // Tekst
                            ctx.fillStyle = 'white';
                            ctx.fillText(labelText, xPos + 9, yPos + 3);
                        }
                        
                        // Teken LT2 label
                        if (lt2Value !== null && lt2Value !== undefined && !isNaN(lt2Value)) {
                            const xPos = xScale.getPixelForValue(lt2Value);
                            const yPos = yScale.top + 30;
                            
                            // Achtergrond voor label
                            ctx.fillStyle = '#f5bc59ff';
                            const labelText = `LT2: ${lt2Value}`;
                            const textWidth = ctx.measureText(labelText).width;
                            ctx.fillRect(xPos + 5, yPos, textWidth + 8, 18);
                            
                            // Tekst
                            ctx.fillStyle = 'white';
                            ctx.fillText(labelText, xPos + 9, yPos + 3);
                        }
                        
                        ctx.restore();
                    }
                };
                
                const myChart = new Chart(ctx, {
                    type: 'scatter',
                    data: {
                        datasets: datasets
                    },
                    plugins: [drempelLabelsPlugin],
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top',
                                labels: { font: { size: 10 } }
                            },
                            title: {
                                display: true,
                                text: 'Hartslag & Lactaat Progressie',
                                font: { size: 12, weight: 'bold' }
                            }
                        },
                        scales: {
                            x: {
                                display: true,
                                reverse: isZwemtest,
                                title: {
                                    display: true,
                                    text: xAxisLabel,
                                    font: { size: 10, weight: 'bold' }
                                },
                                ticks: {
                                    font: { size: 9 },
                                    callback: function(value) {
                                        if (isLooptest) return value.toFixed(1);
                                        if (isZwemtest) {
                                            const min = Math.floor(value);
                                            const sec = Math.round((value - min) * 60);
                                            return `${min}:${sec.toString().padStart(2, '0')}`;
                                        }
                                        return Math.round(value);
                                    }
                                }
                            },
                            y: {
                                type: 'linear',
                                position: 'left',
                                title: {
                                    display: true,
                                    text: 'Hartslag (bpm)',
                                    color: '#0a152dff',
                                    font: { size: 10, weight: 'bold' }
                                },
                                ticks: { color: '#0a152dff', font: { size: 9 } }
                            },
                            y1: {
                                type: 'linear',
                                position: 'right',
                                title: {
                                    display: true,
                                    text: 'Lactaat (mmol/L)',
                                    color: '#84bfd6ff',
                                    font: { size: 10, weight: 'bold' }
                                },
                                ticks: { color: '#84bfd6ff', font: { size: 9 } },
                                grid: { drawOnChartArea: false }
                            }
                        }
                    }
                });
                
                console.log('✅ Grafiek succesvol aangemaakt!', myChart);
            });
        </script>
        
        {{-- Toelichting Grafiek Analyse --}}
        <div class="rapport-toelichting-box">
            <h4>💡 Hoe interpreteer je deze grafiek?</h4>
            <p>
                Deze grafiek toont de <strong>progressie van hartslag en lactaat</strong> tijdens je inspanningstest. 
                @if($isLooptest)
                    Naarmate je snelheid tijdens het lopen toeneemt, stijgen zowel je hartslag als je lactaatproductie.
                @elseif($isZwemtest)
                    Naarmate je zwemtempo toeneemt, stijgen zowel je hartslag als je lactaatproductie.
                @else
                    Naarmate het vermogen (Watt) toeneemt, stijgen zowel je hartslag als je lactaatproductie.
                @endif
            </p>
            <p>
                <strong>Hartslag (zwarte lijn):</strong> Stijgt geleidelijk naarmate je harder werkt. Bij de drempelwaarden versnelt de stijging vaak - dit wijst op overgang naar zwaarder werk voor je hart.
            </p>
            <p>
                <strong>Lactaat (blauwe lijn):</strong> Bij lage intensiteiten blijft lactaat laag en stabiel (aëroob). Vanaf een bepaald punt stijgt de curve steiler - je lichaam schakelt over naar anaërobe energieproductie en maakt lactaat sneller aan dan het kan afbreken.
            </p>
            <p>
                <strong>De drempellijnen:</strong> 
                @if($heeftDrempels)
                    De <strong style="color: #dc2626;">rode lijn (LT1)</strong> markeert waar lactaat begint te stijgen. Tot dit punt kun je zeer lang volhouden. 
                    De <strong style="color: #f97316;">oranje lijn (LT2)</strong> toont waar lactaat snel stijgt - boven dit punt kun je slechts beperkt volhouden.
                @else
                    Deze zijn nog niet bepaald voor jouw test.
                @endif
            </p>
            
            @if($analyseMethode)
            <p style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #fbbf24;">
                <strong>🔬 Toegepaste methode: {{ $analyseMethodeLabel }}</strong><br>
                @if($analyseMethode === 'dmax')
                    Objectieve, wetenschappelijk gevalideerde methode die het punt met maximale afstand tot een rechte lijn tussen begin- en eindpunt zoekt.
                @elseif($analyseMethode === 'dmax_modified')
                    Aangepaste D-max waarbij de hulplijn vanaf LT1 loopt - past beter bij goed getrainde sporters.
                @elseif($analyseMethode === 'lactaat_steady_state')
                    Klassieke methode met vaste waarden (2.0 mmol/L voor LT1, 4.0 mmol/L voor LT2).
                @elseif($analyseMethode === 'hartslag_deflectie')
                    Analyseert de verandering in hartslagstijging om drempels te bepalen.
                @elseif($analyseMethode === 'handmatig')
                    Handmatig bepaald door visuele inspectie - nuttig bij atypische lactaatprofielen.
                @endif
            </p>
            @endif
            
            <p style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #fbbf24;">
                <strong>Praktisch gebruik:</strong> 
                @if($isLooptest)
                    Gebruik deze kennis om je trainingssnelheden te bepalen.
                @elseif($isZwemtest)
                    Gebruik deze kennis om je trainingsintensiteiten te bepalen.
                @else
                    Gebruik deze kennis om je trainingszones nauwkeurig in te stellen.
                @endif
                Train voornamelijk onder LT1 voor basisconditie, tussen LT1-LT2 voor tempo, en beperkt boven LT2 voor intervallen.
            </p>
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
