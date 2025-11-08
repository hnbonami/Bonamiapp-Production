<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use App\Models\EmailSettings;
use App\Models\EmailTrigger;
use App\Models\EmailLog;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class EmailController extends Controller
{
    /**
     * Check admin toegang voor alle email beheer functies
     */
    private function checkAdminAccess()
    {
        if (!in_array(auth()->user()->role, ['admin', 'organisatie_admin', 'superadmin'])) {
            abort(403, 'Geen toegang. Alleen administrators hebben toegang tot email beheer.');
        }
    }
    
    public function index()
    {
        $this->checkAdminAccess();
        
        $templates = EmailTemplate::where('is_active', true)->get();
        $subscriptionStats = $this->getSubscriptionStats();
        
        return view('admin.email-simple', compact('templates', 'subscriptionStats'));
    }

    public function templates()
    {
        $this->checkAdminAccess();
        
        $templates = EmailTemplate::latest()->get();
        
        // Als er geen templates zijn, maak standaard templates aan
        if ($templates->isEmpty()) {
            $this->createDefaultTemplates();
            $templates = EmailTemplate::latest()->get();
        }

        return view('admin.email-templates', compact('templates'));
    }

    public function editTemplate($id)
    {
        $this->checkAdminAccess();
        
        $template = EmailTemplate::findOrFail($id);
        return view('admin.email-edit-template', compact('template'));
    }

    public function updateTemplate(Request $request, $id)
    {
        $this->checkAdminAccess();
        
        $template = EmailTemplate::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|max:255',
            'subject' => 'required|max:255',
            'body_html' => 'required',
            'description' => 'nullable|max:1000',
            'is_active' => 'sometimes|boolean'
        ]);

        $validated['is_active'] = $request->has('is_active');

        $template->update($validated);

        return redirect()->route('admin.email.templates')
                        ->with('success', 'Template succesvol bijgewerkt!');
    }

    public function createTemplate()
    {
        $templateTypes = EmailTemplate::getTypes();
        $settings = EmailSettings::getSettings();
        
        // Default modern email template with logo
        $logoHtml = '';
        if ($settings->hasLogo()) {
            $logoHtml = '<img src="' . $settings->getLogoBase64() . '" alt="' . $settings->company_name . '" style="height: 60px; margin-bottom: 15px;">';
        }
        
        $defaultTemplate = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Template</title>
    <style>
        body { margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4; }
        .email-container { max-width: 600px; margin: 0 auto; background-color: #ffffff; }
        .header { background: linear-gradient(135deg, ' . $settings->primary_color . ' 0%, ' . $settings->secondary_color . ' 100%); padding: 40px 30px; text-align: center; }
        .header h1 { color: #ffffff; margin: 0; font-size: 28px; font-weight: 300; }
        .content { padding: 40px 30px; }
        .content h2 { color: #333333; margin-top: 0; }
        .content p { color: #666666; line-height: 1.6; }
        .button { display: inline-block; padding: 12px 24px; background-color: ' . $settings->primary_color . '; color: #ffffff; text-decoration: none; border-radius: 5px; margin: 20px 0; }
        .footer { background-color: #f8f9fa; padding: 30px; text-align: center; color: #999999; font-size: 14px; }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            ' . $logoHtml . '
            <h1>@{{bedrijf_naam}}</h1>
        </div>
        <div class="content">
            <h2>Beste @{{voornaam}},</h2>
            <p>Hier komt je bericht content...</p>
            
            <a href="#" class="button">Call to Action</a>
            
            <p>' . ($settings->footer_text ?: 'Met vriendelijke groet') . ',<br>Het @{{bedrijf_naam}} team</p>
        </div>
        <div class="footer">
            <p>&copy; @{{jaar}} @{{bedrijf_naam}}. Alle rechten voorbehouden.</p>
            ' . ($settings->signature ? '<p>' . $settings->signature . '</p>' : '') . '
        </div>
    </div>
</body>
</html>';

        return view('admin.email-create-template', compact('templateTypes', 'defaultTemplate', 'settings'));
    }

    public function storeTemplate(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
            'type' => 'required|in:' . implode(',', array_keys(EmailTemplate::getTypes())),
            'subject' => 'required|max:255',
            'body_html' => 'required',
            'description' => 'nullable|max:1000',
            'is_active' => 'sometimes|boolean'
        ]);

        $validated['is_active'] = $request->has('is_active');

        EmailTemplate::create($validated);

        return redirect()->route('admin.email.templates')
                        ->with('success', 'Template succesvol aangemaakt!');
    }

    public function destroyTemplate($id)
    {
        $this->checkAdminAccess();
        
        $template = EmailTemplate::findOrFail($id);
        $template->delete();

        return redirect()->route('admin.email.templates')
                        ->with('success', 'Template succesvol verwijderd!');
    }

    public function settings()
    {
        $this->checkAdminAccess();
        
        $settings = EmailSettings::getSettings();
        return view('admin.email-settings', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|max:255',
            'primary_color' => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'secondary_color' => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'footer_text' => 'nullable|max:500',
            'signature' => 'nullable|max:500',
            'logo' => 'nullable|image|max:2048|mimes:jpg,jpeg,png,gif,svg'
        ]);

        $settings = EmailSettings::getSettings();
        
        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($settings->logo_path && Storage::disk('public')->exists($settings->logo_path)) {
                Storage::disk('public')->delete($settings->logo_path);
            }
            
            // Store new logo
            try {
                $logoPath = $request->file('logo')->store('email-logos', 'public');
                $validated['logo_path'] = $logoPath;
                
                // Debug: Log successful upload
                \Log::info('Logo uploaded successfully: ' . $logoPath);
                
            } catch (\Exception $e) {
                \Log::error('Logo upload failed: ' . $e->getMessage());
                return redirect()->back()->withErrors(['logo' => 'Logo upload mislukt: ' . $e->getMessage()]);
            }
        }

        $settings->update($validated);

        return redirect()->route('admin.email.settings')
                        ->with('success', 'Email instellingen succesvol bijgewerkt!');
    }

    public function triggers()
    {
        $this->checkAdminAccess();
        
        $data = $this->getTriggerStats();
        
        return view('admin.email-triggers', [
            'triggers' => $data['triggers'],
            'statistics' => $data['stats']
        ]);
    }

    public function logs()
    {
        $this->checkAdminAccess();
        
        $logs = EmailLog::orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $stats = [
            'total_sent' => EmailLog::where('status', EmailLog::STATUS_SENT)->count(),
            'sent_today' => EmailLog::where('status', EmailLog::STATUS_SENT)
                ->whereDate('created_at', today())->count(),
            'sent_this_week' => EmailLog::where('status', EmailLog::STATUS_SENT)
                ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'failed_count' => EmailLog::where('status', EmailLog::STATUS_FAILED)->count(),
            'template_emails' => \Schema::hasColumn('email_logs', 'template_id') 
                ? EmailLog::whereNotNull('template_id')->count() 
                : 0, // Will show 0 until migration is run
        ];

        return view('admin.email-logs', compact('logs', 'stats'));
    }

    public function testTriggers(Request $request)
    {
        try {
            $emailService = new EmailService();
            
            $testzadelsSent = $emailService->runTestzadelReminders();
            $birthdaysSent = $this->runBirthdayTrigger();
            
            $totalSent = $testzadelsSent + $birthdaysSent;
            
            if ($totalSent > 0) {
                return redirect()->back()->with('success', 
                    "✅ Email triggers uitgevoerd! {$totalSent} emails verstuurd ({$testzadelsSent} testzadel, {$birthdaysSent} verjaardag)");
            } else {
                return redirect()->back()->with('info', 
                    "ℹ️ Email triggers uitgevoerd. Geen emails hoefden te worden verstuurd op dit moment.");
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 
                "❌ Fout bij uitvoeren triggers: " . $e->getMessage());
        }
    }

    public function setupTriggers(Request $request)
    {
        try {
            $this->createDefaultTriggers();
            
            return redirect()->back()->with('success', 
                "✅ Automatische triggers zijn succesvol geconfigureerd! Alle ontbrekende triggers zijn toegevoegd.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 
                "❌ Fout bij instellen triggers: " . $e->getMessage());
        }
    }

    /**
     * Show edit form for trigger
     */
    public function editTrigger($id)
    {
        $trigger = \App\Models\EmailTrigger::with('emailTemplate')->findOrFail($id);
        $templates = \App\Models\EmailTemplate::where('is_active', true)->get();
        
        return view('admin.email.triggers.edit', compact('trigger', 'templates'));
    }
    
    /**
     * Update trigger
     */
    public function updateTrigger(Request $request, $id)
    {
        $trigger = \App\Models\EmailTrigger::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email_template_id' => 'required|exists:email_templates,id',
            'is_active' => 'boolean',
            'conditions' => 'nullable|array',
            'settings' => 'nullable|array'
        ]);
        
        $trigger->update($validated);
        
        return redirect()->route('admin.email.triggers')->with('success', 'Trigger bijgewerkt!');
    }
    
    /**
     * Run a specific trigger type manually
     */
    public function runTrigger($triggerType)
    {
        try {
            $emailService = new \App\Services\EmailIntegrationService();
            $emailsSent = 0;
            
            switch ($triggerType) {
                case 'birthday':
                    $emailsSent = 0; // Mock
                    break;
                    
                case 'testzadel_reminder':
                    $emailsSent = 0; // Mock
                    break;
                    
                case 'referral_thank_you':
                    $emailsSent = 0; // Mock doorverwijzing trigger
                    break;
                    
                default:
                    throw new \Exception("Onbekend trigger type: {$triggerType}");
            }
            
            return response()->json([
                'success' => true, 
                'emails_sent' => $emailsSent,
                'message' => "{$emailsSent} emails verstuurd"
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Failed to run trigger: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Er is een fout opgetreden']);
        }
    }

    public function migrateTemplates(Request $request)
    {
        try {
            \Artisan::call('email:migrate-templates');
            $output = \Artisan::output();
            
            return redirect()->back()->with('success', 
                "✅ Bestaande templates succesvol gemigreerd! Bekijk ze in Templates beheer.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 
                "❌ Fout bij migreren templates: " . $e->getMessage());
        }
    }

    /**
     * Get email trigger statistics for dashboard
     */
    private function getTriggerStats()
    {
        try {
            // Get all email logs for statistics
            $emailLogs = \App\Models\EmailLog::all();
            
            $stats = [
                'total_sent' => $emailLogs->where('status', 'sent')->count(),
                'sent_today' => $emailLogs->where('status', 'sent')->where('sent_at', '>=', now()->startOfDay())->count(),
                'failed_count' => $emailLogs->where('status', 'failed')->count(),
                'open_rate' => 0,
            ];
            
            // Get all automation triggers
            $automationTriggers = collect();
            try {
                $automationTriggers = \App\Models\EmailTrigger::with('emailTemplate')->get();
            } catch (\Exception $e) {
                \Log::warning('Could not load automation triggers: ' . $e->getMessage());
            }
            
            // Group email logs by template type to count emails per trigger type
            $emailLogsByTemplate = $emailLogs->groupBy('template_id');
            $emailLogsByTrigger = $emailLogs->groupBy('trigger_name');
            
            $triggers = [];
            
            // Process automation triggers and match with email logs
            foreach ($automationTriggers as $trigger) {
                $emailCount = 0;
                $lastRun = null;
                
                // Count emails by template_id
                if ($trigger->emailTemplate && isset($emailLogsByTemplate[$trigger->emailTemplate->id])) {
                    $templateLogs = $emailLogsByTemplate[$trigger->emailTemplate->id];
                    $emailCount = $templateLogs->where('status', 'sent')->count();
                    $lastRun = $templateLogs->max('sent_at');
                }
                
                // Also check by trigger name - IMPORTANT: check trigger_type kolom
                $triggerTypeColumn = $trigger->trigger_type ?? $trigger->type;
                if (isset($emailLogsByTrigger[$triggerTypeColumn])) {
                    $triggerLogs = $emailLogsByTrigger[$triggerTypeColumn];
                    $triggerEmailCount = $triggerLogs->where('status', 'sent')->count();
                    $triggerLastRun = $triggerLogs->max('sent_at');
                    
                    $emailCount = max($emailCount, $triggerEmailCount);
                    $lastRun = $lastRun ? max($lastRun, $triggerLastRun) : $triggerLastRun;
                }
                
                $triggers[] = (object)[
                    'id' => $trigger->id,
                    'name' => $trigger->name,
                    'type' => $triggerTypeColumn, // Gebruik trigger_type kolom
                    'type_name' => $this->getTriggerTypeName($triggerTypeColumn),
                    'emails_sent' => $emailCount,
                    'is_active' => $trigger->is_active ?? true,
                    'last_run_at' => $lastRun ? \Carbon\Carbon::parse($lastRun) : null,
                    'emailTemplate' => $trigger->emailTemplate,
                    'conditions' => $trigger->conditions ?? [],
                    'settings' => $trigger->settings ?? []
                ];
            }
            
            // Add triggers for template types that don't have automation triggers yet
            $templateTypes = [
                'welcome_customer' => 'Welkom Nieuwe Klanten (Automatisch)',
                'welcome_employee' => 'Welkom Nieuwe Medewerkers (Automatisch)', 
                'klant_invitation' => 'Klant Uitnodigingen (Handmatig)',
                'medewerker_invitation' => 'Medewerker Uitnodigingen (Handmatig)',
                'testzadel_reminder' => 'Testzadel Herinneringen',
                'birthday' => 'Verjaardag Felicitaties',
                'referral_thank_you' => 'Doorverwijzing Dankje Email'
            ];
            
            $existingTriggerTypes = $automationTriggers->map(function($trigger) {
                return $trigger->trigger_type ?? $trigger->type;
            })->toArray();
            
            foreach ($templateTypes as $type => $name) {
                if (!in_array($type, $existingTriggerTypes)) {
                    // Find emails for this type
                    $typeEmailCount = 0;
                    $typeLastRun = null;
                    
                    // Check by trigger name in email logs
                    if (isset($emailLogsByTrigger[$type])) {
                        $typeLogs = $emailLogsByTrigger[$type];
                        $typeEmailCount = $typeLogs->where('status', 'sent')->count();
                        $typeLastRun = $typeLogs->max('sent_at');
                    }
                    
                    // Check by template type
                    $template = \App\Models\EmailTemplate::where('type', $type)->first();
                    if ($template && isset($emailLogsByTemplate[$template->id])) {
                        $templateLogs = $emailLogsByTemplate[$template->id];
                        $templateEmailCount = $templateLogs->where('status', 'sent')->count();
                        $templateLastRun = $templateLogs->max('sent_at');
                        
                        $typeEmailCount = max($typeEmailCount, $templateEmailCount);
                        $typeLastRun = $typeLastRun ? max($typeLastRun, $templateLastRun) : $templateLastRun;
                    }
                    
                    if ($typeEmailCount > 0 || $template) {
                        // Zoek of er al een trigger bestaat voor dit type
                        $existingTrigger = \App\Models\EmailTrigger::where('trigger_type', $type)->first();
                        
                        if ($existingTrigger) {
                            // Gebruik bestaande trigger met echte ID
                            $triggers[] = (object)[
                                'id' => $existingTrigger->id,
                                'name' => $existingTrigger->name,
                                'type' => $existingTrigger->trigger_type,
                                'type_name' => $this->getTriggerTypeName($existingTrigger->trigger_type),
                                'emails_sent' => $typeEmailCount,
                                'is_active' => $existingTrigger->is_active,
                                'last_run_at' => $typeLastRun ? \Carbon\Carbon::parse($typeLastRun) : null,
                                'emailTemplate' => $existingTrigger->emailTemplate,
                                'conditions' => $existingTrigger->conditions ?? [],
                                'settings' => $existingTrigger->settings ?? []
                            ];
                        } else {
                            // Maak nieuwe trigger aan zodat deze een ID heeft
                            $newTrigger = \App\Models\EmailTrigger::create([
                                'trigger_key' => $type . '_auto_' . time(),
                                'name' => $name,
                                'type' => $type,
                                'trigger_type' => $type,
                                'description' => 'Automatisch aangemaakte trigger voor ' . $type,
                                'is_active' => true,
                                'email_template_id' => $template ? $template->id : null,
                                'emails_sent' => $typeEmailCount,
                                'conditions' => $this->getDefaultConditions($type),
                                'settings' => $this->getDefaultSettings($type),
                                'trigger_data' => json_encode($this->getDefaultTriggerData($type)),
                                'created_at' => now(),
                                'updated_at' => now()
                            ]);
                            
                            $triggers[] = (object)[
                                'id' => $newTrigger->id,
                                'name' => $newTrigger->name,
                                'type' => $newTrigger->trigger_type,
                                'type_name' => $this->getTriggerTypeName($newTrigger->trigger_type),
                                'emails_sent' => $typeEmailCount,
                                'is_active' => true,
                                'last_run_at' => $typeLastRun ? \Carbon\Carbon::parse($typeLastRun) : null,
                                'emailTemplate' => $template,
                                'conditions' => $newTrigger->conditions ?? [],
                                'settings' => $newTrigger->settings ?? []
                            ];
                        }
                    }
                }
            }
            
            return [
                'stats' => $stats,
                'triggers' => collect($triggers)->sortByDesc('emails_sent')
            ];
            
        } catch (\Exception $e) {
            \Log::error('Failed to get trigger stats: ' . $e->getMessage());
            return [
                'stats' => ['total_sent' => 0, 'sent_today' => 0, 'failed_count' => 0, 'open_rate' => '0%'],
                'triggers' => collect([])
            ];
        }
    }

    /**
     * Helper method om trigger type namen te verkrijgen
     */
    private function getTriggerTypeName($triggerType)
    {
        $typeNames = [
            'welcome_customer' => 'Welkom Nieuwe Klanten',
            'welcome_employee' => 'Welkom Nieuwe Medewerkers',
            'testzadel_reminder' => 'Testzadel Herinneringen',
            'birthday' => 'Verjaardag Felicitaties',
            'referral_thank_you' => 'Doorverwijzing Dankje Email',
            'klant_invitation' => 'Klant Uitnodigingen',
            'medewerker_invitation' => 'Medewerker Uitnodigingen',
        ];
        
        return $typeNames[$triggerType] ?? ucfirst(str_replace('_', ' ', $triggerType));
    }

    /**
     * Helper method om default conditions te krijgen voor trigger type
     */
    private function getDefaultConditions($triggerType)
    {
        switch ($triggerType) {
            case 'testzadel_reminder':
                return [
                    'reminder_days' => 7,
                    'frequency' => 'daily'
                ];
            case 'birthday':
                return [
                    'send_on_birthday' => true
                ];
            case 'welcome_customer':
            case 'welcome_employee':
                return [
                    'trigger_on' => str_replace('welcome_', '', $triggerType) . '_created'
                ];
            default:
                return [];
        }
    }

    /**
     * Helper method om default settings te krijgen voor trigger type
     */
    private function getDefaultSettings($triggerType)
    {
        switch ($triggerType) {
            case 'testzadel_reminder':
                return [
                    'frequency' => 'daily',
                    'max_reminders' => 3,
                    'reminder_interval' => 7
                ];
            case 'birthday':
                return [
                    'frequency' => 'daily',
                    'send_time' => '09:00'
                ];
            case 'welcome_customer':
            case 'welcome_employee':
                return [
                    'delay_minutes' => 0
                ];
            case 'referral_thank_you':
                return [
                    'send_immediately' => true,
                    'track_opens' => true,
                    'track_clicks' => true
                ];
            default:
                return ['frequency' => 'manual'];
        }
    }

    /**
     * Helper method om default trigger data te krijgen voor trigger type
     */
    private function getDefaultTriggerData($triggerType)
    {
        switch ($triggerType) {
            case 'testzadel_reminder':
                return [
                    'time' => '10:00',
                    'schedule' => 'daily',
                    'days_before_due' => 7
                ];
            case 'birthday':
                return [
                    'time' => '09:00',
                    'schedule' => 'daily'
                ];
            case 'welcome_customer':
                return [
                    'event' => 'customer_created',
                    'delay_minutes' => 0
                ];
            case 'welcome_employee':
                return [
                    'event' => 'employee_created',
                    'delay_minutes' => 0
                ];
            case 'referral_thank_you':
                return [
                    'event' => 'customer_referred',
                    'delay_minutes' => 0,
                    'automatic' => true
                ];
            default:
                return ['frequency' => 'manual'];
        }
    }

    private function createDefaultTriggers()
    {
        // Testzadel herinnering trigger
        $testzadelTemplate = EmailTemplate::where('type', EmailTemplate::TYPE_TESTZADEL_REMINDER)->first();
        if ($testzadelTemplate && !EmailTrigger::where('type', EmailTrigger::TYPE_TESTZADEL_REMINDER)->exists()) {
            EmailTrigger::create([
                'name' => 'Testzadel Herinnering (7 dagen)',
                'type' => EmailTrigger::TYPE_TESTZADEL_REMINDER,
                'email_template_id' => $testzadelTemplate->id,
                'is_active' => true,
                'conditions' => [
                    'reminder_days' => 7,
                    'frequency' => 'daily'
                ],
                'settings' => [
                    'frequency' => 'daily',
                    'max_reminders' => 3
                ]
            ]);
        }

        // Verjaardag trigger
        $birthdayTemplate = EmailTemplate::where('type', EmailTemplate::TYPE_BIRTHDAY)->first();
        if ($birthdayTemplate && !EmailTrigger::where('type', EmailTrigger::TYPE_BIRTHDAY)->exists()) {
            EmailTrigger::create([
                'name' => 'Automatische Verjaardagsmails',
                'type' => EmailTrigger::TYPE_BIRTHDAY,
                'email_template_id' => $birthdayTemplate->id,
                'is_active' => true,
                'conditions' => [
                    'send_on_birthday' => true
                ],
                'settings' => [
                    'frequency' => 'daily',
                    'send_time' => '09:00'
                ]
            ]);
        }

        // Welkom klant trigger (automatisch)
        $welcomeCustomerTemplate = EmailTemplate::where('type', EmailTemplate::TYPE_WELCOME_CUSTOMER)->first();
        if ($welcomeCustomerTemplate && !EmailTrigger::where('type', EmailTrigger::TYPE_WELCOME_CUSTOMER)->exists()) {
            EmailTrigger::create([
                'name' => 'Welkom Nieuwe Klanten (Automatisch)',
                'type' => EmailTrigger::TYPE_WELCOME_CUSTOMER,
                'email_template_id' => $welcomeCustomerTemplate->id,
                'is_active' => true,
                'conditions' => [
                    'trigger_on' => 'customer_created'
                ],
                'settings' => [
                    'delay_minutes' => 0
                ]
            ]);
        }

        // Welkom medewerker trigger (automatisch)
        $welcomeEmployeeTemplate = EmailTemplate::where('type', EmailTemplate::TYPE_WELCOME_EMPLOYEE)->first();
        if ($welcomeEmployeeTemplate && !EmailTrigger::where('type', EmailTrigger::TYPE_WELCOME_EMPLOYEE)->exists()) {
            EmailTrigger::create([
                'name' => 'Welkom Nieuwe Medewerkers (Automatisch)',
                'type' => EmailTrigger::TYPE_WELCOME_EMPLOYEE,
                'email_template_id' => $welcomeEmployeeTemplate->id,
                'is_active' => true,
                'conditions' => [
                    'trigger_on' => 'employee_created'
                ],
                'settings' => [
                    'delay_minutes' => 0
                ]
            ]);
        }

        // Klant uitnodiging trigger (handmatig)
        $klantInvitationTemplate = EmailTemplate::where('type', 'klant_invitation')->first();
        if ($klantInvitationTemplate && !EmailTrigger::where('type', EmailTrigger::TYPE_KLANT_INVITATION)->exists()) {
            EmailTrigger::create([
                'name' => 'Klant Uitnodigingen (Handmatig)',
                'type' => EmailTrigger::TYPE_KLANT_INVITATION,
                'email_template_id' => $klantInvitationTemplate->id,
                'is_active' => true,
                'conditions' => [
                    'trigger_on' => 'manual'
                ],
                'settings' => [
                    'frequency' => 'manual'
                ]
            ]);
        }

        // Medewerker uitnodiging trigger (handmatig)
        $medewerkerInvitationTemplate = EmailTemplate::where('type', 'medewerker_invitation')->first();
        if ($medewerkerInvitationTemplate && !EmailTrigger::where('type', EmailTrigger::TYPE_MEDEWERKER_INVITATION)->exists()) {
            EmailTrigger::create([
                'name' => 'Medewerker Uitnodigingen (Handmatig)',
                'type' => EmailTrigger::TYPE_MEDEWERKER_INVITATION,
                'email_template_id' => $medewerkerInvitationTemplate->id,
                'is_active' => true,
                'conditions' => [
                    'trigger_on' => 'manual'
                ],
                'settings' => [
                    'frequency' => 'manual'
                ]
            ]);
        }
    }

    private function createDefaultTemplates()
    {
        $defaultTemplates = [
            [
                'name' => 'Testzadel Herinnering',
                'type' => EmailTemplate::TYPE_TESTZADEL_REMINDER,
                'subject' => 'Herinnering: Testzadel @{{merk}} @{{model}} terugbrengen',
                'body_html' => '<h2>Beste @{{voornaam}},</h2>
<p>Je hebt een testzadel <strong>@{{merk}} @{{model}}</strong> uitgeleend op @{{uitgeleend_op}}.</p>
<p>De verwachte terugbreng datum is <strong>@{{verwachte_retour}}</strong>.</p>
<p>Kun je deze zo spoedig mogelijk terugbrengen?</p>
<p>Met vriendelijke groet,<br>@{{bedrijf_naam}}</p>',
                'description' => 'Voor uitstaande testzadel terugbreng herinneringen',
                'is_active' => true
            ],
            [
                'name' => 'Welkom Klant',
                'type' => EmailTemplate::TYPE_WELCOME_CUSTOMER,
                'subject' => 'Welkom bij @{{bedrijf_naam}}, @{{voornaam}}! 🚴‍♂️',
                'body_html' => '<h2>Welkom @{{voornaam}}!</h2>
<p>Leuk dat je een account hebt aangemaakt bij <strong>@{{bedrijf_naam}}</strong>.</p>
<p><strong>Je inloggegevens:</strong><br>
E-mail: @{{email}}<br>
Tijdelijk wachtwoord: @{{wachtwoord}}</p>
<p><a href="@{{login_url}}" style="background-color: #3B82F6; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Inloggen op je account</a></p>
<p>We kijken ernaar uit om je te helpen met de perfecte bikefit!</p>
<p>Met vriendelijke groet,<br>Het @{{bedrijf_naam}} team</p>',
                'description' => 'Automatisch verstuurd bij nieuwe klant registratie met inloggegevens',
                'is_active' => true
            ],
            [
                'name' => 'Welkom Medewerker',
                'type' => EmailTemplate::TYPE_WELCOME_EMPLOYEE,
                'subject' => 'Welkom bij het @{{bedrijf_naam}} team, @{{voornaam}}! 👥',
                'body_html' => '<h2>Welkom bij het team, @{{voornaam}}!</h2>
<p>Welkom bij <strong>@{{bedrijf_naam}}</strong> als nieuwe @{{functie}}!</p>
<p><strong>Je inloggegevens voor het systeem:</strong><br>
E-mail: @{{email}}<br>
Tijdelijk wachtwoord: @{{wachtwoord}}<br>
Rol: @{{rol}}</p>
<p><a href="@{{login_url}}" style="background-color: #10B981; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Inloggen op het systeem</a></p>
<p>We kijken ernaar uit om met je samen te werken!</p>
<p>Bij vragen kun je altijd contact opnemen met je manager.</p>
<p>Met vriendelijke groet,<br>Het @{{bedrijf_naam}} team</p>',
                'description' => 'Automatisch verstuurd bij nieuwe medewerker registratie met inloggegevens',
                'is_active' => true
            ],
            [
                'name' => 'Verjaardag Felicitaties',
                'type' => EmailTemplate::TYPE_BIRTHDAY,
                'subject' => '🎉 Gefeliciteerd met je verjaardag, @{{voornaam}}!',
                'body_html' => '<h2>Gefeliciteerd @{{voornaam}}! 🎂</h2>
<p>Van harte gefeliciteerd met je verjaardag!</p>
<p>Het hele team van <strong>@{{bedrijf_naam}}</strong> wenst je een fantastische dag toe.</p>
<p>Geniet van je speciale dag!</p>
<p>Met vriendelijke groet,<br>Het @{{bedrijf_naam}} team</p>',
                'description' => 'Automatisch verstuurd op verjaardag klant',
                'is_active' => true
            ]
        ];

        foreach ($defaultTemplates as $templateData) {
            EmailTemplate::create($templateData);
        }
    }

    /**
     * Run birthday trigger and send emails to customers with birthday today
     */
    private function runBirthdayTrigger()
    {
        try {
            // Find birthday trigger
            $birthdayTrigger = EmailTrigger::where('type', EmailTrigger::TYPE_BIRTHDAY)
                                         ->where('is_active', true)
                                         ->first();
            
            if (!$birthdayTrigger || !$birthdayTrigger->emailTemplate) {
                \Log::info('No active birthday trigger or template found');
                return 0;
            }

            // Get customers with birthday today
            $customers = \App\Models\Klant::whereMonth('geboortedatum', now()->month)
                                         ->whereDay('geboortedatum', now()->day)
                                         ->whereNotNull('email')
                                         ->get();

            $emailsSent = 0;

            foreach ($customers as $customer) {
                // Check if birthday email was already sent today
                $alreadySent = \App\Models\EmailLog::where('recipient_email', $customer->email)
                                                  ->where('trigger_name', 'birthday')
                                                  ->whereDate('created_at', today())
                                                  ->exists();

                if ($alreadySent) {
                    continue;
                }

                // Prepare variables for the email template
                $variables = [
                    'voornaam' => $customer->voornaam,
                    'naam' => $customer->naam,
                    'email' => $customer->email,
                    'bedrijf_naam' => 'Bonami Sportcoaching',
                    'datum' => now()->format('d/m/Y'),
                    'jaar' => now()->format('Y'),
                ];

                try {
                    // Send email using the template
                    $subject = $birthdayTrigger->emailTemplate->renderSubject($variables);
                    $body = $birthdayTrigger->emailTemplate->renderBody($variables);

                    // Use your existing email sending method
                    \Mail::html($body, function ($message) use ($customer, $subject) {
                        $message->to($customer->email)
                                ->subject($subject);
                    });

                    // Log the email
                    \App\Models\EmailLog::create([
                        'recipient_email' => $customer->email,
                        'subject' => $subject,
                        'template_id' => $birthdayTrigger->emailTemplate->id,
                        'trigger_name' => 'birthday',
                        'status' => 'sent',
                        'sent_at' => now(),
                        'variables' => $variables
                    ]);

                    $emailsSent++;

                } catch (\Exception $e) {
                    \Log::error('Failed to send birthday email to ' . $customer->email . ': ' . $e->getMessage());
                    
                    // Log failed email
                    \App\Models\EmailLog::create([
                        'recipient_email' => $customer->email,
                        'subject' => $subject ?? 'Birthday email',
                        'template_id' => $birthdayTrigger->emailTemplate->id,
                        'trigger_name' => 'birthday',
                        'status' => 'failed',
                        'error_message' => $e->getMessage(),
                        'variables' => $variables
                    ]);
                }
            }

            // Update trigger statistics
            $birthdayTrigger->increment('emails_sent', $emailsSent);
            $birthdayTrigger->update(['last_run_at' => now()]);

            \Log::info("Birthday trigger completed. Sent {$emailsSent} emails.");
            
            return $emailsSent;

        } catch (\Exception $e) {
            \Log::error('Birthday trigger failed: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get subscription statistics
     */
    private function getSubscriptionStats()
    {
        try {
            $totalKlanten = \App\Models\Klant::count();
            $totalMedewerkers = \App\Models\Medewerker::count();
            
            $subscribedKlanten = \App\Models\EmailSubscription::where('subscriber_type', 'klant')
                                                             ->where('status', 'subscribed')
                                                             ->count();
            $subscribedMedewerkers = \App\Models\EmailSubscription::where('subscriber_type', 'medewerker')
                                                                  ->where('status', 'subscribed')
                                                                  ->count();
            
            $unsubscribedTotal = \App\Models\EmailSubscription::where('status', 'unsubscribed')->count();
            
            return [
                'total_klanten' => $totalKlanten,
                'total_medewerkers' => $totalMedewerkers,
                'subscribed_klanten' => $subscribedKlanten,
                'subscribed_medewerkers' => $subscribedMedewerkers,
                'unsubscribed_total' => $unsubscribedTotal,
                'subscription_rate' => $totalKlanten > 0 ? round(($subscribedKlanten / $totalKlanten) * 100, 1) : 100
            ];
        } catch (\Exception $e) {
            return [
                'total_klanten' => 0,
                'total_medewerkers' => 0,
                'subscribed_klanten' => 0,
                'subscribed_medewerkers' => 0,
                'unsubscribed_total' => 0,
                'subscription_rate' => 100
            ];
        }
    }

    /**
     * Show bulk email form
     */
    public function bulkEmail()
    {
        $this->checkAdminAccess();
        
        $templates = EmailTemplate::where('is_active', true)
                                  ->whereIn('type', ['newsletter', 'custom', 'general'])
                                  ->get();
        
        $stats = $this->getSubscriptionStats();
        
        return view('admin.email-bulk', compact('templates', 'stats'));
    }

    /**
     * Send bulk email to all customers
     */
    public function sendBulkToCustomers(Request $request)
    {
        $validated = $request->validate([
            'template_id' => 'required|exists:email_templates,id',
            'subject' => 'required|string|max:255',
            'custom_message' => 'nullable|string'
        ]);

        try {
            $template = EmailTemplate::findOrFail($validated['template_id']);
            $customers = \App\Models\Klant::whereNotNull('email')->get();
            
            $emailsSent = 0;
            $emailsSkipped = 0;

            foreach ($customers as $customer) {
                // Check subscription status
                if (!\App\Models\EmailSubscription::isEmailSubscribed($customer->email)) {
                    $emailsSkipped++;
                    continue;
                }

                // Create/update subscription record
                $subscription = \App\Models\EmailSubscription::getOrCreateForEmail(
                    $customer->email, 
                    'klant', 
                    $customer->id
                );

                $this->sendBulkEmailToRecipient($customer, $template, $validated, $subscription);
                $emailsSent++;
            }

            return redirect()->back()->with('success', 
                "✅ Bulk email verstuurd naar {$emailsSent} klanten. {$emailsSkipped} overgeslagen (unsubscribed).");

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 
                "❌ Fout bij versturen bulk email: " . $e->getMessage());
        }
    }

    /**
     * Send bulk email to all employees
     */
    public function sendBulkToEmployees(Request $request)
    {
        $validated = $request->validate([
            'template_id' => 'required|exists:email_templates,id',
            'subject' => 'required|string|max:255',
            'custom_message' => 'nullable|string'
        ]);

        try {
            $template = EmailTemplate::findOrFail($validated['template_id']);
            $employees = \App\Models\Medewerker::whereNotNull('email')->get();
            
            $emailsSent = 0;
            $emailsSkipped = 0;

            foreach ($employees as $employee) {
                // Check subscription status
                if (!\App\Models\EmailSubscription::isEmailSubscribed($employee->email)) {
                    $emailsSkipped++;
                    continue;
                }

                // Create/update subscription record
                $subscription = \App\Models\EmailSubscription::getOrCreateForEmail(
                    $employee->email, 
                    'medewerker', 
                    $employee->id
                );

                $this->sendBulkEmailToRecipient($employee, $template, $validated, $subscription);
                $emailsSent++;
            }

            return redirect()->back()->with('success', 
                "✅ Bulk email verstuurd naar {$emailsSent} medewerkers. {$emailsSkipped} overgeslagen (unsubscribed).");

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 
                "❌ Fout bij versturen bulk email: " . $e->getMessage());
        }
    }

    /**
     * Send email to individual recipient with unsubscribe link
     */
    private function sendBulkEmailToRecipient($recipient, $template, $data, $subscription)
    {
        $variables = [
            'voornaam' => $recipient->voornaam ?? 'Beste klant',
            'naam' => $recipient->naam ?? '',
            'email' => $recipient->email,
            'bedrijf_naam' => 'Bonami Sportcoaching',
            'datum' => now()->format('d/m/Y'),
            'jaar' => now()->format('Y'),
            'custom_message' => $data['custom_message'] ?? '',
            'unsubscribe_url' => route('email.unsubscribe', ['token' => $subscription->unsubscribe_token])
        ];

        $subject = $template->renderSubject($variables);
        $body = $template->renderBody($variables);
        
        // Add unsubscribe footer
        $unsubscribeFooter = '
        <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #eee; font-size: 12px; color: #999; text-align: center;">
            <p>Je ontvangt deze email omdat je geabonneerd bent op onze nieuwsbrief.</p>
            <p><a href="' . $variables['unsubscribe_url'] . '" style="color: #999;">Klik hier om je af te melden</a></p>
        </div>';
        
        $body = str_replace('</body>', $unsubscribeFooter . '</body>', $body);

        \Mail::html($body, function ($message) use ($recipient, $subject) {
            $message->to($recipient->email)
                    ->subject($subject);
        });

        // Log the email
        \App\Models\EmailLog::create([
            'recipient_email' => $recipient->email,
            'subject' => $subject,
            'template_id' => $template->id,
            'trigger_name' => 'bulk_email',
            'status' => 'sent',
            'sent_at' => now(),
            'variables' => $variables
        ]);
    }

    /**
     * Show unsubscribed users management
     */
    public function unsubscribed()
    {
        $this->checkAdminAccess();
        
        $unsubscribed = \App\Models\EmailSubscription::with('subscriber')
                                                    ->where('status', 'unsubscribed')
                                                    ->orderBy('unsubscribed_at', 'desc')
                                                    ->paginate(20);
        
        $stats = $this->getSubscriptionStats();
        
        return view('admin.email-unsubscribed', compact('unsubscribed', 'stats'));
    }

    /**
     * Unsubscribe page (public)
     */
    public function unsubscribePage($token)
    {
        $subscription = \App\Models\EmailSubscription::where('unsubscribe_token', $token)->first();
        
        if (!$subscription) {
            abort(404, 'Ongeldige unsubscribe link');
        }
        
        return view('emails.unsubscribe', compact('subscription'));
    }

    /**
     * Show unsubscribe page (public)
     */
    public function showUnsubscribe($token)
    {
        $subscription = \App\Models\EmailSubscription::where('unsubscribe_token', $token)->first();
        
        if (!$subscription) {
            abort(404, 'Ongeldige unsubscribe link');
        }
        
        return view('emails.unsubscribe', compact('subscription'));
    }

    /**
     * Process unsubscribe (public)
     */
    public function processUnsubscribe(Request $request, $token)
    {
        $subscription = \App\Models\EmailSubscription::where('unsubscribe_token', $token)->first();
        
        if (!$subscription) {
            abort(404, 'Ongeldige unsubscribe link');
        }

        if (!$subscription->isSubscribed()) {
            return view('emails.unsubscribe-success', compact('subscription'));
        }

        $reason = $request->input('reason', 'Geen reden opgegeven');
        $additionalFeedback = $request->input('additional_feedback');
        
        // Combine reason and feedback
        $fullReason = $reason;
        if ($additionalFeedback) {
            $fullReason .= ' | Extra feedback: ' . $additionalFeedback;
        }
        
        $subscription->unsubscribe($fullReason);
        
        // Log this unsubscribe for analytics
        \App\Models\EmailLog::create([
            'recipient_email' => $subscription->email,
            'subject' => 'Unsubscribe action',
            'template_id' => null,
            'trigger_name' => 'unsubscribe',
            'status' => 'unsubscribed',
            'sent_at' => now(),
            'variables' => [
                'reason' => $reason,
                'additional_feedback' => $additionalFeedback,
                'subscriber_type' => $subscription->subscriber_type
            ]
        ]);
        
        return view('emails.unsubscribe-success', compact('subscription'));
    }

    /**
     * Resubscribe a user (admin only)
     */
    public function resubscribe($id)
    {
        try {
            $subscription = \App\Models\EmailSubscription::findOrFail($id);
            $subscription->resubscribe();
            
            return redirect()->back()->with('success', 
                "✅ {$subscription->email} is succesvol heraangemeld voor emails.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 
                "❌ Fout bij heraanmelden: " . $e->getMessage());
        }
    }

    /**
     * Preview email template
     */
    public function previewEmail(Request $request)
    {
        $validated = $request->validate([
            'template_id' => 'required|exists:email_templates,id',
            'subject' => 'required|string|max:255',
            'custom_message' => 'nullable|string'
        ]);

        try {
            $template = EmailTemplate::findOrFail($validated['template_id']);
            
            // Mock data for preview
            $variables = [
                'voornaam' => 'Jan',
                'naam' => 'Janssen',
                'email' => 'jan.janssen@example.com',
                'bedrijf_naam' => 'Bonami Sportcoaching',
                'datum' => now()->format('d/m/Y'),
                'jaar' => now()->format('Y'),
                'custom_message' => $validated['custom_message'] ?? '',
                'unsubscribe_url' => route('email.unsubscribe', ['token' => 'preview-token'])
            ];

            $subject = $template->renderSubject($variables);
            $body = $template->renderBody($variables);
            
            // Add unsubscribe footer for preview
            $unsubscribeFooter = '
            <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #eee; font-size: 12px; color: #999; text-align: center;">
                <p>Je ontvangt deze email omdat je geabonneerd bent op onze nieuwsbrief.</p>
                <p><a href="' . $variables['unsubscribe_url'] . '" style="color: #999;">Klik hier om je af te melden</a></p>
            </div>';
            
            $body = str_replace('</body>', $unsubscribeFooter . '</body>', $body);

            return response()->json([
                'success' => true,
                'subject' => $subject,
                'body' => $body
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Preview template for templates page
     */
    public function previewTemplate($id)
    {
        try {
            $template = \App\Models\EmailTemplate::findOrFail($id);
            
            // Mock data for preview
            $variables = [
                'voornaam' => 'Jan',
                'naam' => 'Janssen',
                'email' => 'jan.janssen@example.com',
                'bedrijf_naam' => 'Bonami Sportcoaching',
                'datum' => now()->format('d/m/Y'),
                'jaar' => now()->format('Y'),
                'merk' => 'Selle Italia',
                'model' => 'SLR Boost',
                'uitgeleend_op' => now()->subDays(7)->format('d/m/Y'),
                'unsubscribe_url' => route('email.unsubscribe', ['token' => 'preview-token'])
            ];

            $subject = $template->renderSubject($variables);
            $body = $template->renderBody($variables);

            return response()->json([
                'success' => true,
                'subject' => $subject,
                'body' => $body
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete template
     */
    public function deleteTemplate($id)
    {
        try {
            $template = \App\Models\EmailTemplate::findOrFail($id);
            $templateName = $template->name;
            $template->delete();

            return redirect()->route('admin.email.templates')
                           ->with('success', "Template '{$templateName}' succesvol verwijderd");

        } catch (\Exception $e) {
            return redirect()->route('admin.email.templates')
                           ->with('error', 'Fout bij verwijderen: ' . $e->getMessage());
        }
    }

    /**
     * Preview template for templates listing (JSON response)
     */
    public function previewTemplateJson($id)
    {
        try {
            $template = \App\Models\EmailTemplate::findOrFail($id);
            
            // Mock data for preview
            $variables = [
                'voornaam' => 'Jan',
                'naam' => 'Janssen',
                'email' => 'jan.janssen@example.com',
                'bedrijf_naam' => 'Bonami Sportcoaching',
                'datum' => now()->format('d/m/Y'),
                'jaar' => now()->format('Y'),
                'merk' => 'Selle Italia',
                'model' => 'SLR Boost',
                'uitgeleend_op' => now()->subDays(7)->format('d/m/Y'),
                'unsubscribe_url' => route('email.unsubscribe', ['token' => 'preview-token'])
            ];

            $subject = $template->renderSubject($variables);
            $body = $template->renderBody($variables);

            return response()->json([
                'success' => true,
                'subject' => $subject,
                'body' => $body
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Preview een email template
     */
    public function preview($id)
    {
        $template = EmailTemplate::findOrFail($id);
        
        // Haal demo data op voor preview
        $demoData = $this->getDemoDataForTemplate($template);
        
        // Render de template met demo data
        $renderedContent = $this->renderTemplate($template->content, $demoData);
        
        return view('admin.email-preview', [
            'template' => $template,
            'content' => $renderedContent
        ]);
    }
    
    /**
     * Haal demo data op voor template preview
     */
    private function getDemoDataForTemplate($template)
    {
        return [
            'user_name' => 'Jan Janssen',
            'user_email' => 'jan@example.com',
            'organisation_name' => auth()->user()->organisatie->naam ?? 'Performance Pulse',
            'current_date' => now()->format('d-m-Y'),
            'current_year' => now()->year,
        ];
    }
    
    /**
     * Render template met variabelen
     */
    private function renderTemplate($content, $data)
    {
        foreach ($data as $key => $value) {
            $content = str_replace('{{' . $key . '}}', $value, $content);
        }
        
        return $content;
    }
}