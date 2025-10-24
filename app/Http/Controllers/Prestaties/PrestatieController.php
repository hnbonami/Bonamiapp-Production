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

public function store(Request $request)
    {
        // ...existing code...

        $dienst = Dienst::findOrFail($validated['dienst_id']);
        $user = auth()->user();

        // 🆕 Gebruik medewerker-specifieke commissie berekening
        // Dit houdt rekening met diploma, ervaring en anciënniteit bonussen
        $commissiePercentage = $user->getCommissiePercentageVoorDienst($dienst);
        $commissieBedrag = ($validated['prijs'] * $commissiePercentage) / 100;

        // Maak prestatie aan met berekende commissie
        $prestatie = Prestatie::create([
            'user_id' => $user->id,
            'dienst_id' => $dienst->id,
            'klant_id' => $validated['klant_id'] ?? null,
            'datum_prestatie' => $validated['datum_prestatie'],
            'einddatum_prestatie' => $validated['einddatum_prestatie'] ?? null,
            'bruto_prijs' => $validated['prijs'],
            'commissie_percentage' => $commissiePercentage,
            'commissie_bedrag' => $commissieBedrag,
            'opmerkingen' => $validated['opmerkingen'] ?? null,
        ]);

        \Log::info('✅ Prestatie aangemaakt met medewerker-specifieke commissie', [
            'prestatie_id' => $prestatie->id,
            'user_id' => $user->id,
            'dienst_naam' => $dienst->naam,
            'dienst_basis_commissie' => $dienst->commissie_percentage,
            'berekende_commissie' => $commissiePercentage,
            'commissie_bedrag' => $commissieBedrag,
        ]);

        return redirect()->route('prestaties.index')
            ->with('success', 'Prestatie succesvol toegevoegd! Commissie: €' . number_format($commissieBedrag, 2, ',', '.'));
    }