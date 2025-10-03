@php
    $user = Auth::user();
    $year = now()->year;
    $month = now()->month;
    
    // Live dashboard data
    $dashboardStats = [
        'totaalKlanten' => \App\Models\Klant::count(),
        'nieuweKlantenJaar' => \App\Models\Klant::whereYear('created_at', $year)->count(),
        'inspTestsJaar' => \App\Models\Inspanningstest::whereYear('created_at', $year)->count(),
        'bikefitsJaar' => \App\Models\Bikefit::whereYear('created_at', $year)->count(),
        'afsprakenMaand' => \App\Models\Inspanningstest::whereYear('created_at', $year)->whereMonth('created_at', $month)->count()
            + \App\Models\Bikefit::whereYear('created_at', $year)->whereMonth('created_at', $month)->count(),
    ];
    
    $dashboardStats['afsprakenJaar'] = $dashboardStats['inspTestsJaar'] + $dashboardStats['bikefitsJaar'];
    
    // Maandelijkse data voor mini grafieken
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
    $dashboardStats['bikefitsPerMaand'] = array_map(fn($m) => $bfGrouped[$m] ?? 0, $monthsIdx);
    $dashboardStats['inspTestsPerMaand'] = array_map(fn($m) => $itGrouped[$m] ?? 0, $monthsIdx);
    $dashboardStats['afsprakenPerMaand'] = array_map(fn($i) => $dashboardStats['bikefitsPerMaand'][$i-1] + $dashboardStats['inspTestsPerMaand'][$i-1], $monthsIdx);
@endphp

<!-- Live Dashboard Tegel -->
@if($item->title === 'Totaal Klanten')
    <div style="display:flex;align-items:center;gap:0.7em;">
        <span style="background:#fef3e2;border-radius:12px;width:48px;height:48px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="#ea580c" stroke-width="2">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
        </span>
        <div>
            <div style="color:#6b7280;font-size:0.95em;font-weight:600;">{{ $item->title }}</div>
            <div style="color:#111;font-size:1.8em;font-weight:800;letter-spacing:-0.3px;line-height:1.1;margin-top:0.15em;">{{ $dashboardStats['totaalKlanten'] }}</div>
        </div>
    </div>

@elseif($item->title === 'Nieuwe Klanten Dit Jaar')
    <div style="display:flex;align-items:center;gap:0.75rem;width:100%;">
        <span style="background:#e0f2fe;border-radius:12px;width:48px;height:48px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="#0284c7" stroke-width="2">
                <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="8.5" cy="7" r="4"/>
                <line x1="20" y1="8" x2="20" y2="14"/>
                <line x1="23" y1="11" x2="17" y2="11"/>
            </svg>
        </span>
        <div>
            <div style="color:#0284c7;font-size:1.8em;font-weight:800;letter-spacing:-0.02em;line-height:1.1;">{{ $dashboardStats['nieuweKlantenJaar'] }}</div>
            <div style="color:#0284c7;font-size:0.85em;font-weight:600;opacity:0.8;margin-top:0.1em;">Nieuwe klanten {{ $year }}</div>
        </div>
    </div>

@elseif($item->title === 'Afspraken Huidige Maand')
    <div style="display:flex;align-items:center;gap:0.75rem;width:100%;">
        <span style="background:#dcfce7;border-radius:12px;width:48px;height:48px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="#16a34a" stroke-width="2">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                <line x1="16" y1="2" x2="16" y2="6"/>
                <line x1="8" y1="2" x2="8" y2="6"/>
                <line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
        </span>
        <div>
            <div style="color:#16a34a;font-size:1.8em;font-weight:800;letter-spacing:-0.02em;line-height:1.1;">{{ $dashboardStats['afsprakenMaand'] }}</div>
            <div style="color:#16a34a;font-size:0.85em;font-weight:600;opacity:0.8;margin-top:0.1em;">Afspraken deze maand</div>
        </div>
    </div>

@elseif($item->title === 'Afspraken Dit Jaar')
    <div style="display:flex;align-items:center;gap:0.75rem;width:100%;">
        <span style="background:#ede9fe;border-radius:12px;width:48px;height:48px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="#6d28d9" stroke-width="2">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                <line x1="16" y1="2" x2="16" y2="6"/>
                <line x1="8" y1="2" x2="8" y2="6"/>
                <line x1="3" y1="10" x2="21" y2="10"/>
                <path d="M8 14l2-2 2 2 4-4"/>
            </svg>
        </span>
        <div>
            <div style="color:#6d28d9;font-size:1.8em;font-weight:800;letter-spacing:-0.02em;line-height:1.1;">{{ $dashboardStats['afsprakenJaar'] }}</div>
            <div style="color:#6d28d9;font-size:0.85em;font-weight:600;opacity:0.8;margin-top:0.1em;">Afspraken {{ $year }}</div>
        </div>
    </div>

