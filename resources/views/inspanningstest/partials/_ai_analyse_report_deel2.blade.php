{{-- AI Analyse Rapport - Deel 2: Advies & Progressie --}}
{{-- Variabele sleutel: {{INSPANNINGSTEST_AI_ANALYSE_DEEL2}} --}}

<style>
    .rapport-ai-deel2 {
        font-family: Tahoma, Arial, sans-serif;
        font-size: 9px;
        line-height: 1.5;
        color: #1f2937;
        margin: 15px 0;
        width: 120%;
    }
    
    .rapport-ai-deel2 h3 {
        font-size: 13px;
        font-weight: 700;
        color: #0f4c75;
        margin: 12px 0 8px 0;
        padding: 6px 10px;
        background-color: #c8e1eb;
        border-left: 4px solid #0f4c75;
    }
    
    .ai-sectie-titel {
        font-size: 10px;
        font-weight: 700;
        color: white;
        background: #0f4c75;
        padding: 5px 8px;
        margin: 8px 0 5px 0;
        border-radius: 3px;
    }
    
    .ai-sectie-content {
        font-size: 8.5px;
        line-height: 1.5;
        color: #374151;
        padding: 6px 10px;
        background: #fafafa;
        border-left: 2px solid #c8e1eb;
    }
    
    .ai-2col-layout {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        margin: 6px 0;
    }
    
    .ai-bullet {
        padding: 2px 0 2px 12px;
        position: relative;
    }
    
    .ai-bullet:before {
        content: "●";
        position: absolute;
        left: 0;
        color: #1976d2;
        font-size: 7px;
    }
    
    .ai-subheading {
        margin: 5px 0 2px 0;
        font-weight: 700;
        color: #1f2937;
        font-size: 9px;
    }
    
    .ai-advies-sectie {
        background: #f8fafc;
        border: 1px solid #e5e7eb;
        border-radius: 4px;
        padding: 12px;
        margin: 10px 0;
    }
    
    .ai-advies-sectie h4 {
        font-size: 11px;
        font-weight: 700;
        color: #1976d2;
        margin: 0 0 8px 0;
    }
    
    .ai-advies-sectie p {
        font-size: 9px;
        line-height: 1.5;
        margin: 5px 0;
        color: #374151;
    }
    
    .ai-advies-sectie ul {
        list-style: none;
        padding: 0;
        margin: 5px 0;
    }
    
    .ai-advies-sectie ul li {
        padding: 3px 0 3px 15px;
        font-size: 9px;
        position: relative;
        color: #374151;
    }
    
    .ai-advies-sectie ul li:before {
        content: "●";
        position: absolute;
        left: 0;
        color: #1976d2;
    }
    
    .ai-disclaimer {
        background: #fff8e1;
        border-left: 4px solid #f59e0b;
        padding: 10px 12px;
        margin: 15px 0 10px 0;
        font-size: 8px;
        line-height: 1.5;
        color: #78350f;
    }
</style>

