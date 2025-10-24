@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    {{-- Header met periode selectie --}}
    <div class="flex justify-between items-center mb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Prestaties Overzicht</h1>
            <p class="text-sm text-gray-600 mt-1">Bekijk alle prestaties per medewerker - {{ $huidigKwartaal }} {{ $huidigJaar }}</p>
        </div>
        
        {{-- Jaar & Kwartaal filter --}}
        <div class="flex gap-2 items-center">
            <span class="text-sm text-gray-600">Periode:</span>
            
            {{-- Jaar navigatie met +/- knoppen --}}
            <div class="flex items-center gap-1">
                <a href="{{ route('admin.prestaties.overzicht') }}?jaar={{ $huidigJaar - 1 }}&kwartaal={{ $huidigKwartaal }}" 
                   class="px-2 py-1 text-sm bg-gray-100 hover:bg-gray-200 rounded transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <span class="text-sm font-semibold px-3 py-1 bg-blue-50 text-blue-700 rounded">
                    {{ $huidigJaar }}
                </span>
                <a href="{{ route('admin.prestaties.overzicht') }}?jaar={{ $huidigJaar + 1 }}&kwartaal={{ $huidigKwartaal }}" 
                   class="px-2 py-1 text-sm bg-gray-100 hover:bg-gray-200 rounded transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
            
            <select id="kwartaal-filter" class="text-sm rounded border-gray-300 py-1 px-2">
                <option value="Q1" {{ $huidigKwartaal == 'Q1' ? 'selected' : '' }}>Q1</option>
                <option value="Q2" {{ $huidigKwartaal == 'Q2' ? 'selected' : '' }}>Q2</option>
                <option value="Q3" {{ $huidigKwartaal == 'Q3' ? 'selected' : '' }}>Q3</option>
                <option value="Q4" {{ $huidigKwartaal == 'Q4' ? 'selected' : '' }}>Q4</option>
            </select>
        </div>
    </div>

    {{-- Totaal Overzicht Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Totaal Prestaties</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $totaalPrestaties }}</p>
                </div>
                <div class="bg-blue-100 rounded-full p-3">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Totale Commissie</p>
                    <p class="text-3xl font-bold text-green-600">€{{ number_format($totaleCommissie, 2, ',', '.') }}</p>
                </div>
                <div class="bg-green-100 rounded-full p-3">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Gemiddelde per Prestatie</p>
                    <p class="text-3xl font-bold text-blue-600">€{{ $totaalPrestaties > 0 ? number_format($totaleCommissie / $totaalPrestaties, 2, ',', '.') : '0,00' }}</p>
                </div>
                <div class="bg-blue-100 rounded-full p-3">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Medewerkers Overzicht --}}
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Prestaties per Medewerker</h2>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Medewerker</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aantal Prestaties</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Totale Commissie</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gemiddelde</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acties</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($medewerkerStats as $stat)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                            <span class="text-blue-600 font-semibold">{{ substr($stat->name, 0, 1) }}</span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $stat->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $stat->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-semibold text-gray-900">{{ $stat->aantal_prestaties }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-semibold text-green-600">€{{ number_format($stat->totale_commissie, 2, ',', '.') }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm text-gray-900">€{{ number_format($stat->gemiddelde_commissie, 2, ',', '.') }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('admin.prestaties.coach.detail', $stat->id) }}?jaar={{ $huidigJaar }}&kwartaal={{ $huidigKwartaal }}" 
                                   class="text-blue-600 hover:text-blue-900">
                                    Details →
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <p class="text-gray-500">Nog geen prestaties in deze periode</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Kwartaal filter - redirect bij wijziging
document.getElementById('kwartaal-filter').addEventListener('change', function() {
    const jaar = {{ $huidigJaar }};
    const kwartaal = this.value;
    window.location.href = `/admin/prestaties/overzicht?jaar=${jaar}&kwartaal=${kwartaal}`;
});
</script>
@endsection
