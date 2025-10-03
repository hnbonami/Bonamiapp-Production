<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Klant;
use App\Models\Medewerker;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;

class BirthdayController extends Controller
{
    public function index()
    {
        // Get today's birthdays
        $today = Carbon::today();
        
        $todayBirthdays = $this->getTodaysBirthdays();
        $upcomingBirthdays = $this->getUpcomingBirthdays();
        
        return view('admin.birthdays.index', compact('todayBirthdays', 'upcomingBirthdays'));
    }
    
    public function sendManual(Request $request)
    {
        try {
            // Run the birthday command manually
            Artisan::call('birthday:send-emails');
            $output = Artisan::output();
            
            return response()->json([
                'success' => true,
                'message' => 'Birthday emails sent successfully!',
                'output' => $output
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    private function getTodaysBirthdays()
    {
        $today = Carbon::today();
        
        $klanten = Klant::whereRaw('DATE_FORMAT(geboortedatum, "%m-%d") = ?', [$today->format('m-d')])
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->get();
            
        $medewerkers = Medewerker::whereRaw('DATE_FORMAT(geboortedatum, "%m-%d") = ?', [$today->format('m-d')])
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->get();
        
        return [
            'klanten' => $klanten,
            'medewerkers' => $medewerkers,
            'total' => $klanten->count() + $medewerkers->count()
        ];
    }
    
    private function getUpcomingBirthdays($days = 7)
    {
        $today = Carbon::today();
        $upcoming = collect();
        
        // Check next 7 days
        for ($i = 1; $i <= $days; $i++) {
            $date = $today->copy()->addDays($i);
            
            $klanten = Klant::whereRaw('DATE_FORMAT(geboortedatum, "%m-%d") = ?', [$date->format('m-d')])
                ->whereNotNull('email')
                ->where('email', '!=', '')
                ->get();
                
            $medewerkers = Medewerker::whereRaw('DATE_FORMAT(geboortedatum, "%m-%d") = ?', [$date->format('m-d')])
                ->whereNotNull('email')
                ->where('email', '!=', '')
                ->get();
            
            if ($klanten->count() > 0 || $medewerkers->count() > 0) {
                $upcoming->push([
                    'date' => $date,
                    'klanten' => $klanten,
                    'medewerkers' => $medewerkers
                ]);
            }
        }
        
        return $upcoming;
    }
}