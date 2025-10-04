@extends('layouts.app')

@section('content')
@if(session('success'))
    <div style="background:#d1fae5;color:#065f46;padding:1em;margin-bottom:1em;border-radius:5px;">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div style="background:#fef2f2;color:#dc2626;padding:1em;margin-bottom:1em;border-radius:5px;">
        {{ session('error') }}
    </div>
@endif

<h2 class="text-2xl font-bold mb-6">Gearchiveerde Testzadels</h2>
<p class="text-gray-600 mb-6">Overzicht van alle gearchiveerde testzadels</p>

<!-- Actions -->
<div class="flex justify-between items-center mb-6">
    <div class="flex gap-4">
        <a href="{{ route('testzadels.index') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-medium">
            ← Terug naar actieve uitleningen
        </a>
    </div>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-medium text-gray-900">Gearchiveerde Uitleningen</h3>
        <p class="text-sm text-gray-500">Alle testzadels die zijn gearchiveerd</p>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Klant</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Testzadel</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Uitleen Datum</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Retour Datum</th>
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
                        {{ $testzadel->onderdeel_type }}
                        @if($testzadel->zadel_merk || $testzadel->zadel_model)
                            <div class="text-xs text-gray-500">
                                {{ $testzadel->zadel_merk }} {{ $testzadel->zadel_model }}
                            </div>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $testzadel->uitleen_datum ? \Carbon\Carbon::parse($testzadel->uitleen_datum)->format('d/m/Y') : '-' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $testzadel->werkelijke_retour_datum ? \Carbon\Carbon::parse($testzadel->werkelijke_retour_datum)->format('d/m/Y') : '-' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                            Gearchiveerd
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex gap-2 justify-end">
                            <!-- View -->
                            <a href="{{ route('testzadels.show', $testzadel) }}" 
                               class="bg-blue-100 text-blue-800 hover:bg-blue-200 p-2 rounded text-xs font-medium"
                               title="Bekijken">
                                👁️
                            </a>
                            
                            <!-- Edit -->
                            <a href="{{ route('testzadels.edit', $testzadel) }}" 
                               class="bg-green-100 text-green-800 hover:bg-green-200 p-2 rounded text-xs font-medium"
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
                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                        Geen gearchiveerde testzadels gevonden
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection