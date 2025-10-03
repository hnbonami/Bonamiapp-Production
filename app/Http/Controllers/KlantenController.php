<?php

namespace App\Http\Controllers;

use App\Models\Klant;
use App\Helpers\MailHelper;
use Illuminate\Http\Request;
use App\Imports\KlantenImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Mail;

class KlantenController extends Controller
{
    // In the KlantenController show method, ensure we load the testtype field for both bikefits and inspanningstesten

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

    public function index()
    {
        $klanten = Klant::orderBy('created_at', 'desc')->get();
        return view('klanten.index', compact('klanten'));
    }

    public function create()
    {
        return view('klanten.create');
    }

    public function store(Request $request)
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

        $klant = Klant::create($data);

        // Verstuur automatisch uitnodiging als email is ingevuld
        if (!empty($data['email'])) {
            try {
                // Generate temporary password
                $temporaryPassword = \Str::random(12);
                
                // Create user account for the customer
                $user = \App\Models\User::create([
                    'name' => $data['voornaam'] . ' ' . $data['naam'],
                    'email' => $data['email'],
                    'password' => \Hash::make($temporaryPassword),
                    'role' => 'customer',
                    'klant_id' => $klant->id,
                ]);
                
                // Create invitation token
                $invitationToken = \App\Models\InvitationToken::createForKlant($klant->email, $temporaryPassword);
                
                // Send invitation email
                $emailResult = MailHelper::sendCustomerInvitation($klant, $temporaryPassword, $invitationToken);
                
                // Update klant to show invitation was sent
                $klant->update(['laatste_uitnodiging' => now()]);
                
                if ($emailResult) {
                    $successMessage = 'Klant aangemaakt en uitnodiging verstuurd naar ' . $klant->email . '. Login: ' . $klant->email . ' / ' . $temporaryPassword;
                } else {
                    $successMessage = 'Klant aangemaakt, maar uitnodiging kon niet worden verstuurd';
                }            } catch (\Exception $e) {
                \Log::error('Klant invitation sending failed: ' . $e->getMessage());
                $successMessage = 'Klant aangemaakt, maar uitnodiging kon niet worden verstuurd';
            }
        } else {
            $successMessage = 'Klant aangemaakt';
        }

        return redirect()->route('klanten.show', $klant)->with('success', $successMessage);
    }

    public function edit(Klant $klant)
    {
        return view('klanten.edit', compact('klant'));
    }

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

    public function destroy(Klant $klant)
    {
        $klant->delete();
        return redirect()->route('klanten.index')->with('success', 'Klant verwijderd');
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