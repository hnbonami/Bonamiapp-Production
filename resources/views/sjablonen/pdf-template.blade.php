<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $template->naam ?? 'Sjabloon' }} - {{ $klantModel->naam }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        @page {
            size: A4 portrait;
            margin: 0mm;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 12pt;
            line-height: 1.4;
            color: #333;
            background: white;
        }
        
        .pdf-page {
            width: 210mm;
            height: 297mm;
            position: relative;
            page-break-after: always;
            overflow: hidden;
        }
        
        .pdf-page:last-child {
            page-break-after: avoid;
        }
        
        .pdf-background {
            position: absolute;
            top: 0;
            left: 0;
            width: 210mm;
            height: 297mm;
            z-index: 1;
        }
        
        .pdf-content {
            position: relative;
            z-index: 2;
            padding: 20mm;
            width: 170mm;
            height: 257mm;
            overflow: hidden;
        }
        
        /* Typography for PDF */
        h1, h2, h3, h4, h5, h6 {
            margin-bottom: 10pt;
            color: #333;
        }
        
        p {
            margin-bottom: 8pt;
        }
        
        ul, ol {
            margin-bottom: 8pt;
            margin-left: 20pt;
        }
        
        li {
            margin-bottom: 4pt;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15pt;
        }
        
        th, td {
            border: 1pt solid #ccc;
            padding: 8pt;
            text-align: left;
            vertical-align: top;
        }
        
        th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        
        /* Ensure images fit within page */
        img {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>
<body>
    @foreach($generatedPages as $index => $page)
        <div class="pdf-page">
            @if($page['background_image'])
                <img src="{{ public_path('backgrounds/' . $page['background_image']) }}" 
                     class="pdf-background" 
                     alt="Background">
            @endif
            <div class="pdf-content">
                {!! $page['content'] !!}
            </div>
        </div>
    @endforeach
</body>
</html>