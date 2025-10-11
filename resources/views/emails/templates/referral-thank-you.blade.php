<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bedankt voor uw doorverwijzing - Bonami Sportcoaching</title>
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
            display: block;
            border: none;
            outline: none;
            text-decoration: none;
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

        /* Thank You Box */
        .thank-you-box {
            background: linear-gradient(135deg, #fef3cd 0%, #fef7e0 100%);
            border: 2px solid #f59e0b;
            border-radius: 16px;
            padding: 30px;
            margin: 30px 0;
            position: relative;
            overflow: hidden;
            text-align: center;
        }
        .thank-you-box::before {
            content: '🎉';
            position: absolute;
            top: -10px;
            right: -10px;
            font-size: 60px;
            opacity: 0.1;
            transform: rotate(15deg);
        }
        .thank-you-box h3 {
            color: #92400e;
            font-size: 24px;
            margin: 0 0 15px 0;
            font-weight: bold;
        }
        .thank-you-text {
            color: #92400e;
            font-size: 16px;
            font-weight: 600;
        }

        /* Customer Info Box */
        .customer-info-box {
            background: linear-gradient(135deg, #c8e1eb 0%, #e1f2f7 100%);
            border: 2px solid #c8e1eb;
            border-radius: 16px;
            padding: 25px;
            margin: 25px 0;
        }
        .customer-info-box h4 {
            color: #0f4c5c;
            font-size: 18px;
            margin: 0 0 15px 0;
            font-weight: bold;
        }
        .customer-detail {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            padding: 8px 0;
            border-bottom: 1px solid rgba(15, 76, 92, 0.1);
        }
        .customer-detail:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }
        .customer-label {
            font-weight: 600;
            color: #0f4c5c;
            font-size: 14px;
        }
        .customer-value {
            background-color: rgba(255,255,255,0.8);
            padding: 6px 10px;
            border-radius: 6px;
            font-size: 14px;
            color: #1f2937;
            border: 1px solid rgba(15, 76, 92, 0.2);
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
        .company-info {
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
            .thank-you-box, .customer-info-box {
                padding: 20px;
            }
            .customer-detail {
                flex-direction: column;
                align-items: flex-start;
            }
            .customer-value {
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
                <div style="text-align: center; margin-bottom: 20px;">
                    <img src="https://bonami-sportcoaching.be/logo_bonami.png" 
                         alt="Bonami Sportcoaching" 
                         class="logo"
                         style="max-width: 200px; height: auto; display: block; margin: 0 auto; border: none;"
                         onerror="this.onerror=null; this.src='https://bonamiapp.test/logo_bonami.png'; if(this.complete && this.naturalHeight === 0) { this.style.display='none'; this.nextElementSibling.style.display='block'; }">
                    
                    <div style="display: none; width: 200px; height: 60px; background: rgba(255,255,255,0.9); border: 2px solid rgba(255,255,255,0.8); border-radius: 12px; color: #0f4c5c; font-size: 20px; font-weight: bold; text-align: center; line-height: 56px; margin: 0 auto; position: relative; z-index: 2;">
                        🚴‍♂️ BONAMI SPORTCOACHING
                    </div>
                </div>
                
                <h1 class="header-title">Bonami Sportcoaching</h1>
                <p class="header-subtitle">Bedankt voor uw doorverwijzing! 🙏</p>
            </div>

            <!-- Content -->
            <div class="content">
                <div class="greeting">
                    <strong>Beste @{{voornaam}} @{{naam}},</strong>
                </div>

                <div class="main-text">
                    Wat fantastisch dat u een nieuwe klant naar ons hebt doorverwezen! 
                    We waarderen uw vertrouwen in onze dienstverlening enorm.
                </div>

                <!-- Thank You Box -->
                <div class="thank-you-box">
                    <h3>🎉 Hartelijk dank!</h3>
                    <p class="thank-you-text">
                        Dankzij uw aanbeveling kunnen we nog meer mensen helpen 
                        met hun sport- en fitnessbehoeften.
                    </p>
                </div>

                <!-- Customer Info -->
                <div class="customer-info-box">
                    <h4>👥 Details van de nieuwe klant:</h4>
                    <div class="customer-detail">
                        <span class="customer-label">Naam:</span>
                        <span class="customer-value">@{{referred_customer_name}}</span>
                    </div>
                    <div class="customer-detail">
                        <span class="customer-label">Email:</span>
                        <span class="customer-value">@{{referred_customer_email}}</span>
                    </div>
                    <div class="customer-detail">
                        <span class="customer-label">Doorverwijzing op:</span>
                        <span class="customer-value">@{{referral_date}}</span>
                    </div>
                </div>

                <div class="main-text">
                    <strong>Wat gebeurt er nu?</strong><br>
                    We zullen binnenkort contact opnemen met de nieuwe klant om een afspraak in te plannen. 
                    Natuurlijk houden we u op de hoogte van de voortgang.
                </div>

                <div class="main-text">
                    <strong>Blijf doorverwijzen!</strong><br>
                    Heeft u nog anderen die kunnen profiteren van onze diensten? 
                    Aarzel niet om hen ook naar ons door te verwijzen. 
                    Elke doorverwijzing wordt enorm gewaardeerd! 🚴‍♂️
                </div>

                <!-- Signature -->
                <div class="signature">
                    <div class="signature-text">Met sportieve groeten,</div>
                    <div class="signature-name">Het Bonami Sportcoaching Team</div>
                </div>
            </div>

            <!-- Footer -->
            <div class="footer">
                <div class="footer-content">
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