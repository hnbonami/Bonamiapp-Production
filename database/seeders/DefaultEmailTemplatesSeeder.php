<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmailTemplate;

class DefaultEmailTemplatesSeeder extends Seeder
{
    /**
     * Seed standaard Performance Pulse email templates
     */
    public function run(): void
    {
        \Log::info('🌱 Seeding standaard Performance Pulse email templates...');
        
        $templates = [
            [
                'name' => 'Performance Pulse - Welkom Klant',
                'type' => 'welcome_customer',
                'subject' => 'Welkom bij @{{bedrijf_naam}}! 🎉',
                'description' => 'Standaard welkomstmail voor nieuwe klanten',
                'body_html' => $this->getWelcomeCustomerTemplate(),
            ],
            [
                'name' => 'Performance Pulse - Welkom Medewerker',
                'type' => 'welcome_employee',
                'subject' => 'Welkom in het team van @{{bedrijf_naam}}! 👋',
                'description' => 'Standaard welkomstmail voor nieuwe medewerkers',
                'body_html' => $this->getWelcomeEmployeeTemplate(),
            ],
            [
                'name' => 'Performance Pulse - Testzadel Herinnering',
                'type' => 'testzadel_reminder',
                'subject' => 'Herinnering: Testzadel @{{merk}} @{{model}} 🚴',
                'description' => 'Automatische herinnering voor uitgeleende testzadels',
                'body_html' => $this->getTestzadelReminderTemplate(),
            ],
            [
                'name' => 'Performance Pulse - Verjaardag',
                'type' => 'birthday',
                'subject' => 'Gefeliciteerd met je verjaardag, @{{voornaam}}! 🎂',
                'description' => 'Verjaardagswensen voor klanten',
                'body_html' => $this->getBirthdayTemplate(),
            ],
            [
                'name' => 'Performance Pulse - Bedankt voor Doorverwijzing',
                'type' => 'referral_thank_you',
                'subject' => 'Bedankt voor je doorverwijzing! 🙏',
                'description' => 'Bedankmail voor klanten die anderen doorverwijzen',
                'body_html' => $this->getReferralThankYouTemplate(),
            ],
            [
                'name' => 'Performance Pulse - Algemene Notificatie',
                'type' => 'general_notification',
                'subject' => 'Update van @{{bedrijf_naam}}',
                'description' => 'Algemene notificatie template',
                'body_html' => $this->getGeneralNotificationTemplate(),
            ],
        ];
        
        foreach ($templates as $templateData) {
            EmailTemplate::updateOrCreate(
                [
                    'type' => $templateData['type'],
                    'organisatie_id' => null,
                    'is_default' => true,
                ],
                [
                    'name' => $templateData['name'],
                    'subject' => $templateData['subject'],
                    'description' => $templateData['description'],
                    'body_html' => $templateData['body_html'],
                    'is_active' => true,
                ]
            );
            
            \Log::info('✅ Template aangemaakt/bijgewerkt: ' . $templateData['name']);
        }
        
        \Log::info('🎉 Standaard email templates succesvol geseeded!');
    }
    
