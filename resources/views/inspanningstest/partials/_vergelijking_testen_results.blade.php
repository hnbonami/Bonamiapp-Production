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
<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
    <div class="p-6">
        <div class="flex items-center gap-3 mb-4">
            <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
            </div>
            <div>
                <h3 class="text-2xl font-bold text-gray-900">🔄 Test Vergelijking</h3>
                <p class="text-sm text-gray-600">Vergelijk je progressie over meerdere {{ $inspanningstest->testtype }} testen</p>
            </div>
        </div>

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
                            @foreach($vergelijkbareTesten->take(3)->reverse() as $test)
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
                            @foreach($vergelijkbareTesten->take(3)->reverse() as $test)
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
                            @foreach($vergelijkbareTesten->take(3)->reverse() as $test)
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
                            @foreach($vergelijkbareTesten->take(3)->reverse() as $test)
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
                            @foreach($vergelijkbareTesten->take(3)->reverse() as $test)
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

                        {{-- Max Hartslag --}}
                        @php
                            $oudsteMaxHR = $vergelijkbareTesten->last() ? $vergelijkbareTesten->last()->max_hartslag : null;
                            $huidigeMaxHR = $inspanningstest->max_hartslag;
                            $deltaMaxHR = ($oudsteMaxHR && $huidigeMaxHR) ? (($huidigeMaxHR - $oudsteMaxHR) / $oudsteMaxHR) * 100 : null;
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">Max Hartslag</td>
                            @foreach($vergelijkbareTesten->take(3)->reverse() as $test)
                                <td class="px-4 py-3 text-sm text-center text-gray-700">
                                    {{ $test->max_hartslag ? number_format($test->max_hartslag, 0) . ' bpm' : '-' }}
                                </td>
                            @endforeach
                            <td class="px-4 py-3 text-sm text-center font-bold text-purple-700 bg-purple-50">
                                {{ $huidigeMaxHR ? number_format($huidigeMaxHR, 0) . ' bpm' : '-' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-center font-semibold {{ $deltaMaxHR > 0 ? 'text-green-600' : ($deltaMaxHR < 0 ? 'text-red-600' : 'text-gray-500') }}">
                                {{ $deltaMaxHR !== null ? ($deltaMaxHR > 0 ? '+' : '') . number_format($deltaMaxHR, 1) . '%' : '-' }}
                            </td>
                            <td class="px-4 py-3 text-center text-xl">→</td>
                        </tr>

                        {{-- VO2max (alleen voor fietstesten) --}}
                        @if(!$isLooptest && !$isZwemtest)
                            @php
                                $oudsteVO2 = $vergelijkbareTesten->last() ? $vergelijkbareTesten->last()->vo2max : null;
                                $huidigeVO2 = $inspanningstest->vo2max;
                                $deltaVO2 = ($oudsteVO2 && $huidigeVO2) ? (($huidigeVO2 - $oudsteVO2) / $oudsteVO2) * 100 : null;
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">VO2max</td>
                                @foreach($vergelijkbareTesten->take(3)->reverse() as $test)
                                    <td class="px-4 py-3 text-sm text-center text-gray-700">
                                        {{ $test->vo2max ? number_format($test->vo2max, 1) . ' ml/kg/min' : '-' }}
                                    </td>
                                @endforeach
                                <td class="px-4 py-3 text-sm text-center font-bold text-purple-700 bg-purple-50">
                                    {{ $huidigeVO2 ? number_format($huidigeVO2, 1) . ' ml/kg/min' : '-' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-center font-semibold {{ $deltaVO2 > 0 ? 'text-green-600' : ($deltaVO2 < 0 ? 'text-red-600' : 'text-gray-500') }}">
                                    {{ $deltaVO2 !== null ? ($deltaVO2 > 0 ? '+' : '') . number_format($deltaVO2, 1) . '%' : '-' }}
                                </td>
                                <td class="px-4 py-3 text-center text-xl">
                                    @if($deltaVO2 > 5) 📈
                                    @elseif($deltaVO2 > 0) ↗️
                                    @elseif($deltaVO2 < -5) 📉
                                    @elseif($deltaVO2 < 0) ↘️
                                    @else →
                                    @endif
                                </td>
                            </tr>
                        @endif
                    </tbody>
                        @php
                            $oudsteMaxHR = $vergelijkbareTesten->last() ? $vergelijkbareTesten->last()->max_hartslag : null;
                            $huidigeMaxHR = $inspanningstest->max_hartslag;
                            $deltaMaxHR = ($oudsteMaxHR && $huidigeMaxHR) ? (($huidigeMaxHR - $oudsteMaxHR) / $oudsteMaxHR) * 100 : null;
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">Max Hartslag</td>
                            @foreach($vergelijkbareTesten->take(3)->reverse() as $test)
                                <td class="px-4 py-3 text-sm text-center text-gray-700">
                                    {{ $test->max_hartslag ? $test->max_hartslag . ' bpm' : '-' }}
                                </td>
                            @endforeach
                            <td class="px-4 py-3 text-sm text-center font-bold text-purple-700 bg-purple-50">
                                {{ $huidigeMaxHR ? $huidigeMaxHR . ' bpm' : '-' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-center font-semibold {{ $deltaMaxHR > 0 ? 'text-green-600' : ($deltaMaxHR < 0 ? 'text-red-600' : 'text-gray-500') }}">
                                {{ $deltaMaxHR !== null ? ($deltaMaxHR > 0 ? '+' : '') . number_format($deltaMaxHR, 1) . '%' : '-' }}
                            </td>
                            <td class="px-4 py-3 text-center text-xl">→</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Interpretatie Box --}}
        <div class="mt-6 bg-gradient-to-r from-yellow-50 to-amber-50 border-l-4 border-yellow-400 p-5 rounded-lg">
            <h4 class="font-bold text-gray-900 mb-3 flex items-center gap-2">
                <svg class="w-5 h-5 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                </svg>
                💡 Interpretatie
            </h4>
            <div class="text-sm text-gray-800 space-y-2">
                @if($deltaLT2 !== null && $deltaLT2 > 5)
                    <p class="flex items-start gap-2">
                        <span class="text-green-600 font-bold">✓</span>
                        <span><strong class="text-green-700">Uitstekende progressie!</strong> Je anaerobe drempel is met <strong>{{ number_format($deltaLT2, 1) }}%</strong> gestegen. Dit betekent dat je lichaam efficiënter is geworden in het verwerken van lactaat bij hogere intensiteiten.</span>
                    </p>
                @elseif($deltaLT2 !== null && $deltaLT2 > 0)
                    <p class="flex items-start gap-2">
                        <span class="text-blue-600 font-bold">→</span>
                        <span><strong class="text-blue-700">Positieve ontwikkeling:</strong> Je drempelwaarden zijn licht gestegen (+{{ number_format($deltaLT2, 1) }}%). Blijf doortrainen met focus op je zwakke zones.</span>
                    </p>
                @elseif($deltaLT2 !== null && $deltaLT2 < 0)
                    <p class="flex items-start gap-2">
                        <span class="text-orange-600 font-bold">⚠</span>
                        <span><strong class="text-orange-700">Let op:</strong> Je drempelwaarden zijn gedaald ({{ number_format($deltaLT2, 1) }}%). Dit kan wijzen op overtraining, onderherstel of ziekte. Overweeg rustdagen of een trainingsaanpassing.</span>
                    </p>
                @else
                    <p class="text-gray-700">Vergelijk je testresultaten om progressie te monitoren. Een rechtsverschuiving van de curve duidt op verbeterde prestaties.</p>
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
            if (!test.visible && testIndex !== 0) return; // Skip onzichtbare testen (behalve huidige)

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
        });

        // Voeg drempellijnen toe voor alle zichtbare testen
        alleTesten.forEach((test, testIndex) => {
            if (!test.visible && testIndex !== 0) return;

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
                    borderWidth: 2,
                    borderDash: [8, 4],
                    pointRadius: 0,
                    showLine: true,
                    yAxisID: 'y',
                    fill: false
                });
            }

            // LT2 drempellijn (stippellijn met grotere stippen)
            if (lt2Value !== null && !isNaN(lt2Value)) {
                datasets.push({
                    label: test.label + ' - LT2',
                    data: [
                        { x: lt2Value, y: minY },
                        { x: lt2Value, y: maxY }
                    ],
                    borderColor: test.kleur,
                    backgroundColor: 'transparent',
                    borderWidth: 2,
                    borderDash: [4, 4],
                    pointRadius: 0,
                    showLine: true,
                    yAxisID: 'y',
                    fill: false
                });
            }
        });

        return datasets;
    }

    // X-as label
    let xAxisLabel = 'Vermogen (Watt)';
    if (isLooptest) {
        xAxisLabel = 'Snelheid (km/h)';
    } else if (isZwemtest) {
        xAxisLabel = 'Tempo (mm:ss/100m)';
    }

    // Maak grafiek
    const ctx = document.getElementById('vergelijkingGrafiek').getContext('2d');
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
                legend: { 
                    display: true, 
                    position: 'top',
                    labels: {
                        font: { size: 11 },
                        usePointStyle: true,
                        boxWidth: 8
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
