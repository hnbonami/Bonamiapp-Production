@extends('layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <h1 class="text-2xl font-bold text-gray-900">Sjablonen Beheer</h1>
                        <a href="{{ route('sjablonen.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Nieuw Sjabloon
                        </a>
                    </div>

                    @if(session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                            {{ session('error') }}
                        </div>
                    @endif

                    @php
                        // Safe null check
                        $hasSjablonen = isset($sjablonen) && !is_null($sjablonen) && method_exists($sjablonen, 'count') && $sjablonen->count() > 0;
                    @endphp

                    @if($hasSjablonen)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($sjablonen as $sjabloon)
                                <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                                    <div class="px-6 py-4">
                                        <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $sjabloon->naam ?? 'Onbekend' }}</h3>
                                        <div class="space-y-1 text-sm text-gray-600">
                                            <p><span class="font-medium">Type:</span> {{ ucfirst($sjabloon->categorie ?? 'Onbekend') }}</p>
                                            @if(isset($sjabloon->testtype) && $sjabloon->testtype)
                                                <p><span class="font-medium">Testtype:</span> {{ $sjabloon->testtype }}</p>
                                            @endif
                                            @if(isset($sjabloon->beschrijving) && $sjabloon->beschrijving)
                                                <p><span class="font-medium">Beschrijving:</span> {{ Str::limit($sjabloon->beschrijving, 100) }}</p>
                                            @endif
                                            <p><span class="font-medium">Aangemaakt:</span> {{ $sjabloon->created_at ? $sjabloon->created_at->format('d-m-Y') : 'Onbekend' }}</p>
                                        </div>
                                    </div>
                                    
                                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-between">
                                        <div class="flex space-x-2">
                                            <a href="{{ route('sjablonen.edit', $sjabloon) }}" 
                                               class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-3 rounded text-sm">
                                                Bewerken
                                            </a>
                                            
                                            <a href="{{ route('sjablonen.show', $sjabloon) }}" 
                                               class="bg-green-500 hover:bg-green-700 text-white font-bold py-1 px-3 rounded text-sm">
                                                Bekijken
                                            </a>
                                        </div>
                                        
                                        <form method="POST" action="{{ route('sjablonen.destroy', $sjabloon) }}" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-3 rounded text-sm"
                                                    onclick="return confirm('Weet je zeker dat je dit sjabloon wilt verwijderen?')">
                                                Verwijderen
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <div class="text-gray-500 text-lg mb-4">Nog geen sjablonen aangemaakt</div>
                            <a href="{{ route('sjablonen.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Maak je eerste sjabloon aan
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection