{{-- AI Analyse Rapport - Deel 1: Overzicht & Drempels --}}
{{-- Variabele sleutel: {{INSPANNINGSTEST_AI_ANALYSE_DEEL1}} --}}

<style>
    .rapport-ai-deel1 {
        font-family: Tahoma, Arial, sans-serif;
        font-size: 9px;
        line-height: 1.5;
        color: #1f2937;
        margin: 15px 0;
        width: 120%;
    }
    
    .rapport-ai-deel1 h3 {
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
</style>

@php
    $aiAnalyse = $inspanningstest->complete_ai_analyse ?? null;
    
    // Parse AI analyse in secties (herkenbaar aan emoji headers)
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
    
    // Selecteer ALLEEN eerste helft van secties voor deel 1
    $totalSections = count($parsedSections);
    $midPoint = (int) ceil($totalSections / 2);
    $deel1Secties = array_slice($parsedSections, 0, $midPoint);
    
    // Verbeter titels - verwijder emoji en maak duidelijker
    foreach ($deel1Secties as &$sectie) {
        $titel = $sectie['titel'];
        // Strip emoji maar behoud tekst
        $titel = preg_replace('/[\x{1F000}-\x{1F9FF}]/u', '', $titel);
        $titel = trim($titel);
        
        // Maak titels leesbaarder
        $titel = str_replace(['INSPANNINGSTEST ANALYSE', 'TESTOVERZICHT', 'ATLET PROFIEL', 'GEMETEN DREMPELWAARDEN'], 
                            ['Inspanningstest Analyse', 'Test Overzicht', 'Atleet Profiel', 'Gemeten Drempelwaarden'], $titel);
        
        $sectie['titel'] = $titel;
    }
@endphp

@if($aiAnalyse && count($deel1Secties) > 0)
<div class="rapport-ai-deel1">
    <h3>🧠 AI Performance Analyse - Deel 1</h3>
    
    @php
        // Detecteer welke secties naast elkaar kunnen (korte secties)
        $sectiesMetLengte = array_map(function($sectie) {
            return [
                'sectie' => $sectie,
                'lengte' => strlen($sectie['inhoud'])
            ];
        }, $deel1Secties);
        
        $i = 0;
        $totalSecties = count($deel1Secties);
    @endphp
    
    @while($i < $totalSecties)
        @php
            $huidigeSectie = $deel1Secties[$i];
            $volgendeSectie = ($i + 1 < $totalSecties) ? $deel1Secties[$i + 1] : null;
            
            // Bepaal of deze 2 secties naast elkaar kunnen (als beide < 400 chars)
            $huidigeLengte = strlen($huidigeSectie['inhoud']);
            $volgendeLengte = $volgendeSectie ? strlen($volgendeSectie['inhoud']) : 999999;
            
            $naarElkaar = ($volgendeSectie && $huidigeLengte < 400 && $volgendeLengte < 400);
        @endphp
        
        @if($naarElkaar)
            {{-- 2 Kolommen Layout voor korte secties --}}
            <div class="ai-2col-layout">
                {{-- Linker sectie --}}
                <div>
                    <div class="ai-sectie-titel">{{ $huidigeSectie['titel'] }}</div>
                    <div class="ai-sectie-content">
                        @include('inspanningstest.partials._ai_sectie_content', ['inhoud' => $huidigeSectie['inhoud']])
                    </div>
                </div>
                
                {{-- Rechter sectie --}}
                <div>
                    <div class="ai-sectie-titel">{{ $volgendeSectie['titel'] }}</div>
                    <div class="ai-sectie-content">
                        @include('inspanningstest.partials._ai_sectie_content', ['inhoud' => $volgendeSectie['inhoud']])
                    </div>
                </div>
            </div>
            @php $i += 2; @endphp
        @else
            {{-- Volle breedte voor lange secties --}}
            <div style="margin: 8px 0;">
                <div class="ai-sectie-titel">{{ $huidigeSectie['titel'] }}</div>
                <div class="ai-sectie-content">
                    @include('inspanningstest.partials._ai_sectie_content', ['inhoud' => $huidigeSectie['inhoud']])
                </div>
            </div>
            @php $i += 1; @endphp
        @endif
    @endwhile
</div>
@else
<div class="rapport-ai-deel1">
    <h3>🧠 AI Performance Analyse</h3>
    <p style="text-align: center; padding: 20px; color: #9ca3af; font-size: 10px;">
        Geen AI analyse beschikbaar voor deze test.
    </p>
</div>
@endif