<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Prestatie;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    public function index()
    {
        return view('admin.dashboard.analytics');
    }

    public function getData(Request $request)
    {
        $user = auth()->user();
        $startDatum = $request->input('start', now()->subDays(30)->format('Y-m-d'));
        $eindDatum = $request->input('eind', now()->format('Y-m-d'));
        
        $filter = $this->bepaalDataFilter($user);
        
        try {
            return response()->json([
                'success' => true,
                'filter' => $filter,
                'kpis' => $this->berekenKPIs($filter, $startDatum, $eindDatum),
                'omzetTrend' => $this->berekenOmzetTrend($filter, $startDatum, $eindDatum),
                'dienstenVerdeling' => $this->berekenDienstenVerdeling($filter, $startDatum, $eindDatum),
                'klantenGroei' => $this->berekenKlantenGroei($filter, $startDatum, $eindDatum),
                'medewerkerPrestaties' => $this->berekenMedewerkerPrestaties($filter, $startDatum, $eindDatum),
                'prestatieStatus' => $this->berekenPrestatieStatus($filter, $startDatum, $eindDatum),
                'commissieVerdeling' => $this->berekenCommissieVerdeling($filter, $startDatum, $eindDatum),
                'btwOverzicht' => $this->berekenBTWOverzicht($filter, $startDatum, $eindDatum),
            ]);
        } catch (\Exception $e) {
            \Log::error('Analytics fout: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Fout bij ophalen data'], 500);
        }
    }

    private function bepaalDataFilter($user)
    {
        if ($user->role_id === 1 || $user->is_superadmin) {
            return ['type' => 'superadmin', 'value' => null, 'label' => 'Alle Organisaties'];
        }
        if ($user->role_id === 2 || $user->is_admin) {
            return ['type' => 'organisatie', 'value' => $user->organisatie_id, 'label' => 'Organisatie'];
        }
        return ['type' => 'medewerker', 'value' => $user->id, 'label' => 'Mijn Prestaties'];
    }

    private function pasFilterToe($query, $filter)
    {
        return match($filter['type']) {
            'superadmin' => $query,
            'organisatie' => $query->whereHas('user', fn($q) => $q->where('organisatie_id', $filter['value'])),
            'medewerker' => $query->where('user_id', $filter['value']),
            default => $query
        };
    }

    private function berekenKPIs($filter, $startDatum, $eindDatum)
    {
        $prestaties = $this->pasFilterToe(Prestatie::whereBetween('datum_prestatie', [$startDatum, $eindDatum]), $filter)->get();
        $brutoOmzet = $prestaties->sum('bruto_prijs');
        $nettoOmzet = $prestaties->sum(fn($p) => $p->bruto_prijs / 1.21);
        $commissie = $prestaties->sum(fn($p) => ($p->bruto_prijs / 1.21) * ($p->commissie_percentage / 100));
        
        return [
            'brutoOmzet' => $brutoOmzet,
            'nettoOmzet' => $nettoOmzet,
            'commissie' => $commissie,
            'medewerkerInkomsten' => $nettoOmzet - $commissie,
        ];
    }

    private function berekenOmzetTrend($filter, $startDatum, $eindDatum)
    {
        $prestaties = $this->pasFilterToe(Prestatie::whereBetween('datum_prestatie', [$startDatum, $eindDatum]), $filter)->get();
        $gegroepeerd = $prestaties->groupBy(fn($p) => Carbon::parse($p->datum_prestatie)->startOfWeek()->format('d M'));
        
        return [
            'labels' => $gegroepeerd->keys()->toArray(),
            'bruto' => $gegroepeerd->map(fn($items) => round($items->sum('bruto_prijs'), 2))->values()->toArray(),
            'netto' => $gegroepeerd->map(fn($items) => round($items->sum(fn($p) => $p->bruto_prijs / 1.21), 2))->values()->toArray(),
        ];
    }

    private function berekenDienstenVerdeling($filter, $startDatum, $eindDatum)
    {
        $prestaties = $this->pasFilterToe(Prestatie::whereBetween('datum_prestatie', [$startDatum, $eindDatum])->with('dienst'), $filter)->get();
        $verdeling = $prestaties->groupBy(fn($p) => $p->dienst->naam ?? 'Andere');
        
        return ['labels' => $verdeling->keys()->toArray(), 'values' => $verdeling->map->count()->values()->toArray()];
    }

    private function berekenKlantenGroei($filter, $startDatum, $eindDatum)
    {
        $prestaties = $this->pasFilterToe(Prestatie::whereBetween('datum_prestatie', [$startDatum, $eindDatum])->whereNotNull('klant_id'), $filter)->get();
        $gegroepeerd = $prestaties->groupBy(fn($p) => Carbon::parse($p->datum_prestatie)->startOfWeek()->format('d M'));
        
        return [
            'labels' => $gegroepeerd->keys()->toArray(),
            'values' => $gegroepeerd->map(fn($items) => $items->unique('klant_id')->count())->values()->toArray(),
        ];
    }

    private function berekenMedewerkerPrestaties($filter, $startDatum, $eindDatum)
    {
        if ($filter['type'] === 'medewerker') {
            return ['labels' => ['Mijn prestaties'], 'values' => [Prestatie::where('user_id', $filter['value'])->whereBetween('datum_prestatie', [$startDatum, $eindDatum])->count()]];
        }
        
        $query = User::withCount(['prestaties' => fn($q) => $q->whereBetween('datum_prestatie', [$startDatum, $eindDatum])]);
        if ($filter['type'] === 'organisatie') $query->where('organisatie_id', $filter['value']);
        $stats = $query->having('prestaties_count', '>', 0)->orderBy('prestaties_count', 'desc')->take(5)->get();
        
        return ['labels' => $stats->pluck('name')->toArray(), 'values' => $stats->pluck('prestaties_count')->toArray()];
    }

    private function berekenPrestatieStatus($filter, $startDatum, $eindDatum)
    {
        $prestaties = $this->pasFilterToe(Prestatie::whereBetween('datum_prestatie', [$startDatum, $eindDatum]), $filter)->get();
        return ['uitgevoerd' => $prestaties->where('is_uitgevoerd', true)->count(), 'nietUitgevoerd' => $prestaties->where('is_uitgevoerd', false)->count()];
    }

    private function berekenCommissieVerdeling($filter, $startDatum, $eindDatum)
    {
        $prestaties = $this->pasFilterToe(Prestatie::whereBetween('datum_prestatie', [$startDatum, $eindDatum]), $filter)->get();
        $commissie = $prestaties->sum(fn($p) => ($p->bruto_prijs / 1.21) * ($p->commissie_percentage / 100));
        $netto = $prestaties->sum(fn($p) => $p->bruto_prijs / 1.21);
        
        return ['organisatie' => round($commissie, 2), 'medewerkers' => round($netto - $commissie, 2)];
    }

    private function berekenBTWOverzicht($filter, $startDatum, $eindDatum)
    {
        $prestaties = $this->pasFilterToe(Prestatie::whereBetween('datum_prestatie', [$startDatum, $eindDatum]), $filter)->get();
        $incl = $prestaties->sum('bruto_prijs');
        $excl = $prestaties->sum(fn($p) => $p->bruto_prijs / 1.21);
        
        return ['incl' => round($incl, 2), 'excl' => round($excl, 2), 'totaal' => round($incl - $excl, 2)];
    }
}
