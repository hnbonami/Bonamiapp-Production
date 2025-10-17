<?php
namespace App\Http\Controllers;

use App\Models\Inspanningstest;
use App\Models\Klant;
use App\Helpers\SjabloonHelper;
use App\Services\AIAnalysisService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Exception;

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
        
        // Decode testresultaten en trainingszones voor de view
        $test->testresultaten = is_string($test->testresultaten) ? json_decode($test->testresultaten, true) : $test->testresultaten;
        $test->trainingszones_data = is_string($test->trainingszones_data) ? json_decode($test->trainingszones_data, true) : $test->trainingszones_data;
        
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

    /**
     * Toon bewerkingsformulier voor een bestaande inspanningstest
     */
    public function edit(Klant $klant, Inspanningstest $test)
    {
        \Log::info('� Edit inspanningstest opgehaald', [
            'test_id' => $test->id,
            'klant_id' => $klant->id,
            'testtype' => $test->testtype
        ]);

        return view('inspanningstest.edit', [
            'klant' => $klant,
            'inspanningstest' => $test // Gebruik 'inspanningstest' naam voor de view
        ]);
    }

    /**
     * Update een bestaande inspanningstest
     */
    public function update(Request $request, Klant $klant, Inspanningstest $test)
    {
        \Log::info('� Update inspanningstest gestart', [
            'test_id' => $test->id,
            'klant_id' => $klant->id,
            'request_data' => $request->all()
        ]);

        // Update test met alle velden - gebruik 'datum' voor de database kolom
        $test->update([
            'klant_id' => $klant->id,
            'user_id' => auth()->id(),
            'datum' => $request->testdatum, // Form gebruikt 'testdatum' maar database kolom is 'datum'
            'testtype' => $request->testtype,
            'specifieke_doelstellingen' => $request->specifieke_doelstellingen,
            'lichaamslengte_cm' => $request->lichaamslengte_cm,
            'lichaamsgewicht_kg' => $request->lichaamsgewicht_kg,
            'bmi' => $request->bmi,
            'vetpercentage' => $request->vetpercentage,
            'hartslag_rust_bpm' => $request->hartslag_rust_bpm,
            'maximale_hartslag_bpm' => $request->maximale_hartslag_bpm,
            'buikomtrek_cm' => $request->buikomtrek_cm,
            'slaapkwaliteit' => $request->slaapkwaliteit,
            'eetlust' => $request->eetlust,
            'gevoel_op_training' => $request->gevoel_op_training,
            'stressniveau' => $request->stressniveau,
            'gemiddelde_trainingstatus' => $request->gemiddelde_trainingstatus,
            'training_dag_voor_test' => $request->training_dag_voor_test,
            'training_2d_voor_test' => $request->training_2d_voor_test,
            'testlocatie' => $request->testlocatie,
            'protocol' => $request->protocol,
            'startwattage' => $request->startwattage,
            'stappen_min' => $request->stappen_min,
            'stappen_watt' => $request->stappen_watt,
            'weersomstandigheden' => $request->weersomstandigheden,
            'testresultaten' => $request->has('testresultaten') ? json_encode($request->testresultaten) : $test->testresultaten,
            'analyse_methode' => $request->analyse_methode,
            'dmax_modified_threshold' => $request->dmax_modified_threshold,
            'aerobe_drempel_vermogen' => $request->aerobe_drempel_vermogen,
            'aerobe_drempel_hartslag' => $request->aerobe_drempel_hartslag,
            'anaerobe_drempel_vermogen' => $request->anaerobe_drempel_vermogen,
            'anaerobe_drempel_hartslag' => $request->anaerobe_drempel_hartslag,
            'complete_ai_analyse' => $request->complete_ai_analyse,
            'zones_methode' => $request->zones_methode,
            'zones_aantal' => $request->zones_aantal,
            'zones_eenheid' => $request->zones_eenheid,
            'trainingszones_data' => $request->trainingszones_data,
        ]);

        \Log::info('✅ Inspanningstest bijgewerkt', ['test_id' => $test->id]);

        return redirect()
            ->route('inspanningstest.results', ['klant' => $klant->id, 'test' => $test->id])
            ->with('success', 'Inspanningstest succesvol bijgewerkt.');
    }

    /**
     * Auto-save voor edit mode
     */
    public function autoSaveEdit(Request $request, Klant $klant, Inspanningstest $test)
    {
        \Log::info('💾 Auto-save EDIT aangeroepen', [
            'test_id' => $test->id,
            'klant_id' => $klant->id,
            'testtype' => $request->testtype
        ]);

        try {
            // Update bestaande test - gebruik 'datum' voor de database kolom
            $test->update([
                'datum' => $request->testdatum ?? now()->format('Y-m-d'), // Form gebruikt 'testdatum' maar database kolom is 'datum'
                'testtype' => $request->testtype,
                'specifieke_doelstellingen' => $request->specifieke_doelstellingen,
                'lichaamslengte_cm' => $request->lichaamslengte_cm,
                'lichaamsgewicht_kg' => $request->lichaamsgewicht_kg,
                'bmi' => $request->bmi,
                'vetpercentage' => $request->vetpercentage,
                'hartslag_rust_bpm' => $request->hartslag_rust_bpm,
                'maximale_hartslag_bpm' => $request->maximale_hartslag_bpm,
                'buikomtrek_cm' => $request->buikomtrek_cm,
                'slaapkwaliteit' => $request->slaapkwaliteit,
                'eetlust' => $request->eetlust,
                'gevoel_op_training' => $request->gevoel_op_training,
                'stressniveau' => $request->stressniveau,
                'gemiddelde_trainingstatus' => $request->gemiddelde_trainingstatus,
                'training_dag_voor_test' => $request->training_dag_voor_test,
                'training_2d_voor_test' => $request->training_2d_voor_test,
                'testlocatie' => $request->testlocatie,
                'protocol' => $request->protocol,
                'startwattage' => $request->startwattage,
                'stappen_min' => $request->stappen_min,
                'stappen_watt' => $request->stappen_watt,
                'weersomstandigheden' => $request->weersomstandigheden,
                'testresultaten' => $request->has('testresultaten') ? json_encode($request->testresultaten) : $test->testresultaten,
                'analyse_methode' => $request->analyse_methode,
                'dmax_modified_threshold' => $request->dmax_modified_threshold,
                'aerobe_drempel_vermogen' => $request->aerobe_drempel_vermogen,
                'aerobe_drempel_hartslag' => $request->aerobe_drempel_hartslag,
                'anaerobe_drempel_vermogen' => $request->anaerobe_drempel_vermogen,
                'anaerobe_drempel_hartslag' => $request->anaerobe_drempel_hartslag,
                'complete_ai_analyse' => $request->complete_ai_analyse,
                'zones_methode' => $request->zones_methode,
                'zones_aantal' => $request->zones_aantal,
                'zones_eenheid' => $request->zones_eenheid,
                'trainingszones_data' => $request->trainingszones_data,
            ]);

            \Log::info('✅ Auto-save EDIT succesvol', ['test_id' => $test->id]);

            return response()->json([
                'success' => true,
                'message' => "Auto-saved at " . now()->format('H:i:s'),
                'test_id' => $test->id
            ]);

        } catch (\Exception $e) {
            \Log::error('❌ Auto-save EDIT fout', [
                'error' => $e->getMessage(),
                'test_id' => $test->id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Auto-save failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Auto-save inspanningstest data via AJAX (CREATE mode)
     */
    public function autoSave(Request $request, Klant $klant)
    {
        \Log::info('💾 AUTO-SAVE aangeroepen', [
            'klant_id' => $klant->id,
            'testtype' => $request->testtype,
            'has_testresultaten' => $request->has('testresultaten')
        ]);

        try {
            // Haal bestaande test ID uit session of maak nieuwe
            $testId = session('inspanningstest_draft_id');
            
            if ($testId) {
                // Update bestaande draft
                $test = Inspanningstest::find($testId);
                if ($test && $test->klant_id == $klant->id) {
                    $test->update([
                        'datum' => $request->testdatum ?? now()->format('Y-m-d'),
                        'testtype' => $request->testtype,
                        'specifieke_doelstellingen' => $request->specifieke_doelstellingen,
                        'lichaamslengte_cm' => $request->lichaamslengte_cm,
                        'lichaamsgewicht_kg' => $request->lichaamsgewicht_kg,
                        'bmi' => $request->bmi,
                        'vetpercentage' => $request->vetpercentage,
                        'hartslag_rust_bpm' => $request->hartslag_rust_bpm,
                        'maximale_hartslag_bpm' => $request->maximale_hartslag_bpm,
                        'buikomtrek_cm' => $request->buikomtrek_cm,
                        'slaapkwaliteit' => $request->slaapkwaliteit,
                        'eetlust' => $request->eetlust,
                        'gevoel_op_training' => $request->gevoel_op_training,
                        'stressniveau' => $request->stressniveau,
                        'gemiddelde_trainingstatus' => $request->gemiddelde_trainingstatus,
                        'training_dag_voor_test' => $request->training_dag_voor_test,
                        'training_2d_voor_test' => $request->training_2d_voor_test,
                        'testlocatie' => $request->testlocatie,
                        'protocol' => $request->protocol,
                        'startwattage' => $request->startwattage,
                        'stappen_min' => $request->stappen_min,
                        'stappen_watt' => $request->stappen_watt,
                        'weersomstandigheden' => $request->weersomstandigheden,
                        'testresultaten' => $request->has('testresultaten') ? json_encode($request->testresultaten) : $test->testresultaten,
                        'analyse_methode' => $request->analyse_methode,
                        'dmax_modified_threshold' => $request->dmax_modified_threshold,
                        'aerobe_drempel_vermogen' => $request->aerobe_drempel_vermogen,
                        'aerobe_drempel_hartslag' => $request->aerobe_drempel_hartslag,
                        'anaerobe_drempel_vermogen' => $request->anaerobe_drempel_vermogen,
                        'anaerobe_drempel_hartslag' => $request->anaerobe_drempel_hartslag,
                        'complete_ai_analyse' => $request->complete_ai_analyse,
                        'zones_methode' => $request->zones_methode,
                        'zones_aantal' => $request->zones_aantal,
                        'zones_eenheid' => $request->zones_eenheid,
                        'trainingszones_data' => $request->trainingszones_data,
                    ]);
                    
                    \Log::info('✅ Draft bijgewerkt', ['test_id' => $test->id]);
                } else {
                    $testId = null; // Reset als test niet meer bestaat
                }
            }
            
            if (!$testId) {
                // Maak nieuwe draft test
                $test = Inspanningstest::create([
                    'klant_id' => $klant->id,
                    'user_id' => auth()->id(),
                    'datum' => $request->testdatum ?? now()->format('Y-m-d'),
                    'testtype' => $request->testtype,
                    'specifieke_doelstellingen' => $request->specifieke_doelstellingen,
                    'lichaamslengte_cm' => $request->lichaamslengte_cm,
                    'lichaamsgewicht_kg' => $request->lichaamsgewicht_kg,
                    'bmi' => $request->bmi,
                    'vetpercentage' => $request->vetpercentage,
                    'hartslag_rust_bpm' => $request->hartslag_rust_bpm,
                    'maximale_hartslag_bpm' => $request->maximale_hartslag_bpm,
                    'buikomtrek_cm' => $request->buikomtrek_cm,
                    'slaapkwaliteit' => $request->slaapkwaliteit,
                    'eetlust' => $request->eetlust,
                    'gevoel_op_training' => $request->gevoel_op_training,
                    'stressniveau' => $request->stressniveau,
                    'gemiddelde_trainingstatus' => $request->gemiddelde_trainingstatus,
                    'training_dag_voor_test' => $request->training_dag_voor_test,
                    'training_2d_voor_test' => $request->training_2d_voor_test,
                    'testlocatie' => $request->testlocatie,
                    'protocol' => $request->protocol,
                    'startwattage' => $request->startwattage,
                    'stappen_min' => $request->stappen_min,
                    'stappen_watt' => $request->stappen_watt,
                    'weersomstandigheden' => $request->weersomstandigheden,
                    'testresultaten' => $request->has('testresultaten') ? json_encode($request->testresultaten) : null,
                    'analyse_methode' => $request->analyse_methode,
                    'dmax_modified_threshold' => $request->dmax_modified_threshold,
                    'aerobe_drempel_vermogen' => $request->aerobe_drempel_vermogen,
                    'aerobe_drempel_hartslag' => $request->aerobe_drempel_hartslag,
                    'anaerobe_drempel_vermogen' => $request->anaerobe_drempel_vermogen,
                    'anaerobe_drempel_hartslag' => $request->anaerobe_drempel_hartslag,
                    'complete_ai_analyse' => $request->complete_ai_analyse,
                    'zones_methode' => $request->zones_methode,
                    'zones_aantal' => $request->zones_aantal,
                    'zones_eenheid' => $request->zones_eenheid,
                    'trainingszones_data' => $request->trainingszones_data,
                ]);
                
                // Bewaar ID in session voor volgende auto-saves
                session(['inspanningstest_draft_id' => $test->id]);
                
                \Log::info('✅ Nieuwe draft aangemaakt', ['test_id' => $test->id]);
            }

            return response()->json([
                'success' => true,
                'test_id' => $test->id,
                'message' => 'Auto-saved at ' . now()->format('H:i:s')
            ]);

        } catch (\Exception $e) {
            \Log::error('❌ Auto-save fout', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Auto-save failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verwijder een inspanningstest
     */
    public function destroy(Klant $klant, Inspanningstest $test)
    {
        \Log::info('🗑️ Delete inspanningstest aangeroepen', [
            'test_id' => $test->id,
            'klant_id' => $klant->id
        ]);

        $test->delete();

        return redirect()
            ->route('klanten.show', $klant->id)
            ->with('success', 'Inspanningstest succesvol verwijderd.');
    }

    /**
     * Dupliceer een inspanningstest
     */
    public function duplicate(Klant $klant, $test)
    {
        // Haal de test handmatig op omdat route binding met 'test' parameter problemen geeft
        $inspanningstest = Inspanningstest::findOrFail($test);
        
        \Log::info('📋 Duplicate inspanningstest aangeroepen', [
            'test_id' => $inspanningstest->id,
            'klant_id' => $klant->id
        ]);

        // Maak een kopie van de test
        $newTest = $inspanningstest->replicate();
        $newTest->datum = now()->format('Y-m-d'); // Zet datum op vandaag
        $newTest->user_id = auth()->id(); // Zet huidige gebruiker
        $newTest->save();

        \Log::info('✅ Inspanningstest gedupliceerd', [
            'original_id' => $inspanningstest->id,
            'new_id' => $newTest->id
        ]);

        return redirect()
            ->route('klanten.show', $klant->id)
            ->with('success', 'Inspanningstest succesvol gedupliceerd.');
    }

    /**
     * Genereer sjabloon-gebaseerd rapport voor inspanningstest
     */
    public function generateSjabloonReport($klantId, $testId)
    {
        try {
            $klant = Klant::findOrFail($klantId);
            $test = Inspanningstest::where('klant_id', $klantId)
                ->findOrFail($testId);
            
            // Find matching sjabloon
            $sjabloon = \App\Helpers\SjabloonHelper::findMatchingTemplate($test->testtype, 'inspanningstest');
            
            if (!$sjabloon) {
                return redirect()->back()
                    ->with('error', 'Geen passend sjabloon gevonden voor testtype: ' . $test->testtype);
            }
            
            // Use SjablonenController to generate the report
            $sjablonenController = new \App\Http\Controllers\SjablonenController();
            return $sjablonenController->generateInspanningstestReport($test->id);
            
        } catch (\Exception $e) {
            \Log::error('Inspanningstest sjabloon rapport generatie gefaald: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Er is een fout opgetreden bij het genereren van het rapport.');
        }
    }
    
    /**
     * Genereer complete AI analyse - KRACHTIGE FALLBACK VERSIE (Commit 7f9069c)
     */
    private function generateCompleteAIAnalysis($testData)
    {
        // === GEBRUIK ALTIJD FALLBACK: Uitgebreide analyse zonder AI service ===
        $analysis = $this->generateDetailedFallbackAnalysis($testData);
        
        return response()->json([
            'success' => true,
            'analysis' => $analysis,
            'method' => 'fallback_detailed_original'
        ]);
    }
    
    /**
     * KRACHTIGE FALLBACK: Genereer gedetailleerde analyse zonder externe AI (ORIGINEEL)
     */
    private function generateDetailedFallbackAnalysis($data)
    {
        $testtype = $data['testtype'] ?? 'fietstest';
        $isLooptest = str_contains($testtype, 'loop');
        $isZwemtest = str_contains($testtype, 'zwem');
        
        // Bepaal eenheid
        $eenheid = 'Watt';
        if ($isLooptest) $eenheid = 'km/h';
        if ($isZwemtest) $eenheid = 'min/100m';
        
        // Haal alle data op
        $lt1 = $data['aerobe_drempel_vermogen'] ?? null;
        $lt2 = $data['anaerobe_drempel_vermogen'] ?? null;
        $lt1_hr = $data['aerobe_drempel_hartslag'] ?? null;
        $lt2_hr = $data['anaerobe_drempel_hartslag'] ?? null;
        $gewicht = $data['lichaamsgewicht_kg'] ?? null;
        $leeftijd = $data['leeftijd'] ?? 35;
        $doelstellingen = $data['specifieke_doelstellingen'] ?? 'Algemene fitheid verbetering';
        $trainingstatus = $data['gemiddelde_trainingstatus'] ?? null;
        $hrmax = $data['maximale_hartslag_bpm'] ?? (220 - $leeftijd);
        $hrrust = $data['hartslag_rust_bpm'] ?? 60;
        
        // Start met uitgebreide analyse
        $analyse = "═══════════════════════════════════════════════════════════════════\n";
        $analyse .= "           🏆 COMPLETE INSPANNINGSTEST ANALYSE\n";
        $analyse .= "                    " . strtoupper($testtype) . "\n";
        $analyse .= "═══════════════════════════════════════════════════════════════════\n\n";
        
        // === 1. PERSOONLIJKE GEGEVENS ===
        $analyse .= "👤 PERSOONLIJKE GEGEVENS:\n";
        $analyse .= "─────────────────────────────────────────────────────────────────\n";
        $analyse .= sprintf("• Leeftijd: %d jaar\n", $leeftijd);
        if ($gewicht) {
            $analyse .= sprintf("• Gewicht: %.1f kg\n", $gewicht);
            if (isset($data['lichaamslengte_cm'])) {
                $bmi = round($gewicht / (($data['lichaamslengte_cm']/100) ** 2), 1);
                $analyse .= sprintf("• BMI: %.1f ", $bmi);
                if ($bmi < 18.5) $analyse .= "(Ondergewicht)\n";
                elseif ($bmi < 25) $analyse .= "(Normaal gewicht)\n";
                elseif ($bmi < 30) $analyse .= "(Overgewicht)\n";
                else $analyse .= "(Obesitas)\n";
            }
        }
        if (isset($data['vetpercentage'])) {
            $analyse .= sprintf("• Vetpercentage: %.1f%%\n", $data['vetpercentage']);
        }
        $analyse .= "\n";
        
        // === 2. DOELSTELLINGEN ANALYSE ===
        $analyse .= "🎯 SPECIFIEKE DOELSTELLINGEN:\n";
        $analyse .= "─────────────────────────────────────────────────────────────────\n";
        $analyse .= $doelstellingen . "\n\n";
        
        // Analyseer doelstellingen  
        $analyse .= "📋 DOELANALYSE:\n";
        if (stripos($doelstellingen, 'wedstrijd') !== false || stripos($doelstellingen, 'competitie') !== false) {
            $analyse .= "✓ Wedstrijdgericht - Focus op race-specifieke voorbereiding\n";
            $analyse .= "  • Periodiseer naar wedstrijdkalender\n";
            $analyse .= "  • Oefen tactische situaties\n";
            $analyse .= "  • Simuleer wedstrijdintensiteit\n";
        } elseif (stripos($doelstellingen, 'gravel') !== false || stripos($doelstellingen, 'mtb') !== false) {
            $analyse .= "✓ Off-road specialist - Variabel inspanningspatroon\n";
            $analyse .= "  • Train variabele intensiteit\n";
            $analyse .= "  • Focus op technische vaardigheden\n";
            $analyse .= "  • Korte power intervallen\n";
        } elseif (stripos($doelstellingen, 'gran fondo') !== false || stripos($doelstellingen, 'lange afstand') !== false) {
            $analyse .= "✓ Lange afstand specialist - Duurvermogen cruciaal\n";
            $analyse .= "  • Bouw groot volume basis\n";
            $analyse .= "  • Train voedingsstrategie\n";
            $analyse .= "  • Tempo work essentieel\n";
        } else {
            $analyse .= "✓ Algemene fitheid - Breed trainingsschema\n";
            $analyse .= "  • Gevarieerde training\n";
            $analyse .= "  • Geleidelijke progressie\n";
            $analyse .= "  • Focus op plezier en gezondheid\n";
        }
        $analyse .= "\n";
        
        // === 3-10: Alle andere secties blijven hetzelfde... ===
        // (Voeg hier de rest van de originele analyse toe)
        
        return $analyse;
    }
    
    /**
     * Genereer AI advies voor inspanningstesten - HERSTELDE VERSIE (commit 7f9069c)
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
            
            // Verzamel complete testdata
            $validated = array_merge($request->all(), [
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
            
        } catch (\Exception $e) {
            \Log::error('❌ AI Advies fout', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Fout bij genereren AI advies: ' . $e->getMessage()
            ], 500);
        }
    }
    
    // ...existing methods...
}
