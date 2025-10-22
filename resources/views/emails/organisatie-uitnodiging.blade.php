<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: #c8e1eb;
            padding: 30px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }
        .content {
            background: #ffffff;
            padding: 30px;
            border: 1px solid #e5e7eb;
        }
        .button {
            display: inline-block;
            padding: 15px 30px;
            background: #c8e1eb;
            color: #1f2937;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #6b7280;
            font-size: 14px;
        }
        .info-box {
            background: #f9fafb;
            padding: 15px;
            border-left: 4px solid #c8e1eb;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1 style="margin: 0; color: #1f2937;">🎉 Welkom bij Bonami Sportcoaching!</h1>
    </div>
    
    <div class="content">
        <p>Beste <strong>{{ $organisatie->naam }}</strong>,</p>
        
        <p>Welkom bij het Bonami Sportcoaching platform! Je admin account is aangemaakt en je kunt nu direct aan de slag.</p>
        
        <div class="info-box">
            <strong>� Jouw inloggegevens:</strong><br>
            <strong>Email:</strong> {{ $organisatie->email }}<br>
            <strong>Wachtwoord:</strong> <code style="background: #fee; padding: 3px 8px; border-radius: 4px; font-size: 16px; font-weight: bold;">{{ $password }}</code><br>
            <small style="color: #6b7280; margin-top: 5px; display: block;">💡 Je kunt dit wachtwoord wijzigen na het inloggen</small>
        </div>
        
        <p><strong>Aan de slag:</strong></p>
        <ol>
            <li>Klik op de knop hieronder om in te loggen</li>
            <li>Gebruik bovenstaande inloggegevens</li>
            <li>Wijzig je wachtwoord (optioneel maar aangeraden)</li>
            <li>Begin met het toevoegen van klanten en medewerkers</li>
        </ol>
        
        <div style="text-align: center;">
            <a href="{{ $loginUrl }}" class="button">
                🔑 Inloggen
            </a>
        </div>
        
        <p style="color: #6b7280; font-size: 14px; margin-top: 30px;">
            <strong>⚠️ Belangrijk:</strong> Bewaar deze email goed. Je hebt het wachtwoord nodig bij je eerste login.<br>
            Login URL: <code style="background: #f3f4f6; padding: 5px 10px; border-radius: 4px; display: inline-block; margin-top: 5px;">{{ $loginUrl }}</code>
        </p>
    </div>
    
    <div class="footer">
        <p>
            Met sportieve groet,<br>
            <strong>Team Bonami Sportcoaching</strong>
        </p>
        <p style="font-size: 12px; color: #9ca3af;">
            Vragen? Neem contact met ons op via {{ config('mail.from.address') }}
        </p>
    </div>
</body>
</html>
