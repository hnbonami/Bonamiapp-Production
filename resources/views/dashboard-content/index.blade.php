@extends('layouts.app')

@section('content')
<link href="{{ asset('css/dashboard-content.css') }}" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

@section('content')
<script>
// Dashboard tile action functions
function editTile(id) {
    window.location.href = `/dashboard-content/${id}/edit`;
}

function pinTile(id) {
    fetch(`/dashboard-content/${id}/pin`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        }
    }).then(() => location.reload());
}

function unpinTile(id) {
    fetch(`/dashboard-content/${id}/unpin`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        }
    }).then(() => location.reload());
}

function archiveTile(id) {
    if (confirm('Weet je zeker dat je deze tegel wilt archiveren?')) {
        fetch(`/dashboard-content/${id}/archive`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        }).then(() => location.reload());
    }
}

function deleteTile(id) {
    if (confirm('Weet je zeker dat je deze tegel definitief wilt verwijderen?')) {
        fetch(`/dashboard-content/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        }).then(() => location.reload());
    }
}

// Hide "Nieuwe Content" tile with JavaScript as fallback
document.addEventListener('DOMContentLoaded', function() {
    // Find and hide the "Nieuwe Content" tile
    const tiles = document.querySelectorAll('.dashboard-tile');
    tiles.forEach(tile => {
        const content = tile.textContent || tile.innerText;
        if (content.includes('Nieuwe Content') || 
            content.includes('➕') || 
            tile.classList.contains('bg-gray-50') ||
            tile.classList.contains('border-dashed')) {
            tile.style.display = 'none';
            tile.remove();
        }
    });
});
</script>

<div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-extrabold text-gray-900 ml-8">Welkom {{ auth()->user()->voornaam ?? auth()->user()->name }}</h1>
        @if(auth()->user()->role !== 'klant')
            <div class="flex gap-3">
                <a href="{{ route('dashboard-content.archived') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                    📁 Archief
                </a>
                <a href="{{ route('dashboard-content.create') }}" 
                   class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium text-gray-700 hover:text-gray-900 transition-all duration-200"
                   style="background-color: #c8e1eb; border: 1px solid #a5c9d6;">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Nieuwe Content
                </a>
            </div>
        @endif
    </div>

    @if(session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <div class="dashboard-content-grid" id="content-grid">
        @if($canManage)
            <!-- Add new content button -->
            <a href="{{ route('dashboard-content.create') }}" 
               class="add-content-btn dashboard-tile" 
               style="text-decoration: none;"
               onclick="console.log('Button clicked! Going to:', this.href)">
                <div class="add-content-icon">➕</div>
                <div class="add-content-text">Nieuwe Content</div>
            </a>
        @endif

        @forelse($content as $item)
            @if($hasNewFields && $item->link_url)
                <a href="{{ $item->link_url }}" 
                   @if($item->open_in_new_tab) target="_blank" rel="noopener noreferrer" @endif
                   class="dashboard-tile {{ $item->tile_class }} tile-type-{{ $item->type }} clickable-tile"
                   style="background-color: {{ $item->background_color }}; color: {{ $item->text_color }}; text-decoration: none; display: block;"
                   data-id="{{ $item->id }}"
                   @if($item->is_pinned) data-pinned="true" @endif>
            @else
                <div class="dashboard-tile {{ $hasNewFields ? $item->tile_class : 'dashboard-tile-medium' }} tile-type-{{ $hasNewFields ? $item->type : 'note' }}"
                     style="background-color: {{ $hasNewFields ? $item->background_color : '#ffffff' }}; color: {{ $hasNewFields ? $item->text_color : '#111827' }};"
                     data-id="{{ $item->id }}"
                     @if($hasNewFields && $item->is_pinned) data-pinned="true" @endif>
            @endif
                
                <!-- Priority indicator -->
                @if($hasNewFields)
                    <div class="priority-indicator priority-{{ $item->priority }}"></div>
                @endif
                
                @if($hasNewFields && $item->is_pinned)
                    <div class="tile-pinned"></div>
                @endif

                @if($canManage && $hasNewFields && $item->canDrag(Auth::user()))
                    <div class="drag-handle">⋮⋮</div>
                @elseif($canManage && !$hasNewFields)
                    <div class="drag-handle">⋮⋮</div>
                @endif

                <!-- Tile header -->
                <div class="tile-header">
                    <h3 class="tile-title">{{ $item->title }}</h3>
                    <span class="tile-icon">{{ $hasNewFields ? $item->type_icon : '📝' }}</span>
                </div>

                <!-- Tile image (if exists) -->
                @if($hasNewFields && $item->image_path)
                    <img src="{{ $item->getImageUrl() }}" alt="{{ $item->title }}" class="tile-image">
                @endif

                <!-- Tile content -->
                <div class="tile-content">
                    @include('components.dashboard-tile-content', ['item' => $item, 'hasNewFields' => $hasNewFields])
                </div>

                <!-- Management actions -->
                @if($canManage)
                                        <div class="tile-actions">
                        <button onclick="editTile({{ $item->id }})" class="edit-btn" title="Bewerken">
                            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                            </svg>
                        </button>
                        
                        @if($hasNewFields && $item->is_pinned)
                            <button onclick="unpinTile({{ $item->id }})" class="pin-btn pinned" title="Losmaken">
                                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path d="M12 17l-5-5h3V7h4v5h3l-5 5z"/>
                                </svg>
                            </button>
                        @else
                            <button onclick="pinTile({{ $item->id }})" class="pin-btn" title="Vastpinnen">
                                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path d="M12 7l5 5h-3v5h-4v-5H7l5-5z"/>
                                </svg>
                            </button>
                        @endif
                        
                        <button onclick="archiveTile({{ $item->id }})" class="archive-btn" title="Archiveren">
                            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <polyline points="21,8 21,21 3,21 3,8"/>
                                <rect x="1" y="3" width="22" height="5"/>
                                <line x1="10" y1="12" x2="14" y2="12"/>
                            </svg>
                        </button>
                        
                        <button onclick="deleteTile({{ $item->id }})" class="delete-btn" title="Verwijderen">
                            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <polyline points="3,6 5,6 21,6"/>
                                <path d="M19,6v14a2,2,0,0,1-2,2H7a2,2,0,0,1-2-2V6m3,0V4a2,2,0,0,1,2-2h4a2,2,0,0,1,2,2V6"/>
                                <line x1="10" y1="11" x2="10" y2="17"/>
                                <line x1="14" y1="11" x2="14" y2="17"/>
                            </svg>
                        </button>
                    </div>
                @endif

                <!-- Metadata -->
                <div style="position: absolute; bottom: 8px; left: 12px; font-size: 0.7em; opacity: 0.6;">
                    {{ $item->created_at->diffForHumans() }}
                    @if($hasNewFields && $item->expires_at)
                        • Verloopt {{ $item->expires_at->diffForHumans() }}
                    @endif
                </div>

            @if($hasNewFields && $item->link_url)
                </a>
            @else
                </div>
            @endif
        @empty
            @if(!$canManage)
                <div class="dashboard-tile dashboard-tile-medium" style="background: #f9fafb; border: 2px dashed #d1d5db;">
                    <div style="text-align: center; padding: 20px;">
                        <div style="font-size: 3em; margin-bottom: 12px; opacity: 0.5;">📋</div>
                        <h3 style="color: #6b7280; margin: 0 0 8px 0;">Geen content beschikbaar</h3>
                        <p style="color: #9ca3af; font-size: 0.9em; margin: 0;">
                            Er zijn momenteel geen mededelingen of aankondigingen.
                        </p>
                    </div>
                </div>
            @endif
        @endforelse
    </div>
</div>

@if($canManage && $content->count() > 0)
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
// Drag & Drop functionaliteit
document.addEventListener('DOMContentLoaded', function() {
    const grid = document.getElementById('content-grid');
    
    if (grid) {
        const sortable = Sortable.create(grid, {
            animation: 200,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'sortable-drag',
            handle: '.drag-handle',
            filter: '.add-content-btn',
            
            onEnd: function(evt) {
                // Verzamel nieuwe volgorde
                const items = [];
                const tiles = grid.querySelectorAll('.dashboard-tile[data-id]');
                
                tiles.forEach((tile, index) => {
                    const id = tile.getAttribute('data-id');
                    if (id) {
                        items.push({
                            id: parseInt(id),
                            sort_order: index
                        });
                    }
                });

                // Update server
                fetch('{{ route("dashboard-content.update-order") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ items: items })
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        console.error('Failed to update order');
                        location.reload(); // Reload on failure
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    location.reload();
                });
            }
        });
    }
});

// Custom CSS for sortable
const style = document.createElement('style');
style.textContent = `
    .sortable-ghost {
        opacity: 0.5;
        transform: rotate(2deg);
    }
    .sortable-chosen {
        cursor: grabbing;
    }
    .sortable-drag {
        transform: rotate(5deg);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
    }
`;
document.head.appendChild(style);
</script>
@endif

{{-- Dashboard Content Grid Layout --}}
<style>
/* ONLY TARGET THE ACTUAL DASHBOARD TILES CONTAINER */
#dashboard-tiles-container,
.tiles-display-area {
    display: grid !important;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)) !important;
    gap: 20px !important;
    padding: 20px !important;
    width: 100% !important;
    max-width: 100% !important;
    box-sizing: border-box !important;
}

/* DASHBOARD TILES ONLY */
.dashboard-tile,
.staff-note-tile,
[data-tile-id] {
    width: auto !important;
    max-width: 100% !important;
    min-height: 200px !important;
    flex: none !important;
    display: block !important;
    box-sizing: border-box !important;
    grid-column: span 1 !important;
}

/* TILE SIZE VARIANTS */
.dashboard-tile-small,
[data-tile-size="small"] {
    min-width: 280px !important;
}

.dashboard-tile-medium,
[data-tile-size="medium"] {
    min-width: 350px !important;
}

.dashboard-tile-large,
[data-tile-size="large"] {
    min-width: 450px !important;
    grid-column: span 2 !important;
}

.dashboard-tile-banner,
[data-tile-size="banner"] {
    width: 100% !important;
    grid-column: 1 / -1 !important;
}
</style>



<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('🎯 Dashboard Content Index Grid - Specific Targeting');
    
    // Only target containers that have dashboard tiles
    const tilesContainer = document.querySelector('#dashboard-tiles-container, .tiles-display-area');
    const tiles = document.querySelectorAll('.dashboard-tile, .staff-note-tile, [data-tile-id]');
    
    if (tiles.length > 0 && !tilesContainer) {
        // Find the parent container of the tiles
        const parentContainer = tiles[0].parentElement;
        if (parentContainer) {
            console.log(`Found tiles parent container:`, parentContainer);
            parentContainer.style.cssText = `
                display: grid !important;
                grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)) !important;
                gap: 20px !important;
                padding: 20px !important;
                width: 100% !important;
                max-width: 100% !important;
                box-sizing: border-box !important;
            `;
        }
    }
});
</script>

{{-- Klikbare tegels styling --}}
<style>
.clickable-tile {
    cursor: pointer !important;
    transition: transform 0.2s ease, box-shadow 0.2s ease !important;
    text-decoration: none !important;
}

.clickable-tile:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
    text-decoration: none !important;
}

/* Zorg ervoor dat de actieknoppen nog steeds klikbaar zijn */
.clickable-tile .tile-actions {
    position: relative;
    z-index: 10;
}

.clickable-tile .tile-actions button {
    pointer-events: auto;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('🔗 Adding click functionality to tiles with links...');
    
    // Add the tile data directly to each tile for easier access
    @if(isset($content))
        const tileData = @json($content);
        console.log('Tile data from backend:', tileData);
        
        tileData.forEach(function(item, index) {
            if (item.link_url && item.link_url.trim() !== '') {
                console.log(`🔗 Processing tile with link: ${item.link_url}`);
                
                // Find the corresponding tile element (you might need to adjust selector)
                const tileSelectors = [
                    `.staff-note[data-id="${item.id}"]`,
                    `[data-staff-note-id="${item.id}"]`,
                    `.staff-note:nth-child(${index + 1})`,
                    `.tile:nth-child(${index + 1})`
                ];
                
                let tile = null;
                for (let selector of tileSelectors) {
                    tile = document.querySelector(selector);
                    if (tile) break;
                }
                
                // Fallback: find tile by title or content
                if (!tile) {
                    const allTiles = document.querySelectorAll('.staff-note, [class*="tile"], .card');
                    allTiles.forEach(function(t) {
                        const text = t.textContent || '';
                        if (text.includes(item.title) || text.includes(item.content.substring(0, 50))) {
                            tile = t;
                        }
                    });
                }
                
                if (tile) {
                    console.log(`✅ Found tile element for: ${item.title}`, tile);
                    
                    // Make tile clickable
                    tile.classList.add('clickable-tile');
                    tile.style.cursor = 'pointer';
                    tile.style.position = 'relative';
                    tile.setAttribute('data-link-url', item.link_url);
                    tile.setAttribute('data-open-new-tab', item.open_in_new_tab ? '1' : '0');
                    
                    // Add link indicator
                    const indicator = document.createElement('div');
                    indicator.className = 'tile-link-indicator';
                    indicator.innerHTML = '🔗';
                    indicator.title = `Click to visit: ${item.link_url}`;
                    tile.appendChild(indicator);
                    
                    // Add click handler
                    tile.addEventListener('click', function(e) {
                        // Don't trigger if clicking on edit/delete buttons
                        if (e.target.closest('button, a, .btn, .dropdown')) {
                            console.log('Clicked on button, ignoring...');
                            return;
                        }
                        
                        e.preventDefault();
                        e.stopPropagation();
                        
                        console.log(`🚀 Opening link: ${item.link_url}`);
                        
                        if (item.open_in_new_tab) {
                            window.open(item.link_url, '_blank');
                        } else {
                            window.location.href = item.link_url;
                        }
                    });
                    
                    // Add title for accessibility
                    tile.title = `Click to visit: ${item.link_url}`;
                } else {
                    console.log(`❌ Could not find tile element for: ${item.title}`);
                }
            }
        });
    @endif
    
    // Debug: show all tiles on page
    const allTiles = document.querySelectorAll('.staff-note, [class*="tile"], .card');
    console.log(`📊 Found ${allTiles.length} tile elements on page:`, allTiles);
});
</script>

<script>
// Alternative approach: find tiles by hidden data elements
document.addEventListener('DOMContentLoaded', function() {
    console.log('🔍 Alternative tile linking approach...');
    
    const linkDataElements = document.querySelectorAll('.staff-note-link-data');
    console.log(`Found ${linkDataElements.length} tiles with link data`);
    
    linkDataElements.forEach(function(dataEl) {
        const id = dataEl.getAttribute('data-id');
        const url = dataEl.getAttribute('data-link-url');
        const newTab = dataEl.getAttribute('data-open-new-tab') === '1';
        const title = dataEl.getAttribute('data-title');
        
        console.log(`Processing tile: ${title} (${url})`);
        
        // Find the actual tile element
        const allTiles = document.querySelectorAll('.staff-note, [class*="tile"], .card, .content-item');
        let matchedTile = null;
        
        allTiles.forEach(function(tile) {
            const tileText = tile.textContent || '';
            // Try to match by title or content
            if (tileText.includes(title) && !tile.classList.contains('clickable-tile')) {
                matchedTile = tile;
            }
        });
        
        if (matchedTile) {
            console.log(`✅ Matched tile for: ${title}`, matchedTile);
            
            // Apply click functionality
            matchedTile.classList.add('clickable-tile');
            matchedTile.style.cursor = 'pointer';
            matchedTile.style.position = 'relative';
            
            // Add visual indicator
            const indicator = document.createElement('div');
            indicator.className = 'tile-link-indicator';
            indicator.innerHTML = '🔗';
            indicator.title = `Click to visit: ${url}`;
            indicator.style.cssText = `
                position: absolute;
                top: 8px;
                right: 8px;
                background: rgba(0,0,0,0.7);
                color: white;
                border-radius: 50%;
                width: 24px;
                height: 24px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 12px;
                z-index: 10;
            `;
            matchedTile.appendChild(indicator);
            
            // Add click handler
            matchedTile.addEventListener('click', function(e) {
                // Don't trigger if clicking on buttons
                if (e.target.closest('button, a, .btn, .dropdown, .tile-link-indicator')) {
                    return;
                }
                
                e.preventDefault();
                console.log(`🚀 Clicking tile link: ${url}`);
                
                if (newTab) {
                    window.open(url, '_blank');
                } else {
                    window.location.href = url;
                }
            });
            
            matchedTile.title = `Click to visit: ${url}`;
        } else {
            console.log(`❌ No matching tile found for: ${title}`);
        }
    });
});
</script>


@endsection