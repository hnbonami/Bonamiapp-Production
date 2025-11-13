<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $sjabloon->naam }} - Sjabloon Editor</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- CKEditor 4.22.1 Full Version -->
    <script src="https://cdn.ckeditor.com/4.22.1/full/ckeditor.js"></script>
    
    <!-- Sjablonen Editor Button Enhancements -->
    <link rel="stylesheet" href="/css/sjablonen-editor-buttons.css">
    <script src="/js/sjablonen-editor-buttons.js"></script>
    
    <style>
        .a4-page {
            width: 210mm;
            min-height: 297mm;
            padding: 20mm;
            margin: 0 auto 20px auto;
            background: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            position: relative;
        }
        
        /* Background overlay system */
        .background-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            opacity: 0.4;
            pointer-events: none;
            z-index: 5;
            transition: opacity 0.3s ease;
        }
        
        .background-overlay.hidden {
            opacity: 0;
        }
        
        .overlay-controls {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 10;
            display: flex;
            gap: 5px;
        }
        
        .overlay-btn {
            background: rgba(0,0,0,0.7);
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            cursor: pointer;
            transition: background 0.2s;
        }
        
        .overlay-btn:hover {
            background: rgba(0,0,0,0.9);
        }
        
        .overlay-btn.active {
            background: #3b82f6;
        }
        
        /* CKEditor styling - perfect A4 schaal matching */
        .a4-page .cke {
            position: absolute;
            top: 0;
            left: 0;
            width: 210mm !important;
            height: 297mm !important;
            z-index: 2;
            border: none !important;
        }
        
        .a4-page .cke_top {
            position: absolute;
            top: -170px;
            left: 0;
            right: 0;
            z-index: 1000;
            background: white;
            border: 1px solid #ccc;
            border-radius: 4px 4px 0 0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .a4-page .cke_contents {
            position: absolute !important;
            top: 0 !important;
            left: 0 !important;
            width: 210mm !important;
            height: 297mm !important;
            background: transparent !important;
            border: none !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        
        .a4-page .cke_bottom {
            display: none !important;
        }
        
        .a4-page .cke_contents iframe {
            width: 210mm !important;
            height: 297mm !important;
            background: transparent !important;
            border: none !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        
        .a4-page .cke_wysiwyg_frame {
            background: transparent !important;
            width: 210mm !important;
            height: 297mm !important;
        }
        
        /* Perfect A4 dimensies voor editor content */
        .a4-page .cke_editable {
            background: transparent !important;
            background-color: transparent !important;
            padding: 20mm !important;
            margin: 0 !important;
            width: 210mm !important;
            height: 297mm !important;
            min-height: 297mm !important;
            max-height: 297mm !important;
            box-sizing: border-box !important;
            overflow: hidden !important;
        }
        
        .a4-page .cke_contents iframe html,
        .a4-page .cke_contents iframe body {
            background: transparent !important;
            background-color: transparent !important;
            margin: 0 !important;
            padding: 0 !important;
            width: 210mm !important;
            height: 297mm !important;
            box-sizing: border-box !important;
        }
        
        .a4-page .cke_wysiwyg_frame,
        .a4-page .cke_wysiwyg_frame html,
        .a4-page .cke_wysiwyg_frame body {
            background: transparent !important;
            background-color: transparent !important;
            width: 210mm !important;
            height: 297mm !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        
        .editor-container {
            height: calc(100vh - 200px);
            overflow-y: auto;
            padding-top: 80px; /* Extra ruimte voor CKEditor toolbar */
        }
        
        .key-library {
            height: calc(100vh - 200px);
            overflow-y: auto;
        }
        
        .template-key {
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .template-key:hover {
            background-color: #f3f4f6;
            transform: translateX(4px);
        }
        
        .page-tab {
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .page-tab.active {
            background-color: #3b82f6;
            color: white;
        }
        
        .background-selector {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 10px;
            max-height: 300px;
            overflow-y: auto;
        }
        
        .background-option {
            cursor: pointer;
            border: 2px solid transparent;
            border-radius: 8px;
            overflow: hidden;
            transition: all 0.2s;
            position: relative;
        }
        
        .background-option:hover {
            border-color: #3b82f6;
            transform: scale(1.05);
        }
        
        .background-option.selected {
            border-color: #059669;
            box-shadow: 0 0 0 2px #10b981;
        }
        
        /* Delete button styling - altijd zichtbaar */
        .background-option .delete-btn {
            position: absolute;
            top: 4px;
            right: 4px;
            background-color: #ef4444;
            color: white;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            font-weight: bold;
            border: 2px solid white;
            cursor: pointer;
            transition: all 0.2s;
            z-index: 10;
            box-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }
        
        .background-option .delete-btn:hover {
            background-color: #dc2626;
            transform: scale(1.1);
        }
        
        .cke_contents {
            background: transparent !important;
        }
    </style>
</head>

<body class="font-sans antialiased bg-gray-100">
    <div class="min-h-screen">
        <!-- Header -->
        <div class="bg-white shadow">
            <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-4">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ $sjabloon->naam }}</h1>
                        <p class="text-sm text-gray-600">{{ ucfirst($sjabloon->categorie) }} Sjabloon</p>
                    </div>
                    <div class="flex gap-2">
                        <button onclick="saveCurrentPage()" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                            Opslaan
                        </button>
                        <button onclick="saveAndPreview()" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Voorvertoning
                        </button>
                        <a href="{{ route('sjablonen.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            Terug
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex h-screen">
            <!-- Left Sidebar - Key Library -->
            <div class="w-80 bg-white shadow-lg border-r">
                <div class="p-4 border-b bg-gray-50">
                    <h3 class="font-semibold text-lg">Sleutel Bibliotheek</h3>
                    <p class="text-sm text-gray-600">Klik om toe te voegen</p>
                </div>
                
                <div class="key-library p-4">
                    @foreach($templateKeys as $category => $keys)
                        <div class="mb-6">
                            <h4 class="font-medium text-gray-900 mb-3 capitalize">{{ $category }}</h4>
                            <div class="space-y-1">
                                @foreach($keys as $key)
                                    <div class="template-key p-3 rounded-lg border border-gray-200" 
                                         onclick="insertKey('{{ $key->placeholder }}')"
                                         title="{{ $key->display_name }}">
                                        <div class="flex items-center">
                                            <span class="text-blue-600 font-mono text-sm">🔑</span>
                                            <div class="ml-2">
                                                <div class="text-sm font-medium">{{ $key->display_name }}</div>
                                                <div class="text-xs text-gray-500">{{ $key->placeholder }}</div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                    
                    <!-- 🆕 RAPPORT VARIABELEN - NIEUW TOEGEVOEGD -->
                    <div class="mb-6">
                        <h4 class="font-medium text-gray-900 mb-3">📄 Rapport Instellingen</h4>
                        <div class="space-y-1">
                            <div class="template-key p-3 rounded-lg border border-blue-200 bg-blue-50" onclick="insertKey('@{{ rapport.header }}')" title="Rapport header tekst">
                                <div class="flex items-center">
                                    <span class="text-blue-600 font-mono text-sm">📄</span>
                                    <div class="ml-2">
                                        <div class="text-sm font-medium">Rapport Header</div>
                                        <div class="text-xs text-gray-500">@{{ rapport.header }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="template-key p-3 rounded-lg border border-blue-200 bg-blue-50" onclick="insertKey('@{{ rapport.footer }}')" title="Rapport footer tekst">
                                <div class="flex items-center">
                                    <span class="text-blue-600 font-mono text-sm">📄</span>
                                    <div class="ml-2">
                                        <div class="text-sm font-medium">Rapport Footer</div>
                                        <div class="text-xs text-gray-500">@{{ rapport.footer }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="template-key p-3 rounded-lg border border-blue-200 bg-blue-50" onclick="insertKey('@{{ rapport.logo }}')" title="Organisatie logo (IMG tag)">
                                <div class="flex items-center">
                                    <span class="text-blue-600 font-mono text-sm">🖼️</span>
                                    <div class="ml-2">
                                        <div class="text-sm font-medium">Logo</div>
                                        <div class="text-xs text-gray-500">@{{ rapport.logo }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="template-key p-3 rounded-lg border border-blue-200 bg-blue-50" onclick="insertKey('@{{ rapport.voorblad_foto }}')" title="Voorblad foto (IMG tag)">
                                <div class="flex items-center">
                                    <span class="text-blue-600 font-mono text-sm">🖼️</span>
                                    <div class="ml-2">
                                        <div class="text-sm font-medium">Voorblad Foto</div>
                                        <div class="text-xs text-gray-500">@{{ rapport.voorblad_foto }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="template-key p-3 rounded-lg border border-blue-200 bg-blue-50" onclick="insertKey('@{{ rapport.primaire_kleur }}')" title="Primaire kleur (HEX)">
                                <div class="flex items-center">
                                    <span class="text-blue-600 font-mono text-sm">🎨</span>
                                    <div class="ml-2">
                                        <div class="text-sm font-medium">Primaire Kleur</div>
                                        <div class="text-xs text-gray-500">@{{ rapport.primaire_kleur }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="template-key p-3 rounded-lg border border-blue-200 bg-blue-50" onclick="insertKey('@{{ rapport.secundaire_kleur }}')" title="Secundaire kleur (HEX)">
                                <div class="flex items-center">
                                    <span class="text-blue-600 font-mono text-sm">🎨</span>
                                    <div class="ml-2">
                                        <div class="text-sm font-medium">Secundaire Kleur</div>
                                        <div class="text-xs text-gray-500">@{{ rapport.secundaire_kleur }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="template-key p-3 rounded-lg border border-blue-200 bg-blue-50" onclick="insertKey('@{{ rapport.lettertype }}')" title="Lettertype">
                                <div class="flex items-center">
                                    <span class="text-blue-600 font-mono text-sm">✍️</span>
                                    <div class="ml-2">
                                        <div class="text-sm font-medium">Lettertype</div>
                                        <div class="text-xs text-gray-500">@{{ rapport.lettertype }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="template-key p-3 rounded-lg border border-green-200 bg-green-50" onclick="insertKey('@{{ rapport.inleidende_tekst }}')" title="Inleidende tekst">
                                <div class="flex items-center">
                                    <span class="text-green-600 font-mono text-sm">📝</span>
                                    <div class="ml-2">
                                        <div class="text-sm font-medium">Inleidende Tekst</div>
                                        <div class="text-xs text-gray-500">@{{ rapport.inleidende_tekst }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="template-key p-3 rounded-lg border border-green-200 bg-green-50" onclick="insertKey('@{{ rapport.laatste_blad_tekst }}')" title="Laatste blad tekst">
                                <div class="flex items-center">
                                    <span class="text-green-600 font-mono text-sm">📝</span>
                                    <div class="ml-2">
                                        <div class="text-sm font-medium">Laatste Blad Tekst</div>
                                        <div class="text-xs text-gray-500">@{{ rapport.laatste_blad_tekst }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="template-key p-3 rounded-lg border border-green-200 bg-green-50" onclick="insertKey('@{{ rapport.disclaimer }}')" title="Disclaimer tekst">
                                <div class="flex items-center">
                                    <span class="text-green-600 font-mono text-sm">⚖️</span>
                                    <div class="ml-2">
                                        <div class="text-sm font-medium">Disclaimer</div>
                                        <div class="text-xs text-gray-500">@{{ rapport.disclaimer }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="template-key p-3 rounded-lg border border-purple-200 bg-purple-50" onclick="insertKey('@{{ rapport.contactgegevens }}')" title="Contactgegevens (HTML)">
                                <div class="flex items-center">
                                    <span class="text-purple-600 font-mono text-sm">📞</span>
                                    <div class="ml-2">
                                        <div class="text-sm font-medium">Contactgegevens</div>
                                        <div class="text-xs text-gray-500">@{{ rapport.contactgegevens }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="template-key p-3 rounded-lg border border-purple-200 bg-purple-50" onclick="insertKey('@{{ rapport.contact_adres }}')" title="Contact adres">
                                <div class="flex items-center">
                                    <span class="text-purple-600 font-mono text-sm">🏠</span>
                                    <div class="ml-2">
                                        <div class="text-sm font-medium">Contact Adres</div>
                                        <div class="text-xs text-gray-500">@{{ rapport.contact_adres }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="template-key p-3 rounded-lg border border-purple-200 bg-purple-50" onclick="insertKey('@{{ rapport.contact_telefoon }}')" title="Contact telefoon">
                                <div class="flex items-center">
                                    <span class="text-purple-600 font-mono text-sm">📱</span>
                                    <div class="ml-2">
                                        <div class="text-sm font-medium">Contact Telefoon</div>
                                        <div class="text-xs text-gray-500">@{{ rapport.contact_telefoon }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="template-key p-3 rounded-lg border border-purple-200 bg-purple-50" onclick="insertKey('@{{ rapport.contact_email }}')" title="Contact email">
                                <div class="flex items-center">
                                    <span class="text-purple-600 font-mono text-sm">📧</span>
                                    <div class="ml-2">
                                        <div class="text-sm font-medium">Contact Email</div>
                                        <div class="text-xs text-gray-500">@{{ rapport.contact_email }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="template-key p-3 rounded-lg border border-purple-200 bg-purple-50" onclick="insertKey('@{{ rapport.contact_website }}')" title="Contact website">
                                <div class="flex items-center">
                                    <span class="text-purple-600 font-mono text-sm">🌐</span>
                                    <div class="ml-2">
                                        <div class="text-sm font-medium">Contact Website</div>
                                        <div class="text-xs text-gray-500">@{{ rapport.contact_website }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="template-key p-3 rounded-lg border border-yellow-200 bg-yellow-50" onclick="insertKey('@{{ rapport.qr_code }}')" title="QR Code (IMG tag)">
                                <div class="flex items-center">
                                    <span class="text-yellow-600 font-mono text-sm">📱</span>
                                    <div class="ml-2">
                                        <div class="text-sm font-medium">QR Code</div>
                                        <div class="text-xs text-gray-500">@{{ rapport.qr_code }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="template-key p-3 rounded-lg border border-yellow-200 bg-yellow-50" onclick="insertKey('@{{ rapport.paginanummer }}')" title="Paginanummer">
                                <div class="flex items-center">
                                    <span class="text-yellow-600 font-mono text-sm">🔢</span>
                                    <div class="ml-2">
                                        <div class="text-sm font-medium">Paginanummer</div>
                                        <div class="text-xs text-gray-500">@{{ rapport.paginanummer }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Achtergronden Sectie -->
                    <div class="mb-6">
                        <h4 class="font-medium text-gray-900 mb-3 flex items-center justify-between">
                            <span>Achtergronden</span>
                            <button onclick="uploadNewBackground()" class="text-sm bg-blue-600 text-white px-3 py-1.5 rounded-md hover:bg-blue-700 font-medium shadow-sm border border-blue-700 transition-colors">
                                📤 Upload
                            </button>
                        </h4>
                        <div class="background-selector">
                            <div class="background-option" onclick="setPageBackground('')" title="Geen achtergrond">
                                <div class="aspect-[210/297] bg-gray-100 flex items-center justify-center text-xs text-gray-500">
                                    Geen
                                </div>
                            </div>
                            @php
                                // Scan de juiste backgrounds directory op basis van environment
                                $backgrounds = [];
                                
                                if (app()->environment('production')) {
                                    // PRODUCTIE: Scan httpd.www/uploads/backgrounds
                                    $backgroundsPath = base_path('../httpd.www/uploads/backgrounds');
                                } else {
                                    // LOKAAL: Scan public/backgrounds
                                    $backgroundsPath = public_path('backgrounds');
                                }
                                
                                if (file_exists($backgroundsPath)) {
                                    $files = scandir($backgroundsPath);
                                    foreach ($files as $file) {
                                        if (in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), ['png', 'jpg', 'jpeg', 'gif'])) {
                                            $backgrounds[] = $file;
                                        }
                                    }
                                    sort($backgrounds, SORT_NATURAL);
                                }
                            @endphp
                            
                            @foreach($backgrounds as $background)
                                @php
                                    $backgroundUrl = app()->environment('production') 
                                        ? asset('uploads/backgrounds/' . $background)
                                        : asset('backgrounds/' . $background);
                                @endphp
                                <div class="background-option relative" onclick="setPageBackground('{{ $background }}')" title="Achtergrond {{ $background }}">
                                    <img src="{{ $backgroundUrl }}" alt="Background {{ $background }}" class="w-full aspect-[210/297] object-cover">
                                    <button onclick="deleteBackground('{{ $background }}', event)" class="delete-btn" title="Verwijderen">×</button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Editor Area -->
            <div class="flex-1 flex flex-col">
                <!-- Page Tabs -->
                <div class="bg-white border-b p-4">
                    <div class="flex items-center gap-2 overflow-x-auto">
                        @foreach($sjabloon->pages as $page)
                            <div class="page-tab px-4 py-2 rounded-lg border {{ $loop->first ? 'active' : 'border-gray-300' }}"
                                 data-page-id="{{ $page->id }}"
                                 onclick="switchToPage({{ $page->id }}, {{ $page->page_number }})">
                                <span>Pagina {{ $page->page_number }}</span>
                                @if($page->is_url_page)
                                    <span class="ml-1 text-xs bg-green-100 text-green-800 px-1 rounded">URL</span>
                                @endif
                                @if($sjabloon->pages->count() > 1)
                                    <button onclick="deletePage({{ $page->id }}, event)" class="ml-2 text-red-500 hover:text-red-700 text-xs">×</button>
                                @endif
                            </div>
                        @endforeach
                        
                        <button onclick="addNewPage()" class="px-3 py-2 bg-blue-500 text-white rounded-lg text-sm hover:bg-blue-600">
                            + Pagina
                        </button>
                        
                        <button onclick="addUrlPage()" class="px-3 py-2 bg-green-500 text-white rounded-lg text-sm hover:bg-green-600">
                            + URL Pagina
                        </button>
                    </div>
                </div>

                <!-- Editor Container -->
                <div class="editor-container bg-gray-100">
                    @foreach($sjabloon->pages as $page)
                        <div id="page-{{ $page->id }}" class="page-editor {{ !$loop->first ? 'hidden' : '' }}">
                            @if($page->is_url_page)
                                <div class="max-w-2xl mx-auto bg-white p-6 rounded-lg shadow">
                                    <h3 class="text-lg font-semibold mb-4">URL Pagina {{ $page->page_number }}</h3>
                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">URL</label>
                                        <input type="url" 
                                               id="url-{{ $page->id }}" 
                                               value="{{ $page->url }}"
                                               class="w-full border-gray-300 rounded-md"
                                               placeholder="https://example.com/document.pdf">
                                    </div>
                                    <button onclick="saveUrlPage({{ $page->id }})" class="bg-blue-500 text-white px-4 py-2 rounded">
                                        URL Opslaan
                                    </button>
                                </div>
                            @else
                                <div class="a4-page" id="a4-page-{{ $page->id }}" data-background="{{ $page->background_image ?? '' }}" style="margin-top: 100px;">
                                    <!-- Background overlay -->
                                    <div class="background-overlay" id="overlay-{{ $page->id }}"></div>
                                    
                                    <!-- Overlay controls -->
                                    <div class="overlay-controls">
                                        <button class="overlay-btn active" id="toggle-overlay-{{ $page->id }}" onclick="toggleOverlay({{ $page->id }})">👁️ Overlay</button>
                                        <input type="range" 
                                               id="opacity-{{ $page->id }}" 
                                               min="0" 
                                               max="100" 
                                               value="40" 
                                               onchange="changeOpacity({{ $page->id }}, this.value)"
                                               style="width: 80px;">
                                    </div>
                                    
                                    <textarea id="editor-{{ $page->id }}" name="content">{{ $page->content }}</textarea>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentPageId = {{ $sjabloon->pages->first()->id }};
        let editors = {};
        
        // Initialize CKEditor for each page
        @foreach($sjabloon->pages->where('is_url_page', false) as $page)
            CKEDITOR.replace('editor-{{ $page->id }}', {
                width: '210mm',
                height: '297mm', 
                toolbar: 'Full',
                resize_enabled: false,
                removePlugins: 'elementspath,resize',
                allowedContent: true,
                enterMode: CKEDITOR.ENTER_P,
                shiftEnterMode: CKEDITOR.ENTER_BR,
                contentsCss: [
                    'body { font-family: Arial, sans-serif; font-size: 12pt; line-height: 1.4; margin: 0; padding: 20mm; background: transparent; box-sizing: border-box; width: 210mm; height: 297mm; min-height: 297mm; max-height: 297mm; }'
                ],
                bodyClass: 'a4-content',
                on: {
                    instanceReady: function(evt) {
                        var editor = evt.editor;
                        var contents = editor.ui.space('contents');
                        var editable = editor.editable();
                        
                        // Set exact dimensions for iframe and editable area
                        if (contents) {
                            contents.setStyle('width', '210mm');
                            contents.setStyle('height', '297mm');
                            contents.setStyle('margin', '0');
                            contents.setStyle('padding', '0');
                            contents.setStyle('border', 'none');
                        }
                        
                        // Ensure iframe content matches A4 exactly
                        if (editable) {
                            editable.setStyle('width', '210mm');
                            editable.setStyle('height', '297mm');
                            editable.setStyle('min-height', '297mm');
                            editable.setStyle('max-height', '297mm');
                            editable.setStyle('box-sizing', 'border-box');
                            editable.setStyle('padding', '20mm');
                            editable.setStyle('margin', '0');
                            editable.setStyle('background', 'transparent');
                        }
                    }
                }
            });
            
            editors[{{ $page->id }}] = CKEDITOR.instances['editor-{{ $page->id }}'];
        @endforeach

        // Set initial background
        @foreach($sjabloon->pages->where('is_url_page', false) as $page)
            @if($page->background_image)
                setPageBackgroundImage({{ $page->id }}, '{{ $page->background_image }}');
            @endif
        @endforeach

        function insertKey(placeholder) {
            if (editors[currentPageId]) {
                editors[currentPageId].insertText(placeholder);
            }
        }

        function switchToPage(pageId, pageNumber) {
            // Hide all pages
            document.querySelectorAll('.page-editor').forEach(el => el.classList.add('hidden'));
            document.querySelectorAll('.page-tab').forEach(el => el.classList.remove('active'));
            
            // Show selected page
            document.getElementById('page-' + pageId).classList.remove('hidden');
            document.querySelector('[data-page-id="' + pageId + '"]').classList.add('active');
            
            currentPageId = pageId;
        }

        function setPageBackground(imageName) {
            setPageBackgroundImage(currentPageId, imageName);
            
            // Update visual selection
            document.querySelectorAll('.background-option').forEach(el => el.classList.remove('selected'));
            event.target.closest('.background-option').classList.add('selected');
        }

        function setPageBackgroundImage(pageId, imageName) {
            const overlayElement = document.getElementById('overlay-' + pageId);
            if (overlayElement) {
                if (imageName) {
                    // Gebruik correcte path op basis van environment
                    const isProduction = {{ app()->environment('production') ? 'true' : 'false' }};
                    const backgroundPath = isProduction 
                        ? '/uploads/backgrounds/' + imageName 
                        : '/backgrounds/' + imageName;
                    
                    overlayElement.style.backgroundImage = `url('${backgroundPath}')`;
                    document.getElementById('a4-page-' + pageId).dataset.background = imageName;
                } else {
                    overlayElement.style.backgroundImage = '';
                    document.getElementById('a4-page-' + pageId).dataset.background = '';
                }
            }
        }

        function toggleOverlay(pageId) {
            const overlay = document.getElementById('overlay-' + pageId);
            const button = document.getElementById('toggle-overlay-' + pageId);
            
            if (overlay.classList.contains('hidden')) {
                overlay.classList.remove('hidden');
                button.classList.add('active');
                button.textContent = '👁️ Overlay';
            } else {
                overlay.classList.add('hidden');
                button.classList.remove('active');
                button.textContent = '🚫 Hidden';
            }
        }

        function changeOpacity(pageId, value) {
            const overlay = document.getElementById('overlay-' + pageId);
            overlay.style.opacity = value / 100;
        }

        function saveAndPreview() {
            console.log('🔵 saveAndPreview() started');
            
            // Show loading indicator
            const previewBtn = document.querySelector('button[onclick="saveAndPreview()"]');
            const originalText = previewBtn.textContent;
            previewBtn.textContent = 'Opslaan & Laden...';
            previewBtn.disabled = true;
            
            console.log('🔵 About to save current page:', currentPageId);
            
            // First save current page
            saveCurrentPage()
                .then(() => {
                    console.log('✅ Current page saved, now saving all pages');
                    // Then save all other pages to ensure everything is up to date
                    return saveAllPages();
                })
                .then(() => {
                    console.log('✅ All pages saved, redirecting to preview');
                    showNotification('Alle wijzigingen opgeslagen! Naar voorvertoning...', 'success');
                    // Small delay to show success message
                    setTimeout(() => {
                        window.location.href = '/sjablonen/{{ $sjabloon->id }}/preview';
                    }, 1000);
                })
                .catch(error => {
                    console.error('❌ Error saving before preview:', error);
                    showNotification('Fout bij opslaan voor voorvertoning', 'error');
                    // Restore button
                    previewBtn.textContent = originalText;
                    previewBtn.disabled = false;
                });
        }

        function saveAllPages() {
            const promises = [];
            
            // Save all editor pages
            @foreach($sjabloon->pages->where('is_url_page', false) as $page)
                if (editors[{{ $page->id }}]) {
                    const content = editors[{{ $page->id }}].getData();
                    const backgroundImage = document.getElementById('a4-page-{{ $page->id }}').dataset.background || '';
                    
                    const promise = fetch(`/sjablonen/{{ $sjabloon->id }}/pages/{{ $page->id }}/update`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            content: content,
                            background_image: backgroundImage
                        })
                    });
                    promises.push(promise);
                }
            @endforeach
            
            // Save all URL pages
            @foreach($sjabloon->pages->where('is_url_page', true) as $page)
                const urlInput = document.getElementById('url-{{ $page->id }}');
                if (urlInput) {
                    const promise = fetch(`/sjablonen/{{ $sjabloon->id }}/pages/{{ $page->id }}/update`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            url: urlInput.value,
                            is_url_page: true
                        })
                    });
                    promises.push(promise);
                }
            @endforeach
            
            return Promise.all(promises);
        }

        function saveCurrentPage() {
            return new Promise((resolve, reject) => {
                console.log('💾 Saving page:', currentPageId);
                
                const pageElement = document.getElementById('page-' + currentPageId);
                const isUrlPage = pageElement.querySelector('input[type="url"]') !== null;
                
                if (isUrlPage) {
                    console.log('📄 Saving URL page');
                    saveUrlPagePromise(currentPageId).then(resolve).catch(reject);
                } else {
                    console.log('📝 Saving editor page');
                    
                    if (!editors[currentPageId]) {
                        console.error('❌ No editor found for page:', currentPageId);
                        reject(new Error('No editor found'));
                        return;
                    }
                    
                    const content = editors[currentPageId].getData();
                    const backgroundImage = document.getElementById('a4-page-' + currentPageId).dataset.background || '';
                    
                    console.log('📝 Content length:', content.length);
                    console.log('🎨 Background:', backgroundImage);
                    
                    fetch(`/sjablonen/{{ $sjabloon->id }}/pages/${currentPageId}/update`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            content: content,
                            background_image: backgroundImage
                        })
                    })
                    .then(response => {
                        console.log('📡 Response status:', response.status);
                        return response.json();
                    })
                    .then(data => {
                        console.log('📡 Response data:', data);
                        if (data.success) {
                            showNotification('Pagina opgeslagen!', 'success');
                            resolve(data);
                        } else {
                            reject(new Error('Save failed: ' + (data.message || 'Unknown error')));
                        }
                    })
                    .catch(error => {
                        console.error('❌ Save error:', error);
                        showNotification('Fout bij opslaan', 'error');
                        reject(error);
                    });
                }
            });
        }

        function saveUrlPagePromise(pageId) {
            return new Promise((resolve, reject) => {
                const url = document.getElementById('url-' + pageId).value;
                
                fetch(`/sjablonen/{{ $sjabloon->id }}/pages/${pageId}/update`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        url: url,
                        is_url_page: true
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('URL pagina opgeslagen!', 'success');
                        resolve(data);
                    } else {
                        reject(new Error('Save failed'));
                    }
                })
                .catch(reject);
            });
        }

        function saveUrlPage(pageId) {
            saveUrlPagePromise(pageId).then(() => {
                // Success handled in promise
            }).catch(error => {
                console.error('Error saving URL page:', error);
            });
        }

        function addNewPage() {
            fetch(`/sjablonen/{{ $sjabloon->id }}/pages`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    is_url_page: false
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            });
        }

        function addUrlPage() {
            const url = prompt('Voer URL in:');
            if (url) {
                fetch(`/sjablonen/{{ $sjabloon->id }}/pages`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        is_url_page: true,
                        url: url
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    }
                });
            }
        }

        function deletePage(pageId, event) {
            event.stopPropagation();
            
            if (confirm('Weet je zeker dat je deze pagina wilt verwijderen?')) {
                fetch(`/sjablonen/{{ $sjabloon->id }}/pages/${pageId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    }
                });
            }
        }

        function showNotification(message, type) {
            // Simple notification system
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 p-4 rounded-lg text-white z-50 ${type === 'success' ? 'bg-green-500' : 'bg-red-500'}`;
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }

        function uploadNewBackground() {
            const input = document.createElement('input');
            input.type = 'file';
            input.accept = 'image/*';
            
            input.onchange = async (e) => {
                const file = e.target.files[0];
                if (!file) {
                    console.log('❌ Geen bestand geselecteerd');
                    return;
                }
                
                console.log('📤 Upload gestart:', file.name, 'Size:', file.size);
                
                // Toon loading notificatie
                showNotification('Uploaden: ' + file.name + '...', 'success');
                
                const formData = new FormData();
                formData.append('background', file);
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                
                try {
                    console.log('🚀 Verzenden naar /sjablonen/backgrounds/upload');
                    
                    const response = await fetch('/sjablonen/backgrounds/upload', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'Accept': 'application/json',
                        }
                    });
                    
                    console.log('📡 Response status:', response.status);
                    
                    if (!response.ok) {
                        throw new Error('Upload mislukt met status: ' + response.status);
                    }
                    
                    const data = await response.json();
                    console.log('📡 Response data:', data);
                    
                    if (data.success) {
                        showNotification('✅ Achtergrond geüpload: ' + data.filename, 'success');
                        console.log('✅ Upload succesvol, reloading...');
                        // Wacht 1 seconde zodat gebruiker de melding ziet
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    } else {
                        showNotification('❌ Fout: ' + (data.message || 'Onbekende fout'), 'error');
                        console.error('❌ Upload failed:', data);
                    }
                } catch (error) {
                    console.error('❌ Upload error:', error);
                    showNotification('❌ Netwerk fout: ' + error.message, 'error');
                }
            };
            
            input.click();
        }

        function deleteBackground(filename, event) {
            event.stopPropagation();
            
            if (!confirm(`Weet je zeker dat je "${filename}" wilt verwijderen?`)) {
                return;
            }
            
            fetch('/sjablonen/backgrounds/' + encodeURIComponent(filename), {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Achtergrond verwijderd!', 'success');
                    location.reload();
                } else {
                    showNotification('Fout bij verwijderen', 'error');
                }
            })
            .catch(error => {
                showNotification('Fout bij verwijderen', 'error');
                console.error(error);
            });
        }

        // Auto-save every 30 seconds
        setInterval(saveCurrentPage, 30000);
    </script>
</body>
</html>