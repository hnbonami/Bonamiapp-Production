<!DOCTYPE html>
<html>
<head>
    <title>Welkom bij Bonami Sportcoaching - Medewerker</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welkom bij Bonami Sportcoaching</h1>
        
        <p>Beste {{ $medewerker->voornaam }} {{ $medewerker->naam }},</p>
        
        <p>Je medewerker account is aangemaakt op het Bonami Sportcoaching platform.</p>
        
        <p>Je kunt inloggen via de volgende link:<br>
        <a href="{{ config('app.url') }}">Inloggen</a></p>
        
        <p><strong>Login:</strong> {{ $medewerker->email }}<br>
        <strong>Wachtwoord:</strong> {{ $temporaryPassword }}</p>
        
        <p>Gelieve je wachtwoord te wijzigen na de eerste login.</p>
        
        <p>Je hebt toegang tot:</p>
        <ul>
            @if($medewerker->bikefit)
                <li>Bikefit module</li>
            @endif
            @if($medewerker->inspanningstest)
                <li>Inspanningstest module</li>
            @endif
            <li>Klantenbeheer</li>
            <li>Rapporten</li>
        </ul>
        
        <p>Met sportieve groet,<br>
        Bonami Sportcoaching</p>
    </div>
</body>
</html>