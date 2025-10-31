<?php

namespace App\Http\Controllers;

use App\Models\Sjabloon;
use App\Models\SjabloonPage;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Schema;

class SjablonenController extends Controller
{
    /**
     * Check admin toegang voor sjablonen beheer
     */
    private function checkAdminAccess()
    {
        if (!in_array(auth()->user()->role, ['admin', 'organisatie_admin', 'superadmin'])) {
            abort(403, 'Geen toegang. Alleen administrators hebben toegang tot sjablonen beheer.');
        }
    }

    public function index()
    {
        $this->checkAdminAccess();
        
        $user = auth()->user();
        
        // Superadmin ziet alle sjablonen van alle organisaties
        $query = Sjabloon::where('is_actief', true);
        
        // Andere gebruikers zien alleen sjablonen van eigen organisatie
        if ($user->role !== 'superadmin') {
            $query->where('organisatie_id', $user->organisatie_id);
        }
        
        $sjablonen = $query->orderBy('naam')->get();
        
        return view('sjablonen.index', compact('sjablonen'));
    }

    public function create()
    {
        $this->checkAdminAccess();
        
        return view('sjablonen.create');
    }

    public function store(Request $request)
    {
        $this->checkAdminAccess();
        
        $request->validate([
            'naam' => 'required|string|max:255',
            'categorie' => 'required|string|max:255',
            'testtype' => 'nullable|string|max:255',
            'beschrijving' => 'nullable|string',
        ]);

        // Bouw data array dynamisch op basis van aanwezige kolommen
        $data = [
            'naam' => $request->naam,
            'categorie' => $request->categorie,
            'testtype' => $request->testtype,
            'beschrijving' => $request->beschrijving,
            'is_actief' => true,
            'organisatie_id' => auth()->user()->organisatie_id,
        ];
        
        // Voeg user_id alleen toe als de kolom bestaat
        if (Schema::hasColumn('sjablonen', 'user_id')) {
            $data['user_id'] = auth()->id();
        }

        $sjabloon = Sjabloon::create($data);

        return redirect()->route('sjablonen.edit', $sjabloon)
                        ->with('success', 'Sjabloon aangemaakt!');
    }

    public function show($id)
    {
        $this->checkAdminAccess();
        
        $user = auth()->user();
        
        // Find sjabloon manually to ensure consistency
        $sjabloon = Sjabloon::findOrFail($id);
        
        // Check organisatie toegang (behalve voor superadmin)
        if ($user->role !== 'superadmin' && $sjabloon->organisatie_id !== $user->organisatie_id) {
            abort(403, 'Geen toegang tot sjablonen van andere organisatie');
        }
        
        $sjabloon->load('pages');
        
        return view('sjablonen.show', compact('sjabloon'));
    }

    public function edit($id)
    {
        $this->checkAdminAccess();
        
        $user = auth()->user();
        
        // Find sjabloon manually
        $sjabloon = Sjabloon::findOrFail($id);
        
        // Check organisatie toegang (behalve voor superadmin)
        if ($user->role !== 'superadmin' && $sjabloon->organisatie_id !== $user->organisatie_id) {
            abort(403, 'Geen toegang tot sjablonen van andere organisatie');
        }
        
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
        $this->checkAdminAccess();
        
        $user = auth()->user();
        $sjabloon = Sjabloon::findOrFail($id);
        
        // Check organisatie toegang (behalve voor superadmin)
        if ($user->role !== 'superadmin' && $sjabloon->organisatie_id !== $user->organisatie_id) {
            abort(403, 'Geen toegang tot sjablonen van andere organisatie');
        }
        
        return view('sjablonen.edit-basic', compact('sjabloon'));
    }

