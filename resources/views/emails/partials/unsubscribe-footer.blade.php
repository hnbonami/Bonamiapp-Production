{{-- Email Footer with Unsubscribe Options --}}
<div style="margin-top: 40px; padding-top: 30px; border-top: 2px solid #e5e7eb; background-color: #f8fafc; border-radius: 8px; padding: 30px;">
    <!-- Company Info -->
    <div style="text-align: center; margin-bottom: 25px;">
        <p style="margin: 0; font-size: 16px; font-weight: bold; color: #1f2937;">
            <strong>Bonami Sportcoaching</strong>
        </p>
        <p style="margin: 5px 0 0 0; font-size: 14px; color: #6b7280;">
            📍 Gaverstraat 2, Landegem<br>
            📧 info@bonami-sportcoaching.be<br>
            🌐 www.bonami-sportcoaching.be
        </p>
    </div>

    <!-- Unsubscribe Section -->
    <div style="background-color: #ffffff; border: 1px solid #d1d5db; border-radius: 6px; padding: 20px; margin-bottom: 20px;">
        <h4 style="margin: 0 0 15px 0; font-size: 14px; font-weight: bold; color: #374151; text-align: center;">
            📧 Email Voorkeuren Beheren
        </h4>
        
        <div style="text-align: center; font-size: 13px; color: #6b7280; line-height: 1.5;">
            <p style="margin: 0 0 15px 0;">
                Wil je bepaalde emails niet meer ontvangen? Kies hieronder wat het beste bij je past:
            </p>
            
            <!-- Unsubscribe Links -->
            <div style="margin-bottom: 15px;">
                <a href="@{{unsubscribe_url}}" 
                   style="display: inline-block; background-color: #dc2626; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px; font-size: 12px; margin: 0 5px 5px 0;">
                    🚫 Alle emails afmelden
                </a>
                
                <a href="@{{marketing_unsubscribe_url}}" 
                   style="display: inline-block; background-color: #f59e0b; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px; font-size: 12px; margin: 0 5px 5px 0;">
                    📢 Alleen marketing afmelden
                </a>
                
                <a href="@{{preferences_url}}" 
                   style="display: inline-block; background-color: #3b82f6; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px; font-size: 12px; margin: 0 5px 5px 0;">
                    ⚙️ Voorkeuren wijzigen
                </a>
            </div>
            
            <!-- Alternative text links for email clients that don't support styling -->
            <p style="margin: 10px 0 0 0; font-size: 11px; color: #9ca3af;">
                Links werken niet? Kopieer deze URLs:<br>
                Afmelden: @{{unsubscribe_url}}<br>
                Voorkeuren: @{{preferences_url}}
            </p>
        </div>
    </div>

    <!-- Legal/Privacy Info -->
    <div style="text-align: center; font-size: 11px; color: #9ca3af; line-height: 1.4;">
        <p style="margin: 0 0 10px 0;">
            📧 Je ontvangt deze email omdat je klant bent bij Bonami Sportcoaching.<br>
            🔒 Jouw privacy is belangrijk voor ons. We delen je gegevens nooit met derden.
        </p>
        
        <p style="margin: 0;">
            📍 Bonami Sportcoaching, Gaverstraat 2, Landegem, België<br>
            © {{ date('Y') }} Bonami Sportcoaching. Alle rechten voorbehouden.
        </p>
    </div>

    <!-- Email metadata for tracking -->
    <div style="font-size: 10px; color: #d1d5db; text-align: center; margin-top: 15px;">
        Email ID: @{{email_id}} | Verstuurd op: @{{datum}} om @{{tijd}}
    </div>
</div>