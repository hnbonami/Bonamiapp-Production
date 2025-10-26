public function index(Request $request)
{
    $orgId = auth()->user()->organisatie_id;
    
    \Log::info('🔍 GRONDIG DEBUG - Users vs Klanten verschil', [
        'auth_user_id' => auth()->id(),
        'auth_user_email' => auth()->user()->email,
        'auth_user_org_id' => $orgId,
        'auth_user_role' => auth()->user()->role
    ]);
    
    // STAP 1: Tel ALLE klanten in deze organisatie
    $totalKlanten = \App\Models\Klant::where('organisatie_id', $orgId)->count();
    
    // STAP 2: Tel users met role='klant' in deze organisatie
    $totalKlantUsers = User::where('organisatie_id', $orgId)
        ->where('role', 'klant')
        ->count();
    
    // STAP 3: Haal alle klant emails op
    $klantEmails = \App\Models\Klant::where('organisatie_id', $orgId)
        ->pluck('email')
        ->toArray();
    
    // STAP 4: Check hoeveel klant emails een user account hebben
    $usersMetKlantEmail = User::whereIn('email', $klantEmails)->count();
    
    // STAP 5: Zoek klanten ZONDER user account
    $klantenZonderUser = \App\Models\Klant::where('organisatie_id', $orgId)
        ->whereNotIn('email', function($query) {
            $query->select('email')
                  ->from('users');
        })
        ->get(['id', 'naam', 'email', 'created_at']);
    
    // STAP 6: Zoek users met role=klant maar GEEN klant record
    $usersZonderKlant = User::where('organisatie_id', $orgId)
        ->where('role', 'klant')
        ->whereNotIn('email', $klantEmails)
        ->get(['id', 'name', 'email', 'created_at']);
    
    \Log::info('📊 ANALYSE RESULTAAT', [
        'total_klanten' => $totalKlanten,
        'total_klant_users' => $totalKlantUsers,
        'users_met_klant_email' => $usersMetKlantEmail,
        'klanten_zonder_user_count' => $klantenZonderUser->count(),
        'users_zonder_klant_count' => $usersZonderKlant->count(),
        'verschil' => $totalKlanten - $totalKlantUsers
    ]);
    
    // Log eerste 5 klanten zonder user voor debug
    if ($klantenZonderUser->count() > 0) {
        \Log::warning('⚠️ KLANTEN ZONDER USER ACCOUNT', [
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
        \Log::warning('⚠️ USERS ZONDER KLANT RECORD', [
            'count' => $usersZonderKlant->count(),
            'voorbeelden' => $usersZonderKlant->take(5)->map(fn($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'created_at' => $u->created_at->format('Y-m-d H:i')
            ])
        ]);
    }
    
    // Start query met EXPLICIETE organisatie filtering
    $query = User::where('organisatie_id', '=', $orgId);
    
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
    
    $users = $query->orderBy('created_at', 'desc')->paginate(20);
    
    // Statistics - alleen voor huidige organisatie
    $stats = [
        'total_users' => User::where('organisatie_id', $orgId)->count(),
        'active_users' => User::where('organisatie_id', $orgId)->where('status', 'active')->count(),
        'admin_count' => User::where('organisatie_id', $orgId)->where('role', 'admin')->count(),
        'medewerker_count' => User::where('organisatie_id', $orgId)->where('role', 'medewerker')->count(),
        'klant_count' => $totalKlantUsers, // Gebruik de tel van hierboven
        // Extra debug stats
        'total_klanten_in_db' => $totalKlanten,
        'klanten_zonder_user' => $klantenZonderUser->count(),
        'users_zonder_klant' => $usersZonderKlant->count(),
    ];
    
    \Log::info('✅ Users Index COMPLETE met EXTRA DEBUG', [
        'total_found' => $users->total(),
        'stats' => $stats
    ]);
    
    return view('admin.users.index', compact('users', 'stats'));
}