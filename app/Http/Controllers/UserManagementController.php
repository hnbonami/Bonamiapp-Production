public function index(Request $request)
{
    \Log::info('🔍 UserManagementController index called', [
        'request_params' => $request->all(),
        'user_role' => auth()->user()->role,
        'user_org_id' => auth()->user()->organisatie_id ?? 'NULL'
    ]);
    
    // BELANGRIJK: Filter ALLE queries op organisatie_id
    $orgId = auth()->user()->organisatie_id;
    
    // Start query met organisatie filtering
    $usersQuery = User::where('organisatie_id', $orgId);
    
    // ...existing filter logic (search, role, status)...
    
    if ($request->filled('search')) {
        $usersQuery->where(function($q) use ($request) {
            $q->where('name', 'like', '%' . $request->search . '%')
              ->orWhere('email', 'like', '%' . $request->search . '%');
        });
    }
    
    if ($request->filled('role')) {
        $usersQuery->where('role', $request->role);
    }
    
    if ($request->filled('status')) {
        $usersQuery->where('status', $request->status);
    }
    
    $users = $usersQuery->orderBy('created_at', 'desc')->paginate(15);
    
    // Statistics - ALLEMAAL filteren op organisatie_id!
    $stats = [
        'total_users' => User::where('organisatie_id', $orgId)->count(),
        'medewerkers' => User::where('organisatie_id', $orgId)->where('role', 'medewerker')->count(),
        'klanten' => User::where('organisatie_id', $orgId)->where('role', 'klant')->count(),
        'admin' => User::where('organisatie_id', $orgId)->where('role', 'admin')->count(),
        'admin_count' => User::where('organisatie_id', $orgId)->where('role', 'admin')->count(),
        'admins' => User::where('organisatie_id', $orgId)->where('role', 'admin')->count(),
        'medewerker' => User::where('organisatie_id', $orgId)->where('role', 'medewerker')->count(),
        'medewerker_count' => User::where('organisatie_id', $orgId)->where('role', 'medewerker')->count(),
        'medewerkers_count' => User::where('organisatie_id', $orgId)->where('role', 'medewerker')->count(),
        'klant' => User::where('organisatie_id', $orgId)->where('role', 'klant')->count(),
        'klant_count' => User::where('organisatie_id', $orgId)->where('role', 'klant')->count(),
        'klanten_count' => User::where('organisatie_id', $orgId)->where('role', 'klant')->count(),
        'active' => User::where('organisatie_id', $orgId)->where('status', 'active')->count(),
        'active_users' => User::where('organisatie_id', $orgId)->where('status', 'active')->count(),
        'active_count' => User::where('organisatie_id', $orgId)->where('status', 'active')->count(),
        // ...rest van stats ook met orgId filter...
    ];
    
    \Log::info('✅ UserManagementController final results - GEFILTERD OP ORG_ID', [
        'org_id' => $orgId,
        'total_users' => $totalUsers,
        'admins' => $admins,
        'medewerkers' => $medewerkers,
        'klanten' => $klanten,
        'medewerkersCount_for_UI' => $medewerkersCount,
        'paginated_users_count' => $users->count(),
        'stats_keys' => array_keys($stats)
    ]);
    
    // ...existing code to return view...
}