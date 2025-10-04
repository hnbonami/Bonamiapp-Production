@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-2xl mx-auto">
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-lg font-medium text-yellow-800">
                        Database Setup Vereist
                    </h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <p>De testzadels functionaliteit is nog niet geïnstalleerd. Voer de volgende stappen uit:</p>
                    </div>
                </div>
            </div>
            
            <div class="mt-6">
                <div class="bg-white rounded-md p-4 border border-yellow-200">
                    <h4 class="text-sm font-medium text-gray-900 mb-3">Installatiestappen:</h4>
                    <ol class="list-decimal list-inside space-y-2 text-sm text-gray-700">
                        <li>Open een terminal in je project directory</li>
                        <li>Voer het volgende commando uit: <code class="bg-gray-100 px-2 py-1 rounded">php artisan migrate</code></li>
                        <li>Herlaad deze pagina</li>
                    </ol>
                </div>
                
                <div class="mt-4 flex justify-between">
                    <a href="{{ route('dashboard') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        Terug naar Dashboard
                    </a>
                    
                    <button onclick="location.reload()" 
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Herlaad Pagina
                    </button>
                </div>
            </div>
        </div>
        
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">
                        Over de Testzadels Functionaliteit
                    </h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <p>Na installatie kun je:</p>
                        <ul class="list-disc list-inside mt-1 space-y-1">
                            <li>Testzadel uitleningen beheren</li>
                            <li>Automatische koppelingen met bikefits</li>
                            <li>Herinneringen versturen</li>
                            <li>Archief functionaliteit</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection