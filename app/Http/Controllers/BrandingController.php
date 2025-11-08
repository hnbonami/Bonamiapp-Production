<?php

namespace App\Http\Controllers;

use App\Models\Organisatie;
use App\Models\OrganisatieBranding;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class BrandingController extends Controller
{
    /**
     * Toon branding instellingen pagina
     */
    public function index()
    {
        $user = auth()->user();
        
        // Superadmin kan kiezen welke organisatie te bewerken via query parameter
        if ($user->rol === 'superadmin' && request()->has('organisatie_id')) {
            $organisatieId = request()->get('organisatie_id');
            $organisatie = Organisatie::findOrFail($organisatieId);
        } else {
            // Normale admin gebruikt zijn eigen organisatie
            if (!$user->organisatie_id) {
                abort(403, 'Geen organisatie gekoppeld aan je account.');
            }
            
            $organisatie = Organisatie::findOrFail($user->organisatie_id);
            
            // Check of gebruiker admin is (niet klant, niet medewerker)
            if ($user->rol === 'klant' || $user->rol === 'medewerker') {
                abort(403, 'Alleen organisatie admins kunnen branding wijzigen.');
            }
        }
        
        // Check of organisatie de branding_layout feature heeft
        if (!$organisatie->hasFeature('branding_layout')) {
            abort(403, 'Custom Branding feature is niet actief voor deze organisatie.');
        }
        
        // Haal branding configuratie op of maak aan
        $branding = OrganisatieBranding::getOrCreateForOrganisatie($organisatie->id);
        
        return view('branding.index', compact('organisatie', 'branding'));
    }
    
    /**
     * Update branding configuratie
     */
    public function update(Request $request)
    {
        $user = auth()->user();
        
        // Haal organisatie_id uit request (van hidden field in formulier)
        $organisatieId = $request->input('organisatie_id');
        
        // Als geen organisatie_id in request, gebruik gebruiker zijn organisatie
        if (!$organisatieId) {
            $organisatieId = $user->organisatie_id;
        }
        
        // Beveiligingscheck: admin mag alleen eigen organisatie wijzigen, superadmin mag alles
        if ($user->rol !== 'superadmin') {
            if ($user->organisatie_id != $organisatieId) {
                abort(403, 'Je mag alleen je eigen organisatie branding wijzigen.');
            }
            
            // Check of gebruiker niet gewoon klant of medewerker is
            if ($user->rol === 'klant' || $user->rol === 'medewerker') {
                abort(403, 'Alleen organisatie admins kunnen branding wijzigen.');
            }
        }
        
        // Check of organisatie bestaat
        $organisatie = Organisatie::findOrFail($organisatieId);
        
        // Haal branding voor deze SPECIFIEKE organisatie
        $branding = OrganisatieBranding::where('organisatie_id', $organisatieId)->firstOrFail();
        
        // Validatie - Map Engels naar Nederlands
        $request->validate([
            // File uploads
            'logo' => 'nullable|image|max:2048',
            'logo_klein' => 'nullable|image|max:1024',
            'rapport_logo' => 'nullable|image|max:2048',
            'login_background_image' => 'nullable|image|max:5120',
            'login_background_video' => 'nullable|file|mimes:mp4,mov,avi,webm|max:10240',
            'login_text_color' => 'nullable|string|max:7',
            'login_button_color' => 'nullable|string|max:7',
            'login_button_hover_color' => 'nullable|string|max:7',
            'login_link_color' => 'nullable|string|max:7',
        ]);
        
        // Prepare data met mapping van Engels (form) naar Nederlands (database)
        $validated = [];
        
        // Navbar kleuren
        if ($request->filled('navbar_achtergrond')) {
            $validated['navbar_achtergrond'] = $request->input('navbar_achtergrond');
        }
        if ($request->filled('navbar_tekst_kleur')) {
            $validated['navbar_tekst_kleur'] = $request->input('navbar_tekst_kleur');
        }
        
        // Sidebar kleuren
        if ($request->filled('sidebar_achtergrond')) {
            $validated['sidebar_achtergrond'] = $request->input('sidebar_achtergrond');
        }
        if ($request->filled('sidebar_tekst_kleur')) {
            $validated['sidebar_tekst_kleur'] = $request->input('sidebar_tekst_kleur');
        }
        if ($request->filled('sidebar_actief_achtergrond')) {
            $validated['sidebar_actief_achtergrond'] = $request->input('sidebar_actief_achtergrond');
        }
        if ($request->filled('sidebar_actief_lijn')) {
            $validated['sidebar_actief_lijn'] = $request->input('sidebar_actief_lijn');
        }
        
        // Dark mode kleuren
        if ($request->filled('dark_achtergrond')) {
            $validated['dark_achtergrond'] = $request->input('dark_achtergrond');
        }
        if ($request->filled('dark_tekst')) {
            $validated['dark_tekst'] = $request->input('dark_tekst');
        }
        if ($request->filled('dark_navbar_achtergrond')) {
            $validated['dark_navbar_achtergrond'] = $request->input('dark_navbar_achtergrond');
        }
        if ($request->filled('dark_sidebar_achtergrond')) {
            $validated['dark_sidebar_achtergrond'] = $request->input('dark_sidebar_achtergrond');
        }
        
                // Handle logo upload
        if ($request->hasFile('logo')) {
            // Verwijder oude logo als die bestaat
            if ($branding->logo_pad) {
                Storage::delete('public/' . $branding->logo_pad);
            }
            
            $logoPath = $request->file('logo')->store('branding/logos', 'public');
            $branding->logo_pad = $logoPath; // Alleen logo_pad gebruiken (database kolom)
        }
        
        if ($request->hasFile('logo_klein')) {
            $this->deleteOldFile($branding->logo_klein_pad);
            $validated['logo_klein_pad'] = $request->file('logo_klein')->store('branding/logos', 'public');
        }
        
        if ($request->hasFile('rapport_logo')) {
            $this->deleteOldFile($branding->rapport_logo_pad);
            $validated['rapport_logo_pad'] = $request->file('rapport_logo')->store('branding/rapporten', 'public');
        }
        
        // Upload login achtergrond afbeelding
        if ($request->hasFile('login_background_image')) {
            // Verwijder oude afbeelding
            if ($branding->login_background_image) {
                Storage::disk('public')->delete($branding->login_background_image);
            }
            
            $path = $request->file('login_background_image')->store('branding/login', 'public');
            $branding->login_background_image = $path;
        }
        
        // Upload login achtergrond video
        if ($request->hasFile('login_background_video')) {
            // Verwijder oude video
            if ($branding->login_background_video) {
                Storage::disk('public')->delete($branding->login_background_video);
            }
            
            $path = $request->file('login_background_video')->store('branding/login_videos', 'public');
            $branding->login_background_video = $path;
            
            \Log::info('📹 Login video geüpload', [
                'organisatie_id' => $organisatie->id,
                'path' => $path,
                'user_id' => auth()->id(),
            ]);
        }

        // Update login kleuren
        $branding->login_text_color = $request->input('login_text_color') ?? $branding->login_text_color;
        $branding->login_button_color = $request->input('login_button_color') ?? $branding->login_button_color;
        $branding->login_button_hover_color = $request->input('login_button_hover_color') ?? $branding->login_button_hover_color;
        $branding->login_link_color = $request->input('login_link_color') ?? $branding->login_link_color;

        $branding->save();

        \Log::info('Login branding bijgewerkt', [
            'organisatie_id' => $organisatie->id,
            'user_id' => auth()->id(),
            'changes' => $request->only([
                'login_text_color',
                'login_button_color', 
                'login_button_hover_color',
                'login_link_color'
            ])
        ]);

        // Update branding
        $branding->update($validated);
        
        return redirect()->route('branding.index')
            ->with('success', 'Branding instellingen succesvol opgeslagen!');
    }
    
    /**
     * Verwijder een specifiek branding bestand
     */
    public function deleteFile(Request $request)
    {
        $user = auth()->user();
        
        if (!$user->organisatie_id) {
            return response()->json(['success' => false, 'message' => 'Geen organisatie gekoppeld.'], 403);
        }
        
        $organisatie = Organisatie::findOrFail($user->organisatie_id);
        
        if (!$organisatie->hasFeature('branding_layout') || !$user->isAdminOfOrganisatie($organisatie->id)) {
            return response()->json(['success' => false, 'message' => 'Geen toegang.'], 403);
        }
        
        $validated = $request->validate([
            'field' => 'required|in:logo_pad,logo_klein_pad,rapport_logo_pad'
        ]);
        
        $branding = OrganisatieBranding::where('organisatie_id', $organisatie->id)->first();
        
        if (!$branding) {
            return response()->json(['success' => false, 'message' => 'Geen branding configuratie gevonden.'], 404);
        }
        
        $field = $validated['field'];
        $filePath = $branding->$field;
        
        if ($filePath) {
            $this->deleteOldFile($filePath);
            $branding->update([$field => null]);
            
            Log::info('Branding bestand verwijderd', [
                'organisatie_id' => $organisatie->id,
                'field' => $field,
                'file_path' => $filePath,
            ]);
            
            return response()->json(['success' => true, 'message' => 'Bestand succesvol verwijderd.']);
        }
        
        return response()->json(['success' => false, 'message' => 'Geen bestand gevonden.'], 404);
    }
    
    /**
     * Reset branding naar Performance Pulse defaults (organisatie ID 1)
     */
    public function reset(Request $request)
    {
        $organisatie = auth()->user()->organisatie;
        
        if (!$organisatie) {
            return redirect()->back()->with('error', 'Geen organisatie gevonden.');
        }
        
        // Haal de Performance Pulse master branding op (organisatie ID 1)
        $masterOrganisatie = \App\Models\Organisatie::find(1);
        $masterBranding = \App\Models\OrganisatieBranding::where('organisatie_id', 1)
            ->where('is_actief', true)
            ->first();
        
        if (!$masterOrganisatie) {
            return redirect()->back()->with('error', 'Performance Pulse default branding niet gevonden.');
        }
        
        try {
            \DB::beginTransaction();
            
            // Kopieer organisatie branding velden
            $organisatie->update([
                'branding_enabled' => false, // Zet uit, gebruiken we defaults
                'logo_path' => null, // Verwijder custom logo
                'favicon_path' => null, // Verwijder custom favicon
                'primary_color' => $masterOrganisatie->primary_color ?? '#3b82f6',
                'secondary_color' => $masterOrganisatie->secondary_color ?? '#1e40af',
                'sidebar_color' => $masterOrganisatie->sidebar_color ?? '#1f2937',
                'text_color' => $masterOrganisatie->text_color ?? '#111827',
                'custom_css' => null, // Verwijder custom CSS
            ]);
            
            // Verwijder of update huidige branding naar master template
            $currentBranding = \App\Models\OrganisatieBranding::where('organisatie_id', $organisatie->id)
                ->where('is_actief', true)
                ->first();
            
            if ($masterBranding) {
                // Kopieer ALLE branding velden van master
                $brandingData = [
                    'organisatie_id' => $organisatie->id,
                    'is_actief' => true,
                    'navbar_achtergrond' => $masterBranding->navbar_achtergrond ?? '#c8e1eb',
                    'navbar_tekst_kleur' => $masterBranding->navbar_tekst_kleur ?? '#000000',
                    'sidebar_achtergrond' => $masterBranding->sidebar_achtergrond ?? '#FFFFFF',
                    'sidebar_tekst_kleur' => $masterBranding->sidebar_tekst_kleur ?? '#374151',
                    'sidebar_actief_achtergrond' => $masterBranding->sidebar_actief_achtergrond ?? '#f6fbfe',
                    'sidebar_actief_lijn' => $masterBranding->sidebar_actief_lijn ?? '#c1dfeb',
                    'dark_achtergrond' => $masterBranding->dark_achtergrond ?? '#1F2937',
                    'dark_tekst' => $masterBranding->dark_tekst ?? '#F9FAFB',
                    'dark_navbar_achtergrond' => $masterBranding->dark_navbar_achtergrond ?? '#111827',
                    'dark_sidebar_achtergrond' => $masterBranding->dark_sidebar_achtergrond ?? '#111827',
                    'logo_pad' => null, // Geen custom logo
                    'login_logo' => $masterBranding->login_logo ?? null, // Kopieer login logo PAD niet (willen niet delen)
                    'login_background_image' => $masterBranding->login_background_image ?? null,
                    'login_background_video' => $masterBranding->login_background_video ?? null,
                    'login_text_color' => $masterBranding->login_text_color ?? '#374151',
                    'login_button_color' => $masterBranding->login_button_color ?? '#7fb432',
                    'login_button_hover_color' => $masterBranding->login_button_hover_color ?? '#6a9929',
                    'login_link_color' => $masterBranding->login_link_color ?? '#374151',
                ];
                
                if ($currentBranding) {
                    $currentBranding->update($brandingData);
                } else {
                    \App\Models\OrganisatieBranding::create($brandingData);
                }
            }
            
            \DB::commit();
            
            \Log::info('✅ Branding gereset naar Performance Pulse defaults', [
                'organisatie_id' => $organisatie->id,
                'organisatie_naam' => $organisatie->naam
            ]);
            
            return redirect()->back()->with('success', '✅ Branding succesvol gereset naar Performance Pulse defaults!');
            
        } catch (\Exception $e) {
            \DB::rollBack();
            
            \Log::error('❌ Fout bij resetten branding', [
                'organisatie_id' => $organisatie->id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()->with('error', 'Fout bij resetten branding: ' . $e->getMessage());
        }
    }
    
    /**
     * Helper: verwijder oud bestand van storage
     */
    private function deleteOldFile($path)
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
