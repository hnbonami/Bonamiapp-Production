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
        <div class="flex items-center gap-3">
            <form action="{{ route('organisaties.sendInvitation', $organisatie) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="inline-flex items-center px-4 py-2 text-gray-900 font-semibold rounded-lg transition" style="background-color: #c8e1eb;" onclick="return confirm('Uitnodiging versturen naar {{ $organisatie->email }}?')">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    Stuur Uitnodiging
                </button>
            </form>
            <a href="{{ route('organisaties.edit', $organisatie) }}" class="inline-flex items-center px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white font-semibold rounded-lg transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Bewerken
            </a>
        </div>
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
</div>
@endsection
