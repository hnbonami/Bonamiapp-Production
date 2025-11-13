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
            
            <form action="{{ route('branding.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="organisatie_id" value="{{ $organisatie->id }}">
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
                
                {{-- Logo's Section --}}
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4">🖼️ Logo's & Iconen</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        {{-- Main Logo --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Hoofdlogo</label>
                            @if($branding->logo_pad)
                                <div class="mb-2">
                                    @php
                                        $logoUrl = app()->environment('production') 
                                            ? asset('uploads/' . $branding->logo_pad)
                                            : asset('storage/' . $branding->logo_pad);
                                    @endphp
                                    <img src="{{ $logoUrl }}" alt="Logo" class="h-16 w-auto object-contain border rounded p-2 bg-gray-50">
                                    <button type="button" onclick="deleteFile('logo_pad')" class="text-red-600 text-xs mt-1 hover:underline">Verwijderen</button>
                                </div>
                            @endif
                            <input type="file" name="logo" accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            <p class="text-xs text-gray-500 mt-1">PNG of JPG, max 2MB</p>
                        </div>
                    </div>
                </div>
                
                {{-- Navbar Kleuren Section --}}
                <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">🎯 Hoofdbalk (Navbar) Kleuren</h2>
                    <p class="text-gray-600 mb-6">Pas de kleuren van de bovenste navigatiebalk aan</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">Achtergrondkleur Navbar</label>
                            <div class="flex gap-3 items-center">
                                <input type="color" 
                                       name="navbar_achtergrond" 
                                       id="navbar_achtergrond"
                                       value="{{ old('navbar_achtergrond', $branding->navbar_achtergrond ?? '#1E293B') }}" 
                                       class="h-12 w-20 rounded-lg border-2 border-gray-300 cursor-pointer hover:border-blue-500 transition"
                                       onchange="document.getElementById('navbar_achtergrond_text').value = this.value">
                                <input type="text" 
                                       id="navbar_achtergrond_text"
                                       value="{{ old('navbar_achtergrond', $branding->navbar_achtergrond ?? '#1E293B') }}" 
                                       class="flex-1 px-4 py-3 border-2 border-gray-300 rounded-lg font-mono text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                       readonly>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">💡 Achtergrondkleur van de bovenste balk met logo en gebruiker</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">Tekstkleur Navbar</label>
                            <div class="flex gap-3 items-center">
                                <input type="color" 
                                       name="navbar_tekst_kleur" 
                                       id="navbar_tekst_kleur"
                                       value="{{ old('navbar_tekst_kleur', $branding->navbar_tekst_kleur ?? '#FFFFFF') }}" 
                                       class="h-12 w-20 rounded-lg border-2 border-gray-300 cursor-pointer hover:border-blue-500 transition"
                                       onchange="document.getElementById('navbar_tekst_kleur_text').value = this.value">
                                <input type="text" 
                                       id="navbar_tekst_kleur_text"
                                       value="{{ old('navbar_tekst_kleur', $branding->navbar_tekst_kleur ?? '#FFFFFF') }}" 
                                       class="flex-1 px-4 py-3 border-2 border-gray-300 rounded-lg font-mono text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                       readonly>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">💡 Kleur van tekst, iconen en hamburger menu in de navbar</p>
                        </div>
                    </div>
                </div>
                
                {{-- Sidebar Kleuren Section --}}
                <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">📂 Sidebar Kleuren</h2>
                    <p class="text-gray-600 mb-6">Pas de kleuren van de zijbalk (navigatie) aan</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">Actieve Item Achtergrond</label>
                            <div class="flex gap-3 items-center">
                                <input type="color" 
                                       name="sidebar_actief_achtergrond" 
                                       id="sidebar_actief_achtergrond"
                                       value="{{ old('sidebar_actief_achtergrond', $branding->sidebar_actief_achtergrond ?? '#f6fbfe') }}" 
                                       class="h-12 w-20 rounded-lg border-2 border-gray-300 cursor-pointer hover:border-blue-500 transition"
                                       onchange="document.getElementById('sidebar_actief_achtergrond_text').value = this.value">
                                <input type="text" 
                                       id="sidebar_actief_achtergrond_text"
                                       value="{{ old('sidebar_actief_achtergrond', $branding->sidebar_actief_achtergrond ?? '#f6fbfe') }}" 
                                       class="flex-1 px-4 py-3 border-2 border-gray-300 rounded-lg font-mono text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                       readonly>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">💡 Achtergrondkleur van de actieve pagina in menu</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">Actieve Item Lijn (links)</label>
                            <div class="flex gap-3 items-center">
                                <input type="color" 
                                       name="sidebar_actief_lijn" 
                                       id="sidebar_actief_lijn"
                                       value="{{ old('sidebar_actief_lijn', $branding->sidebar_actief_lijn ?? '#c1dfeb') }}" 
                                       class="h-12 w-20 rounded-lg border-2 border-gray-300 cursor-pointer hover:border-blue-500 transition"
                                       onchange="document.getElementById('sidebar_actief_lijn_text').value = this.value">
                                <input type="text" 
                                       id="sidebar_actief_lijn_text"
                                       value="{{ old('sidebar_actief_lijn', $branding->sidebar_actief_lijn ?? '#c1dfeb') }}" 
                                       class="flex-1 px-4 py-3 border-2 border-gray-300 rounded-lg font-mono text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                       readonly>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">💡 Kleur van het verticale lijntje links bij actieve pagina</p>
                        </div>
                    </div>
                </div>
                
                {{-- Dark Mode Kleuren Section --}}
                <div class="bg-gray-900 rounded-xl shadow-lg p-8 mb-8">
                    <h2 class="text-2xl font-bold text-white mb-6">🌙 Dark Mode Kleuren</h2>
                    <p class="text-gray-300 mb-6">Pas de kleuren aan wanneer gebruikers dark mode activeren</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-300 mb-3">Achtergrondkleur (Dark)</label>
                            <div class="flex gap-3 items-center">
                                <input type="color" 
                                       name="dark_achtergrond" 
                                       id="dark_achtergrond"
                                       value="{{ old('dark_achtergrond', $branding->dark_achtergrond ?? '#1F2937') }}" 
                                       class="h-12 w-20 rounded-lg border-2 border-gray-600 cursor-pointer hover:border-blue-500 transition"
                                       onchange="document.getElementById('dark_achtergrond_text').value = this.value">
                                <input type="text" 
                                       id="dark_achtergrond_text"
                                       value="{{ old('dark_achtergrond', $branding->dark_achtergrond ?? '#1F2937') }}" 
                                       class="flex-1 px-4 py-3 border-2 border-gray-600 bg-gray-800 text-white rounded-lg font-mono text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                       readonly>
                            </div>
                            <p class="text-xs text-gray-400 mt-2">💡 Algemene achtergrondkleur in dark mode</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-300 mb-3">Tekstkleur (Dark)</label>
                            <div class="flex gap-3 items-center">
                                <input type="color" 
                                       name="dark_tekst" 
                                       id="dark_tekst"
                                       value="{{ old('dark_tekst', $branding->dark_tekst ?? '#F9FAFB') }}" 
                                       class="h-12 w-20 rounded-lg border-2 border-gray-600 cursor-pointer hover:border-blue-500 transition"
                                       onchange="document.getElementById('dark_tekst_text').value = this.value">
                                <input type="text" 
                                       id="dark_tekst_text"
                                       value="{{ old('dark_tekst', $branding->dark_tekst ?? '#F9FAFB') }}" 
                                       class="flex-1 px-4 py-3 border-2 border-gray-600 bg-gray-800 text-white rounded-lg font-mono text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                       readonly>
                            </div>
                            <p class="text-xs text-gray-400 mt-2">💡 Tekstkleur in dark mode</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-300 mb-3">Navbar Achtergrond (Dark)</label>
                            <div class="flex gap-3 items-center">
                                <input type="color" 
                                       name="dark_navbar_achtergrond" 
                                       id="dark_navbar_achtergrond"
                                       value="{{ old('dark_navbar_achtergrond', $branding->dark_navbar_achtergrond ?? '#111827') }}" 
                                       class="h-12 w-20 rounded-lg border-2 border-gray-600 cursor-pointer hover:border-blue-500 transition"
                                       onchange="document.getElementById('dark_navbar_achtergrond_text').value = this.value">
                                <input type="text" 
                                       id="dark_navbar_achtergrond_text"
                                       value="{{ old('dark_navbar_achtergrond', $branding->dark_navbar_achtergrond ?? '#111827') }}" 
                                       class="flex-1 px-4 py-3 border-2 border-gray-600 bg-gray-800 text-white rounded-lg font-mono text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                       readonly>
                            </div>
                            <p class="text-xs text-gray-400 mt-2">💡 Hoofdbalk achtergrond in dark mode</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-300 mb-3">Sidebar Achtergrond (Dark)</label>
                            <div class="flex gap-3 items-center">
                                <input type="color" 
                                       name="dark_sidebar_achtergrond" 
                                       id="dark_sidebar_achtergrond"
                                       value="{{ old('dark_sidebar_achtergrond', $branding->dark_sidebar_achtergrond ?? '#111827') }}" 
                                       class="h-12 w-20 rounded-lg border-2 border-gray-600 cursor-pointer hover:border-blue-500 transition"
                                       onchange="document.getElementById('dark_sidebar_achtergrond_text').value = this.value">
                                <input type="text" 
                                       id="dark_sidebar_achtergrond_text"
                                       value="{{ old('dark_sidebar_achtergrond', $branding->dark_sidebar_achtergrond ?? '#111827') }}" 
                                       class="flex-1 px-4 py-3 border-2 border-gray-600 bg-gray-800 text-white rounded-lg font-mono text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                       readonly>
                            </div>
                            <p class="text-xs text-gray-400 mt-2">💡 Zijbalk achtergrond in dark mode</p>
                        </div>
                    </div>
                </div>
                
                
                
                
                
                {{-- Login Pagina Personalisatie --}}
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">🔐 Login Pagina Personalisatie</h3>
                    
                    <!-- Login Achtergrond Media (Foto of Video) -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Login Achtergrond (rechts)</label>
                        <p class="text-xs text-gray-500 mb-3">Kies tussen een foto of video voor de rechterkant van het login scherm</p>
                        
                        @if($branding->login_background_image)
                            <div class="mb-2 p-3 bg-gray-50 border rounded-lg">
                                @php
                                    $bgImageUrl = app()->environment('production') 
                                        ? asset('uploads/' . $branding->login_background_image)
                                        : asset('storage/' . $branding->login_background_image);
                                @endphp
                                <p class="text-xs font-medium text-gray-700 mb-2">Huidige afbeelding:</p>
                                <img src="{{ $bgImageUrl }}" 
                                     alt="Login Achtergrond" 
                                     class="h-32 w-auto object-cover rounded border border-gray-200 bg-white mb-2">
                                <p class="text-xs text-gray-500">{{ basename($branding->login_background_image) }}</p>
                                <p class="text-xs text-blue-600 mt-1">✓ Afbeelding geladen van: {{ $bgImageUrl }}</p>
                            </div>
                        @endif
                        
                        @if($branding->login_background_video)
                            <div class="mb-2 p-3 bg-gray-50 border rounded-lg">
                                @php
                                    $bgVideoUrl = app()->environment('production') 
                                        ? asset('uploads/' . $branding->login_background_video)
                                        : asset('storage/' . $branding->login_background_video);
                                @endphp
                                <p class="text-xs font-medium text-gray-700 mb-2">Huidige video:</p>
                                <video class="h-32 w-auto rounded border border-gray-200 bg-black mb-2" controls muted>
                                    <source src="{{ $bgVideoUrl }}" type="video/mp4">
                                    Je browser ondersteunt geen HTML5 video.
                                </video>
                                <p class="text-xs text-gray-500 mb-1">{{ basename($branding->login_background_video) }}</p>
                                <p class="text-xs text-blue-600">✓ Video geladen van: {{ $bgVideoUrl }}</p>
                                <p class="text-xs text-orange-600 mt-1">⚠️ Als video niet afspeelt, check bestandslocatie in server</p>
                            </div>
                        @else
                            <div class="mb-2 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                                <p class="text-xs text-yellow-800">ℹ️ Geen video geüpload</p>
                            </div>
                        @endif
                        
                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Upload Afbeelding</label>
                                <input type="file" 
                                       name="login_background_image" 
                                       accept="image/*" 
                                       class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                <p class="mt-1 text-xs text-gray-500">Portrait/vierkant formaat, hoge kwaliteit, max 2MB</p>
                            </div>
                            
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Upload Video</label>
                                <input type="file" 
                                       name="login_background_video" 
                                       accept="video/*" 
                                       class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                <p class="mt-1 text-xs text-gray-500">MP4, MOV, WebM of andere video formaten, max 10MB (video heeft voorrang boven afbeelding)</p>
                            </div>
                        </div>
                    </div>

                    <!-- Tekstkleur -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tekstkleur</label>
                        <div class="flex items-center gap-3">
                            <input type="color" 
                                   name="login_text_color" 
                                   value="{{ $branding->login_text_color ?? '#374151' }}"
                                   class="h-10 w-20 rounded border border-gray-300">
                            <input type="text" 
                                   name="login_text_color_hex" 
                                   value="{{ $branding->login_text_color ?? '#374151' }}"
                                   placeholder="#374151"
                                   class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>

                    <!-- Knop Kleur -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Inlogknop Kleur</label>
                        <div class="flex items-center gap-3">
                            <input type="color" 
                                   name="login_button_color" 
                                   value="{{ $branding->login_button_color ?? '#c8e1eb' }}"
                                   class="h-10 w-20 rounded border border-gray-300">
                            <input type="text" 
                                   name="login_button_color_hex" 
                                   value="{{ $branding->login_button_color ?? '#c8e1eb' }}"
                                   placeholder="#c8e1eb"
                                   class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>

                    <!-- Knop Hover Kleur -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Inlogknop Hover Kleur</label>
                        <div class="flex items-center gap-3">
                            <input type="color" 
                                   name="login_button_hover_color" 
                                   value="{{ $branding->login_button_hover_color ?? '#9bb3bd' }}"
                                   class="h-10 w-20 rounded border border-gray-300">
                            <input type="text" 
                                   name="login_button_hover_color_hex" 
                                   value="{{ $branding->login_button_hover_color ?? '#9bb3bd' }}"
                                   placeholder="#9bb3bd"
                                   class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>

                    <!-- Link Kleur -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Link Kleur (Wachtwoord vergeten)</label>
                        <div class="flex items-center gap-3">
                            <input type="color" 
                                   name="login_link_color" 
                                   value="{{ $branding->login_link_color ?? '#c8e1eb' }}"
                                   class="h-10 w-20 rounded border border-gray-300">
                            <input type="text" 
                                   name="login_link_color_hex" 
                                   value="{{ $branding->login_link_color ?? '#c8e1eb' }}"
                                   placeholder="#c8e1eb"
                                   class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
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
    
    @push('scripts')
    <script>
        // Live preview update voor login kleuren
        document.addEventListener('DOMContentLoaded', function() {
            // Color input sync met hex input
            const colorPairs = [
                { color: '[name="login_text_color"]', hex: '[name="login_text_color_hex"]' },
                { color: '[name="login_button_color"]', hex: '[name="login_button_color_hex"]' },
                { color: '[name="login_button_hover_color"]', hex: '[name="login_button_hover_color_hex"]' },
                { color: '[name="login_link_color"]', hex: '[name="login_link_color_hex"]' }
            ];

            colorPairs.forEach(pair => {
                const colorInput = document.querySelector(pair.color);
                const hexInput = document.querySelector(pair.hex);

                if (colorInput && hexInput) {
                    // Sync color picker naar hex input
                    colorInput.addEventListener('input', function() {
                        hexInput.value = this.value;
                        updatePreview();
                    });

                    // Sync hex input naar color picker
                    hexInput.addEventListener('input', function() {
                        if (/^#[0-9A-F]{6}$/i.test(this.value)) {
                            colorInput.value = this.value;
                            updatePreview();
                        }
                    });
                }
            });

            // Update live preview
            function updatePreview() {
                const textColor = document.querySelector('[name="login_text_color"]')?.value || '#374151';
                const buttonColor = document.querySelector('[name="login_button_color"]')?.value || '#c8e1eb';
                const linkColor = document.querySelector('[name="login_link_color"]')?.value || '#c8e1eb';

                // Update preview elementen
                const previewText = document.querySelector('.bg-gray-50 [style*="color"]');
                const previewButton = document.querySelector('.bg-gray-50 button[type="button"]');
                const previewLink = document.querySelector('.bg-gray-50 a');

                if (previewText) previewText.style.color = textColor;
                if (previewButton) previewButton.style.backgroundColor = buttonColor;
                if (previewLink) previewLink.style.color = linkColor;
            }
        });
    </script>
    @endpush
</x-app-layout>
