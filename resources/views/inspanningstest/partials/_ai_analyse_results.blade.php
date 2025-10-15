{{-- AI Analyse Sectie voor Inspanningstest Results --}}
{{-- Variabele sleutel: {{INSPANNINGSTEST_AI_ANALYSE}} --}}

@php
    // Haal AI analyse op
    $aiAnalyse = $inspanningstest->complete_ai_analyse ?? null;
    
    // Parse de AI analyse voor mooie weergave
    function parseAIAnalyse($tekst) {
        if (!$tekst) return null;
        
        // Split op hoofdsecties (herkenbaar aan emoji + hoofdletters)
        $secties = [];
        $lines = explode("\n", $tekst);
        $huidigeSectieTitel = null;
        $huidigeSectieInhoud = [];
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            // Check of dit een hoofdsectie is (emoji + hoofdletters)
            if (preg_match('/^[🎯📊💤🏆💪🎯]\s+([A-Z\s:]+)$/', $line, $matches)) {
                // Bewaar vorige sectie
                if ($huidigeSectieTitel) {
                    $secties[] = [
                        'titel' => $huidigeSectieTitel,
                        'inhoud' => $huidigeSectieInhoud
                    ];
                }
                
                // Start nieuwe sectie
                $huidigeSectieTitel = $line;
                $huidigeSectieInhoud = [];
            } else {
                // Voeg toe aan huidige sectie
                $huidigeSectieInhoud[] = $line;
            }
        }
        
        // Bewaar laatste sectie
        if ($huidigeSectieTitel) {
            $secties[] = [
                'titel' => $huidigeSectieTitel,
                'inhoud' => $huidigeSectieInhoud
            ];
        }
        
        return $secties;
    }
    
    $parsedAnalyse = parseAIAnalyse($aiAnalyse);
@endphp

