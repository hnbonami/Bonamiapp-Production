<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\InvitationToken;
use App\Services\EmailIntegrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MedewerkerController extends Controller
{
    /**
     * Display a listing of medewerkers
     */
    public function index(Request $request)
    {
        // Check admin toegang - admin of organisatie_admin kunnen medewerkers beheren
        if (!in_array(auth()->user()->role, ['admin', 'organisatie_admin', 'superadmin'])) {
            abort(403, 'Geen toegang. Alleen administrators kunnen medewerkers beheren.');
        }
        
        \Log::info('🔍 Medewerkers index aangeroepen', [
            'user_id' => auth()->id(),
            'organisatie_id' => auth()->user()->organisatie_id
        ]);
        
        $userOrganisatieId = auth()->user()->organisatie_id;
        
        // Zoekfunctionaliteit
        $zoekterm = $request->input('zoek');
        
        if ($zoekterm) {
            \Log::info('🔎 Zoeken naar medewerkers', ['zoekterm' => $zoekterm]);
            
            // 🔒 ORGANISATIE FILTER: Alleen medewerkers van eigen organisatie
            $medewerkers = User::where('role', '!=', 'klant')
                ->where('organisatie_id', $userOrganisatieId)
                ->where(function($query) use ($zoekterm) {
                    $query->where('name', 'like', '%' . $zoekterm . '%')
                          ->orWhere('voornaam', 'like', '%' . $zoekterm . '%')
                          ->orWhere('email', 'like', '%' . $zoekterm . '%');
                })
                ->orderBy('created_at', 'desc')
                ->get();
            
            \Log::info('✅ Zoekresultaten gevonden', ['aantal' => $medewerkers->count()]);
        } else {
            // 🔒 ORGANISATIE FILTER: Haal alleen medewerkers van eigen organisatie op
            $medewerkers = User::where('role', '!=', 'klant')
                ->where('organisatie_id', $userOrganisatieId)
                ->orderBy('created_at', 'desc')
                ->get();
        }
        
        return view('medewerkers.index', compact('medewerkers'));
    }

    /**
     * Show the form for creating a new medewerker
     */
    public function create()
    {
        // Check admin toegang - admin of organisatie_admin kunnen medewerkers beheren
        if (!in_array(auth()->user()->role, ['admin', 'organisatie_admin', 'superadmin'])) {
            abort(403, 'Geen toegang. Alleen administrators kunnen medewerkers beheren.');
        }
        
        return view('medewerkers.create');
    }

    /**
     * Store a newly created medewerker in storage
     */
    public function store(Request $request)
    {
        // Check admin toegang - admin of organisatie_admin kunnen medewerkers beheren
        if (!in_array(auth()->user()->role, ['admin', 'organisatie_admin', 'superadmin'])) {
            abort(403, 'Geen toegang. Alleen administrators kunnen medewerkers beheren.');
        }
        
        \Log::info('🚨 MedewerkerController@store CALLED', [
            'all_input' => $request->all(),
            'geslacht_raw' => $request->input('geslacht'),
            'user_org_id' => auth()->user()->organisatie_id
        ]);

        try {
            // BELANGRIJK: form stuurt achternaam, niet naam!
            $validated = $request->validate([
                'voornaam' => 'required|string|max:255',
                'achternaam' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'telefoonnummer' => 'nullable|string|max:20',
                'geboortedatum' => 'nullable|date',
                'geslacht' => 'nullable|in:Man,Vrouw,Anders',
                'straatnaam' => 'nullable|string|max:255',
                'huisnummer' => 'nullable|string|max:20',
                'postcode' => 'nullable|string|max:10',
                'stad' => 'nullable|string|max:255',
                'functie' => 'nullable|string|max:255',
                'rol' => 'nullable|string|in:admin,medewerker,stagiair,organisatie_admin',
                'startdatum' => 'nullable|date',
                'contract_type' => 'nullable|in:Vast,Tijdelijk,Freelance,Stage',
                'status' => 'nullable|string|in:Actief,Inactief,Verlof,Ziek',
                'bikefit' => 'nullable|boolean',
                'inspanningstest' => 'nullable|boolean',
                'upload_documenten' => 'nullable|boolean',
                'notities' => 'nullable|string',
                'avatar' => 'nullable|image|max:2048'
            ]);

            \Log::info('✅ Validation passed', [
                'validated' => $validated,
                'geslacht_in_validated' => isset($validated['geslacht']) ? $validated['geslacht'] : 'NIET AANWEZIG'
            ]);

            // Check of email al bestaat binnen de organisatie (voor alle roles)
            $existingUserInOrg = User::where('email', $validated['email'])
                ->where('organisatie_id', auth()->user()->organisatie_id)
                ->first();
            
            if ($existingUserInOrg) {
                \Log::warning('⚠️ Duplicate email found in organization', [
                    'email' => $validated['email'],
                    'existing_user_id' => $existingUserInOrg->id,
                    'existing_role' => $existingUserInOrg->role
                ]);
                
                return back()
                    ->withErrors(['email' => 'Dit emailadres wordt al gebruikt binnen je organisatie door ' . $existingUserInOrg->name . ' (' . $existingUserInOrg->role . ').'])
                    ->withInput();
            }
            
            // Extra check: bestaat email in andere organisatie? (waarschuwing maar geen blokkade)
            $existingUserOtherOrg = User::where('email', $validated['email'])
                ->where('organisatie_id', '!=', auth()->user()->organisatie_id)
                ->first();
            
            if ($existingUserOtherOrg) {
                \Log::info('ℹ️ Email exists in other organization', [
                    'email' => $validated['email'],
                    'other_org_id' => $existingUserOtherOrg->organisatie_id
                ]);
            }

            // Handle avatar upload - EXACT ZELFDE ALS KLANTCONTROLLER
            $avatarPath = null;
            if ($request->hasFile('avatar') && $request->file('avatar')->isValid()) {
                \Log::info('🖼️ Avatar upload gedetecteerd bij CREATE medewerker', [
                    'file_original_name' => $request->file('avatar')->getClientOriginalName(),
                    'file_size' => $request->file('avatar')->getSize()
                ]);
                
                if (app()->environment('production')) {
                    // PRODUCTIE: Upload direct naar httpd.www/uploads/avatars/medewerkers
                    $uploadsPath = base_path('../httpd.www/uploads/avatars/medewerkers');
                    if (!file_exists($uploadsPath)) {
                        mkdir($uploadsPath, 0755, true);
                    }
                    
                    $fileName = $request->file('avatar')->hashName();
                    $request->file('avatar')->move($uploadsPath, $fileName);
                    $avatarPath = 'avatars/medewerkers/' . $fileName;
                    
                    \Log::info('✅ Avatar opgeslagen in httpd.www/uploads bij CREATE medewerker', [
                        'path' => $avatarPath,
                        'full_path' => $uploadsPath . '/' . $fileName,
                        'file_exists' => file_exists($uploadsPath . '/' . $fileName)
                    ]);
                } else {
                    // LOKAAL: Upload naar storage/app/public
                    $avatarPath = $request->file('avatar')->store('avatars/medewerkers', 'public');
                    
                    \Log::info('✅ Avatar opgeslagen in storage bij CREATE medewerker', [
                        'path' => $avatarPath,
                        'full_path' => storage_path('app/public/' . $avatarPath),
                        'file_exists' => \Storage::disk('public')->exists($avatarPath)
                    ]);
                }
            }

            // Maak een nieuwe medewerker (User) aan met een tijdelijk wachtwoord
            $temporaryPassword = \Str::random(12);
            
            $medewerker = User::create([
                'name' => $validated['voornaam'] . ' ' . $validated['achternaam'],
                'voornaam' => $validated['voornaam'],
                'achternaam' => $validated['achternaam'],
                'email' => $validated['email'],
                'password' => Hash::make($temporaryPassword),
                'role' => $validated['rol'] ?? 'medewerker',
                'organisatie_id' => auth()->user()->organisatie_id,
                'telefoonnummer' => $validated['telefoonnummer'] ?? null,
                'geboortedatum' => $validated['geboortedatum'] ?? null,
                'geslacht' => $validated['geslacht'] ?? null,
                'straatnaam' => $validated['straatnaam'] ?? null,
                'huisnummer' => $validated['huisnummer'] ?? null,
                'postcode' => $validated['postcode'] ?? null,
                'stad' => $validated['stad'] ?? null,
                'functie' => $validated['functie'] ?? null,
                'startdatum' => $validated['startdatum'] ?? null,
                'contract_type' => $validated['contract_type'] ?? null,
                'status' => $validated['status'] ?? 'Actief',
                'bikefit' => $request->has('bikefit') ? 1 : 0,
                'inspanningstest' => $request->has('inspanningstest') ? 1 : 0,
                'upload_documenten' => $request->has('upload_documenten') ? 1 : 0,
                'notities' => $validated['notities'] ?? null,
                'avatar_path' => $avatarPath,
            ]);
            
            // Refresh het model om zeker te zijn dat we de database waarde hebben
            $medewerker->refresh();
            
            \Log::info('✅ Medewerker aangemaakt', [
                'user_id' => $medewerker->id,
                'email' => $medewerker->email,
                'organisatie_id' => $medewerker->organisatie_id,
                'geslacht_in_validated' => $validated['geslacht'] ?? 'NULL in validated',
                'geslacht_in_db' => $medewerker->geslacht ?? 'NULL in database',
                'telefoonnummer' => $medewerker->telefoonnummer
            ]);

            // Maak InvitationToken aan met wachtwoord (zoals bij klanten!)
            InvitationToken::create([
                'email' => $medewerker->email,
                'token' => Str::random(60),
                'temporary_password' => $temporaryPassword,
                'type' => 'medewerker',
                'expires_at' => now()->addDays(7),
                'created_by' => auth()->id()
            ]);

            \Log::info('✅ InvitationToken aangemaakt voor medewerker', [
                'email' => $medewerker->email
            ]);

            // AUTOMATISCH welkomstmail versturen (net zoals bij klanten)
            try {
                $emailService = app(EmailIntegrationService::class);
                $emailSent = $emailService->sendEmployeeWelcomeEmail($medewerker);
                
                if ($emailSent) {
                    \Log::info('✅ Automatische welkomstmail verstuurd', [
                        'medewerker_id' => $medewerker->id,
                        'email' => $medewerker->email
                    ]);
                    
                    return redirect()->route('medewerkers.show', $medewerker->id)
                        ->with('success', 'Medewerker succesvol toegevoegd en welkomstmail verstuurd naar ' . $medewerker->email . '!');
                } else {
                    \Log::warning('⚠️ Medewerker aangemaakt maar welkomstmail kon niet worden verstuurd');
                    
                    return redirect()->route('medewerkers.show', $medewerker->id)
                        ->with('warning', 'Medewerker aangemaakt, maar welkomstmail kon niet worden verstuurd. Gebruik de uitnodiging knop.');
                }
            } catch (\Exception $emailError) {
                \Log::error('❌ Fout bij versturen welkomstmail', [
                    'medewerker_id' => $medewerker->id,
                    'error' => $emailError->getMessage()
                ]);
                
                return redirect()->route('medewerkers.show', $medewerker->id)
                    ->with('warning', 'Medewerker aangemaakt, maar welkomstmail kon niet worden verstuurd: ' . $emailError->getMessage());
            }
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::warning('⚠️ Validation failed', ['errors' => $e->errors()]);
            throw $e;
        } catch (\Exception $e) {
            \Log::error('❌ Fout bij aanmaken medewerker', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withInput()->with('error', 'Fout: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified medewerker
     */
    public function show(User $medewerker)
    {
        if ($medewerker->role === 'klant') {
            abort(404);
        }
        
        // 🔒 Check of medewerker bij dezelfde organisatie hoort
        if ($medewerker->organisatie_id !== auth()->user()->organisatie_id) {
            abort(403, 'Geen toegang tot deze medewerker.');
        }
        
        return view('medewerkers.show', compact('medewerker'));
    }

    /**
     * Show the form for editing the specified medewerker
     */
    public function edit(User $medewerker)
    {
        if ($medewerker->role === 'klant') {
            abort(404);
        }
        
        // 🔒 Check of medewerker bij dezelfde organisatie hoort
        if ($medewerker->organisatie_id !== auth()->user()->organisatie_id) {
            abort(403, 'Geen toegang tot deze medewerker.');
        }
        
        return view('medewerkers.edit', compact('medewerker'));
    }

    /**
     * Update the specified medewerker in storage
     */
    public function update(Request $request, User $medewerker)
    {
        if ($medewerker->role === 'klant') {
            abort(404);
        }

        \Log::info('🔄 MedewerkerController@update CALLED', [
            'medewerker_id' => $medewerker->id,
            'current_email' => $medewerker->email,
            'new_email' => $request->email,
            'geslacht_raw' => $request->input('geslacht'),
            'current_geslacht' => $medewerker->geslacht,
            'organisatie_id' => auth()->user()->organisatie_id
        ]);

        $validated = $request->validate([
            'voornaam' => 'required|string|max:255',
            'achternaam' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'telefoonnummer' => 'nullable|string|max:20',
            'geboortedatum' => 'nullable|date',
            'geslacht' => 'nullable|in:Man,Vrouw,Anders',
            'straatnaam' => 'nullable|string|max:255',
            'huisnummer' => 'nullable|string|max:20',
            'postcode' => 'nullable|string|max:10',
            'stad' => 'nullable|string|max:255',
            'functie' => 'nullable|string|max:255',
            'rol' => 'nullable|in:admin,medewerker,stagiair,organisatie_admin',
            'startdatum' => 'nullable|date',
            'contract_type' => 'nullable|in:Vast,Tijdelijk,Freelance,Stage',
            'status' => 'required|in:Actief,Inactief,Verlof,Ziek',
            'bikefit' => 'nullable|boolean',
            'inspanningstest' => 'nullable|boolean',
            'upload_documenten' => 'nullable|boolean',
            'notities' => 'nullable|string',
            'avatar' => 'nullable|image|max:2048'
        ]);

        // Check of email al bestaat binnen de organisatie (BEHALVE voor deze gebruiker)
        $existingUserInOrg = User::where('email', $validated['email'])
            ->where('organisatie_id', auth()->user()->organisatie_id)
            ->where('id', '!=', $medewerker->id) // Exclusief de huidige gebruiker!
            ->first();
        
        if ($existingUserInOrg) {
            \Log::warning('⚠️ Duplicate email found during update', [
                'email' => $validated['email'],
                'existing_user_id' => $existingUserInOrg->id,
                'current_user_id' => $medewerker->id
            ]);
            
            return back()
                ->withErrors(['email' => 'Dit emailadres wordt al gebruikt binnen je organisatie door ' . $existingUserInOrg->name . '.'])
                ->withInput();
        }

        try {
            // Handle avatar upload - EXACT ZELFDE ALS KLANTCONTROLLER
            $avatarPath = $medewerker->avatar_path;
            if ($request->hasFile('avatar') && $request->file('avatar')->isValid()) {
                \Log::info('🖼️ Avatar upload gedetecteerd bij UPDATE medewerker', [
                    'medewerker_id' => $medewerker->id,
                    'file_original_name' => $request->file('avatar')->getClientOriginalName(),
                    'file_size' => $request->file('avatar')->getSize()
                ]);
                
                if (app()->environment('production')) {
                    // PRODUCTIE: Upload direct naar httpd.www/uploads/avatars/medewerkers
                    $uploadsPath = base_path('../httpd.www/uploads/avatars/medewerkers');
                    if (!file_exists($uploadsPath)) {
                        mkdir($uploadsPath, 0755, true);
                    }
                    
                    // Verwijder oude avatar
                    if ($avatarPath) {
                        $oldPath = base_path('../httpd.www/uploads/' . $avatarPath);
                        if (file_exists($oldPath)) {
                            unlink($oldPath);
                            \Log::info('🗑️ Oude avatar verwijderd', ['path' => $avatarPath]);
                        }
                    }
                    
                    $fileName = $request->file('avatar')->hashName();
                    $request->file('avatar')->move($uploadsPath, $fileName);
                    $avatarPath = 'avatars/medewerkers/' . $fileName;
                    
                    \Log::info('✅ Avatar opgeslagen in httpd.www/uploads bij UPDATE medewerker', [
                        'path' => $avatarPath,
                        'full_path' => $uploadsPath . '/' . $fileName,
                        'file_exists' => file_exists($uploadsPath . '/' . $fileName)
                    ]);
                } else {
                    // LOKAAL: Upload naar storage/app/public
                    // Verwijder oude avatar
                    if ($avatarPath && \Storage::disk('public')->exists($avatarPath)) {
                        \Storage::disk('public')->delete($avatarPath);
                        \Log::info('🗑️ Oude avatar verwijderd', ['path' => $avatarPath]);
                    }
                    
                    $avatarPath = $request->file('avatar')->store('avatars/medewerkers', 'public');
                    
                    \Log::info('✅ Avatar opgeslagen in storage bij UPDATE medewerker', [
                        'path' => $avatarPath,
                        'full_path' => storage_path('app/public/' . $avatarPath),
                        'file_exists' => \Storage::disk('public')->exists($avatarPath)
                    ]);
                }
            }

            $updateData = [
                'name' => $validated['voornaam'] . ' ' . $validated['achternaam'],
                'voornaam' => $validated['voornaam'],
                'achternaam' => $validated['achternaam'],
                'email' => $validated['email'],
                'role' => $validated['rol'] ?? $medewerker->role,
                'telefoonnummer' => $validated['telefoonnummer'] ?? null,
                'geboortedatum' => $validated['geboortedatum'] ?? null,
                'geslacht' => $validated['geslacht'] ?? null,
                'straatnaam' => $validated['straatnaam'] ?? null,
                'huisnummer' => $validated['huisnummer'] ?? null,
                'postcode' => $validated['postcode'] ?? null,
                'stad' => $validated['stad'] ?? null,
                'functie' => $validated['functie'] ?? null,
                'startdatum' => $validated['startdatum'] ?? null,
                'contract_type' => $validated['contract_type'] ?? null,
                'status' => $validated['status'] ?? $medewerker->status,
                'bikefit' => $request->has('bikefit') ? 1 : 0,
                'inspanningstest' => $request->has('inspanningstest') ? 1 : 0,
                'upload_documenten' => $request->has('upload_documenten') ? 1 : 0,
                'notities' => $validated['notities'] ?? null,
                'avatar_path' => $avatarPath,
            ];

            \Log::info('🔍 Data voor update', [
                'geslacht_in_updateData' => $updateData['geslacht'] ?? 'NULL'
            ]);

            $medewerker->update($updateData);
            
            // Refresh model om database waarde te controleren
            $medewerker->refresh();

            \Log::info('✅ Employee updated successfully', [
                'user_id' => $medewerker->id,
                'email' => $medewerker->email,
                'role' => $medewerker->role,
                'geslacht_na_update' => $medewerker->geslacht ?? 'NULL in database'
            ]);

            return redirect()->route('medewerkers.index')
                           ->with('success', 'Medewerker succesvol bijgewerkt.');

        } catch (\Exception $e) {
            \Log::error('❌ Failed to update employee: ' . $e->getMessage(), [
                'user_id' => $medewerker->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Er is een fout opgetreden bij het bijwerken van de medewerker: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified medewerker from storage
     */
    public function destroy(User $medewerker)
    {
        if ($medewerker->role === 'klant') {
            abort(404);
        }
        
        // 🔒 Check of medewerker bij dezelfde organisatie hoort
        if ($medewerker->organisatie_id !== auth()->user()->organisatie_id) {
            abort(403, 'Geen toegang tot deze medewerker.');
        }

        try {
            $email = $medewerker->email;
            $medewerker->delete();

            \Log::info('✅ Employee deleted successfully', [
                'email' => $email
            ]);

            return redirect()->route('medewerkers.index')
                           ->with('success', 'Medewerker succesvol verwijderd.');

        } catch (\Exception $e) {
            \Log::error('❌ Failed to delete employee: ' . $e->getMessage(), [
                'user_id' => $medewerker->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                           ->with('error', 'Er is een fout opgetreden bij het verwijderen van de medewerker: ' . $e->getMessage());
        }
    }

    /**
     * Send invitation email to medewerker (handmatig via knop)
     */
    public function sendInvitation(User $medewerker)
    {
        if ($medewerker->role === 'klant') {
            abort(404);
        }
        
        // 🔒 Check of medewerker bij dezelfde organisatie hoort
        if ($medewerker->organisatie_id !== auth()->user()->organisatie_id) {
            abort(403, 'Geen toegang tot deze medewerker.');
        }

        try {
            \Log::info('� Handmatige uitnodiging versturen naar medewerker', [
                'medewerker_id' => $medewerker->id,
                'email' => $medewerker->email,
                'name' => $medewerker->name
            ]);

            // BELANGRIJK: Genereer NIEUW uniek wachtwoord met timestamp
            $temporaryPassword = Str::random(12);
            $hashedPassword = Hash::make($temporaryPassword);
            
            // Update medewerker wachtwoord in database
            $medewerker->update([
                'password' => $hashedPassword
            ]);

            \Log::info('🔑 Nieuw UNIEK wachtwoord gegenereerd', [
                'medewerker_id' => $medewerker->id,
                'password_first_4' => substr($temporaryPassword, 0, 4),
                'timestamp' => now()->toDateTimeString()
            ]);

            // VERWIJDER oude token eerst
            InvitationToken::where('email', $medewerker->email)
                ->where('type', 'medewerker')
                ->delete();

            // Maak NIEUWE token aan
            $invitationToken = InvitationToken::create([
                'email' => $medewerker->email,
                'type' => 'medewerker',
                'token' => Str::random(60),
                'temporary_password' => $temporaryPassword,
                'expires_at' => now()->addDays(7),
                'created_by' => auth()->id()
            ]);

            \Log::info('✅ InvitationToken bijgewerkt met nieuw wachtwoord', [
                'email' => $medewerker->email
            ]);

            // Stuur welkomstmail via EmailIntegrationService (haalt wachtwoord uit InvitationToken)
            $emailService = app(EmailIntegrationService::class);
            $emailSent = $emailService->sendEmployeeWelcomeEmail($medewerker);
            
            if ($emailSent) {
                \Log::info('✅ Uitnodigingsmail succesvol verstuurd', [
                    'email' => $medewerker->email
                ]);
                
                return redirect()->back()
                    ->with('success', 'Uitnodiging met nieuwe inloggegevens verstuurd naar ' . $medewerker->email . '! 🎉');
            } else {
                \Log::warning('⚠️ Email kon niet worden verstuurd', [
                    'email' => $medewerker->email
                ]);
                
                return redirect()->back()
                    ->with('error', 'Er is een probleem opgetreden bij het versturen van de uitnodiging. Check de email configuratie.');
            }

        } catch (\Exception $e) {
            \Log::error('❌ Fout bij versturen uitnodiging', [
                'medewerker_id' => $medewerker->id,
                'email' => $medewerker->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Fout bij versturen uitnodiging: ' . $e->getMessage());
        }
    }
}