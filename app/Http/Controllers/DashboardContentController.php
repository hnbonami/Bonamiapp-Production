<?php

namespace App\Http\Controllers;

use App\Models\StaffNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DashboardContentController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Log voor debugging
        \Log::info('Dashboard Content Index', [
            'user_id' => $user->id,
            'user_role' => $user->role,
            'organisatie_id' => $user->organisatie_id
        ]);
        
        // Check if nieuwe velden bestaan (na migratie)
        $hasNewFields = \Schema::hasColumn('staff_notes', 'type');
        
        // 🔍 DEBUG: Check of organisatie_id kolom bestaat
        $hasOrganisatieIdColumn = \Schema::hasColumn('staff_notes', 'organisatie_id');
        \Log::info('🔍 Database schema check', [
            'has_new_fields' => $hasNewFields,
            'has_organisatie_id_column' => $hasOrganisatieIdColumn
        ]);
        
        if ($hasNewFields) {
            $query = StaffNote::with('user')
                ->where('is_archived', false)
                ->where(function($q) {
                    $q->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
                })
                ->where(function($q) {
                    $q->whereNull('published_at')
                      ->orWhere('published_at', '<=', now());
                });
            
            // Filter op basis van role en organisatie
            if ($user->role === 'klant') {
                // Klanten zien:
                // 1. Content met visibility 'all' EN hun eigen organisatie_id
                // 2. Content met visibility 'all' EN organisatie_id = NULL (globale content)
                $query->where('visibility', 'all')
                      ->where(function($q) use ($user) {
                          $q->where('organisatie_id', $user->organisatie_id)
                            ->orWhereNull('organisatie_id');
                      });
                      
                \Log::info('✅ Filtering for klant', [
                    'visibility' => 'all',
                    'user_organisatie_id' => $user->organisatie_id,
                    'also_showing' => 'global content (organisatie_id = null)'
                ]);
            } elseif (in_array($user->role, ['medewerker', 'admin', 'organisatie_admin'])) {
                // KRITIEK: Medewerkers/admins zien ALLEEN hun eigen organisatie content
                // GEEN globale content (NULL) om verwarring tussen organisaties te voorkomen
                if ($user->organisatie_id) {
                    // ALLEEN content van eigen organisatie - GEEN NULL
                    $query->where('organisatie_id', $user->organisatie_id);
                    
                    \Log::info('✅ Filtering for medewerker/admin with organisatie', [
                        'user_role' => $user->role,
                        'user_organisatie_id' => $user->organisatie_id,
                        'showing' => 'ONLY own organisation content (NO global)',
                        'sql_query' => $query->toSql(),
                        'bindings' => $query->getBindings()
                    ]);
                } else {
                    // Geen organisatie? Alleen globale content
                    $query->whereNull('organisatie_id');
                    
                    \Log::info('⚠️ Medewerker without organisatie - showing only global content', [
                        'user_role' => $user->role
                    ]);
                }
            } elseif ($user->role === 'superadmin') {
                // Superadmin ziet ALLES (geen filter)
                \Log::info('✅ Superadmin - showing ALL content');
            } else {
                // Fallback: alleen globale content
                $query->whereNull('organisatie_id');
                
                \Log::info('⚠️ Unknown role - showing only global content', [
                    'user_role' => $user->role
                ]);
            }
            
            $content = $query->orderBy('is_pinned', 'desc')
                ->orderBy('sort_order', 'asc')
                ->orderBy('created_at', 'desc')
                ->get();
                
            \Log::info('✅ Content found', [
                'count' => $content->count(),
                'titles' => $content->pluck('title')->toArray(),
                'content_details' => $content->map(function($item) {
                    return [
                        'id' => $item->id,
                        'title' => $item->title,
                        'organisatie_id' => $item->organisatie_id ?? 'NULL',
                        'visibility' => $item->visibility ?? 'unknown'
                    ];
                })->toArray()
            ]);
        } else {
            // Fallback voor oude structuur
            $content = StaffNote::with('user')
                ->visibleFor($user->role, $user->organisatie_id)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        $canManage = in_array($user->role, ['superadmin', 'admin', 'medewerker', 'organisatie_admin']);

        return view('dashboard-content.index', compact('content', 'canManage', 'hasNewFields'));
    }

    public function create()
    {
        // Check if user is staff
        $user = Auth::user();
        if (!in_array($user->role, ['superadmin', 'admin', 'organisatie_admin', 'medewerker'])) {
            abort(403, 'Geen toegang');
        }
        
        $templates = $this->getTemplates();
        
        return view('dashboard-content.create', compact('templates'));
    }

    public function store(Request $request)
    {
        // Check if user is staff
        $user = Auth::user();
        if (!in_array($user->role, ['superadmin', 'admin', 'organisatie_admin', 'medewerker'])) {
            abort(403, 'Geen toegang');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:note,task,announcement,image,mixed',
            'tile_size' => 'required|in:mini,small,medium,large,banner',
            'visibility' => 'required|in:staff,all',
            'priority' => 'required|in:low,medium,high,urgent',
            'background_color' => 'required|string|max:7',
            'text_color' => 'required|string|max:7',
            'expires_at' => 'nullable|date|after:now',
            'published_at' => 'nullable|date',
            'is_pinned' => 'boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'link_url' => 'nullable|url|max:500',
            'open_in_new_tab' => 'boolean'
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('dashboard-content', 'public');
            $validated['image_path'] = $imagePath;
        }

        // Set defaults
        $validated['user_id'] = Auth::id();
        $validated['organisatie_id'] = Auth::user()->organisatie_id;
        $validated['sort_order'] = StaffNote::max('sort_order') + 1;
        $validated['published_at'] = $validated['published_at'] ?? now();

        $content = StaffNote::create($validated);

        return redirect()->route('dashboard-content.index')
            ->with('success', 'Content succesvol aangemaakt!');
    }

    public function edit(StaffNote $dashboardContent)
    {
        // Check if user is staff
        $user = Auth::user();
        if (!in_array($user->role, ['superadmin', 'admin', 'medewerker', 'organisatie_admin'])) {
            abort(403, 'Geen toegang');
        }
        
        // Check organisatie toegang (behalve voor superadmin)
        if ($user->role !== 'superadmin' && $dashboardContent->organisatie_id !== $user->organisatie_id) {
            abort(403, 'Geen toegang tot content van andere organisatie');
        }
        
        $templates = $this->getTemplates();
        
        return view('dashboard-content.edit', compact('dashboardContent', 'templates'));
    }

    public function update(Request $request, StaffNote $dashboardContent)
    {
        // Check if user is staff
        $user = Auth::user();
        if (!in_array($user->role, ['superadmin', 'admin', 'medewerker', 'organisatie_admin'])) {
            abort(403, 'Geen toegang');
        }
        
        // Check organisatie toegang (behalve voor superadmin)
        if ($user->role !== 'superadmin' && $dashboardContent->organisatie_id !== $user->organisatie_id) {
            abort(403, 'Geen toegang tot content van andere organisatie');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:note,task,announcement,image,mixed',
            'tile_size' => 'required|in:mini,small,medium,large,banner',
            'visibility' => 'required|in:staff,all',
            'priority' => 'required|in:low,medium,high,urgent',
            'background_color' => 'required|string|max:7',
            'text_color' => 'required|string|max:7',
            'expires_at' => 'nullable|date',
            'published_at' => 'nullable|date',
            'is_pinned' => 'boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'link_url' => 'nullable|url|max:500',
            'open_in_new_tab' => 'boolean'
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($dashboardContent->image_path) {
                Storage::disk('public')->delete($dashboardContent->image_path);
            }
            
            $imagePath = $request->file('image')->store('dashboard-content', 'public');
            $validated['image_path'] = $imagePath;
        }

        $dashboardContent->update($validated);

        return redirect()->route('dashboard-content.index')
            ->with('success', 'Content succesvol bijgewerkt!');
    }

    public function destroy(StaffNote $dashboardContent)
    {
        // Check if user is staff
        $user = Auth::user();
        if (!in_array($user->role, ['superadmin', 'admin', 'medewerker', 'organisatie_admin'])) {
            abort(403, 'Geen toegang');
        }
        
        // Check organisatie toegang (behalve voor superadmin)
        if ($user->role !== 'superadmin' && $dashboardContent->organisatie_id !== $user->organisatie_id) {
            abort(403, 'Geen toegang tot content van andere organisatie');
        }

        // Delete image if exists
        if ($dashboardContent->image_path) {
            Storage::disk('public')->delete($dashboardContent->image_path);
        }

        $dashboardContent->delete();

        return redirect()->route('dashboard-content.index')
            ->with('success', 'Content verwijderd!');
    }

    public function archive(StaffNote $dashboardContent)
    {
        // Check if user is staff
        $user = Auth::user();
        if (!in_array($user->role, ['superadmin', 'admin', 'organisatie_admin', 'medewerker'])) {
            abort(403, 'Geen toegang');
        }

        $dashboardContent->update(['is_archived' => true]);

        return back()->with('success', 'Content gearchiveerd!');
    }

    public function updateOrder(Request $request)
    {
        // Check if user is staff
        $user = Auth::user();
        if (!in_array($user->role, ['superadmin', 'admin', 'organisatie_admin', 'medewerker'])) {
            abort(403, 'Geen toegang');
        }

        $items = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:staff_notes,id',
            'items.*.sort_order' => 'required|integer'
        ]);

        foreach ($items['items'] as $item) {
            StaffNote::where('id', $item['id'])
                ->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json(['success' => true]);
    }

    public function archived()
    {
        $user = Auth::user();
        if (!in_array($user->role, ['superadmin', 'admin', 'medewerker', 'organisatie_admin'])) {
            abort(403, 'Geen toegang');
        }

        $query = StaffNote::with('user')->where('is_archived', true);
        
        // Filter op organisatie (behalve voor superadmin)
        if ($user->role !== 'superadmin') {
            $query->where('organisatie_id', $user->organisatie_id);
        }
        
        $archivedContent = $query->orderBy('updated_at', 'desc')->get();

        return view('dashboard-content.archived', compact('archivedContent'));
    }

    public function restore(StaffNote $dashboardContent)
    {
        // Check if user is staff
        $user = Auth::user();
        if (!in_array($user->role, ['superadmin', 'admin', 'organisatie_admin', 'medewerker'])) {
            abort(403, 'Geen toegang');
        }

        $dashboardContent->update(['is_archived' => false]);

        return back()->with('success', 'Content hersteld!');
    }

    private function getTemplates()
    {
        return [
            'announcement' => [
                'title' => 'Belangrijke Mededeling',
                'type' => 'announcement',
                'tile_size' => 'banner',
                'background_color' => '#fef2f2',
                'text_color' => '#991b1b',
                'priority' => 'high'
            ],
            'task' => [
                'title' => 'Takenlijst',
                'type' => 'task', 
                'tile_size' => 'medium',
                'background_color' => '#f0f9ff',
                'text_color' => '#0c4a6e',
                'priority' => 'medium'
            ],
            'promotion' => [
                'title' => 'Promotie',
                'type' => 'image',
                'tile_size' => 'large',
                'background_color' => '#ffffff',
                'text_color' => '#111827',
                'priority' => 'medium'
            ]
        ];
    }
}