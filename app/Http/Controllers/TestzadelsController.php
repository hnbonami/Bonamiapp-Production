<?php

namespace App\Http\Controllers;

use App\Models\Testzadel;
use App\Models\Klant;
use App\Models\Bikefit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class TestzadelsController extends Controller
{
    public function index()
    {
        // Eager load klant relatie om null pointer exceptions te voorkomen
        $testzadels = Testzadel::with('klant')
            ->where('status', '!=', 'gearchiveerd')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Stats voor de view (alle keys die frontend verwacht)
        $stats = [
            'total' => $testzadels->count(),
            'uitgeleend' => $testzadels->where('status', 'uitgeleend')->count(),
            'teruggegeven' => $testzadels->where('status', 'teruggegeven')->count(),
            'te_laat' => $testzadels->where('status', 'uitgeleend')
                ->where('verwachte_terugbring_datum', '<', now()->toDateString())->count(),
            'herinnering_nodig' => $testzadels->where('status', 'uitgeleend')
                ->where('verwachte_terugbring_datum', '<=', now()->addDays(3)->toDateString())->count(),
            'verwacht_vandaag' => $testzadels->where('status', 'uitgeleend')
                ->filter(function($testzadel) {
                    return $testzadel->verwachte_terugbring_datum && 
                           \Carbon\Carbon::parse($testzadel->verwachte_terugbring_datum)->isToday();
                })->count()
        ];
        
        return view('testzadels.index', compact('testzadels', 'stats'));
    }

    public function create()
    {
        $klanten = Klant::orderBy('naam')->get();
        $bikefits = Bikefit::orderBy('datum', 'desc')->get();
        
        return view('testzadels.create', compact('klanten', 'bikefits'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'klant_id' => 'required|exists:klanten,id',
            'bikefit_id' => 'nullable|exists:bikefits,id',
            'merk' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'type' => 'nullable|string|max:255',
            'breedte' => 'required|integer',
            'uitgeleend_op' => 'required|date',
            'verwachte_terugbring_datum' => 'required|date',
            'beschrijving' => 'nullable|string',
            'opmerkingen' => 'nullable|string',
        ]);

        $validatedData['status'] = 'uitgeleend';

        Testzadel::create($validatedData);

        return redirect()->route('testzadels.index')
            ->with('success', 'Testzadel succesvol toegevoegd.');
    }

    public function show(Testzadel $testzadel)
    {
        $testzadel->load('klant', 'bikefit');
        return view('testzadels.show', compact('testzadel'));
    }

    public function edit(Testzadel $testzadel)
    {
        $klanten = Klant::orderBy('naam')->get();
        $bikefits = Bikefit::orderBy('datum', 'desc')->get();
        
        return view('testzadels.edit', compact('testzadel', 'klanten', 'bikefits'));
    }

    /**
     * Update testzadel met correcte validation en automatische datum logica
     */
    public function update(Request $request, Testzadel $testzadel)
    {
        \Log::info('🔧 TestzadelsController@update CALLED', [
            'testzadel_id' => $testzadel->id,
            'request_data' => $request->all(),
            'current_onderdeel_type' => $testzadel->onderdeel_type,
            'current_status' => $testzadel->status
        ]);

        $validated = $request->validate([
            'klant_id' => 'nullable|exists:klanten,id',
            'bikefit_id' => 'nullable|exists:bikefits,id', 
            'onderdeel_type' => 'required|string|in:testzadel,zooltjes,cleats,stuurpen',
            'status' => 'required|in:' . implode(',', array_keys(Testzadel::getStatussen())),
            'zadel_merk' => 'nullable|string|max:255',
            'zadel_model' => 'nullable|string|max:255',
            'zadel_type' => 'nullable|string|max:255', 
            'zadel_breedte' => 'nullable|integer|min:50|max:400',
            'automatisch_mailtje' => 'boolean',
            'uitleen_datum' => 'required|date',
            'verwachte_retour_datum' => 'required|date',
            'opmerkingen' => 'nullable|string',
        ]);

        // Zet werkelijke_retour_datum automatisch bij status wijziging naar teruggegeven
        if ($request->status === 'teruggegeven' && $testzadel->status !== 'teruggegeven') {
            $validated['werkelijke_retour_datum'] = now();
            \Log::info('✅ Werkelijke retour datum automatisch gezet', [
                'testzadel_id' => $testzadel->id,
                'werkelijke_retour_datum' => now()->format('Y-m-d H:i:s')
            ]);
        }

        \Log::info('🔄 Updating testzadel met validated data', [
            'testzadel_id' => $testzadel->id,
            'validated_onderdeel_type' => $validated['onderdeel_type'],
            'validated_status' => $validated['status']
        ]);

        $testzadel->update($validated);

        \Log::info('✅ Testzadel updated successfully', [
            'testzadel_id' => $testzadel->id,
            'new_onderdeel_type' => $testzadel->fresh()->onderdeel_type,
            'new_status' => $testzadel->fresh()->status
        ]);

        return redirect()->route('testzadels.index')
                        ->with('success', 'Testzadel succesvol bijgewerkt.');
    }

    public function destroy(Testzadel $testzadel)
    {
        $testzadel->delete();

        return redirect()->route('testzadels.index')
            ->with('success', 'Testzadel succesvol verwijderd.');
    }

    public function archive(Testzadel $testzadel)
    {
        $testzadel->update([
            'status' => 'gearchiveerd',
            'gearchiveerd' => true,
            'gearchiveerd_op' => now()
        ]);

        return redirect()->route('testzadels.index')
            ->with('success', 'Testzadel gearchiveerd.');
    }

    public function archived()
    {
        $testzadels = Testzadel::with('klant')
            ->where('status', 'gearchiveerd')
            ->orderBy('gearchiveerd_op', 'desc')
            ->get();
        
        return view('testzadels.archived', compact('testzadels'));
    }

    public function markAsReturned(Testzadel $testzadel)
    {
        $testzadel->update([
            'status' => 'teruggegeven',
            'werkelijke_terugbring_datum' => now()
        ]);

        return redirect()->route('testzadels.index')
            ->with('success', 'Testzadel gemarkeerd als teruggegeven.');
    }

    public function sendReminder(Testzadel $testzadel)
    {
        $testzadel->load('klant');
        
        if (!$testzadel->klant || !$testzadel->klant->email) {
            return redirect()->back()
                ->with('error', 'Kan geen herinnering verzenden: klant heeft geen email.');
        }

        try {
            Mail::send('emails.testzadel-reminder', [
                'testzadel' => $testzadel,
                'klant' => $testzadel->klant
            ], function($message) use ($testzadel) {
                $message->to($testzadel->klant->email, $testzadel->klant->voornaam . ' ' . $testzadel->klant->naam);
                $message->subject('Herinnering Testzadel - Bonami Sportcoaching');
            });

            $testzadel->update(['laatste_herinnering' => now()]);

            return redirect()->back()
                ->with('success', 'Herinnering verzonden naar ' . $testzadel->klant->email);
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Fout bij verzenden herinnering: ' . $e->getMessage());
        }
    }

    public function sendBulkReminders(Request $request)
    {
        $testzadelIds = $request->input('testzadel_ids', []);
        $sent = 0;
        $errors = [];

        foreach ($testzadelIds as $id) {
            $testzadel = Testzadel::with('klant')->find($id);
            
            if (!$testzadel || !$testzadel->klant || !$testzadel->klant->email) {
                $errors[] = "Testzadel ID $id: geen geldig email adres";
                continue;
            }

            try {
                Mail::send('emails.testzadel-reminder', [
                    'testzadel' => $testzadel,
                    'klant' => $testzadel->klant
                ], function($message) use ($testzadel) {
                    $message->to($testzadel->klant->email, $testzadel->klant->voornaam . ' ' . $testzadel->klant->naam);
                    $message->subject('Herinnering Testzadel - Bonami Sportcoaching');
                });

                $testzadel->update(['laatste_herinnering' => now()]);
                $sent++;
                
            } catch (\Exception $e) {
                $errors[] = "Testzadel ID $id: " . $e->getMessage();
            }
        }

        $message = "$sent herinneringen verzonden.";
        if (!empty($errors)) {
            $message .= " Fouten: " . implode(', ', $errors);
        }

        return redirect()->back()->with('success', $message);
    }
}