@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Analytics Dashboard</h1>
        <p class="text-gray-600 mt-2">Prestatie statistieken en trends</p>
    </div>

    {{-- Periode selectie --}}
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <div class="flex items-center gap-4">
            <label class="text-sm font-medium text-gray-700">Periode:</label>
            <input type="date" id="start-datum" class="border-gray-300 rounded-md text-sm" value="{{ now()->subDays(30)->format('Y-m-d') }}">
            <span class="text-gray-500">tot</span>
            <input type="date" id="eind-datum" class="border-gray-300 rounded-md text-sm" value="{{ now()->format('Y-m-d') }}">
            <button onclick="laadAnalyticsData()" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm font-medium">
                Toepassen
            </button>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-sm text-gray-600 mb-2">Bruto Omzet</div>
            <div class="text-2xl font-bold text-gray-900" id="kpi-bruto">-</div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-sm text-gray-600 mb-2">Netto Omzet</div>
            <div class="text-2xl font-bold text-blue-600" id="kpi-netto">-</div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-sm text-gray-600 mb-2">Commissie</div>
            <div class="text-2xl font-bold text-orange-600" id="kpi-commissie">-</div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-sm text-gray-600 mb-2">Medewerker Inkomsten</div>
            <div class="text-2xl font-bold text-green-600" id="kpi-medewerker">-</div>
        </div>
    </div>

    {{-- Grafieken --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Omzet Trend</h3>
            <canvas id="omzetChart" height="300"></canvas>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Diensten Verdeling</h3>
            <canvas id="dienstenChart" height="300"></canvas>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Top Medewerkers</h3>
            <canvas id="medewerkerChart" height="300"></canvas>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Prestatie Status</h3>
            <canvas id="statusChart" height="300"></canvas>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
let charts = {};

function laadAnalyticsData() {
    const start = document.getElementById('start-datum').value;
    const eind = document.getElementById('eind-datum').value;
    
    fetch(`/api/dashboard/analytics?start=${start}&eind=${eind}`)
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                updateKPIs(data.kpis);
                updateCharts(data);
            }
        });
}

function updateKPIs(kpis) {
    document.getElementById('kpi-bruto').textContent = '€' + kpis.brutoOmzet.toLocaleString('nl-NL', {minimumFractionDigits: 2});
    document.getElementById('kpi-netto').textContent = '€' + kpis.nettoOmzet.toLocaleString('nl-NL', {minimumFractionDigits: 2});
    document.getElementById('kpi-commissie').textContent = '€' + kpis.commissie.toLocaleString('nl-NL', {minimumFractionDigits: 2});
    document.getElementById('kpi-medewerker').textContent = '€' + kpis.medewerkerInkomsten.toLocaleString('nl-NL', {minimumFractionDigits: 2});
}

function updateCharts(data) {
    // Omzet Chart
    if (charts.omzet) charts.omzet.destroy();
    charts.omzet = new Chart(document.getElementById('omzetChart'), {
        type: 'line',
        data: {
            labels: data.omzetTrend.labels,
            datasets: [{
                label: 'Bruto',
                data: data.omzetTrend.bruto,
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1
            }, {
                label: 'Netto',
                data: data.omzetTrend.netto,
                borderColor: 'rgb(54, 162, 235)',
                tension: 0.1
            }]
        }
    });
    
    // Diensten Chart
    if (charts.diensten) charts.diensten.destroy();
    charts.diensten = new Chart(document.getElementById('dienstenChart'), {
        type: 'pie',
        data: {
            labels: data.dienstenVerdeling.labels,
            datasets: [{data: data.dienstenVerdeling.values, backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6']}]
        }
    });
    
    // Medewerker Chart
    if (charts.medewerker) charts.medewerker.destroy();
    charts.medewerker = new Chart(document.getElementById('medewerkerChart'), {
        type: 'bar',
        data: {
            labels: data.medewerkerPrestaties.labels,
            datasets: [{label: 'Prestaties', data: data.medewerkerPrestaties.values, backgroundColor: '#3b82f6'}]
        }
    });
    
    // Status Chart
    if (charts.status) charts.status.destroy();
    charts.status = new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: {
            labels: ['Uitgevoerd', 'Niet uitgevoerd'],
            datasets: [{data: [data.prestatieStatus.uitgevoerd, data.prestatieStatus.nietUitgevoerd], backgroundColor: ['#10b981', '#ef4444']}]
        }
    });
}

// Laad data bij pagina load
laadAnalyticsData();
</script>
@endsection
