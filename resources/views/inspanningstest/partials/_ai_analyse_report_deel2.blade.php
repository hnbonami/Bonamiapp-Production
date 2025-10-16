{{-- AI Analyse Rapport - Deel 2: Advies & Progressie --}}
{{-- Variabele sleutel: {{INSPANNINGSTEST_AI_ANALYSE_DEEL2}} --}}

<style>
    .rapport-ai-deel2 {
        font-family: Tahoma, Arial, sans-serif;
        font-size: 10px;
        line-height: 1.4;
        color: #1f2937;
        margin: 20px 0;
        width: 120%;
    }
    
    .rapport-ai-deel2 h3 {
        font-size: 14px;
        font-weight: 700;
        color: #0f4c75;
        margin: 15px 0 10px 0;
        padding: 8px 10px;
        background-color: #c8e1eb;
        border-left: 4px solid #0f4c75;
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
    
    // Selecteer ALLEEN tweede helft van secties voor deel 2
    $totalSections = count($parsedSections);
    $midPoint = (int) ceil($totalSections / 2);
    $deel2Secties = array_slice($parsedSections, $midPoint); // Start vanaf midpoint, pak de rest
    
    // Debug log
    \Log::info('AI Rapport Deel 2', [
        'total_sections' => $totalSections,
        'midpoint' => $midPoint,
        'deel2_count' => count($deel2Secties),
        'deel2_titles' => array_column($deel2Secties, 'titel')
    ]);
@endphp

@if($aiAnalyse && count($deel2Secties) > 0)
<div class="rapport-ai-deel2">
    <h3>🧠 AI Performance Analyse - Deel 2</h3>
    
    @foreach($deel2Secties as $sectie)
        <div style="margin: 15px 0;">
            {{-- Sectie Titel --}}
            <h4 style="font-size: 12px; font-weight: 700; color: #0f4c75; margin: 10px 0 8px 0; padding: 6px 10px; background: #f0f9ff; border-left: 3px solid #1976d2;">
                {{ $sectie['titel'] }}
            </h4>
            
            {{-- Sectie Inhoud --}}
            <div style="font-size: 9px; line-height: 1.6; color: #374151; padding: 8px 12px; background: #fafafa;">
                @php
                    $lines = explode("\n", $sectie['inhoud']);
                @endphp
                
                @foreach($lines as $line)
                    @php
                        $line = trim($line);
                        if (empty($line)) continue;
                        
                        $isBulletPoint = str_starts_with($line, '•') || str_starts_with($line, '-') || str_starts_with($line, '*') || str_starts_with($line, '◦');
                        $isSubheading = str_contains($line, ':') && strlen($line) < 80 && !$isBulletPoint;
                    @endphp
                    
                    @if($isBulletPoint)
                        <div style="padding: 2px 0 2px 15px; position: relative;">
                            <span style="position: absolute; left: 0; color: #1976d2;">●</span>
                            {{ ltrim($line, '•-*◦ ') }}
                        </div>
                    @elseif($isSubheading)
                        <p style="margin: 6px 0 3px 0; font-weight: 700; color: #1f2937;">
                            {{ $line }}
                        </p>
                    @else
                        <p style="margin: 4px 0;">{{ $line }}</p>
                    @endif
                @endforeach
            </div>
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