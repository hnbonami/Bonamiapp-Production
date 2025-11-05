<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\EmailIntegrationService;
use App\Models\Klant;
use App\Models\Bikefit;
use App\Services\BikefitReportGenerator;
use App\Helpers\SjabloonHelper;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\BikefitCustomResult;
use Exception;

class BikefitController extends Controller
{
    public function create($klantId)
    {
        $klant = Klant::findOrFail($klantId);
        $templates = \App\Models\Template::all();
        // Mapping van testtype naar sjabloon-id
        $templateMap = [];
        foreach ($templates as $template) {
            $templateMap[strtolower($template->type)] = $template->id;
        }
        $defaultMobility = [
            'straight_leg_raise_links' => '_',
            'straight_leg_raise_rechts' => '_',
            'knieflexie_links' => '_',
            'knieflexie_rechts' => '_',
            'heup_endorotatie_links' => '_',
            'heup_endorotatie_rechts' => '_',
            'heup_exorotatie_links' => '_',
            'heup_exorotatie_rechts' => '_',
            'enkeldorsiflexie_links' => '_',
            'enkeldorsiflexie_rechts' => '_',
            'one_leg_squat_links' => '_',
            'one_leg_squat_rechts' => '_',
        ];
        
        // Voor create is er geen testzadel, dus maken we een lege variabele
        $testzadel = null;
        
        return view('bikefit.create', compact('klant', 'templates', 'templateMap', 'defaultMobility', 'testzadel'));
    }

    public function store(Request $request, Klant $klant)
    {
        // Validate the request
        $validated = $request->validate([
            'datum' => 'required|date',
            'testtype' => 'required|string',
            'lengte_cm' => 'nullable|numeric',
            'binnenbeenlengte_cm' => 'nullable|numeric',
            'armlengte_cm' => 'nullable|numeric',
            'romplengte_cm' => 'nullable|numeric',
            'schouderbreedte_cm' => 'nullable|numeric',
            'zadel_trapas_hoek' => 'nullable|numeric',
            'zadel_trapas_afstand' => 'nullable|numeric',
            'stuur_trapas_hoek' => 'nullable|numeric',
            'stuur_trapas_afstand' => 'nullable|numeric',
            'aanpassingen_zadel' => 'nullable|numeric',
            'aanpassingen_setback' => 'nullable|numeric',
            'aanpassingen_reach' => 'nullable|numeric',
            'aanpassingen_drop' => 'nullable|numeric',
            'aanpassingen_stuurpen_aan' => 'nullable|in:0,1',
            'aanpassingen_stuurpen_pre' => 'nullable|numeric',
            'aanpassingen_stuurpen_post' => 'nullable|numeric',
            'type_zadel' => 'nullable|string',
            'zadeltil' => 'nullable|numeric|between:-90,90|regex:/^\-?\d{1,2}(\.\d{1,2})?$/',
            'zadelbreedte' => 'nullable|numeric',
            'rotatie_aanpassingen' => 'nullable|string',
            'inclinatie_aanpassingen' => 'nullable|string',
            'ophoging_li' => 'nullable|numeric',
            'ophoging_re' => 'nullable|numeric',
            'opmerkingen' => 'nullable|string',
            'interne_opmerkingen' => 'nullable|string',
            // New fields
            'fietsmerk' => 'nullable|string',
            'kadermaat' => 'nullable|string',
            'bouwjaar' => 'nullable|integer',
            'algemene_klachten' => 'nullable|string',
            'beenlengteverschil' => 'nullable|in:0,1',
            'beenlengteverschil_cm' => 'nullable|string',
            'lenigheid_hamstrings' => 'nullable|string',
            'steunzolen' => 'nullable|in:0,1',
            'steunzolen_reden' => 'nullable|string',
            // Voetmeting
            'schoenmaat' => 'nullable|integer|min:35|max:50',
            'voetbreedte' => 'nullable|numeric|min:6|max:13',
            'voetpositie' => 'nullable|in:neutraal,pronatie,supinatie',
            // Template selection for report rendering (nullable)
            'template_kind' => 'nullable|string|in:inspanningstest_fietsen,inspanningstest_lopen,standaard_bikefit,professionele_bikefit,zadeldrukmeting,maatbepaling',
            // Type fitting
            'type_fitting' => 'nullable|string',
            // Zadellengte center-top
            'zadel_lengte_center_top' => 'nullable|numeric',
            // Functionele mobiliteit
            'straight_leg_raise_links' => 'nullable|string',
            'straight_leg_raise_rechts' => 'nullable|string',
            'knieflexie_links' => 'nullable|string',
            'knieflexie_rechts' => 'nullable|string',
            'heup_endorotatie_links' => 'nullable|string',
            'heup_endorotatie_rechts' => 'nullable|string',
            'heup_exorotatie_links' => 'nullable|string',
            'heup_exorotatie_rechts' => 'nullable|string',
            'enkeldorsiflexie_links' => 'nullable|string',
            'enkeldorsiflexie_rechts' => 'nullable|string',
            'one_leg_squat_links' => 'nullable|string',
            'one_leg_squat_rechts' => 'nullable|string',
            // nieuw_testzadel field
            'nieuw_testzadel' => 'nullable|string|max:255',
            'type_fiets' => 'nullable|string',
            'frametype' => 'nullable|string',
        ]);
        if (empty($validated['datum'])) {
            $validated['datum'] = now();
        }
        // Zet organisatie_id expliciet
        $validated['organisatie_id'] = auth()->user()->organisatie_id;
        $validated['klant_id'] = $klant->id;
        // Add the current user's ID to track who performed the test
        $validated['user_id'] = auth()->id();
        
        // Verwerk stuurpen data correct
        $validated['aanpassingen_stuurpen_aan'] = $request->has('aanpassingen_stuurpen_aan') ? 1 : 0;
        $validated['aanpassingen_stuurpen_pre'] = !empty($validated['aanpassingen_stuurpen_pre']) ? (float) $validated['aanpassingen_stuurpen_pre'] : null;
        $validated['aanpassingen_stuurpen_post'] = !empty($validated['aanpassingen_stuurpen_post']) ? (float) $validated['aanpassingen_stuurpen_post'] : null;

        // Remove debug logging to fix error
        
        // Save the bikefit
        $bikefit = Bikefit::create($validated);
        
        // Refresh zodat alle relaties up-to-date zijn
        $bikefit->refresh();

        \Log::info('✅ BIKEFIT CREATED - ID: ' . $bikefit->id . ', Org ID: ' . $bikefit->organisatie_id);

        // Verwerk uitleensysteem data (testzadels, zooltjes, etc.)
        $this->handleUitleensysteem($request, $bikefit);

        return redirect()->route('bikefit.results', [
            'klant' => $klant->id, 
            'bikefit' => $bikefit->id
        ])->with('success', 'Bikefit succesvol aangemaakt.');
    }

    public function report(Klant $klant, $bikefitId, BikefitReportGenerator $gen)
    {
        $bikefit = $klant->bikefits()->findOrFail($bikefitId);
        $data = $gen->generate($bikefit);
        return view('bikefit.report', array_merge($data, ['klant' => $klant]));
    }

    public function reportPdf(Klant $klant, $bikefitId, BikefitReportGenerator $gen)
    {
        $bikefit = $klant->bikefits()->findOrFail($bikefitId);
        $data = $gen->generate($bikefit);
        $html = view('bikefit.report', array_merge($data, ['klant' => $klant]))->render();
        $pdf = PDF::loadHTML($html)->setPaper('a4', 'portrait');
        $filename = 'bikefit_' . $bikefit->id . '_report.pdf';
        return $pdf->download($filename);
    }

    public function destroy(Klant $klant, Bikefit $bikefit)
    {
        $bikefit->delete();
        return redirect()->route('klanten.show', $klant->id)->with('success', 'Bikefit succesvol verwijderd.');
    }

    public function duplicate(Request $request, Klant $klant, Bikefit $bikefit)
    {
        $newBikefit = $bikefit->replicate();
        $newBikefit->datum = now();
        $newBikefit->save();

        return redirect()->route('klanten.show', $klant->id)->with('success', 'Bikefit gedupliceerd.');
    }

    public function show(Klant $klant, $bikefitId)
    {
        $bikefit = $klant->bikefits()->with('uploads')->findOrFail($bikefitId);
        
        // Check if there's a matching sjabloon for this bikefit
        $hasMatchingTemplate = SjabloonHelper::hasMatchingTemplate($bikefit->testtype, 'bikefit');
        $matchingTemplate = SjabloonHelper::findMatchingTemplate($bikefit->testtype, 'bikefit');
        
        return view('bikefit.show', compact('klant', 'bikefit', 'hasMatchingTemplate', 'matchingTemplate'));
    }

    public function edit(Klant $klant, $bikefitId) 
    {
        $bikefit = $klant->bikefits()->with('uploads')->findOrFail($bikefitId);
        
        // Haal ook de gekoppelde testzadel op voor uitleensysteem velden
        $testzadel = \App\Models\Testzadel::where('bikefit_id', $bikefit->id)->first();
        
        return view('bikefit.edit', compact('klant', 'bikefit', 'testzadel'));
    }

