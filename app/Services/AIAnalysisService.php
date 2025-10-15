<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * AI Analysis Service voor Inspanningstesten
 * Genereert uitgebreide analyses op basis van testdata
 */
class AIAnalysisService
{
    protected $apiKey;
    protected $model;
    protected $temperature;
    protected $maxTokens;

    public function __construct()
    {
        // Haal API key op uit .env via services config of direct
        $this->apiKey = config('services.openai.key') ?? env('OPENAI_API_KEY');
        $this->model = config('ai_analysis.model', 'gpt-4o-mini');
        $this->temperature = config('ai_analysis.temperature', 0.4);
        $this->maxTokens = config('ai_analysis.max_tokens', 1500);
    }

    /**
     * Genereer complete analyse van inspanningstestdata
     */
    public function generateCompleteAnalysis(array $testData): array
    {
        Log::info('AI Complete Analysis gestart', [
            'testtype' => $testData['testtype'] ?? 'onbekend'
        ]);

        // Check of API key aanwezig is
        if (empty($this->apiKey)) {
            Log::error('OpenAI API key ontbreekt');
            return [
                'success' => false,
                'error' => 'OpenAI API key niet geconfigureerd',
                'fallback' => $this->generateFallbackAnalysis($testData)
            ];
        }

        try {
            // Verrijk data met berekende metrics en populatienormen
            $enrichedData = $this->enrichTestData($testData);
            
            // Bouw de prompt
            $prompt = $this->buildPrompt($enrichedData);
            
            // Roep OpenAI API aan
            $response = $this->callOpenAI($prompt);
            
            return [
                'success' => true,
                'analysis' => $response,
                'metadata' => [
                    'model' => $this->model,
                    'timestamp' => now()->toIso8601String(),
                    'testtype' => $testData['testtype'] ?? 'onbekend',
                ]
            ];

        } catch (\Exception $e) {
            Log::error('AI analyse fout', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'fallback' => $this->generateFallbackAnalysis($testData)
            ];
        }
    }

    /**
     * Verrijk testdata met berekende metrics en populatienormen
     */
    protected function enrichTestData(array $testData): array
    {
        // Bereken Watt/kg voor fietstesten
        if (isset($testData['lichaamsgewicht_kg']) && $testData['lichaamsgewicht_kg'] > 0) {
            $gewicht = (float) $testData['lichaamsgewicht_kg'];
            
            if (isset($testData['aerobe_drempel_vermogen'])) {
                $testData['aerobe_drempel_watt_per_kg'] = round($testData['aerobe_drempel_vermogen'] / $gewicht, 2);
            }
            
            if (isset($testData['anaerobe_drempel_vermogen'])) {
                $testData['anaerobe_drempel_watt_per_kg'] = round($testData['anaerobe_drempel_vermogen'] / $gewicht, 2);
            }
        }
        
        // Bereken hartslagreserve percentages
        if (isset($testData['maximale_hartslag_bpm']) && isset($testData['hartslag_rust_bpm'])) {
            $maxHR = (float) $testData['maximale_hartslag_bpm'];
            $rustHR = (float) $testData['hartslag_rust_bpm'];
            $hrr = $maxHR - $rustHR;
            $testData['hartslagreserve'] = $hrr;
            
            if (isset($testData['aerobe_drempel_hartslag']) && $hrr > 0) {
                $testData['aerobe_drempel_percentage_hrr'] = round((($testData['aerobe_drempel_hartslag'] - $rustHR) / $hrr) * 100, 1);
            }
            
            if (isset($testData['anaerobe_drempel_hartslag']) && $hrr > 0) {
                $testData['anaerobe_drempel_percentage_hrr'] = round((($testData['anaerobe_drempel_hartslag'] - $rustHR) / $hrr) * 100, 1);
            }
        }
        
        // Voeg populatie classificatie toe
        $testData['population_comparison'] = $this->getPopulationComparison($testData);
        
        return $testData;
    }

