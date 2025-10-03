@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-md p-6">
        <h1 class="text-3xl font-bold text-black mb-6">Admin Pagina</h1>
        
        <!-- Import Knoppen -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
                        <h2 class="text-xl font-semibold text-black mb-4">📊 Data Importeren</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <a href="/import/klanten" 
                   class="flex items-center justify-center text-gray-800 font-bold py-3 px-6 rounded-lg transition duration-200"
                   style="background-color: #c8e1eb !important; color: #1f2937 !important;">
                    👥 Klanten Toevoegen
                </a>
                <a href="/import/bikefits" 
                   class="flex items-center justify-center text-gray-800 font-bold py-3 px-6 rounded-lg transition duration-200"
                   style="background-color: #c8e1eb !important; color: #1f2937 !important;">
                    🚴 Bikefits Toevoegen
                </a>
            </div>
            <p class="text-sm text-black mt-3">
                Upload Excel bestanden om grote hoeveelheden klanten en bikefits in één keer toe te voegen.
            </p>
        </div>

        <!-- Export Knoppen -->
        <div class="bg-green-50 border border-green-200 rounded-lg p-6 mb-8">
            <h2 class="text-xl font-semibold text-black mb-4">📤 Data Exporteren</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <a href="/export/klanten" 
                   class="flex items-center justify-center text-gray-800 font-bold py-3 px-6 rounded-lg transition duration-200"
                   style="background-color: #c8e1eb !important; color: #1f2937 !important;">
                    📥 Download Alle Klanten
                </a>
                <a href="/export/bikefits" 
                   class="flex items-center justify-center text-gray-800 font-bold py-3 px-6 rounded-lg transition duration-200"
                   style="background-color: #c8e1eb !important; color: #1f2937 !important;">
                    📊 Download Alle Bikefits
                </a>
            </div>
            <p class="text-sm text-black mt-3">
                Download alle data uit de database als Excel bestanden voor backup of analyse.
            </p>
        </div>

        <!-- Staff Notes Overzicht -->
        <h2 class="text-2xl font-semibold text-black mb-4">Staff Notes Overzicht</h2>
        <div class="bg-white shadow overflow-hidden sm:rounded-md">
            <ul class="divide-y divide-gray-200">
                @forelse($notes as $note)
                    <li class="px-6 py-4">
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0">
                                @if($note->type === 'task')
                                    <div class="w-3 h-3 bg-blue-500 rounded-full mt-2"></div>
                                @else
                                    <div class="w-3 h-3 bg-gray-400 rounded-full mt-2"></div>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0 pl-6">
                                <div class="text-sm text-gray-900">
                                    {!! $note->content !!}
                                </div>
                                <div class="mt-2 text-xs text-gray-500">
                                    Door: {{ $note->user->name }} • 
                                    {{ $note->created_at->diffForHumans() }} •
                                    Type: {{ $note->type === 'task' ? 'Taak' : 'Notitie' }}
                                    @if($note->status)
                                        • Status: {{ ucfirst($note->status) }}
                                    @endif
                                </div>
                            </div>
                        </div>
                    </li>
                @empty
                    <li class="px-6 py-4 text-center text-gray-500">
                        Geen staff notes gevonden.
                    </li>
                @endforelse
            </ul>
        </div>

        <div class="mt-6">
            {{ $notes->links() }}
        </div>
    </div>
</div>
@endsection