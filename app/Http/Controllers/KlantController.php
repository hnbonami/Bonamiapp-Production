<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Klant;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\AccountCreatedMail;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\KlantenImport;
use App\Exports\KlantenExport;
use App\Exports\KlantenTemplateExport;

class KlantController extends Controller
{
    public function verwijderViaPost(Request $request, Klant $klant)
    {
        $email = $klant->email;
        
        // Verwijder gekoppelde user (FORCE DELETE)
        if ($email) {
            $user = \App\Models\User::where('email', $email)->first();
            if ($user) {
                $user->forceDelete();
                \Log::info('✅ User account permanent verwijderd', ['email' => $email]);
            }
        }
        
        // FORCE DELETE klant (geen soft delete)
        $klant->forceDelete();
        
        return redirect()->route('klanten.index')->with('success', 'Klant succesvol verwijderd.');
    }
    public function destroy(Klant $klant)
    {
        $id = $klant->id;
        $email = $klant->email;
        
        // Verwijder gekoppelde user (FORCE DELETE)
        if ($email) {
            $user = \App\Models\User::where('email', $email)->first();
            if ($user) {
                $user->forceDelete(); // FORCE DELETE
                \Log::info('✅ User account permanent verwijderd', ['email' => $email]);
            }
        }
        
        // FORCE DELETE klant (geen soft delete)
        $klant->forceDelete();
        
        \Log::info('🗑️ Klant permanent verwijderd', ['id' => $id, 'email' => $email]);
        return redirect()->route('klanten.index')->with('success', 'Klant en gekoppelde gebruiker succesvol verwijderd.');
    }
    public function edit(Klant $klant)
    {
        // FORCE FRESH DATA - haal altijd verse gegevens op
        $klant = $klant->fresh();
        
        return view('klanten.edit', compact('klant'));
    }

