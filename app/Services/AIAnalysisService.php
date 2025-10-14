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
        
        // Bepaal sporttype
        $sporttype = str_contains($testtype, 'fiets') ? 'cycling' : 'running';
        
        // Bepaal leeftijdsgroep
        $leeftijdsgroep = $this->getLeeftijdsgroep($leeftijd);
        
        // Haal normen op (standaard male)
        $normen = config("ai_analysis.population_norms.{$sporttype}.male.{$leeftijdsgroep}");
        
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
        
        $output = "# 🏃 INSPANNINGSTEST ANALYSE\n\n";
        $output .= "> *Automatisch gegenereerde analyse*\n\n";
        
        $output .= "## 📊 TESTOVERZICHT\n\n";
        $output .= "**Testtype:** " . ucfirst(str_replace('_', ' ', $testtype)) . "\n";
        $output .= "**Datum:** " . ($testData['testdatum'] ?? date('Y-m-d')) . "\n\n";
        
        $output .= "## 👤 ATLET PROFIEL\n\n";
        if (!empty($testData['leeftijd'])) {
            $output .= "- **Leeftijd:** " . $testData['leeftijd'] . " jaar\n";
        }
        if (!empty($testData['lichaamsgewicht_kg'])) {
            $output .= "- **Gewicht:** " . $testData['lichaamsgewicht_kg'] . " kg\n";
        }
        if (!empty($testData['specifieke_doelstellingen'])) {
            $output .= "\n**Doelstellingen:**\n" . $testData['specifieke_doelstellingen'] . "\n";
        }
        $output .= "\n";
        
        $output .= "## 🎯 GEMETEN DREMPELWAARDEN\n\n";
        $lt1 = $testData['aerobe_drempel_vermogen'] ?? null;
        $lt1_hr = $testData['aerobe_drempel_hartslag'] ?? null;
        $lt2 = $testData['anaerobe_drempel_vermogen'] ?? null;
        $lt2_hr = $testData['anaerobe_drempel_hartslag'] ?? null;
        
        if ($lt1 || $lt2) {
            $output .= "### Aërobe Drempel (LT1)\n";
            if ($lt1) $output .= "- **Vermogen/Snelheid:** " . round($lt1, 1) . " " . $eenheid . "\n";
            if ($lt1_hr) $output .= "- **Hartslag:** " . round($lt1_hr) . " bpm\n";
            
            $output .= "\n### Anaërobe Drempel (LT2)\n";
            if ($lt2) $output .= "- **Vermogen/Snelheid:** " . round($lt2, 1) . " " . $eenheid . "\n";
            if ($lt2_hr) $output .= "- **Hartslag:** " . round($lt2_hr) . " bpm\n\n";
        }
        
        if (isset($testData['population_comparison']['classificatie'])) {
            $class = $testData['population_comparison']['classificatie'];
            $output .= "## 🏆 PRESTATIECLASSIFICATIE\n\n";
            $output .= "**Niveau:** " . $class['level'] . " (" . $class['description'] . ")\n\n";
            $output .= "Je prestatie valt in het **top " . $class['percentile'] . "%** van jouw leeftijdsgroep.\n\n";
        }
        
        $output .= "## 💪 TRAININGSADVIES\n\n";
        if ($lt1 && $lt2) {
            $output .= "### Aërobe Basisontwikkeling\n";
            $output .= "- Train **80-85%** van je totale trainingstijd onder de aërobe drempel (LT1)\n";
            $output .= "- Dit betekent trainen onder " . round($lt1, 1) . " " . $eenheid;
            if ($lt1_hr) $output .= " of onder " . round($lt1_hr) . " bpm";
            $output .= "\n\n";
            
            $output .= "### Drempeltraining\n";
            $output .= "- Voeg **1-2x per week** drempelintervals toe rond de anaërobe drempel (LT2)\n";
            $output .= "- Train rond " . round($lt2, 1) . " " . $eenheid;
            if ($lt2_hr) $output .= " of rond " . round($lt2_hr) . " bpm";
            $output .= "\n\n";
        }
        
        // Doelstellingen specifiek
        if (!empty($testData['specifieke_doelstellingen'])) {
            $doelen = strtolower($testData['specifieke_doelstellingen']);
            
            $output .= "### Advies op basis van jouw doelstellingen\n\n";
            
            if (str_contains($doelen, 'marathon')) {
                $output .= "**Marathon voorbereiding:**\n";
                $output .= "- Focus op lange duurtrainingen tot 30-35km\n";
                $output .= "- Oefen race tempo rond je anaërobe drempel\n\n";
            }
            
            if (str_contains($doelen, 'snelheid')) {
                $output .= "**Snelheidsverbetering:**\n";
                $output .= "- Voeg tempo intervallen toe boven LT2\n";
                $output .= "- Train specifieke race snelheden\n\n";
            }
        }
        
        $output .= "## 📈 PROGRESSIE & HERTEST\n\n";
        $output .= "**Verwachte verbeteringen (8-12 weken):**\n";
        $output .= "- Aërobe drempel: 5-10% verbetering\n";
        $output .= "- Anaërobe drempel: 3-8% verbetering\n";
        $output .= "- Lagere hartslag bij zelfde intensiteit\n\n";
        
        $output .= "---\n\n";
        $output .= "*💡 Voor een uitgebreidere AI-analyse, configureer de OpenAI API key.*\n";
        
        return $output;
    }
}