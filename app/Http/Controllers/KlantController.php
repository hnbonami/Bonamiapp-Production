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
            'email' => 'nullable|email|max:255',
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

        // Handle avatar upload - ALLEEN als er een nieuwe is
        if ($request->hasFile('avatar') && $request->file('avatar')->isValid()) {
            \Log::info('🖼️ Avatar upload gedetecteerd', [
                'klant_id' => $klant->id,
                'file_original_name' => $request->file('avatar')->getClientOriginalName(),
                'file_size' => $request->file('avatar')->getSize()
            ]);
            
            // Verwijder oude avatar
            if ($klant->avatar && \Storage::disk('public')->exists($klant->avatar)) {
                \Storage::disk('public')->delete($klant->avatar);
                \Log::info('🗑️ Oude avatar verwijderd', ['path' => $klant->avatar]);
            }
            
            // Upload nieuwe avatar naar avatars/klanten subdirectory
            $avatarPath = $request->file('avatar')->store('avatars/klanten', 'public');
            $validated['avatar'] = $avatarPath;
            
            \Log::info('✅ Avatar opgeslagen in storage', [
                'path' => $avatarPath,
                'full_path' => storage_path('app/public/' . $avatarPath),
                'file_exists' => \Storage::disk('public')->exists($avatarPath)
            ]);
        }

        // Update via DB
        $updateData = array_merge($validated, ['updated_at' => now()]);
        
        \DB::table('klanten')
            ->where('id', $klant->id)
            ->update($updateData);
        
        // Verificatie
        $dbCheck = \DB::table('klanten')->where('id', $klant->id)->first(['avatar', 'voornaam', 'naam']);
        
        \Log::info('✅ Klant bijgewerkt in DB', [
            'klant_id' => $klant->id,
            'avatar_in_update' => isset($updateData['avatar']),
            'avatar_path_saved' => $updateData['avatar'] ?? 'geen',
            'db_verification' => [
                'avatar' => $dbCheck->avatar,
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
            'email' => 'nullable|email|max:255|unique:klanten,email',
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
            
            // Upload naar avatars/klanten subdirectory
            $avatarPath = $request->file('avatar')->store('avatars/klanten', 'public');
            $validated['avatar'] = $avatarPath;
            
            \Log::info('✅ Avatar opgeslagen in storage bij CREATE', [
                'path' => $avatarPath,
                'full_path' => storage_path('app/public/' . $avatarPath),
                'file_exists' => \Storage::disk('public')->exists($avatarPath)
            ]);
        }

        \Log::info('🔥 Creating klant with data:', array_merge($validated, ['has_avatar' => isset($validated['avatar'])]));
        
        // Maak klant aan
        $klant = Klant::create($validated);
        
        // Verificatie direct na create
        $dbCheck = \DB::table('klanten')->where('id', $klant->id)->first(['avatar', 'voornaam', 'naam']);
        
        \Log::info('✅ Klant aangemaakt in DB', [
            'klant_id' => $klant->id,
            'avatar_in_create' => isset($validated['avatar']),
            'avatar_path_saved' => $validated['avatar'] ?? 'geen',
            'db_verification' => [
                'avatar' => $dbCheck->avatar,
                'naam' => $dbCheck->voornaam . ' ' . $dbCheck->naam
            ]
        ]);
        
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
        
        // FORCE FRESH DATA
        $klant = $klant->fresh();
        
        return redirect()->route('klanten.show', $klant->id)
            ->with('success', 'Klant succesvol aangemaakt' . (!empty($klant->email) ? ' en welcome email verzonden.' : '.'));
    }
    public function show(Klant $klant)
    {
        // FORCE FRESH DATA - haal altijd verse gegevens op
        $klant = $klant->fresh();
        
        // Avatar path ophalen - SIMPEL en direct
        $avatarPath = $klant->avatar;
        $cacheKey = $klant->updated_at ? $klant->updated_at->timestamp : time();
        
        \Log::info('🔍 Klant show - avatar check', [
            'klant_id' => $klant->id,
            'avatar_from_model' => $avatarPath,
            'avatar_exists_in_storage' => $avatarPath ? \Storage::disk('public')->exists($avatarPath) : false,
            'cache_key' => $cacheKey,
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
            // Verwijder oude avatar indien aanwezig
            if ($klant->avatar) {
                $oldPath = storage_path('app/public/' . $klant->avatar);
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }

            // Zorg ervoor dat de klanten directory bestaat
            $avatarDir = storage_path('app/public/avatars/klanten');
            if (!is_dir($avatarDir)) {
                mkdir($avatarDir, 0755, true);
            }

            // Upload nieuwe avatar DIRECT naar storage/app/public/avatars/klanten/
            $file = $request->file('avatar');
            $filename = \Illuminate\Support\Str::random(40) . '.' . $file->getClientOriginalExtension();
            
            // Gebruik Laravel Storage facade - sla op in avatars/klanten subdirectory
            $path = $file->storeAs('avatars/klanten', $filename, 'public');
            
            // Update database met het relatieve pad
            $klant->avatar = $path;
            $klant->save();

            \Log::info('✅ Avatar geüpload', [
                'klant_id' => $klant->id,
                'filename' => $filename,
                'path' => $path,
                'full_path' => storage_path('app/public/' . $path),
                'file_exists' => file_exists(storage_path('app/public/' . $path)),
            ]);

            return response()->json([
                'success' => true,
                'avatar_url' => asset('storage/' . $path),
                'message' => 'Avatar succesvol geüpload!'
            ]);

        } catch (\Exception $e) {
            \Log::error('❌ Avatar upload gefaald', [
                'klant_id' => $klant->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Avatar upload mislukt: ' . $e->getMessage()
            ], 500);
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
}
