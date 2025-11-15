<?php

namespace App\Http\Controllers;

use App\Models\Organisatie;
use App\Models\User;
use App\Models\Klant;
use App\Models\Feature;
use App\Models\OrganisatieBranding;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrganisatieController extends Controller
{
    /**
     * Toon overzicht van alle organisaties (alleen voor superadmin)
     */
    public function index()
    {
        // Debug logging
        \Log::info('=== ORGANISATIES INDEX DEBUG ===');
        
        // Laad organisaties met ALLE mogelijke relaties
        $organisaties = Organisatie::with(['users', 'klanten'])
            ->orderBy('created_at', 'desc')
            ->get();
        
    // Voeg handmatig counts toe aan elk organisatie object
    $organisaties->each(function ($org) {
        // Tel alle users
        $org->users_count = $org->users->count();
        $org->klanten_count = $org->klanten->count();
        
        // Debug: Toon ALLE user informatie
        \Log::info("=== ORG: {$org->naam} (ID: {$org->id}) ===");
        \Log::info("Users in relatie: " . $org->users->count());
        
        foreach ($org->users as $user) {
            \Log::info("  - User {$user->id}: {$user->name} | Role: '{$user->role}' | Type: " . gettype($user->role));
        }
        
        // Tel ook via directe DB query om case sensitivity te omzeilen
        $dbMedewerkers = \DB::table('users')
            ->where('organisatie_id', $org->id)
            ->whereIn(\DB::raw('LOWER(role)'), ['medewerker', 'employee', 'staff'])
            ->count();
            
        $dbAdmins = \DB::table('users')
            ->where('organisatie_id', $org->id)
            ->whereIn(\DB::raw('LOWER(role)'), ['admin', 'administrator', 'beheerder', 'organisatie_admin', 'superadmin'])
            ->count();
        
        \Log::info("DB Query (case-insensitive) - Medewerkers: {$dbMedewerkers}, Admins: {$dbAdmins}");
        
        $org->medewerkers_count = $dbMedewerkers;
        $org->admin_count = $dbAdmins;
        
        \Log::info("FINAL - Users: {$org->users_count}, Klanten: {$org->klanten_count}, Medewerkers: {$org->medewerkers_count}, Admins: {$org->admin_count}");
    });

    return view('organisaties.index', compact('organisaties'));
    }

    /**
     * Toon formulier om nieuwe organisatie aan te maken
     */
    public function create()
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        return view('organisaties.create');
    }

    /**
     * Sla nieuwe organisatie op
     */
    public function store(Request $request)
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'naam' => 'required|string|max:255',
            'email' => 'required|email|unique:organisaties,email',
            'telefoon' => 'nullable|string|max:50',
            'adres' => 'nullable|string|max:255',
            'postcode' => 'nullable|string|max:20',
            'plaats' => 'nullable|string|max:100',
            'btw_nummer' => 'nullable|string|max:50',
            'status' => 'required|in:actief,inactief,trial',
            'trial_eindigt_op' => 'nullable|date',
            'maandelijkse_prijs' => 'nullable|numeric|min:0',
            'notities' => 'nullable|string',
        ]);

        try {
            $organisatie = Organisatie::create($validated);
            
            // ✅ Kopieer Performance Pulse default branding naar nieuwe organisatie
            $this->copyDefaultBrandingToOrganisatie($organisatie);

            // 🎉 Maak automatisch welkomst/handleiding widget aan voor nieuwe organisatie
            // Deze widget wordt zichtbaar op het dashboard van de eerste admin die inlogt
            try {
                \App\Http\Controllers\DashboardController::createWelcomeWidget(
                    $organisatie->id,
                    auth()->id() // Superadmin is de creator
                );
                
                Log::info('✅ Welkomst widget automatisch aangemaakt bij nieuwe organisatie', [
                    'organisatie_id' => $organisatie->id,
                    'organisatie_naam' => $organisatie->naam
                ]);
            } catch (\Exception $widgetError) {
                // Log error maar laat organisatie aanmaak doorgaan
                Log::error('⚠️ Kon welkomst widget niet aanmaken', [
                    'organisatie_id' => $organisatie->id,
                    'error' => $widgetError->getMessage()
                ]);
            }

            Log::info('Nieuwe organisatie aangemaakt', [
                'organisatie_id' => $organisatie->id,
                'naam' => $organisatie->naam,
                'created_by' => auth()->id()
            ]);

            return redirect()->route('organisaties.index')
                ->with('success', 'Organisatie "' . $organisatie->naam . '" succesvol aangemaakt (inclusief welkomst widget).');
        } catch (\Exception $e) {
            Log::error('Fout bij aanmaken organisatie', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Er is een fout opgetreden bij het aanmaken van de organisatie.');
        }
    }

    /**
     * Toon details van een organisatie
     */
    public function show(Organisatie $organisatie)
    {
        // Eager load relaties voor optimale performance
        $organisatie->load(['users', 'klanten', 'features']);
        
        // Bereken statistieken
        $stats = [
            'totaal_users' => $organisatie->users()->count(),
            'totaal_klanten' => $organisatie->klanten()->count(),
            'actieve_klanten' => $organisatie->klanten()->where('status', 'Actief')->count(),
            'admins' => $organisatie->users()->where('role', 'organisatie_admin')->count(),
            'medewerkers' => $organisatie->users()->where('role', 'medewerker')->count(),
        ];
        
        // Tel totaal aantal beschikbare features
        $totalFeatures = \App\Models\Feature::count();
        
        return view('organisaties.show', compact('organisatie', 'stats', 'totalFeatures'));
    }

    /**
     * Toon formulier om organisatie te bewerken
     */
    public function edit(Organisatie $organisatie)
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        return view('organisaties.edit', compact('organisatie'));
    }

    /**
     * Update organisatie gegevens
     */
    public function update(Request $request, Organisatie $organisatie)
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'naam' => 'required|string|max:255',
            'email' => 'required|email|unique:organisaties,email,' . $organisatie->id,
            'telefoon' => 'nullable|string|max:50',
            'adres' => 'nullable|string|max:255',
            'postcode' => 'nullable|string|max:20',
            'plaats' => 'nullable|string|max:100',
            'btw_nummer' => 'nullable|string|max:50',
            'status' => 'required|in:actief,inactief,trial',
            'trial_eindigt_op' => 'nullable|date',
            'maandelijkse_prijs' => 'nullable|numeric|min:0',
            'notities' => 'nullable|string',
        ]);

        try {
            $organisatie->update($validated);

            Log::info('Organisatie gewijzigd', [
                'organisatie_id' => $organisatie->id,
                'naam' => $organisatie->naam,
                'updated_by' => auth()->id()
            ]);

            return redirect()->route('organisaties.show', $organisatie)
                ->with('success', 'Organisatie succesvol bijgewerkt.');
        } catch (\Exception $e) {
            Log::error('Fout bij bijwerken organisatie', [
                'organisatie_id' => $organisatie->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Er is een fout opgetreden bij het bijwerken.');
        }
    }

    /**
     * Verwijder een organisatie (soft delete indien mogelijk)
     */
    public function destroy(Organisatie $organisatie)
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        // Check of organisatie geen users of klanten heeft
        if ($organisatie->users()->count() > 0 || $organisatie->klanten()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Kan organisatie niet verwijderen: er zijn nog users of klanten gekoppeld.');
        }

        try {
            $naam = $organisatie->naam;
            $organisatie->delete();

            Log::warning('Organisatie verwijderd', [
                'organisatie_id' => $organisatie->id,
                'naam' => $naam,
                'deleted_by' => auth()->id()
            ]);

            return redirect()->route('organisaties.index')
                ->with('success', 'Organisatie "' . $naam . '" succesvol verwijderd.');
        } catch (\Exception $e) {
            Log::error('Fout bij verwijderen organisatie', [
                'organisatie_id' => $organisatie->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Er is een fout opgetreden bij het verwijderen.');
        }
    }

    /**
     * Stuur uitnodiging naar organisatie admin
     */
    public function sendInvitation(Organisatie $organisatie)
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        try {
            // Genereer automatisch wachtwoord
            $generatedPassword = \Illuminate\Support\Str::random(12);
            
            // Check of er al een user bestaat met dit email (voor ANY organisatie)
            $existingUser = User::where('email', $organisatie->email)->first();

            if ($existingUser) {
                // Update bestaande user
                $existingUser->update([
                    'password' => \Hash::make($generatedPassword),
                    'organisatie_id' => $organisatie->id, // Link aan deze organisatie
                    'role' => 'organisatie_admin'
                ]);
                
                $admin = $existingUser;
                $message = 'Nieuwe inloggegevens verstuurd (bestaand account bijgewerkt)';
                
                Log::info('Bestaand user account bijgewerkt voor organisatie', [
                    'user_id' => $existingUser->id,
                    'organisatie_id' => $organisatie->id
                ]);
            } else {
                // Maak nieuw admin account aan
                $admin = User::create([
                    'name' => $organisatie->naam . ' Admin',
                    'email' => $organisatie->email,
                    'password' => \Hash::make($generatedPassword),
                    'role' => 'organisatie_admin',
                    'organisatie_id' => $organisatie->id,
                    'email_verified_at' => now()
                ]);
                
                $message = 'Uitnodiging succesvol verstuurd';
            }

            // Update organisatie
            $organisatie->update([
                'uitnodiging_verstuurd_op' => now(),
                'uitnodiging_geaccepteerd_op' => now()
            ]);

            // Stuur email met inloggegevens
            \Mail::to($organisatie->email)->send(
                new \App\Mail\OrganisatieUitnodiging($organisatie, $generatedPassword, route('login'))
            );

            Log::info('Organisatie uitnodiging verstuurd met credentials', [
                'organisatie_id' => $organisatie->id,
                'organisatie_naam' => $organisatie->naam,
                'admin_user_id' => $admin->id,
                'email' => $organisatie->email,
                'is_existing' => isset($existingUser),
                'sent_by' => auth()->id()
            ]);

            return redirect()->back()
                ->with('success', $message . ' naar ' . $organisatie->email);
                
        } catch (\Exception $e) {
            Log::error('Fout bij versturen organisatie uitnodiging', [
                'organisatie_id' => $organisatie->id,
                'email' => $organisatie->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Kon uitnodiging niet versturen: ' . $e->getMessage());
        }
    }

    /**
     * Toon acceptatie pagina voor uitnodiging (publiek toegankelijk)
     */
    public function acceptInvitation(string $token)
    {
        $organisatie = Organisatie::where('uitnodiging_token', $token)->first();

        if (!$organisatie) {
            return redirect()->route('login')
                ->with('error', 'Ongeldige uitnodigingslink.');
        }

        // Check of token niet ouder is dan 7 dagen
        if ($organisatie->uitnodiging_verstuurd_op < now()->subDays(7)) {
            return redirect()->route('login')
                ->with('error', 'Deze uitnodigingslink is verlopen. Neem contact op met de beheerder.');
        }

        return view('organisaties.accept-invitation', compact('organisatie', 'token'));
    }

    /**
     * Verwerk uitnodiging en maak admin account aan
     */
    public function processInvitation(Request $request, string $token)
    {
        $organisatie = Organisatie::where('uitnodiging_token', $token)->first();

        if (!$organisatie) {
            return redirect()->route('login')
                ->with('error', 'Ongeldige uitnodigingslink.');
        }

        $validated = $request->validate([
            'naam' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        try {
            // Maak admin user aan voor deze organisatie
            $user = User::create([
                'name' => $validated['naam'],
                'email' => $validated['email'],
                'password' => \Hash::make($validated['password']),
                'role' => 'organisatie_admin',
                'organisatie_id' => $organisatie->id,
                'email_verified_at' => now()
            ]);

            // Markeer uitnodiging als geaccepteerd
            $organisatie->update([
                'uitnodiging_geaccepteerd_op' => now(),
                'uitnodiging_token' => null // Token onbruikbaar maken
            ]);

            Log::info('Organisatie uitnodiging geaccepteerd', [
                'organisatie_id' => $organisatie->id,
                'user_id' => $user->id,
                'user_email' => $user->email
            ]);

            // Log gebruiker automatisch in
            auth()->login($user);

            return redirect()->route('dashboard')
                ->with('success', 'Welkom! Je account is succesvol aangemaakt.');

        } catch (\Exception $e) {
            Log::error('Fout bij accepteren organisatie uitnodiging', [
                'organisatie_id' => $organisatie->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Er is een fout opgetreden: ' . $e->getMessage());
        }
    }

    /**
     * Toggle een feature voor een organisatie aan/uit
     */
    public function toggleFeature(Request $request, Organisatie $organisatie, $featureId)
    {
        try {
            \Log::info('🔄 Feature toggle aangeroepen', [
                'organisatie_id' => $organisatie->id,
                'feature_id' => $featureId,
                'current_user' => auth()->id(),
                'request_data' => $request->all()
            ]);

            // Valideer de request
            $validated = $request->validate([
                'is_active' => 'required|boolean'
            ]);

            // Check of feature bestaat
            $feature = Feature::find($featureId);
            if (!$feature) {
                return response()->json([
                    'success' => false,
                    'message' => 'Feature niet gevonden'
                ], 404);
            }

            // Check of organisatie al deze feature heeft
            $existingPivot = $organisatie->features()->where('feature_id', $featureId)->first();

            if ($existingPivot) {
                // Update bestaande relatie - LET OP: kolom heet 'is_actief' niet 'is_active'
                $organisatie->features()->updateExistingPivot($featureId, [
                    'is_actief' => $validated['is_active']
                ]);
                
                \Log::info('✅ Feature status bijgewerkt', [
                    'feature_id' => $featureId,
                    'new_status' => $validated['is_active']
                ]);
            } else {
                // Voeg feature toe aan organisatie
                $organisatie->features()->attach($featureId, [
                    'is_actief' => $validated['is_active']
                ]);
                
                \Log::info('✅ Feature toegevoegd aan organisatie', [
                    'feature_id' => $featureId,
                    'status' => $validated['is_active']
                ]);
            }

            return response()->json([
                'success' => true,
                'is_active' => $validated['is_active'],
                'message' => $validated['is_active'] ? 'Feature geactiveerd' : 'Feature gedeactiveerd'
            ]);

        } catch (\Exception $e) {
            \Log::error('❌ Feature toggle error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Er is een fout opgetreden: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Kopieer Performance Pulse default branding (organisatie ID 1) naar nieuwe organisatie
     */
    private function copyDefaultBrandingToOrganisatie(Organisatie $organisatie)
    {
        // Haal master organisatie op (Performance Pulse - ID 1)
        $masterOrganisatie = Organisatie::find(1);
        
        if (!$masterOrganisatie) {
            \Log::warning('⚠️ Geen master organisatie gevonden (ID 1)');
            return;
        }
        
        // 1️⃣ Kopieer LOGIN PERSONALISATIE velden van organisatie model
        $organisatie->update([
            'login_bg_afbeelding' => null, // Afbeelding moet organisatie zelf uploaden
            'login_bg_video' => null,      // Video moet organisatie zelf uploaden
            'login_tekstkleur' => $masterOrganisatie->login_tekstkleur,
            'login_inlogknop_kleur' => $masterOrganisatie->login_inlogknop_kleur,
            'login_inlogknop_hover_kleur' => $masterOrganisatie->login_inlogknop_hover_kleur,
            'login_link_kleur' => $masterOrganisatie->login_link_kleur,
        ]);
        
        \Log::info('✅ Login personalisatie kleuren gekopieerd', [
            'van_organisatie' => 1,
            'naar_organisatie' => $organisatie->id,
            'login_tekstkleur' => $masterOrganisatie->login_tekstkleur,
            'login_inlogknop_kleur' => $masterOrganisatie->login_inlogknop_kleur,
        ]);
        
        // 2️⃣ Kopieer BRANDING velden (navbar, sidebar, dark mode)
        $masterBranding = OrganisatieBranding::where('organisatie_id', 1)
            ->where('is_actief', true)
            ->first();
        
        if ($masterBranding) {
            OrganisatieBranding::create([
                'organisatie_id' => $organisatie->id,
                'is_actief' => true,
                'navbar_achtergrond' => $masterBranding->navbar_achtergrond,
                'navbar_tekst_kleur' => $masterBranding->navbar_tekst_kleur,
                'sidebar_achtergrond' => $masterBranding->sidebar_achtergrond,
                'sidebar_tekst_kleur' => $masterBranding->sidebar_tekst_kleur,
                'sidebar_actief_achtergrond' => $masterBranding->sidebar_actief_achtergrond,
                'sidebar_actief_lijn' => $masterBranding->sidebar_actief_lijn,
                'dark_achtergrond' => $masterBranding->dark_achtergrond,
                'dark_tekst' => $masterBranding->dark_tekst,
                'dark_navbar_achtergrond' => $masterBranding->dark_navbar_achtergrond,
                'dark_sidebar_achtergrond' => $masterBranding->dark_sidebar_achtergrond,
                'login_text_color' => $masterBranding->login_text_color,
                'login_button_color' => $masterBranding->login_button_color,
                'login_button_hover_color' => $masterBranding->login_button_hover_color,
                'login_link_color' => $masterBranding->login_link_color,
                // Logo's blijven NULL - organisatie moet eigen uploaden
            ]);
            
            \Log::info('✅ App branding gekopieerd', [
                'van_organisatie' => 1,
                'naar_organisatie' => $organisatie->id,
            ]);
        }
        
        \Log::info('✅ Volledige Performance Pulse branding + login personalisatie gekopieerd', [
            'organisatie_id' => $organisatie->id,
            'organisatie_naam' => $organisatie->naam
        ]);
    }
}
