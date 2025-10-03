<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AvatarController extends Controller
{
    /**
     * Upload avatar for current user and sync with klant if needed
     */
    public function upload(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = auth()->user();
        
        // Delete old avatar if exists
        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
        }
        
        // Store new avatar
        $avatarPath = $request->file('avatar')->store('avatars', 'public');
        
        // Sync avatar between user and klant
        $user->syncAvatarWithKlant($avatarPath);
        
        return redirect()->back()->with('success', 'Profielfoto bijgewerkt!');
    }

    /**
     * Upload avatar for specific klant and sync with user if needed
     */
    public function uploadForKlant(Request $request, \App\Models\Klant $klant)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        
        // Delete old avatar if exists
        if ($klant->avatar_path) {
            Storage::disk('public')->delete($klant->avatar_path);
        }
        
        // Store new avatar
        $avatarPath = $request->file('avatar')->store('avatars', 'public');
        
        // Sync avatar between klant and user
        $klant->syncAvatarWithUser($avatarPath);
        
        return redirect()->back()->with('success', 'Klant profielfoto bijgewerkt!');
    }

    /**
     * Delete avatar
     */
    public function delete()
    {
        $user = auth()->user();
        
        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
            $user->syncAvatarWithKlant(null);
        }
        
        return redirect()->back()->with('success', 'Profielfoto verwijderd!');
    }
}