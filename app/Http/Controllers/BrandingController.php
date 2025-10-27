<?php

namespace App\Http\Controllers;

use App\Models\Organisatie;
use App\Models\OrganisatieBranding;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
        
        // Check of organisatie de custom branding feature heeft
        if (!$organisatie->hasCustomBrandingFeature()) {
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
        
        // Handle file uploads
        if ($request->hasFile('logo')) {
            $this->deleteOldFile($branding->logo_pad);
            $validated['logo_pad'] = $request->file('logo')->store('branding/logos', 'public');
        }
        
        if ($request->hasFile('logo_klein')) {
            $this->deleteOldFile($branding->logo_klein_pad);
            $validated['logo_klein_pad'] = $request->file('logo_klein')->store('branding/logos', 'public');
        }
        
        if ($request->hasFile('rapport_logo')) {
            $this->deleteOldFile($branding->rapport_logo_pad);
            $validated['rapport_logo_pad'] = $request->file('rapport_logo')->store('branding/rapporten', 'public');
        }
        
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
        
        if (!$organisatie->hasCustomBrandingFeature() || !$user->isAdminOfOrganisatie($organisatie->id)) {
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
     * Reset branding naar defaults
     */
    public function reset()
    {
        $user = auth()->user();
        
        if (!$user->organisatie_id) {
            return back()->with('error', 'Geen organisatie gekoppeld.');
        }
        
        $organisatie = Organisatie::findOrFail($user->organisatie_id);
        
        if (!$organisatie->hasCustomBrandingFeature() || !$user->isAdminOfOrganisatie($organisatie->id)) {
            return back()->with('error', 'Geen toegang.');
        }
        
        $branding = OrganisatieBranding::where('organisatie_id', $organisatie->id)->first();
        
        if ($branding) {
            // Verwijder alle uploads
            $this->deleteOldFile($branding->logo_pad);
            $this->deleteOldFile($branding->logo_klein_pad);
            $this->deleteOldFile($branding->rapport_logo_pad);
            
            // Reset naar defaults met ALLE database kolommen
            $branding->update([
                'logo_pad' => null,
                'logo_klein_pad' => null,
                'rapport_logo_pad' => null,
                'primaire_kleur' => '#3B82F6',
                'primaire_kleur_hover' => '#2563EB',
                'primaire_kleur_licht' => '#DBEAFE',
                'secundaire_kleur' => '#1E40AF',
                'accent_kleur' => '#10B981',
                'tekst_kleur_primair' => '#1F2937',
                'tekst_kleur_secundair' => '#6B7280',
                'achtergrond_kleur' => '#FFFFFF',
                'kaart_achtergrond' => '#F9FAFB',
                'navbar_achtergrond' => '#1E293B',
                'navbar_tekst_kleur' => '#FFFFFF',
                'rapport_achtergrond' => '#FFFFFF',
                'rapport_footer_tekst' => null,
                'font_familie' => 'Inter',
                'font_grootte_basis' => 16,
                'toon_logo_in_rapporten' => true,
                'is_actief' => true,
            ]);
            
            Log::info('Branding gereset naar defaults', [
                'organisatie_id' => $organisatie->id,
                'user_id' => $user->id,
            ]);
        }
        
        return back()->with('success', 'Branding instellingen gereset naar standaard waarden.');
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
