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
                        <h1 class="text-3xl font-extrabold text-gray-900">Inspanningstest Resultaten</h1>
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

        <!-- Trainingstatus Sectie -->
        @include('inspanningstest.partials._trainingstatus_results')

        <!-- Testresultaten Tabel -->
        @include('inspanningstest.partials._testresultaten_results')

        <!-- Trainingszones Tabel -->
        @include('inspanningstest.partials._trainingszones_results')

        <!-- Drempelwaarden Overzicht Tabel -->
        @include('inspanningstest.partials._drempelwaarden_overzicht')

        <!-- Grafiek Analyse -->
        @include('inspanningstest.partials._grafiek_analyse')

        <!-- Drempelwaarden (oude sectie) -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6">
                <h3 class="text-xl font-bold mb-4">Drempelwaarden</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Aërobe Drempel -->
                    <div class="bg-red-50 border-2 border-red-200 rounded-lg p-4">
                        <h4 class="font-bold text-red-800 mb-3">🔴 Aërobe Drempel (LT1)</h4>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-700">Vermogen/Snelheid:</span>
                                <span class="font-bold text-red-700">{{ $inspanningstest->aerobe_drempel_vermogen ?? '-' }} 
                                    @if($inspanningstest->testtype == 'looptest') km/h @else Watt @endif
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-700">Hartslag:</span>
                                <span class="font-bold text-red-700">{{ $inspanningstest->aerobe_drempel_hartslag ?? '-' }} bpm</span>
                            </div>
                        </div>
                    </div>

                    <!-- Anaërobe Drempel -->
                    <div class="bg-orange-50 border-2 border-orange-200 rounded-lg p-4">
                        <h4 class="font-bold text-orange-800 mb-3">🟠 Anaërobe Drempel (LT2)</h4>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-700">Vermogen/Snelheid:</span>
                                <span class="font-bold text-orange-700">{{ $inspanningstest->anaerobe_drempel_vermogen ?? '-' }} 
                                    @if($inspanningstest->testtype == 'looptest') km/h @else Watt @endif
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-700">Hartslag:</span>
                                <span class="font-bold text-orange-700">{{ $inspanningstest->anaerobe_drempel_hartslag ?? '-' }} bpm</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Trainingszones -->
        @if(count($trainingszones) > 0)
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6">
                <h3 class="text-xl font-bold mb-4">🎯 Trainingszones</h3>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-3 text-left border-r">Zone</th>
                                <th class="px-4 py-3 text-center border-r" colspan="2">Hartslag (bpm)</th>
                                <th class="px-4 py-3 text-center border-r" colspan="2">Vermogen/Snelheid</th>
                                <th class="px-4 py-3 text-center">Borg</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($trainingszones as $zone)
                            <tr style="background-color: {{ $zone['kleur'] ?? '#FFFFFF' }}">
                                <td class="px-4 py-3 border-r border-b">
                                    <div class="font-bold">{{ $zone['naam'] }}</div>
                                    <div class="text-xs text-gray-600">{{ $zone['beschrijving'] ?? '' }}</div>
                                </td>
                                <td class="px-2 py-3 text-center border-r border-b">{{ $zone['minHartslag'] }}</td>
                                <td class="px-2 py-3 text-center border-r border-b">{{ $zone['maxHartslag'] }}</td>
                                <td class="px-2 py-3 text-center border-r border-b">{{ round($zone['minVermogen']) }}</td>
                                <td class="px-2 py-3 text-center border-r border-b">{{ round($zone['maxVermogen']) }}</td>
                                <td class="px-2 py-3 text-center border-b">{{ $zone['borgMin'] ?? '' }}-{{ $zone['borgMax'] ?? '' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        <!-- AI Analyse -->
        @if($inspanningstest->complete_ai_analyse)
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border-2 border-blue-200 rounded-lg p-6 mb-6">
            <h3 class="text-xl font-bold text-blue-900 mb-4">🧠 AI Performance Analyse</h3>
            <div class="prose max-w-none">
                <pre class="whitespace-pre-wrap text-sm text-gray-800 font-sans">{{ $inspanningstest->complete_ai_analyse }}</pre>
            </div>
        </div>
        @endif

    </div>
</div>

<script>
    // Globale drempelwaarden voor gebruik in plugin
    window.drempelwaardenResults = {
        lt1Vermogen: {{ $inspanningstest->aerobe_drempel_vermogen ?? 'null' }},
        lt2Vermogen: {{ $inspanningstest->anaerobe_drempel_vermogen ?? 'null' }}
    };

    // Custom plugin voor verticale drempellijnen - MET INTERPOLATIE
    const verticalLinePluginResults = {
        id: 'verticalLinesResults',
        afterDatasetsDraw(chart) {
            const { ctx, chartArea: { top, bottom, left, right }, data } = chart;
            const { lt1Vermogen, lt2Vermogen } = window.drempelwaardenResults;
            
            // Haal de X-as labels (vermogen waarden) op
            const labels = data.labels;
            if (!labels || labels.length === 0) return;
            
            const chartWidth = right - left;
            const minVermogen = labels[0];
            const maxVermogen = labels[labels.length - 1];
            const vermogenRange = maxVermogen - minVermogen;
            
            // Functie om X-positie te berekenen met interpolatie
            function berekenXPositie(vermogen) {
                // Bereken percentage positie in de range
                const percentage = (vermogen - minVermogen) / vermogenRange;
                // Converteer naar pixel positie
                return left + (percentage * chartWidth);
            }
            
            // Teken rode verticale lijn voor LT1
            if (lt1Vermogen) {
                const lt1X = berekenXPositie(lt1Vermogen);
                
                ctx.save();
                ctx.strokeStyle = '#dc2626'; // Rood
                ctx.lineWidth = 2;
                ctx.setLineDash([10, 5]);
                ctx.beginPath();
                ctx.moveTo(lt1X, top);
                ctx.lineTo(lt1X, bottom);
                ctx.stroke();
                ctx.setLineDash([]);
                
                // Label bovenaan
                ctx.fillStyle = '#dc2626';
                ctx.font = 'bold 12px sans-serif';
                ctx.fillText(`LT1: ${Math.round(lt1Vermogen)}`, lt1X + 5, top + 15);
                ctx.restore();
                
                console.log('✅ LT1 lijn: vermogen', lt1Vermogen, 'X-pixel', lt1X, 'range', minVermogen, '-', maxVermogen);
            }
            
            // Teken oranje verticale lijn voor LT2
            if (lt2Vermogen) {
                const lt2X = berekenXPositie(lt2Vermogen);
                
                ctx.save();
                ctx.strokeStyle = '#f97316'; // Oranje
                ctx.lineWidth = 2;
                ctx.setLineDash([10, 5]);
                ctx.beginPath();
                ctx.moveTo(lt2X, top);
                ctx.lineTo(lt2X, bottom);
                ctx.stroke();
                ctx.setLineDash([]);
                
                // Label bovenaan
                ctx.fillStyle = '#f97316';
                ctx.font = 'bold 12px sans-serif';
                ctx.fillText(`LT2: ${Math.round(lt2Vermogen)}`, lt2X + 5, top + 30);
                ctx.restore();
                
                console.log('✅ LT2 lijn: vermogen', lt2Vermogen, 'X-pixel', lt2X, 'range', minVermogen, '-', maxVermogen);
            }
        }
    };

    // Registreer het plugin globaal
    Chart.register(verticalLinePluginResults);
    console.log('✅ Vertical line plugin geregistreerd voor results pagina');
</script>
@endsection