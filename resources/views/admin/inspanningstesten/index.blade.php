@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">⚙️ Inspanningstesten Instellingen</h1>
            <p class="mt-2 text-gray-600">Beheer trainingszones templates voor jouw organisatie</p>
        </div>

        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
                {{ session('error') }}
            </div>
        @endif

        <!-- Nieuwe Template Knop -->
        <div class="mb-6">
            <a href="{{ route('admin.inspanningstesten.create') }}" 
               class="inline-flex items-center px-4 py-2 font-medium rounded-lg transition"
               style="background-color: #c8e1eb; color: #1e3a8a;"
               onmouseover="this.style.backgroundColor='#b0d4e0'" 
               onmouseout="this.style.backgroundColor='#c8e1eb'">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nieuwe Zone Template
            </a>
        </div>

        <!-- Templates Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($templates as $template)
                <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition">
                    <!-- Template Header -->
                    <div class="p-6 bg-gradient-to-br from-blue-50 to-indigo-50">
                        <div class="flex items-start justify-between">
                            <div>
                                <h3 class="text-xl font-bold text-gray-900">{{ $template->naam }}</h3>
                                @if($template->is_systeem)
                                    <span class="inline-block mt-1 px-2 py-1 text-xs font-semibold bg-blue-100 text-blue-800 rounded">
                                        Systeem
                                    </span>
                                @else
                                    <span class="inline-block mt-1 px-2 py-1 text-xs font-semibold bg-green-100 text-green-800 rounded">
                                        Custom
                                    </span>
                                @endif
                            </div>
                            <div class="text-right">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                                    @if($template->sport_type === 'fietsen') bg-purple-100 text-purple-800
                                    @elseif($template->sport_type === 'lopen') bg-orange-100 text-orange-800
                                    @else bg-teal-100 text-teal-800
                                    @endif">
                                    @if($template->sport_type === 'fietsen') 🚴 Fietsen
                                    @elseif($template->sport_type === 'lopen') 🏃 Lopen
                                    @else 🏆 Beide
                                    @endif
                                </span>
                            </div>
                        </div>
                        
                        @if($template->beschrijving)
                            <p class="mt-2 text-sm text-gray-600">{{ $template->beschrijving }}</p>
                        @endif
                    </div>

                    <!-- Zones Preview -->
                    <div class="px-6 py-4">
                        <p class="text-sm font-medium text-gray-700 mb-3">
                            {{ $template->zones->count() }} zones • Basis: 
                            <span class="font-semibold">{{ strtoupper($template->berekening_basis) }}</span>
                        </p>
                        
                        <!-- Zones Visual Bar -->
                        <div class="flex h-8 rounded-lg overflow-hidden border border-gray-200 mb-4">
                            @foreach($template->zones as $zone)
                                <div style="background-color: {{ $zone->kleur }}; flex: 1;" 
                                     title="{{ $zone->zone_naam }}: {{ $zone->min_percentage }}-{{ $zone->max_percentage }}%"
                                     class="hover:opacity-80 transition">
                                </div>
                            @endforeach
                        </div>

                        <!-- Zone Names -->
                        <div class="space-y-1">
                            @foreach($template->zones->take(3) as $zone)
                                <div class="flex items-center text-xs">
                                    <div class="w-3 h-3 rounded-full mr-2" style="background-color: {{ $zone->kleur }}"></div>
                                    <span class="text-gray-700">{{ $zone->zone_naam }}</span>
                                    <span class="ml-auto text-gray-500">{{ $zone->min_percentage }}-{{ $zone->max_percentage }}%</span>
                                </div>
                            @endforeach
                            @if($template->zones->count() > 3)
                                <p class="text-xs text-gray-500 italic">+{{ $template->zones->count() - 3 }} meer...</p>
                            @endif
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex gap-2">
                        @if(!$template->is_systeem)
                            <a href="{{ route('admin.inspanningstesten.edit', $template->id) }}" 
                               class="flex-1 text-center px-3 py-2 text-sm font-medium rounded-lg transition"
                               style="background-color: #c8e1eb; color: #1e3a8a;"
                               onmouseover="this.style.backgroundColor='#b0d4e0'" 
                               onmouseout="this.style.backgroundColor='#c8e1eb'">
                                ✏️ Bewerken
                            </a>
                            <form action="{{ route('admin.inspanningstesten.destroy', $template->id) }}" 
                                  method="POST" 
                                  onsubmit="return confirm('Weet je zeker dat je deze template wilt verwijderen?');"
                                  class="flex-1">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="w-full px-3 py-2 bg-red-50 border border-red-200 text-red-700 text-sm font-medium rounded-lg hover:bg-red-100 transition">
                                    🗑️ Verwijderen
                                </button>
                            </form>
                        @else
                            <div class="flex-1 text-center px-3 py-2 bg-gray-100 text-gray-500 text-sm font-medium rounded-lg cursor-not-allowed">
                                🔒 Systeem Template
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-full">
                    <div class="text-center py-12 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Geen zone templates</h3>
                        <p class="mt-1 text-sm text-gray-500">Begin met het maken van je eerste trainingszones template</p>
                        <div class="mt-6">
                            <a href="{{ route('admin.inspanningstesten.create') }}" 
                               class="inline-flex items-center px-4 py-2 font-medium rounded-lg transition"
                               style="background-color: #c8e1eb; color: #1e3a8a;"
                               onmouseover="this.style.backgroundColor='#b0d4e0'" 
                               onmouseout="this.style.backgroundColor='#c8e1eb'">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Maak Eerste Template
                            </a>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
