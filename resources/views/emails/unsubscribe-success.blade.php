<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Succesvol afgemeld - Bonami Sportcoaching</title>
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
            color: #16a34a;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .success-icon {
            font-size: 48px;
            color: #16a34a;
            margin-bottom: 20px;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            margin: 10px;
            background-color: #c8e1eb;
            color: #333;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.2s;
        }
        .btn:hover {
            background-color: #b3d7e3;
            transform: translateY(-1px);
        }
        .info-box {
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            color: #16a34a;
        }
        .feedback-box {
            background-color: #fef9e7;
            border: 1px solid #fed7aa;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            color: #92400e;
            font-size: 14px;
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
            @if($subscription->unsubscribed_at)
                <p><small>Afgemeld op: {{ $subscription->unsubscribed_at->format('d/m/Y om H:i') }}</small></p>
            @endif
        </div>

        @if($subscription->unsubscribe_reason && $subscription->unsubscribe_reason !== 'Geen reden opgegeven')
            <div class="feedback-box">
                <p><strong>📝 Je feedback:</strong></p>
                <p>{{ $subscription->unsubscribe_reason }}</p>
                <p><small>Bedankt voor je feedback! Dit helpt ons onze emails te verbeteren.</small></p>
            </div>
        @endif
        
        <p>We vinden het jammer dat je onze emails niet meer wilt ontvangen. Mocht je van gedachten veranderen, dan kun je altijd contact met ons opnemen.</p>
        
        <div style="background-color: #f8fafc; padding: 20px; border-radius: 8px; margin: 20px 0;">
            <p><strong>🔄 Wil je je toch weer aanmelden?</strong></p>
            <p>Stuur een email naar <a href="mailto:info@bonami-sportcoaching.be" style="color: #2c5aa0; text-decoration: none; font-weight: 500;">info@bonami-sportcoaching.be</a></p>
            <p><small>of bel ons voor meer informatie over onze diensten.</small></p>
        </div>
        
        <div style="margin-top: 30px;">
            <a href="https://bonami-sportcoaching.be" class="btn">
                🏠 Terug naar website
            </a>
            <a href="mailto:info@bonami-sportcoaching.be" class="btn">
                ✉️ Contact opnemen
            </a>
        </div>
        
        <div style="margin-top: 40px; font-size: 14px; color: #666; border-top: 1px solid #e5e7eb; padding-top: 20px;">
            <p>Bedankt voor je interesse in Bonami Sportcoaching!</p>
            <hr style="margin: 20px 0; border: 1px solid #eee;">
            <p><strong>Bonami Sportcoaching</strong><br>
            📍 Landegem, België<br>
            📧 info@bonami-sportcoaching.be<br>
            🌐 www.bonami-sportcoaching.be<br>
            🚴‍♂️ Jouw partner in sportcoaching</p>
        </div>
    </div>
</body>
</html>