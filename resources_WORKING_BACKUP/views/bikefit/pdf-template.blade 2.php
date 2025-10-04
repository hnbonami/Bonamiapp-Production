<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bikefit Rapport - {{ $klant->naam }}</title>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        
        body {
            background: white !important;
            margin: 0 !important;
            padding: 0 !important;
            font-family: 'DejaVu Sans', sans-serif;
        }
        
        /* Gebruik exact dezelfde styling als print-perfect.blade.php */
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
            page-break-after: always;
        }
        
        .a4-preview-content:last-child {
            page-break-after: avoid;
        }
        
        .a4-preview-content > div {
            min-height: 100%;
            height: 100%;
            width: 100%;
            position: relative;
            z-index: 1;
        }
        
        @page {
            size: A4;
            margin: 5mm;
        }
    </style>
</head>
<body>
    @if(isset($htmls) && is_array($htmls))
        @foreach($htmls as $pageIndex => $page)
            @php
                $bg = null;
                if (!empty($images) && isset($images[$pageIndex]['path'])) {
                    $imgPath = ltrim($images[$pageIndex]['path'], '/');
                    $bg = asset($imgPath);
                } elseif (!empty($template->background)) {
                    $bg = asset(ltrim($template->background, '/'));
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
                $bg = asset($images[0]['path']);
            } elseif (!empty($template->background)) {
                $bg = asset($template->background);
            }
            $backgroundStyle = $bg ? "background-image: url('{$bg}'); background-size: cover; background-position: center;" : '';
        @endphp
        <div class="a4-preview-content" style="{{ $backgroundStyle }}">
            <div style="min-height:100%;height:100%;width:100%;position:relative;z-index:1;">
                {!! $html ?? '' !!}
            </div>
        </div>
    @endif
</body>
</html>