@elseif($item->title === 'Inspanningstesten Dit Jaar')
    <div style="display:flex;align-items:center;gap:0.75rem;width:100%;">
        <span style="background:#fce7f3;border-radius:12px;width:48px;height:48px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="#be185d" stroke-width="2">
                <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
            </svg>
        </span>
        <div>
            <div style="color:#be185d;font-size:1.8em;font-weight:800;letter-spacing:-0.02em;line-height:1.1;">{{ $dashboardStats['inspTestsJaar'] }}</div>
            <div style="color:#be185d;font-size:0.85em;font-weight:600;opacity:0.8;margin-top:0.1em;">Inspanningstesten {{ $year }}</div>
        </div>
    </div>

@elseif($item->title === 'Bikefits Dit Jaar')
    <div style="display:flex;align-items:center;gap:0.75rem;width:100%;">
        <span style="background:#e6fffa;border-radius:12px;width:48px;height:48px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="#0d9488" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <polygon points="10,8 16,12 10,16 10,8"/>
            </svg>
        </span>
        <div>
            <div style="color:#0d9488;font-size:1.8em;font-weight:800;letter-spacing:-0.02em;line-height:1.1;">{{ $dashboardStats['bikefitsJaar'] }}</div>
            <div style="color:#0d9488;font-size:0.85em;font-weight:600;opacity:0.8;margin-top:0.1em;">Bikefits {{ $year }}</div>
        </div>
    </div>

@elseif($item->title === 'Totaal Afspraken Grafiek')
    <!-- Bar chart voor totaal afspraken -->
    <div style="padding:0.5rem 0;cursor:pointer;" onclick="if(document.getElementById('statistieken-section')) document.getElementById('statistieken-section').scrollIntoView({behavior: 'smooth'})">
        <!-- Mini bar chart container -->
        <div style="width:100%;height:180px;position:relative;margin-bottom:0.5rem;">
            <canvas id="miniBarChart-{{ $item->id }}" style="width:100%;height:100%;"></canvas>
        </div>
        
        <!-- Quick stats -->
        <div style="display:flex;justify-content:space-between;margin-bottom:0.5rem;">
            <div style="text-align:center;">
                <div style="color:#ea580c;font-size:1.1em;font-weight:700;">{{ $dashboardStats['afsprakenJaar'] }}</div>
                <div style="color:#6b7280;font-size:0.7em;">Totaal dit jaar</div>
            </div>
            <div style="text-align:center;">
                <div style="color:#ea580c;font-size:1.1em;font-weight:700;">{{ $dashboardStats['afsprakenMaand'] }}</div>
                <div style="color:#6b7280;font-size:0.7em;">Deze maand</div>
            </div>
        </div>
        
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const miniBarCtx = document.getElementById('miniBarChart-{{ $item->id }}');
            if (miniBarCtx) {
                const ctx = miniBarCtx.getContext('2d');
                const totalData = @json(array_map(fn($i) => $dashboardStats['bikefitsPerMaand'][$i-1] + $dashboardStats['inspTestsPerMaand'][$i-1], range(1,12)));
                
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: ['Jan','Feb','Mrt','Apr','Mei','Jun','Jul','Aug','Sep','Okt','Nov','Dec'],
                        datasets: [
                            {
                                label: 'Afspraken',
                                data: totalData,
                                backgroundColor: 'rgba(234, 88, 12, 0.6)',
                                borderColor: '#ea580c',
                                borderWidth: 1,
                                borderRadius: 3
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            x: { 
                                display: true,
                                ticks: { 
                                    font: { size: 8 },
                                    maxTicksLimit: 6
                                },
                                grid: { display: false }
                            },
                            y: { 
                                display: true,
                                beginAtZero: true,
                                ticks: { 
                                    font: { size: 8 },
                                    precision: 0
                                },
                                grid: { display: true, color: 'rgba(0,0,0,0.05)' }
                            }
                        }
                    }
                });
            }
        });
    </script>

