@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Bikefit bewerken - {{ $klant->voornaam}} {{ $klant->naam }}</h1>
        <div class="flex space-x-3">
            <a href="{{ route('klanten.show', $klant->id) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Terug naar klant
            </a>
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
        <div class="p-6">
            <!-- Bikefit update form placed before upload form -->
            <form id="bikefit-form" method="POST" action="{{ route('bikefit.update', ['klant' => $klant->id, 'bikefit' => $bikefit->id]) }}">
                @csrf
                @method('PUT')
                
                @include('bikefit._form', ['submitLabel' => '', 'isEdit' => true])

                <!-- Nieuw Uitleensysteem Component -->
                @include('components.bikefit-uitleensysteem')

                <!-- Buttons under uploaded files -->
                <div class="mt-6 mb-8">
                    <div class="bg-white border-t pt-4">
                        <div class="flex items-center justify-start gap-4">
                            <div>
                                <button type="button" onclick="submitBikefit('save_and_results')" class="inline-block text-white font-bold py-2 px-4 rounded shadow-md focus:outline-none" style="background-color:#16a34a!important;color:#ffffff!important;box-shadow:0 6px 12px rgba(6,95,70,0.15);border:1px solid rgba(0,0,0,0.06);">Opslaan</button>
                            </div>
                            <div>
                                <button type="button" onclick="submitBikefit('save_and_back')" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Terug</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

         </div>
     </div>

    <!-- Upload box (moved here - above uploaded files) -->
    <form method="POST" action="{{ route('bikefit.upload', ['klant' => $klant->id, 'bikefit' => $bikefit->id]) }}" enctype="multipart/form-data" class="mt-6 mb-6">
        @csrf
        <div class="mb-4 bg-white border border-gray-300 rounded p-4 shadow-sm">
            <label for="file" class="block text-sm font-medium text-gray-700">Bestand uploaden</label>
            <div class="flex items-center gap-4 mt-2">
                <input type="file" name="file" id="file" class="form-input" required>
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded shadow">Upload</button>
            </div>
            <p class="text-xs text-gray-500 mt-2">Toegestane bestanden: PDF, PNG, JPG. Max grootte 10MB.</p>
        </div>
    </form>

     <div class="mt-8">
         <h3 class="text-lg font-semibold text-gray-800 mb-4">Geüploade bestanden</h3>
         <ul class="list-disc list-inside">
             @foreach($bikefit->uploads as $upload)
                 <li class="mb-2 flex items-center gap-2">
                     <form method="POST" action="{{ route('uploads.destroy', $upload->id) }}" class="inline-block">
                         @csrf
                         @method('DELETE')
                         <button type="submit" class="bg-red-600 text-white font-bold py-1 px-2 rounded hover:bg-red-800">
                             X
                         </button>
                     </form>
                     <a href="{{ route('uploads.show', $upload->id) }}" target="_blank" class="text-blue-600 hover:underline">
                         {{ basename($upload->path) }}
                     </a>
                 </li>
             @endforeach
         </ul>
     </div>

    <script>
        function submitBikefit(actionName) {
            var form = document.getElementById('bikefit-form');
            if(!form) {
                console.log('Form not found!');
                return;
            }
            // Remove existing temp inputs if present
            var existing = document.querySelector('#bikefit-form input[name="' + actionName + '"]');
            if(existing) existing.parentNode.removeChild(existing);
            // Create hidden input
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = actionName;
            input.value = '1';
            form.appendChild(input);
            console.log('Submitting form with action:', actionName);
            form.submit();
        }

        // Beenlengteverschil toggle functionaliteit
        document.addEventListener('DOMContentLoaded', function() {
            const beenlengteSelect = document.querySelector('select[name="beenlengteverschil"]');
            const beenlengteCmField = document.querySelector('[name="beenlengteverschil_cm"]');
            
            if (beenlengteSelect && beenlengteCmField) {
                const cmFieldContainer = beenlengteCmField.closest('.mb-4') || beenlengteCmField.closest('.form-group') || beenlengteCmField.parentElement;
                
                function toggleBeenlengteCmField() {
                    if (beenlengteSelect.value === '1') {
                        cmFieldContainer.style.display = 'block';
                    } else {
                        cmFieldContainer.style.display = 'none';
                    }
                }
                
                beenlengteSelect.addEventListener('change', toggleBeenlengteCmField);
                toggleBeenlengteCmField(); // Initial state
            }
            
            // Steunzolen toggle functionaliteit
            const steunzolenSelect = document.querySelector('select[name="steunzolen"]');
            const steunzolenRedenField = document.querySelector('[name="steunzolen_reden"]');
            
            if (steunzolenSelect && steunzolenRedenField) {
                const redenFieldContainer = steunzolenRedenField.closest('.mb-4') || steunzolenRedenField.closest('.form-group') || steunzolenRedenField.parentElement;
                
                function toggleSteunzolenRedenField() {
                    if (steunzolenSelect.value === '1') {
                        redenFieldContainer.style.display = 'block';
                    } else {
                        redenFieldContainer.style.display = 'none';
                    }
                }
                
                steunzolenSelect.addEventListener('change', toggleSteunzolenRedenField);
                toggleSteunzolenRedenField(); // Initial state
            }
        });
    </script>

    @if(session('upload_success'))
        <div id="upload-success" class="mb-6 p-4 bg-green-100 border border-green-400 text-green-800 rounded">
            Bestand succesvol geüpload! <br>
            <a href="{{ session('upload_link') }}" class="underline text-blue-700" target="_blank">Bekijk het geüploade bestand</a>
        </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
