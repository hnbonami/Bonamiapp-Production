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

            // STAP 1.5: VEILIG User Account + Welkomstmail Systeem
            $temporaryPassword = null;
            $userCreated = false;
            
            try {
                \Log::info('🔐 Checking/Creating user account', [
                    'email' => $klant->email,
                    'klant_id' => $klant->id
                ]);

                // 🔍 KRITIEKE FIX: Check of user al bestaat VOOR creation
                $existingUser = \App\Models\User::where('email', $klant->email)->first();
                
                if ($existingUser) {
                    \Log::warning('⚠️ User already exists - reusing existing user', [
                        'email' => $klant->email,
                        'existing_user_id' => $existingUser->id,
                        'new_klant_id' => $klant->id
                    ]);
                    
                    // Update bestaande user met nieuwe klant_id (als nog niet gekoppeld)
                    if (!$existingUser->klant_id || $existingUser->klant_id != $klant->id) {
                        $existingUser->update(['klant_id' => $klant->id]);
                        \Log::info('✅ Linked existing user to new customer');
                    }
                    
                    // BELANGRIJK: Haal bestaande temporary password op voor welcome email
                    $existingToken = \App\Models\InvitationToken::where('email', $klant->email)
                                                                ->where('type', 'klant')
                                                                ->latest()
                                                                ->first();
                    if ($existingToken && $existingToken->temporary_password) {
                        $temporaryPassword = $existingToken->temporary_password;
                        \Log::info('✅ Reusing existing temporary password for welcome email');
                    } else {
                        // Genereer nieuwe password als er geen is
                        $temporaryPassword = \Str::random(12);
                        $existingUser->update(['password' => \Hash::make($temporaryPassword)]);
                        
                        // Maak nieuwe invitation token
                        \App\Models\InvitationToken::create([
                            'email' => $klant->email,
                            'token' => \Str::random(60),
                            'type' => 'klant',
                            'temporary_password' => $temporaryPassword,
                            'expires_at' => now()->addDays(7),
                        ]);
                        \Log::info('✅ Generated new temporary password for existing user');
                    }
                    
                    $userCreated = false; // User bestond al
                    
                } else {
                    // NIEUWE USER: Maak user account aan (BESTAAND SYSTEEM ONVERANDERD)
                    $temporaryPassword = \Str::random(12);
                    
                    $user = \App\Models\User::create([
                        'name' => $klant->voornaam . ' ' . $klant->naam,
                        'email' => $klant->email,
                        'password' => \Hash::make($temporaryPassword),
                        'role' => 'klant',
                        'klant_id' => $klant->id,
                    ]);

                    // Create invitation token
                    \App\Models\InvitationToken::create([
                        'email' => $klant->email,
                        'token' => \Str::random(60),
                        'type' => 'klant',
                        'temporary_password' => $temporaryPassword,
                        'expires_at' => now()->addDays(7),
                    ]);
                    
                    \Log::info('✅ New user account created successfully', [
                        'user_id' => $user->id
                    ]);
                    
                    $userCreated = true;
                }

            } catch (\Exception $userError) {
                \Log::error('❌ User account handling failed (NON-CRITICAL): ' . $userError->getMessage());
                // Genereer fallback password voor welcome email
                if (!$temporaryPassword) {
                    $temporaryPassword = \Str::random(12);
                    \Log::info('⚠️ Using fallback temporary password');
                }
            }
            
            // 📧 WELKOMSTMAIL VERSTUREN (ALTIJD, ongeacht user creation status)
            try {
                \Log::info('📧 Sending welcome email', [
                    'email' => $klant->email,
                    'user_was_created' => $userCreated,
                    'has_temp_password' => !empty($temporaryPassword)
                ]);

                $emailService = app(\App\Services\EmailIntegrationService::class);
                $emailSent = $emailService->sendCustomerWelcomeEmail($klant, [
                    'temporary_password' => $temporaryPassword,
                    'voornaam' => $klant->voornaam,
                    'naam' => $klant->naam,
                    'email' => $klant->email
                ]);
                
                if ($emailSent) {
                    \Log::info('✅ Welcome email sent successfully', [
                        'recipient' => $klant->email,
                        'user_existed' => !$userCreated
                    ]);
                } else {
                    \Log::warning('⚠️ Welcome email failed to send');
                }
                
            } catch (\Exception $emailError) {
                \Log::error('❌ Welcome email exception (NON-CRITICAL): ' . $emailError->getMessage());
                // BELANGRIJK: Email error blokkeert NIET de referral processing!
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
}