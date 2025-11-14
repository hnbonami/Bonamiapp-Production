{{-- Test Vergelijking Sectie - Results Pagina --}}
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
@endphp

@if($toonVergelijking)
<div class="bg-white rounded-lg shadow-md overflow-hidden mt-6 mb-6" style="border: 2px solid #c8e1eb;">
    {{-- Header --}}
    <div class="px-6 py-4" style="background-color: #c8e1eb; border-bottom: 2px solid #a8c1cb;">
        <div class="flex items-center gap-3">
            <div class="flex-shrink-0 w-10 h-10 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
            </div>
            <div>
                <h3 class="text-xl font-bold text-gray-900">Test Vergelijking</h3>
                <p class="text-sm text-gray-700 mt-1">Vergelijk je progressie over meerdere {{ $inspanningstest->testtype }} testen</p>
            </div>
        </div>
    </div>
    
    <div class="p-6">

        {{-- Test Selector --}}
        <div class="bg-gradient-to-r from-purple-50 to-indigo-50 rounded-lg p-4 mb-6 border border-purple-200">
            <h4 class="font-semibold text-gray-800 mb-3 flex items-center gap-2">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                Selecteer testen om te vergelijken (max 5):
            </h4>
            
            <div class="space-y-2">
                {{-- Huidige test (altijd zichtbaar, niet aanvinken) --}}
                <div class="flex items-center gap-3 p-3 bg-white rounded-lg border-2 border-purple-400 shadow-sm">
                    <div class="flex-shrink-0 w-4 h-4 rounded-full" style="background-color: #8b5cf6;"></div>
                    <div class="flex-1">
                        <span class="font-semibold text-gray-900">
                            {{ \Carbon\Carbon::parse($inspanningstest->datum)->format('d-m-Y') }}
                        </span>
                        <span class="text-xs bg-purple-100 text-purple-800 px-2 py-1 rounded-full ml-2 font-medium">
                            Huidige test
                        </span>
                    </div>
                </div>

                {{-- Vergelijkbare testen (met checkboxes) --}}
                @foreach($vergelijkbareTesten as $index => $test)
                    @php
                        $kleurIndex = $index + 1; // 0 = huidige (paars), 1-4 = vergelijkbare
                        $kleuren = [
                            1 => '#06b6d4', // Cyan
                            2 => '#10b981', // Groen
                            3 => '#f59e0b', // Oranje
                            4 => '#ef4444'  // Rood
                        ];
                        $kleur = $kleuren[$kleurIndex] ?? '#94a3b8';
                        $isTop3 = $index < 3; // Eerste 3 standaard geselecteerd
                    @endphp
                    <label class="flex items-center gap-3 p-3 bg-white rounded-lg border-2 border-gray-200 hover:border-gray-300 cursor-pointer transition-all hover:shadow-sm">
                        <input 
                            type="checkbox" 
                            class="test-vergelijking-checkbox w-5 h-5 text-purple-600 rounded focus:ring-purple-500"
                            data-test-id="{{ $test->id }}"
                            data-test-datum="{{ \Carbon\Carbon::parse($test->datum)->format('d-m-Y') }}"
                            data-test-kleur="{{ $kleur }}"
                            {{ $isTop3 ? 'checked' : '' }}
                        >
                        <div class="flex-shrink-0 w-4 h-4 rounded-full" style="background-color: {{ $kleur }};"></div>
                        <div class="flex-1">
                            <span class="font-semibold text-gray-900">
                                {{ \Carbon\Carbon::parse($test->datum)->format('d-m-Y') }}
                            </span>
                            @if($index === 0)
                                <span class="text-xs bg-gray-100 text-gray-700 px-2 py-1 rounded-full ml-2">
                                    Vorige test
                                </span>
                            @endif
                        </div>
                    </label>
                @endforeach
            </div>

            <div class="mt-3 flex gap-2">
                <button 
                    onclick="selecteerAlleTesten()" 
                    class="text-xs bg-purple-100 hover:bg-purple-200 text-purple-800 px-3 py-1.5 rounded-full font-medium transition"
                >
                    Selecteer alle
                </button>
                <button 
                    onclick="deselecteerAlleTesten()" 
                    class="text-xs bg-gray-100 hover:bg-gray-200 text-gray-800 px-3 py-1.5 rounded-full font-medium transition"
                >
                    Deselecteer alle
                </button>
            </div>
        </div>

        {{-- Grafiek Container --}}
        <div class="bg-gray-50 rounded-lg p-4 mb-6" style="height: 500px;">
            <canvas id="vergelijkingGrafiek"></canvas>
        </div>

        {{-- Progressie Analyse Tabel --}}
        <div class="bg-gradient-to-r from-blue-50 to-cyan-50 rounded-lg p-6 border border-blue-200">
            <h4 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                </svg>
                Progressie Analyse
            </h4>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr class="bg-white">
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">Metric</th>
                            @foreach($vergelijkbareTesten->reverse() as $test)
                                <th class="px-4 py-3 text-center text-xs font-bold text-gray-700">
                                    {{ \Carbon\Carbon::parse($test->datum)->format('d-m-Y') }}
                                </th>
                            @endforeach
                            <th class="px-4 py-3 text-center text-xs font-bold text-purple-700 bg-purple-50">
                                Huidige<br>{{ \Carbon\Carbon::parse($inspanningstest->datum)->format('d-m-Y') }}
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-bold text-gray-700">Δ</th>
                            <th class="px-4 py-3 text-center text-xs font-bold text-gray-700">Trend</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        {{-- DEBUG: Log alle waarden --}}
                        @php
                            \Log::info('Vergelijking Debug - Huidige test:', [
                                'lt1_vermogen' => $inspanningstest->aerobe_drempel_vermogen,
                                'lt1_snelheid' => $inspanningstest->aerobe_drempel_snelheid,
                                'lt2_vermogen' => $inspanningstest->anaerobe_drempel_vermogen,
                                'lt2_snelheid' => $inspanningstest->anaerobe_drempel_snelheid,
                                'testtype' => $inspanningstest->testtype
                            ]);
                        @endphp
                        
                        {{-- LT1 Vermogen/Snelheid --}}
                        @php
                            // Voor looptesten: gebruik ALTIJD snelheid, voor fietstesten ALTIJD vermogen
                            $oudsteLT1 = null;
                            $huidigeLT1 = null;
                            
                            if ($vergelijkbareTesten->last()) {
                                if ($isLooptest || $isZwemtest) {
                                    // Looptest: probeer snelheid, fallback naar vermogen als snelheid leeg is
                                    $oudsteLT1 = $vergelijkbareTesten->last()->aerobe_drempel_snelheid;
                                    if (!$oudsteLT1) {
                                        $oudsteLT1 = $vergelijkbareTesten->last()->aerobe_drempel_vermogen;
                                    }
                                } else {
                                    // Fietstest: gebruik vermogen
                                    $oudsteLT1 = $vergelijkbareTesten->last()->aerobe_drempel_vermogen;
                                }
                            }
                            
                            if ($isLooptest || $isZwemtest) {
                                $huidigeLT1 = $inspanningstest->aerobe_drempel_snelheid;
                                if (!$huidigeLT1) {
                                    $huidigeLT1 = $inspanningstest->aerobe_drempel_vermogen;
                                }
                            } else {
                                $huidigeLT1 = $inspanningstest->aerobe_drempel_vermogen;
                            }
                            
                            $deltaLT1 = ($oudsteLT1 && $huidigeLT1) ? (($huidigeLT1 - $oudsteLT1) / $oudsteLT1) * 100 : null;
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                LT1 {{ $isLooptest || $isZwemtest ? 'Snelheid' : 'Vermogen' }}
                            </td>
                            @foreach($vergelijkbareTesten->reverse() as $test)
                                <td class="px-4 py-3 text-sm text-center text-gray-700">
                                    @php
                                        if ($isLooptest || $isZwemtest) {
                                            $waarde = $test->aerobe_drempel_snelheid;
                                            if (!$waarde) {
                                                $waarde = $test->aerobe_drempel_vermogen; // Fallback
                                            }
                                        } else {
                                            $waarde = $test->aerobe_drempel_vermogen;
                                        }
                                    @endphp
                                    {{ $waarde ? ($isLooptest || $isZwemtest ? number_format($waarde, 1) . ' km/h' : number_format($waarde, 0) . 'W') : '-' }}
                                </td>
                            @endforeach
                            <td class="px-4 py-3 text-sm text-center font-bold text-purple-700 bg-purple-50">
                                {{ $huidigeLT1 ? ($isLooptest || $isZwemtest ? number_format($huidigeLT1, 1) . ' km/h' : number_format($huidigeLT1, 0) . 'W') : '-' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-center font-semibold {{ $deltaLT1 > 0 ? 'text-green-600' : ($deltaLT1 < 0 ? 'text-red-600' : 'text-gray-500') }}">
                                {{ $deltaLT1 !== null ? ($deltaLT1 > 0 ? '+' : '') . number_format($deltaLT1, 1) . '%' : '-' }}
                            </td>
                            <td class="px-4 py-3 text-center text-xl">
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
                            $oudsteLT1HR = $vergelijkbareTesten->last() ? $vergelijkbareTesten->last()->aerobe_drempel_hartslag : null;
                            $huidigeLT1HR = $inspanningstest->aerobe_drempel_hartslag;
                            $deltaLT1HR = ($oudsteLT1HR && $huidigeLT1HR) ? (($huidigeLT1HR - $oudsteLT1HR) / $oudsteLT1HR) * 100 : null;
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">LT1 Hartslag</td>
                            @foreach($vergelijkbareTesten->reverse() as $test)
                                <td class="px-4 py-3 text-sm text-center text-gray-700">
                                    {{ $test->aerobe_drempel_hartslag ? number_format($test->aerobe_drempel_hartslag, 0) . ' bpm' : '-' }}
                                </td>
                            @endforeach
                            <td class="px-4 py-3 text-sm text-center font-bold text-purple-700 bg-purple-50">
                                {{ $huidigeLT1HR ? number_format($huidigeLT1HR, 0) . ' bpm' : '-' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-center font-semibold {{ $deltaLT1HR > 0 ? 'text-green-600' : ($deltaLT1HR < 0 ? 'text-red-600' : 'text-gray-500') }}">
                                {{ $deltaLT1HR !== null ? ($deltaLT1HR > 0 ? '+' : '') . number_format($deltaLT1HR, 1) . '%' : '-' }}
                            </td>
                            <td class="px-4 py-3 text-center text-xl">
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
                            $oudsteLT2 = null;
                            $huidigeLT2 = null;
                            
                            if ($vergelijkbareTesten->last()) {
                                if ($isLooptest || $isZwemtest) {
                                    $oudsteLT2 = $vergelijkbareTesten->last()->anaerobe_drempel_snelheid;
                                    if (!$oudsteLT2) {
                                        $oudsteLT2 = $vergelijkbareTesten->last()->anaerobe_drempel_vermogen;
                                    }
                                } else {
                                    $oudsteLT2 = $vergelijkbareTesten->last()->anaerobe_drempel_vermogen;
                                }
                            }
                            
                            if ($isLooptest || $isZwemtest) {
                                $huidigeLT2 = $inspanningstest->anaerobe_drempel_snelheid;
                                if (!$huidigeLT2) {
                                    $huidigeLT2 = $inspanningstest->anaerobe_drempel_vermogen;
                                }
                            } else {
                                $huidigeLT2 = $inspanningstest->anaerobe_drempel_vermogen;
                            }
                            
                            $deltaLT2 = ($oudsteLT2 && $huidigeLT2) ? (($huidigeLT2 - $oudsteLT2) / $oudsteLT2) * 100 : null;
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                LT2 {{ $isLooptest || $isZwemtest ? 'Snelheid' : 'Vermogen' }}
                            </td>
                            @foreach($vergelijkbareTesten->reverse() as $test)
                                <td class="px-4 py-3 text-sm text-center text-gray-700">
                                    @php
                                        if ($isLooptest || $isZwemtest) {
                                            $waarde = $test->anaerobe_drempel_snelheid;
                                            if (!$waarde) {
                                                $waarde = $test->anaerobe_drempel_vermogen;
                                            }
                                        } else {
                                            $waarde = $test->anaerobe_drempel_vermogen;
                                        }
                                    @endphp
                                    {{ $waarde ? ($isLooptest || $isZwemtest ? number_format($waarde, 1) . ' km/h' : number_format($waarde, 0) . 'W') : '-' }}
                                </td>
                            @endforeach
                            <td class="px-4 py-3 text-sm text-center font-bold text-purple-700 bg-purple-50">
                                {{ $huidigeLT2 ? ($isLooptest || $isZwemtest ? number_format($huidigeLT2, 1) . ' km/h' : number_format($huidigeLT2, 0) . 'W') : '-' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-center font-semibold {{ $deltaLT2 > 0 ? 'text-green-600' : ($deltaLT2 < 0 ? 'text-red-600' : 'text-gray-500') }}">
                                {{ $deltaLT2 !== null ? ($deltaLT2 > 0 ? '+' : '') . number_format($deltaLT2, 1) . '%' : '-' }}
                            </td>
                            <td class="px-4 py-3 text-center text-xl">
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
                            $oudsteLT2HR = $vergelijkbareTesten->last() ? $vergelijkbareTesten->last()->anaerobe_drempel_hartslag : null;
                            $huidigeLT2HR = $inspanningstest->anaerobe_drempel_hartslag;
                            $deltaLT2HR = ($oudsteLT2HR && $huidigeLT2HR) ? (($huidigeLT2HR - $oudsteLT2HR) / $oudsteLT2HR) * 100 : null;
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">LT2 Hartslag</td>
                            @foreach($vergelijkbareTesten->reverse() as $test)
                                <td class="px-4 py-3 text-sm text-center text-gray-700">
                                    {{ $test->anaerobe_drempel_hartslag ? number_format($test->anaerobe_drempel_hartslag, 0) . ' bpm' : '-' }}
                                </td>
                            @endforeach
                            <td class="px-4 py-3 text-sm text-center font-bold text-purple-700 bg-purple-50">
                                {{ $huidigeLT2HR ? number_format($huidigeLT2HR, 0) . ' bpm' : '-' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-center font-semibold {{ $deltaLT2HR > 0 ? 'text-green-600' : ($deltaLT2HR < 0 ? 'text-red-600' : 'text-gray-500') }}">
                                {{ $deltaLT2HR !== null ? ($deltaLT2HR > 0 ? '+' : '') . number_format($deltaLT2HR, 1) . '%' : '-' }}
                            </td>
                            <td class="px-4 py-3 text-center text-xl">
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
                            function getMaxVermogenFromTest($test, $isLooptest, $isZwemtest) {
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
                            $oudsteMax = $vergelijkbareTesten->last() ? getMaxVermogenFromTest($vergelijkbareTesten->last(), $isLooptest, $isZwemtest) : null;
                            $huidigeMax = getMaxVermogenFromTest($inspanningstest, $isLooptest, $isZwemtest);
                            $deltaMax = ($oudsteMax && $huidigeMax) ? (($huidigeMax - $oudsteMax) / $oudsteMax) * 100 : null;
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                Max {{ $isLooptest || $isZwemtest ? 'Snelheid' : 'Vermogen' }}
                            </td>
                            @foreach($vergelijkbareTesten->reverse() as $test)
                                <td class="px-4 py-3 text-sm text-center text-gray-700">
                                    @php
                                        $maxWaarde = getMaxVermogenFromTest($test, $isLooptest, $isZwemtest);
                                    @endphp
                                    {{ $maxWaarde ? ($isLooptest || $isZwemtest ? number_format($maxWaarde, 1) . ' km/h' : number_format($maxWaarde, 0) . 'W') : '-' }}
                                </td>
                            @endforeach
                            <td class="px-4 py-3 text-sm text-center font-bold text-purple-700 bg-purple-50">
                                {{ $huidigeMax ? ($isLooptest || $isZwemtest ? number_format($huidigeMax, 1) . ' km/h' : number_format($huidigeMax, 0) . 'W') : '-' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-center font-semibold {{ $deltaMax > 0 ? 'text-green-600' : ($deltaMax < 0 ? 'text-red-600' : 'text-gray-500') }}">
                                {{ $deltaMax !== null ? ($deltaMax > 0 ? '+' : '') . number_format($deltaMax, 1) . '%' : '-' }}
                            </td>
                            <td class="px-4 py-3 text-center text-xl">
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
            </div>
        </div>

        {{-- Interpretatie Box --}}
        <div class="mt-6 bg-gradient-to-r from-yellow-50 to-amber-50 border-l-4 p-6 rounded-lg" style="border-left-color: #c8e1eb;">
            <h4 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                </svg>
                💡 Interpretatie
            </h4>
            <div class="text-sm text-gray-800 space-y-3">
                {{-- LT1 Analyse --}}
                @if(isset($deltaLT1) && $deltaLT1 !== null)
                    <div class="mb-4">
                        <p class="flex items-start gap-2">
                            <span class="{{ $deltaLT1 > 5 ? 'text-green-600' : ($deltaLT1 < -3 ? 'text-red-600' : 'text-blue-600') }} font-bold text-lg">
                                @if($deltaLT1 > 5) ✓ @elseif($deltaLT1 < -3) ⚠ @else → @endif
                            </span>
                            <span>
                                <strong class="{{ $deltaLT1 > 5 ? 'text-green-700' : ($deltaLT1 < -3 ? 'text-red-700' : 'text-blue-700') }} text-base">
                                    Aerobe Basiscapaciteit (LT1): {{ $deltaLT1 > 0 ? '+' : '' }}{{ number_format($deltaLT1, 1) }}%
                                </strong>
                                <br>
                                @if($deltaLT1 > 10)
                                    <span class="text-gray-800">Exceptionele verbetering! Je LT1 is met <strong>{{ number_format($deltaLT1, 1) }}%</strong> gestegen. Dit is buitengewoon en wijst op fundamentele cardiovasculaire adaptaties. 
                                    <strong>Wat betekent dit?</strong> Je aerobe motor is aanzienlijk krachtiger geworden - je lichaam kan nu veel efficiënter vetten verbranden bij matige intensiteiten. 
                                    Dit betekent dat je langer kunt trainen zonder te verzuren, je basisuithoudingsvermogen is spectaculair verbeterd, en je herstel tussen intensieve sessies is sneller. 
                                    In de praktijk: lange duurtrainingen voelen makkelijker aan, je kunt meer trainingsvolume verdragen, en je fundament voor topprestaties is significant sterker geworden.</span>
                                @elseif($deltaLT1 > 5)
                                    <span class="text-gray-800">Uitstekende verbetering! Je LT1 is met <strong>{{ number_format($deltaLT1, 1) }}%</strong> gestegen. Je basisuithoudingsvermogen is significant verbeterd. 
                                    <strong>Wat betekent dit?</strong> Je aerobe capaciteit - het vermogen om vet als brandstof te gebruiken - is duidelijk toegenomen. 
                                    Dit zorgt ervoor dat je bij lage tot matige intensiteiten efficiënter werkt en minder snel je kostbare glycogeenvoorraden aanbreekt. 
                                    In de praktijk: je kunt langer trainen op een comfortabel tempo, lange ritten/runs voelen minder zwaar aan, en je lichaam is beter voorbereid op intensievere trainingen. 
                                    Een sterke aerobe basis is de sleutel tot alle andere verbeteringen.</span>
                                @elseif($deltaLT1 > 0)
                                    <span class="text-gray-800">Positieve ontwikkeling. Je LT1 is met <strong>{{ number_format($deltaLT1, 1) }}%</strong> gestegen. 
                                    <strong>Wat betekent dit?</strong> Je aerobe capaciteit ontwikkelt zich in de goede richting. Hoewel de verbetering bescheiden is, betekent dit dat je trainingen effect hebben. 
                                    Je vetstofwisseling verbetert geleidelijk, waardoor je iets langer kunt trainen voordat je lichaam overstapt op koolhydraten. 
                                    Om deze vooruitgang te versnellen: focus op meer volume in lage intensiteitszones (zone 1-2), vergroot je wekelijkse trainingsuren geleidelijk, en zorg voor voldoende herstel tussen sessies.</span>
                                @elseif($deltaLT1 < -5)
                                    <span class="text-gray-800">Significante daling. Je LT1 is gedaald met <strong>{{ number_format(abs($deltaLT1), 1) }}%</strong>. Dit vraagt aandacht en mogelijk herstel. 
                                    <strong>Wat betekent dit?</strong> Je aerobe basis is verzwakt - je lichaam is minder efficiënt geworden in het gebruik van vetten als brandstof bij matige intensiteiten. 
                                    Mogelijke oorzaken: overtraining (te veel intensief werk, te weinig rustige basistraining), ondertraining (te weinig trainingsvolume), chronische vermoeidheid, ziekte, of te weinig herstel. 
                                    <strong>Herstelplan:</strong> Neem 1-2 weken relatieve rust met alleen zeer lage intensiteit. Focus op slaap (8+ uur), voeding (voldoende calorieën), en stressreductie. 
                                    Bouw daarna geleidelijk weer op met focus op lage intensiteit basiswerk. Je aerobe motor heeft rust nodig om te herstellen.</span>
                                @else
                                    <span class="text-gray-800">Je aerobe basiscapaciteit is stabiel gebleven. 
                                    <strong>Wat betekent dit?</strong> Stabiliteit kan zowel goed als neutraal zijn, afhankelijk van je trainingsfase. 
                                    Als je in een onderhoudsfase zit, is dit prima - je behoudt je niveau. Als je echter aan het opbouwen bent, zou je eigenlijk een stijging willen zien. 
                                    Overweeg om je trainingsvolume in zone 1-2 te verhogen, langere duurtrainingen toe te voegen, of meer consistency in je trainingsschema aan te brengen. 
                                    Een sterke aerobe basis blijft essentieel voor alle andere aspecten van je fitheid.</span>
                                @endif
                            </span>
                        </p>
                    </div>
                @endif

                {{-- LT2 Analyse --}}
                @if(isset($deltaLT2) && $deltaLT2 !== null)
                    <div class="mb-4">
                        <p class="flex items-start gap-2">
                            <span class="{{ $deltaLT2 > 8 ? 'text-green-600' : ($deltaLT2 < -5 ? 'text-red-600' : 'text-blue-600') }} font-bold text-lg">
                                @if($deltaLT2 > 8) ✓ @elseif($deltaLT2 < -5) ⚠ @else → @endif
                            </span>
                            <span>
                                <strong class="{{ $deltaLT2 > 8 ? 'text-green-700' : ($deltaLT2 < -5 ? 'text-red-700' : 'text-blue-700') }} text-base">
                                    Anaerobe Capaciteit (LT2): {{ $deltaLT2 > 0 ? '+' : '' }}{{ number_format($deltaLT2, 1) }}%
                                </strong>
                                <br>
                                @if($deltaLT2 > 10)
                                    <span class="text-gray-800">Fenomenale verbetering! Je LT2 is met <strong>{{ number_format($deltaLT2, 1) }}%</strong> gestegen. Dit is zeldzaam en zeer significant! 
                                    <strong>Wat betekent dit?</strong> Je anaerobe drempel - het punt waarop lactaatproductie en -verwerking in evenwicht zijn - ligt nu aanzienlijk hoger. 
                                    Dit betekent dat je veel harder kunt rijden/lopen voordat je "verzuurt". Je lactaatbuffering is spectaculair verbeterd, waardoor je hogere intensiteiten veel langer vol kunt houden. 
                                    In de praktijk: je FTP/threshold power is flink gestegen, tempotrainingen voelen makkelijker aan, je kunt langer op wedstrijdtempo blijven, en je herstel tussen intervallen is sneller. 
                                    Deze verbetering vertaalt zich direct in betere wedstrijdprestaties - je kunt harder finishen, beter klimmen, en langer bij het tempo blijven bij aanvallen.</span>
                                @elseif($deltaLT2 > 8)
                                    <span class="text-gray-800">Uitstekende verbetering! Je LT2 is met <strong>{{ number_format($deltaLT2, 1) }}%</strong> gestegen. Je lactaatbuffering is aanzienlijk verbeterd. 
                                    <strong>Wat betekent dit?</strong> Je anaerobe capaciteit is flink toegenomen - je lichaam kan lactaat nu veel efficiënter bufferen en recyclen. 
                                    Dit betekent dat je threshold (het hoogste tempo dat je langdurig vol kunt houden) is gestegen. Je kunt nu harder werken zonder te verzuren. 
                                    In de praktijk: intervaltrainingen voelen makkelijker aan, je kunt hoger tempo volhouden tijdens klimmen of temporitten, en je presteert beter in wedstrijdsituaties waar je langdurig net onder je maximum moet werken. 
                                    Deze zone is cruciaal voor competitieve prestaties.</span>
                                @elseif($deltaLT2 > 5)
                                    <span class="text-gray-800">Je anaerobe drempel is met <strong>{{ number_format($deltaLT2, 1) }}%</strong> gestegen. Dit betekent dat je lichaam efficiënter is geworden in het verwerken van lactaat bij hogere intensiteiten. 
                                    <strong>Wat betekent dit?</strong> Je threshold power/pace ligt nu hoger. Je kunt harder werken voordat lactaat begint op te hopen in je spieren. 
                                    Dit is het directe resultaat van specifieke threshold trainingen - je spieren hebben zich aangepast aan het bufferen van lactaat, en je cardiovasculaire systeem is beter geworden in het afvoeren ervan. 
                                    In de praktijk: je kunt langer op wedstrijdtempo blijven, tempotrainingen (zoals 2x20min) voelen minder zwaar aan, en je prestaties bij langere klimmen zijn verbeterd. 
                                    Blijf dit onderhouden met wekelijkse threshold sessies.</span>
                                @elseif($deltaLT2 > 0)
                                    <span class="text-gray-800">Positieve ontwikkeling. Je LT2 is met <strong>{{ number_format($deltaLT2, 1) }}%</strong> gestegen. Blijf doortrainen met focus op threshold werk. 
                                    <strong>Wat betekent dit?</strong> Je anaerobe capaciteit verbetert geleidelijk. Dit is een goede basis, maar er is nog ruimte voor verbetering. 
                                    Een kleine stijging betekent vaak dat je net begint met specifiek threshold training, of dat de trainingsfrequentie/intensiteit nog geoptimaliseerd kan worden. 
                                    <strong>Hoe verder te verbeteren?</strong> Voeg meer specifiek threshold werk toe: denk aan 2x20min net onder LT2, 3x10min op LT2, of 4x8min net boven LT2. 
                                    Combineer dit met VO2max intervallen (1x per week) voor maximale stimulus. Zorg wel voor voldoende herstel - deze intensiteit vraagt veel van je lichaam.</span>
                                @elseif($deltaLT2 < -5)
                                    <span class="text-gray-800">Daling gedetecteerd. Je LT2 is gedaald met <strong>{{ number_format(abs($deltaLT2), 1) }}%</strong>. Dit kan wijzen op overtraining, onderherstel of ziekte. 
                                    <strong>Wat betekent dit?</strong> Je anaerobe capaciteit is verzwakt - je kunt minder hard werken voordat lactaat begint op te hopen. 
                                    Mogelijke oorzaken: te veel intensieve trainingen zonder voldoende herstel (overtraining), chronische vermoeidheid, ziekte of infectie, te weinig slaap, of inadequate voeding. 
                                    <strong>Herstelplan:</strong> Neem 1-2 weken relatieve rust - alleen lage intensiteit (zone 1-2). Prioriteer herstel: 8+ uur slaap per nacht, voldoende calorieën (vooral koolhydraten), en stressreductie. 
                                    Bouw daarna geleidelijk weer op: start met sweetspot werk (90% threshold), dan threshold, en pas later weer VO2max. 
                                    Als de daling aanhoudt, overweeg dan een bloedtest bij je huisarts.</span>
                                @else
                                    <span class="text-gray-800">Je anaerobe capaciteit is stabiel. 
                                    <strong>Wat betekent dit?</strong> Als je in een onderhoudsfase zit, is stabiliteit goed - je behoudt je niveau. 
                                    Als je echter actief aan het trainen bent om te verbeteren, zou je eigenlijk een stijging willen zien. 
                                    Een plateau in LT2 kan betekenen dat je lichaam gewend is aan de huidige trainingsstimulus. 
                                    <strong>Doorbraak strategie:</strong> Varieer je intervalformats (wissel tussen 2x20min, 3x12min, 4x8min), 
                                    probeer polarisatie (meer zone 1-2, meer zone 5, minder zone 3-4), of voeg blokperiodisering toe (4 weken focus op threshold, dan herstelweek).</span>
                                @endif
                            </span>
                        </p>
                    </div>
                @endif

                {{-- Max Capaciteit Analyse --}}
                @if(isset($deltaMax) && $deltaMax !== null)
                    <div class="mb-4">
                        <p class="flex items-start gap-2">
                            <span class="{{ $deltaMax > 10 ? 'text-green-600' : ($deltaMax < -5 ? 'text-red-600' : 'text-blue-600') }} font-bold text-lg">
                                @if($deltaMax > 10) ✓ @elseif($deltaMax < -5) ⚠ @else → @endif
                            </span>
                            <span>
                                <strong class="{{ $deltaMax > 10 ? 'text-green-700' : ($deltaMax < -5 ? 'text-red-700' : 'text-blue-700') }} text-base">
                                    Maximale Capaciteit (VO2max): {{ $deltaMax > 0 ? '+' : '' }}{{ number_format($deltaMax, 1) }}%
                                </strong>
                                <br>
                                @if($deltaMax > 10)
                                    <span class="text-gray-800">Exceptionele verbetering! Je piekprestatie is met <strong>{{ number_format($deltaMax, 1) }}%</strong> gestegen. Dit toont spectaculaire vooruitgang in je VO2max. 
                                    <strong>Wat betekent dit?</strong> Je maximale zuurstofopname - het absolute plafond van je aërobe systeem - is dramatisch toegenomen. 
                                    Dit betekent dat je hart meer bloed per slag pompt (groter slagvolume), je longen meer zuurstof kunnen opnemen, en je spieren die zuurstof efficiënter kunnen gebruiken. 
                                    In de praktijk: je kunt veel hogere pieken bereiken tijdens sprints en explosive momenten, kortere klimmen voelen makkelijker aan, je maximaal duurvermogen (wat je ~1 uur vol kunt houden) ligt aanzienlijk hoger, 
                                    en je hebt meer reserves voor de finish of aanvallen in wedstrijden. Je absolute prestatieplafond is flink verhoogd - zowel je korte explosive power als je sustained high-intensity vermogen zijn beter.</span>
                                @elseif($deltaMax > 5)
                                    <span class="text-gray-800">Sterke verbetering! Je piekprestatie is met <strong>{{ number_format($deltaMax, 1) }}%</strong> gestegen. 
                                    <strong>Wat betekent dit?</strong> Je VO2max - de maximale hoeveelheid zuurstof die je lichaam kan verwerken - is significant verbeterd. 
                                    Dit is het resultaat van specifieke VO2max training (zone 5 intervallen). Je cardiovasculaire systeem is krachtiger geworden: je hartminuutvolume is toegenomen, 
                                    je hemoglobinewaarden zijn mogelijk hoger (meer zuurstoftransport), en je spiervezels kunnen meer zuurstof extraheren uit het bloed. 
                                    In de praktijk: je kunt harder finishen in wedstrijden, explosive klimmen (1-5min) gaan beter, je kunt hogere power/pace pieken bereiken, 
                                    en je herstelt sneller tussen harde intervallen. Dit is cruciaal voor wedstrijdsituaties waar je boven threshold moet gaan.</span>
                                @elseif($deltaMax > 0)
                                    <span class="text-gray-800">Positieve ontwikkeling. Je piekprestatie is met <strong>{{ number_format($deltaMax, 1) }}%</strong> verbeterd. 
                                    <strong>Wat betekent dit?</strong> Je maximale capaciteit ontwikkelt zich in de goede richting. VO2max is relatief moeilijk te verbeteren, dus elke stijging is waardevol. 
                                    Je hart-long systeem past zich geleidelijk aan aan de trainingsbelasting. Je kunt iets hogere pieken bereiken en je explosive power neemt toe. 
                                    <strong>Hoe verder te verbeteren?</strong> Voeg meer specifiek VO2max werk toe: 4-6x 4min @ 95-105% max HR, of 8-10x 2min @ 110-120% threshold, 
                                    of 15-20x 30sec all-out sprints. Doe dit 1-2x per week voor maximale stimulus. Zorg wel voor 48-72u herstel na deze zware sessies. 
                                    Combineer met krachtraining voor extra power ontwikkeling.</span>
                                @elseif($deltaMax < -5)
                                    <span class="text-gray-800">Daling gedetecteerd. Je max capaciteit is gedaald met <strong>{{ number_format(abs($deltaMax), 1) }}%</strong>. Dit kan wijzen op vermoeidheid of ondertraining. 
                                    <strong>Wat betekent dit?</strong> Je maximale zuurstofopname is afgenomen - je kunt minder hoge pieken bereiken dan voorheen. 
                                    Mogelijke oorzaken: overtraining (chronische vermoeidheid door te veel intensiteit), detraining (gebrek aan VO2max specifieke training >3-4 weken), 
                                    ziekte of infectie (zelfs lichte infecties kunnen je max capaciteit weken verlagen), inadequaat herstel, of ondervoeding. 
                                    <strong>Herstelplan:</strong> Neem 1-2 weken volledige rust of alleen zeer lage intensiteit (zone 1). Evalueer je lifestyle: slaap >8u, verhoog calorieën iets, 
                                    verminder stress waar mogelijk. Bouw daarna geleidelijk weer op: eerst threshold werk, dan pas VO2max. 
                                    Als de daling aanhoudt na 4-6 weken normaal trainen, overweeg dan medische check-up (bloedtest, hartslagvariabiliteit meting).</span>
                                @else
                                    <span class="text-gray-800">Je maximale capaciteit is stabiel. 
                                    <strong>Wat betekent dit?</strong> Max capaciteit is moeilijk te verbeteren eenmaal je een goed niveau hebt bereikt - stabiliteit is vaak normaal, 
                                    vooral als je focus ligt op andere zones (threshold, endurance). Als je echter actief VO2max training doet zonder verbetering te zien, 
                                    kan dit betekenen dat je een genetisch plafond nadert, of dat je trainingsaanpak moet worden aangepast. 
                                    <strong>Opties om door te breken:</strong> Intensiveer je VO2max sessies (hardere intervallen, meer herhalingen tot near-failure), 
                                    voeg meer variatie toe (wissel tussen 30sec sprints, 2min hard, 4min @ max), probeer blokperiodisering (4-6 weken focus op alleen VO2max training), 
                                    of voeg hoogtetraining toe indien mogelijk. Blijf wel voldoende basis werk doen - VO2max kan alleen groeien op een sterke aerobe basis.</span>
                                @endif
                            </span>
                        </p>
                    </div>
                @endif

                <p class="pt-2 border-t border-yellow-200 text-xs text-gray-600">
                    <strong>Tip:</strong> Een curve die naar rechts verschuift betekent dat je bij dezelfde hartslag/lactaat harder kunt werken - een duidelijk teken van vooruitgang!
                </p>
            </div>
        </div>
    </div>
</div>

{{-- Chart.js Script voor Vergelijkingsgrafiek --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Data voor alle testen (inclusief huidige test)
    const alleTesten = [
        // Huidige test (index 0, altijd paars)
        {
            id: {{ $inspanningstest->id }},
            datum: '{{ \Carbon\Carbon::parse($inspanningstest->datum)->format('d-m-Y') }}',
            kleur: '#8b5cf6',
            testresultaten: @json($inspanningstest->testresultaten ?? []),
            label: 'Huidige test ({{ \Carbon\Carbon::parse($inspanningstest->datum)->format('d-m-Y') }})',
            visible: true, // Altijd zichtbaar
            lt1_vermogen: {{ $inspanningstest->aerobe_drempel_vermogen ?? 'null' }},
            lt2_vermogen: {{ $inspanningstest->anaerobe_drempel_vermogen ?? 'null' }},
            lt1_snelheid: {{ $inspanningstest->aerobe_drempel_snelheid ?? 'null' }},
            lt2_snelheid: {{ $inspanningstest->anaerobe_drempel_snelheid ?? 'null' }}
        },
        @foreach($vergelijkbareTesten as $index => $test)
            @php
                $kleurIndex = $index + 1;
                $kleuren = [1 => '#06b6d4', 2 => '#10b981', 3 => '#f59e0b', 4 => '#ef4444'];
                $kleur = $kleuren[$kleurIndex] ?? '#94a3b8';
                $isTop3 = $index < 3;
            @endphp
            {
                id: {{ $test->id }},
                datum: '{{ \Carbon\Carbon::parse($test->datum)->format('d-m-Y') }}',
                kleur: '{{ $kleur }}',
                testresultaten: @json($test->testresultaten ?? []),
                label: 'Test {{ \Carbon\Carbon::parse($test->datum)->format('d-m-Y') }}',
                visible: {{ $isTop3 ? 'true' : 'false' }},
                lt1_vermogen: {{ $test->aerobe_drempel_vermogen ?? 'null' }},
                lt2_vermogen: {{ $test->anaerobe_drempel_vermogen ?? 'null' }},
                lt1_snelheid: {{ $test->aerobe_drempel_snelheid ?? 'null' }},
                lt2_snelheid: {{ $test->anaerobe_drempel_snelheid ?? 'null' }}
            }{{ $loop->last ? '' : ',' }}
        @endforeach
    ];

    console.log('🔄 Vergelijkingstesten geladen:', alleTesten.length);

    const testtype = '{{ $testtype }}';
    const isLooptest = {{ $isLooptest ? 'true' : 'false' }};
    const isZwemtest = {{ $isZwemtest ? 'true' : 'false' }};

    // Functie om datasets te genereren
    function genereerDatasets() {
        const datasets = [];

        alleTesten.forEach((test, testIndex) => {
            if (!test.visible && testIndex !== 0) {
                console.log(`⏭️ Skip test ${testIndex} - niet zichtbaar`);
                return; // Skip onzichtbare testen (behalve huidige)
            }

            console.log(`✅ Genereer dataset voor test ${testIndex} (${test.datum})`);

            let testresultaten = test.testresultaten;
            if (typeof testresultaten === 'string') {
                testresultaten = JSON.parse(testresultaten);
            }
            testresultaten = Array.isArray(testresultaten) ? testresultaten : [];

            console.log(`   Testresultaten stappen: ${testresultaten.length}`);

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
            }).filter(point => point.x > 0 && point.y > 0); // Filter lege datapunten

            // Lactaat dataset
            const lactaatData = testresultatenMetSnelheid.map(stap => {
                const xVal = isLooptest || isZwemtest 
                    ? stap.berekende_snelheid || 0
                    : parseFloat(stap.vermogen) || 0;
                return {
                    x: xVal,
                    y: parseFloat(stap.lactaat) || 0
                };
            }).filter(point => point.x > 0 && point.y > 0); // Filter lege datapunten

            console.log(`   Test ${testIndex}: ${hartslagData.length} hartslag punten, ${lactaatData.length} lactaat punten`);

            // Voeg alleen datasets toe als er data is
            if (hartslagData.length > 0) {
                // Voeg hartslag dataset toe
                datasets.push({
                    label: test.label + ' - Hartslag',
                    data: hartslagData,
                    borderColor: test.kleur,
                    backgroundColor: test.kleur + '20',
                    borderWidth: testIndex === 0 ? 3 : 2,
                    tension: 0.4,
                    yAxisID: 'y',
                    pointRadius: testIndex === 0 ? 5 : 3,
                    pointHoverRadius: 7,
                    showLine: true,
                    borderDash: testIndex === 0 ? [] : [5, 5]
                });
                console.log(`   ✅ Hartslag dataset toegevoegd voor test ${testIndex}`);
            }

            if (lactaatData.length > 0) {
                // Voeg lactaat dataset toe
                datasets.push({
                    label: test.label + ' - Lactaat',
                    data: lactaatData,
                    borderColor: test.kleur,
                    backgroundColor: test.kleur + '20',
                    borderWidth: testIndex === 0 ? 3 : 2,
                    tension: 0.4,
                    yAxisID: 'y1',
                    pointRadius: testIndex === 0 ? 5 : 3,
                    pointHoverRadius: 7,
                    showLine: true,
                    borderDash: testIndex === 0 ? [] : [2, 2]
                });
                console.log(`   ✅ Lactaat dataset toegevoegd voor test ${testIndex}`);
            }
        });

        // Voeg drempellijnen toe voor alle zichtbare testen
        console.log('🔍 Start drempellijnen toevoegen voor', alleTesten.filter((t, i) => t.visible || i === 0).length, 'zichtbare testen');
        
        alleTesten.forEach((test, testIndex) => {
            if (!test.visible && testIndex !== 0) {
                console.log('⏭️ Skip test', testIndex, '- niet zichtbaar');
                return;
            }

            // Haal drempelwaarden op voor deze test (met fallback)
            const lt1Value = isLooptest || isZwemtest 
                ? (test.lt1_snelheid || test.lt1_vermogen)  // Gebruik snelheid, fallback naar vermogen
                : test.lt1_vermogen;
            const lt2Value = isLooptest || isZwemtest 
                ? (test.lt2_snelheid || test.lt2_vermogen)  // Gebruik snelheid, fallback naar vermogen
                : test.lt2_vermogen;
            
            console.log(`📍 Test ${testIndex} (${test.datum}):`, {
                isLooptest,
                lt1_snelheid: test.lt1_snelheid,
                lt1_vermogen: test.lt1_vermogen,
                lt2_snelheid: test.lt2_snelheid,
                lt2_vermogen: test.lt2_vermogen,
                lt1Value,
                lt2Value
            });

            // Bepaal Y-as range voor drempellijnen
            // Voor hartslag: 80 tot 200, voor lactaat: 0 tot 20
            const hartslagMin = 80;
            const hartslagMax = 200;
            const lactaatMin = 0;
            const lactaatMax = 20;

            // Opacity: huidige test volle kleur, oudere testen transparanter
            const opacity = testIndex === 0 ? 0.8 : 0.5;
            
            // Converteer hex kleur naar rgba voor opacity
            function hexToRgba(hex, alpha) {
                const r = parseInt(hex.slice(1, 3), 16);
                const g = parseInt(hex.slice(3, 5), 16);
                const b = parseInt(hex.slice(5, 7), 16);
                return `rgba(${r}, ${g}, ${b}, ${alpha})`;
            }

            const kleurRgba = hexToRgba(test.kleur, opacity);

            console.log(`🔎 Checking drempellijnen voor test ${testIndex}:`, {
                lt1Value,
                lt2Value,
                'lt1Value !== null': lt1Value !== null,
                '!isNaN(lt1Value)': !isNaN(lt1Value),
                'Will add LT1?': lt1Value !== null && !isNaN(lt1Value),
                'Will add LT2?': lt2Value !== null && !isNaN(lt2Value)
            });

            // LT1 drempellijn (lange streepjes) - alleen op lactaat-as
            if (lt1Value !== null && !isNaN(lt1Value) && lt1Value > 0) {
                console.log(`✅ LT1 lijn toevoegen voor test ${testIndex}, waarde: ${lt1Value}`);
                datasets.push({
                    type: 'line',
                    label: `${test.datum} - LT1 (${lt1Value.toFixed(1)})`,
                    data: [
                        { x: lt1Value, y: lactaatMin },
                        { x: lt1Value, y: lactaatMax }
                    ],
                    borderColor: kleurRgba,
                    backgroundColor: 'transparent',
                    borderWidth: testIndex === 0 ? 3 : 2,
                    borderDash: [10, 5],
                    pointRadius: 0,
                    pointHoverRadius: 0,
                    showLine: true,
                    yAxisID: 'y1',
                    fill: false,
                    order: 100 - testIndex,
                    tension: 0,
                    skipLegend: true // Verberg in legende
                });
            }

            // LT2 drempellijn (korte streepjes) - alleen op lactaat-as
            if (lt2Value !== null && !isNaN(lt2Value)) {
                datasets.push({
                    type: 'line',
                    label: `${test.datum} - LT2 (${lt2Value.toFixed(1)})`,
                    data: [
                        { x: lt2Value, y: lactaatMin },
                        { x: lt2Value, y: lactaatMax }
                    ],
                    borderColor: kleurRgba,
                    backgroundColor: 'transparent',
                    borderWidth: testIndex === 0 ? 3 : 2,
                    borderDash: [5, 5],
                    pointRadius: 0,
                    pointHoverRadius: 0,
                    showLine: true,
                    yAxisID: 'y1',
                    fill: false,
                    order: 100 - testIndex,
                    tension: 0,
                    skipLegend: true // Verberg in legende
                });
            }
        });

        console.log('📊 Totaal datasets (incl. drempellijnen):', datasets.length);

        return datasets;
    }

    // X-as label
    let xAxisLabel = 'Vermogen (Watt)';
    if (isLooptest) {
        xAxisLabel = 'Snelheid (km/h)';
    } else if (isZwemtest) {
        xAxisLabel = 'Tempo (mm:ss/100m)';
    }

    // Maak grafiek (zonder verticale lijn plugin)
    const ctx = document.getElementById('vergelijkingGrafiek').getContext('2d');
    
    // Voeg een marker toe aan de canvas zodat de plugin weet deze grafiek over te slaan
    ctx.canvas.dataset.skipVerticalLines = 'true';
    
    const vergelijkingChart = new Chart(ctx, {
        type: 'scatter',
        data: { datasets: genereerDatasets() },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                // Schakel de verticale lijn plugin uit voor deze grafiek
                verticalLinePlugin: false,
                legend: { 
                    display: true, 
                    position: 'top',
                    labels: {
                        font: { size: 10 },
                        usePointStyle: true,
                        boxWidth: 8,
                        padding: 8,
                        filter: function(legendItem, chartData) {
                            // Verberg ALLEEN drempellijnen in de legende
                            const dataset = chartData.datasets[legendItem.datasetIndex];
                            return !dataset.skipLegend;
                        }
                    }
                },
                title: {
                    display: true,
                    text: 'Progressie Vergelijking: Hartslag & Lactaat over Meerdere Testen',
                    font: { size: 14, weight: 'bold' }
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
                        font: { weight: 'bold', size: 12 }
                    },
                    ticks: {
                        stepSize: isLooptest ? 0.5 : (isZwemtest ? 0.1 : 10),
                        font: { size: 10 }
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
                        font: { weight: 'bold', size: 12 }
                    },
                    ticks: { color: '#374151', font: { size: 10 } }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Lactaat (mmol/L)',
                        color: '#06b6d4',
                        font: { weight: 'bold', size: 12 }
                    },
                    ticks: { color: '#06b6d4', font: { size: 10 } },
                    grid: { drawOnChartArea: false }
                }
            }
        }
    });

    // Event listeners voor checkboxes
    document.querySelectorAll('.test-vergelijking-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const testId = parseInt(this.dataset.testId);
            const testIndex = alleTesten.findIndex(t => t.id === testId);
            
            if (testIndex !== -1) {
                alleTesten[testIndex].visible = this.checked;
                
                // Update grafiek
                vergelijkingChart.data.datasets = genereerDatasets();
                vergelijkingChart.update();

                console.log('✅ Test', testId, this.checked ? 'toegevoegd' : 'verwijderd');
            }

            // Check max 5 limit
            const aantalGeselecteerd = document.querySelectorAll('.test-vergelijking-checkbox:checked').length;
            if (aantalGeselecteerd >= 4) { // 4 + huidige test = 5
                document.querySelectorAll('.test-vergelijking-checkbox:not(:checked)').forEach(cb => {
                    cb.disabled = true;
                    cb.parentElement.classList.add('opacity-50', 'cursor-not-allowed');
                });
            } else {
                document.querySelectorAll('.test-vergelijking-checkbox').forEach(cb => {
                    cb.disabled = false;
                    cb.parentElement.classList.remove('opacity-50', 'cursor-not-allowed');
                });
            }
        });
    });

    // Trigger initial checkbox change voor max 5 check
    const eersteCheckbox = document.querySelector('.test-vergelijking-checkbox');
    if (eersteCheckbox) {
        eersteCheckbox.dispatchEvent(new Event('change'));
    }
});

// Hulpfuncties voor selecteer alle/geen
function selecteerAlleTesten() {
    const checkboxes = document.querySelectorAll('.test-vergelijking-checkbox');
    const maxSelectie = Math.min(checkboxes.length, 4); // Max 4 extra (+ huidige = 5)
    
    checkboxes.forEach((checkbox, index) => {
        if (index < maxSelectie && !checkbox.checked) {
            checkbox.checked = true;
            checkbox.dispatchEvent(new Event('change'));
        }
    });
}

function deselecteerAlleTesten() {
    document.querySelectorAll('.test-vergelijking-checkbox:checked').forEach(checkbox => {
        checkbox.checked = false;
        checkbox.dispatchEvent(new Event('change'));
    });
}
</script>
@endif
