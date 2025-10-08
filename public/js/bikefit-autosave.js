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
        
        // Vind het formulier
        this.form = document.querySelector('form[method="POST"]');
        if (!this.form) {
            console.log('❌ No POST form found on page');
            return;
        }
        
        console.log('✅ Form found:', this.form);
        
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
        // Luister naar alle form inputs
        const inputs = this.form.querySelectorAll('input, select, textarea');
        
        console.log(`📝 Found ${inputs.length} form inputs to monitor`);
        
        inputs.forEach((input, index) => {
            input.addEventListener('input', () => {
                console.log(`📝 Input changed: ${input.name || input.id || 'unnamed'}`);
                this.scheduleAutoSave();
            });
            input.addEventListener('change', () => {
                console.log(`🔄 Change event: ${input.name || input.id || 'unnamed'}`);
                this.scheduleAutoSave();
            });
        });
    }
    
    scheduleAutoSave() {
        console.log('⏰ Scheduling auto-save...');
        
        // Cancel bestaande timeout
        if (this.saveTimeout) {
            clearTimeout(this.saveTimeout);
            console.log('⏰ Cancelled previous save timeout');
        }
        
        // Schedule nieuwe save na 3 seconden (iets langer voor testing)
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
                
                // Als we een nieuwe bikefit hebben gemaakt, update de URL en mode
                if (!this.isEdit && result.bikefit_id) {
                    console.log('🔄 Switching to edit mode with ID:', result.bikefit_id);
                    this.bikefitId = result.bikefit_id;
                    this.isEdit = true;
                    // Update browser history zonder page reload
                    const newUrl = `/klanten/${this.klantId}/bikefit/${this.bikefitId}/edit`;
                    window.history.replaceState({}, '', newUrl);
                    console.log('🌐 URL updated to:', newUrl);
                }
                
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

// Initialiseer auto-save wanneer DOM geladen is
console.log('🚀 Loading BikefitAutoSave...');

function initAutoSave() {
    console.log('🎯 Initializing BikefitAutoSave...');
    new BikefitAutoSave();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAutoSave);
} else {
    initAutoSave();
}