<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Dienst;
use App\Models\Prestatie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CoachPrestatieController extends Controller
{
    /**
     * Toon overzicht van alle coaches en hun diensten
     */
    public function index()
    {
        // Haal alle medewerkers op (coaches)
        $coaches = User::where('role', 'medewerker')
            ->with(['diensten'])
            ->orderBy('name')
            ->get();

        $alleDiensten = Dienst::actief()->orderBy('sorteer_volgorde')->get();

        return view('admin.prestaties.coaches', compact('coaches', 'alleDiensten'));
    }

    /**
     * Configureer welke diensten een coach kan uitvoeren
     */
    public function configure(User $user)
    {
        $alleDiensten = Dienst::actief()->orderBy('sorteer_volgorde')->get();
        $coachDiensten = $user->diensten()->get();

        return view('admin.prestaties.coach-configure', compact('user', 'alleDiensten', 'coachDiensten'));
    }

    /**
     * Update diensten voor een coach
     */
    public function updateDiensten(Request $request, User $user)
    {
        $validated = $request->validate([
            'diensten' => 'required|array',
            'diensten.*.dienst_id' => 'required|exists:diensten,id',
            'diensten.*.commissie_percentage' => 'required|numeric|min:0|max:100',
            'diensten.*.custom_prijs' => 'nullable|numeric|min:0',
            'diensten.*.is_actief' => 'boolean',
        ]);

        // Verwijder alle huidige diensten
        $user->diensten()->detach();

        // Voeg nieuwe diensten toe
        foreach ($validated['diensten'] as $dienstData) {
            $user->diensten()->attach($dienstData['dienst_id'], [
                'commissie_percentage' => $dienstData['commissie_percentage'],
                'custom_prijs' => $dienstData['custom_prijs'] ?? null,
                'is_actief' => $dienstData['is_actief'] ?? true,
            ]);
        }

        Log::info('Coach diensten bijgewerkt', [
            'coach_id' => $user->id,
            'coach_name' => $user->name,
            'admin_id' => auth()->id(),
        ]);

        return redirect()->route('admin.prestaties.coaches.index')
            ->with('success', 'Diensten voor ' . $user->name . ' succesvol bijgewerkt!');
    }

    /**
     * Toon overzicht van alle prestaties (per coach)
     */
    public function overzicht(Request $request)
    {
        $jaar = $request->get('jaar', now()->year);
        $kwartaal = $request->get('kwartaal');

        $query = Prestatie::with(['user', 'dienst']);

        if ($jaar) {
            $query->where('jaar', $jaar);
        }

        if ($kwartaal) {
            $query->where('kwartaal', $kwartaal);
        }

        $prestaties = $query->orderBy('datum_prestatie', 'desc')->get();

        // Groepeer per coach
        $prestatiesPerCoach = $prestaties->groupBy('user_id')->map(function($groep) {
            return [
                'coach' => $groep->first()->user,
                'totaal_bruto' => $groep->sum('bruto_prijs'),
                'totaal_btw' => $groep->sum('btw_bedrag'),
                'totaal_netto' => $groep->sum('netto_prijs'),
                'totaal_commissie' => $groep->sum('commissie_bedrag'),
                'aantal' => $groep->count(),
            ];
        });

        return view('admin.prestaties.overzicht', compact('prestatiesPerCoach', 'jaar', 'kwartaal'));
    }

    /**
     * Detail van specifieke coach prestaties
     */
    public function coachDetail(Request $request, User $user)
    {
        $jaar = $request->get('jaar', now()->year);
        $kwartaal = $request->get('kwartaal');

        $query = Prestatie::where('user_id', $user->id)->with(['dienst', 'klant']);

        if ($jaar) {
            $query->where('jaar', $jaar);
        }

        if ($kwartaal) {
            $query->where('kwartaal', $kwartaal);
        }

        $prestaties = $query->orderBy('datum_prestatie', 'desc')->get();

        $totalen = [
            'bruto' => $prestaties->sum('bruto_prijs'),
            'btw' => $prestaties->sum('btw_bedrag'),
            'netto' => $prestaties->sum('netto_prijs'),
            'commissie' => $prestaties->sum('commissie_bedrag'),
        ];

        return view('admin.prestaties.coach-detail', compact('user', 'prestaties', 'totalen', 'jaar', 'kwartaal'));
    }
}
