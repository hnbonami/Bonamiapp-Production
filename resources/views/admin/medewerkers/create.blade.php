@extends('layouts.app')

@section('title', 'Nieuwe Medewerker - Bonami Sportcoaching')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-800">➕ Nieuwe Medewerker</h1>
        <a href="{{ route('medewerkers.index') }}" class="text-gray-600 hover:text-gray-800">← Terug</a>
    </div>

    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white shadow rounded-lg p-6">
        <form method="POST" action="{{ route('medewerkers.store') }}" class="space-y-6">
            @csrf
            
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label for="voornaam" class="block text-sm font-medium text-gray-700">Voornaam *</label>
                    <input type="text" id="voornaam" name="voornaam" value="{{ old('voornaam') }}" required
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                </div>

                <div>
                    <label for="achternaam" class="block text-sm font-medium text-gray-700">Achternaam *</label>
                    <input type="text" id="achternaam" name="achternaam" value="{{ old('achternaam') }}" required
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                </div>
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email *</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
            </div>

            <div>
                <label for="telefoon" class="block text-sm font-medium text-gray-700">Telefoon</label>
                <input type="tel" id="telefoon" name="telefoon" value="{{ old('telefoon') }}"
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
            </div>

            <div>
                <label for="geslacht" class="block text-sm font-medium text-gray-700">Geslacht</label>
                <select id="geslacht" name="geslacht" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    <option value="">Selecteer geslacht...</option>
                    <option value="Man" {{ strtolower(old('geslacht', '')) === 'man' ? 'selected' : '' }}>Man</option>
                    <option value="Vrouw" {{ strtolower(old('geslacht', '')) === 'vrouw' ? 'selected' : '' }}>Vrouw</option>
                    <option value="Anders" {{ strtolower(old('geslacht', '')) === 'anders' ? 'selected' : '' }}>Anders</option>
                </select>
            </div>

            <div>
                <label for="rol" class="block text-sm font-medium text-gray-700">Rol *</label>
                <select id="rol" name="rol" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    <option value="">Selecteer rol...</option>
                    <option value="medewerker" {{ old('rol') === 'medewerker' ? 'selected' : '' }}>Medewerker</option>
                    <option value="stagiair" {{ old('rol') === 'stagiair' ? 'selected' : '' }}>Stagiair</option>
                    <option value="admin" {{ old('rol') === 'admin' ? 'selected' : '' }}>Administrator</option>
                </select>
            </div>

            <!-- Profielfoto -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Profielfoto</label>
                <div class="flex items-center gap-4">
                    <!-- Avatar Preview -->
                    <div class="relative flex-shrink-0" style="width:80px;height:80px;">
                        <label for="avatarInput" style="cursor:pointer;display:block;position:relative;">
                            <div id="avatarPreviewContainer" class="rounded-lg bg-gray-200 flex items-center justify-center text-gray-600 font-semibold" style="width:80px;height:80px;font-size:32px;">
                                ?
                            </div>
                            <!-- Camera overlay icon -->
                            <div style="position:absolute;bottom:4px;right:4px;background:rgba(200,225,235,0.95);border-radius:50%;width:28px;height:28px;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 4px rgba(0,0,0,0.1);">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#333" stroke-width="2">
                                    <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
                                    <circle cx="12" cy="13" r="4"></circle>
                                </svg>
                            </div>
                        </label>
                        <input type="file" name="avatar" id="avatarInput" accept="image/*" style="display:none;">
                    </div>
                    
                    <!-- File info -->
                    <div class="flex-1">
                        <span id="avatarFileName" class="text-sm text-gray-600">Geen bestand gekozen</span>
                        <p class="text-xs text-gray-500 mt-1">Klik op de avatar om een foto te kiezen</p>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-4">
                <a href="{{ route('medewerkers.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">Annuleren</a>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Aanmaken</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('avatarInput');
    const nameEl = document.getElementById('avatarFileName');
    const previewContainer = document.getElementById('avatarPreviewContainer');
    const voornaamInput = document.getElementById('voornaam');
    
    // Update preview placeholder met voornaam
    if (voornaamInput) {
        voornaamInput.addEventListener('input', function() {
            const voornaam = this.value.trim();
            if (voornaam && !input.files.length) {
                previewContainer.textContent = voornaam.charAt(0).toUpperCase();
            }
        });
    }
    
    // Handle file selection
    if (input) {
        input.addEventListener('change', function() {
            const file = this.files && this.files[0];
            if (file) {
                nameEl.textContent = file.name;
                
                // Show image preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewContainer.innerHTML = `<img src="${e.target.result}" alt="Preview" class="rounded-lg object-cover" style="width:80px;height:80px;" />`;
                };
                reader.readAsDataURL(file);
            } else {
                nameEl.textContent = 'Geen bestand gekozen';
                const voornaam = voornaamInput ? voornaamInput.value.trim() : '';
                previewContainer.innerHTML = voornaam ? voornaam.charAt(0).toUpperCase() : '?';
                previewContainer.className = 'rounded-lg bg-gray-200 flex items-center justify-center text-gray-600 font-semibold';
                previewContainer.style.cssText = 'width:80px;height:80px;font-size:32px;';
            }
        });
    }
});
</script>
@endsection