@extends('layouts.app')

@section('content')
<div class="dashboard-container" style="padding:2em;">
    <!-- Header -->
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:2em;">
        <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
        
        <!-- Desktop: knop rechts bovenin -->
        @if(auth()->user()->role !== 'klant')
        <a href="{{ route('dashboard.widgets.create') }}" id="addWidgetBtn" class="inline-flex items-center gap-2" style="background:#c8e1eb;color:#111;padding:0.75em 1.2em;border-radius:7px;font-weight:600;font-size:0.95em;box-shadow:0 1px 3px #e0e7ff;border:none;cursor:pointer;text-decoration:none;">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span>Widget toevoegen</span>
        </a>
        @endif
    </div>

    <!-- Success/Error messages -->
    @if(session('success'))
        <div style="background:#d1fae5;color:#065f46;padding:1em;margin-bottom:1em;border-radius:5px;">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div style="background:#fef2f2;color:#dc2626;padding:1em;margin-bottom:1em;border-radius:5px;">
            {{ session('error') }}
        </div>
    @endif

    <!-- Grid Container voor widgets -->
    <div id="dashboard-grid" class="grid-stack">
        @foreach($layouts as $item)
            @php
                $widget = $item['widget'];
                $layout = $item['layout'];
            @endphp
            
            @if($layout->is_visible)
                <div class="grid-stack-item" 
                     data-gs-x="{{ $layout->grid_x ?? 0 }}" 
                     data-gs-y="{{ $layout->grid_y ?? 0 }}" 
                     data-gs-width="{{ $layout->grid_width ?? 4 }}" 
                     data-gs-height="{{ $layout->grid_height ?? 3 }}"
                     data-widget-id="{{ $widget->id }}"
                     data-is-klant="{{ auth()->user()->role === 'klant' ? 'true' : 'false' }}">
                    <div class="grid-stack-item-content dashboard-widget" 
                         style="background:{{ $widget->background_color }};color:{{ $widget->text_color }};border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,0.1);overflow:hidden;">
                        
                        <!-- Widget Header -->
                        <div class="widget-header" style="display:flex;justify-content:space-between;align-items:center;padding:1em;border-bottom:1px solid rgba(0,0,0,0.1);">
                            <h3 style="font-weight:600;font-size:1.1em;margin:0;">{{ $widget->title }}</h3>
                            <div class="widget-controls" style="display:flex;gap:0.5em;">
                                @can('update', $widget)
                                    <!-- Edit -->
                                    <a href="{{ route('dashboard.widgets.edit', $widget) }}" style="background:transparent;border:none;cursor:pointer;padding:0.3em;text-decoration:none;color:inherit;display:inline-flex;align-items:center;" title="Widget bewerken">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                @endcan
                                
                                <!-- Minimize/Maximize -->
                                <button class="widget-toggle" style="background:transparent;border:none;cursor:pointer;padding:0.3em;">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>
                                
                                @can('delete', $widget)
                                    <!-- Delete -->
                                    <form action="{{ route('dashboard.widgets.destroy', $widget) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" onclick="return confirm('Widget verwijderen?')" style="background:transparent;border:none;cursor:pointer;padding:0.3em;">
                                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        </div>

                        <!-- Widget Content -->
                        <div class="widget-content" style="padding:1.5em;">
                            @if($widget->type === 'text')
                                <div style="font-size:0.95em;line-height:1.6;">
                                    {!! nl2br(e($widget->content)) !!}
                                </div>
                            
                            @elseif($widget->type === 'metric')
                                <div style="text-align:center;">
                                    <div style="font-size:3em;font-weight:700;margin-bottom:0.2em;" id="metric-{{ $widget->id }}">
                                        <span style="font-size:0.5em;opacity:0.5;">⏳</span>
                                    </div>
                                    <div style="font-size:0.9em;opacity:0.7;">
                                        {{ $widget->title }}
                                    </div>
                                </div>
                                <script>
                                    // Laad live metric data - EXACT zoals in create preview!
                                    document.addEventListener('DOMContentLoaded', function() {
                                        const metricEl = document.getElementById('metric-{{ $widget->id }}');
                                        if (!metricEl) return;
                                        
                                        // Parse widget config voor metric type
                                        let config = @json(json_decode($widget->chart_data, true) ?? []);
                                        const metricType = config.metric_type || 'custom';
                                        
                                        console.log('📊 Loading metric {{ $widget->id }}:', metricType);
                                        
                                        // Als custom: toon gewoon de content waarde
                                        if (metricType === 'custom') {
                                            metricEl.textContent = '{{ $widget->content ?? "0" }}';
                                            console.log('✅ Custom metric {{ $widget->id }}: {{ $widget->content ?? "0" }}');
                                            return;
                                        }
                                        
                                        // Voor auto metrics: gebruik DEZELFDE endpoint als create preview
                                        fetch('/dashboard/stats/metric', {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/json',
                                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                            },
                                            body: JSON.stringify({ metric_type: metricType })
                                        })
                                        .then(response => response.json())
                                        .then(data => {
                                            if (data.formatted) {
                                                metricEl.textContent = data.formatted;
                                                console.log('✅ Metric {{ $widget->id }} updated:', data.formatted);
                                            } else {
                                                metricEl.textContent = '0';
                                                console.warn('⚠️ Metric {{ $widget->id }}: geen data gevonden');
                                            }
                                        })
                                        .catch(err => {
                                            console.error('❌ Fout bij laden metric {{ $widget->id }}:', err);
                                            metricEl.textContent = '0';
                                        });
                                    });
                                </script>
                            
                            @elseif($widget->type === 'image' && $widget->image_path)
                                @php
                                    $imageUrl = app()->environment('production') 
                                        ? asset('uploads/' . $widget->image_path)
                                        : asset('storage/' . $widget->image_path);
                                @endphp
                                <img src="{{ $imageUrl }}" alt="{{ $widget->title }}" style="width:100%;height:auto;border-radius:8px;">
                            
                            @elseif($widget->type === 'button')
                                <a href="{{ $widget->button_url }}" class="inline-flex items-center justify-center gap-2" style="background:{{ $widget->button_color ?? '#c8e1eb' }};color:#111;padding:0.8em 1.5em;border-radius:7px;text-decoration:none;font-weight:600;width:100%;text-align:center;">
                                    {{ $widget->button_text }}
                                </a>
                            
                            @elseif($widget->type === 'chart')
                                <canvas id="chart-{{ $widget->id }}" width="400" height="200"></canvas>
                                <script>
                                    // Laad chart data via analytics API
                                    document.addEventListener('DOMContentLoaded', function() {
                                        const ctx = document.getElementById('chart-{{ $widget->id }}');
                                        if (!ctx) return;
                                        
                                        let config = @json(json_decode($widget->chart_data, true));
                                        
                                        // Fallback voor null of lege config
                                        if (!config || !config.chart_type) {
                                            config = {
                                                chart_type: 'diensten',
                                                scope: 'auto',
                                                periode: 'laatste-30-dagen'
                                            };
                                            console.warn('⚠️ Chart {{ $widget->id }} heeft geen config, gebruik fallback:', config);
                                        }
                                        
                                        console.log('📊 Chart config:', config);
                                        
                                        laadChartData{{ $widget->id }}(config);
                                    });
                                    
                                    function laadChartData{{ $widget->id }}(config) {
                                        const chartType = config.chart_type;
                                        const scope = config.scope || 'auto';
                                        const periode = config.periode || 'laatste-30-dagen';
                                        
                                        // Bereken datum range op basis van periode
                                        const { start, eind } = berekenPeriode(periode);
                                        
                                        console.log('🔄 Loading chart data...', { chartType, scope, start, eind });
                                        
                                        fetch(`/api/dashboard/analytics?start=${start}&eind=${eind}&scope=${scope}`)
                                            .then(r => r.json())
                                            .then(data => {
                                                if (!data.success) {
                                                    console.error('❌ Chart data laden mislukt:', data.message);
                                                    return;
                                                }
                                                
                                                renderChart{{ $widget->id }}(chartType, data);
                                            })
                                            .catch(err => console.error('❌ Fout bij laden chart:', err));
                                    }
                                    
                                    function berekenPeriode(periode) {
                                        const vandaag = new Date();
                                        let start, eind = vandaag.toISOString().split('T')[0];
                                        
                                        switch(periode) {
                                            case 'laatste-7-dagen':
                                                start = new Date(vandaag.setDate(vandaag.getDate() - 7)).toISOString().split('T')[0];
                                                break;
                                            case 'laatste-30-dagen':
                                                start = new Date(vandaag.setDate(vandaag.getDate() - 30)).toISOString().split('T')[0];
                                                break;
                                            case 'laatste-90-dagen':
                                                start = new Date(vandaag.setDate(vandaag.getDate() - 90)).toISOString().split('T')[0];
                                                break;
                                            case 'deze-week':
                                                start = new Date(vandaag.setDate(vandaag.getDate() - vandaag.getDay() + 1)).toISOString().split('T')[0];
                                                break;
                                            case 'deze-maand':
                                                start = new Date(vandaag.getFullYear(), vandaag.getMonth(), 1).toISOString().split('T')[0];
                                                break;
                                            case 'dit-kwartaal':
                                                const kwartaal = Math.floor(vandaag.getMonth() / 3);
                                                start = new Date(vandaag.getFullYear(), kwartaal * 3, 1).toISOString().split('T')[0];
                                                break;
                                            case 'dit-jaar':
                                                start = new Date(vandaag.getFullYear(), 0, 1).toISOString().split('T')[0];
                                                break;
                                            default:
                                                start = new Date(vandaag.setDate(vandaag.getDate() - 30)).toISOString().split('T')[0];
                                        }
                                        
                                        return { start, eind };
                                    }
                                    
                                    function renderChart{{ $widget->id }}(type, data) {
                                        const ctx = document.getElementById('chart-{{ $widget->id }}');
                                        
                                        let chartConfig = {};
                                        
                                        switch(type) {
                                            case 'diensten':
                                                chartConfig = {
                                                    type: 'doughnut',
                                                    data: {
                                                        labels: data.dienstenVerdeling.labels,
                                                        datasets: [{data: data.dienstenVerdeling.values, backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899']}]
                                                    },
                                                    options: { responsive: true, maintainAspectRatio: false }
                                                };
                                                break;
                                            case 'status':
                                                chartConfig = {
                                                    type: 'doughnut',
                                                    data: {
                                                        labels: ['Uitgevoerd', 'Niet uitgevoerd'],
                                                        datasets: [{data: [data.prestatieStatus.uitgevoerd, data.prestatieStatus.nietUitgevoerd], backgroundColor: ['#10b981', '#ef4444']}]
                                                    },
                                                    options: { responsive: true, maintainAspectRatio: false }
                                                };
                                                break;
                                            case 'omzet':
                                                chartConfig = {
                                                    type: 'line',
                                                    data: {
                                                        labels: data.omzetTrend.labels,
                                                        datasets: [{
                                                            label: 'Bruto', data: data.omzetTrend.bruto,
                                                            borderColor: '#3b82f6', backgroundColor: 'rgba(59, 130, 246, 0.1)', tension: 0.4, fill: true
                                                        }, {
                                                            label: 'Netto', data: data.omzetTrend.netto,
                                                            borderColor: '#10b981', backgroundColor: 'rgba(16, 185, 129, 0.1)', tension: 0.4, fill: true
                                                        }]
                                                    },
                                                    options: { responsive: true, maintainAspectRatio: false }
                                                };
                                                break;
                                            case 'medewerker':
                                                chartConfig = {
                                                    type: 'bar',
                                                    data: {
                                                        labels: data.medewerkerPrestaties.labels,
                                                        datasets: [{label: 'Prestaties', data: data.medewerkerPrestaties.values, backgroundColor: '#3b82f6'}]
                                                    },
                                                    options: { responsive: true, maintainAspectRatio: false }
                                                };
                                                break;
                                            case 'commissie':
                                                chartConfig = {
                                                    type: 'bar',
                                                    data: {
                                                        labels: data.commissieTrend.labels,
                                                        datasets: [{
                                                            label: 'Organisatie', data: data.commissieTrend.organisatie, backgroundColor: '#f59e0b'
                                                        }, {
                                                            label: 'Medewerkers', data: data.commissieTrend.medewerkers, backgroundColor: '#10b981'
                                                        }]
                                                    },
                                                    options: { responsive: true, maintainAspectRatio: false }
                                                };
                                                break;
                                            case 'bikefits-totaal':
                                                chartConfig = {
                                                    type: 'line',
                                                    data: {
                                                        labels: data.bikefitStats.trend.labels,
                                                        datasets: [{
                                                            label: 'Bikefits', data: data.bikefitStats.trend.values,
                                                            borderColor: '#8b5cf6', backgroundColor: 'rgba(139, 92, 246, 0.1)', tension: 0.4, fill: true
                                                        }]
                                                    },
                                                    options: { responsive: true, maintainAspectRatio: false }
                                                };
                                                break;
                                            case 'bikefits-medewerker':
                                                chartConfig = {
                                                    type: 'bar',
                                                    data: {
                                                        labels: data.bikefitStats.perMedewerker.labels,
                                                        datasets: [{label: 'Bikefits', data: data.bikefitStats.perMedewerker.values, backgroundColor: '#8b5cf6'}]
                                                    },
                                                    options: { responsive: true, maintainAspectRatio: false }
                                                };
                                                break;
                                            case 'testen-totaal':
                                                chartConfig = {
                                                    type: 'line',
                                                    data: {
                                                        labels: data.inspanningstestStats.trend.labels,
                                                        datasets: [{
                                                            label: 'Testen', data: data.inspanningstestStats.trend.values,
                                                            borderColor: '#ec4899', backgroundColor: 'rgba(236, 72, 153, 0.1)', tension: 0.4, fill: true
                                                        }]
                                                    },
                                                    options: { responsive: true, maintainAspectRatio: false }
                                                };
                                                break;
                                            case 'testen-medewerker':
                                                chartConfig = {
                                                    type: 'bar',
                                                    data: {
                                                        labels: data.inspanningstestStats.perMedewerker.labels,
                                                        datasets: [{label: 'Testen', data: data.inspanningstestStats.perMedewerker.values, backgroundColor: '#ec4899'}]
                                                    },
                                                    options: { responsive: true, maintainAspectRatio: false }
                                                };
                                                break;
                                            case 'organisaties':
                                                chartConfig = {
                                                    type: 'bar',
                                                    data: {
                                                        labels: data.omzetPerOrganisatie.labels,
                                                        datasets: [{
                                                            label: 'Bruto', data: data.omzetPerOrganisatie.bruto, backgroundColor: '#3b82f6'
                                                        }, {
                                                            label: 'Netto', data: data.omzetPerOrganisatie.netto, backgroundColor: '#10b981'
                                                        }]
                                                    },
                                                    options: { responsive: true, maintainAspectRatio: false }
                                                };
                                                break;
                                        }
                                        
                                        new Chart(ctx, chartConfig);
                                        console.log('✅ Chart rendered:', type);
                                    }
                                </script>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    </div>
</div>

<!-- Add Widget Modal -->
<div id="addWidgetModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:12px;padding:2em;max-width:500px;width:90%;">
        <h2 style="font-size:1.5em;font-weight:700;margin-bottom:1em;">Widget toevoegen</h2>
        <a href="{{ route('dashboard.widgets.create') }}?type=chart" class="block" style="padding:1em;background:#f3f4f6;border-radius:8px;text-decoration:none;color:#111;margin-bottom:0.5em;transition:background 0.2s;">
            <div style="font-weight:600;">📊 Grafiek</div>
            <div style="font-size:0.9em;opacity:0.7;">Voeg een grafiek toe met data visualisatie</div>
        </a>
        <a href="{{ route('dashboard.widgets.create') }}?type=text" class="block" style="padding:1em;background:#f3f4f6;border-radius:8px;text-decoration:none;color:#111;margin-bottom:0.5em;">
            <div style="font-weight:600;">📝 Tekst</div>
            <div style="font-size:0.9em;opacity:0.7;">Voeg een tekst widget toe</div>
        </a>
        <a href="{{ route('dashboard.widgets.create') }}?type=metric" class="block" style="padding:1em;background:#f3f4f6;border-radius:8px;text-decoration:none;color:#111;margin-bottom:0.5em;">
            <div style="font-weight:600;">📈 Metric</div>
            <div style="font-size:0.9em;opacity:0.7;">Toon een belangrijke metric</div>
        </a>
        <a href="{{ route('dashboard.widgets.create') }}?type=button" class="block" style="padding:1em;background:#f3f4f6;border-radius:8px;text-decoration:none;color:#111;margin-bottom:0.5em;">
            <div style="font-weight:600;">🔘 Knop</div>
            <div style="font-size:0.9em;opacity:0.7;">Voeg een actie knop toe</div>
        </a>
        <a href="{{ route('dashboard.widgets.create') }}?type=image" class="block" style="padding:1em;background:#f3f4f6;border-radius:8px;text-decoration:none;color:#111;margin-bottom:1em;">
            <div style="font-weight:600;">🖼️ Afbeelding</div>
            <div style="font-size:0.9em;opacity:0.7;">Voeg een afbeelding toe</div>
        </a>
        <button id="closeModalBtn" style="width:100%;padding:0.8em;background:#e5e7eb;border:none;border-radius:7px;cursor:pointer;font-weight:600;">
            Annuleren
        </button>
    </div>
</div>

<!-- Gridstack CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/gridstack@8.4.0/dist/gridstack.min.css" />

<!-- Gridstack JS -->
<script src="https://cdn.jsdelivr.net/npm/gridstack@8.4.0/dist/gridstack-all.js"></script>

<!-- Chart.js voor grafieken -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const userRole = '{{ auth()->user()->role }}';
    
    // ⚡ Check of we op mobiel zijn
    const isMobile = window.innerWidth < 768;
    
    if (isMobile) {
        console.log('📱 Mobiel gedetecteerd - Gridstack uitgeschakeld');
        // Op mobiel: geen drag & drop, gewoon statische grid
        return;
    }
    
    // ⚡ Bepaal per widget of deze resizable is
    const widgets = @json($layouts->map(function($item) {
        return [
            'id' => $item['widget']->id,
            'canResize' => $item['widget']->canBeResizedBy(auth()->user()),
            'canDrag' => $item['widget']->canBeDraggedBy(auth()->user())
        ];
    }));
    
    console.log('🔐 Widget permissions:', widgets);
    
    // Initialize Gridstack ZONDER animation + met auto-compact (alleen desktop)
    const grid = GridStack.init({
        cellHeight: 80,
        column: 12,
        float: false, // ⚡ FALSE = auto-compact naar boven!
        animate: false, // Uit tijdens load!
        resizable: {
            handles: 'e, se, s, sw, w' // Standaard handles
        },
        draggable: {
            handle: '.widget-header'
        },
        disableOneColumnMode: true // ⚡ BELANGRIJK: voorkom auto 1-column mode
    });

    // ⚡ FIX: Force correcte groottes NA initialisatie + zet per-widget rechten
    setTimeout(() => {
        console.log('🔧 Fixing widget sizes from database...');
        
        const items = document.querySelectorAll('.grid-stack-item');
        items.forEach(item => {
            const widgetId = parseInt(item.getAttribute('data-widget-id'));
            const w = parseInt(item.getAttribute('data-gs-width')) || 4;
            const h = parseInt(item.getAttribute('data-gs-height')) || 3;
            const x = parseInt(item.getAttribute('data-gs-x')) || 0;
            const y = parseInt(item.getAttribute('data-gs-y')) || 0;
            const isKlant = item.getAttribute('data-is-klant') === 'true';
            
            // Zoek widget permissions
            const widgetPerms = widgets.find(widget => widget.id === widgetId);
            
            console.log(`Widget ${widgetId}: Forcing ${w}x${h} at (${x},${y})`, { 
                isKlant, 
                canResize: widgetPerms?.canResize,
                canDrag: widgetPerms?.canDrag 
            });
            
            // ⚡ Voor KLANTEN: gebruik MASTER grootte + disable resize (maar WEL move!)
            // ⚡ Voor ADMIN/MEDEWERKER: normale rechten
            grid.update(item, {
                x: x,
                y: y,
                w: w,
                h: h,
                noResize: isKlant ? true : !widgetPerms?.canResize, // Klanten kunnen NIET resizen
                noMove: isKlant ? false : !widgetPerms?.canDrag, // ✅ Klanten KUNNEN verplaatsen!
                locked: false // NIET vergrendelen voor klanten
            });
        });
        
        // ⚡ BELANGRIJK: Voor klanten, compact altijd de layout om gaten te verwijderen
        if (userRole === 'klant') {
            console.log('🔧 Klant dashboard: compacting layout om gaten te verwijderen...');
            grid.compact();
        }
        
        grid.opts.animate = true; // Enable animation
        console.log('✅ All widgets configured with correct permissions and sizes!');
    }, 150);

    // Save layout on change (admin/medewerker én klanten kunnen nu verplaatsen!)
    grid.on('change', function(event, items) {
        if (!items || items.length === 0) return;
        
        items.forEach(item => {
            const widgetId = item.el.getAttribute('data-widget-id');
            
            console.log('💾 Gebruiker wijzigt widget layout:', {
                role: userRole,
                id: widgetId,
                x: item.x,
                y: item.y,
                width: item.w,
                height: item.h
            });
            
            fetch('{{ route("dashboard.widgets.updateLayout") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    widget_id: widgetId,
                    grid_x: item.x,
                    grid_y: item.y,
                    grid_width: item.w,
                    grid_height: item.h
                })
            })
            .then(response => response.json())
            .then(data => {
                console.log('✅ Layout opgeslagen:', data);
            })
            .catch(error => {
                console.error('❌ Error bij opslaan layout:', error);
            });
        });
    });
    
    // ⚡ Auto-compact bij widget verwijdering
    grid.on('removed', function(event, items) {
        console.log('🗑️ Widget verwijderd, compacting...');
        grid.compact();
    });

    // Widget toggle (minimize/maximize)
    document.querySelectorAll('.widget-toggle').forEach(btn => {
        btn.addEventListener('click', function() {
            const widget = this.closest('.dashboard-widget');
            const content = widget.querySelector('.widget-content');
            const isHidden = content.style.display === 'none';
            
            content.style.display = isHidden ? 'block' : 'none';
            
            // Rotate icon
            const icon = this.querySelector('svg');
            icon.style.transform = isHidden ? 'rotate(0deg)' : 'rotate(-90deg)';
        });
    });
});
</script>

