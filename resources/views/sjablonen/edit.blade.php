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
    
    <!-- CKEditor 4.22.1 -->
    <script src="https://cdn.ckeditor.com/4.22.1/standard-all/ckeditor.js"></script>
    
    <style>
        .a4-page {
            width: 210mm;
            min-height: 297mm;
            padding: 20mm;
            margin: 0 auto 20px auto;
            background: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            position: relative;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }
        
        .editor-container {
            height: calc(100vh - 200px);
            overflow-y: auto;
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
        }
        
        .background-option:hover {
            border-color: #3b82f6;
            transform: scale(1.05);
        }
        
        .background-option.selected {
            border-color: #059669;
            box-shadow: 0 0 0 2px #10b981;
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
                        <a href="/sjablonen/{{ $sjabloon->id }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Voorvertoning
                        </a>
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
                    
                    <!-- Achtergronden Sectie -->
                    <div class="mb-6">
                        <h4 class="font-medium text-gray-900 mb-3">Achtergronden</h4>
                        <div class="background-selector">
                            <div class="background-option" onclick="setPageBackground('')" title="Geen achtergrond">
                                <div class="aspect-[210/297] bg-gray-100 flex items-center justify-center text-xs text-gray-500">
                                    Geen
                                </div>
                            </div>
                            @for($i = 1; $i <= 10; $i++)
                                <div class="background-option" onclick="setPageBackground('{{ $i }}.png')" title="Achtergrond {{ $i }}">
                                    <img src="/backgrounds/{{ $i }}.png" alt="Background {{ $i }}" class="w-full aspect-[210/297] object-cover">
                                </div>
                            @endfor
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
                <div class="editor-container bg-gray-100 p-8">
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
                                <div class="a4-page" id="a4-page-{{ $page->id }}" data-background="{{ $page->background_image ?? '' }}">
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
                height: '800px',
                toolbar: [
                    { name: 'document', items: ['Source', '-', 'NewPage', 'Preview', 'Print', '-', 'Templates'] },
                    { name: 'clipboard', items: ['Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo'] },
                    { name: 'editing', items: ['Find', 'Replace', '-', 'SelectAll', '-', 'Scayt'] },
                    '/',
                    { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'CopyFormatting', 'RemoveFormat'] },
                    { name: 'paragraph', items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock', '-', 'BidiLtr', 'BidiRtl'] },
                    { name: 'links', items: ['Link', 'Unlink', 'Anchor'] },
                    '/',
                    { name: 'styles', items: ['Styles', 'Format', 'Font', 'FontSize'] },
                    { name: 'colors', items: ['TextColor', 'BGColor'] },
                    { name: 'tools', items: ['Maximize', 'ShowBlocks'] },
                    { name: 'insert', items: ['Image', 'Table', 'HorizontalRule', 'SpecialChar', 'PageBreak', 'Iframe'] }
                ],
                font_names: 'Arial/Arial, Helvetica, sans-serif;Times New Roman/Times New Roman, Times, serif;Verdana/Verdana, Geneva, sans-serif;Georgia/Georgia, serif;Courier New/Courier New, Courier, monospace;Tahoma/Tahoma, Geneva, sans-serif;Impact/Impact, Charcoal, sans-serif;Comic Sans MS/Comic Sans MS, cursive;Lucida Console/Lucida Console, Monaco, monospace;Trebuchet MS/Trebuchet MS, Helvetica, sans-serif',
                fontSize_sizes: '8/8px;9/9px;10/10px;11/11px;12/12px;14/14px;16/16px;18/18px;20/20px;22/22px;24/24px;26/26px;28/28px;36/36px;48/48px;72/72px',
                contentsCss: ['body { font-family: Arial, sans-serif; font-size: 12pt; line-height: 1.4; margin: 0; padding: 0; background: transparent; }'],
                bodyClass: 'a4-content',
                resize_enabled: false,
                removePlugins: 'elementspath'
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
            const pageElement = document.getElementById('a4-page-' + pageId);
            if (pageElement) {
                if (imageName) {
                    pageElement.style.backgroundImage = `url('/backgrounds/${imageName}')`;
                    pageElement.dataset.background = imageName;
                } else {
                    pageElement.style.backgroundImage = '';
                    pageElement.dataset.background = '';
                }
            }
        }

        function saveCurrentPage() {
            const pageElement = document.getElementById('page-' + currentPageId);
            const isUrlPage = pageElement.querySelector('input[type="url"]') !== null;
            
            if (isUrlPage) {
                saveUrlPage(currentPageId);
            } else {
                const content = editors[currentPageId].getData();
                const backgroundImage = document.getElementById('a4-page-' + currentPageId).dataset.background || '';
                
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
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('Pagina opgeslagen!', 'success');
                    }
                })
                .catch(error => {
                    showNotification('Fout bij opslaan', 'error');
                });
            }
        }

        function saveUrlPage(pageId) {
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
                }
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

        // Auto-save every 30 seconds
        setInterval(saveCurrentPage, 30000);
    </script>
</body>
</html>