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
        $diensten = Dienst::orderBy('sorteer_volgorde')->get();
        return view('admin.prestaties.diensten', compact('diensten'));
    }
    
    /**
     * Sla nieuwe dienst op
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'naam' => 'required|string|max:255',
            'omschrijving' => 'nullable|string',
            'prijs' => 'required|numeric|min:0',
            'commissie_percentage' => 'required|numeric|min:0|max:100',
            'actief' => 'nullable|boolean',
        ]);
        
        // Map formulier velden naar database kolommen
        $dienst = Dienst::create([
            'naam' => $validated['naam'],
            'beschrijving' => $validated['omschrijving'] ?? null,
            'standaard_prijs' => (float) $validated['prijs'],
            'commissie_percentage' => (float) $validated['commissie_percentage'],
            'is_actief' => $request->has('actief'),
            'sorteer_volgorde' => (Dienst::max('sorteer_volgorde') ?? 0) + 1,
        ]);
        
        Log::info('Dienst aangemaakt', ['dienst_id' => $dienst->id, 'user_id' => auth()->id()]);
        
        return redirect()->route('admin.prestaties.diensten.index')
            ->with('success', 'Dienst succesvol aangemaakt!');
    }

    /**
     * Update bestaande dienst
     */
    public function update(Request $request, Dienst $dienst)
    {
        $validated = $request->validate([
            'naam' => 'required|string|max:255',
            'omschrijving' => 'nullable|string',
            'prijs' => 'required|numeric|min:0',
            'commissie_percentage' => 'required|numeric|min:0|max:100',
            'actief' => 'nullable|boolean',
        ]);
        
        $dienst->update([
            'naam' => $validated['naam'],
            'beschrijving' => $validated['omschrijving'] ?? null,
            'standaard_prijs' => (float) $validated['prijs'],
            'commissie_percentage' => (float) $validated['commissie_percentage'],
            'is_actief' => $request->has('actief'),
        ]);
        
        Log::info('Dienst bijgewerkt', ['dienst_id' => $dienst->id, 'user_id' => auth()->id()]);
        
        return redirect()->route('admin.prestaties.diensten.index')
            ->with('success', 'Dienst succesvol bijgewerkt!');
    }

    /**
     * Verwijder dienst
     */
    public function destroy(Dienst $dienst)
    {
        $dienst->delete();
        
        Log::info('Dienst verwijderd', ['dienst_id' => $dienst->id, 'user_id' => auth()->id()]);
        
        return redirect()->route('admin.prestaties.diensten.index')
            ->with('success', 'Dienst succesvol verwijderd!');
    }
}
