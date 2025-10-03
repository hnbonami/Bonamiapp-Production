<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Herinnering: Testzadel retourneren</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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
        .logo {
            max-width: 250px;
            height: auto;
            margin-bottom: 20px;
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
            <img src="{{ asset('logo_bonami.png') }}" alt="Bonami Sportcoaching" class="logo">
            <h1 class="main-title">Herinnering: Testzadel retourneren</h1>
            <p class="subtitle">Vriendelijke reminder van het Bonami team</p>
        </div>

        <div class="content">
            <p><strong>Dag {{ $klant->voornaam }},</strong></p>

            <p><strong>Graag horen we hoe het testzadel je bevalt!</strong></p>

            <p>We zien dat je het zadel al een tijdje in gebruik hebt. Zou je het daarom willen terugbrengen? Andere klanten wachten inmiddels om dit type zadel te testen, en we willen hen niet te lang laten wachten.</p>

            <div class="testzadel-info">
                <h3 style="margin-top: 0; color: #92400e;">📋 Testzadel Informatie</h3>
                
                <div class="info-row">
                    <strong>Zadel:</strong>
                    <span>{{ $testzadel->zadel_merk }} {{ $testzadel->zadel_model }}</span>
                </div>
                
                @if($testzadel->zadel_type)
                <div class="info-row">
                    <strong>Type:</strong>
                    <span>{{ $testzadel->zadel_type }}</span>
                </div>
                @endif
                
                @if($testzadel->zadel_breedte)
                <div class="info-row">
                    <strong>Breedte:</strong>
                    <span>{{ $testzadel->zadel_breedte }}mm</span>
                </div>
                @endif
                
                <div class="info-row">
                    <strong>Uitgeleend op:</strong>
                    <span>{{ $testzadel->uitleen_datum->format('d/m/Y') }}</span>
                </div>
                
                <div class="info-row">
                    <strong>Verwachte retour:</strong>
                    <span style="font-weight: bold; color: #dc2626;">{{ $testzadel->verwachte_retour_datum->format('d/m/Y') }}</span>
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
            <p><strong>Team Bonami</strong></p>
            
            <div class="footer">
                <p>
                    <strong>Bonami Sportcoaching</strong><br>
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
</html>