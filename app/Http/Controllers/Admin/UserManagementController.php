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
        // Filter op huidige organisatie
        $organisatieId = auth()->user()->organisatie_id;

                // Calculate statistics - FILTER OP ORGANISATIE
        $organisatieId = auth()->user()->organisatie_id;
        $allUsers = User::where('organisatie_id', $organisatieId)->get();
        
        $totalUsers = $allUsers->count();
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

        // DAARNA: Query voor gepagineerde resultaten - FILTER OP ORGANISATIE
        $paginatedQuery = User::where('organisatie_id', $organisatieId);

        // Filter op rol als opgegeven
        if ($request->filled('role') && $request->role !== 'all') {
            $paginatedQuery->where('role', $request->role);
        }

        // Filter op status als opgegeven
        if ($request->filled('status') && $request->status !== 'all') {
            $paginatedQuery->where('status', $request->status);
        }

        // Search functionaliteit
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $paginatedQuery->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('email', 'like', "%{$searchTerm}%")
                  ->orWhere('voornaam', 'like', "%{$searchTerm}%")  
                  ->orWhere('achternaam', 'like', "%{$searchTerm}%");
            });
        }

        // Haal users op met paginatie
        $users = $paginatedQuery->orderBy('created_at', 'desc')->paginate(15);

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
 * Toon de rollen beheer pagina
 */
public function roles()
{
    // Haal alle beschikbare rollen op in een format dat de view verwacht
    $roles = [
        [
            'key' => 'admin',
            'name' => 'Administrator',
            'description' => 'Volledige toegang tot alle functionaliteiten',
            'permissions' => 'Alle rechten',
            'color' => 'purple'
        ],
        [
            'key' => 'medewerker',
            'name' => 'Medewerker', 
            'description' => 'Toegang tot klantenbeheer en testen',
            'permissions' => 'Bikefit, Inspanningstests, Klanten',
            'color' => 'orange'
        ],
        [
            'key' => 'klant',
            'name' => 'Klant',
            'description' => 'Beperkte toegang tot eigen gegevens',
            'permissions' => 'Alleen eigen profiel',
            'color' => 'cyan'
        ]
    ];

    // Haal gebruikers op per rol voor statistieken - alleen voor huidige organisatie
    $organisatieId = auth()->user()->organisatie_id;
    $users = \App\Models\User::where('organisatie_id', $organisatieId)->get();
    $roleStats = [
        'total' => $users->count(),
        'superadmin' => $users->where('role', 'superadmin')->count(),
        'admin' => $users->where('role', 'admin')->count(),
        'medewerker' => $users->where('role', 'medewerker')->count(),
        'klant' => $users->where('role', 'klant')->count(),
    ];

    return view('admin.users.roles', compact('roles', 'roleStats'));
}    /**
     * Show user activity overview
     */
    public function activity()
    {
        // Filter op huidige organisatie
        $organisatieId = auth()->user()->organisatie_id;
        
        // Haal alleen gebruikers van deze organisatie op voor de filter dropdown
        $users = \App\Models\User::where('organisatie_id', $organisatieId)
            ->orderBy('name')
            ->get();
        
        // Haal login activiteiten op - alleen voor users van deze organisatie
        try {
            $loginActivities = \App\Models\LoginActivity::with('user')
                ->whereHas('user', function($q) use ($organisatieId) {
                    $q->where('organisatie_id', $organisatieId);
                })
                ->orderBy('logged_in_at', 'desc')
                ->paginate(50);
                
            // Bereken statistieken - alleen voor deze organisatie
            $todayStart = now()->startOfDay();
            $weekStart = now()->subDays(7);
            
            $allOrgActivities = \App\Models\LoginActivity::whereHas('user', function($q) use ($organisatieId) {
                $q->where('organisatie_id', $organisatieId);
            })->get();
            
            $stats = [
                'total_logins_today' => $allOrgActivities->where('logged_in_at', '>=', $todayStart)->count(),
                'total_logins_week' => $allOrgActivities->where('logged_in_at', '>=', $weekStart)->count(),
                'unique_users_today' => $allOrgActivities->where('logged_in_at', '>=', $todayStart)->pluck('user_id')->unique()->count(),
                'average_session_time' => $allOrgActivities->avg('session_duration') ?? 0
            ];
        } catch (\Exception $e) {
            // Als LoginActivity model niet bestaat, maak lege collectie
            $loginActivities = new \Illuminate\Pagination\LengthAwarePaginator(
                collect(), // items
                0, // total
                50, // perPage
                1, // currentPage
                ['path' => request()->url()]
            );
            
            $stats = [
                'total_logins_today' => 0,
                'total_logins_week' => 0,
                'unique_users_today' => 0,
                'average_session_time' => 0
            ];
        }

        return view('admin.users.activity-clean', compact('users', 'loginActivities', 'stats'));
    }

    /**
     * Toon login activiteit - aangepaste versie voor activity-clean
     */
    public function activityClean()
    {
        // Filter op huidige organisatie
        $organisatieId = auth()->user()->organisatie_id;
        
        // Haal alleen gebruikers van deze organisatie op voor de filter dropdown
        $users = \App\Models\User::where('organisatie_id', $organisatieId)
            ->orderBy('name')
            ->get();
        
        // Haal login activiteiten op - alleen voor deze organisatie
        $loginActivities = collect(); // Placeholder voor nu
        
        // Demo statistieken
        $stats = [
            'total_logins_today' => 0,
            'total_logins_week' => 0, 
            'unique_users_today' => 0,
            'average_session_time' => 0
        ];

        return view('admin.users.activity-clean', compact('users', 'loginActivities', 'stats'));
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

    /**
     * Update rol rechten (placeholder voor toekomstige functionaliteit)
     */
    public function updateRolePermissions(Request $request)
    {
        // Valideer de input
        $validated = $request->validate([
            'role' => 'required|string|in:admin,medewerker,klant',
            'permissions' => 'array'
        ]);

        // Log de actie voor debugging
        \Log::info('Rol rechten update poging', [
            'user_id' => auth()->id(), 
            'role' => $validated['role'],
            'permissions' => $validated['permissions'] ?? []
        ]);

        // Voor nu tonen we een melding dat deze functie nog niet geïmplementeerd is
        return redirect()->route('admin.users.roles')
            ->with('info', 'Rol rechten wijzigen is nog niet geïmplementeerd. Deze functie komt in een toekomstige versie beschikbaar.');
    }
}