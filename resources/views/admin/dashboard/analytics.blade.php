@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    {{-- Header met filters --}}
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">📊 Analytics Dashboard</h1>
                <p class="text-sm text-gray-600 mt-1">Prestatie statistieken en trends</p>
            </div>
            
            <div class="flex flex-col sm:flex-row gap-3">
                {{-- Scope filter (alleen voor superadmin/admin) --}}
                <div class="flex items-center gap-2">
                    <label class="text-sm font-medium text-gray-700 whitespace-nowrap">Filter:</label>
                    <select id="scope-filter" class="border-gray-300 rounded-md text-sm min-w-[200px]">
                        <option value="auto">Automatisch</option>
                        <option value="organisatie">Mijn Organisatie</option>
                        <option value="medewerker">Alleen Ik</option>
                        <option value="all">Alle Organisaties</option>
                    </select>
                </div>
                
                {{-- Periode selectie --}}
                <div class="flex items-center gap-2">
                    <label class="text-sm font-medium text-gray-700">Van:</label>
                    <input type="date" id="start-datum" class="border-gray-300 rounded-md text-sm" value="{{ now()->subDays(30)->format('Y-m-d') }}">
                </div>
                
                <div class="flex items-center gap-2">
                    <label class="text-sm font-medium text-gray-700">Tot:</label>
                    <input type="date" id="eind-datum" class="border-gray-300 rounded-md text-sm" value="{{ now()->format('Y-m-d') }}">
                </div>
                
                <button onclick="laadAnalyticsData()" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm font-medium whitespace-nowrap">
                    📈 Vernieuwen
                </button>
            </div>
        </div>
    </div>

    {{-- KPI Cards - compacter --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-gradient-to-br from-gray-50 to-white border border-gray-200 rounded-lg shadow-sm p-4 hover:shadow-md transition">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-medium text-gray-600 uppercase tracking-wide">Bruto Omzet</span>
                <span class="text-xl">💰</span>
            </div>
            <div class="text-2xl font-bold text-gray-900" id="kpi-bruto">-</div>
        </div>
        
        <div class="bg-gradient-to-br from-blue-50 to-white border border-blue-200 rounded-lg shadow-sm p-4 hover:shadow-md transition">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-medium text-blue-600 uppercase tracking-wide">Netto Omzet</span>
                <span class="text-xl">💵</span>
            </div>
            <div class="text-2xl font-bold text-blue-600" id="kpi-netto">-</div>
        </div>
        
        <div class="bg-gradient-to-br from-orange-50 to-white border border-orange-200 rounded-lg shadow-sm p-4 hover:shadow-md transition">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-medium text-orange-600 uppercase tracking-wide">Commissie</span>
                <span class="text-xl">🏢</span>
            </div>
            <div class="text-2xl font-bold text-orange-600" id="kpi-commissie">-</div>
        </div>
        
        <div class="bg-gradient-to-br from-green-50 to-white border border-green-200 rounded-lg shadow-sm p-4 hover:shadow-md transition">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-medium text-green-600 uppercase tracking-wide">Medewerkers</span>
                <span class="text-xl">👥</span>
            </div>
            <div class="text-2xl font-bold text-green-600" id="kpi-medewerker">-</div>
        </div>
    </div>

    {{-- Draggable Grafieken Grid - VASTE 4 kolommen op desktop --}}
    <div id="charts-container" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-6">
        {{-- Diensten Verdeling - klein (standaard 1 kolom) --}}
        <div class="chart-card bg-white rounded-lg shadow-sm border border-gray-200 p-4 cursor-move col-span-1" draggable="true" data-chart-id="diensten" data-size="small">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-gray-900">🎯 Diensten</h3>
                <button onclick="toggleChartSize('diensten')" class="text-gray-400 hover:text-gray-600 transition-colors" title="Grootte aanpassen">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                    </svg>
                </button>
            </div>
            <div class="chart-wrapper" style="height: 180px;">
                <canvas id="dienstenChart"></canvas>
            </div>
        </div>

        {{-- Prestatie Status - klein (standaard 1 kolom) --}}
        <div class="chart-card bg-white rounded-lg shadow-sm border border-gray-200 p-4 cursor-move col-span-1" draggable="true" data-chart-id="status" data-size="small">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-gray-900">✅ Status</h3>
                <button onclick="toggleChartSize('status')" class="text-gray-400 hover:text-gray-600 transition-colors" title="Grootte aanpassen">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                    </svg>
                </button>
            </div>
            <div class="chart-wrapper" style="height: 180px;">
                <canvas id="statusChart"></canvas>
            </div>
        </div>

        {{-- Omzet Trend - klein (standaard 1 kolom) --}}
        <div class="chart-card bg-white rounded-lg shadow-sm border border-gray-200 p-4 cursor-move col-span-1" draggable="true" data-chart-id="omzet" data-size="small">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-gray-900">📈 Omzet</h3>
                <button onclick="toggleChartSize('omzet')" class="text-gray-400 hover:text-gray-600 transition-colors" title="Grootte aanpassen">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                    </svg>
                </button>
            </div>
            <div class="chart-wrapper" style="height: 180px;">
                <canvas id="omzetChart"></canvas>
            </div>
        </div>

        {{-- Top Medewerkers - klein (standaard 1 kolom) --}}
        <div class="chart-card bg-white rounded-lg shadow-sm border border-gray-200 p-4 cursor-move col-span-1" draggable="true" data-chart-id="medewerker" data-size="small">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-gray-900">🏆 Medewerkers</h3>
                <button onclick="toggleChartSize('medewerker')" class="text-gray-400 hover:text-gray-600 transition-colors" title="Grootte aanpassen">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                    </svg>
                </button>
            </div>
            <div class="chart-wrapper" style="height: 180px;">
                <canvas id="medewerkerChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Extra statistieken --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">🧾 BTW Overzicht</h3>
            <div class="space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Incl. BTW:</span>
                    <span class="font-semibold" id="btw-incl">-</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Excl. BTW:</span>
                    <span class="font-semibold" id="btw-excl">-</span>
                </div>
                <div class="flex justify-between text-sm border-t pt-2">
                    <span class="text-gray-900 font-medium">BTW (21%):</span>
                    <span class="font-bold text-blue-600" id="btw-totaal">-</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">💼 Commissie Verdeling</h3>
            <div class="space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Organisatie:</span>
                    <span class="font-semibold text-orange-600" id="commissie-org">-</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Medewerkers:</span>
                    <span class="font-semibold text-green-600" id="commissie-med">-</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
let charts = {};
let chartLayouts = JSON.parse(localStorage.getItem('analyticsChartLayout') || '[]');

// Draggable functionaliteit
const container = document.getElementById('charts-container');
new Sortable(container, {
    animation: 150,
    handle: '.chart-card',
    ghostClass: 'opacity-50',
    onEnd: function() {
        const order = Array.from(container.children).map(el => el.dataset.chartId);
        localStorage.setItem('analyticsChartLayout', JSON.stringify(order));
    }
});

// Haal data op
function laadAnalyticsData() {
    const start = document.getElementById('start-datum').value;
    const eind = document.getElementById('eind-datum').value;
    const scope = document.getElementById('scope-filter').value;
    
    console.log('🔄 Analytics data laden...', { start, eind, scope });
    
    // Toon loading state
    document.getElementById('kpi-bruto').textContent = '...';
    document.getElementById('kpi-netto').textContent = '...';
    document.getElementById('kpi-commissie').textContent = '...';
    document.getElementById('kpi-medewerker').textContent = '...';
    
    fetch(`/api/dashboard/analytics?start=${start}&eind=${eind}&scope=${scope}`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin'
    })
        .then(response => {
            console.log('📡 Response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('✅ Data ontvangen:', data);
            if (data.success) {
                updateKPIs(data.kpis);
                updateCharts(data);
                updateExtra(data);
            } else {
                console.error('❌ Data ophalen mislukt:', data.message);
                alert('Fout bij laden data: ' + (data.message || 'Onbekende fout'));
            }
        })
        .catch(err => {
            console.error('❌ Fout bij laden data:', err);
            alert('Fout bij laden analytics data: ' + err.message);
        });
}

function updateKPIs(kpis) {
    console.log('📊 KPIs updaten:', kpis);
    document.getElementById('kpi-bruto').textContent = '€' + Number(kpis.brutoOmzet || 0).toLocaleString('nl-NL', {minimumFractionDigits: 2});
    document.getElementById('kpi-netto').textContent = '€' + Number(kpis.nettoOmzet || 0).toLocaleString('nl-NL', {minimumFractionDigits: 2});
    document.getElementById('kpi-commissie').textContent = '€' + Number(kpis.commissie || 0).toLocaleString('nl-NL', {minimumFractionDigits: 2});
    document.getElementById('kpi-medewerker').textContent = '€' + Number(kpis.medewerkerInkomsten || 0).toLocaleString('nl-NL', {minimumFractionDigits: 2});
}

function updateCharts(data) {
    console.log('📈 Charts updaten:', data);
    
    // Omzet Chart
    if (charts.omzet) charts.omzet.destroy();
    charts.omzet = new Chart(document.getElementById('omzetChart'), {
        type: 'line',
        data: {
            labels: data.omzetTrend.labels || [],
            datasets: [{
                label: 'Bruto', data: data.omzetTrend.bruto || [],
                borderColor: '#3b82f6', backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4, fill: true
            }, {
                label: 'Netto', data: data.omzetTrend.netto || [],
                borderColor: '#10b981', backgroundColor: 'rgba(16, 185, 129, 0.1)',
                tension: 0.4, fill: true
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' }}}
    });
    
    // Diensten Chart
    if (charts.diensten) charts.diensten.destroy();
    charts.diensten = new Chart(document.getElementById('dienstenChart'), {
        type: 'doughnut',
        data: {
            labels: data.dienstenVerdeling.labels || [],
            datasets: [{data: data.dienstenVerdeling.values || [], backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899']}]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' }}}
    });
    
    // Medewerker Chart
    if (charts.medewerker) charts.medewerker.destroy();
    charts.medewerker = new Chart(document.getElementById('medewerkerChart'), {
        type: 'bar',
        data: {
            labels: data.medewerkerPrestaties.labels || [],
            datasets: [{label: 'Prestaties', data: data.medewerkerPrestaties.values || [], backgroundColor: '#3b82f6'}]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }}}
    });
    
    // Status Chart
    if (charts.status) charts.status.destroy();
    charts.status = new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: {
            labels: ['Uitgevoerd', 'Niet uitgevoerd'],
            datasets: [{data: [data.prestatieStatus.uitgevoerd || 0, data.prestatieStatus.nietUitgevoerd || 0], backgroundColor: ['#10b981', '#ef4444']}]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' }}}
    });
}

function updateExtra(data) {
    console.log('📝 Extra data updaten:', data);
    document.getElementById('btw-incl').textContent = '€' + Number(data.btwOverzicht.incl || 0).toLocaleString('nl-NL', {minimumFractionDigits: 2});
    document.getElementById('btw-excl').textContent = '€' + Number(data.btwOverzicht.excl || 0).toLocaleString('nl-NL', {minimumFractionDigits: 2});
    document.getElementById('btw-totaal').textContent = '€' + Number(data.btwOverzicht.totaal || 0).toLocaleString('nl-NL', {minimumFractionDigits: 2});
    document.getElementById('commissie-org').textContent = '€' + Number(data.commissieVerdeling.organisatie || 0).toLocaleString('nl-NL', {minimumFractionDigits: 2});
    document.getElementById('commissie-med').textContent = '€' + Number(data.commissieVerdeling.medewerkers || 0).toLocaleString('nl-NL', {minimumFractionDigits: 2});
}

function toggleChartSize(chartId) {
    const card = document.querySelector(`[data-chart-id="${chartId}"]`);
    const wrapper = card.querySelector('.chart-wrapper');
    const currentSize = card.getAttribute('data-size') || 'small';
    
    console.log('🔄 Toggle size voor', chartId, 'huidige grootte:', currentSize);
    
    // Verwijder ALLE col-span classes (inclusief de standaard col-span-1!)
    card.className = card.className.split(' ').filter(c => !c.includes('col-span')).join(' ');
    
    if (currentSize === 'small') {
        // Klein → Medium (2 kolommen)
        card.classList.add('lg:col-span-2', 'md:col-span-2', 'sm:col-span-2', 'col-span-1');
        card.setAttribute('data-size', 'medium');
        wrapper.style.height = '250px';
        console.log('→ Van Klein naar Medium (2 kolommen)');
    } else if (currentSize === 'medium') {
        // Medium → Groot (volle breedte = 4 kolommen)
        card.classList.add('lg:col-span-4', 'md:col-span-3', 'sm:col-span-2', 'col-span-1');
        card.setAttribute('data-size', 'large');
        wrapper.style.height = '350px';
        console.log('→ Van Medium naar Groot (volle breedte)');
    } else {
        // Groot → Klein (terug naar 1 kolom)
        card.classList.add('col-span-1');
        card.setAttribute('data-size', 'small');
        wrapper.style.height = '180px';
        console.log('→ Van Groot naar Klein (1 kolom)');
    }
    
    // Resize chart met delay voor smooth transition
    if (charts[chartId]) {
        setTimeout(() => {
            charts[chartId].resize();
            console.log('✅ Chart resized:', chartId);
        }, 200);
    }
}

// Laad data bij pagina load
document.addEventListener('DOMContentLoaded', function() {
    console.log('📊 Analytics dashboard geladen');
    laadAnalyticsData();
    
    // Event listeners voor filters
    document.getElementById('scope-filter').addEventListener('change', function() {
        console.log('🔄 Scope filter changed:', this.value);
        laadAnalyticsData();
    });
    
    document.getElementById('start-datum').addEventListener('change', function() {
        console.log('📅 Start datum changed:', this.value);
    });
    
    document.getElementById('eind-datum').addEventListener('change', function() {
        console.log('📅 Eind datum changed:', this.value);
    });
});
</script>
@endsection
