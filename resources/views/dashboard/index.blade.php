@extends('layouts.app')

@section('content')
<div class="dashboard-container" style="padding:2em;">
    <!-- Header -->
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:2em;">
        <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
        
        @if(auth()->user()->role !== 'klant')
        <button id="addWidgetBtn" class="inline-flex items-center gap-2" style="background:#c8e1eb;color:#111;padding:0.5em 1.2em;border-radius:7px;font-weight:600;font-size:0.95em;box-shadow:0 1px 3px #e0e7ff;border:none;cursor:pointer;">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Widget toevoegen
        </button>
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
                     data-widget-id="{{ $widget->id }}">
                    <div class="grid-stack-item-content dashboard-widget" 
                         style="background:{{ $widget->background_color }};color:{{ $widget->text_color }};border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,0.1);overflow:hidden;">
                        
                        <!-- Widget Header -->
                        <div class="widget-header" style="display:flex;justify-content:space-between;align-items:center;padding:1em;border-bottom:1px solid rgba(0,0,0,0.1);">
                            <h3 style="font-weight:600;font-size:1.1em;margin:0;">{{ $widget->title }}</h3>
                            <div class="widget-controls" style="display:flex;gap:0.5em;">
                                @if(auth()->user()->role !== 'klant' && ($widget->created_by === auth()->id() || in_array(auth()->user()->role, ['admin', 'super_admin', 'superadmin'])))
                                <!-- Edit -->
                                <a href="{{ route('dashboard.widgets.edit', $widget) }}" style="background:transparent;border:none;cursor:pointer;padding:0.3em;text-decoration:none;color:inherit;display:inline-flex;align-items:center;" title="Widget bewerken">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                @endif
                                
                                <!-- Minimize/Maximize -->
                                <button class="widget-toggle" style="background:transparent;border:none;cursor:pointer;padding:0.3em;">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>
                                
                                @if(auth()->user()->role !== 'klant')
                                    @php
                                        $canDelete = false;
                                        $userRole = auth()->user()->role;
                                        
                                        // Super admin (beide spellingen)
                                        if (in_array($userRole, ['super_admin', 'superadmin'])) {
                                            $canDelete = true;
                                        }
                                        // Admin mag alles binnen organisatie
                                        elseif ($userRole === 'admin') {
                                            $canDelete = true;
                                        }
                                        // Medewerker mag alleen eigen widgets
                                        elseif ($userRole === 'medewerker' && $widget->created_by === auth()->id()) {
                                            $canDelete = true;
                                        }
                                    @endphp
                                    
                                    @if($canDelete)
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
                                    @endif
                                @endif
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
                                    <div style="font-size:3em;font-weight:700;margin-bottom:0.2em;">
                                        {{ $widget->content }}
                                    </div>
                                    <div style="font-size:0.9em;opacity:0.7;">
                                        {{ $widget->title }}
                                    </div>
                                </div>
                            
                            @elseif($widget->type === 'image' && $widget->image_path)
                                <img src="{{ asset('storage/' . $widget->image_path) }}" alt="{{ $widget->title }}" style="width:100%;height:auto;border-radius:8px;">
                            
                            @elseif($widget->type === 'button')
                                <a href="{{ $widget->button_url }}" class="inline-flex items-center justify-center gap-2" style="background:#c8e1eb;color:#111;padding:0.8em 1.5em;border-radius:7px;text-decoration:none;font-weight:600;width:100%;text-align:center;">
                                    {{ $widget->button_text }}
                                </a>
                            
                            @elseif($widget->type === 'chart')
                                <canvas id="chart-{{ $widget->id }}" width="400" height="200"></canvas>
                                <script>
                                    // Chart.js initialisatie komt hier
                                    document.addEventListener('DOMContentLoaded', function() {
                                        const ctx = document.getElementById('chart-{{ $widget->id }}');
                                        if (ctx) {
                                            new Chart(ctx, {!! json_encode($widget->chart_data) !!});
                                        }
                                    });
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
        <a href="{{ route('dashboard.widgets.create') }}" class="block" style="padding:1em;background:#f3f4f6;border-radius:8px;text-decoration:none;color:#111;margin-bottom:0.5em;transition:background 0.2s;">
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
    
    // Initialize Gridstack ZONDER animation
    const grid = GridStack.init({
        cellHeight: 80,
        column: 12,
        float: true,
        animate: false, // Uit tijdens load!
        resizable: {
            handles: userRole !== 'klant' ? 'e, se, s, sw, w' : false
        },
        draggable: {
            handle: '.widget-header'
        }
    });

    // ⚡ FIX: Force correcte groottes NA initialisatie
    setTimeout(() => {
        console.log('🔧 Fixing widget sizes from database...');
        
        const items = document.querySelectorAll('.grid-stack-item');
        items.forEach(item => {
            const w = parseInt(item.getAttribute('data-gs-width')) || 4;
            const h = parseInt(item.getAttribute('data-gs-height')) || 3;
            const x = parseInt(item.getAttribute('data-gs-x')) || 0;
            const y = parseInt(item.getAttribute('data-gs-y')) || 0;
            
            console.log(`Widget ${item.dataset.widgetId}: Forcing ${w}x${h} at (${x},${y})`);
            
            // Update via Gridstack API
            grid.update(item, {
                x: x,
                y: y,
                w: w,
                h: h
            });
        });
        
        // Enable animation weer
        grid.opts.animate = true;
        console.log('✅ All widgets resized!');
    }, 100);

    // Save layout on change (zowel move als resize)
    grid.on('change', function(event, items) {
        if (!items || items.length === 0) return;
        
        items.forEach(item => {
            const widgetId = item.el.getAttribute('data-widget-id');
            
            // Log voor debugging
            console.log('Widget changed:', {
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
                console.log('Layout opgeslagen:', data);
            })
            .catch(error => {
                console.error('Error bij opslaan layout:', error);
            });
        });
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

    // Add Widget Modal
    const addWidgetBtn = document.getElementById('addWidgetBtn');
    const modal = document.getElementById('addWidgetModal');
    const closeModalBtn = document.getElementById('closeModalBtn');

    if (addWidgetBtn) {
        addWidgetBtn.addEventListener('click', function() {
            modal.style.display = 'flex';
        });
    }

    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', function() {
            modal.style.display = 'none';
        });
    }

    // Close modal on outside click
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });
});
</script>

<style>
.grid-stack-item-content {
    cursor: move;
}

.widget-header {
    cursor: grab;
}

.widget-header:active {
    cursor: grabbing;
}

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

@media (max-width: 768px) {
    .grid-stack {
        grid-template-columns: 1fr !important;
    }
}
</style>
@endsection