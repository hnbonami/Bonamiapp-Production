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
            margin-bottom: 8px;
            font-weight: 500;
            color: #374151;
        }
        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.2s;
        }
        .form-control:focus {
            outline: none;
            border-color: #2c5aa0;
            box-shadow: 0 0 0 3px rgba(44, 90, 160, 0.1);
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            margin: 10px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-danger {
            background-color: #dc3545;
            color: white;
        }
        .btn-danger:hover {
            background-color: #c82333;
        }
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
        }
        .info-box {
            background-color: #f1f5f9;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            text-align: left;
        }
        .already-unsubscribed {
            background-color: #fef2f2;
            border-color: #fecaca;
            color: #dc2626;
        }
        .subscribed {
            background-color: #f0fdf4;
            border-color: #bbf7d0;
            color: #16a34a;
        }
    </style>
</head>
<body>
    <div class="container">
        <img src="{{ asset('logo_bonami.png') }}" alt="Bonami Sportcoaching" class="logo">
        
        <h1 class="title">Afmelden voor emails</h1>
        
        <div class="info-box {{ $subscription->isSubscribed() ? 'subscribed' : 'already-unsubscribed' }}">
            <p><strong>📧 Email adres:</strong> {{ $subscription->email }}</p>
            @if($subscription->isSubscribed())
                <p><strong>✅ Status:</strong> <span style="color: #16a34a;">Geabonneerd</span></p>
                <p><strong>📅 Geabonneerd sinds:</strong> {{ $subscription->subscribed_at ? $subscription->subscribed_at->format('d/m/Y') : 'Onbekend' }}</p>
            @else
                <p><strong>❌ Status:</strong> <span style="color: #dc2626;">Afgemeld</span></p>
                <p><strong>📅 Afgemeld op:</strong> {{ $subscription->unsubscribed_at->format('d/m/Y H:i') }}</p>
                @if($subscription->unsubscribe_reason)
                    <p><strong>💭 Reden:</strong> {{ $subscription->unsubscribe_reason }}</p>
                @endif
            @endif
        </div>

        @if($subscription->isSubscribed())
            <p>We vinden het jammer dat je onze emails niet meer wilt ontvangen. Door je af te melden, ontvang je geen emails meer van <strong>Bonami Sportcoaching</strong>.</p>
            
            <form method="POST" action="{{ route('email.unsubscribe.process', $subscription->unsubscribe_token) }}">
                @csrf
                <div class="form-group">
                    <label for="reason">Waarom meld je je af? (optioneel)</label>
                    <select name="reason" id="reason" class="form-control">
                        <option value="">Selecteer een reden...</option>
                        <option value="Te veel emails">Te veel emails</option>
                        <option value="Niet relevant">Inhoud niet relevant voor mij</option>
                        <option value="Niet meer klant">Ik ben geen klant meer</option>
                        <option value="Verkeerde email">Dit is niet mijn email adres</option>
                        <option value="Privacy redenen">Privacy redenen</option>
                        <option value="Andere reden">Andere reden</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="additional_feedback">Extra feedback (optioneel)</label>
                    <textarea name="additional_feedback" id="additional_feedback" class="form-control" rows="3" 
                              placeholder="Laat ons weten hoe we onze emails kunnen verbeteren..."></textarea>
                </div>
                
                <div style="margin-top: 30px;">
                    <button type="submit" class="btn btn-danger">
                        ❌ Ja, meld me af
                    </button>
                    <a href="https://bonami-sportcoaching.be" class="btn btn-secondary">
                        ↩️ Annuleren
                    </a>
                </div>
            </form>
        @else
            <p>Je bent al afgemeld voor onze emails.</p>
            <p>Wil je je weer aanmelden? Stuur dan een email naar <a href="mailto:info@bonami-sportcoaching.be" style="color: #2c5aa0;">info@bonami-sportcoaching.be</a></p>
            
            <div style="margin-top: 30px;">
                <a href="https://bonami-sportcoaching.be" class="btn btn-secondary">
                    🏠 Terug naar website
                </a>
            </div>
        @endif
        
        <div style="margin-top: 40px; font-size: 14px; color: #666; border-top: 1px solid #e5e7eb; padding-top: 20px;">
            <p><strong>Bonami Sportcoaching</strong><br>
            📍 Landegem, België<br>
            📧 info@bonami-sportcoaching.be<br>
            🌐 www.bonami-sportcoaching.be</p>
        </div>
    </div>
</body>
</html>