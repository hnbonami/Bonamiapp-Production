public function index(Request $request)
    {
        // ...bestaande code...
        
        // Haal ALLE actieve diensten op (niet alleen toegewezen)
        $beschikbareDiensten = Dienst::where('is_actief', true)
            ->orderBy('naam')
            ->get();
        
        // Log voor debugging
        \Log::info('💼 Beschikbare diensten voor prestaties', [
            'user_id' => auth()->id(),
            'totaal_actieve_diensten' => Dienst::where('is_actief', true)->count(),
            'getoonde_diensten' => $beschikbareDiensten->count(),
            'dienst_namen' => $beschikbareDiensten->pluck('naam')->toArray()
        ]);
        
        // ...bestaande code...
    }