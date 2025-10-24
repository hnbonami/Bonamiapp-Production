<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Dienst;
use App\Models\Prestatie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

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
     * Admin overzicht van alle prestaties per medewerker
     */
    public function adminOverzicht(Request $request)
    {
        // Haal jaar en kwartaal uit request, of gebruik huidige waarden
        $huidigJaar = $request->input('jaar', now()->year);
        $huidigKwartaal = $request->input('kwartaal', $this->getHuidigKwartaal());
        
        // Bereken start en eind datum voor het kwartaal
        [$startDatum, $eindDatum] = $this->getKwartaalDatums($huidigJaar, $huidigKwartaal);
        
        // Haal stats per medewerker op
        $medewerkerStats = User::select('users.id', 'users.name', 'users.email')
            ->join('prestaties', 'users.id', '=', 'prestaties.user_id')
            ->whereBetween('prestaties.startdatum', [$startDatum, $eindDatum])
            ->groupBy('users.id', 'users.name', 'users.email')
            ->selectRaw('
                COUNT(prestaties.id) as aantal_prestaties,
                SUM(prestaties.commissie) as totale_commissie,
                AVG(prestaties.commissie) as gemiddelde_commissie
            ')
            ->get();
        
        // Bereken totalen
        $totaalPrestaties = Prestatie::whereBetween('startdatum', [$startDatum, $eindDatum])->count();
        $totaleCommissie = Prestatie::whereBetween('startdatum', [$startDatum, $eindDatum])->sum('commissie');
        
        return view('admin.prestaties.overzicht', compact(
            'medewerkerStats',
            'huidigJaar',
            'huidigKwartaal',
            'totaalPrestaties',
            'totaleCommissie'
        ));
    }
    
    /**
     * Detail view voor een specifieke coach
     */
    public function coachDetail(Request $request, User $user)
    {
        // Haal jaar en kwartaal uit request
        $huidigJaar = $request->input('jaar', now()->year);
        $huidigKwartaal = $request->input('kwartaal', $this->getHuidigKwartaal());
        
        // Bereken start en eind datum voor het kwartaal
        [$startDatum, $eindDatum] = $this->getKwartaalDatums($huidigJaar, $huidigKwartaal);
        
        // Haal prestaties op voor deze coach in deze periode
        $prestaties = Prestatie::where('user_id', $user->id)
            ->whereBetween('startdatum', [$startDatum, $eindDatum])
            ->with(['dienst', 'klant'])
            ->orderBy('startdatum', 'desc')
            ->get();
        
        // Bereken stats
        $aantalPrestaties = $prestaties->count();
        $totaleCommissie = $prestaties->sum('commissie');
        $gemiddeldeCommissie = $aantalPrestaties > 0 ? $totaleCommissie / $aantalPrestaties : 0;
        
        return view('admin.prestaties.coach-detail', compact(
            'user',
            'prestaties',
            'huidigJaar',
            'huidigKwartaal',
            'aantalPrestaties',
            'totaleCommissie',
            'gemiddeldeCommissie'
        ));
    }
    
    /**
     * Helper: Bepaal huidig kwartaal
     */
    private function getHuidigKwartaal()
    {
        $maand = now()->month;
        
        if ($maand <= 3) return 'Q1';
        if ($maand <= 6) return 'Q2';
        if ($maand <= 9) return 'Q3';
        return 'Q4';
    }
    
    /**
     * Helper: Bereken start en eind datum voor een kwartaal
     */
    private function getKwartaalDatums($jaar, $kwartaal)
    {
        $kwartaalMapping = [
            'Q1' => ['start' => 1, 'eind' => 3],
            'Q2' => ['start' => 4, 'eind' => 6],
            'Q3' => ['start' => 7, 'eind' => 9],
            'Q4' => ['start' => 10, 'eind' => 12],
        ];
        
        $maanden = $kwartaalMapping[$kwartaal];
        
        $startDatum = Carbon::createFromDate($jaar, $maanden['start'], 1)->startOfMonth();
        $eindDatum = Carbon::createFromDate($jaar, $maanden['eind'], 1)->endOfMonth();
        
        return [$startDatum, $eindDatum];
    }
}
