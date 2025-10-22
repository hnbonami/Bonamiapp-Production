<?php

namespace App\Http\Controllers;

use App\Models\Organisatie;
use App\Models\User;
use App\Models\Klant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrganisatieController extends Controller
{
    /**
     * Toon overzicht van alle organisaties (alleen voor superadmin)
     */
    public function index()
    {
        // Extra check: alleen superadmin heeft toegang
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Toegang geweigerd. Alleen superadmins hebben toegang.');
        }

        $organisaties = Organisatie::withCount(['users', 'klanten'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('organisaties.index', compact('organisaties'));
    }

    /**
     * Toon formulier om nieuwe organisatie aan te maken
     */
    public function create()
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        return view('organisaties.create');
    }

    /**
     * Sla nieuwe organisatie op
     */
    public function store(Request $request)
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'naam' => 'required|string|max:255',
            'email' => 'required|email|unique:organisaties,email',
            'telefoon' => 'nullable|string|max:50',
            'adres' => 'nullable|string|max:255',
            'postcode' => 'nullable|string|max:20',
            'plaats' => 'nullable|string|max:100',
            'btw_nummer' => 'nullable|string|max:50',
            'status' => 'required|in:actief,inactief,trial',
            'trial_eindigt_op' => 'nullable|date',
            'maandelijkse_prijs' => 'nullable|numeric|min:0',
            'notities' => 'nullable|string',
        ]);

        try {
            $organisatie = Organisatie::create($validated);

            Log::info('Nieuwe organisatie aangemaakt', [
                'organisatie_id' => $organisatie->id,
                'naam' => $organisatie->naam,
                'created_by' => auth()->id()
            ]);

            return redirect()->route('organisaties.index')
                ->with('success', 'Organisatie "' . $organisatie->naam . '" succesvol aangemaakt.');
        } catch (\Exception $e) {
            Log::error('Fout bij aanmaken organisatie', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Er is een fout opgetreden bij het aanmaken van de organisatie.');
        }
    }

    /**
     * Toon details van een organisatie
     */
    public function show(Organisatie $organisatie)
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        $organisatie->load(['users', 'klanten']);

        // Statistieken ophalen
        $stats = [
            'totaal_users' => $organisatie->users()->count(),
            'totaal_klanten' => $organisatie->klanten()->count(),
            'actieve_klanten' => $organisatie->klanten()->where('status', 'Actief')->count(),
            'admins' => $organisatie->users()->where('role', 'organisatie_admin')->count(),
            'medewerkers' => $organisatie->users()->where('role', 'medewerker')->count(),
        ];

        return view('organisaties.show', compact('organisatie', 'stats'));
    }

    /**
     * Toon formulier om organisatie te bewerken
     */
    public function edit(Organisatie $organisatie)
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        return view('organisaties.edit', compact('organisatie'));
    }

    /**
     * Update organisatie gegevens
     */
    public function update(Request $request, Organisatie $organisatie)
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'naam' => 'required|string|max:255',
            'email' => 'required|email|unique:organisaties,email,' . $organisatie->id,
            'telefoon' => 'nullable|string|max:50',
            'adres' => 'nullable|string|max:255',
            'postcode' => 'nullable|string|max:20',
            'plaats' => 'nullable|string|max:100',
            'btw_nummer' => 'nullable|string|max:50',
            'status' => 'required|in:actief,inactief,trial',
            'trial_eindigt_op' => 'nullable|date',
            'maandelijkse_prijs' => 'nullable|numeric|min:0',
            'notities' => 'nullable|string',
        ]);

        try {
            $organisatie->update($validated);

            Log::info('Organisatie gewijzigd', [
                'organisatie_id' => $organisatie->id,
                'naam' => $organisatie->naam,
                'updated_by' => auth()->id()
            ]);

            return redirect()->route('organisaties.show', $organisatie)
                ->with('success', 'Organisatie succesvol bijgewerkt.');
        } catch (\Exception $e) {
            Log::error('Fout bij bijwerken organisatie', [
                'organisatie_id' => $organisatie->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Er is een fout opgetreden bij het bijwerken.');
        }
    }

    /**
     * Verwijder een organisatie (soft delete indien mogelijk)
     */
    public function destroy(Organisatie $organisatie)
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        // Check of organisatie geen users of klanten heeft
        if ($organisatie->users()->count() > 0 || $organisatie->klanten()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Kan organisatie niet verwijderen: er zijn nog users of klanten gekoppeld.');
        }

        try {
            $naam = $organisatie->naam;
            $organisatie->delete();

            Log::warning('Organisatie verwijderd', [
                'organisatie_id' => $organisatie->id,
                'naam' => $naam,
                'deleted_by' => auth()->id()
            ]);

            return redirect()->route('organisaties.index')
                ->with('success', 'Organisatie "' . $naam . '" succesvol verwijderd.');
        } catch (\Exception $e) {
            Log::error('Fout bij verwijderen organisatie', [
                'organisatie_id' => $organisatie->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Er is een fout opgetreden bij het verwijderen.');
        }
    }
}
