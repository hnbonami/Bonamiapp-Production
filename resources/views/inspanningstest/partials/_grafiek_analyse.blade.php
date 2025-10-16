{{-- Grafiek Analyse Partial - Results Page Versie --}}

@php
    // Bepaal testtype en variabelen eerst
    $testtype = $inspanningstest->testtype ?? 'fietstest';
    $isLooptest = str_contains($testtype, 'loop') || str_contains($testtype, 'lopen');
    $isZwemtest = str_contains($testtype, 'zwem');
    
    $testresultaten = $inspanningstest->testresultaten ?? collect();
    if (is_string($testresultaten)) {
        $testresultaten = json_decode($testresultaten) ?? [];
    }
    $testresultaten = collect($testresultaten);
    
    $analyseMethode = $inspanningstest->analyse_methode ?? null;
    $analyseMethodeLabel = match($analyseMethode) {
        'dmax' => 'D-max Methode',
        'dmax_modified' => 'D-max Modified',
        'lactaat_steady_state' => 'Lactaat Steady State',
        'hartslag_deflectie' => 'Hartslagdeflectie',
        'handmatig' => 'Handmatig',
        default => 'Niet gespecificeerd'
    };
    
    $heeftDrempels = ($inspanningstest->aerobe_drempel_vermogen || $inspanningstest->aerobe_drempel_snelheid) 
                  && ($inspanningstest->anaerobe_drempel_vermogen || $inspanningstest->anaerobe_drempel_snelheid);
@endphp

<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-bold text-gray-900">📊 Grafiek Analyse</h3>
        @if($analyseMethode)
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                {{ $analyseMethodeLabel }}
            </span>
        @endif
    </div>

    @if($heeftDrempels && count($testresultaten) > 0)
        {{-- Grafiek Container --}}
        <div class="bg-gray-50 rounded-lg p-4 mb-6" style="height: 500px;">
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
                        borderColor: '#2563eb',
                        backgroundColor: 'rgba(37, 99, 235, 0.1)',
                        borderWidth: 3,
                        tension: 0.3,
                        yAxisID: 'y',
                        pointRadius: 5,
                        pointHoverRadius: 7,
                        showLine: true
                    },
                    {
                        label: 'Lactaat (mmol/L)',
                        data: lactaatData,
                        borderColor: '#16a34a',
                        backgroundColor: 'rgba(22, 163, 74, 0.1)',
                        borderWidth: 3,
                        tension: 0.3,
                        yAxisID: 'y1',
                        pointRadius: 5,
                        pointHoverRadius: 7,
                        showLine: true
                    }
                ];
                
                if (lt1Value !== null && !isNaN(lt1Value)) {
                    const minY = Math.min(...hartslagData.map(d => d.y)) - 10;
                    const maxY = Math.max(...hartslagData.map(d => d.y)) + 10;
                    datasets.push({
                        label: 'LT1 (Aëroob)',
                        data: [{ x: lt1Value, y: minY }, { x: lt1Value, y: maxY }],
                        borderColor: '#dc2626',
                        borderWidth: 3,
                        borderDash: [10, 5],
                        pointRadius: 0,
                        showLine: true,
                        yAxisID: 'y'
                    });
                }
                
                if (lt2Value !== null && !isNaN(lt2Value)) {
                    const minY = Math.min(...hartslagData.map(d => d.y)) - 10;
                    const maxY = Math.max(...hartslagData.map(d => d.y)) + 10;
                    datasets.push({
                        label: 'LT2 (Anaëroob)',
                        data: [{ x: lt2Value, y: minY }, { x: lt2Value, y: maxY }],
                        borderColor: '#f97316',
                        borderWidth: 3,
                        borderDash: [10, 5],
                        pointRadius: 0,
                        showLine: true,
                        yAxisID: 'y'
                    });
                }
                
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
                                position: 'top',
                                labels: {
                                    usePointStyle: true,
                                    padding: 15,
                                    font: { size: 12, weight: '600' }
                                }
                            },
                            title: {
                                display: true,
                                text: 'Hartslag & Lactaat Progressie',
                                font: { size: 16, weight: 'bold' },
                                padding: { bottom: 20 }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                padding: 12,
                                titleFont: { size: 14, weight: 'bold' },
                                bodyFont: { size: 13 },
                                displayColors: true
                            }
                        },
                        scales: {
                            x: {
                                display: true,
                                reverse: isZwemtest,
                                title: {
                                    display: true,
                                    text: xAxisLabel,
                                    font: { size: 13, weight: 'bold' }
                                },
                                grid: { color: 'rgba(0, 0, 0, 0.05)' },
                                ticks: { font: { size: 11 } }
                            },
                            y: {
                                type: 'linear',
                                position: 'left',
                                title: {
                                    display: true,
                                    text: 'Hartslag (bpm)',
                                    color: '#2563eb',
                                    font: { size: 13, weight: 'bold' }
                                },
                                ticks: { color: '#2563eb', font: { size: 11 } },
                                grid: { color: 'rgba(37, 99, 235, 0.1)' }
                            },
                            y1: {
                                type: 'linear',
                                position: 'right',
                                title: {
                                    display: true,
                                    text: 'Lactaat (mmol/L)',
                                    color: '#16a34a',
                                    font: { size: 13, weight: 'bold' }
                                },
                                ticks: { color: '#16a34a', font: { size: 11 } },
                                grid: { drawOnChartArea: false }
                            }
                        }
                    }
                });
            });
        </script>

        {{-- Toelichting --}}
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-r-lg">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <h4 class="text-sm font-bold text-yellow-800">💡 Grafiek Interpretatie</h4>
                    <p class="mt-2 text-sm text-yellow-700">
                        Deze grafiek toont hoe je hartslag en lactaatwaarden stijgen tijdens de test. 
                        De verticale lijnen markeren je drempelwaarden - de belangrijkste referentiepunten voor training.
                    </p>
                </div>
            </div>
        </div>
        
    @else
        {{-- Geen data beschikbaar --}}
        <div class="text-center py-12 bg-gray-50 rounded-lg">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Geen grafiekdata beschikbaar</h3>
            <p class="mt-1 text-sm text-gray-500">Drempelwaarden en testresultaten zijn vereist voor grafiekanalyse</p>
        </div>
    @endif
</div>