    /**
     * Vergelijk testdata met populatienormen
     */
    protected function getPopulationComparison(array $testData): ?array
    {
        $testtype = $testData['testtype'] ?? '';
        $leeftijd = $testData['leeftijd'] ?? 35;
        $geslacht = $testData['geslacht'] ?? 'male'; // Gebruik geslacht uit data
        
        // Bepaal sporttype
        $sporttype = str_contains($testtype, 'fiets') ? 'cycling' : 'running';
        
        // Bepaal leeftijdsgroep
        $leeftijdsgroep = $this->getLeeftijdsgroep($leeftijd);
        
        // Haal normen op voor correct geslacht
        $normen = config("ai_analysis.population_norms.{$sporttype}.{$geslacht}.{$leeftijdsgroep}");
        
        if (!$normen) {
            // Fallback naar male als geslacht niet gevonden
            $normen = config("ai_analysis.population_norms.{$sporttype}.male.{$leeftijdsgroep}");
        }
        
        if (!$normen) {
            return null;
        }
        
        // Classificeer prestatie
        $classificatie = null;
        if ($sporttype === 'cycling' && isset($testData['anaerobe_drempel_watt_per_kg'])) {
            $wattPerKg = (float) $testData['anaerobe_drempel_watt_per_kg'];
            $classificatie = $this->classifyPerformance($wattPerKg, $normen['lt2_watt_per_kg'] ?? []);
        } elseif ($sporttype === 'running' && isset($testData['anaerobe_drempel_vermogen'])) {
            $speed = (float) $testData['anaerobe_drempel_vermogen'];
            $classificatie = $this->classifyPerformance($speed, $normen['lt2_speed_kmh'] ?? []);
        }
        
        return [
            'leeftijdsgroep' => $leeftijdsgroep,
            'sporttype' => $sporttype,
            'geslacht' => $geslacht,
            'normen' => $normen,
            'classificatie' => $classificatie,
        ];
    }

    protected function getLeeftijdsgroep(int $leeftijd): string
    {
        if ($leeftijd >= 18 && $leeftijd <= 29) return '18-29';
        if ($leeftijd >= 30 && $leeftijd <= 39) return '30-39';
        if ($leeftijd >= 40 && $leeftijd <= 49) return '40-49';
        return '50+';
    }

    protected function classifyPerformance(float $value, array $normen): ?array
    {
        if (empty($normen)) return null;

        if ($value >= ($normen['elite'] ?? PHP_INT_MAX)) {
            return ['level' => 'Elite', 'description' => 'Uitzonderlijk hoog niveau', 'percentile' => 95];
        }
        if ($value >= ($normen['good'] ?? PHP_INT_MAX)) {
            return ['level' => 'Zeer Goed', 'description' => 'Bovengemiddeld niveau', 'percentile' => 75];
        }
        if ($value >= ($normen['average'] ?? PHP_INT_MAX)) {
            return ['level' => 'Goed', 'description' => 'Gemiddeld niveau', 'percentile' => 50];
        }
        if ($value >= ($normen['below'] ?? PHP_INT_MAX)) {
            return ['level' => 'Redelijk', 'description' => 'Ontwikkelpotentieel', 'percentile' => 30];
        }
        
        return ['level' => 'Ontwikkeling Nodig', 'description' => 'Veel groeipotentieel', 'percentile' => 15];
    }

    protected function buildPrompt(array $testData): array
    {
        $systemPrompt = config('ai_analysis.system_prompt');
        $template = config('ai_analysis.analysis_template');
        
        $formattedData = $this->formatTestData($testData);
        $userPrompt = str_replace('{testdata}', $formattedData, $template);
        
        return [
            'system' => $systemPrompt,
            'user' => $userPrompt
        ];
    }

