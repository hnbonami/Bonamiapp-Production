<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Klant;
use App\Models\User;
use App\Models\KlantDocument;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\AccountCreatedMail;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\KlantenImport;
use App\Exports\KlantenExport;
use App\Exports\KlantenTemplateExport;
use Illuminate\Support\Facades\Storage;

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
        
        // DEBUG: Check avatar in edit
        \Log::info('🔍 Klant edit - avatar check', [
            'klant_id' => $klant->id,
            'avatar_from_model' => $klant->avatar,
            'avatar_from_db' => \DB::table('klanten')->where('id', $klant->id)->value('avatar')
        ]);
        
        return view('klanten.edit', compact('klant'));
    }

    public function update(Request $request, Klant $klant)
    {
        \Log::info('🎯 KLANTCONTROLLER UPDATE', [
            'klant_id' => $klant->id,
            'has_avatar' => $request->hasFile('avatar')
        ]);
        
        // VALIDATIE MET ALLE JUISTE VELDEN + AVATAR
        $validated = $request->validate([
            'voornaam' => 'required|string|max:255',
            'naam' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'telefoonnummer' => 'nullable|string|max:20',
            'geboortedatum' => 'nullable|date',
            'geslacht' => 'required|in:Man,Vrouw,Anders',
            'status' => 'required|in:Actief,Inactief',
            'straatnaam' => 'nullable|string|max:255',
            'huisnummer' => 'nullable|string|max:20',
            'postcode' => 'nullable|string|max:10',
            'stad' => 'nullable|string|max:255',
            'sport' => 'nullable|string|max:255',
            'niveau' => 'nullable|string|max:255',
            'club' => 'nullable|string|max:255',
            'herkomst' => 'nullable|string|max:255',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        // Verwijder avatar uit validatedData (is een file object, niet een string)
        unset($validated['avatar']);

        // Avatar handling - productie-compatibel
        if ($request->hasFile('avatar')) {
            try {
                $file = $request->file('avatar');
                
                // Genereer unieke bestandsnaam
                $filename = 'avatar_' . $klant->id . '_' . time() . '.' . $file->getClientOriginalExtension();
                
                // Bepaal storage locatie op basis van omgeving
                if (app()->environment('production')) {
                    // PRODUCTIE: direct in public/uploads/avatars (één level boven private/)
                    $uploadPath = dirname(base_path()) . '/public/uploads/avatars';
                    
                    // Maak directory als deze niet bestaat
                    if (!file_exists($uploadPath)) {
                        mkdir($uploadPath, 0755, true);
                    }
                    
                    // Verwijder oude avatar
                    if ($klant->avatar_path) {
                        $oldPath = dirname(base_path()) . '/public/uploads/' . $klant->avatar_path;
                        if (file_exists($oldPath)) {
                            unlink($oldPath);
                        }
                    }
                    
                    // Upload nieuwe avatar
                    $file->move($uploadPath, $filename);
                    $validated['avatar_path'] = 'avatars/' . $filename;
                    
                    // Fix permissions voor productie (kritiek!)
                    $fullPath = $uploadPath . '/' . $filename;
                    chmod($fullPath, 0755);
                    
                    \Log::info('🔐 Permissions ingesteld', [
                        'file' => $fullPath,
                        'permissions' => '0755'
                    ]);
                    
                } else {
                    // LOKAAL: gebruik storage/app/public
                    if ($klant->avatar_path) {
                        Storage::disk('public')->delete($klant->avatar_path);
                    }
                    
                    $path = $file->storeAs('avatars', $filename, 'public');
                    $validated['avatar_path'] = $path;
                }
                
                \Log::info('Avatar succesvol geüpload', [
                    'klant_id' => $klant->id,
                    'filename' => $filename,
                    'environment' => app()->environment()
                ]);
                
            } catch (\Exception $e) {
                \Log::error('Avatar upload fout', [
                    'klant_id' => $klant->id,
                    'error' => $e->getMessage()
                ]);
                
                return redirect()->route('klanten.show', $klant)
                    ->with('error', 'Avatar upload mislukt. Probeer opnieuw.');
            }
        }

        // Update via DB
        $updateData = array_merge($validated, ['updated_at' => now()]);
        
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

        // FORCE FRESH DATA - haal de klant opnieuw op
        $klant = $klant->fresh();

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
        
        // Haal ALLE klanten op (geen paginering)
        $klanten = $query->orderBy('created_at', 'desc')->get();
        
        \Log::info('✅ Klanten gevonden', [
            'total' => $klanten->count(),
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
        // Haal alle actieve klanten op van huidige organisatie voor referral dropdown
        $availableReferringCustomers = Klant::where('organisatie_id', auth()->user()->organisatie_id)
            ->where('status', 'Actief')
            ->orderBy('naam')
            ->orderBy('voornaam')
            ->get()
            ->map(function($klant) {
                return [
                    'id' => $klant->id,
                    'name' => trim($klant->voornaam . ' ' . $klant->naam),
                    'email' => $klant->email ?? 'Geen email'
                ];
            });
        
        \Log::info('📋 Klanten create view geladen', [
            'available_referring_customers' => $availableReferringCustomers->count(),
            'organisatie_id' => auth()->user()->organisatie_id
        ]);
        
        return view('klanten.create', compact('availableReferringCustomers'));
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
            'email' => 'required|email|max:255|unique:klanten,email',
            'telefoonnummer' => 'nullable|string|max:20',
            'geboortedatum' => 'nullable|date',
            'geslacht' => 'required|in:Man,Vrouw,Anders',
            'status' => 'required|in:Actief,Inactief',
            'straatnaam' => 'nullable|string|max:255',
            'huisnummer' => 'nullable|string|max:20',
            'postcode' => 'nullable|string|max:10',
            'stad' => 'nullable|string|max:255',
            'sport' => 'nullable|string|max:255',
            'niveau' => 'nullable|string|max:255',
            'club' => 'nullable|string|max:255',
            'herkomst' => 'nullable|string|max:255',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            // 🔥 NIEUW: Referral velden
            'referral_source' => 'nullable|string|max:255',
            'referring_customer_id' => 'nullable|exists:klanten,id',
            'referral_notes' => 'nullable|string|max:1000',
        ]);

        \Log::info('🎯 KLANT STORE - Referral gegevens', [
            'referral_source' => $request->referral_source,
            'referring_customer_id' => $request->referring_customer_id,
            'referral_notes' => $request->referral_notes,
            'has_referring_customer' => $request->referring_customer_id ? 'YES' : 'NO'
        ]);

        // Verwijder avatar uit validated (is een file object)
        unset($validated['avatar']);

        // Voeg organisatie_id toe
        $validated['organisatie_id'] = auth()->user()->organisatie_id;

        // Handle avatar upload - EXACT ZELFDE ALS UPDATE
        $avatarPath = null;
        if ($request->hasFile('avatar') && $request->file('avatar')->isValid()) {
            \Log::info('🖼️ Avatar upload gedetecteerd bij CREATE', [
                'file_original_name' => $request->file('avatar')->getClientOriginalName(),
                'file_size' => $request->file('avatar')->getSize()
            ]);
            
            if (app()->environment('production')) {
                // PRODUCTIE: Upload direct naar public/uploads/avatars/klanten (één level boven private/)
                $uploadsPath = dirname(base_path()) . '/public/uploads/avatars/klanten';
                if (!file_exists($uploadsPath)) {
                    mkdir($uploadsPath, 0755, true);
                }
                
                $fileName = $request->file('avatar')->hashName();
                $request->file('avatar')->move($uploadsPath, $fileName);
                $avatarPath = 'avatars/klanten/' . $fileName;
                
                // Fix permissions voor productie (kritiek!)
                $fullPath = $uploadsPath . '/' . $fileName;
                chmod($fullPath, 0755);
                
                \Log::info('🔐 Permissions ingesteld', [
                    'file' => $fullPath,
                    'permissions' => '0755'
                ]);
            } else {
                // LOKAAL: Upload naar storage/app/public
                $avatarPath = $request->file('avatar')->store('avatars/klanten', 'public');
                
                \Log::info('✅ Avatar opgeslagen in storage bij CREATE', [
                    'path' => $avatarPath,
                    'full_path' => storage_path('app/public/' . $avatarPath),
                    'file_exists' => \Storage::disk('public')->exists($avatarPath)
                ]);
            }
            
            $validated['avatar_path'] = $avatarPath;
        }

        \Log::info('🔥 Creating klant with data:', array_merge($validated, ['has_avatar' => isset($validated['avatar_path'])]));
        
        // Maak klant aan
        $klant = Klant::create($validated);
        
        // Verificatie direct na create
        $dbCheck = \DB::table('klanten')->where('id', $klant->id)->first(['avatar_path', 'voornaam', 'naam']);
        
        \Log::info('✅ Klant aangemaakt in DB', [
            'klant_id' => $klant->id,
            'avatar_in_create' => isset($validated['avatar_path']),
            'avatar_path_saved' => $validated['avatar_path'] ?? 'geen',
            'db_verification' => [
                'avatar_path' => $dbCheck->avatar_path,
                'naam' => $dbCheck->voornaam . ' ' . $dbCheck->naam
            ]
        ]);
        
        // 🎉 REFERRAL SYSTEEM: Check of deze klant is doorverwezen
        $referralEmailSent = false;
        $referringCustomerName = null;
        
        if ($request->filled('referring_customer_id')) {
            try {
                $referringCustomer = Klant::find($request->referring_customer_id);
                
                if ($referringCustomer && $referringCustomer->email) {
                    \Log::info('🤝 REFERRAL DETECTED - Sending thank you email', [
                        'referring_customer_id' => $referringCustomer->id,
                        'referring_customer_email' => $referringCustomer->email,
                        'referring_customer_name' => $referringCustomer->voornaam . ' ' . $referringCustomer->naam,
                        'new_customer_id' => $klant->id,
                        'new_customer_name' => $klant->voornaam . ' ' . $klant->naam,
                        'referral_source' => $request->referral_source,
                        'referral_notes' => $request->referral_notes
                    ]);
                    
                    // Verstuur bedankingsmail via EmailIntegrationService
                    $emailService = app(\App\Services\EmailIntegrationService::class);
                    $emailSent = $emailService->sendReferralThankYouEmail($referringCustomer, $klant);
                    
                    if ($emailSent) {
                        $referralEmailSent = true;
                        $referringCustomerName = $referringCustomer->voornaam . ' ' . $referringCustomer->naam;
                        
                        \Log::info('✅ Referral thank you email sent successfully', [
                            'to' => $referringCustomer->email,
                            'for_referring' => $klant->voornaam . ' ' . $klant->naam
                        ]);
                    } else {
                        \Log::warning('⚠️ Referral thank you email failed to send', [
                            'to' => $referringCustomer->email
                        ]);
                    }
                } else {
                    \Log::warning('⚠️ Referring customer not found or has no email', [
                        'referring_customer_id' => $request->referring_customer_id,
                        'customer_found' => $referringCustomer ? 'yes' : 'no',
                        'has_email' => $referringCustomer && $referringCustomer->email ? 'yes' : 'no'
                    ]);
                }
            } catch (\Exception $referralError) {
                \Log::error('❌ Failed to send referral thank you email', [
                    'error' => $referralError->getMessage(),
                    'trace' => $referralError->getTraceAsString()
                ]);
            }
        } else {
            \Log::info('ℹ️ No referral detected for this customer', [
                'referral_source' => $request->referral_source ?? 'none',
                'referring_customer_id' => 'none'
            ]);
        }
        
        // Maak user account aan als email is opgegeven
        if (!empty($klant->email)) {
            $existingUser = \App\Models\User::where('email', $klant->email)->first();
            
            if (!$existingUser) {
                try {
                    // 🔥 FIX: Genereer temporary password EERST
                    $temporaryPassword = \Str::random(12);
                    
                    $user = \App\Models\User::create([
                        'name' => $klant->naam,
                        'email' => $klant->email,
                        'password' => \Hash::make($temporaryPassword),
                        'role' => 'klant',
                        'organisatie_id' => $klant->organisatie_id,
                        'status' => 'active',
                        'email_verified_at' => null,
                    ]);
                    
                    \Log::info('✅ User account aangemaakt voor klant', [
                        'klant_id' => $klant->id,
                        'user_id' => $user->id,
                        'email' => $klant->email,
                        'password_length' => strlen($temporaryPassword)
                    ]);
                    
                    // � FIX: Maak InvitationToken aan VOORDAT email wordt verstuurd!
                    \App\Models\InvitationToken::create([
                        'email' => $klant->email,
                        'token' => \Str::random(60),
                        'temporary_password' => $temporaryPassword,
                        'type' => 'klant',
                        'expires_at' => now()->addDays(7),
                        'created_by' => auth()->id()
                    ]);
                    
                    \Log::info('✅ InvitationToken aangemaakt voor klant', [
                        'email' => $klant->email,
                        'password_first_4' => substr($temporaryPassword, 0, 4),
                        'token_saved_before_email' => true
                    ]);
                    
                    // �🚀 AUTOMATISCHE WELCOME EMAIL (nu met wachtwoord in database!)
                    try {
                        $emailService = app(\App\Services\EmailIntegrationService::class);
                        // Geef temporary password mee als parameter voor extra zekerheid
                        $emailSent = $emailService->sendCustomerWelcomeEmail($klant, null, $temporaryPassword);
                        
                        \Log::info('✅ Automatische welcome email verzonden', [
                            'klant_id' => $klant->id,
                            'success' => $emailSent,
                            'password_provided_to_email_service' => true
                        ]);
                    } catch (\Exception $emailError) {
                        \Log::error('❌ Failed to send automatic welcome email', [
                            'klant_id' => $klant->id,
                            'error' => $emailError->getMessage()
                        ]);
                    }
                    
                    // 🎉 WELKOMST WIDGET: Maak automatisch een welkomst widget aan voor nieuwe klant
                    try {
                        \App\Http\Controllers\DashboardController::createCustomerWelcomeWidget(
                            $klant->organisatie_id,
                            $user->id,
                            $klant->voornaam ?? $klant->naam
                        );
                        
                        \Log::info('✅ Welkomst widget aangemaakt voor nieuwe klant', [
                            'klant_id' => $klant->id,
                            'user_id' => $user->id
                        ]);
                    } catch (\Exception $widgetError) {
                        \Log::error('❌ Failed to create welcome widget for customer', [
                            'klant_id' => $klant->id,
                            'error' => $widgetError->getMessage()
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
        
        // FORCE FRESH DATA
        $klant = $klant->fresh();
        
        // Bouw success message op basis van wat er is gebeurd
        $successMessage = 'Klant succesvol aangemaakt';
        
        if (!empty($klant->email)) {
            $successMessage .= ' en welcome email verzonden';
        }
        
        if ($referralEmailSent && $referringCustomerName) {
            $successMessage .= '. Bedankingsmail verzonden naar ' . $referringCustomerName . ' voor de doorverwijzing';
        }
        
        $successMessage .= '.';
        
        return redirect()->route('klanten.show', $klant->id)
            ->with('success', $successMessage);
    }
    public function show(Klant $klant)
    {
        // FORCE FRESH DATA - haal altijd verse gegevens op
        $klant = $klant->fresh();
        
        // Avatar path ophalen - SIMPEL en direct
        $avatarPath = $klant->avatar_path;
        $cacheKey = $klant->updated_at ? $klant->updated_at->timestamp : time();
        
        // Check of avatar bestaat (zonder Storage disk in productie)
        $avatarExists = false;
        if ($avatarPath) {
            if (app()->environment('production')) {
                // PRODUCTIE: check direct in filesystem (één level boven private/)
                $fullPath = dirname(base_path()) . '/public/uploads/' . $avatarPath;
                $avatarExists = file_exists($fullPath);
                
                \Log::info('🔍 Avatar check in show()', [
                    'avatar_path' => $avatarPath,
                    'full_path' => $fullPath,
                    'file_exists' => $avatarExists
                ]);
            } else {
                // LOKAAL: gebruik Storage disk
                $avatarExists = \Storage::disk('public')->exists($avatarPath);
            }
        }
        
        \Log::info('🔍 Klant show - avatar check', [
            'klant_id' => $klant->id,
            'avatar_path' => $avatarPath,
            'avatar_exists' => $avatarExists,
            'cache_key' => $cacheKey,
            'environment' => app()->environment(),
        ]);
        
        // Laad gerelateerde data met correcte relatie namen
        $klant->load(['bikefits', 'inspanningstesten']);

        // Maak user beschikbaar voor de view
        $user = auth()->user();

        return view('klanten.show', compact('klant', 'user', 'cacheKey'));
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
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            // Verwijder oude avatar als deze bestaat
            if ($klant->avatar && Storage::disk('public')->exists($klant->avatar)) {
                Storage::disk('public')->delete($klant->avatar);
            }

            // Upload nieuwe avatar naar storage/app/public/avatars/klanten
            $path = $request->file('avatar')->store('avatars/klanten', 'public');

            // Update klant met nieuwe avatar path
            $klant->update([
                'avatar' => $path,
                'avatar_path' => $path, // Voor backward compatibility
            ]);

            \Log::info('✅ Avatar succesvol geüpload', [
                'klant_id' => $klant->id,
                'path' => $path,
                'url' => Storage::disk('public')->url($path)
            ]);

            return redirect()->back()->with('success', 'Profielfoto succesvol bijgewerkt.');
        } catch (\Exception $e) {
            \Log::error('❌ Avatar upload mislukt', [
                'klant_id' => $klant->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Fout bij uploaden profielfoto: ' . $e->getMessage());
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
    
    public function storeDocument(Request $request, Klant $klant)
    {
        $validated = $request->validate([
            'document' => 'required|file|max:10240', // max 10MB
            'naam' => 'nullable|string|max:255',
            'beschrijving' => 'nullable|string|max:500',
            'toegang' => 'required|in:alle_medewerkers,alleen_mezelf,klant,iedereen',
        ]);

        try {
            $file = $request->file('document');
            
            // Genereer unieke bestandsnaam
            $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            
            // Bepaal storage locatie op basis van omgeving
            if (app()->environment('production')) {
                // PRODUCTIE: direct in public/uploads/documenten (één level boven private/)
                $uploadPath = dirname(base_path()) . '/public/uploads/documenten';
                
                // Maak directory als deze niet bestaat
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }
                
                // Upload document
                $file->move($uploadPath, $filename);
                $bestandspad = 'documenten/' . $filename;
                
            } else {
                // LOKAAL: gebruik storage/app/public
                $bestandspad = $file->storeAs('documenten', $filename, 'public');
            }

            // Sla document metadata op in database
            KlantDocument::create([
                'klant_id' => $klant->id,
                'user_id' => auth()->id(),
                'titel' => $validated['naam'] ?? $file->getClientOriginalName(),
                'beschrijving' => $validated['beschrijving'],
                'bestandspad' => $bestandspad,
                'bestandsnaam' => $file->getClientOriginalName(),
                'bestandstype' => $file->getClientOriginalExtension(),
                'bestandsgrootte' => $file->getSize(),
                'upload_datum' => now(),
                'toegang' => $validated['toegang'],
            ]);

            \Log::info('Document succesvol geüpload', [
                'klant_id' => $klant->id,
                'filename' => $filename,
                'environment' => app()->environment()
            ]);

            return redirect()->route('klanten.show', $klant)
                ->with('success', 'Document succesvol geüpload.');
                
        } catch (\Exception $e) {
            \Log::error('Document upload fout', [
                'klant_id' => $klant->id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->route('klanten.show', $klant)
                ->with('error', 'Document upload mislukt. Probeer opnieuw.');
        }
    }

    public function downloadDocument(Klant $klant, KlantDocument $document)
    {
        try {
            // Bepaal bestandspad op basis van omgeving
            if (app()->environment('production')) {
                $filePath = dirname(base_path()) . '/public/uploads/' . $document->bestandspad;
            } else {
                $filePath = storage_path('app/public/' . $document->bestandspad);
            }

            // Controleer of bestand bestaat
            if (!file_exists($filePath)) {
                \Log::error('Document niet gevonden', [
                    'document_id' => $document->id,
                    'path' => $filePath
                ]);
                
                return redirect()->route('klanten.show', $klant)
                    ->with('error', 'Document niet gevonden.');
            }

            return response()->download($filePath, $document->bestandsnaam);
            
        } catch (\Exception $e) {
            \Log::error('Document download fout', [
                'document_id' => $document->id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->route('klanten.show', $klant)
                ->with('error', 'Download mislukt.');
        }
    }
}
