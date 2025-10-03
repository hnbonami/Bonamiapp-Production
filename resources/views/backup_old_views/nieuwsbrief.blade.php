@extends('layouts.app')

@section('content')

@php
    $year = now()->year;
    $month = now()->month;
    $totaalKlanten = \App\Models\Klant::count();
    $nieuweKlantenJaar = \App\Models\Klant::whereYear('created_at', $year)->count();
    $inspTestsJaar = \App\Models\Inspanningstest::whereYear('created_at', $year)->count();
    $bikefitsJaar = \App\Models\Bikefit::whereYear('created_at', $year)->count();
    $afsprakenMaand = \App\Models\Inspanningstest::whereYear('created_at', $year)->whereMonth('created_at', $month)->count()
        + \App\Models\Bikefit::whereYear('created_at', $year)->whereMonth('created_at', $month)->count();
    $afsprakenJaar = $inspTestsJaar + $bikefitsJaar;

    // Maandelijkse data voor grafieken
    $bfGrouped = \App\Models\Bikefit::selectRaw('MONTH(created_at) as m, COUNT(*) as c')
        ->whereYear('created_at', $year)
        ->groupBy('m')
        ->pluck('c','m')
        ->toArray();
    $itGrouped = \App\Models\Inspanningstest::selectRaw('MONTH(created_at) as m, COUNT(*) as c')
        ->whereYear('created_at', $year)
        ->groupBy('m')
        ->pluck('c','m')
        ->toArray();
    $monthsIdx = range(1,12);
    $bikefitsPerMaand = array_map(fn($m) => $bfGrouped[$m] ?? 0, $monthsIdx);
    $inspTestsPerMaand = array_map(fn($m) => $itGrouped[$m] ?? 0, $monthsIdx);
    $afsprakenPerMaand = array_map(fn($i) => $bikefitsPerMaand[$i-1] + $inspTestsPerMaand[$i-1], $monthsIdx);
@endphp

<style>
    .tiles-grid{display:grid;grid-template-columns:1fr;column-gap:8px;row-gap:24px;align-items:stretch}
    @media (min-width:640px){.tiles-grid{grid-template-columns:repeat(2,1fr)}}
    @media (min-width:768px){.tiles-grid{grid-template-columns:repeat(3,1fr)}}
</style>

@if(request()->routeIs('dashboard'))
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mb-6">
        <h1 class="text-3xl font-extrabold text-gray-900">Dashboard</h1>
    </div>
@endif

<div class="tiles-grid">
    <!-- Totaal aantal klanten -->
    <div style="background:#fff;border-radius:18px;box-shadow:0 2px 8px #e5e7eb;padding:1.0em 1.2em 0.9em 1.2em;display:flex;flex-direction:column;align-items:flex-start;flex:1 1 320px;min-width:260px;max-width:360px;">
        <div style="display:flex;align-items:center;gap:0.7em;">
            <span style="background:#fef3e2;border-radius:50%;width:40px;height:40px;display:flex;align-items:center;justify-content:center;">
                <svg width="22" height="22" fill="none" viewBox="0 0 16 16">
                    <circle cx="8" cy="8" r="8" fill="#fef3e2"/>
                    <g>
                        <circle cx="5.75" cy="7" r="1.1" fill="#ea580c"/>
                        <circle cx="10.25" cy="7" r="1.1" fill="#ea580c"/>
                        <circle cx="8" cy="5.75" r="1.5" fill="#ea580c"/>
                        <path d="M4.25 11c0-1.05 1.75-1.75 3.75-1.75s3.75 0.7 3.75 1.75v0.7a0.7 0.7 0 0 1-0.7 0.7H4.95a0.7 0.7 0 0 1-0.7-0.7V11z" fill="#fdba74"/>
                    </g>
                </svg>
            </span>
            <div>
                <div style="color:#6b7280;font-size:0.95em;font-weight:600;">Totaal aantal klanten</div>
                <div style="color:#111;font-size:1.8em;font-weight:800;letter-spacing:-0.3px;line-height:1.1;margin-top:0.15em;">{{ $totaalKlanten }}</div>
            </div>
        </div>
    </div>

    <!-- Nieuwe klanten dit jaar -->
    <div style="background:#fff;border-radius:18px;box-shadow:0 2px 8px #e5e7eb;padding:1.0em 1.2em 0.9em 1.2em;display:flex;flex-direction:column;align-items:flex-start;flex:1 1 320px;min-width:260px;max-width:360px;">
        <div style="display:flex;align-items:center;gap:0.7em;">
            <span style="background:#e0f2fe;border-radius:50%;width:40px;height:40px;display:flex;align-items:center;justify-content:center;">
                <svg width="22" height="22" viewBox="0 0 16 16" fill="none">
                    <circle cx="8" cy="8" r="8" fill="#e0f2fe"/>
                    <rect x="7.25" y="4.5" width="1.5" height="7" rx="0.75" fill="#0284c7"/>
                    <rect x="4.5" y="7.25" width="7" height="1.5" rx="0.75" fill="#0284c7"/>
                </svg>
            </span>
            <div>
                <div style="color:#6b7280;font-size:0.95em;font-weight:600;">Nieuwe klanten dit jaar</div>
                <div style="color:#111;font-size:1.8em;font-weight:800;letter-spacing:-0.3px;line-height:1.1;margin-top:0.15em;">{{ $nieuweKlantenJaar }}</div>
            </div>
        </div>
    </div>

    <!-- Totaal afspraken huidige maand -->
    <div style="background:#fff;border-radius:18px;box-shadow:0 2px 8px #e5e7eb;padding:1.0em 1.2em 0.9em 1.2em;display:flex;flex-direction:column;align-items:flex-start;flex:1 1 320px;min-width:260px;max-width:360px;">
        <div style="display:flex;align-items:center;gap:0.7em;">
            <span style="background:#dcfce7;border-radius:50%;width:40px;height:40px;display:flex;align-items:center;justify-content:center;">
                <svg width="22" height="22" viewBox="0 0 16 16" fill="none">
                    <rect x="2.5" y="4" width="11" height="9" rx="1.5" fill="#86efac"/>
                    <rect x="2.5" y="4" width="11" height="2" fill="#16a34a"/>
                    <rect x="4.5" y="2.5" width="2" height="3" rx="0.5" fill="#16a34a"/>
                    <rect x="9.5" y="2.5" width="2" height="3" rx="0.5" fill="#16a34a"/>
                </svg>
            </span>
            <div>
                <div style="color:#6b7280;font-size:0.95em;font-weight:600;">Afspraken (huidige maand)</div>
                <div style="color:#111;font-size:1.8em;font-weight:800;letter-spacing:-0.3px;line-height:1.1;margin-top:0.15em;">{{ $afsprakenMaand }}</div>
            </div>
        </div>
    </div>

    <!-- Totaal afspraken jaar -->
    <div style="background:#fff;border-radius:18px;box-shadow:0 2px 8px #e5e7eb;padding:1.0em 1.2em 0.9em 1.2em;display:flex;flex-direction:column;align-items:flex-start;flex:1 1 320px;min-width:260px;max-width:360px;">
        <div style="display:flex;align-items:center;gap:0.7em;">
            <span style="background:#ede9fe;border-radius:50%;width:40px;height:40px;display:flex;align-items:center;justify-content:center;">
                <svg width="22" height="22" viewBox="0 0 16 16" fill="none">
                    <rect x="2.5" y="4" width="11" height="9" rx="1.5" fill="#c4b5fd"/>
                    <rect x="2.5" y="4" width="11" height="2" fill="#6d28d9"/>
                    <rect x="4.5" y="2.5" width="2" height="3" rx="0.5" fill="#6d28d9"/>
                    <rect x="9.5" y="2.5" width="2" height="3" rx="0.5" fill="#6d28d9"/>
                    <circle cx="12.5" cy="12.5" r="2" fill="#6d28d9"/>
                </svg>
            </span>
            <div>
                <div style="color:#6b7280;font-size:0.95em;font-weight:600;">Afspraken (dit jaar)</div>
                <div style="color:#111;font-size:1.8em;font-weight:800;letter-spacing:-0.3px;line-height:1.1;margin-top:0.15em;">{{ $afsprakenJaar }}</div>
            </div>
        </div>
    </div>

    <!-- Inspanningstesten dit jaar -->
    <div style="background:#fff;border-radius:18px;box-shadow:0 2px 8px #e5e7eb;padding:1.0em 1.2em 0.9em 1.2em;display:flex;flex-direction:column;align-items:flex-start;flex:1 1 320px;min-width:260px;max-width:360px;">
        <div style="display:flex;align-items:center;gap:0.7em;">
            <span style="background:#fce7f3;border-radius:50%;width:40px;height:40px;display:flex;align-items:center;justify-content:center;">
                <svg width="22" height="22" viewBox="0 0 16 16" fill="none">
                    <polyline points="2,10 5,10 6.5,6 8.5,12 10,8 14,8" stroke="#be185d" stroke-width="1.6" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>
            <div>
                <div style="color:#6b7280;font-size:0.95em;font-weight:600;">Insp. testen dit jaar</div>
                <div style="color:#111;font-size:1.8em;font-weight:800;letter-spacing:-0.3px;line-height:1.1;margin-top:0.15em;">{{ $inspTestsJaar }}</div>
            </div>
        </div>
    </div>

    <!-- Bikefits dit jaar -->
    <div style="background:#fff;border-radius:18px;box-shadow:0 2px 8px #e5e7eb;padding:1.0em 1.2em 0.9em 1.2em;display:flex;flex-direction:column;align-items:flex-start;flex:1 1 320px;min-width:260px;max-width:360px;">
        <div style="display:flex;align-items:center;gap:0.7em;">
            <span style="background:#e6fffa;border-radius:50%;width:40px;height:40px;display:flex;align-items:center;justify-content:center;">
                <svg width="22" height="22" viewBox="0 0 16 16" fill="none">
                    <circle cx="5" cy="12" r="2" stroke="#0d9488" stroke-width="1.6" fill="none"/>
                    <circle cx="12" cy="12" r="2" stroke="#0d9488" stroke-width="1.6" fill="none"/>
                    <path d="M6.5 12 L9 8 L12 8" stroke="#0d9488" stroke-width="1.6" fill="none" stroke-linecap="round"/>
                </svg>
            </span>
            <div>
                <div style="color:#6b7280;font-size:0.95em;font-weight:600;">Bikefits dit jaar</div>
                <div style="color:#111;font-size:1.8em;font-weight:800;letter-spacing:-0.3px;line-height:1.1;margin-top:0.15em;">{{ $bikefitsJaar }}</div>
            </div>
        </div>
    </div>
