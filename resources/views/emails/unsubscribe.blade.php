<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Afmelden voor emails - Bonami Sportcoaching</title>
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
            color: #2c5aa0;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            margin: 10px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 500;
            cursor: pointer;
        }
        .btn-danger {
            background-color: #dc3545;
            color: white;
        }
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        .btn:hover {
            opacity: 0.9;
        }
        .info-box {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
            text-align: left;
        }
    </style>
</head>
<body>
    <div class="container">
        <img src="{{ asset('logo_bonami.png') }}" alt="Bonami Sportcoaching" class="logo">
        
        <h1 class="title">Afmelden voor emails</h1>
        
        <div class="info-box">
            <p><strong>Email adres:</strong> {{ $subscription->email }}</p>
            @if($subscription->isSubscribed())
                <p><strong>Status:</strong> <span style="color: green;">Geabonneerd</span></p>
            @else
                <p><strong>Status:</strong> <span style="color: red;">Afgemeld</span></p>
                <p><strong>Afgemeld op:</strong> {{ $subscription->unsubscribed_at->format('d/m/Y H:i') }}</p>
            @endif
        </div>

        @if($subscription->isSubscribed())
            <p>We vinden het jammer dat je onze emails niet meer wilt ontvangen. Je kunt je hieronder afmelden voor alle toekomstige emails van Bonami Sportcoaching.</p>
            
            <form method="POST" action="{{ route('email.unsubscribe.process', $subscription->unsubscribe_token) }}">
                @csrf
                <div class="form-group">
                    <label for="reason">Reden voor afmelden (optioneel):</label>
                    <select name="reason" id="reason" class="form-control">
                        <option value="">Selecteer een reden...</option>
                        <option value="Te veel emails">Te veel emails</option>
                        <option value="Niet relevant">Inhoud niet relevant</option>
                        <option value="Niet meer klant">Niet meer klant</option>
                        <option value="Andere reden">Andere reden</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-danger">Ja, meld me af</button>
                <a href="/" class="btn btn-secondary">Annuleren</a>
            </form>
        @else
            <p>Je bent al afgemeld voor onze emails.</p>
            <p>Wil je je weer aanmelden? Stuur dan een email naar <a href="mailto:info@bonami-sportcoaching.be">info@bonami-sportcoaching.be</a></p>
            
            <a href="/" class="btn btn-secondary">Terug naar website</a>
        @endif
        
        <div style="margin-top: 40px; font-size: 14px; color: #666;">
            <p>Bonami Sportcoaching<br>
            📍 Landegem<br>
            📧 info@bonami-sportcoaching.be<br>
            🌐 www.bonami-sportcoaching.be</p>
        </div>
    </div>
</body>
</html>