@extends('layouts.app')

@section('content')
@if(session('success'))
    <<!-- Actions -->
<div class="flex justify-between items-center mb-1">v style="background:#d1fae5;color:#065f46;padding:1em;margin-bottom:1em;border-radius:5px;">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div style="background:#fef2f2;color:#dc2626;padding:1em;margin-bottom:1em;border-radius:5px;">
        {{ session('error') }}
    </div>
@endif

<!-- Stats tiles -->
<div class="grid grid-cols-4 gap-4 mb-4">
    <div class="bg-white rounded-lg shadow p-4">
        <div class="flex items-center">
            <div class="p-2 rounded-full bg-blue-100 text-blue-500">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-xs font-medium text-gray-600">Uitgeleend</p>
                <p class="text-lg font-semibold text-gray-900">{{ $stats['uitgeleend'] }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-4">
        <div class="flex items-center">
            <div class="p-2 rounded-full bg-red-100 text-red-500">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-xs font-medium text-gray-600">Te laat (3+ weken)</p>
                <p class="text-lg font-semibold text-gray-900">{{ $stats['te_laat'] }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-4">
        <div class="flex items-center">
            <div class="p-2 rounded-full bg-yellow-100 text-yellow-500">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4 19h8v2H4z"/>
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-xs font-medium text-gray-600">Herinnering nodig</p>
                <p class="text-lg font-semibold text-gray-900">{{ $stats['herinnering_nodig'] }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-4">
        <div class="flex items-center">
            <div class="p-2 rounded-full bg-green-100 text-green-500">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-xs font-medium text-gray-600">Verwacht vandaag</p>
                <p class="text-lg font-semibold text-gray-900">{{ $stats['verwacht_vandaag'] }}</p>
            </div>
        </div>
    </div>
</div>

<!-- Actions -->
<div class="flex justify-between items-center mb-4">
    <div class="flex gap-4">
        <a href="{{ route('testzadels.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-medium">
            + Nieuwe Uitlening
        </a>
    </div>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
        <div>
            <h3 class="text-lg font-medium text-gray-900">Actieve Uitleningen</h3>
            <p class="text-sm text-gray-500">Alle uitgeleende testzadels die nog niet zijn teruggegeven</p>
        </div>
        <a href="{{ route('testzadels.archived') }}" class="text-gray-600 hover:text-gray-800 font-medium">
            📁 Bekijk Archief
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Klant</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Bikefit</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Testzadel</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Aantal dagen in gebruik</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Automail</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Acties</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @forelse($testzadels as $testzadel)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">{{ $testzadel->klant->voornaam }} {{ $testzadel->klant->naam }}</div>
                        <div class="text-sm text-gray-500">{{ $testzadel->klant->email }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        @if($testzadel->bikefit)
                            {{ $testzadel->bikefit->datum->format('d/m/Y') }}
                            @if($testzadel->bikefit->testtype)
                                <div class="text-xs text-gray-500">{{ $testzadel->bikefit->testtype }}</div>
                            @endif
                        @else
                            -
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $testzadel->onderdeel_type }}
                        @if($testzadel->zadel_merk || $testzadel->zadel_model)
                            <div class="text-xs text-gray-500">
                                {{ $testzadel->zadel_merk }} {{ $testzadel->zadel_model }}
                            </div>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        @php
                            $startDate = \Carbon\Carbon::parse($testzadel->uitleen_datum);
                            $endDate = $testzadel->werkelijke_retour_datum 
                                ? \Carbon\Carbon::parse($testzadel->werkelijke_retour_datum)
                                : \Carbon\Carbon::now();
                            $daysInUse = ceil($startDate->diffInDays($endDate));
                        @endphp
                        {{ $daysInUse }} dagen
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <input type="checkbox" 
                                   {{ $testzadel->automatisch_mailtje ? 'checked' : '' }} 
                                   disabled
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <span class="ml-2 text-sm text-gray-900">
                                {{ $testzadel->automatisch_mailtje ? 'Aan' : 'Uit' }}
                            </span>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                            {{ $testzadel->status === 'uitgeleend' ? 'bg-blue-100 text-blue-800' : 
                               ($testzadel->status === 'teruggegeven' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800') }}">
                            {{ ucfirst($testzadel->status) }}
                        </span>
                        
                        @if($testzadel->status === 'uitgeleend' && $testzadel->verwachte_retour_datum < now())
                            <div class="text-xs text-red-600 mt-1">
                                Te laat: {{ \Carbon\Carbon::parse($testzadel->verwachte_retour_datum)->diffInDays(now()) }} dagen
                            </div>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex gap-2 justify-end">
                            @if($testzadel->status === 'uitgeleend')
                                <!-- Send reminder -->
                                @if($testzadel->automatisch_mailtje)
                                    <form method="POST" action="{{ route('testzadels.reminder', $testzadel) }}" class="inline">
                                        @csrf
                                        <button type="submit" 
                                                class="bg-yellow-100 text-yellow-800 hover:bg-yellow-200 p-2 rounded text-xs font-medium"
                                                title="Verstuur herinnering">
                                            📧
                                        </button>
                                    </form>
                                @endif
                                
                                <!-- Mark as returned -->
                                <form method="POST" action="{{ route('testzadels.returned', $testzadel) }}" class="inline">
                                    @csrf
                                    <button type="submit" 
                                            class="bg-green-100 text-green-800 hover:bg-green-200 p-2 rounded text-xs font-medium"
                                            onclick="return confirm('Markeren als teruggegeven?')"
                                            title="Markeer als teruggegeven">
                                        ✅
                                    </button>
                                </form>
                            @endif
                            
                            @if($testzadel->status === 'teruggegeven')
                                <!-- Archive -->
                                <form method="POST" action="{{ route('testzadels.archive', $testzadel) }}" class="inline">
                                    @csrf
                                    <button type="submit" 
                                            class="bg-gray-100 text-gray-800 hover:bg-gray-200 p-2 rounded text-xs font-medium"
                                            onclick="return confirm('Archiveren?')"
                                            title="Archiveren">
                                        📁
                                    </button>
                                </form>
                            @endif
                            
                            <!-- Edit -->
                            <a href="{{ route('testzadels.edit', $testzadel) }}" 
                               class="bg-blue-100 text-blue-800 hover:bg-blue-200 p-2 rounded text-xs font-medium"
                               title="Bewerken">
                                ✏️
                            </a>
                            
                            <!-- Delete -->
                            <form action="{{ route('testzadels.destroy', $testzadel) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="bg-red-100 text-red-800 hover:bg-red-200 p-2 rounded text-xs font-medium"
                                        onclick="return confirm('Definitief verwijderen?')"
                                        title="Verwijderen">
                                    🗑️
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                        Geen uitgeleende testzadels gevonden
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection