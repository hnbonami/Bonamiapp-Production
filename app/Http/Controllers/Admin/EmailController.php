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
        
        // ⚠️ BELANGRIJK: Email templates zijn ALTIJD toegankelijk voor admin/organisatie_admin
        // Feature "sjablonen" controleert alleen of organisatie deze feature heeft GEKOCHT
        // Maar email templates zijn basis functionaliteit die altijd moet werken
        
        // Superadmin ziet BEIDE Performance Pulse standaard templates EN eigen organisatie templates IN APARTE TABELLEN
        if (auth()->user()->role === 'superadmin') {
            // Haal Performance Pulse standaard templates op (UNIEKE, GEEN DUPLICATEN)
            $performancePulseTemplates = EmailTemplate::whereNull('organisatie_id')
                                                      ->where('is_default', 1) // Gebruik 1 in plaats van true
                                                      ->orderBy('type')
                                                      ->orderBy('name')
                                                      ->get();
            
            // Haal eigen organisatie templates op (als superadmin een organisatie heeft)
            $organisatieTemplates = collect();
            if (auth()->user()->organisatie_id) {
                $organisatieTemplates = EmailTemplate::where('organisatie_id', auth()->user()->organisatie_id)
                                                    ->where(function($query) {
                                                        $query->where('is_default', 0)
                                                              ->orWhereNull('is_default');
                                                    })
                                                    ->orderBy('type')
                                                    ->orderBy('name')
                                                    ->get();
            }
            
            \Log::info('🔐 Superadmin bekijkt templates (APARTE TABELLEN)', [
                'user_id' => auth()->id(),
                'performance_pulse_count' => $performancePulseTemplates->count(),
                'organisatie_count' => $organisatieTemplates->count(),
                'performance_pulse_ids' => $performancePulseTemplates->pluck('id')->toArray(),
                'organisatie_ids' => $organisatieTemplates->pluck('id')->toArray(),
            ]);
            
            // Geef flag mee voor twee aparte tabellen in de view
            $showSeparateTables = true;
            
            // Voor backward compatibility: geef ook lege $templates collection mee
            // View kan checken op $showSeparateTables en dan de aparte collections gebruiken
            $templates = collect();
            
            return view('admin.email-templates', compact('performancePulseTemplates', 'organisatieTemplates', 'showSeparateTables', 'templates'));
        } else {
            // Organisatie admins zien ALLEEN hun eigen gekloneerde templates
            $templates = EmailTemplate::where('organisatie_id', auth()->user()->organisatie_id)
                                      ->orderBy('type')
                                      ->orderBy('name')
                                      ->get();
            
            \Log::info('👥 Organisatie admin bekijkt eigen templates', [
                'user_id' => auth()->id(),
                'organisatie_id' => auth()->user()->organisatie_id,
                'templates_count' => $templates->count()
            ]);
            
            // Check of organisatie al templates heeft
            if ($templates->isEmpty()) {
                // Nog geen templates - toon waarschuwing en knop om te klonen
                $needsCloning = true;
                
                \Log::warning('⚠️ Organisatie heeft nog geen email templates', [
                    'organisatie_id' => auth()->user()->organisatie_id,
                    'user_id' => auth()->id()
                ]);
                
                return view('admin.email-templates', compact('templates', 'needsCloning'));
            }
            
            return view('admin.email-templates', compact('templates'));
        }
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

        // BEVEILIGING: Check of de gebruiker deze template mag bewerken
        if (auth()->user()->role === 'superadmin') {
            // Superadmin mag:
            // 1. Performance Pulse templates (organisatie_id = null EN is_default = true)
            // 2. Templates van eigen organisatie (als superadmin een organisatie heeft)
            
            $isPerformancePulse = ($template->organisatie_id === null && $template->is_default == true);
            $isOwnOrganisation = ($template->organisatie_id !== null && $template->organisatie_id === auth()->user()->organisatie_id);
            
            // Debug logging
            \Log::info('� Superadmin template edit check', [
                'user_id' => auth()->id(),
                'user_organisatie_id' => auth()->user()->organisatie_id,
                'template_id' => $template->id,
                'template_name' => $template->name,
                'template_organisatie_id' => $template->organisatie_id,
                'template_is_default' => $template->is_default,
                'isPerformancePulse' => $isPerformancePulse,
                'isOwnOrganisation' => $isOwnOrganisation,
            ]);
            
            if (!$isPerformancePulse && !$isOwnOrganisation) {
                \Log::warning('🚫 Superadmin probeerde template van andere organisatie te bewerken', [
                    'user_id' => auth()->id(),
                    'template_id' => $template->id,
                    'template_organisatie_id' => $template->organisatie_id,
                ]);
                
                return redirect()->route('admin.email.templates')
                    ->with('error', '❌ Je kunt alleen Performance Pulse standaard templates en je eigen templates bewerken.');
            }
        } else {
            // Organisatie admins mogen ALLEEN hun eigen templates bewerken
            if ($template->organisatie_id !== auth()->user()->organisatie_id) {
                \Log::warning('🚫 Organisatie admin probeerde template van andere organisatie te bewerken', [
                    'user_id' => auth()->id(),
                    'user_organisatie_id' => auth()->user()->organisatie_id,
                    'template_id' => $template->id,
                    'template_organisatie_id' => $template->organisatie_id
                ]);
                
                return redirect()->route('admin.email.templates')
                    ->with('error', '❌ Je kunt alleen je eigen templates bewerken. Wil je een Performance Pulse template aanpassen? Maak eerst een kopie.');
            }
        }

        $validated = $request->validate([
            'name' => 'required|max:255',
            'subject' => 'required|max:255',
            'body_html' => 'required',
            'description' => 'nullable|max:1000',
            'is_active' => 'sometimes|boolean'
        ]);

        $validated['is_active'] = $request->has('is_active');

        $template->update($validated);
        
        \Log::info('✅ Email template bijgewerkt', [
            'template_id' => $template->id,
            'user_id' => auth()->id(),
            'user_role' => auth()->user()->role,
            'is_default' => $template->is_default
        ]);

        return redirect()->route('admin.email.templates')
                        ->with('success', 'Template succesvol bijgewerkt!');
    }

    public function createTemplate()
    {
        $templateTypes = [
            'testzadel_reminder' => 'Testzadel Herinnering',
            'welcome_customer' => 'Welkom Klant',
            'welcome_employee' => 'Welkom Medewerker',
            'birthday' => 'Verjaardag',
            'referral' => 'Referral',
        ];
        
        // Standaard content voor elk template type
        $templateDefaults = [
            'testzadel_reminder' => [
                'subject' => 'Herinnering: Testzadel @{{merk}} @{{model}} 🚴',
                'content' => '<p><strong>Dag @{{voornaam}},</strong></p>
<p><strong>Graag horen we hoe het testzadel je bevalt!</strong></p>
<p>We zien dat je het zadel al een tijdje in gebruik hebt. Zou je het daarom willen terugbrengen? Andere klanten wachten inmiddels om dit type zadel te testen, en we willen hen niet te lang laten wachten.</p>

<div class="testzadel-info">
    <h3 style="margin-top: 0; color: #92400e;">📋 Testzadel Informatie</h3>
    
    <div class="info-row">
        <strong>Zadel:</strong>
        <span>@{{merk}} @{{model}}</span>
    </div>
    
    <div class="info-row">
        <strong>Uitgeleend op:</strong>
        <span>@{{uitgeleend_op}}</span>
    </div>
    
    <div class="info-row">
        <strong>Verwachte retour:</strong>
        <span style="font-weight: bold; color: #dc2626;">@{{verwachte_retour}}</span>
    </div>
</div>

<div class="action-needed">
    <h3 style="margin-top: 0; color: #2563eb;">🚴‍♀️ Je hebt de volgende opties om het zadel terug te brengen:</h3>
    
    <p><strong>Langskomen & Service:</strong> Kom langs bij ons. Dit is het beste als je nog vragen hebt of als je een definitief zadel wilt monteren. We hebben de zadels meestal op voorraad.</p>
    
    <p><strong>Deponeren:</strong> Indien er niemand thuis is, mag je het zadel altijd in de brievenbus deponeren.</p>
</div>

<div style="background-color: #f0f9ff; border: 1px solid #bfdbfe; padding: 20px; border-radius: 8px; margin: 25px 0;">
    <h3 style="margin-top: 0; color: #1e40af;">🤔 Wat is de volgende stap voor jou?</h3>
    
    <p><strong>Tevreden?</strong> Spring even langs, dan monteren we meteen je nieuwe, definitieve zadel.</p>
    
    <p><strong>Nog niet perfect?</strong> Laat het ons zeker weten! Dan gaan we samen op zoek naar een andere oplossing die wel 100% past.</p>
</div>

<p><strong>Laat ons snel iets weten over je bevindingen, zodat we het traject kunnen afronden.</strong></p>

<p style="margin-top: 40px;"><strong>Sportieve groeten,</strong><br>
Team @{{bedrijf_naam}}</p>',
            ],
            'welcome_customer' => [
                'subject' => 'Welkom bij @{{bedrijf_naam}}, @{{voornaam}}! �',
                'content' => '<h2>Welkom, @{{voornaam}}! 👋</h2>

<p>We zijn blij je te verwelkomen bij <strong>@{{bedrijf_naam}}</strong>!</p>

<p>Je account is succesvol aangemaakt en je kunt nu inloggen met de volgende gegevens:</p>

<div style="background: #f0f9ff; padding: 20px; margin: 25px 0; border-radius: 4px;">
    <p style="margin: 0 0 10px 0;"><strong>📧 Email:</strong> @{{email}}</p>
    <p style="margin: 0;"><strong>🔑 Tijdelijk wachtwoord:</strong> @{{temporary_password}}</p>
</div>

<p><strong>Belangrijk:</strong> Wijzig je wachtwoord na je eerste login voor optimale beveiliging.</p>

<div style="text-align: center; margin: 30px 0;">
    <a href="@{{login_url}}" style="background: #c8e1eb; color: #1a1a1a; text-decoration: none; padding: 12px 30px; border-radius: 4px; display: inline-block; font-weight: 500;">Inloggen</a>
</div>

<p>Met vriendelijke groet,<br>
Het @{{bedrijf_naam}} team</p>',
            ],
            'welcome_employee' => [
                'subject' => 'Welkom bij het @{{bedrijf_naam}} team! 👥',
                'content' => '<h2>Welkom bij het team, @{{voornaam}}! 👥</h2>

<p>We zijn verheugd je te verwelkomen bij <strong>@{{bedrijf_naam}}</strong> als nieuwe medewerker!</p>

<p>Je account is aangemaakt en je hebt nu toegang tot ons systeem:</p>

<div style="background: #f0f9ff; padding: 20px; margin: 25px 0; border-radius: 4px;">
    <p style="margin: 0 0 10px 0;"><strong>📧 Email:</strong> @{{email}}</p>
    <p style="margin: 0 0 10px 0;"><strong>🔑 Tijdelijk wachtwoord:</strong> @{{temporary_password}}</p>
    <p style="margin: 0;"><strong>👤 Rol:</strong> @{{rol}}</p>
</div>

<p><strong>Belangrijk:</strong> Wijzig je wachtwoord na je eerste login voor optimale beveiliging.</p>

<div style="text-align: center; margin: 30px 0;">
    <a href="@{{login_url}}" style="background: #c8e1eb; color: #1a1a1a; text-decoration: none; padding: 12px 30px; border-radius: 4px; display: inline-block; font-weight: 500;">Inloggen</a>
</div>

<p>We kijken ernaar uit om met je samen te werken!</p>

<p>Met vriendelijke groet,<br>
Het @{{bedrijf_naam}} team</p>',
            ],
            'birthday' => [
                'subject' => '🎉 Gefeliciteerd met je verjaardag, @{{voornaam}}!',
                'content' => '<h2>Gefeliciteerd @{{voornaam}}! 🎂</h2>
<p>Van harte gefeliciteerd met je verjaardag!</p>
<p>Het hele team van <strong>@{{bedrijf_naam}}</strong> wenst je een fantastische dag toe.</p>
<p>Geniet van je speciale dag!</p>
<p>Met vriendelijke groet,<br>Het @{{bedrijf_naam}} team</p>',
            ],
            'referral' => [
                'subject' => 'Bedankt voor je aanbeveling! 🙏',
                'content' => '<h2>Beste @{{voornaam}},</h2>
<p>Hartelijk dank voor het aanbevelen van <strong>@{{bedrijf_naam}}</strong>!</p>
<p>We waarderen je vertrouwen enorm.</p>
<p>Met vriendelijke groet,<br>Het @{{bedrijf_naam}} team</p>',
            ],
        ];
        
        $settings = EmailSettings::getSettings();
        
        // Haal logo positie en tekstkleur op
        $logoPosition = $settings->email_logo_position ?? 'left';
        $textColor = $settings->email_text_color ?? '#ffffff';
        
        // Default modern email template with logo
        $logoHtml = '';
        if ($settings->hasLogo()) {
            $logoHtml = '<img src="' . $settings->getLogoBase64() . '" alt="' . $settings->company_name . '" style="height: 60px; margin-bottom: 15px; display: inline-block;">';
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
        .header { background: linear-gradient(135deg, ' . $settings->primary_color . ' 0%, ' . $settings->secondary_color . ' 100%); padding: 40px 30px; text-align: ' . $logoPosition . '; }
        .header h1 { color: ' . $textColor . '; margin: 0; font-size: 28px; font-weight: 300; }
        .content { padding: 40px 30px; }
        .content h2 { color: #333333; margin-top: 0; }
        .content p { color: #666666; line-height: 1.6; }
        .highlight { background: #f8f9fa; border-left: 4px solid #c8e1eb; padding: 20px; margin: 25px 0; border-radius: 4px; }
        .button { display: inline-block; padding: 14px 28px; background: #c8e1eb; color: #1a1a1a; text-decoration: none; border-radius: 8px; font-weight: 600; margin: 20px 0; }
        .testzadel-info { background: linear-gradient(135deg, #fef3cd 0%, #fefce8 100%); border-left: 4px solid #d97706; padding: 25px; margin: 30px 0; border-radius: 8px; box-shadow: 0 2px 8px rgba(217, 119, 6, 0.1); }
        .info-row { display: flex; justify-content: space-between; margin-bottom: 10px; padding: 8px 0; border-bottom: 1px solid #fed7aa; }
        .info-row:last-child { border-bottom: none; margin-bottom: 0; }
        .action-needed { background-color: #fef2f2; border: 1px solid #fecaca; padding: 20px; border-radius: 8px; margin: 25px 0; }
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

        return view('admin.email-create-template', compact('templateTypes', 'defaultTemplate', 'settings', 'templateDefaults'));
    }

public function storeTemplate(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'slug' => 'nullable|string|max:255|unique:email_templates,slug',
        'type' => 'nullable|string|max:255',
        'subject' => 'required|string|max:255',
        'content' => 'nullable|string',
        'body_html' => 'nullable|string', // Ook body_html accepteren van formulier
        'description' => 'nullable|string',
        'is_active' => 'nullable|boolean',
    ]);
    
    // Auto-generate slug from name if not provided
    if (empty($validated['slug'])) {
        $validated['slug'] = \Str::slug($validated['name']);
    }
    
    // Set default values
    if (!isset($validated['type']) || empty($validated['type'])) {
        $validated['type'] = 'algemeen';
    }
    
    // Bepaal de content die gewrapt moet worden
    // Probeer eerst body_html (uit formulier), dan content, dan default
    $contentToWrap = $validated['body_html'] ?? $validated['content'] ?? '<p>Beste @{{naam}},</p><p>Uw bericht hier...</p><p>Met vriendelijke groet,<br>@{{bedrijf_naam}}</p>';
    
    // Haal de huisstijl template op voor deze organisatie
    $organisatie = \App\Models\Organisatie::find(auth()->user()->organisatie_id);
    
    // Wrap de content in de huisstijl template als deze bestaat
    if ($organisatie && !empty($organisatie->email_template_html)) {
        // Vervang {{content}} in de huisstijl template met de daadwerkelijke content
        $validated['body_html'] = str_replace('{{content}}', $contentToWrap, $organisatie->email_template_html);
        
        \Log::info('Template wrapped met huisstijl', [
            'organisatie_id' => $organisatie->id,
            'heeft_huisstijl' => true,
        ]);
    } else {
        // Geen huisstijl template, gebruik alleen de content
        $validated['body_html'] = $contentToWrap;
        
        \Log::info('Template zonder huisstijl', [
            'organisatie_id' => auth()->user()->organisatie_id,
            'heeft_huisstijl' => false,
        ]);
    }
    
    // Verwijder content om dubbele kolom error te voorkomen
    unset($validated['content']);
    
    if (!isset($validated['is_active'])) {
        $validated['is_active'] = true;
    }
    
    // NIEUWE TEMPLATES KRIJGEN ALTIJD EEN ORGANISATIE_ID
    // Tenzij dit een superadmin is die expliciet een Performance Pulse template aanmaakt
    if (auth()->user()->role === 'superadmin' && $request->has('create_as_default_template')) {
        // Superadmin maakt bewust een nieuwe Performance Pulse standaard template aan
        // Dit moet expliciet worden aangegeven via een checkbox in het formulier
        $validated['organisatie_id'] = null;
        $validated['is_default'] = true;
        
        \Log::info('🔐 Superadmin maakt nieuwe Performance Pulse standaard template', [
            'user_id' => auth()->id(),
            'template_name' => $validated['name']
        ]);
    } else {
        // ALLE andere templates (ook van superadmin) krijgen organisatie_id
        // Superadmin werkt normaal gezien binnen een organisatie context
        if (!auth()->user()->organisatie_id) {
            \Log::error('❌ Gebruiker heeft geen organisatie_id', [
                'user_id' => auth()->id(),
                'user_role' => auth()->user()->role
            ]);
            
            return redirect()->route('admin.email.templates')
                ->with('error', '❌ Je moet gekoppeld zijn aan een organisatie om templates aan te maken.');
        }
        
        // Normale organisatie template (inclusief superadmin die binnen organisatie werkt)
        $validated['organisatie_id'] = auth()->user()->organisatie_id;
        $validated['is_default'] = false;
        
        \Log::info('👥 Template aangemaakt voor organisatie', [
            'user_id' => auth()->id(),
            'user_role' => auth()->user()->role,
            'organisatie_id' => auth()->user()->organisatie_id,
            'template_name' => $validated['name']
        ]);
    }
    
    $template = EmailTemplate::create($validated);
    
    \Log::info('Nieuwe email template aangemaakt', [
        'template_id' => $template->id,
        'template_name' => $template->name,
        'body_html_length' => strlen($template->body_html ?? ''),
        'user_id' => auth()->id(),
    ]);
    
    return redirect()->route('admin.email.templates')
        ->with('success', '✅ Email template succesvol aangemaakt!');
}    /**
     * Kloon een Performance Pulse template naar organisatie template
     * Organisaties kunnen zo een kopie maken van standaard templates om aan te passen
     */
    public function cloneTemplate($id)
    {
        $this->checkAdminAccess();
        
        try {
            $sourceTemplate = EmailTemplate::findOrFail($id);
            
            // Check dat dit een Performance Pulse template is
            if (!$sourceTemplate->isDefaultTemplate()) {
                \Log::warning('🚫 Poging om niet-default template te klonen', [
                    'user_id' => auth()->id(),
                    'template_id' => $sourceTemplate->id
                ]);
                
                return redirect()->route('admin.email.templates')
                    ->with('error', '❌ Je kunt alleen Performance Pulse templates klonen.');
            }
            
            // Check dat user een organisatie admin is (geen superadmin)
            if (auth()->user()->role === 'superadmin') {
                return redirect()->route('admin.email.templates')
                    ->with('error', '❌ Superadmin hoeft geen templates te klonen.');
            }
            
            // Check of er al een custom template bestaat voor dit type
            $existingCustom = EmailTemplate::where('type', $sourceTemplate->type)
                                          ->where('organisatie_id', auth()->user()->organisatie_id)
                                          ->first();
            
            if ($existingCustom) {
                return redirect()->route('admin.email.templates')
                    ->with('warning', '⚠️ Je hebt al een custom template voor dit type. Bewerk die template in plaats van een nieuwe kopie te maken.');
            }
            
            // Maak een kopie van de template voor deze organisatie
            $clonedTemplate = EmailTemplate::create([
                'name' => $sourceTemplate->name . ' (Custom)',
                'slug' => $sourceTemplate->slug . '-custom-' . auth()->user()->organisatie_id,
                'type' => $sourceTemplate->type,
                'subject' => $sourceTemplate->subject,
                'body_html' => $sourceTemplate->body_html,
                'description' => $sourceTemplate->description . ' (Aangepaste versie voor ' . auth()->user()->organisatie->naam . ')',
                'is_active' => true,
                'is_default' => false,
                'organisatie_id' => auth()->user()->organisatie_id,
                'parent_template_id' => $sourceTemplate->id, // Link naar originele template
            ]);
            
            \Log::info('✅ Performance Pulse template gekloond naar organisatie', [
                'source_template_id' => $sourceTemplate->id,
                'cloned_template_id' => $clonedTemplate->id,
                'organisatie_id' => auth()->user()->organisatie_id,
                'user_id' => auth()->id()
            ]);
            
            return redirect()->route('admin.email.templates.edit', $clonedTemplate->id)
                ->with('success', '✅ Template succesvol gekloond! Je kunt deze nu aanpassen naar wens.');
            
        } catch (\Exception $e) {
            \Log::error('❌ Failed to clone template: ' . $e->getMessage(), [
                'template_id' => $id,
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('admin.email.templates')
                ->with('error', '❌ Er ging iets mis bij het klonen van de template: ' . $e->getMessage());
        }
    }

    /**
     * Initialiseer email templates voor een organisatie
     * Kloont alle 6 Performance Pulse standaard templates naar de organisatie
     */
    public function initializeTemplates()
    {
        $this->checkAdminAccess();
        
        try {
            // Check dat user een organisatie admin is (geen superadmin)
            if (auth()->user()->role === 'superadmin') {
                return redirect()->route('admin.email.templates')
                    ->with('error', '❌ Superadmin gebruikt de Performance Pulse templates direct.');
            }
            
            $organisatieId = auth()->user()->organisatie_id;
            
            // Check of organisatie al templates heeft
            $existingCount = EmailTemplate::where('organisatie_id', $organisatieId)->count();
            
            if ($existingCount > 0) {
                return redirect()->route('admin.email.templates')
                    ->with('warning', '⚠️ Je organisatie heeft al email templates. Gebruik deze in plaats van nieuwe te maken.');
            }
            
            \Log::info('🔄 Initialiseren email templates voor organisatie', [
                'organisatie_id' => $organisatieId,
                'user_id' => auth()->id()
            ]);
            
            // Haal alle Performance Pulse standaard templates op
            $defaultTemplates = EmailTemplate::whereNull('organisatie_id')
                                            ->where('is_default', true)
                                            ->where('is_active', true)
                                            ->get();
            
            if ($defaultTemplates->isEmpty()) {
                \Log::error('❌ Geen Performance Pulse templates gevonden om te klonen');
                return redirect()->route('admin.email.templates')
                    ->with('error', '❌ Er zijn geen standaard templates beschikbaar. Neem contact op met de systeembeheerder.');
            }
            
            $clonedCount = 0;
            
            foreach ($defaultTemplates as $defaultTemplate) {
                // Maak een kopie van de template voor deze organisatie
                $clonedTemplate = EmailTemplate::create([
                    'name' => $defaultTemplate->name,
                    'slug' => $defaultTemplate->slug . '-org-' . $organisatieId,
                    'type' => $defaultTemplate->type,
                    'subject' => $defaultTemplate->subject,
                    'body_html' => $defaultTemplate->body_html,
                    'description' => $defaultTemplate->description,
                    'is_active' => true,
                    'is_default' => false,
                    'organisatie_id' => $organisatieId,
                    'parent_template_id' => $defaultTemplate->id,
                ]);
                
                \Log::info("✅ Template gekloond: {$defaultTemplate->name}", [
                    'source_id' => $defaultTemplate->id,
                    'clone_id' => $clonedTemplate->id
                ]);
                
                $clonedCount++;
            }
            
            \Log::info('✅ Email templates geïnitialiseerd', [
                'organisatie_id' => $organisatieId,
                'templates_cloned' => $clonedCount
            ]);
            
            return redirect()->route('admin.email.templates')
                ->with('success', "✅ Perfect! {$clonedCount} email templates zijn aangemaakt voor jouw organisatie. Je kunt deze nu naar wens aanpassen.");
            
        } catch (\Exception $e) {
            \Log::error('❌ Failed to initialize templates: ' . $e->getMessage(), [
                'organisatie_id' => auth()->user()->organisatie_id,
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('admin.email.templates')
                ->with('error', '❌ Er ging iets mis bij het initialiseren van templates: ' . $e->getMessage());
        }
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
        $organisatie = auth()->user()->organisatie;
        
        return view('admin.email-settings', compact('settings', 'organisatie'));
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            // Bedrijfsinformatie voor emails (nieuwe velden)
            'bedrijf_naam' => 'nullable|string|max:255',
            'website_url' => 'nullable|url|max:255',
            'email_from_name' => 'nullable|string|max:255',
            'email_from_address' => 'nullable|email|max:255',
            'email_signature' => 'nullable|string',
            
            // Bestaande email settings velden
            'company_name' => 'required|string|max:255',
            'logo' => 'nullable|image|max:2048',
            'primary_color' => 'required|string|max:7',
            'secondary_color' => 'required|string|max:7',
            'text_color' => 'nullable|string|max:7',
            'logo_position' => 'nullable|in:left,center,right',
            'footer_text' => 'nullable|string',
            'signature' => 'nullable|string',
        ]);
        
        // Update organisatie velden (voor @{{bedrijf_naam}} etc. in emails)
        $organisatie = auth()->user()->organisatie;
        if ($organisatie) {
            $organisatie->update([
                'bedrijf_naam' => $validated['bedrijf_naam'] ?? $organisatie->naam,
                'website_url' => $validated['website_url'],
                'email_from_name' => $validated['email_from_name'],
                'email_from_address' => $validated['email_from_address'],
                'email_signature' => $validated['email_signature'],
            ]);
            
            \Log::info('📧 Organisatie email info bijgewerkt', [
                'organisatie_id' => $organisatie->id,
                'bedrijf_naam' => $validated['bedrijf_naam'],
            ]);
        }
        
        // Set defaults voor nieuwe velden
        $validated['email_text_color'] = $validated['text_color'] ?? '#ffffff';
        $validated['email_logo_position'] = $validated['logo_position'] ?? 'left';
        
        // Verwijder de oude keys en organisatie keys (die zijn al opgeslagen)
        unset($validated['text_color']);
        unset($validated['logo_position']);
        unset($validated['bedrijf_naam']);
        unset($validated['website_url']);
        unset($validated['email_from_name']);
        unset($validated['email_from_address']);
        unset($validated['email_signature']);
        
        $settings = EmailSettings::getSettings();
        
        // Handle logo upload
        $logoPath = null;
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($settings->logo_path && Storage::disk('public')->exists($settings->logo_path)) {
                Storage::disk('public')->delete($settings->logo_path);
            }
            
            // Store new logo
            try {
                $logoPath = $request->file('logo')->store('email-logos', 'public');
                $validated['logo_path'] = $logoPath;
                
                \Log::info('Logo uploaded successfully: ' . $logoPath);
                
            } catch (\Exception $e) {
                \Log::error('Logo upload failed: ' . $e->getMessage());
                return redirect()->back()->withErrors(['logo' => 'Logo upload mislukt: ' . $e->getMessage()]);
            }
        }

        $settings->update($validated);
        
        \Log::info('Email settings bijgewerkt', [
            'organisatie_id' => auth()->user()->organisatie_id,
            'user_id' => auth()->id(),
            'email_logo_position' => $validated['email_logo_position'],
            'email_text_color' => $validated['email_text_color'],
        ]);
        
        // 🔄 UPDATE ALLE BESTAANDE TEMPLATES VAN DEZE ORGANISATIE
        $this->updateExistingTemplatesWithNewBranding($organisatie, $settings, $logoPath);
        
        return redirect()->route('admin.email.settings')
                        ->with('success', '✅ Email instellingen succesvol bijgewerkt en toegepast op alle templates!');
    }
    
    /**
     * Update alle bestaande templates met nieuwe branding instellingen
     * 
     * @param \App\Models\Organisatie $organisatie
     * @param \App\Models\EmailSettings $settings
     * @param string|null $newLogoPath
     * @return void
     */
    private function updateExistingTemplatesWithNewBranding($organisatie, $settings, $newLogoPath = null)
    {
        if (!$organisatie) {
            \Log::warning('⚠️ Geen organisatie gevonden voor template update');
            return;
        }
        
        // Bepaal welke templates moeten worden bijgewerkt
        if (auth()->user()->role === 'superadmin') {
            // Superadmin: update alleen eigen aangemaakte templates (NIET Performance Pulse standaard)
            $templates = EmailTemplate::where('organisatie_id', $organisatie->id)
                                      ->where('is_default', false)
                                      ->get();
            
            \Log::info('🔐 Superadmin update eigen templates', [
                'organisatie_id' => $organisatie->id,
                'templates_count' => $templates->count()
            ]);
        } else {
            // Organisatie admin: update ALLE templates van de organisatie
            $templates = EmailTemplate::where('organisatie_id', $organisatie->id)->get();
            
            \Log::info('👥 Organisatie admin update alle templates', [
                'organisatie_id' => $organisatie->id,
                'templates_count' => $templates->count()
            ]);
        }
        
        if ($templates->isEmpty()) {
            \Log::info('ℹ️ Geen templates gevonden om bij te werken');
            return;
        }
        
        $updatedCount = 0;
        $logoBase64 = null;
        
        // Genereer logo base64 als er een nieuw logo is
        if ($newLogoPath && Storage::disk('public')->exists($newLogoPath)) {
            try {
                $logoContent = Storage::disk('public')->get($newLogoPath);
                $mimeType = Storage::disk('public')->mimeType($newLogoPath);
                $logoBase64 = 'data:' . $mimeType . ';base64,' . base64_encode($logoContent);
            } catch (\Exception $e) {
                \Log::error('❌ Kon logo niet converteren naar base64: ' . $e->getMessage());
            }
        } elseif ($settings->logo_path && Storage::disk('public')->exists($settings->logo_path)) {
            // Gebruik bestaand logo
            try {
                $logoContent = Storage::disk('public')->get($settings->logo_path);
                $mimeType = Storage::disk('public')->mimeType($settings->logo_path);
                $logoBase64 = 'data:' . $mimeType . ';base64,' . base64_encode($logoContent);
            } catch (\Exception $e) {
                \Log::error('❌ Kon bestaand logo niet converteren naar base64: ' . $e->getMessage());
            }
        }
        
        foreach ($templates as $template) {
            try {
                $bodyHtml = $template->body_html;
                $updated = false;
                
                // Update kleuren in de HTML
                $bodyHtml = preg_replace(
                    '/background:\s*linear-gradient\([^)]+\)/i',
                    'background: linear-gradient(135deg, ' . $settings->primary_color . ' 0%, ' . $settings->secondary_color . ' 100%)',
                    $bodyHtml,
                    -1,
                    $colorCount
                );
                if ($colorCount > 0) $updated = true;
                
                // Update tekstkleur in header
                $bodyHtml = preg_replace(
                    '/(\.header\s+h1[^}]*color:\s*)[^;]+/i',
                    '$1' . $settings->email_text_color,
                    $bodyHtml,
                    -1,
                    $textColorCount
                );
                if ($textColorCount > 0) $updated = true;
                
                // Update logo positie
                $bodyHtml = preg_replace(
                    '/(\.header[^}]*text-align:\s*)[^;]+/i',
                    '$1' . $settings->email_logo_position,
                    $bodyHtml,
                    -1,
                    $logoPositionCount
                );
                if ($logoPositionCount > 0) $updated = true;
                
                // Update logo src als er een nieuw logo is
                if ($logoBase64) {
                    $bodyHtml = preg_replace(
                        '/(<img[^>]*src=")[^"]+("[^>]*alt="[^"]*"[^>]*>)/i',
                        '$1' . $logoBase64 . '$2',
                        $bodyHtml,
                        -1,
                        $logoCount
                    );
                    if ($logoCount > 0) $updated = true;
                }
                
                // Update bedrijfsnaam in template
                if ($organisatie->bedrijf_naam) {
                    // Vervang hardcoded bedrijfsnamen met placeholder
                    $bodyHtml = str_replace(
                        ['Bonami Sportcoaching', 'Level Up Cycling', 'Performance Pulse'],
                        '@{{bedrijf_naam}}',
                        $bodyHtml
                    );
                    $updated = true;
                }
                
                // Update footer text
                if ($settings->footer_text) {
                    $bodyHtml = preg_replace(
                        '/(Met vriendelijke groet,<br>)[^<]+/i',
                        '$1' . $settings->footer_text,
                        $bodyHtml,
                        -1,
                        $footerCount
                    );
                    if ($footerCount > 0) $updated = true;
                }
                
                // Sla wijzigingen op als er iets is veranderd
                if ($updated) {
                    $template->update(['body_html' => $bodyHtml]);
                    $updatedCount++;
                    
                    \Log::info('✅ Template bijgewerkt met nieuwe branding', [
                        'template_id' => $template->id,
                        'template_name' => $template->name
                    ]);
                }
                
            } catch (\Exception $e) {
                \Log::error('❌ Fout bij updaten template: ' . $e->getMessage(), [
                    'template_id' => $template->id,
                    'template_name' => $template->name
                ]);
            }
        }
        
        \Log::info('🎨 Branding update voltooid', [
            'organisatie_id' => $organisatie->id,
            'templates_checked' => $templates->count(),
            'templates_updated' => $updatedCount
        ]);
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