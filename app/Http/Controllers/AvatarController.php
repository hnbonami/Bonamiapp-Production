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
        
        try {
            // Verwijder oude avatar indien aanwezig
            if ($klant->avatar && \Storage::disk('public')->exists($klant->avatar)) {
                \Storage::disk('public')->delete($klant->avatar);
                \Log::info('🗑️ Oude avatar verwijderd', ['path' => $klant->avatar]);
            }

            // Upload nieuwe avatar naar avatars/klanten subdirectory (GEFIXED!)
            $path = $request->file('avatar')->store('avatars/klanten', 'public');
            
            // Update klant record
            $klant->avatar = $path;
            $klant->save();

            \Log::info('✅ Avatar uploaded via AvatarController', [
                'klant_id' => $klant->id,
                'path' => $path,
                'file_exists' => file_exists(storage_path('app/public/' . $path))
            ]);

            return response()->json([
                'success' => true,
                'avatar_url' => asset('storage/' . $path),
                'message' => 'Avatar succesvol geüpload'
            ]);

        } catch (\Exception $e) {
            \Log::error('❌ Avatar upload failed in AvatarController', [
                'error' => $e->getMessage(),
                'klant_id' => $klant->id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Avatar upload mislukt: ' . $e->getMessage()
            ], 500);
        }
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