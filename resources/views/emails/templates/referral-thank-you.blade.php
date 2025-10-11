<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bedankt voor uw doorverwijzing!</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f8fafc;
        }
        .email-wrapper {
            max-width: 600px;
            margin: 0 auto;
            background-color: #f8fafc;
            padding: 20px;
        }
        .email-container {
            background-color: white;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }
        
        /* Header Section */
        .header {
            background: linear-gradient(135deg, #10b981 0%, #059669 50%, #047857 100%);
            padding: 40px 30px;
            text-align: center;
            position: relative;
        }
        .header::before {
            content: '🤝';
            position: absolute;
            top: 20px;
            right: 30px;
            font-size: 60px;
            opacity: 0.2;
        }
        .header-title {
            color: white;
            font-size: 32px;
            font-weight: bold;
            margin: 0 0 10px 0;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
            position: relative;
            z-index: 2;
        }
        .header-subtitle {
            color: rgba(255,255,255,0.95);
            font-size: 18px;
            margin: 0;
            position: relative;
            z-index: 2;
        }

        /* Content Section */
        .content {
            padding: 40px 30px;
        }
        .greeting {
            font-size: 18px;
            margin-bottom: 25px;
            color: #1f2937;
        }
        .main-text {
            font-size: 16px;
            margin-bottom: 20px;
            color: #4b5563;
            line-height: 1.7;
        }

        /* Referral Info Box */
        .referral-box {
            background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
            border: 2px solid #10b981;
            border-radius: 16px;
            padding: 30px;
            margin: 30px 0;
            position: relative;
            overflow: hidden;
        }
        .referral-box::before {
            content: '🎉';
            position: absolute;
            top: -10px;
            right: -10px;
            font-size: 60px;
            opacity: 0.2;
            transform: rotate(15deg);
        }
        .referral-box h3 {
            color: #047857;
            font-size: 20px;
            margin: 0 0 20px 0;
            font-weight: bold;
        }
        .referral-detail {
            background-color: rgba(255,255,255,0.8);
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 10px;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }
        .referral-label {
            font-weight: 600;
            color: #047857;
            font-size: 14px;
        }
        .referral-value {
            color: #1f2937;
            font-size: 16px;
            margin-top: 4px;
        }

        /* Thank You Section */
        .thank-you {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border: 2px solid #f59e0b;
            border-radius: 12px;
            padding: 25px;
            margin: 25px 0;
            text-align: center;
        }
        .thank-you-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }
        .thank-you-text {
            color: #92400e;
            font-weight: 600;
            font-size: 18px;
            margin-bottom: 10px;
        }
        .thank-you-subtext {
            color: #a16207;
            font-size: 14px;
        }

        /* Footer */
        .footer {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            padding: 30px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            font-size: 13px;
            color: #6b7280;
        }
        .company-info {
            font-weight: 600;
            color: #374151;
            margin-bottom: 15px;
        }

        /* Responsive */
        @media (max-width: 600px) {
            .email-wrapper { padding: 10px; }
            .header, .content, .footer { padding: 25px 20px; }
            .header-title { font-size: 24px; }
            .referral-box { padding: 20px; }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">
            <!-- Header -->
            <div class="header">
                <h1 class="header-title">Bedankt voor uw doorverwijzing!</h1>
                <p class="header-subtitle">U krijgt deze mail omdat iemand u heeft genoemd als doorverwijzer</p>
            </div>

            <!-- Content -->
            <div class="content">
                <div class="greeting">
                    <strong>Beste @{{voornaam}} @{{naam}},</strong>
                </div>

                <div class="main-text">
                    Wat geweldig! Iemand heeft u genoemd als de persoon die hen heeft doorverwezen naar Bonami Sportcoaching. 
                    We zijn u erg dankbaar voor het vertrouwen en de aanbeveling!
                </div>

                <!-- Referral Info -->
                <div class="referral-box">
                    <h3>🎯 Details van de doorverwijzing:</h3>
                    <div class="referral-detail">
                        <div class="referral-label">Nieuwe klant:</div>
                        <div class="referral-value">@{{referred_customer_name}}</div>
                    </div>
                    <div class="referral-detail">
                        <div class="referral-label">Email adres:</div>
                        <div class="referral-value">@{{referred_customer_email}}</div>
                    </div>
                    <div class="referral-detail">
                        <div class="referral-label">Datum doorverwijzing:</div>
                        <div class="referral-value">@{{referral_date}}</div>
                    </div>
                </div>

                <!-- Thank You Section -->
                <div class="thank-you">
                    <div class="thank-you-icon">🙏</div>
                    <div class="thank-you-text">Hartelijk dank!</div>
                    <div class="thank-you-subtext">
                        Uw aanbeveling betekent veel voor ons en helpt ons om meer mensen te bereiken.
                    </div>
                </div>

                <div class="main-text">
                    Door uw positieve ervaring te delen, helpt u andere sporters ook de voordelen van onze 
                    professionele bikefit en coaching services te ontdekken.
                </div>

                <div class="main-text">
                    <strong>Heeft u nog vragen of wilt u zelf een nieuwe afspraak maken?</strong><br>
                    Neem gerust contact met ons op via <a href="mailto:info@bonami-sportcoaching.be">info@bonami-sportcoaching.be</a> 
                    of bel ons.
                </div>
            </div>

            <!-- Footer -->
            <div class="footer">
                <div class="company-info">
                    <strong>Bonami Sportcoaching</strong><br>
                    📍 Landegem, België<br>
                    📧 info@bonami-sportcoaching.be<br>
                    🌐 www.bonami-sportcoaching.be
                </div>
                
                <div style="font-size: 10px; color: #d1d5db; margin-top: 15px;">
                    Email ID: @{{email_id}} | Verstuurd op: @{{datum}} om @{{tijd}}
                </div>
            </div>
        </div>
    </div>
</body>
</html>