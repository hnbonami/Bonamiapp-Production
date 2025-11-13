<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class ProfileSettingsController extends Controller
{
    public function index()
    {
        // Fresh user data from database
        $user = User::find(Auth::id());
        
        // Debug: Check if new fields exist
        $hasNewFields = [
            'first_name' => \Schema::hasColumn('users', 'first_name'),
            'last_name' => \Schema::hasColumn('users', 'last_name'),
            'phone' => \Schema::hasColumn('users', 'phone'),
            'birth_date' => \Schema::hasColumn('users', 'birth_date'),
            'avatar_path' => \Schema::hasColumn('users', 'avatar_path'),
        ];
        
        // If fields don't exist, we need to run migration
        if (!$hasNewFields['first_name']) {
            // Try to populate from existing 'name' field
            if ($user->name && !$user->first_name) {
                $nameParts = explode(' ', $user->name, 2);
                $user->first_name = $nameParts[0] ?? '';
                $user->last_name = $nameParts[1] ?? '';
            }
        }
        
        // Calculate profile completion percentage
        $completion = $this->calculateProfileCompletion($user);
        
        // Get available languages
        $languages = [
            'nl' => 'Nederlands',
            'fr' => 'Français', 
            'en' => 'English'
        ];
        
        return view('profile.settings', compact('user', 'completion', 'languages', 'hasNewFields'));
    }
    
    public function updatePersonal(Request $request)
    {
        $user = Auth::user();
        
        try {
            // Validatie met alle mogelijke veldnamen
            $validated = $request->validate([
                'first_name' => 'nullable|string|max:255',
                'last_name' => 'nullable|string|max:255',
                'voornaam' => 'nullable|string|max:255',
                'achternaam' => 'nullable|string|max:255',
                'name' => 'nullable|string|max:255', 
                'email' => 'required|email|unique:users,email,' . $user->id,
                'birth_date' => 'nullable|date',
                'geboortedatum' => 'nullable|date',
                'address' => 'nullable|string|max:255',
                'adres' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:100',
                'stad' => 'nullable|string|max:100',
                'postal_code' => 'nullable|string|max:10',
                'postcode' => 'nullable|string|max:10',
                'phone' => 'nullable|string|max:20',
                'telefoonnummer' => 'nullable|string|max:20',
            ]);
            
            // Update alle velden
            $updateData = ['email' => $validated['email']];
            
            // Voeg naam velden toe
            $firstName = $validated['first_name'] ?? $validated['voornaam'] ?? '';
            $lastName = $validated['last_name'] ?? $validated['achternaam'] ?? '';
            
            if ($firstName) $updateData['voornaam'] = $firstName;
            if ($lastName) $updateData['naam'] = $lastName;
            if ($firstName || $lastName) {
                $updateData['name'] = trim($firstName . ' ' . $lastName);
            }
            
            // Voeg andere velden toe
            if (!empty($validated['birth_date']) || !empty($validated['geboortedatum'])) {
                $updateData['geboortedatum'] = $validated['birth_date'] ?? $validated['geboortedatum'];
            }
            if (!empty($validated['address']) || !empty($validated['adres'])) {
                $updateData['adres'] = $validated['address'] ?? $validated['adres'];
            }
            if (!empty($validated['city']) || !empty($validated['stad'])) {
                $updateData['stad'] = $validated['city'] ?? $validated['stad'];
            }
            if (!empty($validated['postal_code']) || !empty($validated['postcode'])) {
                $updateData['postcode'] = $validated['postal_code'] ?? $validated['postcode'];
            }
            if (!empty($validated['phone']) || !empty($validated['telefoonnummer'])) {
                $updateData['telefoonnummer'] = $validated['phone'] ?? $validated['telefoonnummer'];
            }
            
            $user->update($updateData);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Profiel succesvol bijgewerkt!'
                ]);
            }

            return redirect()->route('profile.settings')->with('success', 'Profiel succesvol bijgewerkt!');
            
        } catch (\Exception $e) {
            \Log::error('Profile update failed: ' . $e->getMessage());
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Er is een fout opgetreden: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('profile.settings')->with('error', 'Er is een fout opgetreden: ' . $e->getMessage());
        }
    }
    
    /**
     * Update avatar - EXACT ZOALS MEDEWERKERCONTROLLER
     */
    public function updateAvatar(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);
        
        \Log::info('🖼️ Avatar upload gedetecteerd via ProfileSettingsController', [
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
                    }
                }
                
                $fileName = $request->file('avatar')->hashName();
                $request->file('avatar')->move($uploadsPath, $fileName);
                $avatarPath = 'avatars/klanten/' . $fileName;
            } else {
                // LOKAAL: Upload naar storage/app/public
                if ($klant->avatar_path && \Storage::disk('public')->exists($klant->avatar_path)) {
                    \Storage::disk('public')->delete($klant->avatar_path);
                }
                
                $avatarPath = $request->file('avatar')->store('avatars/klanten', 'public');
            }
            
            // Update klant record
            \DB::table('klanten')
                ->where('id', $klant->id)
                ->update(['avatar_path' => $avatarPath, 'updated_at' => now()]);
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
                    }
                }
                
                $fileName = $request->file('avatar')->hashName();
                $request->file('avatar')->move($uploadsPath, $fileName);
                $avatarPath = 'avatars/medewerkers/' . $fileName;
            } else {
                // LOKAAL: Upload naar storage/app/public
                if ($user->avatar_path && \Storage::disk('public')->exists($user->avatar_path)) {
                    \Storage::disk('public')->delete($user->avatar_path);
                }
                
                $avatarPath = $request->file('avatar')->store('avatars/medewerkers', 'public');
            }
            
            // Update user record
            $user->update(['avatar_path' => $avatarPath]);
        }
        
        // Return JSON response (voor AJAX)
        return response()->json([
            'success' => true,
            'message' => 'Profielfoto succesvol bijgewerkt'
        ]);
    }
    
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required',
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);
        
        $user = Auth::user();
        
        if (!Hash::check($validated['current_password'], $user->password)) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false, 
                    'errors' => ['current_password' => ['Het huidige wachtwoord is incorrect']]
                ], 422);
            }
            return back()->withErrors(['current_password' => 'Het huidige wachtwoord is incorrect']);
        }
        
        $user->update(['password' => Hash::make($validated['password'])]);
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Wachtwoord succesvol gewijzigd'
            ]);
        }
        
        return back()->with('success', 'Wachtwoord succesvol gewijzigd');
    }
    
    public function updatePreferences(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'language' => 'required|in:nl,fr,en',
            'email_notifications' => 'boolean',
            'profile_visibility' => 'required|in:public,private,staff_only',
            'dark_mode' => 'boolean',
        ]);
        
        // Update user preferences (you might need to add these columns)
        $user->update([
            'language' => $validated['language'],
            'email_notifications' => $validated['email_notifications'] ?? false,
            'profile_visibility' => $validated['profile_visibility'],
            'dark_mode' => $validated['dark_mode'] ?? false,
        ]);
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Voorkeuren succesvol bijgewerkt'
            ]);
        }
        
        return back()->with('success', 'Voorkeuren succesvol bijgewerkt');
    }
    
    public function toggle2FA(Request $request)
    {
        $user = Auth::user();
        
        // Toggle 2FA (basic implementation - you can expand this)
        $user->update([
            'two_factor_enabled' => !($user->two_factor_enabled ?? false)
        ]);
        
        $message = ($user->two_factor_enabled ?? false) ? 
            'Two-factor authenticatie ingeschakeld' : 
            'Two-factor authenticatie uitgeschakeld';
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'enabled' => $user->two_factor_enabled ?? false
            ]);
        }
        
        return back()->with('success', $message);
    }
    
    public function deactivateAccount(Request $request)
    {
        $request->validate([
            'password' => 'required'
        ]);
        
        $user = Auth::user();
        
        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Wachtwoord is incorrect']);
        }
        
        // Soft delete or deactivate account
        $user->update([
            'active' => false,
            'deactivated_at' => now()
        ]);
        
        Auth::logout();
        
        return redirect('/')->with('success', 'Account succesvol gedeactiveerd');
    }
    
    private function calculateProfileCompletion($user)
    {
        $fields = [
            'first_name', 'last_name', 'email', 'phone', 
            'address', 'birth_date', 'avatar_path'
        ];
        
        $completed = 0;
        foreach ($fields as $field) {
            if (!empty($user->$field)) {
                $completed++;
            }
        }
        
        return round(($completed / count($fields)) * 100);
    }
}