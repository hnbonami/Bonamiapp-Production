<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Klant;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\AccountCreatedMail;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\KlantenImport;
use App\Exports\KlantenExport;
use App\Exports\KlantenTemplateExport;

class KlantController extends Controller
{
    public function verwijderViaPost(Request $request, Klant $klant)
    {
        $klant->delete();
        return redirect()->route('klanten.index')->with('success', 'Klant succesvol verwijderd.');
    }
    public function destroy(Klant $klant)
    {
        $id = $klant->id;
        // Verwijder gekoppelde user
        if ($klant->email) {
            $user = \App\Models\User::where('email', $klant->email)->first();
            if ($user) {
                $user->delete();
            }
        }
        $klant->delete();
        \Log::info('Klant en gekoppelde user verwijderd', ['id' => $id]);
        return redirect()->route('klanten.index')->with('success', 'Klant en gekoppelde gebruiker succesvol verwijderd.');
    }
    public function edit(Klant $klant)
    {
        return view('klanten.edit', compact('klant'));
    }

    public function update(Request $request, Klant $klant)
    {
        // 🚨 KRITIEKE DEBUG - JUISTE CONTROLLER!
        \Log::info('🎯 KLANTCONTROLLER UPDATE AANGEROEPEN!', $request->all());
        \Log::info('🎯 KLANT VOOR UPDATE:', $klant->toArray());
        
        // VALIDATIE MET ALLE JUISTE VELDEN
        $validatedData = $request->validate([
            'voornaam' => 'required|string|max:255',
            'naam' => 'required|string|max:255',
            'email' => 'required|email|unique:klanten,email,' . $klant->id,
            'telefoonnummer' => 'nullable|string|max:20',
            'geboortedatum' => 'nullable|date',
            'geslacht' => 'nullable|in:Man,Vrouw,Anders',
            'straatnaam' => 'nullable|string|max:255', // ✅ TOEGEVOEGD!
            'huisnummer' => 'nullable|string|max:50',  // ✅ TOEGEVOEGD!
            'postcode' => 'nullable|string|max:10',    // ✅ TOEGEVOEGD!
            'stad' => 'nullable|string|max:255',       // ✅ TOEGEVOEGD!
            'status' => 'nullable|string|max:255',
            'sport' => 'nullable|string|max:255',
            'niveau' => 'nullable|string|max:255',
            'club' => 'nullable|string|max:255',
            'herkomst' => 'nullable|string|max:255',
            // ...andere velden...
        ]);

        // DEBUG: Check validated data
        \Log::info('🎯 VALIDATED DATA:', $validatedData);

        // ✅ GEBRUIK VALIDATED DATA (niet $request->all())
        $klant->update($validatedData);
        
        // Na update ook loggen
        \Log::info('🎯 KLANT NA UPDATE:', $klant->fresh()->toArray());
        
        return redirect()->route('klanten.show', $klant)->with('success', 'Klant succesvol bijgewerkt!');
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        \Log::info('🔍 Klanten Index START', [
            'auth_user_id' => auth()->id(),
            'auth_user_org_id' => auth()->user()->organisatie_id,
            'auth_user_role' => auth()->user()->role
        ]);

        // Filter op huidige organisatie - DIRECT in query
        $orgId = auth()->user()->organisatie_id;
        $query = Klant::where('organisatie_id', $orgId);

        \Log::info('📊 Klanten Query', [
            'filtering_on_org_id' => $orgId,
            'sql' => $query->toSql(),
            'bindings' => $query->getBindings()
        ]);

        $zoekterm = $request->input('zoek');
        
        // 🔥 DEBUG: Log user info
        \Log::info('🔍 KLANTEN INDEX:', [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_role' => $user->role,
            'user_org_id' => $user->organisatie_id
        ]);
        
        // Start query
        
        // Filter op organisatie (behalve superadmin)
        if ($user->role !== 'superadmin' && $user->organisatie_id) {
            $query->where('organisatie_id', $user->organisatie_id);
            \Log::info('✅ Filter toegepast op organisatie: ' . $user->organisatie_id);
        } else {
            \Log::warning('⚠️ GEEN FILTER - Role: ' . $user->role . ', Org ID: ' . $user->organisatie_id);
        }
        
        // Zoeken
        if ($zoekterm) {
            $query->where(function($q) use ($zoekterm) {
                $q->where('naam', 'like', '%' . $zoekterm . '%')
                  ->orWhere('voornaam', 'like', '%' . $zoekterm . '%')
                  ->orWhere('email', 'like', '%' . $zoekterm . '%');
            });
        }
        
        $klanten = $query->orderBy('created_at', 'desc')->paginate(15);
        
        \Log::info('✅ Klanten gevonden', [
            'total' => $klanten->total(),
            'eerste_3' => $klanten->take(3)->map(fn($k) => [
                'id' => $k->id,
                'naam' => $k->naam,
                'organisatie_id' => $k->organisatie_id
            ])
        ]);
        
        return view('klanten.index', compact('klanten'));
    }

