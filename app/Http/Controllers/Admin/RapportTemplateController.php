<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RapportTemplate;
use App\Models\RapportTemplatePage;
use App\Models\RapportPageType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Services\RapportPreviewService;

/**
 * Admin Controller voor Rapport Template Management
 * 
 * Beheer van visuele templates voor rapporten:
 * - Inspanningstests
 * - Bikefits
 * - Toekomstige rapport types
 */
class RapportTemplateController extends Controller
{
    /**
     * Overzicht van alle templates
     */
    public function index(Request $request)
    {
        $rapportType = $request->get('type', 'all');
        $organisatieId = auth()->user()->organisatie_id;

        $query = RapportTemplate::with(['pages', 'organisatie'])
            ->forOrganisatie($organisatieId);

        if ($rapportType !== 'all') {
            $query->forRapportType($rapportType);
        }

        $templates = $query->latest()->paginate(12);

        // Haal beschikbare rapport types op
        $rapportTypes = RapportPageType::select('rapport_type')
            ->distinct()
            ->pluck('rapport_type');

        return view('admin.rapport-templates.index', compact('templates', 'rapportTypes', 'rapportType'));
    }

    /**
     * Toon create form
     */
    public function create()
    {
        // Haal beschikbare page types op in de JUISTE volgorde
        $pageTypes = [
            // Inspanningstest pages (in gewenste volgorde)
            'inspanningstest_cover' => 'Voorblad',
            'inspanningstest_algemeen' => 'Algemene Informatie',
            'inspanningstest_trainingstatus' => 'Trainingstatus',
            'inspanningstest_testresultaten' => 'Testresultaten',
            'inspanningstest_grafiek' => 'Grafiek Analyse',
            'inspanningstest_drempelwaarden' => 'Drempelwaarden Overzicht',
            'inspanningstest_zones' => 'Trainingszones',
            'inspanningstest_ai_analyse' => 'AI Analyse',
        ];
        
        // Converteer naar objecten voor de view
        $availablePageTypes = collect($pageTypes)->map(function($label, $type) {
            return (object)[
                'type' => $type,
                'type_key' => $type,
                'naam' => $label,
                'label' => $label,
                'beschrijving' => $label,
                'is_required' => false,
                'description' => $label,
            ];
        })->values();
        
        // Rapport types voor dropdown
        $rapportTypes = [
            'inspanningstest' => 'Inspanningstest',
            'bikefit' => 'Bikefit',
        ];
        
        // Defaults
        $rapportType = 'inspanningstest';
        $testType = '';
        
        return view('admin.rapport-templates.create', compact('pageTypes', 'rapportTypes', 'rapportType', 'testType', 'availablePageTypes'));
    }

