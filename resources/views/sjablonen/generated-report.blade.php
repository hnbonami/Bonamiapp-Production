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
        
        /* EXACTE KOPIE VAN PRINT-PERFECT STYLING VOOR BIKEFIT COMPONENTEN */
        .page-content .flex {
            display: flex !important;
        }
        
        .page-content .flex-col {
            flex-direction: column !important;
        }
        
        .page-content .flex-row {
            flex-direction: row !important;
        }
        
        .page-content .gap-8 {
            gap: 2rem !important;
        }
        
        .page-content .items-center {
            align-items: center !important;
        }
        
        .page-content .w-full {
            width: 100% !important;
        }
        
        .page-content .max-w-md {
            max-width: 28rem !important;
        }
        
        .page-content .mx-auto {
            margin-left: auto !important;
            margin-right: auto !important;
        }
        
        .page-content .text-sm {
            font-size: 0.875rem !important;
        }
        
        .page-content .mb-4 {
            margin-bottom: 1rem !important;
        }
        
        .page-content .font-bold {
            font-weight: bold !important;
        }
        
        .page-content .text-blue-700 {
            color: #1d4ed8 !important;
        }
        
        /* RESPONSIVE CLASSES VOOR MEDIUM SCREENS EN GROTER */
        @media (min-width: 768px) {
            .page-content .md\\:flex-row {
                flex-direction: row !important;
            }
            
            .page-content .md\\:w-1\\/2 {
                width: 50% !important;
            }
        }
        
        /* BIKEFIT AFBEELDING STYLING - EXACT ALS PRINT-PERFECT */
        .page-content img[alt="Bikefit schema"] {
            max-width: 28rem !important;
            width: 100% !important;
            height: auto !important;
            margin-left: auto !important;
            margin-right: auto !important;
            display: block !important;
        }
        
        @media (min-width: 768px) {
            .page-content img[alt="Bikefit schema"] {
                width: 50% !important;
            }
        }
        
        /* TABEL STYLING VOOR BIKEFIT RESULTATEN */
        .page-content table {
            border-collapse: collapse !important;
            width: 100% !important;
            font-size: 0.875rem !important;
            margin-bottom: 1rem !important;
        }
        
        .page-content table td {
            padding: 0.25rem 0.5rem !important;
            border: none !important;
        }
        
        .page-content table td:first-child {
            font-weight: bold !important;
            color: #1d4ed8 !important;
            width: 2rem !important;
        }
        
        .page-content table td:nth-child(2) {
            width: auto !important;
        }
        
        .page-content table td:last-child {
            text-align: right !important;
            font-weight: 500 !important;
        }
    </style>
</head>
<body>
    <div class="no-print header-actions">
        <h1>{{ $template->naam ?? $template->name ?? 'Sjabloon' }} - {{ $klantModel->naam }}</h1>
        <div class="header-buttons">
            <button class="btn btn-primary" onclick="window.print()">🖨️ Afdrukken</button>
            <a href="/sjablonen/{{ $template->id }}/pdf" 
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