<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sjabloon;
use App\Models\SjabloonPagina;
use Illuminate\Support\Facades\Storage;

class SjablonenController extends Controller
{
    public function index()
    {
        $sjablonen = Sjabloon::with('paginas')->get();
        return view('sjablonen.index', compact('sjablonen'));
    }

    public function create()
    {
        $testtypes = [
            'bikefit' => [
                'standaard_bikefit' => 'Standaard Bikefit',
                'professionele_bikefit' => 'Professionele Bikefit',
                'zadeldrukmeting' => 'Zadeldrukmeting',
                'maatbepaling' => 'Maatbepaling'
            ],
            'inspanningstest' => [
                'inspanningstest_fietsen' => 'Inspanningstest Fietsen',
                'inspanningstest_lopen' => 'Inspanningstest Lopen'
            ]
        ];
        
        return view('sjablonen.create', compact('testtypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'naam' => 'required|string|max:255',
            'categorie' => 'required|string|in:bikefit,inspanningstest',
            'testtype' => 'required|string',
            'beschrijving' => 'nullable|string'
        ]);

        $sjabloon = Sjabloon::create([
            'naam' => $request->naam,
            'categorie' => $request->categorie,
            'testtype' => $request->testtype,
            'beschrijving' => $request->beschrijving,
            'user_id' => auth()->id()
        ]);

        // Maak standaard eerste pagina aan
        $sjabloon->paginas()->create([
            'pagina_nummer' => 1,
            'achtergrond_url' => null,
            'inhoud' => '<p>Voeg hier uw content toe...</p>'
        ]);

        return redirect()->route('sjablonen.edit', $sjabloon)
            ->with('success', 'Sjabloon aangemaakt! Nu kunt u de inhoud bewerken.');
    }

    public function show($id)
    {
        $sjabloon = Sjabloon::with('paginas')->findOrFail($id);
        
        // Gebruik 'template' in plaats van 'sjabloon' voor backward compatibility met views
        $template = $sjabloon;
        
        return view('sjablonen.show', compact('template', 'sjabloon'));
    }

    public function edit($id)
    {
        $sjabloon = Sjabloon::with('paginas')->findOrFail($id);
        
        // Debug: Log wat we ophalen
        \Log::info('Sjabloon data:', [
            'id' => $sjabloon->id,
            'naam' => $sjabloon->naam,
            'categorie' => $sjabloon->categorie,
            'paginas_count' => $sjabloon->paginas->count()
        ]);
        
        // Haal beschikbare achtergronden op
        $backgrounds = [];
        $backgroundPath = public_path('backgrounds');
        if (is_dir($backgroundPath)) {
            $files = scandir($backgroundPath);
            foreach ($files as $file) {
                if (in_array(pathinfo($file, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'gif'])) {
                    $backgrounds[] = '/backgrounds/' . $file;
                }
            }
        }
        
        // Haal alle beschikbare sleutels op voor de sjabloon editor
        $availableKeys = $this->getAvailableKeys($sjabloon->categorie) ?? [];
        
        // Gebruik 'template' in plaats van 'sjabloon' voor backward compatibility met views
        $template = $sjabloon;
        
        // Voeg templateKeys toe voor backward compatibility
        $templateKeys = $availableKeys;
        
        // Zorg dat paginas altijd een collectie is
        if (!$template->paginas) {
            $template->paginas = collect();
        }
        
        // Als er geen pagina's zijn, maak er een aan
        if ($template->paginas->isEmpty()) {
            $template->paginas()->create([
                'pagina_nummer' => 1,
                'achtergrond_url' => null,
                'inhoud' => '<p>Voeg hier uw content toe...</p>'
            ]);
            $template->load('paginas'); // Herlaad relatie
        }
        
        // Voeg extra variabelen toe die de view mogelijk verwacht
        $pages = $template->paginas ?? collect();
        $currentPage = $pages->first();
        $backgroundLibrary = $backgrounds ?: [];
        
        // Zorg ervoor dat alle mogelijke arrays leeg zijn in plaats van null
        $categories = [];
        $testtypes = [];
        $allBackgrounds = $backgrounds ?: [];
        $availableBackgrounds = $backgrounds ?: [];
        
        // Voeg specifieke variabelen toe die de view mogelijk verwacht op regel 174
        $backgroundImages = $backgrounds ?: [];
        $templateCategories = [];
        $placeholders = [];
        $fieldGroups = [];
        
        // Maak alle variabelen expliciet niet-null
        $variables = [
            'template' => $template, 
            'sjabloon' => $sjabloon, 
            'backgrounds' => $backgrounds ?: [],
            'backgroundLibrary' => $backgroundLibrary,
            'allBackgrounds' => $allBackgrounds,
            'availableBackgrounds' => $availableBackgrounds,
            'backgroundImages' => $backgroundImages,
            'availableKeys' => $availableKeys ?: [], 
            'templateKeys' => $templateKeys ?: [],
            'templateCategories' => $templateCategories,
            'placeholders' => $placeholders,
            'fieldGroups' => $fieldGroups,
            'pages' => $pages ?: collect(),
            'currentPage' => $currentPage,
            'categories' => $categories,
            'testtypes' => $testtypes
        ];
        
        \Log::info('View variables:', array_map(function($var) {
            return is_object($var) ? get_class($var) : gettype($var);
        }, $variables));
        
        return view('sjablonen.edit_safe', $variables);
    }