    /**
     * Store nieuw rapport template
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'naam' => 'required|string|max:255',
            'rapport_type' => 'required|in:inspanningstest,bikefit',
            'beschrijving' => 'nullable|string',
            'testtype' => 'nullable|string',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'primary_color' => 'nullable|string',
            'secondary_color' => 'nullable|string',
            'accent_color' => 'nullable|string',
            'selected_page_types' => 'required|array|min:1',
            'selected_page_types.*' => 'required|string',
        ]);

        try {
            // Bereken totaal aantal pagina's
            $totalPages = count($validated['selected_page_types']);

            // Maak template aan
            $template = RapportTemplate::create([
                'naam' => $validated['naam'],
                'rapport_type' => $validated['rapport_type'],
                'beschrijving' => $validated['beschrijving'] ?? null,
                'testtype' => $validated['testtype'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
                'is_default' => $validated['is_default'] ?? false,
                'total_pages' => $totalPages,
                'organisatie_id' => auth()->user()->organisatie_id,
                'created_by' => auth()->id(),
                'style_settings' => [
                    'colors' => [
                        'primary' => $validated['primary_color'] ?? '#1e40af',
                        'secondary' => $validated['secondary_color'] ?? '#c8e1eb',
                        'accent' => $validated['accent_color'] ?? '#ff6b35',
                    ],
                ],
            ]);

            // Maak pagina's aan
            $pageNumber = 1;
            foreach ($validated['selected_page_types'] as $pageTypeKey) {
                // Bepaal page_type met juiste prefix
                $pageType = $validated['rapport_type'] . '_' . $pageTypeKey;
                
                // Bepaal page_title op basis van type
                $pageTitles = [
                    'inspanningstest_cover' => 'Voorblad',
                    'inspanningstest_algemeen' => 'Algemene Informatie',
                    'inspanningstest_trainingstatus' => 'Trainingstatus',
                    'inspanningstest_testresultaten' => 'Testresultaten',
                    'inspanningstest_grafiek' => 'Grafiek Analyse',
                    'inspanningstest_drempelwaarden' => 'Drempelwaarden Overzicht',
                    'inspanningstest_zones' => 'Trainingszones',
                    'inspanningstest_ai_analyse' => 'AI Analyse',
                    'bikefit_cover' => 'Voorblad',
                    'bikefit_algemeen' => 'Algemene Informatie',
                    'bikefit_metingen' => 'Metingen',
                    'bikefit_resultaten' => 'Resultaten',
                ];

                $pageTitle = $pageTitles[$pageType] ?? ucfirst(str_replace('_', ' ', $pageTypeKey));

                RapportTemplatePage::create([
                    'template_id' => $template->id,
                    'page_number' => $pageNumber,
                    'page_type' => $pageType,
                    'page_title' => $pageTitle,
                    'layout_type' => 'standard',
                    'show_logo' => true,
                    'media_position' => 'top',
                    'media_size' => 50,
                ]);

                $pageNumber++;
            }

            Log::info('Rapport template aangemaakt', [
                'template_id' => $template->id,
                'naam' => $template->naam,
                'pages' => $totalPages,
            ]);

            return redirect()->route('admin.rapport-templates.edit', $template)
                ->with('success', "Template '{$template->naam}' succesvol aangemaakt met {$totalPages} pagina's!");

        } catch (\Exception $e) {
            Log::error('Template aanmaken mislukt', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Template aanmaken mislukt: ' . $e->getMessage());
        }
    }

    /**
     * Edit rapport template
     */
    public function edit(RapportTemplate $rapportTemplate)
    {
        // Eager load pages met page_type
        $rapportTemplate->load(['pages' => function($query) {
            $query->orderBy('page_number');
        }]);
        
        // Prepareer pages data voor JavaScript - INCLUSIEF page_id voor foto's
        $pagesData = $rapportTemplate->pages->map(function($page) {
            $pageTypeString = $page->page_type ?? '';
            
            // Verwijder rapport type prefix en vervang underscores met dashes
            $slug = str_replace('inspanningstest_', '', $pageTypeString);
            $slug = str_replace('bikefit_', '', $slug);
            $slug = str_replace('_', '-', $slug);
            
            return [
                'number' => $page->page_number,
                'type' => $slug ?: 'cover',
                'page_id' => $page->id, // NIEUW: page_id meesturen voor foto's
            ];
        });
        
        return view('admin.rapport-templates.edit', compact('rapportTemplate', 'pagesData'));
    }

