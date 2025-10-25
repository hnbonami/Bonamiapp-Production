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
            'dienst_id' => 'required', // Niet alleen exists:diensten,id omdat "andere" ook geldig is
            'klant_id' => 'nullable|exists:klanten,id',
            'prijs' => 'required|numeric|min:0',
            'opmerkingen' => 'nullable|string',
        ]);
        
        // Check of het "andere" dienst is
        $isAndereDienst = ($validated['dienst_id'] === 'andere');
        
        // Haal dienst op voor commissie berekening (of gebruik NULL voor "andere")
        if ($isAndereDienst) {
            $dienst = null;
            $dienstId = null;
            // Voor "andere" dienst: gebruik standaard commissie percentage van gebruiker
            $commissiePercentage = 0; // Of een standaard percentage
        } else {
            $dienst = Dienst::findOrFail($validated['dienst_id']);
            $dienstId = $dienst->id;
            
            // Haal het juiste commissie percentage op voor deze medewerker
            $user = auth()->user();
            
            // Check of er een custom commissie is ingesteld voor deze dienst
            $customFactor = $user->commissieFactoren()
                ->where('dienst_id', $dienst->id)
                ->actief()
                ->first();
            
            if ($customFactor && $customFactor->custom_commissie_percentage !== null) {
                // Gebruik custom commissie percentage
                $commissiePercentage = $customFactor->custom_commissie_percentage;
            } else {
                // Gebruik berekende commissie op basis van factoren
                $commissiePercentage = $user->getCommissiePercentageVoorDienst($dienst);
            }
        }
        
        \Log::info('💰 Commissie berekening voor nieuwe prestatie', [
            'user_id' => auth()->id(),
            'user_name' => auth()->user()->name,
            'is_andere_dienst' => $isAndereDienst,
            'dienst_id' => $dienstId,
            'dienst_naam' => $dienst ? $dienst->naam : 'Andere',
            'dienst_standaard_percentage' => $dienst ? $dienst->commissie_percentage : null,
            'berekende_commissie_percentage' => $commissiePercentage,
            'prijs' => $validated['prijs']
        ]);
        
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
            'organisatie_id' => auth()->user()->organisatie_id, // ORGANISATIE ID toevoegen
            'dienst_id' => $dienstId, // NULL als het "andere" is
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
            'is_uitgevoerd' => $request->has('is_uitgevoerd') ? true : false,
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

    /**
     * Haal prestatie data op voor edit modal (AJAX)
     */
    public function edit(Prestatie $prestatie)
    {
        // Controleer of gebruiker eigenaar is
        if ($prestatie->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Geen toegang tot deze prestatie'
            ], 403);
        }
        
        $prestatie->load('klant', 'dienst');
        
        return response()->json([
            'success' => true,
            'prestatie' => $prestatie
        ]);
    }

    /**
     * Dupliceer een prestatie
     */
    public function duplicate(Prestatie $prestatie)
    {
        try {
            // Controleer of gebruiker eigenaar is
            if ($prestatie->user_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Geen toegang tot deze prestatie'
                ], 403);
            }
            
            // Maak een kopie van de prestatie
            $nieuwePrestatie = $prestatie->replicate();
            $nieuwePrestatie->datum_prestatie = now();
            $nieuwePrestatie->is_uitgevoerd = false;
            $nieuwePrestatie->save();
            
            \Log::info('Prestatie gedupliceerd', [
                'origineel_id' => $prestatie->id,
                'nieuwe_id' => $nieuwePrestatie->id,
                'user_id' => auth()->id()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Prestatie succesvol gedupliceerd',
                'prestatie_id' => $nieuwePrestatie->id
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Prestatie dupliceren mislukt', [
                'error' => $e->getMessage(),
                'prestatie_id' => $prestatie->id
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Er ging iets mis bij het dupliceren'
            ], 500);
        }
    }

    /**
     * Toggle is_uitgevoerd status via AJAX
     */
    public function toggleUitgevoerd(Request $request, Prestatie $prestatie)
    {
        try {
            // Controleer of gebruiker eigenaar is
            if ($prestatie->user_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Geen toegang tot deze prestatie'
                ], 403);
            }
            
            $validated = $request->validate([
                'is_uitgevoerd' => 'required|boolean'
            ]);
            
            $prestatie->update([
                'is_uitgevoerd' => $validated['is_uitgevoerd']
            ]);
            
            \Log::info('Uitgevoerd status updated', [
                'prestatie_id' => $prestatie->id,
                'is_uitgevoerd' => $validated['is_uitgevoerd']
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Status bijgewerkt',
                'is_uitgevoerd' => $prestatie->is_uitgevoerd
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Toggle uitgevoerd failed', [
                'error' => $e->getMessage(),
                'prestatie_id' => $prestatie->id
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Er ging iets mis: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle factuur naar klant status van prestatie (alleen voor admins)
     */
    public function toggleFactuurNaarKlant(Request $request, Prestatie $prestatie)
    {
        // Alleen admins mogen dit wijzigen
        if (!auth()->user()->is_admin) {
            return response()->json(['success' => false, 'message' => 'Geen toegang'], 403);
        }

        $validated = $request->validate([
            'factuur_naar_klant' => 'required|boolean'
        ]);

        $prestatie->factuur_naar_klant = $validated['factuur_naar_klant'];
        $prestatie->save();

        Log::info('Factuur naar klant status gewijzigd', [
            'prestatie_id' => $prestatie->id,
            'factuur_naar_klant' => $prestatie->factuur_naar_klant,
            'user_id' => auth()->id(),
        ]);

        return response()->json(['success' => true]);
    }
}
