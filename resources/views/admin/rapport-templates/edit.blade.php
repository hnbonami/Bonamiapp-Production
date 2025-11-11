@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        
        {{-- Header --}}
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6">
                <div class="flex justify-between items-start">
                    <div>
                        <h1 class="text-3xl font-extrabold text-gray-900">Bewerk Template: {{ $rapportTemplate->naam }}</h1>
                        <p class="text-lg text-gray-600 mt-2">{{ ucfirst($rapportTemplate->rapport_type) }} • {{ $rapportTemplate->total_pages }} pagina's</p>
                    </div>
                    <a href="{{ route('admin.rapport-templates.index') }}" 
                       class="rounded-full px-6 py-2 text-gray-800 font-bold hover:opacity-80 transition" 
                       style="background-color: #c8e1eb;">
                        ← Terug
                    </a>
                </div>
            </div>
        </div>

        {{-- Algemene Instellingen --}}
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">⚙️ Algemene Instellingen</h2>

                <form action="{{ route('admin.rapport-templates.update', $rapportTemplate) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Template Naam --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Template Naam *</label>
                            <input type="text" 
                                   name="naam" 
                                   required
                                   value="{{ old('naam', $rapportTemplate->naam) }}"
                                   class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                            @error('naam')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Status Toggles --}}
                        <div class="space-y-3">
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       name="is_active" 
                                       value="1" 
                                       {{ $rapportTemplate->is_active ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-700">Template is actief</span>
                            </label>

                            <label class="flex items-center">
                                <input type="checkbox" 
                                       name="is_default" 
                                       value="1"
                                       {{ $rapportTemplate->is_default ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-700">Standaard template voor dit type</span>
                            </label>
                        </div>
                    </div>

                    {{-- Beschrijving --}}
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Beschrijving</label>
                        <textarea name="beschrijving" 
                                  rows="3"
                                  class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">{{ old('beschrijving', $rapportTemplate->beschrijving) }}</textarea>
                    </div>

                    {{-- Testtype Koppeling --}}
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Testtype Koppeling</label>
                        <select name="testtype" 
                                id="testtype_select"
                                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Alle testtypes (universeel)</option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">
                            Dit template wordt automatisch gebruikt wanneer dit testtype geselecteerd wordt
                        </p>
                    </div>
                    
                    <script>
                    // Dynamische testtype opties op basis van rapport type
                    document.addEventListener('DOMContentLoaded', function() {
                        const rapportType = '{{ $rapportTemplate->rapport_type }}';
                        const currentTesttype = '{{ old("testtype", $rapportTemplate->testtype) }}';
                        const testtypeSelect = document.getElementById('testtype_select');
                        
                        // EXACTE testtype opties zoals ze in de formulieren staan
                        const testtypeOptions = {
                            'inspanningstest': [
                                { value: 'fietstest', label: 'Inspanningstest Fietsen' },
                                { value: 'looptest', label: 'Inspanningstest Lopen' },
                                { value: 'veldtest_fietsen', label: 'Veldtest Fietsen' },
                                { value: 'veldtest_lopen', label: 'Veldtest Lopen' },
                                { value: 'veldtest_zwemmen', label: 'Veldtest Zwemmen' }
                            ],
                            'bikefit': [
                                { value: 'standaard', label: 'Standaard bikefit' },
                                { value: 'professioneel', label: 'Professionele bikefit' },
                                { value: 'maten_bepalen', label: 'Maten bepalen' },
                                { value: 'zadeldrukmeting', label: 'Zadeldrukmeting' }
                            ]
                        };
                        
                        // Clear en vul opties
                        testtypeSelect.innerHTML = '<option value="">Alle testtypes (universeel)</option>';
                        
                        if (rapportType && testtypeOptions[rapportType]) {
                            testtypeOptions[rapportType].forEach(option => {
                                const opt = document.createElement('option');
                                opt.value = option.value;
                                opt.textContent = option.label;
                                
                                // Select de huidige waarde
                                if (option.value === currentTesttype) {
                                    opt.selected = true;
                                }
                                
                                testtypeSelect.appendChild(opt);
                            });
                        }
                    });
                    </script>

                    {{-- Kleuren --}}
                    <div class="mt-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-3">🎨 Kleuren</h3>
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Primair</label>
                                <input type="color" 
                                       name="primary_color" 
                                       value="{{ $rapportTemplate->getPrimaryColor() }}"
                                       class="w-full h-12 rounded border-gray-300">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Secondair</label>
                                <input type="color" 
                                       name="secondary_color" 
                                       value="{{ $rapportTemplate->getSecondaryColor() }}"
                                       class="w-full h-12 rounded border-gray-300">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Accent</label>
                                <input type="color" 
                                       name="accent_color" 
                                       value="{{ $rapportTemplate->style_settings['colors']['accent'] ?? '#ff6b35' }}"
                                       class="w-full h-12 rounded border-gray-300">
                            </div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <button type="submit" 
                                class="px-6 py-2 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 transition">
                            💾 Opslaan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Pagina's Beheer --}}
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">📄 Pagina's ({{ $rapportTemplate->total_pages }})</h2>
                <p class="text-sm text-gray-600 mb-6">Klik op een pagina om foto's toe te voegen en instellingen aan te passen</p>

                <div class="space-y-3">
                    @foreach($rapportTemplate->pages as $page)
                        <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-500 hover:shadow-md transition">
                            <div class="flex items-center justify-between">
                                {{-- Pagina Info --}}
                                <div class="flex items-center gap-4 flex-1">
                                    <div class="w-12 h-12 rounded-lg bg-blue-100 flex items-center justify-center font-bold text-blue-600">
                                        {{ $page->page_number }}
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="font-bold text-gray-900">{{ $page->page_title }}</h3>
                                        <p class="text-sm text-gray-600">{{ $page->pageType->naam ?? 'Custom pagina' }}</p>
                                    </div>

                                    {{-- Media Status --}}
                                    @if($page->hasMedia())
                                        <div class="flex items-center gap-2 text-sm text-green-600">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/>
                                            </svg>
                                            <span class="font-medium">Foto toegevoegd</span>
                                        </div>
                                    @else
                                        <div class="flex items-center gap-2 text-sm text-gray-400">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            <span>Geen foto</span>
                                        </div>
                                    @endif

                                    {{-- Layout Type --}}
                                    <div class="text-sm text-gray-600">
                                        <span class="px-2 py-1 bg-gray-100 rounded">{{ ucfirst($page->layout_type) }}</span>
                                    </div>
                                </div>

                                {{-- Actions --}}
                                <a href="{{ route('admin.rapport-templates.pages.edit', [$rapportTemplate, $page]) }}" 
                                   class="ml-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium">
                                    ✏️ Bewerken
                                </a>
                            </div>

                            {{-- Preview Foto (indien aanwezig) --}}
                            @if($page->hasMedia())
                                <div class="mt-4 border-t pt-4">
                                    <img src="{{ $page->media_url }}" 
                                         alt="Pagina foto" 
                                         class="h-32 rounded-lg object-cover">
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- NIEUW: Live Preview Sectie --}}
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6" id="preview-section">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">👁️ Live Preview</h2>
                        <p class="text-sm text-gray-600 mt-1">Bekijk hoe jouw rapport eruit ziet met dummy data • A4 formaat (210×297mm)</p>
                    </div>
                    
                    <div class="flex items-center gap-3">
                        {{-- Grid Toggle --}}
                        <label class="flex items-center text-sm text-gray-700 cursor-pointer">
                            <input type="checkbox" 
                                   id="show-margins" 
                                   onchange="toggleMargins()"
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 mr-2">
                            Toon marges
                        </label>
                        
                        <button onclick="refreshPreview()" 
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Ververs Preview
                        </button>
                    </div>
                </div>

                {{-- Pagina Navigatie met Zoom Controls --}}
                <div class="flex items-center justify-center gap-4 mb-6 bg-gray-50 rounded-lg p-4">
                    <button onclick="previousPage()" 
                            id="prev-btn"
                            class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition font-medium disabled:opacity-50 disabled:cursor-not-allowed">
                        ◀ Vorige
                    </button>
                    
                    <div class="text-center">
                        <div class="text-sm text-gray-600">Pagina</div>
                        <div class="text-xl font-bold text-gray-900">
                            <span id="current-page">1</span> / <span id="total-pages">{{ $rapportTemplate->total_pages }}</span>
                        </div>
                    </div>
                    
                    <button onclick="nextPage()" 
                            id="next-btn"
                            class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition font-medium disabled:opacity-50 disabled:cursor-not-allowed">
                        Volgende ▶
                    </button>
                    
                    {{-- Zoom Controls --}}
                    <div class="border-l pl-4 ml-4 flex items-center gap-2">
                        <span class="text-sm text-gray-600">Zoom:</span>
                        <button onclick="zoomOut()" 
                                class="px-3 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                            🔍−
                        </button>
                        <span id="zoom-level" class="text-sm font-medium text-gray-900 min-w-[50px] text-center">100%</span>
                        <button onclick="zoomIn()" 
                                class="px-3 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                            🔍+
                        </button>
                        <button onclick="resetZoom()" 
                                class="px-3 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition text-xs">
                            Reset
                        </button>
                    </div>
                </div>

                {{-- Preview Container met A4 afmetingen --}}
                <div class="bg-gray-100 rounded-lg p-8 flex items-center justify-center">
                    <div class="relative">
                        {{-- A4 Pagina Container --}}
                        <div id="a4-preview-page" class="a4-page shadow-2xl bg-white border border-gray-300">
                            {{-- Loading State --}}
                            <div id="preview-loading" class="flex items-center justify-center h-full">
                                <div class="text-center">
                                    <svg class="animate-spin h-12 w-12 text-blue-600 mx-auto mb-4" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <p class="text-gray-600">Preview laden...</p>
                                </div>
                            </div>
                            
                            {{-- Preview Content --}}
                            <div id="preview-content" class="hidden h-full overflow-auto relative">
                                <!-- Preview content wordt hier geladen via AJAX -->
                            </div>
                        </div>
                        
                        {{-- A4 Dimensie Indicator --}}
                        <div class="absolute -top-6 right-0 bg-blue-600 text-white px-3 py-1 rounded text-xs font-bold shadow-lg">
                            📄 A4 (210 × 297 mm)
                        </div>
                    </div>
                </div>
                
                {{-- Preview Styling voor A4 en foto posities --}}
                <style>
                    /* A4 Pagina Afmetingen (210mm x 297mm bij 96 DPI) */
                    .a4-page {
                        width: 210mm;
                        height: 297mm;
                        padding: 20mm; /* Standaard margins voor print */
                        box-sizing: border-box;
                        position: relative;
                        overflow: hidden;
                        transition: transform 0.3s ease; /* Smooth zoom transitions */
                    }
                    
                    /* Responsive scaling voor kleinere schermen */
                    @media (max-width: 900px) {
                        .a4-page {
                            width: 100%;
                            height: auto;
                            min-height: 297mm;
                            max-width: 210mm;
                        }
                    }
                    
                    @media (max-width: 640px) {
                        .a4-page {
                            padding: 15mm; /* Kleinere marges op mobiel */
                        }
                    }
                    
                    /* Preview content container met overflow detectie */
                    #preview-content {
                        position: relative;
                        width: 100%;
                        height: 100%;
                        font-size: 10pt; /* Standaard print font size */
                        line-height: 1.4; /* Compacter voor meer content */
                        color: #000;
                        overflow: auto; /* Scroll als content te groot is */
                    }
                    
                    /* PRINT OPTIMALISATIES */
                    
                    /* Compact headers voor print */
                    #preview-content h1 {
                        font-size: 18pt;
                        font-weight: bold;
                        margin: 0 0 8pt 0;
                        color: #1a1a1a;
                        line-height: 1.2;
                    }
                    
                    #preview-content h2 {
                        font-size: 14pt;
                        font-weight: bold;
                        margin: 6pt 0 6pt 0;
                        color: #1a1a1a;
                        line-height: 1.2;
                    }
                    
                    #preview-content h3 {
                        font-size: 12pt;
                        font-weight: bold;
                        margin: 4pt 0 4pt 0;
                        color: #1a1a1a;
                        line-height: 1.2;
                    }
                    
                    #preview-content p {
                        margin: 0 0 6pt 0;
                        font-size: 10pt;
                    }
                    
                    /* Compacte tabellen */
                    #preview-content table {
                        width: 100%;
                        border-collapse: collapse;
                        margin: 6pt 0;
                        font-size: 9pt; /* Kleiner voor tabellen */
                        page-break-inside: avoid; /* Probeer tabel niet te splitsen */
                    }
                    
                    #preview-content table th,
                    #preview-content table td {
                        padding: 3pt 6pt; /* Compacter */
                        border: 1px solid #ddd;
                        line-height: 1.3;
                    }
                    
                    #preview-content table th {
                        background-color: #f3f4f6;
                        font-weight: 600;
                    }
                    
                    /* Compacte lists */
                    #preview-content ul,
                    #preview-content ol {
                        margin: 4pt 0;
                        padding-left: 20pt;
                    }
                    
                    #preview-content li {
                        margin: 2pt 0;
                        font-size: 10pt;
                    }
                    
                    /* Grafieken en afbeeldingen */
                    #preview-content canvas,
                    #preview-content img:not(.page-media-wrapper img) {
                        max-width: 100% !important;
                        height: auto !important;
                        page-break-inside: avoid;
                    }
                    
                    /* Grid layouts compacter */
                    #preview-content .grid {
                        gap: 8pt !important;
                    }
                    
                    /* Cards en sections compacter */
                    #preview-content .bg-white,
                    #preview-content .border {
                        padding: 8pt !important;
                        margin: 6pt 0 !important;
                    }
                    
                    /* Verberg web-only elementen */
                    #preview-content .print\\:hidden {
                        display: none !important;
                    }
                    
                    /* Page break hints */
                    .page-break-before {
                        page-break-before: always;
                    }
                    
                    .page-break-after {
                        page-break-after: always;
                    }
                    
                    .page-break-avoid {
                        page-break-inside: avoid;
                    }
                    
                    /* Overflow warning indicator */
                    #preview-content.overflow-warning::after {
                        content: '⚠️ Content is te groot voor A4';
                        position: fixed;
                        bottom: 10px;
                        right: 10px;
                        background: #f59e0b;
                        color: white;
                        padding: 8pt 12pt;
                        border-radius: 6px;
                        font-size: 9pt;
                        font-weight: bold;
                        z-index: 9999;
                        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                    }
                    
                    /* Foto wrapper styling */
                    .page-media-wrapper {
                        max-width: 100%;
                    }
                    
                    .page-media-wrapper img {
                        display: block;
                        max-width: 100%;
                        height: auto;
                    }
                    
                    /* Background positie styling */
                    .page-media-wrapper[data-position="background"] {
                        pointer-events: none;
                        position: absolute !important;
                        top: 0 !important;
                        left: 0 !important;
                        width: 100% !important;
                        height: 100% !important;
                    }
                    
                    /* Float clearing voor left/right posities */
                    #preview-content::after {
                        content: "";
                        display: table;
                        clear: both;
                    }
                    
                    /* Zorg dat content na gefloate afbeeldingen goed weergegeven wordt */
                    .page-media-wrapper + * {
                        display: block;
                    }
                    
                    /* Print marges visualisatie (alleen zichtbaar als show-margins actief is) */
                    .a4-page::before {
                        content: '';
                        position: absolute;
                        top: 20mm;
                        left: 20mm;
                        right: 20mm;
                        bottom: 20mm;
                        border: 1px dashed transparent;
                        pointer-events: none;
                        z-index: 1000;
                        transition: border-color 0.3s ease;
                    }
                    
                    .a4-page.show-margins::before {
                        border-color: rgba(59, 130, 246, 0.4);
                    }
                    
                    .a4-page.show-margins::after {
                        content: 'Print gebied (170mm × 257mm)';
                        position: absolute;
                        top: 22mm;
                        left: 22mm;
                        font-size: 8pt;
                        color: rgba(59, 130, 246, 0.7);
                        font-weight: 600;
                        pointer-events: none;
                        z-index: 1001;
                    }
                    
                    /* Print-vriendelijke fonts */
                    #preview-content {
                        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
                    }
                    
                    /* Shadow voor 3D effect */
                    .a4-page.shadow-2xl {
                        box-shadow: 
                            0 20px 25px -5px rgba(0, 0, 0, 0.1),
                            0 10px 10px -5px rgba(0, 0, 0, 0.04),
                            0 0 0 1px rgba(0, 0, 0, 0.05);
                    }
                </style>
            </div>
        </div>

        {{-- Info Box met Tips --}}
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mt-6">
            <div class="flex gap-3">
                <svg class="w-6 h-6 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <h3 class="font-bold text-blue-900 mb-2">💡 Preview Tips & Shortcuts</h3>
                    <ul class="text-sm text-blue-800 space-y-1">
                        <li>• <strong>A4 Formaat:</strong> Preview toont exacte print afmetingen (210×297mm)</li>
                        <li>• <strong>Navigatie:</strong> Gebruik <kbd class="px-2 py-1 bg-blue-100 rounded text-xs">←</kbd> <kbd class="px-2 py-1 bg-blue-100 rounded text-xs">→</kbd> om tussen pagina's te bladeren</li>
                        <li>• <strong>Zoom:</strong> <kbd class="px-2 py-1 bg-blue-100 rounded text-xs">Ctrl</kbd> + <kbd class="px-2 py-1 bg-blue-100 rounded text-xs">+</kbd> / <kbd class="px-2 py-1 bg-blue-100 rounded text-xs">-</kbd> of gebruik de zoom knoppen (50-200%)</li>
                        <li>• <strong>Reset:</strong> <kbd class="px-2 py-1 bg-blue-100 rounded text-xs">Ctrl</kbd> + <kbd class="px-2 py-1 bg-blue-100 rounded text-xs">0</kbd> om zoom te resetten</li>
                        <li>• <strong>Marges:</strong> Toggle "Toon marges" om het print gebied te zien (170×257mm werkbaar)</li>
                        <li>• <strong>Overflow Check:</strong> Oranje waarschuwing = content te groot, partial moet worden aangepast</li>
                        <li>• <strong>Guide:</strong> Zie <code class="bg-blue-100 px-1">docs/A4_PRINT_GUIDE.md</code> voor optimalisatie tips</li>
                    </ul>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- Preview JavaScript --}}