@php
    $aiAnalyse = $inspanningstest->complete_ai_analyse ?? null;
    
    // Parse AI analyse in secties (herkenbaar aan emoji headers)
    // Gebruik dezelfde functie als deel 1 (functie wordt maar 1x gedeclareerd)
    if (!function_exists('parseAISections')) {
        function parseAISections($tekst) {
            if (!$tekst) return [];
            
            $secties = [];
            $lines = explode("\n", $tekst);
            $huidigeSectieTitel = null;
            $huidigeSectieInhoud = [];
            
            foreach ($lines as $line) {
                $line = trim($line);
                
                // Skip lege lijnen
                if (empty($line)) continue;
                
                // Skip separators (━━━)
                if (str_contains($line, '━━━')) continue;
                
                // Check voor sectie header (emoji aan het begin)
                if (preg_match('/^[🎯📊💤🏆💪👤⚖️🏃⚡🚴🏊📈💡]/u', $line)) {
                    // Bewaar vorige sectie (als die inhoud heeft)
                    if ($huidigeSectieTitel && !empty($huidigeSectieInhoud)) {
                        $secties[] = [
                            'titel' => $huidigeSectieTitel,
                            'inhoud' => implode("\n", $huidigeSectieInhoud)
                        ];
                    }
                    
                    // Start nieuwe sectie
                    $huidigeSectieTitel = $line;
                    $huidigeSectieInhoud = [];
                } else {
                    // Voeg toe aan huidige sectie
                    if ($huidigeSectieTitel) {
                        $huidigeSectieInhoud[] = $line;
                    }
                }
            }
            
            // Bewaar laatste sectie (als die inhoud heeft)
            if ($huidigeSectieTitel && !empty($huidigeSectieInhoud)) {
                $secties[] = [
                    'titel' => $huidigeSectieTitel,
                    'inhoud' => implode("\n", $huidigeSectieInhoud)
                ];
            }
            
            return $secties;
        }
    }
    
    $parsedSections = parseAISections($aiAnalyse);
    
    // Selecteer trainingsadvies secties voor deel 2 (ZONDER progressie/monitoring)
    $deel2Secties = [];
    $gezieneSecties = []; // Deduplicatie array
    
    foreach ($parsedSections as $sectie) {
        $titelUpper = strtoupper($sectie['titel']);
        
        // Exclude progressie en monitoring (die komen in deel 3)
        if (str_contains($titelUpper, 'PROGRESSIE') || 
            str_contains($titelUpper, 'HERTEST') || 
            str_contains($titelUpper, 'MONITOREN') ||
            str_contains($titelUpper, 'MONITOR') ||
            str_contains($titelUpper, 'METRICS')) {
            continue;
        }
        
        // Exclude ook de eerste secties (die komen in deel 1)
        if (str_contains($titelUpper, 'TESTOVERZICHT') ||
            str_contains($titelUpper, 'ATLET') ||
            str_contains($titelUpper, 'PROFIEL') ||
            str_contains($titelUpper, 'DREMPEL') ||
            (str_contains($titelUpper, 'ANALYSE') && !str_contains($titelUpper, 'TRAINING'))) {
            continue;
        }
        
        // Deduplicatie: check of we deze sectie al hebben gezien
        $normaliseerTitel = preg_replace('/[^A-Z0-9]/', '', $titelUpper);
        if (in_array($normaliseerTitel, $gezieneSecties)) {
            continue; // Skip duplicaat
        }
        
        // Include trainingsadvies, doelstellingen en specifiek advies
        $deel2Secties[] = $sectie;
        $gezieneSecties[] = $normaliseerTitel;
    }
    
    // Verbeter titels - verwijder emoji en maak duidelijker
    foreach ($deel2Secties as &$sectie) {
        $titel = $sectie['titel'];
        // Strip emoji maar behoud tekst
        $titel = preg_replace('/[\x{1F000}-\x{1F9FF}]/u', '', $titel);
        $titel = trim($titel);
        
        // Maak titels specifiek voor trainingsadvies duidelijker
        $titelMappings = [
            'TRAININGSADVIES' => 'Trainingsadvies (80/20 Principe)',
            'TRIATHLON / IRONMAN VOORBEREIDING' => 'Triathlon / Ironman Voorbereiding',
            'TRIATHLON' => 'Triathlon / Ironman Voorbereiding',
            'IRONMAN' => 'Triathlon / Ironman Voorbereiding',
            'GRAN FONDO VOORBEREIDING' => 'Gran Fondo Voorbereiding',
            'GRAN FONDO' => 'Gran Fondo Voorbereiding',
            'KLASSIEKERS / RONDE VAN VLAANDEREN VOORBEREIDING' => 'Klassiekers Voorbereiding',
            'KLASSIEKERS' => 'Klassiekers Voorbereiding',
            'MARATHON VOORBEREIDING' => 'Marathon Voorbereiding (42.2 km)',
            'HALVE MARATHON VOORBEREIDING' => 'Halve Marathon (21.1 km)',
            '10KM WEDSTRIJD VOORBEREIDING' => '10km Wedstrijd',
            '5KM WEDSTRIJD VOORBEREIDING' => '5km Wedstrijd',
            'SNELHEIDSVERBETERING' => 'Snelheid & Tempo Training',
            'GEWICHTSVERLIES' => 'Gewichtsverlies & Lichaamssamenstelling',
            'SPECIFIEK ADVIES' => 'Specifiek Advies voor jouw Doelstellingen',
            'DOELSTELLINGEN' => 'Specifiek Advies voor jouw Doelstellingen'
        ];
        
        foreach ($titelMappings as $oud => $nieuw) {
            if (str_contains(strtoupper($titel), $oud)) {
                $titel = $nieuw;
                break;
            }
        }
        
        $sectie['titel'] = $titel;
    }
