{{-- Grafiek Analyse Sectie voor Inspanningstest Results --}}
{{-- Variabele sleutel: {{INSPANNINGSTEST_GRAFIEK}} --}}

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
    $heeftDrempels = $inspanningstest->aerobe_drempel_vermogen && $inspanningstest->anaerobe_drempel_vermogen;
@endphp

<div class="bg-white rounded-lg shadow-md overflow-hidden mb-6" style="border: 2px solid #c8e1eb;">
    {{-- Header --}}
    <div class="px-6 py-4" style="background-color: #c8e1eb; border-bottom: 2px solid #a8c1cb;">
        <div class="flex justify-between items-center">
            <div>
                <h3 class="text-xl font-bold text-gray-900">📈 Grafiek Analyse</h3>
                <p class="text-sm text-gray-700 mt-1">Visualisatie van hartslag en lactaat progressie</p>
            </div>
            @if($analyseMethode)
                <span class="text-xs font-semibold text-gray-700 bg-white px-3 py-1 rounded-full border-2" style="border-color: #a8c1cb;">
                    {{ $analyseMethodeLabel }}
                </span>
            @endif
        </div>
    </div>
    
    {{-- Content --}}
    <div class="p-6">
        @if($heeftDrempels && count($testresultaten) > 0)
            {{-- Grafiek Instructies --}}
            <div class="bg-blue-50 rounded-lg p-4 mb-4" style="border: 1px solid #c8e1eb;">
                <h4 class="text-sm font-bold text-gray-900 mb-2">📊 Grafiek Interpretatie:</h4>
                <ul class="text-sm text-gray-700 space-y-1">
                    <li><span class="font-semibold" style="color: #2563eb;">● Blauwe lijn:</span> Hartslag progressie tijdens de test</li>
                    <li><span class="font-semibold" style="color: #16a34a;">● Groene lijn:</span> Lactaat progressie tijdens de test</li>
                    <li><span class="font-semibold" style="color: #dc2626;">● Rode lijn:</span> Aërobe drempel (LT1)</li>
                    <li><span class="font-semibold" style="color: #f59e0b;">● Oranje lijn:</span> Anaërobe drempel (LT2)</li>
                </ul>
            </div>
            
            {{-- Grafiek Container met Chart.js --}}
            <div class="bg-white rounded-lg p-4 mb-4" style="border: 1px solid #c8e1eb;">
                <canvas id="testResultatenGrafiek" style="max-height: 400px;"></canvas>
            </div>
            
            {{-- Chart.js Script --}}
            <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    // Haal testresultaten op
                    const testresultaten = @json($testresultaten);
                    
                    // Bereid grafiek data voor - gebruik juiste veld op basis van testtype
                    const testtype = '{{ $inspanningstest->testtype }}';
                    const isLooptest = testtype.includes('loop') || testtype.includes('lopen');
                    const isZwemtest = testtype.includes('zwem');
                    
                    // Voor X-as: gebruik snelheid voor loop/zwem, vermogen voor fiets
                    const xValues = testresultaten.map(stap => {
                        if (isLooptest || isZwemtest) {
                            return stap.snelheid || 0;
                        }
                        return stap.vermogen || 0;
                    });
                    
                    const hartslagData = testresultaten.map(stap => ({
                        x: stap.snelheid || stap.vermogen || 0,
                        y: stap.hartslag
                    }));
                    
                    const lactaatData = testresultaten.map(stap => ({
                        x: stap.snelheid || stap.vermogen || 0,
                        y: stap.lactaat
                    }));
                    
                    console.log('📊 Grafiek data:', {
                        testtype: '{{ $inspanningstest->testtype }}',
                        isLooptest,
                        xValues,
                        hartslagData,
                        lactaatData
                    });
                    
                    // Gebruik globale drempelwaarden (geen dubbele declaratie!)
                    const { lt1Vermogen, lt2Vermogen } = window.drempelwaardenResults || {};
                    const lt1Hartslag = {{ $inspanningstest->aerobe_drempel_hartslag ?? 'null' }};
                    const lt2Hartslag = {{ $inspanningstest->anaerobe_drempel_hartslag ?? 'null' }};
                    
                    // Bereid X-as label voor
                    let xAxisLabel = 'Vermogen (Watt)';
                    
                    if (isLooptest) {
                        xAxisLabel = 'Snelheid (km/h)';
                    } else if (isZwemtest) {
                        xAxisLabel = 'Tempo (mm:ss/100m)';
                    }
                    
                    // Configureer Chart.js
                    const ctx = document.getElementById('testResultatenGrafiek').getContext('2d');
                    const chart = new Chart(ctx, {
                        type: 'scatter', // BELANGRIJK: scatter voor xy-data in plaats van line
                        data: {
                            datasets: [
                                {
                                    label: 'Hartslag (bpm)',
                                    data: hartslagData,
                                    borderColor: '#2563eb',
                                    backgroundColor: 'rgba(37, 99, 235, 0.1)',
                                    borderWidth: 3,
                                    tension: 0.4,
                                    yAxisID: 'y',
                                    pointRadius: 5,
                                    pointHoverRadius: 7,
                                    showLine: true // Toon lijn tussen punten
                                },
                                {
                                    label: 'Lactaat (mmol/L)',
                                    data: lactaatData,
                                    borderColor: '#16a34a',
                                    backgroundColor: 'rgba(22, 163, 74, 0.1)',
                                    borderWidth: 3,
                                    tension: 0.4,
                                    yAxisID: 'y1',
                                    pointRadius: 5,
                                    pointHoverRadius: 7,
                                    showLine: true // Toon lijn tussen punten
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            interaction: {
                                mode: 'index',
                                intersect: false,
                            },
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'top',
                                },
                                title: {
                                    display: true,
                                    text: 'Hartslag & Lactaat Progressie met Drempels',
                                    font: {
                                        size: 16,
                                        weight: 'bold'
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            let label = context.dataset.label || '';
                                            if (label) {
                                                label += ': ';
                                            }
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
                                    reverse: isZwemtest, // Keer X-as om voor zwemtesten (snel = rechts)
                                    title: {
                                        display: true,
                                        text: xAxisLabel,
                                        font: {
                                            weight: 'bold'
                                        }
                                    },
                                    ticks: {
                                        // Dynamische step size op basis van testtype
                                        stepSize: isLooptest ? 0.5 : (isZwemtest ? 0.1 : 10),
                                        callback: function(value) {
                                            // Voor looptesten: toon met 1 decimaal
                                            if (isLooptest) {
                                                return value.toFixed(1);
                                            }
                                            // Voor zwemtesten: toon mm:ss formaat
                                            if (isZwemtest) {
                                                const minuten = Math.floor(value);
                                                const seconden = Math.round((value - minuten) * 60);
                                                return `${minuten}:${seconden.toString().padStart(2, '0')}`;
                                            }
                                            // Voor fietstesten: toon hele getallen (per 10 watt)
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
                                        color: '#2563eb',
                                        font: {
                                            weight: 'bold'
                                        }
                                    },
                                    ticks: {
                                        color: '#2563eb'
                                    }
                                },
                                y1: {
                                    type: 'linear',
                                    display: true,
                                    position: 'right',
                                    title: {
                                        display: true,
                                        text: 'Lactaat (mmol/L)',
                                        color: '#16a34a',
                                        font: {
                                            weight: 'bold'
                                        }
                                    },
                                    ticks: {
                                        color: '#16a34a'
                                    },
                                    grid: {
                                        drawOnChartArea: false,
                                    }
                                }
                            }
                        }
                    });
                    
                    console.log('📊 Grafiek succesvol geladen', {
                        lt1Vermogen,
                        lt2Vermogen
                    });
                });
            </script>
            
            {{-- Analyse Uitleg --}}
            @if($analyseMethode === 'dmax')
                <div class="bg-white rounded-lg p-4" style="border: 1px solid #c8e1eb;">
                    <h4 class="text-sm font-bold text-gray-900 mb-2">🔬 D-max Methode Uitleg:</h4>
                    <p class="text-sm text-gray-700 mb-2">
                        De D-max methode bepaalt de anaërobe drempel door het punt te vinden met de <strong>maximale afstand</strong> 
                        tussen de lactaatcurve en een rechte lijn van het eerste naar het laatste meetpunt.
                    </p>
                    <ul class="text-sm text-gray-600 list-disc pl-5 space-y-1">
                        <li>Aërobe drempel: baseline lactaat + 0.4 mmol/L</li>
                        <li>Anaërobe drempel: maximale afstand tussen curve en hulplijn</li>
                        <li>Wetenschappelijk gevalideerde methode voor drempelbepaling</li>
                    </ul>
                </div>
            @elseif($analyseMethode === 'dmax_modified')
                <div class="bg-white rounded-lg p-4" style="border: 1px solid #c8e1eb;">
                    <h4 class="text-sm font-bold text-gray-900 mb-2">🔬 D-max Modified Methode Uitleg:</h4>
                    <p class="text-sm text-gray-700 mb-2">
                        De D-max Modified methode is een <strong>aangepaste versie</strong> waarbij de hulplijn loopt van de 
                        aërobe drempel (baseline + 0.4 mmol/L) naar het laatste meetpunt.
                    </p>
                    <ul class="text-sm text-gray-600 list-disc pl-5 space-y-1">
                        <li>Aërobe drempel: baseline lactaat + configureerbare waarde (standaard 0.4 mmol/L)</li>
                        <li>Anaërobe drempel: maximale afstand tussen curve en aangepaste hulplijn</li>
                        <li>Geschikt voor individuele lactaatprofielen</li>
                    </ul>
                </div>
            @elseif($analyseMethode === 'lactaat_steady_state')
                <div class="bg-white rounded-lg p-4" style="border: 1px solid #c8e1eb;">
                    <h4 class="text-sm font-bold text-gray-900 mb-2">🔬 Lactaat Steady State Methode Uitleg:</h4>
                    <p class="text-sm text-gray-700 mb-2">
                        Deze methode gebruikt <strong>vaste lactaatwaarden</strong> als drempelmarkeringen.
                    </p>
                    <ul class="text-sm text-gray-600 list-disc pl-5 space-y-1">
                        <li>Aërobe drempel: 2.0 mmol/L lactaat</li>
                        <li>Anaërobe drempel: 4.0 mmol/L lactaat</li>
                        <li>Klassieke benadering voor steady-state bepaling</li>
                    </ul>
                </div>
            @elseif($analyseMethode === 'hartslag_deflectie')
                <div class="bg-white rounded-lg p-4" style="border: 1px solid #c8e1eb;">
                    <h4 class="text-sm font-bold text-gray-900 mb-2">🔬 Hartslagdeflectie Methode Uitleg:</h4>
                    <p class="text-sm text-gray-700 mb-2">
                        Deze methode analyseert de <strong>verandering in hartslagstijging</strong> tijdens de test.
                    </p>
                    <ul class="text-sm text-gray-600 list-disc pl-5 space-y-1">
                        <li>Zoekt naar afbuigpunten in de hartslagcurve</li>
                        <li>Eerste deflectie = aërobe drempel</li>
                        <li>Maximale deflectie = anaërobe drempel</li>
                    </ul>
                </div>
            @elseif($analyseMethode === 'handmatig')
                <div class="bg-white rounded-lg p-4" style="border: 1px solid #c8e1eb;">
                    <h4 class="text-sm font-bold text-gray-900 mb-2">✋ Handmatige Drempelbepaling:</h4>
                    <p class="text-sm text-gray-700">
                        De drempelwaarden zijn <strong>handmatig bepaald</strong> op basis van visuele inspectie 
                        van de lactaat- en hartslagcurves door de tester.
                    </p>
                </div>
            @endif
            
        @else
            {{-- Geen drempelwaarden of testresultaten --}}
            <div class="text-center py-8">
                <svg class="w-16 h-16 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                <p class="text-gray-500 text-sm">Geen grafiekdata beschikbaar</p>
                <p class="text-gray-400 text-xs mt-1">Drempelwaarden en testresultaten zijn vereist voor grafiekanalyse</p>
            </div>
        @endif
    </div>
</div>
