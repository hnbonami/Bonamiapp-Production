<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TrainingsZonesTemplate;
use Illuminate\Http\Request;

class InspanningstestenInstellingenController extends Controller
{
    /**
     * Toon overzicht van alle zone templates
     */
    public function index()
    {
        $organisatie = auth()->user()->organisatie;
        
        // Haal alle templates op voor deze organisatie + systeem templates
        $templates = TrainingsZonesTemplate::with('zones')
            ->where(function($query) use ($organisatie) {
                $query->where('organisatie_id', $organisatie->id)
                      ->orWhere('is_systeem', true);
            })
            ->where('is_actief', true)
            ->orderBy('is_systeem', 'desc') // Systeem templates eerst
            ->orderBy('naam')
            ->get();
        
        return view('admin.inspanningstesten.index', compact('templates'));
    }
    
    /**
     * Toon formulier voor nieuwe template
     */
    public function create()
    {
        return view('admin.inspanningstesten.create');
    }
    
    /**
     * Sla nieuwe template op
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'naam' => 'required|string|max:255',
            'sport_type' => 'required|in:fietsen,lopen,beide',
            'berekening_basis' => 'required|in:lt1,lt2,max,ftp,custom',
            'beschrijving' => 'nullable|string',
            'zones' => 'required|array|min:1',
            'zones.*.zone_naam' => 'required|string',
            'zones.*.kleur' => 'required|string',
            'zones.*.min_percentage' => 'required|integer|min:0|max:200',
            'zones.*.max_percentage' => 'required|integer|min:0|max:200',
            'zones.*.referentie_waarde' => 'nullable|string',
            'zones.*.beschrijving' => 'nullable|string',
        ]);
        
        $organisatie = auth()->user()->organisatie;
        
        $template = TrainingsZonesTemplate::create([
            'organisatie_id' => $organisatie->id,
            'naam' => $validated['naam'],
            'sport_type' => $validated['sport_type'],
            'berekening_basis' => $validated['berekening_basis'],
            'beschrijving' => $validated['beschrijving'] ?? null,
        ]);
        
        // Voeg zones toe
        foreach ($validated['zones'] as $index => $zoneData) {
            $template->zones()->create([
                'zone_naam' => $zoneData['zone_naam'],
                'kleur' => $zoneData['kleur'],
                'min_percentage' => $zoneData['min_percentage'],
                'max_percentage' => $zoneData['max_percentage'],
                'referentie_waarde' => $zoneData['referentie_waarde'] ?? null,
                'volgorde' => $index,
                'beschrijving' => $zoneData['beschrijving'] ?? null,
            ]);
        }
        
        return redirect()
            ->route('admin.inspanningstesten.instellingen')
            ->with('success', 'Zone template succesvol aangemaakt!');
    }
    
    /**
     * Bewerk bestaande template
     */
    public function edit($id)
    {
        $template = TrainingsZonesTemplate::with('zones')
            ->where('organisatie_id', auth()->user()->organisatie_id)
            ->findOrFail($id);
        
        // Systeem templates kunnen niet bewerkt worden
        if ($template->is_systeem) {
            return redirect()
                ->route('admin.inspanningstesten.instellingen')
                ->with('error', 'Systeem templates kunnen niet bewerkt worden.');
        }
        
        return view('admin.inspanningstesten.edit', compact('template'));
    }
    
    /**
     * Update template
     */
    public function update(Request $request, $id)
    {
        $template = TrainingsZonesTemplate::where('organisatie_id', auth()->user()->organisatie_id)
            ->findOrFail($id);
        
        if ($template->is_systeem) {
            return redirect()
                ->route('admin.inspanningstesten.instellingen')
                ->with('error', 'Systeem templates kunnen niet bewerkt worden.');
        }
        
        $validated = $request->validate([
            'naam' => 'required|string|max:255',
            'sport_type' => 'required|in:fietsen,lopen,beide',
            'berekening_basis' => 'required|in:lt1,lt2,max,ftp,custom',
            'beschrijving' => 'nullable|string',
            'zones' => 'required|array|min:1',
            'zones.*.zone_naam' => 'required|string',
            'zones.*.kleur' => 'required|string',
            'zones.*.min_percentage' => 'required|integer|min:0|max:200',
            'zones.*.max_percentage' => 'required|integer|min:0|max:200',
            'zones.*.referentie_waarde' => 'nullable|string',
            'zones.*.beschrijving' => 'nullable|string',
        ]);
        
        $template->update([
            'naam' => $validated['naam'],
            'sport_type' => $validated['sport_type'],
            'berekening_basis' => $validated['berekening_basis'],
            'beschrijving' => $validated['beschrijving'] ?? null,
        ]);
        
        // Verwijder oude zones en maak nieuwe
        $template->zones()->delete();
        
        foreach ($validated['zones'] as $index => $zoneData) {
            $template->zones()->create([
                'zone_naam' => $zoneData['zone_naam'],
                'kleur' => $zoneData['kleur'],
                'min_percentage' => $zoneData['min_percentage'],
                'max_percentage' => $zoneData['max_percentage'],
                'referentie_waarde' => $zoneData['referentie_waarde'] ?? null,
                'volgorde' => $index,
                'beschrijving' => $zoneData['beschrijving'] ?? null,
            ]);
        }
        
        return redirect()
            ->route('admin.inspanningstesten.instellingen')
            ->with('success', 'Zone template succesvol bijgewerkt!');
    }
    
    /**
     * Verwijder template
     */
    public function destroy($id)
    {
        $template = TrainingsZonesTemplate::where('organisatie_id', auth()->user()->organisatie_id)
            ->findOrFail($id);
        
        if ($template->is_systeem) {
            return redirect()
                ->route('admin.inspanningstesten.instellingen')
                ->with('error', 'Systeem templates kunnen niet verwijderd worden.');
        }
        
        $template->delete();
        
        return redirect()
            ->route('admin.inspanningstesten.instellingen')
            ->with('success', 'Zone template succesvol verwijderd!');
    }
}
