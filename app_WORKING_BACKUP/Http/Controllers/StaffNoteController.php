<?php

namespace App\Http\Controllers;

use App\Models\StaffNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StaffNoteController extends Controller
{
    public function index()
    {
        $notes = StaffNote::latest()->paginate(10);
        return view('staff-notes.index', compact('notes'));
    }

    public function create()
    {
        $user = Auth::user();
        if (!in_array($user->role, ['admin', 'medewerker'])) {
            abort(403, 'Geen toegang');
        }
        return view('staff-notes.create');
    }

    public function store(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        
        $user = Auth::user();
        if (!in_array($user->role, ['admin', 'medewerker'])) {
            abort(403, 'Geen toegang');
        }

        try {
            $validated = $request->validate([
                'content' => 'required|string',
                'type' => 'required|in:note,task',
                'visibility' => 'required|in:public,staff',
                'priority' => 'nullable|in:low,normal,high,urgent',
                'due_date' => 'nullable|date|after:today',
                'status' => 'nullable|in:open,in_progress,completed'
            ]);

            $validated['user_id'] = Auth::id();
            StaffNote::create($validated);
            
            return redirect()->route('staffnotes.index')->with('success', 'Notitie succesvol aangemaakt!');
            
        } catch (\Exception $e) {
            \Log::error('Staff note creation failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Er is een fout opgetreden bij het aanmaken van de notitie.']);
        }
    }

    public function show(StaffNote $staffNote)
    {
        $user = Auth::user();
        if ($staffNote->visibility == 'staff' && !in_array($user->role, ['admin', 'medewerker'])) {
            abort(403, 'Geen toegang tot deze notitie');
        }
        return view('staff-notes.show', compact('staffNote'));
    }

    public function edit(StaffNote $staffNote)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['admin', 'medewerker'])) {
            abort(403, 'Geen toegang');
        }
        return view('staff-notes.edit', compact('staffNote'));
    }

    public function update(Request $request, StaffNote $staffNote)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['admin', 'medewerker'])) {
            abort(403, 'Geen toegang');
        }

        try {
            $validated = $request->validate([
                'content' => 'required|string',
                'type' => 'required|in:note,task',
                'visibility' => 'required|in:public,staff',
                'priority' => 'nullable|in:low,normal,high,urgent',
                'due_date' => 'nullable|date',
                'status' => 'nullable|in:open,in_progress,completed'
            ]);

            $staffNote->update($validated);
            return redirect()->route('staffnotes.index')->with('success', 'Notitie succesvol bijgewerkt!');
            
        } catch (\Exception $e) {
            \Log::error('Staff note update failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Er is een fout opgetreden bij het bijwerken van de notitie.']);
        }
    }

    public function destroy(StaffNote $staffNote)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['admin', 'medewerker'])) {
            abort(403, 'Geen toegang');
        }
        $staffNote->delete();
        return redirect()->route('staffnotes.index')->with('success', 'Notitie verwijderd');
    }

    /**
     * Admin overview - FORCE LOAD DATABASE TOOLS VIEW
     */
    public function adminOverview()
    {
        // Debug: show which view Laravel tries to load
        \Log::info('AdminOverview called - loading admin tools view');
        
        $notes = StaffNote::with('user')->latest()->paginate(10);
        
        // ABSOLUTELY FORCE the correct view with database tools
        $viewPath = 'admin.staff-notes.overview';
        
        \Log::info('Loading view: ' . $viewPath);
        
        // Check if the view actually exists and has database tools
        $fullPath = resource_path('views/admin/staff-notes/overview.blade.php');
        if (file_exists($fullPath)) {
            $content = file_get_contents($fullPath);
            if (strpos($content, 'Klanten Toevoegen') !== false) {
                \Log::info('SUCCESS: Database tools found in view');
                return view($viewPath, compact('notes'));
            } else {
                \Log::error('ERROR: Database tools NOT found in view');
                return response('<h1>ERROR: View exists but no database tools found!</h1><p>Path: ' . $fullPath . '</p>');
            }
        } else {
            \Log::error('ERROR: View file does not exist: ' . $fullPath);
            return response('<h1>ERROR: View file not found!</h1><p>Path: ' . $fullPath . '</p>');
        }
    }

    public function markAllNotesRead()
    {
        return response()->json(['success' => true]);
    }
}
