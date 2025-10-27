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
        
        // Check of gebruiker een organisatie heeft
        if (!$user->organisatie_id) {
            abort(403, 'Geen organisatie gekoppeld aan je account.');
        }
        
        $organisatie = Organisatie::findOrFail($user->organisatie_id);
        
        // Check of organisatie de custom branding feature heeft
        if (!$organisatie->hasCustomBrandingFeature()) {
            abort(403, 'Custom Branding feature is niet actief voor je organisatie.');
        }
        
        // Check of gebruiker admin is van deze organisatie
        if (!$user->isAdminOfOrganisatie($organisatie->id)) {
            abort(403, 'Alleen organisatie admins kunnen branding wijzigen.');
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
        
        // Bepaal welke organisatie we moeten updaten
        if ($user->rol === 'superadmin' && $request->has('organisatie_id')) {
            $organisatieId = $request->get('organisatie_id');
        } else {
            $organisatieId = $user->organisatie_id;
        }
        
        // Beveiligingscheck: admin mag alleen eigen organisatie wijzigen
        if ($user->rol !== 'superadmin' && $user->organisatie_id != $organisatieId) {
            abort(403, 'Je mag alleen je eigen organisatie branding wijzigen.');
        }
        
        // Haal branding voor deze SPECIFIEKE organisatie
        $branding = OrganisatieBranding::where('organisatie_id', $organisatieId)->firstOrFail();
        
        // Validatie met ALLE bestaande database kolommen
        $validated = $request->validate([
            // Kleuren
            'primaire_kleur' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'primaire_kleur_hover' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'primaire_kleur_licht' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'secundaire_kleur' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'accent_kleur' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'tekst_kleur_primair' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'tekst_kleur_secundair' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'achtergrond_kleur' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'kaart_achtergrond' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'navbar_achtergrond' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'navbar_tekst_kleur' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'rapport_achtergrond' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            
            // Sidebar kleuren (NIEUW)
            'sidebar_achtergrond' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'sidebar_tekst_kleur' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'sidebar_actief_achtergrond' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'sidebar_actief_lijn' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            
            // Dark mode kleuren (NIEUW)
            'dark_achtergrond' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'dark_tekst' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'dark_navbar_achtergrond' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'dark_sidebar_achtergrond' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            
            // Typografie
            'font_familie' => 'nullable|string|max:100',
            'font_grootte_basis' => 'nullable|integer|min:10|max:24',
            
            // Rapport instellingen
            'rapport_footer_tekst' => 'nullable|string|max:1000',
            'toon_logo_in_rapporten' => 'boolean',
            
            // Status
            'is_actief' => 'boolean',
            
            // File uploads
            'logo' => 'nullable|image|max:2048',
            'logo_klein' => 'nullable|image|max:1024',
            'rapport_logo' => 'nullable|image|max:2048',
        ]);
        
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
