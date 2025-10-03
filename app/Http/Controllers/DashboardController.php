<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Klant;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index()
    {
        $klant = null;
        
        try {
            // Log voor debugging
            Log::info('Dashboard accessed by user', [
                'user_id' => auth()->id(),
                'user_type' => auth()->user()->user_type,
                'klant_id' => auth()->user()->klant_id ?? 'null',
                'email' => auth()->user()->email
            ]);
            
                        // Als de gebruiker een admin is, toon admin dashboard
            if (auth()->user()->user_type === 'admin') {
                Log::info('Admin user accessing dashboard');
                // Voor admin: null klant variabele meegeven en dashboard view gebruiken
                $klant = null;
                Log::info('Admin dashboard: klant is null, returning dashboard view');
                return view('dashboard', compact('klant'));
            }
            
            // Als de gebruiker een klant is, probeer klant informatie op te halen
            if (auth()->user()->user_type === 'klant') {
                // Eerst proberen via klant_id
                if (auth()->user()->klant_id) {
                    $klant = Klant::find(auth()->user()->klant_id);
                    Log::info('Klant gevonden via klant_id', ['klant_id' => $klant?->id]);
                }
                
                // Als geen klant gevonden via klant_id, probeer via email
                if (!$klant) {
                    $klant = Klant::where('email', auth()->user()->email)->first();
                    Log::info('Klant gezocht via email', ['found' => !!$klant, 'klant_id' => $klant?->id]);
                    
                    // Als klant gevonden via email, update user record
                    if ($klant) {
                        auth()->user()->update(['klant_id' => $klant->id]);
                        Log::info('User klant_id updated', ['klant_id' => $klant->id]);
                    }
                }
                
                return view('dashboard', compact('klant'));
            }
            
        } catch (\Exception $e) {
            Log::error('Dashboard error: ' . $e->getMessage());
            // Fallback voor onbekende user types
        }
        
        // Fallback: generieke dashboard view
        return view('dashboard', compact('klant'));
    }
}