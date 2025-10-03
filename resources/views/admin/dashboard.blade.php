@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <h1 class="text-3xl font-bold text-gray-900 mb-6">Admin Dashboard</h1>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Klanten Overzicht -->
                    <div class="bg-blue-50 p-6 rounded-lg">
                        <h3 class="text-lg font-semibold text-blue-800 mb-2">Klanten</h3>
                        <p class="text-3xl font-bold text-blue-600 mb-2">{{ \App\Models\Klant::count() }}</p>
                        <p class="text-sm text-blue-600">Totaal aantal klanten</p>
                        <a href="{{ route('klanten.index') }}" class="inline-block mt-4 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                            Bekijk Klanten
                        </a>
                    </div>

                    <!-- Inspanningstesten Overzicht -->
                    <div class="bg-green-50 p-6 rounded-lg">
                        <h3 class="text-lg font-semibold text-green-800 mb-2">Inspanningstesten</h3>
                        <p class="text-3xl font-bold text-green-600 mb-2">{{ \App\Models\Inspanningstest::count() }}</p>
                        <p class="text-sm text-green-600">Totaal aantal testen</p>
                        <a href="{{ route('inspanningstest.index') }}" class="inline-block mt-4 bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                            Bekijk Testen
                        </a>
                    </div>

                    <!-- Bikefits Overzicht -->
                    <div class="bg-purple-50 p-6 rounded-lg">
                        <h3 class="text-lg font-semibold text-purple-800 mb-2">Bikefits</h3>
                        <p class="text-3xl font-bold text-purple-600 mb-2">{{ \App\Models\Bikefit::count() }}</p>
                        <p class="text-sm text-purple-600">Totaal aantal bikefits</p>
                        <a href="{{ route('bikefit.index') }}" class="inline-block mt-4 bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700">
                            Bekijk Bikefits
                        </a>
                    </div>
                </div>

                <!-- Recente Activiteit -->
                <div class="mt-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Recente Activiteit</h2>
                    
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Recente Klanten -->
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Nieuwe Klanten (deze maand)</h3>
                            @php
                                $recenteKlanten = \App\Models\Klant::whereMonth('created_at', now()->month)->latest()->limit(5)->get();
                            @endphp
                            @if($recenteKlanten->count() > 0)
                                <ul class="space-y-2">
                                    @foreach($recenteKlanten as $klant)
                                        <li class="flex justify-between items-center">
                                            <span>{{ $klant->naam }}</span>
                                            <span class="text-sm text-gray-500">{{ $klant->created_at->format('d/m/Y') }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-gray-500">Geen nieuwe klanten deze maand</p>
                            @endif
                        </div>

                        <!-- Recente Testen -->
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Recente Testen</h3>
                            @php
                                $recenteTesten = \App\Models\Inspanningstest::with('klant')->latest()->limit(5)->get();
                            @endphp
                            @if($recenteTesten->count() > 0)
                                <ul class="space-y-2">
                                    @foreach($recenteTesten as $test)
                                        <li class="flex justify-between items-center">
                                            <span>{{ $test->klant->naam ?? 'Onbekend' }} - {{ $test->testtype }}</span>
                                            <span class="text-sm text-gray-500">{{ $test->created_at->format('d/m/Y') }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-gray-500">Geen recente testen</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection