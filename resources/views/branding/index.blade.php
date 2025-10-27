<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                🎨 Custom Branding
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('organisaties.show', $organisatie) }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm transition">
                    ← Terug naar Organisatie
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            {{-- Success/Error Messages --}}
            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
                    ✅ {{ session('success') }}
                </div>
            @endif
            
            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
                    ❌ {{ session('error') }}
                </div>
            @endif
            
            {{-- Preview Card --}}
            <div class="bg-white shadow-sm rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">📱 Live Preview</h3>
                <div class="border-2 border-gray-200 rounded-lg p-6" style="
                    background-color: {{ $branding->background_color }};
                    color: {{ $branding->text_color }};
                    font-family: {{ $branding->body_font }}, sans-serif;
                ">
                    <div class="flex items-center gap-4 mb-4">
                        @if($branding->logo_path)
                            <img src="{{ $branding->logo_url }}" alt="Logo" class="h-12 object-contain">
                        @else
                            <div class="h-12 w-12 bg-gray-200 rounded flex items-center justify-center text-xs">
                                Logo
                            </div>
                        @endif
                        <div>
                            <h4 style="font-family: {{ $branding->heading_font }}, sans-serif; color: {{ $branding->primary_color }};" class="text-xl font-bold">
                                {{ $branding->company_name ?? $organisatie->naam }}
                            </h4>
                            @if($branding->tagline)
                                <p class="text-sm opacity-75">{{ $branding->tagline }}</p>
                            @endif
                        </div>
                    </div>
                    
                    <div class="space-y-2">
                        <button style="background-color: {{ $branding->primary_color }};" class="text-white px-4 py-2 rounded-lg text-sm">
                            Primary Button
                        </button>
                        <button style="background-color: {{ $branding->secondary_color }};" class="text-white px-4 py-2 rounded-lg text-sm ml-2">
                            Secondary Button
                        </button>
                        <button style="background-color: {{ $branding->accent_color }};" class="text-white px-4 py-2 rounded-lg text-sm ml-2">
                            Accent Button
                        </button>
                    </div>
                </div>
            </div>
            
            <form action="{{ route('branding.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                {{-- Logo's Section --}}
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4">🖼️ Logo's & Iconen</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        {{-- Main Logo --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Hoofdlogo</label>
                            @if($branding->logo_path)
                                <div class="mb-2">
                                    <img src="{{ $branding->logo_url }}" alt="Logo" class="h-20 object-contain border rounded p-2">
                                    <button type="button" onclick="deleteFile('logo_path')" class="text-red-600 text-xs mt-1">Verwijderen</button>
                                </div>
                            @endif
                            <input type="file" name="logo" accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            <p class="text-xs text-gray-500 mt-1">PNG of JPG, max 2MB</p>
                        </div>
                        
                        {{-- Dark Mode Logo --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Logo (Dark Mode)</label>
                            @if($branding->logo_dark_path)
                                <div class="mb-2 bg-gray-900 p-2 rounded">
                                    <img src="{{ $branding->logo_dark_url }}" alt="Dark Logo" class="h-20 object-contain">
                                    <button type="button" onclick="deleteFile('logo_dark_path')" class="text-red-600 text-xs mt-1">Verwijderen</button>
                                </div>
                            @endif
                            <input type="file" name="logo_dark" accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            <p class="text-xs text-gray-500 mt-1">Optioneel: voor dark mode</p>
                        </div>
                        
                        {{-- Small Logo --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Klein Logo (Mobiel)</label>
                            @if($branding->logo_small_path)
                                <div class="mb-2">
                                    <img src="{{ $branding->logo_small_url }}" alt="Small Logo" class="h-12 object-contain border rounded p-2">
                                    <button type="button" onclick="deleteFile('logo_small_path')" class="text-red-600 text-xs mt-1">Verwijderen</button>
                                </div>
                            @endif
                            <input type="file" name="logo_small" accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            <p class="text-xs text-gray-500 mt-1">Voor mobiele weergave</p>
                        </div>
                        
                        {{-- Favicon --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Favicon</label>
                            @if($branding->favicon_path)
                                <div class="mb-2">
                                    <img src="{{ $branding->favicon_url }}" alt="Favicon" class="h-8 w-8 object-contain border rounded">
                                    <button type="button" onclick="deleteFile('favicon_path')" class="text-red-600 text-xs mt-1">Verwijderen</button>
                                </div>
                            @endif
                            <input type="file" name="favicon" accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            <p class="text-xs text-gray-500 mt-1">32x32px icoon voor browser tab</p>
                        </div>
                        
                        {{-- Rapport Logo --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Rapport Logo</label>
                            @if($branding->rapport_logo_path)
                                <div class="mb-2">
                                    <img src="{{ $branding->rapport_logo_url }}" alt="Rapport Logo" class="h-20 object-contain border rounded p-2">
                                    <button type="button" onclick="deleteFile('rapport_logo_path')" class="text-red-600 text-xs mt-1">Verwijderen</button>
                                </div>
                            @endif
                            <input type="file" name="rapport_logo" accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            <p class="text-xs text-gray-500 mt-1">Voor PDF rapporten</p>
                        </div>
                        
                        {{-- Watermark --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Watermark</label>
                            @if($branding->rapport_watermark_path)
                                <div class="mb-2">
                                    <img src="{{ $branding->watermark_url }}" alt="Watermark" class="h-20 object-contain border rounded p-2 opacity-30">
                                    <button type="button" onclick="deleteFile('rapport_watermark_path')" class="text-red-600 text-xs mt-1">Verwijderen</button>
                                </div>
                            @endif
                            <input type="file" name="watermark" accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            <div class="mt-2">
                                <label class="flex items-center">
                                    <input type="checkbox" name="show_watermark" value="1" {{ $branding->show_watermark ? 'checked' : '' }} class="rounded border-gray-300">
                                    <span class="ml-2 text-sm">Toon watermark in rapporten</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                
                {{-- Kleuren Section --}}
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4">🎨 Kleurenschema</h3>
                    
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Primary Color</label>
                            <div class="flex items-center gap-2">
                                <input type="color" name="primary_color" value="{{ $branding->primary_color }}" class="h-10 w-16 rounded border">
                                <input type="text" value="{{ $branding->primary_color }}" readonly class="flex-1 px-2 py-1 border rounded text-sm">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Secondary Color</label>
                            <div class="flex items-center gap-2">
                                <input type="color" name="secondary_color" value="{{ $branding->secondary_color }}" class="h-10 w-16 rounded border">
                                <input type="text" value="{{ $branding->secondary_color }}" readonly class="flex-1 px-2 py-1 border rounded text-sm">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Accent Color</label>
                            <div class="flex items-center gap-2">
                                <input type="color" name="accent_color" value="{{ $branding->accent_color }}" class="h-10 w-16 rounded border">
                                <input type="text" value="{{ $branding->accent_color }}" readonly class="flex-1 px-2 py-1 border rounded text-sm">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Text Color</label>
                            <div class="flex items-center gap-2">
                                <input type="color" name="text_color" value="{{ $branding->text_color }}" class="h-10 w-16 rounded border">
                                <input type="text" value="{{ $branding->text_color }}" readonly class="flex-1 px-2 py-1 border rounded text-sm">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Background Color</label>
                            <div class="flex items-center gap-2">
                                <input type="color" name="background_color" value="{{ $branding->background_color }}" class="h-10 w-16 rounded border">
                                <input type="text" value="{{ $branding->background_color }}" readonly class="flex-1 px-2 py-1 border rounded text-sm">
                            </div>
                        </div>
                    </div>
                </div>
                
                {{-- Typografie Section --}}
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4">✍️ Typografie</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Heading Font</label>
                            <select name="heading_font" class="w-full rounded-lg border-gray-300">
                                <option value="Inter" {{ $branding->heading_font == 'Inter' ? 'selected' : '' }}>Inter (Modern)</option>
                                <option value="Roboto" {{ $branding->heading_font == 'Roboto' ? 'selected' : '' }}>Roboto (Clean)</option>
                                <option value="Montserrat" {{ $branding->heading_font == 'Montserrat' ? 'selected' : '' }}>Montserrat (Bold)</option>
                                <option value="Poppins" {{ $branding->heading_font == 'Poppins' ? 'selected' : '' }}>Poppins (Friendly)</option>
                                <option value="Arial" {{ $branding->heading_font == 'Arial' ? 'selected' : '' }}>Arial (Classic)</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Body Font</label>
                            <select name="body_font" class="w-full rounded-lg border-gray-300">
                                <option value="Inter" {{ $branding->body_font == 'Inter' ? 'selected' : '' }}>Inter (Modern)</option>
                                <option value="Roboto" {{ $branding->body_font == 'Roboto' ? 'selected' : '' }}>Roboto (Clean)</option>
                                <option value="Open Sans" {{ $branding->body_font == 'Open Sans' ? 'selected' : '' }}>Open Sans (Readable)</option>
                                <option value="Lato" {{ $branding->body_font == 'Lato' ? 'selected' : '' }}>Lato (Professional)</option>
                                <option value="Arial" {{ $branding->body_font == 'Arial' ? 'selected' : '' }}>Arial (Classic)</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                {{-- Bedrijfsinfo Section --}}
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4">🏢 Bedrijfsinformatie</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Bedrijfsnaam (optioneel override)</label>
                            <input type="text" name="company_name" value="{{ $branding->company_name }}" placeholder="{{ $organisatie->naam }}" class="w-full rounded-lg border-gray-300">
                            <p class="text-xs text-gray-500 mt-1">Laat leeg om organisatienaam te gebruiken</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tagline / Slogan</label>
                            <input type="text" name="tagline" value="{{ $branding->tagline }}" placeholder="Jouw sportcoaching partner" class="w-full rounded-lg border-gray-300">
                        </div>
                    </div>
                </div>
                
                {{-- Rapport Styling Section --}}
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4">📄 Rapport Styling</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Rapport Header Tekst</label>
                            <textarea name="rapport_header" rows="3" class="w-full rounded-lg border-gray-300" placeholder="Bijvoorbeeld: Professionele bikefit analyse">{{ $branding->rapport_header }}</textarea>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Rapport Footer Tekst</label>
                            <textarea name="rapport_footer" rows="3" class="w-full rounded-lg border-gray-300" placeholder="Bijvoorbeeld: Copyright © 2025 - Alle rechten voorbehouden">{{ $branding->rapport_footer }}</textarea>
                        </div>
                    </div>
                </div>
                
                {{-- Email Branding Section --}}
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4">📧 Email Branding</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" name="use_custom_email_branding" value="1" {{ $branding->use_custom_email_branding ? 'checked' : '' }} class="rounded border-gray-300">
                                <span class="ml-2 text-sm font-medium">Gebruik custom branding in emails</span>
                            </label>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email Header Kleur</label>
                            <div class="flex items-center gap-2">
                                <input type="color" name="email_header_color" value="{{ $branding->email_header_color ?? $branding->primary_color }}" class="h-10 w-16 rounded border">
                                <input type="text" value="{{ $branding->email_header_color ?? $branding->primary_color }}" readonly class="flex-1 px-2 py-1 border rounded text-sm">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email Footer Tekst</label>
                            <textarea name="email_footer_text" rows="2" class="w-full rounded-lg border-gray-300" placeholder="Bijvoorbeeld: Met sportieve groet, Team {{ $organisatie->naam }}">{{ $branding->email_footer_text }}</textarea>
                        </div>
                    </div>
                </div>
                
                {{-- Action Buttons --}}
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition">
                            💾 Instellingen Opslaan
                        </button>
                        
                        <button type="button" onclick="resetBranding()" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg transition">
                            🔄 Reset naar Defaults
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <script>
    // Sync color picker met text input
    document.querySelectorAll('input[type="color"]').forEach(colorInput => {
        colorInput.addEventListener('input', function() {
            this.nextElementSibling.value = this.value.toUpperCase();
        });
    });
    
    // Delete file functie
    function deleteFile(field) {
        if (!confirm('Weet je zeker dat je dit bestand wilt verwijderen?')) {
            return;
        }
        
        fetch('{{ route("branding.delete-file") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ field: field })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Fout: ' + data.message);
            }
        })
        .catch(error => {
            alert('Er ging iets mis bij het verwijderen.');
            console.error(error);
        });
    }
    
    // Reset branding functie
    function resetBranding() {
        if (!confirm('Weet je zeker dat je alle branding instellingen wilt resetten naar de standaard waarden? Deze actie kan niet ongedaan worden gemaakt.')) {
            return;
        }
        
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("branding.reset") }}';
        
        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = '{{ csrf_token() }}';
        form.appendChild(csrf);
        
        document.body.appendChild(form);
        form.submit();
    }
    </script>
</x-app-layout>
