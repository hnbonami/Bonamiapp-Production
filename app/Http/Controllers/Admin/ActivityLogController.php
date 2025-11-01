    public function index(Request $request)
    {
        $query = \App\Models\LoginActivity::with('user')->orderBy('logged_in_at', 'desc');

        // Filter op huidige organisatie - via user relatie
        $organisatieId = auth()->user()->organisatie_id;
        if ($organisatieId) {
            $query->whereHas('user', function($q) use ($organisatieId) {
                $q->where('organisatie_id', $organisatieId);
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

        // Bereken statistieken
        $stats = [
            'total_logins_today' => \App\Models\LoginActivity::whereDate('logged_in_at', today())
                ->whereHas('user', function($q) use ($organisatieId) {
                    if ($organisatieId) {
                        $q->where('organisatie_id', $organisatieId);
                    }
                })
                ->count(),
            
            'total_logins_week' => \App\Models\LoginActivity::whereBetween('logged_in_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->whereHas('user', function($q) use ($organisatieId) {
                    if ($organisatieId) {
                        $q->where('organisatie_id', $organisatieId);
                    }
                })
                ->count(),
            
            'unique_users_today' => \App\Models\LoginActivity::whereDate('logged_in_at', today())
                ->whereHas('user', function($q) use ($organisatieId) {
                    if ($organisatieId) {
                        $q->where('organisatie_id', $organisatieId);
                    }
                })
                ->distinct('user_id')
                ->count('user_id'),
            
            'average_session_time' => \App\Models\LoginActivity::whereNotNull('logged_out_at')
                ->whereNotNull('session_duration')
                ->whereHas('user', function($q) use ($organisatieId) {
                    if ($organisatieId) {
                        $q->where('organisatie_id', $organisatieId);
                    }
                })
                ->avg('session_duration')
        ];

        // Haal alle gebruikers op voor filter dropdown
        $users = \App\Models\User::where('organisatie_id', $organisatieId)
            ->orderBy('name')
            ->get();

        return view('admin.users.activity', compact('logs', 'stats', 'users'));