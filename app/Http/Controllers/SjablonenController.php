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
        
        // Get template keys for the sidebar
        $templateKeys = collect([
            'klant' => [
                (object)['placeholder' => '{{klant.naam}}', 'display_name' => 'Klant Naam'],
                (object)['placeholder' => '{{klant.voornaam}}', 'display_name' => 'Klant Voornaam'],
                (object)['placeholder' => '{{klant.email}}', 'display_name' => 'Klant Email'],
                (object)['placeholder' => '{{klant.geboortedatum}}', 'display_name' => 'Geboortedatum'],
            ],
            'bikefit' => [
                (object)['placeholder' => '{{bikefit.datum}}', 'display_name' => 'Bikefit Datum'],
                (object)['placeholder' => '{{bikefit.testtype}}', 'display_name' => 'Test Type'],
                (object)['placeholder' => '{{bikefit.lengte_cm}}', 'display_name' => 'Lengte (cm)'],
                (object)['placeholder' => '{{bikefit.binnenbeenlengte_cm}}', 'display_name' => 'Binnenbeenlengte (cm)'],
                (object)['placeholder' => '$mobility_table_report$', 'display_name' => 'Mobiliteit Tabel'],
            ]
        ]);
        
        return view('sjablonen.edit', compact('sjabloon', 'templateKeys'));
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
                
                $generatedPages[] = [
                    'is_url_page' => false,
                    'content' => $content,
                    'background_image' => $page->background_image,
                    'url' => null,
                    'page_number' => $page->page_number
                ];
            }
        }
        
        // Debug: Log how many pages we're sending to view
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
}