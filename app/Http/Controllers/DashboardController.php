<?php

namespace App\Http\Controllers;

use App\Models\DashboardWidget;
use App\Models\DashboardUserLayout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DashboardController extends Controller
{
    /**
     * Toon dashboard met widgets
     */
    public function index()
    {
        $user = Auth::user();
        
        $widgets = DashboardWidget::where('is_active', true)
            ->get()
            ->filter(function($widget) use ($user) {
                return $widget->canBeSeenBy($user);
            });

        $layouts = [];
        foreach ($widgets as $widget) {
            $layout = DashboardUserLayout::where('user_id', $user->id)
                ->where('widget_id', $widget->id)
                ->first();
            
            if (!$layout) {
                $layout = DashboardUserLayout::create([
                    'user_id' => $user->id,
                    'widget_id' => $widget->id,
                    'grid_x' => $widget->grid_x,
                    'grid_y' => $widget->grid_y,
                    'grid_width' => $widget->grid_width,
                    'grid_height' => $widget->grid_height,
                    'is_visible' => true,
                ]);
            }
            
            $layouts[] = [
                'widget' => $widget,
                'layout' => $layout,
            ];
        }

        return view('dashboard.index', compact('layouts', 'user'));
    }

    /**
     * Toon create form voor widget
     */
    public function create(Request $request)
    {
        $type = $request->input('type', 'chart'); // Default naar chart
        
        return view('dashboard.create', compact('type'));
    }    /**
     * Sla nieuwe widget op
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $allowedRoles = ['medewerker', 'admin', 'super_admin', 'superadmin'];
        
        if (!in_array($user->role, $allowedRoles)) {
            abort(403, 'Geen toegang');
        }

        $validated = $request->validate([
            'type' => 'required|in:chart,text,image,button,metric',
            'title' => 'nullable|string|max:255',
            'content' => 'nullable|string',
            'chart_type' => 'nullable|string',
            'chart_scope' => 'nullable|string',
            'chart_periode' => 'nullable|string',
            'button_text' => 'nullable|string|max:100',
            'button_url' => 'nullable|string|max:255',
            'background_color' => 'nullable|string|max:7',
            'text_color' => 'nullable|string|max:7',
            'grid_width' => 'required|integer|min:1|max:12',
            'grid_height' => 'required|integer|min:1|max:12',
            'visibility' => 'required|in:everyone,medewerkers,only_me',
            'image' => 'nullable|image|max:2048',
        ]);

        // Voor chart widgets: sla chart config op
        if ($validated['type'] === 'chart') {
            $validated['chart_data'] = json_encode([
                'chart_type' => $request->input('chart_type'),
                'scope' => $request->input('chart_scope', 'auto'),
                'periode' => $request->input('chart_periode', 'laatste-30-dagen'),
            ]);
        }

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('dashboard/widgets', 'public');
        }

        $validated['created_by'] = $user->id;
        $validated['grid_x'] = 0;
        $validated['grid_y'] = 0;

        $widget = DashboardWidget::create($validated);

        return redirect()->route('dashboard.index')
            ->with('success', 'Widget succesvol aangemaakt!');
    }

    /**
     * Toon edit form voor widget
     */
    public function edit(DashboardWidget $widget)
    {
        $user = Auth::user();
        
        // Check permissions
        if ($widget->created_by !== $user->id && !in_array($user->role, ['admin', 'super_admin', 'superadmin'])) {
            abort(403, 'Je mag alleen je eigen widgets bewerken');
        }
        
        return view('dashboard.edit', compact('widget'));
    }

    /**
     * Update widget
     */
    public function update(Request $request, DashboardWidget $widget)
    {
        $user = Auth::user();
        
        // Check permissions
        if ($widget->created_by !== $user->id && !in_array($user->role, ['admin', 'super_admin', 'superadmin'])) {
            abort(403, 'Je mag alleen je eigen widgets bewerken');
        }

        $validated = $request->validate([
            'type' => 'required|in:chart,text,image,button,metric',
            'title' => 'nullable|string|max:255',
            'content' => 'nullable|string',
            'chart_type' => 'nullable|string',
            'chart_scope' => 'nullable|string',
            'chart_periode' => 'nullable|string',
            'button_text' => 'nullable|string|max:100',
            'button_url' => 'nullable|string|max:255',
            'background_color' => 'nullable|string|max:7',
            'text_color' => 'nullable|string|max:7',
            'grid_width' => 'required|integer|min:1|max:12',
            'grid_height' => 'required|integer|min:1|max:12',
            'visibility' => 'required|in:everyone,medewerkers,only_me',
            'image' => 'nullable|image|max:2048',
        ]);

        // Upload nieuwe image als aanwezig
        if ($request->hasFile('image')) {
            // Verwijder oude image
            if ($widget->image_path && Storage::disk('public')->exists($widget->image_path)) {
                Storage::disk('public')->delete($widget->image_path);
            }
            $validated['image_path'] = $request->file('image')->store('dashboard/widgets', 'public');
        }

        // Voor chart widgets: sla chart config op
        if ($validated['type'] === 'chart') {
            $validated['chart_data'] = json_encode([
                'chart_type' => $request->input('chart_type'),
                'scope' => $request->input('chart_scope', 'auto'),
                'periode' => $request->input('chart_periode', 'laatste-30-dagen'),
            ]);
        }

        $widget->update($validated);

        return redirect()->route('dashboard.index')
            ->with('success', 'Widget succesvol bijgewerkt!');
    }

    /**
     * Update widget layout
     */
    public function updateLayout(Request $request)
    {
        $validated = $request->validate([
            'widget_id' => 'required|exists:dashboard_widgets,id',
            'grid_x' => 'required|integer|min:0',
            'grid_y' => 'required|integer|min:0',
            'grid_width' => 'required|integer|min:1|max:12',
            'grid_height' => 'required|integer|min:1|max:12',
        ]);

        $user = Auth::user();
        $widgetId = $validated['widget_id'];

        $layout = DashboardUserLayout::firstOrCreate(
            [
                'user_id' => $user->id,
                'widget_id' => $widgetId,
            ],
            [
                'grid_x' => $validated['grid_x'],
                'grid_y' => $validated['grid_y'],
                'grid_width' => $validated['grid_width'],
                'grid_height' => $validated['grid_height'],
                'is_visible' => true,
            ]
        );

        $layout->update([
            'grid_x' => $validated['grid_x'],
            'grid_y' => $validated['grid_y'],
            'grid_width' => $validated['grid_width'],
            'grid_height' => $validated['grid_height'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Layout opgeslagen',
            'layout' => $layout
        ]);
    }

    /**
     * Toggle widget visibility
     */
    public function toggleVisibility(DashboardWidget $widget)
    {
        $user = Auth::user();
        
        $layout = DashboardUserLayout::where('user_id', $user->id)
            ->where('widget_id', $widget->id)
            ->first();

        if ($layout) {
            $layout->update(['is_visible' => !$layout->is_visible]);
        }

        return redirect()->back()->with('success', 'Widget zichtbaarheid aangepast');
    }

    /**
     * Verwijder widget
     */
    public function destroy(DashboardWidget $widget)
    {
        $user = Auth::user();
        
        if (in_array($user->role, ['super_admin', 'superadmin'])) {
            // OK
        } elseif ($user->role === 'admin') {
            // OK
        } elseif ($user->role === 'medewerker') {
            if ($widget->created_by !== $user->id) {
                abort(403, 'Je mag alleen je eigen widgets verwijderen');
            }
        } else {
            abort(403, 'Geen toegang');
        }

        if ($widget->image_path && Storage::disk('public')->exists($widget->image_path)) {
            Storage::disk('public')->delete($widget->image_path);
        }

        $widget->delete();

        return redirect()->route('dashboard.index')
            ->with('success', 'Widget succesvol verwijderd!');
    }
}