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
            
            {{-- Toelichting Grafiek Analyse --}}
            <div class="mt-6 p-6" style="background-color: #fff8e1;">
                <div class="flex items-start">
                    <div class="flex-shrink-0 mr-4 text-2xl">
                        💡
                    </div>
                    <div class="flex-1">
                        <h4 class="text-base font-bold text-gray-900 mb-3">Hoe interpreteer je deze grafiek?</h4>
                        <div class="text-sm text-gray-700 space-y-3">
                            <p>
                                Deze grafiek toont de <strong>progressie van hartslag en lactaat</strong> tijdens je inspanningstest. 
                                @if($isLooptest)
                                    Naarmate je snelheid tijdens het lopen toeneemt, stijgen zowel je hartslag als je lactaatproductie.
                                @elseif($isZwemtest)
                                    Naarmate je zwemtempo toeneemt, stijgen zowel je hartslag als je lactaatproductie.
                                @else
                                    Naarmate het vermogen (Watt) toeneemt, stijgen zowel je hartslag als je lactaatproductie.
                                @endif
                                De manier waarop deze waarden stijgen vertelt ons veel over je conditie en trainingstoestand.
                            </p>
                            <p>
                                <strong>Hartslag (blauwe lijn):</strong> In het begin van de test stijgt je hartslag geleidelijk en vrij lineair. 
                                Naarmate de intensiteit toeneemt, moet je hart harder werken om voldoende zuurstof naar je spieren te pompen. 
                                Bij de drempelwaarden zie je vaak dat de hartslagstijging versnelt - dit wijst op een overgang naar zwaarder werk voor je cardiovasculaire systeem.
                            </p>
                            <p>
                                <strong>Lactaat (groene lijn):</strong> Deze curve is cruciaal. Bij lage intensiteiten blijft lactaat relatief laag en stabiel - 
                                je lichaam kan het geproduceerde lactaat nog goed afbreken (aëroob = met zuurstof). Vanaf een bepaald punt begint de curve steiler te stijgen - 
                                dit is het moment waarop je lichaam overschakelt naar meer anaërobe (zonder zuurstof) energieproductie en lactaat sneller aanmaakt dan het kan afbreken.
                            </p>
                            <p>
                                <strong>De drempellijnen:</strong> 
                                @if($heeftDrempels)
                                    De <span style="color: #dc2626; font-weight: bold;">rode lijn (LT1 - aërobe drempel)</span> markeert het punt waarop lactaat begint te stijgen boven het basisniveau. 
                                    Tot dit punt kun je zeer lang volhouden zonder vermoeidheid. 
                                    De <span style="color: #f97316; font-weight: bold;">oranje lijn (LT2 - anaërobe drempel)</span> toont het punt waarop lactaat snel stijgt. 
                                    Boven dit punt kun je slechts beperkte tijd volhouden voordat vermoeidheid toeslaat.
                                @else
                                    Deze zijn nog niet bepaald voor jouw test. Met drempelwaarden zou je de overgangen tussen aëroob en anaëroob werk visueel kunnen zien.
                                @endif
                            </p>
                            
                            @if($analyseMethode)
                                <div class="mt-4 pt-4 border-t border-gray-300">
                                    <p class="font-bold text-gray-900 mb-2">🔬 Toegepaste analyse methode: {{ $analyseMethodeLabel }}</p>
                                    @if($analyseMethode === 'dmax')
                                        <p>
                                            De <strong>D-max methode</strong> is een objectieve, wetenschappelijk gevalideerde methode. 
                                            Deze zoekt het punt op de lactaatcurve met de maximale afstand tot een rechte lijn tussen begin- en eindpunt. 
                                            Dit punt vertegenwoordigt de anaërobe drempel (LT2). De aërobe drempel (LT1) wordt bepaald als het punt waar lactaat 
                                            0.4 mmol/L boven je baseline (rustwaarde) komt. Deze methode is zeer betrouwbaar en reproduceerbaar.
                                        </p>
                                    @elseif($analyseMethode === 'dmax_modified')
                                        <p>
                                            De <strong>D-max Modified methode</strong> is een aangepaste versie waarbij de hulplijn niet vanaf het startpunt, 
                                            maar vanaf de aërobe drempel (LT1) naar het eindpunt loopt. Dit geeft vaak een iets hogere anaërobe drempel en 
                                            kan beter passen bij individuele lactaatprofielen, vooral bij goed getrainde sporters die een fluwelere lactaatcurve hebben.
                                        </p>
                                    @elseif($analyseMethode === 'lactaat_steady_state')
                                        <p>
                                            De <strong>Lactaat Steady State methode</strong> gebruikt vaste lactaatwaarden: 2.0 mmol/L voor LT1 en 4.0 mmol/L voor LT2. 
                                            Dit is een klassieke en eenvoudige benadering. Het voordeel is dat het makkelijk te begrijpen en toe te passen is, 
                                            maar het houdt geen rekening met individuele verschillen in baseline lactaat of lactaatmetabolisme.
                                        </p>
                                    @elseif($analyseMethode === 'hartslag_deflectie')
                                        <p>
                                            De <strong>Hartslagdeflectie methode</strong> analyseert de verandering in hartslagstijging in plaats van lactaat. 
                                            Wanneer je lichaam zwaarder moet werken (overgang naar anaëroob), zie je vaak een versnelling in de hartslagstijging. 
                                            Deze methode is nuttig als aanvulling op lactaatmetingen en kan helpen bij het valideren van drempelwaarden.
                                        </p>
                                    @elseif($analyseMethode === 'handmatig')
                                        <p>
                                            De drempelwaarden zijn <strong>handmatig bepaald</strong> door visuele inspectie van de curves. 
                                            Dit vereist ervaring van de tester maar kan zeer nauwkeurig zijn, vooral bij atypische lactaatprofielen 
                                            waar automatische methoden minder betrouwbaar zijn. De tester kijkt naar het punt waar de lactaatcurve begint 
                                            te "buigen" (LT1) en waar deze sterk versnelt (LT2).
                                        </p>
                                    @endif
                                </div>
                            @endif
                            
                            <p class="mt-4 pt-4 border-t border-gray-300">
                                <strong>Praktisch gebruik:</strong> Deze grafiek helpt je om je eigen lichaam beter te begrijpen. 
                                @if($isLooptest)
                                    Je ziet precies bij welke snelheid je lichaam "omschakelt". Gebruik deze kennis om je trainingssnelheden te bepalen.
                                @elseif($isZwemtest)
                                    Je ziet precies bij welk tempo je lichaam "omschakelt". Gebruik deze kennis om je trainingsintensiteiten te bepalen.
                                @else
                                    Je ziet precies bij welk vermogen je lichaam "omschakelt". Gebruik deze kennis om je trainingszones nauwkeurig in te stellen.
                                @endif
                                Train voornamelijk onder je LT1 voor basisconditie, tussen LT1 en LT2 voor tempo-ontwikkeling, 
                                en slechts beperkt boven LT2 voor hoogintensieve intervallen.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
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