</div>


<div class="mt-8" style="display:flex;flex-wrap:wrap;gap:24px;align-items:flex-start;">
    <div style="background:#fff;border-radius:18px;box-shadow:0 2px 8px #e5e7eb;padding:1.2em;min-height:320px;flex:1 1 48%;max-width:48%;box-sizing:border-box;">
        <canvas id="lineChart" height="220" style="width:100%;display:block;max-width:100%;"></canvas>
    </div>
    <div style="background:#fff;border-radius:18px;box-shadow:0 2px 8px #e5e7eb;padding:1.2em;min-height:320px;flex:1 1 48%;max-width:48%;box-sizing:border-box;">
        <canvas id="barChart" height="220" style="width:100%;display:block;max-width:100%;"></canvas>
    </div>
</div>

<div class="mt-8" style="display:flex;flex-wrap:wrap;gap:24px;align-items:flex-start;">
    @include('components.testzadel-dashboard-widget')
</div>


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const labels = ['Jan','Feb','Mrt','Apr','Mei','Jun','Jul','Aug','Sep','Okt','Nov','Dec'];
    const bikefitsData = @json($bikefitsPerMaand);
    const inspData = @json($inspTestsPerMaand);
    const totalData = @json($afsprakenPerMaand);

    const lineCtx = document.getElementById('lineChart').getContext('2d');
    new Chart(lineCtx, {
        type: 'line',
        data: {
            labels,
            datasets: [
                {
                    label: 'Bikefits',
                    data: bikefitsData,
                    borderColor: '#0d9488',
                    backgroundColor: 'rgba(13, 148, 136, 0.15)',
                    tension: 0.3,
                    fill: true,
                    borderWidth: 2
                },
                {
                    label: 'Inspanningstesten',
                    data: inspData,
                    borderColor: '#6d28d9',
                    backgroundColor: 'rgba(109, 40, 217, 0.15)',
                    tension: 0.3,
                    fill: true,
                    borderWidth: 2
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top' },
                title: {
                    display: true,
                    text: 'Bikefits en inspanningstesten per maand ({{ $year }})',
                    font: { size: 16, weight: '600' }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    title: { display: true, text: 'Maand' }
                },
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0 },
                    title: { display: true, text: 'Aantal' }
                }
            }
        }
    });

    const barCtx = document.getElementById('barChart').getContext('2d');
    new Chart(barCtx, {
        type: 'bar',
        data: {
            labels,
            datasets: [
                {
                    label: 'Afspraken per maand',
                    data: totalData,
                    backgroundColor: 'rgba(234, 88, 12, 0.3)',
                    borderColor: '#ea580c',
                    borderWidth: 1.5,
                    borderRadius: 6
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                title: {
                    display: true,
                    text: 'Totaal aantal afspraken per maand ({{ $year }})',
                    font: { size: 16, weight: '600' }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    title: { display: true, text: 'Maand' }
                },
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0 },
                    title: { display: true, text: 'Aantal afspraken' }
                }
            }
        }
    });
</script>

{{-- Staff Notes & Taken Widget onder de grafieken --}}

@php
    $user = Auth::user();
@endphp
@if($user && in_array($user->role, ['admin', 'medewerker']))
    <div class="mt-8 mb-8 flex justify-center" style="width:100%;">
    <div style="width:100%;max-width:1100px;">
            @include('components.staff-notes-dashboard-widget-live')
        </div>
    </div>
@endif
@endsection
