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
        
        // Check if nieuwe velden bestaan (na migratie)
        $hasNewFields = \Schema::hasColumn('staff_notes', 'type');
        
        if ($hasNewFields) {
            $content = StaffNote::with('user')
                ->visibleFor($user->role)
                ->where('is_archived', false)
                ->where(function($q) {
                    $q->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
                })
                ->where(function($q) {
                    $q->whereNull('published_at')
                      ->orWhere('published_at', '<=', now());
                })
                ->orderBy('is_pinned', 'desc')
                ->orderBy('sort_order', 'asc')
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            // Fallback voor oude structuur
            $content = StaffNote::with('user')
                ->visibleFor($user->role)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        $canManage = in_array($user->role, ['admin', 'medewerker']);

        return view('dashboard-content.index', compact('content', 'canManage', 'hasNewFields'));
    }

    public function create()
    {
        $templates = $this->getTemplates();
        
        return view('dashboard-content.create', compact('templates'));
    }

    public function store(Request $request)
    {
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
        $validated['sort_order'] = StaffNote::max('sort_order') + 1;
        $validated['published_at'] = $validated['published_at'] ?? now();

        $content = StaffNote::create($validated);

        return redirect()->route('dashboard-content.index')
            ->with('success', 'Content succesvol aangemaakt!');
    }

    public function edit(StaffNote $dashboardContent)
    {
        $templates = $this->getTemplates();
        
        return view('dashboard-content.edit', compact('dashboardContent', 'templates'));
    }

    public function update(Request $request, StaffNote $dashboardContent)
    {
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
        $dashboardContent->update(['is_archived' => true]);

        return back()->with('success', 'Content gearchiveerd!');
    }

    public function updateOrder(Request $request)
    {
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
        $archivedContent = StaffNote::with('user')
            ->where('is_archived', true)
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('dashboard-content.archived', compact('archivedContent'));
    }

    public function restore(StaffNote $dashboardContent)
    {
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