    /**
     * Update template
     */
    public function update(Request $request, RapportTemplate $rapportTemplate)
    {
        // Check toegang
        if ($rapportTemplate->organisatie_id !== auth()->user()->organisatie_id) {
            abort(403);
        }

        $validated = $request->validate([
            'naam' => 'required|string|max:255',
            'testtype' => 'nullable|string',
            'beschrijving' => 'nullable|string',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'primary_color' => 'nullable|string',
            'secondary_color' => 'nullable|string',
            'accent_color' => 'nullable|string',
        ]);

        $rapportTemplate->update([
            'naam' => $validated['naam'],
            'testtype' => $validated['testtype'] ?? null,
            'beschrijving' => $validated['beschrijving'] ?? null,
            'is_active' => $validated['is_active'] ?? $rapportTemplate->is_active,
            'is_default' => $validated['is_default'] ?? $rapportTemplate->is_default,
            'style_settings' => [
                'colors' => [
                    'primary' => $validated['primary_color'] ?? $rapportTemplate->getPrimaryColor(),
                    'secondary' => $validated['secondary_color'] ?? $rapportTemplate->getSecondaryColor(),
                    'accent' => $validated['accent_color'] ?? '#ff6b35',
                ],
            ],
        ]);

        return redirect()
            ->route('admin.rapport-templates.edit', $rapportTemplate)
            ->with('success', 'Template succesvol bijgewerkt!');
    }

    /**
     * Verwijder template
     */
    public function destroy(RapportTemplate $rapportTemplate)
    {
        // Check toegang
        if ($rapportTemplate->organisatie_id !== auth()->user()->organisatie_id) {
            abort(403);
        }

        // Systeem templates kunnen niet verwijderd worden
        if ($rapportTemplate->is_system_template) {
            return redirect()
                ->route('admin.rapport-templates.index')
                ->with('error', 'Systeem templates kunnen niet verwijderd worden.');
        }

        $naam = $rapportTemplate->naam;
        
        // Verwijder alle pagina foto's
        foreach ($rapportTemplate->pages as $page) {
            if ($page->media_path) {
                Storage::delete($page->media_path);
            }
        }

        $rapportTemplate->delete();

        Log::info('Rapport template verwijderd', ['naam' => $naam]);

        return redirect()
            ->route('admin.rapport-templates.index')
            ->with('success', "Template '{$naam}' succesvol verwijderd.");
    }

    /**
     * Bewerk specifieke pagina
     */
    public function editPage(RapportTemplate $rapportTemplate, RapportTemplatePage $page)
    {
        // Check toegang
        if ($rapportTemplate->organisatie_id !== auth()->user()->organisatie_id) {
            abort(403);
        }

        // Check of pagina bij template hoort
        if ($page->template_id !== $rapportTemplate->id) {
            abort(404);
        }

        return view('admin.rapport-templates.page-editor', compact('rapportTemplate', 'page'));
    }

