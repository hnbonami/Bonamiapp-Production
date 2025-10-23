@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto">
    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-6 flex items-center justify-between">
        <a href="{{ route('organisaties.index') }}" class="inline-flex items-center text-gray-600 hover:text-gray-900">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Terug naar overzicht
        </a>
        <a href="{{ route('organisaties.edit', $organisatie) }}" class="inline-flex items-center px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white font-semibold rounded-lg transition">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            Bewerken
        </a>
    </div>

    <!-- Organisatie Header -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-8 mb-6">
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ $organisatie->naam }}</h1>
                <p class="text-gray-600 mt-1">{{ $organisatie->email }}</p>
            </div>
            <div>
                @if($organisatie->status === 'actief')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                        <span class="w-2 h-2 mr-2 rounded-full bg-green-600"></span>
                        Actief
                    </span>
                @elseif($organisatie->status === 'trial')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-orange-100 text-orange-800">
                        <span class="w-2 h-2 mr-2 rounded-full bg-orange-600"></span>
                        Trial
                    </span>
                @else
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                        <span class="w-2 h-2 mr-2 rounded-full bg-gray-600"></span>
                        Inactief
                    </span>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6 pt-6 border-t border-gray-200">
            <div>
                <p class="text-sm text-gray-500 font-medium">Telefoon</p>
                <p class="text-gray-900 mt-1">{{ $organisatie->telefoon ?: '-' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 font-medium">BTW Nummer</p>
                <p class="text-gray-900 mt-1">{{ $organisatie->btw_nummer ?: '-' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 font-medium">Maandelijkse Prijs</p>
                <p class="text-gray-900 mt-1">{{ $organisatie->maandelijkse_prijs ? '€ ' . number_format($organisatie->maandelijkse_prijs, 2) : '-' }}</p>
            </div>
        </div>

        @if($organisatie->adres || $organisatie->postcode || $organisatie->plaats)
        <div class="mt-6 pt-6 border-t border-gray-200">
            <p class="text-sm text-gray-500 font-medium mb-2">Adres</p>
            <p class="text-gray-900">
                {{ $organisatie->adres }}<br>
                {{ $organisatie->postcode }} {{ $organisatie->plaats }}
            </p>
        </div>
        @endif

        @if($organisatie->notities)
        <div class="mt-6 pt-6 border-t border-gray-200">
            <p class="text-sm text-gray-500 font-medium mb-2">Notities</p>
            <p class="text-gray-700 whitespace-pre-wrap">{{ $organisatie->notities }}</p>
        </div>
        @endif
    </div>

    <!-- Statistieken -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow p-6 border border-gray-100">
            <p class="text-sm text-gray-600 font-medium">Totaal Users</p>
            <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['totaal_users'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow p-6 border border-gray-100">
            <p class="text-sm text-gray-600 font-medium">Admins</p>
            <p class="text-3xl font-bold text-blue-600 mt-2">{{ $stats['admins'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow p-6 border border-gray-100">
            <p class="text-sm text-gray-600 font-medium">Medewerkers</p>
            <p class="text-3xl font-bold text-purple-600 mt-2">{{ $stats['medewerkers'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow p-6 border border-gray-100">
            <p class="text-sm text-gray-600 font-medium">Totaal Klanten</p>
            <p class="text-3xl font-bold text-green-600 mt-2">{{ $stats['totaal_klanten'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow p-6 border border-gray-100">
            <p class="text-sm text-gray-600 font-medium">Actieve Klanten</p>
            <p class="text-3xl font-bold text-green-700 mt-2">{{ $stats['actieve_klanten'] }}</p>
        </div>
    </div>

    <!-- Users en Klanten lijsten -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Gebruikers -->
        <div class="bg-white rounded-xl shadow border border-gray-100">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-900">Gebruikers</h2>
            </div>
            <div class="p-6">
                @forelse($organisatie->users->take(10) as $user)
                <div class="flex items-center justify-between py-3 {{ !$loop->last ? 'border-b border-gray-100' : '' }}">
                    <div>
                        <p class="font-medium text-gray-900">{{ $user->name }}</p>
                        <p class="text-sm text-gray-500">{{ $user->email }}</p>
                    </div>
                    <span class="text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-700">{{ $user->role }}</span>
                </div>
                @empty
                <p class="text-gray-500 text-center py-4">Geen gebruikers</p>
                @endforelse
            </div>
        </div>

        <!-- Klanten -->
        <div class="bg-white rounded-xl shadow border border-gray-100">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-900">Recente Klanten</h2>
            </div>
            <div class="p-6">
                @forelse($organisatie->klanten->take(10) as $klant)
                <div class="flex items-center justify-between py-3 {{ !$loop->last ? 'border-b border-gray-100' : '' }}">
                    <div>
                        <p class="font-medium text-gray-900">{{ $klant->voornaam }} {{ $klant->naam }}</p>
                        <p class="text-sm text-gray-500">{{ $klant->email }}</p>
                    </div>
                </div>
                @empty
                <p class="text-gray-500 text-center py-4">Geen klanten</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- NIEUWE SECTIE: Features Beheer --}}
    <div class="bg-white rounded-lg shadow-sm p-6 mt-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900">Beschikbare Features</h3>
            <span class="text-sm text-gray-500">{{ $organisatie->features()->count() }} van {{ \App\Models\Feature::count() }} features actief</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach(\App\Models\Feature::orderBy('categorie')->orderBy('sorteer_volgorde')->get() as $feature)
                @php
                    $isEnabled = $organisatie->hasFeature($feature->key);
                @endphp
                
                <div class="border rounded-lg p-4 {{ $isEnabled ? 'bg-blue-50 border-blue-200' : 'bg-gray-50 border-gray-200' }}">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <svg class="w-5 h-5 {{ $isEnabled ? 'text-blue-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <h4 class="font-medium {{ $isEnabled ? 'text-blue-900' : 'text-gray-700' }}">
                                    {{ $feature->naam }}
                                </h4>
                                @if($feature->is_premium)
                                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        Premium
                                    </span>
                                @endif
                            </div>
                            
                            <p class="text-sm {{ $isEnabled ? 'text-blue-700' : 'text-gray-500' }} mb-2">
                                {{ $feature->beschrijving }}
                            </p>
                            
                            <div class="flex items-center gap-3 text-xs">
                                <span class="text-gray-500">
                                    <span class="font-medium">Categorie:</span> {{ ucfirst($feature->categorie) }}
                                </span>
                                @if($feature->is_premium && $feature->prijs_per_maand)
                                    <span class="text-gray-500">
                                        <span class="font-medium">Prijs:</span> €{{ number_format($feature->prijs_per_maand, 2) }}/mnd
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Toggle Switch --}}
                        <div class="ml-4">
                            <form action="{{ route('organisaties.features.toggle', [$organisatie, $feature]) }}" 
                                  method="POST" 
                                  class="feature-toggle-form">
                                @csrf
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" 
                                           class="sr-only peer feature-toggle"
                                           {{ $isEnabled ? 'checked' : '' }}
                                           onchange="this.form.submit()">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
