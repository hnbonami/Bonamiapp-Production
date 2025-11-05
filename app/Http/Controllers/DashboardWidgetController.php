public function index()
{
    $user = auth()->user();
    
    \Log::info('Dashboard geladen voor user', [
        'user_id' => $user->id,
        'role' => $user->role,
        'organisatie_id' => $user->organisatie_id
    ]);

    // Haal widgets op basis van zichtbaarheid en organisatie
    $widgets = DashboardWidget::query()
        ->where(function($query) use ($user) {
            // Superadmin ziet alles
            if ($user->isSuperAdmin()) {
                return;
            }
            
            // Anders: alleen widgets van eigen organisatie OF globale widgets
            $query->where('organisatie_id', $user->organisatie_id)
                  ->orWhereNull('organisatie_id');
        })
        ->get();

    // ⚡ EENVOUDIG: Gebruik altijd de widget layout (gedeeld voor iedereen)
    $layouts = $widgets->map(function($widget) {
        return [
            'widget' => $widget,
            'layout' => (object)[
                'grid_x' => $widget->grid_x ?? 0,
                'grid_y' => $widget->grid_y ?? 0,
                'grid_width' => $widget->grid_width ?? 4,
                'grid_height' => $widget->grid_height ?? 3,
                'is_visible' => true
            ]
        ];
    });

    \Log::info('Widgets geladen', [
        'user_id' => $user->id,
        'widgets_count' => $layouts->count()
    ]);

    return view('dashboard.index', [
        'layouts' => $layouts
    ]);
}

public function updateLayout(Request $request)
{
    $validated = $request->validate([
        'widget_id' => 'required|exists:dashboard_widgets,id',
        'grid_x' => 'required|integer|min:0',
        'grid_y' => 'required|integer|min:0',
        'grid_width' => 'required|integer|min:1|max:12',
        'grid_height' => 'required|integer|min:1',
    ]);

    $widget = DashboardWidget::findOrFail($validated['widget_id']);
    $user = auth()->user();

    // ⚡ EENVOUDIG: Update altijd de widget zelf (voor iedereen)
    $widget->update([
        'grid_x' => $validated['grid_x'],
        'grid_y' => $validated['grid_y'],
        'grid_width' => $validated['grid_width'],
        'grid_height' => $validated['grid_height'],
    ]);

    \Log::info('✅ Widget layout bijgewerkt voor iedereen', [
        'widget_id' => $widget->id,
        'title' => $widget->title,
        'zichtbaarheid' => $widget->zichtbaarheid ?? 'niet ingesteld',
        'updated_by' => $user->id,
        'position' => "{$validated['grid_x']},{$validated['grid_y']}",
        'size' => "{$validated['grid_width']}x{$validated['grid_height']}"
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Layout opgeslagen voor alle gebruikers'
    ]);
}