<script>
    const templateId = {{ $rapportTemplate->id }};
    const totalPages = {{ $rapportTemplate->total_pages }};
    let currentPageNumber = 1;
    let currentZoom = 100; // Zoom percentage
    
    // Pagina's data vanuit controller (al voorbereid)
    const pages = @json($pagesData);
    
    console.log('📊 Template Data:', {
        templateId: templateId,
        totalPages: totalPages,
        pages: pages
    });

    // Laad eerste pagina bij page load
    document.addEventListener('DOMContentLoaded', function() {
        if (pages.length > 0) {
            loadPreview(1);
        } else {
            console.error('❌ Geen pages data beschikbaar!');
        }
        
        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Pijltje links = vorige pagina
            if (e.key === 'ArrowLeft' && currentPageNumber > 1) {
                e.preventDefault();
                previousPage();
            }
            // Pijltje rechts = volgende pagina
            if (e.key === 'ArrowRight' && currentPageNumber < totalPages) {
                e.preventDefault();
                nextPage();
            }
            // + of = voor zoom in
            if ((e.key === '+' || e.key === '=') && e.ctrlKey) {
                e.preventDefault();
                zoomIn();
            }
            // - voor zoom out
            if (e.key === '-' && e.ctrlKey) {
                e.preventDefault();
                zoomOut();
            }
            // 0 voor reset zoom
            if (e.key === '0' && e.ctrlKey) {
                e.preventDefault();
                resetZoom();
            }
        });
    });

    function loadPreview(pageNumber) {
        const loading = document.getElementById('preview-loading');
        const content = document.getElementById('preview-content');
        const currentPageSpan = document.getElementById('current-page');
        const prevBtn = document.getElementById('prev-btn');
        const nextBtn = document.getElementById('next-btn');

        // Toon loading
        loading.classList.remove('hidden');
        content.classList.add('hidden');

        // Update current page display
        currentPageSpan.textContent = pageNumber;
        currentPageNumber = pageNumber;

        // Update button states
        prevBtn.disabled = (pageNumber === 1);
        nextBtn.disabled = (pageNumber === totalPages);

        // Vind de juiste pagina type
        const pageData = pages.find(p => p.number === pageNumber);
        if (!pageData) {
            console.error('Pagina data niet gevonden voor nummer:', pageNumber);
            content.innerHTML = '<div class="text-red-500 text-center p-8">Pagina niet gevonden</div>';
            loading.classList.add('hidden');
            content.classList.remove('hidden');
            return;
        }

        const pageType = pageData.type;
        const pageId = pageData.page_id; // NIEUW: Haal page_id op
        
        if (!pageId) {
            console.error('Geen page_id gevonden voor pagina:', pageNumber, pageData);
            content.innerHTML = '<div class="text-red-500 text-center p-8">Geen page_id gevonden</div>';
            loading.classList.add('hidden');
            content.classList.remove('hidden');
            return;
        }
        
        const previewUrl = `/admin/rapport-templates/${templateId}/preview/${pageType}?page_id=${pageId}`;
        
        console.log('🔍 Preview laden:', {
            pageNumber: pageNumber,
            pageType: pageType,
            pageId: pageId,
            url: previewUrl,
            pageData: pageData
        });
        
        // Laad preview via AJAX
        fetch(previewUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html'
            }
        })
        .then(response => {
            console.log('📡 Response ontvangen:', {
                status: response.status,
                statusText: response.statusText,
                ok: response.ok
            });
            
            if (!response.ok) {
                return response.text().then(text => {
                    console.error('❌ Error response:', text);
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                });
            }
            return response.text();
        })
        .then(html => {
            console.log('✅ Preview HTML ontvangen, lengte:', html.length);
            // Render alleen de partial HTML in de preview
            content.innerHTML = html;
            loading.classList.add('hidden');
            content.classList.remove('hidden');
            
            // Check voor overflow (content groter dan A4)
            setTimeout(() => {
                checkContentOverflow();
            }, 100);
        })
        .catch(error => {
            console.error('❌ Preview load error:', error);
            content.innerHTML = `<div class="p-8 text-center text-red-500">
                <p class="font-bold mb-2">Preview kon niet geladen worden</p>
                <p class="text-sm">${error.message}</p>
                <p class="text-xs mt-2 text-gray-500">Check de browser console voor meer details</p>
            </div>`;
            loading.classList.add('hidden');
            content.classList.remove('hidden');
        });
    }

    function previousPage() {
        if (currentPageNumber > 1) {
            loadPreview(currentPageNumber - 1);
        }
    }

    function nextPage() {
        if (currentPageNumber < totalPages) {
            loadPreview(currentPageNumber + 1);
        }
    }

    function refreshPreview() {
        // Herlaad huidige pagina
        loadPreview(currentPageNumber);
    }
    
    // Zoom functies
    function zoomIn() {
        if (currentZoom < 200) {
            currentZoom += 10;
            applyZoom();
        }
    }
    
    function zoomOut() {
        if (currentZoom > 50) {
            currentZoom -= 10;
            applyZoom();
        }
    }
    
    function resetZoom() {
        currentZoom = 100;
        applyZoom();
    }
    
    function applyZoom() {
        const previewPage = document.getElementById('a4-preview-page');
        const zoomLevel = document.getElementById('zoom-level');
        
        if (previewPage) {
            previewPage.style.transform = `scale(${currentZoom / 100})`;
            previewPage.style.transformOrigin = 'top center';
        }
        
        if (zoomLevel) {
            zoomLevel.textContent = currentZoom + '%';
        }
    }
    
    function toggleMargins() {
        const checkbox = document.getElementById('show-margins');
        const previewPage = document.getElementById('a4-preview-page');
        
        if (checkbox && previewPage) {
            if (checkbox.checked) {
                previewPage.classList.add('show-margins');
            } else {
                previewPage.classList.remove('show-margins');
            }
        }
    }
    
    // Check of content buiten A4 valt
    function checkContentOverflow() {
        const content = document.getElementById('preview-content');
        const page = document.getElementById('a4-preview-page');
        
        if (!content || !page) return;
        
        // Haal daadwerkelijke content hoogte op
        const contentHeight = content.scrollHeight;
        const pageHeight = page.clientHeight;
        const padding = 40; // 20mm top + 20mm bottom in pixels (ongeveer)
        const availableHeight = pageHeight - padding;
        
        console.log('📏 Content check:', {
            contentHeight: contentHeight + 'px',
            availableHeight: availableHeight + 'px',
            overflow: contentHeight > availableHeight
        });
        
        // Toon warning als content te groot is
        if (contentHeight > availableHeight) {
            content.classList.add('overflow-warning');
            console.warn('⚠️ Content past niet op één A4 pagina!');
        } else {
            content.classList.remove('overflow-warning');
        }
    }
</script>

@endsection