    public function update(Request $request, Klant $klant)
    {
        \Log::info('🎯 KLANTCONTROLLER UPDATE', [
            'klant_id' => $klant->id,
            'has_avatar' => $request->hasFile('avatar')
        ]);
        
        // VALIDATIE MET ALLE JUISTE VELDEN + AVATAR
        $validatedData = $request->validate([
            'voornaam' => 'required|string|max:255',
            'naam' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'telefoonnummer' => 'nullable|string|max:20',
            'geboortedatum' => 'nullable|date',
            'geslacht' => 'nullable|in:Man,Vrouw,X',
            'straatnaam' => 'nullable|string|max:255',
            'huisnummer' => 'nullable|string|max:10',
            'postcode' => 'nullable|string|max:10',
            'stad' => 'nullable|string|max:255',
            'status' => 'required|in:Actief,Inactief',
            'notities' => 'nullable|string',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Verwijder avatar uit validatedData (is een file object, niet een string)
        unset($validatedData['avatar']);

        // Handle avatar upload - ALLEEN als er een nieuwe is
        if ($request->hasFile('avatar') && $request->file('avatar')->isValid()) {
            \Log::info('🖼️ Avatar upload gedetecteerd', [
                'klant_id' => $klant->id,
                'file_original_name' => $request->file('avatar')->getClientOriginalName(),
                'file_size' => $request->file('avatar')->getSize()
            ]);
            
            // Verwijder oude avatar
            if ($klant->avatar_path && \Storage::disk('public')->exists($klant->avatar_path)) {
                \Storage::disk('public')->delete($klant->avatar_path);
                \Log::info('🗑️ Oude avatar verwijderd', ['path' => $klant->avatar_path]);
            }
            
            // Upload nieuwe avatar
            $avatarPath = $request->file('avatar')->store('avatars/klanten', 'public');
            $validatedData['avatar_path'] = $avatarPath;
            
            \Log::info('✅ Avatar opgeslagen in storage', [
                'path' => $avatarPath,
                'full_path' => storage_path('app/public/' . $avatarPath),
                'file_exists' => \Storage::disk('public')->exists($avatarPath)
            ]);
        }

        // Update via DB
        $updateData = array_merge($validatedData, ['updated_at' => now()]);
        
        \DB::table('klanten')
            ->where('id', $klant->id)
            ->update($updateData);
        
        // Verificatie
        $dbCheck = \DB::table('klanten')->where('id', $klant->id)->first(['avatar_path', 'voornaam', 'naam']);
        
        \Log::info('✅ Klant bijgewerkt in DB', [
            'klant_id' => $klant->id,
            'avatar_in_update' => isset($updateData['avatar_path']),
            'avatar_path_saved' => $updateData['avatar_path'] ?? 'geen',
            'db_verification' => [
                'avatar_path' => $dbCheck->avatar_path,
                'naam' => $dbCheck->voornaam . ' ' . $dbCheck->naam
            ]
        ]);

        return redirect()->route('klanten.show', $klant->id)->with('success', 'Klant succesvol bijgewerkt!');
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        \Log::info('🔍 Klanten Index START', [
            'auth_user_id' => auth()->id(),
            'auth_user_org_id' => auth()->user()->organisatie_id,
            'auth_user_role' => auth()->user()->role
        ]);

        // Filter op huidige organisatie - DIRECT in query
        $orgId = auth()->user()->organisatie_id;
        $query = Klant::where('organisatie_id', $orgId);

        \Log::info('📊 Klanten Query', [
            'filtering_on_org_id' => $orgId,
            'sql' => $query->toSql(),
            'bindings' => $query->getBindings()
        ]);

        $zoekterm = $request->input('zoek');
        
        // Zoeken
        if ($zoekterm) {
            $query->where(function($q) use ($zoekterm) {
                $q->where('naam', 'like', '%' . $zoekterm . '%')
                  ->orWhere('voornaam', 'like', '%' . $zoekterm . '%')
                  ->orWhere('email', 'like', '%' . $zoekterm . '%');
            });
        }
        
        $klanten = $query->orderBy('created_at', 'desc')->paginate(15);
        
        \Log::info('✅ Klanten gevonden', [
            'total' => $klanten->total(),
            'eerste_3' => $klanten->take(3)->map(fn($k) => [
                'id' => $k->id,
                'naam' => $k->naam,
                'organisatie_id' => $k->organisatie_id
            ])
        ]);
        
        return view('klanten.index', compact('klanten'));
    }

    public function create()
    {
        return view('klanten.create');
    }

    public function store(Request $request)
    {
        // Check of email uniek is binnen de organisatie voor klanten
        $existingKlantInOrg = User::where('email', $request->email)
            ->where('organisatie_id', auth()->user()->organisatie_id)
            ->where('role', 'klant')
            ->first();
        
        if ($existingKlantInOrg) {
            return back()->withErrors([
                'email' => 'Dit emailadres wordt al gebruikt door een klant binnen je organisatie.'
            ])->withInput();
        }

        $validated = $request->validate([
            'voornaam' => 'required|string|max:255',
            'naam' => 'required|string|max:255',
            'email' => 'required|email',
            'telefoon' => 'nullable|string|max:20',
            'geboortedatum' => 'nullable|date',
            'geslacht' => 'nullable|in:Man,Vrouw,Anders',
            'straat' => 'nullable|string|max:255',
            'huisnummer' => 'nullable|string|max:10',
            'postcode' => 'nullable|string|max:10',
            'stad' => 'nullable|string|max:100',
            'land' => 'nullable|string|max:100',
            'status' => 'nullable|in:Actief,Inactief',
            'notities' => 'nullable|string',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $validated['organisatie_id'] = auth()->user()->organisatie_id;
        $validated['name'] = $validated['voornaam'] . ' ' . $validated['naam'];
        $validated['role'] = 'klant';
        $validated['password'] = \Hash::make(\Illuminate\Support\Str::random(12));
        $validated['status'] = $validated['status'] ?? 'Actief';

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars/klanten', 'public');
            $validated['avatar_path'] = $path;
        }

        \Log::info('🔥 Creating klant with data:', $validated);
        
        $klant = Klant::create($validated);
        
        // Maak user account aan als email is opgegeven
        if (!empty($klant->email)) {
            $existingUser = \App\Models\User::where('email', $klant->email)->first();
            
            if (!$existingUser) {
                try {
                    $user = \App\Models\User::create([
                        'name' => $klant->naam,
                        'email' => $klant->email,
                        'password' => \Hash::make(\Str::random(16)),
                        'role' => 'klant',
                        'organisatie_id' => $klant->organisatie_id,
                        'status' => 'active',
                        'email_verified_at' => null,
                    ]);
                    
                    \Log::info('✅ User account aangemaakt voor klant', [
                        'klant_id' => $klant->id,
                        'user_id' => $user->id,
                        'email' => $klant->email
                    ]);
                    
                    // 🚀 AUTOMATISCHE WELCOME EMAIL
                    try {
                        $emailService = app(\App\Services\EmailIntegrationService::class);
                        $emailSent = $emailService->sendCustomerWelcomeEmail($klant);
                        
                        \Log::info('✅ Automatische welcome email verzonden', [
                            'klant_id' => $klant->id,
                            'success' => $emailSent
                        ]);
                    } catch (\Exception $emailError) {
                        \Log::error('❌ Failed to send automatic welcome email', [
                            'klant_id' => $klant->id,
                            'error' => $emailError->getMessage()
                        ]);
                    }
                    
                } catch (\Exception $e) {
                    \Log::error('❌ Fout bij aanmaken user account', [
                        'klant_id' => $klant->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }
        
        return redirect()->route('klanten.show', $klant->id)
            ->with('success', 'Klant succesvol aangemaakt' . (!empty($klant->email) ? ' en welcome email verzonden.' : '.'));
    }
    public function show(Klant $klant)
    {
        // FORCE FRESH DATA - haal altijd verse gegevens op
        $klant = $klant->fresh();
        
        // DEBUG: Check avatar_path
        \Log::info('🔍 Klant show - avatar check', [
            'klant_id' => $klant->id,
            'avatar_path_from_model' => $klant->avatar_path,
            'avatar_path_from_db' => \DB::table('klanten')->where('id', $klant->id)->value('avatar_path'),
            'db_columns' => \DB::select('SHOW COLUMNS FROM klanten WHERE Field = "avatar_path"')
        ]);
        
        // Laad gerelateerde data met correcte relatie namen
        $klant->load(['bikefits', 'inspanningstesten']);

        // Maak user beschikbaar voor de view
        $user = auth()->user();

        return view('klanten.show', compact('klant', 'user'));
    }
    public function sendInvitation(Request $request, Klant $klant)
    {
        // Zoek de user op basis van e-mail
        $user = User::where('email', $klant->email)->first();
        if ($user) {
            // Genereer een nieuw wachtwoord
            $password = Str::random(12);
            $user->password = Hash::make($password);
            $user->save();
            $loginUrl = url('/login');
            Mail::to($user->email)->send(new AccountCreatedMail($user->name, $user->email, $password, $loginUrl));
            return back()->with('success', 'Uitnodiging opnieuw verzonden!');
        } else {
            return back()->with('error', 'Er is geen gebruiker gekoppeld aan dit e-mailadres.');
        }
    }

    public function updateAvatar(Request $request, Klant $klant)
    {
        $request->validate([
            'avatar' => 'required|image|max:5120',
        ]);

        try {
            // Verwijder oude avatar indien aanwezig
            if ($klant->avatar_path && \Storage::disk('public')->exists($klant->avatar_path)) {
                \Storage::disk('public')->delete($klant->avatar_path);
                \Log::info('🗑️ Oude avatar verwijderd', ['path' => $klant->avatar_path]);
            }
            
            // Upload nieuwe avatar
            $path = $request->file('avatar')->store('avatars/klanten', 'public');
            
            // Update direct op database - geen cache
            \DB::table('klanten')
                ->where('id', $klant->id)
                ->update([
                    'avatar_path' => $path,
                    'updated_at' => now()
                ]);
            
            \Log::info('✅ Avatar bijgewerkt via DB (KlantController)', [
                'klant_id' => $klant->id,
                'nieuwe_path' => $path,
                'file_exists' => \Storage::disk('public')->exists($path)
            ]);
            
            // Clear model cache
            $klant = $klant->fresh();
        
            return redirect()->route('klanten.show', $klant->id)
                           ->with('success', 'Profielfoto succesvol bijgewerkt!');
            
        } catch (\Exception $e) {
            \Log::error('❌ Avatar update failed', [
                'error' => $e->getMessage(),
                'klant_id' => $klant->id
            ]);
            
            return redirect()->back()->with('error', 'Fout bij uploaden avatar: ' . $e->getMessage());
        }
    }

    /**
     * Show import form for klanten
     */
    public function showImport()
    {
        return view('klanten.import');
    }
    
    /**
     * Import klanten from Excel file
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240'
        ]);
        
        try {
            Excel::import(new KlantenImport, $request->file('file'));
            
            return redirect()->back()->with('success', 'Klanten succesvol geïmporteerd!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Import mislukt: ' . $e->getMessage());
        }
    }
    
    /**
     * Export all klanten to Excel
     */
    public function exportKlanten()
    {
        return Excel::download(new KlantenExport, 'klanten_export_' . date('Y-m-d') . '.xlsx');
    }
    
    /**
     * Download Excel template for klanten import
     */
    public function downloadTemplate()
    {
        return Excel::download(new KlantenTemplateExport, 'klanten_import_template.xlsx');
    }
    
    /**
     * Maak automatisch een user account aan voor een klant
     */
    private function createUserAccountForKlant(\App\Models\Klant $klant)
    {
        // Check of er al een user bestaat met dit email
        $existingUser = \App\Models\User::where('email', $klant->email)->first();
        
        if ($existingUser) {
            \Log::info('User bestaat al voor klant', [
                'klant_id' => $klant->id,
                'email' => $klant->email,
                'existing_user_id' => $existingUser->id
            ]);
            return $existingUser;
        }
        
        // Maak nieuwe user aan
        try {
            $user = \App\Models\User::create([
                'name' => $klant->naam,
                'email' => $klant->email,
                'password' => \Hash::make(\Str::random(16)), // Random wachtwoord
                'role' => 'klant',
                'organisatie_id' => $klant->organisatie_id,
                'status' => 'active',
                'email_verified_at' => null, // Klant moet email verifiëren
            ]);
            
            \Log::info('✅ User account aangemaakt voor geïmporteerde klant', [
                'klant_id' => $klant->id,
                'user_id' => $user->id,
                'email' => $klant->email
            ]);
            
            // Optioneel: stuur uitnodigingsmail (implementeer dit later indien gewenst)
            // $user->sendEmailVerificationNotification();
            
            return $user;
            
        } catch (\Exception $e) {
            \Log::error('❌ Fout bij aanmaken user account voor klant', [
                'klant_id' => $klant->id,
                'email' => $klant->email,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
}
