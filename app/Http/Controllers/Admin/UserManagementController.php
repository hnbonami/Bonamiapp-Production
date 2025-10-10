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

        // Include login activity data for debugging
        $users->load(['loginActivities' => function($query) {
            $query->latest('logged_in_at')->limit(1);
        }]);

        // Debug: Log user info
        \Log::info('Users with login activities', [
            'users_count' => $users->count(),
            'users_with_activities' => $users->map(function($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'last_login' => $user->loginActivities->first() ? $user->loginActivities->first()->logged_in_at : null,
                    'login_count' => \App\Models\LoginActivity::where('user_id', $user->id)->count()
                ];
            })
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
        // Start with base query
        $query = \App\Models\LoginActivity::with('user');
        
        // Apply filters
        if (request('user_id')) {
            $query->where('user_id', request('user_id'));
        }
        
        if (request('search')) {
            $search = request('search');
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        if (request('date_from')) {
            $query->whereDate('logged_in_at', '>=', request('date_from'));
        }
        
        if (request('date_to')) {
            $query->whereDate('logged_in_at', '<=', request('date_to'));
        }
        
        // Get filtered login activities
        $loginActivities = $query->orderBy('logged_in_at', 'desc')->paginate(50);
        
        // Get all users for the filter dropdown
        $users = \App\Models\User::orderBy('name')->get();
        
        // Create empty logs collection for the view
        $logs = new \Illuminate\Pagination\LengthAwarePaginator(
            collect(), // Empty collection
            0, // Total items
            10, // Items per page
            1, // Current page
            ['path' => request()->url()]
        );
        
        // Statistics for the cards (use unfiltered data for stats)
        $stats = [
            'total_logins_today' => \App\Models\LoginActivity::whereDate('logged_in_at', today())->count(),
            'unique_users_today' => \App\Models\LoginActivity::whereDate('logged_in_at', today())->distinct('user_id')->count(),
            'unique_logins_today' => \App\Models\LoginActivity::whereDate('logged_in_at', today())->distinct('user_id')->count(),
            'total_logins_week' => \App\Models\LoginActivity::whereBetween('logged_in_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'total_logins_this_week' => \App\Models\LoginActivity::whereBetween('logged_in_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'total_login_activities' => \App\Models\LoginActivity::count(),
            'most_recent_login' => \App\Models\LoginActivity::latest('logged_in_at')->first(),
            'average_session_time' => $this->calculateAverageSessionTime(),
            'total_users' => \App\Models\User::count(),
            'active_users_today' => \App\Models\LoginActivity::whereDate('logged_in_at', today())->distinct('user_id')->count(),
        ];
        
        // Debug log for activity page
        \Log::info('🔍 Activity Page Data', [
            'login_activities_count' => $loginActivities->total(),
            'login_activities_items' => $loginActivities->count(),
            'users_count' => $users->count(),
            'filters' => [
                'user_id' => request('user_id'),
                'search' => request('search'),
                'date_from' => request('date_from'),
                'date_to' => request('date_to')
            ],
            'stats' => $stats,
            'first_activity' => $loginActivities->first() ? [
                'id' => $loginActivities->first()->id,
                'user_id' => $loginActivities->first()->user_id,
                'user_name' => $loginActivities->first()->user ? $loginActivities->first()->user->name : 'No user',
                'logged_in_at' => $loginActivities->first()->logged_in_at,
                'logged_out_at' => $loginActivities->first()->logged_out_at,
                'ip_address' => $loginActivities->first()->ip_address
            ] : 'NO ACTIVITIES FOUND'
        ]);
        
        return view('admin.users.activity-clean', compact('loginActivities', 'users', 'logs', 'stats'));
    }

    /**
     * Calculate average session time in seconds
     */
    private function calculateAverageSessionTime()
    {
        $sessions = \App\Models\LoginActivity::whereNotNull('logged_out_at')
            ->get()
            ->map(function($activity) {
                return $activity->logged_out_at->diffInSeconds($activity->logged_in_at);
            });

        return $sessions->count() > 0 ? $sessions->average() : 1800; // Default 30 minutes
    }
}