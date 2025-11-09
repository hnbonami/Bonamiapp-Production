@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Email Branding</h1>
            <p class="text-gray-600 mt-1">Beheer logo, kleuren en branding voor al je emails</p>
        </div>
        <a href="{{ url('/admin') }}" 
           class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Terug naar Dashboard
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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Settings Form -->
        <div class="lg:col-span-2">
            <div class="bg-white shadow rounded-lg">
                <form action="{{ route('admin.email.settings.update') }}" method="POST" enctype="multipart/form-data" class="p-6">
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

                    <!-- Bedrijfsinformatie voor Emails -->
                    <div class="mb-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
                        <h3 class="text-lg font-medium text-blue-900 mb-2">📧 Bedrijfsinformatie voor Emails</h3>
                        <p class="text-sm text-blue-700 mb-4">Deze informatie wordt gebruikt in email templates via variabelen zoals @{{bedrijf_naam}}, @{{website_url}}, etc.</p>
                        
                        <div class="space-y-4">
                            <div>
                                <label for="bedrijf_naam" class="block text-sm font-medium text-gray-700 mb-2">
                                    Bedrijfsnaam voor Emails <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="bedrijf_naam" id="bedrijf_naam" 
                                       value="{{ old('bedrijf_naam', $organisatie->bedrijf_naam ?? $organisatie->naam) }}" 
                                       placeholder="Bijv. Level Up Cycling"
                                       class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <p class="mt-1 text-sm text-gray-500">Wordt gebruikt als @{{bedrijf_naam}} in email templates</p>
                                @error('bedrijf_naam')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="website_url_email" class="block text-sm font-medium text-gray-700 mb-2">
                                    Website URL
                                </label>
                                <input type="url" name="website_url" id="website_url_email" 
                                       value="{{ old('website_url', $organisatie->website_url) }}" 
                                       placeholder="https://jouwbedrijf.be"
                                       class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <p class="mt-1 text-sm text-gray-500">Wordt gebruikt als @{{website_url}} in email templates</p>
                                @error('website_url')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="email_from_name" class="block text-sm font-medium text-gray-700 mb-2">
                                    Afzender Naam
                                </label>
                                <input type="text" name="email_from_name" id="email_from_name" 
                                       value="{{ old('email_from_name', $organisatie->email_from_name ?? $organisatie->naam) }}" 
                                       placeholder="Bijv. Level Up Cycling Team"
                                       class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <p class="mt-1 text-sm text-gray-500">Deze naam verschijnt als afzender in emails</p>
                                @error('email_from_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="email_from_address" class="block text-sm font-medium text-gray-700 mb-2">
                                    Afzender Email Adres
                                </label>
                                <input type="email" name="email_from_address" id="email_from_address" 
                                       value="{{ old('email_from_address', $organisatie->email_from_address) }}" 
                                       placeholder="info@jouwbedrijf.be"
                                       class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <p class="mt-1 text-sm text-gray-500">Email adres gebruikt als afzender (optioneel)</p>
                                @error('email_from_address')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="email_signature_org" class="block text-sm font-medium text-gray-700 mb-2">
                                    Email Handtekening
                                </label>
                                <textarea name="email_signature" id="email_signature_org" rows="3" 
                                          placeholder="Sportieve groet,&#10;{{ $organisatie->bedrijf_naam ?? $organisatie->naam }} Team"
                                          class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">{{ old('email_signature', $organisatie->email_signature) }}</textarea>
                                <p class="mt-1 text-sm text-gray-500">Standaard handtekening voor alle emails van jouw organisatie</p>
                                @error('email_signature')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Company Branding -->
                    <div class="mb-8">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">🏢 Bedrijf Branding</h3>
                        
                        <div class="space-y-6">
                            <div>
                                <label for="company_name" class="block text-sm font-medium text-gray-700 mb-2">
                                    Bedrijfsnaam <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="company_name" id="company_name" 
                                       value="{{ old('company_name', $organisatie->bedrijf_naam ?? $organisatie->naam ?? $settings->company_name) }}" required
                                       class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                @error('company_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="logo" class="block text-sm font-medium text-gray-700 mb-2">
                                    Bedrijfslogo
                                </label>
                                
                                @if($settings->hasLogo())
                                    <div class="mb-4 p-4 border border-gray-200 rounded-lg bg-gray-50">
                                        <div class="flex items-center space-x-4">
                                            @if($settings->logo_url)
                                                <img src="{{ $settings->logo_url }}" alt="Huidig logo" class="h-16 w-auto" 
                                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                                <div style="display: none;" class="h-16 w-16 bg-gray-200 rounded flex items-center justify-center">
                                                    <span class="text-gray-500 text-xs">Geen logo</span>
                                                </div>
                                            @else
                                                <div class="h-16 w-16 bg-gray-200 rounded flex items-center justify-center">
                                                    <span class="text-gray-500 text-xs">Logo fout</span>
                                                </div>
                                            @endif
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">Huidig logo</p>
                                                <p class="text-sm text-gray-500">Upload een nieuw bestand om te vervangen</p>
                                                @if($settings->logo_path)
                                                    <p class="text-xs text-gray-400">Bestand: {{ $settings->logo_path }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="mb-4 p-4 border border-dashed border-gray-300 rounded-lg bg-gray-50">
                                        <div class="flex items-center space-x-4">
                                            <div class="h-16 w-16 bg-gray-200 rounded flex items-center justify-center">
                                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">Geen logo geüpload</p>
                                                <p class="text-sm text-gray-500">Upload je Bonami Cycling logo hieronder</p>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                
                                <input type="file" name="logo" id="logo" accept="image/*"
                                       class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                <p class="mt-1 text-sm text-gray-500">PNG, JPG, GIF tot 2MB. Voor beste resultaten: 200x80px</p>
                                @error('logo')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Color Scheme -->
                    <div class="mb-8 border-t border-gray-200 pt-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">🎨 Kleurenschema</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="primary_color" class="block text-sm font-medium text-gray-700 mb-2">
                                    Primaire Kleur <span class="text-red-500">*</span>
                                </label>
                                <div class="flex items-center space-x-3">
                                    <input type="color" name="primary_color" id="primary_color" 
                                           value="{{ old('primary_color', $settings->primary_color) }}" required
                                           class="h-10 w-16 border border-gray-300 rounded-md cursor-pointer">
                                    <input type="text" value="{{ old('primary_color', $settings->primary_color) }}" 
                                           readonly class="flex-1 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <p class="mt-1 text-sm text-gray-500">Gebruikt voor buttons en accenten</p>
                                @error('primary_color')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="secondary_color" class="block text-sm font-medium text-gray-700 mb-2">
                                    Secundaire Kleur <span class="text-red-500">*</span>
                                </label>
                                <div class="flex items-center space-x-3">
                                    <input type="color" name="secondary_color" id="secondary_color" 
                                           value="{{ old('secondary_color', $settings->secondary_color) }}" required
                                           class="h-10 w-16 border border-gray-300 rounded-md cursor-pointer">
                                    <input type="text" value="{{ old('secondary_color', $settings->secondary_color) }}" 
                                           readonly class="flex-1 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <p class="mt-1 text-sm text-gray-500">Gebruikt voor gradients en headers</p>
                                @error('secondary_color')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="text_color" class="block text-sm font-medium text-gray-700 mb-2">
                                    Tekstkleur Header
                                </label>
                                <div class="flex items-center space-x-3">
                                    <input type="color" name="text_color" id="text_color" 
                                           value="{{ old('text_color', $settings->email_text_color ?? '#ffffff') }}"
                                           class="h-10 w-16 border border-gray-300 rounded-md cursor-pointer">
                                    <input type="text" value="{{ old('text_color', $settings->email_text_color ?? '#ffffff') }}" 
                                           readonly class="flex-1 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <p class="mt-1 text-sm text-gray-500">Kleur van tekst in email header</p>
                                @error('text_color')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="logo_position" class="block text-sm font-medium text-gray-700 mb-2">
                                    Logo Positie
                                </label>
                                <select name="logo_position" id="logo_position"
                                        class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    <option value="left" {{ old('logo_position', $settings->email_logo_position ?? 'left') === 'left' ? 'selected' : '' }}>Links</option>
                                    <option value="center" {{ old('logo_position', $settings->email_logo_position ?? 'left') === 'center' ? 'selected' : '' }}>Midden</option>
                                    <option value="right" {{ old('logo_position', $settings->email_logo_position ?? 'left') === 'right' ? 'selected' : '' }}>Rechts</option>
                                </select>
                                <p class="mt-1 text-sm text-gray-500">Positie van het logo in de email header</p>
                                @error('logo_position')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Email Content -->
                    <div class="mb-8 border-t border-gray-200 pt-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">📝 Email Inhoud</h3>
                        
                        <div class="space-y-6">
                            <div>
                                <label for="footer_text" class="block text-sm font-medium text-gray-700 mb-2">
                                    Footer Tekst
                                </label>
                                @php
                                    $footerText = old('footer_text', $settings->footer_text);
                                    // Vervang oude "Bonami Sportcoaching" of "LEVELUP" met organisatie naam
                                    if ($footerText && (str_contains($footerText, 'Bonami Sportcoaching') || str_contains($footerText, 'LEVELUP'))) {
                                        $footerText = str_replace(['Bonami Sportcoaching', 'LEVELUP'], $organisatie->bedrijf_naam ?? $organisatie->naam, $footerText);
                                    } elseif (!$footerText) {
                                        $footerText = 'Met vriendelijke groet, Het ' . ($organisatie->bedrijf_naam ?? $organisatie->naam);
                                    }
                                @endphp
                                <input type="text" name="footer_text" id="footer_text" 
                                       value="{{ $footerText }}"
                                       placeholder="Met vriendelijke groet, Het {{ $organisatie->bedrijf_naam ?? $organisatie->naam }} Team"
                                       class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <p class="mt-1 text-sm text-gray-500">Verschijnt onderaan alle emails</p>
                                @error('footer_text')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="signature" class="block text-sm font-medium text-gray-700 mb-2">
                                    Handtekening
                                </label>
                                @php
                                    $signature = old('signature', $settings->signature);
                                    // Vervang oude "Bonami Sportcoaching" of "LEVELUP" met organisatie naam
                                    if ($signature && (str_contains($signature, 'Bonami Sportcoaching') || str_contains($signature, 'LEVELUP'))) {
                                        $signature = str_replace(['Bonami Sportcoaching', 'LEVELUP'], $organisatie->bedrijf_naam ?? $organisatie->naam, $signature);
                                    } elseif (!$signature) {
                                        $signature = ($organisatie->bedrijf_naam ?? $organisatie->naam) . ' - Haal meer uit je sportprestaties';
                                    }
                                @endphp
                                <textarea name="signature" id="signature" rows="3" 
                                          placeholder="{{ $organisatie->bedrijf_naam ?? $organisatie->naam }} - Jouw partner voor optimale sportprestaties"
                                          class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">{{ $signature }}</textarea>
                                <p class="mt-1 text-sm text-gray-500">Extra informatie in de footer</p>
                                @error('signature')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end space-x-3 border-t border-gray-200 pt-6">
                        <a href="{{ url('/admin') }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            Annuleren
                        </a>
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Instellingen Opslaan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Preview -->
        <div class="lg:col-span-1">
            <div class="bg-white shadow rounded-lg p-6 sticky top-8">
                <h3 class="text-lg font-medium text-gray-900 mb-4">👀 Live Preview</h3>
                
                <div id="email-preview" class="border border-gray-200 rounded-lg overflow-hidden">
                    <!-- Live preview wordt hier geladen -->
                    <div class="header" style="background: linear-gradient(135deg, {{ $settings->primary_color }} 0%, {{ $settings->secondary_color }} 100%); padding: 20px; text-align: {{ $settings->email_logo_position ?? 'left' }};">
                        @if($settings->hasLogo())
                            <img src="{{ $settings->getLogoUrl() }}" alt="Logo" style="height: 40px; margin-bottom: 10px; display: inline-block;">
                        @endif
                        <h1 style="color: {{ $settings->email_text_color ?? '#ffffff' }}; margin: 0; font-size: 20px;">{{ $settings->company_name }}</h1>
                    </div>
                    <div style="padding: 20px;">
                        <h2 style="margin-top: 0;">Beste Jan,</h2>
                        <p>Dit is een voorbeeld van hoe je emails eruit zullen zien met de huidige instellingen.</p>
                        <a href="#" style="display: inline-block; padding: 10px 20px; background-color: {{ $settings->primary_color }}; color: white; text-decoration: none; border-radius: 5px;">
                            Call to Action
                        </a>
                        <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee; color: #666; font-size: 14px;">
                            @if($settings->footer_text)
                                <p>{{ $settings->footer_text }}</p>
                            @endif
                            @if($settings->signature)
                                <p style="margin: 5px 0;">{{ $settings->signature }}</p>
                            @endif
                        </div>
                    </div>
                </div>
                
                <p class="text-sm text-gray-500 mt-4">
                    Deze preview toont hoe je nieuwe templates eruit zullen zien met de huidige instellingen.
                </p>
            </div>
        </div>
    </div>
</div>

<script>
// Update preview when colors or logo position change
document.addEventListener('DOMContentLoaded', function() {
    const primaryColorInput = document.getElementById('primary_color');
    const secondaryColorInput = document.getElementById('secondary_color');
    const textColorInput = document.getElementById('text_color');
    const logoPositionInput = document.getElementById('logo_position');
    
    function updatePreview() {
        const preview = document.getElementById('email-preview');
        const primaryColor = primaryColorInput.value;
        const secondaryColor = secondaryColorInput.value;
        const textColor = textColorInput ? textColorInput.value : '#ffffff';
        const logoPosition = logoPositionInput ? logoPositionInput.value : 'left';
        
        // Update gradient in header
        const header = preview.querySelector('div[style*="background"]');
        if (header) {
            header.style.background = `linear-gradient(135deg, ${primaryColor} 0%, ${secondaryColor} 100%)`;
            
            // Update text alignment based on logo position
            if (logoPosition === 'left') {
                header.style.textAlign = 'left';
            } else if (logoPosition === 'center') {
                header.style.textAlign = 'center';
            } else if (logoPosition === 'right') {
                header.style.textAlign = 'right';
            }
        }
        
        // Update text color in header
        const headerTexts = preview.querySelectorAll('.header h1, .header img + h1');
        headerTexts.forEach(function(el) {
            el.style.color = textColor;
        });
        
        // Update button color
        const button = preview.querySelector('a[style*="background-color"]');
        if (button) {
            button.style.backgroundColor = primaryColor;
        }
        
        // Update text input values
        primaryColorInput.nextElementSibling.value = primaryColor;
        secondaryColorInput.nextElementSibling.value = secondaryColor;
        if (textColorInput && textColorInput.nextElementSibling) {
            textColorInput.nextElementSibling.value = textColor;
        }
    }
    
    primaryColorInput.addEventListener('change', updatePreview);
    secondaryColorInput.addEventListener('change', updatePreview);
    if (textColorInput) {
        textColorInput.addEventListener('change', updatePreview);
    }
    if (logoPositionInput) {
        logoPositionInput.addEventListener('change', updatePreview);
    }
    
    // Initial update
    updatePreview();
});
</script>
@endsection