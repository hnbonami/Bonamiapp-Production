<?php

namespace App\Http\Controllers;

use App\Models\Testzadel;
use App\Models\Klant;
use App\Models\Bikefit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class TestzadelsController extends Controller
{
    /**
     * Display overzicht van alle testzadels
     */
    public function index()
    {
        try {
            $testzadels = Testzadel::with(['klant', 'bikefit'])
                ->where('gearchiveerd', false)
                ->orderBy('created_at', 'desc')
                ->get();
                
            // Statistieken
            $stats = [
                'uitgeleend' => Testzadel::where('gearchiveerd', false)->where('status', 'uitgeleend')->count(),
                'te_laat' => Testzadel::where('gearchiveerd', false)
                    ->where('status', 'uitgeleend')
                    ->where('verwachte_retour_datum', '<', now())
                    ->count(),
                'herinnering_nodig' => Testzadel::where('gearchiveerd', false)
                    ->where('status', 'uitgeleend')
                    ->where('verwachte_retour_datum', '<=', now()->addWeeks(1))
                    ->count(),
                'verwacht_vandaag' => Testzadel::where('gearchiveerd', false)
                    ->where('status', 'uitgeleend')
                    ->whereDate('verwachte_retour_datum', today())
                    ->count(),
            ];

            return view('testzadels.index', compact('testzadels', 'stats'));
            
        } catch (\Illuminate\Database\QueryException $e) {
            // Als de tabel niet bestaat, toon een melding
            if (str_contains($e->getMessage(), "doesn't exist") || str_contains($e->getMessage(), "Unknown column")) {
                return view('testzadels.setup-required');
            }
            throw $e;
        }
    }    /**
     * Show archived testzadels
     */
    public function archived()
    {
        $testzadels = Testzadel::with(['klant', 'bikefit'])
            ->where('gearchiveerd', true)
            ->orderBy('gearchiveerd_op', 'desc')
            ->get();

        return view('testzadels.archived', compact('testzadels'));
    }

    /**
     * Show form voor nieuwe testzadel
     */
    public function create()
    {
        $klanten = \App\Models\Klant::orderBy('naam')->get();
        $bikefits = \App\Models\Bikefit::with('klant')->orderBy('datum', 'desc')->get();
        
        return view('testzadels.create', compact('klanten', 'bikefits'));
    }

    /**
     * Store nieuwe testzadel
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'klant_id' => 'required|exists:klanten,id',
            'bikefit_id' => 'nullable|exists:bikefits,id',
            'merk' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'type' => 'nullable|string|max:50',
            'breedte' => 'required|integer|min:100|max:200',
            'uitgeleend_op' => 'required|date',
            'verwachte_terugbring_datum' => 'required|date|after:uitgeleend_op',
            'beschrijving' => 'nullable|string',
            'opmerkingen' => 'nullable|string',
            'foto_zadel' => 'nullable|image|mimes:jpeg,png,jpg|max:5120' // 5MB
        ]);

        // Map to database column names
        $data = [
            'klant_id' => $validated['klant_id'],
            'bikefit_id' => $validated['bikefit_id'],
            'zadel_merk' => $validated['merk'],
            'zadel_model' => $validated['model'],
            'zadel_type' => $validated['type'],
            'zadel_breedte' => $validated['breedte'],
            'uitleen_datum' => $validated['uitgeleend_op'],
            'verwachte_retour_datum' => $validated['verwachte_terugbring_datum'],
            'zadel_beschrijving' => $validated['beschrijving'],
            'opmerkingen' => $validated['opmerkingen'],
            'status' => 'uitgeleend',
            'gearchiveerd' => false
        ];

        // Handle foto upload
        if ($request->hasFile('foto_zadel')) {
            $foto = $request->file('foto_zadel');
            $filename = time() . '_' . $foto->getClientOriginalName();
            $path = $foto->storeAs('testzadels', $filename, 'public');
            $data['foto_path'] = $path;
        }

        Testzadel::create($data);

        return redirect()->route('testzadels.index')
            ->with('success', 'Testzadel uitlening geregistreerd!');
    }    /**
     * Show specific testzadel
     */
    public function show(Testzadel $testzadel)
    {
        $testzadel->load(['klant', 'bikefit']);
        return view('testzadels.show', compact('testzadel'));
    }

    /**
     * Show edit form
     */
    public function edit(Testzadel $testzadel)
    {
        $klanten = \App\Models\Klant::orderBy('naam')->get();
        $bikefits = \App\Models\Bikefit::with('klant')->orderBy('datum', 'desc')->get();
        
        return view('testzadels.edit', compact('testzadel', 'klanten', 'bikefits'));
    }

    /**
     * Update testzadel
     */
    public function update(Request $request, Testzadel $testzadel)
    {
        $validated = $request->validate([
            'klant_id' => 'required|exists:klanten,id',
            'bikefit_id' => 'nullable|exists:bikefits,id',
            'merk' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'breedte' => 'required|integer|min:100|max:200',
            'uitgeleend_op' => 'required|date',
            'verwachte_terugbring_datum' => 'required|date|after:uitgeleend_op',
            'opmerkingen' => 'nullable|string'
        ]);

        $testzadel->update($validated);

        return redirect()->route('testzadels.show', $testzadel)
                        ->with('success', 'Testzadel bijgewerkt!');
    }

    /**
     * Delete testzadel
     */
    public function destroy(Testzadel $testzadel)
    {
        $testzadel->delete();
        
        return redirect()->route('testzadels.index')
                        ->with('success', 'Testzadel verwijderd!');
    }

    /**
     * Archive testzadel
     */
    public function archive(Testzadel $testzadel)
    {
        $testzadel->update([
            'gearchiveerd' => true,
            'gearchiveerd_op' => now(),
            'status' => 'gearchiveerd'
        ]);

        return redirect()->route('testzadels.index')
                        ->with('success', 'Testzadel gearchiveerd!');
    }

    /**
     * Mark as returned
     */
    public function markAsReturned(Testzadel $testzadel)
    {
        try {
            $testzadel->update([
                'status' => 'teruggegeven',
                'werkelijke_retour_datum' => now(),
            ]);

            return redirect()->route('testzadels.index')
                ->with('success', 'Testzadel succesvol gemarkeerd als teruggegeven.');
        } catch (\Exception $e) {
            \Log::error('Testzadel markeren als teruggegeven mislukt: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Kon testzadel niet markeren als teruggegeven.');
        }
    }    /**
     * Send reminder email to client
     */
    public function sendReminder(Testzadel $testzadel)
    {
        try {
            // Update laatste herinnering timestamp
            $testzadel->update([
                'laatste_herinnering' => now(),
                'herinnering_verstuurd' => true,
                'herinnering_verstuurd_op' => now()
            ]);

            // Get klant info
            $klant = $testzadel->klant;
            
            if (!$klant || !$klant->email) {
                return response()->json([
                    'success' => false,
                    'message' => 'Klant heeft geen email adres'
                ], 400);
            }

            // Send reminder email
            \Mail::send('emails.testzadel-reminder', [
                'testzadel' => $testzadel,
                'klant' => $klant
            ], function($message) use ($klant, $testzadel) {
                $message->to($klant->email, $klant->voornaam . ' ' . $klant->naam);
                $message->from(config('mail.from.address'), config('mail.from.name'));
                $message->replyTo(config('mail.from.address'), config('mail.from.name'));
                $message->subject('Herinnering: Testzadel retourneren - Bonami Sportcoaching');
            });

            \Log::info("Testzadel reminder sent to: {$klant->email} for testzadel ID: {$testzadel->id}");

            return response()->json([
                'success' => true,
                'message' => "Herinnering verstuurd naar {$klant->email}"
            ]);

        } catch (\Exception $e) {
            \Log::error("Failed to send testzadel reminder: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Fout bij versturen herinnering: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk send reminders to all late testzadels
     */
    public function sendBulkReminders(Request $request)
    {
        try {
            $testzadels = Testzadel::where('status', 'uitgeleend')
                ->whereDate('verwachte_retour_datum', '<', now())
                ->with('klant')
                ->get();

            $sent = 0;
            $errors = [];

            foreach ($testzadels as $testzadel) {
                try {
                    if ($testzadel->klant && $testzadel->klant->email) {
                        $this->sendReminder($testzadel);
                        $sent++;
                    }
                } catch (\Exception $e) {
                    $errors[] = "Fout bij {$testzadel->klant->naam}: " . $e->getMessage();
                }
            }

            return response()->json([
                'success' => true,
                'message' => "{$sent} herinneringen verstuurd",
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Fout bij bulk versturen: ' . $e->getMessage()
            ], 500);
        }
    }
}