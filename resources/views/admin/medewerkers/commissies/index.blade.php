@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    {{-- Header --}}
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Medewerker Commissies</h1>
            <p class="text-sm text-gray-600 mt-1">Beheer commissie factoren per medewerker</p>
        </div>
    </div>

    {{-- Success/Error berichten --}}
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-500 text-green-800 rounded flex items-center justify-between">
            <span>✅ {{ session('success') }}</span>
            <button onclick="this.parentElement.remove()" class="text-green-600 hover:text-green-800">&times;</button>
        </div>
    @endif

    {{-- Medewerkers Tabel --}}
    <div class="bg-white rounded-lg shadow">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Medewerker</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Diploma Bonus</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ervaring Bonus</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Anciënniteit Bonus</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Totale Bonus</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acties</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($medewerkers as $medewerker)
                        @php
                            $factoren = $medewerker->commissieFactoren->first();
                            $totaleBonus = $factoren ? $factoren->totale_bonus : 0;
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $medewerker->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $medewerker->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm {{ $factoren && $factoren->diploma_factor > 0 ? 'text-green-600 font-semibold' : 'text-gray-400' }}">
                                    +{{ $factoren ? number_format($factoren->diploma_factor, 1) : '0.0' }}%
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm {{ $factoren && $factoren->ervaring_factor > 0 ? 'text-green-600 font-semibold' : 'text-gray-400' }}">
                                    +{{ $factoren ? number_format($factoren->ervaring_factor, 1) : '0.0' }}%
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm {{ $factoren && $factoren->ancienniteit_factor > 0 ? 'text-green-600 font-semibold' : 'text-gray-400' }}">
                                    +{{ $factoren ? number_format($factoren->ancienniteit_factor, 1) : '0.0' }}%
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1 inline-flex text-sm leading-5 font-bold rounded-full 
                                    {{ $totaleBonus > 0 ? 'bg-green-100 text-green-800' : ($totaleBonus < 0 ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-600') }}">
                                    {{ $totaleBonus > 0 ? '+' : '' }}{{ number_format($totaleBonus, 1) }}%
                                </span>
                                @if($factoren)
                                    <div class="text-xs text-gray-500 mt-1">
                                        {{ $factoren->bonus_richting === 'plus' ? '→ Naar medewerker' : '→ Naar organisatie' }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <a href="{{ route('admin.medewerkers.commissies.edit', $medewerker) }}" 
                                   class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white hover:opacity-90 transition"
                                   style="background-color: #c8e1eb; color: #111;">
                                    Bewerken
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                    <p class="text-gray-500">Geen medewerkers gevonden</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
