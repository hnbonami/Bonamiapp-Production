<?php

namespace App\Http\Controllers;

use App\Models\Sjabloon;
use Illuminate\Http\Request;

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

    public function show(Sjabloon $sjabloon)
    {
        return view('sjablonen.show', compact('sjabloon'));
    }

    public function edit(Sjabloon $sjabloon)
    {
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

        // Ensure sjabloon has pages for the editor - ALWAYS create a valid collection
        $sjabloon->pages = collect([
            (object)[
                'id' => 1,
                'page_number' => 1,
                'content' => '<p>Start met bewerken...</p>',
                'is_url_page' => false,
                'background_image' => null,
                'url' => null
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

    public function destroy(Sjabloon $sjabloon)
    {
        $sjabloon->update(['is_actief' => false]);
        
        return redirect()->route('sjablonen.index')
                        ->with('success', 'Sjabloon gearchiveerd!');
    }
}