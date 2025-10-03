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
            font-family: 'DejaVu Sans', Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: white;
        }
        
        .report-page {
            width: 210mm;
            height: 297mm;
            margin: 0;
            padding: 0;
            background: white;
            position: relative;
            background-size: 210mm 297mm !important;
            background-position: 0 0 !important;
            background-repeat: no-repeat !important;
            background-attachment: local !important;
            page-break-after: always;
            overflow: hidden;
        }
        
        .report-page:last-child {
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
        
        @page {
            size: A4;
            margin: 0;
        }
        
        /* Verbeter tabel weergave */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        
        table td, table th {
            border: 1px solid #333;
            padding: 8px;
            text-align: left;
        }
        
        /* Verbeter tekst weergave */
        p, div, span {
            line-height: 1.4;
        }
        
        h1, h2, h3, h4, h5, h6 {
            margin-top: 0;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    @foreach($generatedPages as $index => $page)
        @if($page['is_url_page'])
            <div class="report-page">
                <div class="page-content">
                    <h3>Externe Pagina {{ $index + 1 }}</h3>
                    <p><strong>URL:</strong> {{ $page['url'] }}</p>
                    <p><em>Deze pagina bevat externe content die niet in de PDF kan worden weergegeven.</em></p>
                </div>
            </div>
        @else
            <div class="report-page">
                @if($page['background_image'])
                    <div class="background-image" style="position: absolute; top: 0; left: 0; width: 210mm; height: 297mm; z-index: -1;">
                        <img src="{{ public_path('backgrounds/' . $page['background_image']) }}" 
                             style="width: 210mm; height: 297mm; object-fit: cover; display: block;">
                    </div>
                @endif
                <div class="page-content">
                    {!! $page['content'] !!}
                </div>
            </div>
        @endif
    @endforeach
</body>
</html>