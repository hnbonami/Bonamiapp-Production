@extends('layouts.app')

@section('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Email Templates</h1>
                <p class="text-gray-600 mt-2">Beheer je email templates voor automatische communicatie</p>
            </div>
            <a href="/admin" 
               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Terug naar Dashboard
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    @if(session('warning'))
        <div class="mb-6 bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded-lg">
            {{ session('warning') }}
        </div>
    @endif

    {{-- Check of organisatie templates moet initialiseren --}}
    @if(isset($needsCloning) && $needsCloning && auth()->user()->role !== 'superadmin')
        <div class="bg-white shadow rounded-lg p-8 text-center">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-blue-100 mb-4">
                <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
            
            <h3 class="text-xl font-semibold text-gray-900 mb-2">Email Templates Initialiseren</h3>
            <p class="text-gray-600 mb-6 max-w-2xl mx-auto">
                Je organisatie heeft nog geen email templates. Klik op de knop hieronder om automatisch 
                <strong>6 standaard email templates</strong> aan te maken die je daarna naar wens kunt aanpassen.
            </p>
            
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6 max-w-2xl mx-auto">
                <h4 class="font-semibold text-blue-900 mb-2">📧 Templates die worden aangemaakt:</h4>
                <ul class="text-left text-blue-800 space-y-1">
                    <li>✅ Testzadel Herinnering</li>
                    <li>✅ Welkom Nieuwe Klant</li>
                    <li>✅ Welkom Nieuwe Medewerker</li>
                    <li>✅ Verjaardag Felicitatie</li>
                    <li>✅ Klant Uitnodiging</li>
                    <li>✅ Medewerker Uitnodiging</li>
                </ul>
            </div>
            
            <form action="{{ route('admin.email.templates.initialize') }}" method="POST" class="inline-block">
                @csrf
                <button type="submit" 
                        class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Initialiseer Email Templates
                </button>
            </form>
            
            <p class="text-sm text-gray-500 mt-4">
                Dit duurt slechts een paar seconden en je kunt de templates daarna direct aanpassen.
            </p>
        </div>
    @else
        {{-- Templates lijst --}}
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-medium text-gray-900">
                        @if(auth()->user()->role === 'superadmin')
                            Performance Pulse Standaard Templates
                        @else
                            Jouw Email Templates
                        @endif
                    </h3>
                    <p class="text-sm text-gray-500 mt-1">
                        @if(auth()->user()->role === 'superadmin')
                            Deze templates worden gebruikt als standaard voor alle organisaties
                        @else
                            Pas deze templates aan naar de huisstijl van jouw organisatie
                        @endif
                    </p>
                </div>
                
                <a href="{{ route('admin.email.templates.create') }}" 
                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-gray-900 hover:text-gray-700"
                   style="background-color: #c8e1eb;">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Nieuwe Template
                </a>
            </div>

            @if($templates->isEmpty())
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Geen email templates</h3>
                    <p class="mt-1 text-sm text-gray-500">Klik op "Initialiseer Email Templates" om te beginnen.</p>
                </div>
            @else
                <div class="divide-y divide-gray-200">
                    @foreach($templates as $template)
                        <div class="px-6 py-4 hover:bg-gray-50 transition-colors">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-3">
                                        <h4 class="text-base font-medium text-gray-900">
                                            {{ $template->name }}
                                        </h4>
                                        
                                        @if($template->isDefaultTemplate())
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                Performance Pulse
                                            </span>
                                        @endif
                                        
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                            {{ $template->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                            {{ $template->is_active ? 'Actief' : 'Inactief' }}
                                        </span>
                                    </div>
                                    
                                    @if($template->description)
                                        <p class="mt-1 text-sm text-gray-500">{{ $template->description }}</p>
                                    @endif
                                    
                                    <div class="mt-2 flex items-center space-x-4 text-xs text-gray-500">
                                        <span>Type: <strong>{{ ucfirst($template->type) }}</strong></span>
                                        <span>•</span>
                                        <span>Onderwerp: {{ \Str::limit($template->subject, 50) }}</span>
                                    </div>
                                </div>
                                
                                <div class="ml-6 flex items-center space-x-2">
                                    <a href="{{ route('admin.email.templates.edit', $template->id) }}" 
                                       class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-gray-900 hover:text-gray-700"
                                       style="background-color: #c8e1eb;">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Bewerken
                                    </a>
                                    
                                    <form action="{{ route('admin.email.templates.destroy', $template->id) }}" 
                                          method="POST" 
                                          class="inline-block"
                                          onsubmit="return confirm('Weet je zeker dat je deze template wilt verwijderen?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="inline-flex items-center px-3 py-2 border border-red-300 text-sm font-medium rounded-md text-red-700 bg-white hover:bg-red-50">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                            Verwijderen
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
        
        @if(auth()->user()->role === 'superadmin' && $templates->isNotEmpty())
            <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800">Let op: Superadmin Templates</h3>
                        <div class="mt-2 text-sm text-yellow-700">
                            <p>Wijzigingen aan deze templates zijn zichtbaar voor alle organisaties die nog geen eigen versie hebben aangemaakt.</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>

<script>
function deleteTemplate(templateId, templateName) {
    if (confirm(`Weet je zeker dat je template "${templateName}" wilt verwijderen? Dit kan niet ongedaan worden gemaakt.`)) {
        // Create a form for DELETE request
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/email/templates/${templateId}`;
        form.style.display = 'none';
        
        // Add CSRF token
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        form.appendChild(csrfInput);
        
        // Add method override for DELETE
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        form.appendChild(methodInput);
        
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endsection