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
        
        return view('admin.prestaties.diensten', compact('diensten'));
    }

    /**
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
