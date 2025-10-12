<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Medewerker;
use App\Models\User;
use App\Models\InvitationToken;
use App\Services\EmailIntegrationService;
use App\Helpers\MailHelper;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class MedewerkerController extends Controller
{
    public function index()
    {
        $medewerkers = \App\Models\Medewerker::all();
        return view('medewerkers.index', compact('medewerkers'));
    }

    public function create()
    {
    // Toon het formulier om een medewerker toe te voegen
    return view('medewerkers.create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'voornaam' => 'required|string|max:255',
            'achternaam' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'telefoonnummer' => 'nullable|string|max:255',
            'geboortedatum' => 'nullable|date',
            'geslacht' => 'nullable|in:Man,Vrouw,Anders',
            'straatnaam' => 'nullable|string|max:255',
            'huisnummer' => 'nullable|string|max:50',
            'postcode' => 'nullable|string|max:10',
            'stad' => 'nullable|string|max:255',
            'functie' => 'nullable|string|max:255',
            'rol' => 'nullable|string|max:255',
            'afdeling' => 'nullable|string|max:255',
            'salaris' => 'nullable|numeric',
            'toegangsniveau' => 'nullable|in:admin,manager,medewerker,gast',
            'toegangsrechten' => 'nullable|array',
            'uurloon' => 'nullable|numeric',
            'status' => 'nullable|string|max:255',
            'contract_type' => 'nullable|string|max:255',
            'startdatum' => 'nullable|date',
            'bikefit' => 'nullable|boolean',
            'inspanningstest' => 'nullable|boolean',
            'notities' => 'nullable|string',
            'avatar' => 'nullable|image|max:5120',
        ]);

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $validatedData['avatar_path'] = $avatarPath;
        }

        $medewerker = Medewerker::create($validatedData);

        return redirect()->route('medewerkers.show', $medewerker)
                         ->with('success', 'Medewerker succesvol aangemaakt!');
    }    public function show(\App\Models\Medewerker $medewerker)
    {
        return view('medewerkers.show', compact('medewerker'));
    }

    public function edit(\App\Models\Medewerker $medewerker)
    {
        return view('medewerkers.edit', compact('medewerker'));
    }

    public function update(Request $request, Medewerker $medewerker)
    {
        // DEBUG: Check wat er wordt verstuurd
        \Log::info('Medewerker update data:', $request->all());
        \Log::info('Medewerker before update:', $medewerker->toArray());

        $validatedData = $request->validate([
            'voornaam' => 'required|string|max:255',
            'achternaam' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'telefoonnummer' => 'nullable|string|max:255',
            'geboortedatum' => 'nullable|date',
            'geslacht' => 'nullable|in:Man,Vrouw,Anders',
            'straatnaam' => 'nullable|string|max:255',
            'huisnummer' => 'nullable|string|max:50',
            'postcode' => 'nullable|string|max:10',
            'stad' => 'nullable|string|max:255',
            'functie' => 'nullable|string|max:255',
            'rol' => 'nullable|string|max:255',
            'afdeling' => 'nullable|string|max:255',
            'salaris' => 'nullable|numeric',
            'toegangsniveau' => 'nullable|in:admin,manager,medewerker,gast',
            'toegangsrechten' => 'nullable|array',
            'uurloon' => 'nullable|numeric',
            'status' => 'nullable|string|max:255',
            'contract_type' => 'nullable|string|max:255',
            'startdatum' => 'nullable|date',
            'bikefit' => 'nullable|boolean',
            'inspanningstest' => 'nullable|boolean',
            'notities' => 'nullable|string',
            'avatar' => 'nullable|image|max:5120',
        ]);

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $validatedData['avatar_path'] = $avatarPath;
        }

        // Remove any 'naam' from the data if it exists
        unset($validatedData['naam']);

        \Log::info('Validated data:', $validatedData);

        $medewerker->update($validatedData);
        
        // DEBUG: Check het resultaat na update
        \Log::info('Medewerker after update:', $medewerker->fresh()->toArray());

        return redirect()->route('medewerkers.show', $medewerker)
                         ->with('success', 'Medewerker succesvol bijgewerkt!');
    }

    public function destroy(\App\Models\Medewerker $medewerker)
    {
        // Verwijder gekoppelde user
        if ($medewerker->email) {
            $user = \App\Models\User::where('email', $medewerker->email)->first();
            if ($user) {
                $user->delete();
            }
        }
        $medewerker->delete();
        return redirect()->route('medewerkers.index')->with('success', 'Medewerker en gekoppelde gebruiker verwijderd!');
    }

    public function updateAvatar(Request $request, \App\Models\Medewerker $medewerker)
    {
        $request->validate([
            'avatar' => 'required|image|max:5120',
        ]);

        if ($medewerker->avatar_path && \Storage::disk('public')->exists($medewerker->avatar_path)) {
            \Storage::disk('public')->delete($medewerker->avatar_path);
        }

        $path = $request->file('avatar')->store('avatars/medewerkers', 'public');
        $medewerker->update(['avatar_path' => $path]);

        return back()->with('success', 'Profielfoto bijgewerkt.');
    }

    // Add invitation functionality for medewerkers
    public function sendInvitation(Request $request, \App\Models\Medewerker $medewerker)
    {
        try {
            // Generate temporary password
            $temporaryPassword = \Str::random(12);
            
            // Create or update User record for login
            $user = \App\Models\User::updateOrCreate(
                ['email' => $medewerker->email],
                [
                    'name' => $medewerker->voornaam . ' ' . $medewerker->achternaam,
                    'password' => \Hash::make($temporaryPassword),
                    'email_verified_at' => now(),
                ]
            );
            
            // Create invitation token
            $invitationToken = \App\Models\InvitationToken::createForMedewerker($medewerker->email, $temporaryPassword);
            
            // Send invitation email
            $emailSent = \App\Helpers\MailHelper::sendMedewerkerInvitation($medewerker, $temporaryPassword, $invitationToken);
            
            // Update medewerker to show invitation was sent
            $medewerker->update(['laatste_uitnodiging' => now()]);
            
            return redirect()->back()->with('success', 'Uitnodiging verstuurd naar ' . $medewerker->naam);
            
        } catch (\Exception $e) {
            \Log::error('Medewerker invitation sending failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Fout bij versturen uitnodiging: ' . $e->getMessage());
        }
    }
}
