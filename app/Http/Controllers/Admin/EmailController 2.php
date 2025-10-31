<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use App\Models\EmailSettings;
use App\Models\EmailTrigger;
use App\Models\EmailLog;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EmailController extends Controller
{
    public function index()
    {
        return view('admin.email-simple');
    }

    public function templates()
    {
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
        $template = EmailTemplate::findOrFail($id);
        return view('admin.email-edit-template', compact('template'));
    }

    public function updateTemplate(Request $request, $id)
    {
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
        $template = EmailTemplate::findOrFail($id);
        $template->delete();

        return redirect()->route('admin.email.templates')
                        ->with('success', 'Template succesvol verwijderd!');
    }

    public function settings()
    {
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
        $triggers = EmailTrigger::with('emailTemplate')->latest()->get();
        $templates = EmailTemplate::active()->get();
        $statistics = (new EmailService())->getStatistics();
        
        return view('admin.email-triggers', compact('triggers', 'templates', 'statistics'));
    }

    public function logs()
    {
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
            $birthdaysSent = $emailService->runBirthdayEmails();
            
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
                "✅ Automatische triggers zijn succesvol geconfigureerd! Het systeem is nu volledig automatisch.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 
                "❌ Fout bij instellen triggers: " . $e->getMessage());
        }
    }

    public function editTrigger($id)
    {
        $trigger = EmailTrigger::with('emailTemplate')->findOrFail($id);
        $templates = EmailTemplate::active()->get();
        
        return view('admin.email-edit-trigger', compact('trigger', 'templates'));
    }

    public function updateTrigger(Request $request, $id)
    {
        $trigger = EmailTrigger::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|max:255',
            'email_template_id' => 'required|exists:email_templates,id',
            'is_active' => 'sometimes|boolean',
            'reminder_days' => 'nullable|integer|min:1|max:365',
            'frequency' => 'required|in:hourly,daily,weekly',
            'max_reminders' => 'nullable|integer|min:1|max:10'
        ]);

        // Build conditions based on trigger type
        $conditions = $trigger->conditions ?? [];
        
        if ($trigger->type === EmailTrigger::TYPE_TESTZADEL_REMINDER) {
            $conditions['reminder_days'] = (int)$request->input('reminder_days', 7);
            $conditions['frequency'] = $request->input('frequency', 'daily');
        }

        // Build settings
        $settings = $trigger->settings ?? [];
        $settings['frequency'] = $request->input('frequency', 'daily');
        
        if ($request->filled('max_reminders')) {
            $settings['max_reminders'] = (int)$request->input('max_reminders');
        }

        $trigger->update([
            'name' => $validated['name'],
            'email_template_id' => $validated['email_template_id'],
            'is_active' => $request->has('is_active'),
            'conditions' => $conditions,
            'settings' => $settings
        ]);

        return redirect()->route('admin.email.triggers')
                        ->with('success', 'Trigger succesvol bijgewerkt!');
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
                    'reminder_days' => 7, // Kan aangepast worden naar 21
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

        // Welkom klant trigger
        $welcomeTemplate = EmailTemplate::where('type', EmailTemplate::TYPE_WELCOME_CUSTOMER)->first();
        if ($welcomeTemplate && !EmailTrigger::where('type', EmailTrigger::TYPE_WELCOME_CUSTOMER)->exists()) {
            EmailTrigger::create([
                'name' => 'Welkom Nieuwe Klanten',
                'type' => EmailTrigger::TYPE_WELCOME_CUSTOMER,
                'email_template_id' => $welcomeTemplate->id,
                'is_active' => true,
                'conditions' => [
                    'trigger_on' => 'customer_created'
                ],
                'settings' => [
                    'delay_minutes' => 0 // Direct versturen
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
                'name' => 'Welkom Nieuwe Klant',
                'type' => EmailTemplate::TYPE_WELCOME_CUSTOMER,
                'subject' => 'Welkom bij @{{bedrijf_naam}}, @{{voornaam}}!',
                'body_html' => '<h2>Welkom @{{voornaam}}!</h2>
<p>Leuk dat je een account hebt aangemaakt bij <strong>@{{bedrijf_naam}}</strong>.</p>
<p>We kijken ernaar uit om je te helpen met de perfecte bikefit.</p>
<p>Heb je vragen? Neem gerust contact met ons op!</p>
<p>Met vriendelijke groet,<br>Het @{{bedrijf_naam}} team</p>',
                'description' => 'Automatisch verstuurd bij nieuwe klant registratie',
                'is_active' => true
            ],
            [
                'name' => 'Verjaardag Felicitatie',
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
}