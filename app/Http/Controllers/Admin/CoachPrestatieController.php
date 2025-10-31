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
     * Check admin toegang voor alle prestaties beheer functies
     */
    private function checkAdminAccess()
    {
        if (!in_array(auth()->user()->role, ['admin', 'organisatie_admin', 'superadmin'])) {
            abort(403, 'Geen toegang. Alleen administrators hebben toegang tot prestaties beheer.');
        }
    }

    /**
     * Toon overzicht van alle coaches en hun diensten
     */
    public function index()
    {
        $this->checkAdminAccess();

        $userOrganisatieId = auth()->user()->organisatie_id;
        
        // Haal alle medewerkers op (coaches) van dezelfde organisatie
        $coaches = User::where('role', 'medewerker')
            ->where('organisatie_id', $userOrganisatieId)
            ->with(['diensten'])
            ->orderBy('name')
            ->get();

        $alleDiensten = Dienst::where('organisatie_id', $userOrganisatieId)
            ->actief()
            ->orderBy('sorteer_volgorde')
            ->get();

        return view('admin.prestaties.coaches', compact('coaches', 'alleDiensten'));
    }

    /**
     * Configureer welke diensten een coach kan uitvoeren
     */
    public function configure(User $user)
    {
        $this->checkAdminAccess();

        // Check of de user bij dezelfde organisatie hoort
        if ($user->organisatie_id !== auth()->user()->organisatie_id) {
            abort(403, 'Geen toegang tot deze coach.');
        }
        
        $alleDiensten = Dienst::where('organisatie_id', auth()->user()->organisatie_id)
            ->actief()
            ->orderBy('sorteer_volgorde')
            ->get();
        $coachDiensten = $user->diensten()->get();

        return view('admin.prestaties.coach-configure', compact('user', 'alleDiensten', 'coachDiensten'));
    }

    /**
     * Update diensten voor een coach
     */
    public function updateDiensten(Request $request, User $user)
    {
        $this->checkAdminAccess();

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
        $this->checkAdminAccess();

        $userOrganisatieId = auth()->user()->organisatie_id;
        
        // Haal jaar en kwartaal uit request, of gebruik huidige waarden
        $huidigJaar = $request->input('jaar', now()->year);
        $huidigKwartaal = $request->input('kwartaal', $this->getHuidigKwartaal());
        
        // Bereken start en eind datum voor het kwartaal
        [$startDatum, $eindDatum] = $this->getKwartaalDatums($huidigJaar, $huidigKwartaal);
        
        // Haal stats per medewerker op - met raw aggregaties die compatibel zijn met collectie
        $statsData = \DB::table('users')
            ->join('prestaties', 'users.id', '=', 'prestaties.user_id')
            ->where('users.organisatie_id', $userOrganisatieId)
            ->whereBetween('prestaties.datum_prestatie', [$startDatum, $eindDatum])
            ->whereNull('prestaties.deleted_at')
            ->groupBy('users.id', 'users.name', 'users.email')
            ->select(
                'users.id',
                'users.name',
                'users.email',
                \DB::raw('COUNT(prestaties.id) as aantal_prestaties'),
                \DB::raw('SUM(prestaties.commissie_bedrag) as totale_commissie'),
                \DB::raw('AVG(prestaties.commissie_bedrag) as gemiddelde_commissie')
            )
            ->get();
        
        // Map naar User models met stats eigenschappen
        $medewerkerStats = $statsData->map(function ($stat) {
            $user = User::find($stat->id);
            $user->aantal_prestaties = (int) $stat->aantal_prestaties;
            $user->totale_commissie = (float) $stat->totale_commissie;
            $user->gemiddelde_commissie = (float) $stat->gemiddelde_commissie;
            return $user;
        });
        
        // Bereken totalen
        $totaalPrestaties = Prestatie::whereBetween('datum_prestatie', [$startDatum, $eindDatum])->count();
        $totaleCommissie = Prestatie::whereBetween('datum_prestatie', [$startDatum, $eindDatum])->sum('commissie_bedrag');
        
        return view('admin.prestaties.overzicht', compact(
            'medewerkerStats',
            'totaalPrestaties',
            'totaleCommissie',
            'huidigJaar',
            'huidigKwartaal'
        ));
    }

    /**
     * Admin overzicht van alle prestaties per medewerker
     */
    public function adminOverzicht(Request $request)
    {
        $this->checkAdminAccess();

        $userOrganisatieId = auth()->user()->organisatie_id;
        
        // Haal jaar en kwartaal uit request, of gebruik huidige waarden
        $huidigJaar = $request->input('jaar', now()->year);
        $huidigKwartaal = $request->input('kwartaal', $this->getHuidigKwartaal());
        
        // Bereken start en eind datum voor het kwartaal
        [$startDatum, $eindDatum] = $this->getKwartaalDatums($huidigJaar, $huidigKwartaal);
        
        // Haal stats per medewerker op - alleen van deze organisatie
        $medewerkerStats = User::select('users.id', 'users.name', 'users.email')
            ->where('users.organisatie_id', $userOrganisatieId)
            ->join('prestaties', 'users.id', '=', 'prestaties.user_id')
            ->whereBetween('prestaties.datum_prestatie', [$startDatum, $eindDatum])
            ->groupBy('users.id', 'users.name', 'users.email')
            ->selectRaw('
                COUNT(prestaties.id) as aantal_prestaties,
                SUM(prestaties.commissie_bedrag) as totale_commissie,
                AVG(prestaties.commissie_bedrag) as gemiddelde_commissie
            ')
            ->get();
        
        // Bereken totalen - alleen voor deze organisatie
        $totaalPrestaties = Prestatie::whereHas('user', function($query) use ($userOrganisatieId) {
                $query->where('organisatie_id', $userOrganisatieId);
            })
            ->whereBetween('datum_prestatie', [$startDatum, $eindDatum])
            ->count();
            
        $totaleCommissie = Prestatie::whereHas('user', function($query) use ($userOrganisatieId) {
                $query->where('organisatie_id', $userOrganisatieId);
            })
            ->whereBetween('datum_prestatie', [$startDatum, $eindDatum])
            ->sum('commissie_bedrag');
        
        return view('admin.prestaties.overzicht', compact(
            'medewerkerStats',
            'totaalPrestaties',
            'totaleCommissie',
            'huidigJaar',
            'huidigKwartaal'
        ));
    }
    
    /**
     * Detail view voor een specifieke coach
     */
    public function coachDetail(Request $request, User $user)
    {
        $this->checkAdminAccess();

        // Check of de user bij dezelfde organisatie hoort
        if ($user->organisatie_id !== auth()->user()->organisatie_id) {
            abort(403, 'Geen toegang tot deze coach.');
        }
        
        // Haal jaar en kwartaal uit request
        $huidigJaar = $request->input('jaar', now()->year);
        $huidigKwartaal = $request->input('kwartaal', $this->getHuidigKwartaal());
        
        // Bereken start en eind datum voor het kwartaal
        [$startDatum, $eindDatum] = $this->getKwartaalDatums($huidigJaar, $huidigKwartaal);
        
        // Haal prestaties op voor deze coach in deze periode
        $prestaties = Prestatie::where('user_id', $user->id)
            ->whereBetween('datum_prestatie', [$startDatum, $eindDatum])
            ->with(['dienst', 'klant'])
            ->orderBy('datum_prestatie', 'desc')
            ->get();
        
        // Bereken stats
        $aantalPrestaties = $prestaties->count();
        $totaleCommissie = $prestaties->sum('commissie_bedrag');
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
     * Toggle factuur naar klant status via AJAX
     */
    public function toggleFactuur(Request $request, Prestatie $prestatie)
    {
        try {
            // Check of de prestatie bij de juiste organisatie hoort
            if ($prestatie->user->organisatie_id !== auth()->user()->organisatie_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Geen toegang tot deze prestatie.'
                ], 403);
            }
            
            $validated = $request->validate([
                'factuur_naar_klant' => 'required|boolean'
            ]);
            
            $prestatie->update([
                'factuur_naar_klant' => $validated['factuur_naar_klant']
            ]);
            
            \Log::info('Factuur status updated', [
                'prestatie_id' => $prestatie->id,
                'factuur_naar_klant' => $validated['factuur_naar_klant']
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Factuur status bijgewerkt',
                'factuur_naar_klant' => $prestatie->factuur_naar_klant
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Toggle factuur failed', [
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
