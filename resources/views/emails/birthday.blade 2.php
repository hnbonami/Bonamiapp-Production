<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gefeliciteerd met je verjaardag!</title>
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
            border-bottom: 2px solid #e8f4fd;
        }
        .logo {
            max-width: 250px;
            height: auto;
            margin-bottom: 20px;
        }
        .main-title {
            color: #2c5aa0;
            font-size: 28px;
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
        .highlight-box {
            background: linear-gradient(135deg, #e8f4fd 0%, #f0f9ff 100%);
            border-left: 4px solid #2c5aa0;
            padding: 25px;
            margin: 30px 0;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(44, 90, 160, 0.1);
        }
        .signature {
            margin-top: 40px;
            border-top: 2px solid #e8f4fd;
            padding-top: 25px;
        }
        .footer {
            text-align: center;
            font-size: 14px;
            color: #666;
            margin-top: 25px;
        }
        .logo {
            max-width: 200px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <img src="{{ asset('logo_bonami.png') }}" alt="Bonami Sportcoaching" class="logo">
            <h1 class="main-title">Hiep hiep hoera!</h1>
            <p class="subtitle">Een sportieve felicitatie van het Bonami team</p>
        </div>

        <div class="content">
            <p><strong>Beste {{ $person->voornaam }},</strong></p>

            <p>Vandaag is jouw dag! Het hele team van <strong>Bonami Sportcoaching</strong> wenst je een fantastische verjaardag toe. Hopelijk staat er een mooie rit, run of training op het programma, of geniet je gewoon volop van een welverdiende rustdag.</p>

            <p>Een extra jaartje erbij betekent meer ervaring, meer kracht en meer kilometers op de teller! 💪</p>

            <div class="highlight-box">
                <p><strong>Daarom hebben wij een kleine traktatie voor je klaarstaan:</strong></p>
                
                <p>We nodigen je van harte uit om in de loop van de week (of wanneer het jou past) eens langs te komen in <strong>Landegem</strong>. De koffie (of thee) staat klaar en het is de perfecte gelegenheid om even bij te praten over je sportieve doelen.</p>
                
                <p><strong>☕ Spring gerust binnen voor een gratis potje koffie/thee op de zaak!</strong></p>
            </div>

            <p>We wensen je nog een prachtig en sportief nieuw levensjaar. Blijf je dromen najagen! 🚴‍♀️</p>
        </div>

        <div class="signature">
            <p><strong>Sportieve groeten,</strong></p>
            <p><strong>Het Bonami Team</strong></p>
            
            <div class="footer">
                <p>
                    <strong>Bonami Sportcoaching</strong><br>
                    📍 Landegem<br>
                    📧 info@bonami-sportcoaching.be<br>
                    🌐 www.bonami-sportcoaching.be
                </p>
                
                <p style="font-size: 12px; margin-top: 20px; color: #999;">
                    Deze email werd automatisch verstuurd omdat vandaag je verjaardag is. 
                    Proficiat nogmaals! 🎈
                </p>
            </div>
        </div>
    </div>
</body>
</html>