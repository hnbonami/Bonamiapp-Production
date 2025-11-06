@extends('layouts.app')

@section('content')
<!-- Chart.js voor grafieken -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6">
                <div class="flex justify-between items-start">
                    <div>
                        <h1 class="text-3xl font-extrabold text-gray-900">Inspanningstest Resultaten - {{ $klant->voornaam}} {{ $klant->naam }}</h1>
                        <p class="text-lg text-gray-600 mt-2">{{ $klant->naam }} - {{ \Carbon\Carbon::parse($inspanningstest->datum)->format('d-m-Y') }}</p>
                    </div>
                    <div class="flex gap-3">
                        <a href="{{ route('klanten.show', $klant->id) }}" 
                           class="rounded-full px-6 py-2 text-gray-800 font-bold text-sm hover:opacity-80 transition" 
                           style="background-color: #c8e1eb;">
                            Terug naar Klant
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Algemene Informatie --}}
        @include('inspanningstest.partials._algemene_info_results')

        <!-- Trainingstatus Sectie -->
        @include('inspanningstest.partials._trainingstatus_results')

        <!-- Testresultaten Tabel -->
        @include('inspanningstest.partials._testresultaten_results')

        <!-- Grafiek Analyse -->
        @include('inspanningstest.partials._grafiek_analyse')

        <!-- Drempelwaarden Overzicht Tabel -->
        @include('inspanningstest.partials._drempelwaarden_overzicht')

        <!-- Trainingszones Tabel -->
        @include('inspanningstest.partials._trainingszones_results')

        <!-- AI Analyse -->
        @include('inspanningstest.partials._ai_analyse_results')

        <!-- Rapport Generatie Knop - Onderaan Pagina -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
            <div class="p-6 text-center">
                <h3 class="text-xl font-bold text-gray-900 mb-4">📄 Rapport Genereren</h3>
                <p class="text-gray-600 mb-6">Genereer een professioneel rapport op basis van de sjablonen</p>
                <a href="{{ route('inspanningstest.sjabloon-rapport', ['klant' => $klant->id, 'test' => $inspanningstest->id]) }}" 
                   class="inline-block rounded-full px-8 py-3 bg-green-500 text-white font-bold text-lg hover:bg-green-600 transition shadow-lg">
                    📄 Genereer Rapport
                </a>
            </div>
        </div>

    </div>
</div>

<script>
    // Globale drempelwaarden voor gebruik in plugin
    window.drempelwaardenResults = {
        lt1Vermogen: {{ $inspanningstest->aerobe_drempel_vermogen ?? 'null' }},
        lt2Vermogen: {{ $inspanningstest->anaerobe_drempel_vermogen ?? 'null' }}
    };

    // Custom plugin voor verticale drempellijnen - HYBRIDE VERSIE (werkt voor ALLE testtypes)
    const verticalLinePluginResults = {
        id: 'verticalLinesResults',
        afterDatasetsDraw(chart) {
            const { ctx, chartArea: { top, bottom, left, right }, scales, data } = chart;
            const { lt1Vermogen, lt2Vermogen } = window.drempelwaardenResults;
            
            if (!lt1Vermogen && !lt2Vermogen) {
                console.log('⚠️ Geen drempelwaarden om te tekenen');
                return;
            }
            
            const xScale = scales.x;
            const labels = data.labels || [];
            
            console.log('📊 Plugin debug:', {
                lt1: lt1Vermogen,
                lt2: lt2Vermogen,
                labels: labels,
                scaleType: xScale?.type,
                scaleMin: xScale?.min,
                scaleMax: xScale?.max
            });
            
            // FUNCTIE: Bereken X-positie (werkt voor linear EN category scales)
            function berekenXPositie(waarde) {
                // Probeer eerst Chart.js getPixelForValue (voor linear scales)
                if (xScale && typeof xScale.getPixelForValue === 'function') {
                    try {
                        const pixel = xScale.getPixelForValue(waarde);
                        // Check of het resultaat geldig is
                        if (!isNaN(pixel) && pixel >= left && pixel <= right) {
                            console.log(`  ✅ getPixelForValue(${waarde}) = ${pixel}`);
                            return pixel;
                        }
                    } catch (e) {
                        console.log(`  ⚠️ getPixelForValue failed:`, e.message);
                    }
                }
                
                // Fallback: Handmatige interpolatie tussen labels
                if (labels.length > 0) {
                    const numLabels = labels.map(l => parseFloat(l));
                    const minVal = Math.min(...numLabels);
                    const maxVal = Math.max(...numLabels);
                    const chartWidth = right - left;
                    
                    if (waarde >= minVal && waarde <= maxVal) {
                        const percentage = (waarde - minVal) / (maxVal - minVal);
                        const pixel = left + (percentage * chartWidth);
                        console.log(`  ✅ Handmatige interpolatie: ${waarde} -> ${pixel}px (${minVal}-${maxVal})`);
                        return pixel;
                    }
                }
                
                console.log(`  ❌ Kan geen positie berekenen voor ${waarde}`);
                return null;
            }
            
            // Teken rode verticale lijn voor LT1
            if (lt1Vermogen) {
                const lt1X = berekenXPositie(lt1Vermogen);
                
                if (lt1X !== null) {
                    ctx.save();
                    ctx.strokeStyle = '#dc2626'; // Rood
                    ctx.lineWidth = 3;
                    ctx.setLineDash([10, 5]);
                    ctx.beginPath();
                    ctx.moveTo(lt1X, top);
                    ctx.lineTo(lt1X, bottom);
                    ctx.stroke();
                    ctx.setLineDash([]);
                    
                    // Label bovenaan
                    ctx.fillStyle = '#dc2626';
                    ctx.font = 'bold 12px sans-serif';
                    const lt1Label = lt1Vermogen % 1 === 0 ? lt1Vermogen : lt1Vermogen.toFixed(1);
                    ctx.fillText(`LT1: ${lt1Label}`, lt1X + 5, top + 15);
                    ctx.restore();
                    
                    console.log('✅ LT1 lijn getekend op', lt1X, 'px');
                }
            }
            
            // Teken oranje verticale lijn voor LT2
            if (lt2Vermogen) {
                const lt2X = berekenXPositie(lt2Vermogen);
                
                if (lt2X !== null) {
                    ctx.save();
                    ctx.strokeStyle = '#f97316'; // Oranje
                    ctx.lineWidth = 3;
                    ctx.setLineDash([10, 5]);
                    ctx.beginPath();
                    ctx.moveTo(lt2X, top);
                    ctx.lineTo(lt2X, bottom);
                    ctx.stroke();
                    ctx.setLineDash([]);
                    
                    // Label bovenaan
                    ctx.fillStyle = '#f97316';
                    ctx.font = 'bold 12px sans-serif';
                    const lt2Label = lt2Vermogen % 1 === 0 ? lt2Vermogen : lt2Vermogen.toFixed(1);
                    ctx.fillText(`LT2: ${lt2Label}`, lt2X + 5, top + 30);
                    ctx.restore();
                    
                    console.log('✅ LT2 lijn getekend op', lt2X, 'px');
                }
            }
        }
    };

    // Registreer het plugin globaal
    Chart.register(verticalLinePluginResults);
    console.log('✅ Vertical line plugin geregistreerd (HYBRIDE VERSIE - werkt voor alle testtypes)');
</script>
@endsection