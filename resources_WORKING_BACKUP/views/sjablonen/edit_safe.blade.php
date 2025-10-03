{{-- 
Backup van originele edit.blade.php met safety fixes
Dit is een veilige versie die null checks heeft om foreach errors te voorkomen
--}}

@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3>Sjabloon bewerken: {{ $template->naam ?? 'Onbekend' }}</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <!-- Template Editor -->
                            <div id="template-editor">
                                <h4>Template Editor</h4>
                                <div id="editor-container">
                                    <!-- CKEditor gaat hier -->
                                    <textarea id="ckeditor" name="content">
                                        @if(isset($currentPage) && $currentPage)
                                            {!! $currentPage->inhoud !!}
                                        @else
                                            <p>Voeg hier uw content toe...</p>
                                        @endif
                                    </textarea>
                                </div>
                                
                                <div class="mt-3">
                                    <button class="btn btn-primary" onclick="saveContent()">Opslaan</button>
                                    <a href="{{ route('sjablonen.show', $template->id ?? 1) }}" class="btn btn-secondary">Bekijken</a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <!-- Template Keys Sidebar -->
                            <div class="sidebar">
                                <h4>Template Sleutels</h4>
                                
                                @if(isset($templateKeys) && is_array($templateKeys))
                                    @foreach($templateKeys as $category => $keys)
                                        <div class="key-category mb-3">
                                            <h5>{{ ucfirst($category) }}</h5>
                                            <div class="key-list">
                                                @if(is_array($keys) || is_object($keys))
                                                    @foreach($keys as $key)
                                                        @if(is_object($key) && isset($key->placeholder))
                                                            <span class="badge badge-secondary key-item" 
                                                                  onclick="insertKey('{{ $key->placeholder }}')"
                                                                  title="{{ $key->description ?? '' }}">
                                                                {{ $key->display_name ?? $key->placeholder }}
                                                            </span>
                                                        @elseif(is_string($key))
                                                            <span class="badge badge-secondary key-item" 
                                                                  onclick="insertKey('{{ $key }}')">
                                                                {{ $key }}
                                                            </span>
                                                        @endif
                                                    @endforeach
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                                
                                <!-- Background Library -->
                                <h4>Achtergrond Bibliotheek</h4>
                                <div class="background-library">
                                    @if(isset($backgrounds) && is_array($backgrounds))
                                        @foreach($backgrounds as $background)
                                            <div class="background-item" onclick="setBackground('{{ $background }}')">
                                                <img src="{{ $background }}" alt="Background" style="width: 60px; height: 40px; object-fit: cover;">
                                            </div>
                                        @endforeach
                                    @else
                                        <p>Geen achtergronden beschikbaar</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
<script>
    // CKEditor initialisatie
    CKEDITOR.replace('ckeditor');
    
    // Template key functies
    function insertKey(key) {
        CKEDITOR.instances.ckeditor.insertText(key);
    }
    
    // Achtergrond functies
    function setBackground(backgroundUrl) {
        console.log('Setting background:', backgroundUrl);
        // Implement background setting logic
    }
    
    // Content opslaan
    function saveContent() {
        const content = CKEDITOR.instances.ckeditor.getData();
        const templateId = {{ $template->id ?? 1 }};
        const pageId = {{ isset($currentPage) ? $currentPage->id : 1 }};
        
        fetch(`/sjablonen/${templateId}/paginas/${pageId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                inhoud: content,
                achtergrond_url: null // TODO: implement background selection
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Content opgeslagen!');
            } else {
                alert('Fout bij opslaan');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Fout bij opslaan');
        });
    }
</script>

<style>
    .key-item {
        cursor: pointer;
        margin: 2px;
    }
    
    .key-item:hover {
        background-color: #007bff;
    }
    
    .background-item {
        cursor: pointer;
        margin: 5px;
        border: 2px solid transparent;
    }
    
    .background-item:hover {
        border-color: #007bff;
    }
    
    .sidebar {
        max-height: 80vh;
        overflow-y: auto;
    }
</style>
@endsection