console.log('🚀 AUTO-SAVE SCRIPT LOADED!');

// Auto-save functionaliteit voor bikefit formulieren
class BikefitAutoSave {
    constructor() {
        this.form = null;
        this.saveTimeout = null;
        this.lastSaved = null;
        this.isEdit = false;
        this.klantId = null;
        this.bikefitId = null;
        this.statusElement = null;
        
        this.init();
    }
    
    init() {
        console.log('🔧 BikefitAutoSave initializing...');
        console.log('📍 Current URL:', window.location.pathname);
        
        // Detecteer of we op een bikefit pagina zijn
        const path = window.location.pathname;
        const bikefitMatch = path.match(/\/klanten\/(\d+)\/bikefit(?:\/(\d+))?/);
        
        if (!bikefitMatch) {
            console.log('❌ Not on a bikefit page, auto-save disabled');
            return;
        }
        
        this.klantId = bikefitMatch[1];
        this.bikefitId = bikefitMatch[2] || null;
        this.isEdit = !!this.bikefitId;
        
        console.log('✅ Bikefit page detected:', {
            klantId: this.klantId,
            bikefitId: this.bikefitId,
            isEdit: this.isEdit
        });
        
        // Vind het formulier - zoek specifiek naar de bikefit form
        this.form = document.querySelector('form[action*="bikefit"]') || 
                   document.querySelector('form:not([action*="logout"])') ||
                   document.querySelector('form[method="POST"]');
        
        if (!this.form) {
            console.log('❌ No bikefit form found on page');
            return;
        }
        
        console.log('✅ Form found:', this.form);
        console.log('📋 Form action:', this.form.action);
        
        // Voeg status indicator toe
        this.addStatusIndicator();
        
        // Luister naar form changes
        this.attachEventListeners();
        
        console.log('🚀 Auto-save activated for', this.isEdit ? 'EDIT' : 'CREATE', 'mode');
    }
    
