<!DOCTYPE html>
<html>
<head>
    <title>Welkom bij Bonami Sportcoaching</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welkom bij Bonami Sportcoaching</h1>
        
        <p>Beste {{ $klant->voornaam }} {{ $klant->naam }},</p>
        
        <p>Je account is aangemaakt op het Bonami Sportcoaching platform.</p>
        
        <p>Je kunt inloggen via de volgende link:<br>
        <a href="{{ config('app.url') }}">Inloggen</a></p>
        
        <p><strong>Login:</strong> @{{email}}<br>
        <strong>Wachtwoord:</strong> @{{wachtwoord}}</p>
        
        <p>Gelieve je wachtwoord te wijzigen na de eerste login.</p>
        
        <p>Met sportieve groet,<br>
        Bonami Sportcoaching</p>
    </div>
</body>
</html>