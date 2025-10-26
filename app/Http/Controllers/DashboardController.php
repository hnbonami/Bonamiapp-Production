<?php

namespace App\Http\Controllers;

use App\Models\DashboardWidget;
use App\Models\DashboardUserLayout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Klant;
use App\Models\Bikefit;
use App\Models\Inspanningstest;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    /**
     * Toon dashboard met widgets
     */
    public function index()
    {
        $user = Auth::user();
        $klant = null;
        $organisatieId = auth()->user()->organisatie_id;
        
        try {
            // Log voor debugging
            Log::info('Dashboard accessed by user', [
                'user_id' => auth()->id(),
                'user_type' => auth()->user()->user_type,
                'klant_id' => auth()->user()->klant_id ?? 'null',
                'email' => auth()->user()->email
            ]);
            
                        // Als de gebruiker een admin is, toon admin dashboard
            if (auth()->user()->user_type === 'admin') {
                Log::info('Admin user accessing dashboard');
                // Voor admin: null klant variabele meegeven en dashboard view gebruiken
                $klant = null;
                Log::info('Admin dashboard: klant is null, returning dashboard view');
                return view('dashboard', compact('klant'));
            }
            
            // Als de gebruiker een klant is, probeer klant informatie op te halen
            if (auth()->user()->user_type === 'klant') {
                // Eerst proberen via klant_id
                if (auth()->user()->klant_id) {
                    $klant = Klant::find(auth()->user()->klant_id);
                    Log::info('Klant gevonden via klant_id', ['klant_id' => $klant?->id]);
                }
                
                // Als geen klant gevonden via klant_id, probeer via email
                if (!$klant) {
                    $klant = Klant::where('email', auth()->user()->email)->first();
                    Log::info('Klant gezocht via email', ['found' => !!$klant, 'klant_id' => $klant?->id]);
                    
                    // Als klant gevonden via email, update user record
                    if ($klant) {
                        auth()->user()->update(['klant_id' => $klant->id]);
                        Log::info('User klant_id updated', ['klant_id' => $klant->id]);
                    }
                }
                
                return view('dashboard', compact('klant'));
            }
            
            $stats = [
                'totaal_klanten' => Klant::where('organisatie_id', $organisatieId)->count(),
                'totaal_bikefits' => Bikefit::whereHas('klant', function($q) use ($organisatieId) {
                    $q->where('organisatie_id', $organisatieId);
                })->count(),
                'totaal_inspanningstests' => Inspanningstest::whereHas('klant', function($q) use ($organisatieId) {
                    $q->where('organisatie_id', $organisatieId);
                })->count(),
                // ...bestaande statistieken...
            ];
            
        } catch (\Exception $e) {
            Log::error('Dashboard error: ' . $e->getMessage());
            // Fallback voor onbekende user types
        }
        
        // Haal alle widgets op die user mag zien
        $widgets = DashboardWidget::where('is_active', true)
            ->get()
            ->filter(function($widget) use ($user) {
                return $widget->canBeSeenBy($user);
            });

        // Haal user layouts op of maak defaults
        $layouts = [];
        foreach ($widgets as $widget) {
            $layout = $widget->getLayoutForUser($user);
            
            if (!$layout) {
                // Maak default layout voor user
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
     * Toon widget create form
     */
    public function create()
    {
        $user = Auth::user();
        
        // Debug logging
        \Log::info('Dashboard create access attempt', [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_role' => $user->role,
            'allowed_roles' => ['medewerker', 'admin', 'super_admin', 'superadmin']
        ]);
        
        // Alleen medewerkers, admins en super admins mogen widgets maken
        // Accepteer zowel 'super_admin' als 'superadmin'
        $allowedRoles = ['medewerker', 'admin', 'super_admin', 'superadmin'];
        
        if (!in_array($user->role, $allowedRoles)) {
            \Log::warning('Dashboard create access denied', [
                'user_role' => $user->role,
                'user_id' => $user->id
            ]);
            abort(403, 'Geen toegang - alleen medewerkers en hoger kunnen widgets aanmaken. Je huidige role: ' . $user->role);
        }
        
        return view('dashboard.create');
    }

    /**
     * Sla nieuwe widget op
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        // Alleen medewerkers, admins en super admins mogen widgets maken
        // Accepteer zowel 'super_admin' als 'superadmin'
        $allowedRoles = ['medewerker', 'admin', 'super_admin', 'superadmin'];
        
        if (!in_array($user->role, $allowedRoles)) {
            \Log::warning('Dashboard store access denied', [
                'user_role' => $user->role,
                'user_id' => $user->id
            ]);
            abort(403, 'Geen toegang - alleen medewerkers en hoger kunnen widgets aanmaken. Je huidige role: ' . $user->role);
        }

        $validated = $request->validate([
            'type' => 'required|in:chart,text,image,button,metric',
            'title' => 'nullable|string|max:255',
            'content' => 'nullable|string',
            'chart_type' => 'nullable|string',
            'chart_data' => 'nullable|json',
            'button_text' => 'nullable|string|max:100',
            'button_url' => 'nullable|string|max:255',
            'background_color' => 'nullable|string|max:7',
            'text_color' => 'nullable|string|max:7',
            'grid_width' => 'required|integer|min:1|max:12',
            'grid_height' => 'required|integer|min:1|max:12',
            'visibility' => 'required|in:everyone,medewerkers,only_me',
            'image' => 'nullable|image|max:2048',
        ]);

        // Upload image als aanwezig
        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('dashboard/widgets', 'public');
        }

        $validated['created_by'] = $user->id;
        $validated['grid_x'] = 0;
        $validated['grid_y'] = 0;

        $widget = DashboardWidget::create($validated);

        \Log::info('Dashboard widget aangemaakt:', [
            'widget_id' => $widget->id,
            'type' => $widget->type,
            'created_by' => $user->name,
            'role' => $user->role
        ]);

        return redirect()->route('dashboard.index')
            ->with('success', 'Widget succesvol aangemaakt!');
    }

    /**
     * Update widget layout (drag & drop, resize)
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
        $widget = DashboardWidget::findOrFail($validated['widget_id']);

        // Check of user widget mag zien
        if (!$widget->canBeSeenBy($user)) {
            return response()->json(['error' => 'Geen toegang'], 403);
        }

        // Update of create layout
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

        return response()->json([
            'success' => true,
            'layout' => $layout
        ]);
    }

    /**
     * Toggle widget visibility
     */
    public function toggleVisibility(Request $request, DashboardWidget $widget)
    {
        $user = Auth::user();

        $layout = DashboardUserLayout::where('user_id', $user->id)
            ->where('widget_id', $widget->id)
            ->firstOrFail();

        $layout->is_visible = !$layout->is_visible;
        $layout->save();

        return response()->json([
            'success' => true,
            'is_visible' => $layout->is_visible
        ]);
    }

    /**
     * Verwijder widget
     * - Medewerker: alleen eigen widgets
     * - Admin: alle widgets binnen organisatie
     * - Super Admin: alle widgets
     */
    public function destroy(DashboardWidget $widget)
    {
        $user = Auth::user();
        
        // Super Admin mag alles verwijderen (beide spellingen)
        if (in_array($user->role, ['super_admin', 'superadmin'])) {
            // OK - super admin mag alles
        }
        // Admin mag alles binnen organisatie verwijderen
        elseif ($user->role === 'admin') {
            // Check of widget binnen zelfde organisatie is (indien van toepassing)
            // Voor nu: admin mag alles
        }
        // Medewerker mag alleen eigen widgets verwijderen
        elseif ($user->role === 'medewerker') {
            if ($widget->created_by !== $user->id) {
                abort(403, 'Je mag alleen je eigen widgets verwijderen');
            }
        }
        // Klanten mogen helemaal niks verwijderen
        else {
            abort(403, 'Geen toegang om widgets te verwijderen. Je role: ' . $user->role);
        }

        // Verwijder image als aanwezig
        if ($widget->image_path && Storage::disk('public')->exists($widget->image_path)) {
            Storage::disk('public')->delete($widget->image_path);
        }

        $widget->delete();

        \Log::info('Dashboard widget verwijderd:', [
            'widget_id' => $widget->id,
            'deleted_by' => $user->name,
            'role' => $user->role
        ]);

        return redirect()->route('dashboard.index')
            ->with('success', 'Widget succesvol verwijderd!');
    }
}