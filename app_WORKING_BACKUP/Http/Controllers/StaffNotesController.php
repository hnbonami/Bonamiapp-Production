<?php

namespace App\Http\Controllers;

use App\Models\StaffNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StaffNotesController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Filter notities op basis van gebruikersrol
        $notes = StaffNote::with('user')
            ->visibleFor($user->role)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('staff-notes.index', compact('notes'));
    }

    public function create()
    {
        // Alleen staff mag notities aanmaken
        $user = Auth::user();
        if (!in_array($user->role, ['admin', 'medewerker'])) {
            abort(403, 'Geen toegang');
        }

        return view('staff-notes.create');
    }

    public function store(Request $request)
    {
        // Alleen staff mag notities aanmaken
        $user = Auth::user();
        if (!in_array($user->role, ['admin', 'medewerker'])) {
            abort(403, 'Geen toegang');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'visibility' => 'required|in:staff,all'
        ]);

        $validated['user_id'] = Auth::id();

        StaffNote::create($validated);

        return redirect()->route('staff-notes.index')
            ->with('success', 'Notitie succesvol toegevoegd!');
    }

    public function edit(StaffNote $staffNote)
    {
        // Alleen staff mag notities bewerken
        $user = Auth::user();
        if (!in_array($user->role, ['admin', 'medewerker'])) {
            abort(403, 'Geen toegang');
        }

        return view('staff-notes.edit', compact('staffNote'));
    }

    public function update(Request $request, StaffNote $staffNote)
    {
        // Alleen staff mag notities bewerken
        $user = Auth::user();
        if (!in_array($user->role, ['admin', 'medewerker'])) {
            abort(403, 'Geen toegang');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'visibility' => 'required|in:staff,all'
        ]);

        $staffNote->update($validated);

        return redirect()->route('staff-notes.index')
            ->with('success', 'Notitie succesvol bijgewerkt!');
    }

    public function destroy(StaffNote $staffNote)
    {
        // Alleen staff mag notities verwijderen
        $user = Auth::user();
        if (!in_array($user->role, ['admin', 'medewerker'])) {
            abort(403, 'Geen toegang');
        }

        $staffNote->delete();

        return redirect()->route('staff-notes.index')
            ->with('success', 'Notitie verwijderd!');
    }
}