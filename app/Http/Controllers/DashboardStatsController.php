<?php

namespace App\Http\Controllers;

use App\Models\Klant;
use App\Models\Prestatie;
use App\Models\Bikefit;
use App\Models\Inspanningstest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardStatsController extends Controller
{
    /**
     * Get live statistics voor dashboard widgets
     */
    public function getLiveStats()
    {
        $user = auth()->user();
        
        // Basis statistieken
        $stats = [
            'total_klanten' => Klant::count(),
            'actieve_klanten' => Klant::where('status', 'Actief')->count(),
            'nieuwe_klanten_vandaag' => Klant::whereDate('created_at', today())->count(),
            'nieuwe_klanten_deze_week' => Klant::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'nieuwe_klanten_deze_maand' => Klant::whereMonth('created_at', now()->month)->count(),
        ];

        // Bikefit statistieken (alleen voor medewerkers en hoger)
        if (in_array($user->role, ['medewerker', 'admin', 'super_admin'])) {
            $stats['total_bikefits'] = Bikefit::count();
            $stats['bikefits_deze_maand'] = Bikefit::whereMonth('datum', now()->month)->count();
            $stats['bikefits_vandaag'] = Bikefit::whereDate('datum', today())->count();
            $stats['laatste_bikefit'] = Bikefit::with('klant')->latest('datum')->first();
        }

        // Inspanningstests statistieken
        if (in_array($user->role, ['medewerker', 'admin', 'super_admin'])) {
            $stats['total_inspanningstests'] = Inspanningstest::count();
            $stats['tests_deze_maand'] = Inspanningstest::whereMonth('datum', now()->month)->count();
        }

        // Medewerkers statistieken (alleen voor admin en hoger)
        if (in_array($user->role, ['admin', 'super_admin'])) {
            $stats['total_medewerkers'] = User::where('role', 'medewerker')->count();
            $stats['actieve_medewerkers'] = User::where('role', 'medewerker')->where('status', 'Actief')->count();
        }

        // Chart data voor trends
        $stats['klanten_per_maand'] = $this->getKlantenPerMaand();
        $stats['bikefits_per_maand'] = $this->getBikefitsPerMaand();
        $stats['status_verdeling'] = $this->getStatusVerdeling();

        // Recente activiteit
        $stats['recent_activity'] = $this->getRecentActivity();

        return response()->json($stats);
    }

    /**
     * Haal klanten per maand op voor grafiek
     */
    private function getKlantenPerMaand()
    {
        $data = Klant::select(
            DB::raw('MONTH(created_at) as maand'),
            DB::raw('COUNT(*) as aantal')
        )
        ->whereYear('created_at', now()->year)
        ->groupBy('maand')
        ->orderBy('maand')
        ->get();

        $maanden = ['Jan', 'Feb', 'Mrt', 'Apr', 'Mei', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dec'];
        $result = array_fill(0, 12, 0);

        foreach ($data as $item) {
            $result[$item->maand - 1] = $item->aantal;
        }

        return [
            'labels' => $maanden,
            'data' => $result
        ];
    }

    /**
     * Haal bikefits per maand op
     */
    private function getBikefitsPerMaand()
    {
        if (!in_array(auth()->user()->role, ['medewerker', 'admin', 'super_admin'])) {
            return ['labels' => [], 'data' => []];
        }

        $data = Bikefit::select(
            DB::raw('MONTH(datum) as maand'),
            DB::raw('COUNT(*) as aantal')
        )
        ->whereYear('datum', now()->year)
        ->groupBy('maand')
        ->orderBy('maand')
        ->get();

        $maanden = ['Jan', 'Feb', 'Mrt', 'Apr', 'Mei', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dec'];
        $result = array_fill(0, 12, 0);

        foreach ($data as $item) {
            $result[$item->maand - 1] = $item->aantal;
        }

        return [
            'labels' => $maanden,
            'data' => $result
        ];
    }

    /**
     * Status verdeling voor pie chart
     */
    private function getStatusVerdeling()
    {
        $data = Klant::select('status', DB::raw('COUNT(*) as aantal'))
            ->groupBy('status')
            ->get();

        return [
            'labels' => $data->pluck('status')->toArray(),
            'data' => $data->pluck('aantal')->toArray()
        ];
    }

    /**
     * Recente activiteit
     */
    private function getRecentActivity()
    {
        $activities = [];

        // Laatste klanten
        $recenteKlanten = Klant::latest()->take(5)->get();
        foreach ($recenteKlanten as $klant) {
            $activities[] = [
                'type' => 'klant',
                'message' => "Nieuwe klant: {$klant->voornaam} {$klant->naam}",
                'time' => $klant->created_at->diffForHumans(),
                'url' => route('klanten.show', $klant)
            ];
        }

        // Laatste bikefits (alleen voor medewerkers)
        if (in_array(auth()->user()->role, ['medewerker', 'admin', 'super_admin'])) {
            $recenteBikefits = Bikefit::with('klant')->latest('datum')->take(3)->get();
            foreach ($recenteBikefits as $bikefit) {
                $activities[] = [
                    'type' => 'bikefit',
                    'message' => "Bikefit voor {$bikefit->klant->naam}",
                    'time' => $bikefit->datum->diffForHumans(),
                    'url' => route('klanten.show', $bikefit->klant_id)
                ];
            }
        }

        // Sorteer op tijd
        usort($activities, function($a, $b) {
            return strcmp($b['time'], $a['time']);
        });

        return array_slice($activities, 0, 8);
    }

    /**
     * Get data voor specifieke widget type
     */
    public function getWidgetData(Request $request)
    {
        $type = $request->input('type');
        
        switch ($type) {
            case 'klanten_trend':
                return response()->json($this->getKlantenPerMaand());
            
            case 'bikefits_trend':
                return response()->json($this->getBikefitsPerMaand());
            
            case 'status_pie':
                return response()->json($this->getStatusVerdeling());
            
            case 'recent':
                return response()->json($this->getRecentActivity());
            
            default:
                return response()->json(['error' => 'Unknown widget type'], 400);
        }
    }
    
    /**
     * Haal beschikbare metrics op basis van user rol
     */
    public function getAvailableMetrics()
    {
        $user = auth()->user();
        
        // Basis metrics die iedereen kan zien
        $metrics = [
            'custom' => [
                'label' => 'Custom Waarde',
                'description' => 'Typ je eigen waarde in',
                'type' => 'manual'
            ]
        ];
        
        // Metrics voor medewerkers en admins
        if ($user->isMedewerker() || $user->isBeheerder()) {
            $metrics = array_merge($metrics, [
                'mijn_bikefits' => [
                    'label' => 'Mijn Bikefits',
                    'description' => 'Aantal bikefits door jou uitgevoerd',
                    'type' => 'auto',
                    'icon' => '🚴'
                ],
                'mijn_inspanningstests' => [
                    'label' => 'Mijn Inspanningstests',
                    'description' => 'Aantal inspanningstests door jou uitgevoerd',
                    'type' => 'auto',
                    'icon' => '💪'
                ],
                'mijn_klanten' => [
                    'label' => 'Mijn Klanten',
                    'description' => 'Aantal klanten toegewezen aan jou',
                    'type' => 'auto',
                    'icon' => '👥'
                ],
                'mijn_omzet_maand' => [
                    'label' => 'Mijn Omzet (Deze Maand)',
                    'description' => 'Jouw omzet deze maand uit prestaties',
                    'type' => 'auto',
                    'icon' => '💰'
                ],
                'mijn_omzet_kwartaal' => [
                    'label' => 'Mijn Omzet (Dit Kwartaal)',
                    'description' => 'Jouw omzet dit kwartaal uit prestaties',
                    'type' => 'auto',
                    'icon' => '📊'
                ]
            ]);
        }
        
        // Extra metrics voor admins
        if ($user->isBeheerder()) {
            $metrics = array_merge($metrics, [
                'totaal_klanten' => [
                    'label' => 'Totaal Klanten',
                    'description' => 'Alle klanten in organisatie',
                    'type' => 'auto',
                    'icon' => '👥'
                ],
                'totaal_bikefits' => [
                    'label' => 'Totaal Bikefits',
                    'description' => 'Alle bikefits in organisatie',
                    'type' => 'auto',
                    'icon' => '🚴'
                ],
                'nieuwe_klanten_maand' => [
                    'label' => 'Nieuwe Klanten (Deze Maand)',
                    'description' => 'Klanten toegevoegd deze maand',
                    'type' => 'auto',
                    'icon' => '✨'
                ],
                'omzet_organisatie_maand' => [
                    'label' => 'Organisatie Omzet (Deze Maand)',
                    'description' => 'Totale omzet organisatie deze maand',
                    'type' => 'auto',
                    'icon' => '💰'
                ],
                'omzet_organisatie_kwartaal' => [
                    'label' => 'Organisatie Omzet (Dit Kwartaal)',
                    'description' => 'Totale omzet organisatie dit kwartaal',
                    'type' => 'auto',
                    'icon' => '📈'
                ],
                'actieve_medewerkers' => [
                    'label' => 'Actieve Medewerkers',
                    'description' => 'Aantal actieve medewerkers',
                    'type' => 'auto',
                    'icon' => '👨‍💼'
                ]
            ]);
        }
        
        return response()->json($metrics);
    }
    
    /**
     * Bereken waarde voor een specifieke metric
     */
    public function calculateMetric(Request $request)
    {
        $metricType = $request->input('metric_type');
        $user = auth()->user();
        
        // Security check: Medewerkers mogen alleen hun eigen metrics ophalen
        $adminOnlyMetrics = [
            'totaal_klanten',
            'totaal_bikefits',
            'nieuwe_klanten_maand',
            'omzet_organisatie_maand',
            'omzet_organisatie_kwartaal',
            'actieve_medewerkers'
        ];
        
        if (in_array($metricType, $adminOnlyMetrics) && !$user->isBeheerder()) {
            return response()->json([
                'error' => 'Geen toegang tot deze metric',
                'value' => 0,
                'prefix' => '',
                'suffix' => '',
                'formatted' => 'Geen toegang'
            ], 403);
        }
        
        $value = 0;
        $prefix = '';
        $suffix = '';
        
        switch ($metricType) {
            // Medewerker metrics
            case 'mijn_bikefits':
                $value = Bikefit::where('user_id', $user->id)->count();
                break;
                
            case 'mijn_inspanningstests':
                $value = Inspanningstest::where('user_id', $user->id)->count();
                break;
                
            case 'mijn_klanten':
                // Klanten gekoppeld aan medewerker via bikefits/inspanningstests
                $value = Klant::whereHas('bikefits', function($q) use ($user) {
                    $q->where('user_id', $user->id);
                })->orWhereHas('inspanningstests', function($q) use ($user) {
                    $q->where('user_id', $user->id);
                })->distinct()->count();
                break;
                
            case 'mijn_omzet_maand':
                if ($user->isMedewerker() || $user->isBeheerder()) {
                    $value = Prestatie::where('user_id', $user->id)
                        ->whereYear('startdatum', now()->year)
                        ->whereMonth('startdatum', now()->month)
                        ->where('dienst_uitgevoerd', true)
                        ->sum(DB::raw('CAST(REPLACE(REPLACE(prijs, "€", ""), ",", ".") AS DECIMAL(10,2))'));
                    $prefix = '€';
                    $value = number_format($value, 2, ',', '.');
                }
                break;
                
            case 'mijn_omzet_kwartaal':
                if ($user->isMedewerker() || $user->isBeheerder()) {
                    $kwartaal = 'Q' . now()->quarter;
                    $value = Prestatie::where('user_id', $user->id)
                        ->where('jaar', now()->year)
                        ->where('kwartaal', $kwartaal)
                        ->where('dienst_uitgevoerd', true)
                        ->sum(DB::raw('CAST(REPLACE(REPLACE(prijs, "€", ""), ",", ".") AS DECIMAL(10,2))'));
                    $prefix = '€';
                    $value = number_format($value, 2, ',', '.');
                }
                break;
                
            // Admin metrics
            case 'totaal_klanten':
                if ($user->isBeheerder()) {
                    $value = Klant::where('organisatie_id', $user->organisatie_id)->count();
                }
                break;
                
            case 'totaal_bikefits':
                if ($user->isBeheerder()) {
                    $value = Bikefit::whereHas('klant', function($q) use ($user) {
                        $q->where('organisatie_id', $user->organisatie_id);
                    })->count();
                }
                break;
                
            case 'nieuwe_klanten_maand':
                if ($user->isBeheerder()) {
                    $value = Klant::where('organisatie_id', $user->organisatie_id)
                        ->whereYear('created_at', now()->year)
                        ->whereMonth('created_at', now()->month)
                        ->count();
                }
                break;
                
            case 'omzet_organisatie_maand':
                if ($user->isBeheerder()) {
                    $value = Prestatie::whereHas('user', function($q) use ($user) {
                        $q->where('organisatie_id', $user->organisatie_id);
                    })
                    ->whereYear('startdatum', now()->year)
                    ->whereMonth('startdatum', now()->month)
                    ->where('dienst_uitgevoerd', true)
                    ->sum(DB::raw('CAST(REPLACE(REPLACE(prijs, "€", ""), ",", ".") AS DECIMAL(10,2))'));
                    $prefix = '€';
                    $value = number_format($value, 2, ',', '.');
                }
                break;
                
            case 'omzet_organisatie_kwartaal':
                if ($user->isBeheerder()) {
                    $kwartaal = 'Q' . now()->quarter;
                    $value = Prestatie::whereHas('user', function($q) use ($user) {
                        $q->where('organisatie_id', $user->organisatie_id);
                    })
                    ->where('jaar', now()->year)
                    ->where('kwartaal', $kwartaal)
                    ->where('dienst_uitgevoerd', true)
                    ->sum(DB::raw('CAST(REPLACE(REPLACE(prijs, "€", ""), ",", ".") AS DECIMAL(10,2))'));
                    $prefix = '€';
                    $value = number_format($value, 2, ',', '.');
                }
                break;
                
            case 'actieve_medewerkers':
                if ($user->isBeheerder()) {
                    $value = \App\Models\User::where('organisatie_id', $user->organisatie_id)
                        ->where('role', '!=', 'klant')
                        ->whereNotNull('email_verified_at')
                        ->count();
                }
                break;
                
            default:
                // Custom waarde - wordt niet automatisch berekend
                break;
        }
        
        return response()->json([
            'value' => $value,
            'prefix' => $prefix,
            'suffix' => $suffix,
            'formatted' => $prefix . $value . $suffix
        ]);
    }
}