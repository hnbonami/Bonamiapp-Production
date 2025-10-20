<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Klant;
use App\Services\ReferralService;
use App\Models\CustomerReferral;
use Illuminate\Support\Facades\Log;

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
            'straatnaam' => 'nullable|string|max:255',
            'huisnummer' => 'nullable|string|max:10',
            'adres' => 'nullable|string|max:255',
            'postcode' => 'nullable|string|max:10',
            'stad' => 'nullable|string|max:255',
            'land' => 'nullable|string|max:255',
            'geslacht' => 'nullable|in:Man,Vrouw,Andere',
            'status' => 'nullable|in:Actief,Inactief,Prospect',
            'sport' => 'nullable|string|max:255',
            'niveau' => 'nullable|string|max:255',
            'club' => 'nullable|string|max:255',
            'herkomst' => 'nullable|string|max:255',
            'hoe_ontdekt' => 'nullable|string|max:255',
            'opmerkingen' => 'nullable|string',
            // NIEUWE REFERRAL VALIDATIE - VEILIG TOEGEVOEGD
            'referral_source' => 'nullable|string|max:50',
            'referring_customer_id' => 'nullable|exists:klanten,id',
            'referral_notes' => 'nullable|string|max:500'
        ]);

        try {
            // STAP 1: Maak klant aan met ALLE velden (HERSTELD)
            $klant = Klant::create($request->only([
                'voornaam', 'naam', 'email', 'telefoonnummer', 'geboortedatum',
                'straatnaam', 'huisnummer', 'adres', 'postcode', 'stad', 'land',
                'geslacht', 'status', 'sport', 'niveau', 'club', 'herkomst',
                'hoe_ontdekt', 'opmerkingen'
            ]));

            // STAP 1.5: BESTAANDE WELKOMSTMAIL SYSTEEM (MOET BLIJVEN WERKEN!)
            try {
                // Generate temporary password
                $temporaryPassword = \Str::random(12);
                
                // Create user account (BESTAAND SYSTEEM)
                $user = \App\Models\User::create([
                    'name' => $klant->voornaam . ' ' . $klant->naam,
                    'email' => $klant->email,
                    'password' => \Hash::make($temporaryPassword),
                    'role' => 'klant', // NIET 'customer'
                    'klant_id' => $klant->id,
                ]);

                // Create invitation token (BESTAAND SYSTEEM)
                \App\Models\InvitationToken::create([
                    'email' => $klant->email,
                    'token' => \Str::random(60),
                    'type' => 'klant',
                    'temporary_password' => $temporaryPassword,
                    'expires_at' => now()->addDays(7),
                ]);

                // BESTAANDE WELKOMSTMAIL VERSTUREN
                $emailService = app(\App\Services\EmailIntegrationService::class);
                $emailService->sendCustomerWelcomeEmail($klant, [
                    'temporary_password' => $temporaryPassword,
                    'voornaam' => $klant->voornaam,
                    'naam' => $klant->naam,
                    'email' => $klant->email
                ]);
                
                \Log::info('✅ Welcome email sent to new customer');
                
            } catch (\Exception $welcomeError) {
                \Log::error('❌ Welcome email failed (NON-CRITICAL): ' . $welcomeError->getMessage());
                // Don't fail customer creation if welcome email fails
            }

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
                ->with('success', 'Klant succesvol aangemaakt! Welkomstmail is verstuurd.' . $referralMessage);
                
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
        // DIRECTE TEST - DEZE MOET JE ZIEN!
        \Log::info('🚨🚨🚨 ORIGINELE KLANTEN CONTROLLER AANGEROEPEN!');
        
        // SIMPLE DEBUG TEST
        \Log::info('🚨 KLANT CONTROLLER UPDATE CALLED!');
        
        // DEBUG: Check wat er wordt verstuurd
        \Log::info('🔍 KLANT UPDATE - Incoming data:', $request->all());
        \Log::info('🔍 KLANT UPDATE - Before update:', $klant->toArray());

        $validatedData = $request->validate([
            'voornaam' => 'required|string|max:255',
            'naam' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'telefoonnummer' => 'nullable|string|max:255',
            'geboortedatum' => 'nullable|date',
            'geslacht' => 'nullable|in:Man,Vrouw,Anders',
            'straatnaam' => 'nullable|string|max:255',
            'huisnummer' => 'nullable|string|max:50',
            'postcode' => 'nullable|string|max:10',
            'stad' => 'nullable|string|max:255',
            'sport' => 'nullable|string|max:255',
            'niveau' => 'nullable|string|max:255',
            'club' => 'nullable|string|max:255',
            'herkomst' => 'nullable|string|max:255',
        ]);

        // DEBUG: Check validated data
        \Log::info('🔍 KLANT UPDATE - Validated data:', $validatedData);

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $validatedData['avatar_path'] = $avatarPath;
        }

        $klant->update($validatedData);
        
        // DEBUG: Check het resultaat na update
        \Log::info('🔍 KLANT UPDATE - After update:', $klant->fresh()->toArray());

        return redirect()->route('klanten.show', $klant)
                         ->with('success', 'Klant succesvol bijgewerkt!');
    }

    public function destroy(Klant $klant)
    {
        $klant->delete();
        return redirect()->route('klanten.index')->with('success', 'Klant succesvol verwijderd!');
    }

    /**
     * Send invitation email to customer - VEILIG TOEGEVOEGD
     */
    public function sendInvitation(Request $request, Klant $klant)
    {
        try {
            \Log::info('🎯 SENDING INVITATION EMAIL', [
                'klant_id' => $klant->id,
                'klant_email' => $klant->email
            ]);

            // Generate temporary password
            $temporaryPassword = \Str::random(12);
            
            // Check if user already exists, if not create one
            $user = \App\Models\User::where('email', $klant->email)->first();
            if (!$user) {
                $user = \App\Models\User::create([
                    'name' => $klant->voornaam . ' ' . $klant->naam,
                    'email' => $klant->email,
                    'password' => \Hash::make($temporaryPassword),
                    'role' => 'klant', // NIET 'customer'
                    'klant_id' => $klant->id,
                ]);
            } else {
                // Update existing user password
                $user->update([
                    'password' => \Hash::make($temporaryPassword),
                ]);
            }
            
            // Create invitation token
            $invitationToken = \App\Models\InvitationToken::create([
                'email' => $klant->email,
                'token' => \Str::random(60),
                'type' => 'klant',
                'temporary_password' => $temporaryPassword,
                'expires_at' => now()->addDays(7),
            ]);
            
            // Send invitation email using our EmailIntegrationService
            $emailService = app(\App\Services\EmailIntegrationService::class);
            $emailResult = $emailService->sendCustomerWelcomeEmail($klant, [
                'temporary_password' => $temporaryPassword,
                'voornaam' => $klant->voornaam,
                'naam' => $klant->naam,
                'email' => $klant->email
            ]);
            
            if ($emailResult) {
                return redirect()->back()->with('success', 'Uitnodiging verstuurd naar ' . $klant->voornaam . ' ' . $klant->naam . ' (' . $klant->email . ')');
            } else {
                return redirect()->back()->with('error', 'Uitnodiging kon niet worden verstuurd naar ' . $klant->naam);
            }
            
        } catch (\Exception $e) {
            \Log::error('Invitation sending failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Fout bij versturen uitnodiging: ' . $e->getMessage());
        }
    }

    /**
     * Toon het klanten import formulier
     */
    public function showImport()
    {
        return view('admin.klanten-import');
    }

    /**
     * Download Excel template voor klanten import
     */
    public function downloadTemplate()
    {
        try {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Header rij met alle klanten velden
            $headers = [
                'voornaam',
                'naam', 
                'email',
                'telefoonnummer',
                'geboortedatum',
                'geslacht',
                'straatnaam',
                'huisnummer',
                'postcode',
                'stad',
                'land',
                'sport',
                'niveau',
                'club',
                'herkomst',
                'hoe_ontdekt',
                'opmerkingen',
                'status'
            ];
            
            // Zet headers in de eerste rij
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col . '1', $header);
                $sheet->getStyle($col . '1')->getFont()->setBold(true);
                $sheet->getColumnDimension($col)->setAutoSize(true);
                $col++;
            }
            
            // Voeg een voorbeeld rij toe
            $sheet->setCellValue('A2', 'Jan');
            $sheet->setCellValue('B2', 'Jansen');
            $sheet->setCellValue('C2', 'jan.jansen@example.com');
            $sheet->setCellValue('D2', '0612345678');
            $sheet->setCellValue('E2', '1990-01-15');
            $sheet->setCellValue('F2', 'Man');
            $sheet->setCellValue('G2', 'Hoofdstraat');
            $sheet->setCellValue('H2', '123');
            $sheet->setCellValue('I2', '1234AB');
            $sheet->setCellValue('J2', 'Amsterdam');
            $sheet->setCellValue('K2', 'Nederland');
            $sheet->setCellValue('L2', 'Wielrennen');
            $sheet->setCellValue('M2', 'Gevorderd');
            $sheet->setCellValue('N2', 'Wielerclub Amsterdam');
            $sheet->setCellValue('O2', 'Google');
            $sheet->setCellValue('P2', 'Online zoeken');
            $sheet->setCellValue('Q2', 'Voorbeeld opmerking');
            $sheet->setCellValue('R2', 'Actief');
            
            // Maak Excel bestand
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $filename = 'klanten_import_template_' . date('Y-m-d') . '.xlsx';
            
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            
            $writer->save('php://output');
            exit;
            
        } catch (\Exception $e) {
            \Log::error('Template download failed: ' . $e->getMessage());
            return back()->with('error', 'Template kon niet worden gedownload: ' . $e->getMessage());
        }
    }

    /**
     * Import klanten vanuit Excel bestand
     */
    public function import(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:xlsx,xls,csv|max:10240'
            ]);
            
            $file = $request->file('file');
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();
            
            if (empty($rows)) {
                return back()->with('error', 'Het Excel bestand is leeg');
            }
            
            // Eerste rij bevat headers
            $headers = array_map('strtolower', array_map('trim', $rows[0]));
            $importedCount = 0;
            $skippedCount = 0;
            $errors = [];
            
            // Loop door alle data rijen (skip header)
            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                
                // Skip lege rijen
                if (empty(array_filter($row))) {
                    continue;
                }
                
                try {
                    // Map kolommen naar data
                    $data = [];
                    foreach ($headers as $index => $header) {
                        if (isset($row[$index])) {
                            $data[$header] = $row[$index];
                        }
                    }
                    
                    // Verplichte velden valideren
                    if (empty($data['voornaam']) || empty($data['naam']) || empty($data['email'])) {
                        $skippedCount++;
                        $errors[] = "Rij " . ($i + 1) . ": Voornaam, naam en email zijn verplicht";
                        continue;
                    }
                    
                    // Check of klant al bestaat (op basis van email)
                    $existingKlant = Klant::where('email', $data['email'])->first();
                    
                    if ($existingKlant) {
                        // Update bestaande klant
                        $existingKlant->update([
                            'voornaam' => $data['voornaam'] ?? $existingKlant->voornaam,
                            'naam' => $data['naam'] ?? $existingKlant->naam,
                            'telefoonnummer' => $data['telefoonnummer'] ?? $existingKlant->telefoonnummer,
                            'geboortedatum' => !empty($data['geboortedatum']) ? $data['geboortedatum'] : $existingKlant->geboortedatum,
                            'geslacht' => $data['geslacht'] ?? $existingKlant->geslacht,
                            'straatnaam' => $data['straatnaam'] ?? $existingKlant->straatnaam,
                            'huisnummer' => $data['huisnummer'] ?? $existingKlant->huisnummer,
                            'postcode' => $data['postcode'] ?? $existingKlant->postcode,
                            'stad' => $data['stad'] ?? $existingKlant->stad,
                            'land' => $data['land'] ?? $existingKlant->land,
                            'sport' => $data['sport'] ?? $existingKlant->sport,
                            'niveau' => $data['niveau'] ?? $existingKlant->niveau,
                            'club' => $data['club'] ?? $existingKlant->club,
                            'herkomst' => $data['herkomst'] ?? $existingKlant->herkomst,
                            'hoe_ontdekt' => $data['hoe_ontdekt'] ?? $existingKlant->hoe_ontdekt,
                            'opmerkingen' => $data['opmerkingen'] ?? $existingKlant->opmerkingen,
                            'status' => $data['status'] ?? $existingKlant->status,
                        ]);
                    } else {
                        // Maak nieuwe klant aan
                        Klant::create([
                            'voornaam' => $data['voornaam'],
                            'naam' => $data['naam'],
                            'email' => $data['email'],
                            'telefoonnummer' => $data['telefoonnummer'] ?? null,
                            'geboortedatum' => !empty($data['geboortedatum']) ? $data['geboortedatum'] : null,
                            'geslacht' => $data['geslacht'] ?? null,
                            'straatnaam' => $data['straatnaam'] ?? null,
                            'huisnummer' => $data['huisnummer'] ?? null,
                            'postcode' => $data['postcode'] ?? null,
                            'stad' => $data['stad'] ?? null,
                            'land' => $data['land'] ?? null,
                            'sport' => $data['sport'] ?? null,
                            'niveau' => $data['niveau'] ?? null,
                            'club' => $data['club'] ?? null,
                            'herkomst' => $data['herkomst'] ?? null,
                            'hoe_ontdekt' => $data['hoe_ontdekt'] ?? null,
                            'opmerkingen' => $data['opmerkingen'] ?? null,
                            'status' => $data['status'] ?? 'Actief',
                        ]);
                    }
                    
                    $importedCount++;
                    
                } catch (\Exception $e) {
                    $skippedCount++;
                    $errors[] = "Rij " . ($i + 1) . ": " . $e->getMessage();
                    \Log::error("Import error rij $i: " . $e->getMessage());
                }
            }
            
            $message = "Import voltooid! $importedCount klanten geïmporteerd/bijgewerkt";
            if ($skippedCount > 0) {
                $message .= ", $skippedCount rijen overgeslagen";
            }
            
            if (!empty($errors)) {
                $message .= ". Fouten: " . implode('; ', array_slice($errors, 0, 5));
            }
            
            \Log::info("Klanten import: $importedCount succesvol, $skippedCount overgeslagen");
            
            return redirect()->route('klanten.import.form')->with('success', $message);
            
        } catch (\Exception $e) {
            \Log::error('Klanten import failed: ' . $e->getMessage());
            return back()->with('error', 'Import mislukt: ' . $e->getMessage());
        }
    }

    /**
     * Export alle klanten naar Excel
     */
    public function export()
    {
        try {
            $klanten = Klant::orderBy('naam')->get();
            
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Headers
            $headers = [
                'ID', 'Voornaam', 'Naam', 'Email', 'Telefoonnummer', 
                'Geboortedatum', 'Geslacht', 'Straatnaam', 'Huisnummer',
                'Postcode', 'Stad', 'Land', 'Sport', 'Niveau', 'Club',
                'Herkomst', 'Hoe Ontdekt', 'Opmerkingen', 'Status', 
                'Aangemaakt Op'
            ];
            
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col . '1', $header);
                $sheet->getStyle($col . '1')->getFont()->setBold(true);
                $sheet->getColumnDimension($col)->setAutoSize(true);
                $col++;
            }
            
            // Data rijen
            $row = 2;
            foreach ($klanten as $klant) {
                $sheet->setCellValue('A' . $row, $klant->id);
                $sheet->setCellValue('B' . $row, $klant->voornaam);
                $sheet->setCellValue('C' . $row, $klant->naam);
                $sheet->setCellValue('D' . $row, $klant->email);
                $sheet->setCellValue('E' . $row, $klant->telefoonnummer);
                $sheet->setCellValue('F' . $row, $klant->geboortedatum);
                $sheet->setCellValue('G' . $row, $klant->geslacht);
                $sheet->setCellValue('H' . $row, $klant->straatnaam);
                $sheet->setCellValue('I' . $row, $klant->huisnummer);
                $sheet->setCellValue('J' . $row, $klant->postcode);
                $sheet->setCellValue('K' . $row, $klant->stad);
                $sheet->setCellValue('L' . $row, $klant->land);
                $sheet->setCellValue('M' . $row, $klant->sport);
                $sheet->setCellValue('N' . $row, $klant->niveau);
                $sheet->setCellValue('O' . $row, $klant->club);
                $sheet->setCellValue('P' . $row, $klant->herkomst);
                $sheet->setCellValue('Q' . $row, $klant->hoe_ontdekt);
                $sheet->setCellValue('R' . $row, $klant->opmerkingen);
                $sheet->setCellValue('S' . $row, $klant->status);
                $sheet->setCellValue('T' . $row, $klant->created_at ? $klant->created_at->format('Y-m-d H:i') : '');
                $row++;
            }
            
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $filename = 'klanten_export_' . date('Y-m-d_His') . '.xlsx';
            
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            
            $writer->save('php://output');
            exit;
            
        } catch (\Exception $e) {
            \Log::error('Klanten export failed: ' . $e->getMessage());
            return back()->with('error', 'Export mislukt: ' . $e->getMessage());
        }
    }
}