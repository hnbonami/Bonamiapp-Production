<?php
namespace App\Http\Controllers;

use App\Models\Inspanningstest;
use App\Models\Klant;
use App\Helpers\SjabloonHelper;
use App\Services\AIAnalysisService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class InspanningstestController extends Controller {
    public function results($klantId, $testId)
    {
        $klant = \App\Models\Klant::findOrFail($klantId);
        $test = Inspanningstest::where('klant_id', $klantId)->findOrFail($testId);
        // Hier kun je straks berekeningen toevoegen
        $results = [];
        return view('inspanningstest.results', compact('klant', 'test', 'results', 'klantId'));
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

    public function store(Request $request, $klantId)
    {
    \Log::info('InspanningstestController@store called', ['klantId' => $klantId, 'input_keys' => array_keys($request->all())]);
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
            'aerobe_drempel_vermogen' => 'nullable|integer',
            'aerobe_drempel_hartslag' => 'nullable|integer',
            'anaerobe_drempel_vermogen' => 'nullable|integer',
            'anaerobe_drempel_hartslag' => 'nullable|integer',
            'besluit_lichaamssamenstelling' => 'nullable|string',
            'advies_aerobe_drempel' => 'nullable|string',
            'advies_anaerobe_drempel' => 'nullable|string',
            // Template kind for mapping to report templates (nullable)
            'template_kind' => 'nullable|string|in:inspanningstest_fietsen,inspanningstest_lopen,standaard_bikefit,professionele_bikefit,zadeldrukmeting,maatbepaling',
        ]);
        // Add the current user's ID to track who performed the test
        $data['user_id'] = auth()->id();
        $data['klant_id'] = $klantId;
        $test = Inspanningstest::create($data);
        return redirect()->route('inspanningstest.results', [
            'klant' => $klantId,
            'test' => $test->id
        ])->with('success', 'Inspanningstest opgeslagen.');
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
            'aerobe_drempel_vermogen' => 'nullable|integer',
            'aerobe_drempel_hartslag' => 'nullable|integer',
            'anaerobe_drempel_vermogen' => 'nullable|integer',
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
            $validated = $request->validate([
                'testtype' => 'required|string',
                'aerobe_drempel_vermogen' => 'nullable|numeric',
                'aerobe_drempel_hartslag' => 'nullable|numeric',
                'anaerobe_drempel_vermogen' => 'nullable|numeric', 
                'anaerobe_drempel_hartslag' => 'nullable|numeric',
                'specifieke_doelstellingen' => 'nullable|string',
                'lichaamsgewicht_kg' => 'nullable|numeric',
                'lichaamslengte_cm' => 'nullable|numeric',
                'leeftijd' => 'nullable|numeric',
                'maximale_hartslag_bpm' => 'nullable|numeric',
                'hartslag_rust_bpm' => 'nullable|numeric',
                'bmi' => 'nullable|numeric',
                'buikomtrek_cm' => 'nullable|numeric',
                'analyse_methode' => 'nullable|string',
                'testlocatie' => 'nullable|string',
                'besluit_lichaamssamenstelling' => 'nullable|string',
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
