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
        
        // Bouw adres string uit organisatie gegevens
        $adresString = null;
        if ($organisatie->adres || $organisatie->postcode || $organisatie->plaats) {
            $adresParts = array_filter([
                $organisatie->adres,
                $organisatie->postcode,
                $organisatie->plaats
            ]);
            $adresString = implode(', ', $adresParts);
        }
        
        // Haal bestaande instellingen op of maak nieuwe met defaults + organisatie gegevens
        $instellingen = OrganisatieRapportInstelling::firstOrCreate(
            ['organisatie_id' => $organisatie->id],
            array_merge(
                OrganisatieRapportInstelling::getDefaults(),
                [
                    // Pre-fill met organisatiegegevens als deze nog niet bestaan
                    'contact_adres' => $adresString,
                    'contact_telefoon' => $organisatie->telefoon,
                    'contact_email' => $organisatie->email,
                    'contact_website' => null, // Website staat niet in organisaties tabel
                ]
            )
        );
        
        // SMART PRE-FILL: Als contactvelden nog leeg zijn, vul ze dan met organisatiegegevens
        $isUpdated = false;
        
        if (empty($instellingen->contact_adres) && $adresString) {
            $instellingen->contact_adres = $adresString;
            $isUpdated = true;
        }
        
        if (empty($instellingen->contact_telefoon) && $organisatie->telefoon) {
            $instellingen->contact_telefoon = $organisatie->telefoon;
            $isUpdated = true;
        }
        
        // Email: vul ALTIJD in met organisatie email, behalve als er een custom email is ingesteld
        if ($organisatie->email) {
            $currentEmail = $instellingen->contact_email;
            $isDefaultOrEmpty = empty($currentEmail) || $currentEmail === 'info@performancepulse.nl';
            
            // Als het leeg is of de default waarde heeft, gebruik organisatie email
            if ($isDefaultOrEmpty) {
                $instellingen->contact_email = $organisatie->email;
                $isUpdated = true;
            }
        }
        
        // Sla automatisch op als we updates hebben gedaan
        if ($isUpdated) {
            $instellingen->save();
            \Log::info('📝 Contactgegevens automatisch vooraf ingevuld', [
                'organisatie_id' => $organisatie->id,
                'organisatie_naam' => $organisatie->naam,
                'adres' => $instellingen->contact_adres,
                'telefoon' => $instellingen->contact_telefoon,
                'email' => $instellingen->contact_email
            ]);
        }
        
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
        
        // Log upload info voor debugging
        \Log::info('📸 Upload attempt', [
            'has_logo' => $request->hasFile('logo'),
            'has_voorblad' => $request->hasFile('voorblad_foto'),
            'logo_valid' => $request->hasFile('logo') ? $request->file('logo')->isValid() : null,
            'voorblad_valid' => $request->hasFile('voorblad_foto') ? $request->file('voorblad_foto')->isValid() : null,
            'logo_error' => $request->hasFile('logo') ? $request->file('logo')->getError() : null,
            'voorblad_error' => $request->hasFile('voorblad_foto') ? $request->file('voorblad_foto')->getError() : null,
            'logo_size' => $request->hasFile('logo') ? $request->file('logo')->getSize() : null,
            'voorblad_size' => $request->hasFile('voorblad_foto') ? $request->file('voorblad_foto')->getSize() : null,
        ]);
        
        // Validatie
        $validated = $request->validate([
            'header_tekst' => 'nullable|string|max:255',
            'footer_tekst' => 'nullable|string|max:255',
            'logo' => 'nullable|file|mimes:jpeg,jpg,png,svg|max:2048',
            'voorblad_foto' => 'nullable|file|mimes:jpeg,jpg,png|max:20480',
            'inleidende_tekst' => 'nullable|string',
            'laatste_blad_tekst' => 'nullable|string',
            'disclaimer_tekst' => 'nullable|string',
            'primaire_kleur' => 'nullable|string|max:7',
            'secundaire_kleur' => 'nullable|string|max:7',
            'lettertype' => 'nullable|string|max:50',
            'contact_adres' => 'nullable|string|max:255',
            'contact_telefoon' => 'nullable|string|max:50',
            'contact_email' => 'nullable|email|max:255',
            'contact_website' => 'nullable|url|max:255',
            'contactgegevens_in_footer' => 'nullable|boolean',
            'qr_code_tonen' => 'nullable|boolean',
            'qr_code_url' => 'nullable|url|max:255',
            'qr_code_positie' => 'nullable|in:rechtsonder,linksboven,footer',
            'paginanummering_tonen' => 'nullable|boolean',
            'paginanummering_positie' => 'nullable|in:rechtsonder,rechtsboven,linksonder,linksboven,midden',
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
            $file = $request->file('voorblad_foto');
            
            \Log::info('📸 Voorblad foto upload details', [
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'error' => $file->getError(),
                'is_valid' => $file->isValid(),
            ]);
            
            if (!$file->isValid()) {
                \Log::error('❌ Voorblad foto upload FAILED', [
                    'error_code' => $file->getError(),
                    'error_message' => $file->getErrorMessage(),
                ]);
                
                return back()->withErrors([
                    'voorblad_foto' => 'Upload mislukt: ' . $file->getErrorMessage() . ' (Error code: ' . $file->getError() . ')'
                ])->withInput();
            }
            
            // Verwijder oude foto
            if ($instellingen->voorblad_foto_path) {
                Storage::disk('public')->delete($instellingen->voorblad_foto_path);
            }
            
            try {
                $fotoPath = $file->store('rapporten/voorbladfotos', 'public');
                $validated['voorblad_foto_path'] = $fotoPath;
                \Log::info('✅ Voorblad foto uploaded', ['path' => $fotoPath]);
            } catch (\Exception $e) {
                \Log::error('❌ Storage failed', ['error' => $e->getMessage()]);
                return back()->withErrors(['voorblad_foto' => 'Opslaan mislukt: ' . $e->getMessage()])->withInput();
            }
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
