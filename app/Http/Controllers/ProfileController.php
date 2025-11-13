<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Services\SecureFileUpload;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Return the profile modal partial HTML (AJAX-loaded).
     */
    public function modal(Request $request): View
    {
        return view('profile.modal', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request)
    {
        $user = $request->user();
        
        \Log::info('🔄 Profile update gestart', [
            'user_id' => $user->id,
            'has_avatar' => $request->hasFile('avatar'),
            'klant_id' => $user->klant_id ?? 'geen'
        ]);
        
        // Avatar upload - update KLANT record voor alle gebruikers met klant_id
        if ($request->hasFile('avatar') && $user->klant_id) {
            $request->validate([
                'avatar' => SecureFileUpload::getAvatarValidationRules()
            ], [
                'avatar.required' => 'Selecteer een afbeelding om te uploaden.',
                'avatar.image' => 'Het bestand moet een afbeelding zijn.',
                'avatar.mimes' => 'Alleen JPG, JPEG, PNG, GIF en WebP bestanden zijn toegestaan.',
                'avatar.max' => 'De afbeelding mag maximaal 2MB groot zijn.',
                'avatar.dimensions' => 'De afbeelding moet minimaal 100x100 pixels zijn en maximaal 4000x4000 pixels.'
            ]);

            try {
                // Haal klant record op
                $klant = \App\Models\Klant::findOrFail($user->klant_id);
                
                // Upload avatar en update klant record
                $path = SecureFileUpload::uploadAvatar(
                    $request->file('avatar'),
                    $klant->avatar // Gebruik klant->avatar in plaats van user->avatar_path
                );
                
                $klant->avatar = $path;
                $klant->touch(); // Update timestamp voor cache busting
                $klant->save();
                
                \Log::info('✅ Avatar succesvol geüpload via profile', [
                    'user_id' => $user->id,
                    'klant_id' => $klant->id,
                    'avatar_path' => $path
                ]);
                
                // Force full page redirect om cache te clearen
                return redirect()->route('profile.edit', ['tab' => 'personal'])
                    ->with('success', 'Profielfoto succesvol bijgewerkt');
                
            } catch (\Exception $e) {
                \Log::error('❌ Avatar upload gefaald', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
                return back()->withErrors(['avatar' => $e->getMessage()]);
            }
        }
        
        // Update andere user velden indien nodig
        $user->fill($request->validated());

        // If separate first/last names were provided, keep a combined display name in `name`
        $voornaam = trim((string) $request->input('voornaam', ''));
        $naam = trim((string) $request->input('naam', ''));
        if ($voornaam !== '' || $naam !== '') {
            $combined = trim($voornaam . ' ' . $naam);
            if ($combined !== '') {
                $user->name = $combined;
            }
        }

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        // Redirect met success message in plaats van JSON
        return redirect()->back()->with('success', 'Profiel succesvol bijgewerkt');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
