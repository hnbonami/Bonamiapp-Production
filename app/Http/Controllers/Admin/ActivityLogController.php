public function index(Request $request)
    {
        $user = auth()->user();
        $isSuperAdmin = $user->role === 'superadmin';
        
        // Debug: Log wat we ophalen
        \Log::info('ActivityLog Index - User Role: ' . $user->role . ', Organisatie: ' . $user->organisatie_id);
        
        $query = \App\Models\LoginActivity::with('user')->orderBy('logged_in_at', 'desc');

        // Debug: Hoeveel records zijn er totaal?
        $totalRecords = \App\Models\LoginActivity::count();
        \Log::info('ActivityLog - Totaal aantal LoginActivity records: ' . $totalRecords);

        // Superadmin ziet alles, gewone admin alleen eigen organisatie
        if (!$isSuperAdmin && $user->organisatie_id) {
            $query->whereHas('user', function($q) use ($user) {
                $q->where('organisatie_id', $user->organisatie_id);
            });
        }

        // Filter op organisatie (alleen voor superadmin)
        if ($isSuperAdmin && $request->filled('organisatie_id')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('organisatie_id', $request->organisatie_id);
            });
        }

        // Filter op specifieke gebruiker
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter op datum range
        if ($request->filled('date_from')) {
            $query->whereDate('logged_in_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('logged_in_at', '<=', $request->date_to);
        }

        // Haal logs op met paginatie
        $logs = $query->paginate(20);
        $loginActivities = $logs; // Alias voor backward compatibility met view

        // Bereken statistieken - superadmin ziet alles, admin alleen eigen organisatie
        $statsQuery = \App\Models\LoginActivity::query();
        if (!$isSuperAdmin && $user->organisatie_id) {
            $statsQuery->whereHas('user', function($q) use ($user) {
                $q->where('organisatie_id', $user->organisatie_id);
            });
        }
        
        // Als superadmin organisatie filter heeft, pas ook toe op stats
        if ($isSuperAdmin && $request->filled('organisatie_id')) {
            $statsQuery->whereHas('user', function($q) use ($request) {
                $q->where('organisatie_id', $request->organisatie_id);
            });
        }

        $stats = [
            'total_logins_today' => (clone $statsQuery)
                ->whereDate('logged_in_at', today())
                ->count(),
            
            'total_logins_week' => (clone $statsQuery)
                ->whereBetween('logged_in_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->count(),
            
            'unique_users_today' => (clone $statsQuery)
                ->whereDate('logged_in_at', today())
                ->distinct('user_id')
                ->count('user_id'),
            
            'average_session_time' => (clone $statsQuery)
                ->whereNotNull('logged_out_at')
                ->whereNotNull('session_duration')
                ->avg('session_duration')
        ];

        // Haal alle gebruikers op voor filter dropdown
        $usersQuery = \App\Models\User::orderBy('name');
        if (!$isSuperAdmin && $user->organisatie_id) {
            $usersQuery->where('organisatie_id', $user->organisatie_id);
        }
        $users = $usersQuery->get();

        // Haal alle organisaties op voor superadmin filter
        $organisaties = collect();
        if ($isSuperAdmin) {
            $organisaties = \App\Models\Organisatie::orderBy('naam')->get();
            \Log::info('Organisaties opgehaald voor superadmin', [
                'count' => $organisaties->count(),
                'organisaties' => $organisaties->pluck('naam', 'id')
            ]);
        }

        return view('admin.users.activity-clean', compact('logs', 'loginActivities', 'stats', 'users', 'organisaties', 'isSuperAdmin'));