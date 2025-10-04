<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bikefit Rapport - {{ $klant->naam }}</title>
    
    <!-- Include Tailwind CSS en andere app styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: #888a8d !important;
            margin: 0;
            padding: 0;
        }
        
        /* Exacte kopie van alle CSS uit report_preview.blade.php */
        .a4-preview-content *:not(.a4-preview-content) {
            background: transparent !important;
        }
        
        body {
            background: #888a8d !important;
            margin: 0;
            padding: 0;
        }
        
        .a4-preview {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: transparent;
            margin: 0;
            padding: 40px 0;
            box-sizing: border-box;
        }
        
        .a4-preview-content *:not(.a4-preview-content) {
            background: transparent !important;
        }
        
        .a4-preview-content {
            width: 210mm;
            height: 297mm;
            box-shadow: 0 4px 24px rgba(0,0,0,0.15);
            border-radius: 6px;
            margin-bottom: 40px;
            position: relative;
            display: block;
            overflow: visible;
            padding: 0;
            background-color: #fff;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }
        
        .a4-preview-content > div[style*='background'] {
            width: 210mm !important;
            height: 297mm !important;
            background-size: contain !important;
        }
        
        /* Verwijderd - dubbele @media print regel */
        
        /* Behoud achtergrondafbeeldingen en andere styling */
        * {
            -webkit-print-color-adjust: exact !important;
            color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        
        .print-container {
            width: 100%;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            padding: 20px;
            background: transparent;
            box-sizing: border-box;
        }
        
        .no-print {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }
        
        .print-btn {
            background: #3b82f6;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            font-size: 16px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .print-btn:hover {
            background: #2563eb;
        }
        
        @media print {
            .no-print {
                display: none !important;
            }
            
            body {
                background: white !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            
            .a4-preview {
                min-height: auto !important;
                display: block !important;
                background: white !important;
                margin: 0 !important;
                padding: 0 !important;
                box-sizing: border-box !important;
            }
            
            .a4-preview-content {
                width: 210mm !important;
                height: 297mm !important;
                box-shadow: none !important;
                border-radius: 0 !important;
                margin: 0 !important;
                margin-bottom: 0 !important;
                position: relative !important;
                display: block !important;
                overflow: visible !important;
                padding: 0 !important;
                background-color: #fff !important;
                background-size: cover !important;
                background-position: center !important;
                background-repeat: no-repeat !important;
                -webkit-print-color-adjust: exact !important;
                color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            * {
                -webkit-print-color-adjust: exact !important;
                color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
        
        @page {
            size: A4;
            margin: 5mm;
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button class="print-btn" onclick="window.print()">🖨️ Print/PDF</button>
        <button class="print-btn" onclick="downloadPDF()" style="background: #059669; margin-left: 10px;">📄 Download PDF</button>
        <button class="print-btn" onclick="closeWindow()" style="background: #6b7280; margin-left: 10px;">✕ Sluiten</button>
    </div>
    
    <div class="a4-preview">
        <div id="report-content">
            @if(isset($htmls) && is_array($htmls))
                @foreach($htmls as $pageIndex => $page)
                    @php
                        $bg = null;
                        if (!empty($images) && isset($images[$pageIndex]['path'])) {
                            $imgPath = ltrim($images[$pageIndex]['path'], '/');
                            $bg = url($imgPath);
                        } elseif (!empty($template->background)) {
                            $bg = url(ltrim($template->background, '/'));
                        }
                    @endphp
                    <div class="a4-preview-content" style="position:relative; overflow:hidden; background:#fff; min-height:297mm; height:297mm; width:210mm; background-image: url('{{ $bg ?? '' }}'); background-size: cover; background-position: center; background-repeat: no-repeat;">
                        <div style="min-height:100%;height:100%;width:100%;position:relative;z-index:1;">
                            {!! $page !!}
                        </div>
                    </div>
                @endforeach
            @else
                @php
                    $bg = null;
                    if (!empty($images) && isset($images[0]['path'])) {
                        $bg = url($images[0]['path']);
                    } elseif (!empty($template->background)) {
                        $bg = url($template->background);
                    }
                    $backgroundStyle = $bg ? "background-image: url('{$bg}'); background-size: cover; background-position: center;" : '';
                @endphp
                <div class="a4-preview-content" style="{{ $backgroundStyle }}">{!! $html ?? '' !!}</div>
            @endif
        </div>
    </div>
    
    <script>
        // Auto-focus op print knop
        document.querySelector('.print-btn').focus();
        
        // Download PDF functie - verbeterde versie met debugging
        function downloadPDF() {
            console.log('Download PDF functie aangeroepen');
            
            try {
                const currentUrl = window.location.href;
                console.log('Huidige URL:', currentUrl);
                
                const downloadUrl = currentUrl.replace('/print-perfect', '/generate-pdf');
                console.log('Download URL:', downloadUrl);
                
                // Directe download via window.location
                window.location.href = downloadUrl;
                
            } catch (error) {
                console.error('Download error:', error);
                alert('Er ging iets mis bij het downloaden van de PDF. Probeer de Print/PDF knop.');
            }
        }
        
        // Sluiten functie
        function closeWindow() {
            console.log('Sluiten functie aangeroepen');
            
            // Probeer verschillende manieren om het venster te sluiten
            if (window.opener) {
                window.close();
            } else {
                // Als het niet kan sluiten, ga terug in geschiedenis
                history.back();
            }
        }
        
        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'p') {
                e.preventDefault();
                window.print();
            }
            if (e.key === 'Escape') {
                window.close();
            }
        });
    </script>
</body>
</html>