<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\MailHelper;
use App\Models\Testzadel;
use App\Models\Klant;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class TestzadelsController extends Controller
{
    public function index()
    {
        // Alleen niet-gearchiveerde testzadels tonen in hoofdtabel
        $testzadels = Testzadel::with(['klant', 'bikefit'])
            ->where('status', '!=', 'gearchiveerd')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Calculate stats for the view using collection methods
        $stats = [
            'total' => $testzadels->count(),
            'uitgeleend' => $testzadels->where('status', 'uitgeleend')->count(),
            'teruggegeven' => $testzadels->where('status', 'teruggegeven')->count(),
            'te_laat' => $testzadels->filter(function($item) {
                return $item->status === 'uitgeleend' && $item->verwachte_retour_datum < now();
            })->count(),
            'herinnering_nodig' => $testzadels->filter(function($item) {
                return $item->status === 'uitgeleend' && $item->verwachte_retour_datum <= now()->addDays(3);
            })->count(),
            'verwacht_vandaag' => $testzadels->filter(function($item) {
                return $item->status === 'uitgeleend' && 
                       Carbon::parse($item->verwachte_retour_datum)->isToday();
            })->count(),
        ];
        
        return view('testzadels.index', compact('testzadels', 'stats'));
    }

    public function create()
    {
        $klanten = Klant::orderBy('voornaam')->get();
        return view('testzadels.create', compact('klanten'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'klant_id' => 'required|exists:klanten,id',
            'bikefit_id' => 'nullable|exists:bikefits,id',
            'onderdeel_type' => 'required|string|max:255',
            'zadel_merk' => 'nullable|string|max:255',
            'zadel_model' => 'nullable|string|max:255',
            'zadel_type' => 'nullable|string|max:255',
            'zadel_breedte' => 'nullable|numeric',
            'uitleen_datum' => 'required|date',
            'verwachte_retour_datum' => 'required|date|after:uitleen_datum',
            'opmerkingen' => 'nullable|string',
            'automatisch_mailtje' => 'boolean',
        ]);

        $testzadel = Testzadel::create($validatedData);
        return redirect()->route('testzadels.index')->with('success', 'Testzadel toegevoegd!');
    }

    public function show(Testzadel $testzadel)
    {
        return view('testzadels.show', compact('testzadel'));
    }

    public function edit(Testzadel $testzadel)
    {
        $klanten = Klant::orderBy('voornaam')->get();
        $bikefits = \App\Models\Bikefit::orderBy('created_at', 'desc')->get();
        
        return view('testzadels.edit', compact('testzadel', 'klanten', 'bikefits'));
    }

    public function update(Request $request, Testzadel $testzadel)
    {
        $validatedData = $request->validate([
            'klant_id' => 'required|exists:klanten,id',
            'bikefit_id' => 'nullable|exists:bikefits,id',
            'onderdeel_type' => 'required|string|max:255',
            'zadel_merk' => 'nullable|string|max:255',
            'zadel_model' => 'nullable|string|max:255',
            'zadel_type' => 'nullable|string|max:255',
            'zadel_breedte' => 'nullable|numeric',
            'uitleen_datum' => 'required|date',
            'verwachte_retour_datum' => 'required|date|after:uitleen_datum',
            'opmerkingen' => 'nullable|string',
            'automatisch_mailtje' => 'boolean',
            'status' => 'nullable|string|in:uitgeleend,teruggegeven,gearchiveerd', // Status validatie toegevoegd
        ]);
        
        // Als status wordt gewijzigd naar 'teruggegeven', zet automatisch werkelijke_retour_datum
        if (isset($validatedData['status']) && $validatedData['status'] === 'teruggegeven' && $testzadel->status !== 'teruggegeven') {
            $validatedData['werkelijke_retour_datum'] = now();
        }
        
        // Als status wordt gewijzigd van 'teruggegeven' naar iets anders, reset werkelijke_retour_datum
        if (isset($validatedData['status']) && $validatedData['status'] !== 'teruggegeven' && $testzadel->status === 'teruggegeven') {
            $validatedData['werkelijke_retour_datum'] = null;
        }
        
        $testzadel->update($validatedData);
        return redirect()->route('testzadels.index')->with('success', 'Testzadel bijgewerkt!');
    }

    public function destroy(Testzadel $testzadel)
    {
        $testzadel->delete();
        return redirect()->route('testzadels.index')->with('success', 'Testzadel verwijderd!');
    }

    /**
     * Send reminder email using new email template system
     */
    public function sendReminder(Testzadel $testzadel)
    {
        // Only send if automatic mailing is enabled
        if (!$testzadel->automatisch_mailtje) {
            return redirect()->back()->with('warning', 'Automatische herinneringen zijn uitgeschakeld voor deze uitlening');
        }
        
        try {
            \Log::info('Attempting to send testzadel reminder', [
                'testzadel_id' => $testzadel->id,
                'klant_email' => $testzadel->klant->email,
                'klant_naam' => $testzadel->klant->naam
            ]);

            // Check if email template exists
            $template = \App\Models\EmailTemplate::where('type', 'testzadel_reminder')
                                                ->where('is_active', true)
                                                ->first();
            
            if (!$template) {
                \Log::error('No active testzadel_reminder template found');
                return redirect()->back()->with('error', 'Geen actieve email template gevonden voor testzadel herinneringen. Ga naar Email Beheer om een template aan te maken.');
            }

            // Use new Email Template Service
            $emailService = new \App\Services\EmailIntegrationService();
            
            // Prepare template variables
            $variables = [
                'voornaam' => $testzadel->klant->voornaam,
                'naam' => $testzadel->klant->naam,
                'email' => $testzadel->klant->email,
                'merk' => $testzadel->zadel_merk ?? '',
                'model' => $testzadel->zadel_model ?? '',
                'type' => $testzadel->zadel_type ?? '',
                'breedte' => $testzadel->zadel_breedte ?? '',
                'uitgeleend_op' => $testzadel->uitleen_datum->format('d/m/Y'),
                'verwachte_retour' => $testzadel->verwachte_retour_datum->format('d/m/Y'),
                'bedrijf_naam' => 'Bonami Sportcoaching',
                'datum' => now()->format('d/m/Y'),
                'jaar' => now()->format('Y'),
            ];
            
            \Log::info('Sending testzadel reminder with variables', $variables);
            
            // Send using template system
            $result = $emailService->sendTestzadelReminderEmail($testzadel->klant, $variables);
            
            if ($result) {
                // Update reminder status
                $testzadel->update([
                    'herinnering_verstuurd' => true,
                    'herinnering_verstuurd_op' => now(),
                    'laatste_herinnering' => now()
                ]);
                
                \Log::info('Testzadel reminder sent successfully', [
                    'testzadel_id' => $testzadel->id,
                    'recipient' => $testzadel->klant->email
                ]);
                
                return redirect()->back()->with('success', 'Herinnering verstuurd naar ' . $testzadel->klant->voornaam . ' ' . $testzadel->klant->naam);
            } else {
                \Log::error('EmailIntegrationService returned false');
                return redirect()->back()->with('error', 'Er is een fout opgetreden bij het versturen van de herinnering');
            }
            
        } catch (\Exception $e) {
            \Log::error('Failed to send testzadel reminder: ' . $e->getMessage(), [
                'testzadel_id' => $testzadel->id,
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Er is een fout opgetreden bij het versturen van de herinnering: ' . $e->getMessage());
        }
    }
    
    /**
     * Automatically send reminders for overdue testzadels (only if automatic mailing enabled)
     */
    public function sendAutomaticReminders()
    {
        $overdueTestzadels = \App\Models\Testzadel::where('status', 'uitgeleend')
            ->where('automatisch_mailtje', true) // Only for testzadels with automatic mailing enabled
            ->where('verwachte_retour_datum', '<', now())
            ->whereNull('laatste_herinnering')
            ->orWhere('laatste_herinnering', '<', now()->subDays(7))
            ->get();
            
        $emailService = new \App\Services\EmailIntegrationService();
        $sentCount = 0;
        
        foreach ($overdueTestzadels as $testzadel) {
            try {
                $variables = [
                    'voornaam' => $testzadel->klant->voornaam,
                    'naam' => $testzadel->klant->naam,
                    'email' => $testzadel->klant->email,
                    'onderdeel_type' => $testzadel->onderdeel_type,
                    'zadel_merk' => $testzadel->zadel_merk,
                    'zadel_model' => $testzadel->zadel_model,
                    'uitleen_datum' => $testzadel->uitleen_datum,
                    'verwachte_retour_datum' => $testzadel->verwachte_retour_datum,
                    'datum' => now()->format('d-m-Y'),
                ];
                
                $emailResult = $emailService->sendTestzadelReminderEmail(
                    $testzadel->klant,
                    $variables
                );
                
                if ($emailResult) {
                    $testzadel->update([
                        'herinnering_verstuurd' => true,
                        'herinnering_verstuurd_op' => now(),
                        'laatste_herinnering' => now()
                    ]);
                    $sentCount++;
                }
                
            } catch (\Exception $e) {
                \Log::error('Failed to send automatic reminder for testzadel ' . $testzadel->id . ': ' . $e->getMessage());
            }
        }
        
        \Log::info('Automatic testzadel reminders sent: ' . $sentCount);
        return $sentCount;
    }
    
    public function markAsReturned(Testzadel $testzadel)
    {
        $testzadel->update([
            'status' => 'teruggegeven',
            'werkelijke_retour_datum' => now(),
        ]);

        return redirect()->route('testzadels.index')->with('success', 'Testzadel gemarkeerd als teruggegeven!');
    }
    
    public function archive(Testzadel $testzadel)
    {
        try {
            $testzadel->update(['status' => 'gearchiveerd']);
            
            return redirect()->route('testzadels.index')
                ->with('success', 'Testzadel is succesvol gearchiveerd.');
        } catch (\Exception $e) {
            return redirect()->route('testzadels.index')
                ->with('error', 'Er is een fout opgetreden bij het archiveren van de testzadel.');
        }
    }
    
    public function archived()
    {
        $testzadels = Testzadel::with('klant')
            ->where('status', 'gearchiveerd')
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('testzadels.archived', compact('testzadels'));
    }
    
    public function sendReminderAjax(Testzadel $testzadel)
    {
        \Log::info('=== AJAX REMINDER START ===', ['testzadel_id' => $testzadel->id]);
        
        try {
            // Only send if automatic mailing is enabled
            if (!$testzadel->automatisch_mailtje) {
                \Log::warning('Automatic mailing disabled for testzadel', ['testzadel_id' => $testzadel->id]);
                return response()->json(['success' => false, 'message' => 'Automatische herinneringen zijn uitgeschakeld']);
            }
            
            \Log::info('AJAX: Attempting to send testzadel reminder', [
                'testzadel_id' => $testzadel->id,
                'klant_email' => $testzadel->klant->email,
                'klant_naam' => $testzadel->klant->naam
            ]);

            // Check if email template exists
            $template = \App\Models\EmailTemplate::where('type', 'testzadel_reminder')
                                                ->where('is_active', true)
                                                ->first();
            
            if (!$template) {
                \Log::error('AJAX: No active testzadel_reminder template found');
                return response()->json(['success' => false, 'message' => 'Geen actieve email template gevonden. Ga naar Email Beheer.']);
            }
            
            \Log::info('Template found', ['template_id' => $template->id, 'template_name' => $template->name]);
            
            // Use new Email Template Service
            $emailService = new \App\Services\EmailIntegrationService();
            
            // Prepare template variables
            $variables = [
                'voornaam' => $testzadel->klant->voornaam,
                'naam' => $testzadel->klant->naam,
                'email' => $testzadel->klant->email,
                'merk' => $testzadel->zadel_merk ?? '',
                'model' => $testzadel->zadel_model ?? '',
                'type' => $testzadel->zadel_type ?? '',
                'breedte' => $testzadel->zadel_breedte ?? '',
                'uitgeleend_op' => $testzadel->uitleen_datum->format('d/m/Y'),
                'verwachte_retour' => $testzadel->verwachte_retour_datum->format('d/m/Y'),
                'bedrijf_naam' => 'Bonami Sportcoaching',
                'datum' => now()->format('d/m/Y'),
                'jaar' => now()->format('Y'),
            ];
            
            \Log::info('AJAX: Sending testzadel reminder with variables', $variables);
            
            // Send using template system
            $result = $emailService->sendTestzadelReminderEmail($testzadel->klant, $variables);
            
            \Log::info('Email service result', ['result' => $result]);
            
            if ($result) {
                // Update reminder status
                $updateResult = $testzadel->update([
                    'herinnering_verstuurd' => true,
                    'herinnering_verstuurd_op' => now(),
                    'laatste_herinnering' => now()
                ]);
                
                \Log::info('Testzadel update result', ['update_result' => $updateResult]);
                
                \Log::info('AJAX: Testzadel reminder sent successfully', [
                    'testzadel_id' => $testzadel->id,
                    'recipient' => $testzadel->klant->email
                ]);
                
                \Log::info('=== AJAX REMINDER SUCCESS ===');
                
                return response()->json([
                    'success' => true, 
                    'message' => 'Herinnering verstuurd naar ' . $testzadel->klant->voornaam . ' ' . $testzadel->klant->naam
                ]);
            } else {
                \Log::error('AJAX: EmailIntegrationService returned false');
                return response()->json(['success' => false, 'message' => 'Email service geeft geen succes terug']);
            }
            
        } catch (\Exception $e) {
            \Log::error('=== AJAX REMINDER ERROR ===', [
                'testzadel_id' => $testzadel->id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['success' => false, 'message' => 'Fout: ' . $e->getMessage()]);
        }
    }
    
    public function markReturnedAjax(Testzadel $testzadel)
    {
        try {
            $testzadel->update([
                'status' => 'teruggegeven',
                'werkelijke_retour_datum' => now(),
            ]);

            return response()->json(['success' => true, 'message' => 'Testzadel gemarkeerd als teruggegeven']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Er is een fout opgetreden']);
        }
    }
    
    public function archiveAjax(Testzadel $testzadel)
    {
        try {
            $testzadel->update(['status' => 'gearchiveerd']);
            
            return response()->json(['success' => true, 'message' => 'Testzadel gearchiveerd']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Er is een fout opgetreden bij het archiveren']);
        }
    }
}