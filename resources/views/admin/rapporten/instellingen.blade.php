@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">📄 Rapporten Configureren</h1>
                <p class="text-gray-600 mt-2">Personaliseer je rapporten met eigen header, footer, logo en huisstijl</p>
            </div>
            <a href="{{ route('admin.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                ← Terug naar Admin
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('admin.rapporten.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <!-- Tab Navigatie -->
        <div class="mb-6 border-b border-gray-200">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <button type="button" onclick="switchTab('algemeen')" class="tab-button active border-b-2 py-4 px-1 text-sm font-medium" data-tab="algemeen">
                    Algemeen
                </button>
                <button type="button" onclick="switchTab('branding')" class="tab-button border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700" data-tab="branding">
                    Branding & Kleuren
                </button>
                <button type="button" onclick="switchTab('teksten')" class="tab-button border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700" data-tab="teksten">
                    Teksten
                </button>
                <button type="button" onclick="switchTab('contact')" class="tab-button border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700" data-tab="contact">
                    Contactgegevens
                </button>
            </nav>
        </div>

        <!-- Tab Content: Algemeen -->
        <div id="tab-algemeen" class="tab-content">
            <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">⚙️ Algemene Instellingen</h2>
                
                <!-- Header Tekst -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Header Tekst (elke pagina)</label>
                    <input type="text" name="header_tekst" value="{{ old('header_tekst', $instellingen->header_tekst) }}" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                           placeholder="Bijv: Performance Pulse Rapport">
                    @error('header_tekst')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Footer Tekst -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Footer Tekst (elke pagina)</label>
                    <input type="text" name="footer_tekst" value="{{ old('footer_tekst', $instellingen->footer_tekst) }}" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                           placeholder="Bijv: © 2024 Performance Pulse - Sportcoaching">
                    @error('footer_tekst')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Lettertype -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Lettertype</label>
                    <select name="lettertype" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="Arial" {{ old('lettertype', $instellingen->lettertype) == 'Arial' ? 'selected' : '' }}>Arial</option>
                        <option value="Tahoma" {{ old('lettertype', $instellingen->lettertype) == 'Tahoma' ? 'selected' : '' }}>Tahoma</option>
                        <option value="Calibri" {{ old('lettertype', $instellingen->lettertype) == 'Calibri' ? 'selected' : '' }}>Calibri</option>
                        <option value="Helvetica" {{ old('lettertype', $instellingen->lettertype) == 'Helvetica' ? 'selected' : '' }}>Helvetica</option>
                    </select>
                </div>

                <!-- Paginanummering -->
                <div class="mb-6">
                    <label class="flex items-center mb-4">
                        <input type="checkbox" name="paginanummering_tonen" value="1" 
                               {{ old('paginanummering_tonen', $instellingen->paginanummering_tonen) ? 'checked' : '' }}
                               class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <span class="ml-2 text-sm font-medium text-gray-700">Paginanummering tonen</span>
                    </label>
                    
                    <label class="block text-sm font-medium text-gray-700 mb-2">Positie paginanummering</label>
                    <select name="paginanummering_positie" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="rechtsonder" {{ old('paginanummering_positie', $instellingen->paginanummering_positie) == 'rechtsonder' ? 'selected' : '' }}>Rechtsonder</option>
                        <option value="rechtsboven" {{ old('paginanummering_positie', $instellingen->paginanummering_positie) == 'rechtsboven' ? 'selected' : '' }}>Rechtsboven</option>
                        <option value="linksonder" {{ old('paginanummering_positie', $instellingen->paginanummering_positie) == 'linksonder' ? 'selected' : '' }}>Linksonder</option>
                        <option value="linksboven" {{ old('paginanummering_positie', $instellingen->paginanummering_positie) == 'linksboven' ? 'selected' : '' }}>Linksboven</option>
                        <option value="midden" {{ old('paginanummering_positie', $instellingen->paginanummering_positie) == 'midden' ? 'selected' : '' }}>Midden</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Tab Content: Branding -->
        <div id="tab-branding" class="tab-content hidden">
            <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">🎨 Branding & Kleuren</h2>
                
                <!-- Logo Upload -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Logo (PNG, JPG, SVG - max 2MB)</label>
                    @if($instellingen->logo_path)
                        <div class="mb-4 p-4 bg-gray-50 rounded-lg flex items-center justify-between">
                            <img src="{{ asset('storage/' . $instellingen->logo_path) }}" alt="Logo" class="h-16">
                            <button type="button" onclick="deleteLogo()" class="text-red-600 hover:text-red-800">
                                🗑️ Verwijderen
                            </button>
                        </div>
                    @endif
                    <input type="file" name="logo" accept="image/png,image/jpeg,image/jpg,image/svg+xml" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    @error('logo')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Voorblad Foto -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Voorblad Foto (PNG, JPG - max 5MB)</label>
                    @if($instellingen->voorblad_foto_path)
                        <div class="mb-4 p-4 bg-gray-50 rounded-lg flex items-center justify-between">
                            <img src="{{ asset('storage/' . $instellingen->voorblad_foto_path) }}" alt="Voorblad" class="h-32">
                            <button type="button" onclick="deleteVoorbladFoto()" class="text-red-600 hover:text-red-800">
                                🗑️ Verwijderen
                            </button>
                        </div>
                    @endif
                    <input type="file" name="voorblad_foto" accept="image/png,image/jpeg,image/jpg" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    @error('voorblad_foto')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Kleuren -->
                <div class="grid grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Primaire Kleur</label>
                        <div class="flex items-center gap-2">
                            <input type="color" name="primaire_kleur" value="{{ old('primaire_kleur', $instellingen->primaire_kleur) }}" 
                                   class="w-16 h-10 border border-gray-300 rounded cursor-pointer">
                            <input type="text" value="{{ old('primaire_kleur', $instellingen->primaire_kleur) }}" 
                                   class="flex-1 px-4 py-2 border border-gray-300 rounded-lg" readonly>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Secundaire Kleur</label>
                        <div class="flex items-center gap-2">
                            <input type="color" name="secundaire_kleur" value="{{ old('secundaire_kleur', $instellingen->secundaire_kleur) }}" 
                                   class="w-16 h-10 border border-gray-300 rounded cursor-pointer">
                            <input type="text" value="{{ old('secundaire_kleur', $instellingen->secundaire_kleur) }}" 
                                   class="flex-1 px-4 py-2 border border-gray-300 rounded-lg" readonly>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab Content: Teksten -->
        <div id="tab-teksten" class="tab-content hidden">
            <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">📝 Rapport Teksten</h2>
                
                <!-- Inleidende Tekst -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Inleidende Tekst (voorblad/eerste pagina)</label>
                    <textarea name="inleidende_tekst" rows="5" 
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                              placeholder="Bijv: Welkom bij jouw persoonlijke bikefit rapport...">{{ old('inleidende_tekst', $instellingen->inleidende_tekst) }}</textarea>
                </div>

                <!-- Laatste Blad Tekst -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Laatste Blad Tekst (afsluitende pagina)</label>
                    <textarea name="laatste_blad_tekst" rows="5" 
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                              placeholder="Bijv: Bedankt voor je vertrouwen...">{{ old('laatste_blad_tekst', $instellingen->laatste_blad_tekst) }}</textarea>
                </div>

                <!-- Disclaimer Tekst -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Disclaimer Tekst (juridische tekst)</label>
                    <textarea name="disclaimer_tekst" rows="4" 
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                              placeholder="Bijv: Dit rapport is uitsluitend bedoeld voor...">{{ old('disclaimer_tekst', $instellingen->disclaimer_tekst) }}</textarea>
                </div>
            </div>
        </div>

        <!-- Tab Content: Contact -->
        <div id="tab-contact" class="tab-content hidden">
            <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">📞 Contactgegevens & QR Code</h2>
                
                <!-- Contactgegevens -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Adres</label>
                    <input type="text" name="contact_adres" value="{{ old('contact_adres', $instellingen->contact_adres) }}" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                           placeholder="Bijv: Sportlaan 123, 1234 AB Amsterdam">
                </div>

                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Telefoonnummer</label>
                        <input type="text" name="contact_telefoon" value="{{ old('contact_telefoon', $instellingen->contact_telefoon) }}" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                               placeholder="Bijv: 06-12345678">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">E-mailadres</label>
                        <input type="email" name="contact_email" value="{{ old('contact_email', $instellingen->contact_email) }}" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                               placeholder="Bijv: info@performancepulse.nl">
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Website</label>
                    <input type="url" name="contact_website" value="{{ old('contact_website', $instellingen->contact_website) }}" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                           placeholder="Bijv: https://www.performancepulse.nl">
                </div>

                <div class="mb-6">
                    <label class="flex items-center">
                        <input type="checkbox" name="contactgegevens_in_footer" value="1" 
                               {{ old('contactgegevens_in_footer', $instellingen->contactgegevens_in_footer) ? 'checked' : '' }}
                               class="w-4 h-4 text-blue-600 border-gray-300 rounded">
                        <span class="ml-2 text-sm font-medium text-gray-700">Contactgegevens in footer tonen</span>
                    </label>
                </div>

                <hr class="my-8">

                <!-- QR Code -->
                <h3 class="text-lg font-semibold text-gray-800 mb-4">📲 QR Code Instellingen</h3>
                
                <div class="mb-6">
                    <label class="flex items-center mb-4">
                        <input type="checkbox" name="qr_code_tonen" value="1" 
                               {{ old('qr_code_tonen', $instellingen->qr_code_tonen) ? 'checked' : '' }}
                               class="w-4 h-4 text-blue-600 border-gray-300 rounded">
                        <span class="ml-2 text-sm font-medium text-gray-700">QR code tonen op rapporten</span>
                    </label>
                    
                    <label class="block text-sm font-medium text-gray-700 mb-2">QR Code URL (link waar QR code naartoe gaat)</label>
                    <input type="url" name="qr_code_url" value="{{ old('qr_code_url', $instellingen->qr_code_url) }}" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                           placeholder="Bijv: https://www.performancepulse.nl/contact">
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">QR Code Positie</label>
                    <select name="qr_code_positie" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        <option value="rechtsonder" {{ old('qr_code_positie', $instellingen->qr_code_positie) == 'rechtsonder' ? 'selected' : '' }}>Rechtsonder</option>
                        <option value="linksboven" {{ old('qr_code_positie', $instellingen->qr_code_positie) == 'linksboven' ? 'selected' : '' }}>Linksboven</option>
                        <option value="footer" {{ old('qr_code_positie', $instellingen->qr_code_positie) == 'footer' ? 'selected' : '' }}>In Footer</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Actie Knoppen -->
        <div class="flex justify-between items-center mt-8">
            <button type="button" onclick="resetInstellingen()" 
                    class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                🔄 Reset naar Standaard
            </button>
            <button type="submit" class="px-8 py-3 text-white rounded-lg hover:opacity-90 transition" 
                    style="background-color: #c8e1eb; color: #111;">
                💾 Instellingen Opslaan
            </button>
        </div>
    </form>
</div>

<script>
    // Tab switching
    function switchTab(tabName) {
        // Hide all tabs
        document.querySelectorAll('.tab-content').forEach(tab => {
            tab.classList.add('hidden');
        });
        
        // Remove active class from all buttons
        document.querySelectorAll('.tab-button').forEach(btn => {
            btn.classList.remove('active', 'border-blue-500', 'text-blue-600');
            btn.classList.add('border-transparent', 'text-gray-500');
        });
        
        // Show selected tab
        document.getElementById('tab-' + tabName).classList.remove('hidden');
        
        // Add active class to clicked button
        const activeBtn = document.querySelector(`[data-tab="${tabName}"]`);
        activeBtn.classList.add('active', 'border-blue-500', 'text-blue-600');
        activeBtn.classList.remove('border-transparent', 'text-gray-500');
    }

    // Delete logo
    function deleteLogo() {
        if (!confirm('Weet je zeker dat je het logo wilt verwijderen?')) return;
        
        fetch('{{ route("admin.rapporten.delete-logo") }}', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }

    // Delete voorblad foto
    function deleteVoorbladFoto() {
        if (!confirm('Weet je zeker dat je de voorblad foto wilt verwijderen?')) return;
        
        fetch('{{ route("admin.rapporten.delete-voorblad-foto") }}', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }

    // Reset instellingen
    function resetInstellingen() {
        if (!confirm('Weet je zeker dat je alle instellingen wilt resetten naar standaard waarden? Dit kan niet ongedaan worden gemaakt.')) return;
        
        window.location.href = '{{ route("admin.rapporten.reset") }}';
    }

    // Color picker sync met text input
    document.querySelectorAll('input[type="color"]').forEach(colorPicker => {
        const textInput = colorPicker.nextElementSibling;
        
        colorPicker.addEventListener('input', (e) => {
            textInput.value = e.target.value;
        });
    });
</script>

<style>
    .tab-button.active {
        border-color: #3b82f6 !important;
        color: #3b82f6 !important;
    }
</style>
@endsection
