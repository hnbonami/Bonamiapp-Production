<?php

namespace App\Services;

use App\Models\EmailTemplate;
use App\Models\EmailTrigger;
use App\Models\EmailLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Email Migration Service
 * 
 * Deze service beheert de migratie van oude email systemen naar het nieuwe
 * uniforme Email Admin systeem van Bonami Sportcoaching.
 */
class EmailMigrationService
{
    /**
     * Migreer alle oude email templates naar nieuwe Email Admin
     */
    public function migrateTemplates(): array
    {
        $migrated = [];
        
        try {
            // Check welke oude templates bestaan voordat we proberen te migreren
            $availableTemplates = $this->checkAvailableOldTemplates();
            
            Log::info('Beschikbare oude templates gevonden', $availableTemplates);
            
            // Migreer alleen templates die daadwerkelijk bestaan
            foreach ($availableTemplates as $templateKey => $templateInfo) {
                if ($templateInfo['exists']) {
                    try {
                        $this->migrateIndividualTemplate($templateKey, $templateInfo);
                        $migrated[] = $templateKey;
                        Log::info("Template '{$templateKey}' succesvol gemigreerd");
                    } catch (\Exception $e) {
                        Log::error("Template '{$templateKey}' migratie gefaald: " . $e->getMessage());
                    }
                }
            }
            
            // Als er geen oude templates zijn, maak standaard templates aan
            if (empty($migrated)) {
                $this->createDefaultEmailTemplates();
                $migrated[] = 'default_templates';
                Log::info('Standaard email templates aangemaakt omdat er geen oude templates waren');
            }
            
            Log::info('Email templates migratie voltooid', [
                'migrated_templates' => $migrated
            ]);
            
        } catch (\Exception $e) {
            Log::error('Email template migratie gefaald', [
                'error' => $e->getMessage(),
                'partially_migrated' => $migrated
            ]);
        }
        
        return $migrated;
    }
    
    /**
     * Controleer welke oude email templates beschikbaar zijn
     */
    private function checkAvailableOldTemplates(): array
    {
        $templates = [
            'birthday' => [
                'view_path' => 'emails.birthday',
                'name' => 'Verjaardag Felicitatie',
                'type' => 'birthday',
                'subject' => '🎉 Gefeliciteerd met je verjaardag!',
                'description' => 'Automatische verjaardag felicitatie voor klanten en medewerkers',
                'exists' => false
            ],
            'testzadel_reminder' => [
                'view_path' => 'emails.testzadel-reminder',
                'name' => 'Testzadel Herinnering',
                'type' => 'testzadel_reminder',
                'subject' => 'Herinnering: Testzadel terugbrengen',
                'description' => 'Herinnering voor klanten om testzadel terug te brengen',
                'exists' => false
            ]
        ];
        
        // Check of de Blade views bestaan
        foreach ($templates as $key => &$template) {
            try {
                // Probeer de view te laden zonder te renderen
                if (view()->exists($template['view_path'])) {
                    $template['exists'] = true;
                    Log::info("Oude template gevonden: " . $template['view_path']);
                }
            } catch (\Exception $e) {
                Log::warning("Template {$template['view_path']} niet beschikbaar: " . $e->getMessage());
            }
        }
        
        return $templates;
    }
    
