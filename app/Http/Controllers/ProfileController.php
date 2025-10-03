<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
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

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $path = $file->store('avatars', 'public');
            $user->avatar_path = $path;
        }

        $user->save();

        // If AJAX request (modal), return the partial HTML so client can update the modal content
        if ($request->ajax()) {
            return view('profile.modal', ['user' => $user]);
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
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