    /**
     * Upload foto voor pagina
     */
    public function uploadMedia(Request $request, RapportTemplate $rapportTemplate, RapportTemplatePage $page)
    {
        // Check toegang
        if ($rapportTemplate->organisatie_id !== auth()->user()->organisatie_id && !$rapportTemplate->is_system_template) {
            abort(403, 'Geen toegang tot deze template');
        }

        // Check of pagina bij template hoort
        if ($page->template_id !== $rapportTemplate->id) {
            abort(404, 'Pagina hoort niet bij deze template');
        }

        $validated = $request->validate([
            'media' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:10240', // Max 10MB
        ]);

        try {
            // Verwijder oude foto indien aanwezig
            if ($page->media_path && Storage::disk('public')->exists($page->media_path)) {
                Storage::disk('public')->delete($page->media_path);
                Log::info('Oude foto verwijderd', ['path' => $page->media_path]);
            }

            // Upload nieuwe foto naar public disk
            $file = $request->file('media');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('rapport-templates/pages', $filename, 'public');

            Log::info('Foto uploaded', [
                'original_name' => $file->getClientOriginalName(),
                'stored_path' => $path,
                'size' => $file->getSize(),
            ]);

            // Haal VASTE media instellingen op basis van pagina type
            $defaultSettings = RapportTemplatePage::getDefaultMediaSettings($page->page_type);

            // Update pagina met foto en VASTE instellingen
            $page->update([
                'media_path' => $path,
                'media_type' => 'image',
                'media_position' => $defaultSettings['position'],
                'media_size' => $defaultSettings['size'],
                'media_settings' => [
                    'opacity' => $defaultSettings['opacity'],
                ],
            ]);

            Log::info('Pagina updated met foto en vaste instellingen', [
                'template_id' => $rapportTemplate->id,
                'page_id' => $page->id,
                'page_type' => $page->page_type,
                'media_path' => $path,
                'settings' => $defaultSettings,
            ]);

            return redirect()
                ->back()
                ->with('success', 'Foto succesvol geüpload!');

        } catch (\Exception $e) {
            Log::error('Foto upload failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'template_id' => $rapportTemplate->id,
                'page_id' => $page->id,
            ]);

            return redirect()
                ->back()
                ->with('error', 'Foto upload mislukt: ' . $e->getMessage());
        }
    }

    /**
     * Update pagina instellingen
     */
    public function updatePage(Request $request, RapportTemplate $rapportTemplate, RapportTemplatePage $page)
    {
        // Check toegang
        if ($rapportTemplate->organisatie_id !== auth()->user()->organisatie_id) {
            abort(403);
        }

        $validated = $request->validate([
            'page_title' => 'nullable|string|max:255',
            'show_logo' => 'boolean',
            'custom_header' => 'nullable|string',
            'custom_footer' => 'nullable|string',
            'layout_type' => 'nullable|string|in:standard,two-column,full-width,sidebar',
        ]);

        // NIET toestaan: media_position en media_size (zijn vast per pagina type)
        $page->update($validated);

        return redirect()
            ->back()
            ->with('success', 'Pagina instellingen opgeslagen!');
    }

    /**
     * Dupliceer template
     */
    public function duplicate(RapportTemplate $rapportTemplate)
    {
        try {
            $newTemplate = $rapportTemplate->duplicate(
                $rapportTemplate->naam . ' (Kopie)',
                auth()->user()->organisatie_id
            );

            Log::info('Template gedupliceerd', [
                'original_id' => $rapportTemplate->id,
                'new_id' => $newTemplate->id,
            ]);

            return redirect()
                ->route('admin.rapport-templates.edit', $newTemplate)
                ->with('success', 'Template succesvol gedupliceerd!');

        } catch (\Exception $e) {
            Log::error('Template duplicatie mislukt', [
                'error' => $e->getMessage(),
                'template_id' => $rapportTemplate->id,
            ]);

            return redirect()
                ->back()
                ->with('error', 'Template duplicatie mislukt: ' . $e->getMessage());
        }
    }

    /**
     * Toon preview van specifieke pagina met dummy data
     */
    public function preview(Request $request, RapportTemplate $rapportTemplate, string $pageType)
    {
        try {
            // Check toegang
            if ($rapportTemplate->organisatie_id !== auth()->user()->organisatie_id && !$rapportTemplate->is_system_template) {
                abort(403, 'Geen toegang tot deze template');
            }

            // Haal page_id op uit query parameter
            $pageId = $request->query('page_id');
            
            // ALTIJD de specifieke pagina laden via page_id (bevat foto!)
            $page = null;
            if ($pageId) {
                $page = RapportTemplatePage::find($pageId);
                
                // Controleer of pagina bij deze template hoort
                if ($page && $page->template_id !== $rapportTemplate->id) {
                    Log::warning('Pagina hoort niet bij deze template', [
                        'page_id' => $pageId,
                        'template_id' => $rapportTemplate->id,
                        'page_template_id' => $page->template_id
                    ]);
                    abort(403, 'Pagina hoort niet bij deze template');
                }
                
                if (!$page) {
                    Log::error('Pagina niet gevonden', [
                        'page_id' => $pageId,
                        'template_id' => $rapportTemplate->id
                    ]);
                    abort(404, 'Pagina niet gevonden');
                }
                
                Log::info('Preview laden voor pagina', [
                    'page_id' => $page->id,
                    'page_type' => $page->page_type,
                    'page_number' => $page->page_number,
                    'has_media' => $page->hasMedia(),
                    'media_path' => $page->media_path,
                ]);
            } else {
                // GEEN page_id: dit zou NOOIT moeten gebeuren
                Log::error('Preview aangeroepen zonder page_id', [
                    'template_id' => $rapportTemplate->id,
                    'page_type' => $pageType
                ]);
                abort(400, 'page_id parameter is verplicht');
            }

            // Genereer dummy data op basis van rapport type
            if ($rapportTemplate->rapport_type === 'inspanningstest') {
                $dummyData = $this->generateDummyInspanningstestData();
                $klant = $dummyData['klant'];
                $inspanningstest = $dummyData['inspanningstest'];
                $grafiekData = $dummyData['grafiekData'] ?? null;
                $grafiekAnalyse = $dummyData['grafiekAnalyse'] ?? null;
            } else {
                // Bikefit dummy data (toekomstig)
                return response()->json([
                    'error' => 'Bikefit preview nog niet geïmplementeerd'
                ], 501);
            }

            // Map pageType naar de juiste partial in rapport-partials folder
            $partialPath = "inspanningstest.rapport-partials.{$pageType}";
            
            if (!view()->exists($partialPath)) {
                Log::error('Rapport partial niet gevonden', [
                    'template_id' => $rapportTemplate->id,
                    'page_type' => $pageType,
                    'tried_path' => $partialPath
                ]);
                
                return response()->json([
                    'error' => "Partial '{$pageType}' niet gevonden",
                    'available_types' => ['cover', 'algemeen', 'trainingstatus', 'testresultaten', 'drempelwaarden', 'zones', 'grafiek', 'ai-analyse']
                ], 404);
            }

            // Render alleen de partial HTML zonder layout
            $html = view($partialPath, [
                'klant' => $klant,
                'inspanningstest' => $inspanningstest,
                'grafiekData' => $grafiekData ?? null,
                'grafiekAnalyse' => $grafiekAnalyse ?? null,
                'page' => $page, // BELANGRIJK: Specifieke pagina met foto en instellingen
                'template' => $rapportTemplate,
            ])->render();
            
            // Check of de partial al een foto toont (om dubbele foto's te voorkomen)
            $partialHasImage = $this->partialAlreadyShowsImage($html);
            
            // Voeg ALLEEN een foto wrapper toe als:
            // 1. De pagina media heeft
            // 2. De partial deze NIET al toont
            if ($page && $page->hasMedia() && !$partialHasImage) {
                $mediaHtml = $this->renderPageMediaWrapper($page);
                $html = $mediaHtml . $html;
                
                Log::info('Foto wrapper toegevoegd aan preview', [
                    'page_id' => $page->id,
                    'page_type' => $page->page_type,
                    'position' => $page->media_position,
                ]);
            } elseif ($partialHasImage) {
                Log::info('Partial toont al een foto, wrapper overgeslagen', [
                    'page_id' => $page->id,
                    'page_type' => $page->page_type,
                ]);
            }
            
            return response($html)->header('Content-Type', 'text/html');
            
        } catch (\Exception $e) {
            Log::error('Preview render error', [
                'template_id' => $rapportTemplate->id,
                'page_type' => $pageType,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Preview fout: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Genereer dummy inspanningstest data voor preview
     */
    private function generateDummyInspanningstestData(): array
    {
        // Maak dummy klant object
        $klant = new \App\Models\Klant([
            'id' => 999,
            'voornaam' => 'Jan',
            'achternaam' => 'Janssen',
            'email' => 'jan.janssen@example.com',
            'geboortedatum' => '1988-05-15',
            'geslacht' => 'man',
            'telefoonnummer' => '06-12345678',
        ]);

        // Bereken leeftijd
        $klant->leeftijd = 35;

        // Maak dummy inspanningstest object
        $inspanningstest = new \App\Models\Inspanningstest([
            'id' => 999,
            'klant_id' => 999,
            'testdatum' => now()->format('Y-m-d'),
            'testtype' => 'looptest',
            'testlocatie' => 'Bonami Sportmedisch Centrum',
            
            // Fysieke gegevens
            'lichaamslengte_cm' => 180,
            'lichaamsgewicht_kg' => 75,
            'bmi' => 23.1,
            'vetpercentage' => 12.5,
            'buikomtrek_cm' => 85,
            
            // Hartslag gegevens
            'hartslag_rust_bpm' => 55,
            'maximale_hartslag_bpm' => 185,
            
            // Trainingstatus
            'slaapkwaliteit' => 8,
            'eetlust' => 7,
            'gevoel_op_training' => 7,
            'stressniveau' => 4,
            'gemiddelde_trainingstatus' => 6.5,
            'training_dag_voor_test' => 'Rustige duurloop 45 min',
            'training_2d_voor_test' => 'Intervaltraining',
            
            // Drempelwaarden
            'aerobe_drempel_vermogen' => 250,
            'aerobe_drempel_hartslag' => 145,
            'anaerobe_drempel_vermogen' => 310,
            'anaerobe_drempel_hartslag' => 170,
            
            // Doelstellingen
            'specifieke_doelstellingen' => 'Verbeteren van marathon tijd naar sub 3:30',
            
            // Zones data
            'zones_eenheid' => 'hartslag',
            'zones_aantal' => 6,
            'trainingszones_data' => json_encode([
                ['naam' => 'HERSTEL', 'minHartslag' => 100, 'maxHartslag' => 125, 'kleur' => '#E3F2FD'],
                ['naam' => 'LANGE DUUR', 'minHartslag' => 125, 'maxHartslag' => 145, 'kleur' => '#E8F5E8'],
                ['naam' => 'EXTENSIEF', 'minHartslag' => 145, 'maxHartslag' => 160, 'kleur' => '#F1F8E9'],
                ['naam' => 'INTENSIEF', 'minHartslag' => 160, 'maxHartslag' => 170, 'kleur' => '#FFF3E0'],
                ['naam' => 'TEMPO', 'minHartslag' => 170, 'maxHartslag' => 180, 'kleur' => '#FFEBEE'],
                ['naam' => 'MAXIMAAL', 'minHartslag' => 180, 'maxHartslag' => 185, 'kleur' => '#FFCDD2'],
            ]),
            
            // AI Analyse
            'complete_ai_analyse' => "🏃‍♂️ ANALYSE\n\nJe hebt een sterke aerobe basis met goede drempelwaarden. Focus op tempo-intervallen om je anaerobe capaciteit te verbeteren.\n\nAanbevolen training:\n- 80% onder aerobe drempel\n- 15% tempo/interval\n- 5% herstel",
        ]);

        // Relatie simuleren
        $inspanningstest->klant = $klant;

        // Dummy testresultaten als Collection (simuleer database relatie)
        $testresultaten = collect([
            (object)['tijd' => 3, 'snelheid' => 8.0, 'hartslag' => 120, 'lactaat' => 1.2, 'borg' => 8, 'vermogen' => 150],
            (object)['tijd' => 6, 'snelheid' => 9.0, 'hartslag' => 135, 'lactaat' => 1.8, 'borg' => 10, 'vermogen' => 180],
            (object)['tijd' => 9, 'snelheid' => 10.0, 'hartslag' => 150, 'lactaat' => 2.5, 'borg' => 12, 'vermogen' => 210],
            (object)['tijd' => 12, 'snelheid' => 11.0, 'hartslag' => 165, 'lactaat' => 3.8, 'borg' => 14, 'vermogen' => 240],
            (object)['tijd' => 15, 'snelheid' => 12.0, 'hartslag' => 178, 'lactaat' => 6.5, 'borg' => 17, 'vermogen' => 270],
        ]);

        // Zowel als property EN als method (voor backward compatibility)
        $inspanningstest->testresultaten = $testresultaten;
        $inspanningstest->setRelation('testresultaten', $testresultaten);
        
        // Extra properties die partials mogelijk verwachten
        $inspanningstest->heeft_testresultaten = true;
        $inspanningstest->aantal_testresultaten = $testresultaten->count();
        $inspanningstest->heeft_grafiek = true;
        
        // Dummy grafiek data voor Chart.js
        $grafiekData = [
            'labels' => [3, 6, 9, 12, 15],
            'datasets' => [
                [
                    'label' => 'Hartslag (bpm)',
                    'data' => [120, 135, 150, 165, 178],
                    'borderColor' => 'rgb(239, 68, 68)',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                ],
                [
                    'label' => 'Lactaat (mmol/L)',
                    'data' => [1.2, 1.8, 2.5, 3.8, 6.5],
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                ]
            ]
        ];
        
        // Dummy grafiek analyse (voor grafiek partial)
        $grafiekAnalyse = [
            'aerobe_drempel' => [
                'tijd' => 9,
                'hartslag' => 145,
                'lactaat' => 2.5,
                'snelheid' => 10.0
            ],
            'anaerobe_drempel' => [
                'tijd' => 12,
                'hartslag' => 170,
                'lactaat' => 3.8,
                'snelheid' => 11.0
            ]
        ];

        return [
            'klant' => $klant,
            'inspanningstest' => $inspanningstest,
            'grafiekData' => $grafiekData,
            'grafiekAnalyse' => $grafiekAnalyse,
        ];
    }
    
    private function getPartialPathForPageType(string $pageType): ?string
    {
        // Map van page types naar partial bestandsnamen (zonder prefix)
        $partialMap = [
            'ai-analyse' => 'ai-analyse',
            'algemeen' => 'algemeen',
            'cover' => 'cover',
            'drempelwaarden' => 'drempelwaarden',
            'grafiek' => 'grafiek',
            'testresultaten' => 'testresultaten',
            'trainingstatus' => 'trainingstatus',
            'zones' => 'zones'
        ];

        if (!isset($partialMap[$pageType])) {
            \Log::error('Partial niet gevonden in map', [
                'page_type' => $pageType,
                'available_types' => array_keys($partialMap)
            ]);
            return null;
        }

        $partialName = $partialMap[$pageType];
        
        // Check in rapport-partials/inspanningstest directory
        $viewPath = "rapport-partials.inspanningstest.{$partialName}";
        
        if (view()->exists($viewPath)) {
            return $viewPath;
        }
        
        // Fallback: check in inspanningstest/partials directory
        $fallbackPath = "inspanningstest.partials._{$partialName}";
        
        if (view()->exists($fallbackPath)) {
            return $fallbackPath;
        }

        \Log::error('Partial view bestaat niet', [
            'page_type' => $pageType,
            'tried_paths' => [$viewPath, $fallbackPath]
        ]);
        
        return null;
    }

    /**
     * Refresh preview cache
     */
    public function refreshPreview()
    {
        $previewService = app(RapportPreviewService::class);
        $previewService->clearCache();
        
        return response()->json([
            'success' => true,
            'message' => 'Preview cache ververst'
        ]);
    }

    private function getPartialViewForPageType($pageType, $categorie = 'inspanningstest')
    {
        // Verwijder het categorie prefix als het aanwezig is (bijv. 'inspanningstest_ai_analyse' -> 'ai-analyse')
        $cleanPageType = str_replace($categorie . '_', '', $pageType);
        $cleanPageType = str_replace('_', '-', $cleanPageType);
        
        $partialPath = "sjablonen.partials.{$categorie}.{$cleanPageType}";
        
        if (view()->exists($partialPath)) {
            return $partialPath;
        }
        
        // Fallback: probeer zonder categorie prefix
        $fallbackPath = "sjablonen.partials.{$cleanPageType}";
        if (view()->exists($fallbackPath)) {
            return $fallbackPath;
        }
        
        // Log beschikbare types voor debugging
        $availableTypes = $this->getAvailablePartialTypes($categorie);
        \Log::error('Partial niet gevonden in map', [
            'page_type' => $pageType,
            'available_types' => $availableTypes
        ]);
        
        return null;
    }

    private function getAvailablePartialTypes($categorie = 'inspanningstest')
    {
        $partialPath = resource_path("views/sjablonen/partials/{$categorie}");
        
        if (!is_dir($partialPath)) {
            return [];
        }
        
        $files = scandir($partialPath);
        $types = [];
        
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..' && str_ends_with($file, '.blade.php')) {
                $types[] = str_replace('.blade.php', '', $file);
            }
        }
        
        return $types;
    }

    /**
     * Check of de partial HTML al een afbeelding bevat
     * Dit voorkomt dubbele foto's in de preview
     */
    private function partialAlreadyShowsImage(string $html): bool
    {
        // Check voor verschillende patronen die aangeven dat er al een foto is
        $patterns = [
            '/<img[^>]+data-page-id/',           // Onze eigen wrapper
            '/<img[^>]+class="[^"]*page-media/', // Page media class
            '/<div[^>]+class="[^"]*cover-image/', // Cover image
            '/storage\/rapport-templates/',       // Direct uploaded foto's
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $html)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Genereer HTML wrapper voor pagina media
     * Dit zorgt ervoor dat foto's ALTIJD getoond worden in de preview
     */
    private function renderPageMediaWrapper(RapportTemplatePage $page): string
    {
        if (!$page->hasMedia()) {
            return '';
        }

        $mediaUrl = $page->media_url;
        $position = $page->media_position;
        $size = $page->media_size;
        $opacity = $page->getMediaOpacity();

        // Bepaal CSS op basis van positie met inline styles (werkt altijd)
        $containerStyle = '';
        $imgStyle = '';
        $containerClass = 'page-media-wrapper';
        
        switch ($position) {
            case 'background':
                // Achtergrond: volledige breedte/hoogte, achter content
                $containerStyle = "position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: -1; opacity: {$opacity};";
                $imgStyle = "width: 100%; height: 100%; object-fit: cover; display: block;";
                break;
                
            case 'top':
                // Bovenkant: gecentreerd, met margin
                $containerStyle = "margin-bottom: 1.5rem; text-align: center; clear: both;";
                $imgStyle = "max-width: {$size}%; height: auto; border-radius: 0.5rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1); display: inline-block;";
                break;
                
            case 'bottom':
                // Onderkant: gecentreerd, met margin
                $containerStyle = "margin-top: 1.5rem; text-align: center; clear: both;";
                $imgStyle = "max-width: {$size}%; height: auto; border-radius: 0.5rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1); display: inline-block;";
                break;
                
            case 'left':
                // Links: float, tekst loopt eromheen
                $containerStyle = "float: left; margin: 0 1.5rem 1rem 0;";
                $imgStyle = "max-width: {$size}%; height: auto; border-radius: 0.5rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1); display: block;";
                break;
                
            case 'right':
                // Rechts: float, tekst loopt eromheen
                $containerStyle = "float: right; margin: 0 0 1rem 1.5rem;";
                $imgStyle = "max-width: {$size}%; height: auto; border-radius: 0.5rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1); display: block;";
                break;
                
            default:
                // Fallback: centered
                $containerStyle = "margin-bottom: 1.5rem; text-align: center;";
                $imgStyle = "max-width: {$size}%; height: auto; border-radius: 0.5rem; display: inline-block;";
                break;
        }

        // Return clean HTML (geen newlines voor betere rendering)
        return sprintf(
            '<div class="%s" style="%s"><img src="%s" alt="Pagina %d media" style="%s" data-page-id="%d" data-position="%s" data-size="%d" title="Foto positie: %s | Grootte: %d%% | Opacity: %.1f"></div>',
            htmlspecialchars($containerClass),
            $containerStyle,
            htmlspecialchars($mediaUrl),
            $page->page_number,
            $imgStyle,
            $page->id,
            htmlspecialchars($position),
            $size,
            $position,
            $size,
            $opacity
        );
    }
}
