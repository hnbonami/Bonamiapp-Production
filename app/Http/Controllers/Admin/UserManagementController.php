<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\RolePermission;
use App\Models\RoleTestPermission;
use App\Models\UserLoginLog;
use Illuminate\Http\Request;

class UserManagementController extends Controller
{
    /**
     * Display users overview
     */
    public function index(Request $request)
    {
        $query = User::query()->with(['loginLogs' => function($q) {
            $q->latest('login_at')->limit(1);
        }]);

        // Filter by role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by name or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(20);

        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('status', 'active')->count(),
            'admin_count' => User::where('role', 'admin')->count(),
            'medewerker_count' => User::where('role', 'medewerker')->count(),
            'klant_count' => User::where('role', 'klant')->count(),
        ];

        // Debug info
        \Log::info('User Management Stats', [
            'total_users_in_db' => User::count(),
            'klanten_table_count' => \App\Models\Klant::count(),
            'medewerkers_table_count' => \App\Models\Medewerker::count(),
            'users_with_klant_role' => User::where('role', 'klant')->count(),
            'users_with_medewerker_role' => User::where('role', 'medewerker')->count(),
        ]);

        return view('admin.users.index', compact('users', 'stats'));
    }

    /**
     * Show user edit form
     */
    public function edit(User $user)
    {
        $availableTabs = [
            'dashboard' => 'Dashboard',
            'klanten' => 'Klanten',
            'medewerkers' => 'Medewerkers', 
            'instagram' => 'Instagram',
            'nieuwsbrief' => 'Nieuwsbrief',
            'sjablonen' => 'Sjablonen',
            'testzadels' => 'Testzadels',
            'admin' => 'Admin'
        ];

        $availableTests = [
            'bikefit' => 'Bikefit',
            'inspanningstest_fietsen' => 'Inspanningstest Fietsen',
            'inspanningstest_lopen' => 'Inspanningstest Lopen',
            'voedingsadvies' => 'Voedingsadvies',
            'zadeldrukmeting' => 'Zadeldrukmeting',
            'maatbepaling' => 'Maatbepaling'
        ];

        $recentLogins = $user->loginLogs()
                            ->latest('login_at')
                            ->limit(10)
                            ->get();

        return view('admin.users.edit', compact('user', 'availableTabs', 'availableTests', 'recentLogins'));
    }

    /**
     * Update user
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,medewerker,klant',
            'status' => 'required|in:active,inactive,suspended',
            'admin_notes' => 'nullable|string'
        ]);

        $user->update($request->only(['name', 'email', 'role', 'status', 'admin_notes']));

        return redirect()->route('admin.users.index')
                        ->with('success', '✅ Gebruiker "' . $user->name . '" succesvol bijgewerkt!');
    }

    /**
     * Show role permissions management
     */
    public function roles()
    {
        $roles = ['admin', 'medewerker', 'klant'];
        
        $availableTabs = [
            'dashboard' => 'Dashboard',
            'klanten' => 'Klanten',
            'medewerkers' => 'Medewerkers',
            'instagram' => 'Instagram', 
            'nieuwsbrief' => 'Nieuwsbrief',
            'sjablonen' => 'Sjablonen',
            'testzadels' => 'Testzadels',
            'admin' => 'Admin'
        ];

        $availableTests = [
            'bikefit' => 'Bikefit',
            'inspanningstest_fietsen' => 'Inspanningstest Fietsen',
            'inspanningstest_lopen' => 'Inspanningstest Lopen', 
            'voedingsadvies' => 'Voedingsadvies',
            'zadeldrukmeting' => 'Zadeldrukmeting',
            'maatbepaling' => 'Maatbepaling'
        ];

        // Get current permissions
        $rolePermissions = [];
        $roleTestPermissions = [];

        foreach ($roles as $role) {
            $rolePermissions[$role] = RolePermission::where('role_name', $role)
                                                  ->where('can_access', true)
                                                  ->pluck('tab_name')
                                                  ->toArray();

            $roleTestPermissions[$role] = RoleTestPermission::where('role_name', $role)
                                                           ->get()
                                                           ->keyBy('test_type');
        }

        return view('admin.users.roles', compact(
            'roles', 'availableTabs', 'availableTests', 
            'rolePermissions', 'roleTestPermissions'
        ));
    }

    /**
     * Update role permissions
     */
    public function updateRolePermissions(Request $request)
    {
        $request->validate([
            'role' => 'required|in:admin,medewerker,klant',
            'tab_permissions' => 'array',
            'test_permissions' => 'array'
        ]);

        $role = $request->role;

        // Update tab permissions
        $availableTabs = ['dashboard', 'klanten', 'medewerkers', 'instagram', 'nieuwsbrief', 'sjablonen', 'testzadels', 'admin'];
        
        foreach ($availableTabs as $tab) {
            $canAccess = in_array($tab, $request->tab_permissions ?? []);
            RolePermission::setTabAccess($role, $tab, $canAccess);
        }

        // Update test permissions
        $availableTests = ['bikefit', 'inspanningstest_fietsen', 'inspanningstest_lopen', 'voedingsadvies', 'zadeldrukmeting', 'maatbepaling'];
        
        foreach ($availableTests as $test) {
            $testPerms = $request->test_permissions[$test] ?? [];
            
            RoleTestPermission::setTestPermission($role, $test, [
                'can_access' => in_array('access', $testPerms),
                'can_create' => in_array('create', $testPerms), 
                'can_edit' => in_array('edit', $testPerms)
            ]);
        }

        return redirect()->route('admin.users.roles')
                        ->with('success', "🔐 Rechten voor {$role} succesvol bijgewerkt! Wijzigingen zijn direct actief.");
    }

    /**
     * Show login activity
     */
    public function activity()
    {
        $loginActivities = \App\Models\LoginActivity::with('user')
            ->orderBy('logged_in_at', 'desc')
            ->paginate(50);
        
        // Get all users for the view
        $users = \App\Models\User::orderBy('name')->get();
        
        // Get recent logs (if you have a logs system, otherwise empty paginated collection)
        $logs = new \Illuminate\Pagination\LengthAwarePaginator(
            collect(), // Empty collection
            0, // Total items
            10, // Items per page
            1, // Current page
            ['path' => request()->url()]
        );
        
        // Complete statistics - add ALL possible variables the view might expect
        $stats = [
            // Today stats
            'total_logins_today' => \App\Models\LoginActivity::whereDate('logged_in_at', today())->count(),
            'unique_users_today' => \App\Models\LoginActivity::whereDate('logged_in_at', today())
                ->distinct('user_id')
                ->count(),
            'unique_logins_today' => \App\Models\LoginActivity::whereDate('logged_in_at', today())
                ->distinct('user_id')
                ->count(),
                
            // Week stats  
            'total_logins_week' => \App\Models\LoginActivity::whereBetween('logged_in_at', [
                now()->startOfWeek(), 
                now()->endOfWeek()
            ])->count(),
            'total_logins_this_week' => \App\Models\LoginActivity::whereBetween('logged_in_at', [
                now()->startOfWeek(), 
                now()->endOfWeek()
            ])->count(),
            
            // General stats
            'total_login_activities' => \App\Models\LoginActivity::count(),
            'most_recent_login' => \App\Models\LoginActivity::latest('logged_in_at')->first(),
            
            // Additional stats that might be needed
            'average_session_time' => 1800, // 30 minutes in seconds (placeholder since we don't track session end)
            'total_users' => \App\Models\User::count(),
            'active_users_today' => \App\Models\LoginActivity::whereDate('logged_in_at', today())
                ->distinct('user_id')
                ->count(),
        ];
        
        return view('admin.users.activity', compact('loginActivities', 'users', 'logs', 'stats'));
    }
}