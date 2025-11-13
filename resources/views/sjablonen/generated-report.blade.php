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
            overflow: visible !important;
        }
        
        /* FORCEER ALLE TEKST ZICHTBAAR BIJ PRINT - FIX VOOR VERDWIJNENDE NAAM/DATUM */
        .page-content *,
        .page-content h1,
        .page-content h2,
        .page-content h3,
        .page-content h4,
        .page-content p,
        .page-content span,
        .page-content div,
        .page-content strong,
        .report-page * {
            opacity: 1 !important;
            visibility: visible !important;
            color: inherit !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        
        /* Remove ALL scrollbars from page content */
        .page-content,
        .page-content *,
        .page-content div,
        .page-content p,
        .page-content span,
        .page-content iframe {
            overflow: visible !important;
            overflow-x: visible !important;
            overflow-y: visible !important;
            scrollbar-width: none !important;
            -ms-overflow-style: none !important;
        }
        
        .page-content::-webkit-scrollbar,
        .page-content *::-webkit-scrollbar {
            display: none !important;
            width: 0 !important;
            height: 0 !important;
        }
        
        /* Force no scroll for any container */
        .page-content [style*="overflow"],
        .page-content [style*="scroll"] {
            overflow: visible !important;
        }
        
        /* EXTRA: Remove scroll from specific container classes */
        .page-content .container,
        .page-content .scroll-container,
        .page-content [class*="scroll"],
        .page-content [class*="overflow"] {
            overflow: visible !important;
            max-height: none !important;
            height: auto !important;
        }
        
        /* AGGRESSIVE: Force all elements to be visible */
        .page-content * {
            overflow: visible !important;
            overflow-x: visible !important;
            overflow-y: visible !important;
            max-height: none !important;
            height: auto !important;
        }
        
        /* BUT: Preserve color styling and visual elements */
        .page-content .scorebar,
        .page-content .scorebar *,
        .page-content .mobility-bar,
        .page-content .mobility-bar *,
        .page-content .mobility-bar-segment,
        .page-content .mobility-bar-segment.heel-laag,
        .page-content .mobility-bar-segment.laag,
        .page-content .mobility-bar-segment.gemiddeld,
        .page-content .mobility-bar-segment.hoog,
        .page-content .mobility-bar-segment.heel-hoog,
        .page-content .mobility-bar-segment.selected,
        .page-content [style*="background"],
        .page-content [style*="color"],
        .page-content [class*="bg-"],
        .page-content [class*="text-"] {
            /* Keep original styling for colors and backgrounds */
        }
        
        /* Specifically preserve mobility bar colors */
        /* Specifically preserve mobility bar colors - EXACT from _mobility_table_report.blade.php */
        .mobility-bar {
            display: flex !important;
            width: 120px !important;
            height: 18px !important;
            border-radius: 6px !important;
            overflow: hidden !important;
            margin: 0 auto 4px auto !important;
            box-shadow: 0 1px 2px #ddd !important;
        }
        .mobility-bar-segment {
            flex: 1 !important;
            height: 100% !important;
            position: relative !important;
        }
        
        /* Make sure ONLY the header row has consistent blue background */
        .mobility-report-table thead tr,
        .mobility-report-table thead th,
        .mobility-report-table thead td,
        .page-content table thead tr,
        .page-content table thead th,
        .page-content table thead td,
        .report-container table thead tr,
        .report-container table thead th,
        .report-container table thead td {
            background-color: #c8e1eb !important;
        }
        
        /* Round the corners of mobility bars */
        .mobility-bar {
            border-radius: 8px !important;
        }
        
        .mobility-bar-segment:first-child {
            border-top-left-radius: 8px !important;
            border-bottom-left-radius: 8px !important;
        }
        
        .mobility-bar-segment:last-child {
            border-top-right-radius: 8px !important;
            border-bottom-right-radius: 8px !important;
        }
        
        .mobility-bar-segment.heel-laag,
        .page-content .mobility-bar-segment.heel-laag,
        .report-container .mobility-bar-segment.heel-laag { 
            background: #ef4444 !important; 
            background-color: #ef4444 !important;
        }
        .mobility-bar-segment.laag,
        .page-content .mobility-bar-segment.laag,
        .report-container .mobility-bar-segment.laag { 
            background: #f59e42 !important; 
            background-color: #f59e42 !important;
        }
        .mobility-bar-segment.gemiddeld,
        .page-content .mobility-bar-segment.gemiddeld,
        .report-container .mobility-bar-segment.gemiddeld { 
            background: #fde047 !important; 
            background-color: #fde047 !important;
        }
        .mobility-bar-segment.hoog,
        .page-content .mobility-bar-segment.hoog,
        .report-container .mobility-bar-segment.hoog { 
            background: #4ade80 !important; 
            background-color: #4ade80 !important;
        }
        .mobility-bar-segment.heel-hoog,
        .page-content .mobility-bar-segment.heel-hoog,
        .report-container .mobility-bar-segment.heel-hoog { 
            background: #16a34a !important; 
            background-color: #16a34a !important;
        }
        .mobility-bar-segment.selected::after,
        .page-content .mobility-bar-segment.selected::after,
        .report-container .mobility-bar-segment.selected::after {
            content: '' !important;
            position: absolute !important;
            top: 2px !important;
            left: 2px !important;
            right: 2px !important;
            bottom: 2px !important;
            border: 2px solid #222 !important;
            border-radius: 4px !important;
            pointer-events: none !important;
        }
        .page-content .mobility-bar-segment.selected::after {
            content: '';
            position: absolute;
            top: 2px;
            left: 2px;
            right: 2px;
            bottom: 2px;
            border: 2px solid #222;
            border-radius: 4px;
            pointer-events: none;
        }
        
        /* Specifically preserve scorebar functionality */
        .page-content .scorebar,
        .page-content .scorebar div,
        .report-container .scorebar,
        .report-container .scorebar div {
            background: inherit !important;
            background-color: inherit !important;
            box-shadow: inherit !important;
            border-radius: inherit !important;
            transition: inherit !important;
            flex: inherit !important;
            height: inherit !important;
            display: inherit !important;
            width: inherit !important;
            border: inherit !important;
            overflow: inherit !important;
        }
        
        /* Target specific CKEditor containers that might cause scroll */
        .page-content .cke_editable,
        .page-content .cke_contents,
        .page-content [contenteditable],
        .page-content iframe,
        .page-content .text-container,
        .page-content .content-area {
            overflow: visible !important;
            max-height: none !important;
            height: auto !important;
            resize: none !important;
        }
        
        /* Fix datum display - hide time part (00:00:00) */
        .page-content [style*="date"],
        .page-content time,
        .page-content .date,
        .page-content span:contains("00:00:00"),
        .page-content div:contains("00:00:00") {
            font-size: inherit !important;
        }
        
        /* Hide time part that shows 00:00:00 */
        .page-content *[title*="00:00:00"]::after,
        .page-content span[title*="00:00:00"],
        .page-content time[datetime*="00:00:00"] {
            display: none !important;
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
            
            /* FORCEER ALLE TEKST ZICHTBAAR BIJ PRINT */
            .page-content *,
            .page-content h1,
            .page-content h2,
            .page-content h3,
            .page-content p,
            .page-content strong,
            .report-page * {
                opacity: 1 !important;
                visibility: visible !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color: inherit !important;
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
            {{-- Verborgen knoppen: Download PDF en Sjabloon Bewerken --}}
            {{--
            <a href="/sjablonen/{{ $template->id }}/pdf?klant={{ $klantModel->id ?? '' }}&context=generated-report" 
               class="btn btn-danger">📄 Download PDF</a>
            <a href="/sjablonen/{{ $template->id }}/edit" class="btn btn-secondary">✏️ Sjabloon Bewerken</a>
            --}}
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
                @php
                    // Genereer correcte background URL op basis van environment
                    $backgroundUrl = null;
                    if ($page['background_image']) {
                        $backgroundUrl = app()->environment('production') 
                            ? asset('uploads/backgrounds/' . $page['background_image'])
                            : asset('backgrounds/' . $page['background_image']);
                    }
                @endphp
                @php
                    // Fix alle storage URLs in content naar uploads voor productie
                    $pageContent = $page['content'];
                    if (app()->environment('production')) {
                        // Vervang alle /storage/ URLs naar /uploads/
                        $pageContent = str_replace('/storage/rapporten/', '/uploads/rapporten/', $pageContent);
                        $pageContent = str_replace('src="/storage/', 'src="/uploads/', $pageContent);
                        $pageContent = str_replace("src='/storage/", "src='/uploads/", $pageContent);
                        
                        // Ook voor volledige URLs
                        $currentDomain = request()->getSchemeAndHttpHost();
                        $pageContent = str_replace($currentDomain . '/storage/', $currentDomain . '/uploads/', $pageContent);
                    }
                @endphp
                <div class="report-page" 
                     @if($backgroundUrl)
                         style="background-image: url('{{ $backgroundUrl }}');"
                     @endif>
                    <div class="page-content">
                        {!! $pageContent !!}
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

        // Remove all scrollbars after page load
        document.addEventListener('DOMContentLoaded', function() {
            // Force remove scrollbars from all elements
            const allElements = document.querySelectorAll('.page-content, .page-content *');
            allElements.forEach(function(element) {
                element.style.overflow = 'visible';
                element.style.overflowX = 'visible';
                element.style.overflowY = 'visible';
                element.style.scrollbarWidth = 'none';
                element.style.msOverflowStyle = 'none';
            });
        });

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
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Remove scroll containers and fix overflow
    function removeScrollContainers() {
        const allElements = document.querySelectorAll('.page-content, .page-content *');
        allElements.forEach(element => {
            // AGGRESSIVE: Remove ALL overflow styling
            element.style.overflow = 'visible';
            element.style.overflowY = 'visible';
            element.style.overflowX = 'visible';
            element.style.maxHeight = 'none';
            element.style.height = 'auto';
            
            // Remove specific problematic attributes
            if (element.hasAttribute('scrolling')) {
                element.removeAttribute('scrolling');
            }
            
            // Target CKEditor and contenteditable elements specifically
            if (element.classList.contains('cke_editable') || 
                element.hasAttribute('contenteditable') ||
                element.tagName === 'IFRAME') {
                element.style.overflow = 'visible';
                element.style.resize = 'none';
                element.style.maxHeight = 'none';
                element.style.height = 'auto';
            }
            
            // Remove scroll from any container with text content
            if (element.textContent && element.textContent.trim().length > 0) {
                element.style.overflow = 'visible';
                element.style.overflowWrap = 'break-word';
                element.style.wordWrap = 'break-word';
            }
        });
        
        // EXTRA: Force all computed styles to visible
        const computedElements = document.querySelectorAll('.page-content *');
        computedElements.forEach(element => {
            const computedStyle = window.getComputedStyle(element);
            if (computedStyle.overflow !== 'visible' || 
                computedStyle.overflowY !== 'visible' || 
                computedStyle.overflowX !== 'visible') {
                element.style.setProperty('overflow', 'visible', 'important');
                element.style.setProperty('overflow-x', 'visible', 'important');
                element.style.setProperty('overflow-y', 'visible', 'important');
            }
        });
    }
    
    // Fix datum formatting - remove time parts
    function fixDateFormatting() {
        const textNodes = document.createTreeWalker(
            document.querySelector('.report-container'),
            NodeFilter.SHOW_TEXT,
            null,
            false
        );
        
        let textNode;
        while (textNode = textNodes.nextNode()) {
            // Remove time part from dates (00:00:00)
            if (textNode.textContent.includes('00:00:00')) {
                textNode.textContent = textNode.textContent.replace(/ 00:00:00/g, '');
                textNode.textContent = textNode.textContent.replace(/T00:00:00/g, '');
            }
            
            // Clean up other common time formats
            textNode.textContent = textNode.textContent.replace(/ 00:00$/g, '');
            textNode.textContent = textNode.textContent.replace(/T00:00$/g, '');
        }
        
        // Also check for datetime attributes and title attributes
        const dateElements = document.querySelectorAll('[datetime], [title*="00:00"]');
        dateElements.forEach(element => {
            if (element.textContent.includes('00:00')) {
                element.textContent = element.textContent.replace(/ 00:00:00/g, '');
                element.textContent = element.textContent.replace(/T00:00:00/g, '');
            }
        });
    }
    
    // Run fixes
    removeScrollContainers();
    fixDateFormatting();
    
    // PRESERVE: Re-run scorebar coloring after our aggressive cleaning
    preserveScorebarStyling();
    
    // Run again after a short delay to catch dynamically loaded content
    setTimeout(() => {
        removeScrollContainers();
        fixDateFormatting();
        preserveScorebarStyling();
    }, 500);
    
    // Function to preserve scorebar styling
    function preserveScorebarStyling() {
        // EXACT colors from _mobility_results.blade.php
        const colors = ['#43a047','#aeea00','#fdd835','#fb8c00','#e53935'];
        const labels = ['Heel hoog','Hoog','Gemiddeld','Laag','Heel laag'];
        
        document.querySelectorAll('.scorebar').forEach(function(bar) {
            const score = bar.getAttribute('data-score');
            if (!score) return;
            
            let idx = labels.indexOf(score);
            if(idx === -1) idx = 2; // default: gemiddeld
            
            // Rebuild the scorebar with colors - EXACTLY like the original
            bar.innerHTML = '';
            bar.style.display = 'flex';
            bar.style.width = '180px';
            bar.style.height = '28px';
            bar.style.borderRadius = '14px';
            bar.style.overflow = 'hidden';
            bar.style.border = '2px solid #ddd';
            bar.style.boxShadow = '0 2px 8px rgba(0,0,0,0.07)';
            
            for(let i=0;i<5;i++) {
                let seg = document.createElement('div');
                seg.style.flex = '1';
                seg.style.height = '100%';
                seg.style.background = colors[i];
                seg.style.backgroundColor = colors[i];
                seg.style.setProperty('background', colors[i], 'important');
                seg.style.setProperty('background-color', colors[i], 'important');
                seg.style.transition = 'box-shadow 0.3s';
                if(i === idx) {
                    seg.style.boxShadow = '0 0 12px 2px #333';
                    seg.style.borderRadius = '14px';
                }
                bar.appendChild(seg);
            }
        });
        
        // Preserve mobility-bar styling - EXACT from _mobility_table_report.blade.php
        const mobilityColors = {
            'heel-laag': '#ef4444',
            'laag': '#f59e42', 
            'gemiddeld': '#fde047',
            'hoog': '#4ade80',
            'heel-hoog': '#16a34a'
        };
        
        // Force apply colors to all mobility segments
        document.querySelectorAll('.mobility-bar-segment').forEach(function(segment) {
            Object.keys(mobilityColors).forEach(function(className) {
                if (segment.classList.contains(className)) {
                    const color = mobilityColors[className];
                    segment.style.background = color;
                    segment.style.backgroundColor = color;
                    segment.style.setProperty('background', color, 'important');
                    segment.style.setProperty('background-color', color, 'important');
                    segment.style.flex = '1';
                    segment.style.height = '100%';
                    segment.style.position = 'relative';
                }
            });
            
            // Handle selected styling
            if (segment.classList.contains('selected')) {
                segment.style.position = 'relative';
                // Ensure the ::after pseudo-element works
                const afterStyle = `
                    .mobility-bar-segment.selected::after {
                        content: '';
                        position: absolute;
                        top: 2px;
                        left: 2px;
                        right: 2px;
                        bottom: 2px;
                        border: 2px solid #222;
                        border-radius: 4px;
                        pointer-events: none;
                    }
                `;
                if (!document.querySelector('#mobility-selected-style')) {
                    const style = document.createElement('style');
                    style.id = 'mobility-selected-style';
                    style.textContent = afterStyle;
                    document.head.appendChild(style);
                }
            }
        });
        
        // Ensure mobility-bar containers have correct styling
        document.querySelectorAll('.mobility-bar').forEach(function(bar) {
            bar.style.display = 'flex';
            bar.style.width = '120px';
            bar.style.height = '18px';
            bar.style.borderRadius = '6px';
            bar.style.overflow = 'hidden';
            bar.style.margin = '0 auto 4px auto';
            bar.style.boxShadow = '0 1px 2px #ddd';
        });
    }
});
</script>
</html>