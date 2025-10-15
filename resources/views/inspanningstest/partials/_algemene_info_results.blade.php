{{-- Algemene Informatie Sectie voor Inspanningstest Results --}}
{{-- Variabele sleutel: {{INSPANNINGSTEST_ALGEMEEN}} --}}

<div class="bg-white rounded-lg shadow-md overflow-hidden mb-6" style="border: 2px solid #c8e1eb;">
    {{-- Header --}}
    <div class="px-6 py-4" style="background-color: #c8e1eb; border-bottom: 2px solid #a8c1cb;">
        <div class="flex justify-between items-center">
            <div>
                <h3 class="text-xl font-bold text-gray-900">📋 Algemene Informatie</h3>
                <p class="text-sm text-gray-700 mt-1">Klantgegevens en testparameters</p>
            </div>
            <span class="text-xs font-semibold text-gray-700 bg-white px-3 py-1 rounded-full border-2" style="border-color: #a8c1cb;">
                Test #{{ $inspanningstest->id }}
            </span>
        </div>
    </div>
    
    {{-- Content --}}
    <div class="p-6">
        {{-- Klantgegevens Sectie --}}
        <div class="mb-6 pb-6 border-b border-gray-200">
            <h4 class="text-lg font-bold text-gray-900 mb-4" style="color: #1976d2;">
                👤 Klantgegevens
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                {{-- Naam --}}
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Naam</p>
                    <p class="text-base font-semibold text-gray-900">
                        {{ $klant->voornaam }} {{ $klant->achternaam ?? $klant->naam }}
                    </p>
                </div>
                
                {{-- Email --}}
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Email</p>
                    <p class="text-base font-semibold text-gray-900">
                        {{ $klant->email ?? 'Niet opgegeven' }}
                    </p>
                </div>
                
                {{-- Geboortedatum & Leeftijd --}}
                @php
                    $leeftijd = null;
                    $geboortedatumFormatted = 'Niet opgegeven';
                    if ($klant->geboortedatum) {
                        try {
                            $geboortedatum = \Carbon\Carbon::parse($klant->geboortedatum);
                            $leeftijd = $geboortedatum->age;
                            $geboortedatumFormatted = $geboortedatum->format('d-m-Y');
                        } catch (\Exception $e) {
                            // Fallback als datum niet te parsen is
                        }
                    }
                @endphp
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Geboortedatum</p>
                    <p class="text-base font-semibold text-gray-900">
                        {{ $geboortedatumFormatted }}
                        @if($leeftijd)
                            <span class="text-sm text-gray-600">({{ $leeftijd }} jaar)</span>
                        @endif
                    </p>
                </div>
                
                {{-- Sport --}}
                @if($klant->sport)
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Sport</p>
                        <p class="text-base font-semibold text-gray-900">
                            {{ ucfirst($klant->sport) }}
                        </p>
                    </div>
                @endif
            </div>
        </div>
        
        {{-- Testgegevens Sectie --}}
        <div class="mb-6 pb-6 border-b border-gray-200">
            <h4 class="text-lg font-bold text-gray-900 mb-4" style="color: #1976d2;">
                🏃 Testgegevens
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                {{-- Testdatum --}}
                <div class="bg-blue-50 rounded-lg p-4">
                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Testdatum</p>
                    <p class="text-base font-semibold text-gray-900">
                        {{ $inspanningstest->datum ? \Carbon\Carbon::parse($inspanningstest->datum)->format('d-m-Y') : 'Niet opgegeven' }}
                    </p>
                </div>
                
                {{-- Testtype --}}
                <div class="bg-blue-50 rounded-lg p-4">
                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Testtype</p>
                    <p class="text-base font-semibold text-gray-900">
                        @php
                            $testtypeLabel = match($inspanningstest->testtype) {
                                'fietstest' => '🚴 Fietstest',
                                'looptest' => '🏃 Looptest',
                                'veldtest_lopen' => '🏃 Veldtest Lopen',
                                'veldtest_fietsen' => '🚴 Veldtest Fietsen',
                                'veldtest_zwemmen' => '🏊 Veldtest Zwemmen',
                                default => ucfirst($inspanningstest->testtype)
                            };
                        @endphp
                        {{ $testtypeLabel }}
                    </p>
                </div>
                
                {{-- Testlocatie --}}
                @if($inspanningstest->testlocatie)
                    <div class="bg-blue-50 rounded-lg p-4">
                        <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Testlocatie</p>
                        <p class="text-base font-semibold text-gray-900">
                            {{ $inspanningstest->testlocatie }}
                        </p>
                    </div>
                @endif
            </div>
            
            {{-- Specifieke Doelstellingen (volle breedte) --}}
            @if($inspanningstest->specifieke_doelstellingen)
                <div class="mt-4 bg-yellow-50 rounded-lg p-4 border-l-4" style="border-color: #f59e0b;">
                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-2">🎯 Specifieke Doelstellingen</p>
                    <p class="text-sm text-gray-700 leading-relaxed">
                        {{ $inspanningstest->specifieke_doelstellingen }}
                    </p>
                </div>
            @endif
        </div>
        
        {{-- Lichaamssamenstelling Sectie --}}
        <div>
            <h4 class="text-lg font-bold text-gray-900 mb-4" style="color: #1976d2;">
                ⚖️ Lichaamssamenstelling & Fysiologie
            </h4>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                {{-- Lengte --}}
                <div class="bg-gray-50 rounded-lg p-3">
                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Lengte</p>
                    <p class="text-lg font-bold text-gray-900">
                        {{ $inspanningstest->lichaamslengte_cm ?? '-' }}
                        @if($inspanningstest->lichaamslengte_cm)
                            <span class="text-sm font-normal text-gray-600">cm</span>
                        @endif
                    </p>
                </div>
                
                {{-- Gewicht --}}
                <div class="bg-gray-50 rounded-lg p-3">
                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Gewicht</p>
                    <p class="text-lg font-bold text-gray-900">
                        {{ $inspanningstest->lichaamsgewicht_kg ?? '-' }}
                        @if($inspanningstest->lichaamsgewicht_kg)
                            <span class="text-sm font-normal text-gray-600">kg</span>
                        @endif
                    </p>
                </div>
                
                {{-- BMI --}}
                <div class="bg-gray-50 rounded-lg p-3">
                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">BMI</p>
                    <p class="text-lg font-bold text-gray-900">
                        @if($inspanningstest->bmi)
                            {{ number_format($inspanningstest->bmi, 1) }}
                            @php
                                $bmi = $inspanningstest->bmi;
                                $bmiKleur = 'text-gray-600';
                                if ($bmi < 18.5) $bmiKleur = 'text-blue-600';
                                elseif ($bmi < 25) $bmiKleur = 'text-green-600';
                                elseif ($bmi < 30) $bmiKleur = 'text-orange-600';
                                else $bmiKleur = 'text-red-600';
                            @endphp
                            <span class="text-xs font-normal {{ $bmiKleur }}">
                                @if($bmi < 18.5) (ondergewicht)
                                @elseif($bmi < 25) (normaal)
                                @elseif($bmi < 30) (overgewicht)
                                @else (obesitas)
                                @endif
                            </span>
                        @else
                            -
                        @endif
                    </p>
                </div>
                
                {{-- Vetpercentage --}}
                <div class="bg-gray-50 rounded-lg p-3">
                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Vetpercentage</p>
                    <p class="text-lg font-bold text-gray-900">
                        {{ $inspanningstest->vetpercentage ?? '-' }}
                        @if($inspanningstest->vetpercentage)
                            <span class="text-sm font-normal text-gray-600">%</span>
                        @endif
                    </p>
                </div>
                
                {{-- Hartslag Rust --}}
                <div class="bg-red-50 rounded-lg p-3">
                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Hartslag Rust</p>
                    <p class="text-lg font-bold text-red-700">
                        {{ $inspanningstest->hartslag_rust_bpm ?? '-' }}
                        @if($inspanningstest->hartslag_rust_bpm)
                            <span class="text-sm font-normal text-gray-600">bpm</span>
                        @endif
                    </p>
                </div>
                
                {{-- Hartslag Max --}}
                <div class="bg-red-50 rounded-lg p-3">
                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Hartslag Max</p>
                    <p class="text-lg font-bold text-red-700">
                        {{ $inspanningstest->maximale_hartslag_bpm ?? '-' }}
                        @if($inspanningstest->maximale_hartslag_bpm)
                            <span class="text-sm font-normal text-gray-600">bpm</span>
                        @endif
                    </p>
                </div>
                
                {{-- Buikomtrek --}}
                <div class="bg-gray-50 rounded-lg p-3">
                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Buikomtrek</p>
                    <p class="text-lg font-bold text-gray-900">
                        {{ $inspanningstest->buikomtrek_cm ?? '-' }}
                        @if($inspanningstest->buikomtrek_cm)
                            <span class="text-sm font-normal text-gray-600">cm</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