    addStatusIndicator() {
        // Voeg een subtiele status indicator toe
        const statusDiv = document.createElement('div');
        statusDiv.id = 'auto-save-status';
        statusDiv.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 10px 15px;
            font-size: 13px;
            color: #6c757d;
            z-index: 9999;
            opacity: 1;
            transition: opacity 0.3s ease;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            font-family: -apple-system, BlinkMacSystemFont, sans-serif;
        `;
        statusDiv.innerHTML = '💾 Auto-save ready';
        document.body.appendChild(statusDiv);
        this.statusElement = statusDiv;
        
        console.log('✅ Status indicator added');
        
        // Verberg na 3 seconden
        setTimeout(() => this.hideStatus(), 3000);
    }
    
    attachEventListeners() {
        var self = this;
        
        // Wacht 1 seconde zodat ALLE includes geladen zijn
        setTimeout(function() {
            var allInputs = document.querySelectorAll('input, select, textarea');
            var formInputs = [];
            
            for (var i = 0; i < allInputs.length; i++) {
                var input = allInputs[i];
                if (input.form && (input.form.id === 'bikefit-form' || (input.form.action && input.form.action.indexOf('bikefit') > -1))) {
                    formInputs.push(input);
                }
            }
            
            console.log('📝 Found ' + formInputs.length + ' form inputs (after 1000ms)');
            
            for (var j = 0; j < formInputs.length; j++) {
                (function(inp) {
                    inp.addEventListener('input', function() {
                        console.log('📝 Input: ' + inp.name);
                        self.scheduleAutoSave();
                    });
                    inp.addEventListener('change', function() {
                        console.log('🔄 Change: ' + inp.name);
                        self.scheduleAutoSave();
                    });
                })(formInputs[j]);
            }
        }, 1000);
    }
    
    scheduleAutoSave() {
        console.log('⏰ Scheduling auto-save...');
        
        // Cancel bestaande timeout
        if (this.saveTimeout) {
            clearTimeout(this.saveTimeout);
            console.log('⏰ Cancelled previous save timeout');
        }
        
        // Schedule nieuwe save na 3 seconden
        this.saveTimeout = setTimeout(() => {
            this.performAutoSave();
        }, 3000);
        
        this.showStatus('⏳ Auto-save in 3 seconds...', '#ffc107');
    }
    
    async performAutoSave() {
        console.log('💾 Starting auto-save...');
        
        try {
            this.showStatus('💾 Saving...', '#007bff');
            
            const formData = new FormData(this.form);
            
            // Verwijder _method veld voor auto-save (we willen altijd POST)
            formData.delete('_method');
            
            // Voeg extra debug info toe
            console.log('📦 FormData contents:');
            for (let [key, value] of formData.entries()) {
                console.log(`  ${key}: ${value}`);
            }
            
            // Bepaal de juiste URL
            const url = this.isEdit 
                ? `/klanten/${this.klantId}/bikefit/${this.bikefitId}/auto-save`
                : `/klanten/${this.klantId}/bikefit/auto-save`;
            
            console.log('🌐 Sending to URL:', url);
            
            // Zorg voor CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                             document.querySelector('input[name="_token"]')?.value;
            
            if (!csrfToken) {
                throw new Error('No CSRF token found');
            }
            
            console.log('🔐 CSRF token found:', csrfToken.substring(0, 10) + '...');
            
            const response = await fetch(url, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });
            
            console.log('📡 Response status:', response.status);
            console.log('📡 Response headers:', Object.fromEntries(response.headers.entries()));
            
            const responseText = await response.text();
            console.log('📡 Raw response:', responseText);
            
            let result;
            try {
                result = JSON.parse(responseText);
            } catch (e) {
                throw new Error(`Invalid JSON response: ${responseText}`);
            }
            
            if (response.ok && result.success) {
                this.lastSaved = new Date();
                console.log('✅ Auto-save successful:', result);
                this.showStatus('✅ ' + result.message, '#28a745');
                
                setTimeout(() => this.hideStatus(), 4000);
            } else {
                console.error('❌ Auto-save failed:', result);
                this.showStatus('❌ ' + (result.message || 'Save failed'), '#dc3545');
                setTimeout(() => this.hideStatus(), 6000);
            }
            
        } catch (error) {
            console.error('💥 Auto-save error:', error);
            this.showStatus('❌ Connection error: ' + error.message, '#dc3545');
            setTimeout(() => this.hideStatus(), 6000);
        }
    }
    
    showStatus(message, color = '#6c757d') {
        if (!this.statusElement) return;
        
        this.statusElement.innerHTML = message;
        this.statusElement.style.borderColor = color;
        this.statusElement.style.color = color;
        this.statusElement.style.opacity = '1';
        
        console.log('📢 Status:', message);
    }
    
    hideStatus() {
        if (!this.statusElement) return;
        this.statusElement.style.opacity = '0';
    }
}

// Start auto-save
console.log('🎯 Starting auto-save initialization...');
new BikefitAutoSave();
</script>
@endsection
