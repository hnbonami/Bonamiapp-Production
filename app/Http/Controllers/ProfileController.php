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

        // Handle avatar delete flag
        if ($request->input('avatar_delete') == '1') {
            if ($user->avatar_path) {
                try { @unlink(storage_path('app/public/' . $user->avatar_path)); } catch(\Throwable $e) {}
            }
            $user->avatar_path = null;
        }

                // Avatar upload met veilige service class
        if ($request->hasFile('avatar')) {
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
                $path = SecureFileUpload::uploadAvatar(
                    $request->file('avatar'),
                    $user->avatar_path
                );
                $user->avatar_path = $path;
            } catch (\Exception $e) {
                return back()->withErrors(['avatar' => $e->getMessage()]);
            }
        }

        $user->save();

        // Redirect met success message in plaats van JSON
        return redirect()->back()->with('success', 'Profielfoto succesvol bijgewerkt');
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