    protected function formatTestData(array $testData): string
    {
        $output = "=== TESTINFORMATIE ===\n";
        $output .= "Testtype: " . ($testData['testtype'] ?? 'Onbekend') . "\n";
        $output .= "Datum: " . ($testData['testdatum'] ?? 'Onbekend') . "\n";
        
        $output .= "\n=== ATLEET PROFIEL ===\n";
        $output .= "Leeftijd: " . ($testData['leeftijd'] ?? 35) . " jaar\n";
        $output .= "Gewicht: " . ($testData['lichaamsgewicht_kg'] ?? 'Onbekend') . " kg\n";
        $output .= "Doelstellingen: " . ($testData['specifieke_doelstellingen'] ?? 'Algemene fitheid') . "\n";
        
        $output .= "\n=== GEMETEN DREMPELWAARDEN ===\n";
        $output .= "Aërobe drempel (LT1):\n";
        $output .= "  - Vermogen/Snelheid: " . ($testData['aerobe_drempel_vermogen'] ?? 'N/A') . "\n";
        $output .= "  - Hartslag: " . ($testData['aerobe_drempel_hartslag'] ?? 'N/A') . " bpm\n";
        
        if (isset($testData['aerobe_drempel_watt_per_kg'])) {
            $output .= "  - Watt/kg: " . $testData['aerobe_drempel_watt_per_kg'] . " W/kg\n";
        }
        
        $output .= "\nAnaërobe drempel (LT2):\n";
        $output .= "  - Vermogen/Snelheid: " . ($testData['anaerobe_drempel_vermogen'] ?? 'N/A') . "\n";
        $output .= "  - Hartslag: " . ($testData['anaerobe_drempel_hartslag'] ?? 'N/A') . " bpm\n";
        
        if (isset($testData['anaerobe_drempel_watt_per_kg'])) {
            $output .= "  - Watt/kg: " . $testData['anaerobe_drempel_watt_per_kg'] . " W/kg\n";
        }
        
        if (isset($testData['population_comparison']['classificatie'])) {
            $class = $testData['population_comparison']['classificatie'];
            $output .= "\n=== POPULATIE VERGELIJKING ===\n";
            $output .= "Classificatie: " . $class['level'] . "\n";
            $output .= "Beschrijving: " . $class['description'] . "\n";
            $output .= "Percentiel: Top " . $class['percentile'] . "%\n";
        }
        
        return $output;
    }

