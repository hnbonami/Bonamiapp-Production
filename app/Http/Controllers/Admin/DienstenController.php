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
        $diensten = Dienst::orderBy('sorteer_volgorde')->get();
        
        return view('admin.diensten.index', compact('diensten'));
    }

    /**
     * Sla nieuwe dienst op
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'naam' => 'required|string|max:255',
            'omschrijving' => 'nullable|string',
            'standaard_prijs' => 'required|numeric|min:0',
            'btw_percentage' => 'required|numeric|in:0,6,12,21',
            'prijs_type' => 'required|in:incl,excl',
            'commissie_percentage' => 'required|numeric|min:0|max:100',
            'is_actief' => 'boolean',
        ]);

        // Bereken automatisch incl/excl prijzen op basis van prijs_type
        if ($validated['prijs_type'] === 'incl') {
            $validated['prijs_incl_btw'] = $validated['standaard_prijs'];
            $validated['prijs_excl_btw'] = $validated['standaard_prijs'] / (1 + ($validated['btw_percentage'] / 100));
        } else {
            $validated['prijs_excl_btw'] = $validated['standaard_prijs'];
            $validated['prijs_incl_btw'] = $validated['standaard_prijs'] * (1 + ($validated['btw_percentage'] / 100));
        }

        // Verwijder prijs_type uit validated data (niet in database)
        unset($validated['prijs_type']);
        
        // Zorg dat is_actief een boolean is
        $validated['is_actief'] = $request->has('is_actief') ? true : false;

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
            'omschrijving' => 'nullable|string',
            'standaard_prijs' => 'required|numeric|min:0',
            'btw_percentage' => 'required|numeric|in:0,6,12,21',
            'prijs_type' => 'required|in:incl,excl',
            'commissie_percentage' => 'required|numeric|min:0|max:100',
            'is_actief' => 'boolean',
        ]);

        // Bereken automatisch incl/excl prijzen op basis van prijs_type
        if ($validated['prijs_type'] === 'incl') {
            $validated['prijs_incl_btw'] = $validated['standaard_prijs'];
            $validated['prijs_excl_btw'] = $validated['standaard_prijs'] / (1 + ($validated['btw_percentage'] / 100));
        } else {
            $validated['prijs_excl_btw'] = $validated['standaard_prijs'];
            $validated['prijs_incl_btw'] = $validated['standaard_prijs'] * (1 + ($validated['btw_percentage'] / 100));
        }

        // Verwijder prijs_type uit validated data
        unset($validated['prijs_type']);
        
        // Zorg dat is_actief een boolean is
        $validated['is_actief'] = $request->has('is_actief') ? true : false;

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

        return redirect()->route('admin.diensten.index')
            ->with('success', 'Dienst succesvol verwijderd!');
    }
}
