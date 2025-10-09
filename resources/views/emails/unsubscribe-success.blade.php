<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Afgemeld - Bonami Sportcoaching</title>
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
        .container {
            background-color: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border: 1px solid #e2e8f0;
            text-align: center;
        }
        .logo {
            max-width: 200px;
            margin-bottom: 20px;
        }
        .title {
            color: #28a745;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .success-icon {
            font-size: 48px;
            color: #28a745;
            margin-bottom: 20px;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            margin: 10px;
            background-color: #c8e1eb;
            color: #333;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 500;
        }
        .btn:hover {
            opacity: 0.9;
        }
        .info-box {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <img src="{{ asset('logo_bonami.png') }}" alt="Bonami Sportcoaching" class="logo">
        
        <div class="success-icon">✅</div>
        <h1 class="title">Succesvol afgemeld</h1>
        
        <div class="info-box">
            <p><strong>{{ $subscription->email }}</strong> is succesvol afgemeld voor onze emails.</p>
            <p>Je zult geen emails meer ontvangen van Bonami Sportcoaching.</p>
        </div>
        
        <p>We vinden het jammer dat je onze emails niet meer wilt ontvangen. Mocht je van gedachten veranderen, dan kun je altijd contact met ons opnemen.</p>
        
        <p><strong>Wil je je toch weer aanmelden?</strong><br>
        Stuur een email naar <a href="mailto:info@bonami-sportcoaching.be" style="color: #2c5aa0;">info@bonami-sportcoaching.be</a></p>
        
        <a href="https://www.bonami-sportcoaching.be" class="btn">Terug naar website</a>
        
        <div style="margin-top: 40px; font-size: 14px; color: #666;">
            <p>Bedankt voor je interesse in Bonami Sportcoaching!</p>
            <hr style="margin: 20px 0; border: 1px solid #eee;">
            <p>Bonami Sportcoaching<br>
            📍 Landegem<br>
            📧 info@bonami-sportcoaching.be<br>
            🌐 www.bonami-sportcoaching.be</p>
        </div>
    </div>
</body>
</html>