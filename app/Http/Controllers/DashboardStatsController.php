<?php

namespace App\Http\Controllers;

use App\Models\Klant;
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
}