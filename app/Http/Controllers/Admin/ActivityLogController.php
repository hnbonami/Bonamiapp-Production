public function index(Request $request)
{
    $query = ActivityLog::query();

    // Filter op huidige organisatie - via user relatie
    $organisatieId = auth()->user()->organisatie_id;
    $query->whereHas('user', function($q) use ($organisatieId) {
        $q->where('organisatie_id', $organisatieId);
    });

    // ...existing filters...