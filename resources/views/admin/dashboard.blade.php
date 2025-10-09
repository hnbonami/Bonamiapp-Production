@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <h1 class="text-3xl font-bold text-gray-900 mb-6">🔧 Admin Dashboard</h1>
                <p class="text-gray-600 mb-8">Beheer van alle admin functies en beschikbare tools</p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Database Tools -->
                    <div class="bg-blue-50 p-6 rounded-lg">
                        <div class="flex items-center mb-4">
                            <svg class="w-8 h-8 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 1.79 4 4 4h8c2.21 0 4-1.79 4-4V7c0-2.21-1.79-4-4-4H8c-2.21 0-4 1.79-4 4z"/>
                            </svg>
                            <h3 class="text-xl font-semibold text-blue-800">Database Tools</h3>
                        </div>
                        <p class="text-blue-600 mb-4">Beheer database & notities. Bekijk en beheer database tabellen, staff notities en systeemdata.</p>
                        <a href="#" class="inline-flex items-center text-blue-600 hover:text-blue-800 font-medium">
                            Database Beheer Openen 
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>

                    <!-- Email Beheer KAKA -->
                    <div class="bg-green-50 p-6 rounded-lg">
                        <div class="flex items-center mb-4">
                            <svg class="w-8 h-8 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            <h3 class="text-xl font-semibold text-green-800">Email Beheer</h3>
                        </div>
                        <p class="text-green-600 mb-4">Beheer email templates, instellingen en logs. Inclusief verjaardagen en automatische herinneringen.</p>
                        <div class="space-y-2">
                            <a href="{{ route('admin.email.index') }}" class="inline-flex items-center text-green-600 hover:text-green-800 font-medium">
                                Email Beheer Openen 
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                            <br>
                            <a href="{{ route('admin.email.logs') }}" class="inline-flex items-center text-green-600 hover:text-green-800 font-medium">
                                📊 Email Logs
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Bikefit Uitleenbeheer -->
                <div class="mt-6">
                    <div class="bg-yellow-50 p-6 rounded-lg">
                        <div class="flex items-center mb-4">
                            <svg class="w-8 h-8 text-yellow-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            <h3 class="text-xl font-semibold text-yellow-800">⚡ Bikefit Uitleenbeheer</h3>
                        </div>
                        <p class="text-yellow-600 mb-4">Uitlening & retour beheer. Beheer testzadel uitleningen, herinneringen en retour administratie.</p>
                        <a href="#" class="inline-flex items-center text-yellow-600 hover:text-yellow-800 font-medium">
                            Uitleenbeheer Openen 
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
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