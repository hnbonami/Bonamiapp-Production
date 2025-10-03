@extends('layouts.app')

@section('content')

@php
    $user = Auth::user();
    $isStaff = $user && in_array($user->role, ['admin', 'medewerker']);
    
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

@if(request()->routeIs('dashboard'))
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mb-6">
        <h1 class="text-3xl font-extrabold text-gray-900">Dashboard</h1>
    </div>
@endif

@if($isStaff)
    <!-- Legacy dashboard tegels -->
    <div style="display:flex;flex-wrap:wrap;gap:24px;align-items:flex-start;justify-content:flex-start;margin:0 auto;max-width:1200px;padding:0 1rem;">
        <!-- Totaal aantal klanten -->
        <div style="background:#fff;border-radius:18px;box-shadow:0 2px 8px #e5e7eb;padding:1.0em 1.2em 0.9em 1.2em;display:flex;flex-direction:column;align-items:flex-start;flex:1 1 320px;min-width:260px;max-width:360px;">
            <div style="display:flex;align-items:center;gap:0.7em;">
                <span style="background:#fef3e2;border-radius:10px;width:40px;height:40px;display:flex;align-items:center;justify-content:center;">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="#ea580c" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
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
                <span style="background:#e0f2fe;border-radius:10px;width:40px;height:40px;display:flex;align-items:center;justify-content:center;">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="#0284c7" stroke-width="2">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="8.5" cy="7" r="4"/>
                        <line x1="20" y1="8" x2="20" y2="14"/>
                        <line x1="23" y1="11" x2="17" y2="11"/>
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
                <span style="background:#dcfce7;border-radius:10px;width:40px;height:40px;display:flex;align-items:center;justify-content:center;">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="#16a34a" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
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
                <span style="background:#ede9fe;border-radius:10px;width:40px;height:40px;display:flex;align-items:center;justify-content:center;">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="#6d28d9" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                        <path d="M8 14l2-2 2 2 4-4"/>
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
                <span style="background:#fce7f3;border-radius:10px;width:40px;height:40px;display:flex;align-items:center;justify-content:center;">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="#be185d" stroke-width="2">
                        <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
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
                <span style="background:#e6fffa;border-radius:10px;width:40px;height:40px;display:flex;align-items:center;justify-content:center;">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="#0d9488" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <polygon points="10,8 16,12 10,16 10,8"/>
                    </svg>
                </span>
                <div>
                    <div style="color:#6b7280;font-size:0.95em;font-weight:600;">Bikefits dit jaar</div>
                    <div style="color:#111;font-size:1.8em;font-weight:800;letter-spacing:-0.3px;line-height:1.1;margin-top:0.15em;">{{ $bikefitsJaar }}</div>
                </div>
            </div>
        </div>
    </div>

<div id="statistieken-section" class="mt-8" style="display:flex;flex-wrap:wrap;gap:24px;align-items:flex-start;">
    <div style="background:#fff;border-radius:18px;box-shadow:0 2px 8px #e5e7eb;padding:1.2em;min-height:320px;flex:1 1 48%;max-width:48%;box-sizing:border-box;">
        <canvas id="lineChart" height="220" style="width:100%;display:block;max-width:100%;"></canvas>
    </div>
    <div style="background:#fff;border-radius:18px;box-shadow:0 2px 8px #e5e7eb;padding:1.2em;min-height:320px;flex:1 1 48%;max-width:48%;box-sizing:border-box;">
        <canvas id="barChart" height="220" style="width:100%;display:block;max-width:100%;"></canvas>
    </div>
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
@endif

{{-- Welkomstbericht voor klanten --}}
@if(!$isStaff)
<div class="max-w-4xl mx-auto sm:px-6 lg:px-8 mb-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-8 text-gray-900">
            <h2 class="text-2xl font-bold mb-4">Welkom bij Bonami Sportcoaching</h2>
            <p class="text-lg text-gray-700 mb-6">
                Welkom op jouw persoonlijke dashboard. Hier kun je binnenkort je afspraken bekijken, 
                rapporten downloaden en je gegevens beheren.
            </p>
        </div>
    </div>
</div>
@endif

@endsection
