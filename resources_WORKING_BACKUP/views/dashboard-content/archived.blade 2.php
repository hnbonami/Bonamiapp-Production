@extends('layouts.app')

@section('content')
<link href="{{ asset('css/dashboard-content.css') }}" rel="stylesheet">

<div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-extrabold text-gray-900">Gearchiveerde Content</h1>
            <p class="text-gray-600 mt-2">{{ $archivedContent->count() }} gearchiveerde items</p>
        </div>
        
        <a href="{{ route('dashboard-content.index') }}" 
           class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg hover:opacity-80 transition-opacity"
           style="background: #f3f4f6; color: #374151; border: 1px solid #d1d5db; text-decoration: none;">
            <span class="mr-2">←</span>
            Terug naar Dashboard
        </a>
    </div>

    @if(session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if($archivedContent->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($archivedContent as $item)
                <div class="bg-white rounded-lg shadow border border-gray-200 p-6 relative">
                    <!-- Archived indicator -->
                    <div class="absolute top-3 right-3 bg-gray-100 text-gray-600 px-2 py-1 rounded text-xs font-medium">
                        📦 Gearchiveerd
                    </div>

                    <!-- Content preview -->
                    <div class="mb-4">
                        <h3 class="font-semibold text-lg text-gray-900 mb-2">
                            {{ $item->type_icon }} {{ $item->title }}
                        </h3>
                        
                        @if($item->image_path)
                            <img src="{{ $item->getImageUrl() }}" alt="{{ $item->title }}" 
                                 class="w-full h-32 object-cover rounded-lg mb-3">
                        @endif

                        <div class="text-gray-600 text-sm leading-relaxed">
                            {!! Str::limit(strip_tags($item->content), 150) !!}
                        </div>
                    </div>

                    <!-- Metadata -->
                    <div class="border-t border-gray-100 pt-4 text-xs text-gray-500">
                        <div class="flex justify-between items-center mb-2">
                            <span>Gemaakt door: {{ $item->user->name ?? 'Onbekend' }}</span>
                            <span class="px-2 py-1 rounded" style="background-color: {{ $item->priority_color }}20; color: {{ $item->priority_color }};">
                                {{ ucfirst($item->priority) }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span>Aangemaakt: {{ $item->created_at->format('d-m-Y H:i') }}</span>
                            <span>Gearchiveerd: {{ $item->updated_at->format('d-m-Y H:i') }}</span>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-between items-center mt-4 pt-4 border-t border-gray-100">
                        <div class="flex gap-2">
                            <form action="{{ route('dashboard-content.restore', $item) }}" method="POST" style="display: inline;">
                                @csrf
                                @method('PATCH')
                                <button type="submit" 
                                        class="px-3 py-1 bg-blue-50 text-blue-700 rounded text-sm hover:bg-blue-100 transition-colors"
                                        onclick="return confirm('Content herstellen?')">
                                    ↩️ Herstellen
                                </button>
                            </form>

                            <a href="{{ route('dashboard-content.edit', $item) }}" 
                               class="px-3 py-1 bg-gray-50 text-gray-700 rounded text-sm hover:bg-gray-100 transition-colors">
                                ✏️ Bewerken
                            </a>
                        </div>

                        <form action="{{ route('dashboard-content.destroy', $item) }}" method="POST" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="px-3 py-1 bg-red-50 text-red-700 rounded text-sm hover:bg-red-100 transition-colors"
                                    onclick="return confirm('Definitief verwijderen? Dit kan niet ongedaan gemaakt worden!')">
                                🗑️ Verwijderen
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-12">
            <div class="text-6xl mb-4 opacity-30">📦</div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">Geen gearchiveerde content</h3>
            <p class="text-gray-600 mb-6">
                Er zijn nog geen items gearchiveerd. Content die je archiveert verschijnt hier.
            </p>
            <a href="{{ route('dashboard-content.index') }}" 
               class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg hover:opacity-80 transition-opacity"
               style="background: linear-gradient(135deg, #c8e1eb 0%, #b5d5e0 100%); color: #0f172a; border: 1px solid #94a3b8; text-decoration: none;">
                <span class="mr-2">←</span>
                Terug naar Dashboard
            </a>
        </div>
    @endif
</div>

@endsection