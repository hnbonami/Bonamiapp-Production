{{-- AI Analyse Rapport - Deel 3: Progressie & Monitoring --}}
{{-- Variabele sleutel: {{INSPANNINGSTEST_AI_ANALYSE_DEEL3}} --}}

<style>
    .rapport-ai-deel3 {
        font-family: Tahoma, Arial, sans-serif;
        font-size: 9px;
        line-height: 1.5;
        color: #1f2937;
        margin: 15px 0;
        width: 130%;
    }
    
    .rapport-ai-deel3 h3 {
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
    
    // Parse AI analyse in secties
    if (!function_exists('parseAISections')) {
        function parseAISections($tekst) {
            if (!$tekst) return [];
            
            $secties = [];
            $lines = explode("\n", $tekst);
            $huidigeSectieTitel = null;
            $huidigeSectieInhoud = [];
            
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;
                if (str_contains($line, '━━━')) continue;
                
                if (preg_match('/^[🎯📊💤🏆💪👤⚖️🏃⚡🚴🏊📈💡]/u', $line)) {
                    if ($huidigeSectieTitel && !empty($huidigeSectieInhoud)) {
                        $secties[] = [
                            'titel' => $huidigeSectieTitel,
                            'inhoud' => implode("\n", $huidigeSectieInhoud)
                        ];
                    }
                    $huidigeSectieTitel = $line;
                    $huidigeSectieInhoud = [];
                } else {
                    if ($huidigeSectieTitel) {
                        $huidigeSectieInhoud[] = $line;
                    }
                }
            }
            
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
    
    // Vind progressie en monitoring secties (meestal de laatste secties)
    $deel3Secties = [];
    foreach ($parsedSections as $sectie) {
        $titelUpper = strtoupper($sectie['titel']);
        if (str_contains($titelUpper, 'PROGRESSIE') || 
            str_contains($titelUpper, 'HERTEST') || 
            str_contains($titelUpper, 'MONITOREN') ||
            str_contains($titelUpper, 'METRICS')) {
            $deel3Secties[] = $sectie;
        }
    }
    
    // Verbeter titels
    foreach ($deel3Secties as &$sectie) {
        $titel = $sectie['titel'];
        $titel = preg_replace('/[\x{1F000}-\x{1F9FF}]/u', '', $titel);
        $titel = trim($titel);
        
        $titelMappings = [
            'PROGRESSIE EN HERTEST' => 'Progressie & Hertesten',
            'TE MONITOREN METRICS' => 'Belangrijke Monitoring Metrics',
            'MONITORING' => 'Training Monitoring'
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

@if($aiAnalyse && count($deel3Secties) > 0)
<div class="rapport-ai-deel3">
    <h3>🧠 AI Performance Analyse - Deel 3</h3>
    
    @php
        $i = 0;
        $totalSecties = count($deel3Secties);
    @endphp
    
    @while($i < $totalSecties)
        @php
            $huidigeSectie = $deel3Secties[$i];
            $volgendeSectie = ($i + 1 < $totalSecties) ? $deel3Secties[$i + 1] : null;
            
            $huidigeLengte = strlen($huidigeSectie['inhoud']);
            $volgendeLengte = $volgendeSectie ? strlen($volgendeSectie['inhoud']) : 999999;
            
            $naarElkaar = ($volgendeSectie && $huidigeLengte < 400 && $volgendeLengte < 400);
        @endphp
        
        @if($naarElkaar)
            {{-- 2 Kolommen Layout --}}
            <div class="ai-2col-layout">
                <div>
                    <div class="ai-sectie-titel">{{ $huidigeSectie['titel'] }}</div>
                    <div class="ai-sectie-content">
                        @include('inspanningstest.partials._ai_sectie_content', ['inhoud' => $huidigeSectie['inhoud']])
                    </div>
                </div>
                <div>
                    <div class="ai-sectie-titel">{{ $volgendeSectie['titel'] }}</div>
                    <div class="ai-sectie-content">
                        @include('inspanningstest.partials._ai_sectie_content', ['inhoud' => $volgendeSectie['inhoud']])
                    </div>
                </div>
            </div>
            @php $i += 2; @endphp
        @else
            {{-- Volle breedte --}}
            <div style="margin: 8px 0;">
                <div class="ai-sectie-titel">{{ $huidigeSectie['titel'] }}</div>
                <div class="ai-sectie-content">
                    @include('inspanningstest.partials._ai_sectie_content', ['inhoud' => $huidigeSectie['inhoud']])
                </div>
            </div>
            @php $i += 1; @endphp
        @endif
    @endwhile
    
    {{-- Disclaimer --}}
    <div class="ai-disclaimer">
        <strong>💡 Let op:</strong> Deze AI-gegenereerde analyse dient als aanvulling op professioneel sportadvies. 
        De AI analyseert patronen en doet aanbevelingen, maar kan niet alle individuele omstandigheden meewegen. 
        Bespreek belangrijke trainingsbeslissingen altijd met je trainer of coach. Start geleidelijk met nieuwe trainingen en luister naar je lichaam.
    </div>
</div>
@else
<div class="rapport-ai-deel3">
    <h3>🧠 AI Performance Analyse - Progressie</h3>
    <p style="text-align: center; padding: 20px; color: #9ca3af; font-size: 10px;">
        Geen progressie analyse beschikbaar.
    </p>
</div>
@endif
