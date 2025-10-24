public function index(Request $request)
    {
        // ...bestaande code...
        
        // 🔒 KRITIEK: Organisatie filter toevoegen aan alle queries!
        $organisatieId = auth()->user()->organisatie_id;

        $prestaties = Prestatie::with(['dienst', 'klant'])
            ->where('user_id', auth()->id())
            ->where('organisatie_id', $organisatieId) // ORGANISATIE FILTER
            ->whereBetween('datum_prestatie', [$kwartaalStart, $kwartaalEind])
            ->orderBy('datum_prestatie', 'desc')
            ->get();

        // Haal ALLE actieve diensten op (niet alleen toegewezen)
        $beschikbareDiensten = Dienst::where('is_actief', true)
            ->where('organisatie_id', $organisatieId) // ORGANISATIE FILTER
            ->orderBy('naam')
            ->get();

        $klanten = Klant::where('organisatie_id', $organisatieId) // ORGANISATIE FILTER
            ->select('id', 'voornaam', 'naam')
            ->orderBy('naam')
            ->get()
            ->map(function($klant) {
                return [
                    'id' => $klant->id,
                    'naam' => $klant->voornaam . ' ' . $klant->naam
                ];
            });

        \Log::info('📊 Prestaties geladen MET organisatie filter', [
            'user_id' => auth()->id(),
            'organisatie_id' => $organisatieId,
            'aantal_prestaties' => $prestaties->count()
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

        // 🔒 KRITIEK: Organisatie ID toevoegen bij aanmaken
        $prestatie = Prestatie::create([
            'user_id' => $user->id,
            'organisatie_id' => $user->organisatie_id, // ORGANISATIE FILTER
            'dienst_id' => $dienst->id,
            'klant_id' => $validated['klant_id'] ?? null,
            'datum_prestatie' => $validated['datum_prestatie'],
            'einddatum_prestatie' => $validated['einddatum_prestatie'] ?? null,
            'bruto_prijs' => $validated['prijs'],
            'commissie_percentage' => $commissiePercentage,
            'commissie_bedrag' => $commissieBedrag,
            'opmerkingen' => $validated['opmerkingen'] ?? null,
        ]);

        \Log::info('✅ Prestatie aangemaakt MET organisatie filter', [
            'prestatie_id' => $prestatie->id,
            'organisatie_id' => $user->organisatie_id,
            'user_id' => $user->id
        ]);

        return redirect()->route('prestaties.index')
            ->with('success', 'Prestatie succesvol toegevoegd! Commissie: €' . number_format($commissieBedrag, 2, ',', '.'));
    }