<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    /**
     * Display a listing of all users (both medewerkers and klanten)
     */
    public function index(Request $request)
    {
        // Log debug info om te zien wat er gebeurt
        \Log::info('🔍 AdminUserController index called', [
            'request_params' => $request->all(),
            'user_role' => auth()->user()->role ?? 'not_authenticated'
        ]);

        $query = User::query();

        // Filter op rol als opgegeven
        if ($request->filled('role') && $request->role !== 'all') {
            $query->where('role', $request->role);
        }

        // Filter op status als opgegeven
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Search functionaliteit
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('email', 'like', "%{$searchTerm}%")
                  ->orWhere('voornaam', 'like', "%{$searchTerm}%")
                  ->orWhere('achternaam', 'like', "%{$searchTerm}%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')->get();

        // Log de resultaten voor debugging
        \Log::info('👥 Users query results', [
            'total_users' => $users->count(),
            'admins' => $users->where('role', 'admin')->count(),
            'medewerkers' => $users->where('role', 'medewerker')->count(),
            'klanten' => $users->where('role', 'klant')->count(),
            'user_emails' => $users->pluck('email', 'role')->toArray()
        ]);

        // Separate counts voor de UI
        $medewerkersCount = $users->whereIn('role', ['admin', 'medewerker'])->count();
        $klantenCount = $users->where('role', 'klant')->count();

        return view('admin.users.index', compact('users', 'medewerkersCount', 'klantenCount'));
    }

    /**
     * Show specific user details
     */
    public function show(User $user)
    {
        return view('admin.users.show', compact('user'));
    }

    /**
     * Update user status or basic info
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'status' => 'sometimes|in:active,inactive,suspended',
            'admin_notes' => 'sometimes|string|nullable'
        ]);

        $user->update($validated);

        return redirect()->back()->with('success', 'Gebruiker succesvol bijgewerkt.');
    }

    /**
     * Delete a user
     */
    public function destroy(User $user)
    {
        // Prevent deletion of current user
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'Je kunt jezelf niet verwijderen.');
        }

        $email = $user->email;
        $user->delete();

        \Log::info('🗑️ User deleted by admin', [
            'deleted_user_email' => $email,
            'deleted_by' => auth()->user()->email
        ]);

        return redirect()->route('admin.users')->with('success', 'Gebruiker succesvol verwijderd.');
    }
}