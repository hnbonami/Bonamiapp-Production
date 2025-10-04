<?php

namespace App\Http\Controllers;

use App\Models\Sjabloon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SjablonenController extends Controller
{
    /**
     * Display a listing of templates.
     */
    /**
     * Display a listing of the templates.
     */
    public function index()
    {
        try {
            $sjablonen = Sjabloon::orderBy('created_at', 'desc')->get();
        } catch (\Exception $e) {
            // Fallback to empty collection if database error
            $sjablonen = collect([]);
        }
        
        // Always ensure we have a collection, never null
        if (is_null($sjablonen)) {
            $sjablonen = collect([]);
        }
        
        return view('sjablonen.index', compact('sjablonen'));
    }

    /**
     * Show the form for creating a new template.
     */
    public function create()
    {
        return view('sjablonen.create');
    }

    /**
     * Store a newly created template in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'naam' => 'required|string|max:255',
            'categorie' => 'required|in:bikefit,inspanningstest',
            'testtype' => 'nullable|string|max:255',
            'beschrijving' => 'nullable|string',
        ]);

        Sjabloon::create($request->all());

        return redirect()->route('sjablonen.index')
            ->with('success', 'Sjabloon succesvol aangemaakt!');
    }

    /**
     * Display the specified template.
     */
    public function show(Sjabloon $sjablonen)
    {
        return view('sjablonen.show', ['sjabloon' => $sjablonen]);
    }

    /**
     * Show the form for editing the specified template.
     */
    public function edit(Sjabloon $sjablonen)
    {
        return view('sjablonen.edit', ['sjabloon' => $sjablonen]);
    }

    /**
     * Update the specified template in storage.
     */
    public function update(Request $request, Sjabloon $sjabloon)
    {
        $request->validate([
            'naam' => 'required|string|max:255',
            'categorie' => 'required|in:bikefit,inspanningstest',
            'testtype' => 'nullable|string|max:255',
            'beschrijving' => 'nullable|string',
            'inhoud' => 'nullable|string',
        ]);

        $sjabloon->update($request->all());

        return redirect()->route('sjablonen.index')
            ->with('success', 'Sjabloon bijgewerkt!');
    }

    /**
     * Remove the specified template from storage.
     */
    public function destroy(Sjabloon $sjabloon)
    {
        $sjabloon->delete();
        return redirect()->route('sjablonen.index')
            ->with('success', 'Sjabloon verwijderd!');
    }
    
    /**
     * Add a new page to the template
     */
    public function addPagina(Request $request, Sjabloon $sjabloon)
    {
        $request->validate([
            'titel' => 'required|string|max:255',
            'achtergrond_url' => 'nullable|string',
        ]);

        $pagina = DB::table('sjabloon_paginas')->insertGetId([
            'sjabloon_id' => $sjabloon->id,
            'titel' => $request->titel,
            'inhoud' => '',
            'achtergrond_url' => $request->achtergrond_url,
            'volgorde' => DB::table('sjabloon_paginas')->where('sjabloon_id', $sjabloon->id)->count() + 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'pagina_id' => $pagina]);
    }

    /**
     * Update a template page
     */
    public function updatePagina(Request $request, Sjabloon $sjabloon, $pagina)
    {
        $request->validate([
            'inhoud' => 'required|string',
            'achtergrond_url' => 'nullable|string',
        ]);

        DB::table('sjabloon_paginas')
            ->where('id', $pagina)
            ->where('sjabloon_id', $sjabloon->id)
            ->update([
                'inhoud' => $request->inhoud,
                'achtergrond_url' => $request->achtergrond_url,
                'updated_at' => now(),
            ]);

        return response()->json(['success' => true]);
    }

    /**
     * Delete a template page
     */
    public function deletePagina(Sjabloon $sjabloon, $pagina)
    {
        DB::table('sjabloon_paginas')
            ->where('id', $pagina)
            ->where('sjabloon_id', $sjabloon->id)
            ->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Generate report from template
     */
    public function generateReport(Sjabloon $sjabloon)
    {
        $paginas = DB::table('sjabloon_paginas')
            ->where('sjabloon_id', $sjabloon->id)
            ->orderBy('volgorde')
            ->get();

        return view('sjablonen.generated-report', compact('sjabloon', 'paginas'));
    }
}