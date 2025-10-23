<?php

namespace App\Http\Controllers;

use App\Models\DashboardContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class DashboardContentController extends Controller
{
    /**
     * Haal dashboard content op voor de homepage
     */
    public function index()
    {
        \Log::info('📊 Dashboard laden - TEGELS VIEW');
        
        // Haal alle actieve (niet gearchiveerde) dashboard content op
        $content = DashboardContent::where('is_archived', false)
            ->where('is_actief', true)
            ->orderBy('volgorde')
            ->orderBy('created_at', 'desc')
            ->get();

        \Log::info('✅ Dashboard content geladen', [
            'count' => $content->count(),
            'items' => $content->pluck('titel')->toArray()
        ]);

        // Check of gebruiker content kan beheren
        $canManage = auth()->user()->role === 'admin' || auth()->user()->role === 'medewerker';
        
        // Check of nieuwe velden bestaan (voor backwards compatibility)
        $hasNewFields = Schema::hasColumn('dashboard_contents', 'tile_size');

        // GEBRUIK dashboard-content.index view met TEGELS!
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
     * Store nieuwe dashboard content
     */
    public function store(Request $request)
    {
        // Log alle input voor debugging
        \Log::info('📝 Dashboard content store request', [
            'all_input' => $request->all(),
            'has_titel' => $request->has('titel'),
            'has_inhoud' => $request->has('inhoud'),
            'has_content' => $request->has('content'),
        ]);

        // Validatie - BELANGRIJK: 'inhoud' is NIET required als 'content' bestaat
        $validated = $request->validate([
            'titel' => 'required|string|max:255',
            'inhoud' => 'nullable|string', // Niet required!
            'content' => 'nullable|string', // Alternatieve veldnaam
            'type' => 'nullable|string',
            'kleur' => 'nullable|string',
            'icoon' => 'nullable|string',
            'link_url' => 'nullable|url',
            'link_tekst' => 'nullable|string',
            'is_actief' => 'nullable|boolean',
        ]);

        // Map 'content' naar 'inhoud' als het bestaat
        if (isset($validated['content']) && !isset($validated['inhoud'])) {
            $validated['inhoud'] = $validated['content'];
            unset($validated['content']);
        }

        // Als BEIDE niet bestaan, zet lege string
        if (!isset($validated['inhoud'])) {
            $validated['inhoud'] = '';
        }

        // Zet defaults
        $validated['user_id'] = auth()->id();
        $validated['organisatie_id'] = auth()->user()->organisatie_id ?? null;
        $validated['is_actief'] = $request->has('is_actief') ? true : false;
        $validated['is_archived'] = false;
        
        // Bepaal volgorde (laatste positie)
        $maxOrder = DashboardContent::max('volgorde') ?? 0;
        $validated['volgorde'] = $maxOrder + 1;

        try {
            $content = DashboardContent::create($validated);

            \Log::info('✅ Dashboard content aangemaakt', ['id' => $content->id]);

            return redirect()->route('dashboard')
                ->with('success', 'Dashboard content succesvol aangemaakt!');
        } catch (\Exception $e) {
            \Log::error('❌ Dashboard content aanmaken mislukt', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'validated' => $validated
            ]);

            return back()->withInput()
                ->withErrors(['error' => 'Er ging iets mis bij het opslaan: ' . $e->getMessage()]);
        }
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