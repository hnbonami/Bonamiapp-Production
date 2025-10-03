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
        return view('sjablonen.edit', compact('sjabloon'));
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