@extends('layouts.app')

@section('content')
<link href="{{ asset('css/dashboard-content.css') }}" rel="stylesheet">

<div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-3xl font-extrabold text-gray-900">Dashboard Content Bewerken</h1>
        <p class="text-gray-600 mt-2">Bewerk: {{ $dashboardContent->title }}</p>
    </div>

    <form method="POST" action="{{ route('dashboard-content.update', $dashboardContent) }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PATCH')

        <!-- Content Type Selector -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-lg font-semibold mb-4">1. Content Type</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach([
                    'note' => ['📝', 'Notitie', 'Eenvoudige tekstnotitie'],
                    'task' => ['✅', 'Takenlijst', 'Lijst met taken/checklist'],
                    'announcement' => ['📢', 'Mededeling', 'Belangrijke aankondiging'],
                    'image' => ['🖼️', 'Afbeelding/Poster', 'Visual content met afbeelding'],
                    'mixed' => ['📊', 'Gemengd', 'Tekst en afbeelding combinatie']
                ] as $type => $info)
                    <label class="type-option cursor-pointer">
                        <input type="radio" name="type" value="{{ $type }}" class="sr-only type-input" 
                               @if(old('type', $dashboardContent->type) === $type) checked @endif>
                        <div class="type-card border-2 rounded-lg p-4 text-center transition-all hover:shadow-md">
                            <div class="text-3xl mb-2">{{ $info[0] }}</div>
                            <div class="font-semibold">{{ $info[1] }}</div>
                            <div class="text-sm text-gray-600 mt-1">{{ $info[2] }}</div>
                        </div>
                    </label>
                @endforeach
            </div>
        </div>

        <!-- Basic Information -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-lg font-semibold mb-4">2. Basis Informatie</h2>
            
            <div class="space-y-4">
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Titel *</label>
                    <input type="text" name="title" id="title" value="{{ old('title', $dashboardContent->title) }}" 
                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                           required>
                </div>

                <div>
                    <label for="content" class="block text-sm font-medium text-gray-700 mb-2">Inhoud *</label>
                    <textarea name="content" id="content" rows="6" 
                              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                              required>{{ old('content', $dashboardContent->content) }}</textarea>
                </div>

                <!-- Current Image Display -->
                @if($dashboardContent->image_path)
                    <div class="current-image">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Huidige Afbeelding</label>
                        <div class="flex items-center space-x-4">
                            <img src="{{ $dashboardContent->getImageUrl() }}" alt="Huidige afbeelding" class="w-32 h-24 object-cover rounded-lg">
                            <div>
                                <p class="text-sm text-gray-600">Huidige afbeelding</p>
                                <label class="text-sm text-blue-600 cursor-pointer hover:underline">
                                    <input type="checkbox" name="remove_image" value="1" class="mr-1">
                                    Afbeelding verwijderen
                                </label>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Image Upload -->
                <div id="image-section">
                    <label for="image" class="block text-sm font-medium text-gray-700 mb-2">
                        {{ $dashboardContent->image_path ? 'Nieuwe Afbeelding (optioneel)' : 'Afbeelding' }}
                    </label>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-gray-400 transition-colors">
                        <input type="file" name="image" id="image" accept="image/*" class="hidden">
                        <div id="image-upload-area" class="cursor-pointer" onclick="document.getElementById('image').click()">
                            <div class="text-4xl text-gray-400 mb-2">📁</div>
                            <p class="text-gray-600">Klik om een {{ $dashboardContent->image_path ? 'nieuwe ' : '' }}afbeelding te selecteren</p>
                            <p class="text-sm text-gray-500 mt-1">JPG, PNG, GIF tot 5MB</p>
                        </div>
                        <div id="image-preview" class="hidden">
                            <img id="preview-img" class="max-w-full h-48 object-cover mx-auto rounded-lg">
                            <button type="button" onclick="removeNewImage()" class="mt-2 text-red-600 text-sm">Verwijderen</button>
                        </div>
                    </div>
                </div>

                <!-- URL Field -->
                <div class="mb-3">
                    <label for="link_url" class="form-label">Link URL (optioneel)</label>
                    <input type="url" class="form-control" id="link_url" name="link_url" 
                           placeholder="https://example.com" 
                           value="{{ old('link_url', $dashboardContent->link_url) }}">
                    <div class="form-text">Maak de tegel aanklikbaar door een website link toe te voegen</div>
                </div>

                <!-- Open in New Tab -->
                <div class="mb-3">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="open_in_new_tab" name="open_in_new_tab" value="1"
                               {{ old('open_in_new_tab', $dashboardContent->open_in_new_tab) ? 'checked' : '' }}>
                        <label class="form-check-label" for="open_in_new_tab">
                            Open link in nieuwe tab
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Appearance Settings -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-lg font-semibold mb-4">3. Uiterlijk & Grootte</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Tile Size -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">Tegel Grootte</label>
                    <div class="space-y-2">
                        @foreach([
                            'small' => 'Klein (1x1) - Korte notities',
                            'medium' => 'Medium (2x1) - Standaard mededelingen', 
                            'large' => 'Groot (2x2) - Afbeeldingen en uitgebreide content',
                            'banner' => 'Banner (volledige breedte) - Belangrijke aankondigingen'
                        ] as $size => $description)
                            <label class="flex items-center">
                                <input type="radio" name="tile_size" value="{{ $size }}" 
                                       @if(old('tile_size', $dashboardContent->tile_size) === $size) checked @endif
                                       class="mr-3 text-blue-600">
                                <span>{{ $description }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- Colors -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">Kleuren</label>
                    
                    <div class="space-y-3">
                        <div>
                            <label for="background_color" class="block text-sm text-gray-600 mb-1">Achtergrondkleur</label>
                            <div class="flex items-center space-x-2">
                                <input type="color" name="background_color" id="background_color" 
                                       value="{{ old('background_color', $dashboardContent->background_color) }}" 
                                       class="w-12 h-10 border border-gray-300 rounded">
                                <input type="text" id="bg_color_text" value="{{ old('background_color', $dashboardContent->background_color) }}" 
                                       class="flex-1 border border-gray-300 rounded-md px-3 py-2 text-sm">
                            </div>
                        </div>
                        
                        <div>
                            <label for="text_color" class="block text-sm text-gray-600 mb-1">Tekstkleur</label>
                            <div class="flex items-center space-x-2">
                                <input type="color" name="text_color" id="text_color" 
                                       value="{{ old('text_color', $dashboardContent->text_color) }}" 
                                       class="w-12 h-10 border border-gray-300 rounded">
                                <input type="text" id="text_color_text" value="{{ old('text_color', $dashboardContent->text_color) }}" 
                                       class="flex-1 border border-gray-300 rounded-md px-3 py-2 text-sm">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Settings -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-lg font-semibold mb-4">4. Instellingen</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="priority" class="block text-sm font-medium text-gray-700 mb-2">Prioriteit</label>
                    <select name="priority" id="priority" class="w-full border border-gray-300 rounded-md px-3 py-2">
                        <option value="low" @if(old('priority', $dashboardContent->priority) === 'low') selected @endif>Laag</option>
                        <option value="medium" @if(old('priority', $dashboardContent->priority) === 'medium') selected @endif>Medium</option>
                        <option value="high" @if(old('priority', $dashboardContent->priority) === 'high') selected @endif>Hoog</option>
                        <option value="urgent" @if(old('priority', $dashboardContent->priority) === 'urgent') selected @endif>Urgent</option>
                    </select>
                </div>

                <div>
                    <label for="visibility" class="block text-sm font-medium text-gray-700 mb-2">Zichtbaarheid</label>
                    <select name="visibility" id="visibility" class="w-full border border-gray-300 rounded-md px-3 py-2">
                        <option value="staff" @if(old('visibility', $dashboardContent->visibility) === 'staff') selected @endif>Alleen Staff</option>
                        <option value="all" @if(old('visibility', $dashboardContent->visibility) === 'all') selected @endif>Iedereen</option>
                    </select>
                </div>

                <div>
                    <label for="expires_at" class="block text-sm font-medium text-gray-700 mb-2">Vervaldatum (optioneel)</label>
                    <input type="datetime-local" name="expires_at" id="expires_at" 
                           value="{{ old('expires_at', $dashboardContent->expires_at?->format('Y-m-d\TH:i')) }}" 
                           class="w-full border border-gray-300 rounded-md px-3 py-2">
                </div>

                <div class="flex items-center">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_pinned" value="1" 
                               @if(old('is_pinned', $dashboardContent->is_pinned)) checked @endif
                               class="mr-2 text-blue-600">
                        <span class="text-sm font-medium text-gray-700">Vastpinnen bovenaan</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Buttons -->
        <div class="flex justify-end space-x-4">
            <a href="{{ route('dashboard-content.index') }}" 
               class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                Annuleren
            </a>
            <button type="submit" 
                    class="px-6 py-2 rounded-md text-white font-semibold"
                    style="background: linear-gradient(135deg, #c8e1eb 0%, #b5d5e0 100%); color: #0f172a;">
                Wijzigingen Opslaan
            </button>
        </div>
    </form>
</div>

<!-- CKEditor -->
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>

<script>
// Initialize CKEditor
let editor;
ClassicEditor.create(document.querySelector('#content'))
    .then(newEditor => {
        editor = newEditor;
    });

// Type selection handling
document.querySelectorAll('.type-input').forEach(input => {
    input.addEventListener('change', function() {
        updateTypeSelection();
    });
});

function updateTypeSelection() {
    const selectedType = document.querySelector('input[name="type"]:checked').value;
    
    // Update type card styling
    document.querySelectorAll('.type-card').forEach(card => {
        card.style.borderColor = '#e5e7eb';
        card.style.backgroundColor = 'white';
    });
    
    const selectedCard = document.querySelector(`input[value="${selectedType}"]`).nextElementSibling;
    selectedCard.style.borderColor = '#3b82f6';
    selectedCard.style.backgroundColor = '#eff6ff';
}

// Color picker sync
document.getElementById('background_color').addEventListener('input', function() {
    document.getElementById('bg_color_text').value = this.value;
});

document.getElementById('text_color').addEventListener('input', function() {
    document.getElementById('text_color_text').value = this.value;
});

// Image upload handling
document.getElementById('image').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview-img').src = e.target.result;
            document.getElementById('image-upload-area').style.display = 'none';
            document.getElementById('image-preview').style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
});

function removeNewImage() {
    document.getElementById('image').value = '';
    document.getElementById('image-upload-area').style.display = 'block';
    document.getElementById('image-preview').style.display = 'none';
}

// Form submission handling
document.querySelector('form').addEventListener('submit', function(e) {
    if (editor) {
        document.getElementById('content').value = editor.getData();
    }
});

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    updateTypeSelection();
});
</script>

{{-- REMOVED - This CSS was too broad and affected the form layout --}}
@endsection