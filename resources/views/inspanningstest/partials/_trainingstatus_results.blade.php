{{-- Trainingstatus Sectie voor Inspanningstest Results --}}
{{-- Read-only versie met visuele status balkjes --}}
{{-- Variabele sleutel: {{INSPANNINGSTEST_TRAININGSTATUS}} --}}

@php
    // Haal trainingstatus waarden op
    $slaapkwaliteit = $inspanningstest->slaapkwaliteit ?? null;
    $eetlust = $inspanningstest->eetlust ?? null;
    $gevoelOpTraining = $inspanningstest->gevoel_op_training ?? null;
    $stressniveau = $inspanningstest->stressniveau ?? null;
    $gemiddeldeStatus = $inspanningstest->gemiddelde_trainingstatus ?? null;
    $trainingDagVoor = $inspanningstest->training_dag_voor_test ?? null;
    $training2dVoor = $inspanningstest->training_2d_voor_test ?? null;
    
    // Bereken positie voor marker (0-10 schaal -> 0-100%)
    function berekenMarkerPositie($waarde) {
        if ($waarde === null) return 50; // Midden als geen waarde
        return ($waarde / 10) * 100;
    }
@endphp

<div class="bg-white rounded-lg shadow-md overflow-hidden mb-6" style="border: 2px solid #c8e1eb;">
    {{-- Header --}}
    <div class="px-6 py-4" style="background-color: #c8e1eb; border-bottom: 2px solid #a8c1cb;">
        <h3 class="text-xl font-bold text-gray-900">💪 Trainingstatus bij Test</h3>
        <p class="text-sm text-gray-700 mt-1">Algemene conditie en herstelstatus op testmoment</p>
    </div>
    
    {{-- Content --}}
    <div class="p-6">
        {{-- Individuele Status Balkjes --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            {{-- Slaapkwaliteit --}}
            <div>
                <div class="flex justify-between items-center mb-2">
                    <label class="text-sm font-semibold text-gray-700">Slaapkwaliteit</label>
                    <span class="text-sm text-gray-500">(0 = slecht, 10 = perfect)</span>
                </div>
                <div class="relative w-full h-8 rounded-full overflow-hidden" style="background: linear-gradient(to right, #ef4444 0%, #f59e0b 50%, #10b981 100%);">
                    @if($slaapkwaliteit !== null)
                        <div class="absolute top-0 bottom-0 w-1 bg-white shadow-lg" style="left: {{ berekenMarkerPositie($slaapkwaliteit) }}%; transform: translateX(-50%); border: 2px solid #1f2937;"></div>
                    @endif
                </div>
                <div class="flex justify-between text-xs text-gray-600 mt-1">
                    <span>0 (slecht)</span>
                    <span class="font-bold text-base" style="color: #1f2937;">{{ $slaapkwaliteit ?? '-' }}</span>
                    <span>10 (perfect)</span>
                </div>
            </div>

            {{-- Eetlust --}}
            <div>
                <div class="flex justify-between items-center mb-2">
                    <label class="text-sm font-semibold text-gray-700">Eetlust</label>
                    <span class="text-sm text-gray-500">(0 = slecht, 10 = perfect)</span>
                </div>
                <div class="relative w-full h-8 rounded-full overflow-hidden" style="background: linear-gradient(to right, #ef4444 0%, #f59e0b 50%, #10b981 100%);">
                    @if($eetlust !== null)
                        <div class="absolute top-0 bottom-0 w-1 bg-white shadow-lg" style="left: {{ berekenMarkerPositie($eetlust) }}%; transform: translateX(-50%); border: 2px solid #1f2937;"></div>
                    @endif
                </div>
                <div class="flex justify-between text-xs text-gray-600 mt-1">
                    <span>0 (slecht)</span>
                    <span class="font-bold text-base" style="color: #1f2937;">{{ $eetlust ?? '-' }}</span>
                    <span>10 (perfect)</span>
                </div>
            </div>

            {{-- Gevoel op training --}}
            <div>
                <div class="flex justify-between items-center mb-2">
                    <label class="text-sm font-semibold text-gray-700">Gevoel op training</label>
                    <span class="text-sm text-gray-500">(0 = slecht, 10 = perfect)</span>
                </div>
                <div class="relative w-full h-8 rounded-full overflow-hidden" style="background: linear-gradient(to right, #ef4444 0%, #f59e0b 50%, #10b981 100%);">
                    @if($gevoelOpTraining !== null)
                        <div class="absolute top-0 bottom-0 w-1 bg-white shadow-lg" style="left: {{ berekenMarkerPositie($gevoelOpTraining) }}%; transform: translateX(-50%); border: 2px solid #1f2937;"></div>
                    @endif
                </div>
                <div class="flex justify-between text-xs text-gray-600 mt-1">
                    <span>0 (slecht)</span>
                    <span class="font-bold text-base" style="color: #1f2937;">{{ $gevoelOpTraining ?? '-' }}</span>
                    <span>10 (perfect)</span>
                </div>
            </div>

            {{-- Stressniveau --}}
            <div>
                <div class="flex justify-between items-center mb-2">
                    <label class="text-sm font-semibold text-gray-700">Stressniveau</label>
                    <span class="text-sm text-gray-500">(0 = veel stress, 10 = geen stress)</span>
                </div>
                <div class="relative w-full h-8 rounded-full overflow-hidden" style="background: linear-gradient(to right, #ef4444 0%, #f59e0b 50%, #10b981 100%);">
                    @if($stressniveau !== null)
                        <div class="absolute top-0 bottom-0 w-1 bg-white shadow-lg" style="left: {{ berekenMarkerPositie($stressniveau) }}%; transform: translateX(-50%); border: 2px solid #1f2937;"></div>
                    @endif
                </div>
                <div class="flex justify-between text-xs text-gray-600 mt-1">
                    <span>0 (veel)</span>
                    <span class="font-bold text-base" style="color: #1f2937;">{{ $stressniveau ?? '-' }}</span>
                    <span>10 (geen)</span>
                </div>
            </div>
        </div>

        {{-- Gemiddelde Score --}}
        @if($gemiddeldeStatus !== null)
            <div class="rounded-lg p-6 mb-6" style="background-color: #e3f2fd; border: 2px solid #c8e1eb;">
                <div class="text-center">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Gemiddelde Trainingstatus Score</label>
                    <div class="text-6xl font-bold mb-2" style="color: #2563eb;">{{ number_format($gemiddeldeStatus, 1) }}</div>
                    <p class="text-sm text-gray-600">Automatisch berekend gemiddelde van bovenstaande scores</p>
                    
                    {{-- Status interpretatie --}}
                    @php
                        $statusTekst = '';
                        $statusKleur = '';
                        if ($gemiddeldeStatus >= 8) {
                            $statusTekst = '✅ Uitstekend - Optimaal voor intensieve training';
                            $statusKleur = '#16a34a';
                        } elseif ($gemiddeldeStatus >= 6) {
                            $statusTekst = '👍 Goed - Geschikt voor normale training';
                            $statusKleur = '#10b981';
                        } elseif ($gemiddeldeStatus >= 4) {
                            $statusTekst = '⚠️ Matig - Focus op herstel en lichte training';
                            $statusKleur = '#f59e0b';
                        } else {
                            $statusTekst = '🚨 Laag - Herstel prioriteit, geen intensieve training';
                            $statusKleur = '#ef4444';
                        }
                    @endphp
                    <p class="text-sm font-semibold mt-3" style="color: {{ $statusKleur }};">{{ $statusTekst }}</p>
                </div>
            </div>
        @endif

        {{-- Training Beschrijvingen --}}
        @if($trainingDagVoor || $training2dVoor)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Training dag voor test --}}
                @if($trainingDagVoor)
                    <div class="rounded-lg p-4" style="background-color: #f9fafb; border: 1px solid #c8e1eb;">
                        <h4 class="text-sm font-bold text-gray-900 mb-2">📅 Training 1 dag voor de test</h4>
                        <p class="text-sm text-gray-700 whitespace-pre-line">{{ $trainingDagVoor }}</p>
                    </div>
                @endif

                {{-- Training 2 dagen voor test --}}
                @if($training2dVoor)
                    <div class="rounded-lg p-4" style="background-color: #f9fafb; border: 1px solid #c8e1eb;">
                        <h4 class="text-sm font-bold text-gray-900 mb-2">📅 Training 2 dagen voor de test</h4>
                        <p class="text-sm text-gray-700 whitespace-pre-line">{{ $training2dVoor }}</p>
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>
