<?php

namespace App\Http\Controllers;

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
        }els\Klant;
use App\Services\ReferralService;
use App\Models\CustomerReferral;
use App\Models\User;
use App\Models\InvitationToken;
use App\Services\EmailService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use App\Helpers\MailHelper;

class KlantenController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $klanten = Klant::orderBy('created_at', 'desc')->get();
        return view('klanten.index', compact('klanten'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Add unique email validation to existing validation rules
        $validatedData = $request->validate([
            'voornaam' => 'required|string|max:255',
            'naam' => 'required|string|max:255',
            'email' => 'required|email|unique:klanten,email', // Add unique constraint
            'telefoonnummer' => 'nullable|string|max:20',
            'straatnaam' => 'nullable|string|max:255',
            'huisnummer' => 'nullable|string|max:10',
            'postcode' => 'nullable|string|max:10',
            'stad' => 'nullable|string|max:255',
            'geboortedatum' => 'nullable|date',
            'geslacht' => 'nullable|in:Man,Vrouw,Andere',
            'status' => 'required|in:Actief,Inactief,Prospect',
            'sport' => 'nullable|string|max:255',
            'niveau' => 'nullable|string|max:255',
            'club' => 'nullable|string|max:255',
            'herkomst' => 'nullable|string|max:255',
        ]);
        
        try {
            // Create the customer
            $klant = Klant::create($validatedData);

            // Create a user account for the customer
            $temporaryPassword = Str::random(12);
            $user = User::create([
                'name' => $klant->voornaam . ' ' . $klant->naam,
                'email' => $klant->email,
                'password' => Hash::make($temporaryPassword),
                'role' => 'customer',
                'klant_id' => $klant->id,
            ]);

            // Create invitation token
            InvitationToken::create([
                'email' => $klant->email,
                'token' => Str::random(60),
                'type' => 'klant',
                'temporary_password' => $temporaryPassword,
                'expires_at' => now()->addDays(7),
            ]);

            // Send welcome email with temporary password
            try {
                EmailService::sendWelcomeEmail($klant, $temporaryPassword);
            } catch (\Exception $e) {
                \Log::error('Failed to send welcome email for customer ' . $klant->id . ': ' . $e->getMessage());
                // Don't fail the customer creation if email fails
            }

            return redirect()->route('klanten.index')
                ->with('success', 'Klant succesvol aangemaakt! Een welkomst email is verstuurd.');
                
        } catch (\Exception $e) {
            \Log::error('Failed to create customer: ' . $e->getMessage());
            return redirect()->back()
                ->withErrors(['error' => 'Er is een fout opgetreden bij het aanmaken van de klant.'])
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Klant  $klant
     * @return \Illuminate\Http\Response
     */
    public function show(Klant $klant)
    {
        // Load all tests with their testtype fields and user relationships
        $bikefits = $klant->bikefits()->with('user')->select('id', 'klant_id', 'datum', 'testtype', 'user_id', 'created_at', 'updated_at')->get();
        $inspanningstesten = $klant->inspanningstesten()->with('user')->select('id', 'klant_id', 'datum', 'testtype', 'user_id', 'created_at', 'updated_at')->get();
        
        // Combine and sort by date
        $alleTests = $bikefits->concat($inspanningstesten)->sortByDesc('datum');
        
        return view('klanten.show', compact('klant', 'alleTests'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Klant  $klant
     * @return \Illuminate\Http\Response
     */
    public function edit(Klant $klant)
    {
        return view('klanten.edit', compact('klant'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Klant  $klant
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Klant $klant)
    {
        $data = $request->validate([
            'voornaam' => 'required|string|max:255',
            'naam' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'telefoonnummer' => 'nullable|string|max:255',
            'straatnaam' => 'nullable|string|max:255',
            'huisnummer' => 'nullable|string|max:10',
            'postcode' => 'nullable|string|max:10',
            'stad' => 'nullable|string|max:255',
            'geboortedatum' => 'nullable|date',
            'geslacht' => 'required|in:Man,Vrouw,Anders',
            'status' => 'required|in:Actief,Inactief',
            'sport' => 'nullable|string|max:255',
            'niveau' => 'nullable|string|max:255',
            'club' => 'nullable|string|max:255',
            'herkomst' => 'nullable|string|max:255',
        ]);

        $klant->update($data);
        return redirect()->route('klanten.show', $klant)->with('success', 'Klant bijgewerkt');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Klant  $klant
     * @return \Illuminate\Http\Response
     */
    public function destroy(Klant $klant)
    {
        $klant->delete();
        return redirect()->route('klanten.index')->with('success', 'Klant verwijderd');
    }

    /**
     * Show the import form
     */
    public function showImport()
    {
        return view('klanten.import');
    }

    /**
     * Handle the Excel import
     */
    public function import(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // 10MB max
        ]);

        try {
            $import = new \App\Imports\KlantenImport();
            \Maatwebsite\Excel\Facades\Excel::import($import, $request->file('excel_file'));
            
            return redirect('/klanten')->with('success', 'Klanten succesvol geïmporteerd uit Excel bestand!');
            
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errors = [];
            
            foreach ($failures as $failure) {
                $errors[] = "Rij {$failure->row()}: {$failure->attribute()} - " . implode(', ', $failure->errors());
            }
            
            return redirect()->back()
                ->withErrors($errors)
                ->with('error', 'Er zijn validatiefouten opgetreden bij het importeren.');
                
        } catch (\Exception $e) {
            \Log::error('Excel import failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Er is een fout opgetreden bij het importeren: ' . $e->getMessage());
        }
    }

    /**
     * Download example Excel template
     */
    public function downloadTemplate()
    {
        $headers = [
            'naam',
            'email', 
            'telefoon',
            'adres',
            'postcode',
            'plaats',
            'geboortedatum',
            'geslacht',
            'lengte_cm',
            'gewicht_kg',
            'sport',
            'niveau',
            'doelen',
            'medische_info',
            'opmerkingen'
        ];

        $filename = 'klanten_import_template.csv';
        
        return response()->streamDownload(function() use ($headers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            
            // Add example row
            fputcsv($file, [
                'Jan Janssen',
                'jan@example.com',
                '0612345678',
                'Voorbeeldstraat 123',
                '1234AB',
                'Amsterdam',
                '1990-01-15',
                'man',
                '180',
                '75',
                'wielrennen',
                'recreatief',
                'beter fietsen',
                'geen',
                'eerste klant'
            ]);
            
            fclose($file);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    /**
     * Export all klanten to Excel
     */
    public function exportKlanten()
    {
        $filename = 'klanten_export_' . date('Y-m-d_H-i-s') . '.xlsx';
        return Excel::download(new \App\Exports\KlantenExport, $filename);
    }

    /**
     * Send invitation email to klant
     */
    public function sendInvitation(Request $request, Klant $klant)
    {
        try {
            // Generate temporary password
            $temporaryPassword = \Str::random(12);
            
            // Check if user already exists, if not create one
            $user = \App\Models\User::where('email', $klant->email)->first();
            if (!$user) {
                $user = \App\Models\User::create([
                    'name' => $klant->voornaam . ' ' . $klant->naam,
                    'email' => $klant->email,
                    'password' => \Hash::make($temporaryPassword),
                    'role' => 'customer',
                    'klant_id' => $klant->id,
                ]);
            } else {
                // Update existing user password
                $user->update([
                    'password' => \Hash::make($temporaryPassword),
                ]);
            }
            
            // Create invitation token
            $invitationToken = \App\Models\InvitationToken::createForKlant($klant->email, $temporaryPassword);
            
            // Send invitation email with working login credentials
            $emailResult = MailHelper::sendCustomerInvitation($klant, $temporaryPassword, $invitationToken);
            
            // Update klant status to show invitation was sent
            $klant->update(['laatste_uitnodiging' => now()]);
            
            if ($emailResult) {
                return redirect()->back()->with('success', 'Uitnodiging verstuurd naar ' . $klant->voornaam . ' ' . $klant->naam . ' (' . $klant->email . '). Login: ' . $klant->email . ' / ' . $temporaryPassword);
            } else {
                return redirect()->back()->with('error', 'Uitnodiging kon niet worden verstuurd naar ' . $klant->naam);
            }
            
        } catch (\Exception $e) {
            \Log::error('Invitation sending failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Fout bij versturen uitnodiging: ' . $e->getMessage());
        }
    }
}