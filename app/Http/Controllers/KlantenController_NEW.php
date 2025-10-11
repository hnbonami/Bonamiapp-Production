<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Klant;
use App\Services\ReferralService;
use App\Models\CustomerReferral;

class KlantenController extends Controller
{
    public function index()
    {
        $klanten = Klant::all();
        return view('klanten.index', compact('klanten'));
    }

    public function create()
    {
        try {
            // VEILIG: Haal referral data op ZONDER te crashen
            $referralService = app(ReferralService::class);
            $availableReferringCustomers = $referralService->getAvailableReferringCustomers();
            $referralSources = CustomerReferral::getReferralSources();
            
            return view('klanten.create', compact('availableReferringCustomers', 'referralSources'));
        } catch (\Exception $e) {
            // VEILIG: Als referral systeem faalt, toon gewoon normale create pagina
            \Log::warning('Referral system unavailable, showing normal create form: ' . $e->getMessage());
            
            return view('klanten.create', [
                'availableReferringCustomers' => collect(),
                'referralSources' => []
            ]);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'voornaam' => 'required|string|max:255',
            'naam' => 'required|string|max:255',
            'email' => 'required|email|unique:klanten,email',
            'telefoonnummer' => 'nullable|string|max:20',
            'geboortedatum' => 'nullable|date',
            'adres' => 'nullable|string|max:255',
            'postcode' => 'nullable|string|max:10',
            'stad' => 'nullable|string|max:255',
            'land' => 'nullable|string|max:255',
            'opmerkingen' => 'nullable|string',
            'hoe_ontdekt' => 'nullable|string|max:255',
            // NIEUWE REFERRAL VALIDATIE - VEILIG TOEGEVOEGD
            'referral_source' => 'nullable|string|max:50',
            'referring_customer_id' => 'nullable|exists:klanten,id',
            'referral_notes' => 'nullable|string|max:500'
        ]);

        try {
            // STAP 1: Maak klant aan (BESTAANDE FUNCTIONALITEIT - ONGEWIJZIGD)
            $klant = Klant::create($request->only([
                'voornaam', 'naam', 'email', 'telefoonnummer', 'geboortedatum',
                'adres', 'postcode', 'stad', 'land', 'opmerkingen', 'hoe_ontdekt'
            ]));

            // STAP 2: VEILIG verwerken van referral (NIEUWE FUNCTIONALITEIT)
            $referralMessage = '';
            if ($request->filled('referral_source')) {
                try {
                    $referralService = app(ReferralService::class);
                    $referralData = [
                        'source' => $request->referral_source,
                        'referring_customer_id' => $request->referring_customer_id,
                        'notes' => $request->referral_notes
                    ];
                    
                    $referral = $referralService->processNewCustomerReferral($klant, $referralData);
                    
                    if ($referral && $request->referring_customer_id) {
                        $referralMessage = ' Bedankmail wordt verstuurd naar de doorverwijzende klant.';
                    }
                } catch (\Exception $referralError) {
                    // VEILIG: Referral error crasht NIET de klant aanmaak
                    \Log::error('Referral processing failed (NON-CRITICAL): ' . $referralError->getMessage());
                }
            }

            // STAP 3: Redirect met success (BESTAANDE FUNCTIONALITEIT + referral message)
            return redirect()
                ->route('klanten.show', $klant)
                ->with('success', 'Klant succesvol aangemaakt!' . $referralMessage);
                
        } catch (\Exception $e) {
            \Log::error('Failed to create customer: ' . $e->getMessage());
            return back()
                ->withInput()
                ->withErrors(['error' => 'Er is een fout opgetreden bij het aanmaken van de klant.']);
        }
    }

    public function show(Klant $klant)
    {
        return view('klanten.show', compact('klant'));
    }

    public function edit(Klant $klant)
    {
        return view('klanten.edit', compact('klant'));
    }

    public function update(Request $request, Klant $klant)
    {
        $request->validate([
            'voornaam' => 'required|string|max:255',
            'naam' => 'required|string|max:255',
            'email' => 'required|email|unique:klanten,email,' . $klant->id,
            'telefoonnummer' => 'nullable|string|max:20',
            'geboortedatum' => 'nullable|date',
            'adres' => 'nullable|string|max:255',
            'postcode' => 'nullable|string|max:10',
            'stad' => 'nullable|string|max:255',
            'land' => 'nullable|string|max:255',
            'opmerkingen' => 'nullable|string',
            'hoe_ontdekt' => 'nullable|string|max:255',
        ]);

        $klant->update($request->all());

        return redirect()->route('klanten.show', $klant)->with('success', 'Klant succesvol bijgewerkt!');
    }

    public function destroy(Klant $klant)
    {
        $klant->delete();
        return redirect()->route('klanten.index')->with('success', 'Klant succesvol verwijderd!');
    }
}