@if($aiAnalyse)
    <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6" style="border: 2px solid #c8e1eb;">
        {{-- Header --}}
        <div class="px-6 py-4" style="background-color: #c8e1eb; border-bottom: 2px solid #a8c1cb;">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="text-xl font-bold text-gray-900">🧠 AI Performance Analyse</h3>
                    <p class="text-sm text-gray-700 mt-1">Uitgebreide analyse van alle testparameters</p>
                </div>
                <span class="text-xs font-semibold text-gray-700 bg-white px-3 py-1 rounded-full border-2" style="border-color: #a8c1cb;">
                    AI Gegenereerd
                </span>
            </div>
        </div>
        
        {{-- Content --}}
        <div class="p-6">
            @if($parsedAnalyse && count($parsedAnalyse) > 0)
                @foreach($parsedAnalyse as $sectieIndex => $sectie)
                    <div class="mb-6 {{ $sectieIndex < count($parsedAnalyse) - 1 ? 'pb-6 border-b border-gray-200' : '' }}">
                        {{-- Sectie Titel --}}
                        <h4 class="text-lg font-bold text-gray-900 mb-4" style="color: #1976d2;">
                            {{ $sectie['titel'] }}
                        </h4>
                        
                        {{-- Sectie Inhoud --}}
                        <div class="space-y-3">
                            @foreach($sectie['inhoud'] as $line)
                                @php
                                    // Detecteer verschillende types content
                                    $isBulletPoint = str_starts_with($line, '•') || str_starts_with($line, '-') || str_starts_with($line, '*');
                                    $isNumberedPoint = preg_match('/^\d+\./', $line);
                                    $isSubheading = preg_match('/^[A-Z][a-z]+:/', $line);
                                @endphp
                                
                                @if($isBulletPoint)
                                    {{-- Bullet point met icoon --}}
                                    <div class="flex items-start gap-2 ml-4">
                                        <span class="text-blue-600 mt-1">●</span>
                                        <p class="text-sm text-gray-700 flex-1">
                                            {!! nl2br(e(ltrim($line, '•-* '))) !!}
                                        </p>
                                    </div>
                                @elseif($isNumberedPoint)
                                    {{-- Genummerd punt --}}
                                    <div class="flex items-start gap-2 ml-4">
                                        <span class="text-blue-600 font-bold mt-1">{{ substr($line, 0, strpos($line, '.') + 1) }}</span>
                                        <p class="text-sm text-gray-700 flex-1">
                                            {!! nl2br(e(trim(substr($line, strpos($line, '.') + 1)))) !!}
                                        </p>
                                    </div>
                                @elseif($isSubheading)
                                    {{-- Subheading met vetgedrukte tekst --}}
                                    @php
                                        $parts = explode(':', $line, 2);
                                        $label = $parts[0] ?? '';
                                        $value = $parts[1] ?? '';
                                    @endphp
                                    <p class="text-sm text-gray-800">
                                        <strong class="font-bold text-gray-900">{{ $label }}:</strong>
                                        <span class="text-gray-700">{{ trim($value) }}</span>
                                    </p>
                                @else
                                    {{-- Normale paragraaf --}}
                                    <p class="text-sm text-gray-700 leading-relaxed">
                                        {!! nl2br(e($line)) !!}
                                    </p>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @else
                {{-- Fallback: toon ruwe tekst als parsing faalt --}}
                <div class="prose max-w-none">
                    <pre class="whitespace-pre-wrap text-sm text-gray-700 leading-relaxed font-sans">{{ $aiAnalyse }}</pre>
                </div>
            @endif
        </div>
        
        {{-- Toelichting AI Analyse --}}
        <div class="mx-6 mb-6 mt-4 p-6" style="background-color: #fff8e1;">
            <div class="flex items-start">
                <div class="flex-shrink-0 mr-4 text-2xl">
                    💡
                </div>
                <div class="flex-1">
                    <h4 class="text-base font-bold text-gray-900 mb-3">Over deze AI analyse</h4>
                    <div class="text-sm text-gray-700 space-y-3">
                        <p>
                            Deze analyse is <strong>automatisch gegenereerd</strong> door kunstmatige intelligentie op basis van alle beschikbare testparameters. 
                            De AI heeft gekeken naar je drempelwaarden, trainingstatus, lichaamssamenstelling en je specifieke doelstellingen 
                            om een gepersonaliseerd advies te formuleren.
                        </p>
                        <p>
                            <strong>Let op:</strong> Deze analyse dient als aanvulling op professioneel sportadvies. 
                            De AI kan patronen herkennen en algemene aanbevelingen doen, maar kan niet alle individuele omstandigheden meewegen. 
                            Bespreek belangrijke trainingsbeslissingen altijd met je trainer of coach.
                        </p>
                        <p>
                            <strong>Populatievergelijkingen:</strong> De AI vergelijkt je prestaties met referentiewaarden voor je leeftijdsgroep en geslacht. 
                            Dit geeft context aan je resultaten, maar onthoud dat iedereen uniek is en zijn eigen progressiesnelheid heeft.
                        </p>
                        <p>
                            <strong>Trainingsadvies:</strong> De aanbevelingen zijn gebaseerd op wetenschappelijke principes en je specifieke doelstellingen. 
                            Start geleidelijk met nieuwe trainingen en luister naar je lichaam. Bij twijfel of klachten: raadpleeg een professional.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@else
    {{-- Geen AI analyse beschikbaar - verbeterde melding --}}
    <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6" style="border: 2px solid #c8e1eb;">
        <div class="px-6 py-4" style="background-color: #c8e1eb; border-bottom: 2px solid #a8c1cb;">
            <h3 class="text-xl font-bold text-gray-900">🧠 AI Performance Analyse</h3>
        </div>
        <div class="p-6">
            <div class="text-center py-8">
                <svg class="w-16 h-16 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                </svg>
                <p class="text-gray-500 text-sm font-semibold">Geen AI analyse beschikbaar</p>
                <p class="text-gray-400 text-xs mt-2">Deze analyse kan worden gegenereerd tijdens het aanmaken of bewerken van de test</p>
                <p class="text-gray-400 text-xs mt-1">Klik op "Bewerken" en gebruik de knop "Genereer Complete Analyse" om een AI analyse toe te voegen</p>
            </div>
        </div>
    </div>
@endif
