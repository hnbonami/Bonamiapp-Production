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
        \Log::info('🔍 ROUTE REGISTERED: klanten using KlantController');
        
        // Zoekfunctionaliteit
        $zoekterm = $request->input('zoek');
        
        if ($zoekterm) {
            \Log::info('🔎 Zoeken naar klanten', ['zoekterm' => $zoekterm]);
            
            $klanten = Klant::where(function($query) use ($zoekterm) {
                $query->where('naam', 'like', '%' . $zoekterm . '%')
                      ->orWhere('voornaam', 'like', '%' . $zoekterm . '%')
                      ->orWhere('email', 'like', '%' . $zoekterm . '%');
            })->orderBy('created_at', 'desc')->get();
            
            \Log::info('✅ Zoekresultaten gevonden', ['aantal' => $klanten->count()]);
        } else {
            $klanten = Klant::orderBy('created_at', 'desc')->get();
        }
        
        return view('klanten.index', compact('klanten'));
    }

    public function create()
    {
        return view('klanten.create');
    }

    public function store(Request $request)
    {
        // Validatie en opslaan
        $validated = $request->validate([
            'voornaam' => 'required',
            'naam' => 'required',
            'email' => [
                'nullable',
                'email',
                'unique:users,email',
            ],
            'geboortedatum' => 'nullable|date',
            'geslacht' => 'nullable|string',
            'sport' => 'nullable|string',
            'niveau' => 'nullable|string',
            'club' => 'nullable|string',
            'herkomst' => 'nullable|string',
            'status' => 'required',
            'avatar' => 'nullable|image|max:5120',
        ], [
            'email.unique' => 'Dit e-mailadres is al in gebruik. Kies een ander e-mailadres.'
        ]);
        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars/klanten', 'public');
            $validated['avatar_path'] = $path;
        }

        $klant = Klant::create($validated);

        // User aanmaken
        if (!empty($validated['email'])) {
            $password = Str::random(12);
            $user = User::create([
                'name' => $validated['voornaam'] . ' ' . $validated['naam'],
                'email' => $validated['email'],
                'password' => Hash::make($password),
                'role' => 'klant',
            ]);
            $loginUrl = url('/login');
            Mail::to($user->email)->send(new AccountCreatedMail($user->name, $user->email, $password, $loginUrl));
        }
        return redirect()->route('klanten.index')->with('success', 'Klant toegevoegd en loginmail verzonden!');
    }
    public function show($id)
    {
        $klant = Klant::with('documenten')->findOrFail($id);
        
        return view('klanten.show', compact('klant'));
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

        // Verwijder oude avatar indien aanwezig
        if ($klant->avatar_path && \Storage::disk('public')->exists($klant->avatar_path)) {
            \Storage::disk('public')->delete($klant->avatar_path);
        }

        $path = $request->file('avatar')->store('avatars/klanten', 'public');
        $klant->update(['avatar_path' => $path]);

        $klant->save();
    
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
}