<style>
/* DESKTOP: Normale knop in header */
@media (min-width: 769px) {
    #addWidgetBtn {
        position: relative !important;
    }
}

/* ⚡ MOBIEL: Floating Action Button rechtsonder */
@media (max-width: 768px) {
    #addWidgetBtn {
        position: fixed !important;
        bottom: 16px !important;
        right: 16px !important;
        z-index: 9999 !important;
        box-shadow: 0 4px 16px rgba(0,0,0,0.3) !important;
        padding: 0.7em 1em !important;
        font-size: 0.85em !important;
    }
    
    #addWidgetBtn svg {
        width: 14px !important;
        height: 14px !important;
    }
    
    #addWidgetBtn:active {
        transform: scale(0.95) !important;
    }
}

/* ⚡ MOBIEL: Statische grid layout (geen Gridstack) */
@media (max-width: 768px) {
    #dashboard-grid {
        display: flex !important;
        flex-direction: column !important;
        gap: 1rem !important;
    }
    
    .grid-stack-item {
        position: static !important;
        width: 100% !important;
        height: auto !important;
        transform: none !important;
        margin-bottom: 0 !important;
    }
    
    .grid-stack-item-content {
        position: static !important;
        width: 100% !important;
        height: auto !important;
        min-height: 200px !important;
        cursor: default !important;
    }
    
    .widget-header {
        cursor: default !important;
    }
    
    /* Verberg resize handles op mobiel */
    .grid-stack-item > .ui-resizable-handle {
        display: none !important;
    }
}

/* DESKTOP: Gridstack styling */
@media (min-width: 769px) {
    /* Admin/Medewerker: draggable widgets */
    .grid-stack-item-content {
        cursor: move;
    }

    .widget-header {
        cursor: grab;
    }

    .widget-header:active {
        cursor: grabbing;
    }

    /* ⚡ KLANTEN: kunnen verplaatsen, maar NIET resizen */
    @if(auth()->user()->role === 'klant')
    /* Verberg resize handles voor klanten */
    .grid-stack-item > .ui-resizable-handle {
        display: none !important;
    }
    @endif

    .widget-content {
        cursor: default !important;
    }

    /* Gridstack custom styling */
    .grid-stack-item {
        transition: all 0.3s ease;
    }

    .grid-stack-item:hover {
        z-index: 100;
    }
}
</style>
@endsection