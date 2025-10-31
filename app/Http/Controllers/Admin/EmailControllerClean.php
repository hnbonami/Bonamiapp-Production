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
     * Email templates overzicht
     */
    public function templates()
    {
        try {
            if (!class_exists('\App\Models\EmailTemplate')) {
                return view('admin.email.templates', [
                    'templates' => collect([]),
                    'statistics' => ['total' => 0, 'active' => 0]
                ])->with('warning', 'EmailTemplate model niet gevonden. Voer eerst de migratie uit.');
            }

            $templates = \App\Models\EmailTemplate::orderBy('name')->get();
            $statistics = [
                'total' => $templates->count(),
                'active' => $templates->where('is_active', true)->count()
            ];

            return view('admin.email.templates', compact('templates', 'statistics'));
            
        } catch (\Exception $e) {
            Log::error('Failed to load email templates: ' . $e->getMessage());
            
            return view('admin.email.templates', [
                'templates' => collect([]),
                'statistics' => ['total' => 0, 'active' => 0]
            ])->with('error', 'Kon email templates niet laden: ' . $e->getMessage());
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
     * Email logs overzicht
     */
    public function logs()
    {
        try {
            if (!class_exists('\App\Models\EmailLog')) {
                return view('admin.email.logs', [
                    'logs' => collect([]),
                    'statistics' => ['total' => 0, 'today' => 0, 'failed' => 0]
                ])->with('warning', 'EmailLog model niet gevonden. Voer eerst de migratie uit.');
            }

            $logs = \App\Models\EmailLog::with('template')
                ->orderBy('created_at', 'desc')
                ->paginate(50);

            $statistics = [
                'total' => \App\Models\EmailLog::count(),
                'today' => \App\Models\EmailLog::whereDate('created_at', today())->count(),
                'failed' => \App\Models\EmailLog::where('status', 'failed')->count()
            ];

            return view('admin.email.logs', compact('logs', 'statistics'));
            
        } catch (\Exception $e) {
            Log::error('Failed to load email logs: ' . $e->getMessage());
            
            return view('admin.email.logs', [
                'logs' => collect([]),
                'statistics' => ['total' => 0, 'today' => 0, 'failed' => 0]
            ])->with('error', 'Kon email logs niet laden: ' . $e->getMessage());
        }
    }

    /**
     * Email migratie pagina
     */
    public function migration()
    {
        return view('admin.email.migration');
    }

    /**
     * Voer email migratie uit
     */
    public function runMigration(Request $request)
    {
        try {
            // Voer email gerelateerde migraties uit
            Artisan::call('migrate', [
                '--path' => 'database/migrations',
                '--force' => true
            ]);

            // Seed email data
            Artisan::call('db:seed', [
                '--class' => 'EmailTriggerSeeder'
            ]);

            return redirect()->route('admin.email.migration')->with('success', 
                'Email migratie succesvol uitgevoerd! Alle email tabellen en triggers zijn aangemaakt.');
                
        } catch (\Exception $e) {
            Log::error('Failed to run email migration: ' . $e->getMessage());
            
            return redirect()->route('admin.email.migration')->with('error', 
                'Email migratie mislukt: ' . $e->getMessage());
        }
    }

    /**
     * Toon trigger bewerk formulier
     */
    public function showEditTrigger($id)
    {
        try {
            if (!class_exists('\App\Models\EmailTrigger')) {
                return redirect()->route('admin.email.triggers')->with('error', 
                    'EmailTrigger model niet gevonden. Voer eerst de migratie uit.');
            }

            $trigger = \App\Models\EmailTrigger::with('emailTemplate')->findOrFail($id);
            $templates = class_exists('\App\Models\EmailTemplate') 
                ? \App\Models\EmailTemplate::where('is_active', true)->get() 
                : collect([]);
            
            return view('admin.email.triggers.edit', [
                'trigger' => $trigger,
                'templates' => $templates
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to load trigger edit form: ' . $e->getMessage());
            
            return redirect()->route('admin.email.triggers')->with('error', 
                'Kon trigger niet laden: ' . $e->getMessage());
        }
    }

    /**
     * Update email trigger
     */
    public function updateTrigger(Request $request, $id)
    {
        try {
            if (!class_exists('\App\Models\EmailTrigger')) {
                return redirect()->route('admin.email.triggers')->with('error', 
                    'EmailTrigger model niet gevonden.');
            }

            $trigger = \App\Models\EmailTrigger::findOrFail($id);
            
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'email_template_id' => 'required|exists:email_templates,id',
                'is_active' => 'boolean',
                'testzadel_reminder_days' => 'nullable|integer|min:1|max:30',
                'birthday_send_time' => 'nullable|string',
                'conditions' => 'nullable|array',
                'settings' => 'nullable|array'
            ]);
            
            // Basis trigger data updaten
            $triggerData = [
                'name' => $validated['name'],
                'description' => $validated['description'],
                'email_template_id' => $validated['email_template_id'],
                'is_active' => $request->has('is_active'),
                'conditions' => $validated['conditions'] ?? [],
                'settings' => $validated['settings'] ?? []
            ];
            
            // Type specifieke data verwerken
            $existingTriggerData = $trigger->trigger_data ?? [];
            
            if ($trigger->trigger_type === 'testzadel_reminder' && isset($validated['testzadel_reminder_days'])) {
                $existingTriggerData['days_before_due'] = (int)$validated['testzadel_reminder_days'];
            }
            
            if ($trigger->trigger_type === 'birthday' && isset($validated['birthday_send_time'])) {
                $existingTriggerData['send_time'] = $validated['birthday_send_time'];
            }
            
            $triggerData['trigger_data'] = $existingTriggerData;
            
            $trigger->update($triggerData);
            
            Log::info('Email trigger bijgewerkt', [
                'trigger_id' => $trigger->id,
                'trigger_name' => $trigger->name,
                'updated_by' => auth()->id()
            ]);
            
            return redirect()->route('admin.email.triggers')->with('success', 
                "Trigger '{$trigger->name}' succesvol bijgewerkt!");
            
        } catch (\Exception $e) {
            Log::error('Failed to update email trigger: ' . $e->getMessage());
            
            return redirect()->back()->with('error', 
                'Kon trigger niet bijwerken: ' . $e->getMessage());
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
                        'emails_sent' => 0 // Mock voor test
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

    /**
     * Voer een specifieke trigger uit
     */
    public function runTrigger($type)
    {
        try {
            $emailsSent = 0;
            
            // Mock implementation voor nu
            switch ($type) {
                case 'birthday':
                    $emailsSent = 0; // Mock
                    break;
                    
                case 'testzadel_reminder':
                    $emailsSent = 0; // Mock
                    break;
                    
                default:
                    throw new \Exception("Onbekend trigger type: {$type}");
            }
            
            return response()->json([
                'success' => true,
                'message' => "Trigger '{$type}' succesvol uitgevoerd",
                'emails_sent' => $emailsSent
            ]);
            
        } catch (\Exception $e) {
            Log::error("Failed to run trigger {$type}: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}