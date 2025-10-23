public function index(Request $request)
{
    \Log::info('🔍 Users Index START - Auth check', [
        'auth_user_id' => auth()->id(),
        'auth_user_email' => auth()->user()->email,
        'auth_user_org_id' => auth()->user()->organisatie_id ?? 'NULL',
        'auth_user_role' => auth()->user()->role
    ]);
    
    // Check of organisatie_id bestaat
    if (!auth()->user()->organisatie_id) {
        \Log::error('❌ PROBLEEM: Auth user heeft GEEN organisatie_id!');
    }
    
    // Start query met EXPLICIETE organisatie filtering
    $orgId = auth()->user()->organisatie_id;
    $query = User::where('organisatie_id', '=', $orgId);
    
    \Log::info('📊 Query building', [
        'filtering_on_org_id' => $orgId,
        'has_search' => $request->filled('search'),
        'has_role_filter' => $request->filled('role'),
        'has_status_filter' => $request->filled('status')
    ]);
    
    // Filters
    if ($request->filled('search')) {
        $query->where(function($q) use ($request) {
            $q->where('name', 'like', '%' . $request->search . '%')
              ->orWhere('email', 'like', '%' . $request->search . '%');
        });
    }
    
    if ($request->filled('role')) {
        $query->where('role', $request->role);
    }
    
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }
    
    // Debug: Check de raw SQL query
    $sql = $query->toSql();
    $bindings = $query->getBindings();
    \Log::info('🔧 SQL Query Debug', [
        'sql' => $sql,
        'bindings' => $bindings
    ]);
    
    $users = $query->orderBy('created_at', 'desc')->paginate(20);
    
    // Log eerste paar users om te checken
    \Log::info('👥 Eerste 3 users in resultaat', [
        'users' => $users->take(3)->map(fn($u) => [
            'id' => $u->id,
            'name' => $u->name,
            'email' => $u->email,
            'organisatie_id' => $u->organisatie_id,
            'role' => $u->role
        ])
    ]);
    
        // Statistics - alleen voor huidige organisatie
        $organisatieId = auth()->user()->organisatie_id;
        $stats = [
            'total_users' => User::where('organisatie_id', $organisatieId)->count(),
            'active_users' => User::where('organisatie_id', $organisatieId)->where('status', 'active')->count(),
            'admin_count' => User::where('organisatie_id', $organisatieId)->where('role', 'admin')->count(),
            'medewerker_count' => User::where('organisatie_id', $organisatieId)->where('role', 'medewerker')->count(),
            'klant_count' => User::where('organisatie_id', $organisatieId)->where('role', 'klant')->count(),
        ];    \Log::info('✅ Users Index COMPLETE', [
        'total_found' => $users->total(),
        'stats' => $stats,
        'org_id_filter' => $orgId
    ]);
    
    return view('admin.users.index', compact('users', 'stats'));
}