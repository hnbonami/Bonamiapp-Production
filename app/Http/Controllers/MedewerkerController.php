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
        \Log::info('🔍 Medewerkers index aangeroepen');
        
        // Zoekfunctionaliteit
        $zoekterm = $request->input('zoek');
        
        if ($zoekterm) {
            \Log::info('🔎 Zoeken naar medewerkers', ['zoekterm' => $zoekterm]);
            
            $medewerkers = User::where('role', '!=', 'klant')
                ->where(function($query) use ($zoekterm) {
                    $query->where('name', 'like', '%' . $zoekterm . '%')
                          ->orWhere('voornaam', 'like', '%' . $zoekterm . '%')
                          ->orWhere('email', 'like', '%' . $zoekterm . '%');
                })
                ->orderBy('created_at', 'desc')
                ->get();
            
            \Log::info('✅ Zoekresultaten gevonden', ['aantal' => $medewerkers->count()]);
        } else {
            $medewerkers = User::where('role', '!=', 'klant')
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
        return view('medewerkers.create');
    }

    /**
     * Store a newly created medewerker in storage
     */
    public function store(Request $request)
    {
        \Log::info('🚨 MedewerkerController@store CALLED', [
            'request_method' => $request->method(),
            'all_input' => $request->all(),
            'url' => $request->url(),
            'route_name' => $request->route() ? $request->route()->getName() : 'NO_ROUTE'
        ]);

        $validated = $request->validate([
            'voornaam' => 'required|string|max:255',
            'achternaam' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'telefoonnummer' => 'nullable|string|max:20',
            'geboortedatum' => 'nullable|date',
            'geslacht' => 'nullable|in:Man,Vrouw,Anders',
            'straatnaam' => 'nullable|string|max:255',
            'huisnummer' => 'nullable|string|max:20',
            'postcode' => 'nullable|string|max:10',
            'stad' => 'nullable|string|max:255',
            'functie' => 'nullable|string|max:255',
            'rol' => 'nullable|in:admin,manager,medewerker,stagiair',
            'startdatum' => 'nullable|date',
            'contract_type' => 'nullable|in:Vast,Tijdelijk,Freelance,Stage',
            'status' => 'required|in:Actief,Inactief,Verlof,Ziek',
            'bikefit' => 'nullable|boolean',
            'inspanningstest' => 'nullable|boolean',
            'upload_documenten' => 'nullable|boolean',
            'notities' => 'nullable|string',
            'avatar' => 'nullable|image|max:2048'
        ]);

        try {
            \Log::info('🔄 Creating new employee (medewerker)', [
                'email' => $validated['email'],
                'role' => $validated['rol'] ?? 'medewerker',
                'name' => $validated['voornaam'] . ' ' . $validated['achternaam']
            ]);

            // Generate a temporary password
            $temporaryPassword = Str::random(12);
            
            // Handle avatar upload
            $avatarPath = null;
            if ($request->hasFile('avatar')) {
                $avatarPath = $request->file('avatar')->store('avatars', 'public');
                \Log::info('✅ Avatar uploaded', ['path' => $avatarPath]);
            }
            
            // Create user record met ALLE velden
            $user = User::create([
                'name' => $validated['voornaam'] . ' ' . $validated['achternaam'],
                'voornaam' => $validated['voornaam'],
                'achternaam' => $validated['achternaam'],
                'email' => $validated['email'],
                'password' => Hash::make($temporaryPassword),
                'role' => $validated['rol'] ?? 'medewerker',
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
                'email_verified_at' => now(), // Auto-verify employee emails
            ]);

            \Log::info('✅ User record created successfully with ALL fields', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
                'voornaam' => $user->voornaam,
                'achternaam' => $user->achternaam,
                'telefoonnummer' => $user->telefoonnummer,
                'geboortedatum' => $user->geboortedatum,
                'functie' => $user->functie,
                'bikefit' => $user->bikefit,
                'inspanningstest' => $user->inspanningstest,
                'upload_documenten' => $user->upload_documenten,
                'created_at' => $user->created_at,
            ]);

            // IMMEDIATE CHECK: Verify user exists in database right after creation
            $immediateCheck = User::find($user->id);
            \Log::info('🔍 IMMEDIATE DATABASE CHECK after User::create()', [
                'user_found' => $immediateCheck ? 'YES' : 'NO',
                'user_id' => $user->id,
                'email' => $validated['email']
            ]);

            // Create invitation token for password setup
            $token = Str::random(60);
            
            InvitationToken::create([
                'email' => $validated['email'],
                'token' => $token,
                'temporary_password' => $temporaryPassword,
                'type' => 'medewerker',
                'expires_at' => now()->addDays(7),
                'created_by' => auth()->id()
            ]);

            \Log::info('✅ Invitation token created', [
                'email' => $validated['email'],
                'token_length' => strlen($token),
                'expires_at' => now()->addDays(7)->format('Y-m-d H:i:s')
            ]);

            // Send welcome email using EmailIntegrationService
            try {
                $emailService = app(EmailIntegrationService::class);
                $emailSent = $emailService->sendEmployeeWelcomeEmail($user);
                
                if ($emailSent) {
                    \Log::info('✅ Welcome email sent to new employee', ['email' => $user->email]);
                } else {
                    \Log::warning('⚠️ Welcome email failed to send', ['email' => $user->email]);
                }
            } catch (\Exception $emailError) {
                \Log::error('❌ Welcome email system error: ' . $emailError->getMessage(), [
                    'email' => $user->email,
                    'trace' => $emailError->getTraceAsString()
                ]);
            }

            return redirect()->route('medewerkers.index')
                           ->with('success', 'Medewerker succesvol aangemaakt en uitnodiging verstuurd naar ' . $validated['email']);

        } catch (\Exception $e) {
            \Log::error('❌ Failed to create employee: ' . $e->getMessage(), [
                'email' => $validated['email'] ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Er is een fout opgetreden bij het aanmaken van de medewerker: ' . $e->getMessage());
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

        $validated = $request->validate([
            'voornaam' => 'required|string|max:255',
            'achternaam' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $medewerker->id,
            'telefoonnummer' => 'nullable|string|max:20',
            'geboortedatum' => 'nullable|date',
            'geslacht' => 'nullable|in:Man,Vrouw,Anders',
            'straatnaam' => 'nullable|string|max:255',
            'huisnummer' => 'nullable|string|max:20',
            'postcode' => 'nullable|string|max:10',
            'stad' => 'nullable|string|max:255',
            'functie' => 'nullable|string|max:255',
            'rol' => 'nullable|in:admin,manager,medewerker,stagiair',
            'startdatum' => 'nullable|date',
            'contract_type' => 'nullable|in:Vast,Tijdelijk,Freelance,Stage',
            'status' => 'required|in:Actief,Inactief,Verlof,Ziek',
            'bikefit' => 'nullable|boolean',
            'inspanningstest' => 'nullable|boolean',
            'upload_documenten' => 'nullable|boolean',
            'notities' => 'nullable|string',
            'avatar' => 'nullable|image|max:2048'
        ]);

        try {
            // Handle avatar upload
            $avatarPath = $medewerker->avatar_path;
            if ($request->hasFile('avatar')) {
                // Verwijder oude avatar als die bestaat
                if ($avatarPath && \Storage::disk('public')->exists($avatarPath)) {
                    \Storage::disk('public')->delete($avatarPath);
                }
                $avatarPath = $request->file('avatar')->store('avatars', 'public');
                \Log::info('✅ Avatar updated', ['path' => $avatarPath]);
            }

            $medewerker->update([
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
            ]);

            \Log::info('✅ Employee updated successfully', [
                'user_id' => $medewerker->id,
                'email' => $medewerker->email,
                'role' => $medewerker->role
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
     * Send invitation email to medewerker
     */
    public function sendInvitation(User $medewerker)
    {
        if ($medewerker->role === 'klant') {
            abort(404);
        }

        try {
            \Log::info('🚨 Sending invitation to medewerker', [
                'medewerker_id' => $medewerker->id,
                'email' => $medewerker->email,
                'name' => $medewerker->name
            ]);

            // Check if there's already an active invitation token
            $existingToken = InvitationToken::where('email', $medewerker->email)
                                           ->where('expires_at', '>', now())
                                           ->first();

            if (!$existingToken) {
                // Generate a new temporary password and token
                $temporaryPassword = Str::random(12);
                $token = Str::random(60);
                
                InvitationToken::create([
                    'email' => $medewerker->email,
                    'token' => $token,
                    'temporary_password' => $temporaryPassword,
                    'type' => 'medewerker',
                    'expires_at' => now()->addDays(7),
                    'created_by' => auth()->id()
                ]);

                \Log::info('✅ New invitation token created', [
                    'email' => $medewerker->email,
                    'expires_at' => now()->addDays(7)->format('Y-m-d H:i:s')
                ]);
            }

            // Send welcome email using EmailIntegrationService
            $emailService = app(EmailIntegrationService::class);
            $emailSent = $emailService->sendEmployeeWelcomeEmail($medewerker);
            
            if ($emailSent) {
                \Log::info('✅ Invitation email sent successfully', ['email' => $medewerker->email]);
                
                return redirect()->route('medewerkers.index')
                               ->with('success', 'Uitnodiging succesvol verstuurd naar ' . $medewerker->email);
            } else {
                \Log::warning('⚠️ Invitation email failed to send', ['email' => $medewerker->email]);
                
                return redirect()->back()
                               ->with('error', 'Er is een probleem opgetreden bij het versturen van de uitnodiging.');
            }

        } catch (\Exception $e) {
            \Log::error('❌ Failed to send invitation: ' . $e->getMessage(), [
                'medewerker_id' => $medewerker->id,
                'email' => $medewerker->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                           ->with('error', 'Er is een fout opgetreden bij het versturen van de uitnodiging: ' . $e->getMessage());
        }
    }
}