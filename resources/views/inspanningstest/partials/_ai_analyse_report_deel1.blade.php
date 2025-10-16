{{-- AI Analyse Rapport - Deel 1: Overzicht & Drempels --}}
{{-- Variabele sleutel: {{INSPANNINGSTEST_AI_ANALYSE_DEEL1}} --}}

<style>
    .rapport-ai-deel1 {
        font-family: Tahoma, Arial, sans-serif;
        font-size: 10px;
        line-height: 1.4;
        color: #1f2937;
        margin: 20px 0;
        width: 120%;
    }
    
    .rapport-ai-deel1 h3 {
        font-size: 14px;
        font-weight: 700;
        color: #0f4c75;
        margin: 15px 0 10px 0;
        padding: 8px 10px;
        background-color: #c8e1eb;
        border-left: 4px solid #0f4c75;
    }
    
    .ai-grid-2col {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
        margin: 10px 0;
    }
    
    .ai-info-box {
        background: #f8fafc;
        border: 1px solid #e5e7eb;
        border-radius: 4px;
        padding: 12px;
    }
    
    .ai-info-box h4 {
        font-size: 11px;
        font-weight: 700;
        color: #1976d2;
        margin: 0 0 8px 0;
        padding-bottom: 6px;
        border-bottom: 2px solid #c8e1eb;
    }
    
    .ai-info-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .ai-info-list li {
        padding: 3px 0;
        font-size: 9px;
        color: #374151;
    }
    
    .ai-info-list li strong {
        color: #1f2937;
        font-weight: 600;
    }
    
    .drempel-box {
        background: white;
        border: 2px solid #c8e1eb;
        border-radius: 4px;
        padding: 10px;
        margin: 8px 0;
    }
    
    .drempel-box h5 {
        font-size: 10px;
        font-weight: 700;
        color: #0f4c75;
        margin: 0 0 6px 0;
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
    
    // Debug log
    \Log::info('AI Rapport Deel 1', [
        'total_sections' => $totalSections,
        'midpoint' => $midPoint,
        'deel1_count' => count($deel1Secties),
        'deel1_titles' => array_column($deel1Secties, 'titel')
    ]);
@endphp

@if($aiAnalyse && count($deel1Secties) > 0)
<div class="rapport-ai-deel1">
    <h3>🧠 AI Performance Analyse - Deel 1</h3>
    
    @foreach($deel1Secties as $sectie)
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
</div>
@else
<div class="rapport-ai-deel1">
    <h3>🧠 AI Performance Analyse</h3>
    <p style="text-align: center; padding: 20px; color: #9ca3af; font-size: 10px;">
        Geen AI analyse beschikbaar voor deze test.
    </p>
</div>
@endif