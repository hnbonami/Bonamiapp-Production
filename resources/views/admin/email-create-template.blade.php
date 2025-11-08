@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">🎨 Nieuwe Email Template</h1>
            <p class="text-gray-600 mt-1">Maak een moderne, professionele email template</p>
        </div>
        <a href="{{ route('admin.email.templates') }}" 
           class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Terug naar Templates
        </a>
    </div>

    <!-- Form -->
    <div class="bg-white shadow rounded-lg">
        <form action="{{ route('admin.email.templates.store') }}" method="POST" class="p-6">
            @csrf
            
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

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Template Settings -->
                <div class="lg:col-span-1 space-y-6">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Template Instellingen</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                    Template Naam <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                       class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                                    Template Type <span class="text-red-500">*</span>
                                </label>
                                <select name="type" id="type" required
                                        class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Selecteer type...</option>
                                    @foreach($templateTypes as $value => $label)
                                        <option value="{{ $value }}" {{ old('type') == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('type')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">
                                    Email Onderwerp <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="subject" id="subject" value="{{ old('subject') }}" required
                                       class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                @error('subject')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Beschrijving</label>
                                <textarea name="description" id="description" rows="3" 
                                          class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">{{ old('description') }}</textarea>
                            </div>

                            <div class="flex items-center">
                                <input type="checkbox" name="is_active" id="is_active" value="1" 
                                       {{ old('is_active', true) ? 'checked' : '' }}
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="is_active" class="ml-2 block text-sm text-gray-900">
                                    Template direct activeren
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Templates -->
                    <div>
                        <h4 class="font-medium text-gray-900 mb-3">🚀 Snelle Start Templates</h4>
                        <div class="space-y-2">
                            <button type="button" onclick="loadQuickTemplate('testzadel')"
                                    class="w-full text-left px-3 py-2 border border-gray-200 rounded-md hover:bg-gray-50 text-sm">
                                📧 Testzadel Herinnering
                            </button>
                            <button type="button" onclick="loadQuickTemplate('welcome')"
                                    class="w-full text-left px-3 py-2 border border-gray-200 rounded-md hover:bg-gray-50 text-sm">
                                🎉 Welkom Template
                            </button>
                            <button type="button" onclick="loadQuickTemplate('birthday')"
                                    class="w-full text-left px-3 py-2 border border-gray-200 rounded-md hover:bg-gray-50 text-sm">
                                🎂 Verjaardag Template
                            </button>
                            <button type="button" onclick="loadQuickTemplate('modern')"
                                    class="w-full text-left px-3 py-2 border border-gray-200 rounded-md hover:bg-gray-50 text-sm">
                                ✨ Modern Design
                            </button>
                        </div>
                    </div>

                    <!-- Variables Guide -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-medium text-gray-900 mb-3">🔧 Beschikbare Variabelen</h4>
                        <div class="space-y-2 text-sm">
                            <div><code class="bg-white px-2 py-1 rounded">@{{voornaam}}</code> - Voornaam</div>
                            <div><code class="bg-white px-2 py-1 rounded">@{{naam}}</code> - Achternaam</div>
                            <div><code class="bg-white px-2 py-1 rounded">@{{email}}</code> - Email adres</div>
                            <div><code class="bg-white px-2 py-1 rounded">@{{bedrijf_naam}}</code> - Bedrijfsnaam</div>
                            <div><code class="bg-white px-2 py-1 rounded">@{{jaar}}</code> - Huidig jaar</div>
                        </div>
                    </div>
                </div>

                <!-- Email Content Editor -->
                <div class="lg:col-span-2">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Email Inhoud Designer</h3>
                    
                    <div class="mb-4 flex space-x-2">
                        <button type="button" onclick="toggleEditor('visual')" id="visual-tab"
                                class="px-4 py-2 text-sm font-medium bg-blue-600 text-white rounded-l-md">
                            Visual Editor
                        </button>
                        <button type="button" onclick="toggleEditor('html')" id="html-tab"
                                class="px-4 py-2 text-sm font-medium bg-gray-200 text-gray-700 rounded-r-md">
                            HTML Code
                        </button>
                    </div>

                    <!-- Visual Editor -->
                    <div id="visual-editor">
                        <div class="border border-gray-300 rounded-md p-4 bg-gray-50 mb-4">
                            <h4 class="font-medium text-gray-900 mb-2">Live Preview</h4>
                            <div id="email-preview" class="bg-white border rounded max-w-md mx-auto" style="max-height: 400px; overflow-y: auto;">
                                <!-- Preview wordt hier geladen -->
                            </div>
                        </div>
                    </div>

                    <!-- HTML Editor -->
                    <div id="html-editor" style="display: none;">
                        <textarea name="body_html" id="body_html" rows="25" required
                                  class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 font-mono text-sm">{{ old('body_html', $defaultTemplate) }}</textarea>
                        @error('body_html')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="mt-8 flex justify-end space-x-3 border-t border-gray-200 pt-6">
                <a href="{{ route('admin.email.templates') }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    Annuleren
                </a>
                <button type="button" onclick="previewInNewWindow()"
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
</div>

<script>
// Tab switching
function toggleEditor(mode) {
    const visualEditor = document.getElementById('visual-editor');
    const htmlEditor = document.getElementById('html-editor');
    const visualTab = document.getElementById('visual-tab');
    const htmlTab = document.getElementById('html-tab');
    
    if (mode === 'visual') {
        visualEditor.style.display = 'block';
        htmlEditor.style.display = 'none';
        visualTab.className = 'px-4 py-2 text-sm font-medium bg-blue-600 text-white rounded-l-md';
        htmlTab.className = 'px-4 py-2 text-sm font-medium bg-gray-200 text-gray-700 rounded-r-md';
        updatePreview();
    } else {
        visualEditor.style.display = 'none';
        htmlEditor.style.display = 'block';
        htmlTab.className = 'px-4 py-2 text-sm font-medium bg-blue-600 text-white rounded-r-md';
        visualTab.className = 'px-4 py-2 text-sm font-medium bg-gray-200 text-gray-700 rounded-l-md';
    }
}

// Update preview
function updatePreview() {
    const html = document.getElementById('body_html').value;
    const preview = document.getElementById('email-preview');
    
    // Replace variables with demo data
    let demoHtml = html
        .replace(/@\{\{voornaam\}\}/g, 'Jan')
        .replace(/@\{\{naam\}\}/g, 'Janssen')
        .replace(/@\{\{email\}\}/g, 'jan@voorbeeld.nl')
        .replace(/@\{\{bedrijf_naam\}\}/g, 'Bonami Cycling')
        .replace(/@\{\{merk\}\}/g, 'Selle Italia')
        .replace(/@\{\{model\}\}/g, 'SLR Boost')
        .replace(/@\{\{jaar\}\}/g, new Date().getFullYear());
    
    preview.innerHTML = demoHtml;
}

// Quick templates
function loadQuickTemplate(type) {
    const htmlEditor = document.getElementById('body_html');
    let template = '';
    
    if (type === 'testzadel') {
        template = `<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; }
        .header { background: linear-gradient(135deg, #FF6B6B 0%, #4ECDC4 100%); padding: 40px 30px; text-align: center; }
        .header h1 { color: #ffffff; margin: 0; font-size: 28px; }
        .content { padding: 40px 30px; }
        .highlight { background: #FFF3CD; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .button { display: inline-block; padding: 12px 24px; background-color: #FF6B6B; color: #ffffff; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚴‍♂️ @{{bedrijf_naam}}</h1>
        </div>
        <div class="content">
            <h2>Beste @{{voornaam}},</h2>
            <p>Je hebt een testzadel <strong>@{{merk}} @{{model}}</strong> uitgeleend.</p>
            <div class="highlight">
                <strong>⏰ Herinnering:</strong> Kun je de testzadel binnenkort terugbrengen?
            </div>
            <p>Bedankt voor je medewerking!</p>
        </div>
    </div>
</body>
</html>`;
    } else if (type === 'modern') {
        template = `<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { margin: 0; padding: 0; font-family: 'Segoe UI', Arial, sans-serif; background-color: #f8f9fa; }
        .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 10px; overflow: hidden; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 40px 30px; text-align: center; }
        .header h1 { color: #ffffff; margin: 0; font-size: 32px; font-weight: 300; }
        .content { padding: 40px 30px; }
        .card { background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0; }
        .button { display: inline-block; padding: 15px 30px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #ffffff; text-decoration: none; border-radius: 25px; }
        .footer { background-color: #f8f9fa; padding: 30px; text-align: center; color: #6c757d; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✨ @{{bedrijf_naam}}</h1>
        </div>
        <div class="content">
            <h2>Hallo @{{voornaam}}!</h2>
            <p>We hebben een belangrijke update voor je...</p>
            <div class="card">
                <h3>🎯 Speciaal voor jou</h3>
                <p>Hier komt je persoonlijke bericht.</p>
            </div>
            <a href="#" class="button">Bekijk Details</a>
        </div>
        <div class="footer">
            <p>&copy; @{{jaar}} @{{bedrijf_naam}}. Met ❤️ gemaakt.</p>
        </div>
    </div>
</body>
</html>`;
    }
    
    if (template) {
        htmlEditor.value = template;
        updatePreview();
    }
}

// Preview in new window
function previewInNewWindow() {
    const subject = document.getElementById('subject').value || 'Email Preview';
    const html = document.getElementById('body_html').value;
    
    const previewWindow = window.open('', '_blank', 'width=800,height=600');
    const demoHtml = html
        .replace(/@\{\{voornaam\}\}/g, 'Jan')
        .replace(/@\{\{naam\}\}/g, 'Janssen')
        .replace(/@\{\{bedrijf_naam\}\}/g, 'Bonami Cycling')
        .replace(/@\{\{jaar\}\}/g, new Date().getFullYear());
    
    previewWindow.document.write(`
        <html>
            <head><title>${subject}</title></head>
            <body style="margin: 0; padding: 20px; background: #f4f4f4;">
                ${demoHtml}
            </body>
        </html>
    `);
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    updatePreview();
    
    // Update preview when HTML changes
    document.getElementById('body_html').addEventListener('input', updatePreview);
});
</script>
@endsection