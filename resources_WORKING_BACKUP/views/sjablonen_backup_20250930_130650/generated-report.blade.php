<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $template->name }} - {{ $klantModel->naam }}</title>
    <style>
        * {
            -webkit-print-color-adjust: exact !important;
            color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: #888a8d;
            display: flex;
            flex-direction: column;
            align-items: center;
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
        }
        
        .report-container {
            background: #888a8d;
            min-height: 100vh;
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px 0;
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
            page-break-after: always;
            overflow: hidden;
        }
        
        .report-page:last-child {
            margin-bottom: 0;
            page-break-after: avoid;
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
            * {
                -webkit-print-color-adjust: exact !important;
                color-adjust: exact !important;
                print-color-adjust: exact !important;
                box-sizing: border-box !important;
            }
            
            @page {
                size: A4 portrait;
                margin: 0mm;
            }
            
            html, body { 
                width: 210mm;
                height: auto;
                margin: 0 !important;
                padding: 0 !important;
                background: white !important;
                font-size: 12pt;
                line-height: 1.2;
            }
            
            .header-actions { 
                display: none !important; 
            }
            
            .report-container {
                background: white !important;
                padding: 0 !important;
                margin: 0 !important;
                width: 210mm !important;
                display: block !important;
                flex-direction: initial !important;
                align-items: initial !important;
            }
            
            .report-page { 
                display: block !important;
                box-shadow: none !important; 
                margin: 0 !important;
                padding: 0 !important;
                width: 210mm !important;
                height: 297mm !important;
                max-width: 210mm !important;
                max-height: 297mm !important;
                min-width: 210mm !important;
                min-height: 297mm !important;
                page-break-before: auto !important;
                page-break-inside: avoid !important;
                position: relative !important;
                overflow: hidden !important;
                background-color: white !important;
            }
            
            .report-page:not(:last-child) {
                page-break-after: always !important;
            }
            
            .report-page:last-child {
                page-break-after: avoid !important;
            }
            
            .report-page[style*="background-image"] {
                background-size: 210mm 297mm !important;
                background-position: 0mm 0mm !important;
                background-repeat: no-repeat !important;
                background-attachment: scroll !important;
            }
            
            .page-content {
                display: block !important;
                padding: 20mm !important;
                height: 257mm !important;
                width: 170mm !important;
                max-width: 170mm !important;
                max-height: 257mm !important;
                position: relative !important;
                z-index: 2 !important;
                box-sizing: border-box !important;
                overflow: hidden !important;
                margin: 0 !important;
            }
            
            .no-print { 
                display: none !important; 
            }
            
            /* Extra specificiteit voor Safari/WebKit */
            .report-page + .report-page {
                page-break-before: always !important;
                margin-top: 0 !important;
            }
        }
        
        .btn {
            padding: 12px 24px;
            margin: 0 8px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
            transition: all 0.2s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        .btn-primary { background: #007bff; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-danger { background: #dc3545; color: white; }
    </style>
</head>
<body>
    <div class="no-print header-actions">
        <h1 style="margin-bottom: 15px; color: #333;">{{ $template->name }} - {{ $klantModel->naam }}</h1>
        <button class="btn btn-primary" onclick="window.print()">🖨️ Afdrukken</button>
        <a href="{{ route('sjablonen.generate-pdf', ['template' => $template->id, 'klant' => $klantModel->id, 'test_id' => request()->route('test_id'), 'type' => request()->route('type')]) }}" 
           class="btn btn-danger">📄 Export naar PDF</a>
        <a href="{{ route('sjablonen.edit', $template) }}" class="btn btn-secondary">✏️ Sjabloon Bewerken</a>
        <a href="{{ route('klanten.show', $klantModel) }}" class="btn btn-success">👤 Terug naar Klant</a>
    </div>

    <div class="report-container">
        @foreach($generatedPages as $index => $page)
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
        @endforeach
    </div>

    <script>
        // Advanced print optimization
        window.addEventListener('beforeprint', function() {
            console.log('🖨️ PRINT PREPARATION STARTING');
            
            // Remove flexbox from container to prevent overlap
            const container = document.querySelector('.report-container');
            if (container) {
                container.style.display = 'block';
                container.style.flexDirection = 'initial';
                container.style.alignItems = 'initial';
                container.style.justifyContent = 'initial';
                container.style.minHeight = 'initial';
                console.log('✅ Container display fixed');
            }
            
            // Process each page individually
            const pages = document.querySelectorAll('.report-page');
            console.log(`📄 Processing ${pages.length} pages`);
            
            pages.forEach(function(page, index) {
                console.log(`🔧 Configuring page ${index + 1}`);
                
                // Remove any transforms or positioning that could cause overlap
                page.style.transform = 'none';
                page.style.position = 'relative';
                page.style.float = 'none';
                page.style.clear = 'both';
                
                // Force exact A4 dimensions
                page.style.width = '210mm';
                page.style.height = '297mm';
                page.style.minWidth = '210mm';
                page.style.minHeight = '297mm';
                page.style.maxWidth = '210mm';
                page.style.maxHeight = '297mm';
                page.style.margin = '0';
                page.style.padding = '0';
                page.style.overflow = 'hidden';
                page.style.display = 'block';
                
                // Simple page break logic
                page.style.pageBreakInside = 'avoid';
                
                if (index === pages.length - 1) {
                    page.style.pageBreakAfter = 'avoid';
                } else {
                    page.style.pageBreakAfter = 'page';
                }
                
                // Background handling
                const style = page.getAttribute('style');
                if (style && style.includes('background-image')) {
                    console.log(`🎨 Setting background for page ${index + 1}`);
                    page.style.backgroundSize = '210mm 297mm';
                    page.style.backgroundPosition = '0mm 0mm';
                    page.style.backgroundRepeat = 'no-repeat';
                    page.style.backgroundAttachment = 'scroll';
                }
                
                // Content container
                const content = page.querySelector('.page-content');
                if (content) {
                    content.style.display = 'block';
                    content.style.padding = '20mm';
                    content.style.width = '170mm';
                    content.style.height = '257mm';
                    content.style.maxWidth = '170mm';
                    content.style.maxHeight = '257mm';
                    content.style.boxSizing = 'border-box';
                    content.style.position = 'relative';
                    content.style.zIndex = '2';
                    content.style.overflow = 'hidden';
                    content.style.margin = '0';
                }
            });
            
            // Set body and html
            document.documentElement.style.width = '210mm';
            document.documentElement.style.height = 'auto';
            document.body.style.margin = '0';
            document.body.style.padding = '0';
            document.body.style.width = '210mm';
            document.body.style.height = 'auto';
            document.body.style.display = 'block';
            
            console.log('✅ PRINT PREPARATION COMPLETE');
        });

        window.addEventListener('afterprint', function() {
            console.log('🔄 RESETTING TO NORMAL VIEW');
            
            // Reset container
            const container = document.querySelector('.report-container');
            if (container) {
                container.style.display = 'flex';
                container.style.flexDirection = 'column';
                container.style.alignItems = 'center';
                container.style.minHeight = '100vh';
            }
            
            // Reset pages
            const pages = document.querySelectorAll('.report-page');
            pages.forEach(function(page, index) {
                page.style.width = '210mm';
                page.style.height = '297mm';
                page.style.margin = '0 0 20mm 0';
                page.style.transform = '';
                
                const style = page.getAttribute('style');
                if (style && style.includes('background-image')) {
                    page.style.backgroundSize = 'cover';
                    page.style.backgroundPosition = 'center';
                }
            });
            
            console.log('✅ RESET COMPLETE');
        });

        // Initialize on load
        document.addEventListener('DOMContentLoaded', function() {
            const pages = document.querySelectorAll('.report-page');
            console.log(`📋 Report loaded with ${pages.length} pages`);
            
            // Ensure proper spacing between pages in normal view
            pages.forEach(function(page, index) {
                if (index > 0) {
                    page.style.marginTop = '20mm';
                }
            });
        });
    </script>
</body>
</html>