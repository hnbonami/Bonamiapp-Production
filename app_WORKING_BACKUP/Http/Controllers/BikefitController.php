<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Klant;
use App\Models\Bikefit;
use App\Services\BikefitReportGenerator;
use Barryvdh\DomPDF\Facade\Pdf;

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
        return view('bikefit.create', compact('klant', 'templates', 'templateMap', 'defaultMobility'));
    }

    public function store(Request $request, $klantId)
    {
    $data = $request->validate([
            'datum' => 'nullable|date',
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
            'zadeltil' => 'nullable|numeric',
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
        if (empty($data['datum'])) {
            $data['datum'] = now();
        }
        $data['klant_id'] = $klantId;
        // Add the current user's ID to track who performed the test
        $data['user_id'] = auth()->id();
        
        // Verwerk stuurpen data correct
        $data['aanpassingen_stuurpen_aan'] = $request->has('aanpassingen_stuurpen_aan') ? 1 : 0;
        $data['aanpassingen_stuurpen_pre'] = !empty($data['aanpassingen_stuurpen_pre']) ? (float) $data['aanpassingen_stuurpen_pre'] : null;
        $data['aanpassingen_stuurpen_post'] = !empty($data['aanpassingen_stuurpen_post']) ? (float) $data['aanpassingen_stuurpen_post'] : null;

        // Debug logging
        \Log::info('Bikefit store - form data:', $request->all());
        \Log::info('Has onderdeel_type:', $request->filled('onderdeel_type'));
        
        $bikefit = Bikefit::create($data);
        
        // Handle uitleensysteem data if provided
        if ($request->filled('onderdeel_type')) {
            \Log::info('Calling handleUitleensysteem method');
            try {
                $this->handleUitleensysteem($request, $bikefit);
                \Log::info('handleUitleensysteem completed successfully');
            } catch (\Exception $e) {
                \Log::error('handleUitleensysteem failed:', ['error' => $e->getMessage()]);
            }
        } else {
            \Log::info('No onderdeel_type filled - skipping uitleensysteem');
        }
        
        // Generate and save PDF report
        try {
            $reportPath = app(BikefitReportGenerator::class)->savePdf($bikefit);
            // Provide a download link back on the klant show page
            session()->flash('success', 'Bikefit opgeslagen. Verslag gegenereerd.');
            session()->flash('bikefit_report_path', $reportPath);
        } catch (\Throwable $e) {
            // Don't block the save if PDF generation fails; log and continue
            \Log::error('PDF report save failed: ' . $e->getMessage());
            session()->flash('error', 'Bikefit opgeslagen, maar verslag kon niet worden gegenereerd.');
        }

        return redirect()->route('bikefit.results', [
            'klant' => $klantId,
            'bikefit' => $bikefit->id
        ]);
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
        return view('bikefit.show', compact('klant', 'bikefit'));
    }

    public function edit(Klant $klant, $bikefitId)
    {
        $bikefit = $klant->bikefits()->with('uploads')->findOrFail($bikefitId);
        return view('bikefit.edit', compact('klant', 'bikefit'));
    }

    public function update(Request $request, Klant $klant, Bikefit $bikefit)
    {
        // Debug aan het begin
        \Log::info('Update method called for bikefit:', [
            'bikefit_id' => $bikefit->id,
            'has_stuurpen_pre' => $request->has('aanpassingen_stuurpen_pre'),
            'has_stuurpen_post' => $request->has('aanpassingen_stuurpen_post'),
            'stuurpen_pre_value' => $request->input('aanpassingen_stuurpen_pre'),
            'stuurpen_post_value' => $request->input('aanpassingen_stuurpen_post'),
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
            'zadeltil' => 'nullable|numeric',
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
            // nieuw_testzadel field
            'nieuw_testzadel' => 'nullable|string|max:255',
            'type_fiets' => 'nullable|string',
            'frametype' => 'nullable|string',
            // Zadellengte center-top
            'zadel_lengte_center_top' => 'nullable|numeric',
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

        // Verwerk stuurpen data correct
        $data['aanpassingen_stuurpen_aan'] = $request->has('aanpassingen_stuurpen_aan') ? 1 : 0;
        $data['aanpassingen_stuurpen_pre'] = $request->input('aanpassingen_stuurpen_pre') ?: null;
        $data['aanpassingen_stuurpen_post'] = $request->input('aanpassingen_stuurpen_post') ?: null;

        $bikefit->update($data);

        // Debug: Log wat er daadwerkelijk is opgeslagen
        $bikefit->refresh();
        \Log::info('After update - database values:', [
            'aanpassingen_stuurpen_aan' => $bikefit->aanpassingen_stuurpen_aan,
            'aanpassingen_stuurpen_pre' => $bikefit->aanpassingen_stuurpen_pre,
            'aanpassingen_stuurpen_post' => $bikefit->aanpassingen_stuurpen_post,
        ]);

        // Check of de gebruiker op "Opslaan" heeft geklikt (naar results)
        if ($request->has('save_and_results')) {
            return redirect()->route('bikefit.results', ['klant' => $klant->id, 'bikefit' => $bikefit->id])
                ->with('success', 'Bikefit bijgewerkt.');
        }

        // Check of de gebruiker op "Terug" heeft geklikt
        if ($request->has('save_and_back')) {
            return redirect()->route('klanten.show', $klant->id)
                ->with('success', 'Bikefit bijgewerkt.');
        }

        // Herlaad bikefit met uploads-relatie en toon edit view
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
            'file' => 'required|file|mimes:pdf,png,jpg,jpeg|max:10240', // 10MB max
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('uploads/bikefit/' . $bikefit->id, $filename, 'public');

            // Create upload record using correct column names
            $upload = new \App\Models\Upload();
            $upload->bikefit_id = $bikefit->id;
            $upload->path = $path; // Keep original path without storage/ prefix
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
            'excel_file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // 10MB max
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
        // Check if any uitleensysteem data is provided
        if (!$request->filled('onderdeel_type')) {
            return;
        }

        $uitleenData = [
            'klant_id' => $bikefit->klant_id,
            'bikefit_id' => $bikefit->id,
            'onderdeel_type' => $request->input('onderdeel_type'),
            'onderdeel_status' => $request->input('onderdeel_status'),
            'automatisch_mailtje' => $request->boolean('automatisch_mailtje'),
            'onderdeel_omschrijving' => $request->input('onderdeel_omschrijving'),
        ];

        // Handle type-specific data
        $onderdeelType = $request->input('onderdeel_type');
        
        if (in_array($onderdeelType, ['testzadel', 'nieuw zadel'])) {
            // Use zadel-specific fields
            $uitleenData['zadel_merk'] = $request->input('zadel_merk');
            $uitleenData['zadel_model'] = $request->input('zadel_model');
            $uitleenData['zadel_type'] = $request->input('zadel_type');
            $uitleenData['zadel_breedte'] = $request->input('zadel_breedte');
        } else {
            // For zooltjes and Lake schoenen, use the general fields
            $uitleenData['zadel_merk'] = $request->input('overig_merk');
        }

        // Handle dates
        $uitleenData['uitleen_datum'] = $request->input('uitgeleend_op');
        $uitleenData['verwachte_retour_datum'] = $request->input('verwachte_terugbring_datum');
        $uitleenData['opmerkingen'] = $request->input('onderdeel_opmerkingen');
        $uitleenData['status'] = 'uitgeleend';

        // Create testzadel record
        \App\Models\Testzadel::create($uitleenData);
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
}
