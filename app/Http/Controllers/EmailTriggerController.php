<?php

namespace App\Http\Controllers;

use App\Models\EmailTrigger;
use App\Models\EmailTemplate;
use App\Services\EmailIntegrationService;
use Illuminate\Http\Request;

class EmailTriggerController extends Controller
{
    /**
     * Check of gebruiker admin toegang heeft
     */
    private function checkAdminAccess()
    {
        // Sta alle authenticated users toe (voor nu - kan later worden aangescherpt)
        if (!auth()->check()) {
            abort(403, 'Geen toegang. Log eerst in.');
        }
        
        // Log voor debugging
        \Log::info('🔐 EmailTrigger Access Check', [
            'user_id' => auth()->id(),
            'user_email' => auth()->user()->email,
            'has_access' => true
        ]);
    }

    /**
     * Toon alle email triggers
     */
    public function index()
    {
        $this->checkAdminAccess();

        $triggers = EmailTrigger::with('template')
            ->where('organisatie_id', auth()->user()->organisatie_id)
            ->orderBy('name')
            ->get();
        
        // Bereken statistieken voor de view
        $stats = [
            'total_sent' => \DB::table('email_logs')->count(),
            'today_sent' => \DB::table('email_logs')->whereDate('created_at', today())->count(),
            'failed' => \DB::table('email_logs')->where('status', 'failed')->count(),
            'open_rate' => '0%' // Placeholder voor open rate
        ];
        
        return view('admin.email.triggers', compact('triggers', 'stats'));
    }

    /**
     * Run alle actieve triggers nu
     */
    public function runNow(EmailIntegrationService $emailService)
    {
        $this->checkAdminAccess();

        try {
            \Log::info('🔄 Manual run all triggers initiated', [
                'user_id' => auth()->id()
            ]);
            
            $results = $emailService->runAllTriggers();
            
            $message = "✅ Triggers uitgevoerd: {$results['triggers_run']} trigger(s), {$results['emails_sent']} email(s) verstuurd";
            
            if (!empty($results['errors'])) {
                $message .= " ⚠️ met " . count($results['errors']) . " fout(en)";
            }
            
            \Log::info('✅ Manual run completed', [
                'triggers_run' => $results['triggers_run'],
                'emails_sent' => $results['emails_sent']
            ]);
            
            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            \Log::error('❌ Failed to run all triggers', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->with('error', 'Fout bij uitvoeren triggers: ' . $e->getMessage());
        }
    }

    /**
     * Test een specifieke trigger
     */
    public function test($triggerId, EmailIntegrationService $emailService)
    {
        $this->checkAdminAccess();

        try {
            $trigger = EmailTrigger::findOrFail($triggerId);
            
            // Check organisatie toegang
            if ($trigger->organisatie_id !== auth()->user()->organisatie_id) {
                abort(403, 'Geen toegang tot deze trigger');
            }
            
            \Log::info('🧪 Testing single trigger', [
                'trigger_id' => $trigger->id,
                'trigger_name' => $trigger->name,
                'trigger_type' => $trigger->trigger_type,
                'user_id' => auth()->id()
            ]);
            
            $sent = $emailService->runTrigger($trigger);
            
            \Log::info('✅ Single trigger test completed', [
                'trigger_name' => $trigger->name,
                'emails_sent' => $sent
            ]);
            
            if ($sent > 0) {
                $message = "✅ Test geslaagd: {$sent} email(s) verstuurd voor trigger '{$trigger->name}'";
            } else {
                $message = "ℹ️ Test voltooid: Geen ontvangers gevonden voor trigger '{$trigger->name}' (geen data voldoet aan voorwaarden)";
            }
            
            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            \Log::error("❌ Test trigger failed: {$triggerId}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->with('error', 'Test gefaald: ' . $e->getMessage());
        }
    }

    /**
     * Toon edit formulier voor een trigger
     */
    public function edit(EmailTrigger $trigger)
    {
        \Log::info('📝 EmailTrigger Edit aangeroepen', [
            'trigger_id' => $trigger->id,
            'trigger_name' => $trigger->name,
            'user_id' => auth()->id(),
            'user_email' => auth()->user()->email ?? 'unknown'
        ]);

        // Haal alle templates op voor de dropdown
        $templates = EmailTemplate::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.email.triggers.edit', compact('trigger', 'templates'));
    }

    /**
     * Update trigger instellingen
     */
    public function update(Request $request, EmailTrigger $trigger)
    {
        $this->checkAdminAccess();

        // Check organisatie toegang
        if ($trigger->organisatie_id !== auth()->user()->organisatie_id) {
            abort(403, 'Geen toegang tot deze trigger');
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
            'template_id' => 'nullable|exists:templates,id',
            'frequency' => 'required|string|in:daily,weekly,monthly',
        ]);
        
        $trigger->update($validated);
        
        \Log::info('Trigger updated', [
            'trigger_id' => $trigger->id,
            'trigger_name' => $trigger->name,
            'user_id' => auth()->id()
        ]);
        
        return redirect()->route('admin.email.triggers')
            ->with('success', "Trigger '{$trigger->name}' bijgewerkt");
    }

    /**
     * Run een specifieke trigger op basis van type (voor JavaScript API call)
     */
    public function runSingle($triggerType, EmailIntegrationService $emailService)
    {
        $this->checkAdminAccess();

        try {
            \Log::info('🧪 Running single trigger via API', [
                'trigger_type' => $triggerType,
                'user_id' => auth()->id(),
                'organisatie_id' => auth()->user()->organisatie_id
            ]);
            
            // Probeer eerst op trigger_type te zoeken
            $trigger = EmailTrigger::where('trigger_type', $triggerType)
                ->where('is_active', true)
                ->first();
            
            // Als niet gevonden, probeer op name te zoeken (case-insensitive)
            if (!$trigger) {
                $trigger = EmailTrigger::whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($triggerType) . '%'])
                    ->where('is_active', true)
                    ->first();
            }
            
            // Als nog steeds niet gevonden, geef duidelijke foutmelding
            if (!$trigger) {
                \Log::warning('Trigger niet gevonden', [
                    'trigger_type' => $triggerType,
                    'available_triggers' => EmailTrigger::pluck('name', 'trigger_type')->toArray()
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => "Geen actieve trigger gevonden voor type: {$triggerType}. Controleer of de trigger bestaat en actief is."
                ], 404);
            }
            
            $sent = $emailService->runTrigger($trigger);
            
            \Log::info('✅ Single trigger API call completed', [
                'trigger_name' => $trigger->name,
                'trigger_type' => $trigger->trigger_type,
                'emails_sent' => $sent
            ]);
            
            return response()->json([
                'success' => true,
                'emails_sent' => $sent,
                'message' => "Trigger '{$trigger->name}' succesvol uitgevoerd"
            ]);
        } catch (\Exception $e) {
            \Log::error("❌ Single trigger API call failed: {$triggerType}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Fout: ' . $e->getMessage()
            ], 500);
        }
    }
}