    public function create()
    {
        return view('klanten.create');
    }

    public function store(Request $request)
    {
        // Check of email uniek is binnen de organisatie voor klanten
        $existingKlantInOrg = User::where('email', $request->email)
            ->where('organisatie_id', auth()->user()->organisatie_id)
            ->where('role', 'klant')
            ->first();
        
        if ($existingKlantInOrg) {
            return back()->withErrors([
                'email' => 'Dit emailadres wordt al gebruikt door een klant binnen je organisatie.'
            ])->withInput();
        }

        $validated = $request->validate([
            'voornaam' => 'required|string|max:255',
            'naam' => 'required|string|max:255',
            'email' => 'required|email', // Geen |unique:users,email meer!
            'telefoon' => 'nullable|string|max:20',
            'geboortedatum' => 'nullable|date',
            'geslacht' => 'nullable|in:Man,Vrouw,Anders',
            'straat' => 'nullable|string|max:255',
            'huisnummer' => 'nullable|string|max:10',
            'postcode' => 'nullable|string|max:10',
            'stad' => 'nullable|string|max:100',
            'land' => 'nullable|string|max:100',
            'status' => 'nullable|in:Actief,Inactief',
            'notities' => 'nullable|string',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // 🔥 KRITIEK: Voeg organisatie_id toe ALS EERSTE!
        $validated['organisatie_id'] = auth()->user()->organisatie_id;
        $validated['name'] = $validated['voornaam'] . ' ' . $validated['naam'];
        $validated['role'] = 'klant';
        $validated['password'] = \Hash::make(\Illuminate\Support\Str::random(12)); // Genereer random wachtwoord
        $validated['status'] = $validated['status'] ?? 'Actief';

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars/klanten', 'public');
            $validated['avatar_path'] = $path;
        }

        // Log voor debugging
        \Log::info('🔥 Creating klant with data:', $validated);

        // Maak klant aan
        $klant = Klant::create($validated);
        
        // Refresh de klant zodat alle relaties up-to-date zijn
        $klant->refresh();
        
        // NIEUW: Maak automatisch user account aan voor de klant
        $this->createUserAccountForKlant($klant);

        return redirect()->route('klanten.show', $klant->id)
            ->with('success', 'Klant succesvol toegevoegd!');
    }
    public function show(Klant $klant)
    {
        // Laad gerelateerde data met correcte relatie namen
        $klant->load(['bikefits', 'inspanningstesten']);

        // Maak user beschikbaar voor de view
        $user = auth()->user();

        return view('klanten.show', compact('klant', 'user'));
    }
    public function sendInvitation(Request $request, Klant $klant)
    {
        // Zoek de user op basis van e-mail
        $user = User::where('email', $klant->email)->first();
        if ($user) {
            // Genereer een nieuw wachtwoord
            $password = Str::random(12);
            $user->password = Hash::make($password);
            $user->save();
            $loginUrl = url('/login');
            Mail::to($user->email)->send(new AccountCreatedMail($user->name, $user->email, $password, $loginUrl));
            return back()->with('success', 'Uitnodiging opnieuw verzonden!');
        } else {
            return back()->with('error', 'Er is geen gebruiker gekoppeld aan dit e-mailadres.');
        }
    }

    public function updateAvatar(Request $request, Klant $klant)
    {
        $request->validate([
            'avatar' => 'required|image|max:5120',
        ]);

        // Upload nieuwe avatar
        $path = $request->file('avatar')->store('avatars/klanten', 'public');
        
        // Verwijder oude avatar indien aanwezig (van klant EN user)
        if ($klant->avatar_path && \Storage::disk('public')->exists($klant->avatar_path)) {
            \Storage::disk('public')->delete($klant->avatar_path);
        }
        if ($klant->user && $klant->user->avatar_path && \Storage::disk('public')->exists($klant->user->avatar_path)) {
            \Storage::disk('public')->delete($klant->user->avatar_path);
        }
        
        // Update BEIDE: klant én gekoppelde user
        $klant->update(['avatar_path' => $path]);
        
        if ($klant->user) {
            $klant->user->update(['avatar_path' => $path]);
            \Log::info('🖼️ Avatar bijgewerkt voor klant EN user', [
                'klant_id' => $klant->id,
                'user_id' => $klant->user->id,
                'avatar_path' => $path
            ]);
        }
    
        // Redirect met success message
        return redirect()->back()->with('success', 'Profielfoto succesvol bijgewerkt');
    }

    /**
     * Show import form for klanten
     */
    public function showImport()
    {
        return view('klanten.import');
    }
    
    /**
     * Import klanten from Excel file
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240'
        ]);
        
        try {
            Excel::import(new KlantenImport, $request->file('file'));
            
            return redirect()->back()->with('success', 'Klanten succesvol geïmporteerd!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Import mislukt: ' . $e->getMessage());
        }
    }
    
    /**
     * Export all klanten to Excel
     */
    public function exportKlanten()
    {
        return Excel::download(new KlantenExport, 'klanten_export_' . date('Y-m-d') . '.xlsx');
    }
    
    /**
     * Download Excel template for klanten import
     */
    public function downloadTemplate()
    {
        return Excel::download(new KlantenTemplateExport, 'klanten_import_template.xlsx');
    }
    
    /**
     * Maak automatisch een user account aan voor een klant
     */
    private function createUserAccountForKlant(\App\Models\Klant $klant)
    {
        // Check of er al een user bestaat met dit email
        $existingUser = \App\Models\User::where('email', $klant->email)->first();
        
        if ($existingUser) {
            \Log::info('User bestaat al voor klant', [
                'klant_id' => $klant->id,
                'email' => $klant->email,
                'existing_user_id' => $existingUser->id
            ]);
            return $existingUser;
        }
        
        // Maak nieuwe user aan
        try {
            $user = \App\Models\User::create([
                'name' => $klant->naam,
                'email' => $klant->email,
                'password' => \Hash::make(\Str::random(16)), // Random wachtwoord
                'role' => 'klant',
                'organisatie_id' => $klant->organisatie_id,
                'status' => 'active',
                'email_verified_at' => null, // Klant moet email verifiëren
            ]);
            
            \Log::info('✅ User account aangemaakt voor geïmporteerde klant', [
                'klant_id' => $klant->id,
                'user_id' => $user->id,
                'email' => $klant->email
            ]);
            
            // Optioneel: stuur uitnodigingsmail (implementeer dit later indien gewenst)
            // $user->sendEmailVerificationNotification();
            
            return $user;
            
        } catch (\Exception $e) {
            \Log::error('❌ Fout bij aanmaken user account voor klant', [
                'klant_id' => $klant->id,
                'email' => $klant->email,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
}
