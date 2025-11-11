<?php

namespace App\Http\Controllers;

use App\Models\OrganisatieRapportInstelling;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RapportInstellingenController extends Controller
{
    /**
     * Check admin toegang voor rapport instellingen
     */
    private function checkAdminAccess()
    {
        $user = auth()->user();
        
        if (!in_array($user->role, ['admin', 'organisatie_admin', 'superadmin'])) {
            abort(403, 'Geen toegang. Alleen administrators hebben toegang tot rapport instellingen.');
        }

        // Check of organisatie de feature heeft
        if ($user->role !== 'superadmin') {
            $organisatie = $user->organisatie;
            
            if (!$organisatie || !$organisatie->hasFeature('rapporten_opmaken')) {
                abort(403, 'Deze feature is niet actief voor jouw organisatie.');
            }
        }
    }

    /**
     * Toon rapport instellingen pagina
     */
    public function index()
    {
        $this->checkAdminAccess();
        
        $user = auth()->user();
        $organisatie = $user->organisatie;
        
        // Haal bestaande instellingen op of maak nieuwe met defaults
        $instellingen = OrganisatieRapportInstelling::firstOrCreate(
            ['organisatie_id' => $organisatie->id],
            OrganisatieRapportInstelling::getDefaults()
        );
        
        return view('admin.rapporten.instellingen', compact('instellingen', 'organisatie'));
    }

    /**
     * Update rapport instellingen
     */
    public function update(Request $request)
    {
        $this->checkAdminAccess();
        
        $user = auth()->user();
        $organisatie = $user->organisatie;
        
        $validated = $request->validate([
            'header_tekst' => 'nullable|string|max:1000',
            'footer_tekst' => 'nullable|string|max:1000',
            'inleidende_tekst' => 'nullable|string',
            'laatste_blad_tekst' => 'nullable|string',
            'disclaimer_tekst' => 'nullable|string',
            'primaire_kleur' => 'nullable|string|max:7',
            'secundaire_kleur' => 'nullable|string|max:7',
            'lettertype' => 'required|in:Arial,Tahoma,Calibri,Helvetica',
            'paginanummering_tonen' => 'boolean',
            'paginanummering_positie' => 'required|in:rechtsonder,rechtsboven,linksonder,linksboven,midden',
            'contact_adres' => 'nullable|string|max:255',
            'contact_telefoon' => 'nullable|string|max:50',
            'contact_email' => 'nullable|email|max:255',
            'contact_website' => 'nullable|url|max:255',
            'contactgegevens_in_footer' => 'boolean',
            'qr_code_tonen' => 'boolean',
            'qr_code_url' => 'nullable|url|max:255',
            'qr_code_positie' => 'required|in:rechtsonder,linksboven,footer',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
            'voorblad_foto' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $instellingen = OrganisatieRapportInstelling::firstOrNew(
            ['organisatie_id' => $organisatie->id]
        );

        // Upload logo
        if ($request->hasFile('logo')) {
            // Verwijder oude logo
            if ($instellingen->logo_path) {
                Storage::disk('public')->delete($instellingen->logo_path);
            }
            
            $logoPath = $request->file('logo')->store('rapporten/logos', 'public');
            $validated['logo_path'] = $logoPath;
        }

        // Upload voorblad foto
        if ($request->hasFile('voorblad_foto')) {
            // Verwijder oude foto
            if ($instellingen->voorblad_foto_path) {
                Storage::disk('public')->delete($instellingen->voorblad_foto_path);
            }
            
            $fotoPath = $request->file('voorblad_foto')->store('rapporten/voorbladfotos', 'public');
            $validated['voorblad_foto_path'] = $fotoPath;
        }

        $instellingen->fill($validated);
        $instellingen->save();

        return redirect()->route('admin.rapporten.instellingen')
            ->with('success', 'Rapport instellingen succesvol bijgewerkt!');
    }

    /**
     * Verwijder logo
     */
    public function deleteLogo()
    {
        $this->checkAdminAccess();
        
        $user = auth()->user();
        $instellingen = OrganisatieRapportInstelling::where('organisatie_id', $user->organisatie_id)->first();
        
        if ($instellingen && $instellingen->logo_path) {
            Storage::disk('public')->delete($instellingen->logo_path);
            $instellingen->logo_path = null;
            $instellingen->save();
        }

        return response()->json(['success' => true, 'message' => 'Logo verwijderd']);
    }

    /**
     * Verwijder voorblad foto
     */
    public function deleteVoorbladFoto()
    {
        $this->checkAdminAccess();
        
        $user = auth()->user();
        $instellingen = OrganisatieRapportInstelling::where('organisatie_id', $user->organisatie_id)->first();
        
        if ($instellingen && $instellingen->voorblad_foto_path) {
            Storage::disk('public')->delete($instellingen->voorblad_foto_path);
            $instellingen->voorblad_foto_path = null;
            $instellingen->save();
        }

        return response()->json(['success' => true, 'message' => 'Voorblad foto verwijderd']);
    }

    /**
     * Reset naar default instellingen
     */
    public function reset()
    {
        $this->checkAdminAccess();
        
        $user = auth()->user();
        $instellingen = OrganisatieRapportInstelling::where('organisatie_id', $user->organisatie_id)->first();
        
        if ($instellingen) {
            // Verwijder uploads
            if ($instellingen->logo_path) {
                Storage::disk('public')->delete($instellingen->logo_path);
            }
            if ($instellingen->voorblad_foto_path) {
                Storage::disk('public')->delete($instellingen->voorblad_foto_path);
            }
            
            // Reset naar defaults
            $instellingen->fill(OrganisatieRapportInstelling::getDefaults());
            $instellingen->logo_path = null;
            $instellingen->voorblad_foto_path = null;
            $instellingen->save();
        }

        return redirect()->route('admin.rapporten.instellingen')
            ->with('success', 'Rapport instellingen gereset naar standaard waarden!');
    }
}
