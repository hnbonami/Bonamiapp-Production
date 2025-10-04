<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sjabloon;

class SjablonenController extends Controller
{
    public function index()
    {
        try {
            $sjablonen = Sjabloon::all();
            return view('sjablonen.index', compact('sjablonen'));
        } catch (\Exception $e) {
            // Fallback if view doesn't exist yet
            return response()->json([
                'message' => 'Sjablonen INDEX werkt! Aantal sjablonen: ' . ($sjablonen->count() ?? 0),
                'sjablonen' => $sjablonen ?? []
            ]);
        }
    }

    public function create()
    {
        return view('sjablonen.create');
    }

    public function store(Request $request)
    {
        $sjabloon = Sjabloon::create($request->all());
        return redirect()->route('sjablonen.index')->with('success', 'Sjabloon aangemaakt!');
    }

    public function show(Sjabloon $sjabloon)
    {
        return view('sjablonen.show', compact('sjabloon'));
    }

    public function edit(Sjabloon $sjabloon)
    {
        // Load pages relation and ensure it's not null
        $sjabloon->load('pages');
        if (!$sjabloon->pages) {
            $sjabloon->pages = collect();
        }
        
        // Ensure at least one page exists - only if sjabloon has valid ID
        if ($sjabloon->id && $sjabloon->pages->isEmpty()) {
            $page = new SjabloonPage([
                'sjabloon_id' => $sjabloon->id,
                'page_number' => 1,
                'content' => '<p>Start met bewerken...</p>',
                'is_url_page' => false
            ]);
            $page->save();
            $sjabloon->load('pages'); // Reload pages
        }
        
        // If still no pages, create a temporary one for the view
        if ($sjabloon->pages->isEmpty()) {
            $sjabloon->pages = collect([
                (object)[
                    'id' => 'temp-1',
                    'page_number' => 1,
                    'content' => '<p>Start met bewerken...</p>',
                    'is_url_page' => false,
                    'background_image' => null,
                    'url' => null
                ]
            ]);
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
        $sjabloon->update($request->all());
        return redirect()->route('sjablonen.index')->with('success', 'Sjabloon bijgewerkt!');
    }

    public function destroy(Sjabloon $sjabloon)
    {
        $sjabloon->delete();
        return redirect()->route('sjablonen.index')->with('success', 'Sjabloon verwijderd!');
    }

    public function getTesttypes($categorie)
    {
        // AJAX endpoint voor testtypes
        $testtypes = ['bikefit', 'inspanningstest', 'algemeen'];
        return response()->json($testtypes);
    }

    public function updatePagina(Request $request, $sjabloon, $pagina)
    {
        // AJAX endpoint voor pagina updates
        return response()->json(['success' => true]);
    }

    public function addPagina(Request $request, $sjabloon)
    {
        // AJAX endpoint voor nieuwe pagina
        return response()->json(['success' => true]);
    }

    public function deletePagina($sjabloon, $pagina)
    {
        // AJAX endpoint voor pagina verwijderen
        return response()->json(['success' => true]);
    }
}