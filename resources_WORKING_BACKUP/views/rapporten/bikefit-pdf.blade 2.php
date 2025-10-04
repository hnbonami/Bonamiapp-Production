<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bikefit Rapport - {{ $bikefit->klant->naam }}</title>
    <style>
        @page {
            margin: 0;
            size: A4;
        }
        
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            font-size: 12pt;
            line-height: 1.4;
        }
        
        .page {
            width: 210mm;
            min-height: 297mm;
            padding: 20mm;
            margin: 0;
            background: white;
            position: relative;
            page-break-after: always;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }
        
        .page:last-child {
            page-break-after: avoid;
        }
        
        .page-content {
            position: relative;
            z-index: 1;
        }
        
        h1, h2, h3, h4, h5, h6 {
            color: #333;
            margin-bottom: 1em;
        }
        
        p {
            margin-bottom: 1em;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 1em 0;
        }
        
        table th,
        table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        
        table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        
        .mobility-table td:nth-child(2),
        .mobility-table td:nth-child(3) {
            text-align: center;
        }
        
        .url-reference {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
        }
        
        .url-reference h5 {
            color: #495057;
            margin-bottom: 10px;
        }
        
        .url-reference a {
            color: #007bff;
            text-decoration: none;
        }
    </style>
</head>
<body>
    @foreach($rapport['paginas'] as $pagina)
        @if($pagina['type'] === 'url')
            <div class="page">
                <div class="page-content">
                    <div class="url-reference">
                        <h5>Externe Referentie - Pagina {{ $pagina['pagina_nummer'] }}</h5>
                        <p>Deze pagina verwijst naar een externe URL:</p>
                        <p><strong>URL:</strong> {{ $pagina['url'] }}</p>
                        <p><em>Open deze URL in een webbrowser voor meer informatie.</em></p>
                    </div>
                </div>
            </div>
        @else
            <div class="page" @if($pagina['background']) style="background-image: url('{{ public_path($pagina['background']) }}');" @endif>
                <div class="page-content">
                    {!! $pagina['content'] !!}
                </div>
            </div>
        @endif
    @endforeach
</body>
</html>