    /**
     * Update basic sjabloon information
     */
    public function updateBasic(Request $request, $id)
    {
        $this->checkAdminAccess();
        
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
        $this->checkAdminAccess();
        
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
        $this->checkAdminAccess();
        
        try {
            $user = auth()->user();
            
            // Find the sjabloon by ID
            $sjabloon = Sjabloon::findOrFail($id);
            
            // Check organisatie toegang (behalve voor superadmin)
            if ($user->role !== 'superadmin' && $sjabloon->organisatie_id !== $user->organisatie_id) {
                abort(403, 'Geen toegang tot sjablonen van andere organisatie');
            }
            
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
        $this->checkAdminAccess();
        
        try {
            $user = auth()->user();
            
            // Find the sjabloon by ID
            $sjabloon = Sjabloon::findOrFail($id);
            
            // Check organisatie toegang (behalve voor superadmin)
            if ($user->role !== 'superadmin' && $sjabloon->organisatie_id !== $user->organisatie_id) {
                abort(403, 'Geen toegang tot sjablonen van andere organisatie');
            }
            
            \Log::info('Duplicating sjabloon: ' . $sjabloon->id . ' (naam: ' . $sjabloon->naam . ')');
            
            // Maak een kopie van het sjabloon
            $newSjabloon = $sjabloon->replicate();
            $newSjabloon->naam = $sjabloon->naam . ' (Kopie)';
            
            // Voeg user_id alleen toe als de kolom bestaat
            if (Schema::hasColumn('sjablonen', 'user_id')) {
                $newSjabloon->user_id = auth()->id();
            }
            
            $newSjabloon->organisatie_id = auth()->user()->organisatie_id;
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

    /**
     * Upload achtergrondafbeelding voor sjablonen
     */
    public function uploadBackground(Request $request)
    {
        try {
            \Log::info('🔥 uploadBackground called', [
                'has_file' => $request->hasFile('background'),
                'all_files' => $request->allFiles(),
                'all_input' => $request->all()
            ]);

            $request->validate([
                'background' => 'required|image|mimes:jpeg,png,jpg,gif|max:10240', // Max 10MB
            ]);

            if ($request->hasFile('background')) {
                $file = $request->file('background');
                $fileName = 'background_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                
                \Log::info('📁 Uploading file', [
                    'filename' => $fileName,
                    'original' => $file->getClientOriginalName(),
                    'size' => $file->getSize()
                ]);
                
                // Sla op in public/backgrounds directory
                $destinationPath = public_path('backgrounds');
                
                // Zorg dat de directory bestaat
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }
                
                // Verplaats het bestand
                $file->move($destinationPath, $fileName);
                
                \Log::info('✅ File uploaded successfully', ['path' => $destinationPath . '/' . $fileName]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Achtergrond succesvol geüpload!',
                    'filename' => $fileName,
                    'path' => '/backgrounds/' . $fileName
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Geen bestand ontvangen'
            ], 400);

        } catch (\Exception $e) {
            \Log::error('❌ Background upload error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Fout bij uploaden: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verwijder achtergrondafbeelding
     */
    public function deleteBackground($filename)
    {
        try {
            $path = public_path('backgrounds/' . $filename);
            
            if (file_exists($path)) {
                unlink($path);
                
                \Log::info('✅ Background deleted', ['filename' => $filename]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Achtergrond verwijderd!'
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Bestand niet gevonden'
            ], 404);
            
        } catch (\Exception $e) {
            \Log::error('❌ Background delete error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Fout bij verwijderen: ' . $e->getMessage()
            ], 500);
        }
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
        
        // Debug: Log hoe veel pagina's we sturen naar de view
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
        $user = auth()->user();
        
        // Base query met organisatie filter
        $query = Sjabloon::where('is_actief', true);
        
        // Filter op organisatie (behalve voor superadmin)
        if ($user && $user->role !== 'superadmin') {
            $query->where('organisatie_id', $user->organisatie_id);
        }
        
        \Log::info('🔍 Finding template', [
            'testtype' => $testtype,
            'category' => $category,
            'user_org' => $user ? $user->organisatie_id : null,
            'is_superadmin' => $user ? ($user->role === 'superadmin') : false
        ]);
        
        // First try to match both testtype and category (exact match)
        if ($testtype && $category) {
            $template = (clone $query)->where('testtype', $testtype)
                             ->where('categorie', $category)
                             ->first();
            if ($template) {
                \Log::info('✅ Template found (exact match)', ['template_id' => $template->id]);
                return $template;
            }
        }
        
        // If no exact match, try just testtype (exact match)
        if ($testtype) {
            $template = (clone $query)->where('testtype', $testtype)->first();
            if ($template) {
                \Log::info('✅ Template found (testtype match)', ['template_id' => $template->id]);
                return $template;
            }
        }
        
        // If still no match, try LIKE search on testtype (fuzzy match)
        if ($testtype) {
            $template = (clone $query)->where('testtype', 'LIKE', '%' . $testtype . '%')->first();
            if ($template) {
                \Log::info('✅ Template found (testtype fuzzy match)', ['template_id' => $template->id]);
                return $template;
            }
        }
        
        // If still no match, try just category + NULL testtype (wildcard voor alle testtypes)
        if ($category) {
            $template = (clone $query)->where('categorie', $category)
                             ->whereNull('testtype')
                             ->first();
            if ($template) {
                \Log::info('✅ Template found (category wildcard)', ['template_id' => $template->id]);
                return $template;
            }
        }
        
        // Last resort: just category (any testtype)
        if ($category) {
            $template = (clone $query)->where('categorie', $category)->first();
            if ($template) {
                \Log::info('✅ Template found (category match)', ['template_id' => $template->id]);
                return $template;
            }
        }
        
        \Log::warning('❌ No template found', [
            'testtype' => $testtype,
            'category' => $category,
            'user_org' => $user ? $user->organisatie_id : null
        ]);
        
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
     * Generate report for inspanningstest using matching sjabloon
     */
    public function generateInspanningstestReport($inspanningstestId)
    {
        try {
            \Log::info('🏃 Generating inspanningstest report', ['test_id' => $inspanningstestId]);
            
            // Haal inspanningstest op
            $inspanningstest = \App\Models\Inspanningstest::with('klant')->findOrFail($inspanningstestId);
            
            // Zoek matching sjabloon
            $template = \App\Helpers\SjabloonHelper::findMatchingTemplate($inspanningstest->testtype, 'inspanningstest');
            
            if (!$template) {
                \Log::warning('❌ No matching template found', ['testtype' => $inspanningstest->testtype]);
                return redirect()->route('inspanningstest.results', [
                    'klant' => $inspanningstest->klant_id,
                    'test' => $inspanningstest->id
                ])->with('error', 'Geen passend sjabloon gevonden voor testtype: ' . $inspanningstest->testtype);
            }
            
            // Genereer pagina's met vervangen placeholders
            $generatedPages = $this->generatePagesForInspanningstest($template, $inspanningstest);
            
            \Log::info('✅ Generated inspanningstest pages', ['page_count' => count($generatedPages)]);
            
            // Return de generated-report view
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
            
            return redirect()->back()->with('error', 'Fout bij genereren rapport: ' . $e->getMessage());
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
                
                // ONTBREKENDE ZADEL EN SCHOENPLAATJES VELDEN - CORRECTE DATABASE VELDNAMEN
                $content = str_replace('{{bikefit.type_zadel}}', $bikefit->type_zadel ?? '', $content);
                $content = str_replace('{{bikefit.zadelbreedte}}', $bikefit->zadelbreedte ?? '', $content);
                
                // Zadel inclinatie - DATABASE VELD = zadeltil
                $zadelInclinatie = $bikefit->zadeltil ?? '';
                $content = str_replace('{{bikefit.inclinatie_zadel}}', $zadelInclinatie, $content);
                $content = str_replace('{{bikefit.zadelinclinatie}}', $zadelInclinatie, $content);
                $content = str_replace('{{bikefit.zadeltil}}', $zadelInclinatie, $content);
                
                // Schoenplaatjes rotatie - DATABASE VELD = rotatie_aanpassingen
                $schoenplaatjesRotatie = $bikefit->rotatie_aanpassingen ?? '';
                $content = str_replace('{{bikefit.rotatie_schoenplaatjes}}', $schoenplaatjesRotatie, $content);
                $content = str_replace('{{bikefit.schoenplaatjes_rotatie}}', $schoenplaatjesRotatie, $content);
                $content = str_replace('{{bikefit.rotatie_aanpassingen}}', $schoenplaatjesRotatie, $content);
                // FIX: verkeerde prefix in sjabloon
                $content = str_replace('{{inclinatie.rotatie_schoenplaatjes}}', $schoenplaatjesRotatie, $content);
                
                // Schoenplaatjes inclinatie - DATABASE VELD = inclinatie_aanpassingen
                $schoenplaatjesInclinatie = $bikefit->inclinatie_aanpassingen ?? '';
                $content = str_replace('{{bikefit.inclinatie_schoenplaatjes}}', $schoenplaatjesInclinatie, $content);
                $content = str_replace('{{bikefit.schoenplaatjes_inclinatie}}', $schoenplaatjesInclinatie, $content);
                $content = str_replace('{{bikefit.inclinatie_aanpassingen}}', $schoenplaatjesInclinatie, $content);
                
                // Ophoging - DATABASE VELDEN = ophoging_li en ophoging_re
                $content = str_replace('{{bikefit.ophoging_li}}', $bikefit->ophoging_li ?? '', $content);
                $content = str_replace('{{bikefit.ophoging_re}}', $bikefit->ophoging_re ?? '', $content);
                $content = str_replace('{{bikefit.ophoging_links}}', $bikefit->ophoging_li ?? '', $content);
                $content = str_replace('{{bikefit.ophoging_rechts}}', $bikefit->ophoging_re ?? '', $content);
                
                // Stuurpen lengte VOOR - DATABASE VELD = aanpassingen_stuurpen_pre
                $stuurpenVoor = $bikefit->aanpassingen_stuurpen_pre ?? '';
                $content = str_replace('{{bikefit.stuurpenlengte_voor}}', $stuurpenVoor, $content);
                $content = str_replace('{{bikefit.stuurpen_lengte_voor}}', $stuurpenVoor, $content);
                $content = str_replace('{{bikefit.aanpassingen_stuurpen_pre}}', $stuurpenVoor, $content);
                // FIX: korte variant zonder "lengte"
                $content = str_replace('{{bikefit.stuurpen_voor}}', $stuurpenVoor, $content);
                
                // Stuurpen lengte NA - DATABASE VELD = aanpassingen_stuurpen_post
                $stuurpenNa = $bikefit->aanpassingen_stuurpen_post ?? '';
                $content = str_replace('{{bikefit.stuurpenlengte_na}}', $stuurpenNa, $content);
                $content = str_replace('{{bikefit.stuurpen_lengte_na}}', $stuurpenNa, $content);
                $content = str_replace('{{bikefit.aanpassingen_stuurpen_post}}', $stuurpenNa, $content);
                // FIX: korte variant zonder "lengte"
                $content = str_replace('{{bikefit.stuurpen_na}}', $stuurpenNa, $content);
                
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
        \Log::info('🔥 Generating pages for inspanningstest', ['test_id' => $inspanningstest->id]);
        
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
                $content = $page->content ?? '<p>Geen content</p>';
                
                // ✅ GEBRUIK SJABLOONSERVICE VOOR INSPANNINGSTEST
                $sjabloonService = new \App\Services\SjabloonService();
                $content = $sjabloonService->vervangSleutels($content, null, $inspanningstest->klant, $inspanningstest);
                
                // Verberg CKEditor table borders
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
        
        \Log::info('✅ generatePagesForInspanningstest DONE', ['pages' => count($generatedPages)]);
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
     * Replace Bikefit HTML components with actual rendered HTML - BACK TO WORKING VERSION
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
        
        // TERUG NAAR LARAVEL VIEW RENDERING
        try {
            // Resultaten VOOR
            $resultatenVoorHtml = view('bikefit._results_section', [
                'results' => $resultsVoor, 
                'bikefit' => $bikefitVoor
            ])->render();
            $content = str_replace('$ResultatenVoor$', $resultatenVoorHtml, $content);
            
            // Resultaten NA
            $resultatenNaHtml = view('bikefit._results_section', [
                'results' => $resultsNa, 
                'bikefit' => $bikefitNa
            ])->render();
            $content = str_replace('$ResultatenNa$', $resultatenNaHtml, $content);
            
            // Prognose zitpositie
            $prognoseZitpositieHtml = view('bikefit._prognose_zitpositie_report', [
                'bikefit' => $bikefit,
                'results' => $results
            ])->render();
            $content = str_replace('$Bikefit.prognose_zitpositie_html$', $prognoseZitpositieHtml, $content);
            
            // Body measurements - 5% KLEINER + TAHOMA FONT
            $bodyMeasurementsCSS = '<style>
                /* 5% KLEINER LICHAAMSMATEN DIAGRAM */
                *[class*="body"],
                *[class*="measurement"],
                *[class*="lichaam"],
                *[class*="diagram"],
                *[class*="bikefit"] div:has(svg),
                div:has(svg[viewBox]),
                svg[viewBox],
                svg,
                .body-measurements,
                .lichaamsmaten,
                .body-measurements-block,
                .measurements-container,
                .measurements,
                .measurement-diagram,
                .bikefit-measurements {
                    transform: scale(0.95) !important;
                    transform-origin: left !important;
                    margin: 10px auto !important;
                    font-family: Tahoma, Arial, sans-serif !important;
                    width: auto !important;
                    height: auto !important;
                }
                
                /* 5% KLEINERE SVG ELEMENTEN */
                svg {
                    transform: scale(0.95) !important;
                    transform-origin: left !important;
                    width: auto !important;
                    height: auto !important;
                    max-width: none !important;
                    max-height: none !important;
                }
                
                /* CONTAINER RUIMTE VOOR KLEINER DIAGRAM */
                div:has(svg) {
                    padding: 10px !important;
                    margin: 5px auto !important;
                    overflow: visible !important;
                }
                
                /* FORCEER ALLE TEKST OP TAHOMA */
                * {
                    font-family: Tahoma, Arial, sans-serif !important;
                }
                
                /* ALLE SVG TEKST GROTER EN DIKKER */
                svg text,
                svg tspan,
                text,
                tspan {
                    font-family: Tahoma, Arial, sans-serif !important;
                    font-weight: 600 !important;
                    font-size: 11px !important;
                }
                
                /* ALLE MOGELIJKE CIJFER ELEMENTEN */
                .value,
                .number,
                .measurement-value,
                span:contains("cm"),
                span:contains("."),
                *[class*="value"] {
                    font-family: Tahoma, Arial, sans-serif !important;
                    font-weight: 600 !important;
                    font-size: 12px !important;
                }
            </style>';
            
            $bodyMeasurementsHtml = $bodyMeasurementsCSS . view('bikefit._body_measurements', [
                'bikefit' => $bikefit
            ])->render();
            $content = str_replace('$Bikefit.body_measurements_block_html$', $bodyMeasurementsHtml, $content);
            
            // Mobiliteit resultaten - HERSTELD + VERBETERDE CSS
            $mobiliteitCSS = '<style>
                .mobility-table {
                    width: 100% !important;
                    max-width: 1000px !important;
                    margin: 20px auto !important;
                }
                .mobility-table table {
                    width: 100% !important;
                    border-collapse: separate !important;
                    border-spacing: 0 !important;
                    border: 1px solid #d1d5db !important;
                    border-radius: 8px !important;
                    overflow: hidden !important;
                    font-size: 14px !important;
                }
                .mobility-table th,
                .mobility-table td {
                    border: 1px solid #e5e7eb !important;
                    padding: 12px 16px !important;
                    text-align: center !important;
                    vertical-align: middle !important;
                }
                .mobility-table th {
                    background-color: #c8e1eb !important;
                    font-weight: 600 !important;
                    text-align: center !important;
                }
                .mobility-table td:first-child {
                    text-align: left !important;
                    font-weight: 600 !important;
                    width: 40% !important;
                }
            </style>';
            
            $mobiliteitHtml = $mobiliteitCSS . view('bikefit._mobility_results', [
                'bikefit' => $bikefitNa
            ])->render();
            $content = str_replace('$MobiliteitTabel$', $mobiliteitHtml, $content);
            
            // Mobiliteit klant tabel - HERSTELD + VERBETERDE STYLING
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
            
            // AANGEPASTE CSS VOOR MOBILITEIT TABEL - KLEINERE SCORES + ALLEEN HEADER BLAUW + ZWARTE TEKST
            $mobiliteitTableCSS = '<style>
                /* Container fix voor volledige zichtbaarheid */
                .page-content, .report-content, .generated-content {
                    width: 100% !important;
                    max-width: none !important;
                    overflow-x: auto !important;
                }
                
                .mobility-report-table,
                table.mobility-report-table,
                div .mobility-report-table {
                    width: 100% !important;
                    max-width: 100% !important;
                    margin: 10px 0 !important;
                    border-collapse: separate !important;
                    border-spacing: 0 !important;
                    border: 1px solid #d1d5db !important;
                    border-radius: 6px !important;
                    overflow: hidden !important;
                    font-size: 11px !important;
                    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1) !important;
                    table-layout: fixed !important;
                }
                .mobility-report-table th,
                .mobility-report-table td,
                table.mobility-report-table th,
                table.mobility-report-table td {
                    border: 1px solid #e5e7eb !important;
                    padding: 8px 6px !important;
                    text-align: center !important;
                    vertical-align: top !important;
                    font-size: 10px !important;
                    line-height: 1.2 !important;
                    word-wrap: break-word !important;
                    min-height: 40px !important;
                }
                
                /* ALLEEN ECHTE HEADER BLAUW - NIET EERSTE RIJ DATA */
                .mobility-report-table thead th,
                table.mobility-report-table thead th,
                .mobility-report-table > thead > tr > th,
                table.mobility-report-table > thead > tr > th {
                    background-color: #c8e1eb !important;
                    color: #1f2937 !important;
                    font-weight: 600 !important;
                    border: 1px solid #a5c9d6 !important;
                    border-bottom: 2px solid #a5c9d6 !important;
                    text-align: center !important;
                    font-size: 11px !important;
                    padding: 8px !important;
                    vertical-align: middle !important;
                }
                
                /* KLEINERE LINKSE KOLOM - VAN 25% NAAR 18% + ZWARTE TEKST */
                .mobility-report-table .test-name,
                table.mobility-report-table .test-name,
                .mobility-report-table td:first-child,
                table.mobility-report-table td:first-child {
                    text-align: left !important;
                    font-weight: 600 !important;
                    width: 20% !important;
                    background-color: #f8fafc !important;
                    font-size: 10px !important;
                    vertical-align: top !important;
                    padding: 12px 8px !important;
                    color: #000000 !important;
                }
                
                /* VEEL BREDERE SCORE KOLOMMEN - VAN 38.75% NAAR 42.5% */
                .mobility-report-table .score-cell,
                table.mobility-report-table .score-cell,
                .mobility-report-table td:not(:first-child),
                table.mobility-report-table td:not(:first-child) {
                    text-align: center !important;
                    width: 42.5% !important;
                    font-weight: 500 !important;
                    font-size: 10px !important;
                    vertical-align: top !important;
                    padding: 12px 4px !important;
                }
                
                /* HEEL KLEINE BALKJES - GEEN SCORE TEKST */
                .mobility-report-table .score-bars,
                .mobility-report-table .score-text,
                table.mobility-report-table .score-bars,
                table.mobility-report-table .score-text,
                .mobility-report-table .score-container,
                table.mobility-report-table .score-container,
                .mobility-report-table .score-wrapper,
                table.mobility-report-table .score-wrapper {
                    font-size: 3px !important;
                    line-height: 0.9 !important;
                }
                
                .mobility-report-table .score-bar,
                table.mobility-report-table .score-bar,
                .mobility-report-table .score-block,
                table.mobility-report-table .score-block {
                    height: 3px !important;
                    margin: 0px !important;
                    border-radius: 1px !important;
                    display: inline-block !important;
                    width: 6px !important;
                    font-size: 3px !important;
                }
                
                /* VERBERG ALLE SCORE LABELS EN TEKST */
                .mobility-report-table .score-label,
                table.mobility-report-table .score-label,
                .mobility-report-table .score-value,
                table.mobility-report-table .score-value {
                    display: none !important;
                    visibility: hidden !important;
                    font-size: 0px !important;
                    height: 0px !important;
                    margin: 0px !important;
                    padding: 0px !important;
                }
                
                /* SCORE CONTAINERS MINIMAAL */
                .mobility-report-table .score-container,
                table.mobility-report-table .score-container {
                    margin: 0px !important;
                    padding: 0px !important;
                }
                
                /* UITLEG TEKST BETER LEESBAAR */
                .mobility-report-table p,
                .mobility-report-table .explanation {
                    font-size: 9px !important;
                    line-height: 1.3 !important;
                    margin: 4px 0 !important;
                    text-align: justify !important;
                }
                
                /* ULTRA STERKE OVERRIDES VOOR ALLE SCORE ELEMENTEN */
                * .mobility-report-table td {
                    text-align: center !important;
                    font-size: 10px !important;
                    white-space: normal !important;
                    min-height: 40px !important;
                }
                * .mobility-report-table td:first-child {
                    text-align: left !important;
                    font-weight: 600 !important;
                    white-space: normal !important;
                    font-size: 10px !important;
                    width: 15% !important;
                    color: #000000 !important;
                }
                * .mobility-report-table th {
                    font-size: 11px !important;
                    min-height: 30px !important;
                }
                
                /* MEGA STERKE SCORE OVERRIDES - VERBERG ALLE TEKST + KLEINERE BALKEN */
                * .mobility-report-table .score-bar,
                * .mobility-report-table .score-block {
                    height: 3px !important;
                    width: 6px !important;
                    font-size: 2px !important;
                    margin: 0px !important;
                }
                * .mobility-report-table .score-label,
                * .mobility-report.table .score-value,
                * .mobility-report_table .score-text,
                * .mobility-report-table span,
                * .mobility-report-table small {
                    display: none !important;
                    visibility: hidden !important;
                    font-size: 0px !important;
                    line-height: 0 !important;
                    height: 0px !important;
                    width: 0px !important;
                    margin: 0px !important;
                    padding: 0px !important;
                    opacity: 0 !important;
                }
                
                /* EXTRA STERKE OVERRIDES VOOR ALLE TEKST IN SCORE CELLEN */
                .mobility-report-table td:not(:first-child) span,
                table.mobility-report-table td:not(:first-child) span,
                .mobility-report-table td:not(:first-child) small,
                table.mobility-report-table td:not(:first-child) small,
                .mobility-report-table .score-cell span,
                table.mobility-report-table .score-cell span {
                    display: none !important;
                    visibility: hidden !important;
                    font-size: 0px !important;
                    height: 0px !important;
                                       opacity: 0 !important;
                }
            </style>';
            
            $mobiliteitklantHtml = $mobiliteitTableCSS . view('bikefit._mobility_table_report', [
                'mobiliteitklant' => $mobiliteitklantData
            ])->render();
            $content = str_replace('$mobiliteitklant$', $mobiliteitklantHtml, $content);
            
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