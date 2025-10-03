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

{{-- Dashboard Statistics Styling --}}
<link href="{{ asset('css/dashboard-stats.css') }}" rel="stylesheet">

<style>
    /* DIRECT STYLING FOR STATISTICS TILES */
    /* Target common patterns for dashboard statistics */
    .row .col .card,
    .statistics .card,
    .metrics .card,
    [class*="dashboard"] [class*="stat"] {
        min-height: 80px !important;
        max-height: 100px !important;
        padding: 12px 16px !important;
    }

    /* Smaller text in stat tiles */
    .row .col .card h1,
    .row .col .card h2,
    .row .col .card .display-4,
    .statistics .card .number {
        font-size: 1.6em !important;
        line-height: 1.1 !important;
        margin-bottom: 2px !important;
    }

    .row .col .card p,
    .row .col .card small,
    .statistics .card .label {
        font-size: 0.8em !important;
        line-height: 1.2 !important;
        margin: 0 !important;
    }

    /* Target tiles with numbers (likely statistics) */
    .card:has(.display-4),
    .card:has(h1),
    .tile:has(.number) {
        min-height: 80px !important;
        max-height: 100px !important;
    }
</style>

@if(request()->routeIs('dashboard'))
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mb-6">
        <h1 class="text-3xl font-extrabold text-gray-900">Dashboard</h1>
    </div>
@endif

@if($isStaff)
    <!-- Nieuwe Dashboard Content Systeem -->
    <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="font-semibold text-blue-900">🎉 Nieuw Dashboard Systeem Beschikbaar!</h3>
                <p class="text-blue-700 text-sm mt-1">
                    Je dashboard tegels zijn nu volledig aanpasbaar. Bekijk het nieuwe interactieve dashboard!
                </p>
            </div>
            <a href="{{ route('dashboard-content.index') }}" 
               class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                Ga naar Nieuw Dashboard →
            </a>
        </div>
    </div>

    <!-- Legacy dashboard tegels (blijven werken) -->
    <div class="tiles-grid">
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

{{-- Debug: Show actual HTML structure --}}
<script>
console.log('Dashboard HTML structure:');
document.addEventListener('DOMContentLoaded', function() {
    const main = document.querySelector('main');
    if (main) {
        console.log('Main element:', main);
        console.log('Main innerHTML:', main.innerHTML);
        
        // Find all divs that might contain tiles
        const divs = main.querySelectorAll('div');
        divs.forEach((div, index) => {
            if (div.children.length > 0) {
                console.log(`Div ${index}:`, div);
                console.log(`Classes: ${div.className}`);
                console.log(`ID: ${div.id}`);
                console.log(`Children count: ${div.children.length}`);
            }
        });
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
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">
                            Je klantendashboard wordt binnenkort verder uitgebreid met meer functionaliteiten.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Nieuws & Aankondigingen widget voor klanten --}}
<div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
    @include('components.staff-notes-dashboard-widget-live')
</div>
@endif

{{-- Staff Notes & Taken Widget onder de grafieken --}}

@if($isStaff)
    <div class="mt-8 mb-8 flex justify-center" style="width:100%;">
        <div style="width:100%;max-width:1100px;">
            @include('components.staff-notes-dashboard-widget-live')
        </div>
    </div>
@endif

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('🎯 Applying smaller height to statistics tiles...');
    
    // Find tiles that likely contain statistics (numbers + labels)
    const allTiles = document.querySelectorAll('.card, .tile, [class*="col"]');
    
    allTiles.forEach(tile => {
        const text = tile.textContent;
        const hasNumbers = /\d+/.test(text);
        
        // Check if it contains typical dashboard stat words
        const statKeywords = ['klanten', 'inspanningen', 'nieuwe', 'totaal', 'afspraken', 'tests'];
        const hasStatKeywords = statKeywords.some(keyword => 
            text.toLowerCase().includes(keyword)
        );
        
        // Check if it has large numbers (likely statistics)
        const hasLargeNumber = tile.querySelector('h1, h2, .display-4, [class*="number"]');
        
        if ((hasNumbers && hasStatKeywords) || hasLargeNumber) {
            console.log('Found statistics tile:', tile);
            
            // Apply smaller height
            tile.style.cssText += `
                min-height: 80px !important;
                max-height: 100px !important;
                padding: 12px 16px !important;
            `;
            
            // Adjust text sizes within
            const numberElements = tile.querySelectorAll('h1, h2, h3, .display-4, [class*="number"]');
            numberElements.forEach(num => {
                num.style.cssText += `
                    font-size: 1.6em !important;
                    line-height: 1.1 !important;
                    margin-bottom: 2px !important;
                `;
            });
            
            const textElements = tile.querySelectorAll('p, small, span:not([class*="number"])');
            textElements.forEach(text => {
                text.style.cssText += `
                    font-size: 0.8em !important;
                    line-height: 1.2 !important;
                    margin: 0 !important;
                `;
            });
            
            // Add visual indicator for debugging
            tile.style.border = '2px solid orange';
        }
    });
});
</script>
@endsection
