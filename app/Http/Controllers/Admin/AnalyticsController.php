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
        
        \Log::info('📊 Analytics getData aangeroepen', [
            'user_id' => $user->id,
            'organisatie_id' => $user->organisatie_id,
            'role' => $user->role,
            'request_params' => $request->all()
        ]);
        
        $startDatum = $request->input('start', now()->subDays(30)->format('Y-m-d'));
        $eindDatum = $request->input('eind', now()->format('Y-m-d'));
        $scope = $request->input('scope', 'auto'); // auto, organisatie, medewerker, all
        
        // Bepaal filter op basis van scope en gebruikersrol
        $filter = $this->bepaalDataFilter($user, $scope);
        
        \Log::info('🔍 Filter bepaald', ['filter' => $filter]);
        
        try {
            $result = [
                'success' => true,
                'filter' => $filter,
                'periode' => ['start' => $startDatum, 'eind' => $eindDatum],
                'kpis' => $this->berekenKPIs($filter, $startDatum, $eindDatum),
                'omzetTrend' => $this->berekenOmzetTrend($filter, $startDatum, $eindDatum),
                'dienstenVerdeling' => $this->berekenDienstenVerdeling($filter, $startDatum, $eindDatum),
                'klantenGroei' => $this->berekenKlantenGroei($filter, $startDatum, $eindDatum),
                'medewerkerPrestaties' => $this->berekenMedewerkerPrestaties($filter, $startDatum, $eindDatum),
                'prestatieStatus' => $this->berekenPrestatieStatus($filter, $startDatum, $eindDatum),
                'commissieVerdeling' => $this->berekenCommissieVerdeling($filter, $startDatum, $eindDatum),
                'btwOverzicht' => $this->berekenBTWOverzicht($filter, $startDatum, $eindDatum),
            ];
            
            \Log::info('✅ Analytics data succesvol berekend', [
                'kpi_bruto' => $result['kpis']['brutoOmzet'],
                'aantal_prestaties' => $result['prestatieStatus']['uitgevoerd'] + $result['prestatieStatus']['nietUitgevoerd']
            ]);
            
            return response()->json($result);
        } catch (\Exception $e) {
            \Log::error('❌ Analytics fout: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['success' => false, 'message' => 'Fout bij ophalen data: ' . $e->getMessage()], 500);
        }
    }

    private function bepaalDataFilter($user, $scope = 'auto')
    {
        // 🔒 ORGANISATIE FILTER: Standaard altijd organisatie-gebonden, tenzij anders
        
        // Als scope handmatig is ingesteld
        if ($scope !== 'auto') {
            if ($scope === 'all') {
                // Alleen superadmin mag alles zien
                return ['type' => 'superadmin', 'value' => null, 'label' => 'Alle Organisaties'];
            }
            if ($scope === 'organisatie' && $user->organisatie_id) {
                return ['type' => 'organisatie', 'value' => $user->organisatie_id, 'label' => 'Mijn Organisatie'];
            }
            if ($scope === 'medewerker') {
                return ['type' => 'medewerker', 'value' => $user->id, 'label' => 'Alleen Ik', 'organisatie_id' => $user->organisatie_id];
            }
        }
        
        // Automatisch bepalen op basis van rol - ALTIJD gefilterd op organisatie
        if (in_array($user->role, ['admin', 'organisatie_admin'])) {
            return ['type' => 'organisatie', 'value' => $user->organisatie_id, 'label' => 'Organisatie'];
        }
        
        // Medewerkers zien alleen hun eigen prestaties (binnen hun organisatie)
        return ['type' => 'medewerker', 'value' => $user->id, 'label' => 'Mijn Prestaties', 'organisatie_id' => $user->organisatie_id];
    }

    private function pasFilterToe($query, $filter)
    {
        // 🔒 PAS ORGANISATIE FILTER TOE
        return match($filter['type']) {
            'superadmin' => $query, // Alleen voor echte superadmin - geen filter
            'organisatie' => $query->whereHas('user', fn($q) => $q->where('organisatie_id', $filter['value'])),
            'medewerker' => $query->where('user_id', $filter['value'])
                                  ->whereHas('user', fn($q) => $q->where('organisatie_id', $filter['organisatie_id'] ?? null)),
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
