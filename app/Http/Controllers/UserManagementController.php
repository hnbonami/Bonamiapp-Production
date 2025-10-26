public function index(Request $request)
{
    $orgId = auth()->user()->organisatie_id;
    
    // SCHRIJF NAAR EIGEN BESTAND VOOR DEBUG
    $debugFile = storage_path('logs/users-klanten-debug.txt');
    file_put_contents($debugFile, "=== DEBUG START: " . now() . " ===\n", FILE_APPEND);
    file_put_contents($debugFile, "Auth User ID: " . auth()->id() . "\n", FILE_APPEND);
    file_put_contents($debugFile, "Organisatie ID: " . $orgId . "\n", FILE_APPEND);
    
    \Log::info('🔍 GRONDIG DEBUG - Users vs Klanten verschil (UserManagementController)', [
        'auth_user_id' => auth()->id(),
        'auth_user_email' => auth()->user()->email,
        'auth_user_org_id' => $orgId,
        'auth_user_role' => auth()->user()->role
    ]);
    
    // STAP 1: Tel ALLE klanten in deze organisatie
    $totalKlanten = \App\Models\Klant::where('organisatie_id', $orgId)->count();
    file_put_contents($debugFile, "Total Klanten: " . $totalKlanten . "\n", FILE_APPEND);
    
    // STAP 2: Tel users met role='klant' in deze organisatie
    $totalKlantUsers = \App\Models\User::where('organisatie_id', $orgId)
        ->where('role', 'klant')
        ->count();
    file_put_contents($debugFile, "Total Klant Users: " . $totalKlantUsers . "\n", FILE_APPEND);
    file_put_contents($debugFile, "VERSCHIL: " . ($totalKlanten - $totalKlantUsers) . "\n", FILE_APPEND);
    
    // STAP 3: Haal alle klant emails op
    $klantEmails = \App\Models\Klant::where('organisatie_id', $orgId)
        ->pluck('email')
        ->toArray();
    
    // STAP 4: Check hoeveel klant emails een user account hebben
    $usersMetKlantEmail = \App\Models\User::whereIn('email', $klantEmails)->count();
    
    // STAP 5: Zoek klanten ZONDER user account
    $klantenZonderUser = \App\Models\Klant::where('organisatie_id', $orgId)
        ->whereNotIn('email', function($query) {
            $query->select('email')
                  ->from('users');
        })
        ->get(['id', 'naam', 'email', 'created_at']);
    
    file_put_contents($debugFile, "Klanten ZONDER user: " . $klantenZonderUser->count() . "\n", FILE_APPEND);
    
    if ($klantenZonderUser->count() > 0) {
        file_put_contents($debugFile, "\nEERSTE 5 KLANTEN ZONDER USER:\n", FILE_APPEND);
        foreach ($klantenZonderUser->take(5) as $k) {
            file_put_contents($debugFile, "  - " . $k->naam . " (" . $k->email . ")\n", FILE_APPEND);
        }
    }
    
    file_put_contents($debugFile, "\n=== DEBUG END ===\n\n", FILE_APPEND);
    
    // STAP 6: Zoek users met role=klant maar GEEN klant record
    $usersZonderKlant = \App\Models\User::where('organisatie_id', $orgId)
        ->where('role', 'klant')
        ->whereNotIn('email', $klantEmails)
        ->get(['id', 'name', 'email', 'created_at']);
    
    \Log::info('📊 ANALYSE RESULTAAT (UserManagementController)', [
        'total_klanten' => $totalKlanten,
        'total_klant_users' => $totalKlantUsers,
        'users_met_klant_email' => $usersMetKlantEmail,
        'klanten_zonder_user_count' => $klantenZonderUser->count(),
        'users_zonder_klant_count' => $usersZonderKlant->count(),
        'verschil' => $totalKlanten - $totalKlantUsers
    ]);
    
    // Log eerste 5 klanten zonder user voor debug
    if ($klantenZonderUser->count() > 0) {
        \Log::warning('⚠️ KLANTEN ZONDER USER ACCOUNT (UserManagementController)', [
            'count' => $klantenZonderUser->count(),
            'voorbeelden' => $klantenZonderUser->take(5)->map(fn($k) => [
                'id' => $k->id,
                'naam' => $k->naam,
                'email' => $k->email,
                'created_at' => $k->created_at->format('Y-m-d H:i')
            ])
        ]);
    }
    
    // Log users zonder klant record
    if ($usersZonderKlant->count() > 0) {
        \Log::warning('⚠️ USERS ZONDER KLANT RECORD (UserManagementController)', [
            'count' => $usersZonderKlant->count(),
            'voorbeelden' => $usersZonderKlant->take(5)->map(fn($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'created_at' => $u->created_at->format('Y-m-d H:i')
            ])
        ]);
    }
    
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