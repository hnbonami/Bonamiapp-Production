<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dienst;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DienstenController extends Controller
{
    /**
     * Toon diensten beheer overzicht
     */
    public function index()
    {
        // Haal alle diensten op, gesorteerd op volgorde
        $diensten = Dienst::orderBy('sorteer_volgorde')->get();
        
        return view('admin.prestaties.diensten', compact('diensten'));
    }
    
    /**
     * Sla nieuwe dienst op
     */
    public function store(Request $request)
    {
        // Valideer input
        $validated = $request->validate([
            'naam' => 'required|string|max:255',
            'omschrijving' => 'nullable|string',
            'prijs' => 'required|numeric|min:0',
            'commissie_percentage' => 'required|numeric|min:0|max:100',
            'actief' => 'nullable|boolean',
        ]);
        
        // Zet actief op true als checkbox aangevinkt was
        $validated['actief'] = $request->has('actief') ? true : false;
        
        // Bepaal sorteer volgorde (laatste + 1)
        $maxVolgorde = Dienst::max('sorteer_volgorde') ?? 0;
        $validated['sorteer_volgorde'] = $maxVolgorde + 1;
        
        // Maak dienst aan
        Dienst::create($validated);
        
        return redirect()->route('admin.prestaties.diensten.index')
            ->with('success', 'Dienst succesvol aangemaakt!');
    }    /**
     * Sla nieuwe dienst op
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'naam' => 'required|string|max:255',
            'beschrijving' => 'nullable|string',
            'standaard_prijs' => 'required|numeric|min:0',
            'btw_percentage' => 'required|numeric|min:0|max:100',
            'is_actief' => 'boolean',
        ]);

        $dienst = Dienst::create($validated);

        Log::info('Dienst aangemaakt', [
            'dienst_id' => $dienst->id,
            'naam' => $dienst->naam,
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('admin.diensten.index')
            ->with('success', 'Dienst succesvol aangemaakt!');
    }

    /**
     * Update bestaande dienst
     */
    public function update(Request $request, Dienst $dienst)
    {
        $validated = $request->validate([
            'naam' => 'required|string|max:255',
            'beschrijving' => 'nullable|string',
            'standaard_prijs' => 'required|numeric|min:0',
            'btw_percentage' => 'required|numeric|min:0|max:100',
            'is_actief' => 'boolean',
            'sorteer_volgorde' => 'nullable|integer',
        ]);

        $dienst->update($validated);

        Log::info('Dienst bijgewerkt', [
            'dienst_id' => $dienst->id,
            'naam' => $dienst->naam,
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('admin.diensten.index')
            ->with('success', 'Dienst succesvol bijgewerkt!');
    }

    /**
     * Verwijder dienst (soft delete)
     */
    public function destroy(Dienst $dienst)
    {
        $dienst->delete();

        Log::info('Dienst verwijderd', [
            'dienst_id' => $dienst->id,
            'naam' => $dienst->naam,
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('admin.diensten.index')
            ->with('success', 'Dienst succesvol verwijderd!');
    }
}
