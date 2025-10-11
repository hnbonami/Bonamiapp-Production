<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bedankt voor je doorverwijzing!</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        .content {
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #6c757d;
            font-size: 14px;
        }
        h1 {
            color: #007bff;
            margin: 0;
        }
        h2 {
            color: #333;
            margin-top: 0;
        }
        .highlight {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🎉 Bedankt voor je doorverwijzing!</h1>
    </div>
    
    <div class="content">
        <h2>Beste {{ $referringCustomer->voornaam }},</h2>

        <p>Hartelijk dank voor je doorverwijzing! 🙏</p>

        <div class="highlight">
            <p>We hebben zojuist <strong>{{ $newCustomer->voornaam }} {{ $newCustomer->naam }}</strong> als nieuwe klant mogen verwelkomen en dat hebben we aan jou te danken.</p>
        </div>

        <p>Het betekent veel voor ons dat je ons aanbeveelt aan vrienden en familie. Jouw vertrouwen in onze service waarderen we enorm!</p>

        <p>Als blijk van waardering krijg je binnenkort een kleine attentie van ons.</p>

        <p>Nogmaals bedankt en tot snel!</p>

        <p>Met vriendelijke groet,<br>
        <strong>Het {{ config('app.name', 'Bonami') }} team</strong></p>
    </div>
    
    <div class="footer">
        <p>Deze email is automatisch verstuurd omdat {{ $newCustomer->voornaam }} {{ $newCustomer->naam }} jou heeft genoemd als doorverwijzer.</p>
    </div>
</body>
</html>