    protected function callOpenAI(array $prompts): string
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(60)->post('https://api.openai.com/v1/chat/completions', [
            'model' => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => $prompts['system']],
                ['role' => 'user', 'content' => $prompts['user']]
            ],
            'temperature' => $this->temperature,
            'max_tokens' => $this->maxTokens,
        ]);

        if (!$response->successful()) {
            throw new \Exception('OpenAI API fout: ' . $response->body());
        }

        $data = $response->json();
        return $data['choices'][0]['message']['content'] ?? 'Geen analyse gegenereerd';
    }

    protected function generateFallbackAnalysis(array $testData): string
    {
        $testtype = $testData['testtype'] ?? 'Onbekend';
        $eenheid = str_contains($testtype, 'fiets') ? 'Watt' : 'km/h';
        
        $output = "🏃‍♂️ INSPANNINGSTEST ANALYSE\n\n";
        $output .= "Geautomatiseerde analyse op basis van uw testresultaten\n\n";
        $output .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        
        // TESTOVERZICHT
        $output .= "📊 TESTOVERZICHT\n\n";
        $output .= "• Testtype: " . ucfirst(str_replace('_', ' ', $testtype)) . "\n";
        $output .= "• Datum: " . ($testData['testdatum'] ?? date('Y-m-d')) . "\n";
        if (!empty($testData['testlocatie'])) {
            $output .= "• Locatie: " . $testData['testlocatie'] . "\n";
        }
        if (!empty($testData['analyse_methode'])) {
            $output .= "• Analyse methode: " . ucfirst(str_replace('_', ' ', $testData['analyse_methode'])) . "\n";
        }
        $output .= "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        
        // ATLET PROFIEL
        $output .= "👤 ATLET PROFIEL\n\n";
        $leeftijd = $testData['leeftijd'] ?? null;
        $gewicht = $testData['lichaamsgewicht_kg'] ?? null;
        $geslacht = $testData['geslacht'] ?? 'male';
        $geslachtLabel = $geslacht === 'female' ? 'Vrouw' : 'Man';
        
        if ($leeftijd) $output .= "• Leeftijd: " . $leeftijd . " jaar\n";
        if ($geslacht) $output .= "• Geslacht: " . $geslachtLabel . "\n";
        if ($gewicht) $output .= "• Gewicht: " . $gewicht . " kg\n";
        if (!empty($testData['lichaamslengte_cm'])) {
            $output .= "• Lengte: " . $testData['lichaamslengte_cm'] . " cm\n";
        }
        if (!empty($testData['bmi'])) {
            $output .= "• BMI: " . $testData['bmi'] . "\n";
        }
        
        if (!empty($testData['specifieke_doelstellingen'])) {
            $output .= "\nUw doelstellingen:\n";
            $output .= $testData['specifieke_doelstellingen'] . "\n";
        }
        $output .= "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        
        // GEMETEN DREMPELWAARDEN
        $output .= "🎯 GEMETEN DREMPELWAARDEN\n\n";
        
        $lt1 = $testData['aerobe_drempel_vermogen'] ?? null;
        $lt1_hr = $testData['aerobe_drempel_hartslag'] ?? null;
        $lt2 = $testData['anaerobe_drempel_vermogen'] ?? null;
        $lt2_hr = $testData['anaerobe_drempel_hartslag'] ?? null;
        
        if ($lt1 || $lt2) {
            // Aerobe drempel
            $output .= "Aërobe Drempel (LT1)\n\n";
            if ($lt1) $output .= "• Vermogen/Snelheid: " . round($lt1, 1) . " " . $eenheid . "\n";
            if ($lt1_hr) $output .= "• Hartslag: " . round($lt1_hr) . " bpm\n";
            if (isset($testData['aerobe_drempel_watt_per_kg'])) {
                $output .= "• Relatief vermogen: " . $testData['aerobe_drempel_watt_per_kg'] . " W/kg\n";
            }
            if (isset($testData['aerobe_drempel_percentage_hrr'])) {
                $output .= "• Percentage hartslagreserve: " . $testData['aerobe_drempel_percentage_hrr'] . "%\n";
            }
            
            // Anaerobe drempel
            $output .= "\nAnaërobe Drempel (LT2)\n\n";
            if ($lt2) $output .= "• Vermogen/Snelheid: " . round($lt2, 1) . " " . $eenheid . "\n";
            if ($lt2_hr) $output .= "• Hartslag: " . round($lt2_hr) . " bpm\n";
            if (isset($testData['anaerobe_drempel_watt_per_kg'])) {
                $output .= "• Relatief vermogen: " . $testData['anaerobe_drempel_watt_per_kg'] . " W/kg\n";
            }
            if (isset($testData['anaerobe_drempel_percentage_hrr'])) {
                $output .= "• Percentage hartslagreserve: " . $testData['anaerobe_drempel_percentage_hrr'] . "%\n";
            }
            $output .= "\n";
        } else {
            $output .= "Drempelwaarden zijn nog niet bepaald. Vul de testresultaten in en genereer de grafiek.\n\n";
        }
        
        $output .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        
        // PRESTATIE CLASSIFICATIE MET POPULATIEVERGELIJKING
        if (isset($testData['population_comparison']['classificatie'])) {
            $class = $testData['population_comparison']['classificatie'];
            $normen = $testData['population_comparison']['normen'] ?? [];
            $leeftijdsgroep = $testData['population_comparison']['leeftijdsgroep'] ?? '';
            $geslachtComp = $testData['population_comparison']['geslacht'] ?? 'male';
            $geslachtCompLabel = $geslachtComp === 'female' ? 'vrouwen' : 'mannen';
            
            $output .= "🏆 PRESTATIECLASSIFICATIE\n\n";
            $output .= "Uw niveau: " . $class['level'] . "\n\n";
            $output .= $class['description'] . " - Uw prestatie valt in het top " . $class['percentile'] . "% van " . $geslachtCompLabel . " in uw leeftijdsgroep (" . $leeftijdsgroep . " jaar).\n\n";
            
            // Toon normen tabel voor fietstesten
            if (str_contains($testtype, 'fiets') && isset($testData['anaerobe_drempel_watt_per_kg']) && !empty($normen)) {
                $output .= "Populatienormen voor anaërobe drempel (W/kg) - " . ucfirst($geslachtCompLabel) . " " . $leeftijdsgroep . " jaar:\n\n";
                
                $uwWattPerKg = $testData['anaerobe_drempel_watt_per_kg'];
                
                if (isset($normen['lt2_watt_per_kg'])) {
                    $normData = $normen['lt2_watt_per_kg'];
                    $output .= sprintf("%-15s %-10s %-15s\n", "Niveau", "W/kg", "Uw waarde");
                    $output .= str_repeat("─", 40) . "\n";
                    $output .= sprintf("%-15s %-10s %-15s\n", "Elite", ($normData['elite'] ?? 'N/A') . "+", ($uwWattPerKg >= ($normData['elite'] ?? 999) ? "✅ " . $uwWattPerKg : ""));
                    $output .= sprintf("%-15s %-10s %-15s\n", "Zeer Goed", ($normData['good'] ?? 'N/A') . "+", ($uwWattPerKg >= ($normData['good'] ?? 999) && $uwWattPerKg < ($normData['elite'] ?? 999) ? "✅ " . $uwWattPerKg : ""));
                    $output .= sprintf("%-15s %-10s %-15s\n", "Goed", ($normData['average'] ?? 'N/A') . "+", ($uwWattPerKg >= ($normData['average'] ?? 999) && $uwWattPerKg < ($normData['good'] ?? 999) ? "✅ " . $uwWattPerKg : ""));
                    $output .= sprintf("%-15s %-10s %-15s\n", "Redelijk", ($normData['below'] ?? 'N/A') . "+", ($uwWattPerKg >= ($normData['below'] ?? 999) && $uwWattPerKg < ($normData['average'] ?? 999) ? "✅ " . $uwWattPerKg : ""));
                }
                $output .= "\n";
                
                // Interpretatie
                if ($uwWattPerKg >= ($normData['elite'] ?? 999)) {
                    $output .= "Interpretatie: U presteert op ELITE NIVEAU! Dit is uitzonderlijk goed voor uw leeftijdsgroep en geslacht. U heeft het vermogen van een competitieve wielrenner.\n\n";
                } elseif ($uwWattPerKg >= ($normData['good'] ?? 999)) {
                    $output .= "Interpretatie: U presteert ZEER GOED! Dit is een bovengemiddeld niveau. Met gericht trainen kunt u nog verder groeien richting elite niveau.\n\n";
                } elseif ($uwWattPerKg >= ($normData['average'] ?? 999)) {
                    $output .= "Interpretatie: U presteert op GOED NIVEAU. Dit is een gezond, gemiddeld niveau voor recreatieve sporters. Er is veel potentieel voor verbetering.\n\n";
                } else {
                    $output .= "Interpretatie: U heeft nog VEEL GROEIPOTENTIEEL. Met een gestructureerd trainingsprogramma kunt u significant verbeteren.\n\n";
                }
            }
            
            $output .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        }
        
        // TRAININGSADVIES
        $output .= "## 💪 TRAININGSADVIES\n\n";
        
        if ($lt1 && $lt2) {
            $output .= "**Polarized Training (80/20 principe)**\n\n";
            $output .= "Voor optimale resultaten raden we het 80/20 trainingsmodel aan:\n\n";
            
            $output .= "**1. Aërobe Basistraining (80% van trainingstijd)**\n\n";
            $output .= "- Train onder de aërobe drempel (LT1): < " . round($lt1, 1) . " " . $eenheid;
            if ($lt1_hr) {
                $output .= " of < " . round($lt1_hr) . " bpm";
            }
            $output .= "\n";
            $output .= "- Dit voelt als een **comfortabel tempo** waar je nog makkelijk kunt praten\n";
            $output .= "- Duur: 60-120 minuten per sessie\n";
            $output .= "- Frequentie: 4-5x per week\n";
            $output .= "- Effect: Verbetert vetstofwisseling, uithoudingsvermogen en aerobe capaciteit\n\n";
            
            $output .= "**2. Drempel/Interval Training (15% van trainingstijd)**\n\n";
            $output .= "- Train rond de anaërobe drempel (LT2): " . round($lt2, 1) . " " . $eenheid;
            if ($lt2_hr) {
                $output .= " of " . round($lt2_hr) . " bpm";
            }
            $output .= "\n";
            $output .= "- Dit voelt als een **zwaar maar houdbaar tempo**\n";
            $output .= "- Voorbeelden:\n";
            $output .= "  - 4-6x 5 minuten @ LT2 met 2-3 min rust\n";
            $output .= "  - 3x 10 minuten @ LT2 met 5 min rust\n";
            $output .= "  - 2x 20 minuten @ LT2 met 10 min rust\n";
            $output .= "- Frequentie: 1-2x per week\n";
            $output .= "- Effect: Verhoogt de anaërobe drempel en VO2max\n\n";
            
            $output .= "**3. Herstel & Mobiliteit (5% van trainingstijd)**\n\n";
            $output .= "- Zeer lage intensiteit of complete rust\n";
            $output .= "- Yoga, stretching, massage\n";
            $output .= "- Slaap: minimaal 7-8 uur per nacht\n\n";
        } else {
            $output .= "- Bepaal eerst je drempelwaarden door testresultaten in te vullen\n";
            $output .= "- Begin met aerobe basistraining (lage intensiteit, lange duur)\n";
            $output .= "- Bouw geleidelijk volume op voordat je intensiteit toevoegt\n\n";
        }
        
        $output .= "---\n\n";
        
        // DOELSTELLINGEN SPECIFIEK
        if (!empty($testData['specifieke_doelstellingen'])) {
            $doelen = strtolower($testData['specifieke_doelstellingen']);
            
            $output .= "## 🎯 SPECIFIEK ADVIES VOOR UW DOELSTELLINGEN\n\n";
            
            if (str_contains($doelen, 'gran fondo') || str_contains($doelen, 'granfondo')) {
                $output .= "**Gran Fondo voorbereiding**\n\n";
                $output .= "- Focus op lange duurritten (3-5 uur) aan aerobe tempo\n";
                $output .= "- Train op parcours met klimmen indien mogelijk\n";
                $output .= "- Oefen voeding en hydratatie strategie tijdens lange ritten\n";
                $output .= "- Voeg 1x per week klimintervals toe voor kracht in de benen\n";
                $output .= "- Plan een tapering van 2 weken voor het event\n\n";
            }
            
            if (str_contains($doelen, 'snelheid') || str_contains($doelen, 'sneller')) {
                $output .= "**Snelheidsverbetering**\n\n";
                $output .= "- Tempo intervallen: 4-6x 5 min boven LT2\n";
                $output .= "- Sprint intervallen: 8-10x 30 sec all-out met volledige rust\n";
                $output .= "- Race pace training: 20-30 min aan target snelheid\n";
                $output .= "- Techniekwerk: loopeconomie of pedaalefficiency\n\n";
            }
            
            if (str_contains($doelen, 'gewicht') || str_contains($doelen, 'afvallen') || str_contains($doelen, 'verliezen')) {
                $output .= "**Gewichtsverlies**\n\n";
                $output .= "- Caloriedeficit: 300-500 kcal per dag voor gezond gewichtsverlies\n";
                $output .= "- Train veel aan lage intensiteit (optimale vetstofwisseling)\n";
                $output .= "- Frequentie: 5-6x per week trainen\n";
                $output .= "- Voeding: Eiwitrijk (1.6-2g per kg lichaamsgewicht)\n";
                $output .= "- Vermijd hongertrainen - tank bij voor zware sessies\n";
                $output .= "- Doel: 0.5-1 kg per week gewichtsverlies (duurzaam)\n\n";
            }
            
            if (str_contains($doelen, 'marathon') || str_contains($doelen, 'halve marathon')) {
                $output .= "**Marathon voorbereiding**\n\n";
                $output .= "- Bouw long runs op tot 30-35 km\n";
                $output .= "- Marathon pace runs: 2x per maand 15-20km @ race tempo\n";
                $output .= "- Specifieke voedingsstrategie oefenen tijdens training\n";
                $output .= "- Tapering: 3 weken voor de race\n\n";
            }
        }
        
        $output .= "---\n\n";
        
        // PROGRESSIE
        $output .= "## 📈 PROGRESSIE & HERTEST\n\n";
        $output .= "**Verwachte verbeteringen (8-12 weken consistent trainen):**\n\n";
        $output .= "- Aërobe drempel: 5-10% verbetering\n";
        $output .= "- Anaërobe drempel: 3-8% verbetering\n";
        $output .= "- Verbeterde loopeconomie of fietsefficiency\n";
        $output .= "- Lagere hartslag bij zelfde intensiteit (betere efficiency)\n";
        $output .= "- Sneller herstel tussen inspanningen\n\n";
        
        $output .= "**Wanneer hertesten?**\n\n";
        $output .= "- Na 8-12 weken gestructureerd trainen\n";
        $output .= "- Bij plateau in prestaties\n";
        $output .= "- Voor belangrijke wedstrijden of events\n";
        $output .= "- Na een trainingsperiode wijziging\n\n";
        
        $output .= "---\n\n";
        
        // BELANGRIJKE METRICS
        $output .= "## 📊 TE MONITOREN METRICS\n\n";
        $output .= "Volg deze waarden om progressie te zien:\n\n";
        $output .= "- ✅ Hartslag bij vaste trainingsintensiteit (moet dalen)\n";
        $output .= "- ✅ Gemiddelde snelheid/vermogen (moet stijgen)\n";
        $output .= "- ✅ Herstellijden tussen intervallen (moet verkorten)\n";
        $output .= "- ✅ Algemeen energieniveau en slaapkwaliteit\n";
        $output .= "- ✅ Watt/kg ratio (moet stijgen)\n\n";
        
        $output .= "---\n\n";
        $output .= "*💡 Voor een nog uitgebreidere AI-gegenereerde analyse met meer specifieke adviezen, voeg OpenAI credits toe aan uw account.*\n";
        
        return $output;
    }
}