@endphp

@if($aiAnalyse && count($deel2Secties) > 0)
<div class="rapport-ai-deel2">
    <h3>🧠 AI Performance Analyse - Deel 2</h3>
    
    @foreach($deel2Secties as $sectie)
        @php
            // Split trainingsadvies in subsecties voor compactere weergave
            $lines = explode("\n", $sectie['inhoud']);
            $subsecties = [];
            $huidigeSubsectie = null;
            $huidigeInhoud = [];
            
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;
                
                // Detecteer subsectie headers (nummering, boldtext met :, of losse regels < 60 chars)
                $isSubheader = preg_match('/^\d+\.\s+/', $line) || 
                              (str_contains($line, ':') && strlen($line) < 65 && !str_starts_with($line, '•') && !str_starts_with($line, '-'));
                
                if ($isSubheader) {
                    // Bewaar vorige subsectie
                    if ($huidigeSubsectie && !empty($huidigeInhoud)) {
                        $subsecties[] = [
                            'titel' => $huidigeSubsectie,
                            'inhoud' => implode("\n", $huidigeInhoud)
                        ];
                    }
                    
                    // Start nieuwe subsectie
                    $huidigeSubsectie = $line;
                    $huidigeInhoud = [];
                } else {
                    $huidigeInhoud[] = $line;
                }
            }
            
            // Bewaar laatste subsectie
            if ($huidigeSubsectie && !empty($huidigeInhoud)) {
                $subsecties[] = [
                    'titel' => $huidigeSubsectie,
                    'inhoud' => implode("\n", $huidigeInhoud)
                ];
            }
            
            $heeftSubsecties = count($subsecties) > 0;
        @endphp
        
        <div style="margin: 8px 0;">
            <div class="ai-sectie-titel">{{ $sectie['titel'] }}</div>
            
            @if($heeftSubsecties)
                {{-- Toon subsecties in 2-kolommen grid --}}
                <div class="ai-2col-layout">
                    @foreach($subsecties as $sub)
                        <div style="margin-bottom: 6px;">
                            <div class="ai-subheading">{{ $sub['titel'] }}</div>
                            <div class="ai-sectie-content" style="margin-top: 2px;">
                                @include('inspanningstest.partials._ai_sectie_content', ['inhoud' => $sub['inhoud']])
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                {{-- Geen subsecties, toon gewoon de volledige inhoud --}}
                <div class="ai-sectie-content">
                    @include('inspanningstest.partials._ai_sectie_content', ['inhoud' => $sectie['inhoud']])
                </div>
            @endif
        </div>
    @endforeach
    
    {{-- Disclaimer --}}
    <div class="ai-disclaimer">
        <strong>💡 Let op:</strong> Deze AI-gegenereerde analyse dient als aanvulling op professioneel sportadvies. 
        De AI analyseert patronen en doet aanbevelingen, maar kan niet alle individuele omstandigheden meewegen. 
        Bespreek belangrijke trainingsbeslissingen altijd met je trainer of coach. Start geleidelijk met nieuwe trainingen en luister naar je lichaam.
    </div>
</div>
@else
<div class="rapport-ai-deel2">
    <h3>🧠 AI Performance Analyse - Advies</h3>
    <p style="text-align: center; padding: 20px; color: #9ca3af; font-size: 10px;">
        Geen AI analyse beschikbaar voor deze test.
    </p>
</div>
@endif