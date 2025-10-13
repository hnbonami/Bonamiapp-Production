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
    public function index()
    {
        $medewerkers = User::where('role', '!=', 'klant')
                          ->orderBy('created_at', 'desc')
                          ->get();
        
        return view('admin.medewerkers.index', compact('medewerkers'));
    }

    /**
     * Show the form for creating a new medewerker
     */
    public function create()
    {
        return view('admin.medewerkers.create');
    }

    /**
     * Store a newly created medewerker in storage
     */
    public function store(Request $request)
    {
        // EERSTE TEST: Check of deze method überhaupt wordt aangeroepen
        \Log::info('🚨 MedewerkersController@store CALLED', [
            'request_method' => $request->method(),
            'all_input' => $request->all(),
            'url' => $request->url(),
            'route_name' => $request->route() ? $request->route()->getName() : 'NO_ROUTE'
        ]);

        $validated = $request->validate([
            'voornaam' => 'required|string|max:255',
            'achternaam' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'telefoon' => 'nullable|string|max:20',
            'rol' => 'required|in:admin,medewerker'
        ]);

        try {
            \Log::info('🔄 Creating new employee (medewerker)', [
                'email' => $validated['email'],
                'role' => $validated['rol'],
                'name' => $validated['voornaam'] . ' ' . $validated['achternaam']
            ]);

            // Generate a temporary password
            $temporaryPassword = Str::random(12);
            
            // Create user record with ALL required fields for medewerkers
            $user = User::create([
                'name' => $validated['voornaam'] . ' ' . $validated['achternaam'],
                'voornaam' => $validated['voornaam'], // BELANGRIJK: voornaam voor email templates
                'achternaam' => $validated['achternaam'], // BELANGRIJK: achternaam voor email templates
                'email' => $validated['email'],
                'password' => Hash::make($temporaryPassword),
                'role' => $validated['rol'],
                'telefoon' => $validated['telefoon'] ?? null, // BELANGRIJK: telefoon veld
                'email_verified_at' => now(), // Auto-verify employee emails
            ]);

            \Log::info('✅ User record created successfully with all fields', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
                'voornaam' => $user->voornaam,
                'achternaam' => $user->achternaam,
                'telefoon' => $user->telefoon,
                'name' => $user->name,
                'created_at' => $user->created_at,
                'user_exists_check' => $user->exists ? 'YES' : 'NO'
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

            // EXTRA VERIFICATIE: Check of user daadwerkelijk is aangemaakt
            $verifyUser = User::where('email', $validated['email'])->first();
            if ($verifyUser) {
                \Log::info('✅ VERIFICATION: User was successfully created and saved', [
                    'user_id' => $verifyUser->id,
                    'email' => $verifyUser->email,
                    'role' => $verifyUser->role,
                    'database_check' => 'SUCCESS'
                ]);
            } else {
                \Log::error('❌ VERIFICATION FAILED: User was created but not found in database!', [
                    'email' => $validated['email'],
                    'database_check' => 'FAILED'
                ]);
            }

            return redirect()->route('admin.users')
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
        
        return view('admin.medewerkers.show', compact('medewerker'));
    }

    /**
     * Show the form for editing the specified medewerker
     */
    public function edit(User $medewerker)
    {
        if ($medewerker->role === 'klant') {
            abort(404);
        }
        
        return view('admin.medewerkers.edit', compact('medewerker'));
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
            'telefoon' => 'nullable|string|max:20',
            'rol' => 'required|in:admin,medewerker'
        ]);

        try {
            $medewerker->update([
                'name' => $validated['voornaam'] . ' ' . $validated['achternaam'],
                'voornaam' => $validated['voornaam'],
                'achternaam' => $validated['achternaam'],
                'email' => $validated['email'],
                'role' => $validated['rol'],
                'telefoon' => $validated['telefoon'] ?? null,
            ]);

            \Log::info('✅ Employee updated successfully', [
                'user_id' => $medewerker->id,
                'email' => $medewerker->email,
                'role' => $medewerker->role
            ]);

            return redirect()->route('admin.users')
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

            return redirect()->route('admin.users')
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
}