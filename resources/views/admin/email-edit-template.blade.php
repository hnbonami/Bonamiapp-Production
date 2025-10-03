@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">📝 Template Bewerken</h1>
            <p class="text-gray-600 mt-1">{{ $template['name'] }}</p>
        </div>
        <a href="{{ route('admin.email.templates') }}" 
           class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Terug naar Templates
        </a>
    </div>

    <!-- Success Message -->
    @if(session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 rounded-md p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Form -->
    <div class="bg-white shadow rounded-lg">
        <form action="{{ route('admin.email.templates.update', $template['id']) }}" method="POST" class="p-6">
            @csrf
            @method('PUT')
            
            <!-- Error Messages -->
            @if ($errors->any())
                <div class="mb-6 bg-red-50 border border-red-200 rounded-md p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Er zijn fouten opgetreden:</h3>
                            <div class="mt-2 text-sm text-red-700">
                                <ul class="list-disc list-inside space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Template Details -->
                <div class="space-y-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Template Naam <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" id="name" value="{{ old('name', $template['name']) }}" required
                               class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">
                            Onderwerp <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="subject" id="subject" value="{{ old('subject', $template['subject']) }}" required
                               class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        @error('subject')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Beschrijving</label>
                        <textarea name="description" id="description" rows="3" 
                                  class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">{{ old('description', $template['description']) }}</textarea>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" name="is_active" id="is_active" value="1" 
                               {{ old('is_active', $template['is_active']) ? 'checked' : '' }}
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="is_active" class="ml-2 block text-sm text-gray-900">
                            Template actief
                        </label>
                    </div>
                </div>

                <!-- Available Variables -->
                <div class="bg-gray-50 p-6 rounded-lg">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Beschikbare Variabelen</h3>
                    <div class="space-y-3 text-sm">
                        <div>
                            <h4 class="font-medium text-gray-900">Klant:</h4>
                            <div class="mt-1 space-y-1 text-gray-600">
                                <div><code class="bg-white px-2 py-1 rounded">@{{voornaam}}</code></div>
                                <div><code class="bg-white px-2 py-1 rounded">@{{naam}}</code></div>
                                <div><code class="bg-white px-2 py-1 rounded">@{{email}}</code></div>
                            </div>
                        </div>
                        
                        <div>
                            <h4 class="font-medium text-gray-900">Testzadel:</h4>
                            <div class="mt-1 space-y-1 text-gray-600">
                                <div><code class="bg-white px-2 py-1 rounded">@{{merk}}</code></div>
                                <div><code class="bg-white px-2 py-1 rounded">@{{model}}</code></div>
                                <div><code class="bg-white px-2 py-1 rounded">@{{uitgeleend_op}}</code></div>
                                <div><code class="bg-white px-2 py-1 rounded">@{{verwachte_retour}}</code></div>
                            </div>
                        </div>
                        
                        <div>
                            <h4 class="font-medium text-gray-900">Algemeen:</h4>
                            <div class="mt-1 space-y-1 text-gray-600">
                                <div><code class="bg-white px-2 py-1 rounded">@{{bedrijf_naam}}</code></div>
                                <div><code class="bg-white px-2 py-1 rounded">@{{datum}}</code></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Email Content -->
            <div class="mt-8 border-t border-gray-200 pt-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Email Inhoud</h3>
                
                <div>
                    <label for="body_html" class="block text-sm font-medium text-gray-700 mb-2">
                        HTML Inhoud <span class="text-red-500">*</span>
                    </label>
                    <textarea name="body_html" id="body_html" rows="15" required
                              class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 font-mono text-sm">{{ old('body_html', $template['body_html']) }}</textarea>
                    @error('body_html')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-500">Gebruik @{{variabele}} voor dynamische content</p>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="mt-8 flex justify-end space-x-3 border-t border-gray-200 pt-6">
                <a href="{{ route('admin.email.templates') }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    Annuleren
                </a>
                <button type="button" onclick="previewTemplate()"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Preview
                </button>
                <button type="submit" 
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Template Opslaan
                </button>
            </div>
        </form>
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
                    <strong>STAP 3 COMPLEET:</strong> Template bewerken functionaliteit werkend! 
                    Controller geladen, formulier validatie werkend. Klaar voor stap 4!
                </p>
            </div>
        </div>
    </div>
</div>

<script>
function previewTemplate() {
    const subject = document.getElementById('subject').value;
    const bodyHtml = document.getElementById('body_html').value;
    
    if (!subject || !bodyHtml) {
        alert('Vul eerst het onderwerp en de HTML inhoud in.');
        return;
    }
    
    const previewWindow = window.open('', '_blank', 'width=800,height=600');
    previewWindow.document.write(`
        <html>
            <head>
                <title>Email Preview: ${subject}</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 20px; }
                    .email-header { background: #f3f4f6; padding: 10px; margin-bottom: 20px; border-radius: 5px; }
                    .email-content { border: 1px solid #e5e7eb; padding: 20px; border-radius: 5px; }
                </style>
            </head>
            <body>
                <div class="email-header">
                    <strong>Onderwerp:</strong> ${subject}
                </div>
                <div class="email-content">
                    ${bodyHtml}
                </div>
            </body>
        </html>
    `);
}
</script>
@endsection