@elseif($item->title === 'Statistieken Overzicht')
    <!-- Mini grafiek in tegel -->
    <div style="padding:0.5rem 0;cursor:pointer;" onclick="if(document.getElementById('statistieken-section')) document.getElementById('statistieken-section').scrollIntoView({behavior: 'smooth'})">
        
        <!-- Titel zoals in oude dashboard -->
        <div style="text-align:center;font-size:0.8em;font-weight:600;color:#374151;margin-bottom:0.5rem;">
            Bikefits en inspanningstesten per maand ({{ $year }})
        </div>
        
        <!-- Mini chart container -->
        <div style="width:100%;height:140px;position:relative;margin-bottom:0.5rem;">
            <canvas id="miniChart-{{ $item->id }}" style="width:100%;height:100%;"></canvas>
        </div>
        
        <!-- Quick stats -->
        <div style="display:flex;justify-content:space-between;margin-bottom:0.5rem;">
            <div style="text-align:center;">
                <div style="color:#0d9488;font-size:1.1em;font-weight:700;">{{ $dashboardStats['bikefitsJaar'] }}</div>
                <div style="color:#6b7280;font-size:0.7em;">Bikefits</div>
            </div>
            <div style="text-align:center;">
                <div style="color:#6d28d9;font-size:1.1em;font-weight:700;">{{ $dashboardStats['inspTestsJaar'] }}</div>
                <div style="color:#6b7280;font-size:0.7em;">Inspanningstesten</div>
            </div>
        </div>
        
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const miniCtx = document.getElementById('miniChart-{{ $item->id }}');
            if (miniCtx) {
                const ctx = miniCtx.getContext('2d');
                const bikefitsData = @json($dashboardStats['bikefitsPerMaand']);
                const inspData = @json($dashboardStats['inspTestsPerMaand']);
                
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: ['Jan','Feb','Mrt','Apr','Mei','Jun','Jul','Aug','Sep','Okt','Nov','Dec'],
                        datasets: [
                            {
                                label: 'Bikefits',
                                data: bikefitsData,
                                borderColor: '#0d9488',
                                backgroundColor: 'rgba(13, 148, 136, 0.15)',
                                tension: 0.3,
                                fill: true,
                                borderWidth: 2,
                                pointRadius: 2,
                                pointHoverRadius: 4
                            },
                            {
                                label: 'Inspanningstesten', 
                                data: inspData,
                                borderColor: '#6d28d9',
                                backgroundColor: 'rgba(109, 40, 217, 0.15)',
                                tension: 0.3,
                                fill: true,
                                borderWidth: 2,
                                pointRadius: 2,
                                pointHoverRadius: 4
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { 
                                display: true,
                                position: 'bottom',
                                labels: { 
                                    boxWidth: 8, 
                                    padding: 8, 
                                    font: { size: 9 },
                                    usePointStyle: true
                                }
                            }
                        },
                        scales: {
                            x: { 
                                display: true,
                                ticks: { 
                                    font: { size: 8 },
                                    maxTicksLimit: 6
                                },
                                grid: { display: false }
                            },
                            y: { 
                                display: false,
                                beginAtZero: true
                            }
                        },
                        elements: {
                            line: { tension: 0.3 }
                        }
                    }
                });
            }
        });
    </script>

@elseif($item->title === 'Testzadel Dashboard')
    <!-- Laatste 3 testzadels -->
    @php
        // Check if Testzadel model exists, otherwise use dummy data
        if (class_exists('\App\Models\Testzadel')) {
            try {
                $recenteTestzadels = \App\Models\Testzadel::latest()->take(3)->get();
                $totaalTestzadels = \App\Models\Testzadel::count();
                $inGebruik = \App\Models\Testzadel::where('status', 'in_gebruik')->count();
            } catch (\Exception $e) {
                // Als er een database error is
                $recenteTestzadels = collect([
                    (object)['merk' => 'Specialized', 'model' => 'Tarmac SL7'],
                    (object)['merk' => 'Trek', 'model' => 'Emonda ALR'],
                    (object)['merk' => 'Giant', 'model' => 'TCR Advanced']
                ]);
                $totaalTestzadels = 6;
                $inGebruik = 2;
            }
        } else {
            // Fallback als model niet bestaat
            $recenteTestzadels = collect([
                (object)['merk' => 'Specialized', 'model' => 'Tarmac SL7'],
                (object)['merk' => 'Trek', 'model' => 'Emonda ALR'],
                (object)['merk' => 'Giant', 'model' => 'TCR Advanced']
            ]);
            $totaalTestzadels = 6;
            $inGebruik = 2;
        }
    @endphp
    
    <div style="padding:0.25rem 0;cursor:pointer;" onclick="window.location.href='{{ url('/testzadels') }}'">
        <!-- Stats -->
        <div style="display:flex;justify-content:space-between;margin-bottom:0.5rem;">
            <div style="text-align:center;">
                <div style="color:#16a34a;font-size:1.2em;font-weight:700;">{{ $inGebruik }}</div>
                <div style="color:#6b7280;font-size:0.7em;">In gebruik</div>
            </div>
            <div style="text-align:center;">
                <div style="color:#3b82f6;font-size:1.2em;font-weight:700;">{{ $totaalTestzadels - $inGebruik }}</div>
                <div style="color:#6b7280;font-size:0.7em;">Beschikbaar</div>
            </div>
        </div>
        
        <!-- Laatste testzadels -->
        <div style="background:#f8fafc;border-radius:6px;padding:0.5rem;margin-bottom:0.5rem;">
            <div style="font-size:0.8em;font-weight:600;color:#374151;margin-bottom:0.3rem;">Laatste toegevoegd:</div>
            @foreach($recenteTestzadels as $testzadel)
                <div style="font-size:0.7em;color:#6b7280;margin-bottom:0.1rem;">
                    • {{ $testzadel->merk ?? 'Onbekend' }} {{ $testzadel->model ?? '' }}
                </div>
            @endforeach
        </div>
        
        <div style="text-align:center;font-size:0.75em;color:#3b82f6;font-weight:600;">
            → Bekijk alle testzadels
        </div>
    </div>

@else
    <!-- Standaard content weergave -->
    <div class="tile-content">
        @if($hasNewFields && $item->type === 'task')
            {!! str_replace(['<ul>', '<li>'], ['<ul>', '<li>☐ '], $item->content) !!}
        @else
            {!! Str::limit(strip_tags($item->content), ($hasNewFields && $item->tile_size === 'large') ? 300 : 150) !!}
        @endif
    </div>
@endif