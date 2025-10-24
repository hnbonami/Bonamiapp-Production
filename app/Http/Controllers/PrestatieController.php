<?php

namespace App\Http\Controllers;

use App\Models\Prestatie;
use App\Models\Dienst;
use App\Models\KwartaalOverzicht;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PrestatieController extends Controller
{
    /**
     * Toon prestaties overzicht voor ingelogde coach
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Huidige kwartaal bepalen
        $huidigJaar = $request->input('jaar', date('Y'));
        $huidigKwartaal = $request->input('kwartaal', 'Q' . ceil(date('n') / 3));

        // Haal prestaties op voor dit kwartaal
        $prestaties = Prestatie::where('user_id', $user->id)
            ->where('jaar', $huidigJaar)
            ->where('kwartaal', $huidigKwartaal)
            ->with('dienst')
            ->orderBy('datum_prestatie', 'desc')
            ->get();

        // Haal beschikbare diensten op voor deze coach
        $beschikbareDiensten = Dienst::where('is_actief', true)
            ->orderBy('naam')
            ->get();

        // Haal alle klanten op voor dropdown (alleen voornaam + naam)
        $klanten = \App\Models\Klant::select('id', 'voornaam', 'naam')
            ->orderBy('naam')
            ->orderBy('voornaam')
            ->get()
            ->map(function($klant) {
                return [
                    'id' => $klant->id,
                    'naam' => $klant->voornaam . ' ' . $klant->naam,
                ];
            });

        // Bereken totalen
        $totalen = [
            'bruto' => $prestaties->sum('bruto_prijs'),
            'btw' => $prestaties->sum('btw_bedrag'),
            'netto' => $prestaties->sum('netto_prijs'),
            'commissie' => $prestaties->sum('commissie_bedrag'),
        ];

        // Haal beschikbare jaren op (voor filter dropdown)
        $beschikbareJaren = \App\Models\Prestatie::where('user_id', $user->id)
            ->selectRaw('DISTINCT jaar')
            ->pluck('jaar');
        
        // Bereken totale commissie voor dit kwartaal
        $totaleCommissie = $prestaties->sum('commissie_bedrag');
        
        return view('prestaties.index', compact(
            'prestaties',
            'beschikbareDiensten',
            'klanten',
            'huidigJaar',
            'huidigKwartaal',
            'beschikbareJaren',
            'totaleCommissie'
        ));
    }

    /**
     * Sla nieuwe prestatie op
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'datum_prestatie' => 'required|date',
            'einddatum_prestatie' => 'nullable|date|after_or_equal:datum_prestatie',
            'dienst_id' => 'required|exists:diensten,id',
            'klant_id' => 'nullable|exists:klanten,id',
            'prijs' => 'required|numeric|min:0',
            'opmerkingen' => 'nullable|string',
        ]);
        
        // Haal dienst op voor commissie berekening
        $dienst = Dienst::findOrFail($validated['dienst_id']);
        
        // Haal commissie percentage op (custom of standaard)
        $userDienst = auth()->user()->diensten()->where('dienst_id', $dienst->id)->first();
        $commissiePercentage = $userDienst ? $userDienst->pivot->commissie_percentage : $dienst->commissie_percentage;
        
        // Bereken commissie bedrag
        $commissieBedrag = ($validated['prijs'] * $commissiePercentage) / 100;
        
        // BTW berekening (standaard 21%, later uit te breiden per dienst)
        $btwPercentage = 21; // TODO: Later toevoegen aan diensten tabel (incl/excl BTW optie)
        $btwBedrag = ($validated['prijs'] * $btwPercentage) / 100;
        $nettoPrijs = $validated['prijs'] - $btwBedrag;
        
        // Haal klant naam op als klant_id is opgegeven
        $klantNaam = null;
        if (!empty($validated['klant_id'])) {
            $klant = \App\Models\Klant::find($validated['klant_id']);
            if ($klant) {
                $klantNaam = trim($klant->voornaam . ' ' . $klant->naam);
            }
        }
        
        // Maak prestatie aan
        Prestatie::create([
            'user_id' => auth()->id(),
            'dienst_id' => $validated['dienst_id'],
            'klant_id' => $validated['klant_id'] ?? null,
            'klant_naam' => $klantNaam ?? 'Geen klant',
            'datum_prestatie' => $validated['datum_prestatie'],
            'einddatum_prestatie' => $validated['einddatum_prestatie'] ?? null,
            'bruto_prijs' => $validated['prijs'],
            'btw_percentage' => $btwPercentage,
            'btw_bedrag' => $btwBedrag,
            'netto_prijs' => $nettoPrijs,
            'commissie_percentage' => $commissiePercentage,
            'commissie_bedrag' => $commissieBedrag,
            'opmerkingen' => $validated['opmerkingen'],
            'jaar' => date('Y', strtotime($validated['datum_prestatie'])),
            'kwartaal' => 'Q' . ceil(date('n', strtotime($validated['datum_prestatie'])) / 3),
        ]);
        
        return redirect()->route('prestaties.index')->with('success', 'Prestatie succesvol toegevoegd!');
    }

    /**
     * Update bestaande prestatie
     */
    public function update(Request $request, Prestatie $prestatie)
    {
        // Check of coach deze prestatie mag bewerken
        if ($prestatie->user_id !== auth()->id() && !auth()->user()->is_admin) {
            abort(403, 'Geen toegang tot deze prestatie');
        }

        $validated = $request->validate([
            'dienst_id' => 'required|exists:diensten,id',
            'klant_naam' => 'required|string|max:255',
            'omschrijving_dienst' => 'nullable|string',
            'datum_prestatie' => 'required|date',
            'bruto_prijs' => 'required|numeric|min:0',
            'is_gefactureerd' => 'boolean',
            'factuur_nummer' => 'nullable|string|max:255',
            'opmerkingen' => 'nullable|string',
        ]);

        $prestatie->update($validated);
        $prestatie->berekenBedragen();
        $prestatie->save();

        Log::info('Prestatie bijgewerkt', [
            'prestatie_id' => $prestatie->id,
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('prestaties.index')
            ->with('success', 'Prestatie succesvol bijgewerkt!');
    }

    /**
     * Verwijder prestatie
     */
    public function destroy(Prestatie $prestatie)
    {
        // Check of coach deze prestatie mag verwijderen
        if ($prestatie->user_id !== auth()->id() && !auth()->user()->is_admin) {
            abort(403, 'Geen toegang tot deze prestatie');
        }

        $prestatie->delete();

        Log::info('Prestatie verwijderd', [
            'prestatie_id' => $prestatie->id,
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('prestaties.index')
            ->with('success', 'Prestatie succesvol verwijderd!');
    }
}
