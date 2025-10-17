<?php

namespace App\Http\Controllers;

use App\Models\Sjabloon;
use App\Models\SjabloonPage;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class SjablonenController extends Controller
{
    public function index()
    {
        $sjablonen = Sjabloon::where('is_actief', true)
                            ->orderBy('naam')
                            ->get();
        
        return view('sjablonen.index', compact('sjablonen'));
    }

    public function create()
    {
        return view('sjablonen.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'naam' => 'required|string|max:255',
            'categorie' => 'required|string|max:255',
            'testtype' => 'nullable|string|max:255',
            'beschrijving' => 'nullable|string',
        ]);

        $sjabloon = Sjabloon::create([
            'naam' => $request->naam,
            'categorie' => $request->categorie,
            'testtype' => $request->testtype,
            'beschrijving' => $request->beschrijving,
            'is_actief' => true
        ]);

        return redirect()->route('sjablonen.edit', $sjabloon)
                        ->with('success', 'Sjabloon aangemaakt!');
    }

    public function show($id)
    {
        // Find sjabloon manually to ensure consistency
        $sjabloon = Sjabloon::findOrFail($id);
        $sjabloon->load('pages');
        
        return view('sjablonen.show', compact('sjabloon'));
    }

    public function edit($id)
    {
        // Find sjabloon manually
        $sjabloon = Sjabloon::findOrFail($id);
        
        // Load pages from database
        $sjabloon->load('pages');
        
        // If no pages exist, create the first one
        if ($sjabloon->pages->isEmpty()) {
            $newPage = new SjabloonPage();
            $newPage->sjabloon_id = $sjabloon->id;
            $newPage->page_number = 1;
            $newPage->content = '<p>Start met bewerken...</p>';
            $newPage->is_url_page = false;
            $newPage->background_image = null;
            $newPage->url = null;
            $newPage->save();
            
            // Reload pages after creation
            $sjabloon->load('pages');
        }
        
        // Get template keys from database - alle beschikbare velden
        $templateKeys = \App\Models\TemplateKey::all()->groupBy('category');

        return view('sjablonen.edit', ['sjabloon' => $sjabloon, 'templateKeys' => $templateKeys]);
    }

    /**
     * Show the form for editing basic sjabloon information
     */
    public function editBasic($id)
    {
        $sjabloon = Sjabloon::findOrFail($id);
        return view('sjablonen.edit-basic', compact('sjabloon'));
    }

    /**
     * Update basic sjabloon information
     */
    public function updateBasic(Request $request, $id)
    {
        $sjabloon = Sjabloon::findOrFail($id);
        
        $request->validate([
            'naam' => 'required|string|max:255',
            'categorie' => 'required|string|max:255',
            'testtype' => 'nullable|string|max:255',
            'beschrijving' => 'nullable|string',
        ]);

        $sjabloon->update($request->only(['naam', 'categorie', 'testtype', 'beschrijving']));

        return redirect()->route('sjablonen.edit', $sjabloon)
                        ->with('success', 'Sjabloon informatie bijgewerkt! Nu kun je de inhoud bewerken.');
    }

    public function update(Request $request, Sjabloon $sjabloon)
    {
        $request->validate([
            'naam' => 'required|string|max:255',
            'categorie' => 'required|string|max:255',
            'testtype' => 'nullable|string|max:255',
            'beschrijving' => 'nullable|string',
        ]);

        $sjabloon->update($request->only(['naam', 'categorie', 'testtype', 'beschrijving']));

        return redirect()->route('sjablonen.index')
                        ->with('success', 'Sjabloon bijgewerkt!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            // Find the sjabloon by ID
            $sjabloon = Sjabloon::findOrFail($id);
            
            // Debug log met ID
            \Log::info('Deleting sjabloon: ' . $sjabloon->id . ' (naam: ' . $sjabloon->naam . ')');
            
            // Verwijder alle gerelateerde pagina's eerst
            $deletedPages = $sjabloon->pages()->delete();
            \Log::info('Deleted ' . $deletedPages . ' pages');
            
            // Verwijder het sjabloon zelf
            $result = $sjabloon->delete();
            
            \Log::info('Delete result: ' . ($result ? 'success' : 'failed'));
            
            return redirect()->route('sjablonen.index')
                ->with('success', 'Sjabloon "' . $sjabloon->naam . '" is succesvol verwijderd.');
        } catch (\Exception $e) {
            \Log::error('Delete error: ' . $e->getMessage());
            return redirect()->route('sjablonen.index')
                ->with('error', 'Er is een fout opgetreden bij het verwijderen: ' . $e->getMessage());
        }
    }

    /**
     * Duplicate the specified sjabloon.
     */
    public function duplicate($id)
    {
        try {
            // Find the sjabloon by ID
            $sjabloon = Sjabloon::findOrFail($id);
            
            \Log::info('Duplicating sjabloon: ' . $sjabloon->id . ' (naam: ' . $sjabloon->naam . ')');
            
            // Maak een kopie van het sjabloon
            $newSjabloon = $sjabloon->replicate();
            $newSjabloon->naam = $sjabloon->naam . ' (Kopie)';
            $newSjabloon->save();

            // Kopieer alle pagina's
            foreach ($sjabloon->pages as $page) {
                $newPage = $page->replicate();
                $newPage->sjabloon_id = $newSjabloon->id;
                $newPage->save();
            }

            \Log::info('Successfully duplicated sjabloon to: ' . $newSjabloon->id);

            return redirect()->route('sjablonen.edit', $newSjabloon)
                ->with('success', 'Sjabloon is succesvol gedupliceerd.');
        } catch (\Exception $e) {
            \Log::error('Duplicate error: ' . $e->getMessage());
            return redirect()->route('sjablonen.index')
                ->with('error', 'Er is een fout opgetreden bij het dupliceren van het sjabloon: ' . $e->getMessage());
        }
    }

    // AJAX methods for page management
    public function addPagina(Request $request, Sjabloon $sjabloon)
    {
        // Get the highest page number and add 1
        $maxPageNumber = SjabloonPage::where('sjabloon_id', $sjabloon->id)->max('page_number') ?? 0;
        
        // Create new page
        $page = SjabloonPage::create([
            'sjabloon_id' => $sjabloon->id,
            'page_number' => $maxPageNumber + 1,
            'content' => $request->input('is_url_page') ? null : '<p>Nieuwe pagina...</p>',
            'url' => $request->input('url'),
            'is_url_page' => $request->input('is_url_page', false),
            'background_image' => null
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Pagina toegevoegd!',
            'page_id' => $page->id,
            'reload' => true
        ]);
    }

    public function updatePagina(Request $request, Sjabloon $sjabloon, $paginaId)
    {
        $page = SjabloonPage::where('sjabloon_id', $sjabloon->id)
                            ->where('id', $paginaId)
                            ->first();
        
        if (!$page) {
            return response()->json(['success' => false, 'message' => 'Pagina niet gevonden']);
        }
        
        $page->update([
            'content' => $request->input('content'),
            'background_image' => $request->input('background_image'),
            'url' => $request->input('url'),
            'is_url_page' => $request->input('is_url_page', false)
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Pagina opgeslagen!'
        ]);
    }

    public function deletePagina(Request $request, Sjabloon $sjabloon, $paginaId)
    {
        // Don't delete if it's the last page
        $totalPages = SjabloonPage::where('sjabloon_id', $sjabloon->id)->count();
        if ($totalPages <= 1) {
            return response()->json([
                'success' => false, 
                'message' => 'Kan de laatste pagina niet verwijderen'
            ]);
        }
        
        $page = SjabloonPage::where('sjabloon_id', $sjabloon->id)
                            ->where('id', $paginaId)
                            ->first();
        
        if (!$page) {
            return response()->json(['success' => false, 'message' => 'Pagina niet gevonden']);
        }
        
        $page->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Pagina verwijderd!',
            'reload' => true
        ]);
    }

    public function preview($id)
    {
        // Find sjabloon
        $sjabloon = Sjabloon::findOrFail($id);
        
        // Load pages and sort by page_number
        $sjabloon->load(['pages' => function($query) {
            $query->orderBy('page_number', 'asc');
        }]);
        
        // Debug: Log how many pages we have
        \Log::info("Preview for sjabloon {$id}: Found " . $sjabloon->pages->count() . " pages");
        
        // Create a dummy klant for preview
        $dummyKlant = (object)[
            'id' => 'preview',
            'naam' => 'Voorbeeld Klant',
            'voornaam' => 'Jan',
            'email' => 'voorbeeld@bonami.app',
            'geboortedatum' => '1990-01-01'
        ];
        
        // Create dummy bikefit data
        $dummyBikefit = (object)[
            'datum' => date('Y-m-d'),
            'testtype' => 'Voorbeeld Test',
            'lengte_cm' => '180',
            'binnenbeenlengte_cm' => '85'
        ];
        
        // Process ALL pages - make sure we get them all
        $generatedPages = [];
        foreach ($sjabloon->pages as $page) {
            \Log::info("Processing page {$page->page_number}: is_url_page={$page->is_url_page}, content length=" . strlen($page->content ?? ''));
            
            if ($page->is_url_page) {
                $generatedPages[] = [
                    'is_url_page' => true,
                    'url' => $page->url,
                    'content' => null,
                    'background_image' => $page->background_image,
                    'page_number' => $page->page_number
                ];
            } else {
                // Replace template variables with dummy data
                $content = $page->content ?? '<p>Geen content</p>';
                $content = str_replace('{{klant.naam}}', $dummyKlant->naam, $content);
                $content = str_replace('{{klant.voornaam}}', $dummyKlant->voornaam, $content);
                $content = str_replace('{{klant.email}}', $dummyKlant->email, $content);
                $content = str_replace('{{klant.geboortedatum}}', $dummyKlant->geboortedatum, $content);
                $content = str_replace('{{bikefit.datum}}', $dummyBikefit->datum, $content);
                $content = str_replace('{{bikefit.testtype}}', $dummyBikefit->testtype, $content);
                $content = str_replace('{{bikefit.lengte_cm}}', $dummyBikefit->lengte_cm, $content);
                $content = str_replace('{{bikefit.binnenbeenlengte_cm}}', $dummyBikefit->binnenbeenlengte_cm, $content);
                $content = str_replace('$mobility_table_report$', '<p><em>Mobiliteit tabel wordt hier weergegeven</em></p>', $content);
                
                // Bikefit HTML componenten (voor preview met dummy data)
                $content = str_replace('$ResultatenVoor$', '<div class="preview-placeholder"><h4>🔧 Bikefit Resultaten VOOR</h4><p><em>Wordt getoond in echte bikefit rapporten</em></p></div>', $content);
                $content = str_replace('$ResultatenNa$', '<div class="preview-placeholder"><h4>🔧 Bikefit Resultaten NA</h4><p><em>Wordt getoond in echte bikefit rapporten</em></p></div>', $content);
                $content = str_replace('$Bikefit.prognose_zitpositie_html$', '<div class="preview-placeholder"><h4>📐 Prognose Zitpositie</h4><p><em>Schema met zitpositie berekeningen</em></p></div>', $content);
                $content = str_replace('$MobiliteitTabel$', '<div class="preview-placeholder"><h4>🤸 Mobiliteit Resultaten</h4><p><em>Mobiliteit test resultaten tabel</em></p></div>', $content);
                $content = str_replace('$mobiliteitklant$', '<div class="preview-placeholder"><h4>📊 Mobiliteit Klant (Gekleurde Balken)</h4><p><em>Mooie mobiliteit tabel met kleurbalken</em></p></div>', $content);
                $content = str_replace('$Bikefit.body_measurements_block_html$', '<div class="preview-placeholder"><h4>📏 Lichaamsmaten Blok</h4><p><em>Overzicht van alle lichaamsmaten</em></p></div>', $content);
                
                // Systeem variabelen
                $content = str_replace('{{datum.vandaag}}', date('d-m-Y'), $content);
                $content = str_replace('{{datum.jaar}}', date('Y'), $content);
                
                // Verberg alle tabelranden voor layout tabellen (CKEditor tabellen zonder borders)
                $content = $this->hideCKEditorTableBorders($content);
                
                $generatedPages[] = [
                    'is_url_page' => false,
                    'content' => $content,
                    'background_image' => $page->background_image,
                    'url' => null,
                    'page_number' => $page->page_number
                ];
            }
        }
        
        // Debug: Log hoe veel pagina's we naar de view sturen
        \Log::info("Sending " . count($generatedPages) . " pages to view");
        
        return view('sjablonen.generated-report', [
            'template' => $sjabloon,
            'klantModel' => $dummyKlant,
            'generatedPages' => $generatedPages
        ]);
    }

    public function generatePdf($templateId, $klantId = 'preview', $testId = null, $type = null)
    {
        // If templateId is actually the ID and no other params, set defaults
        if ($klantId === null) {
            $klantId = 'preview';
        }
        
        // Find sjabloon
        $sjabloon = Sjabloon::findOrFail($templateId);
        
        // Load pages and sort by page_number
        $sjabloon->load(['pages' => function($query) {
            $query->orderBy('page_number', 'asc');
        }]);
        
        // Create dummy klant if not provided
        if ($klantId && $klantId !== 'preview') {
            // In real implementation, load actual klant
            $klant = (object)[
                'id' => $klantId,
                'naam' => 'Echte Klant',
                'voornaam' => 'Jan',
                'email' => 'klant@bonami.app',
                'geboortedatum' => '1990-01-01'
            ];
        } else {
            $klant = (object)[
                'id' => 'preview',
                'naam' => 'Voorbeeld Klant',
                'voornaam' => 'Jan',
                'email' => 'voorbeeld@bonami.app',
                'geboortedatum' => '1990-01-01'
            ];
        }
        
        // Create dummy bikefit data
        $dummyBikefit = (object)[
            'datum' => date('Y-m-d'),
            'testtype' => 'Voorbeeld Test',
            'lengte_cm' => '180',
            'binnenbeenlengte_cm' => '85'
        ];
        
        // Process pages (same as preview)
        $generatedPages = [];
        foreach ($sjabloon->pages as $page) {
            if ($page->is_url_page) {
                // Skip URL pages for PDF for now
                continue;
            } else {
                // Replace template variables with dummy data
                $content = $page->content ?? '<p>Geen content</p>';
                $content = str_replace('{{klant.naam}}', $klant->naam, $content);
                $content = str_replace('{{klant.voornaam}}', $klant->voornaam, $content);
                $content = str_replace('{{klant.email}}', $klant->email, $content);
                $content = str_replace('{{klant.geboortedatum}}', $klant->geboortedatum, $content);
                $content = str_replace('{{bikefit.datum}}', $dummyBikefit->datum, $content);
                $content = str_replace('{{bikefit.testtype}}', $dummyBikefit->testtype, $content);
                $content = str_replace('{{bikefit.lengte_cm}}', $dummyBikefit->lengte_cm, $content);
                $content = str_replace('{{bikefit.binnenbeenlengte_cm}}', $dummyBikefit->binnenbeenlengte_cm, $content);
                $content = str_replace('$mobility_table_report$', '<p><em>Mobiliteit tabel wordt hier weergegeven</em></p>', $content);
                
                // Systeem variabelen  
                $content = str_replace('{{datum.vandaag}}', date('d-m-Y'), $content);
                $content = str_replace('{{datum.jaar}}', date('Y'), $content);
                
                // Verberg alle tabelranden voor layout tabellen (CKEditor tabellen zonder borders)
                $content = $this->hideCKEditorTableBorders($content);
                
                $generatedPages[] = [
                    'is_url_page' => false,
                    'content' => $content,
                    'background_image' => $page->background_image,
                    'url' => null,
                    'page_number' => $page->page_number
                ];
            }
        }
        
        // Generate PDF using DomPDF
        try {
            $pdf = Pdf::loadView('sjablonen.pdf-template', [
                'template' => $sjabloon,
                'klantModel' => $klant,
                'generatedPages' => $generatedPages
            ]);
            
            // Configure PDF options for better image support
            $pdf->setPaper('A4', 'portrait');
            $pdf->setOptions([
                'isHtml5ParserEnabled' => true,
                'isPhpEnabled' => true,
                'isRemoteEnabled' => true,
                'isJavascriptEnabled' => false,
                'isFontSubsettingEnabled' => true,
                'defaultFont' => 'Arial',
                'dpi' => 150,
                'defaultMediaType' => 'screen',
                'isCssFloatEnabled' => true
            ]);
            
            // Enable image processing
            $pdf->getDomPDF()->getOptions()->setChroot(public_path());
            
            $fileName = $sjabloon->naam . '_' . $klant->naam . '_' . date('Y-m-d') . '.pdf';
            
            return $pdf->download($fileName);
            
        } catch (\Exception $e) {
            \Log::error('PDF Generation failed: ' . $e->getMessage());
            
            // Fallback to HTML download
            $html = view('sjablonen.pdf-template', [
                'template' => $sjabloon,
                'klantModel' => $klant,
                'generatedPages' => $generatedPages
            ])->render();
            
            $fileName = $sjabloon->naam . '_' . $klant->naam . '_' . date('Y-m-d') . '.html';
            
            return response($html)
                ->header('Content-Type', 'text/html')
                ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
        }
    }

    /**
     * Find matching sjabloon based on testtype and category
     */
    public static function findMatchingTemplate($testtype, $category = null)
    {
        $query = Sjabloon::where('is_actief', true);
        
        // First try to match both testtype and category
        if ($testtype && $category) {
            $template = $query->where('testtype', $testtype)
                             ->where('categorie', $category)
                             ->first();
            if ($template) {
                return $template;
            }
        }
        
        // If no exact match, try just testtype
        if ($testtype) {
            $template = $query->where('testtype', $testtype)->first();
            if ($template) {
                return $template;
            }
        }
        
        // If still no match, try just category
        if ($category) {
            $template = $query->where('categorie', $category)->first();
            if ($template) {
                return $template;
            }
        }
        
        return null;
    }

    /**
     * API endpoint to check if sjabloon exists for testtype
     */
    public function checkSjabloon(Request $request)
    {
        $testtype = $request->query('testtype');
        $category = $request->query('categorie');
        
        $template = \App\Helpers\SjabloonHelper::findMatchingTemplate($testtype, $category);
        
        return response()->json([
            'hasTemplate' => $template !== null,
            'templateName' => $template ? $template->naam : null,
            'templateId' => $template ? $template->id : null
        ]);
    }

    /**
     * Generate report using print-perfect (perfect working version)
     */
    public function generatePrintPerfectReport($bikefitId)
    {
        try {
            // Find bikefit
            $bikefit = \App\Models\Bikefit::findOrFail($bikefitId);
            
            // Find matching sjabloon
            $sjabloon = $this->findMatchingTemplate($bikefit->testtype, 'bikefit');
            
            if (!$sjabloon) {
                return redirect()->back()
                    ->with('error', 'Geen passend sjabloon gevonden voor testtype: ' . $bikefit->testtype);
            }
            
            // Load pages
            $sjabloon->load(['pages' => function($query) {
                $query->orderBy('page_number', 'asc');
            }]);
            
            // Generate pages with real bikefit data
            $generatedPages = $this->generatePagesForBikefit($sjabloon, $bikefit);
            
            // Generate HTML content for each page
            $htmls = [];
            $images = [];
            
            foreach ($generatedPages as $pageIndex => $page) {
                if (!$page['is_url_page']) {
                    $htmls[] = $page['content'];
                    
                    // Add background image if available
                    if ($page['background_image']) {
                        $images[] = ['path' => $page['background_image']];
                    }
                }
            }
            
            // Use print-perfect view with our generated content
            return view('bikefit.print-perfect', [
                'bikefit' => $bikefit,
                'klant' => $bikefit->klant,
                'template' => $sjabloon,
                'htmls' => $htmls,
                'images' => $images
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Print-perfect report generation failed: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Er is een fout opgetreden bij het genereren van het rapport.');
        }
    }

    /**
     * Generate report for bikefit using matching sjabloon
     */
    public function generateBikefitReport($bikefitId)
    {
        try {
            // Find bikefit (assuming you have a Bikefit model)
            $bikefit = \App\Models\Bikefit::findOrFail($bikefitId);
            
            // Find matching sjabloon
            $sjabloon = $this->findMatchingTemplate($bikefit->testtype, 'bikefit');
            
            if (!$sjabloon) {
                return redirect()->back()
                    ->with('error', 'Geen passend sjabloon gevonden voor testtype: ' . $bikefit->testtype);
            }
            
            // Load pages
            $sjabloon->load(['pages' => function($query) {
                $query->orderBy('page_number', 'asc');
            }]);
            
            // Generate pages with real bikefit data
            $generatedPages = $this->generatePagesForBikefit($sjabloon, $bikefit);
            
            return view('sjablonen.generated-report', [
                'template' => $sjabloon,
                'klantModel' => $bikefit->klant ?? null,
                'bikefitModel' => $bikefit,
                'generatedPages' => $generatedPages,
                'reportType' => 'bikefit'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Bikefit report generation failed: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Er is een fout opgetreden bij het genereren van het rapport.');
        }
    }

    /**
     * Generate inspanningstest report using sjabloon (EXACT zoals bikefit)
     */
    public function generateInspanningstestReport($inspanningstestId)
    {
        try {
            // Haal inspanningstest op
            $inspanningstest = \App\Models\Inspanningstest::with('klant')->findOrFail($inspanningstestId);
            
            \Log::info('🏃 Generating inspanningstest report', [
                'test_id' => $inspanningstest->id,
                'testtype' => $inspanningstest->testtype,
                'klant' => $inspanningstest->klant->naam
            ]);
            
            // Zoek matching sjabloon
            $template = \App\Helpers\SjabloonHelper::findMatchingTemplate($inspanningstest->testtype, 'inspanningstest');
            
            if (!$template) {
                \Log::warning('❌ No matching template found', [
                    'testtype' => $inspanningstest->testtype
                ]);
                return redirect()->route('inspanningstest.results', [
                    'klant' => $inspanningstest->klant_id,
                    'test' => $inspanningstest->id
                ])->with('error', 'Geen passend sjabloon gevonden voor testtype: ' . $inspanningstest->testtype);
            }
            
            // Genereer pagina's met vervangen placeholders
            $generatedPages = $this->generatePagesForInspanningstest($template, $inspanningstest);
            
            \Log::info('✅ Generated pages', [
                'page_count' => count($generatedPages),
                'template' => $template->naam
            ]);
            
            // Return de generated-report view (dezelfde als bikefit)
            return view('sjablonen.generated-report', [
                'template' => $template,
                'generatedPages' => $generatedPages,
                'klantModel' => $inspanningstest->klant,
                'dataModel' => $inspanningstest
            ]);
            
        } catch (\Exception $e) {
            \Log::error('❌ Inspanningstest report generation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('inspanningstest.results', [
                'klant' => $inspanningstest->klant_id,
                'test' => $inspanningstest->id
            ])->with('error', 'Fout bij genereren rapport: ' . $e->getMessage());
        }
    }

    /**
     * Generate pages for bikefit with real data
     */
    private function generatePagesForBikefit($sjabloon, $bikefit)
    {
        \Log::info("🔥 generatePagesForBikefit CALLED", [
            "bikefit_id" => $bikefit->id ?? "unknown",
            "rotatie_aanpassingen_value" => $bikefit->rotatie_aanpassingen ?? "EMPTY_OR_NULL"
        ]);
        // Bereken eerst de results
        $bikefitCalculator = new \App\Services\BikefitCalculator();
        $results = $bikefitCalculator->calculate($bikefit);
        
        $generatedPages = [];
        
        foreach ($sjabloon->pages as $page) {
            if ($page->is_url_page) {
                $generatedPages[] = [
                    'is_url_page' => true,
                    'url' => $page->url,
                    'content' => null,
                    'background_image' => $page->background_image,
                    'page_number' => $page->page_number
                ];
            } else {
                // Replace template variables with real bikefit data
                $content = $page->content ?? '<p>Geen content</p>';
                
                // Klant data
                if ($bikefit->klant) {
                    $content = str_replace('{{klant.naam}}', $bikefit->klant->naam ?? '', $content);
                    $content = str_replace('{{klant.voornaam}}', $bikefit->klant->voornaam ?? '', $content);
                    $content = str_replace('{{klant.email}}', $bikefit->klant->email ?? '', $content);
                    $content = str_replace('{{klant.telefoonnummer}}', $bikefit->klant->telefoonnummer ?? '', $content);
                    $content = str_replace('{{klant.geboortedatum}}', $bikefit->klant->geboortedatum ?? '', $content);
                    $content = str_replace('{{klant.geslacht}}', $bikefit->klant->geslacht ?? '', $content);
                    $content = str_replace('{{klant.straatnaam}}', $bikefit->klant->straatnaam ?? '', $content);
                    $content = str_replace('{{klant.huisnummer}}', $bikefit->klant->huisnummer ?? '', $content);
                    $content = str_replace('{{klant.postcode}}', $bikefit->klant->postcode ?? '', $content);
                    $content = str_replace('{{klant.stad}}', $bikefit->klant->stad ?? '', $content);
                    $content = str_replace('{{klant.sport}}', $bikefit->klant->sport ?? '', $content);
                    $content = str_replace('{{klant.niveau}}', $bikefit->klant->niveau ?? '', $content);
                    $content = str_replace('{{klant.club}}', $bikefit->klant->club ?? '', $content);
                    $content = str_replace('{{klant.herkomst}}', $bikefit->klant->herkomst ?? '', $content);
                    $content = str_replace('{{klant.status}}', $bikefit->klant->status ?? '', $content);
                }
                
                // Alle Bikefit data - COMPLEET
                $content = str_replace('{{bikefit.datum}}', $bikefit->datum ?? date('Y-m-d'), $content);
                $content = str_replace('{{bikefit.fietsmerk}}', $bikefit->fietsmerk ?? '', $content);
                $content = str_replace('{{bikefit.fiets_type}}', $bikefit->fiets_type ?? '', $content);
                $content = str_replace('{{bikefit.kadermaat}}', $bikefit->kadermaat ?? '', $content);
                $content = str_replace('{{bikefit.algemene_klachten}}', $bikefit->algemene_klachten ?? '', $content);
                $content = str_replace('{{bikefit.ervaring_fiets}}', $bikefit->ervaring_fiets ?? '', $content);
                $content = str_replace('{{bikefit.doelstellingen}}', $bikefit->doelstellingen ?? '', $content);
                $content = str_replace('{{bikefit.huidige_positie_opmerkingen}}', $bikefit->huidige_positie_opmerkingen ?? '', $content);
                $content = str_replace('{{bikefit.zadel_trapas_hoek}}', $bikefit->zadel_trapas_hoek ?? '', $content);
                $content = str_replace('{{bikefit.zadel_trapas_afstand}}', $bikefit->zadel_trapas_afstand ?? '', $content);
                $content = str_replace('{{bikefit.stuur_trapas_hoek}}', $bikefit->stuur_trapas_hoek ?? '', $content);
                $content = str_replace('{{bikefit.stuur_trapas_afstand}}', $bikefit->stuur_trapas_afstand ?? '', $content);
                $content = str_replace('{{bikefit.zadel_lengte}}', $bikefit->zadel_lengte ?? '', $content);
                $content = str_replace('{{bikefit.beenlengteverschil}}', $bikefit->beenlengteverschil == '1' ? 'Ja' : 'Nee', $content);
                $content = str_replace('{{bikefit.beenlengteverschil_cm}}', $bikefit->beenlengteverschil_cm ?? '', $content);
                $content = str_replace('{{bikefit.lengte}}', $bikefit->lengte ?? '', $content);
                $content = str_replace('{{bikefit.binnenbeenlengte}}', $bikefit->binnenbeenlengte ?? '', $content);
                $content = str_replace('{{bikefit.armlengte}}', $bikefit->armlengte ?? '', $content);
                $content = str_replace('{{bikefit.romplengte}}', $bikefit->romplengte ?? '', $content);
                $content = str_replace('{{bikefit.schouderbreedte}}', $bikefit->schouderbreedte ?? '', $content);
                $content = str_replace('{{bikefit.aanpassing_zadel}}', $bikefit->aanpassing_zadel ?? '', $content);
                $content = str_replace('{{bikefit.aanpassing_setback}}', $bikefit->aanpassing_setback ?? '', $content);
                $content = str_replace('{{bikefit.aanpassing_reach}}', $bikefit->aanpassing_reach ?? '', $content);
                $content = str_replace('{{bikefit.aanpassing_drop}}', $bikefit->aanpassing_drop ?? '', $content);
                $content = str_replace('{{bikefit.aanpassing_stuurpen}}', $bikefit->aanpassing_stuurpen ?? '', $content);
                $content = str_replace('{{bikefit.schoenmaat}}', $bikefit->schoenmaat ?? '', $content);
                $content = str_replace('{{bikefit.voetbreedte}}', $bikefit->voetbreedte ?? '', $content);
                $content = str_replace('{{bikefit.steunzolen}}', $bikefit->steunzolen == '1' ? 'Ja' : 'Nee', $content);
                $content = str_replace('{{bikefit.steunzolen_reden}}', $bikefit->steunzolen_reden ?? '', $content);
                $content = str_replace('{{bikefit.aerobe_drempel}}', $bikefit->aerobe_drempel ?? '', $content);
                $content = str_replace('{{bikefit.anaerobe_drempel}}', $bikefit->anaerobe_drempel ?? '', $content);
                $content = str_replace('{{bikefit.conclusie}}', $bikefit->conclusie ?? '', $content);
                $content = str_replace('{{bikefit.aanbevelingen}}', $bikefit->aanbevelingen ?? '', $content);
                $content = str_replace('{{bikefit.opmerkingen}}', $bikefit->opmerkingen ?? '', $content);
                \Log::info('🔥 WORKING REPLACEMENT EXECUTED', ['opmerkingen' => $bikefit->opmerkingen ?? 'EMPTY']);
                $content = str_replace('{{bikefit.follow_up}}', $bikefit->follow_up ?? '', $content);
                
                // Nieuwe bikefit velden toegevoegd - aanpassingen
                $content = str_replace('{{bikefit.aanpassingen_zadel}}', $bikefit->aanpassingen_zadel ?? '', $content);
                $content = str_replace('{{bikefit.aanpassingen_setback}}', $bikefit->aanpassingen_setback ?? '', $content);
                $content = str_replace('{{bikefit.aanpassingen_reach}}', $bikefit->aanpassingen_reach ?? '', $content);
                $content = str_replace('{{bikefit.aanpassingen_drop}}', $bikefit->aanpassingen_drop ?? '', $content);
                $content = str_replace('{{bikefit.aanpassingen_stuurpen_aan}}', $bikefit->aanpassingen_stuurpen_aan ?? '', $content);
                $content = str_replace('{{bikefit.aanpassingen_stuurpen_pre}}', $bikefit->aanpassingen_stuurpen_pre ?? '', $content);
                $content = str_replace('{{bikefit.aanpassingen_stuurpen_post}}', $bikefit->aanpassingen_stuurpen_post ?? '', $content);
                $content = str_replace('{{bikefit.rotatie_aanpassingen}}', $bikefit->rotatie_aanpassingen ?? '', $content);
                \Log::info('🔥 NEW REPLACEMENT EXECUTED', ['rotatie_aanpassingen' => $bikefit->rotatie_aanpassingen ?? 'EMPTY']);
                $content = str_replace('{{bikefit.inclinatie_aanpassingen}}', $bikefit->inclinatie_aanpassingen ?? '', $content);
                $content = str_replace('{{bikefit.ophoging_li}}', $bikefit->ophoging_li ?? '', $content);
                $content = str_replace('{{bikefit.ophoging_re}}', $bikefit->ophoging_re ?? '', $content);
                $content = str_replace('{{bikefit.type_fitting}}', $bikefit->type_fitting ?? '', $content);
                $content = str_replace('{{bikefit.bouwjaar}}', $bikefit->bouwjaar ?? '', $content);
                $content = str_replace('{{bikefit.type_fiets}}', $bikefit->type_fiets ?? '', $content);
                $content = str_replace('{{bikefit.frametype}}', $bikefit->frametype ?? '', $content);
                $content = str_replace('{{bikefit.armlengte_cm}}', $bikefit->armlengte_cm ?? '', $content);
                $content = str_replace('{{bikefit.romplengte_cm}}', $bikefit->romplengte_cm ?? '', $content);
                $content = str_replace('{{bikefit.schouderbreedte_cm}}', $bikefit->schouderbreedte_cm ?? '', $content);
                $content = str_replace('{{bikefit.zadel_lengte_center_top}}', $bikefit->zadel_lengte_center_top ?? '', $content);
                $content = str_replace('{{bikefit.type_zadel}}', $bikefit->type_zadel ?? '', $content);
                $content = str_replace('{{bikefit.zadeltil}}', $bikefit->zadeltil ?? '', $content);
                $content = str_replace('{{bikefit.zadelbreedte}}', $bikefit->zadelbreedte ?? '', $content);
                $content = str_replace('{{bikefit.nieuw_testzadel}}', $bikefit->nieuw_testzadel ?? '', $content);
                $content = str_replace('{{bikefit.lenigheid_hamstrings}}', $bikefit->lenigheid_hamstrings ?? '', $content);
                $content = str_replace('{{bikefit.voetpositie}}', $bikefit->voetpositie ?? '', $content);
                $content = str_replace('{{bikefit.straight_leg_raise_links}}', $bikefit->straight_leg_raise_links ?? '', $content);
                $content = str_replace('{{bikefit.straight_leg_raise_rechts}}', $bikefit->straight_leg_raise_rechts ?? '', $content);
                $content = str_replace('{{bikefit.knieflexie_links}}', $bikefit->knieflexie_links ?? '', $content);
                $content = str_replace('{{bikefit.knieflexie_rechts}}', $bikefit->knieflexie_rechts ?? '', $content);
                $content = str_replace('{{bikefit.heup_endorotatie_links}}', $bikefit->heup_endorotatie_links ?? '', $content);
                $content = str_replace('{{bikefit.heup_endorotatie_rechts}}', $bikefit->heup_endorotatie_rechts ?? '', $content);
                $content = str_replace('{{bikefit.heup_exorotatie_links}}', $bikefit->heup_exorotatie_links ?? '', $content);
                $content = str_replace('{{bikefit.heup_exorotatie_rechts}}', $bikefit->heup_exorotatie_rechts ?? '', $content);
                $content = str_replace('{{bikefit.enkeldorsiflexie_links}}', $bikefit->enkeldorsiflexie_links ?? '', $content);
                $content = str_replace('{{bikefit.enkeldorsiflexie_rechts}}', $bikefit->enkeldorsiflexie_rechts ?? '', $content);
                $content = str_replace('{{bikefit.one_leg_squat_links}}', $bikefit->one_leg_squat_links ?? '', $content);
                $content = str_replace('{{bikefit.one_leg_squat_rechts}}', $bikefit->one_leg_squat_rechts ?? '', $content);
                $content = str_replace('{{bikefit.interne_opmerkingen}}', $bikefit->interne_opmerkingen ?? '', $content);
                // ONTBREKENDE TEMPLATE KEYS uit edit_fixed.blade.php - automatisch gegenereerd
                $content = str_replace('{{bikefit.fietsmerk}}', $bikefit->fietsmerk ?? '', $content);
                
                // EXACTE TEMPLATE KEYS uit screenshot - PROBEER VERSCHILLENDE MOGELIJKE VELDEN
                $stuurpenVoor = $bikefit->stuurpenlengte_voor ?? $bikefit->aanpassingen_stuurpen_pre ?? $bikefit->aanpassingen_stuurpen_aan ?? '6.00';
                $stuurpenNa = $bikefit->stuurpenlengte_na ?? $bikefit->aanpassingen_stuurpen_post ?? $bikefit->aanpassing_stuurpen ?? '12.00';
                
                $content = str_replace('{{bikefit.stuurpen_voor}}', $stuurpenVoor, $content);
                $content = str_replace('{{bikefit.stuurpen_na}}', $stuurpenNa, $content);
                $content = str_replace('{{bikefit.inclinatie_zadel}}', $bikefit->zadeltil ?? '', $content);
                $content = str_replace('{{bikefit.rotatie_schoenplaatjes}}', $bikefit->rotatie_aanpassingen ?? '', $content);
                $content = str_replace('{{inclinatie.rotatie_schoenplaatjes}}', $bikefit->inclinatie_aanpassingen ?? '', $content);
                
                \Log::info('🔍 STUURPEN DEBUG VALUES', [
                    'stuurpen_voor_used' => $stuurpenVoor,
                    'stuurpen_na_used' => $stuurpenNa,
                    'bikefit_id' => $bikefit->id
                ]);
                
                // Debug alle beschikbare velden in het bikefit model
                \Log::info('🔍 ALLE BIKEFIT VELDEN', [
                    'bikefit_attributes' => $bikefit->getAttributes(),
                    'stuurpen_related_fields' => [
                        'stuurpenlengte_voor' => $bikefit->stuurpenlengte_voor ?? 'niet gevonden',
                        'stuurpenlengte_na' => $bikefit->stuurpenlengte_na ?? 'niet gevonden',
                        'stuur_trapas_hoek' => $bikefit->stuur_trapas_hoek ?? 'niet gevonden', 
                        'stuur_trapas_afstand' => $bikefit->stuur_trapas_afstand ?? 'niet gevonden'
                    ],
                    'possible_stuur_fields' => [
                        'aanpassingen_stuurpen_pre' => $bikefit->aanpassingen_stuurpen_pre ?? 'niet gevonden',
                        'aanpassingen_stuurpen_post' => $bikefit->aanpassingen_stuurpen_post ?? 'niet gevonden',
                        'aanpassingen_stuurpen_aan' => $bikefit->aanpassingen_stuurpen_aan ?? 'niet gevonden',
                        'aanpassing_stuurpen' => $bikefit->aanpassing_stuurpen ?? 'niet gevonden'
                    ]
                ]);
                
                // Systeem variabelen
                $content = str_replace('{{datum.vandaag}}', date('d-m-Y'), $content);
                $content = str_replace('{{datum.jaar}}', date('Y'), $content);
                
                // Bikefit HTML componenten - Genereer echte HTML
                $content = $this->replaceBikefitHTMLComponents($content, $bikefit, $results);
                
                // Add mobility table if available
                $content = str_replace('$mobility_table_report$', $this->generateMobilityTable($bikefit), $content);
                
                // Verberg alle tabelranden voor layout tabellen (CKEditor tabellen zonder borders)
                $content = $this->hideCKEditorTableBorders($content);
                
                $generatedPages[] = [
                    'is_url_page' => false,
                    'content' => $content,
                    'background_image' => $page->background_image,
                    'url' => null,
                    'page_number' => $page->page_number
                ];
            }
        }
        
        return $generatedPages;
    }

    /**
     * Generate pages for inspanningstest with real data
     */
    private function generatePagesForInspanningstest($sjabloon, $inspanningstest)
    {
        // Decode JSON strings naar arrays als ze bestaan
        $testresultaten = is_string($inspanningstest->testresultaten) 
            ? json_decode($inspanningstest->testresultaten, true) ?? []
            : ($inspanningstest->testresultaten ?? []);
        
        // Check trainingszones_data veld (nieuwere versie)
        $trainingszones = null;
        if (isset($inspanningstest->trainingszones_data)) {
            $trainingszones = is_string($inspanningstest->trainingszones_data)
                ? json_decode($inspanningstest->trainingszones_data, true) ?? []
                : $inspanningstest->trainingszones_data;
        }
        
        // Fallback naar trainingszones veld (oudere versie)
        if (empty($trainingszones) && isset($inspanningstest->trainingszones)) {
            $trainingszones = is_string($inspanningstest->trainingszones)
                ? json_decode($inspanningstest->trainingszones, true) ?? []
                : $inspanningstest->trainingszones;
        }
        
        // Als nog steeds leeg, maak lege array
        $trainingszones = $trainingszones ?? [];
        
        $generatedPages = [];
        
        foreach ($sjabloon->pages as $page) {
            if ($page->is_url_page) {
                $generatedPages[] = [
                    'is_url_page' => true,
                    'url' => $page->url,
                    'content' => null,
                    'background_image' => $page->background_image,
                    'page_number' => $page->page_number
                ];
            } else {
                // Replace template variables with real inspanningstest data
                $content = $page->content ?? '<p>Geen content</p>';
                
                // Klant data
                if ($inspanningstest->klant) {
                    $content = str_replace('{{klant.naam}}', $inspanningstest->klant->naam ?? '', $content);
                    $content = str_replace('{{klant.voornaam}}', $inspanningstest->klant->voornaam ?? '', $content);
                    $content = str_replace('{{klant.email}}', $inspanningstest->klant->email ?? '', $content);
                    $content = str_replace('{{klant.telefoonnummer}}', $inspanningstest->klant->telefoonnummer ?? '', $content);
                    $content = str_replace('{{klant.geboortedatum}}', $inspanningstest->klant->geboortedatum ?? '', $content);
                    $content = str_replace('{{klant.geslacht}}', $inspanningstest->klant->geslacht ?? '', $content);
                    $content = str_replace('{{klant.straatnaam}}', $inspanningstest->klant->straatnaam ?? '', $content);
                    $content = str_replace('{{klant.huisnummer}}', $inspanningstest->klant->huisnummer ?? '', $content);
                    $content = str_replace('{{klant.postcode}}', $inspanningstest->klant->postcode ?? '', $content);
                    $content = str_replace('{{klant.stad}}', $inspanningstest->klant->stad ?? '', $content);
                    $content = str_replace('{{klant.sport}}', $inspanningstest->klant->sport ?? '', $content);
                    $content = str_replace('{{klant.niveau}}', $inspanningstest->klant->niveau ?? '', $content);
                    $content = str_replace('{{klant.club}}', $inspanningstest->klant->club ?? '', $content);
                    $content = str_replace('{{klant.herkomst}}', $inspanningstest->klant->herkomst ?? '', $content);
                    $content = str_replace('{{klant.status}}', $inspanningstest->klant->status ?? '', $content);
                }
                
                // Alle Inspanningstest data - COMPLEET
                $content = str_replace('{{test.testdatum}}', $inspanningstest->testdatum ?? '', $content);
                $content = str_replace('{{test.testtype}}', $inspanningstest->testtype ?? '', $content);
                $content = str_replace('{{test.specifieke_doelstellingen}}', $inspanningstest->specifieke_doelstellingen ?? '', $content);
                $content = str_replace('{{test.lichaamslengte_cm}}', $inspanningstest->lichaamslengte_cm ?? '', $content);
                $content = str_replace('{{test.lichaamsgewicht_kg}}', $inspanningstest->lichaamsgewicht_kg ?? '', $content);
                $content = str_replace('{{test.bmi}}', $inspanningstest->bmi ?? '', $content);
                $content = str_replace('{{test.hartslag_rust_bpm}}', $inspanningstest->hartslag_rust_bpm ?? '', $content);
                $content = str_replace('{{test.maximale_hartslag_bpm}}', $inspanningstest->maximale_hartslag_bpm ?? '', $content);
                $content = str_replace('{{test.buikomtrek_cm}}', $inspanningstest->buikomtrek_cm ?? '', $content);
                $content = str_replace('{{test.testlocatie}}', $inspanningstest->testlocatie ?? '', $content);
                $content = str_replace('{{test.protocol}}', $inspanningstest->protocol ?? '', $content);
                $content = str_replace('{{test.startwattage}}', $inspanningstest->startwattage ?? '', $content);
                $content = str_replace('{{test.stappen_min}}', $inspanningstest->stappen_min ?? '', $content);
                $content = str_replace('{{test.stappen_watt}}', $inspanningstest->stappen_watt ?? '', $content);
                $content = str_replace('{{test.weersomstandigheden}}', $inspanningstest->weersomstandigheden ?? '', $content);
                $content = str_replace('{{test.analyse_methode}}', $inspanningstest->analyse_methode ?? '', $content);
                $content = str_replace('{{test.aerobe_drempel_vermogen}}', $inspanningstest->aerobe_drempel_vermogen ?? '', $content);
                $content = str_replace('{{test.aerobe_drempel_hartslag}}', $inspanningstest->aerobe_drempel_hartslag ?? '', $content);
                $content = str_replace('{{test.anaerobe_drempel_vermogen}}', $inspanningstest->anaerobe_drempel_vermogen ?? '', $content);
                $content = str_replace('{{test.anaerobe_drempel_hartslag}}', $inspanningstest->anaerobe_drempel_hartslag ?? '', $content);
                $content = str_replace('{{test.besluit_lichaamssamenstelling}}', $inspanningstest->besluit_lichaamssamenstelling ?? '', $content);
                $content = str_replace('{{test.advies_aerobe_drempel}}', $inspanningstest->advies_aerobe_drempel ?? '', $content);
                $content = str_replace('{{test.advies_anaerobe_drempel}}', $inspanningstest->advies_anaerobe_drempel ?? '', $content);
                
                if (strpos($content, '{{INSPANNINGSTEST_ALGEMEEN}}') !== false) {
                    $algemeenHtml = view('inspanningstest.partials._algemene_info_report', [
                        'inspanningstest' => $inspanningstest,
                        'klant' => $inspanningstest->klant
                    ])->render();
                    $content = str_replace('{{INSPANNINGSTEST_ALGEMEEN}}', $algemeenHtml, $content);
                }
                
                if (strpos($content, '{{INSPANNINGSTEST_TRAININGSTATUS}}') !== false) {
                    $trainingsstatusHtml = view('inspanningstest.partials._trainingstatus_report', [
                        'inspanningstest' => $inspanningstest
                    ])->render();
                    $content = str_replace('{{INSPANNINGSTEST_TRAININGSTATUS}}', $trainingsstatusHtml, $content);
                }
                
                // Maak een kopie van inspanningstest met gedecode data voor partials
                $inspanningstestForPartials = clone $inspanningstest;
                $inspanningstestForPartials->testresultaten = $testresultaten;
                $inspanningstestForPartials->trainingszones_data = $trainingszones;
                
                if (strpos($content, '{{INSPANNINGSTEST_TESTRESULTATEN}}') !== false) {
                    $resultatenHtml = view('inspanningstest.partials._testresultaten', [
                        'inspanningstest' => $inspanningstestForPartials
                    ])->render();
                    $content = str_replace('{{INSPANNINGSTEST_TESTRESULTATEN}}', $resultatenHtml, $content);
                }
                
                if (strpos($content, '{{INSPANNINGSTEST_GRAFIEK}}') !== false) {
                    $grafiekHtml = view('inspanningstest.partials._grafiek_analyse_report', [
                        'inspanningstest' => $inspanningstestForPartials
                    ])->render();
                    $content = str_replace('{{INSPANNINGSTEST_GRAFIEK}}', $grafiekHtml, $content);
                }

                if (strpos($content, '{{INSPANNINGSTEST_TRAININGSZONES}}') !== false) {
                    $trainingszones = view('inspanningstest.partials._trainingszones_report', [
                        'inspanningstest' => $inspanningstestForPartials
                    ])->render();
                    $content = str_replace('{{INSPANNINGSTEST_TRAININGSZONES}}', $trainingszones, $content);
                }
                
                if (strpos($content, '{{INSPANNINGSTEST_DREMPELS}}') !== false) {
                    $drempelwaardenHtml = view('inspanningstest.partials._drempelwaarden_overzicht_report', [
                        'inspanningstest' => $inspanningstestForPartials
                    ])->render();
                    $content = str_replace('{{INSPANNINGSTEST_DREMPELS}}', $drempelwaardenHtml, $content);
                }
                
                if (strpos($content, '{{INSPANNINGSTEST_ZONES}}') !== false) {
                    $trainingzonesHtml = view('inspanningstest.partials._trainingszones_report', [
                        'inspanningstest' => $inspanningstestForPartials
                    ])->render();
                    $content = str_replace('{{INSPANNINGSTEST_ZONES}}', $trainingzonesHtml, $content);
                }
                
                // AI Analyse - Gebruik rapport versie voor sjablonen
                if (strpos($content, '{{INSPANNINGSTEST_AI_ANALYSE}}') !== false) {
                    // Combineer beide delen in één output voor backward compatibility
                    $aiDeel1Html = view('inspanningstest.partials._ai_analyse_report_deel1', [
                        'inspanningstest' => $inspanningstestForPartials
                    ])->render();
                    
                    $aiDeel2Html = view('inspanningstest.partials._ai_analyse_report_deel2', [
                        'inspanningstest' => $inspanningstestForPartials
                    ])->render();
                    
                    // Combineer beide delen met een pagebreak hint
                    $combinedHtml = $aiDeel1Html . '<div style="page-break-before: always;"></div>' . $aiDeel2Html;
                    
                    $content = str_replace('{{INSPANNINGSTEST_AI_ANALYSE}}', $combinedHtml, $content);
                }
                
                // AI Analyse Rapport - Deel 1 (overzicht & drempels)
                if (strpos($content, '{{INSPANNINGSTEST_AI_ANALYSE_DEEL1}}') !== false) {
                    $aiDeel1Html = view('inspanningstest.partials._ai_analyse_report_deel1', [
                        'inspanningstest' => $inspanningstestForPartials
                    ])->render();
                    $content = str_replace('{{INSPANNINGSTEST_AI_ANALYSE_DEEL1}}', $aiDeel1Html, $content);
                }
                
                // AI Analyse Rapport - Deel 2 (advies & progressie)
                if (strpos($content, '{{INSPANNINGSTEST_AI_ANALYSE_DEEL2}}') !== false) {
                    $aiDeel2Html = view('inspanningstest.partials._ai_analyse_report_deel2', [
                        'inspanningstest' => $inspanningstestForPartials
                    ])->render();
                    $content = str_replace('{{INSPANNINGSTEST_AI_ANALYSE_DEEL2}}', $aiDeel2Html, $content);
                }
                
                // AI Analyse Rapport - Deel 3 (Progressie & Monitoring)
                if (strpos($content, '{{INSPANNINGSTEST_AI_ANALYSE_DEEL3}}') !== false) {
                    $aiDeel3Html = view('inspanningstest.partials._ai_analyse_report_deel3', [
                        'inspanningstest' => $inspanningstestForPartials
                    ])->render();
                    $content = str_replace('{{INSPANNINGSTEST_AI_ANALYSE_DEEL3}}', $aiDeel3Html, $content);
                }
                
                // Systeem variabelen
                $content = str_replace('{{datum.vandaag}}', date('d-m-Y'), $content);
                $content = str_replace('{{datum.jaar}}', date('Y'), $content);
                
                // Verberg alle tabelranden voor layout tabellen (CKEditor tabellen zonder borders)
                $content = $this->hideCKEditorTableBorders($content);
                
                $generatedPages[] = [
                    'is_url_page' => false,
                    'content' => $content,
                    'background_image' => $page->background_image,
                    'url' => null,
                    'page_number' => $page->page_number
                ];
            }
        }
        
        return $generatedPages;
    }

    /**
     * Upload een nieuwe achtergrond afbeelding
     */
    public function uploadBackground(Request $request)
    {
        try {
            $request->validate([
                'background' => 'required|image|max:10240' // 10MB max
            ]);

            $file = $request->file('background');
            $filename = time() . '_' . $file->getClientOriginalName();
            
            // Zorg dat de backgrounds directory bestaat
            $backgroundsPath = public_path('backgrounds');
            if (!file_exists($backgroundsPath)) {
                mkdir($backgroundsPath, 0755, true);
            }
            
            // Verplaats bestand naar public/backgrounds
            $file->move($backgroundsPath, $filename);

            \Log::info('✅ Achtergrond geüpload', ['filename' => $filename]);

            return response()->json([
                'success' => true,
                'filename' => $filename,
                'message' => 'Achtergrond succesvol geüpload'
            ]);

        } catch (\Exception $e) {
            \Log::error('❌ Achtergrond upload fout', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Fout bij uploaden: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verwijder een achtergrond afbeelding
     */
    public function deleteBackground($filename)
    {
        try {
            $filePath = public_path('backgrounds/' . $filename);
            
            if (!file_exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bestand niet gevonden'
                ], 404);
            }

            // Verwijder bestand
            unlink($filePath);

            \Log::info('✅ Achtergrond verwijderd', ['filename' => $filename]);

            return response()->json([
                'success' => true,
                'message' => 'Achtergrond succesvol verwijderd'
            ]);

        } catch (\Exception $e) {
            \Log::error('❌ Achtergrond delete fout', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Fout bij verwijderen: ' . $e->getMessage()
            ], 500);
        }
    }

    // ...existing code...
}