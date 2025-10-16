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
        
        \Log::info('🔍 AI SERVICE - buildPrompt', [
            'is_triathlon_doel_aanwezig' => isset($testData['is_triathlon_doel']),
            'is_triathlon_doel_waarde' => $testData['is_triathlon_doel'] ?? 'NIET GEZET',
            'doelstellingen' => $testData['specifieke_doelstellingen'] ?? 'LEEG'
        ]);
        
        // 🏊 TRIATHLON/IRONMAN EXTRA EMPHASIS
        if (isset($testData['is_triathlon_doel']) && $testData['is_triathlon_doel'] === true) {
            $doelstellingen = $testData['specifieke_doelstellingen'] ?? '';
            
            $triathlonEmphasis = "\n\n🚨 ULTRA BELANGRIJK - TRIATHLON/IRONMAN ATLEET GEDETECTEERD! 🚨\n\n";
            $triathlonEmphasis .= "De atleet heeft EXPLICIET triathlon/ironman doelstellingen:\n";
            $triathlonEmphasis .= "\"" . $doelstellingen . "\"\n\n";
            $triathlonEmphasis .= "VERPLICHTE ACTIE:\n";
            $triathlonEmphasis .= "➡️ Voeg de COMPLETE Sectie I (SPECIFIEK VOOR TRIATHLON/IRONMAN ATLETEN) toe!\n";
            $triathlonEmphasis .= "➡️ Dit moet minimaal 1500 extra woorden bevatten!\n";
            $triathlonEmphasis .= "➡️ Geef ALLE 10 subsecties uitgebreid weer:\n";
            $triathlonEmphasis .= "   1. Multisport Volume Verdeling\n";
            $triathlonEmphasis .= "   2. Brick Workouts (Essentieel!)\n";
            $triathlonEmphasis .= "   3. Triathlon Specifieke Drempels\n";
            $triathlonEmphasis .= "   4. Periodisering Triathlon Seizoen\n";
            $triathlonEmphasis .= "   5. Triathlon Race Day Voeding\n";
            $triathlonEmphasis .= "   6. Triathlon Race Strategie\n";
            $triathlonEmphasis .= "   7. Mental Game & Pacing\n";
            $triathlonEmphasis .= "   8. Recovery Triathlon\n";
            $triathlonEmphasis .= "   9. Equipment Checklist Triathlon\n";
            $triathlonEmphasis .= "   10. Veelgemaakte Triathlon Fouten\n\n";
            $triathlonEmphasis .= "➡️ Pas ALLE trainingsadviezen aan voor multisport training!\n";
            $triathlonEmphasis .= "➡️ Focus op brick workouts, wissels, en specifieke race strategie!\n\n";
            
            // Voeg emphasis toe aan het einde van de user prompt (voor de testdata)
            $userPrompt = $triathlonEmphasis . $userPrompt;
            
            \Log::info('🏊 TRIATHLON EMPHASIS TOEGEVOEGD AAN AI PROMPT', [
                'doelstellingen' => $doelstellingen,
                'emphasis_length' => strlen($triathlonEmphasis)
            ]);
        }
        
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
        // Verrijk de data eerst met populatievergelijking!
        $testData = $this->enrichTestData($testData);
        
        $testtype = $testData['testtype'] ?? 'Onbekend';
        $eenheid = str_contains($testtype, 'fiets') ? 'Watt' : 'km/h';
        
        $output = "🏃‍♂️ INSPANNINGSTEST ANALYSE\n\n";
        $output .= "Geautomatiseerde analyse op basis van uw testresultaten\n\n";
        $output .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
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
            
            // Anaërobe drempel
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
            $sporttype = $testData['population_comparison']['sporttype'] ?? 'cycling';
            
            $output .= "🏆 PRESTATIECLASSIFICATIE\n\n";
            $output .= "Uw niveau: " . $class['level'] . "\n\n";
            $output .= $class['description'] . " - Uw prestatie valt in het top " . $class['percentile'] . " procent van " . $geslachtCompLabel . " in uw leeftijdsgroep (" . $leeftijdsgroep . " jaar).\n\n";
            
            // Toon normen tabel voor FIETSTESTEN
            if ($sporttype === 'cycling' && isset($testData['anaerobe_drempel_watt_per_kg']) && !empty($normen)) {
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
            }
            
            // Toon normen tabel voor LOOPTESTEN
            if ($sporttype === 'running' && isset($lt2) && !empty($normen)) {
                $output .= "Populatienormen voor anaërobe drempel (km/h) - " . ucfirst($geslachtCompLabel) . " " . $leeftijdsgroep . " jaar:\n\n";
                
                $uwSnelheid = $lt2;
                
                if (isset($normen['lt2_speed_kmh'])) {
                    $normData = $normen['lt2_speed_kmh'];
                    $output .= sprintf("%-15s %-10s %-15s\n", "Niveau", "km/h", "Uw waarde");
                    $output .= str_repeat("─", 40) . "\n";
                    $output .= sprintf("%-15s %-10s %-15s\n", "Elite", ($normData['elite'] ?? 'N/A') . "+", ($uwSnelheid >= ($normData['elite'] ?? 999) ? "✅ " . round($uwSnelheid, 1) : ""));
                    $output .= sprintf("%-15s %-10s %-15s\n", "Zeer Goed", ($normData['good'] ?? 'N/A') . "+", ($uwSnelheid >= ($normData['good'] ?? 999) && $uwSnelheid < ($normData['elite'] ?? 999) ? "✅ " . round($uwSnelheid, 1) : ""));
                    $output .= sprintf("%-15s %-10s %-15s\n", "Goed", ($normData['average'] ?? 'N/A') . "+", ($uwSnelheid >= ($normData['average'] ?? 999) && $uwSnelheid < ($normData['good'] ?? 999) ? "✅ " . round($uwSnelheid, 1) : ""));
                    $output .= sprintf("%-15s %-10s %-15s\n", "Redelijk", ($normData['below'] ?? 'N/A') . "+", ($uwSnelheid >= ($normData['below'] ?? 999) && $uwSnelheid < ($normData['average'] ?? 999) ? "✅ " . round($uwSnelheid, 1) : ""));
                    $output .= "\n";
                    
                    // Interpretatie
                    if ($uwSnelheid >= ($normData['elite'] ?? 999)) {
                        $output .= "Interpretatie: U presteert op ELITE NIVEAU! Dit is uitzonderlijk goed voor uw leeftijdsgroep en geslacht. U heeft de snelheid van een competitieve loper.\n\n";
                    } elseif ($uwSnelheid >= ($normData['good'] ?? 999)) {
                        $output .= "Interpretatie: U presteert ZEER GOED! Dit is een bovengemiddeld niveau. Met gericht trainen kunt u nog verder groeien richting elite niveau.\n\n";
                    } elseif ($uwSnelheid >= ($normData['average'] ?? 999)) {
                        $output .= "Interpretatie: U presteert op GOED NIVEAU. Dit is een gezond, gemiddeld niveau voor recreatieve lopers. Er is veel potentieel voor verbetering.\n\n";
                    } else {
                        $output .= "Interpretatie: U heeft nog VEEL GROEIPOTENTIEEL. Met een gestructureerd trainingsprogramma kunt u significant verbeteren.\n\n";
                    }
                }
            }
            
            $output .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        } else {
            // Als er geen classificatie is, toon dan een melding
            \Log::warning('Geen populatievergelijking beschikbaar', [
                'testData_keys' => array_keys($testData),
                'population_comparison' => $testData['population_comparison'] ?? 'not set'
            ]);
        }
        
        // TRAININGSADVIES
        $output .= "💪 TRAININGSADVIES\n\n";
        
        if ($lt1 && $lt2) {
            $output .= "Polarized Training (80/20 principe)\n\n";
            $output .= "Voor optimale resultaten raden we het 80/20 trainingsmodel aan:\n\n";
            
            $output .= "1. Aërobe Basistraining (80 procent van trainingstijd)\n\n";
            $output .= "• Train onder de aërobe drempel (LT1): < " . round($lt1, 1) . " " . $eenheid;
            if ($lt1_hr) {
                $output .= " of < " . round($lt1_hr) . " bpm";
            }
            $output .= "\n";
            $output .= "• Dit voelt als een comfortabel tempo waar je nog makkelijk kunt praten\n";
            $output .= "• Duur: 60-120 minuten per sessie\n";
            $output .= "• Frequentie: 4-5x per week\n";
            $output .= "• Effect: Verbetert vetstofwisseling, uithoudingsvermogen en aerobe capaciteit\n\n";
            
            $output .= "2. Drempel/Interval Training (15 procent van trainingstijd)\n\n";
            $output .= "• Train rond de anaërobe drempel (LT2): " . round($lt2, 1) . " " . $eenheid;
            if ($lt2_hr) {
                $output .= " of " . round($lt2_hr) . " bpm";
            }
            $output .= "\n";
            $output .= "• Dit voelt als een zwaar maar houdbaar tempo\n";
            $output .= "• Voorbeelden:\n";
            $output .= "  ◦ 4-6x 5 minuten bij LT2 met 2-3 min rust\n";
            $output .= "  ◦ 3x 10 minuten bij LT2 met 5 min rust\n";
            $output .= "  ◦ 2x 20 minuten bij LT2 met 10 min rust\n";
            $output .= "• Frequentie: 1-2x per week\n";
            $output .= "• Effect: Verhoogt de anaërobe drempel en VO2max\n\n";
            
            $output .= "3. Herstel en Mobiliteit (5 procent van trainingstijd)\n\n";
            $output .= "• Zeer lage intensiteit of complete rust\n";
            $output .= "• Yoga, stretching, massage\n";
            $output .= "• Slaap: minimaal 7-8 uur per nacht\n\n";
        } else {
            $output .= "• Bepaal eerst je drempelwaarden door testresultaten in te vullen\n";
            $output .= "• Begin met aerobe basistraining (lage intensiteit, lange duur)\n";
            $output .= "• Bouw geleidelijk volume op voordat je intensiteit toevoegt\n\n";
        }
        
        $output .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        
        // DOELSTELLINGEN SPECIFIEK
        if (!empty($testData['specifieke_doelstellingen'])) {
            $doelen = strtolower($testData['specifieke_doelstellingen']);
            
            $output .= "🎯 SPECIFIEK ADVIES VOOR UW DOELSTELLINGEN\n\n";
            
            // 🏊 TRIATHLON / IRONMAN DETECTIE
            $isTriathlon = str_contains($doelen, 'triathlon') || str_contains($doelen, 'triatlon') 
                        || str_contains($doelen, 'ironman') || str_contains($doelen, 'iron man')
                        || str_contains($doelen, '70.3') || str_contains($doelen, 'half ironman')
                        || str_contains($doelen, 'hawaii') || str_contains($doelen, 'kona');
            
            if ($isTriathlon) {
                $output .= "🏊🚴🏃 TRIATHLON / IRONMAN VOORBEREIDING\n\n";
                $output .= "Als triatleet train je drie sporten en moet je slim je trainingstijd verdelen.\n\n";
                
                $output .= "Multisport Volume Verdeling:\n\n";
                
                if (str_contains($doelen, 'ironman') || str_contains($doelen, 'hawaii') || str_contains($doelen, 'full')) {
                    $output .= "• Full Ironman (3.8km zwemmen + 180km fietsen + 42km lopen)\n";
                    $output .= "  - Zwemmen: 10-15% van trainingstijd (~2-3 sessies/week)\n";
                    $output .= "  - Fietsen: 55-60% van trainingstijd (~4-5 ritten/week)\n";
                    $output .= "  - Lopen: 25-30% van trainingstijd (~3-4 runs/week)\n";
                    $output .= "  - Totaal volume: 15-20 uur per week in peak periode\n\n";
                } elseif (str_contains($doelen, '70.3') || str_contains($doelen, 'half ironman')) {
                    $output .= "• Half Ironman 70.3 (1.9km zwemmen + 90km fietsen + 21km lopen)\n";
                    $output .= "  - Zwemmen: 15-20% van trainingstijd (~2-3 sessies/week)\n";
                    $output .= "  - Fietsen: 50-55% van trainingstijd (~3-4 ritten/week)\n";
                    $output .= "  - Lopen: 25-30% van trainingstijd (~3-4 runs/week)\n";
                    $output .= "  - Totaal volume: 10-15 uur per week in peak periode\n\n";
                } else {
                    $output .= "• Sprint/Olympische Triathlon\n";
                    $output .= "  - Zwemmen: 20-25% van trainingstijd (~2-3 sessies/week)\n";
                    $output .= "  - Fietsen: 45-50% van trainingstijd (~3-4 ritten/week)\n";
                    $output .= "  - Lopen: 30-35% van trainingstijd (~3-4 runs/week)\n";
                    $output .= "  - Totaal volume: 8-12 uur per week\n\n";
                }
                
                $output .= "Brick Workouts (ESSENTIEEL voor triathlon!):\n\n";
                $output .= "• Wat: Fietsen direct gevolgd door lopen\n";
                $output .= "• Waarom: Train de overgang en 'loopbenen' na fietsen\n";
                $output .= "• Frequentie: Minimaal 1x per week tijdens build fase\n";
                $output .= "• Voorbeelden:\n";
                $output .= "  - 60min fietsen @ LT1 + 20min lopen @ tempo\n";
                $output .= "  - 90min fietsen met 3x 10min @ LT2 + 30min lopen progressief\n";
                $output .= "  - 3u fietsen @ race pace + 45min lopen @ race pace\n\n";
                
                $output .= "Triathlon Race Strategie:\n\n";
                $output .= "• Zwemmen: Start conservatief, zoek draft, laatste 200m opvoeren\n";
                $output .= "• T1 (wissel 1): Rustig, hartslag laten zakken\n";
                $output .= "• Fietsen: Eerste 15-20min rustig, dan steady @ 85-90% FTP\n";
                $output .= "  BELANGRIJK: Blijf ETEN en DRINKEN! (60-90g KH per uur)\n";
                $output .= "• T2 (wissel 2): Quick change, focus vinden\n";
                $output .= "• Lopen: EERSTE 2-3 KM RUSTIG! Negatieve split strategie\n\n";
                
                $output .= "Triathlon Voeding (cruciaal!):\n\n";
                $output .= "• Pre-race (3-4u voor start): 2-3g KH/kg lichaamsgewicht\n";
                $output .= "• Tijdens fietsen: 60-90g KH per uur (mix: drank + gels + bars)\n";
                $output .= "• Tijdens lopen: Voortzetten, gels makkelijker verteerbaar\n";
                $output .= "• Vocht: 400-800ml/uur (afhankelijk van temperatuur)\n";
                $output .= "• Natrium: 500-1000mg/uur bij warm weer\n";
                $output .= "• TRAIN DIT IN TRAINING! Nooit nieuwe voeding op race day\n\n";
                
                $output .= "Triathlon Periodisering:\n\n";
                $output .= "• Base (8-12 weken): Volume opbouw, techniek alle disciplines\n";
                $output .= "• Build (8-12 weken): Race specifieke intensiteit, brick workouts\n";
                $output .= "• Peak (4-6 weken): Race simulation, long bike + brick run\n";
                $output .= "• Taper (2-3 weken): Volume -25% / -40% / -60%\n\n";
                
                $output .= "Veelgemaakte Triathlon Fouten:\n\n";
                $output .= "❌ Te hard zwemmen → geeft niets, kost wel energie\n";
                $output .= "❌ Te snel starten op de fiets → loopbenen kwijt\n";
                $output .= "❌ Onvoldoende eten/drinken → bonk op de run\n";
                $output .= "❌ Te weinig brick training → overgang shock\n";
                $output .= "✅ DOE: Train je race, race je training!\n\n";
            }
            
            // 🚴 WIELRENNEN SPECIFIEK
            if (str_contains($doelen, 'gran fondo') || str_contains($doelen, 'granfondo')) {
                $output .= "🚴 GRAN FONDO VOORBEREIDING\n\n";
                $output .= "• Focus op lange duurritten (3-5 uur) aan aerobe tempo\n";
                $output .= "• Train op parcours met klimmen indien mogelijk\n";
                $output .= "• Oefen voeding en hydratatie strategie tijdens lange ritten\n";
                $output .= "• Voeg 1x per week klimintervals toe voor kracht in de benen\n";
                $output .= "• Specifieke klimtraining: 2-3x 10-15min @ LT2 op helling\n";
                $output .= "• Groepsritten: oefen in het wiel rijden en wisselen\n";
                $output .= "• Plan een tapering van 2 weken voor het event\n";
                $output .= "• Race day: Start conservatief, eet en drink elk uur!\n\n";
            }
            
            if (str_contains($doelen, 'ronde') || str_contains($doelen, 'vlaanderen') || str_contains($doelen, 'klassieker')) {
                $output .= "🚴 KLASSIEKERS / RONDE VAN VLAANDEREN VOORBEREIDING\n\n";
                $output .= "• Focus op explosive power en herhaalde inspanningen\n";
                $output .= "• Specifieke training:\n";
                $output .= "  - Korte klimmen: 10-15x 1-2min all-out met 2-3min rust\n";
                $output .= "  - Kasseien simulatie: 6-8x 3-5min hoog vermogen op gravel/slechte weg\n";
                $output .= "  - Attack/Counter-attack: 8-10x 30sec sprint + 2min tempo\n";
                $output .= "• Krachtraining: Focus op core en bovenlichaam (kasseien!)\n";
                $output .= "• Techniek: Oefenen uit het zadel klimmen en positie op kasseien\n";
                $output .= "• Materiaal: Bredere banden (28-30mm), check fiets setup\n";
                $output .= "• Voeding: Extra carbs dag ervoor, elk uur 60-90g tijdens race\n\n";
            }
            
            // 🏃 LOPEN SPECIFIEK
            if (str_contains($doelen, 'marathon') && !str_contains($doelen, 'half')) {
                $output .= "🏃 MARATHON VOORBEREIDING (42.2 KM)\n\n";
                $output .= "• Trainingsplan: 16-20 weken voorbereiding\n";
                $output .= "• Long runs: Bouw op tot 30-35 km (3-4 weken voor race)\n";
                $output .= "• Marathon pace runs: 2x per maand 15-20km bij race tempo\n";
                $output .= "• Tempo runs: 1x per week 8-12km @ LT2\n";
                $output .= "• Easy runs: 3-4x per week recovery pace\n";
                $output .= "• Peak volume: 60-80 km per week\n";
                $output .= "• Voeding tijdens race:\n";
                $output .= "  - Start na 45-60 minuten\n";
                $output .= "  - 30-60g KH per uur (gels elk 30-45min)\n";
                $output .= "  - Water bij elk aid station\n";
                $output .= "• Race strategie: Negatieve split! Eerste helft conservatief\n";
                $output .= "• Tapering: 3 weken voor de race (volume -30%/-50%/-70%)\n\n";
            }
            
            if (str_contains($doelen, 'halve marathon') || str_contains($doelen, 'half marathon')) {
                $output .= "🏃 HALVE MARATHON VOORBEREIDING (21.1 KM)\n\n";
                $output .= "• Trainingsplan: 10-12 weken voorbereiding\n";
                $output .= "• Long runs: Bouw op tot 18-20 km\n";
                $output .= "• Race pace runs: 2x per maand 10-12km bij target tempo\n";
                $output .= "• Tempo runs: 1x per week 6-8km @ LT2\n";
                $output .= "• Interval training: 1x per week (bijv. 6x 1km @ 10k pace)\n";
                $output .= "• Peak volume: 40-60 km per week\n";
                $output .= "• Voeding: Meestal niet nodig, maar optioneel 1 gel na 10km\n";
                $output .= "• Race strategie: Even pace, laatste 5km versnellen\n";
                $output .= "• Tapering: 2 weken (volume -30%/-60%)\n\n";
            }
            
            if (str_contains($doelen, '10km') || str_contains($doelen, '10 km')) {
                $output .= "🏃 10KM WEDSTRIJD VOORBEREIDING\n\n";
                $output .= "• Trainingsplan: 8-10 weken voorbereiding\n";
                $output .= "• Long runs: Tot 12-15 km\n";
                $output .= "• Tempo runs: 1x per week 5-7km @ race pace\n";
                $output .= "• Interval training:\n";
                $output .= "  - 8-10x 400m @ 5k pace (rest = interval tijd)\n";
                $output .= "  - 5-6x 1km @ 10k pace (2min rest)\n";
                $output .= "  - 3-4x 2km @ HM pace (3min rest)\n";
                $output .= "• Peak volume: 35-50 km per week\n";
                $output .= "• Voeding: Niet nodig tijdens race, wel pre-race meal\n";
                $output .= "• Race strategie: Aggressive start mogelijk, last 2km all-out\n";
                $output .= "• Tapering: 1 week (volume -40%, laatste 3 dagen minimaal)\n\n";
            }
            
            if (str_contains($doelen, '5km') || str_contains($doelen, '5 km')) {
                $output .= "🏃 5KM WEDSTRIJD VOORBEREIDING\n\n";
                $output .= "• Focus: Snelheid en VO2max ontwikkeling\n";
                $output .= "• Interval training (2-3x per week):\n";
                $output .= "  - 12-16x 400m @ 5k pace\n";
                $output .= "  - 6-8x 800m @ 5k pace\n";
                $output .= "  - 4-5x 1km @ 5k pace\n";
                $output .= "• Tempo runs: 1x per week 3-4km @ LT2\n";
                $output .= "• Long run: 1x per week 8-10km easy\n";
                $output .= "• Race strategie: Fast start, hold on!\n";
                $output .= "• Geen voeding nodig, hydrateer goed voor de race\n\n";
            }
            
            // 🎯 ALGEMENE DOELEN
            if (str_contains($doelen, 'snelheid') || str_contains($doelen, 'sneller')) {
                $output .= "⚡ SNELHEIDSVERBETERING\n\n";
                $output .= "• Tempo intervallen: 4-6x 5 min boven LT2\n";
                $output .= "• Sprint intervallen: 8-10x 30 sec all-out met volledige rust\n";
                $output .= "• Race pace training: 20-30 min aan target snelheid\n";
                $output .= "• Techniekwerk: loopeconomie of pedaalefficiency\n";
                $output .= "• Krachtraining: 2x per week explosieve oefeningen\n\n";
            }
            
            if (str_contains($doelen, 'gewicht') || str_contains($doelen, 'afvallen') || str_contains($doelen, 'verliezen')) {
                $output .= "⚖️ GEWICHTSVERLIES\n\n";
                $output .= "• Caloriedeficit: 300-500 kcal per dag voor gezond gewichtsverlies\n";
                $output .= "• Train veel aan lage intensiteit (optimale vetstofwisseling)\n";
                $output .= "• Frequentie: 5-6x per week trainen\n";
                $output .= "• Voeding: Eiwitrijk (1.6-2g per kg lichaamsgewicht)\n";
                $output .= "• Vermijd hongertrainen - tank bij voor zware sessies\n";
                $output .= "• Doel: 0.5-1 kg per week gewichtsverlies (duurzaam)\n";
                $output .= "• Combineer cardio met krachtraining voor spierbehoud\n\n";
            }
            
            $output .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        }
        
        // PROGRESSIE
        $output .= "📈 PROGRESSIE EN HERTEST\n\n";
        $output .= "Verwachte verbeteringen (8-12 weken consistent trainen):\n\n";
        $output .= "• Aërobe drempel: 5-10 procent verbetering\n";
        $output .= "• Anaërobe drempel: 3-8 procent verbetering\n";
        $output .= "• Verbeterde loopeconomie of fietsefficiency\n";
        $output .= "• Lagere hartslag bij zelfde intensiteit (betere efficiency)\n";
        $output .= "• Sneller herstel tussen inspanningen\n\n";
        
        $output .= "Wanneer hertesten?\n\n";
        $output .= "• Na 8-12 weken gestructureerd trainen\n";
        $output .= "• Bij plateau in prestaties\n";
        $output .= "• Voor belangrijke wedstrijden of events\n";
        $output .= "• Na een trainingsperiode wijziging\n\n";
        
        $output .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        
        // BELANGRIJKE METRICS
        $output .= "📊 TE MONITOREN METRICS\n\n";
        $output .= "Volg deze waarden om progressie te zien:\n\n";
        $output .= "✅ Hartslag bij vaste trainingsintensiteit (moet dalen)\n";
        $output .= "✅ Gemiddelde snelheid/vermogen (moet stijgen)\n";
        $output .= "✅ Herstellijden tussen intervallen (moet verkorten)\n";
        $output .= "✅ Algemeen energieniveau en slaapkwaliteit\n";
        $output .= "✅ Watt/kg ratio (moet stijgen)\n\n";
        
        $output .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        $output .= "💡 Voor een nog uitgebreidere AI-gegenereerde analyse met meer specifieke adviezen, voeg OpenAI credits toe aan uw account.\n";
        
        return $output;
    }
}