<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\EmailTemplate;
use App\Models\EmailSettings;
use Illuminate\Support\Facades\File;

class MigrateExistingTemplates extends Command
{
    protected $signature = 'email:migrate-templates';
    protected $description = 'Migrate existing email templates to new template system';

    public function handle()
    {
        $this->info('🔄 Migreer bestaande email templates...');
        
        $settings = EmailSettings::getSettings();
        $logoHtml = '';
        if ($settings->hasLogo()) {
            $logoHtml = '<img src="' . $settings->getLogoBase64() . '" alt="' . $settings->company_name . '" style="height: 60px; margin-bottom: 15px;">';
        }
        
        // Base template structure
        $baseTemplate = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@{{subject}}</title>
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
        .highlight { background-color: #fff3cd; padding: 15px; border-radius: 5px; border-left: 4px solid #ffc107; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            ' . $logoHtml . '
            <h1>@{{bedrijf_naam}}</h1>
        </div>
        <div class="content">
            {{CONTENT}}
        </div>
        <div class="footer">
            <p>&copy; @{{jaar}} @{{bedrijf_naam}}. Alle rechten voorbehouden.</p>
            ' . ($settings->signature ? '<p>' . $settings->signature . '</p>' : '') . '
        </div>
    </div>
</body>
</html>';

        // 1. Birthday Template
        $this->migrateTemplate(
            'birthday',
            'Verjaardag Felicitatie!',
            'Gefeliciteerd @{{voornaam}}! 🎂',
            $baseTemplate,
            '<h2>Gefeliciteerd @{{voornaam}}! 🎂</h2>
            <p>Van harte gefeliciteerd met je verjaardag!</p>
            <p>Het hele team van @{{bedrijf_naam}} wenst je een fantastische dag toe. We hopen dat al je wensen uitkomen en dat je een geweldig nieuw levensjaar tegemoet gaat.</p>
            <div class="highlight">
                <p><strong>🎁 Speciaal verjaardagscadeau:</strong><br>
                Kom langs in onze winkel en ontvang 10% korting op je volgende aankoop!</p>
            </div>
            <p>Met vriendelijke groet,<br>Het @{{bedrijf_naam}} team</p>',
            'birthday'
        );

        // 2. Welcome Customer Template  
        $this->migrateTemplate(
            'welcome_customer',
            'Welkom bij Bonami Cycling!',
            'Welkom @{{voornaam}}! Fijn dat je bij ons bent 🚴‍♂️',
            $baseTemplate,
            '<h2>Welkom @{{voornaam}}! 🚴‍♂️</h2>
            <p>Wat fijn dat je klant bent geworden bij @{{bedrijf_naam}}!</p>
            <p>We zijn gespecialiseerd in bikefits en zorgen ervoor dat jij de perfecte fietspositie krijgt. Ons ervaren team staat klaar om je te helpen bij al je fietsgerelateerde vragen.</p>
            <div class="highlight">
                <p><strong>🎯 Wat kunnen we voor je doen?</strong></p>
                <ul>
                    <li>Professionele bikefits</li>
                    <li>Testzadels om de perfecte zadel te vinden</li>
                    <li>Expert advies voor optimaal fietscomfort</li>
                </ul>
            </div>
            <a href="#" class="button">Plan je eerste bikefit</a>
            <p>Met vriendelijke groet,<br>Het @{{bedrijf_naam}} team</p>',
            'welcome_customer'
        );

        // 3. Employee Welcome Template
        $this->migrateTemplate(
            'welcome_employee', 
            'Welkom bij het team!',
            'Welkom in het team @{{voornaam}}! 👋',
            $baseTemplate,
            '<h2>Welkom in het team @{{voornaam}}! 👋</h2>
            <p>Fantastisch dat je bij @{{bedrijf_naam}} komt werken!</p>
            <p>We zijn blij je te verwelkomen in ons enthousiaste team. Samen gaan we klanten helpen om de perfecte fietspositie te vinden en hun fietsplezier te maximaliseren.</p>
            <div class="highlight">
                <p><strong>📋 Je eerste dagen:</strong></p>
                <ul>
                    <li>Introductie met het team</li>
                    <li>Training over onze bikefit procedures</li>
                    <li>Kennismaking met onze systemen</li>
                </ul>
            </div>
            <p>We kijken ernaar uit om met je samen te werken!</p>
            <p>Met vriendelijke groet,<br>Het @{{bedrijf_naam}} team</p>',
            'welcome_employee'
        );

        // 4. Testzadel Reminder Template
        $this->migrateTemplate(
            'testzadel_reminder',
            'Testzadel terugbrengen',
            'Herinnering: testzadel @{{merk}} @{{model}} terugbrengen 🚴‍♂️',
            $baseTemplate,
            '<h2>Hoi @{{voornaam}}! 🚴‍♂️</h2>
            <p>Je hebt op @{{uitgeleend_op}} een testzadel van ons geleend om te testen.</p>
            <div class="highlight">
                <p><strong>📋 Testzadel details:</strong></p>
                <ul>
                    <li><strong>Merk:</strong> @{{merk}}</li>
                    <li><strong>Model:</strong> @{{model}}</li>
                    <li><strong>Uitgeleend op:</strong> @{{uitgeleend_op}}</li>
                    <li><strong>Verwachte retour:</strong> @{{verwachte_retour}}</li>
                </ul>
            </div>
            <p>We hopen dat je tevreden bent met de testzadel! Zou je hem kunnen terugbrengen zodat andere klanten er ook van kunnen profiteren?</p>
            <p><strong>Heb je de perfecte zadel gevonden?</strong> Laat het ons weten, dan kunnen we hem voor je bestellen!</p>
            <a href="#" class="button">Contact opnemen</a>
            <p>Met vriendelijke groet,<br>Het @{{bedrijf_naam}} team</p>',
            'testzadel_reminder'
        );

        // 5. Account Created Template
        $this->migrateTemplate(
            'account_created',
            'Account aangemaakt',
            'Je account is aangemaakt @{{voornaam}}! ✅',
            $baseTemplate,
            '<h2>Je account is klaar @{{voornaam}}! ✅</h2>
            <p>Je account bij @{{bedrijf_naam}} is succesvol aangemaakt.</p>
            <div class="highlight">
                <p><strong>📧 Account details:</strong></p>
                <ul>
                    <li><strong>Email:</strong> @{{email}}</li>
                    <li><strong>Aangemaakt op:</strong> @{{datum}}</li>
                </ul>
            </div>
            <p>Je kunt nu inloggen en gebruik maken van al onze diensten. We staan klaar om je te helpen met de perfecte bikefit!</p>
            <a href="#" class="button">Inloggen op je account</a>
            <p>Met vriendelijke groet,<br>Het @{{bedrijf_naam}} team</p>',
            'general'
        );

        $this->info('✅ Alle templates succesvol gemigreerd naar het nieuwe systeem!');
        $this->info('🎯 Ga naar /admin/email/templates om ze te bekijken en aan te passen.');
        
        return Command::SUCCESS;
    }

    private function migrateTemplate($name, $subject, $description, $baseTemplate, $content, $type)
    {
        // Check if template already exists
        $existing = EmailTemplate::where('type', $type)->first();
        
        if ($existing) {
            $this->warn("⚠️  Template {$name} bestaat al, wordt overgeslagen.");
            return;
        }

        $fullHtml = str_replace('{{CONTENT}}', $content, $baseTemplate);

        EmailTemplate::create([
            'name' => ucfirst(str_replace('_', ' ', $name)),
            'subject' => $subject,
            'body_html' => $fullHtml,
            'type' => $type,
            'is_active' => true,
            'description' => $description
        ]);

        $this->info("✅ {$name} template gemigreerd");
    }
}