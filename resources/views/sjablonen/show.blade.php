<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $template->name }} - Voorvertoning
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('sjablonen.edit', $template) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Bewerken
                </a>
                <a href="{{ route('sjablonen.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Terug
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-2">Sjabloon Informatie</h3>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p><strong>Type:</strong> {{ $template->type_display }}</p>
                            @if($template->description)
                                <p><strong>Beschrijving:</strong> {{ $template->description }}</p>
                            @endif
                            <p><strong>Aantal pagina's:</strong> {{ $template->pages->count() }}</p>
                            <p><strong>Laatst gewijzigd:</strong> {{ $template->updated_at->format('d-m-Y H:i') }}</p>
                        </div>
                    </div>

                    <div class="space-y-8">
                        @foreach($template->pages as $page)
                            <div class="border-l-4 border-blue-500 pl-6">
                                <h4 class="text-md font-semibold mb-4 flex items-center">
                                    Pagina {{ $page->page_number }}
                                    @if($page->is_url_page)
                                        <span class="ml-2 px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">URL Pagina</span>
                                    @endif
                                </h4>
                                
                                @if($page->is_url_page)
                                    <div class="bg-green-50 p-4 rounded-lg">
                                        <p><strong>URL:</strong> 
                                            <a href="{{ $page->url }}" target="_blank" class="text-blue-600 hover:text-blue-800 underline">
                                                {{ $page->url }}
                                            </a>
                                        </p>
                                    </div>
                                @else
                                    <!-- A4 Preview Style -->
                                    <div class="bg-white border border-gray-300 shadow-lg mx-auto" 
                                         style="width: 210mm; min-height: 297mm; padding: 20mm; font-family: 'Times New Roman', serif;
                                                @if($page->background_image) 
                                                    background-image: url('/backgrounds/{{ $page->background_image }}'); 
                                                    background-size: cover; 
                                                    background-position: center; 
                                                    background-repeat: no-repeat;
                                                @endif">
                                        <div class="preview-content">
                                            {!! $page->content !!}
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .preview-content {
            font-size: 12pt;
            line-height: 1.4;
        }
        
        .preview-content h1 { font-size: 18pt; font-weight: bold; margin-bottom: 12pt; }
        .preview-content h2 { font-size: 16pt; font-weight: bold; margin-bottom: 10pt; }
        .preview-content h3 { font-size: 14pt; font-weight: bold; margin-bottom: 8pt; }
        .preview-content p { margin-bottom: 6pt; }
        .preview-content table { width: 100%; border-collapse: collapse; margin: 12pt 0; }
        .preview-content td, .preview-content th { border: 1px solid #000; padding: 6pt; }
    </style>
</x-app-layout>