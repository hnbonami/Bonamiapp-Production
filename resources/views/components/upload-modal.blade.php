{{-- Document Upload Modal Component --}}
<div id="uploadModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center pb-3 border-b">
            <h3 class="text-lg font-semibold text-gray-900">Document Uploaden</h3>
            <button onclick="closeUploadModal()" class="text-gray-400 hover:text-gray-500">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form id="uploadForm" action="{{ $action ?? '/uploads' }}" method="POST" enctype="multipart/form-data" class="mt-4">
            @csrf
            
            {{-- Hidden fields voor klant/bikefit koppeling --}}
            @if(isset($klantId))
                <input type="hidden" name="klant_id" value="{{ $klantId }}">
            @endif
            @if(isset($bikefitId))
                <input type="hidden" name="bikefit_id" value="{{ $bikefitId }}">
            @endif

            {{-- Bestand selecteren --}}
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Selecteer bestand <span class="text-red-500">*</span>
                </label>
                <input 
                    type="file" 
                    name="file" 
                    required 
                    onchange="document.getElementById('filename-display').textContent = this.files[0]?.name || 'geen bestand geselecteerd'"
                    class="hidden" 
                    id="fileInput"
                />
                <button 
                    type="button" 
                    onclick="document.getElementById('fileInput').click()"
                    class="w-full px-4 py-2 border-2 border-dashed border-gray-300 rounded-lg text-sm text-gray-600 hover:border-blue-500 hover:text-blue-600 transition"
                >
                    📎 Klik om bestand te selecteren
                </button>
                <p id="filename-display" class="text-xs text-gray-500 mt-1 italic">geen bestand geselecteerd</p>
                @error('file')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Naam --}}
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Naam (optioneel)
                </label>
                <input 
                    type="text" 
                    name="naam" 
                    placeholder="Document naam..." 
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
            </div>

            {{-- Beschrijving --}}
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Beschrijving (optioneel)
                </label>
                <textarea 
                    name="beschrijving" 
                    rows="2" 
                    placeholder="Korte beschrijving..."
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                ></textarea>
            </div>

            {{-- TOEGANGSRECHTEN - NIEUW! --}}
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Toegang <span class="text-red-500">*</span>
                </label>
                <select 
                    name="toegang" 
                    required 
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <option value="alle_medewerkers" selected>🏢 Alle medewerkers</option>
                    <option value="alleen_mezelf">🔒 Alleen mezelf</option>
                    <option value="klant">👤 Klant + mezelf</option>
                    <option value="iedereen">🌍 Iedereen</option>
                </select>
                <div class="mt-2 p-2 bg-blue-50 rounded text-xs text-gray-600 space-y-1">
                    <p><strong>🔒 Alleen mezelf:</strong> Alleen jij hebt toegang</p>
                    <p><strong>👤 Klant + mezelf:</strong> De gekoppelde klant en jij</p>
                    <p><strong>🏢 Alle medewerkers:</strong> Alle medewerkers en admins</p>
                    <p><strong>🌍 Iedereen:</strong> Publiek toegankelijk</p>
                </div>
            </div>

            {{-- Cover foto checkbox --}}
            @if(isset($showCoverOption) && $showCoverOption)
            <div class="mb-4">
                <label class="inline-flex items-center">
                    <input 
                        type="checkbox" 
                        name="is_cover" 
                        value="1" 
                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                    />
                    <span class="ml-2 text-sm text-gray-700">Markeer als cover afbeelding</span>
                </label>
            </div>
            @endif

            {{-- Actie knoppen --}}
            <div class="flex justify-end gap-3 pt-4 border-t">
                <button 
                    type="button" 
                    onclick="closeUploadModal()"
                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition text-sm font-medium"
                >
                    Annuleren
                </button>
                <button 
                    type="submit"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-medium"
                >
                    Uploaden
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openUploadModal() {
    document.getElementById('uploadModal').classList.remove('hidden');
}

function closeUploadModal() {
    document.getElementById('uploadModal').classList.add('hidden');
    document.getElementById('uploadForm').reset();
    document.getElementById('filename-display').textContent = 'geen bestand geselecteerd';
}

// Sluit modal bij klikken buiten de modal
document.getElementById('uploadModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeUploadModal();
    }
});

// Sluit modal met ESC toets
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeUploadModal();
    }
});
</script>
