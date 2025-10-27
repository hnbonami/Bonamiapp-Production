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
                
                @if($organisatie->logo_path)
                    <div class="mb-4 p-4 bg-gray-50 rounded-lg">
                        <p class="text-sm text-gray-600 mb-2">Huidige logo:</p>
                        <img src="{{ $organisatie->logo_url }}" alt="Logo" class="max-h-20 mb-2">
                        <form action="{{ route('admin.branding.deleteLogo') }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 text-sm hover:underline" onclick="return confirm('Logo verwijderen?')">
                                🗑️ Verwijderen
                            </button>
                        </form>
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
                <a href="{{ route('admin.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                    ← Terug
                </a>
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
</script>
@endsection