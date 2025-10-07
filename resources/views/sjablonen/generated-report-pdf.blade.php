<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ $template->naam }} - {{ $klantModel->naam }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 20px;
            color: #333;
        }
        
        .page {
            page-break-after: always;
            margin-bottom: 30px;
        }
        
        .page:last-child {
            page-break-after: avoid;
        }
        
        h1, h2, h3, h4 {
            color: #2c3e50;
            margin-top: 0;
            margin-bottom: 10px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        
        table, th, td {
            border: 1px solid #ddd;
        }
        
        th, td {
            padding: 8px;
            text-align: left;
        }
        
        th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        
        .mobility-table {
            border: 2px solid #007bff;
        }
        
        .mobility-table th {
            background-color: #007bff;
            color: white;
            text-align: center;
        }
        
        .mobility-table td {
            text-align: center;
        }
        
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        
        p { margin: 8px 0; }
        
        img {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>
<body>
    @foreach ($generatedPages as $page)
        <div class="page">
            @if (!empty($page['title']))
                <h2>{{ $page['title'] }}</h2>
            @endif
            
            <div class="page-content">
                {!! $page['content'] !!}
            </div>
        </div>
    @endforeach
</body>
</html>