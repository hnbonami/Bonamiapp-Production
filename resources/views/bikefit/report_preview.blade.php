@extends('layouts.app')
@section('content')
<div class="a4-preview">
    <div class="flex gap-2 mb-4">
        <a href="{{ route('bikefit.report.pdf.preview', ['klant' => $klantId, 'bikefit' => $bikefit->id]) }}" target="_blank" rel="noopener" class="px-4 py-2 bg-green-500 text-white rounded font-semibold">Download PDF (Pixel-perfect)</a>
        <a href="{{ route('bikefit.report.print.perfect', ['klant' => $klantId ?? $bikefit->klant_id, 'bikefit' => $bikefit->id]) }}" target="_blank" rel="noopener" class="px-4 py-2 bg-blue-600 text-white rounded font-semibold hover:bg-blue-700">🖨️ Perfect Print/PDF</a>
        <a href="{{ route('bikefit.report.print', ['klant' => $klantId ?? $bikefit->klant_id, 'bikefit' => $bikefit->id]) }}" target="_blank" rel="noopener" class="px-4 py-2 bg-green-500 text-white rounded font-semibold" style="background:#22c55e;color:#fff;padding:10px 24px;border-radius:6px;text-decoration:none;font-weight:600;box-shadow:0 2px 6px rgba(34,197,94,0.18);border:2px solid #22c55e;font-size:16px;">Print rapport (Nieuwe versie)</a>
        <a href="{{ route('bikefit.report.print.direct', ['klant' => $klantId ?? $bikefit->klant_id, 'bikefit' => $bikefit->id]) }}" target="_blank" rel="noopener" class="px-4 py-2 bg-blue-500 text-white rounded font-semibold">Print rapport (Direct)</a>
    </div>
    <h1 class="text-2xl font-bold mb-4">Bikefit verslag preview</h1>
    <div class="mb-6">
    </div>
    <div class="mb-4">
    </div>
    <!-- De evaluatieblokken zijn verwijderd, alleen de A4-preview blijft over -->
    <style>
    .a4-preview-content *:not(.a4-preview-content) {
        background: transparent !important;
    }
    <style>
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
    @media print {
        /* Minimale print CSS - verberg alleen navigatie */
        .flex.gap-2, h1.text-2xl, .mb-6, .mb-4, .bg-gray-300, 
        a[href*="results"] {
            display: none !important;
        }
        
        /* Behoud achtergrondafbeeldingen */
        * {
            -webkit-print-color-adjust: exact !important;
            color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
    }
    </style>
        <div id="report-content">
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
                        {{-- Achtergrondafbeelding URL info verwijderd --}}
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
                <div class="a4-preview-content" style="{{ $backgroundStyle }}">{!! $html ?? '' !!}</div>
            @endif
        </div>
    </div>
    <!-- Geen splits-script meer, alles direct zichtbaar -->
        <script>
            document.getElementById('print-btn').addEventListener('click', function() {
                window.print();
            });
        </script>
    <a href="{{ route('bikefit.results', ['klant' => $klantId, 'bikefit' => $bikefit->id]) }}" class="bg-gray-300 hover:bg-gray-400 text-black font-bold py-2 px-4 rounded">Terug naar resultaten</a>
    <!-- einde .a4-preview -->
</div>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Oude print functionaliteit (fallback)
        if (document.getElementById('print-btn')) {
            document.getElementById('print-btn').addEventListener('click', function() {
                window.print();
            });
        }
        
        // Print rapport (Direct) functionaliteit
        document.addEventListener('click', function(e) {
            if (e.target && e.target.textContent && e.target.textContent.includes('Print rapport (Direct)')) {
                e.preventDefault();
                var printWindow = window.open('', '', 'width=800,height=600');
                printWindow.document.write('<html><head><title>Verslag printen</title></head><body>' + document.getElementById('report-content').innerHTML + '</body></html>');
                printWindow.document.close();
                printWindow.focus();
                printWindow.print();
            }
        });
    });
    </script>
@endsection