    public function update(Request $request, Klant $klant, Bikefit $bikefit)
    {
        \Log::info('🔧 Bikefit UPDATE gestart', [
            'klant_id' => $klant->id,
            'bikefit_id' => $bikefit->id,
            'request_method' => $request->method(),
            'all_input' => $request->all()
        ]);

        $data = $request->validate([
            'lengte_cm' => 'nullable|numeric',
            'binnenbeenlengte_cm' => 'nullable|numeric',
            'armlengte_cm' => 'nullable|numeric',
            'romplengte_cm' => 'nullable|numeric',
            'schouderbreedte_cm' => 'nullable|numeric',
            'zadel_trapas_hoek' => 'nullable|numeric',
            'zadel_trapas_afstand' => 'nullable|numeric',
            'stuur_trapas_hoek' => 'nullable|numeric',
            'stuur_trapas_afstand' => 'nullable|numeric',
            'aanpassingen_zadel' => 'nullable|numeric',
            'aanpassingen_setback' => 'nullable|numeric',
            'aanpassingen_reach' => 'nullable|numeric',
            'aanpassingen_drop' => 'nullable|numeric',
            'aanpassingen_stuurpen_aan' => 'nullable|in:0,1',
            'aanpassingen_stuurpen_pre' => 'nullable|numeric',
            'aanpassingen_stuurpen_post' => 'nullable|numeric',
            // old free-text aanpassingen_stuurpen removed
            'type_zadel' => 'nullable|string',
            'zadeltil' => 'nullable|numeric|between:-90,90|regex:/^\-?\d{1,2}(\.\d{1,2})?$/',
            'zadelbreedte' => 'nullable|numeric',
            'rotatie_aanpassingen' => 'nullable|string',
            'inclinatie_aanpassingen' => 'nullable|string',
            'ophoging_li' => 'nullable|numeric',
            'ophoging_re' => 'nullable|numeric',
            'opmerkingen' => 'nullable|string',
            'interne_opmerkingen' => 'nullable|string',
            // New fields
            'datum' => 'nullable|date',
            'testtype' => 'nullable|string',
            'fietsmerk' => 'nullable|string',
            'kadermaat' => 'nullable|string',
            'bouwjaar' => 'nullable|integer',
            'algemene_klachten' => 'nullable|string',
            'beenlengteverschil' => 'nullable|in:0,1',
            'beenlengteverschil_cm' => 'nullable|string',
            'lenigheid_hamstrings' => 'nullable|string',
            'steunzolen' => 'nullable|in:0,1',
            'steunzolen_reden' => 'nullable|string',
            // Voetmeting
            'schoenmaat' => 'nullable|numeric|min:35|max:50',
            'voetbreedte' => 'nullable|numeric|min:6|max:13',
            'voetpositie' => 'nullable|in:neutraal,pronatie,supinatie',
            // Template selection for report rendering (nullable)
            'template_kind' => 'nullable|string|in:inspanningstest_fietsen,inspanningstest_lopen,standaard_bikefit,professionele_bikefit,zadeldrukmeting,maatbepaling',
            // Type fitting - TOEGEVOEGD!
            'type_fitting' => 'nullable|string',
            // nieuw_testzadel field
            'nieuw_testzadel' => 'nullable|string|max:255',
            'type_fiets' => 'nullable|string',
            'frametype' => 'nullable|string',
            // Zadellengte center-top
            'zadel_lengte_center_top' => 'nullable|numeric',
            // Functionele mobiliteit - TOEGEVOEGD!
            'straight_leg_raise_links' => 'nullable|string',
            'straight_leg_raise_rechts' => 'nullable|string',
            'knieflexie_links' => 'nullable|string',
            'knieflexie_rechts' => 'nullable|string',
            'heup_endorotatie_links' => 'nullable|string',
            'heup_endorotatie_rechts' => 'nullable|string',
            'heup_exorotatie_links' => 'nullable|string',
            'heup_exorotatie_rechts' => 'nullable|string',
            'enkeldorsiflexie_links' => 'nullable|string',
            'enkeldorsiflexie_rechts' => 'nullable|string',
            'one_leg_squat_links' => 'nullable|string',
            'one_leg_squat_rechts' => 'nullable|string',
        ]);

        // Debug: Log wat er wordt verstuurd
        \Log::info('Stuurpen debug update - RAW DATA:', [
            'aanpassingen_stuurpen_aan' => $request->input('aanpassingen_stuurpen_aan'),
            'aanpassingen_stuurpen_pre' => $request->input('aanpassingen_stuurpen_pre'),
            'aanpassingen_stuurpen_post' => $request->input('aanpassingen_stuurpen_post'),
            'has_aan' => $request->has('aanpassingen_stuurpen_aan'),
            'has_pre' => $request->has('aanpassingen_stuurpen_pre'),
            'has_post' => $request->has('aanpassingen_stuurpen_post'),
            'validated_data' => [
                'pre' => $data['aanpassingen_stuurpen_pre'] ?? 'not set',
                'post' => $data['aanpassingen_stuurpen_post'] ?? 'not set'
            ],
            'data_before_processing' => [
                'pre' => $data['aanpassingen_stuurpen_pre'] ?? 'missing',
                'post' => $data['aanpassingen_stuurpen_post'] ?? 'missing',
                'aan' => $data['aanpassingen_stuurpen_aan'] ?? 'missing'
            ]
        ]);

        \Log::info('✅ Validation passed, data:', [
            'validated_keys' => array_keys($data),
            'validated_count' => count($data)
        ]);

        // Verwerk stuurpen data correct
        $data['aanpassingen_stuurpen_aan'] = $request->has('aanpassingen_stuurpen_aan') ? 1 : 0;
        $data['aanpassingen_stuurpen_pre'] = $request->input('aanpassingen_stuurpen_pre') ?: null;
        $data['aanpassingen_stuurpen_post'] = $request->input('aanpassingen_stuurpen_post') ?: null;

        // Update the bikefit
        \Log::info('💾 Updating bikefit met data...', [
            'data_keys' => array_keys($data)
        ]);
        
        try {
            $bikefit->update($data);
            \Log::info('✅ Bikefit update successful');
        } catch (\Exception $e) {
            \Log::error('❌ Bikefit update FAILED', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }

        // Debug: Log wat er daadwerkelijk is opgeslagen
        $bikefit->refresh();
        \Log::info('After update - database values:', [
            'aanpassingen_stuurpen_aan' => $bikefit->aanpassingen_stuurpen_aan,
            'aanpassingen_stuurpen_pre' => $bikefit->aanpassingen_stuurpen_pre,
            'aanpassingen_stuurpen_post' => $bikefit->aanpassingen_stuurpen_post,
        ]);

        // Verwerk uitleensysteem data (testzadels, zooltjes, etc.)
        // Check of er al een testzadel bestaat voor dit bikefit
        $testzadel = \App\Models\Testzadel::where('bikefit_id', $bikefit->id)->first();
        
        if ($testzadel && $request->filled('onderdeel_type')) {
            // Update bestaande testzadel
            $this->updateTestzadel($request, $testzadel);
        } elseif ($request->filled('onderdeel_type')) {
            // Maak nieuwe testzadel aan
            $this->handleUitleensysteem($request, $bikefit);
        }

        // Check of de gebruiker op "Opslaan" heeft geklikt (naar results)
        if ($request->has('save_and_results')) {
            \Log::info('🔄 Redirecting naar results');
            return redirect()->route('bikefit.results', ['klant' => $klant->id, 'bikefit' => $bikefit->id])
                ->with('success', 'Bikefit bijgewerkt.');
        }

        // Check of de gebruiker op "Terug" heeft geklikt
        if ($request->has('save_and_back')) {
            \Log::info('🔙 Redirecting naar klant show');
            return redirect()->route('klanten.show', $klant->id)
                ->with('success', 'Bikefit bijgewerkt.');
        }

        // Herlaad bikefit met uploads-relatie en toon edit view
        \Log::info('🔄 Returning to edit view');
        $bikefit = $klant->bikefits()->with('uploads')->findOrFail($bikefit->id);
        return view('bikefit.edit', compact('klant', 'bikefit'))
            ->with('success', 'Bikefit bijgewerkt.');
    }

    public function downloadPdf(Klant $klant, $bikefitId)
    {
        $bikefit = Bikefit::findOrFail($bikefitId);
        
        // Get the same data as the print-perfect view
        $report = $bikefit->report;
        $template = $report ? $report->template : null;
        
        $htmls = [];
        $images = [];
        
        if ($report && $report->generated_content) {
            $content = json_decode($report->generated_content, true);
            if (isset($content['htmls'])) {
                $htmls = $content['htmls'];
            }
            if (isset($content['images'])) {
                $images = $content['images'];
            }
        }
        
        // Create PDF using DomPDF
        $pdf = PDF::loadView('bikefit.pdf-template', [
            'klant' => $klant,
            'bikefit' => $bikefit,
            'report' => $report,
            'template' => $template,
            'htmls' => $htmls,
            'images' => $images,
            'html' => $htmls[0] ?? ''
        ]);
        
        // Set PDF options for better quality
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions([
            'dpi' => 150,
            'defaultFont' => 'sans-serif',
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true
        ]);
        
        $fileName = 'Bikefit_Rapport_' . $klant->naam . '_' . date('Y-m-d') . '.pdf';
        
        return $pdf->download($fileName);
    }
    
    public function generatePdf($klantId, $bikefitId)
    {
        try {
            // Haal de models op zoals Laravel route model binding zou doen
            $klant = Klant::findOrFail($klantId);
            $bikefit = Bikefit::findOrFail($bikefitId);
            
            // Controleer of bikefit bij klant hoort
            if ($bikefit->klant_id != $klant->id) {
                abort(404, 'Bikefit niet gevonden voor deze klant');
            }
            
            // Genereer de URL naar de print-perfect pagina met volledige URL
            $printUrl = url(route('bikefit.report.print.perfect', ['klant' => $klant->id, 'bikefit' => $bikefit->id], false));
            
            // Gebruik Puppeteer (moderne vervanger van wkhtmltopdf)
            $filename = 'Bikefit_Rapport_' . str_replace(' ', '_', $klant->naam) . '_' . date('Y-m-d') . '.pdf';
            $tempPath = storage_path('app/temp/' . $filename);
            
            // Zorg dat de temp directory bestaat
            if (!file_exists(dirname($tempPath))) {
                mkdir(dirname($tempPath), 0755, true);
            }
            
            // Probeer Puppeteer (via Node.js) - gebruik .cjs voor CommonJS
            $nodeScript = base_path('resources/js/pdf-generator.cjs');
            
            // Gebruik .cjs extensie voor CommonJS
            $nodeScript = base_path('resources/js/pdf-generator.cjs');
            
        // Eerste pagina speciaal, andere pagina's verbeterd - script regenereren
        if (file_exists($nodeScript)) {
            unlink($nodeScript);
        }
        $this->createPuppeteerScript($nodeScript);            // Haal cookies op voor authenticatie
            $cookies = request()->header('Cookie', '');
            
            // Voer Puppeteer uit met cookies
            $command = "node '{$nodeScript}' '{$printUrl}' '{$tempPath}' '{$cookies}' 2>&1";
            exec($command, $output, $return_var);
            
            \Log::info('Puppeteer command executed', [
                'command' => $command,
                'return_code' => $return_var,
                'output' => implode("\n", $output),
                'file_exists' => file_exists($tempPath),
                'file_size' => file_exists($tempPath) ? filesize($tempPath) : 0
            ]);
            
            if ($return_var === 0 && file_exists($tempPath) && filesize($tempPath) > 1000) {
                // PDF succesvol gegenereerd via Puppeteer (minimaal 1KB)
                \Log::info('Puppeteer PDF succesvol gegenereerd: ' . $filename);
                return response()->download($tempPath, $filename)->deleteFileAfterSend(true);
            } else {
                \Log::warning('Puppeteer gefaald. Return code: ' . $return_var . '. Output: ' . implode(' ', $output));
                // Verwijder leeg bestand
                if (file_exists($tempPath)) {
                    unlink($tempPath);
                }
            }
            
            // Fallback: probeer nog steeds wkhtmltopdf als Puppeteer niet werkt
            $wkhtmltopdfPaths = ['/usr/local/bin/wkhtmltopdf', '/usr/bin/wkhtmltopdf', 'wkhtmltopdf'];
            
            foreach ($wkhtmltopdfPaths as $path) {
                if ($this->commandExists($path)) {
                    $command = "{$path} --page-size A4 --margin-top 0 --margin-bottom 0 --margin-left 0 --margin-right 0 --disable-smart-shrinking --print-media-type '{$printUrl}' '{$tempPath}' 2>&1";
                    exec($command, $wkOutput, $wkReturn);
                    
                    if ($wkReturn === 0 && file_exists($tempPath) && filesize($tempPath) > 0) {
                        return response()->download($tempPath, $filename)->deleteFileAfterSend(true);
                    }
                }
            }
            
            // Laatste fallback: probeer eerst een eenvoudigere Puppeteer aanroep
            \Log::info('Trying simple Puppeteer without authentication');
            
            // Probeer zonder cookies (publieke URL maken)
            $simpleCommand = "node '{$nodeScript}' '{$printUrl}' '{$tempPath}' '' 2>&1";
            exec($simpleCommand, $simpleOutput, $simpleReturn);
            
            if ($simpleReturn === 0 && file_exists($tempPath) && filesize($tempPath) > 1000) {
                \Log::info('Simple Puppeteer succesvol: ' . $filename);
                return response()->download($tempPath, $filename)->deleteFileAfterSend(true);
            }
            
            \Log::warning('Simple Puppeteer ook gefaald: ' . implode(' ', $simpleOutput));
            
            // Als alles faalt, gebruik DomPDF
            return $this->generatePdfWithDomPdf($klant, $bikefit, $printUrl, $filename);
            
        } catch (\Exception $e) {
            \Log::error('PDF generatie fout: ' . $e->getMessage());
            return response()->json([
                'error' => 'PDF kon niet gegenereerd worden',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    private function commandExists($command) 
    {
        exec("which {$command}", $output, $return_var);
        return $return_var === 0;
    }
    
    private function createPuppeteerScript($scriptPath)
    {
        $scriptContent = "
const puppeteer = require('puppeteer');
const fs = require('fs');

(async () => {
    const url = process.argv[2];
    const outputPath = process.argv[3];
    const cookies = process.argv[4];
    
    if (!url || !outputPath) {
        console.error('Usage: node pdf-generator.js <url> <output-path> [cookies]');
        process.exit(1);
    }
    
    try {
                const browser = await puppeteer.launch({
            headless: 'new',
            args: [
                '--no-sandbox', 
                '--disable-setuid-sandbox',
                '--disable-dev-shm-usage',
                '--disable-gpu',
                '--no-first-run',
                '--disable-extensions',
                '--disable-web-security',
                '--single-process',
                '--disable-background-networking',
                '--disable-background-timer-throttling',
                '--disable-backgrounding-occluded-windows',
                '--disable-breakpad',
                '--disable-client-side-phishing-detection',
                '--disable-component-extensions-with-background-pages',
                '--disable-default-apps',
                '--disable-features=TranslateUI',
                '--disable-hang-monitor',
                '--disable-ipc-flooding-protection',
                '--disable-popup-blocking',
                '--disable-prompt-on-repost',
                '--disable-renderer-backgrounding',
                '--disable-sync',
                '--force-color-profile=srgb',
                '--metrics-recording-only',
                '--no-default-browser-check',
                '--password-store=basic',
                '--use-mock-keychain'
            ]
        });
        
        const page = await browser.newPage();
        
        // Optimale viewport voor achtergrondafbeeldingen
        await page.setViewport({ width: 1200, height: 1600, deviceScaleFactor: 1 });
        
        // Set cookies if provided and valid
        if (cookies && cookies.trim() !== '') {
            try {
                const cookieArray = cookies.split(';').map(c => {
                    const [name, value] = c.trim().split('=');
                    if (name && value) {
                        return {
                            name: name,
                            value: value,
                            domain: new URL(url).hostname
                        };
                    }
                    return null;
                }).filter(c => c !== null);
                
                if (cookieArray.length > 0) {
                    await page.setCookie(...cookieArray);
                }
            } catch (cookieError) {
                console.warn('Cookie parsing failed:', cookieError.message);
            }
        }
        
        // Set user agent
        await page.setUserAgent('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
        
        console.log('Navigating to URL:', url);
        await page.goto(url, { 
            waitUntil: 'networkidle0',
            timeout: 60000 
        });
        
        // Langere wachttijd voor achtergrondafbeeldingen
        await new Promise(resolve => setTimeout(resolve, 2500));
        
        // Preload en forceer laden van achtergrondafbeeldingen
        await page.evaluate(() => {
            return new Promise((resolve) => {
                const elementsWithBg = document.querySelectorAll('[style*=\"background-image\"]');
                let loadedImages = 0;
                const totalImages = elementsWithBg.length;
                
                if (totalImages === 0) {
                    resolve();
                    return;
                }
                
                elementsWithBg.forEach(el => {
                    const computedStyle = window.getComputedStyle(el);
                    const bgImage = computedStyle.backgroundImage;
                    
                    if (bgImage && bgImage !== \"none\") {
                        // Extract URL from background-image
                        const urlMatch = bgImage.match(/url\\([\"']?(.+?)[\"']?\\)/);
                        if (urlMatch && urlMatch[1]) {
                            const img = new Image();
                            img.onload = () => {
                                // Force repaint after image loads
                                el.style.transform = \"translateZ(0)\";
                                el.offsetHeight; // Trigger reflow
                                el.style.transform = \"\";
                                
                                loadedImages++;
                                if (loadedImages >= totalImages) {
                                    resolve();
                                }
                            };
                            img.onerror = () => {
                                loadedImages++;
                                if (loadedImages >= totalImages) {
                                    resolve();
                                }
                            };
                            img.src = urlMatch[1];
                        } else {
                            loadedImages++;
                            if (loadedImages >= totalImages) {
                                resolve();
                            }
                        }
                    } else {
                        loadedImages++;
                        if (loadedImages >= totalImages) {
                            resolve();
                        }
                    }
                });
                
                // Fallback timeout
                setTimeout(resolve, 5000);
            });
        });
        
        // Extra wachttijd na forceren
        await new Promise(resolve => setTimeout(resolve, 1500));
        
        // Debug: Log alle achtergrondafbeeldingen om te zien wat er mis gaat
        await page.evaluate(() => {
            const allElements = document.querySelectorAll('.a4-preview-content, [style*=\"background-image\"]');
            console.log('=== DEBUGGING ACHTERGRONDEN ===');
            console.log('Totaal gevonden elementen:', allElements.length);
            
            allElements.forEach((el, idx) => {
                const style = el.getAttribute('style') || '';
                const computedStyle = window.getComputedStyle(el);
                const bgImage = computedStyle.backgroundImage;
                
                console.log('Element ' + (idx + 1) + ':');
                console.log('  - Class:', el.className);
                console.log('  - Inline style:', style);
                console.log('  - Computed bg:', bgImage);
                console.log('  - Has bg-image in style:', style.includes('background-image'));
                console.log('---');
            });
        });
        
        // CSS optimalisaties 
        await page.addStyleTag({
            content: `
                .no-print, button { display: none !important; }
                body { 
                    margin: 0 !important; 
                    padding: 0 !important; 
                    background: #888a8d !important;
                }
                * { 
                    -webkit-print-color-adjust: exact !important; 
                    color-adjust: exact !important;
                    print-color-adjust: exact !important;
                }
                .a4-preview-content { 
                    transform: none !important; 
                    margin-top: 0 !important;
                    background-size: cover !important;
                    background-position: center !important;
                    background-repeat: no-repeat !important;
                    background-attachment: local !important;
                }
            `
        });
        
        // ULTRA-SPECIFIEKE FIX VOOR EERSTE PAGINA + RADICALE CONVERSIE
        await page.evaluate(() => {
            console.log('=== ULTRA-SPECIFIEKE EERSTE PAGINA FIX ===');
            
            const allContentElements = document.querySelectorAll('.a4-preview-content');
            console.log('Totaal content elementen:', allContentElements.length);
            
            allContentElements.forEach((el, idx) => {
                const originalStyle = el.getAttribute('style') || '';
                console.log('Element ' + (idx + 1) + ' originele style:', originalStyle);
                
                // SPECIALE BEHANDELING VOOR EERSTE ELEMENT (idx === 0)
                if (idx === 0) {
                    console.log('=== EERSTE PAGINA - EXTRA AGRESSIEVE BEHANDELING ===');
                    
                    // Probeer ALLE mogelijke regex patronen voor background-image
                    const patterns = [
                        /background-image:\\s*url\\([\"']?([^\"')]+)[\"']?\\)/i,
                        /background-image:\\s*url\\(([^)]+)\\)/i,
                        /background:\\s*[^;]*url\\([\"']?([^\"')]+)[\"']?\\)/i
                    ];
                    
                    let bgUrl = null;
                    for (const pattern of patterns) {
                        const match = originalStyle.match(pattern);
                        if (match && match[1]) {
                            bgUrl = match[1].replace(/[\"']/g, ''); // Remove quotes
                            console.log('EERSTE PAGINA - URL gevonden met patroon:', bgUrl);
                            break;
                        }
                    }
                    
                    // Fallback: zoek in computed style als inline style faalt
                    if (!bgUrl) {
                        const computedStyle = window.getComputedStyle(el);
                        const computedBg = computedStyle.backgroundImage;
                        console.log('EERSTE PAGINA - Computed bg:', computedBg);
                        
                        if (computedBg && computedBg !== 'none') {
                            const match = computedBg.match(/url\\([\"']?([^\"')]+)[\"']?\\)/);
                            if (match && match[1]) {
                                bgUrl = match[1];
                                console.log('EERSTE PAGINA - URL uit computed style:', bgUrl);
                            }
                        }
                    }
                    
                    if (bgUrl) {
                        console.log('EERSTE PAGINA - Converteer naar IMG:', bgUrl);
                        
                        // Verwijder ALLE background properties
                        let newStyle = originalStyle
                            .replace(/background[^;]*;?/gi, '')
                            .replace(/;;+/g, ';')
                            .replace(/^;|;$/g, '');
                        
                        el.setAttribute('style', newStyle);
                        
                        // Maak IMG met extra eigenschappen voor eerste pagina
                        const img = document.createElement('img');
                        img.src = bgUrl;
                        img.style.cssText = `
                            position: absolute !important;
                            top: 0 !important;
                            left: 0 !important;
                            width: 100% !important;
                            height: 100% !important;
                            object-fit: cover !important;
                            object-position: center !important;
                            z-index: -10 !important;
                            pointer-events: none !important;
                            display: block !important;
                        `;
                        
                        // Forceer positioning
                        el.style.position = 'relative';
                        el.style.zIndex = '1';
                        
                        // Voeg IMG toe als allereerste element
                        if (el.firstChild) {
                            el.insertBefore(img, el.firstChild);
                        } else {
                            el.appendChild(img);
                        }
                        
                        console.log('EERSTE PAGINA - IMG toegevoegd met extra forcering');
                    } else {
                        console.log('EERSTE PAGINA - GEEN URL GEVONDEN!');
                    }
                } else {
                    // Verbeterde behandeling voor andere pagina's (idx > 0)
                    console.log('=== PAGINA ' + (idx + 1) + ' - VERBETERDE BEHANDELING ===');
                    
                    // Probeer meerdere patronen ook voor andere pagina's
                    const patterns = [
                        /background-image:\\s*url\\([\"']?([^\"')]+)[\"']?\\)/i,
                        /background-image:\\s*url\\(([^)]+)\\)/i,
                        /background:\\s*[^;]*url\\([\"']?([^\"')]+)[\"']?\\)/i
                    ];
                    
                    let bgUrl = null;
                    for (const pattern of patterns) {
                        const match = originalStyle.match(pattern);
                        if (match && match[1]) {
                            bgUrl = match[1].replace(/[\"']/g, '');
                            console.log('Pagina ' + (idx + 1) + ' - URL gevonden:', bgUrl);
                            break;
                        }
                    }
                    
                    // Fallback naar computed style
                    if (!bgUrl) {
                        const computedStyle = window.getComputedStyle(el);
                        const computedBg = computedStyle.backgroundImage;
                        if (computedBg && computedBg !== 'none') {
                            const match = computedBg.match(/url\\([\"']?([^\"')]+)[\"']?\\)/);
                            if (match && match[1]) {
                                bgUrl = match[1];
                                console.log('Pagina ' + (idx + 1) + ' - URL uit computed:', bgUrl);
                            }
                        }
                    }
                    
                    if (bgUrl) {
                        console.log('Pagina ' + (idx + 1) + ' - Converteer naar IMG:', bgUrl);
                        
                        // Verwijder alle background properties zoals bij eerste pagina
                        let newStyle = originalStyle
                            .replace(/background[^;]*;?/gi, '')
                            .replace(/;;+/g, ';')
                            .replace(/^;|;$/g, '');
                        
                        el.setAttribute('style', newStyle);
                        
                        const img = document.createElement('img');
                        img.src = bgUrl;
                        img.style.cssText = `
                            position: absolute !important;
                            top: 0 !important;
                            left: 0 !important;
                            width: 100% !important;
                            height: 100% !important;
                            object-fit: cover !important;
                            object-position: center !important;
                            z-index: -5 !important;
                            pointer-events: none !important;
                            display: block !important;
                        `;
                        
                        el.style.position = 'relative';
                        el.style.zIndex = '1';
                        
                        if (el.firstChild) {
                            el.insertBefore(img, el.firstChild);
                        } else {
                            el.appendChild(img);
                        }
                        
                        console.log('Pagina ' + (idx + 1) + ' - IMG toegevoegd met verbeterde styling');
                    } else {
                        console.log('Pagina ' + (idx + 1) + ' - GEEN URL GEVONDEN!');
                    }
                }
            });
        });
        
        // Wacht tot alle IMG elementen geladen zijn
        await page.evaluate(() => {
            return new Promise((resolve) => {
                const allImages = document.querySelectorAll('.a4-preview-content img');
                console.log('Wachten op ' + allImages.length + ' afbeeldingen...');
                
                if (allImages.length === 0) {
                    resolve();
                    return;
                }
                
                let loadedCount = 0;
                
                allImages.forEach((img, idx) => {
                    if (img.complete) {
                        loadedCount++;
                        console.log('Afbeelding ' + (idx + 1) + ' al geladen');
                        if (loadedCount >= allImages.length) {
                            resolve();
                        }
                    } else {
                        img.onload = () => {
                            loadedCount++;
                            console.log('Afbeelding ' + (idx + 1) + ' geladen (' + loadedCount + '/' + allImages.length + ')');
                            if (loadedCount >= allImages.length) {
                                resolve();
                            }
                        };
                        img.onerror = () => {
                            loadedCount++;
                            console.log('Afbeelding ' + (idx + 1) + ' FOUT (' + loadedCount + '/' + allImages.length + ')');
                            if (loadedCount >= allImages.length) {
                                resolve();
                            }
                        };
                    }
                });
                
                // Fallback timeout
                setTimeout(() => {
                    console.log('Timeout: verdergaan met ' + loadedCount + '/' + allImages.length + ' geladen');
                    resolve();
                }, 10000);
            });
        });
        
        console.log('Alle afbeeldingen verwerkt, wachten op finale render...');
        await new Promise(resolve => setTimeout(resolve, 3000));
        
        // Skip JavaScript evaluatie voor snelheid
        
        const pdf = await page.pdf({
            path: outputPath,
            format: 'A4',
            printBackground: true,
            preferCSSPageSize: true,
            margin: { top: '0mm', right: '0mm', bottom: '0mm', left: '0mm' }
        });
        
        await browser.close();
        console.log('PDF successfully generated at:', outputPath);
        process.exit(0);
    } catch (error) {
        console.error('Error generating PDF:', error.message);
        console.error('Stack trace:', error.stack);
        process.exit(1);
    }
})();
";
        
        // Zorg dat de directory bestaat
        if (!file_exists(dirname($scriptPath))) {
            mkdir(dirname($scriptPath), 0755, true);
        }
        
        // Fix package.json voor CommonJS
        $packageJsonPath = base_path('package.json');
        $packageJson = [
            "name" => "bonamiapp-pdf-generator",
            "version" => "1.0.0",
            "description" => "PDF generation for Bonami Bikefit reports",
            "main" => "resources/js/pdf-generator.js",
            "scripts" => [
                "test-pdf" => "node resources/js/pdf-generator.js"
            ],
            "dependencies" => [
                "puppeteer" => "^21.0.0"
            ],
            "engines" => [
                "node" => ">=16.0.0"
            ]
        ];
        file_put_contents($packageJsonPath, json_encode($packageJson, JSON_PRETTY_PRINT));
        
        file_put_contents($scriptPath, $scriptContent);
    }

    private function replacePlaceholders($html, $klant, $bikefit, $measurements)
    {
        // Basic placeholder replacements
        $replacements = [
            '{{klant.naam}}' => $klant->naam ?? '',
            '{{klant.email}}' => $klant->email ?? '',
            '{{bikefit.created_at}}' => $bikefit->created_at ? $bikefit->created_at->format('d-m-Y') : '',
            '{{bikefit.sport}}' => $bikefit->sport ?? '',
            '{{bikefit.niveau}}' => $bikefit->niveau ?? '',
            '{{bikefit.afspraak}}' => $bikefit->afspraak ?? '',
        ];
        
        // Add measurement replacements
        if ($measurements && is_array($measurements)) {
            foreach ($measurements as $key => $value) {
                $replacements["{{measurements.{$key}}}"] = $value ?? '';
            }
        }
        
        // Replace all placeholders
        return str_replace(array_keys($replacements), array_values($replacements), $html);
    }

    private function createFallbackHtml($klant, $bikefit, $measurements)
    {
        $html = '
        <div style="padding: 40px; font-family: Arial, sans-serif;">
            <h1 style="color: #333; border-bottom: 2px solid #0066cc;">Bikefit Rapport</h1>
            
            <div style="margin: 30px 0;">
                <h2 style="color: #0066cc;">Klantgegevens</h2>
                <p><strong>Naam:</strong> ' . ($klant->naam ?? '') . '</p>
                <p><strong>Email:</strong> ' . ($klant->email ?? '') . '</p>
            </div>
            
            <div style="margin: 30px 0;">
                <h2 style="color: #0066cc;">Bikefit Details</h2>
                <p><strong>Datum:</strong> ' . ($bikefit->created_at ? $bikefit->created_at->format('d-m-Y') : '') . '</p>
                <p><strong>Sport:</strong> ' . ($bikefit->sport ?? '') . '</p>
                <p><strong>Niveau:</strong> ' . ($bikefit->niveau ?? '') . '</p>
                <p><strong>Afspraak:</strong> ' . ($bikefit->afspraak ?? '') . '</p>
            </div>';
        
        if ($measurements && is_array($measurements)) {
            $html .= '
            <div style="margin: 30px 0;">
                <h2 style="color: #0066cc;">Metingen</h2>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background-color: #f5f5f5;">
                            <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Meting</th>
                            <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Waarde</th>
                        </tr>
                    </thead>
                    <tbody>';
        
            foreach ($measurements as $key => $value) {
                $html .= '
                        <tr>
                            <td style="border: 1px solid #ddd; padding: 8px;">' . $key . '</td>
                            <td style="border: 1px solid #ddd; padding: 8px;">' . ($value ?? '') . '</td>
                        </tr>';
            }
            
            $html .= '
                    </tbody>
                </table>
            </div>';
        }
        
        $html .= '
            <div style="margin-top: 50px; text-align: center; color: #666; font-size: 12px;">
                <p>Gegenereerd op ' . date('d-m-Y H:i') . '</p>
            </div>
        </div>';
        
        return $html;
    }

    private function cleanHtmlForPdf($html)
    {
    // Alleen de knoppen verwijderen, alle andere styling behouden
    $html = preg_replace('/<div[^>]*class="no-print"[^>]*>.*?<\/div>/s', '', $html);
    
    // Voeg PDF-geoptimaliseerde CSS toe die achtergronden behoudt
    $pdfStyles = '
    <style>
        * {
            -webkit-print-color-adjust: exact !important;
            color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        
        body {
            background: #888a8d !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        
        .a4-preview {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: transparent;
            margin: 0;
            padding: 0;
        }
        
        .a4-preview-content {
            width: 210mm !important;
            height: 297mm !important;
            margin-bottom: 0 !important;
            position: relative !important;
            display: block !important;
            overflow: visible !important;
            padding: 0 !important;
            background-color: #fff !important;
            background-size: cover !important;
            background-position: center !important;
            background-repeat: no-repeat !important;
            page-break-after: always;
            page-break-inside: avoid;
            box-shadow: none !important;
            border-radius: 0 !important;
        }
        
        .a4-preview-content:last-child {
            page-break-after: avoid;
        }
        
        @page {
            size: A4;
            margin: 0;
        }
    </style>';
    
    // Voeg de styles toe aan de head (zonder bestaande styles te verwijderen)
    $html = str_replace('</head>', $pdfStyles . '</head>', $html);        return $html;
    }

    private function generatePdfWithDomPdf($klant, $bikefit, $printUrl, $filename)
    {
        try {
        // Haal HTML direct van de BikefitResultsController
        $bikefitResultsController = app(\App\Http\Controllers\BikefitResultsController::class);
        $response = $bikefitResultsController->printPerfect($klant, $bikefit);
        
        if ($response instanceof \Illuminate\View\View) {
            $html = $response->render();
        } else {
            $html = $response->getContent();
        }
        
        // Voeg uitgebreide PDF-optimalisatie toe
        $pdfOptimization = '<style>
            .no-print, button { display: none !important; }
            * { 
                -webkit-print-color-adjust: exact !important; 
                color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            body { 
                background: #888a8d !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .a4-preview {
                background: #888a8d !important;
                min-height: 100vh !important;
                display: flex !important;
                flex-direction: column !important;
                align-items: center !important;
                justify-content: center !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .a4-preview-content {
                width: 210mm !important;
                height: 297mm !important;
                background: white !important;
                background-size: cover !important;
                background-position: center !important;
                background-repeat: no-repeat !important;
                page-break-after: always;
                page-break-inside: avoid;
                margin: 0 !important;
                box-shadow: none !important;
                border-radius: 0 !important;
                position: relative !important;
            }
            .a4-preview-content:last-child {
                page-break-after: avoid;
            }
            @page {
                size: A4;
                margin: 0;
            }
        </style>';
        
        $html = str_replace('</head>', $pdfOptimization . '</head>', $html);            // Genereer PDF met DomPDF
            $pdf = Pdf::loadHTML($html)
                 ->setPaper('A4', 'portrait')
                 ->setOptions([
                     'isPhpEnabled' => true,
                     'isRemoteEnabled' => true,
                     'defaultFont' => 'DejaVu Sans',
                     'isHtml5ParserEnabled' => true,
                     'isPhpEnabled' => true
                 ]);
            
            return $pdf->download($filename);
            
        } catch (\Exception $e) {
            \Log::error('DomPDF fallback fout: ' . $e->getMessage());
            
            // Allerlaatste fallback: redirect naar print-perfect
            $printUrl = route('bikefit.report.print.perfect', ['klant' => $klant->id, 'bikefit' => $bikefit->id]);
            return redirect($printUrl . '?pdf=1')->with('info', 'Automatische PDF generatie niet beschikbaar. Gebruik Cmd+P (Mac) of Ctrl+P (Windows/Linux) en kies "Als PDF bewaren" voor perfecte kwaliteit.');
        }
    }

    /**
     * Handle file upload for bikefit
     */
    public function upload(Request $request, Klant $klant, Bikefit $bikefit)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,png,jpg,jpeg|max:51200', // 50MB max
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('uploads/bikefit/' . $bikefit->id, $filename, 'public');

            // Create upload record using correct column names
            $upload = new \App\Models\Upload();
            $upload->bikefit_id = $bikefit->id;
            $upload->path = $path;
            $upload->filename = $filename;
            $upload->original_name = $file->getClientOriginalName();
            $upload->mime_type = $file->getClientMimeType();
            $upload->file_size = $file->getSize();
            $upload->user_id = auth()->id();
            $upload->save();

            return redirect()->back()->with([
                'upload_success' => true,
                'upload_link' => route('uploads.show', $upload->id)
            ]);
        }

        return redirect()->back()->with('error', 'Bestand uploaden mislukt.');
    }

    /**
     * Show the bikefit import form
     */
    public function showImport()
    {
        return view('bikefit.import');
    }

    /**
     * Handle the bikefit Excel import
     */
    public function importBikefits(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv|max:51200', // 50MB max
        ]);

        try {
            $import = new \App\Imports\BikefitImport();
            \Maatwebsite\Excel\Facades\Excel::import($import, $request->file('excel_file'));
            
            return redirect('/bikefit')->with('success', 'Bikefits succesvol geïmporteerd uit Excel bestand!');
            
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errors = [];
            
            foreach ($failures as $failure) {
                $errors[] = "Rij {$failure->row()}: {$failure->attribute()} - " . implode(', ', $failure->errors());
            }
            
            return redirect()->back()
                ->withErrors($errors)
                ->with('error', 'Er zijn validatiefouten opgetreden bij het importeren van bikefits.');
                
        } catch (\Exception $e) {
            \Log::error('Bikefit Excel import failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Er is een fout opgetreden bij het importeren: ' . $e->getMessage());
        }
    }

    /**
     * Download bikefit Excel template
     */
    public function downloadBikefitTemplate()
    {
        $headers = [
            // Klant koppeling (verplicht!)
            'klant_email',
            'klant_naam',
            
            // Algemeen
            'datum',
            'testtype',
            'type_fitting',
            
            // Fiets info
            'fietsmerk',
            'kadermaat',
            'bouwjaar',
            'frametype',
            
            // Lichaamsmaten
            'lengte_cm',
            'binnenbeenlengte_cm',
            'armlengte_cm',
            'romplengte_cm',
            'schouderbreedte_cm',
            
            // Zitpositie
            'zadel_trapas_hoek',
            'zadel_trapas_afstand',
            'stuur_trapas_hoek',
            'stuur_trapas_afstand',
            'zadel_lengte_center_top',
            
            // Aanpassingen
            'aanpassingen_zadel',
            'aanpassingen_setback',
            'aanpassingen_reach',
            'aanpassingen_drop',
            
            // Stuurpen
            'aanpassingen_stuurpen_aan',
            'aanpassingen_stuurpen_pre',
            'aanpassingen_stuurpen_post',
            
            // Zadel
            'type_zadel',
            'zadeltil',
            'zadelbreedte',
            'nieuw_testzadel',
            
            // Schoenplaatjes
            'rotatie_aanpassingen',
            'inclinatie_aanpassingen',
            'ophoging_li',
            'ophoging_re',
            
            // Anamnese
            'algemene_klachten',
            'beenlengteverschil',
            'beenlengteverschil_cm',
            'lenigheid_hamstrings',
            'steunzolen',
            'steunzolen_reden',
            
            // Voetmeting
            'schoenmaat',
            'voetbreedte',
            'voetpositie',
            
            // Mobiliteit
            'straight_leg_raise_links',
            'straight_leg_raise_rechts',
            'knieflexie_links',
            'knieflexie_rechts',
            'heup_endorotatie_links',
            'heup_endorotatie_rechts',
            'heup_exorotatie_links',
            'heup_exorotatie_rechts',
            'enkeldorsiflexie_links',
            'enkeldorsiflexie_rechts',
            'one_leg_squat_links',
            'one_leg_squat_rechts',
            
            // Opmerkingen
            'opmerkingen',
            'interne_opmerkingen'
        ];

        $filename = 'bikefit_import_template.csv';
        
        return response()->streamDownload(function() use ($headers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            
            // Add example row
            fputcsv($file, [
                'jan@example.com', // klant_email
                'Jan Janssen', // klant_naam
                '2024-01-15', // datum
                'standaard bikefit', // testtype
                'comfort', // type_fitting
                'Trek', // fietsmerk
                '56', // kadermaat
                '2020', // bouwjaar
                'racefiets', // frametype
                '180', // lengte_cm
                '85', // binnenbeenlengte_cm
                '65', // armlengte_cm
                '95', // romplengte_cm
                '42', // schouderbreedte_cm
                '75', // zadel_trapas_hoek
                '74', // zadel_trapas_afstand
                '80', // stuur_trapas_hoek
                '65', // stuur_trapas_afstand
                '14', // zadel_lengte_center_top
                '2', // aanpassingen_zadel
                '1', // aanpassingen_setback
                '0', // aanpassingen_reach
                '0', // aanpassingen_drop
                'ja', // aanpassingen_stuurpen_aan
                '100', // aanpassingen_stuurpen_pre
                '90', // aanpassingen_stuurpen_post
                'Selle Italia', // type_zadel
                '0', // zadeltil
                '143', // zadelbreedte
                'Fizik Antares', // nieuw_testzadel
                'nvt', // rotatie_aanpassingen
                'nvt', // inclinatie_aanpassingen
                '0', // ophoging_li
                '0', // ophoging_re
                'geen klachten', // algemene_klachten
                'nee', // beenlengteverschil
                '', // beenlengteverschil_cm
                'gemiddeld', // lenigheid_hamstrings
                'nee', // steunzolen
                '', // steunzolen_reden
                '44', // schoenmaat
                '10', // voetbreedte
                'neutraal', // voetpositie
                'Hoog', // straight_leg_raise_links
                'Hoog', // straight_leg_raise_rechts
                'Gemiddeld', // knieflexie_links
                'Gemiddeld', // knieflexie_rechts
                '', // heup_endorotatie_links
                '', // heup_endorotatie_rechts
                '', // heup_exorotatie_links
                '', // heup_exorotatie_rechts
                '', // enkeldorsiflexie_links
                '', // enkeldorsiflexie_rechts
                '', // one_leg_squat_links
                '', // one_leg_squat_rechts
                'Alles goed gegaan', // opmerkingen
                'Standaard bikefit' // interne_opmerkingen
            ]);
            
            fclose($file);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    /**
     * Export all bikefits to Excel
     */
    public function exportBikefits()
    {
        $filename = 'bikefits_export_' . date('Y-m-d_H-i-s') . '.xlsx';
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\BikefitsExport, $filename);
    }

    /**
     * Serve uploaded files for bikefit
     */
    public function serveUpload($uploadId)
    {
        $upload = \App\Models\Upload::findOrFail($uploadId);
        
        // Build the full path to the file
        $fullPath = storage_path('app/public/' . $upload->path);
        
        // Check if file exists
        if (!file_exists($fullPath)) {
            abort(404, 'Bestand niet gevonden');
        }
        
        // Return the file
        return response()->file($fullPath, [
            'Content-Type' => $upload->mime_type,
            'Content-Disposition' => 'inline; filename="' . $upload->original_name . '"'
        ]);
    }

    /**
     * Handle uitleensysteem data from bikefit forms
     */
    private function handleUitleensysteem($request, $bikefit)
    {
        \Log::info('🔍 handleUitleensysteem called', [
            'bikefit_id' => $bikefit->id,
            'klant_id' => $bikefit->klant_id,
            'has_onderdeel_type' => $request->filled('onderdeel_type'),
            'onderdeel_type_value' => $request->input('onderdeel_type'),
            'all_request_keys' => array_keys($request->all())
        ]);
        
        // Check if any uitleensysteem data is provided
        if (!$request->filled('onderdeel_type')) {
            \Log::info('⚠️ Geen onderdeel_type gevonden, skip uitleensysteem handling');
            return;
        }

        $uitleenData = [
            'organisatie_id' => auth()->user()->organisatie_id, // BELANGRIJK: zet organisatie_id
            'klant_id' => $bikefit->klant_id,
            'bikefit_id' => $bikefit->id,
            'onderdeel_type' => $request->input('onderdeel_type'),
            'onderdeel_status' => $request->input('onderdeel_status', 'nieuw'), // Default 'nieuw'
            'automatisch_mailtje' => $request->boolean('automatisch_mailtje'),
            'onderdeel_omschrijving' => $request->input('onderdeel_omschrijving'),
        ];

        // Handle type-specific data using the correct field names
        $onderdeelType = $request->input('onderdeel_type');
        
        \Log::info('📦 Processing onderdeel type', [
            'type' => $onderdeelType,
            'is_testzadel' => in_array($onderdeelType, ['testzadel', 'Testzadel', 'nieuw zadel'])
        ]);
        
        if (in_array(strtolower($onderdeelType), ['testzadel', 'nieuw zadel'])) {
            // Use zadel-specific fields with fallbacks
            $uitleenData['zadel_merk'] = $request->input('zadel_merk');
            $uitleenData['zadel_model'] = $request->input('zadel_model');
            $uitleenData['zadel_type'] = $request->input('zadel_type');
            $uitleenData['zadel_breedte'] = $request->input('zadel_breedte');
        } else {
            // For zooltjes and Lake schoenen, use the general fields
            $uitleenData['zadel_merk'] = $request->input('overig_merk');
        }

        // Handle dates and other fields
        $uitleenData['uitleen_datum'] = $request->input('uitgeleend_op', now()); // Default now
        $uitleenData['verwachte_retour_datum'] = $request->input('verwachte_terugbring_datum');
        $uitleenData['opmerkingen'] = $request->input('onderdeel_opmerkingen');
        $uitleenData['status'] = 'uitgeleend'; // EXPLICIET uitgeleend zetten

        // Create testzadel record met uitgebreide logging
        \Log::info('🔧 Creating new testzadel with data:', [
            'organisatie_id' => $uitleenData['organisatie_id'],
            'klant_id' => $uitleenData['klant_id'],
            'bikefit_id' => $uitleenData['bikefit_id'],
            'status' => $uitleenData['status'],
            'onderdeel_type' => $uitleenData['onderdeel_type'],
            'zadel_merk' => $uitleenData['zadel_merk'] ?? 'niet ingevuld',
            'full_data' => $uitleenData
        ]);
        
        try {
            $testzadel = \App\Models\Testzadel::create($uitleenData);
            
            \Log::info('✅ Testzadel succesvol aangemaakt!', [
                'testzadel_id' => $testzadel->id,
                'onderdeel_type' => $testzadel->onderdeel_type,
                'status' => $testzadel->status,
                'klant_naam' => $testzadel->klant->naam ?? 'onbekend'
            ]);
            
            // Schedule reminder email if automatisch_mailtje is enabled
            if ($uitleenData['automatisch_mailtje']) {
                $this->scheduleReminderEmail($testzadel);
            }
        } catch (\Exception $e) {
            \Log::error('❌ Testzadel aanmaken GEFAALD', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $uitleenData
            ]);
        }
    }

    /**
     * Update existing testzadel record
     */
    private function updateTestzadel($request, $testzadel)
    {
        $updateData = [
            'onderdeel_type' => $request->input('onderdeel_type'),
            'onderdeel_status' => $request->input('onderdeel_status'),
            'automatisch_mailtje' => $request->boolean('automatisch_mailtje'),
            'onderdeel_omschrijving' => $request->input('onderdeel_omschrijving'),
        ];

        // Handle type-specific data
        $onderdeelType = $request->input('onderdeel_type');
        
        if (in_array($onderdeelType, ['testzadel', 'nieuw zadel'])) {
            $updateData['zadel_merk'] = $request->input('zadel_merk');
            $updateData['zadel_model'] = $request->input('zadel_model');
            $updateData['zadel_type'] = $request->input('zadel_type');
            $updateData['zadel_breedte'] = $request->input('zadel_breedte');
        } else {
            $updateData['zadel_merk'] = $request->input('overig_merk');
        }

        // Handle dates
        $updateData['uitleen_datum'] = $request->input('uitgeleend_op');
        $updateData['verwachte_retour_datum'] = $request->input('verwachte_terugbring_datum');
        $updateData['opmerkingen'] = $request->input('onderdeel_opmerkingen');

        $testzadel->update($updateData);
    }

    /**
     * Schedule automatic reminder email for testzadel
     */
    private function scheduleReminderEmail($testzadel)
    {
        // Use the email integration service to schedule reminder
        $emailService = new \App\Services\EmailIntegrationService();
        
        try {
            // Send testzadel reminder email
            $variables = [
                'voornaam' => $testzadel->klant->voornaam,
                'naam' => $testzadel->klant->naam,
                'email' => $testzadel->klant->email,
                'onderdeel_type' => $testzadel->onderdeel_type,
                'zadel_merk' => $testzadel->zadel_merk,
                'zadel_model' => $testzadel->zadel_model,
                'uitleen_datum' => $testzadel->uitleen_datum,
                'verwachte_retour_datum' => $testzadel->verwachte_retour_datum,
                'datum' => now()->format('d-m-Y'),
            ];
            
            $emailResult = $emailService->sendTestzadelReminderEmail(
                $testzadel->klant,
                $variables
            );
            
            if ($emailResult) {
                $testzadel->update([
                    'herinnering_verstuurd' => true,
                    'herinnering_verstuurd_op' => now(),
                    'laatste_herinnering' => now()
                ]);
                
                \Log::info('Testzadel reminder email scheduled for: ' . $testzadel->klant->email);
            }
            
        } catch (\Exception $e) {
            \Log::error('Failed to schedule testzadel reminder email: ' . $e->getMessage());
        }
    }

    /**
     * Generate sjabloon-based report for bikefit
     */
    public function generateSjabloonReport($klantId, $bikefitId)
    {
        try {
            $klant = Klant::findOrFail($klantId);
            $bikefit = $klant->bikefits()->findOrFail($bikefitId);
            
            // Find matching sjabloon
            $sjabloon = SjabloonHelper::findMatchingTemplate($bikefit->testtype, 'bikefit');
            
            if (!$sjabloon) {
                return redirect()->back()
                    ->with('error', 'Geen passend sjabloon gevonden voor testtype: ' . $bikefit->testtype);
            }
            
            // Use SjablonenController to generate the report
            $sjablonenController = new \App\Http\Controllers\SjablonenController();
            return $sjablonenController->generateBikefitReport($bikefit->id);
            
        } catch (\Exception $e) {
            \Log::error('Bikefit sjabloon report generation failed: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Er is een fout opgetreden bij het genereren van het rapport.');
        }
    }

    /**
     * Generate PDF EXACTLY like the working web report - EXACT COPY
     */
    public function generateSjabloonReportPdf($klantId, $bikefitId)
    {
        try {
            // Get models
            $klant = \App\Models\Klant::findOrFail($klantId);
            $bikefit = \App\Models\Bikefit::where('id', $bikefitId)
                ->where('klant_id', $klantId)
                ->firstOrFail();

            // Use the EXACT SAME method as the working web version
            $sjablonenController = app(\App\Http\Controllers\SjablonenController::class);
            $response = $sjablonenController->generateBikefitReport($bikefitId);
            
            // Get the HTML from the working response
            if ($response instanceof \Illuminate\View\View) {
                $html = $response->render();
            } else {
                $html = $response->getContent();
            }
            
            // MINIMAL PDF styling - keep everything the same, just hide buttons
            $pdfStyles = '
            <style>
                .no-print, .header-buttons, button { display: none !important; }
                @media print {
                    .no-print, .header-buttons, button { display: none !important; }
                }
            </style>';
            
            // Add ONLY the minimal styles to hide buttons
            $html = str_replace('</head>', $pdfStyles . '</head>', $html);
            
            // Generate PDF with DomPDF - BASIC settings to preserve original
            $pdf = \PDF::loadHTML($html);
            $pdf->setPaper('A4', 'portrait');
            $pdf->setOptions([
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true
            ]);
            
            $filename = 'Bikefit_Rapport_' . $klant->naam . '_' . date('Y-m-d') . '.pdf';
            
            return $pdf->download($filename);

        } catch (\Exception $e) {
            \Log::error('PDF generation failed: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'PDF generatie mislukt',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Auto-save bikefit data (AJAX endpoint voor create en edit)
     */
    public function autoSave(Request $request, Klant $klant, $bikefitId = null)
    {
        try {
            // Haal alle data op zonder strikte validatie
            $data = $request->except(['_token', '_method']);
            
            // Verwijder lege waarden
            $data = array_filter($data, function($value) {
                return $value !== null && $value !== '';
            });
            
            if ($bikefitId) {
                // UPDATE: bestaande bikefit
                $bikefit = Bikefit::where('id', $bikefitId)
                    ->where('klant_id', $klant->id)
                                                                             ->first();
                
                if (!$bikefit) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Bikefit niet gevonden'
                    ], 404);
                }
                
                $bikefit->update($data);
                
                return response()->json([
                    'success' => true,
                    'bikefit_id' => $bikefit->id,
                    'message' => 'Auto-saved at ' . now()->format('H:i:s')
                ]);
            } else {
                // CREATE: nieuwe bikefit
                $data['klant_id'] = $klant->id;
                $data['user_id'] = auth()->id();
                
                $bikefit = Bikefit::create($data);
                
                return response()->json([
                    'success' => true,
                    'bikefit_id' => $bikefit->id,
                    'message' => 'Auto-saved at ' . now()->format('H:i:s')
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Auto-save failed: ' . $e->getMessage(), [
                'klant_id' => $klant->id,
                'bikefit_id' => $bikefitId,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Save failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Importeer bikefits vanuit database bestand
     */
    public function import(Request $request)
    {
        // Log incoming request voor debugging
        \Log::info('📥 Bikefit import request ontvangen', [
            'has_excel_file' => $request->hasFile('excel_file'),
            'all_files' => array_keys($request->allFiles()),
            'input_keys' => array_keys($request->all())
        ]);

        // Check of er een file is (de form gebruikt 'excel_file' als naam)
        if (!$request->hasFile('excel_file')) {
            return redirect()->back()->with('error', 'Geen bestand geselecteerd. Upload een Excel of SQL bestand.');
        }

        // Valideer het bestand
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv,sql,txt|max:10240' // Max 10MB
        ]);

        try {
            $organisatieId = auth()->user()->organisatie_id;
            
            // Check of organisatie_id aanwezig is
            if (!$organisatieId) {
                \Log::error('❌ Geen organisatie_id gevonden voor gebruiker', [
                    'user_id' => auth()->id()
                ]);
                return redirect()->back()->with('error', 'Geen organisatie gekoppeld aan je account. Neem contact op met een administrator.');
            }
            
            // Haal het geüploade bestand op
            $file = $request->file('excel_file');
            $extension = strtolower($file->getClientOriginalExtension());
            
            \Log::info('📥 Bikefit import gestart', [
                'user_id' => auth()->id(),
                'organisatie_id' => $organisatieId,
                'filename' => $file->getClientOriginalName(),
                'extension' => $extension
            ]);
            
            // Check bestandstype
            if (in_array($extension, ['xlsx', 'xls', 'csv'])) {
                \Log::info('📊 Excel/CSV import gestart');
                
                // INLINE Excel/CSV import met fallback
                try {
                    if (class_exists('\PhpOffice\PhpSpreadsheet\IOFactory')) {
                        // PhpSpreadsheet beschikbaar
                        \Log::info('✅ PhpSpreadsheet gevonden, laden bestand...');
                        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealPath());
                        $worksheet = $spreadsheet->getActiveSheet();
                        $rows = $worksheet->toArray();
                        \Log::info('📊 Excel gelezen, aantal rijen: ' . count($rows));
                    } else {
                        // Fallback: converteer naar CSV en lees met PHP
                        \Log::error('❌ PhpSpreadsheet niet gevonden');
                        return redirect()->back()->with('error', 
                            'PhpSpreadsheet niet geïnstalleerd. Installeer met: composer require phpoffice/phpspreadsheet');
                    }
                } catch (\Exception $e) {
                    \Log::error('❌ Excel read failed', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    return redirect()->back()->with('error', 
                        'Excel bestand kon niet gelezen worden: ' . $e->getMessage());
                }
                
                // Verwijder header rij
                $header = array_shift($rows);
                
                $imported = 0;
                $errors = [];
                
                foreach ($rows as $index => $row) {
                    try {
                        // Skip lege rijen
                        if (empty(array_filter($row))) {
                            \Log::info("⏭️ Rij " . ($index + 2) . " is leeg, overslaan");
                            continue;
                        }
                        
                        // Zoek klant op basis van email (kolom 0) of naam (kolom 1)
                        $klantEmail = trim($row[0] ?? '');
                        $klantNaam = trim($row[1] ?? '');
                        
                        \Log::info('🔍 Zoek klant', [
                            'row' => $index + 2,
                            'email' => $klantEmail,
                            'naam' => $klantNaam,
                            'organisatie_id' => $organisatieId
                        ]);
                        
                        $klant = null;
                        
                        // Zoek eerst op email als aanwezig
                        if ($klantEmail) {
                            $klant = \App\Models\Klant::where('email', $klantEmail)
                                ->where('organisatie_id', $organisatieId)
                                ->first();
                            \Log::info('📧 Email zoekresultaat', [
                                'found' => $klant ? 'ja' : 'nee',
                                'klant_id' => $klant ? $klant->id : null
                            ]);
                        }
                        
                        // Als niet gevonden op email, probeer op naam
                        if (!$klant && $klantNaam) {
                            $klant = \App\Models\Klant::where('naam', $klantNaam)
                                ->where('organisatie_id', $organisatieId)
                                ->first();
                            \Log::info('👤 Naam zoekresultaat', [
                                'found' => $klant ? 'ja' : 'nee',
                                'klant_id' => $klant ? $klant->id : null
                            ]);
                        }
                        
                        if (!$klant) {
                            // Toon beschikbare klanten in foutmelding (max 5)
                            $beschikbareKlanten = \App\Models\Klant::where('organisatie_id', $organisatieId)
                                ->take(5)
                                ->get(['naam', 'email'])
                                ->map(fn($k) => "{$k->naam} ({$k->email})")
                                ->implode(', ');
                            
                            $error = "Rij " . ($index + 2) . ": Klant '{$klantNaam}' (email: '{$klantEmail}') niet gevonden in organisatie ID{$organisatieId}.";
                            if ($beschikbareKlanten) {
                                $error .= " Voorbeelden: {$beschikbareKlanten}";
                            }
                            
                            $errors[] = $error;
                            \Log::warning('⚠️ ' . $error);
                            continue;
                        }
                        
                        // Map alle Excel kolommen naar bikefit velden (met veilige datum parsing)
                        $bikefitData = [
                            'organisatie_id' => $organisatieId,
                            'klant_id' => $klant->id,
                            'datum' => $this->parseExcelDate($row[2] ?? null), // Kolom C
                            'testtype' => !empty($row[3]) ? $row[3] : 'standaard bikefit',
                            'type_fitting' => $row[4] ?? null,
                            'fietsmerk' => $row[5] ?? null,
                            'kadermaat' => $row[6] ?? null,
                            'bouwjaar' => !empty($row[7]) && is_numeric($row[7]) ? (int)$row[7] : null,
                            'frametype' => $row[8] ?? null,
                            'lengte_cm' => !empty($row[9]) && is_numeric($row[9]) ? (float)$row[9] : null,
                            'binnenbeenlengte_cm' => !empty($row[10]) && is_numeric($row[10]) ? (float)$row[10] : null,
                            'user_id' => auth()->id(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                        
                        \Log::info('💾 Bikefit data voorbereid', [
                            'klant_id' => $bikefitData['klant_id'],
                            'testtype' => $bikefitData['testtype'],
                            'datum' => $bikefitData['datum']
                        ]);
                        
                        $bikefit = \App\Models\Bikefit::create($bikefitData);
                        $imported++;
                        
                        \Log::info('✅ Bikefit aangemaakt', [
                            'bikefit_id' => $bikefit->id,
                            'klant_id' => $klant->id,
                            'klant_naam' => $klant->naam,
                            'organisatie_id' => $organisatieId,
                            'testtype' => $bikefitData['testtype']
                        ]);
                    } catch (\Exception $e) {
                        $errorMsg = "Rij " . ($index + 2) . ": " . $e->getMessage();
                        $errors[] = $errorMsg;
                        \Log::error('❌ Import rij fout', [
                            'row' => $index + 2,
                            'data' => array_slice($row, 0, 11),
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                    }
                }
                
                \Log::info('Excel import voltooid', [
                    'imported' => $imported,
                    'errors' => count($errors)
                ]);
                
                if (count($errors) > 0) {
                    return redirect()->back()
                        ->with('warning', "Import: {$imported} bikefits, " . count($errors) . " fouten.")
                        ->with('import_errors', $errors);
                }
                
                return redirect()->back()
                    ->with('success', "Excel import succesvol! {$imported} bikefits geïmporteerd.");
            }
            
            if (!in_array($extension, ['sql', 'txt'])) {
                return redirect()->back()->with('error', 
                    'Ongeldig bestandstype. Upload een Excel (.xlsx) of SQL bestand (.sql).');
            }

            // SQL import
            $sqlContent = file_get_contents($file->getRealPath());
            
            // Split SQL statements op basis van ;
            $statements = array_filter(
                array_map('trim', explode(';', $sqlContent)),
                fn($stmt) => !empty($stmt)
            );
            
            $imported = 0;
            $errors = [];
            
            // Voer elke statement uit
            foreach ($statements as $statement) {
                try {
                    // Skip comments en lege regels
                    if (empty($statement) || str_starts_with($statement, '--') || str_starts_with($statement, '/*')) {
                        continue;
                    }
                    
                    \DB::statement($statement);
                    $imported++;
                } catch (\Exception $e) {
                    $errors[] = substr($statement, 0, 100) . '... - ' . $e->getMessage();
                    \Log::warning('⚠️ SQL statement gefaald tijdens import', [
                        'statement' => substr($statement, 0, 200),
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            // Update alle geïmporteerde bikefits met juiste organisatie_id
            \DB::table('bikefits')
                ->whereNull('organisatie_id')
                ->update(['organisatie_id' => $organisatieId]);
            
            \Log::info('✅ Bikefit import voltooid', [
                'statements_executed' => $imported,
                'errors_count' => count($errors),
                'organisatie_id' => $organisatieId
            ]);
            
            if (count($errors) > 0) {
                return redirect()->back()
                    ->with('warning', "Import voltooid met {$imported} statements. " . count($errors) . " errors gevonden.")
                    ->with('import_errors', $errors);
            }
            
            return redirect()->back()
                ->with('success', "Database import succesvol! {$imported} SQL statements uitgevoerd.");
                
        } catch (\Exception $e) {
            \Log::error('❌ Bikefit import gefaald', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'Import gefaald: ' . $e->getMessage());
        }
    }

    public function index(Request $request, Klant $klant)
    {
        // Controleer of klant bij huidige organisatie hoort
        if ($klant->organisatie_id !== auth()->user()->organisatie_id) {
            abort(403, 'Geen toegang tot deze klant');
        }
        
        // ...existing code...
    }

    /**
     * Importeer bikefits vanuit Excel bestand
     */
    private function importFromExcel($file, $organisatieId)
    {
        try {
            // Gebruik PhpSpreadsheet om Excel te lezen
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealPath());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
            
            // Verwijder header rij
            $header = array_shift($rows);
            
            $imported = 0;
            $errors = [];
            
            foreach ($rows as $index => $row) {
                try {
                    // Skip lege rijen
                    if (empty(array_filter($row))) {
                        continue;
                    }
                    
                    // Map Excel kolommen naar database velden
                    // Pas dit aan op basis van je Excel structuur
                    $bikefitData = [
                        'organisatie_id' => $organisatieId,
                        'klant_id' => $row[0] ?? null, // Pas aan naar juiste kolom
                        'datum' => $row[1] ?? now(),
                        // Voeg hier meer velden toe op basis van je Excel structuur
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    
                    // Valideer en maak bikefit aan
                    if ($bikefitData['klant_id']) {
                        \App\Models\Bikefit::create($bikefitData);
                        $imported++;
                    }
                    
                } catch (\Exception $e) {
                    $errors[] = "Rij " . ($index + 2) . ": " . $e->getMessage();
                    \Log::warning('⚠️ Excel rij import gefaald', [
                        'row' => $index + 2,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            \Log::info('✅ Excel import voltooid', [
                'imported' => $imported,
                'errors_count' => count($errors),
                'organisatie_id' => $organisatieId
            ]);
            
            if (count($errors) > 0) {
                return redirect()->back()
                    ->with('warning', "Import voltooid: {$imported} bikefits geïmporteerd, " . count($errors) . " fouten.")
                    ->with('import_errors', $errors);
            }
            
            return redirect()->back()
                ->with('success', "Excel import succesvol! {$imported} bikefits geïmporteerd.");
                
        } catch (\Exception $e) {
            \Log::error('❌ Excel import gefaald', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'Excel import gefaald: ' . $e->getMessage());
        }
    }

    /**
     * Parse Excel datum naar Laravel datum formaat
     */
    private function parseExcelDate($value)
    {
        if (empty($value)) {
            return null;
        }
        
        // Als het al een datum string is, probeer te parsen
        if (is_string($value)) {
            try {
                return \Carbon\Carbon::parse($value)->format('Y-m-d');
            } catch (\Exception $e) {
                \Log::warning('Kon datum niet parsen: ' . $value);
                return null;
            }
        }
        
        // Excel numerieke datum (dagen sinds 1900-01-01)
        if (is_numeric($value)) {
            try {
                $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value);
                return $date->format('Y-m-d');
            } catch (\Exception $e) {
                \Log::warning('Kon Excel datum niet converteren: ' . $value);
                return null;
            }
        }
        
        return null;
    }

    /**
     * Sla aangepaste resultaten op voor prognose/voor/na
     */
    public function saveCustomResults(Request $request, Klant $klant, Bikefit $bikefit)
    {
        try {
            \Log::info('💾 saveCustomResults aangeroepen', [
                'klant_id' => $klant->id,
                'bikefit_id' => $bikefit->id,
                'context' => $request->input('context'),
                'data' => $request->all()
            ]);

            // Valideer input
            $validated = $request->validate([
                'context' => 'required|in:prognose,voor,na',
                'values' => 'required|array'
            ]);

            $context = $validated['context'];
            $values = $validated['values'];

            // Map context naar de juiste kolom in bikefits tabel
            // We slaan de custom waarden op in de bikefit zelf met een prefix
            $columnPrefix = $context . '_'; // bijv. 'prognose_', 'voor_', 'na_'

            // Verzamel alle updates in één array voor efficiëntie
            $updateData = [];
            
            \Log::info('🔍 Fillable kolommen check', [
                'fillable_count' => count($bikefit->getFillable()),
                'first_10_fillable' => array_slice($bikefit->getFillable(), 0, 10)
            ]);
            
            foreach ($values as $field => $value) {
                $columnName = $columnPrefix . $field;
                \Log::info("Voorbereiden custom result: {$field} = {$value} (context: {$context})");
                
                // Voeg ALTIJD toe, laat Laravel beslissen of het werkt
                $updateData[$columnName] = $value;
                \Log::info("✅ Toegevoegd aan updateData: {$columnName} = {$value}");
            }

            // Voer één update uit met alle waarden
            if (!empty($updateData)) {
                \Log::info('💾 Update wordt uitgevoerd met data:', $updateData);
                
                // Probeer de update
                $updateResult = $bikefit->update($updateData);
                \Log::info('📊 Update result:', ['success' => $updateResult]);
                
                // BELANGRIJK: Refresh bikefit om nieuwe waarden direct beschikbaar te maken
                $bikefit->refresh();
                
                // Verificatie: haal DIRECT uit database op
                $freshBikefit = \App\Models\Bikefit::find($bikefit->id);
                
                // Log ter verificatie wat er in de database staat
                foreach ($updateData as $column => $value) {
                    $refreshedValue = $bikefit->$column;
                    $dbValue = $freshBikefit->$column;
                    \Log::info("🔍 Verificatie {$column}:", [
                        'input' => $value,
                        'after_refresh' => $refreshedValue,
                        'direct_from_db' => $dbValue,
                        'match' => ($dbValue == $value)
                    ]);
                }
            }

            \Log::info('✅ Custom results opgeslagen', [
                'bikefit_id' => $bikefit->id,
                'context' => $context,
                'aantal_velden' => count($updateData),
                'update_data' => $updateData
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Waarden succesvol opgeslagen',
                'saved_count' => count($values)
            ]);

        } catch (\Exception $e) {
            \Log::error('❌ saveCustomResults gefaald', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Opslaan mislukt: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reset aangepaste waarden naar berekende waarden (verwijder custom values)
     */
    public function resetToCalculated(Request $request, Klant $klant, Bikefit $bikefit)
    {
        try {
            \Log::info('🔄 resetToCalculated aangeroepen', [
                'klant_id' => $klant->id,
                'bikefit_id' => $bikefit->id,
                'context' => $request->input('context')
            ]);

            // Valideer input
            $validated = $request->validate([
                'context' => 'required|in:prognose,voor,na',
                'fields' => 'nullable|array' // Optioneel: specifieke velden resetten
            ]);

            $context = $validated['context'];
            $fields = $validated['fields'] ?? null;

            // Lijst van alle mogelijke velden die gereset kunnen worden
            $allFields = [
                'zadelhoogte',
                'zadelterugstand',
                'zadelterugstand_top',
                'horizontale_reach',
                'reach',
                'drop',
                'cranklengte',
                'stuurbreedte'
            ];

            // Bepaal welke velden gereset moeten worden
            $fieldsToReset = $fields ?: $allFields;

            $resetCount = 0;
            $columnPrefix = $context . '_';

            // Reset elk veld door de custom waarde op NULL te zetten
            foreach ($fieldsToReset as $field) {
                $columnName = $columnPrefix . $field;
                
                try {
                    // Zet de waarde op NULL om terug te vallen op de berekende waarde
                    $bikefit->update([
                        $columnName => null
                    ]);
                    $resetCount++;
                    \Log::info("✅ Veld gereset: {$columnName}");
                } catch (\Exception $updateError) {
                    \Log::warning("⚠️ Kolom {$columnName} bestaat mogelijk niet, skip");
                    continue;
                }
            }

            \Log::info('✅ Reset voltooid', [
                'bikefit_id' => $bikefit->id,
                'context' => $context,
                'reset_count' => $resetCount
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Waarden succesvol gereset naar berekende waarden',
                'reset_count' => $resetCount
            ]);

        } catch (\Exception $e) {
            \Log::error('❌ resetToCalculated gefaald', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Reset mislukt: ' . $e->getMessage()
            ], 500);
        }
    }
}
