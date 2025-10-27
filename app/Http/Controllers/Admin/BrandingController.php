<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organisatie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BrandingController extends Controller
{
    /**
     * Toon branding instellingen formulier
     */
    public function index()
    {
        $user = auth()->user();
        
        // Check of user organisatie admin is
        if (!$user->organisatie_id || !in_array($user->role, ['admin', 'organisatie_admin'])) {
            abort(403, 'Geen toegang tot branding instellingen');
        }
        
        $organisatie = Organisatie::findOrFail($user->organisatie_id);
        
        return view('admin.branding.index', compact('organisatie'));
    }
    
    /**
     * Update branding instellingen
     */
    public function update(Request $request)
    {
        $user = auth()->user();
        
        // Validatie
        if (!$user->organisatie_id || !in_array($user->role, ['admin', 'organisatie_admin'])) {
            abort(403, 'Geen toegang');
        }
        
        $organisatie = Organisatie::findOrFail($user->organisatie_id);
        
        // Valideer input
        $validated = $request->validate([
            'primary_color' => 'required|regex:/^#[0-9A-F]{6}$/i',
            'secondary_color' => 'required|regex:/^#[0-9A-F]{6}$/i',
            'sidebar_color' => 'required|regex:/^#[0-9A-F]{6}$/i',
            'text_color' => 'required|regex:/^#[0-9A-F]{6}$/i',
            'custom_css' => 'nullable|string|max:10000',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
            'favicon' => 'nullable|image|mimes:ico,png,jpg|max:512',
            'branding_enabled' => 'boolean',
        ]);
        
        // Upload logo
        if ($request->hasFile('logo')) {
            // Verwijder oude logo
            if ($organisatie->logo_path) {
                Storage::disk('public')->delete($organisatie->logo_path);
            }
            
            $logoPath = $request->file('logo')->store('branding/logos', 'public');
            $organisatie->logo_path = $logoPath;
        }
        
        // Upload favicon
        if ($request->hasFile('favicon')) {
            // Verwijder oude favicon
            if ($organisatie->favicon_path) {
                Storage::disk('public')->delete($organisatie->favicon_path);
            }
            
            $faviconPath = $request->file('favicon')->store('branding/favicons', 'public');
            $organisatie->favicon_path = $faviconPath;
        }
        
        // Update kleuren
        $organisatie->primary_color = $validated['primary_color'];
        $organisatie->secondary_color = $validated['secondary_color'];
        $organisatie->sidebar_color = $validated['sidebar_color'];
        $organisatie->text_color = $validated['text_color'];
        $organisatie->custom_css = $validated['custom_css'] ?? null;
        $organisatie->branding_enabled = $request->has('branding_enabled');
        
        $organisatie->save();
        
        \Log::info('🎨 Branding bijgewerkt', [
            'organisatie_id' => $organisatie->id,
            'user_id' => $user->id,
            'branding_enabled' => $organisatie->branding_enabled,
        ]);
        
        return redirect()->route('admin.branding.index')
            ->with('success', 'Branding instellingen succesvol bijgewerkt!');
    }
    
    /**
     * Verwijder logo
     */
    public function deleteLogo()
    {
        $user = auth()->user();
        $organisatie = Organisatie::findOrFail($user->organisatie_id);
        
        if ($organisatie->logo_path) {
            Storage::disk('public')->delete($organisatie->logo_path);
            $organisatie->logo_path = null;
            $organisatie->save();
        }
        
        return back()->with('success', 'Logo verwijderd');
    }
    
    /**
     * Verwijder favicon
     */
    public function deleteFavicon()
    {
        $user = auth()->user();
        $organisatie = Organisatie::findOrFail($user->organisatie_id);
        
        if ($organisatie->favicon_path) {
            Storage::disk('public')->delete($organisatie->favicon_path);
            $organisatie->favicon_path = null;
            $organisatie->save();
        }
        
        return back()->with('success', 'Favicon verwijderd');
    }
}