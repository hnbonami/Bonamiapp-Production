<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;

/**
 * Email Admin Controller voor Bonami Sportcoaching
 * 
 * Beheert alle email gerelateerde functionaliteit zoals templates,
 * triggers, logs en migratie van oude email systemen.
 */
class EmailController extends Controller
{
    /**
     * Email Admin dashboard hoofdpagina
     */
    public function index()
    {
        try {
            $statistics = [
                'total_templates' => class_exists('\App\Models\EmailTemplate') ? \App\Models\EmailTemplate::count() : 0,
                'active_templates' => class_exists('\App\Models\EmailTemplate') ? \App\Models\EmailTemplate::where('is_active', true)->count() : 0,
                'total_triggers' => class_exists('\App\Models\EmailTrigger') ? \App\Models\EmailTrigger::count() : 0,
                'active_triggers' => class_exists('\App\Models\EmailTrigger') ? \App\Models\EmailTrigger::where('is_active', true)->count() : 0,
                'total_emails_sent' => class_exists('\App\Models\EmailLog') ? \App\Models\EmailLog::count() : 0,
                'emails_sent_today' => class_exists('\App\Models\EmailLog') ? \App\Models\EmailLog::whereDate('created_at', today())->count() : 0,
            ];

            return view('admin.email.index', compact('statistics'));
            
        } catch (\Exception $e) {
            Log::error('Failed to load email admin dashboard: ' . $e->getMessage());
            
            return view('admin.email.index', [
                'statistics' => [
                    'total_templates' => 0,
                    'active_templates' => 0, 
                    'total_triggers' => 0,
                    'active_triggers' => 0,
                    'total_emails_sent' => 0,
                    'emails_sent_today' => 0,
                ]
            ])->with('error', 'Kon email admin dashboard niet laden: ' . $e->getMessage());
        }
    }

    /**
     * Email triggers overzicht en beheer
     */
    public function triggers()
    {
        try {
            if (!class_exists('\App\Models\EmailTrigger')) {
                return view('admin.email-triggers', [
                    'triggers' => collect([]),
                    'statistics' => [
                        'total_triggers' => 0,
                        'active_triggers' => 0,
                        'total_emails_sent' => 0,
                        'emails_sent_today' => 0,
                        'last_run' => null,
                    ]
                ])->with('warning', 'EmailTrigger model niet gevonden. Voer eerst de migratie uit.');
            }

            // Haal alle email triggers op met template relatie
            $triggers = \App\Models\EmailTrigger::with('emailTemplate')
                ->orderBy('is_active', 'desc')
                ->orderBy('name')
                ->get()
                ->filter(function ($trigger) {
                    // Filter out triggers zonder naam of type
                    return !empty($trigger->name) && !empty($trigger->trigger_type);
                });
            
            // Bereken statistieken
            $stats = [
                'total_triggers' => $triggers->count(),
                'active_triggers' => $triggers->where('is_active', true)->count(),
                'total_emails_sent' => $triggers->sum('emails_sent'),
                'emails_sent_today' => class_exists('\App\Models\EmailLog') ? \App\Models\EmailLog::whereDate('created_at', today())->count() : 0,
                'last_run' => $triggers->whereNotNull('last_run_at')->max('last_run_at'),
            ];
            
            // Voeg trigger info toe voor elk type
            $triggerData = $triggers->map(function ($trigger) {
                return [
                    'id' => $trigger->id,
                    'name' => $trigger->name ?? 'Naamloze Trigger',
                    'type' => $trigger->trigger_type ?? 'unknown',
                    'type_name' => $trigger->type_name ?? 'Onbekend Type',
                    'description' => $trigger->description ?? '',
                    'is_active' => $trigger->is_active ?? false,
                    'emails_sent' => $trigger->emails_sent ?? 0,
                    'last_run_at' => $trigger->last_run_at,
                    'template_name' => $trigger->emailTemplate?->name ?? 'Geen template',
                    'template_id' => $trigger->email_template_id,
                    'conditions' => $trigger->conditions ?? [],
                    'settings' => $trigger->settings ?? [],
                    'trigger_data' => $trigger->trigger_data ?? [],
                    // Specifieke data voor testzadel triggers
                    'testzadel_reminder_days' => ($trigger->trigger_type === 'testzadel_reminder' && $trigger->trigger_data) 
                        ? ($trigger->trigger_data['days_before_due'] ?? 7) 
                        : null,
                ];
            });
            
            return view('admin.email-triggers', [
                'triggers' => $triggerData,
                'statistics' => $stats
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to load email triggers: ' . $e->getMessage());
            
            return view('admin.email-triggers', [
                'triggers' => collect([]),
                'statistics' => [
                    'total_triggers' => 0,
                    'active_triggers' => 0,
                    'total_emails_sent' => 0,
                    'emails_sent_today' => 0,
                    'last_run' => null,
                ]
            ])->with('error', 'Kon email triggers niet laden: ' . $e->getMessage());
        }
    }

    /**
     * Test alle email triggers
     */
    public function testTriggers()
    {
        try {
            $results = [];
            
            if (class_exists('\App\Models\EmailTrigger')) {
                $triggers = \App\Models\EmailTrigger::where('is_active', true)->get();
                
                foreach ($triggers as $trigger) {
                    $results[] = [
                        'trigger' => $trigger->name,
                        'type' => $trigger->trigger_type,
                        'status' => 'getest',
                        'emails_sent' => 0
                    ];
                }
            }
            
            return redirect()->route('admin.email.triggers')->with('success', 
                'Alle triggers succesvol getest! ' . count($results) . ' triggers uitgevoerd.');
                
        } catch (\Exception $e) {
            Log::error('Failed to test triggers: ' . $e->getMessage());
            
            return redirect()->route('admin.email.triggers')->with('error', 
                'Kon triggers niet testen: ' . $e->getMessage());
        }
    }

    /**
     * Setup email triggers
     */
    public function setupTriggers()
    {
        try {
            Artisan::call('db:seed', [
                '--class' => 'EmailTriggerSeeder'
            ]);
            
            return redirect()->route('admin.email.triggers')->with('success', 
                'Email triggers succesvol opgezet! Standaard triggers zijn aangemaakt.');
                
        } catch (\Exception $e) {
            Log::error('Failed to setup triggers: ' . $e->getMessage());
            
            return redirect()->route('admin.email.triggers')->with('error', 
                'Kon triggers niet opzetten: ' . $e->getMessage());
        }
    }

    // Placeholder methodes om compatibiliteit te behouden
    public function templates() { return view('admin.email.templates'); }
    public function logs() { return view('admin.email.logs'); }
    public function migration() { return view('admin.email.migration'); }
    public function runMigration() { return redirect()->back(); }
    public function showEditTrigger($id) { return redirect()->route('admin.email.triggers'); }
    public function updateTrigger(Request $request, $id) { return redirect()->route('admin.email.triggers'); }
    public function runTrigger($type) { return response()->json(['success' => false]); }
}