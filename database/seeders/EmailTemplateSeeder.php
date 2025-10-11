<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    public function run()
    {
        // Create testzadel reminder template only if it doesn't exist
        if (!EmailTemplate::where('type', 'testzadel_reminder')->exists()) {
            EmailTemplate::create([
                'name' => 'Testzadel Herinnering',
                'type' => 'testzadel_reminder',
                'subject' => 'Herinnering: Testzadel @{{merk}} @{{model}} terugbrengen',
                'body_html' => '
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #f8f9fa; padding: 20px; text-align: center; border-radius: 5px; }
        .content { padding: 20px 0; }
        .footer { background: #f8f9fa; padding: 15px; text-align: center; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>@{{bedrijf_naam}}</h2>
            <h3>Testzadel Herinnering</h3>
        </div>
        
        <div class="content">
            <p>Beste @{{voornaam}},</p>
            
            <p>Je hebt een testzadel <strong>@{{merk}} @{{model}}</strong> uitgeleend op @{{uitgeleend_op}}.</p>
            
            <p>De verwachte terugbreng datum is <strong>@{{verwachte_retour}}</strong>.</p>
            
            <p>Wij willen je vriendelijk verzoeken om de testzadel zo spoedig mogelijk terug te brengen.</p>
            
            <p>Heb je nog vragen? Neem gerust contact met ons op.</p>
            
            <p>Met vriendelijke groet,<br>
            Het @{{bedrijf_naam}} team</p>
        </div>
        
        <div class="footer">
            <p>&copy; @{{jaar}} @{{bedrijf_naam}}. Alle rechten voorbehouden.</p>
        </div>
    </div>
</body>
</html>',
                'description' => 'Template voor testzadel terugbreng herinneringen',
                'is_active' => true
            ]);
        }

        // Create other default templates if they don't exist
        if (!EmailTemplate::where('type', 'welcome_customer')->exists()) {
            EmailTemplate::create([
                'name' => 'Welkom Nieuwe Klant',
                'type' => 'welcome_customer',
                'subject' => 'Welkom bij @{{bedrijf_naam}}, @{{voornaam}}!',
                'body_html' => '
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Welkom @{{voornaam}}!</h2>
        <p>Bedankt voor je registratie bij @{{bedrijf_naam}}.</p>
        <p>We kijken ernaar uit om je te helpen!</p>
        <p>Met vriendelijke groet,<br>Het @{{bedrijf_naam}} team</p>
    </div>
</body>
</html>',
                'description' => 'Welkom email voor nieuwe klanten',
                'is_active' => true
            ]);
        }
    }
}