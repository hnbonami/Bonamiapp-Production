@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1>Bikefit Rapport</h1>
                    <p class="text-muted">{{ $bikefit->klant->voornaam }} {{ $bikefit->klant->naam }} - {{ $bikefit->created_at->format('d-m-Y') }}</p>
                </div>
                <div class="btn-group">
                    <a href="{{ route('rapporten.bikefit.pdf', $bikefit) }}{{ request('sjabloon_id') ? '?sjabloon_id=' . request('sjabloon_id') : '' }}" 
                       class="btn btn-danger">
                        <i class="fas fa-file-pdf"></i> Download PDF
                    </a>
                    <button onclick="window.print()" class="btn btn-secondary">
                        <i class="fas fa-print"></i> Afdrukken
                    </button>
                    <a href="{{ route('bikefits.show', $bikefit) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Terug naar Bikefit
                    </a>
                </div>
            </div>

            <div class="rapport-content">
                @foreach($rapport['paginas'] as $pagina)
                    @if($pagina['type'] === 'url')
                        <div class="page-break url-page">
                            <div class="alert alert-info">
                                <h5><i class="fas fa-external-link-alt"></i> Externe Referentie - Pagina {{ $pagina['pagina_nummer'] }}</h5>
                                <p>Deze pagina verwijst naar een externe URL:</p>
                                <a href="{{ $pagina['url'] }}" target="_blank" class="btn btn-primary">
                                    {{ $pagina['url'] }} <i class="fas fa-external-link-alt"></i>
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="page-break rapport-page" 
                             @if($pagina['background']) 
                                style="background-image: url('{{ $pagina['background'] }}'); background-size: cover; background-position: center; background-repeat: no-repeat;"
                             @endif>
                            <div class="page-content">
                                {!! $pagina['content'] !!}
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
</div>

<style>
.rapport-content {
    background: white;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}

.rapport-page {
    width: 100%;
    min-height: 297mm;
    padding: 20mm;
    margin-bottom: 20px;
    background: white;
    position: relative;
}

.page-content {
    position: relative;
    z-index: 1;
    background: rgba(255, 255, 255, 0.95);
    padding: 20px;
    border-radius: 8px;
}

.url-page {
    padding: 40px;
    min-height: 200px;
}

@media print {
    .btn-group, .container-fluid > .row > .col-md-12 > .d-flex {
        display: none !important;
    }
    
    .page-break {
        page-break-after: always;
    }
    
    .page-break:last-child {
        page-break-after: auto;
    }
    
    .rapport-page {
        margin: 0;
        box-shadow: none;
        min-height: 297mm;
    }
    
    .page-content {
        background: transparent !important;
        box-shadow: none;
        padding: 0;
    }
}

/* Template styling */
.mobility-table {
    width: 100%;
    border-collapse: collapse;
    margin: 20px 0;
}

.mobility-table th,
.mobility-table td {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: left;
}

.mobility-table th {
    background-color: #f5f5f5;
    font-weight: bold;
}

.mobility-table td:nth-child(2),
.mobility-table td:nth-child(3) {
    text-align: center;
}
</style>
@endsection