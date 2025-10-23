@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="text-3xl font-bold text-gray-900">📋 Laravel Log Viewer</h1>
            <p class="text-gray-600 mt-2">Bekijk en download applicatie logs (alleen voor debugging)</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Sidebar met log bestanden -->
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">📁 Log Bestanden</h5>
                </div>
                <div class="list-group list-group-flush" style="max-height: 600px; overflow-y: auto;">
                    @forelse($logFiles as $file)
                        <a href="{{ route('logs.index', ['file' => $file['name'], 'lines' => $lines]) }}" 
                           class="list-group-item list-group-item-action {{ $selectedLog === $file['name'] ? 'active' : '' }}">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <div class="fw-bold text-truncate" style="max-width: 150px;">
                                        {{ $file['name'] }}
                                    </div>
                                    <small class="text-muted">{{ $file['size'] }}</small>
                                </div>
                            </div>
                            <small class="text-muted d-block mt-1">{{ $file['modified'] }}</small>
                        </a>
                    @empty
                        <div class="list-group-item">
                            <p class="text-muted mb-0">Geen log bestanden gevonden</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Filter opties -->
            <div class="card shadow-sm mt-3">
                <div class="card-body">
                    <h6 class="card-title">🔍 Filter Opties</h6>
                    <form method="GET" action="{{ route('logs.index') }}">
                        <input type="hidden" name="file" value="{{ $selectedLog }}">
                        
                        <div class="mb-3">
                            <label class="form-label">Aantal regels</label>
                            <select name="lines" class="form-select" onchange="this.form.submit()">
                                <option value="50" {{ $lines == 50 ? 'selected' : '' }}>50 regels</option>
                                <option value="100" {{ $lines == 100 ? 'selected' : '' }}>100 regels</option>
                                <option value="200" {{ $lines == 200 ? 'selected' : '' }}>200 regels</option>
                                <option value="500" {{ $lines == 500 ? 'selected' : '' }}>500 regels</option>
                                <option value="1000" {{ $lines == 1000 ? 'selected' : '' }}>1000 regels</option>
                            </select>
                        </div>
                    </form>

                    @if($selectedLog)
                        <div class="d-grid gap-2">
                            <a href="{{ route('logs.download', $selectedLog) }}" 
                               class="btn btn-sm btn-outline-primary">
                                📥 Download
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Log inhoud -->
        <div class="col-md-9">
            <div class="card shadow-sm">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">📄 {{ $selectedLog }}</h5>
                    <button class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
                        🔄 Ververs
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="log-content" style="max-height: 70vh; overflow-y: auto; font-family: 'Courier New', monospace; font-size: 12px; background-color: #1e1e1e; color: #d4d4d4;">
                        @if(is_array($logContent) && count($logContent) > 0)
                            @foreach($logContent as $index => $logLine)
                                <div class="log-line px-3 py-1 border-bottom border-dark" style="white-space: pre-wrap; word-wrap: break-word;">
                                    <span class="text-muted me-2">{{ count($logContent) - $index }}</span>
                                    <span class="{{ $logLine['class'] }}">{{ $logLine['content'] }}</span>
                                </div>
                            @endforeach
                        @else
                            <div class="p-4 text-center text-muted">
                                {{ is_string($logContent) ? $logContent : 'Geen log data beschikbaar' }}
                            </div>
                        @endif
                    </div>
                </div>
                <div class="card-footer text-muted">
                    <small>Laatste {{ $lines }} regels van {{ $selectedLog }} (nieuwste bovenaan)</small>
                </div>
            </div>

            <!-- Quick Search binnen logs -->
            <div class="card shadow-sm mt-3">
                <div class="card-body">
                    <h6>🔎 Zoeken in logs</h6>
                    <input type="text" id="logSearch" class="form-control" placeholder="Zoek in zichtbare logs...">
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.log-line:hover {
    background-color: #2d2d2d !important;
}

.log-content::-webkit-scrollbar {
    width: 10px;
}

.log-content::-webkit-scrollbar-track {
    background: #1e1e1e;
}

.log-content::-webkit-scrollbar-thumb {
    background: #555;
    border-radius: 5px;
}

.log-content::-webkit-scrollbar-thumb:hover {
    background: #777;
}
</style>

<script>
// Live zoeken in logs
document.getElementById('logSearch').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const logLines = document.querySelectorAll('.log-line');
    
    logLines.forEach(line => {
        const text = line.textContent.toLowerCase();
        if (text.includes(searchTerm)) {
            line.style.display = 'block';
            if (searchTerm !== '') {
                line.style.backgroundColor = '#3a3a00';
            } else {
                line.style.backgroundColor = '';
            }
        } else {
            line.style.display = searchTerm === '' ? 'block' : 'none';
        }
    });
});

// Auto-scroll naar laatste log bij laden
document.addEventListener('DOMContentLoaded', function() {
    const logContent = document.querySelector('.log-content');
    if (logContent) {
        logContent.scrollTop = 0; // Start bovenaan (nieuwste logs)
    }
});
</script>

@endsection
