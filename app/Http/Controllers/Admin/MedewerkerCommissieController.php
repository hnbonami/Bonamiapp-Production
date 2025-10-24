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
     * Overzicht alle medewerkers met hun commissie factoren
     */
    public function index()
    {
        // 🔒 ORGANISATIE FILTER: Alleen medewerkers van eigen organisatie
        $medewerkers = User::with(['commissieFactoren' => function($query) {
            $query->algemeen()->actief();
        }])
        ->where('organisatie_id', auth()->user()->organisatie_id) // ORGANISATIE FILTER
        ->whereIn('role', ['medewerker', 'admin', 'super_admin'])
        ->orderBy('name')
        ->get();

        \Log::info('💼 Medewerker commissies overzicht geladen MET organisatie filter', [
            'organisatie_id' => auth()->user()->organisatie_id,
            'aantal_medewerkers' => $medewerkers->count(),
            'rollen' => $medewerkers->pluck('role', 'name')->toArray()
        ]);

        return view('admin.medewerkers.commissies.index', compact('medewerkers'));
    }

    /**
     * Bewerk commissie factoren voor een medewerker
     */
    public function edit(User $medewerker)
    {
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
