<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welkom bij Bonami Sportcoaching</title>
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
            background: linear-gradient(135deg, #c8e1eb 0%, #b3d7e3 50%, #9ecadb 100%);
            padding: 40px 30px;
            text-align: center;
            position: relative;
        }
        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="20" cy="20" r="2" fill="rgba(255,255,255,0.1)"/><circle cx="80" cy="40" r="3" fill="rgba(255,255,255,0.1)"/><circle cx="40" cy="80" r="2" fill="rgba(255,255,255,0.1)"/></svg>') repeat;
            opacity: 0.3;
        }
        .logo {
            max-width: 200px;
            height: auto;
            margin-bottom: 20px;
            position: relative;
            z-index: 2;
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
            margin-bottom: 30px;
            color: #4b5563;
            line-height: 1.7;
        }

        /* Login Info Box */
        .login-box {
            background: linear-gradient(135deg, #c8e1eb 0%, #e1f2f7 100%);
            border: 2px solid #c8e1eb;
            border-radius: 16px;
            padding: 30px;
            margin: 30px 0;
            position: relative;
            overflow: hidden;
        }
        .login-box::before {
            content: '🔐';
            position: absolute;
            top: -10px;
            right: -10px;
            font-size: 60px;
            opacity: 0.1;
            transform: rotate(15deg);
        }
        .login-box h3 {
            color: #0f4c5c;
            font-size: 20px;
            margin: 0 0 20px 0;
            font-weight: bold;
        }
        .login-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding: 12px 0;
            border-bottom: 1px solid rgba(15, 76, 92, 0.1);
        }
        .login-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }
        .login-label {
            font-weight: 600;
            color: #0f4c5c;
            font-size: 14px;
        }
        .login-value {
            font-family: 'Courier New', monospace;
            background-color: rgba(255,255,255,0.8);
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 14px;
            color: #1f2937;
            border: 1px solid rgba(15, 76, 92, 0.2);
        }

        /* CTA Button */
        .cta-section {
            text-align: center;
            margin: 35px 0;
        }
        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #c8e1eb 0%, #9ecadb 100%);
            color: #0f4c5c;
            padding: 16px 40px;
            text-decoration: none;
            border-radius: 50px;
            font-weight: bold;
            font-size: 16px;
            box-shadow: 0 4px 15px rgba(200, 225, 235, 0.4);
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        .cta-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(200, 225, 235, 0.6);
            border-color: #9ecadb;
        }

        /* Alternative Login */
        .alt-login {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            margin: 25px 0;
            text-align: center;
        }
        .alt-login-text {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 10px;
        }
        .alt-login-url {
            font-family: 'Courier New', monospace;
            color: #3b82f6;
            text-decoration: none;
            font-size: 14px;
            background-color: white;
            padding: 8px 16px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            display: inline-block;
        }

        /* Warning Box */
        .warning-box {
            background: linear-gradient(135deg, #fef3cd 0%, #fef7e0 100%);
            border: 2px solid #f59e0b;
            border-radius: 12px;
            padding: 20px;
            margin: 25px 0;
            display: flex;
            align-items: center;
        }
        .warning-icon {
            font-size: 24px;
            margin-right: 15px;
        }
        .warning-text {
            color: #92400e;
            font-weight: 600;
            font-size: 14px;
        }

        /* Signature */
        .signature {
            margin-top: 40px;
            padding-top: 30px;
            border-top: 2px solid #f1f5f9;
        }
        .signature-text {
            font-size: 16px;
            color: #1f2937;
            margin-bottom: 10px;
        }
        .signature-name {
            font-weight: bold;
            color: #0f4c5c;
            font-size: 16px;
        }

        /* Footer */
        .footer {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            padding: 30px;
            border-top: 1px solid #e2e8f0;
        }
        .footer-content {
            text-align: center;
            font-size: 13px;
            color: #6b7280;
            line-height: 1.6;
        }
        .unsubscribe-section {
            background-color: white;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .unsubscribe-text {
            margin: 0 0 15px 0;
            font-size: 13px;
            color: #4b5563;
        }
        .unsubscribe-link {
            display: inline-block;
            background-color: #ef4444;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            transition: background-color 0.2s;
        }
        .unsubscribe-link:hover {
            background-color: #dc2626;
        }
        .company-info {
            margin-top: 20px;
            font-weight: 600;
            color: #374151;
        }

        /* Responsive */
        @media (max-width: 600px) {
            .email-wrapper {
                padding: 10px;
            }
            .header, .content, .footer {
                padding: 25px 20px;
            }
            .header-title {
                font-size: 24px;
            }
            .login-box {
                padding: 20px;
            }
            .login-item {
                flex-direction: column;
                align-items: flex-start;
                text-align: left;
            }
            .login-value {
                margin-top: 5px;
                width: 100%;
                box-sizing: border-box;
            }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">
            <!-- Header -->
            <div class="header">
                <img src="{{ asset('logo_bonami.png') }}" alt="Bonami Sportcoaching" class="logo">
                <h1 class="header-title">Bonami Sportcoaching</h1>
                <p class="header-subtitle">Welkom @{{voornaam}}! 👋</p>
            </div>

            <!-- Content -->
            <div class="content">
                <div class="greeting">
                    <strong>Beste @{{voornaam}} @{{naam}},</strong>
                </div>

                <div class="main-text">
                    Je bent uitgenodigd om toegang te krijgen tot onze klanten portal waar je al 
                    je testresultaten kunt bekijken en op de hoogte blijft van alle laatste nieuwtjes.
                </div>

                <!-- Login Info Box -->
                <div class="login-box">
                    <h3>🔐 Je logingegevens:</h3>
                    <div class="login-item">
                        <span class="login-label">Email:</span>
                        <span class="login-value">@{{email}}</span>
                    </div>
                    <div class="login-item">
                        <span class="login-label">Tijdelijk wachtwoord:</span>
                        <span class="login-value">@{{temporary_password}}</span>
                    </div>
                </div>

                <!-- CTA Button -->
                <div class="cta-section">
                    <a href="@{{website_url}}/login" class="cta-button">
                        🚀 Inloggen op het portaal
                    </a>
                </div>

                <!-- Alternative Login -->
                <div class="alt-login">
                    <div class="alt-login-text">Je kunt ook handmatig inloggen op:</div>
                    <a href="@{{website_url}}/login" class="alt-login-url">@{{website_url}}/login</a>
                </div>

                <!-- Warning -->
                <div class="warning-box">
                    <span class="warning-icon">⚠️</span>
                    <div class="warning-text">
                        <strong>Belangrijk:</strong> Wijzig je wachtwoord na de eerste login voor de veiligheid.
                    </div>
                </div>

                <!-- Signature -->
                <div class="signature">
                    <div class="signature-text">Met vriendelijke groet,</div>
                    <div class="signature-name">Het Bonami Sportcoaching Team</div>
                </div>
            </div>

            <!-- Footer -->
            <div class="footer">
                <div class="footer-content">
                    <!-- Unsubscribe Section -->
                    <div class="unsubscribe-section">
                        <p class="unsubscribe-text">
                            📧 Je ontvangt deze email omdat je geabonneerd bent op onze nieuwsbrief van Bonami Sportcoaching.
                        </p>
                        <p class="unsubscribe-text">
                            Wil je geen emails meer ontvangen?
                        </p>
                        <a href="@{{unsubscribe_url}}" class="unsubscribe-link">
                            🚫 Klik hier om je af te melden
                        </a>
                    </div>

                    <!-- Company Info -->
                    <div class="company-info">
                        <strong>Bonami Sportcoaching</strong><br>
                        📍 Landegem, België<br>
                        📧 info@bonami-sportcoaching.be<br>
                        🌐 www.bonami-sportcoaching.be
                    </div>

                    <!-- Email metadata -->
                    <div style="font-size: 10px; color: #d1d5db; margin-top: 15px;">
                        Email ID: @{{email_id}} | Verstuurd op: @{{datum}} om @{{tijd}}
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>