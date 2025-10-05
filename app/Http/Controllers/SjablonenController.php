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
        
        // Get template keys for the sidebar - alleen werkende database velden
        $templateKeys = collect([
            'klant' => [
                (object)['placeholder' => '{{klant.voornaam}}', 'display_name' => 'Voornaam'],
                (object)['placeholder' => '{{klant.naam}}', 'display_name' => 'Naam'],
                (object)['placeholder' => '{{klant.email}}', 'display_name' => 'E-mailadres'],
                (object)['placeholder' => '{{klant.telefoonnummer}}', 'display_name' => 'Telefoonnummer'],
                (object)['placeholder' => '{{klant.geboortedatum}}', 'display_name' => 'Geboortedatum'],
                (object)['placeholder' => '{{klant.geslacht}}', 'display_name' => 'Geslacht'],
                (object)['placeholder' => '{{klant.straatnaam}}', 'display_name' => 'Straatnaam'],
                (object)['placeholder' => '{{klant.huisnummer}}', 'display_name' => 'Huisnummer'],
                (object)['placeholder' => '{{klant.postcode}}', 'display_name' => 'Postcode'],
                (object)['placeholder' => '{{klant.stad}}', 'display_name' => 'Stad'],
                (object)['placeholder' => '{{klant.sport}}', 'display_name' => 'Sport'],
                (object)['placeholder' => '{{klant.niveau}}', 'display_name' => 'Niveau'],
                (object)['placeholder' => '{{klant.club}}', 'display_name' => 'Club / Ploeg'],
                (object)['placeholder' => '{{klant.herkomst}}', 'display_name' => 'Herkomst'],
                (object)['placeholder' => '{{klant.status}}', 'display_name' => 'Status'],
            ],
            'bikefit' => [
                (object)['placeholder' => '{{bikefit.datum}}', 'display_name' => 'Bikefit Datum'],
                (object)['placeholder' => '{{bikefit.fietsmerk}}', 'display_name' => 'Fiets Merk'],
                (object)['placeholder' => '{{bikefit.fiets_type}}', 'display_name' => 'Fiets Type'],
                (object)['placeholder' => '{{bikefit.kadermaat}}', 'display_name' => 'Kadermaat'],
                (object)['placeholder' => '{{bikefit.algemene_klachten}}', 'display_name' => 'Algemene Klachten'],
                (object)['placeholder' => '{{bikefit.ervaring_fiets}}', 'display_name' => 'Ervaring met Fiets'],
                (object)['placeholder' => '{{bikefit.doelstellingen}}', 'display_name' => 'Doelstellingen'],
                (object)['placeholder' => '{{bikefit.huidige_positie_opmerkingen}}', 'display_name' => 'Huidige Positie Opmerkingen'],
                (object)['placeholder' => '{{bikefit.zadel_trapas_hoek}}', 'display_name' => 'Zadel-trapas hoek (graden)'],
                (object)['placeholder' => '{{bikefit.zadel_trapas_afstand}}', 'display_name' => 'Zadel-trapas afstand (cm)'],
                (object)['placeholder' => '{{bikefit.stuur_trapas_hoek}}', 'display_name' => 'Stuur-trapas hoek (graden)'],
                (object)['placeholder' => '{{bikefit.stuur_trapas_afstand}}', 'display_name' => 'Stuur-trapas afstand (cm)'],
                (object)['placeholder' => '{{bikefit.zadel_lengte}}', 'display_name' => 'Zadel lengte (cm)'],
                (object)['placeholder' => '{{bikefit.beenlengteverschil}}', 'display_name' => 'Beenlengteverschil'],
                (object)['placeholder' => '{{bikefit.beenlengteverschil_cm}}', 'display_name' => 'Beenlengteverschil (cm)'],
                (object)['placeholder' => '{{bikefit.lengte}}', 'display_name' => 'Lengte (cm)'],
                (object)['placeholder' => '{{bikefit.binnenbeenlengte}}', 'display_name' => 'Binnenbeenlengte (cm)'],
                (object)['placeholder' => '{{bikefit.armlengte}}', 'display_name' => 'Armlengte (cm)'],
                (object)['placeholder' => '{{bikefit.romplengte}}', 'display_name' => 'Romplengte (cm)'],
                (object)['placeholder' => '{{bikefit.schouderbreedte}}', 'display_name' => 'Schouderbreedte (cm)'],
                (object)['placeholder' => '{{bikefit.aanpassing_zadel}}', 'display_name' => 'Aanpassing zadel (cm)'],
                (object)['placeholder' => '{{bikefit.aanpassing_setback}}', 'display_name' => 'Aanpassing setback (cm)'],
                (object)['placeholder' => '{{bikefit.aanpassing_reach}}', 'display_name' => 'Aanpassing reach (cm)'],
                (object)['placeholder' => '{{bikefit.aanpassing_drop}}', 'display_name' => 'Aanpassing drop (cm)'],
                (object)['placeholder' => '{{bikefit.aanpassing_stuurpen}}', 'display_name' => 'Aanpassing stuurpen (cm)'],
                (object)['placeholder' => '{{bikefit.schoenmaat}}', 'display_name' => 'Schoenmaat'],
                (object)['placeholder' => '{{bikefit.voetbreedte}}', 'display_name' => 'Voetbreedte (cm)'],
                (object)['placeholder' => '{{bikefit.steunzolen}}', 'display_name' => 'Steunzolen'],
                (object)['placeholder' => '{{bikefit.steunzolen_reden}}', 'display_name' => 'Steunzolen Reden'],
                (object)['placeholder' => '{{bikefit.aerobe_drempel}}', 'display_name' => 'Aërobe Drempel'],
                (object)['placeholder' => '{{bikefit.anaerobe_drempel}}', 'display_name' => 'Anaërobe Drempel'],
                (object)['placeholder' => '{{bikefit.conclusie}}', 'display_name' => 'Conclusie'],
                (object)['placeholder' => '{{bikefit.aanbevelingen}}', 'display_name' => 'Aanbevelingen'],
                (object)['placeholder' => '{{bikefit.opmerkingen}}', 'display_name' => 'Opmerkingen'],
                (object)['placeholder' => '{{bikefit.follow_up}}', 'display_name' => 'Follow-up'],
            ],
            'inspanningstest' => [
                (object)['placeholder' => '{{test.testdatum}}', 'display_name' => 'Testdatum'],
                (object)['placeholder' => '{{test.testtype}}', 'display_name' => 'Testtype'],
                (object)['placeholder' => '{{test.specifieke_doelstellingen}}', 'display_name' => 'Specifieke doelstellingen'],
                (object)['placeholder' => '{{test.lichaamslengte_cm}}', 'display_name' => 'Lengte (cm)'],
                (object)['placeholder' => '{{test.lichaamsgewicht_kg}}', 'display_name' => 'Gewicht (kg)'],
                (object)['placeholder' => '{{test.bmi}}', 'display_name' => 'BMI'],
                (object)['placeholder' => '{{test.hartslag_rust_bpm}}', 'display_name' => 'Hartslag rust (bpm)'],
                (object)['placeholder' => '{{test.maximale_hartslag_bpm}}', 'display_name' => 'Hartslag max (bpm)'],
                (object)['placeholder' => '{{test.buikomtrek_cm}}', 'display_name' => 'Buikomtrek (cm)'],
                (object)['placeholder' => '{{test.testlocatie}}', 'display_name' => 'Testlocatie'],
                (object)['placeholder' => '{{test.protocol}}', 'display_name' => 'Protocol'],
                (object)['placeholder' => '{{test.startwattage}}', 'display_name' => 'Start wattage'],
                (object)['placeholder' => '{{test.stappen_min}}', 'display_name' => 'Stappen (minuten)'],
                (object)['placeholder' => '{{test.stappen_watt}}', 'display_name' => 'Stappen (watt)'],
                (object)['placeholder' => '{{test.weersomstandigheden}}', 'display_name' => 'Weersomstandigheden'],
                (object)['placeholder' => '{{test.analyse_methode}}', 'display_name' => 'Analyse methode'],
                (object)['placeholder' => '{{test.aerobe_drempel_vermogen}}', 'display_name' => 'Aërobe drempel - Vermogen (W)'],
                (object)['placeholder' => '{{test.aerobe_drempel_hartslag}}', 'display_name' => 'Aërobe drempel - Hartslag (bpm)'],
                (object)['placeholder' => '{{test.anaerobe_drempel_vermogen}}', 'display_name' => 'Anaërobe drempel - Vermogen (W)'],
                (object)['placeholder' => '{{test.anaerobe_drempel_hartslag}}', 'display_name' => 'Anaërobe drempel - Hartslag (bpm)'],
                (object)['placeholder' => '{{test.besluit_lichaamssamenstelling}}', 'display_name' => 'Besluit Lichaamssamenstelling'],
                (object)['placeholder' => '{{test.advies_aerobe_drempel}}', 'display_name' => 'Advies Aërobe Drempel'],
                (object)['placeholder' => '{{test.advies_anaerobe_drempel}}', 'display_name' => 'Advies Anaërobe Drempel'],
            ],
            'systeem' => [
                (object)['placeholder' => '{{datum.vandaag}}', 'display_name' => 'Datum Vandaag'],
                (object)['placeholder' => '{{datum.jaar}}', 'display_name' => 'Huidig Jaar'],
            ],
            'bikefit_html_componenten' => [
                (object)['placeholder' => '$ResultatenVoor$', 'display_name' => 'Bikefit Resultaten VOOR (HTML Tabel)'],
                (object)['placeholder' => '$ResultatenNa$', 'display_name' => 'Bikefit Resultaten NA (HTML Tabel)'],
                (object)['placeholder' => '$Bikefit.prognose_zitpositie_html$', 'display_name' => 'Prognose Zitpositie (Schema + Tabel)'],
                (object)['placeholder' => '$MobiliteitTabel$', 'display_name' => 'Mobiliteit Resultaten (HTML Tabel)'],
                (object)['placeholder' => '$mobiliteitklant$', 'display_name' => 'Mobiliteit Klant Tabel (Gekleurde Balken)'],
                (object)['placeholder' => '$Bikefit.body_measurements_block_html$', 'display_name' => 'Lichaamsmaten Blok (HTML)'],
            ]
        ]);
        
        return view('sjablonen.edit', compact('sjabloon', 'templateKeys'));
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
     * Generate report for inspanningstest using matching sjabloon
     */
    public function generateInspanningstestReport($inspanningstestId)
    {
        try {
            // Find inspanningstest (assuming you have an Inspanningstest model)
            $inspanningstest = \App\Models\Inspanningstest::findOrFail($inspanningstestId);
            
            // Find matching sjabloon
            $sjabloon = $this->findMatchingTemplate($inspanningstest->testtype, 'inspanningstest');
            
            if (!$sjabloon) {
                return redirect()->back()
                    ->with('error', 'Geen passend sjabloon gevonden voor testtype: ' . $inspanningstest->testtype);
            }
            
            // Load pages
            $sjabloon->load(['pages' => function($query) {
                $query->orderBy('page_number', 'asc');
            }]);
            
            // Generate pages with real inspanningstest data
            $generatedPages = $this->generatePagesForInspanningstest($sjabloon, $inspanningstest);
            
            return view('sjablonen.generated-report', [
                'template' => $sjabloon,
                'klantModel' => $inspanningstest->klant ?? null,
                'inspanningstestModel' => $inspanningstest,
                'generatedPages' => $generatedPages,
                'reportType' => 'inspanningstest'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Inspanningstest report generation failed: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Er is een fout opgetreden bij het genereren van het rapport.');
        }
    }

    /**
     * Generate pages for bikefit with real data
     */
    private function generatePagesForBikefit($sjabloon, $bikefit)
    {
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
                $content = str_replace('{{bikefit.follow_up}}', $bikefit->follow_up ?? '', $content);
                
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
     * Generate mobility table HTML
     */
    private function generateMobilityTable($bikefit)
    {
        // This would generate the actual mobility table based on your bikefit data
        // For now, return placeholder
        return '<div class="mobility-table">
                    <h3>Mobiliteit Rapport</h3>
                    <p>Mobiliteit data voor: ' . ($bikefit->klant->naam ?? 'Onbekend') . '</p>
                    <!-- Add your actual mobility table generation logic here -->
                </div>';
    }
    
    /**
     * Hide table borders for CKEditor layout tables
     */
    private function hideCKEditorTableBorders($content)
    {
        // Voeg CSS toe om alle tabelranden te verbergen
        $css = '<style>
            table, table td, table th, table tr {
                border: none !important;
                border-collapse: collapse !important;
                border-spacing: 0 !important;
            }
            table {
                border: 0 !important;
                cellspacing: 0 !important;
                cellpadding: 5px !important;
            }
            td, th {
                border: 0 !important;
                padding: 5px !important;
            }
            /* Preview placeholder styling */
            .preview-placeholder {
                background: #f3f4f6;
                border: 2px dashed #9ca3af;
                padding: 20px;
                margin: 10px 0;
                text-align: center;
                border-radius: 8px;
                color: #6b7280;
            }
            .preview-placeholder h4 {
                margin: 0 0 10px 0;
                color: #374151;
            }
        </style>';
        
        // Voeg CSS toe aan het begin van de content
        return $css . $content;
    }
    
    /**
     * Replace Bikefit HTML components with actual rendered HTML
     */
    private function replaceBikefitHTMLComponents($content, $bikefit, $results)
    {
        // Voor/na berekeningen maken
        $bikefitVoor = clone $bikefit;
        $bikefitVoor->context = 'voor';
        $bikefitNa = clone $bikefit;
        $bikefitNa->context = 'na';
        
        // Herbereken results voor voor/na
        $bikefitCalculator = new \App\Services\BikefitCalculator();
        $resultsNa = $bikefitCalculator->calculate($bikefitNa);
        $resultsVoor = $bikefitCalculator->calculate($bikefitVoor, $resultsNa);
        
        // Genereer HTML voor elk component
        try {
            // Resultaten VOOR - zeer kleine schaling voor compacte weergave
            $resultatenVoorHtml = '<div style="transform:scale(0.35); transform-origin:top left; width:285%; margin-bottom:-280px;">' 
                . view('bikefit._results_section', [
                    'results' => $resultsVoor, 
                    'bikefit' => $bikefitVoor
                ])->render() 
                . '</div>';
            $content = str_replace('$ResultatenVoor$', $resultatenVoorHtml, $content);
            
            // Resultaten NA - zeer kleine schaling voor compacte weergave
            $resultatenNaHtml = '<div style="transform:scale(0.35); transform-origin:top left; width:285%; margin-bottom:-280px;">' 
                . view('bikefit._results_section', [
                    'results' => $resultsNa, 
                    'bikefit' => $bikefitNa
                ])->render() 
                . '</div>';
            $content = str_replace('$ResultatenNa$', $resultatenNaHtml, $content);
            
            // Prognose zitpositie - gebruik speciale rapport versie met leesbare tekst
            $prognoseZitpositieHtml = '<div style="transform:scale(0.7); transform-origin:top left; width:143%; margin-bottom:-100px;">' 
                . view('bikefit._prognose_zitpositie_report', [
                    'bikefit' => $bikefit,
                    'results' => $results
                ])->render() 
                . '</div>';
            $content = str_replace('$Bikefit.prognose_zitpositie_html$', $prognoseZitpositieHtml, $content);
            
            // Mobiliteit resultaten - juiste schaling zoals op results pagina
            $mobiliteitHtml = '<div style="transform:scale(0.65); transform-origin:top left; width:154%; margin-bottom:-120px;">' 
                . view('bikefit._mobility_results', [
                    'bikefit' => $bikefitNa
                ])->render() 
                . '</div>';
            $content = str_replace('$MobiliteitTabel$', $mobiliteitHtml, $content);
            
            // Mobiliteit klant tabel (met gekleurde balken) - verbeterde styling
            $mobiliteitklantData = [
                'slr_links' => $bikefit->straight_leg_raise_links ?? '',
                'slr_rechts' => $bikefit->straight_leg_raise_rechts ?? '',
                'knieflexie_links' => $bikefit->knieflexie_links ?? '',
                'knieflexie_rechts' => $bikefit->knieflexie_rechts ?? '',
                'heup_endorotatie_links' => $bikefit->heup_endorotatie_links ?? '',
                'heup_endorotatie_rechts' => $bikefit->heup_endorotatie_rechts ?? '',
                'heup_exorotatie_links' => $bikefit->heup_exorotatie_links ?? '',
                'heup_exorotatie_rechts' => $bikefit->heup_exorotatie_rechts ?? '',
                'enkeldorsiflexie_links' => $bikefit->enkeldorsiflexie_links ?? '',
                'enkeldorsiflexie_rechts' => $bikefit->enkeldorsiflexie_rechts ?? '',
                'one_leg_squat_links' => $bikefit->one_leg_squat_links ?? '',
                'one_leg_squat_rechts' => $bikefit->one_leg_squat_rechts ?? '',
            ];
            
            // Voeg extra CSS toe voor betere tabel styling
            $mobiliteitTableCSS = '<style>
                .mobility-report-table {
                    width: 100% !important;
                    margin: 0 auto !important;
                    border-collapse: separate !important;
                    border-spacing: 0 !important;
                    border: 1px solid #d1d5db !important;
                    border-radius: 8px !important;
                    overflow: hidden !important;
                    table-layout: fixed !important;
                }
                .mobility-report-table th,
                .mobility-report-table td {
                    border: 1px solid #e5e7eb !important;
                    padding: 12px 16px !important;
                    text-align: left !important;
                    overflow-wrap: break-word !important;
                }
                .mobility-report-table th {
                    background-color: #c8e1eb !important;
                    font-weight: 600 !important;
                    border-bottom: 2px solid #a5c9d6 !important;
                }
                .mobility-report-table tbody tr:nth-child(even) {
                    background-color: #f9fafb !important;
                }
                .mobility-report-table .test-name {
                    font-weight: 600 !important;
                    width: 33.33% !important;
                }
                .mobility-report-table .score-cell {
                    text-align: center !important;
                    width: 33.33% !important;
                }
            </style>';
            
            $mobiliteitklantHtml = $mobiliteitTableCSS . '<div style="margin: 20px 0; width: 100%; overflow-x: auto;">' 
                . view('bikefit._mobility_table_report', [
                    'mobiliteitklant' => $mobiliteitklantData
                ])->render() 
                . '</div>';
            $content = str_replace('$mobiliteitklant$', $mobiliteitklantHtml, $content);
            
            // Body measurements - compact weergegeven
            $bodyMeasurementsHtml = '<div style="transform:scale(0.8); transform-origin:top left; width:125%; margin-bottom:-50px;">' 
                . view('bikefit._body_measurements', [
                    'bikefit' => $bikefit
                ])->render() 
                . '</div>';
            $content = str_replace('$Bikefit.body_measurements_block_html$', $bodyMeasurementsHtml, $content);
            
        } catch (\Exception $e) {
            \Log::error('Error rendering bikefit HTML components: ' . $e->getMessage());
            // Fallback bij fouten
            $content = str_replace('$ResultatenVoor$', '<p><em>Resultaten VOOR kunnen niet worden geladen</em></p>', $content);
            $content = str_replace('$ResultatenNa$', '<p><em>Resultaten NA kunnen niet worden geladen</em></p>', $content);
            $content = str_replace('$Bikefit.prognose_zitpositie_html$', '<p><em>Prognose zitpositie kan niet worden geladen</em></p>', $content);
            $content = str_replace('$MobiliteitTabel$', '<p><em>Mobiliteit tabel kan niet worden geladen</em></p>', $content);
            $content = str_replace('$mobiliteitklant$', '<p><em>Mobiliteit klant tabel kan niet worden geladen</em></p>', $content);
            $content = str_replace('$Bikefit.body_measurements_block_html$', '<p><em>Lichaamsmaten kunnen niet worden geladen</em></p>', $content);
        }
        
        return $content;
    }
}