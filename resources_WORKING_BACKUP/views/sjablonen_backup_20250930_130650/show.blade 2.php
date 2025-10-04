@extends('layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h1 class="text-2xl font-bold text-gray-900 mb-6">Sjabloon: {{ $sjabloon->naam }}</h1>

                    <div class="mb-6">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <span class="font-medium text-gray-700">Type:</span>
                                    <span class="text-gray-900">{{ ucfirst($sjabloon->categorie) }}</span>
                                </div>
                                @if($sjabloon->testtype)
                                <div>
                                    <span class="font-medium text-gray-700">Testtype:</span>
                                    <span class="text-gray-900">{{ $sjabloon->testtype }}</span>
                                </div>
                                @endif
                            </div>
                            @if($sjabloon->beschrijving)
                            <div class="mt-4">
                                <span class="font-medium text-gray-700">Beschrijving:</span>
                                <p class="text-gray-900 mt-1">{{ $sjabloon->beschrijving }}</p>
                            </div>
                            @endif
                        </div>
                    </div>

                    <div class="flex justify-between">
                        <a href="{{ route('sjablonen.index') }}" 
                           class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            Terug naar overzicht
                        </a>
                        
                        <div class="flex space-x-3">
                            <a href="{{ route('sjablonen.edit', $sjabloon) }}" 
                               class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Bewerken
                            </a>
                            
                            <form method="POST" action="{{ route('sjablonen.destroy', $sjabloon) }}" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded"
                                        onclick="return confirm('Weet je zeker dat je dit sjabloon wilt verwijderen?')">
                                    Verwijderen
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection