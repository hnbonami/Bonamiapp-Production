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
        
        \Log::info('🏠 Dashboard geladen', [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'organisatie_id' => $user->organisatie_id,
            'role' => $user->role,
        ]);
        
        // 🔒 BEPAAL WELKE WIDGETS DE GEBRUIKER MAG ZIEN
        $widgetsQuery = DashboardWidget::where('is_active', true)->with('creator');
        
        // Filter op basis van visibility EN organisatie
        $widgetsQuery->where(function($q) use ($user) {
            // Visibility: everyone (maar ALLEEN binnen eigen organisatie of geen organisatie)
            $q->where(function($subQ) use ($user) {
                $subQ->where('visibility', 'everyone');
                
                // Als user organisatie heeft, filter op organisatie van creator
                if ($user->organisatie_id) {
                    $subQ->whereHas('creator', function($creatorQ) use ($user) {
                        $creatorQ->where('organisatie_id', $user->organisatie_id);
                    });
                }
            })
            // OF visibility: medewerkers (binnen eigen organisatie)
            ->orWhere(function($subQ) use ($user) {
                $subQ->where('visibility', 'medewerkers');
                
                if ($user->organisatie_id) {
                    $subQ->whereHas('creator', function($creatorQ) use ($user) {
                        $creatorQ->where('organisatie_id', $user->organisatie_id);
                    });
                }
            })
            // OF visibility: only_me (alleen eigen widgets)
            ->orWhere(function($subQ) use ($user) {
                $subQ->where('visibility', 'only_me')
                     ->where('created_by', $user->id);
            })
            // OF eigen widgets (altijd zichtbaar)
            ->orWhere('created_by', $user->id);
        });
        
        $widgets = $widgetsQuery->get();
        
        \Log::info('📊 Widgets gefilterd - DETAILS', [
            'totaal_widgets' => $widgets->count(),
            'widget_ids' => $widgets->pluck('id')->toArray(),
            'widget_details' => $widgets->map(fn($w) => [
                'id' => $w->id,
                'title' => $w->title,
                'created_by' => $w->created_by,
                'creator_name' => $w->creator->name ?? 'Unknown',
                'creator_org_id' => $w->creator->organisatie_id ?? 'NULL',
                'visibility' => $w->visibility,
            ])->toArray(),
        ]);
        
        // Haal layouts op voor deze user
        $layouts = $widgets->map(function($widget) use ($user) {
            $layout = DashboardUserLayout::firstOrCreate(
                ['user_id' => $user->id, 'widget_id' => $widget->id],
                [
                    'grid_x' => 0,
                    'grid_y' => 0,
                    'grid_width' => $widget->grid_width ?? 4,
                    'grid_height' => $widget->grid_height ?? 3,
                    'is_visible' => true,
                ]
            );
            
            return ['widget' => $widget, 'layout' => $layout];
        });
        
        return view('dashboard.index', compact('layouts'));
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
        
        // 🔒 AUTHORIZATION CHECK - INCLUSIEF organisatie_admin
        $canEdit = false;
        
        if (in_array($user->role, ['super_admin', 'superadmin'])) {
            $canEdit = true;
        } elseif (in_array($user->role, ['admin', 'organisatie_admin']) && $user->organisatie_id && $widget->creator->organisatie_id === $user->organisatie_id) {
            $canEdit = true;
        } elseif ($user->role === 'medewerker' && $widget->created_by === $user->id) {
            $canEdit = true;
        }
        
        \Log::info('✏️ Widget update authorization check', [
            'user_id' => $user->id,
            'user_role' => $user->role,
            'user_org' => $user->organisatie_id,
            'widget_id' => $widget->id,
            'widget_creator_org' => $widget->creator->organisatie_id ?? null,
            'can_edit' => $canEdit,
        ]);
        
        if (!$canEdit) {
            abort(403, 'Je hebt geen rechten om deze widget te bewerken');
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
        
        // 🔒 AUTHORIZATION CHECK - INCLUSIEF organisatie_admin
        $canDelete = false;
        
        if (in_array($user->role, ['super_admin', 'superadmin'])) {
            $canDelete = true;
        } elseif (in_array($user->role, ['admin', 'organisatie_admin']) && $user->organisatie_id && $widget->creator->organisatie_id === $user->organisatie_id) {
            $canDelete = true;
        } elseif ($user->role === 'medewerker' && $widget->created_by === $user->id) {
            $canDelete = true;
        }
        
        \Log::info('🗑️ Widget delete authorization check', [
            'user_id' => $user->id,
            'user_role' => $user->role,
            'user_org' => $user->organisatie_id,
            'widget_id' => $widget->id,
            'widget_creator_org' => $widget->creator->organisatie_id ?? null,
            'can_delete' => $canDelete,
        ]);
        
        if (!$canDelete) {
            abort(403, 'Je hebt geen rechten om deze widget te verwijderen');
        }
        
        // Verwijder widget en gerelateerde layouts
        DashboardUserLayout::where('widget_id', $widget->id)->delete();
        $widget->delete();
        
        return redirect()->route('dashboard.index')->with('success', 'Widget succesvol verwijderd!');
    }
}