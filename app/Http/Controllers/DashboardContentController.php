<?php

namespace App\Http\Controllers;

use App\Models\DashboardContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class DashboardContentController extends Controller
{
    /**
     * Haal dashboard content op voor de homepage
     */
    public function index()
    {
        // Haal alleen actieve, niet-gearchiveerde content op voor de huidige organisatie
        $user = auth()->user();
        $organisatieId = $user->organisatie_id ?? optional($user->medewerker)->organisatie_id ?? null;

        // Check of de tabel bestaat
        if (!Schema::hasTable('dashboard_contents')) {
            // Fallback: probeer oude staff_notes tabel
            if (Schema::hasTable('staff_notes')) {
                \Log::info('⚠️ dashboard_contents tabel niet gevonden, gebruik staff_notes als fallback');
                $content = \DB::table('staff_notes')
                    ->where('is_actief', true)
                    ->where('is_archived', false)
                    ->orderBy('volgorde')
                    ->get();
            } else {
                $content = collect();
            }
        } else {
            $content = DashboardContent::where('is_actief', true)
                ->where('is_archived', false)
                ->when($organisatieId, function($query, $organisatieId) {
                    return $query->where('organisatie_id', $organisatieId);
                })
                ->orderBy('volgorde')
                ->get();
        }

        // Check of user admin rechten heeft (niet klant)
        $canManage = $this->canManageContent();

        // Check of nieuwe velden bestaan in de tabel (voor backward compatibility)
        $hasNewFields = Schema::hasColumn('dashboard_contents', 'type_icon');

        return view('dashboard-content.index', compact('content', 'canManage', 'hasNewFields'));
    }

    /**
     * Toon formulier voor nieuwe content
     */
    public function create()
    {
        // Alleen admins, medewerkers en eigenaren mogen content aanmaken
        if (!$this->canManageContent()) {
            abort(403, 'Je hebt geen rechten om dashboard content aan te maken.');
        }

        return view('dashboard-content.create');
    }

    /**
     * Sla nieuwe content op
     */
    public function store(Request $request)
    {
        // Alleen admins, medewerkers en eigenaren mogen content aanmaken
        if (!$this->canManageContent()) {
            abort(403, 'Je hebt geen rechten om dashboard content aan te maken.');
        }

        $validated = $request->validate([
            'titel' => 'required|string|max:255',
            'inhoud' => 'required|string',
            'type' => 'required|in:info,waarschuwing,succes,belangrijk',
            'kleur' => 'nullable|string|max:50',
            'icoon' => 'nullable|string|max:50',
            'link_url' => 'nullable|url|max:500',
            'link_tekst' => 'nullable|string|max:100',
            'is_actief' => 'boolean',
        ]);

        // Voeg organisatie_id en user_id toe
        $user = auth()->user();
        $validated['organisatie_id'] = $user->organisatie_id ?? optional($user->medewerker)->organisatie_id ?? null;
        $validated['user_id'] = $user->id;
        $validated['volgorde'] = DashboardContent::where('organisatie_id', $validated['organisatie_id'])->max('volgorde') + 1;

        DashboardContent::create($validated);

        return redirect()->route('dashboard')->with('success', 'Dashboard content succesvol aangemaakt!');
    }

    /**
     * Toon gearchiveerde content
     */
    public function archived()
    {
        // Alleen admins, medewerkers en eigenaren mogen archief zien
        if (!$this->canManageContent()) {
            abort(403, 'Je hebt geen rechten om het archief te bekijken.');
        }

        $user = auth()->user();
        $organisatieId = $user->organisatie_id ?? optional($user->medewerker)->organisatie_id ?? null;

        $archivedContent = DashboardContent::where('is_archived', true)
            ->when($organisatieId, function($query, $organisatieId) {
                return $query->where('organisatie_id', $organisatieId);
            })
            ->orderBy('archived_at', 'desc')
            ->get();
        
        return view('dashboard-content.archived', compact('archivedContent'));
    }

    /**
     * Helper method: Check of user content mag beheren
     */
    private function canManageContent(): bool
    {
        $user = auth()->user();
        
        // Als user geen speciale role heeft, geef true (default allow)
        // Dit voorkomt dat normale users geblokkeerd worden
        if (!isset($user->role)) {
            return true;
        }
        
        // Klanten mogen GEEN content beheren (expliciet geblokkeerd)
        if ($user->role === 'klant') {
            return false;
        }

        // Alle andere users (admin, medewerker, eigenaar) mogen WEL
        return true;
    }

    /**
     * Toon specifieke content
     */
    public function show(DashboardContent $dashboardContent)
    {
        return view('dashboard-content.show', compact('dashboardContent'));
    }

    /**
     * Toon edit formulier
     */
    public function edit(DashboardContent $dashboardContent)
    {
        if (!$this->canManageContent()) {
            abort(403, 'Je hebt geen rechten om dashboard content te bewerken.');
        }

        return view('dashboard-content.edit', compact('dashboardContent'));
    }

    /**
     * Update content
     */
    public function update(Request $request, DashboardContent $dashboardContent)
    {
        if (!$this->canManageContent()) {
            abort(403, 'Je hebt geen rechten om dashboard content te bewerken.');
        }

        $validated = $request->validate([
            'titel' => 'required|string|max:255',
            'inhoud' => 'required|string',
            'type' => 'required|in:info,waarschuwing,succes,belangrijk',
            'kleur' => 'nullable|string|max:50',
            'icoon' => 'nullable|string|max:50',
            'link_url' => 'nullable|url|max:500',
            'link_tekst' => 'nullable|string|max:100',
            'is_actief' => 'boolean',
        ]);

        $dashboardContent->update($validated);

        return redirect()->route('dashboard')->with('success', 'Dashboard content succesvol bijgewerkt!');
    }

    /**
     * Verwijder content
     */
    public function destroy(DashboardContent $dashboardContent)
    {
        if (!$this->canManageContent()) {
            abort(403, 'Je hebt geen rechten om dashboard content te verwijderen.');
        }

        $dashboardContent->delete();

        return redirect()->route('dashboard')->with('success', 'Dashboard content succesvol verwijderd!');
    }

    /**
     * Archiveer content
     */
    public function archive(DashboardContent $dashboardContent)
    {
        if (!$this->canManageContent()) {
            abort(403, 'Je hebt geen rechten om dashboard content te archiveren.');
        }

        $dashboardContent->update([
            'is_archived' => true,
            'archived_at' => now(),
        ]);

        return redirect()->route('dashboard')->with('success', 'Dashboard content gearchiveerd!');
    }

    /**
     * Herstel gearchiveerde content
     */
    public function restore(DashboardContent $dashboardContent)
    {
        if (!$this->canManageContent()) {
            abort(403, 'Je hebt geen rechten om dashboard content te herstellen.');
        }

        $dashboardContent->update([
            'is_archived' => false,
            'archived_at' => null,
        ]);

        return redirect()->route('dashboard-content.archived')->with('success', 'Dashboard content hersteld!');
    }

    /**
     * Update de volgorde van content items
     */
    public function updateOrder(Request $request)
    {
        if (!$this->canManageContent()) {
            abort(403, 'Je hebt geen rechten om de volgorde te wijzigen.');
        }

        $order = $request->input('order', []);

        foreach ($order as $index => $id) {
            DashboardContent::where('id', $id)->update(['volgorde' => $index]);
        }

        return response()->json(['success' => true]);
    }
}