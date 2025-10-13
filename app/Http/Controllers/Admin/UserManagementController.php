<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserManagementController extends Controller
{
    /**
     * Display a listing of all users (both medewerkers and klanten)
     */
    public function index(Request $request)
    {
        // Log debug info om te zien wat er gebeurt
        \Log::info('🔍 UserManagementController index called', [
            'request_params' => $request->all(),
            'user_role' => auth()->user()->role ?? 'not_authenticated'
        ]);

        // EERST: Bereken alle statistieken met alle users - FRESH query zonder cache
        $allUsers = User::withoutGlobalScopes()->get();
        $adminCount = $allUsers->where('role', 'admin')->count();
        $medewerkerCount = $allUsers->where('role', 'medewerker')->count();
        $klantCount = $allUsers->where('role', 'klant')->count();
        $activeCount = $allUsers->where('status', 'active')->count();
        $inactiveCount = $allUsers->where('status', 'inactive')->count();
        $suspendedCount = $allUsers->where('status', 'suspended')->count();
        $verifiedCount = $allUsers->whereNotNull('email_verified_at')->count();
        $unverifiedCount = $allUsers->whereNull('email_verified_at')->count();
        $recentLoginsCount = $allUsers->where('last_login_at', '>=', now()->subDays(7))->count();

        // UI counts - admin EN medewerker rollen tellen als medewerkers  
        $medewerkersCount = $adminCount + $medewerkerCount;
        $klantenCount = $klantCount;

        // DAARNA: Query voor gepagineerde resultaten - FRESH query
        $query = User::withoutGlobalScopes();

        // Filter op rol als opgegeven
        if ($request->filled('role') && $request->role !== 'all') {
            $query->where('role', $request->role);
        }

        // Filter op status als opgegeven
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Search functionaliteit
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('email', 'like', "%{$searchTerm}%")
                  ->orWhere('voornaam', 'like', "%{$searchTerm}%")  
                  ->orWhere('achternaam', 'like', "%{$searchTerm}%");
            });
        }

        // Haal users op met paginatie
        $users = $query->orderBy('created_at', 'desc')->paginate(15);

        // Maak uitgebreide stats array met alle mogelijke keys
        $stats = [
            // Basis counts
            'total_users' => $adminCount + $medewerkerCount + $klantCount,
            'medewerkers' => $medewerkersCount,
            'klanten' => $klantenCount,
            
            // Role counts - verschillende varianten
            'admin' => $adminCount,
            'admin_count' => $adminCount,
            'admins' => $adminCount,
            'medewerker' => $medewerkerCount,
            'medewerker_count' => $medewerkerCount,
            'medewerkers_count' => $medewerkersCount,
            'klant' => $klantCount,
            'klant_count' => $klantCount,
            'klanten_count' => $klantenCount,
            
            // Status counts - verschillende varianten
            'active' => $activeCount,
            'active_users' => $activeCount,
            'active_count' => $activeCount,
            'inactive' => $inactiveCount,
            'inactive_users' => $inactiveCount,
            'inactive_count' => $inactiveCount,
            'suspended' => $suspendedCount,
            'suspended_users' => $suspendedCount,
            'suspended_count' => $suspendedCount,
            
            // Verification status
            'verified' => $verifiedCount,
            'verified_users' => $verifiedCount,
            'verified_count' => $verifiedCount,
            'unverified' => $unverifiedCount,
            'unverified_users' => $unverifiedCount,
            'unverified_count' => $unverifiedCount,
            
            // Activity
            'recent_logins' => $recentLoginsCount,
            'recent_login_count' => $recentLoginsCount,
        ];

        // Log de resultaten voor debugging
        \Log::info('✅ UserManagementController final results', [
            'total_users' => $allUsers->count(),
            'admins' => $adminCount,
            'medewerkers' => $medewerkerCount,
            'klanten' => $klantCount,
            'medewerkersCount_for_UI' => $medewerkersCount,
            'paginated_users_count' => $users->count(),
            'stats_keys' => array_keys($stats)
        ]);

        // Log EXACT wat er naar de view wordt gestuurd - met timestamps
        \Log::info('🎯 EXACT DATA SENT TO VIEW - ' . now()->format('Y-m-d H:i:s'), [
            'users_collection_count' => $users->count(),
            'users_total' => $users->total(),
            'medewerkersCount' => $medewerkersCount,
            'klantenCount' => $klantenCount,
            'users_in_current_page' => $users->map(function($user) {
                return [
                    'id' => $user->id,
                    'email' => $user->email,
                    'role' => $user->role,
                    'name' => $user->name,
                    'voornaam' => $user->voornaam,
                    'achternaam' => $user->achternaam,
                    'status' => $user->status ?? 'NULL'
                ];
            })->toArray(),
            'stats_admin_count' => $stats['admin_count'] ?? 'MISSING',
            'stats_medewerker_count' => $stats['medewerker_count'] ?? 'MISSING',
            'stats_klant_count' => $stats['klant_count'] ?? 'MISSING'
        ]);

        return view('admin.users.index', compact('users', 'medewerkersCount', 'klantenCount', 'stats'));
    }

    /**
     * Show user roles overview
     */
    public function roles()
    {
        $users = User::all();
        
        $roleStats = [
            'admin' => $users->where('role', 'admin')->count(),
            'medewerker' => $users->where('role', 'medewerker')->count(), 
            'klant' => $users->where('role', 'klant')->count(),
        ];

        return view('admin.users.roles', compact('users', 'roleStats'));
    }

    /**
     * Show user activity overview
     */
    public function activity()
    {
        $users = User::whereNotNull('last_login_at')
                    ->orderBy('last_login_at', 'desc')
                    ->limit(50)
                    ->get();

        return view('admin.users.activity', compact('users'));
    }

    /**
     * Show specific user details
     */
    public function show(User $user)
    {
        return view('admin.users.show', compact('user'));
    }

    /**
     * Update user status or basic info
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'status' => 'sometimes|in:active,inactive,suspended',
            'admin_notes' => 'sometimes|string|nullable'
        ]);

        $user->update($validated);

        \Log::info('👤 User updated via UserManagementController', [
            'user_id' => $user->id,
            'updated_by' => auth()->id(),
            'changes' => $validated
        ]);

        return redirect()->back()->with('success', 'Gebruiker succesvol bijgewerkt.');
    }

    /**
     * Delete a user
     */
    public function destroy(User $user)
    {
        // Prevent deletion of current user
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'Je kunt jezelf niet verwijderen.');
        }

        $email = $user->email;
        $role = $user->role;
        $user->delete();

        \Log::info('🗑️ User deleted via UserManagementController', [
            'deleted_user_email' => $email,
            'deleted_user_role' => $role,
            'deleted_by' => auth()->user()->email
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Gebruiker succesvol verwijderd.');
    }
}