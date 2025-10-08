<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmailTemplate;

class EmailTemplateSeeder extends Seeder
{
    public function run()
    {
        // Testzadel reminder template
        EmailTemplate::updateOrCreate(
            ['type' => 'testzadel_reminder'],
            [
                'name' => 'Testzadel Herinnering',
                'subject' => 'Herinnering: Testzadel retourneren - @{{merk}} @{{model}}',
                'description' => 'Automatische herinnering voor het retourneren van testzadels',
                'body_html' => '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Herinnering: Testzadel retourneren</title>
    <style>
        body {
            font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8fafc;
        }
        .email-container {
            background-color: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border: 1px solid #e2e8f0;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #fef3cd;
        }
        .main-title {
            color: #d97706;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .subtitle {
            color: #666;
            font-size: 16px;
            font-weight: normal;
        }
        .content {
            font-size: 16px;
            margin-bottom: 20px;
        }
        .testzadel-info {
            background: linear-gradient(135deg, #fef3cd 0%, #fefce8 100%);
            border-left: 4px solid #d97706;
            padding: 25px;
            margin: 30px 0;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(217, 119, 6, 0.1);
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 8px 0;
            border-bottom: 1px solid #fed7aa;
        }
        .info-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }
        .signature {
            margin-top: 40px;
            border-top: 2px solid #fef3cd;
            padding-top: 25px;
        }
        .footer {
            text-align: center;
            font-size: 14px;
            color: #666;
            margin-top: 25px;
        }
        .action-needed {
            background-color: #fef2f2;
            border: 1px solid #fecaca;
            padding: 20px;
            border-radius: 8px;
            margin: 25px 0;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1 class="main-title">Herinnering: Testzadel retourneren</h1>
            <p class="subtitle">Vriendelijke reminder van het Bonami team</p>
        </div>

        <div class="content">
            <p><strong>Dag @{{voornaam}},</strong></p>

            <p><strong>Graag horen we hoe het testzadel je bevalt!</strong></p>

            <p>We zien dat je het zadel al een tijdje in gebruik hebt. Zou je het daarom willen terugbrengen? Andere klanten wachten inmiddels om dit type zadel te testen, en we willen hen niet te lang laten wachten.</p>

            <div class="testzadel-info">
                <h3 style="margin-top: 0; color: #92400e;">📋 Testzadel Informatie</h3>
                
                <div class="info-row">
                    <strong>Zadel:</strong>
                    <span>@{{merk}} @{{model}}</span>
                </div>
                
                <div class="info-row">
                    <strong>Type:</strong>
                    <span>@{{type}}</span>
                </div>
                
                <div class="info-row">
                    <strong>Breedte:</strong>
                    <span>@{{breedte}}mm</span>
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
                
                <p><strong>Langskomen & Service:</strong> Kom langs in Landegem, Gaverstraat 2. Dit is het beste als je nog vragen hebt of als je een definitief zadel wilt monteren. We hebben de zadels meestal op voorraad.</p>
                
                <p><strong>Deponeren:</strong> Indien er niemand thuis is, mag je het zadel altijd in de brievenbus deponeren.</p>
            </div>

            <div style="background-color: #f0f9ff; border: 1px solid #bfdbfe; padding: 20px; border-radius: 8px; margin: 25px 0;">
                <h3 style="margin-top: 0; color: #1e40af;">🤔 Wat is de volgende stap voor jou?</h3>
                
                <p><strong>Tevreden?</strong> Spring even langs, dan monteren we meteen je nieuwe, definitieve zadel.</p>
                
                <p><strong>Nog niet perfect?</strong> Laat het ons zeker weten! Dan gaan we samen op zoek naar een andere oplossing die wel 100% past.</p>
            </div>

            <p><strong>Laat ons snel iets weten over je bevindingen, zodat we het traject kunnen afronden.</strong></p>
        </div>

        <div class="signature">
            <p><strong>Sportieve groeten,</strong></p>
            <p><strong>Team @{{bedrijf_naam}}</strong></p>
            
            <div class="footer">
                <p>
                    <strong>@{{bedrijf_naam}}</strong><br>
                    📍 Landegem<br>
                    📧 info@bonami-sportcoaching.be<br>
                    🌐 www.bonami-sportcoaching.be
                </p>
                
                <p style="font-size: 12px; margin-top: 20px; color: #999;">
                    Deze herinnering werd automatisch verstuurd. Heb je de testzadel al geretourneerd? 
                    Dan kun je deze email negeren.
                </p>
            </div>
        </div>
    </div>
</body>
</html>',
                'body_text' => 'Dag @{{voornaam}},

Graag horen we hoe het testzadel je bevalt!

We zien dat je het zadel al een tijdje in gebruik hebt. Zou je het daarom willen terugbrengen? Andere klanten wachten inmiddels om dit type zadel te testen.

Testzadel Informatie:
- Zadel: @{{merk}} @{{model}}
- Type: @{{type}}
- Breedte: @{{breedte}}mm
- Uitgeleend op: @{{uitgeleend_op}}
- Verwachte retour: @{{verwachte_retour}}

Je kunt het zadel terugbrengen door:
1. Langskomen in Landegem, Gaverstraat 2
2. Deponeren in de brievenbus indien niemand thuis is

Laat ons snel iets weten over je bevindingen, zodat we het traject kunnen afronden.

Sportieve groeten,
Team @{{bedrijf_naam}}',
                'is_active' => true
            ]
        );

        // Welcome customer template - MUCH BETTER VERSION
        EmailTemplate::updateOrCreate(
            ['type' => 'welcome_customer'],
            [
                'name' => 'Welkom Nieuwe Klant',
                'subject' => 'Welkom bij @{{bedrijf_naam}} - Jouw account is klaar! 🎉',
                'description' => 'Welkomst email voor nieuwe klanten met login gegevens',
                'body_html' => '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Welkom bij @{{bedrijf_naam}}</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; background: #f8fafc; }
        .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { text-align: center; margin-bottom: 30px; color: #10b981; }
        .login-box { background: #f0fdf4; border: 2px solid #10b981; padding: 20px; border-radius: 8px; margin: 20px 0; text-align: center; }
        .credentials { background: rgba(16,185,129,0.1); padding: 15px; border-radius: 6px; margin: 15px 0; }
        .cta-button { display: inline-block; background: #10b981; color: white; padding: 15px 30px; text-decoration: none; border-radius: 6px; margin: 15px 0; font-weight: bold; }
        .features { background: #eff6ff; border: 1px solid #3b82f6; padding: 20px; border-radius: 6px; margin: 20px 0; }
        .footer { text-align: center; font-size: 14px; color: #666; margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px; }
        .warning { color: #dc2626; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎉 Welkom bij @{{bedrijf_naam}}!</h1>
            <p>Jouw account is succesvol aangemaakt</p>
        </div>

        <p>Dag @{{voornaam}},</p>
        
        <p>Wat fantastisch dat je ervoor hebt gekozen om met ons samen te werken! We hebben jouw persoonlijke account aangemaakt en je kunt nu direct inloggen op ons klantportaal.</p>

        <div class="login-box">
            <h3>🔐 Jouw persoonlijke login gegevens</h3>
            <div class="credentials">
                <p><strong>Email:</strong> @{{email}}</p>
                <p><strong>Tijdelijk wachtwoord:</strong> <code>@{{temporary_password}}</code></p>
            </div>
            <p class="warning">⚠️ BELANGRIJK: Wijzig je wachtwoord direct na de eerste login!</p>
            
            <a href="http://127.0.0.1:8003/login" class="cta-button">🚀 Inloggen op mijn klantportaal</a>
        </div>

        <div class="features">
            <h3>📋 Wat kun je allemaal doen in jouw klantportaal?</h3>
            <ul>
                <li><strong>📅 Afspraken beheren:</strong> Bekijk komende bikefits en inspanningstesten</li>
                <li><strong>📊 Testresultaten:</strong> Download je fietsdata en uitgebreide rapportages</li>
                <li><strong>🚴‍♀️ Testzadels:</strong> Overzicht van uitgeleende zadels en retourdata</li>
                <li><strong>👤 Persoonlijke data:</strong> Update je profiel, voorkeuren en doelen</li>
                <li><strong>📈 Vooruitgang:</strong> Bekijk je ontwikkeling en alle vorige sessies</li>
                <li><strong>💬 Communicatie:</strong> Direct contact met je coach</li>
            </ul>
        </div>

        <p><strong>Heb je vragen of problemen bij het inloggen?</strong> Aarzel niet om contact met ons op te nemen. We helpen je graag verder!</p>

        <p>We kijken er enorm naar uit om samen aan jouw fietsdoelen te werken en je naar een hoger niveau te brengen! 🚴‍♀️💪</p>

        <div class="footer">
            <p><strong>@{{bedrijf_naam}}</strong><br>
            📍 Landegem, Gaverstraat 2<br>
            📧 info@bonami-sportcoaching.be<br>
            🌐 www.bonami-sportcoaching.be<br>
            📞 +32 123 456 789</p>
            
            <p style="font-size: 12px; margin-top: 15px; color: #999;">
                Deze email werd automatisch verstuurd omdat er een account voor je is aangemaakt.
            </p>
        </div>
    </div>
</body>
</html>',
                'body_text' => 'Dag @{{voornaam}},

Welkom bij @{{bedrijf_naam}}!

Jouw account is succesvol aangemaakt. Hier zijn je login gegevens:

Email: @{{email}}
Tijdelijk wachtwoord: @{{temporary_password}}

⚠️ BELANGRIJK: Wijzig je wachtwoord na de eerste login!

Login op: http://127.0.0.1:8003/login

In jouw klantportaal kun je:
- Afspraken beheren
- Testresultaten downloaden
- Testzadels overzicht
- Persoonlijke data updaten
- Vooruitgang bekijken
- Direct contact met je coach

Vragen? Neem gerust contact op!

We kijken ernaar uit om samen aan je fietsdoelen te werken!

Met sportieve groeten,
Team @{{bedrijf_naam}}

---
@{{bedrijf_naam}}
Landegem, Gaverstraat 2
info@bonami-sportcoaching.be
www.bonami-sportcoaching.be',
                'is_active' => true
            ]
        );

        // Birthday template
        EmailTemplate::updateOrCreate(
            ['type' => 'birthday'],
            [
                'name' => 'Verjaardagsmail',
                'subject' => '🎉 Gefeliciteerd @{{voornaam}}! - Team @{{bedrijf_naam}}',
                'description' => 'Automatische verjaardagswensen',
                'body_html' => '<h1>🎉 Gefeliciteerd @{{voornaam}}!</h1><p>Het hele team van @{{bedrijf_naam}} wenst je een fantastische verjaardag!</p>',
                'body_text' => 'Gefeliciteerd @{{voornaam}}! Het hele team van @{{bedrijf_naam}} wenst je een fantastische verjaardag!',
                'is_active' => true
            ]
        );
    }
}