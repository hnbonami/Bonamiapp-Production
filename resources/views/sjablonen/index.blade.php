@extends('layouts.app')

@section('title', 'Sjablonen Manager')

@section('content')
    <!-- Sjablonen Button Enhancements -->
    <link rel="stylesheet" href="/css/sjablonen-editor-buttons.css">

    <!-- Header -->
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-6 py-4">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Sjablonen Manager</h1>
                    <p class="text-sm text-gray-600">Beheer je document sjablonen</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('sjablonen.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        + Nieuw Sjabloon
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Message -->
    @if(session('success'))
        <div class="mb-6">
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                {{ session('success') }}
            </div>
        </div>
    @endif

    <!-- Main Content -->
    @if($sjablonen->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($sjablonen as $sjabloon)
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-200">
                    <!-- Card Header -->
                    <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-indigo-50 border-b">
                        <h3 class="text-lg font-semibold text-gray-900">{{ $sjabloon->naam }}</h3>
                        <div class="flex items-center space-x-2 mt-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ ucfirst($sjabloon->categorie) }}
                            </span>
                            @if($sjabloon->testtype)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    {{ $sjabloon->testtype }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="px-6 py-4">
                        @if($sjabloon->beschrijving)
                            <p class="text-gray-600 text-sm mb-4">{{ Str::limit($sjabloon->beschrijving, 100) }}</p>
                        @endif
                        
                        <div class="text-xs text-gray-500 mb-4">
                            <p>Aangemaakt: {{ $sjabloon->created_at->format('d-m-Y H:i') }}</p>
                            @if($sjabloon->updated_at != $sjabloon->created_at)
                                <p>Laatst bewerkt: {{ $sjabloon->updated_at->format('d-m-Y H:i') }}</p>
                            @endif
                        </div>
                    </div>

                    <!-- Card Actions -->
                    <div class="px-6 py-4 bg-gray-50 border-t flex justify-between space-x-2">
                        <div class="flex space-x-2">
                            <a href="{{ route('sjablonen.edit', $sjabloon) }}" 
                               class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-3 rounded text-sm">
                                Bewerken
                            </a>
                            <a href="{{ route('sjablonen.show', $sjabloon) }}" 
                               class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-3 rounded text-sm">
                                Bekijken
                            </a>
                        </div>
                        
                        <form method="POST" action="{{ route('sjablonen.destroy', $sjabloon) }}" 
                              onsubmit="return confirm('Weet je zeker dat je dit sjabloon wilt archiveren?')" 
                              class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-3 rounded text-sm">
                                Archiveren
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <!-- Empty State -->
        <div class="text-center py-12">
            <div class="mx-auto h-24 w-24 text-gray-400">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
            <h3 class="mt-4 text-lg font-medium text-gray-900">Geen sjablonen gevonden</h3>
            <p class="mt-2 text-gray-500">Maak je eerste sjabloon aan om te beginnen.</p>
            <div class="mt-6">
                <a href="{{ route('sjablonen.create') }}" 
                   class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    + Eerste Sjabloon Aanmaken
                </a>
            </div>
        </div>
    @endif
@endsection