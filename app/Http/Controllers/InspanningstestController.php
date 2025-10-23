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

            \Log::info('✅ INSPANNINGSTEST CREATED - ID: ' . $inspanningstest->id . ', Org ID: ' . $inspanningstest->organisatie_id);

            // BELANGRIJK: Correcte parameter naam moet 'test' zijn (niet 'inspanningstest')!
            return redirect()->route('inspanningstest.results', [
                'klant' => $klant->id, 
                'test' => $inspanningstest->id
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
        // Check of de test bij deze klant hoort
        if ($test->klant_id !== $klant->id) {
            abort(404);
        }
        
        // Laad testresultaten uit JSON kolom
        $testresultaten = [];
        if ($test->testresultaten) {
            $testresultaten = json_decode($test->testresultaten, true) ?? [];
        }
        
        // Geef $test door als $inspanningstest voor consistentie met de view
        $inspanningstest = $test;
        
        return view('inspanningstest.edit', compact('klant', 'inspanningstest', 'testresultaten'));
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
        \Log::info('✅ CORRECT: autoSaveEdit (EDIT) aangeroepen', [
            'test_id' => $test->id,
            'klant_id' => $klant->id,
            'testtype' => $request->testtype,
            'url' => $request->fullUrl(),
            'method' => $request->method()
        ]);

        try {
            // 🔧 COMPLETE FIX: Converteer lege strings naar null voor alle velden
            $updateData = [
                'datum' => $request->input('testdatum', now()->format('Y-m-d')),
                'testtype' => $request->input('testtype'),
                'specifieke_doelstellingen' => $request->input('specifieke_doelstellingen') ?: null,
                'lichaamslengte_cm' => $request->input('lichaamslengte_cm') ?: null,
                'lichaamsgewicht_kg' => $request->input('lichaamsgewicht_kg') ?: null,
                'bmi' => $request->input('bmi') ?: null,
                'vetpercentage' => $request->input('vetpercentage') ?: null,
                'hartslag_rust_bpm' => $request->input('hartslag_rust_bpm') ?: null,
                'maximale_hartslag_bpm' => $request->input('maximale_hartslag_bpm') ?: null,
                'buikomtrek_cm' => $request->input('buikomtrek_cm') ?: null,
                'slaapkwaliteit' => $request->input('slaapkwaliteit') ?: null,
                'eetlust' => $request->input('eetlust') ?: null,
                'gevoel_op_training' => $request->input('gevoel_op_training') ?: null,
                'stressniveau' => $request->input('stressniveau') ?: null,
                'gemiddelde_trainingstatus' => $request->input('gemiddelde_trainingstatus') ?: null,
                'training_dag_voor_test' => $request->input('training_dag_voor_test') ?: null,
                'training_2d_voor_test' => $request->input('training_2d_voor_test') ?: null,
                'testlocatie' => $request->input('testlocatie') ?: null,
                'protocol' => $request->input('protocol') ?: null,
                'startwattage' => $request->filled('startwattage') ? $request->input('startwattage') : null,
                'stappen_min' => $request->filled('stappen_min') ? $request->input('stappen_min') : null,
                'stappen_watt' => $request->filled('stappen_watt') ? $request->input('stappen_watt') : null,
                'weersomstandigheden' => $request->input('weersomstandigheden') ?: null,
                'analyse_methode' => $request->input('analyse_methode') ?: null,
                'dmax_modified_threshold' => $request->input('dmax_modified_threshold') ?: null,
                'aerobe_drempel_vermogen' => $request->input('aerobe_drempel_vermogen') ?: null,
                'aerobe_drempel_hartslag' => $request->input('aerobe_drempel_hartslag') ?: null,
                'anaerobe_drempel_vermogen' => $request->input('anaerobe_drempel_vermogen') ?: null,
                'anaerobe_drempel_hartslag' => $request->input('anaerobe_drempel_hartslag') ?: null,
                'complete_ai_analyse' => $request->input('complete_ai_analyse') ?: null,
                'zones_methode' => $request->input('zones_methode') ?: null,
                'zones_aantal' => $request->input('zones_aantal') ?: null,
                'zones_eenheid' => $request->input('zones_eenheid') ?: null,
                'trainingszones_data' => $request->input('trainingszones_data') ?: null,
            ];
            
            // 🔧 TESTRESULTATEN: ALTIJD opslaan als JSON (ook als leeg)
            if ($request->has('testresultaten') && is_array($request->testresultaten)) {
                $updateData['testresultaten'] = json_encode($request->testresultaten);
                \Log::info('📊 Testresultaten worden opgeslagen:', [
                    'aantal_rijen' => count($request->testresultaten),
                    'eerste_rij' => $request->testresultaten[0] ?? null,
                    'json_preview' => substr($updateData['testresultaten'], 0, 200)
                ]);
            } else {
                // Als geen testresultaten in request, behoud bestaande
                \Log::info('⚠️ Geen testresultaten in request - behoud bestaande');
            }
            
            // 📝 COMPLETE LOG: Log ALLE velden die naar database gaan
            \Log::info('💾 === COMPLETE UPDATE DATA ===', [
                'datum' => $updateData['datum'] ?? 'NULL',
                'testtype' => $updateData['testtype'] ?? 'NULL',
                'testlocatie' => $updateData['testlocatie'] ?? 'NULL',
                'protocol' => $updateData['protocol'] ?? 'NULL',
                'startwattage' => $updateData['startwattage'] ?? 'NULL',
                'stappen_min' => $updateData['stappen_min'] ?? 'NULL',
                'stappen_watt' => $updateData['stappen_watt'] ?? 'NULL',
                'analyse_methode' => $updateData['analyse_methode'] ?? 'NULL',
                'aerobe_drempel_vermogen' => $updateData['aerobe_drempel_vermogen'] ?? 'NULL',
                'anaerobe_drempel_vermogen' => $updateData['anaerobe_drempel_vermogen'] ?? 'NULL',
                'testresultaten_present' => isset($updateData['testresultaten']),
                'total_fields' => count($updateData)
            ]);
            
            // Update bestaande test
            $test->update($updateData);
            
            // 🔍 VERIFICATIE: Check of data echt is opgeslagen
            $test->refresh();
            \Log::info('✅ === VERIFICATIE NA DATABASE SAVE ===', [
                'protocol_in_db' => $test->protocol ?? 'NULL',
                'startwattage_in_db' => $test->startwattage ?? 'NULL',
                'stappen_min_in_db' => $test->stappen_min ?? 'NULL',
                'stappen_watt_in_db' => $test->stappen_watt ?? 'NULL',
                'testresultaten_length' => strlen($test->testresultaten ?? ''),
                'analyse_methode_in_db' => $test->analyse_methode ?? 'NULL'
            ]);

            return response()->json([
                'success' => true,
                'message' => "Auto-saved at " . now()->format('H:i:s'),
                'test_id' => $test->id
            ]);

        } catch (\Exception $e) {
            \Log::error('❌ Auto-save EDIT fout', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
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
        \Log::info('� WAARSCHUWING: autoSave (CREATE) aangeroepen maar zou autoSaveEdit (EDIT) moeten zijn!', [
            'klant_id' => $klant->id,
            'testtype' => $request->testtype,
            'has_testresultaten' => $request->has('testresultaten'),
            'url' => $request->fullUrl(),
            'method' => $request->method()
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
     * Genereer complete AI analyse voor inspanningstest (SUPER UITGEBREIDE FALLBACK!)
     */
    public function generateCompleteAIAnalysis(Request $request)
    {
        try {
            \Log::info('🤖 Complete AI analyse aangevraagd', [
                'request_data' => $request->all()
            ]);

            // Haal klant data op als beschikbaar
            $klant = null;
            if ($request->has('klant_id')) {
                $klant = \App\Models\Klant::find($request->klant_id);
            }

            // === SUPER UITGEBREIDE FALLBACK ANALYSE (IDENTIEK AAN CREATE) ===
            $testtype = $request->testtype ?? 'onbekend';
            $datum = $request->datum ?? date('Y-m-d');
            
            // Drempelwaarden
            $LT1_power = $request->aerobe_drempel_vermogen ?? 0;
            $LT2_power = $request->anaerobe_drempel_vermogen ?? 0;
            $LT1_HR = $request->aerobe_drempel_hartslag ?? 0;
            $LT2_HR = $request->anaerobe_drempel_hartslag ?? 0;
            
            // Klant gegevens
            $geslacht = $klant->geslacht ?? 'Man';
            $leeftijd = $klant ? \Carbon\Carbon::parse($klant->geboortedatum)->age : 35;
            
            $uitgebreideAnalyse = "🏃‍♂️ INSPANNINGSTEST ANALYSE\n\n";
            $uitgebreideAnalyse .= "Geautomatiseerde analyse op basis van uw testresultaten\n\n";
            $uitgebreideAnalyse .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
            
            // TESTOVERZICHT
            $uitgebreideAnalyse .= "📊 TESTOVERZICHT\n\n";
            $uitgebreideAnalyse .= "• Testtype: " . ucfirst($testtype) . "\n";
            $uitgebreideAnalyse .= "• Datum: " . date('d-m-Y', strtotime($datum)) . "\n";
            $uitgebreideAnalyse .= "• Locatie: " . ($request->testlocatie ?? 'Bonami sportmedisch centrum') . "\n";
            $uitgebreideAnalyse .= "• Analyse methode: " . ($request->analyse_methode == 'dmax_modified' ? 'Dmax modified' : 'Lactaat steady state') . "\n\n";
            $uitgebreideAnalyse .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
            
            // ATLET PROFIEL
            $uitgebreideAnalyse .= "👤 ATLET PROFIEL\n\n";
            $uitgebreideAnalyse .= "• Leeftijd: {$leeftijd} jaar\n";
            $uitgebreideAnalyse .= "• Geslacht: {$geslacht}\n\n";
            
            if ($request->specifieke_doelstellingen) {
                $uitgebreideAnalyse .= "Uw doelstellingen:\n";
                $uitgebreideAnalyse .= $request->specifieke_doelstellingen . "\n\n";
            } else {
                $uitgebreideAnalyse .= "Algemene fitheid verbetering\n\n";
            }
            
            $uitgebreideAnalyse .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
            
            // DREMPELWAARDEN
            $uitgebreideAnalyse .= "🎯 GEMETEN DREMPELWAARDEN\n\n";
            $uitgebreideAnalyse .= "Aërobe Drempel (LT1)\n\n";
            
            if ($testtype == 'looptest') {
                $uitgebreideAnalyse .= "• Snelheid: " . number_format($LT1_power, 1) . " km/u\n";
            } else {
                $uitgebreideAnalyse .= "• Vermogen/Snelheid: " . number_format($LT1_power, 0) . " Watt\n";
            }
            $uitgebreideAnalyse .= "• Hartslag: " . number_format($LT1_HR, 0) . " bpm\n\n";
            
            $uitgebreideAnalyse .= "Anaërobe Drempel (LT2)\n\n";
            if ($testtype == 'looptest') {
                $uitgebreideAnalyse .= "• Snelheid: " . number_format($LT2_power, 1) . " km/u\n";
            } else {
                $uitgebreideAnalyse .= "• Vermogen/Snelheid: " . number_format($LT2_power, 0) . " Watt\n";
            }
            $uitgebreideAnalyse .= "• Hartslag: " . number_format($LT2_HR, 0) . " bpm\n\n";
            
            $uitgebreideAnalyse .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
            
            // TRAININGSADVIES
            $uitgebreideAnalyse .= "💪 TRAININGSADVIES\n\n";
            $uitgebreideAnalyse .= "Polarized Training (80/20 principe)\n\n";
            $uitgebreideAnalyse .= "Voor optimale resultaten raden we het 80/20 trainingsmodel aan:\n\n";
            
            $uitgebreideAnalyse .= "1. Aërobe Basistraining (80 procent van trainingstijd)\n\n";
            if ($testtype == 'looptest') {
                $uitgebreideAnalyse .= "• Train onder de aërobe drempel (LT1): < " . number_format($LT1_power, 1) . " km/u of < " . number_format($LT1_HR, 0) . " bpm\n";
            } else {
                $uitgebreideAnalyse .= "• Train onder de aërobe drempel (LT1): < " . number_format($LT1_power, 0) . " Watt of < " . number_format($LT1_HR, 0) . " bpm\n";
            }
            $uitgebreideAnalyse .= "• Dit voelt als een comfortabel tempo waar je nog makkelijk kunt praten\n";
            $uitgebreideAnalyse .= "• Duur: 60-120 minuten per sessie\n";
            $uitgebreideAnalyse .= "• Frequentie: 4-5x per week\n";
            $uitgebreideAnalyse .= "• Effect: Verbetert vetstofwisseling, uithoudingsvermogen en aerobe capaciteit\n\n";
            
            $uitgebreideAnalyse .= "2. Drempel/Interval Training (15 procent van trainingstijd)\n\n";
            if ($testtype == 'looptest') {
                $uitgebreideAnalyse .= "• Train rond de anaërobe drempel (LT2): " . number_format($LT2_power, 1) . " km/u of " . number_format($LT2_HR, 0) . " bpm\n";
            } else {
                $uitgebreideAnalyse .= "• Train rond de anaërobe drempel (LT2): " . number_format($LT2_power, 0) . " Watt of " . number_format($LT2_HR, 0) . " bpm\n";
            }
            $uitgebreideAnalyse .= "• Dit voelt als een zwaar maar houdbaar tempo\n";
            $uitgebreideAnalyse .= "• Voorbeelden:\n";
            $uitgebreideAnalyse .= "  ◦ 4-6x 5 minuten bij LT2 met 2-3 min rust\n";
            $uitgebreideAnalyse .= "  ◦ 3x 10 minuten bij LT2 met 5 min rust\n";
            $uitgebreideAnalyse .= "  ◦ 2x 20 minuten bij LT2 met 10 min rust\n";
            $uitgebreideAnalyse .= "• Frequentie: 1-2x per week\n";
            $uitgebreideAnalyse .= "• Effect: Verhoogt de anaërobe drempel en VO2max\n\n";
            
            $uitgebreideAnalyse .= "3. Herstel en Mobiliteit (5 procent van trainingstijd)\n\n";
            $uitgebreideAnalyse .= "• Zeer lage intensiteit of complete rust\n";
            $uitgebreideAnalyse .= "• Yoga, stretching, massage\n";
            $uitgebreideAnalyse .= "• Slaap: minimaal 7-8 uur per nacht\n\n";
            
            $uitgebreideAnalyse .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
            
            // SPECIFIEK ADVIES
            if ($request->specifieke_doelstellingen) {
                $uitgebreideAnalyse .= "🎯 SPECIFIEK ADVIES VOOR UW DOELSTELLINGEN\n\n";
                $uitgebreideAnalyse .= $request->specifieke_doelstellingen . "\n\n";
                $uitgebreideAnalyse .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
            }
            
            // PROGRESSIE
            $uitgebreideAnalyse .= "📈 PROGRESSIE EN HERSTEST\n\n";
            $uitgebreideAnalyse .= "Verwachte verbeteringen (8-12 weken consistent trainen):\n\n";
            $uitgebreideAnalyse .= "• Aërobe drempel: 5-10 procent verbetering\n";
            $uitgebreideAnalyse .= "• Anaërobe drempel: 3-8 procent verbetering\n";
            $uitgebreideAnalyse .= "• Verbeterde loopeconomie of fietsefficiency\n";
            $uitgebreideAnalyse .= "• Lagere hartslag bij zelfde intensiteit (betere efficiency)\n";
            $uitgebreideAnalyse .= "• Sneller herstel tussen inspanningen\n\n";
            
            $uitgebreideAnalyse .= "Wanneer hertesten?\n\n";
            $uitgebreideAnalyse .= "• Na 8-12 weken gestructureerd trainen\n";
            $uitgebreideAnalyse .= "• Bij plateau in prestaties\n";
            $uitgebreideAnalyse .= "• Voor belangrijke wedstrijden of events\n";
            $uitgebreideAnalyse .= "• Na een trainingsperiode wijziging\n\n";
            
            $uitgebreideAnalyse .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
            
            // METRICS
            $uitgebreideAnalyse .= "📊 TE MONITOREN METRICS\n\n";
            $uitgebreideAnalyse .= "Volg deze waarden om progressie te zien:\n\n";
            $uitgebreideAnalyse .= "✅ Hartslag bij vaste trainingsintensiteit (moet dalen)\n";
            $uitgebreideAnalyse .= "✅ Gemiddelde snelheid/vermogen (moet stijgen)\n";
            $uitgebreideAnalyse .= "✅ Herstellijden tussen intervallen (moet verkorten)\n";
            $uitgebreideAnalyse .= "✅ Algemeen energieniveau en slaapkwaliteit\n";
            $uitgebreideAnalyse .= "✅ Watt/kg ratio (moet stijgen)\n\n";
            
            $uitgebreideAnalyse .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
            $uitgebreideAnalyse .= "💡 Voor een nog uitgebreidere AI-gegenereerde analyse met meer specifieke adviezen, voeg OpenAI credits toe aan uw account.\n";

            return response()->json([
                'success' => true,
                'analysis' => $uitgebreideAnalyse,
                'method' => 'fallback_detailed_original'
            ]);

        } catch (\Exception $e) {
            \Log::error('❌ Complete AI analyse fout: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Fout bij genereren AI analyse: ' . $e->getMessage()
            ], 500);
        }
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
    
    /**
     * Organisatie gefilterde index voor inspanningstesten
     */
    public function index(Request $request, Klant $klant)
    {
        // Controleer of klant bij huidige organisatie hoort
        if ($klant->organisatie_id !== auth()->user()->organisatie_id) {
            abort(403, 'Geen toegang tot deze klant');
        }
        
        // Haal alle inspanningstesten voor deze klant op, gesorteerd op datum
        $tests = Inspanningstest::where('klant_id', $klant->id)
            ->orderBy('datum', 'desc')
            ->get();
        
        return view('inspanningstest.index', compact('klant', 'tests'));
    }
}