    public function update(Request $request, $id)
    {
        $sjabloon = Sjabloon::findOrFail($id);
        
        $request->validate([
            'naam' => 'required|string|max:255',
            'beschrijving' => 'nullable|string'
        ]);

        $sjabloon->update([
            'naam' => $request->naam,
            'beschrijving' => $request->beschrijving
        ]);

        return redirect()->route('sjablonen.edit', $sjabloon->id)
            ->with('success', 'Sjabloon bijgewerkt!');
    }

    public function destroy($id)
    {
        $sjabloon = Sjabloon::findOrFail($id);
        $sjabloon->delete();
        return redirect()->route('sjablonen.index')
            ->with('success', 'Sjabloon verwijderd!');
    }

    public function updatePagina(Request $request, $sjabloonId, $paginaId)
    {
        $sjabloon = Sjabloon::findOrFail($sjabloonId);
        $pagina = SjabloonPagina::where('sjabloon_id', $sjabloonId)->findOrFail($paginaId);
        
        $request->validate([
            'inhoud' => 'required|string',
            'achtergrond_url' => 'nullable|string'
        ]);

        $pagina->update([
            'inhoud' => $request->inhoud,
            'achtergrond_url' => $request->achtergrond_url
        ]);

        return response()->json(['success' => true]);
    }

    public function addPagina(Request $request, $sjabloonId)
    {
        $sjabloon = Sjabloon::findOrFail($sjabloonId);
        $laatstePagina = $sjabloon->paginas()->orderBy('pagina_nummer', 'desc')->first();
        $nieuwPaginaNummer = $laatstePagina ? $laatstePagina->pagina_nummer + 1 : 1;

        $pagina = $sjabloon->paginas()->create([
            'pagina_nummer' => $nieuwPaginaNummer,
            'achtergrond_url' => null,
            'inhoud' => '<p>Nieuwe pagina...</p>'
        ]);

        return response()->json([
            'success' => true,
            'pagina' => $pagina
        ]);
    }

    public function deletePagina($sjabloonId, $paginaId)
    {
        $sjabloon = Sjabloon::findOrFail($sjabloonId);
        $pagina = SjabloonPagina::where('sjabloon_id', $sjabloonId)->findOrFail($paginaId);
        
        if ($sjabloon->paginas()->count() <= 1) {
            return response()->json([
                'success' => false,
                'message' => 'Kan de laatste pagina niet verwijderen'
            ], 400);
        }

        $pagina->delete();

        return response()->json(['success' => true]);
    }

