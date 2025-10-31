<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dienst;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DienstenController extends Controller
{
    /**
     * Check admin toegang voor alle diensten beheer functies
     */
    private function checkAdminAccess()
    {
        if (!in_array(auth()->user()->role, ['admin', 'organisatie_admin', 'superadmin'])) {
            abort(403, 'Geen toegang. Alleen administrators hebben toegang tot diensten beheer.');
        }
    }

    /**
     * Toon overzicht van alle diensten
     */
    public function index()
    {
        $this->checkAdminAccess();
        // 🔒 ORGANISATIE FILTER: Alleen diensten van eigen organisatie
        $diensten = Dienst::where('organisatie_id', auth()->user()->organisatie_id)
            ->orderBy('naam')
            ->get();

        return view('admin.prestaties.diensten', compact('diensten'));
    }

    /**
     * Toon formulier voor het aanmaken van een nieuwe dienst
     */
    public function create()
    {
        $this->checkAdminAccess();
        return view('admin.prestaties.diensten.create');
    }

    /**
     * Sla nieuwe dienst op
     */
    public function store(Request $request)
    {
        $this->checkAdminAccess();
        try {
            \Log::info('💾 STORE DIENST START', [
                'all_input' => $request->all(),
                'user_id' => auth()->id(),
                'organisatie_id' => auth()->user()->organisatie_id
            ]);
            
            // Valideer input
            $validated = $request->validate([
                'naam' => 'required|string|max:255',
                'omschrijving' => 'nullable|string',
                'prijs' => 'required|numeric|min:0',
                'commissie_percentage' => 'required|numeric|min:0|max:100',
            ]);

            \Log::info('✅ Validatie geslaagd', ['validated' => $validated]);

            // BTW berekeningen (standaard 21%)
            $btwPercentage = 21;
            $prijsInclBtw = $validated['prijs'];
            $prijsExclBtw = round($prijsInclBtw / (1 + ($btwPercentage / 100)), 2);
            
            // Commissie wordt berekend over prijs EXCL. BTW
            $commissieBedrag = round($prijsExclBtw * ($validated['commissie_percentage'] / 100), 2);
            $nettoBedrag = round($prijsExclBtw - $commissieBedrag, 2);

            // Bereid data voor
            $data = [
                'naam' => $validated['naam'],
                'omschrijving' => $validated['omschrijving'] ?? null,
                'standaard_prijs' => round($prijsInclBtw, 2),
                'btw_percentage' => $btwPercentage,
                'commissie_percentage' => $validated['commissie_percentage'],
                'prijs_incl_btw' => round($prijsInclBtw, 2),
                'prijs_excl_btw' => $prijsExclBtw,
                'is_actief' => $request->has('actief') ? true : false,
                'organisatie_id' => auth()->user()->organisatie_id,
            ];

            \Log::info('📦 Data klaar voor create', [
                'data' => $data,
                'prijs_excl_btw' => $prijsExclBtw,
                'commissie_bedrag' => $commissieBedrag,
                'netto_bedrag' => $nettoBedrag
            ]);

            // Maak dienst aan
            $dienst = Dienst::create($data);

            // Maak dienst aan
            $dienst = Dienst::create($data);

            \Log::info('✅ Dienst aangemaakt', [
                'dienst_id' => $dienst->id,
                'naam' => $dienst->naam,
                'user_id' => auth()->id(),
            ]);

            return redirect()->route('admin.prestaties.diensten.index')
                ->with('success', 'Dienst succesvol aangemaakt!');
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('❌ VALIDATIE FOUT', ['errors' => $e->errors()]);
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            \Log::error('❌ STORE DIENST FAILED', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withErrors(['error' => 'Fout bij opslaan: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Toon details van een dienst
     */
    public function show(Dienst $dienst)
    {
        // 🔒 Check organisatie
        if ($dienst->organisatie_id !== auth()->user()->organisatie_id) {
            abort(403);
        }

        return response()->json($dienst);
    }

    /**
     * Toon formulier voor het bewerken van een dienst
     */
    public function edit(Dienst $dienst)
    {
        $this->checkAdminAccess();
        // 🔒 Check organisatie
        if ($dienst->organisatie_id !== auth()->user()->organisatie_id) {
            abort(403);
        }

        return view('admin.prestaties.diensten.edit', compact('dienst'));
    }

    /**
     * Update bestaande dienst
     */
    public function update(Request $request, Dienst $dienst)
    {
        $this->checkAdminAccess();
        \Log::info('✅ Update method START', [
            'dienst_id' => $dienst->id,
            'request_all' => $request->all()
        ]);

        // 🔒 Check organisatie
        if ($dienst->organisatie_id !== auth()->user()->organisatie_id) {
            \Log::error('❌ Organisatie mismatch');
            abort(403);
        }

        try {
            $validated = $request->validate([
                'naam' => 'required|string|max:255',
                'omschrijving' => 'nullable|string',
                'prijs' => 'required|numeric|min:0',
                'commissie_percentage' => 'required|numeric|min:0|max:100',
            ]);

            \Log::info('✅ Validation passed', ['validated' => $validated]);

            // BTW berekeningen
            $btwPercentage = 21;
            $prijsInclBtw = $validated['prijs'];
            $prijsExclBtw = round($prijsInclBtw / (1 + ($btwPercentage / 100)), 2);
            
            // Commissie wordt berekend over prijs EXCL. BTW
            $commissieBedrag = round($prijsExclBtw * ($validated['commissie_percentage'] / 100), 2);
            $nettoBedrag = round($prijsExclBtw - $commissieBedrag, 2);

            $dienst->naam = $validated['naam'];
            $dienst->omschrijving = $validated['omschrijving'] ?? null;
            $dienst->standaard_prijs = round($prijsInclBtw, 2);
            $dienst->btw_percentage = $btwPercentage;
            $dienst->commissie_percentage = $validated['commissie_percentage'];
            $dienst->prijs_incl_btw = round($prijsInclBtw, 2);
            $dienst->prijs_excl_btw = $prijsExclBtw;
            $dienst->is_actief = $request->has('actief') ? 1 : 0;
            
            $dienst->save();

            \Log::info('✅ Dienst saved successfully', [
                'prijs_excl_btw' => $prijsExclBtw,
                'commissie_bedrag' => $commissieBedrag,
                'netto_bedrag' => $nettoBedrag
            ]);

            return redirect()->route('admin.prestaties.diensten.index')
                ->with('success', 'Dienst succesvol bijgewerkt!');
                
        } catch (\Exception $e) {
            \Log::error('❌ Update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withErrors(['error' => 'Fout bij opslaan: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Verwijder dienst
     */
    public function destroy(Dienst $dienst)
    {
        $this->checkAdminAccess();
        // 🔒 Check organisatie
        if ($dienst->organisatie_id !== auth()->user()->organisatie_id) {
            abort(403);
        }

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
