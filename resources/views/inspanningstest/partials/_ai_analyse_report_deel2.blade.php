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
    
    // Selecteer ALLEEN tweede helft van secties voor deel 2
    $totalSections = count($parsedSections);
    $midPoint = (int) ceil($totalSections / 2);
    $deel2Secties = array_slice($parsedSections, $midPoint); // Start vanaf midpoint, pak de rest
    
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
            'GRAN FONDO VOORBEREIDING' => 'Gran Fondo Voorbereiding',
            'KLASSIEKERS / RONDE VAN VLAANDEREN VOORBEREIDING' => 'Klassiekers Voorbereiding',
            'MARATHON VOORBEREIDING' => 'Marathon Voorbereiding (42.2 km)',
            'HALVE MARATHON VOORBEREIDING' => 'Halve Marathon (21.1 km)',
            '10KM WEDSTRIJD VOORBEREIDING' => '10km Wedstrijd',
            '5KM WEDSTRIJD VOORBEREIDING' => '5km Wedstrijd',
            'SNELHEIDSVERBETERING' => 'Snelheid & Tempo Training',
            'GEWICHTSVERLIES' => 'Gewichtsverlies & Lichaamssamenstelling',
            'PROGRESSIE EN HERTEST' => 'Progressie & Hertesten',
            'TE MONITOREN METRICS' => 'Belangrijke Metrics'
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
    
    @php
        // Herstructureer trainingsadvies in subsecties voor 2-kolommen layout
        function splitTrainingsadviesInSubsecties($inhoud) {
            $lines = explode("\n", $inhoud);
            $subsecties = [];
            $huidigeSubsectie = null;
            $huidigeInhoud = [];
            
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;
                
                // Detecteer subsectie headers (nummer + titel of vetgedrukte tekst met :)
                if (preg_match('/^\d+\.\s+(.+)/', $line, $matches) || 
                    (str_contains($line, ':') && strlen($line) < 60 && !str_starts_with($line, '•'))) {
                    
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
            
            return $subsecties;
        }
        
        $i = 0;
        $totalSecties = count($deel2Secties);
    @endphp
    
    @while($i < $totalSecties)
        @php
            $huidigeSectie = $deel2Secties[$i];
            $sectieNaam = strtoupper($huidigeSectie['titel']);
            
            // Check of dit trainingsadvies of doelstellingen sectie is
            $isTrainingsAdvies = str_contains($sectieNaam, 'TRAINING') || 
                                 str_contains($sectieNaam, 'TRIATHLON') || 
                                 str_contains($sectieNaam, 'MARATHON') ||
                                 str_contains($sectieNaam, 'FONDO') ||
                                 str_contains($sectieNaam, 'KLASSIEK') ||
                                 str_contains($sectieNaam, 'SNELHEID') ||
                                 str_contains($sectieNaam, 'GEWICHT') ||
                                 str_contains($sectieNaam, 'WEDSTRIJD');
            
            $volgendeSectie = ($i + 1 < $totalSecties) ? $deel2Secties[$i + 1] : null;
            $huidigeLengte = strlen($huidigeSectie['inhoud']);
            $volgendeLengte = $volgendeSectie ? strlen($volgendeSectie['inhoud']) : 999999;
            $naarElkaar = ($volgendeSectie && $huidigeLengte < 350 && $volgendeLengte < 350);
        @endphp
        
        @if($isTrainingsAdvies)
            {{-- Trainingsadvies: split in subsecties en toon in 2 kolommen --}}
            @php
                $subsecties = splitTrainingsadviesInSubsecties($huidigeSectie['inhoud']);
                $totalSubs = count($subsecties);
            @endphp
            
            <div style="margin: 8px 0;">
                <div class="ai-sectie-titel">{{ $huidigeSectie['titel'] }}</div>
                
                {{-- Toon subsecties in 2 kolommen --}}
                @if($totalSubs > 1)
                    <div class="ai-2col-layout">
                        @php $subIndex = 0; @endphp
                        @while($subIndex < $totalSubs)
                            @php
                                $linksSub = $subsecties[$subIndex];
                                $rechtsSub = ($subIndex + 1 < $totalSubs) ? $subsecties[$subIndex + 1] : null;
                            @endphp
                            
                            {{-- Linker kolom --}}
                            <div>
                                <div class="ai-subheading">{{ $linksSub['titel'] }}</div>
                                <div class="ai-sectie-content" style="margin-top: 3px;">
                                    @include('inspanningstest.partials._ai_sectie_content', ['inhoud' => $linksSub['inhoud']])
                                </div>
                            </div>
                            
                            {{-- Rechter kolom --}}
                            @if($rechtsSub)
                                <div>
                                    <div class="ai-subheading">{{ $rechtsSub['titel'] }}</div>
                                    <div class="ai-sectie-content" style="margin-top: 3px;">
                                        @include('inspanningstest.partials._ai_sectie_content', ['inhoud' => $rechtsSub['inhoud']])
                                    </div>
                                </div>
                                @php $subIndex += 2; @endphp
                            @else
                                {{-- Lege kolom als er geen rechter subsectie is --}}
                                <div></div>
                                @php $subIndex += 1; @endphp
                            @endif
                        @endwhile
                    </div>
                @else
                    {{-- Geen subsecties, toon gewoon de inhoud --}}
                    <div class="ai-sectie-content">
                        @include('inspanningstest.partials._ai_sectie_content', ['inhoud' => $huidigeSectie['inhoud']])
                    </div>
                @endif
            </div>
            @php $i += 1; @endphp
            
        @elseif($naarElkaar)
            {{-- 2 Kolommen Layout voor korte niet-trainingsadvies secties --}}
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
            {{-- Volle breedte voor lange niet-trainingsadvies secties --}}
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
<div class="rapport-ai-deel2">
    <h3>🧠 AI Performance Analyse - Advies</h3>
    <p style="text-align: center; padding: 20px; color: #9ca3af; font-size: 10px;">
        Geen AI analyse beschikbaar voor deze test.
    </p>
</div>
@endif