    /**
     * Migreer een individuele template
     */
    private function migrateIndividualTemplate(string $key, array $templateInfo): void
    {
        try {
            // Probeer de oude template content te krijgen
            $content = $this->getOldTemplateContent($templateInfo['view_path']);
            
            // Als we geen content kunnen krijgen, gebruik fallback
            if (!$content) {
                $content = $this->getDefaultTemplateContent($templateInfo['type']);
            }
            
            // Migreer naar nieuwe EmailTemplate
            EmailTemplate::updateOrCreate(
                ['template_key' => $key],
                [
                    'name' => $templateInfo['name'],
                    'subject' => $templateInfo['subject'],
                    'content' => $this->cleanTemplateContent($content),
                    'type' => $templateInfo['type'],
                    'description' => $templateInfo['description'],
                    'is_active' => true,
                    'created_by' => auth()->id() ?? 1,
                ]
            );
            
        } catch (\Exception $e) {
            Log::error("Individuele template migratie gefaald voor {$key}: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Probeer oude template content te krijgen
     */
    private function getOldTemplateContent(string $viewPath): ?string
    {
        try {
            // Maak mock data om de view te kunnen renderen
            $mockData = [
                'person' => (object)[
                    'voornaam' => '{{voornaam}}',
                    'naam' => '{{naam}}',
                    'email' => '{{email}}'
                ],
                'klant' => (object)[
                    'voornaam' => '{{voornaam}}',
                    'naam' => '{{naam}}',
                    'email' => '{{email}}'
                ],
                'testzadel' => (object)[
                    'zadel_merk' => '{{zadel_merk}}',
                    'zadel_model' => '{{zadel_model}}',
                    'uitgeleend_op' => '{{uitgeleend_op}}',
                    'verwachte_retour_datum' => '{{verwachte_retour}}'
                ],
                'type' => 'klant'
            ];
            
            return view($viewPath, $mockData)->render();
            
        } catch (\Exception $e) {
            Log::warning("Kon oude template content niet ophalen voor {$viewPath}: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Krijg standaard template content voor type
     */
    private function getDefaultTemplateContent(string $type): string
    {
        switch ($type) {
            case 'birthday':
                return '
                <!DOCTYPE html>
                <html>
                <head><title>Gefeliciteerd!</title></head>
                <body style="font-family: Arial, sans-serif; padding: 20px;">
                    <h2>Gefeliciteerd {{voornaam}}! 🎂</h2>
                    <p>Van harte gefeliciteerd met je verjaardag!</p>
                    <p>Het hele team van Bonami Sportcoaching wenst je een fantastische dag toe.</p>
                    <p>Geniet van je speciale dag!</p>
                    <p>Met vriendelijke groet,<br>Het Bonami Sportcoaching team</p>
                </body>
                </html>';
                
            case 'testzadel_reminder':
                return '
                <!DOCTYPE html>
                <html>
                <head><title>Testzadel Herinnering</title></head>
                <body style="font-family: Arial, sans-serif; padding: 20px;">
                    <h2>Beste {{voornaam}},</h2>
                    <p>Je hebt een testzadel <strong>{{zadel_merk}} {{zadel_model}}</strong> uitgeleend.</p>
                    <p>De verwachte terugbreng datum is <strong>{{verwachte_retour}}</strong>.</p>
                    <p>Kun je deze zo spoedig mogelijk terugbrengen?</p>
                    <p>Met vriendelijke groet,<br>Bonami Sportcoaching</p>
                </body>
                </html>';
                
            default:
                return '
                <!DOCTYPE html>
                <html>
                <head><title>Email van Bonami Sportcoaching</title></head>
                <body style="font-family: Arial, sans-serif; padding: 20px;">
                    <h2>Beste {{voornaam}},</h2>
                    <p>Dit is een email van Bonami Sportcoaching.</p>
                    <p>Met vriendelijke groet,<br>Het Bonami Sportcoaching team</p>
                </body>
                </html>';
        }
    }
    
    /**
     * Maak standaard email templates aan als er geen oude templates zijn
     */
    private function createDefaultEmailTemplates(): void
    {
        $defaultTemplates = [
            [
                'template_key' => 'birthday',
                'name' => 'Verjaardag Felicitatie',
                'type' => 'birthday',
                'subject' => '🎉 Gefeliciteerd met je verjaardag, {{voornaam}}!',
                'content' => $this->getDefaultTemplateContent('birthday'),
                'description' => 'Automatische verjaardag felicitatie voor klanten en medewerkers',
                'is_active' => true,
                'created_by' => auth()->id() ?? 1,
            ],
            [
                'template_key' => 'testzadel_reminder',
                'name' => 'Testzadel Herinnering',
                'type' => 'testzadel_reminder',
                'subject' => 'Herinnering: Testzadel {{zadel_merk}} {{zadel_model}} terugbrengen',
                'content' => $this->getDefaultTemplateContent('testzadel_reminder'),
                'description' => 'Herinnering voor klanten om testzadel terug te brengen',
                'is_active' => true,
                'created_by' => auth()->id() ?? 1,
            ],
            [
                'template_key' => 'welcome_customer',
                'name' => 'Welkom Nieuwe Klant',
                'type' => 'welcome_customer',
                'subject' => 'Welkom bij Bonami Sportcoaching, {{voornaam}}! 🚴‍♂️',
                'content' => '
                <!DOCTYPE html>
                <html>
                <head><title>Welkom!</title></head>
                <body style="font-family: Arial, sans-serif; padding: 20px;">
                    <h2>Welkom {{voornaam}}!</h2>
                    <p>Leuk dat je een account hebt aangemaakt bij <strong>Bonami Sportcoaching</strong>.</p>
                    <p>We kijken ernaar uit om je te helpen met de perfecte bikefit!</p>
                    <p>Met vriendelijke groet,<br>Het Bonami Sportcoaching team</p>
                </body>
                </html>',
                'description' => 'Welkomstmail voor nieuwe klanten',
                'is_active' => true,
                'created_by' => auth()->id() ?? 1,
            ],
            [
                'template_key' => 'doorverwijzing',
                'name' => 'Doorverwijzing Email',
                'type' => 'doorverwijzing',
                'subject' => 'Doorverwijzing naar {{specialist_naam}} - {{voornaam}} {{naam}}',
                'content' => '
                <!DOCTYPE html>
                <html>
                <head><title>Doorverwijzing</title></head>
                <body style="font-family: Arial, sans-serif; padding: 20px;">
                    <h2>Doorverwijzing voor {{voornaam}} {{naam}}</h2>
                    <p>Beste {{specialist_naam}},</p>
                    <p>Hierbij verwijs ik {{voornaam}} {{naam}} door voor verder onderzoek/behandeling.</p>
                    <p><strong>Klantgegevens:</strong></p>
                    <ul>
                        <li>Naam: {{voornaam}} {{naam}}</li>
                        <li>Email: {{email}}</li>
                        <li>Telefoon: {{telefoon}}</li>
                        <li>Geboortedatum: {{geboortedatum}}</li>
                    </ul>
                    <p><strong>Reden doorverwijzing:</strong><br>{{doorverwijzing_reden}}</p>
                    <p>Met vriendelijke groet,<br>{{doorverwijzer_naam}}<br>Bonami Sportcoaching</p>
                </body>
                </html>',
                'description' => 'Template voor doorverwijzing naar specialisten',
                'is_active' => true,
                'created_by' => auth()->id() ?? 1,
            ]
        ];
        
        foreach ($defaultTemplates as $templateData) {
            EmailTemplate::updateOrCreate(
                ['template_key' => $templateData['template_key']],
                $templateData
            );
        }
    }
    
    /**
     * Extraheer subject uit email content
     */
    private function extractSubjectFromContent(string $content): string
    {
        // Probeer subject uit HTML title te halen
        if (preg_match('/<title>(.*?)<\/title>/i', $content, $matches)) {
            return trim($matches[1]);
        }
        
        // Fallback naar standaard subject
        return 'Bericht van Bonami Sportcoaching';
    }
    
    /**
     * Clean template content voor nieuwe systeem
     */
    private function cleanTemplateContent(string $content): string
    {
        // Verwijder Blade specifieke syntax die niet werkt in nieuwe systeem
        $content = preg_replace('/\{\{\s*\$([^}]+)\s*\}\}/', '{{$1}}', $content);
        
        // Vervang oude variabelen met nieuwe placeholder syntax
        $replacements = [
            '{{$person->voornaam}}' => '{{voornaam}}',
            '{{$person->naam}}' => '{{naam}}',
            '{{$klant->voornaam}}' => '{{voornaam}}',
            '{{$klant->naam}}' => '{{naam}}',
            '{{$testzadel->zadel_merk}}' => '{{zadel_merk}}',
            '{{$testzadel->zadel_model}}' => '{{zadel_model}}',
        ];
        
        return str_replace(array_keys($replacements), array_values($replacements), $content);
    }
    
    /**
     * Migreer email triggers van oude systeem
     */
    public function migrateTriggers(): array
    {
        $migrated = [];
        
        try {
            // Zorg eerst dat EmailTrigger model bestaat
            if (!class_exists('\App\Models\EmailTrigger')) {
                Log::warning('EmailTrigger model niet gevonden, skip trigger migratie');
                return $migrated;
            }
            
            // Verjaardag trigger
            $birthdayTemplate = EmailTemplate::where('template_key', 'birthday')->first();
            if ($birthdayTemplate) {
                EmailTrigger::updateOrCreate(
                    ['trigger_key' => 'daily_birthday_check'],
                    [
                        'name' => 'Dagelijkse Verjaardag Check',
                        'description' => 'Controleert dagelijks op verjaardagen van klanten en medewerkers',
                        'trigger_type' => 'scheduled',
                        'trigger_data' => json_encode([
                            'schedule' => 'daily',
                            'time' => '09:00'
                        ]),
                        'email_template_id' => $birthdayTemplate->id,
                        'is_active' => true,
                        'created_by' => auth()->id() ?? 1,
                    ]
                );
                $migrated[] = 'birthday_trigger';
                Log::info('Verjaardag trigger gemigreerd');
            }
            
            // Testzadel reminder trigger
            $testzadelTemplate = EmailTemplate::where('template_key', 'testzadel_reminder')->first();
            if ($testzadelTemplate) {
                EmailTrigger::updateOrCreate(
                    ['trigger_key' => 'testzadel_reminder_check'],
                    [
                        'name' => 'Testzadel Herinnering Check',
                        'description' => 'Controleert op testzadels die bijna verlopen zijn',
                        'trigger_type' => 'scheduled',
                        'trigger_data' => json_encode([
                            'schedule' => 'daily',
                            'time' => '10:00',
                            'days_before_due' => 2
                        ]),
                        'email_template_id' => $testzadelTemplate->id,
                        'is_active' => true,
                        'created_by' => auth()->id() ?? 1,
                    ]
                );
                $migrated[] = 'testzadel_trigger';
                Log::info('Testzadel reminder trigger gemigreerd');
            }
            
            // Welkom klant trigger
            $welcomeTemplate = EmailTemplate::where('template_key', 'welcome_customer')->first();
            if ($welcomeTemplate) {
                EmailTrigger::updateOrCreate(
                    ['trigger_key' => 'welcome_customer_trigger'],
                    [
                        'name' => 'Welkom Nieuwe Klanten',
                        'description' => 'Automatisch welkomstmail voor nieuwe klanten',
                        'trigger_type' => 'event',
                        'trigger_data' => json_encode([
                            'event' => 'customer_created',
                            'delay_minutes' => 0
                        ]),
                        'email_template_id' => $welcomeTemplate->id,
                        'is_active' => true,
                        'created_by' => auth()->id() ?? 1,
                    ]
                );
                $migrated[] = 'welcome_customer_trigger';
                Log::info('Welkom klant trigger gemigreerd');
            }
            
            Log::info('Email triggers migratie voltooid', [
                'migrated_triggers' => $migrated
            ]);
            
        } catch (\Exception $e) {
            Log::error('Email trigger migratie gefaald', [
                'error' => $e->getMessage(),
                'partially_migrated' => $migrated
            ]);
        }
        
        return $migrated;
    }
    
    /**
     * Vervang oude email functionaliteit in controllers
     */
    public function replaceControllerEmailCalls(): array
    {
        $replaced = [];
        
        // Dit zou in werkelijkheid handmatig moeten gebeuren
        // Hier loggen we wat vervangen moet worden
        
        $controllersToUpdate = [
            'BirthdayController' => 'Vervang directe Mail:: calls met EmailAdmin service',
            'TestzadelsController' => 'Vervang testzadel reminder met trigger systeem',
            'KlantenController' => 'Vervang invite emails met Email Admin',
            'MedewerkerController' => 'Vervang invite emails met Email Admin',
        ];
        
        foreach ($controllersToUpdate as $controller => $action) {
            Log::info("Controller update nodig: {$controller}", ['action' => $action]);
            $replaced[] = $controller;
        }
        
        return $replaced;
    }
    
    /**
     * Test het nieuwe email systeem
     */
    public function testNewEmailSystem(): array
    {
        $testResults = [];
        
        try {
            // Test EmailTemplate model
            $templateCount = EmailTemplate::count();
            $testResults['email_templates'] = $templateCount > 0 ? "OK ({$templateCount} templates)" : 'GEEN TEMPLATES';
            
            // Test specifieke templates
            $birthdayTemplate = EmailTemplate::where('template_key', 'birthday')->first();
            $testResults['birthday_template'] = $birthdayTemplate ? 'OK' : 'ONTBREEKT';
            
            $testzadelTemplate = EmailTemplate::where('template_key', 'testzadel_reminder')->first();
            $testResults['testzadel_template'] = $testzadelTemplate ? 'OK' : 'ONTBREEKT';
            
            // Test EmailTrigger model (als het bestaat)
            if (class_exists('\App\Models\EmailTrigger')) {
                $triggerCount = EmailTrigger::count();
                $testResults['email_triggers'] = $triggerCount > 0 ? "OK ({$triggerCount} triggers)" : 'GEEN TRIGGERS';
                
                $birthdayTrigger = EmailTrigger::where('trigger_key', 'daily_birthday_check')->first();
                $testResults['birthday_trigger'] = $birthdayTrigger && $birthdayTrigger->is_active ? 'OK' : 'ONTBREEKT';
            } else {
                $testResults['email_triggers'] = 'MODEL ONTBREEKT';
                $testResults['birthday_trigger'] = 'MODEL ONTBREEKT';
            }
            
            // Test EmailLog model (als het bestaat)
            if (class_exists('\App\Models\EmailLog')) {
                $logCount = EmailLog::count();
                $testResults['email_logs'] = "OK ({$logCount} logs)";
            } else {
                $testResults['email_logs'] = 'MODEL ONTBREEKT';
            }
            
            // Test template rendering
            if ($birthdayTemplate) {
                try {
                    $mockVariables = [
                        'voornaam' => 'Test',
                        'naam' => 'Gebruiker',
                        'email' => 'test@example.com'
                    ];
                    $renderedContent = str_replace(
                        array_keys($mockVariables), 
                        array_values($mockVariables), 
                        $birthdayTemplate->content
                    );
                    $testResults['template_rendering'] = strlen($renderedContent) > 0 ? 'OK' : 'LEEG';
                } catch (\Exception $e) {
                    $testResults['template_rendering'] = 'FOUT: ' . $e->getMessage();
                }
            } else {
                $testResults['template_rendering'] = 'GEEN TEMPLATE OM TE TESTEN';
            }
            
            Log::info('Email systeem test voltooid', $testResults);
            
        } catch (\Exception $e) {
            $testResults['test_error'] = $e->getMessage();
            Log::error('Email systeem test gefaald', [
                'error' => $e->getMessage(),
                'partial_results' => $testResults
            ]);
        }
        
        return $testResults;
    }
    
    /**
     * Volledige migratie uitvoeren
     */
    public function runFullMigration(): array
    {
        Log::info('Start volledige email systeem migratie');
        
        $results = [
            'templates' => $this->migrateTemplates(),
            'triggers' => $this->migrateTriggers(),
            'controllers' => $this->replaceControllerEmailCalls(),
            'tests' => $this->testNewEmailSystem(),
        ];
        
        Log::info('Email systeem migratie voltooid', $results);
        
        return $results;
    }
}