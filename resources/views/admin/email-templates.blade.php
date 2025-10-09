@extends('layouts.app')

@section('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">📧 Email Templates</h1>
            <p class="text-gray-600 mt-2">Beheer alle email templates op één plek</p>
        </div>
        <a href="{{ route('admin.email.index') }}" 
           class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Terug naar Email Beheer
        </a>
    </div>

    <!-- Current Templates -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        @forelse($templates as $template)
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    @if($template->type === 'testzadel_reminder')
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                    @elseif($template->type === 'welcome_customer')
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    @elseif($template->type === 'birthday')
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 15.546c-.523 0-1.046.151-1.5.454a2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0A1.5 1.5 0 013 15.546V12a1.5 1.5 0 011.5-1.5h15A1.5 1.5 0 0121 12v3.546z"/>
                                    @else
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    @endif
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">{{ $template->name }}</h3>
                                <p class="text-sm text-gray-500">{{ $template->description }}</p>
                            </div>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $template->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ $template->is_active ? 'Actief' : 'Inactief' }}
                        </span>
                    </div>
                    
                    <div class="border border-gray-200 rounded-md p-4 bg-gray-50 mb-4">
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Onderwerp:</h4>
                        <p class="text-sm text-gray-900">{{ $template->subject }}</p>
                        
                        <h4 class="text-sm font-medium text-gray-700 mb-2 mt-3">Inhoud preview:</h4>
                        <div class="text-sm text-gray-600 prose-sm max-w-none">
                            {!! Str::limit(strip_tags($template->body_html), 150) !!}
                        </div>
                    </div>
                    
                    <div class="flex space-x-2">
                        <a href="{{ route('admin.email.templates.edit', $template->id) }}" 
                           class="flex-1 px-4 py-2 rounded-md text-sm font-medium text-center text-gray-800 hover:opacity-80" 
                           style="background-color: #c8e1eb;">
                            Bewerken
                        </a>
                        <button onclick="deleteTemplate({{ $template->id }}, '{{ $template->name }}')" 
                                class="px-3 py-2 border border-red-300 rounded-md text-sm font-medium text-red-700 hover:bg-red-50">
                            🗑️
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-2 text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Geen templates gevonden</h3>
                <p class="mt-1 text-sm text-gray-500">Begin met het maken van je eerste email template.</p>
            </div>
        @endforelse

        <!-- Add New Template Card -->
        <div class="bg-white shadow rounded-lg overflow-hidden border-2 border-dashed border-gray-200 hover:border-gray-300 transition-colors">
            <div class="p-6 text-center">
                <div class="w-16 h-16 bg-gray-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Nieuwe Template</h3>
                <p class="text-sm text-gray-500 mb-4">Maak een nieuwe email template voor automatische of handmatige verzending</p>
                <a href="{{ route('admin.email.templates.create') }}" 
                   class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700">
                    Template Maken
                </a>
            </div>
        </div>
    </div>

    <!-- Template Variables Guide -->
    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">📝 Beschikbare Template Variabelen</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <h4 class="font-medium text-gray-900 mb-3">Klant Gegevens</h4>
                <div class="space-y-2">
                    <div class="flex items-center text-sm">
                        <code class="bg-gray-100 px-2 py-1 rounded text-xs font-mono mr-2">@{{voornaam}}</code>
                        <span class="text-gray-600">Voornaam</span>
                    </div>
                    <div class="flex items-center text-sm">
                        <code class="bg-gray-100 px-2 py-1 rounded text-xs font-mono mr-2">@{{naam}}</code>
                        <span class="text-gray-600">Achternaam</span>
                    </div>
                    <div class="flex items-center text-sm">
                        <code class="bg-gray-100 px-2 py-1 rounded text-xs font-mono mr-2">@{{email}}</code>
                        <span class="text-gray-600">Email adres</span>
                    </div>
                </div>
            </div>
            
            <div>
                <h4 class="font-medium text-gray-900 mb-3">Testzadel Info</h4>
                <div class="space-y-2">
                    <div class="flex items-center text-sm">
                        <code class="bg-gray-100 px-2 py-1 rounded text-xs font-mono mr-2">@{{merk}}</code>
                        <span class="text-gray-600">Zadel merk</span>
                    </div>
                    <div class="flex items-center text-sm">
                        <code class="bg-gray-100 px-2 py-1 rounded text-xs font-mono mr-2">@{{model}}</code>
                        <span class="text-gray-600">Zadel model</span>
                    </div>
                    <div class="flex items-center text-sm">
                        <code class="bg-gray-100 px-2 py-1 rounded text-xs font-mono mr-2">@{{uitgeleend_op}}</code>
                        <span class="text-gray-600">Uitleen datum</span>
                    </div>
                </div>
            </div>
            
            <div>
                <h4 class="font-medium text-gray-900 mb-3">Algemeen</h4>
                <div class="space-y-2">
                    <div class="flex items-center text-sm">
                        <code class="bg-gray-100 px-2 py-1 rounded text-xs font-mono mr-2">@{{bedrijf_naam}}</code>
                        <span class="text-gray-600">Bedrijfsnaam</span>
                    </div>
                    <div class="flex items-center text-sm">
                        <code class="bg-gray-100 px-2 py-1 rounded text-xs font-mono mr-2">@{{datum}}</code>
                        <span class="text-gray-600">Huidige datum</span>
                    </div>
                    <div class="flex items-center text-sm">
                        <code class="bg-gray-100 px-2 py-1 rounded text-xs font-mono mr-2">@{{jaar}}</code>
                        <span class="text-gray-600">Huidig jaar</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Step Progress -->
    <div class="mt-8 bg-blue-50 border border-blue-200 rounded-md p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">Development Status</h3>
                <p class="mt-1 text-sm text-blue-700">
                    <strong>STAP 2 COMPLEET:</strong> Email templates overzicht geladen. 
                    Huidige templates gevisualiseerd. Klaar voor stap 3: Bewerk functionaliteit!
                </p>
            </div>
        </div>
    </div>
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