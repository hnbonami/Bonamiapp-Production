<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Klant;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    /**
     * Display a listing of all users (medewerkers en klanten)
     */
    public function index(Request $request)
    {
        // Log voor debugging
        \Log::info('🔍 UsersController@index called', [
            'request_params' => $request->all(),
            'user' => auth()->user()->email
        ]);

        // Haal alle users op en tel ze
        $query = User::query();
        
        // Filter op rol als opgegeven
        if ($request->filled('rol') && $request->rol !== 'alle_rollen') {
            $query->where('role', $request->rol);
        }
        
        // Filter op status als opgegeven
        if ($request->filled('status') && $request->status !== 'alle_statussen') {
            $query->where('status', $request->status);
        }
        
        // Zoekfunctionaliteit
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('voornaam', 'like', "%{$search}%")
                  ->orWhere('achternaam', 'like', "%{$search}%");
            });
        }
        
        $users = $query->orderBy('created_at', 'desc')->get();
        
        // Tel verschillende categorieën
        $totalUsers = User::count();
        $medewerkersCount = User::where('role', '!=', 'klant')->count();
        $klantenCount = User::where('role', 'klant')->count();
        
        // Log statistieken voor debugging
        \Log::info('📊 Users statistics', [
            'total_users' => $totalUsers,
            'medewerkers_count' => $medewerkersCount,
            'klanten_count' => $klantenCount,
            'filtered_results' => $users->count(),
            'request_filters' => [
                'rol' => $request->rol,
                'status' => $request->status,
                'search' => $request->search
            ]
        ]);
        
        // Log alle users voor debugging
        \Log::info('👥 All users being sent to view', [
            'users' => $users->map(function($user) {
                return [
                    'id' => $user->id,
                    'email' => $user->email,
                    'role' => $user->role,
                    'name' => $user->name,
                    'voornaam' => $user->voornaam,
                    'achternaam' => $user->achternaam
                ];
            })->toArray()
        ]);

        return view('admin.users.index', compact('users', 'medewerkersCount', 'klantenCount'));
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created user in storage
     */
    public function store(Request $request)
    {
        // Deze functie zou gebruikt kunnen worden voor algemene user creation
        // maar waarschijnlijk wordt MedewerkersController en KlantenController gebruikt
        
        return redirect()->route('admin.users.index')
                        ->with('info', 'Gebruik de specifieke medewerker of klant aanmaak functionaliteit.');
    }

    /**
     * Display the specified user
     */
    public function show(User $user)
    {
        \Log::info('🔍 Showing user details', [
            'user_id' => $user->id,
            'email' => $user->email,
            'role' => $user->role
        ]);

        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified user in storage
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,medewerker,klant',
            'status' => 'nullable|string',
        ]);

        try {
            $user->update($validated);

            \Log::info('✅ User updated successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role
            ]);

            return redirect()->route('admin.users.index')
                           ->with('success', 'Gebruiker succesvol bijgewerkt.');

        } catch (\Exception $e) {
            \Log::error('❌ Failed to update user: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Er is een fout opgetreden bij het bijwerken van de gebruiker.');
        }
    }

    /**
     * Remove the specified user from storage
     */
    public function destroy(User $user)
    {
        try {
            $email = $user->email;
            $role = $user->role;
            
            $user->delete();

            \Log::info('✅ User deleted successfully', [
                'email' => $email,
                'role' => $role
            ]);

            return redirect()->route('admin.users.index')
                           ->with('success', 'Gebruiker succesvol verwijderd.');

        } catch (\Exception $e) {
            \Log::error('❌ Failed to delete user: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                           ->with('error', 'Er is een fout opgetreden bij het verwijderen van de gebruiker.');
        }
    }
}