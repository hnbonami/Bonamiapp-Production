{{-- Edit Upload Modal Component --}}
<div id="editUploadModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center;">
    <div class="bg-white rounded-lg shadow-xl" style="width:90%;max-width:600px;padding:2rem;position:relative;">
        <button onclick="closeEditUploadModal()" style="position:absolute;top:1rem;right:1rem;background:none;border:none;font-size:1.5rem;cursor:pointer;color:#6b7280;">&times;</button>
        
        <h3 style="font-size:1.25rem;font-weight:600;margin-bottom:1.5rem;">Document Bewerken</h3>
        
        <form id="editUploadForm" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div style="margin-bottom:1rem;">
                <label for="edit-naam" class="block text-sm font-medium text-gray-700 mb-2">Naam (optioneel)</label>
                <input type="text" name="naam" id="edit-naam" placeholder="Document naam..."
                       class="block w-full text-sm border border-gray-300 rounded-lg" style="padding:0.625rem 0.75rem;">
            </div>
            
            <div style="margin-bottom:1rem;">
                <label for="edit-beschrijving" class="block text-sm font-medium text-gray-700 mb-2">Beschrijving (optioneel)</label>
                <textarea name="beschrijving" id="edit-beschrijving" placeholder="Korte beschrijving..." rows="3"
                          class="block w-full text-sm border border-gray-300 rounded-lg" style="padding:0.625rem 0.75rem;"></textarea>
            </div>
            
            {{-- TOEGANGSRECHTEN --}}
            <div style="margin-bottom:1.5rem;">
                <label for="edit-toegang" class="block text-sm font-medium text-gray-700 mb-2">
                    Toegang <span style="color:#ef4444;">*</span>
                </label>
                <select name="toegang" id="edit-toegang" required
                        class="block w-full text-sm border border-gray-300 rounded-lg" style="padding:0.625rem 0.75rem;">
                    <option value="alle_medewerkers">🏢 Alle medewerkers</option>
                    <option value="alleen_mezelf">🔒 Alleen mezelf</option>
                    <option value="klant">👤 Klant + mezelf</option>
                    <option value="iedereen">🌍 Iedereen</option>
                </select>
                <div style="margin-top:0.5rem;padding:0.625rem;background:#eff6ff;border-radius:6px;font-size:0.75rem;color:#1e40af;">
                    <p style="margin:0 0 0.25rem 0;"><strong>🔒 Alleen mezelf:</strong> Alleen jij hebt toegang</p>
                    <p style="margin:0 0 0.25rem 0;"><strong>👤 Klant + mezelf:</strong> De gekoppelde klant en jij</p>
                    <p style="margin:0 0 0.25rem 0;"><strong>🏢 Alle medewerkers:</strong> Alle medewerkers en admins</p>
                    <p style="margin:0;"><strong>🌍 Iedereen:</strong> Publiek toegankelijk</p>
                </div>
            </div>
            
            <div style="display:flex;gap:0.75rem;justify-content:flex-end;">
                <button type="button" onclick="closeEditUploadModal()" style="background:#e5e7eb;color:#374151;padding:0.625rem 1.5rem;border-radius:6px;font-weight:600;border:none;cursor:pointer;">
                    Annuleren
                </button>
                <button type="submit" style="background:#c8e1eb;color:#111;padding:0.625rem 1.5rem;border-radius:6px;font-weight:600;border:none;cursor:pointer;">
                    Opslaan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditUploadModal(documentId, naam, beschrijving, toegang) {
    const modal = document.getElementById('editUploadModal');
    const form = document.getElementById('editUploadForm');
    
    if (modal && form) {
        // Stel form action in met juiste route
        form.action = `/klanten/{{ $klant->id }}/documenten/${documentId}`;
        
        // Vul formulier met huidige waarden
        document.getElementById('edit-naam').value = naam || '';
        document.getElementById('edit-beschrijving').value = beschrijving || '';
        document.getElementById('edit-toegang').value = toegang || 'alle_medewerkers';
        
        // Toon modal
        modal.style.display = 'flex';
    }
}

function closeEditUploadModal() {
    const modal = document.getElementById('editUploadModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

// Sluit modal bij klik buiten de modal
const editUploadModal = document.getElementById('editUploadModal');
if (editUploadModal) {
    editUploadModal.addEventListener('click', function(e) {
        if (e.target === this) {
            closeEditUploadModal();
        }
    });
}

// Sluit modal met ESC toets
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeEditUploadModal();
    }
});
</script>