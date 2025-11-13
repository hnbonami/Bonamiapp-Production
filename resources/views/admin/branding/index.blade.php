@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">🎨 Branding & Layout</h1>
            <p class="text-gray-600 mt-2">Pas het uiterlijk van de applicatie aan voor {{ $organisatie->naam }}</p>
        </div>

        <!-- Success Message -->
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6">
                ✅ {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('admin.branding.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <!-- Branding Activeren -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Aangepaste Branding Activeren</h3>
                        <p class="text-sm text-gray-600 mt-1">Schakel custom logo en kleuren in voor je organisatie</p>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" name="branding_enabled" value="1" {{ $organisatie->branding_enabled ? 'checked' : '' }}>
                        <span class="toggle-slider"></span>
                    </label>
                </div>
            </div>

            <!-- Logo Upload -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Logo</h3>
                
                @if($branding && $branding->logo_pad)
                    <div class="mb-4 p-4 bg-gray-50 rounded-lg">
                        <p class="text-sm text-gray-600 mb-2">Huidige logo:</p>
                        @php
                            $logoUrl = app()->environment('production') 
                                ? asset('uploads/' . $branding->logo_pad)
                                : asset('storage/' . $branding->logo_pad);
                        @endphp
                        <img src="{{ $logoUrl }}" alt="Logo" class="max-h-20 mb-2">
                        <button type="button" onclick="deleteLogo()" class="text-red-600 text-sm hover:underline">
                            🗑️ Verwijderen
                        </button>
                    </div>
                @endif
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Upload nieuw logo</label>
                    <input type="file" name="logo" accept="image/png,image/jpeg,image/jpg,image/svg+xml" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none">
                    <p class="text-xs text-gray-500 mt-1">PNG, JPG, JPEG of SVG (max 2MB)</p>
                </div>
            </div>

            <!-- Favicon Upload -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Favicon</h3>
                
                @if($organisatie->favicon_path)
                    <div class="mb-4 p-4 bg-gray-50 rounded-lg">
                        <p class="text-sm text-gray-600 mb-2">Huidig favicon:</p>
                        <img src="{{ $organisatie->favicon_url }}" alt="Favicon" class="max-h-8 mb-2">
                        <form action="{{ route('admin.branding.deleteFavicon') }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 text-sm hover:underline" onclick="return confirm('Favicon verwijderen?')">
                                🗑️ Verwijderen
                            </button>
                        </form>
                    </div>
                @endif
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Upload nieuw favicon</label>
                    <input type="file" name="favicon" accept="image/png,image/x-icon,image/vnd.microsoft.icon" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none">
                    <p class="text-xs text-gray-500 mt-1">ICO of PNG (max 512KB, aanbevolen: 32x32px)</p>
                </div>
            </div>

            <!-- Login Pagina Instellingen -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">🔐 Login Pagina</h3>
                
                <div class="space-y-6">
                    <!-- Login Logo -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Login Logo</label>
                        @if($branding && $branding->login_logo)
                            <div class="mb-3 p-3 bg-gray-50 rounded-lg">
                                @php
                                    $loginLogoUrl = app()->environment('production') 
                                        ? asset('uploads/' . $branding->login_logo)
                                        : asset('storage/' . $branding->login_logo);
                                @endphp
                                <img src="{{ $loginLogoUrl }}" alt="Login logo" class="h-16 w-auto mb-2">
                                <p class="text-xs text-gray-500">Huidige logo</p>
                            </div>
                        @endif
                        <input type="file" name="login_logo" accept="image/*" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none">
                        <p class="text-xs text-gray-500 mt-1">Verschijnt boven het login formulier (PNG transparant aanbevolen, max 500KB)</p>
                    </div>

                    <!-- Login Achtergrondafbeelding -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Login Achtergrond (rechts)</label>
                        <p class="text-xs text-gray-500 mb-3">Kies tussen een foto of video voor de rechterkant van het login scherm</p>
                        
                        @if($branding && $branding->login_background_image)
                            <div class="mb-3 p-3 bg-gray-50 rounded-lg">
                                <p class="text-xs text-gray-600 mb-2">Huidige afbeelding:</p>
                                @php
                                    $bgImageUrl = app()->environment('production') 
                                        ? asset('uploads/' . $branding->login_background_image)
                                        : asset('storage/' . $branding->login_background_image);
                                @endphp
                                <img src="{{ $bgImageUrl }}" alt="Login achtergrond" class="h-32 w-auto mb-2 object-cover rounded">
                            </div>
                        @endif
                        
                        @if($branding && $branding->login_background_video)
                            <div class="mb-3 p-3 bg-gray-50 rounded-lg">
                                <p class="text-xs text-gray-600 mb-2">Huidige video:</p>
                                @php
                                    $bgVideoUrl = app()->environment('production') 
                                        ? asset('uploads/' . $branding->login_background_video)
                                        : asset('storage/' . $branding->login_background_video);
                                @endphp
                                <video class="h-32 w-auto mb-2 rounded" muted loop>
                                    <source src="{{ $bgVideoUrl }}" type="video/mp4">
                                </video>
                                <p class="text-xs text-gray-500">{{ basename($branding->login_background_video) }}</p>
                            </div>
                        @endif
                        
                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Upload Afbeelding</label>
                                <input type="file" name="login_background_image" accept="image/*" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none">
                                <p class="text-xs text-gray-500 mt-1">Portrait/vierkant formaat, hoge kwaliteit, max 2MB</p>
                            </div>
                            
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Upload Video</label>
                                <input type="file" name="login_background_video" accept="video/*" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none">
                                <p class="text-xs text-gray-500 mt-1">MP4, MOV, WebM of andere video formaten, max 10MB (video heeft voorrang boven afbeelding)</p>
                            </div>
                        </div>
                    </div>

                    <!-- Login Kleuren -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Login Tekstkleur</label>
                            <div class="flex items-center gap-3">
                                <input type="color" name="login_text_color" value="{{ $branding->login_text_color ?? '#374151' }}" class="h-10 w-20 rounded cursor-pointer border border-gray-300">
                                <input type="text" value="{{ $branding->login_text_color ?? '#374151' }}" readonly class="flex-1 px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-sm font-mono">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Login Button Kleur</label>
                            <div class="flex items-center gap-3">
                                <input type="color" name="login_button_color" value="{{ $branding->login_button_color ?? '#7fb432' }}" class="h-10 w-20 rounded cursor-pointer border border-gray-300">
                                <input type="text" value="{{ $branding->login_button_color ?? '#7fb432' }}" readonly class="flex-1 px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-sm font-mono">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Login Button Hover</label>
                            <div class="flex items-center gap-3">
                                <input type="color" name="login_button_hover_color" value="{{ $branding->login_button_hover_color ?? '#6a9929' }}" class="h-10 w-20 rounded cursor-pointer border border-gray-300">
                                <input type="text" value="{{ $branding->login_button_hover_color ?? '#6a9929' }}" readonly class="flex-1 px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-sm font-mono">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Login Link Kleur</label>
                            <div class="flex items-center gap-3">
                                <input type="color" name="login_link_color" value="{{ $branding->login_link_color ?? '#374151' }}" class="h-10 w-20 rounded cursor-pointer border border-gray-300">
                                <input type="text" value="{{ $branding->login_link_color ?? '#374151' }}" readonly class="flex-1 px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-sm font-mono">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kleurinstellingen -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Themakleuren</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Primary Color -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Primary Color (Hoofdkleur)</label>
                        <div class="flex items-center gap-3">
                            <input type="color" name="primary_color" value="{{ $organisatie->primary_color }}" class="h-10 w-20 rounded cursor-pointer border border-gray-300">
                            <input type="text" value="{{ $organisatie->primary_color }}" readonly class="flex-1 px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-sm font-mono">
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Gebruikt voor buttons, links en accenten</p>
                    </div>

                    <!-- Secondary Color -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Secondary Color (Accent)</label>
                        <div class="flex items-center gap-3">
                            <input type="color" name="secondary_color" value="{{ $organisatie->secondary_color }}" class="h-10 w-20 rounded cursor-pointer border border-gray-300">
                            <input type="text" value="{{ $organisatie->secondary_color }}" readonly class="flex-1 px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-sm font-mono">
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Accent kleur voor hover states</p>
                    </div>

                    <!-- Sidebar Color -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Sidebar Color (Navigatie)</label>
                        <div class="flex items-center gap-3">
                            <input type="color" name="sidebar_color" value="{{ $organisatie->sidebar_color }}" class="h-10 w-20 rounded cursor-pointer border border-gray-300">
                            <input type="text" value="{{ $organisatie->sidebar_color }}" readonly class="flex-1 px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-sm font-mono">
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Achtergrondkleur voor navigatiebalk</p>
                    </div>

                    <!-- Text Color -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Text Color (Tekstkleur)</label>
                        <div class="flex items-center gap-3">
                            <input type="color" name="text_color" value="{{ $organisatie->text_color }}" class="h-10 w-20 rounded cursor-pointer border border-gray-300">
                            <input type="text" value="{{ $organisatie->text_color }}" readonly class="flex-1 px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-sm font-mono">
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Primaire tekstkleur</p>
                    </div>
                </div>
            </div>

            <!-- Custom CSS (Geavanceerd) -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Custom CSS (Geavanceerd)</h3>
                <p class="text-sm text-gray-600 mb-4">Voor geavanceerde styling aanpassingen</p>
                
                <textarea name="custom_css" rows="10" class="w-full px-3 py-2 border border-gray-300 rounded-lg font-mono text-sm" placeholder="/* Voorbeeld: */
.navbar {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.btn-primary {
    border-radius: 8px;
}">{{ $organisatie->custom_css }}</textarea>
                <p class="text-xs text-gray-500 mt-1">⚠️ Let op: Ongeldige CSS kan de layout breken</p>
            </div>

            <!-- Acties -->
            <div class="flex justify-between items-center">
                <div class="flex gap-3">
                    <a href="{{ route('admin.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                        ← Terug
                    </a>
                    
                    <form action="{{ route('branding.reset') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" 
                                onclick="return confirm('⚠️ Dit reset ALLE branding naar Performance Pulse defaults. Weet je het zeker?')"
                                class="px-4 py-2 bg-orange-100 text-orange-700 rounded-lg hover:bg-orange-200 transition font-medium">
                            🔄 Reset naar Performance Pulse Defaults
                        </button>
                    </form>
                </div>
                
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold">
                    💾 Opslaan
                </button>
            </div>
        </form>
    </div>
</div>

<style>
/* Toggle switch styling (hergebruik van organisaties/show.blade.php) */
.toggle-switch {
    position: relative;
    display: inline-block;
    width: 48px;
    height: 24px;
}

.toggle-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.toggle-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #cbd5e0;
    transition: .3s;
    border-radius: 24px;
}

.toggle-slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: .3s;
    border-radius: 50%;
}

input:checked + .toggle-slider {
    background-color: #3b82f6;
}

input:checked + .toggle-slider:before {
    transform: translateX(24px);
}
</style>

<script>
// Sync color picker met text input
document.querySelectorAll('input[type="color"]').forEach(colorPicker => {
    const textInput = colorPicker.nextElementSibling.nextElementSibling;
    
    colorPicker.addEventListener('input', function() {
        textInput.value = this.value.toUpperCase();
    });
});

// Verwijder logo functie
function deleteLogo() {
    if (!confirm('Logo verwijderen?')) return;
    
    fetch('{{ route("branding.deleteFile") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            field: 'logo_pad'
        })
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
        alert('Fout bij verwijderen: ' + error);
    });
}
</script>
@endsection