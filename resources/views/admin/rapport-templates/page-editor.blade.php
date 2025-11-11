@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
        
        {{-- Header --}}
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6">
                <div class="flex justify-between items-start">
                    <div>
                        <h1 class="text-3xl font-extrabold text-gray-900">📸 Pagina Editor</h1>
                        <p class="text-lg text-gray-600 mt-2">
                            Pagina {{ $page->page_number }}: {{ $page->page_title }}
                        </p>
                    </div>
                    <a href="{{ route('admin.rapport-templates.edit', $rapportTemplate) }}" 
                       class="rounded-full px-6 py-2 text-gray-800 font-bold hover:opacity-80 transition" 
                       style="background-color: #c8e1eb;">
                        ← Terug naar Template
                    </a>
                </div>
            </div>
        </div>

        {{-- Success/Error Messages --}}
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-green-800 font-medium">{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-red-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-red-800 font-medium">{{ session('error') }}</span>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            {{-- Foto Upload --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">📷 Foto Upload</h2>

                    {{-- Huidige Foto --}}
                    @if($page->hasMedia())
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Huidige Foto</label>
                            <div class="relative group">
                                <img src="{{ $page->media_url }}" 
                                     alt="Pagina foto" 
                                     class="w-full rounded-lg object-cover max-h-96">
                                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition rounded-lg flex items-center justify-center">
                                    <span class="text-white opacity-0 group-hover:opacity-100 font-medium">
                                        Upload nieuwe foto om te vervangen
                                    </span>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="mb-6 border-2 border-dashed border-gray-300 rounded-lg p-12 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <p class="mt-2 text-sm text-gray-600">Nog geen foto geüpload</p>
                        </div>
                    @endif

                    {{-- Upload Form --}}
                    <form action="{{ route('admin.rapport-templates.pages.upload-media', [$rapportTemplate, $page]) }}" 
                          method="POST" 
                          enctype="multipart/form-data"
                          id="uploadForm">
                        @csrf

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                {{ $page->hasMedia() ? 'Nieuwe Foto Uploaden' : 'Foto Selecteren' }}
                            </label>
                            <input type="file" 
                                   name="media" 
                                   accept="image/*"
                                   required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   onchange="previewImage(this)">
                            @error('media')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500">Max 10MB • JPG, PNG, WebP</p>
                        </div>

                        {{-- Preview --}}
                        <div id="imagePreview" class="mb-4 hidden">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Preview</label>
                            <img id="previewImg" src="" alt="Preview" class="w-full rounded-lg max-h-64 object-cover">
                        </div>

                        {{-- Vaste instellingen info box --}}
                        <div class="mb-4 bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                                <div class="flex-1">
                                    <h4 class="font-medium text-blue-900 mb-1">Vaste Foto Instellingen</h4>
                                    <p class="text-sm text-blue-800">
                                        Positie: <strong>{{ ucfirst($page->media_position) }}</strong> • 
                                        Grootte: <strong>{{ $page->media_size }}%</strong>
                                    </p>
                                    <p class="text-xs text-blue-700 mt-1">
                                        Deze instellingen zijn vast voor dit pagina type en worden automatisch toegepast.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <button type="submit" 
                                class="w-full px-6 py-3 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 transition">
                            📤 Foto Uploaden
                        </button>
                    </form>
                </div>
            </div>

            {{-- Pagina Instellingen --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">⚙️ Pagina Instellingen</h2>

                    <form action="{{ route('admin.rapport-templates.pages.update', [$rapportTemplate, $page]) }}" 
                          method="POST">
                        @csrf
                        @method('PUT')

                        {{-- Pagina Titel --}}
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Pagina Titel</label>
                            <input type="text" 
                                   name="page_title" 
                                   value="{{ old('page_title', $page->page_title) }}"
                                   class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        {{-- Layout Type --}}
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Layout Type</label>
                            <select name="layout_type" 
                                    class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                <option value="standard" {{ $page->layout_type === 'standard' ? 'selected' : '' }}>Standaard</option>
                                <option value="two-column" {{ $page->layout_type === 'two-column' ? 'selected' : '' }}>Twee Kolommen</option>
                                <option value="full-width" {{ $page->layout_type === 'full-width' ? 'selected' : '' }}>Volledige Breedte</option>
                                <option value="sidebar" {{ $page->layout_type === 'sidebar' ? 'selected' : '' }}>Met Sidebar</option>
                            </select>
                        </div>

                        {{-- Logo Weergave --}}
                        <div class="mb-4">
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       name="show_logo" 
                                       value="1" 
                                       {{ $page->show_logo ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-700">Toon organisatie logo op deze pagina</span>
                            </label>
                        </div>

                        {{-- Custom Header --}}
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Custom Header (HTML)</label>
                            <textarea name="custom_header" 
                                      rows="3"
                                      placeholder="<h2>Extra kopje...</h2>"
                                      class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 font-mono text-sm">{{ old('custom_header', $page->custom_header) }}</textarea>
                            <p class="mt-1 text-xs text-gray-500">Optionele HTML voor boven de pagina content</p>
                        </div>

                        {{-- Custom Footer --}}
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Custom Footer (HTML)</label>
                            <textarea name="custom_footer" 
                                      rows="3"
                                      placeholder="<p>Disclaimer tekst...</p>"
                                      class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 font-mono text-sm">{{ old('custom_footer', $page->custom_footer) }}</textarea>
                            <p class="mt-1 text-xs text-gray-500">Optionele HTML voor onder de pagina content</p>
                        </div>

                        <button type="submit" 
                                class="w-full px-6 py-3 bg-green-600 text-white font-bold rounded-lg hover:bg-green-700 transition">
                                💾 Instellingen Opslaan
                        </button>
                    </form>
                </div>
            </div>

        </div>

        {{-- Info Box --}}
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mt-6">
            <div class="flex gap-3">
                <svg class="w-6 h-6 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <h3 class="font-bold text-blue-900 mb-2">💡 Tips voor foto's</h3>
                    <ul class="text-sm text-blue-800 space-y-1">
                        <li>• Gebruik hoogwaardige afbeeldingen (min. 1920x1080px)</li>
                        <li>• Foto's worden automatisch geoptimaliseerd voor print</li>
                        <li>• Background positie is ideaal voor voorblad pagina's</li>
                        <li>• Top/Bottom positie werkt goed voor content pagina's</li>
                    </ul>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
    // Preview image before upload
    function previewImage(input) {
        const preview = document.getElementById('imagePreview');
        const previewImg = document.getElementById('previewImg');
        
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                preview.classList.remove('hidden');
            }
            
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endsection
