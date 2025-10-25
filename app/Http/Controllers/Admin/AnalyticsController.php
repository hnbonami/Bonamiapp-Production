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
                'commissieTrend' => $this->berekenCommissieTrend($filter, $startDatum, $eindDatum),
                'bikefitStats' => $this->berekenBikefitStats($filter, $startDatum, $eindDatum),
                'inspanningstestStats' => $this->berekenInspanningstestStats($filter, $startDatum, $eindDatum),
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
        // 🔒 VALIDATIE: Check super admin status
        $isSuperAdmin = $user->is_super_admin || in_array($user->email, ['info@bonami-sportcoaching.be', 'admin@bonami-sportcoaching.be']);
        
        \Log::info('🔍 Filter bepalen', [
            'scope' => $scope,
            'user_id' => $user->id,
            'is_super_admin' => $isSuperAdmin,
            'organisatie_id' => $user->organisatie_id,
        ]);
        
        // Als scope handmatig is ingesteld
        if ($scope !== 'auto') {
            if ($scope === 'all') {
                // ✅ ALLEEN superadmin mag alles zien
                if ($isSuperAdmin) {
                    \Log::info('✅ Super admin scope: alle organisaties');
                    return ['type' => 'superadmin', 'value' => null, 'label' => 'Alle Organisaties'];
                }
                \Log::warning('⚠️ Niet-superadmin probeerde "all" scope, terugvallen naar organisatie');
                return ['type' => 'organisatie', 'value' => $user->organisatie_id, 'label' => 'Mijn Organisatie'];
            }
            if ($scope === 'organisatie' && $user->organisatie_id) {
                \Log::info('✅ Organisatie scope geselecteerd');
                return ['type' => 'organisatie', 'value' => $user->organisatie_id, 'label' => 'Mijn Organisatie'];
            }
            if ($scope === 'medewerker') {
                \Log::info('✅ Medewerker scope geselecteerd');
                return ['type' => 'medewerker', 'value' => $user->id, 'label' => 'Alleen Ik', 'organisatie_id' => $user->organisatie_id];
            }
        }
        
        // Automatisch bepalen op basis van rol
        if ($isSuperAdmin) {
            \Log::info('✅ Auto scope: super admin → alle organisaties');
            return ['type' => 'superadmin', 'value' => null, 'label' => 'Alle Organisaties'];
        }
        
        if (in_array($user->role, ['admin', 'organisatie_admin']) && $user->organisatie_id) {
            \Log::info('✅ Auto scope: admin → organisatie');
            return ['type' => 'organisatie', 'value' => $user->organisatie_id, 'label' => 'Organisatie'];
        }
        
        // Medewerkers zien alleen hun eigen prestaties (binnen hun organisatie)
        \Log::info('✅ Auto scope: medewerker → eigen prestaties');
        return ['type' => 'medewerker', 'value' => $user->id, 'label' => 'Mijn Prestaties', 'organisatie_id' => $user->organisatie_id];
    }

    private function pasFilterToe($query, $filter)
    {
        \Log::info('🔍 Filter toepassen', [
            'filter_type' => $filter['type'],
            'filter_value' => $filter['value'] ?? null,
            'model' => get_class($query->getModel())
        ]);
        
        // 🔒 PAS ORGANISATIE FILTER TOE
        return match($filter['type']) {
            'superadmin' => $query, // ✅ Alleen voor echte superadmin - GEEN filter, alle data!
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
        
        \Log::info('📈 Omzet trend berekenen', [
            'start' => $startDatum,
            'eind' => $eindDatum,
            'aantal_prestaties' => $prestaties->count(),
            'eerste_datum' => $prestaties->first()->datum_prestatie ?? null,
            'laatste_datum' => $prestaties->last()->datum_prestatie ?? null,
        ]);
        
        if ($prestaties->isEmpty()) {
            return [
                'labels' => [],
                'bruto' => [],
                'netto' => [],
            ];
        }
        
        // Bepaal groepering op basis van periode lengte
        $start = Carbon::parse($startDatum);
        $eind = Carbon::parse($eindDatum);
        $dagenVerschil = $start->diffInDays($eind);
        
        // Groepeer per dag als periode < 14 dagen, anders per week
        if ($dagenVerschil <= 14) {
            $gegroepeerd = $prestaties->groupBy(fn($p) => Carbon::parse($p->datum_prestatie)->format('d M'));
        } else {
            $gegroepeerd = $prestaties->groupBy(fn($p) => Carbon::parse($p->datum_prestatie)->startOfWeek()->format('d M'));
        }
        
        \Log::info('✅ Omzet trend gegroepeerd', [
            'groepering' => $dagenVerschil <= 14 ? 'per dag' : 'per week',
            'aantal_groepen' => $gegroepeerd->count(),
            'labels' => $gegroepeerd->keys()->toArray(),
        ]);
        
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

    private function berekenCommissieTrend($filter, $startDatum, $eindDatum)
    {
        try {
            $prestaties = $this->pasFilterToe(Prestatie::whereBetween('datum_prestatie', [$startDatum, $eindDatum]), $filter)->get();
            
            \Log::info('💰 Commissie trend berekenen', [
                'aantal_prestaties' => $prestaties->count(),
                'filter_type' => $filter['type'] ?? 'onbekend',
            ]);
            
            if ($prestaties->isEmpty()) {
                return [
                    'labels' => [],
                    'organisatie' => [],
                    'medewerkers' => [],
                ];
            }
            
            // Bepaal groepering op basis van periode lengte
            $start = Carbon::parse($startDatum);
            $eind = Carbon::parse($eindDatum);
            $dagenVerschil = $start->diffInDays($eind);
            
            // Groepeer per dag als periode < 14 dagen, anders per week
            if ($dagenVerschil <= 14) {
                $gegroepeerd = $prestaties->groupBy(fn($p) => Carbon::parse($p->datum_prestatie)->format('d M'));
            } else {
                $gegroepeerd = $prestaties->groupBy(fn($p) => Carbon::parse($p->datum_prestatie)->startOfWeek()->format('d M'));
            }
            
            \Log::info('✅ Commissie trend gegroepeerd', [
                'aantal_groepen' => $gegroepeerd->count(),
            ]);
            
            return [
                'labels' => $gegroepeerd->keys()->toArray(),
                'organisatie' => $gegroepeerd->map(function($items) {
                    return round($items->sum(function($p) {
                        $prijsExclBtw = $p->bruto_prijs / 1.21;
                        return $prijsExclBtw * ($p->commissie_percentage / 100);
                    }), 2);
                })->values()->toArray(),
                'medewerkers' => $gegroepeerd->map(function($items) {
                    return round($items->sum(function($p) {
                        $prijsExclBtw = $p->bruto_prijs / 1.21;
                        $bonamiCommissie = $prijsExclBtw * ($p->commissie_percentage / 100);
                        return $prijsExclBtw - $bonamiCommissie;
                    }), 2);
                })->values()->toArray(),
            ];
        } catch (\Exception $e) {
            \Log::error('❌ Fout in berekenCommissieTrend', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
            ]);
            
            return [
                'labels' => [],
                'organisatie' => [],
                'medewerkers' => [],
            ];
        }
    }
    
    private function berekenBikefitStats($filter, $startDatum, $eindDatum)
    {
        try {
            // Totaal aantal bikefits
            $totaalBikefits = $this->pasFilterToe(
                \App\Models\Bikefit::whereBetween('datum', [$startDatum, $eindDatum]),
                $filter
            )->count();
            
            // Bikefits per medewerker
            $bikefitsPerMedewerker = $this->pasFilterToe(
                \App\Models\Bikefit::whereBetween('datum', [$startDatum, $eindDatum]),
                $filter
            )
            ->with('user')
            ->get()
            ->groupBy('user_id')
            ->map(function($items, $userId) {
                $user = $items->first()->user;
                return [
                    'naam' => $user ? $user->name : 'Onbekend',
                    'aantal' => $items->count()
                ];
            })
            ->sortByDesc('aantal')
            ->take(10);
            
            // Bikefits trend per week/dag
            $bikefit = $this->pasFilterToe(
                \App\Models\Bikefit::whereBetween('datum', [$startDatum, $eindDatum]),
                $filter
            )->get();
            
            $start = Carbon::parse($startDatum);
            $eind = Carbon::parse($eindDatum);
            $dagenVerschil = $start->diffInDays($eind);
            
            if ($dagenVerschil <= 14) {
                $gegroepeerd = $bikefit->groupBy(fn($b) => Carbon::parse($b->datum)->format('d M'));
            } else {
                $gegroepeerd = $bikefit->groupBy(fn($b) => Carbon::parse($b->datum)->startOfWeek()->format('d M'));
            }
            
            \Log::info('📊 Bikefit stats berekend', [
                'totaal' => $totaalBikefits,
                'per_medewerker' => $bikefitsPerMedewerker->count(),
            ]);
            
            return [
                'totaal' => $totaalBikefits,
                'perMedewerker' => [
                    'labels' => $bikefitsPerMedewerker->pluck('naam')->toArray(),
                    'values' => $bikefitsPerMedewerker->pluck('aantal')->toArray(),
                ],
                'trend' => [
                    'labels' => $gegroepeerd->keys()->toArray(),
                    'values' => $gegroepeerd->map(fn($items) => $items->count())->values()->toArray(),
                ],
            ];
        } catch (\Exception $e) {
            \Log::error('❌ Fout in berekenBikefitStats', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
            ]);
            
            return [
                'totaal' => 0,
                'perMedewerker' => ['labels' => [], 'values' => []],
                'trend' => ['labels' => [], 'values' => []],
            ];
        }
    }
    
    private function berekenInspanningstestStats($filter, $startDatum, $eindDatum)
    {
        try {
            // Totaal aantal inspanningstesten
            $totaalTesten = $this->pasFilterToe(
                \App\Models\Inspanningstest::whereBetween('datum', [$startDatum, $eindDatum]),
                $filter
            )->count();
            
            // Inspanningstesten per medewerker
            $testenPerMedewerker = $this->pasFilterToe(
                \App\Models\Inspanningstest::whereBetween('datum', [$startDatum, $eindDatum]),
                $filter
            )
            ->with('user')
            ->get()
            ->groupBy('user_id')
            ->map(function($items, $userId) {
                $user = $items->first()->user;
                return [
                    'naam' => $user ? $user->name : 'Onbekend',
                    'aantal' => $items->count()
                ];
            })
            ->sortByDesc('aantal')
            ->take(10);
            
            // Inspanningstesten trend per week/dag
            $testen = $this->pasFilterToe(
                \App\Models\Inspanningstest::whereBetween('datum', [$startDatum, $eindDatum]),
                $filter
            )->get();
            
            $start = Carbon::parse($startDatum);
            $eind = Carbon::parse($eindDatum);
            $dagenVerschil = $start->diffInDays($eind);
            
            if ($dagenVerschil <= 14) {
                $gegroepeerd = $testen->groupBy(fn($t) => Carbon::parse($t->datum)->format('d M'));
            } else {
                $gegroepeerd = $testen->groupBy(fn($t) => Carbon::parse($t->datum)->startOfWeek()->format('d M'));
            }
            
            \Log::info('🏃 Inspanningstesten stats berekend', [
                'totaal' => $totaalTesten,
                'per_medewerker' => $testenPerMedewerker->count(),
            ]);
            
            return [
                'totaal' => $totaalTesten,
                'perMedewerker' => [
                    'labels' => $testenPerMedewerker->pluck('naam')->toArray(),
                    'values' => $testenPerMedewerker->pluck('aantal')->toArray(),
                ],
                'trend' => [
                    'labels' => $gegroepeerd->keys()->toArray(),
                    'values' => $gegroepeerd->map(fn($items) => $items->count())->values()->toArray(),
                ],
            ];
        } catch (\Exception $e) {
            \Log::error('❌ Fout in berekenInspanningstestStats', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
            ]);
            
            return [
                'totaal' => 0,
                'perMedewerker' => ['labels' => [], 'values' => []],
                'trend' => ['labels' => [], 'values' => []],
            ];
        }
    }
}
