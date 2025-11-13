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
            'klant_id' => $user->klant_id ?? 'geen',
            'user_role' => $user->role
        ]);
        
        // Avatar upload - VOOR ALLE GEBRUIKERS (klanten, medewerkers, beheerders)
        if ($request->hasFile('avatar') && $request->file('avatar')->isValid()) {
            $request->validate([
                'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            \Log::info('🖼️ Avatar upload gedetecteerd via ProfileController', [
                'user_id' => $user->id,
                'user_role' => $user->role,
                'klant_id' => $user->klant_id ?? 'geen',
                'file_original_name' => $request->file('avatar')->getClientOriginalName(),
                'file_size' => $request->file('avatar')->getSize()
            ]);
            
            // VOOR KLANTEN: update klant record
            if ($user->role === 'klant' && $user->klant_id) {
                $klant = \App\Models\Klant::findOrFail($user->klant_id);
                
                if (app()->environment('production')) {
                    // PRODUCTIE: Upload naar httpd.www/uploads/avatars/klanten
                    $uploadsPath = base_path('../httpd.www/uploads/avatars/klanten');
                    if (!file_exists($uploadsPath)) {
                        mkdir($uploadsPath, 0755, true);
                    }
                    
                    // Verwijder oude avatar
                    if ($klant->avatar_path) {
                        $oldPath = base_path('../httpd.www/uploads/' . $klant->avatar_path);
                        if (file_exists($oldPath)) {
                            unlink($oldPath);
                            \Log::info('🗑️ Oude klant avatar verwijderd', ['path' => $klant->avatar_path]);
                        }
                    }
                    
                    $fileName = $request->file('avatar')->hashName();
                    $request->file('avatar')->move($uploadsPath, $fileName);
                    $avatarPath = 'avatars/klanten/' . $fileName;
                    
                    \Log::info('✅ Klant avatar opgeslagen in httpd.www/uploads via ProfileController', [
                        'path' => $avatarPath,
                        'full_path' => $uploadsPath . '/' . $fileName,
                        'file_exists' => file_exists($uploadsPath . '/' . $fileName)
                    ]);
                } else {
                    // LOKAAL: Upload naar storage/app/public
                    if ($klant->avatar_path && \Storage::disk('public')->exists($klant->avatar_path)) {
                        \Storage::disk('public')->delete($klant->avatar_path);
                        \Log::info('🗑️ Oude klant avatar verwijderd', ['path' => $klant->avatar_path]);
                    }
                    
                    $avatarPath = $request->file('avatar')->store('avatars/klanten', 'public');
                    
                    \Log::info('✅ Klant avatar opgeslagen in storage via ProfileController', [
                        'path' => $avatarPath,
                        'full_path' => storage_path('app/public/' . $avatarPath),
                        'file_exists' => \Storage::disk('public')->exists($avatarPath)
                    ]);
                }
                
                // Update klant record
                \DB::table('klanten')
                    ->where('id', $klant->id)
                    ->update(['avatar_path' => $avatarPath, 'updated_at' => now()]);
                
                \Log::info('✅ Klant avatar bijgewerkt in DB', [
                    'klant_id' => $klant->id,
                    'avatar_path' => $avatarPath
                ]);
            } 
            // VOOR MEDEWERKERS/BEHEERDERS: update user record
            else {
                if (app()->environment('production')) {
                    // PRODUCTIE: Upload naar httpd.www/uploads/avatars/medewerkers
                    $uploadsPath = base_path('../httpd.www/uploads/avatars/medewerkers');
                    if (!file_exists($uploadsPath)) {
                        mkdir($uploadsPath, 0755, true);
                    }
                    
                    // Verwijder oude avatar
                    if ($user->avatar_path) {
                        $oldPath = base_path('../httpd.www/uploads/' . $user->avatar_path);
                        if (file_exists($oldPath)) {
                            unlink($oldPath);
                            \Log::info('🗑️ Oude user avatar verwijderd', ['path' => $user->avatar_path]);
                        }
                    }
                    
                    $fileName = $request->file('avatar')->hashName();
                    $request->file('avatar')->move($uploadsPath, $fileName);
                    $avatarPath = 'avatars/medewerkers/' . $fileName;
                    
                    \Log::info('✅ User avatar opgeslagen in httpd.www/uploads via ProfileController', [
                        'path' => $avatarPath,
                        'full_path' => $uploadsPath . '/' . $fileName,
                        'file_exists' => file_exists($uploadsPath . '/' . $fileName)
                    ]);
                } else {
                    // LOKAAL: Upload naar storage/app/public
                    if ($user->avatar_path && \Storage::disk('public')->exists($user->avatar_path)) {
                        \Storage::disk('public')->delete($user->avatar_path);
                        \Log::info('🗑️ Oude user avatar verwijderd', ['path' => $user->avatar_path]);
                    }
                    
                    $avatarPath = $request->file('avatar')->store('avatars/medewerkers', 'public');
                    
                    \Log::info('✅ User avatar opgeslagen in storage via ProfileController', [
                        'path' => $avatarPath,
                        'full_path' => storage_path('app/public/' . $avatarPath),
                        'file_exists' => \Storage::disk('public')->exists($avatarPath)
                    ]);
                }
                
                // Update user record
                $user->update(['avatar_path' => $avatarPath]);
                
                \Log::info('✅ User avatar bijgewerkt in DB', [
                    'user_id' => $user->id,
                    'avatar_path' => $avatarPath
                ]);
            }
            
            // Force full page redirect om cache te clearen
            return redirect()->route('profile.edit')
                ->with('success', 'Profielfoto succesvol bijgewerkt');
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
