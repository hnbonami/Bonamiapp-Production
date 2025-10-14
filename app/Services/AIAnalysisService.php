<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service voor AI-gestuurde complete analyse van inspanningstesten
 * Gebruikt OpenAI GPT-4o-mini voor uitgebreide sportmedische adviezen
 */
class AIAnalysisService
{
    private string $apiKey;
    private string $model;
    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('ai.openai_key');
        $this->model = config('ai.model', 'gpt-4o-mini');
        $this->baseUrl = 'https://api.openai.com/v1/chat/completions';
    }

    /**
     * Genereer complete AI analyse van de inspanningstest
     */
    public function genereerCompleteAnalyse(array $testData): string
    {
        Log::info('🧠 Genereren complete AI analyse', [
            'test_type' => $testData['testtype'] ?? 'onbekend',
            'goals' => $testData['specifieke_doelstellingen'] ?? 'geen'
        ]);

        $prompt = $this->bouwCompleteAnalysePrompt($testData);
        
        try {
            $response = $this->callOpenAI($prompt);
            
            Log::info('✅ Complete AI analyse succesvol gegenereerd');
            return $response;
            
        } catch (Exception $e) {
            Log::error('❌ Fout bij genereren complete analyse: ' . $e->getMessage());
            return $this->getFallbackCompleteAnalyse($testData);
        }
    }

    /**
     * Bouw uitgebreide prompt voor complete testanalyse
     */
    private function bouwCompleteAnalysePrompt(array $data): string
    {
        // Extraheer alle beschikbare data
        $testtype = $data['testtype'] ?? 'onbekend';
        $doelstellingen = $data['specifieke_doelstellingen'] ?? 'algemene fitheid';
        
        // Persoonlijke gegevens
        $leeftijd = $data['leeftijd'] ?? 'onbekend';
        $gewicht = $data['lichaamsgewicht_kg'] ?? 'onbekend';
        $lengte = $data['lichaamslengte_cm'] ?? 'onbekend';
        $bmi = $data['bmi'] ?? 'onbekend';
        
        // Drempelwaarden
        $aerobeVermogen = $data['aerobe_drempel_vermogen'] ?? 'niet gemeten';
        $aerobeHartslag = $data['aerobe_drempel_hartslag'] ?? 'niet gemeten';
        $anaerobeVermogen = $data['anaerobe_drempel_vermogen'] ?? 'niet gemeten';
        $anaerobeHartslag = $data['anaerobe_drempel_hartslag'] ?? 'niet gemeten';
        
        // Hartslaggegevens
        $maxHartslag = $data['maximale_hartslag_bpm'] ?? 'niet gemeten';
        $rustHartslag = $data['hartslag_rust_bpm'] ?? 'niet gemeten';
        
        // Lichaamssamenstelling
        $buikomtrek = $data['buikomtrek_cm'] ?? 'niet gemeten';
        
        // Protocol informatie
        $analyseMethode = $data['analyse_methode'] ?? 'niet gespecificeerd';
        $testlocatie = $data['testlocatie'] ?? 'onbekend';
        
        // Besluit velden
        $besluitLichaamssamenstelling = $data['besluit_lichaamssamenstelling'] ?? '';
        
        // Bepaal eenheid en bereken ratio's
        $eenheid = $this->bepaalEenheid($testtype);
        $analyseData = $this->berekenAnalyseRatios($data);

        return "Je bent een wereldklasse sportfysioloog en performance consultant met 25+ jaar ervaring. Je hebt gewerkt met Olympische atleten, Tour de France renners, Boston Marathon winnaars en duizenden recreatieve sporters. Je bent gespecialiseerd in lactaattesten, trainingsperiodisering en goal-specific performance optimization.

═══════════════════════════════════════════════════════════════════════════════════
📊 COMPLETE INSPANNINGSTEST ANALYSE RAPPORT
═══════════════════════════════════════════════════════════════════════════════════

🏃‍♂️ ATLEET PROFIEL:
───────────────────────────────────────────────────────────────────────────────────
• Leeftijd: {$leeftijd} jaar
• Gewicht: {$gewicht} kg | Lengte: {$lengte} cm | BMI: {$bmi}
• Buikomtrek: {$buikomtrek} cm
• Testtype: {$testtype}
• Testlocatie: {$testlocatie}
• Analyse methode: {$analyseMethode}

🎯 SPECIFIEKE DOELSTELLINGEN:
───────────────────────────────────────────────────────────────────────────────────
{$doelstellingen}

📈 GEMETEN FYSIOLOGISCHE PARAMETERS:
───────────────────────────────────────────────────────────────────────────────────
• Hartslag rust: {$rustHartslag} bpm
• Hartslag maximum: {$maxHartslag} bpm
• Aërobe drempel (LT1): {$aerobeVermogen} {$eenheid} bij {$aerobeHartslag} bpm
• Anaërobe drempel (LT2): {$anaerobeVermogen} {$eenheid} bij {$anaerobeHartslag} bpm

🔬 LICHAAMSSAMENSTELLING BEVINDINGEN:
───────────────────────────────────────────────────────────────────────────────────
{$besluitLichaamssamenstelling}

{$analyseData}

═══════════════════════════════════════════════════════════════════════════════════
📋 OPDRACHT: COMPLETE PERFORMANCE ANALYSE
═══════════════════════════════════════════════════════════════════════════════════

Schrijf een uitgebreide, wetenschappelijk onderbouwde analyse die ALLE bovenstaande parameters integreert:

🏆 1. PRESTATIECLASSIFICATIE & POPULATIEVERGELIJKING
─────────────────────────────────────────────────────────────────────────────────
• Vergelijk ALLE parameters met leeftijd/geslacht specifieke normwaarden
• Classificatie: recreational/trained/competitive/elite niveau
• Percentiel rankings binnen relevante populatie
• Specifieke vergelijking met doelgroep (bijv. marathonlopers indien Marathon van Berlijn doel)
• Identificeer sterktes en zwaktes vs benchmarks

💡 2. FYSIOLOGISCHE INTERPRETATIE & METABOLISME
─────────────────────────────────────────────────────────────────────────────────
• Aerobe vs anaerobe capaciteit breakdown
• Metabolische flexibiliteit en substrate utilization
• Hartslagreserve en cardiac efficiency
• VO2max schatting en comparison
• Lactaat kinetics en buffering capacity

📊 3. GOAL-SPECIFIC PERFORMANCE PROGNOSIS
─────────────────────────────────────────────────────────────────────────────────
• DIRECTE relatie van alle metingen tot specifieke doelstellingen
• Realistische performance predictions voor gestelde doelen
• Tijdlijn voor doelrealisatie (bijv. marathon target times)
• Limiterende factoren voor goal achievement
• Race strategy recommendations gebaseerd op drempel profiel

🎯 4. GEÏNTEGREERDE TRAININGSPERIODISERING
─────────────────────────────────────────────────────────────────────────────────
• Macro/meso/micro cyclus planning
• Zone distribution op basis van gemeten drempels
• Volume vs intensiteit prioritization
• Specifieke workout prescriptions met exacte power/pace targets
• Recovery protocols en monitoring parameters

⚡ 5. PERFORMANCE OPTIMIZATION ROADMAP
─────────────────────────────────────────────────────────────────────────────────
• Prioritized improvement areas (grootste ROI)
• Specific interventions (training, nutrition, recovery)
• Testing frequency en progress markers
• Red flags en injury prevention strategies
• Equipment/technology recommendations

🔄 6. MONITORING & PROGRESSION STRATEGY
─────────────────────────────────────────────────────────────────────────────────
• KPI's om progress te tracken
• Retest protocols en timing
• Adjustments based on response
• Long-term development pathway

SCHRIJFSTIJL VEREISTEN:
═══════════════════════════════════════════════════════════════════════════════════
• Expert niveau, wetenschappelijk précies maar praktisch toepasbaar
• Nederlands, professioneel en direct
• Gebruik ALLE beschikbare data punten in de analyse
• Concrete cijfers, percentages en vergelijkingen
• Specifieke referenties naar de gestelde doelstellingen
• 800-1200 woorden (uitgebreid en compleet)
• Gebruik bulletpoints en structuur voor leesbaarheid
• Focus op actionable insights en implementatie

KRITISCH BELANGRIJK:
• Integreer ALLE gemeten parameters in één coherent verhaal
• Geef altijd populatie benchmarks en contextual comparisons
• Relateer ALLES aan de specifieke doelstellingen
• Wees specifiek over wat deze cijfers betekenen voor prestatie
• Geef concrete next steps en action items

Begin met een executive summary van de key findings en classificatie.";
    }

    /**
     * Bereken relevante ratio's en vergelijkingen
     */
    private function berekenAnalyseRatios(array $data): string
    {
        $analyseData = "\n🧮 BEREKENDE PERFORMANCE METRICS:\n";
        $analyseData .= "───────────────────────────────────────────────────────────────────────────────────\n";
        
        // Watt/kg berekeningen
        if (isset($data['lichaamsgewicht_kg']) && isset($data['aerobe_drempel_vermogen']) && 
            is_numeric($data['lichaamsgewicht_kg']) && is_numeric($data['aerobe_drempel_vermogen'])) {
            $aerobeWattPerKg = round($data['aerobe_drempel_vermogen'] / $data['lichaamsgewicht_kg'], 2);
            $analyseData .= "• Aërobe watt/kg: {$aerobeWattPerKg} W/kg\n";
        }
        
        if (isset($data['lichaamsgewicht_kg']) && isset($data['anaerobe_drempel_vermogen']) && 
            is_numeric($data['lichaamsgewicht_kg']) && is_numeric($data['anaerobe_drempel_vermogen'])) {
            $anaerobeWattPerKg = round($data['anaerobe_drempel_vermogen'] / $data['lichaamsgewicht_kg'], 2);
            $analyseData .= "• Anaërobe watt/kg: {$anaerobeWattPerKg} W/kg\n";
        }
        
        // LT1/LT2 verhouding
        if (isset($data['aerobe_drempel_vermogen']) && isset($data['anaerobe_drempel_vermogen']) && 
            is_numeric($data['aerobe_drempel_vermogen']) && is_numeric($data['anaerobe_drempel_vermogen'])) {
            $ratio = round(($data['anaerobe_drempel_vermogen'] / $data['aerobe_drempel_vermogen']) * 100, 1);
            $analyseData .= "• LT2/LT1 ratio: {$ratio}% (anaërobe reserve)\n";
        }
        
        // Hartslagreserve
        if (isset($data['maximale_hartslag_bpm']) && isset($data['hartslag_rust_bpm']) && 
            is_numeric($data['maximale_hartslag_bpm']) && is_numeric($data['hartslag_rust_bpm'])) {
            $hrReserve = $data['maximale_hartslag_bpm'] - $data['hartslag_rust_bpm'];
            $analyseData .= "• Hartslagreserve: {$hrReserve} bpm\n";
        }
        
        return $analyseData;
    }

    /**
     * Bepaal de juiste eenheid op basis van testtype
     */
    private function bepaalEenheid(string $testtype): string
    {
        return match($testtype) {
            'looptest', 'veldtest_lopen' => 'km/h',
            'veldtest_zwemmen' => 'min/100m',
            'fietstest', 'veldtest_fietsen' => 'Watt',
            default => 'Watt'
        };
    }

    /**
     * Voer OpenAI API call uit
     */
    private function callOpenAI(string $prompt): string
    {
        if (empty($this->apiKey)) {
            throw new Exception('OpenAI API key niet geconfigureerd');
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(30)->post($this->baseUrl, [
            'model' => $this->model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'Je bent een Nederlandse sportfysioloog gespecialiseerd in lactaattesten en trainingsadvies. Je hebt 20+ jaar ervaring met atleten van recreatief tot elite niveau.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'max_tokens' => config('ai.max_tokens', 1500),
            'temperature' => config('ai.temperature', 0.4),
        ]);

        if (!$response->successful()) {
            throw new Exception('OpenAI API fout: ' . $response->body());
        }

        $data = $response->json();
        
        if (!isset($data['choices'][0]['message']['content'])) {
            throw new Exception('Onverwacht OpenAI response formaat');
        }

        return trim($data['choices'][0]['message']['content']);
    }

    /**
     * Fallback bij API fouten - complete analyse
     */
    private function getFallbackCompleteAnalyse(array $data): string
    {
        $testtype = $data['testtype'] ?? 'fietstest';
        $doelstellingen = $data['specifieke_doelstellingen'] ?? 'algemene fitheid';
        $aerobeVermogen = $data['aerobe_drempel_vermogen'] ?? 'niet gemeten';
        $anaerobeVermogen = $data['anaerobe_drempel_vermogen'] ?? 'niet gemeten';
        
        return "COMPLETE INSPANNINGSTEST ANALYSE

PRESTATIECLASSIFICATIE:
Uw gemeten drempelwaardes (LT1: {$aerobeVermogen}, LT2: {$anaerobeVermogen}) worden geanalyseerd in de context van uw doelstellingen: {$doelstellingen}.

BELANGRIJKE BEVINDINGEN:
• Voor {$testtype} tonen uw resultaten een solide basis voor verdere ontwikkeling
• Uw drempelprofiel suggereert specifieke trainingsaanbevelingen
• De verhouding tussen aërobe en anaërobe capaciteit biedt inzichten voor periodisering

AANBEVELINGEN:
1. Focus op gestructureerde training binnen uw gemeten zones
2. Bouw geleidelijk volume op met 80% onder LT1 intensiteit
3. Voeg gerichte intervaltraining toe rond LT2 niveau
4. Monitor progressie met regelmatige hertesten

Voor een uitgebreidere analyse adviseren wij een vervolgconsultatie waarbij alle parameters gedetailleerd worden besproken in relatie tot uw specifieke doelstellingen.";
    }
}