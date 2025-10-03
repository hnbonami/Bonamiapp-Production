@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Nieuwe Email Template</h1>
            <p class="text-gray-600 mt-1">Maak een nieuwe email template voor automatische of handmatige verzending</p>
        </div>
        <a href="{{ route('admin.email.index') }}" 
           class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Terug naar Email Beheer
        </a>
    </div>

    <!-- Form -->
    <div class="bg-white shadow rounded-lg">
        <form method="POST" action="{{ route('admin.email.templates.store') }}" class="p-6">
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

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Template Details -->
                <div class="space-y-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Template Naam <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required
                               placeholder="bijv. Testzadel Herinnering"
                               class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Beschrijving</label>
                        <textarea name="description" id="description" rows="3" 
                                  placeholder="Beschrijf waarvoor deze template gebruikt wordt..."
                                  class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">{{ old('description') }}</textarea>
                    </div>

                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                            Template Type <span class="text-red-500">*</span>
                        </label>
                        <select name="type" id="type" required
                                class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Selecteer type...</option>
                            <option value="testzadel_reminder" {{ old('type') == 'testzadel_reminder' ? 'selected' : '' }}>Testzadel Herinnering</option>
                            <option value="welcome_customer" {{ old('type') == 'welcome_customer' ? 'selected' : '' }}>Welkom Nieuwe Klant</option>
                            <option value="welcome_employee" {{ old('type') == 'welcome_employee' ? 'selected' : '' }}>Welkom Nieuwe Medewerker</option>
                            <option value="birthday" {{ old('type') == 'birthday' ? 'selected' : '' }}>Verjaardag</option>
                            <option value="bikefit_confirmation" {{ old('type') == 'bikefit_confirmation' ? 'selected' : '' }}>Bikefit Bevestiging</option>
                            <option value="bikefit_reminder" {{ old('type') == 'bikefit_reminder' ? 'selected' : '' }}>Bikefit Herinnering</option>
                            <option value="newsletter" {{ old('type') == 'newsletter' ? 'selected' : '' }}>Nieuwsbrief</option>
                            <option value="custom" {{ old('type') == 'custom' ? 'selected' : '' }}>Aangepast</option>
                        </select>
                        @error('type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">
                            Onderwerp <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="subject" id="subject" value="{{ old('subject') }}" required
                               placeholder="bijv. Herinnering: Testzadel terugbrengen"
                               class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        @error('subject')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" name="is_active" id="is_active" value="1" 
                               {{ old('is_active', true) ? 'checked' : '' }}
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
                            <h4 class="font-medium text-gray-900">Klant Variabelen:</h4>
                            <div class="mt-1 space-y-1 text-gray-600">
                                <div><code class="bg-white px-2 py-1 rounded">{{voornaam}}</code> - Voornaam van de klant</div>
                                <div><code class="bg-white px-2 py-1 rounded">{{naam}}</code> - Achternaam van de klant</div>
                                <div><code class="bg-white px-2 py-1 rounded">{{email}}</code> - Email adres</div>
                                <div><code class="bg-white px-2 py-1 rounded">{{telefoon}}</code> - Telefoonnummer</div>
                            </div>
                        </div>
                        
                        <div>
                            <h4 class="font-medium text-gray-900">Testzadel Variabelen:</h4>
                            <div class="mt-1 space-y-1 text-gray-600">
                                <div><code class="bg-white px-2 py-1 rounded">{{zadel_merk}}</code> - Merk van de testzadel</div>
                                <div><code class="bg-white px-2 py-1 rounded">{{zadel_model}}</code> - Model van de testzadel</div>
                                <div><code class="bg-white px-2 py-1 rounded">{{uitgeleend_op}}</code> - Uitleen datum</div>
                                <div><code class="bg-white px-2 py-1 rounded">{{verwachte_retour}}</code> - Verwachte retour datum</div>
                            </div>
                        </div>
                        
                        <div>
                            <h4 class="font-medium text-gray-900">Algemene Variabelen:</h4>
                            <div class="mt-1 space-y-1 text-gray-600">
                                <div><code class="bg-white px-2 py-1 rounded">{{bedrijf_naam}}</code> - Naam van je bedrijf</div>
                                <div><code class="bg-white px-2 py-1 rounded">{{datum}}</code> - Huidige datum</div>
                                <div><code class="bg-white px-2 py-1 rounded">{{jaar}}</code> - Huidig jaar</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Email Content -->
            <div class="mt-8 border-t border-gray-200 pt-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Email Inhoud</h3>
                
                <div class="space-y-6">
                    <div>
                        <label for="body_html" class="block text-sm font-medium text-gray-700 mb-2">
                            HTML Inhoud <span class="text-red-500">*</span>
                        </label>
                        <textarea name="body_html" id="body_html" rows="15" required
                                  placeholder="HTML inhoud van de email..."
                                  class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 font-mono text-sm">{{ old('body_html') }}</textarea>
                        @error('body_html')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="body_text" class="block text-sm font-medium text-gray-700 mb-2">Platte Tekst Versie</label>
                        <textarea name="body_text" id="body_text" rows="10"
                                  placeholder="Platte tekst versie (optioneel, wordt automatisch gegenereerd als leeg gelaten)"
                                  class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">{{ old('body_text') }}</textarea>
                        <p class="mt-1 text-sm text-gray-500">Als je dit leeg laat, wordt automatisch een platte tekst versie gegenereerd van de HTML inhoud.</p>
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="mt-8 flex justify-end space-x-3 border-t border-gray-200 pt-6">
                <a href="{{ route('admin.email.index') }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    Annuleren
                </a>
                <button type="button" onclick="previewTemplate()"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
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