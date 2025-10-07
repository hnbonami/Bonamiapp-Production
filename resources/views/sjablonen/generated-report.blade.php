<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $template->naam ?? $template->name ?? 'Sjabloon' }} - {{ $klantModel->naam }}</title>
    <style>
        * {
            -webkit-print-color-adjust: exact !important;
            color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        
        html {
            width: 100%;
        }
        
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: #888a8d;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
            width: 100%;
        }
        
        .header-actions {
            background: white;
            padding: 20px;
            text-align: center;
            width: 100%;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            z-index: 1000;
            position: sticky;
            top: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            box-sizing: border-box;
        }
        
        .header-actions h1 {
            margin: 0 0 15px 0;
            color: #333;
        }
        
        .header-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .report-container {
            background: #888a8d;
            min-height: calc(100vh - 120px);
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            padding: 20px 0;
            box-sizing: border-box;
            position: relative;
        }
        
        .report-page {
            width: 210mm;
            height: 297mm;
            margin: 0 0 20mm 0;
            padding: 0;
            background: white;
            box-shadow: 0 0 20px rgba(0,0,0,0.3);
            position: relative;
            background-size: cover !important;
            background-position: center !important;
            background-repeat: no-repeat !important;
            overflow: hidden;
        }
        
        .report-page:last-child {
            margin-bottom: 0;
        }
        
        .page-content {
            padding: 20mm;
            height: calc(297mm - 40mm);
            width: calc(210mm - 40mm);
            box-sizing: border-box;
            position: relative;
            z-index: 1;
        }
        
        .no-print {
            display: block;
        }
        
        @media print {
            @page {
                size: A4;
                margin: 0;
            }
            
            * {
                -webkit-print-color-adjust: exact !important;
                color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            html, body { 
                margin: 0;
                padding: 0;
                background: white;
                font-size: 12pt;
            }
            
            .header-actions { 
                display: none !important; 
            }
            
            .report-container {
                background: white !important;
                padding: 0 !important;
                margin: 0 !important;
                display: block !important;
            }
            
            .report-page { 
                margin: 0 !important;
                padding: 0 !important;
                width: 210mm !important;
                height: 297mm !important;
                background-color: white !important;
                box-shadow: none !important;
                page-break-after: page !important;
                page-break-inside: avoid !important;
                display: block !important;
            }
            
            .report-page:last-child {
                page-break-after: avoid !important;
            }
            
            .page-content {
                padding: 20mm !important;
                width: 170mm !important;
                height: 257mm !important;
                margin: 0 !important;
                box-sizing: border-box !important;
            }
            
            .no-print { 
                display: none !important; 
            }
        }
        
        .btn {
            padding: 12px 24px;
            margin: 0 4px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
            transition: all 0.2s ease;
            box-sizing: border-box;
            min-width: 140px;
            text-align: center;
        }
        
        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        
        .btn:active, .btn:focus {
            transform: translateY(0);
            outline: none;
        }
        
        .btn-primary { 
            background: #007bff; 
            color: white; 
        }
        
        .btn-primary:hover { 
            background: #0056b3; 
        }
        
        .btn-success { 
            background: #28a745; 
            color: white; 
        }
        
        .btn-success:hover { 
            background: #1e7e34; 
        }
        
        .btn-secondary { 
            background: #6c757d; 
            color: white; 
        }
        
        .btn-secondary:hover { 
            background: #545b62; 
        }
        
        .btn-danger { 
            background: #dc3545; 
            color: white; 
        }
        
        .btn-danger:hover { 
            background: #c82333; 
        }
        
        /* ALLEEN BIKEFIT RESULTATEN COMPONENTEN - EXACT ALS RESULTS PAGINA */
        
        /* Specifieke styling voor de bikefit resultaten container - MEER NAAR LINKS */
        .page-content div[style*="width: 500px"] {
            width: 500px !important;
            margin: 0 0 0 -9px !important;
            min-height: auto !important;
            position: relative !important;
        }
        
        /* Specifieke styling voor bikefit afbeelding - 20% GROTER DAN ORIGINEEL (320px * 1.2 = 384px) */
        .page-content img[src*="bikefit-schema"] {
            width: 500px !important;
            height: auto !important;
            display: block !important;
            margin: 0 auto !important;
        }
        
        /* PROGNOSE TABEL SPECIFIEK - EXACT ALS BIKEFIT RESULTATEN */
        .page-content h4:contains("Prognose") + div table,
        .page-content div:has(img[src*="bikefit-schema"]) table {
            width: 100% !important;
            font-size: 11.9px !important;
            border-collapse: collapse !important;
            table-layout: fixed !important;
            margin-bottom: 0 !important;
        }
        
        .page-content h4:contains("Prognose") + div tr:nth-child(odd),
        .page-content div:has(img[src*="bikefit-schema"]) tr:nth-child(odd) {
            background-color: white !important;
        }
        
        .page-content h4:contains("Prognose") + div tr:nth-child(even),
        .page-content div:has(img[src*="bikefit-schema"]) tr:nth-child(even) {
            background-color: #f9fafb !important;
        }
        
        .page-content h4:contains("Prognose") + div td,
        .page-content div:has(img[src*="bikefit-schema"]) td {
            padding: 0.25rem 0.5rem !important;
            border-bottom: 1px solid #d1d5db !important;
            font-size: 11.9px !important;
        }
        
        .page-content h4:contains("Prognose") + div td:first-child,
        .page-content div:has(img[src*="bikefit-schema"]) td:first-child {
            font-weight: bold !important;
            color: #000000 !important;
            text-align: center !important;
            width: 2rem !important;
        }
        
        .page-content h4:contains("Prognose") + div td:nth-child(2) {
            text-align: left !important;
        }
        
        .page-content h4:contains("Prognose") + div td:last-child,
        .page-content div:has(img[src*="bikefit-schema"]) td:last-child {
            text-align: right !important;
        }
        
        /* Specifieke styling voor bikefit tabel container - GEEN EXTRA RUIMTE */
        .page-content div[style*="border: 1px solid #d1d5db"] {
            width: 500px !important;
            border: 1px solid #d1d5db !important;
            border-radius: 8px !important;
            overflow: hidden !important;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
            background-color: white !important;
            margin-bottom: 0 !important;
            padding-bottom: 0 !important;
        }
        
        /* Specifieke styling voor bikefit tabel */
        .page-content table[style*="table-layout: fixed"] {
            width: 100% !important;
            font-size: 11.9px !important;
            border-collapse: collapse !important;
            table-layout: fixed !important;
            margin-bottom: 0 !important;
        }
        
        /* Bikefit tabel rijen */
        .page-content table[style*="table-layout: fixed"] tr.bg-white {
            background-color: white !important;
        }
        
        .page-content table[style*="table-layout: fixed"] tr.bg-gray-50 {
            background-color: #f9fafb !important;
        }
        
        /* Bikefit tabel cellen */
        .page-content table[style*="table-layout: fixed"] td {
            padding: 0.25rem 0.5rem !important;
            border-bottom: 1px solid #d1d5db !important;
            font-size: 11.9px !important;
        }
        
        .page-content table[style*="table-layout: fixed"] td.font-bold {
            font-weight: bold !important;
            color: #000000 !important;
            text-align: center !important;
        }
        
        /* LAATSTE RIJ GEEN BORDER BOTTOM */
        .page-content table[style*="table-layout: fixed"] tr:last-child td {
            border-bottom: none !important;
        }
        
        /* Bikefit input velden - KLEINER LETTERTYPE VOOR CIJFERS */
        .page-content input[name*="zadelhoogte"],
        .page-content input[name*="zadelterugstand"],
        .page-content input[name*="reach"],
        .page-content input[name*="drop"],
        .page-content input[name*="cranklengte"],
        .page-content input[name*="stuurbreedte"] {
            padding: 0.25rem 0.5rem !important;
            width: 4rem !important;
            text-align: right !important;
            background: transparent !important;
            border: 0 !important;
            outline: none !important;
            font-size: 11.9px !important;
            font-weight: 400 !important;
            font-family: Arial, sans-serif !important;
        }
    </style>
</head>
<body>
    <div class="no-print header-actions">
        <h1>{{ $template->naam ?? $template->name ?? 'Sjabloon' }} - {{ $klantModel->naam }}</h1>
        <div class="header-buttons">
            <button class="btn btn-primary" onclick="window.print()">🖨️ Afdrukken</button>
            <button class="btn btn-success" onclick="printToPDF()">📄 Afdruk PDF</button>
            <a href="/sjablonen/{{ $template->id }}/pdf?klant={{ $klantModel->id ?? '' }}&context=generated-report" 
               class="btn btn-danger">📄 Download PDF</a>
            <a href="/sjablonen/{{ $template->id }}/edit" class="btn btn-secondary">✏️ Sjabloon Bewerken</a>
        </div>
    </div>

    <div class="report-container">
        @forelse($generatedPages as $index => $page)
            @if($page['is_url_page'])
                <div class="report-page">
                    <div class="page-content">
                        <h3>Externe Pagina {{ $index + 1 }}</h3>
                        <p><strong>URL:</strong> <a href="{{ $page['url'] }}" target="_blank">{{ $page['url'] }}</a></p>
                        <iframe src="{{ $page['url'] }}" width="100%" height="80%" style="border: 1px solid #ccc;"></iframe>
                    </div>
                </div>
            @else
                <div class="report-page" 
                     @if($page['background_image'])
                         style="background-image: url('/backgrounds/{{ $page['background_image'] }}');"
                     @endif>
                    <div class="page-content">
                        {!! $page['content'] !!}
                    </div>
                </div>
            @endif
        @empty
            <div class="report-page">
                <div class="page-content">
                    <h2>Geen pagina's gevonden</h2>
                    <p>Dit sjabloon heeft nog geen pagina's.</p>
                </div>
            </div>
        @endforelse
    </div>

    <script>
        // NEW: AFDRUK PDF FUNCTION - Shows instructions and opens print dialog
        function printToPDF() {
            console.log('📄 Showing PDF print instructions and opening print dialog...');
            
            // Show clear step-by-step instructions
            showNotification('🖨️ <strong>PDF Afdrukken:</strong><br>1️⃣ Kies "Opslaan als PDF" in het print venster<br>2️⃣ Klik "Opslaan" - Klaar!<br><em>Het print venster wordt nu automatisch geopend...</em>', 'success');
            
            // Open print dialog after a short delay so user can read the notification
            setTimeout(() => {
                window.print();
            }, 1000);
        }

        // NOTIFICATION SYSTEM
        function showNotification(message, type) {
            // Remove existing notifications
            const existing = document.querySelectorAll('.pdf-notification');
            existing.forEach(n => n.remove());
            
            // Create notification
            const notification = document.createElement('div');
            notification.className = 'pdf-notification';
            notification.style.cssText = `
                position: fixed;
                top: 80px;
                right: 20px;
                background: ${type === 'success' ? '#28a745' : '#3b82f6'};
                color: white;
                padding: 15px 18px;
                border-radius: 10px;
                font-size: 13px;
                font-weight: 500;
                box-shadow: 0 10px 25px rgba(0,0,0,0.2);
                z-index: 9999;
                max-width: 280px;
                animation: slideIn 0.3s ease-out;
                line-height: 1.3;
            `;
            
            notification.innerHTML = `
                <div style="display: flex; align-items: flex-start; gap: 12px;">
                    <div style="flex: 1;">${message}</div>
                    <button onclick="this.parentElement.parentElement.remove()" 
                            style="background: rgba(255,255,255,0.2); border: none; color: white; 
                                   border-radius: 50%; width: 28px; height: 28px; cursor: pointer; 
                                   font-size: 16px; display: flex; align-items: center; justify-content: center;">×</button>
                </div>
            `;
            
            // Add CSS animation
            if (!document.querySelector('#notification-styles')) {
                const styles = document.createElement('style');
                styles.id = 'notification-styles';
                styles.textContent = `
                    @keyframes slideIn {
                        from { transform: translateX(100%); opacity: 0; }
                        to { transform: translateX(0); opacity: 1; }
                    }
                `;
                document.head.appendChild(styles);
            }
            
            document.body.appendChild(notification);
            
            // Auto remove after 10 seconds
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.style.animation = 'slideIn 0.3s ease-out reverse';
                    setTimeout(() => notification.remove(), 300);
                }
            }, 10000);
        }

        // Simple print optimization - no layout changes
        window.addEventListener('beforeprint', function() {
            console.log('🖨️ SIMPLE PRINT PREPARATION');
            
            const pages = document.querySelectorAll('.report-page');
            pages.forEach(function(page, index) {
                // Only remove box shadow for print
                page.style.boxShadow = 'none';
                
                // Background optimization for print only
                const style = page.getAttribute('style');
                if (style && style.includes('background-image')) {
                    page.style.backgroundSize = '210mm 297mm';
                    page.style.backgroundPosition = '0mm 0mm';
                    page.style.backgroundRepeat = 'no-repeat';
                }
            });
        });

        window.addEventListener('afterprint', function() {
            console.log('🔄 SIMPLE RESTORE');
            
            const pages = document.querySelectorAll('.report-page');
            pages.forEach(function(page) {
                // Restore box shadow
                page.style.boxShadow = '0 0 20px rgba(0,0,0,0.3)';
                
                // Restore background for screen
                const style = page.getAttribute('style');
                if (style && style.includes('background-image')) {
                    page.style.backgroundSize = 'cover';
                    page.style.backgroundPosition = 'center';
                }
            });
        });
    </script>
</body>
</html>