    public function getTesttypes($categorie)
    {
        $testtypes = [];
        
        switch ($categorie) {
            case 'bikefit':
                $testtypes = [
                    'standaard_bikefit' => 'Standaard Bikefit',
                    'professionele_bikefit' => 'Professionele Bikefit',
                    'zadeldrukmeting' => 'Zadeldrukmeting',
                    'maatbepaling' => 'Maatbepaling'
                ];
                break;
            case 'inspanningstest':
                $testtypes = [
                    'inspanningstest_fietsen' => 'Inspanningstest Fietsen',
                    'inspanningstest_lopen' => 'Inspanningstest Lopen'
                ];
                break;
        }
        
        return response()->json($testtypes);
    }

    private function getAvailableKeys($categorie)
    {
        $keys = [
            'klant' => [
                (object)['placeholder' => '{{klant.naam}}', 'description' => 'Naam van de klant', 'display_name' => 'Naam'],
                (object)['placeholder' => '{{klant.voornaam}}', 'description' => 'Voornaam van de klant', 'display_name' => 'Voornaam'],
                (object)['placeholder' => '{{klant.email}}', 'description' => 'Email van de klant', 'display_name' => 'Email'],
                (object)['placeholder' => '{{klant.telefoon}}', 'description' => 'Telefoonnummer', 'display_name' => 'Telefoon'],
                (object)['placeholder' => '{{klant.geboortedatum}}', 'description' => 'Geboortedatum', 'display_name' => 'Geboortedatum'],
                (object)['placeholder' => '{{klant.sport}}', 'description' => 'Sport van de klant', 'display_name' => 'Sport'],
                (object)['placeholder' => '{{klant.niveau}}', 'description' => 'Niveau van de klant', 'display_name' => 'Niveau'],
                (object)['placeholder' => '{{klant.lengte}}', 'description' => 'Lengte van de klant', 'display_name' => 'Lengte'],
                (object)['placeholder' => '{{klant.gewicht}}', 'description' => 'Gewicht van de klant', 'display_name' => 'Gewicht']
            ]
        ];

        if ($categorie === 'bikefit') {
            $keys['bikefit'] = [
                (object)['placeholder' => '{{bikefit.datum}}', 'description' => 'Datum van de bikefit', 'display_name' => 'Datum'],
                (object)['placeholder' => '{{bikefit.testtype}}', 'description' => 'Type van de bikefit test', 'display_name' => 'Testtype'],
                (object)['placeholder' => '{{bikefit.lengte_cm}}', 'description' => 'Lengte in cm', 'display_name' => 'Lengte (cm)'],
                (object)['placeholder' => '{{bikefit.binnenbeenlengte_cm}}', 'description' => 'Binnenbeenlengte in cm', 'display_name' => 'Binnenbeenlengte (cm)'],
                (object)['placeholder' => '{{bikefit.armlengte_cm}}', 'description' => 'Armlengte in cm', 'display_name' => 'Armlengte (cm)'],
                (object)['placeholder' => '{{bikefit.romplengte_cm}}', 'description' => 'Romplengte in cm', 'display_name' => 'Romplengte (cm)'],
                (object)['placeholder' => '{{bikefit.schouderbreedte_cm}}', 'description' => 'Schouderbreedte in cm', 'display_name' => 'Schouderbreedte (cm)'],
                (object)['placeholder' => '{{bikefit.zadel_trapas_hoek}}', 'description' => 'Zadel-trapas hoek', 'display_name' => 'Zadel-trapas hoek'],
                (object)['placeholder' => '{{bikefit.zadel_trapas_afstand}}', 'description' => 'Zadel-trapas afstand', 'display_name' => 'Zadel-trapas afstand'],
                (object)['placeholder' => '{{bikefit.stuur_trapas_hoek}}', 'description' => 'Stuur-trapas hoek', 'display_name' => 'Stuur-trapas hoek'],
                (object)['placeholder' => '{{bikefit.stuur_trapas_afstand}}', 'description' => 'Stuur-trapas afstand', 'display_name' => 'Stuur-trapas afstand'],
                (object)['placeholder' => '{{bikefit.aanpassingen_zadel}}', 'description' => 'Zadel aanpassingen', 'display_name' => 'Zadel aanpassingen'],
                (object)['placeholder' => '{{bikefit.aanpassingen_setback}}', 'description' => 'Setback aanpassingen', 'display_name' => 'Setback aanpassingen'],
                (object)['placeholder' => '{{bikefit.aanpassingen_reach}}', 'description' => 'Reach aanpassingen', 'display_name' => 'Reach aanpassingen'],
                (object)['placeholder' => '{{bikefit.aanpassingen_drop}}', 'description' => 'Drop aanpassingen', 'display_name' => 'Drop aanpassingen'],
                (object)['placeholder' => '{{bikefit.type_zadel}}', 'description' => 'Type zadel', 'display_name' => 'Type zadel'],
                (object)['placeholder' => '{{bikefit.zadeltil}}', 'description' => 'Zadeltil', 'display_name' => 'Zadeltil'],
                (object)['placeholder' => '{{bikefit.zadelbreedte}}', 'description' => 'Zadelbreedte', 'display_name' => 'Zadelbreedte'],
                (object)['placeholder' => '{{bikefit.fietsmerk}}', 'description' => 'Fietsmerk', 'display_name' => 'Fietsmerk'],
                (object)['placeholder' => '{{bikefit.kadermaat}}', 'description' => 'Kadermaat', 'display_name' => 'Kadermaat'],
                (object)['placeholder' => '{{bikefit.frametype}}', 'description' => 'Frametype', 'display_name' => 'Frametype'],
                (object)['placeholder' => '{{bikefit.algemene_klachten}}', 'description' => 'Algemene klachten', 'display_name' => 'Algemene klachten'],
                (object)['placeholder' => '{{bikefit.opmerkingen}}', 'description' => 'Opmerkingen', 'display_name' => 'Opmerkingen'],
                (object)['placeholder' => '$mobiliteit_tabel_html$', 'description' => 'Mobiliteit tabel (HTML)', 'display_name' => 'Mobiliteit tabel']
            ];
        }

        if ($categorie === 'inspanningstest') {
            $keys['inspanningstest'] = [
                (object)['placeholder' => '{{inspanningstest.datum}}', 'description' => 'Datum van de test', 'display_name' => 'Datum'],
                (object)['placeholder' => '{{inspanningstest.testtype}}', 'description' => 'Type van de test', 'display_name' => 'Testtype'],
                (object)['placeholder' => '{{inspanningstest.sport}}', 'description' => 'Sport', 'display_name' => 'Sport'],
                (object)['placeholder' => '{{inspanningstest.niveau}}', 'description' => 'Niveau', 'display_name' => 'Niveau'],
                (object)['placeholder' => '{{inspanningstest.leeftijd}}', 'description' => 'Leeftijd', 'display_name' => 'Leeftijd'],
                (object)['placeholder' => '{{inspanningstest.gewicht}}', 'description' => 'Gewicht', 'display_name' => 'Gewicht'],
                (object)['placeholder' => '{{inspanningstest.lengte}}', 'description' => 'Lengte', 'display_name' => 'Lengte'],
                (object)['placeholder' => '{{inspanningstest.rustpols}}', 'description' => 'Rustpols', 'display_name' => 'Rustpols'],
                (object)['placeholder' => '{{inspanningstest.maximale_pols}}', 'description' => 'Maximale pols', 'display_name' => 'Maximale pols'],
                (object)['placeholder' => '{{inspanningstest.vo2_max}}', 'description' => 'VO2 max', 'display_name' => 'VO2 max'],
                (object)['placeholder' => '{{inspanningstest.opmerkingen}}', 'description' => 'Opmerkingen', 'display_name' => 'Opmerkingen']
            ];
        }

        return $keys;
    }
}