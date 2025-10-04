@extends('layouts.app')

@section('title', ($sjabloon->naam ?? $template->naam ?? 'Sjabloon') . ' - Sjablonen Manager')

@section('content')
    @php
        // Support both $sjabloon and $template variables for compatibility
        $currentSjabloon = $sjabloon ?? $template ?? null;
    @endphp

    @if(!$currentSjabloon)
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
            Sjabloon niet gevonden.
        </div>
    @else
        <!-- Header -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ $currentSjabloon->naam }}</h1>
                        <p class="text-sm text-gray-600">{{ ucfirst($currentSjabloon->categorie) }} sjabloon</p>
                        @if($currentSjabloon->testtype)
                            <p class="text-sm text-blue-600">{{ $currentSjabloon->testtype }}</p>
                        @endif
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('sjablonen.preview', $currentSjabloon->id) }}" 
                           class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            👁️ Voorvertoning
                        </a>
                        <a href="{{ route('sjablonen.edit', $currentSjabloon->id) }}" 
                           class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                            ✏️ Bewerken
                        </a>
                        <a href="{{ route('sjablonen.index') }}" 
                           class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            ← Terug naar Overzicht
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sjabloon Details -->
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Sjabloon Informatie</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Naam</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $currentSjabloon->naam }}</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Categorie</label>
                    <p class="mt-1 text-sm text-gray-900">{{ ucfirst($currentSjabloon->categorie) }}</p>
                </div>
                
                @if($currentSjabloon->testtype)
                <div>
                    <label class="block text-sm font-medium text-gray-700">Test Type</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $currentSjabloon->testtype }}</p>
                </div>
                @endif
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Aangemaakt</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $currentSjabloon->created_at->format('d-m-Y H:i') }}</p>
                </div>
            </div>
            
            @if($currentSjabloon->beschrijving)
            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700">Beschrijving</label>
                <p class="mt-1 text-sm text-gray-900">{{ $currentSjabloon->beschrijving }}</p>
            </div>
            @endif
        </div>

        <!-- Pages Overview -->
        @if($currentSjabloon->pages && $currentSjabloon->pages->count() > 0)
        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">
                Pagina's ({{ $currentSjabloon->pages->count() }})
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach($currentSjabloon->pages as $page)
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="font-medium text-gray-900">
                                Pagina {{ $page->page_number }}
                            </h3>
                            @if($page->is_url_page)
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    URL
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Content
                                </span>
                            @endif
                        </div>
                        
                        @if($page->is_url_page)
                            <p class="text-sm text-gray-600 break-all">{{ $page->url }}</p>
                        @else
                            <p class="text-sm text-gray-600">
                                {{ Str::limit(strip_tags($page->content), 100) }}
                            </p>
                        @endif
                        
                        @if($page->background_image)
                            <p class="text-xs text-gray-500 mt-2">
                                🖼️ Achtergrond: {{ $page->background_image }}
                            </p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
        @else
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">Geen pagina's</h3>
                    <p class="text-sm text-yellow-700 mt-1">
                        Dit sjabloon heeft nog geen pagina's. 
                        <a href="{{ route('sjablonen.edit', $currentSjabloon->id) }}" class="underline font-medium">
                            Ga naar de editor om pagina's toe te voegen.
                        </a>
                    </p>
                </div>
            </div>
        </div>
        @endif
    @endif
@endsection