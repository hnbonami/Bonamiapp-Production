@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h1 class="text-2xl font-bold text-gray-900 mb-6">Selecteer een Sjabloon</h1>
            
            <p class="text-gray-600 mb-6">
                Kies een sjabloon voor het genereren van het rapport voor {{ $type }} - {{ $testtype }}.
            </p>
            
            @if($sjablonen->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($sjablonen as $sjabloon)
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                            <h3 class="font-semibold text-lg mb-2">{{ $sjabloon->naam }}</h3>
                            
                            @if($sjabloon->beschrijving)
                                <p class="text-gray-600 text-sm mb-3">{{ $sjabloon->beschrijving }}</p>
                            @endif
                            
                            <div class="text-xs text-gray-500 mb-3">
                                {{ $sjabloon->paginas->count() }} pagina(s)
                            </div>
                            
                            <div class="flex space-x-2">
                                @if($type === 'bikefit')
                                    <a href="{{ route('rapporten.bikefit', ['bikefit' => request('bikefit_id'), 'sjabloon_id' => $sjabloon->id]) }}" 
                                       class="bg-blue-600 text-white px-3 py-2 rounded text-sm hover:bg-blue-700">
                                        Bekijk Rapport
                                    </a>
                                    <a href="{{ route('rapporten.bikefit.pdf', ['bikefit' => request('bikefit_id'), 'sjabloon_id' => $sjabloon->id]) }}" 
                                       class="bg-red-600 text-white px-3 py-2 rounded text-sm hover:bg-red-700">
                                        Download PDF
                                    </a>
                                @elseif($type === 'inspanningstest')
                                    <a href="{{ route('rapporten.inspanningstest', ['inspanningstest' => request('inspanningstest_id'), 'sjabloon_id' => $sjabloon->id]) }}" 
                                       class="bg-blue-600 text-white px-3 py-2 rounded text-sm hover:bg-blue-700">
                                        Bekijk Rapport
                                    </a>
                                    <a href="{{ route('rapporten.inspanningstest.pdf', ['inspanningstest' => request('inspanningstest_id'), 'sjabloon_id' => $sjabloon->id]) }}" 
                                       class="bg-red-600 text-white px-3 py-2 rounded text-sm hover:bg-red-700">
                                        Download PDF
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <p class="text-gray-500 mb-4">Er zijn nog geen sjablonen aangemaakt voor {{ $type }} - {{ $testtype }}.</p>
                    <a href="{{ route('sjablonen.create') }}" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                        Maak een nieuw sjabloon
                    </a>
                </div>
            @endif
            
            <div class="mt-6 pt-4 border-t border-gray-200">
                <a href="javascript:history.back()" class="text-gray-600 hover:text-gray-800">
                    ← Terug
                </a>
            </div>
        </div>
    </div>
</div>
@endsection