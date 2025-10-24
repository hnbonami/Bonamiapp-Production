<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dienst;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DienstenController extends Controller
{
    /**
     * Toon overzicht van alle diensten
     */
    public function index()
    {
        // 🔒 ORGANISATIE FILTER: Alleen diensten van eigen organisatie
        $diensten = Dienst::where('organisatie_id', auth()->user()->organisatie_id)
            ->orderBy('naam')
            ->get();

        return view('admin.prestaties.diensten', compact('diensten'));
    }

    /**
     * Sla nieuwe dienst op
     */
    public function store(Request $request)
    {
        // Log alle input voor debugging
        \Log::info('Store dienst request', [
            'all_input' => $request->all(),
            'has_actief' => $request->has('actief'),
            'actief_value' => $request->input('actief'),
        ]);
        
        // Valideer input
        $validated = $request->validate([
            'naam' => 'required|string|max:255',
            'beschrijving' => 'nullable|string',  // CHANGED from omschrijving
            'standaard_prijs' => 'required|numeric|min:0',
            'btw_percentage' => 'required|numeric|in:0,6,12,21',
            'prijs_type' => 'nullable|in:incl,excl',
            'commissie_percentage' => 'required|numeric|min:0|max:100',
        ]);

        // Bereken automatisch incl/excl prijzen op basis van prijs_type
        if ($validated['prijs_type'] === 'incl') {
            $validated['prijs_incl_btw'] = $validated['standaard_prijs'];
            $validated['prijs_excl_btw'] = $validated['standaard_prijs'] / (1 + ($validated['btw_percentage'] / 100));
        } else {
            $validated['prijs_excl_btw'] = $validated['standaard_prijs'];
            $validated['prijs_incl_btw'] = $validated['standaard_prijs'] * (1 + ($validated['btw_percentage'] / 100));
        }
        
        // Actief status - BELANGRIJKE FIX: checkbox is aanwezig = true, niet aanwezig = false
        $validated['is_actief'] = $request->has('actief') && $request->input('actief') == '1';
        
        // 🔒 KRITIEK: Voeg organisatie_id toe
        $validated['organisatie_id'] = auth()->user()->organisatie_id;
        
        \Log::info('Store dienst validated data', [
            'validated' => $validated,
            'is_actief_final' => $validated['is_actief']
        ]);
        
        // Maak dienst aan
        $dienst = Dienst::create($validated);

        Log::info('Dienst aangemaakt', [
            'dienst_id' => $dienst->id,
            'naam' => $dienst->naam,
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('admin.prestaties.diensten.index')
            ->with('success', 'Dienst succesvol aangemaakt!');
    }

    /**
     * Update bestaande dienst
     */
    public function update(Request $request, Dienst $dienst)
    {
        // Log alle input voor debugging
        \Log::info('Update dienst request', [
            'dienst_id' => $dienst->id,
            'all_input' => $request->all(),
            'has_actief' => $request->has('actief'),
            'actief_value' => $request->input('actief'),
        ]);
        
        // Valideer input
        $validated = $request->validate([
            'naam' => 'required|string|max:255',
            'beschrijving' => 'nullable|string',
            'standaard_prijs' => 'required|numeric|min:0',
            'btw_percentage' => 'required|numeric|in:0,6,12,21',
            'prijs_type' => 'nullable|in:incl,excl',
            'commissie_percentage' => 'required|numeric|min:0|max:100',
        ]);
        
        // Bereken BTW prijzen
        $btwPercentage = $validated['btw_percentage'];
        $prijsType = $request->input('prijs_type', 'incl');
        
        if ($prijsType === 'incl') {
            $validated['prijs_incl_btw'] = $validated['standaard_prijs'];
            $validated['prijs_excl_btw'] = $validated['standaard_prijs'] / (1 + ($btwPercentage / 100));
        } else {
            $validated['prijs_excl_btw'] = $validated['standaard_prijs'];
            $validated['prijs_incl_btw'] = $validated['standaard_prijs'] * (1 + ($btwPercentage / 100));
        }
        
        // Actief status - BELANGRIJKE FIX: checkbox is aanwezig = true, niet aanwezig = false
        $validated['is_actief'] = $request->has('actief') && $request->input('actief') == '1';
        
        \Log::info('Update dienst validated data', [
            'validated' => $validated,
            'is_actief_final' => $validated['is_actief']
        ]);
        
        // Update dienst
        $dienst->update($validated);
        
        \Log::info('Dienst updated', [
            'dienst_id' => $dienst->id,
            'is_actief_na_update' => $dienst->fresh()->is_actief
        ]);
        
        return redirect()->route('admin.prestaties.diensten.index')
            ->with('success', 'Dienst succesvol bijgewerkt!');
    }

    /**
     * Verwijder dienst
     */
    public function destroy(Dienst $dienst)
    {
        $dienst->delete();

        Log::info('Dienst verwijderd', [
            'dienst_id' => $dienst->id,
            'naam' => $dienst->naam,
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('admin.prestaties.diensten.index')
            ->with('success', 'Dienst succesvol verwijderd!');
    }
}