    private function getWelcomeCustomerTemplate(): string
    {
        $logoUrl = asset('images/performance-pulse-logo.png');
        return '<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f7fa; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #c8e1eb 0%, #a8d5e2 100%); padding: 40px 30px; text-align: center; }
        .header h1 { color: #1a1a1a; margin: 0; font-size: 28px; }
        .content { padding: 40px 30px; color: #333; }
        .highlight { background: #f8f9fa; border-left: 4px solid #c8e1eb; padding: 20px; margin: 25px 0; border-radius: 4px; }
        .button { display: inline-block; padding: 14px 28px; background: #c8e1eb; color: #1a1a1a; text-decoration: none; border-radius: 8px; font-weight: 600; margin: 20px 0; }
        .footer { background: #f8f9fa; padding: 30px; text-align: center; color: #6c757d; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="' . $logoUrl . '" alt="Performance Pulse Logo" style="max-width: 70px; height: auto; margin-bottom: 15px;">
            <h1>⚡ Performance Pulse</h1>
            <p style="color: #333; margin-top: 8px;">Powered by @{{bedrijf_naam}}</p>
        </div>
        <div class="content">
            <h2>Welkom, @{{voornaam}}! 👋</h2>
            <p>We zijn blij je te verwelkomen bij <strong>@{{bedrijf_naam}}</strong>!</p>
            <p>Je account is succesvol aangemaakt en je kunt nu inloggen met de volgende gegevens:</p>
            <div class="highlight">
                <p style="margin-bottom: 10px;"><strong>📧 Email:</strong> @{{email}}</p>
                <p style="margin: 0;"><strong>🔑 Tijdelijk wachtwoord:</strong> <code style="background: #e9ecef; padding: 4px 8px; border-radius: 4px;">@{{temporary_password}}</code></p>
            </div>
            <p><strong>Belangrijk:</strong> Wijzig je wachtwoord na je eerste login voor optimale beveiliging.</p>
            <a href="@{{website_url}}/login" class="button">Inloggen</a>
            <p>Heb je vragen? Neem gerust contact met ons op!</p>
            <p>Met sportieve groet,<br>Het team van @{{bedrijf_naam}}</p>
        </div>
        <div class="footer">
            <p>© @{{jaar}} Performance Pulse - Jouw partner in sportprestaties</p>
            <p style="margin-top: 10px;"><a href="@{{website_url}}" style="color: #6c757d;">Website</a> • <a href="@{{unsubscribe_url}}" style="color: #6c757d;">Uitschrijven</a></p>
        </div>
    </div>
</body>
</html>';
    }
    
    private function getWelcomeEmployeeTemplate(): string
    {
        $logoUrl = asset('images/performance-pulse-logo.png');
        return '<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f7fa; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #c8e1eb 0%, #a8d5e2 100%); padding: 40px 30px; text-align: center; }
        .header h1 { color: #1a1a1a; margin: 0; font-size: 28px; }
        .content { padding: 40px 30px; color: #333; }
        .highlight { background: #f8f9fa; border-left: 4px solid #c8e1eb; padding: 20px; margin: 25px 0; border-radius: 4px; }
        .button { display: inline-block; padding: 14px 28px; background: #c8e1eb; color: #1a1a1a; text-decoration: none; border-radius: 8px; font-weight: 600; margin: 20px 0; }
        .footer { background: #f8f9fa; padding: 30px; text-align: center; color: #6c757d; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="' . $logoUrl . '" alt="Performance Pulse Logo" style="max-width: 70px; height: auto; margin-bottom: 15px;">
            <h1>⚡ Performance Pulse</h1>
            <p style="color: #333; margin-top: 8px;">Powered by @{{bedrijf_naam}}</p>
        </div>
        <div class="content">
            <h2>Welkom in het team, @{{voornaam}}! 🎉</h2>
            <p>Geweldig dat je deel uitmaakt van <strong>@{{bedrijf_naam}}</strong>!</p>
            <p>Je medewerker account is klaar voor gebruik. Log in met deze gegevens:</p>
            <div class="highlight">
                <p style="margin-bottom: 10px;"><strong>📧 Email:</strong> @{{email}}</p>
                <p style="margin: 0;"><strong>🔑 Tijdelijk wachtwoord:</strong> <code style="background: #e9ecef; padding: 4px 8px; border-radius: 4px;">@{{temporary_password}}</code></p>
            </div>
            <p>Na je eerste login kun je direct aan de slag!</p>
            <a href="@{{website_url}}/login" class="button">Inloggen</a>
            <p>Succes en veel plezier!<br>Het team van @{{bedrijf_naam}}</p>
        </div>
        <div class="footer">
            <p>© @{{jaar}} Performance Pulse</p>
        </div>
    </div>
</body>
</html>';
    }
    
    private function getTestzadelReminderTemplate(): string
    {
        $logoUrl = asset('images/performance-pulse-logo.png');
        return '<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f7fa; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #c8e1eb 0%, #a8d5e2 100%); padding: 40px 30px; text-align: center; }
        .header h1 { color: #1a1a1a; margin: 0; font-size: 28px; }
        .content { padding: 40px 30px; color: #333; }
        .highlight { background: #f8f9fa; border-left: 4px solid #c8e1eb; padding: 20px; margin: 25px 0; border-radius: 4px; }
        .footer { background: #f8f9fa; padding: 30px; text-align: center; color: #6c757d; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="' . $logoUrl . '" alt="Performance Pulse Logo" style="max-width: 70px; height: auto; margin-bottom: 15px;">
            <h1>⚡ Performance Pulse</h1>
            <p style="color: #333; margin-top: 8px;">Powered by @{{bedrijf_naam}}</p>
        </div>
        <div class="content">
            <h2>Herinnering: Testzadel 🚴</h2>
            <p>Beste @{{voornaam}},</p>
            <p>Je hebt een testzadel van ons uitgeleend. We hopen dat je hier goed op kunt rijden!</p>
            <div class="highlight">
                <p><strong>Zadel:</strong> @{{merk}} @{{model}}</p>
                <p><strong>Uitgeleend op:</strong> @{{uitgeleend_op}}</p>
            </div>
            <p><strong>⏰ Let op:</strong> Kun je het zadel binnenkort terugbrengen? Bedankt!</p>
            <p>Met sportieve groet,<br>@{{bedrijf_naam}}</p>
        </div>
        <div class="footer">
            <p>© @{{jaar}} Performance Pulse</p>
        </div>
    </div>
</body>
</html>';
    }
    
    private function getBirthdayTemplate(): string
    {
        $logoUrl = asset('images/performance-pulse-logo.png');
        return '<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f7fa; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #c8e1eb 0%, #a8d5e2 100%); padding: 40px 30px; text-align: center; }
        .header h1 { color: #1a1a1a; margin: 0; font-size: 28px; }
        .content { padding: 40px 30px; color: #333; text-align: center; }
        .highlight { background: #f8f9fa; border-left: 4px solid #c8e1eb; padding: 20px; margin: 25px 0; border-radius: 4px; }
        .footer { background: #f8f9fa; padding: 30px; text-align: center; color: #6c757d; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="' . $logoUrl . '" alt="Performance Pulse Logo" style="max-width: 70px; height: auto; margin-bottom: 15px;">
            <h1>⚡ Performance Pulse</h1>
        </div>
        <div class="content">
            <h2>🎂 Gefeliciteerd, @{{voornaam}}!</h2>
            <div style="font-size: 48px; margin: 20px 0;">🎉 🎈 🎁</div>
            <p style="font-size: 20px; font-weight: 600;">Vandaag is het jouw dag!</p>
            <p>Het hele team van <strong>@{{bedrijf_naam}}</strong> wenst je een fantastische verjaardag toe!</p>
            <div class="highlight">
                <p style="font-size: 18px; margin: 0;"><strong>@{{leeftijd}}</strong> jaar jong! 💪</p>
            </div>
            <p>Geniet van je dag!<br>Het team van @{{bedrijf_naam}}</p>
        </div>
        <div class="footer">
            <p>© @{{jaar}} Performance Pulse</p>
        </div>
    </div>
</body>
</html>';
    }
    
    private function getReferralThankYouTemplate(): string
    {
        $logoUrl = asset('images/performance-pulse-logo.png');
        return '<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f7fa; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #c8e1eb 0%, #a8d5e2 100%); padding: 40px 30px; text-align: center; }
        .header h1 { color: #1a1a1a; margin: 0; font-size: 28px; }
        .content { padding: 40px 30px; color: #333; }
        .highlight { background: #f8f9fa; border-left: 4px solid #c8e1eb; padding: 20px; margin: 25px 0; border-radius: 4px; text-align: center; }
        .footer { background: #f8f9fa; padding: 30px; text-align: center; color: #6c757d; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="' . $logoUrl . '" alt="Performance Pulse Logo" style="max-width: 70px; height: auto; margin-bottom: 15px;">
            <h1>⚡ Performance Pulse</h1>
            <p style="color: #333; margin-top: 8px;">Powered by @{{bedrijf_naam}}</p>
        </div>
        <div class="content">
            <h2>Bedankt voor je doorverwijzing! 🙏</h2>
            <p>Beste @{{voornaam}},</p>
            <p>Wat geweldig dat je <strong>@{{referred_customer_name}}</strong> hebt doorverwezen naar ons!</p>
            <div class="highlight">
                <p style="font-size: 32px; margin: 10px 0;">⭐⭐⭐⭐⭐</p>
                <p style="margin: 0;"><strong>Je vertrouwen waarderen we enorm!</strong></p>
            </div>
            <p>Bedankt dat je ons doorvertelt aan andere wielrenliefhebbers!</p>
            <p>Met sportieve groet,<br>Het team van @{{bedrijf_naam}}</p>
        </div>
        <div class="footer">
            <p>© @{{jaar}} Performance Pulse</p>
        </div>
    </div>
</body>
</html>';
    }
    
    private function getGeneralNotificationTemplate(): string
    {
        $logoUrl = asset('images/performance-pulse-logo.png');
        return '<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f7fa; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #c8e1eb 0%, #a8d5e2 100%); padding: 40px 30px; text-align: center; }
        .header h1 { color: #1a1a1a; margin: 0; font-size: 28px; }
        .content { padding: 40px 30px; color: #333; }
        .highlight { background: #f8f9fa; border-left: 4px solid #c8e1eb; padding: 20px; margin: 25px 0; border-radius: 4px; }
        .button { display: inline-block; padding: 14px 28px; background: #c8e1eb; color: #1a1a1a; text-decoration: none; border-radius: 8px; font-weight: 600; margin: 20px 0; }
        .footer { background: #f8f9fa; padding: 30px; text-align: center; color: #6c757d; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="' . $logoUrl . '" alt="Performance Pulse Logo" style="max-width: 70px; height: auto; margin-bottom: 15px;">
            <h1>⚡ Performance Pulse</h1>
            <p style="color: #333; margin-top: 8px;">Powered by @{{bedrijf_naam}}</p>
        </div>
        <div class="content">
            <h2>Update van @{{bedrijf_naam}}</h2>
            <p>Beste @{{voornaam}},</p>
            <p>We hebben een update voor je!</p>
            <div class="highlight">
                <p style="margin: 0;"><strong>📢 Belangrijk bericht</strong></p>
            </div>
            <a href="@{{website_url}}" class="button">Bekijk Details</a>
            <p>Met sportieve groet,<br>Het team van @{{bedrijf_naam}}</p>
        </div>
        <div class="footer">
            <p>© @{{jaar}} Performance Pulse</p>
        </div>
    </div>
</body>
</html>';
    }
}
