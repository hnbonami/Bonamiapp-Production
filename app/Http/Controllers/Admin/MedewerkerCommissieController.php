<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Dienst;
use App\Models\MedewerkerCommissieFactor;
use Illuminate\Http\Request;

class MedewerkerCommissieController extends Controller
{
    /**
     * Check admin toegang voor alle commissie beheer functies
     */
    private function checkAdminAccess()
    {
        if (!in_array(auth()->user()->role, ['admin', 'organisatie_admin', 'superadmin'])) {
            abort(403, 'Geen toegang. Alleen administrators hebben toegang tot commissie beheer.');
        }
    }
    
    /**
     * Overzicht alle medewerkers met hun commissie factoren
     */
    public function index()
    {
        $this->checkAdminAccess();

        // DEBUG: Check ingelogde gebruiker
        $huidigGebruiker = auth()->user();
        
        \Log::info('🔍 DEBUG: Ingelogde gebruiker data', [
            'id' => $huidigGebruiker->id,
            'name' => $huidigGebruiker->name,
            'email' => $huidigGebruiker->email,
            'role' => $huidigGebruiker->role,
            'organisatie_id' => $huidigGebruiker->organisatie_id,
        ]);

        // 🔒 ORGANISATIE FILTER: Alleen medewerkers van eigen organisatie (GEEN klanten)
        $medewerkers = User::with(['commissieFactoren' => function($query) {
            $query->algemeen()->actief();
        }])
        ->where('organisatie_id', $huidigGebruiker->organisatie_id) // ORGANISATIE FILTER
        ->where('role', '!=', 'klant') // Alle roles BEHALVE klanten
        ->orderByRaw("FIELD(role, 'superadmin', 'super_admin', 'organisatie_admin', 'admin', 'medewerker')") // Super admin eerst
        ->orderBy('name')
        ->get();

        // DEBUG: Check alle gevonden medewerkers
        \Log::info('💼 Medewerker commissies overzicht geladen', [
            'gezochte_organisatie_id' => $huidigGebruiker->organisatie_id,
            'aantal_medewerkers' => $medewerkers->count(),
            'gevonden_medewerkers' => $medewerkers->map(function($m) {
                return [
                    'id' => $m->id,
                    'name' => $m->name,
                    'email' => $m->email,
                    'role' => $m->role,
                    'organisatie_id' => $m->organisatie_id
                ];
            })->toArray(),
            'ingelogde_gebruiker_in_lijst' => $medewerkers->contains('id', $huidigGebruiker->id) ? 'JA' : 'NEE'
        ]);

        // Check of ingelogde gebruiker in lijst staat
        if (!$medewerkers->contains('id', $huidigGebruiker->id)) {
            \Log::warning('⚠️ WAARSCHUWING: Ingelogde gebruiker staat NIET in de medewerkers lijst!', [
                'mogelijke_oorzaken' => [
                    'organisatie_id_mismatch' => $huidigGebruiker->organisatie_id,
                    'role' => $huidigGebruiker->role,
                    'is_klant' => $huidigGebruiker->role === 'klant'
                ]
            ]);
        }

        return view('admin.medewerkers.commissies.index', compact('medewerkers'));
    }

    /**
     * Bewerk commissie factoren voor een medewerker
     */
    public function edit(User $medewerker)
    {
        $this->checkAdminAccess();

        // 🔒 BEVEILIGING: Check of medewerker bij zelfde organisatie hoort
        if ($medewerker->organisatie_id !== auth()->user()->organisatie_id) {
            abort(403, 'Geen toegang tot deze medewerker');
        }

        // Haal algemene factoren op (of maak nieuwe aan)
        $algemeneFactoren = $medewerker->commissieFactoren()
            ->algemeen()
            ->actief()
            ->first();

        // Haal alle diensten op met eventuele custom commissies
        $diensten = Dienst::where('is_actief', true)
            ->where('organisatie_id', auth()->user()->organisatie_id) // ORGANISATIE FILTER
            ->orderBy('naam')
            ->get()
            ->map(function($dienst) use ($medewerker) {
                $customFactor = $medewerker->commissieFactoren()
                    ->where('dienst_id', $dienst->id)
                    ->actief()
                    ->first();

                return [
                    'dienst' => $dienst,
                    'custom_factor' => $customFactor,
                    'berekende_commissie' => $medewerker->getCommissiePercentageVoorDienst($dienst)
                ];
            });

        return view('admin.medewerkers.commissies.edit', compact('medewerker', 'algemeneFactoren', 'diensten'));
    }

    /**
     * Update algemene commissie factoren
     */
    public function update(Request $request, User $medewerker)
    {
        $this->checkAdminAccess();

        $validated = $request->validate([
            'diploma_factor' => 'required|numeric|min:0|max:100',
            'ervaring_factor' => 'required|numeric|min:0|max:100',
            'ancienniteit_factor' => 'required|numeric|min:0|max:100',
            'bonus_richting' => 'required|in:plus,min',
            'opmerking' => 'nullable|string|max:500',
        ]);

        // Update of create algemene factoren
        MedewerkerCommissieFactor::updateOrCreate(
            [
                'user_id' => $medewerker->id,
                'dienst_id' => null // algemeen
            ],
            $validated
        );

        \Log::info('✅ Commissie factoren bijgewerkt', [
            'medewerker_id' => $medewerker->id,
            'factoren' => $validated
        ]);

        return redirect()
            ->route('admin.medewerkers.commissies.edit', $medewerker)
            ->with('success', 'Commissie factoren succesvol bijgewerkt!');
    }

    /**
     * Update dienst-specifieke commissie
     */
    public function updateDienstCommissie(Request $request, User $medewerker, Dienst $dienst)
    {
        $this->checkAdminAccess();

        // 🔒 BEVEILIGING: Check of medewerker en dienst bij zelfde organisatie horen
        if ($medewerker->organisatie_id !== auth()->user()->organisatie_id) {
            abort(403, 'Geen toegang tot deze medewerker');
        }
        
        if ($dienst->organisatie_id !== auth()->user()->organisatie_id) {
            abort(403, 'Geen toegang tot deze dienst');
        }
        
        $validated = $request->validate([
            'custom_commissie_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        if ($validated['custom_commissie_percentage'] === null) {
            // Verwijder custom commissie
            $medewerker->commissieFactoren()
                ->where('dienst_id', $dienst->id)
                ->delete();

            $message = 'Custom commissie verwijderd, standaard wordt nu gebruikt.';
        } else {
            // Update of create dienst-specifieke commissie
            MedewerkerCommissieFactor::updateOrCreate(
                [
                    'user_id' => $medewerker->id,
                    'dienst_id' => $dienst->id
                ],
                [
                    'custom_commissie_percentage' => $validated['custom_commissie_percentage'],
                    'diploma_factor' => 0,
                    'ervaring_factor' => 0,
                    'ancienniteit_factor' => 0,
                ]
            );

            $message = 'Custom commissie ingesteld voor ' . $dienst->naam;
        }

        \Log::info('✅ Dienst-specifieke commissie bijgewerkt', [
            'medewerker_id' => $medewerker->id,
            'dienst_id' => $dienst->id,
            'custom_percentage' => $validated['custom_commissie_percentage']
        ]);

        return redirect()
            ->route('admin.medewerkers.commissies.edit', $medewerker)
            ->with('success', $message);
    }
}
