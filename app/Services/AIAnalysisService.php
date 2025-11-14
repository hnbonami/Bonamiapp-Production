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
        
        $output = "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $output .= "                 🏃‍♂️ COMPLETE INSPANNINGSTEST ANALYSE 🏃‍♂️\n";
        $output .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        $output .= "Geautomatiseerde wetenschappelijke analyse van uw testresultaten\n";
        $output .= "Gegenereerd op: " . now()->format('d-m-Y H:i') . "\n\n";
        $output .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        
        $output .= "═══════════════════════════════════════════════════════════════\n";
        $output .= "                         📊 SECTIE I: TESTOVERZICHT\n";
        $output .= "═══════════════════════════════════════════════════════════════\n\n";
        
        $output .= "🔬 TESTGEGEVENS\n\n";
        $output .= "• Testtype: " . ucfirst(str_replace('_', ' ', $testtype)) . "\n";
        $output .= "• Testdatum: " . ($testData['testdatum'] ?? date('Y-m-d')) . "\n";
        if (!empty($testData['testlocatie'])) {
            $output .= "• Testlocatie: " . $testData['testlocatie'] . "\n";
        }
        if (!empty($testData['analyse_methode'])) {
            $analyseMethode = str_replace('_', ' ', $testData['analyse_methode']);
            $output .= "• Analyse methode: " . ucfirst($analyseMethode) . "\n";
            
            // Uitleg analyse methode
            $output .= "\n📖 Wat betekent deze analyse methode?\n";
            if (str_contains($testData['analyse_methode'], 'dmax')) {
                $output .= "  De D-max methode bepaalt drempels door het punt te vinden met de\n";
                $output .= "  grootste afstand tussen de lactaatcurve en een hulplijn. Dit is een\n";
                $output .= "  wetenschappelijk gevalideerde methode voor nauwkeurige drempelbepaling.\n";
            } elseif (str_contains($testData['analyse_methode'], 'lactaat_steady_state')) {
                $output .= "  Deze methode gebruikt vaste lactaatwaarden (2 mmol/L voor LT1,\n";
                $output .= "  4 mmol/L voor LT2) om drempels te bepalen. Dit is een praktische\n";
                $output .= "  benadering die breed gebruikt wordt in de sportfysiologie.\n";
            }
        }
        $output .= "\n";
        
        // ATLET PROFIEL
        $output .= "👤 ATLEET PROFIEL\n\n";
        $leeftijd = $testData['leeftijd'] ?? null;
        $gewicht = $testData['lichaamsgewicht_kg'] ?? null;
        $lengte = $testData['lichaamslengte_cm'] ?? null;
        $geslacht = $testData['geslacht'] ?? 'male';
        $geslachtLabel = $geslacht === 'female' ? 'Vrouw' : 'Man';
        
        if ($leeftijd) $output .= "• Leeftijd: " . $leeftijd . " jaar\n";
        if ($geslacht) $output .= "• Geslacht: " . $geslachtLabel . "\n";
        if ($gewicht) $output .= "• Gewicht: " . $gewicht . " kg\n";
        if ($lengte) {
            $output .= "• Lengte: " . $lengte . " cm\n";
        }
        
        if (!empty($testData['specifieke_doelstellingen'])) {
            $output .= "\n🎯 Uw doelstellingen:\n";
            $output .= $testData['specifieke_doelstellingen'] . "\n";
        }
        $output .= "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        
        // ═══════════════════════════════════════════════════════════════
        // SECTIE II: LICHAAMSSAMENSTELLING ANALYSE (VOLLEDIG NIEUW!)
        // ═══════════════════════════════════════════════════════════════
        
        $output .= "═══════════════════════════════════════════════════════════════\n";
        $output .= "               🧬 SECTIE II: LICHAAMSSAMENSTELLING ANALYSE\n";
        $output .= "═══════════════════════════════════════════════════════════════\n\n";
        
        $bmi = $testData['bmi'] ?? null;
        $vetpercentage = $testData['vetpercentage'] ?? null;
        $buikomtrek = $testData['buikomtrek_cm'] ?? null;
        
        // BMI ANALYSE
        if ($bmi) {
            $output .= "📊 BODY MASS INDEX (BMI)\n\n";
            $output .= "Uw BMI: " . number_format($bmi, 1) . "\n\n";
            
            // BMI Classificatie
            if ($bmi < 18.5) {
                $output .= "Classificatie: Ondergewicht\n";
                $output .= "⚠️ ATTENTIEPUNT: Uw BMI is aan de lage kant. Voor sporters kan dit\n";
                $output .= "   acceptabel zijn bij lage vetpercentages, maar monitor uw energieniveaus\n";
                $output .= "   en herstellcapaciteit nauwkeurig.\n\n";
                $output .= "Risico's bij ondergewicht:\n";
                $output .= "• Verhoogd blessurerisico (stress fracturen, overbelasting)\n";
                $output .= "• Verminderde immuunfunctie\n";
                $output .= "• Mogelijk tekort aan essentiële voedingsstoffen\n";
                $output .= "• Bij vrouwen: mogelijk hormonale verstoringen\n\n";
                $output .= "Advies: Consulteer een sportdiëtist voor een voedingsplan dat\n";
                $output .= "        voldoende energie levert voor training en herstel.\n\n";
            } elseif ($bmi >= 18.5 && $bmi < 25) {
                $output .= "Classificatie: Normaal gewicht ✅\n\n";
                $output .= "Uw BMI valt binnen het gezonde bereik. Voor sporters is de\n";
                $output .= "verdeling tussen spiermassa en vetmassa echter belangrijker dan\n";
                $output .= "alleen BMI. Zie vetpercentage analyse hieronder.\n\n";
                
                // Specifieke BMI adviezen voor sporters
                if ($bmi >= 18.5 && $bmi < 20) {
                    $output .= "Voor uithoudingssporten: Uw BMI is aan de lagere kant van normaal,\n";
                    $output .= "wat voordelig kan zijn voor klimmen en lopen (lagere carrying cost).\n";
                    $output .= "Let wel op voldoende energieinname en spierbehoud.\n\n";
                } elseif ($bmi >= 20 && $bmi < 23) {
                    $output .= "Voor uithoudingssporten: Uw BMI is optimaal voor de meeste\n";
                    $output .= "uithoudingsdisciplines. Goede balans tussen kracht en gewicht.\n\n";
                } else {
                    $output .= "Voor uithoudingssporten: Uw BMI is aan de hogere kant van normaal.\n";
                    $output .= "Als dit vooral spiermassa is (laag vetpercentage), is dit prima.\n";
                    $output .= "Bij hoger vetpercentage kan gewichtsverlies prestaties verbeteren.\n\n";
                }
            } elseif ($bmi >= 25 && $bmi < 30) {
                $output .= "Classificatie: Overgewicht\n";
                $output .= "💡 VERBETERPOTENTIEEL: Voor uithoudingssporten kan gewichtsverlies\n";
                $output .= "   significante prestatieverbetering opleveren, vooral bij klimmen en lopen.\n\n";
                $output .= "Impact op prestatie:\n";
                $output .= "• Verhoogde carrying cost (meer energie per km)\n";
                $output .= "• Lagere Watt/kg ratio (belangrijk voor klimmen)\n";
                $output .= "• Meer belasting op gewrichten bij lopen\n";
                $output .= "• Mogelijk slechtere warmteafvoer bij intensieve inspanning\n\n";
                $output .= "Realistisch doel: 0.5-1 kg gewichtsverlies per week\n";
                $output .= "Methode: Caloriedeficit van 300-500 kcal/dag + behoud trainingsvolume\n";
                $output .= "Belangrijkste focus: Eiwitinname behouden (1.6-2g/kg lichaamsgewicht)\n";
                $output .= "                    om spiermassa te behouden tijdens gewichtsverlies.\n\n";
            } else {
                $output .= "Classificatie: Obesitas\n";
                $output .= "⚠️ GEZONDHEIDSRISICO: Voor zowel gezondheid als sportprestaties is\n";
                $output .= "   gewichtsverlies sterk aan te raden.\n\n";
                $output .= "Advies: Werk samen met een sportarts en/of diëtist voor een veilig\n";
                $output .= "        en effectief gewichtsverliesplan. Combineer voedingsaanpassing\n";
                $output .= "        met geleidelijke opbouw van trainingsvolume.\n\n";
            }
            
            $output .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        }
        
        // VETPERCENTAGE ANALYSE
        if ($vetpercentage) {
            $output .= "🔥 VETPERCENTAGE ANALYSE\n\n";
            $output .= "Uw vetpercentage: " . number_format($vetpercentage, 1) . "%\n\n";
            
            // Vetpercentage normen op basis van geslacht
            if ($geslacht === 'female') {
                // Vrouwen
                $output .= "Classificatie voor vrouwen:\n\n";
                if ($vetpercentage < 14) {
                    $output .= "Categorie: Essentieel vet / Atletisch (zeer laag)\n";
                    $output .= "⚠️ ATTENTIE: Dit is zeer laag voor vrouwen. Let op hormoonbalans!\n\n";
                    $output .= "Risico's bij te laag vetpercentage (vrouwen):\n";
                    $output .= "• Amenorroe (uitblijven menstruatie)\n";
                    $output .= "• Verminderde botdichtheid (osteoporose risico)\n";
                    $output .= "• Hormoonverstoringen (oestrogeen tekort)\n";
                    $output .= "• Verzwakte immuunfunctie\n\n";
                    $output .= "Advies: Monitor menstruatiecyclus en overweeg bloedonderzoek.\n\n";
                } elseif ($vetpercentage >= 14 && $vetpercentage < 20) {
                    $output .= "Categorie: Atletisch / Fit ✅\n\n";
                    $output .= "Uitstekend vetpercentage voor vrouwelijke uithoudingssporters!\n";
                    $output .= "Dit niveau ondersteunt goede prestaties zonder gezondheidsrisico's.\n\n";
                    $output .= "Voor uw sport:\n";
                    $output .= "• Optimaal voor klimmen en lopen (lage carrying cost)\n";
                    $output .= "• Goede balans tussen prestatie en gezondheid\n";
                    $output .= "• Ondersteunt effectieve warmteregulatie\n\n";
                } elseif ($vetpercentage >= 20 && $vetpercentage < 25) {
                    $output .= "Categorie: Fitness / Gemiddeld\n\n";
                    $output .= "Dit is een gezond vetpercentage. Voor competitieve uithoudingssporten\n";
                    $output .= "kan enige verlaging prestatieverbetering opleveren.\n\n";
                    $output .= "Verbeterpotentieel:\n";
                    $output .= "• 1-2% vetpercentage verlagen kan Watt/kg met 2-5% verhogen\n";
                    $output .= "• Betere warmteafvoer bij intensieve inspanning\n";
                    $output .= "• Lagere carrying cost bij klimmen en lopen\n\n";
                } elseif ($vetpercentage >= 25 && $vetpercentage < 32) {
                    $output .= "Categorie: Acceptabel / Matig\n\n";
                    $output .= "💡 VERBETERKANS: Voor sportieve doelen is vetpercentage verlaging\n";
                    $output .= "   aan te raden. Dit zal prestaties significant verbeteren.\n\n";
                    $output .= "Doel vetpercentage: 18-22% (realistisch binnen 3-6 maanden)\n";
                    $output .= "Verwachte prestatieverbetering: 5-10% in Watt/kg\n\n";
                } else {
                    $output .= "Categorie: Hoog\n\n";
                    $output .= "⚠️ Voor zowel gezondheid als prestaties is vetpercentage verlaging\n";
                    $output .= "   sterk aan te raden.\n\n";
                    $output .= "Prioriteit: Gezondheid eerst, daarna prestatie.\n";
                    $output .= "Advies: Werk samen met een sportdiëtist en trainer.\n\n";
                }
            } else {
                // Mannen
                $output .= "Classificatie voor mannen:\n\n";
                if ($vetpercentage < 6) {
                    $output .= "Categorie: Essentieel vet (te laag)\n";
                    $output .= "⚠️ ATTENTIE: Dit is extreem laag! Gezondheidsrisico's aanwezig.\n\n";
                    $output .= "Risico's:\n";
                    $output .= "• Hormoonverstoringen (testosteron daling)\n";
                    $output .= "• Verzwakte immuunfunctie\n";
                    $output .= "• Slechtere herstelcapaciteit\n";
                    $output .= "• Mogelijk cardiovasculaire problemen\n\n";
                } elseif ($vetpercentage >= 6 && $vetpercentage < 14) {
                    $output .= "Categorie: Atletisch / Elite ✅\n\n";
                    $output .= "Perfect vetpercentage voor mannelijke uithoudingssporters!\n";
                    $output .= "Dit is het niveau van professionele wielrenners en marathonlopers.\n\n";
                    $output .= "Prestatiewoordelen:\n";
                    $output .= "• Maximale Watt/kg ratio\n";
                    $output .= "• Optimale warmteafvoer\n";
                    $output .= "• Minimale carrying cost\n";
                    $output .= "• Efficiënte zuurstofopname per kg lichaamsgewicht\n\n";
                } elseif ($vetpercentage >= 14 && $vetpercentage < 18) {
                    $output .= "Categorie: Fit / Sportief ✅\n\n";
                    $output .= "Gezond en goed vetpercentage voor recreatieve tot competitieve sporters.\n\n";
                    $output .= "Voor topprestaties: Enige verlaging naar 10-13% kan helpen,\n";
                    $output .= "maar is niet strikt noodzakelijk voor goede resultaten.\n\n";
                } elseif ($vetpercentage >= 18 && $vetpercentage < 25) {
                    $output .= "Categorie: Gemiddeld / Acceptabel\n\n";
                    $output .= "💡 VERBETERPOTENTIEEL: Voor sportieve doelen is vetpercentage\n";
                    $output .= "   verlaging aan te raden.\n\n";
                    $output .= "Doel: 12-15% (realistisch binnen 3-6 maanden)\n";
                    $output .= "Verwachte verbetering: 8-15% in Watt/kg ratio\n\n";
                } else {
                    $output .= "Categorie: Matig tot Hoog\n\n";
                    $output .= "⚠️ Voor sportprestaties is vetpercentage verlaging sterk aan te raden.\n\n";
                    $output .= "Prioriteit: Geleidelijk en gezond gewichtsverlies.\n";
                    $output .= "Advies: Professionele begeleiding door diëtist en trainer.\n\n";
                }
            }
            
            // Vetpercentage doelstellingen voor verschillende sporten
            $output .= "🎯 Optimale vetpercentages per sportdiscipline:\n\n";
            if ($geslacht === 'male') {
                $output .= "Mannen:\n";
                $output .= "• Wielrennen (elite): 5-10%\n";
                $output .= "• Marathon lopen (elite): 5-11%\n";
                $output .= "• Triathlon (elite): 6-12%\n";
                $output .= "• Recreatief uithoudingssport: 12-18%\n";
            } else {
                $output .= "Vrouwen:\n";
                $output .= "• Wielrennen (elite): 12-16%\n";
                $output .= "• Marathon lopen (elite): 10-15%\n";
                $output .= "• Triathlon (elite): 12-18%\n";
                $output .= "• Recreatief uithoudingssport: 18-24%\n";
            }
            
            $output .= "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        }
        
        // BUIKOMTREK ANALYSE (Cardiovasculair risico)
        if ($buikomtrek) {
            $output .= "📏 BUIKOMTREK ANALYSE (Cardiovasculaire Gezondheid)\n\n";
            $output .= "Uw buikomtrek: " . $buikomtrek . " cm\n\n";
            
            // WHO normen voor cardiovasculair risico
            $risicoGrens = ($geslacht === 'male') ? 94 : 80;
            $hoogRisicoGrens = ($geslacht === 'male') ? 102 : 88;
            
            if ($buikomtrek < $risicoGrens) {
                $output .= "Risico: Laag ✅\n\n";
                $output .= "Uw buikomtrek valt binnen het gezonde bereik. Visceraal vet\n";
                $output .= "(vet rond organen) is waarschijnlijk minimaal, wat goed is voor\n";
                $output .= "cardiovasculaire gezondheid en sportprestaties.\n\n";
            } elseif ($buikomtrek >= $risicoGrens && $buikomtrek < $hoogRisicoGrens) {
                $output .= "Risico: Verhoogd ⚠️\n\n";
                $output .= "Uw buikomtrek overschrijdt de gezondheidsgrens. Dit wijst op mogelijk\n";
                $output .= "verhoogd visceraal vet, wat samenhangt met:\n";
                $output .= "• Verhoogd risico op hart- en vaatziekten\n";
                $output .= "• Type 2 diabetes risico\n";
                $output .= "• Metabool syndroom\n\n";
                $output .= "Advies: Focus op gewichtsverlies, vooral rond de buik.\n";
                $output .= "        Combineer cardio met krachtraining en gezonde voeding.\n\n";
            } else {
                $output .= "Risico: Sterk Verhoogd ⛔\n\n";
                $output .= "Uw buikomtrek is aanzienlijk te hoog. Dit wijst op significant\n";
                $output .= "visceraal vet met ernstige gezondheidsrisico's.\n\n";
                $output .= "DRINGEND ADVIES: Consulteer een arts en/of sportarts.\n";
                $output .= "Prioriteer gewichtsverlies en leefstijlverandering.\n\n";
            }
            
            $output .= "WHO Normen:\n";
            if ($geslacht === 'male') {
                $output .= "• Mannen < 94 cm: Laag risico\n";
                $output .= "• Mannen 94-102 cm: Verhoogd risico\n";
                $output .= "• Mannen > 102 cm: Sterk verhoogd risico\n";
            } else {
                $output .= "• Vrouwen < 80 cm: Laag risico\n";
                $output .= "• Vrouwen 80-88 cm: Verhoogd risico\n";
                $output .= "• Vrouwen > 88 cm: Sterk verhoogd risico\n";
            }
            
            $output .= "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        }
        
        // LICHAAMSSAMENSTELLING IMPACT OP PRESTATIE
        if ($gewicht && ($vetpercentage || $bmi)) {
            $output .= "⚡ LICHAAMSSAMENSTELLING & PRESTATIE-IMPACT\n\n";
            
            // Bereken magere massa (spiermassa + botmassa + organen)
            if ($vetpercentage) {
                $vetMassa = $gewicht * ($vetpercentage / 100);
                $magereMassa = $gewicht - $vetMassa;
                
                $output .= "Berekende waarden:\n";
                $output .= "• Vetmassa: " . number_format($vetMassa, 1) . " kg (" . number_format($vetpercentage, 1) . "%)\n";
                $output .= "• Magere massa: " . number_format($magereMassa, 1) . " kg (" . number_format(100 - $vetpercentage, 1) . "%)\n\n";
                
                // Scenario analyse: wat als vetpercentage lager was?
                if ($vetpercentage > 12 && $geslacht === 'male') {
                    $doelVetperc = 12;
                    $potentieelGewicht = $magereMassa / (1 - ($doelVetperc / 100));
                    $mogelijkVerlies = $gewicht - $potentieelGewicht;
                    
                    $output .= "💡 PRESTATIE SCENARIO:\n\n";
                    $output .= "Als u uw vetpercentage verlaagt naar " . $doelVetperc . "% (atletisch niveau)\n";
                    $output .= "terwijl u spiermassa behoudt:\n\n";
                    $output .= "• Nieuw gewicht: " . number_format($potentieelGewicht, 1) . " kg\n";
                    $output .= "• Gewichtsverlies: " . number_format($mogelijkVerlies, 1) . " kg (alleen vet)\n";
                    
                    // Bereken impact op Watt/kg als drempelwaarden bekend zijn
                    if (isset($testData['anaerobe_drempel_vermogen'])) {
                        $huidigeWattPerKg = $testData['anaerobe_drempel_vermogen'] / $gewicht;
                        $nieuweWattPerKg = $testData['anaerobe_drempel_vermogen'] / $potentieelGewicht;
                        $verbetering = (($nieuweWattPerKg / $huidigeWattPerKg) - 1) * 100;
                        
                        $output .= "• Huidige Watt/kg: " . number_format($huidigeWattPerKg, 2) . " W/kg\n";
                        $output .= "• Potentiële Watt/kg: " . number_format($nieuweWattPerKg, 2) . " W/kg\n";
                        $output .= "• Prestatieverbetering: +" . number_format($verbetering, 1) . "%\n\n";
                        
                        $output .= "Praktisch betekent dit:\n";
                        $output .= "• Sneller klimmen (minder gewicht om omhoog te tillen)\n";
                        $output .= "• Betere acceleratie (lagere inertie)\n";
                        $output .= "• Lagere energiekost per kilometer\n";
                        $output .= "• Betere warmteafvoer bij intensieve inspanning\n\n";
                    }
                } elseif ($vetpercentage > 18 && $geslacht === 'female') {
                    $doelVetperc = 18;
                    $potentieelGewicht = $magereMassa / (1 - ($doelVetperc / 100));
                    $mogelijkVerlies = $gewicht - $potentieelGewicht;
                    
                    $output .= "💡 PRESTATIE SCENARIO:\n\n";
                    $output .= "Als u uw vetpercentage verlaagt naar " . $doelVetperc . "% (atletisch niveau)\n";
                    $output .= "terwijl u spiermassa behoudt:\n\n";
                    $output .= "• Nieuw gewicht: " . number_format($potentieelGewicht, 1) . " kg\n";
                    $output .= "• Gewichtsverlies: " . number_format($mogelijkVerlies, 1) . " kg (alleen vet)\n";
                    
                    if (isset($testData['anaerobe_drempel_vermogen'])) {
                        $huidigeWattPerKg = $testData['anaerobe_drempel_vermogen'] / $gewicht;
                        $nieuweWattPerKg = $testData['anaerobe_drempel_vermogen'] / $potentieelGewicht;
                        $verbetering = (($nieuweWattPerKg / $huidigeWattPerKg) - 1) * 100;
                        
                        $output .= "• Huidige Watt/kg: " . number_format($huidigeWattPerKg, 2) . " W/kg\n";
                        $output .= "• Potentiële Watt/kg: " . number_format($nieuweWattPerKg, 2) . " W/kg\n";
                        $output .= "• Prestatieverbetering: +" . number_format($verbetering, 1) . "%\n\n";
                    }
                }
            }
            
            $output .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        }
        
        $output .= "═══════════════════════════════════════════════════════════════\n";
        $output .= "                🎯 SECTIE III: DREMPELWAARDEN & PRESTATIE\n";
        $output .= "═══════════════════════════════════════════════════════════════\n\n";
        
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
        
        // GEMETEN DREMPELWAARDEN MET UITGEBREIDE INTERPRETATIE
        $output .= "🎯 GEMETEN DREMPELWAARDEN\n\n";
        
        $lt1 = $testData['aerobe_drempel_vermogen'] ?? null;
        $lt1_hr = $testData['aerobe_drempel_hartslag'] ?? null;
        $lt2 = $testData['anaerobe_drempel_vermogen'] ?? null;
        $lt2_hr = $testData['anaerobe_drempel_hartslag'] ?? null;
        
        if ($lt1 || $lt2) {
            // Aerobe drempel met uitleg
            $output .= "Aërobe Drempel (LT1) - Vetverbrandingszone\n\n";
            if ($lt1) $output .= "• Vermogen/Snelheid: " . round($lt1, 1) . " " . $eenheid . "\n";
            if ($lt1_hr) $output .= "• Hartslag: " . round($lt1_hr) . " bpm\n";
            if (isset($testData['aerobe_drempel_watt_per_kg'])) {
                $output .= "• Relatief vermogen: " . $testData['aerobe_drempel_watt_per_kg'] . " W/kg\n";
            }
            if (isset($testData['aerobe_drempel_percentage_hrr'])) {
                $output .= "• Percentage hartslagreserve: " . $testData['aerobe_drempel_percentage_hrr'] . "%\n";
            }
            
            $output .= "\n📖 Wat betekent dit?\n";
            $output .= "De aërobe drempel markeert de intensiteit waarbij uw lichaam nog\n";
            $output .= "overwegend op vetten draait voor energievoorziening. Dit is uw\n";
            $output .= "'all day pace' - u kunt uren achter elkaar trainen op dit niveau\n";
            $output .= "zonder extreme vermoeidheid op te bouwen. Dit is de foundation van\n";
            $output .= "alle uithoudingssporten.\n\n";
            
            // Anaërobe drempel met uitleg
            $output .= "\nAnaërobe Drempel (LT2) - Lactaatdrempel\n\n";
            if ($lt2) $output .= "• Vermogen/Snelheid: " . round($lt2, 1) . " " . $eenheid . "\n";
            if ($lt2_hr) $output .= "• Hartslag: " . round($lt2_hr) . " bpm\n";
            if (isset($testData['anaerobe_drempel_watt_per_kg'])) {
                $output .= "• Relatief vermogen: " . $testData['anaerobe_drempel_watt_per_kg'] . " W/kg\n";
            }
            if (isset($testData['anaerobe_drempel_percentage_hrr'])) {
                $output .= "• Percentage hartslagreserve: " . $testData['anaerobe_drempel_percentage_hrr'] . "%\n";
            }
            
            $output .= "\n📖 Wat betekent dit?\n";
            $output .= "De anaërobe drempel is de hoogste intensiteit die u langere tijd\n";
            $output .= "kunt volhouden (20-60 minuten). Boven dit punt stapelt lactaat zich\n";
            $output .= "sneller op dan het kan worden afgevoerd, wat leidt tot vermoeidheid.\n";
            $output .= "Deze drempel bepaalt uw 'race pace' voor de meeste events.\n\n";
            
            // 🔬 DREMPEL EFFICIËNTIE ANALYSE
            if ($lt1 && $lt2) {
                $bereik = $lt2 - $lt1;
                $percentageVerschil = (($lt2 - $lt1) / $lt1) * 100;
                
                $output .= "\n🔬 DREMPEL EFFICIËNTIE ANALYSE\n\n";
                $output .= "Verschil tussen LT1 en LT2: " . round($bereik, 1) . " " . $eenheid . " (" . round($percentageVerschil, 1) . "%)\n\n";
                
                if ($percentageVerschil < 15) {
                    $output .= "Interpretatie: SMAL DREMPEL BEREIK ⚠️\n\n";
                    $output .= "Uw drempels liggen dicht bij elkaar, wat duidt op:\n";
                    $output .= "• Beperkt trainingsscope tussen aerobe en anaerobe zone\n";
                    $output .= "• Mogelijk gebrek aan aerobe basistraining\n";
                    $output .= "• Kans op snelle vermoeidheid bij intensivering\n";
                    $output .= "• Beperkte lactaat clearing capacity\n\n";
                    $output .= "💡 Wat te doen?\n";
                    $output .= "Focus op uitbreiding van uw aerobe capaciteit!\n";
                    $output .= "→ 85-90% van training ONDER LT1 (zeer lage intensiteit)\n";
                    $output .= "→ Lange duurtraining (2-5 uur) aan zeer rustig tempo\n";
                    $output .= "→ Minimale intensieve training (1x per 10-14 dagen)\n";
                    $output .= "→ Bouw eerst aerobe motor op voordat u gaat intervallentrainen\n\n";
                } elseif ($percentageVerschil >= 15 && $percentageVerschil < 30) {
                    $output .= "Interpretatie: GOED DREMPEL BEREIK ✅\n\n";
                    $output .= "U heeft een gezond bereik tussen uw drempels, wat wijst op:\n";
                    $output .= "• Solide aerobe basis\n";
                    $output .= "• Effectieve lactaat buffering en clearing\n";
                    $output .= "• Breed trainingsscope voor intervalwerk\n";
                    $output .= "• Goede metabole flexibiliteit\n\n";
                    $output .= "💡 Wat te doen?\n";
                    $output .= "Behoud dit met polarized training (80/20 principe)!\n";
                    $output .= "→ 80% onder LT1 voor aerobe onderhoud\n";
                    $output .= "→ 20% boven LT2 voor anaerobe ontwikkeling\n";
                    $output .= "→ Vermijd 'junk miles' tussen LT1 en LT2 (gray zone)\n";
                    $output .= "→ Focus op specifieke race simulaties\n\n";
                } else {
                    $output .= "Interpretatie: BREED DREMPEL BEREIK 🌟\n\n";
                    $output .= "Uitstekend! U heeft een zeer breed bereik, wat wijst op:\n";
                    $output .= "• Excellente aerobe basis en mitochondriaal dichtheid\n";
                    $output .= "• Sterke lactaat clearing capacity\n";
                    $output .= "• Hoog ontwikkelde metabole flexibiliteit\n";
                    $output .= "• Elite-niveau cardiovasculaire efficiency\n\n";
                    $output .= "💡 Wat te doen?\n";
                    $output .= "U bent klaar voor race-specifieke intensiteitsopbouw!\n";
                    $output .= "→ Sweet spot intervallen zijn nu effectief (90-95% LT2)\n";
                    $output .= "→ Focus op race pace simulaties\n";
                    $output .= "→ Specifieke hoge intensiteit intervallen voor VO2max\n";
                    $output .= "→ Maintain aerobe base met lange easy runs/rides\n\n";
                }
            }
            
        } else {
            $output .= "Drempelwaarden zijn nog niet bepaald. Vul de testresultaten in en\n";
            $output .= "genereer de grafiek om automatische drempelberekening te krijgen.\n\n";
        }
        
        $output .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        // ═══════════════════════════════════════════════════════════════
        // SECTIE VI: POPULATIEVERGELIJKING & PRESTATIECLASSIFICATIE
        // ═══════════════════════════════════════════════════════════════
        
        $output .= "═══════════════════════════════════════════════════════════════\n";
        $output .= "            � SECTIE VI: POPULATIE & PRESTATIECLASSIFICATIE\n";
        $output .= "═══════════════════════════════════════════════════════════════\n\n";
        
        // Populatievergelijking indien beschikbaar
        if (isset($testData['population_comparison']) && $testData['population_comparison']) {
            $popData = $testData['population_comparison'];
            
            if (isset($popData['classificatie'])) {
                $class = $popData['classificatie'];
                
                $output .= "🏆 UW PRESTATIECLASSIFICATIE\n\n";
                $output .= "Niveau: " . $class['level'] . "\n";
                $output .= "Omschrijving: " . $class['description'] . "\n";
                $output .= "Percentiel: Top " . $class['percentile'] . "% van uw leeftijdsgroep\n\n";
                
                $output .= "Dit betekent:\n";
                if ($class['percentile'] >= 95) {
                    $output .= "U behoort tot de TOP " . (100 - $class['percentile']) . "% van atleten in uw categorie!\n";
                    $output .= "Dit is het niveau van regionale/nationale competitie.\n\n";
                } elseif ($class['percentile'] >= 75) {
                    $output .= "U presteert beter dan " . $class['percentile'] . "% van uw leeftijdsgenoten.\n";
                    $output .= "Dit is een sterk competitief niveau.\n\n";
                } elseif ($class['percentile'] >= 50) {
                    $output .= "U presteert bovengemiddeld vergeleken met uw leeftijdsgroep.\n";
                    $output .= "Met gerichte training is nog veel groei mogelijk.\n\n";
                } else {
                    $output .= "Er is veel groeipotentieel aanwezig!\n";
                    $output .= "Focus op consistente training en u zult snel progressie zien.\n\n";
                }
            }
            
            if (isset($popData['normen'])) {
                $output .= "Referentiewaarden voor uw categorie:\n";
                $output .= "  Leeftijdsgroep: " . ($popData['leeftijdsgroep'] ?? 'N/A') . " jaar\n";
                $output .= "  Sport: " . ucfirst($popData['sporttype'] ?? 'N/A') . "\n\n";
            }
            
            $output .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        }
        
        // ═══════════════════════════════════════════════════════════════
        // SECTIE VII: TRAININGSADVIES & PERIODISERING
        // ═══════════════════════════════════════════════════════════════
        
        $output .= "═══════════════════════════════════════════════════════════════\n";
        $output .= "              💪 SECTIE VII: TRAININGSADVIES & PERIODISERING\n";
        $output .= "═══════════════════════════════════════════════════════════════\n\n";
        
        if ($lt1 && $lt2) {
            $output .= "🎯 POLARIZED TRAINING (80/20 PRINCIPE)\n\n";
            $output .= "Voor optimale resultaten raden we het wetenschappelijk onderbouwde\n";
            $output .= "80/20 trainingsmodel aan:\n\n";
            
            $output .= "1️⃣ AËROBE BASISTRAINING (80% van trainingstijd)\n\n";
            $output .= "Train ONDER de aërobe drempel (LT1):\n";
            $output .= "  • Vermogen/Snelheid: < " . round($lt1, 1) . " " . $eenheid . "\n";
            if ($lt1_hr) {
                $output .= "  • Hartslag: < " . round($lt1_hr) . " bpm\n";
            }
            $output .= "\n";
            $output .= "Hoe voelt dit?\n";
            $output .= "  → Comfortabel tempo waar je nog makkelijk kunt praten\n";
            $output .= "  → Je zou dit uren kunnen volhouden\n";
            $output .= "  → Gevoel: 'Is dit wel genoeg?'\n\n";
            $output .= "Training opzet:\n";
            $output .= "  • Duur: 60-180 minuten per sessie\n";
            $output .= "  • Frequentie: 4-5x per week\n";
            $output .= "  • Totaal: 8-12 uur per week\n\n";
            $output .= "Fysiologische adaptaties:\n";
            $output .= "  ✅ Verhoogt mitochondriaal dichtheid (+20-40%)\n";
            $output .= "  ✅ Verbetert vetstofwisseling (fat max +15-30%)\n";
            $output .= "  ✅ Vergroot capillair netwerk in spieren\n";
            $output .= "  ✅ Verhoogt slagvolume van het hart\n";
            $output .= "  ✅ Verbetert economie (minder energie per km/Watt)\n\n";
            
            $output .= "2️⃣ DREMPEL/INTERVAL TRAINING (15-20% van trainingstijd)\n\n";
            $output .= "Train ROND de anaërobe drempel (LT2):\n";
            $output .= "  • Vermogen/Snelheid: " . round($lt2, 1) . " " . $eenheid . "\n";
            if ($lt2_hr) {
                $output .= "  • Hartslag: " . round($lt2_hr) . " bpm\n";
            }
            $output .= "\n";
            $output .= "Hoe voelt dit?\n";
            $output .= "  → Zwaar maar houdbaar tempo\n";
            $output .= "  → Praten wordt moeilijk\n";
            $output .= "  → Gevoel: 'Dit is pittig!'\n\n";
            $output .= "Concrete interval voorbeelden:\n";
            $output .= "  Beginner:\n";
            $output .= "    • 4x 5 minuten @ LT2, rust 3 min\n";
            $output .= "    • 3x 8 minuten @ 95% LT2, rust 4 min\n\n";
            $output .= "  Gemiddeld:\n";
            $output .= "    • 4x 8 minuten @ LT2, rust 3 min\n";
            $output .= "    • 3x 12 minuten @ 95-100% LT2, rust 5 min\n";
            $output .= "    • 2x 20 minuten @ 95% LT2, rust 8 min\n\n";
            $output .= "  Gevorderd:\n";
            $output .= "    • 5x 10 minuten @ LT2, rust 2.5 min\n";
            $output .= "    • 3x 15 minuten @ 100-105% LT2, rust 5 min\n";
            $output .= "    • 1x 40 minuten @ 95-100% LT2 (sweet spot)\n\n";
            $output .= "Frequentie: 1-2x per week (NOOIT op opeenvolgende dagen!)\n\n";
            $output .= "Fysiologische adaptaties:\n";
            $output .= "  ✅ Verhoogt anaërobe drempel (+5-10% in 8 weken)\n";
            $output .= "  ✅ Verbetert lactaat buffering capaciteit\n";
            $output .= "  ✅ Vergroot VO2max (+3-8%)\n";
            $output .= "  ✅ Verhoogt pijn tolerantie en mentale weerbaarheid\n\n";
            
            $output .= "3️⃣ HERSTEL & REGENERATIE (5% van trainingstijd)\n\n";
            $output .= "  • Zeer lage intensiteit (< 60% LT1) of complete rust\n";
            $output .= "  • Yoga, stretching, foam rolling\n";
            $output .= "  • Massage, sauna (indien beschikbaar)\n";
            $output .= "  • Slaap: minimaal 7-9 uur per nacht\n";
            $output .= "  • Voeding: eiwitrijk (1.6-2g/kg/dag)\n\n";
            
            $output .= "⚠️ VEELGEMAAKTE FOUTEN:\n\n";
            $output .= "❌ Te veel 'gray zone' training (tussen LT1 en LT2)\n";
            $output .= "   → Dit is te zwaar voor aerobe ontwikkeling\n";
            $output .= "   → Maar te licht voor effectieve drempelverhoging\n";
            $output .= "   → Result: veel vermoeidheid, weinig adaptatie\n\n";
            $output .= "❌ Elke training 'medium hard'\n";
            $output .= "   → Je wordt nooit echt goed in één systeem\n";
            $output .= "   → Chronische vermoeidheid\n\n";
            $output .= "✅ JUIST: Maak sessies ECHT easy of ECHT hard!\n\n";
            
            $output .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
            
        } else {
            $output .= "🎯 ALGEMEEN TRAININGSADVIES\n\n";
            $output .= "• Bepaal eerst uw drempelwaarden door testresultaten in te vullen\n";
            $output .= "• Begin met aerobe basistraining (lage intensiteit, lange duur)\n";
            $output .= "• Bouw geleidelijk volume op voordat u intensiteit toevoegt\n";
            $output .= "• Vermijd te snel te veel intensieve training\n\n";
            $output .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        }
        
        // DOELSTELLINGEN SPECIFIEK ADVIES (gedetailleerd per sport/doel)
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
        
        // ═══════════════════════════════════════════════════════════════
        // SECTIE VIII: PROGRESSIE & HERTEST PLANNING
        // ═══════════════════════════════════════════════════════════════
        
        $output .= "═══════════════════════════════════════════════════════════════\n";
        $output .= "              📈 SECTIE VIII: PROGRESSIE & HERTEST PLANNING\n";
        $output .= "═══════════════════════════════════════════════════════════════\n\n";
        
        $output .= "🎯 VERWACHTE VERBETERINGEN\n\n";
        $output .= "Na 8-12 weken consistent trainen volgens bovenstaand advies:\n\n";
        $output .= "Fysiologische adaptaties:\n";
        $output .= "• Aërobe drempel (LT1): +5-10% verbetering\n";
        $output .= "• Anaërobe drempel (LT2): +3-8% verbetering\n";
        $output .= "• VO2max: +3-8% stijging\n";
        $output .= "• Rusthartslag: -5-10 bpm daling\n";
        $output .= "• Vetpercentage: -1-3% bij gewichtsverlies focus\n";
        $output .= "• Watt/kg ratio: +5-12% (afhankelijk van trainingstatus)\n\n";
        
        $output .= "⏱️ WANNEER HERTESTEN?\n\n";
        $output .= "• Na 8-12 weken gestructureerd trainen\n";
        $output .= "• Voor belangrijke wedstrijden (6-8 weken ervoor)\n";
        $output .= "• Bij plateau in prestaties\n";
        $output .= "• Minimaal 2x per jaar voor serieuze atleten\n\n";
        
        $output .= "📊 TE MONITOREN METRICS\n\n";
        $output .= "Dagelijks:\n";
        $output .= "✅ Rusthartslag 's ochtends\n";
        $output .= "✅ Slaapkwaliteit en -duur\n";
        $output .= "✅ Trainingstatus score\n\n";
        
        $output .= "Per training:\n";
        $output .= "✅ Hartslag bij vaste intensiteit (moet dalen)\n";
        $output .= "✅ Gemiddelde snelheid/vermogen (moet stijgen)\n";
        $output .= "✅ Perceived exertion bij vaste intensiteit (moet dalen)\n\n";
        
        $output .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        
        $output .= "═══════════════════════════════════════════════════════════════\n";
        $output .= "                           🎓 CONCLUSIE\n";
        $output .= "═══════════════════════════════════════════════════════════════\n\n";
        
        $output .= "De drie pijlers voor succes:\n\n";
        $output .= "1️⃣ TRAINING: Polarized 80/20 principe\n";
        $output .= "2️⃣ HERSTEL: Slaap, voeding en stress management\n";
        $output .= "3️⃣ CONSISTENTIE: Geduld en volume opbouw\n\n";
        
        $output .= "🚀 Succes met uw training!\n\n";
        
        $output .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $output .= "         💡 Voor nog meer detail: Upgrade naar AI-analyse\n";
        $output .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        
        return $output;
    }
}