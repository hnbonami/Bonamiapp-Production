<?php

namespace App\Http\Controllers;

use App\Models\DashboardWidget;
use App\Models\DashboardUserLayout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class DashboardController extends Controller
{
    use AuthorizesRequests;
    /**
     * Toon het dashboard met widgets
     */
    public function index()
    {
        $user = auth()->user();
        
        // Haal widgets op op basis van rol en organisatie
        $widgets = DashboardWidget::visibleFor($user)
            ->active()
            ->with('creator')
            ->get();

        // Haal user-specific layouts op, of maak defaults aan
        $layouts = $widgets->map(function ($widget) use ($user) {
            $layout = DashboardUserLayout::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'widget_id' => $widget->id,
                ],
                [
                    'grid_x' => $widget->grid_x ?? 0,
                    'grid_y' => $widget->grid_y ?? 0,
                    'grid_width' => $widget->grid_width ?? 4,
                    'grid_height' => $widget->grid_height ?? 3,
                    'is_visible' => true,
                ]
            );

            return [
                'widget' => $widget,
                'layout' => $layout,
            ];
        });

        Log::info('Dashboard geladen voor user', [
            'user_id' => $user->id,
            'role' => $user->role,
            'organisatie_id' => $user->organisatie_id,
            'widgets_count' => $widgets->count()
        ]);

        return view('dashboard.index', compact('layouts'));
    }

    /**
     * Toon formulier voor nieuwe widget
     */
    public function create(Request $request)
    {
        $user = auth()->user();
        
        // Check autorisatie via policy
        $this->authorize('create', DashboardWidget::class);

        $type = $request->get('type', 'text');

        return view('dashboard.create', compact('type'));
    }

    /**
     * Sla nieuwe widget op
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        
        // Check autorisatie via policy
        $this->authorize('create', DashboardWidget::class);

        $validated = $request->validate([
            'type' => 'required|in:text,metric,chart,image,button',
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'chart_type' => 'nullable|string',
            'chart_data' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'button_text' => 'nullable|string|max:255',
            'button_url' => 'nullable|string|max:255',
            'background_color' => 'nullable|string',
            'text_color' => 'nullable|string',
            'grid_width' => 'nullable|integer|min:1|max:12',
            'grid_height' => 'nullable|integer|min:1|max:10',
            'visibility' => 'required|in:everyone,medewerkers,only_me',
            'metric_type' => 'nullable|string', // NIEUW: Valideer metric type
        ]);

        // Medewerkers mogen geen 'everyone' visibility instellen (alleen admin)
        if ($user->role === 'medewerker' && $validated['visibility'] === 'everyone') {
            $validated['visibility'] = 'medewerkers';
            Log::warning('Medewerker probeerde everyone visibility in te stellen', [
                'user_id' => $user->id,
                'forced_to' => 'medewerkers'
            ]);
        }

        // Voor chart widgets: zorg dat chart_data altijd valid JSON is
        if ($validated['type'] === 'chart' && !empty($validated['chart_type'])) {
            $validated['chart_data'] = json_encode([
                'chart_type' => $validated['chart_type'],
                'scope' => 'auto',
                'periode' => 'laatste-30-dagen'
            ]);
        }

        // Upload afbeelding indien aanwezig
        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('widgets', 'public');
        }

        // Zet creator en organisatie
        $validated['created_by'] = $user->id;
        $validated['organisatie_id'] = $user->organisatie_id;
        $validated['is_active'] = true;

        // Standaard posities en groottes
        $validated['grid_x'] = $validated['grid_x'] ?? 0;
        $validated['grid_y'] = $validated['grid_y'] ?? 0;
        $validated['grid_width'] = $validated['grid_width'] ?? 4;
        $validated['grid_height'] = $validated['grid_height'] ?? 3;

        // Als metric_type is ingesteld en niet 'custom', haal live data op
        if ($validated['type'] === 'metric' && isset($validated['metric_type']) && $validated['metric_type'] !== 'custom') {
            // Bereken de metric waarde
            $metricValue = $this->calculateMetricValue($validated['metric_type']);
            $validated['content'] = $metricValue['formatted'];
        }

        $widget = DashboardWidget::create($validated);

        Log::info('Widget aangemaakt', [
            'widget_id' => $widget->id,
            'type' => $widget->type,
            'user_id' => $user->id,
            'organisatie_id' => $widget->organisatie_id
        ]);

        return redirect()
            ->route('dashboard.index')
            ->with('success', 'Widget succesvol toegevoegd!');
    }

    /**
     * Bereken metric waarde (helper method)
     */
    private function calculateMetricValue($metricType)
    {
        $controller = new DashboardStatsController();
        $request = new Request(['metric_type' => $metricType]);
        $response = $controller->calculateMetric($request);
        
        return json_decode($response->getContent(), true);
    }

    /**
     * Toon formulier voor widget bewerken
     */
    public function edit(DashboardWidget $widget)
    {
        $this->authorize('update', $widget);

        return view('dashboard.edit', compact('widget'));
    }

    /**
     * Update een widget
     */
    public function update(Request $request, DashboardWidget $widget)
    {
        $this->authorize('update', $widget);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'chart_type' => 'nullable|string',
            'chart_data' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'button_text' => 'nullable|string|max:255',
            'button_url' => 'nullable|string|max:255',
            'background_color' => 'nullable|string',
            'text_color' => 'nullable|string',
            'visibility' => 'required|in:everyone,medewerkers,only_me',
        ]);

        // Medewerkers mogen geen 'everyone' visibility instellen (alleen admin)
        if (auth()->user()->role === 'medewerker' && $validated['visibility'] === 'everyone') {
            $validated['visibility'] = 'medewerkers';
            Log::warning('Medewerker probeerde everyone visibility in te stellen', [
                'user_id' => auth()->id(),
                'widget_id' => $widget->id,
                'forced_to' => 'medewerkers'
            ]);
        }

        // Voor chart widgets: update chart_data indien chart_type is gewijzigd
        if ($widget->type === 'chart' && !empty($validated['chart_type'])) {
            $validated['chart_data'] = json_encode([
                'chart_type' => $validated['chart_type'],
                'scope' => 'auto',
                'periode' => 'laatste-30-dagen'
            ]);
        }

        // Upload nieuwe afbeelding indien aanwezig
        if ($request->hasFile('image')) {
            // Verwijder oude afbeelding
            if ($widget->image_path) {
                Storage::disk('public')->delete($widget->image_path);
            }
            $validated['image_path'] = $request->file('image')->store('widgets', 'public');
        }

        $widget->update($validated);

        Log::info('Widget bijgewerkt', [
            'widget_id' => $widget->id,
            'user_id' => auth()->id()
        ]);

        return redirect()
            ->route('dashboard.index')
            ->with('success', 'Widget succesvol bijgewerkt!');
    }

    /**
     * Update widget layout (positie en grootte)
     */
    public function updateLayout(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'widget_id' => 'required|exists:dashboard_widgets,id',
            'grid_x' => 'required|integer|min:0',
            'grid_y' => 'required|integer|min:0',
            'grid_width' => 'required|integer|min:1|max:12',
            'grid_height' => 'required|integer|min:1|max:10',
        ]);

        $widget = DashboardWidget::findOrFail($validated['widget_id']);

        // Check of user deze widget mag verplaatsen/resizen
        if (!$widget->canBeDraggedBy($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Je hebt geen toestemming om deze widget te verplaatsen.'
            ], 403);
        }

        // Update of create user layout
        $layout = DashboardUserLayout::updateOrCreate(
            [
                'user_id' => $user->id,
                'widget_id' => $widget->id,
            ],
            [
                'grid_x' => $validated['grid_x'],
                'grid_y' => $validated['grid_y'],
                'grid_width' => $validated['grid_width'],
                'grid_height' => $validated['grid_height'],
            ]
        );

        Log::info('Widget layout bijgewerkt', [
            'widget_id' => $widget->id,
            'user_id' => $user->id,
            'position' => "{$validated['grid_x']},{$validated['grid_y']}",
            'size' => "{$validated['grid_width']}x{$validated['grid_height']}"
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Layout opgeslagen!',
        ]);
    }

    /**
     * Toggle widget visibility
     */
    public function toggleVisibility(DashboardWidget $widget)
    {
        $this->authorize('view', $widget);

        $user = auth()->user();

        $layout = DashboardUserLayout::where('user_id', $user->id)
            ->where('widget_id', $widget->id)
            ->firstOrFail();

        $layout->update([
            'is_visible' => !$layout->is_visible,
        ]);

        return response()->json([
            'success' => true,
            'is_visible' => $layout->is_visible,
        ]);
    }

    /**
     * Verwijder een widget
     */
    public function destroy(DashboardWidget $widget)
    {
        $this->authorize('delete', $widget);

        // Verwijder afbeelding indien aanwezig
        if ($widget->image_path) {
            Storage::disk('public')->delete($widget->image_path);
        }

        // Verwijder alle user layouts
        $widget->userLayouts()->delete();

        Log::info('Widget verwijderd', [
            'widget_id' => $widget->id,
            'user_id' => auth()->id()
        ]);

        $widget->delete();

        return redirect()
            ->route('dashboard.index')
            ->with('success', 'Widget succesvol verwijderd!');
    }
}