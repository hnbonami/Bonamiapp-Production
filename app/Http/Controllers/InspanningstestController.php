<?php
namespace App\Http\Controllers;

use App\Models\Inspanningstest;
use App\Models\Klant;
use App\Helpers\SjabloonHelper;
use App\Services\AIAnalysisService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class InspanningstestController extends Controller {
    /**
     * Toon resultaten van een inspanningstest
     */
    public function results(Klant $klant, Inspanningstest $test)
    {
        // Controleer of inspanningstest bij klant hoort
        if ($test->klant_id !== $klant->id) {
            abort(404);
        }
        
        // Decode JSON data
        $testresultaten = json_decode($test->testresultaten, true) ?? [];
        $trainingszones = json_decode($test->trainingszones_data, true) ?? [];
        
        // 🏃 VELDTEST SNELHEID BEREKENING - Voor correcte tabel en grafiek weergave
        if (in_array($test->testtype, ['veldtest_lopen', 'veldtest_zwemmen'])) {
            \Log::info('🏃 Veldtest detected - bereken snelheden', [
                'testtype' => $test->testtype,
                'resultaten_count' => count($testresultaten),
                'eerste_rij' => $testresultaten[0] ?? null
            ]);
            
            foreach ($testresultaten as $index => &$resultaat) {
                // Voor veldtest lopen: bereken snelheid in km/h
                if ($test->testtype === 'veldtest_lopen' && isset($resultaat['afstand'])) {
                    $afstandKm = $resultaat['afstand'] / 1000; // meter naar km
                    $tijdMinuten = ($resultaat['tijd_min'] ?? 0) + (($resultaat['tijd_sec'] ?? 0) / 60); // totale tijd in minuten
                    $tijdUur = $tijdMinuten / 60; // minuten naar uur
                    
                    if ($tijdUur > 0) {
                        $resultaat['snelheid'] = round($afstandKm / $tijdUur, 2); // km/h
                        \Log::info("  🏃 Rij {$index}: {$resultaat['afstand']}m in {$tijdMinuten}min = {$resultaat['snelheid']} km/h");
                    } else {
                        $resultaat['snelheid'] = 0;
                    }
                }
                
                // Voor veldtest zwemmen: bereken snelheid in min/100m
                if ($test->testtype === 'veldtest_zwemmen' && isset($resultaat['afstand'])) {
                    $totaleTijdSec = (($resultaat['tijd_min'] ?? 0) * 60) + ($resultaat['tijd_sec'] ?? 0);
                    
                    if ($totaleTijdSec > 0 && $resultaat['afstand'] > 0) {
                        $resultaat['snelheid'] = round(($totaleTijdSec / 60) * (100 / $resultaat['afstand']), 2); // min/100m
                        \Log::info("  🏊 Rij {$index}: {$resultaat['afstand']}m in {$totaleTijdSec}sec = {$resultaat['snelheid']} min/100m");
                    } else {
                        $resultaat['snelheid'] = 0;
                    }
                }
            }
            unset($resultaat); // Break reference
            
            \Log::info('✅ Veldtest snelheden berekend', [
                'testresultaten_na_berekening' => $testresultaten
            ]);
        }
        
        // Log voor debugging
        \Log::info('Results pagina - Data check:', [
            'test_id' => $test->id,
            'testtype' => $test->testtype,
            'resultaten_count' => count($testresultaten),
            'zones_count' => count($trainingszones),
            'aerobe_drempel_vermogen' => $test->aerobe_drempel_vermogen,
            'anaerobe_drempel_vermogen' => $test->anaerobe_drempel_vermogen
        ]);
        
        // Hernoem variabele voor de view (backward compatibility)
        $inspanningstest = $test;
        
        return view('inspanningstest.results', compact('klant', 'inspanningstest', 'testresultaten', 'trainingszones'));
    }

    public function generateReport(Request $request, $klantId, $testId)
    {
        // Hier kun je straks het HTML verslag genereren
        return redirect()->route('inspanningstest.results', ['klant' => $klantId, 'test' => $testId])->with('success', 'HTML verslag gegenereerd!');
    }
    public function create($klantId)
    {
        $klant = \App\Models\Klant::findOrFail($klantId);
        return view('inspanningstest.create', compact('klant'));
    }

    public function store(Request $request, Klant $klant)
    {
        // Log ALLE incoming data voor debugging
        \Log::info('=== INSPANNINGSTEST STORE DEBUG ===');
        \Log::info('Alle request data:', $request->all());
        
        // Valideer minimale verplichte velden - accepteer beide namen
        $request->validate([
            'testdatum' => 'nullable|date',
            'datum' => 'nullable|date',
            'testtype' => 'required|string',
        ]);
        
        // Gebruik testdatum OF datum (whichever is ingevuld), fallback naar vandaag
        $testDatum = $request->input('testdatum') ?: ($request->input('datum') ?: now()->format('Y-m-d'));
        
        \Log::info('Datum bepaald als:', ['datum' => $testDatum]);
        
        // Start met verplichte velden - datum is ALTIJD ingevuld
        $dataVoorDatabase = [
            'datum' => $testDatum, // Altijd ingevuld, fallback naar vandaag
            'testtype' => $request->testtype,
            'klant_id' => $klant->id,
            'user_id' => auth()->id(),
        ];
        
        // Voeg testresultaten toe als JSON
        if ($request->filled('testresultaten')) {
            $dataVoorDatabase['testresultaten'] = json_encode($request->testresultaten);
        }
        
        // Alle optionele tekstvelden
        $tekstVelden = [
            'testlocatie',
            'protocol',
            'weersomstandigheden',
            'specifieke_doelstellingen',
            'complete_ai_analyse',
            'analyse_methode',
            'zones_methode',
            'zones_eenheid',
            'trainingszones_data',
            'training_dag_voor_test',
            'training_2d_voor_test',
        ];
        
        foreach ($tekstVelden as $veld) {
            if ($request->filled($veld)) {
                $dataVoorDatabase[$veld] = $request->$veld;
            }
        }
        
        // Alle optionele numerieke velden
        $numeriekeVelden = [
            'vetpercentage',
            'zones_aantal',
            'lichaamsgewicht_kg',
            'lichaamslengte_cm',
            'bmi',
            'buikomtrek_cm',
            'hartslag_rust_bpm',
            'maximale_hartslag_bpm',
            'slaapkwaliteit',
            'eetlust',
            'gevoel_op_training',
            'stressniveau',
            'gemiddelde_trainingstatus',
            'startwattage',
            'stappen_min',
            'stappen_watt',
            'aerobe_drempel_vermogen',
            'aerobe_drempel_hartslag',
            'anaerobe_drempel_vermogen',
            'anaerobe_drempel_hartslag',
        ];
        
        foreach ($numeriekeVelden as $veld) {
            if ($request->filled($veld)) {
                $dataVoorDatabase[$veld] = $request->$veld;
            }
        }
        
        // Log data voor debugging - VOOR create
        \Log::info('Data VOOR create():', $dataVoorDatabase);
        \Log::info('🔍 complete_ai_analyse veld check:', [
            'aanwezig_in_request' => $request->has('complete_ai_analyse'),
            'filled_in_request' => $request->filled('complete_ai_analyse'),
            'waarde' => $request->input('complete_ai_analyse') ? substr($request->input('complete_ai_analyse'), 0, 100) . '...' : 'LEEG',
            'in_dataVoorDatabase' => isset($dataVoorDatabase['complete_ai_analyse']),
            'lengte' => $request->filled('complete_ai_analyse') ? strlen($request->input('complete_ai_analyse')) : 0
        ]);
        
        // BELANGRIJK: Controleer of datum er nog in zit
        if (!isset($dataVoorDatabase['datum']) || empty($dataVoorDatabase['datum'])) {
            \Log::error('DATUM IS LEEG! Forceer vandaag als fallback');
            $dataVoorDatabase['datum'] = now()->format('Y-m-d');
        }
        
        try {
            // Maak inspanningstest aan
            $inspanningstest = Inspanningstest::create($dataVoorDatabase);
            
            // Redirect naar RESULTS pagina met success bericht
            return redirect()->route('inspanningstest.results', [
                'klant' => $klant->id,
                'test' => $inspanningstest->id  // 'test' ipv 'inspanningstest'
            ])->with('success', 'Inspanningstest succesvol aangemaakt!');
            
        } catch (\Exception $e) {
            \Log::error('Fout bij opslaan inspanningstest: ' . $e->getMessage());
            \Log::error('Data die probeerde op te slaan:', $dataVoorDatabase);
            
            return back()
                ->withInput()
                ->withErrors(['error' => 'Er ging iets mis bij het opslaan: ' . $e->getMessage()]);
        }
    }
    public function show($klantId, $testId)
    {
        $klant = \App\Models\Klant::findOrFail($klantId);
        $test = Inspanningstest::where('klant_id', $klantId)->findOrFail($testId);
        
        // Check if there's a matching sjabloon for this inspanningstest
        $hasMatchingTemplate = SjabloonHelper::hasMatchingTemplate($test->testtype, 'inspanningstest');
        $matchingTemplate = SjabloonHelper::findMatchingTemplate($test->testtype, 'inspanningstest');
        
        return view('inspanningstest.show', compact('klant', 'test', 'hasMatchingTemplate', 'matchingTemplate'));
    }

    public function edit($klantId, $testId)
    {
        $klant = \App\Models\Klant::findOrFail($klantId);
        $test = Inspanningstest::where('klant_id', $klantId)->findOrFail($testId);
        return view('inspanningstest.edit', compact('klant', 'test'));
    }

    public function update(Request $request, $klantId, $testId)
    {
        $test = Inspanningstest::where('klant_id', $klantId)->findOrFail($testId);
        $data = $request->validate([
            'testdatum' => 'required|date',
            'testtype' => 'required|in:looptest,fietstest',
            'lichaamslengte_cm' => 'nullable|integer',
            'lichaamsgewicht_kg' => 'nullable|numeric',
            'bmi' => 'nullable|numeric',
            'hartslag_rust_bpm' => 'nullable|integer',
            'buikomtrek_cm' => 'nullable|integer',
            'startwattage' => 'nullable|integer',
            'stappen_min' => 'nullable|integer',
            'testresultaten' => 'nullable|array',
            'aerobe_drempel_vermogen' => 'nullable|numeric',
            'aerobe_drempel_hartslag' => 'nullable|integer',
            'anaerobe_drempel_vermogen' => 'nullable|numeric',
            'anaerobe_drempel_hartslag' => 'nullable|integer',
            'besluit_lichaamssamenstelling' => 'nullable|string',
            'advies_aerobe_drempel' => 'nullable|string',
            'advies_anaerobe_drempel' => 'nullable|string',
            // Template kind for mapping to report templates (nullable)
            'template_kind' => 'nullable|string|in:inspanningstest_fietsen,inspanningstest_lopen,standaard_bikefit,professionele_bikefit,zadeldrukmeting,maatbepaling',
        ]);
        $test->update($data);
        return redirect()->route('klanten.show', $klantId)->with('success', 'Inspanningstest bijgewerkt.');
    }

    public function destroy($klantId, $testId)
    {
        $test = Inspanningstest::where('klant_id', $klantId)->findOrFail($testId);
        $test->delete();
        return redirect()->route('klanten.show', $klantId)->with('success', 'Inspanningstest verwijderd.');
    }

    public function duplicate($klantId, $testId)
    {
        $test = Inspanningstest::where('klant_id', $klantId)->findOrFail($testId);
        $newTest = $test->replicate();
        $newTest->testdatum = now();
        $newTest->save();
        return redirect()->route('klanten.show', $klantId)->with('success', 'Inspanningstest gedupliceerd.');
    }

    public function pdf($klantId, $testId)
    {
        $klant = \App\Models\Klant::findOrFail($klantId);
        $test = Inspanningstest::where('klant_id', $klantId)->findOrFail($testId);
        $pdf = \PDF::loadView('inspanningstest.pdf', compact('klant', 'test'));
        return $pdf->download('inspanningstest_'.$klant->id.'_'.$test->id.'.pdf');
    }

    public function report($klantId, $testId)
    {
        $klant = \App\Models\Klant::findOrFail($klantId);
        $test = Inspanningstest::where('klant_id', $klantId)->findOrFail($testId);
        // Reuse the same view used for PDF generation but render as HTML preview
        return view('inspanningstest.show', compact('klant', 'test'));
    }
    /**
     * Generate sjabloon-based report for inspanningstest
     */
    public function generateSjabloonReport($klantId, $testId)
    {
        try {
            $klant = \App\Models\Klant::findOrFail($klantId);
            $test = Inspanningstest::where('klant_id', $klantId)->findOrFail($testId);
            
            // Find matching sjabloon
            $sjabloon = SjabloonHelper::findMatchingTemplate($test->testtype, 'inspanningstest');
            
            if (!$sjabloon) {
                return redirect()->back()
                    ->with('error', 'Geen passend sjabloon gevonden voor testtype: ' . $test->testtype);
            }
            
            // Use SjablonenController to generate the report
            $sjablonenController = new \App\Http\Controllers\SjablonenController();
            return $sjablonenController->generateInspanningstestReport($test->id);
            
        } catch (\Exception $e) {
            \Log::error('Inspanningstest sjabloon report generation failed: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Er is een fout opgetreden bij het genereren van het rapport.');
        }
    }

    /**
     * Genereer AI-gedreven complete analyse van de inspanningstest
     */
    public function generateAIAdvice(Request $request): JsonResponse
    {
        try {
            \Log::info('🤖 GenerateAIAdvice aangeroepen');
            
            // Haal klant op voor geslacht en geboortedatum
            $klant = \App\Models\Klant::find($request->input('klant_id'));
            
            // Bereken leeftijd uit geboortedatum
            $leeftijd = 35; // Default
            if ($klant && $klant->geboortedatum) {
                try {
                    $leeftijd = \Carbon\Carbon::parse($klant->geboortedatum)->age;
                } catch (\Exception $e) {
                    \Log::warning('Kon leeftijd niet berekenen uit geboortedatum', ['error' => $e->getMessage()]);
                }
            }
            
            // Converteer geslacht naar male/female voor normen
            $geslacht = 'male'; // Default
            if ($klant && $klant->geslacht) {
                $geslachtLower = strtolower($klant->geslacht);
                if (in_array($geslachtLower, ['vrouw', 'female', 'woman'])) {
                    $geslacht = 'female';
                } else if (in_array($geslachtLower, ['man', 'male'])) {
                    $geslacht = 'male';
                }
            }
            
            // Verzamel complete testdata
            $validated = array_merge($request->all(), [
                'geslacht' => $geslacht,
                'leeftijd' => $leeftijd,
                'geboortedatum' => $klant ? $klant->geboortedatum : null,
            ]);

            // Check of AI enabled is
            if (!config('ai.enabled', true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'AI analyse is momenteel uitgeschakeld'
                ], 503);
            }

            $aiService = new AIAnalysisService();
            
            // Genereer complete analyse met de service
            $result = $aiService->generateCompleteAnalysis($validated);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'analysis' => $result['analysis'],
                    'metadata' => $result['metadata'] ?? []
                ]);
            } else {
                // Gebruik fallback analyse bij fout
                return response()->json([
                    'success' => true,
                    'analysis' => $result['fallback'] ?? 'Kon geen analyse genereren.',
                    'is_fallback' => true
                ]);
            }

            \Log::info('Complete AI analyse succesvol gegenereerd', [
                'testtype' => $validated['testtype'],
                'goals' => $validated['specifieke_doelstellingen'] ?? 'geen doelstellingen',
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'analysis' => $analysis,
                'type' => 'complete'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validatie fout: ' . $e->getMessage(),
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            \Log::error('Fout bij AI analyse generatie: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Er is een fout opgetreden bij het genereren van AI analyse. Probeer het opnieuw.'
            ], 500);
        }
    }
}
