// Editable Results functionaliteit voor bikefit results pagina
class EditableResults {
    constructor() {
        this.klantId = null;
        this.bikefitId = null;
        this.statusElement = null;
        this.modifiedFields = new Set();
        
        this.init();
    }
    
    init() {
        console.log('🔧 EditableResults initializing...');
        
        // Detecteer of we op een results pagina zijn
        const path = window.location.pathname;
        const resultsMatch = path.match(/\/klanten\/(\d+)\/bikefit\/(\d+)\/results/);
        
        if (!resultsMatch) {
            console.log('❌ Not on a results page, editable results disabled');
            return;
        }
        
        this.klantId = resultsMatch[1];
        this.bikefitId = resultsMatch[2];
        
        console.log('✅ Results page detected:', {
            klantId: this.klantId,
            bikefitId: this.bikefitId
        });
        
        // Voeg section buttons toe
        setTimeout(() => {
            this.addSectionButtons();
            // Initialiseer alle input velden direct
            this.findAllNumericValues();
        }, 2000);
        
        console.log('🚀 Editable results activated');
    }
    
    addSectionButtons() {
        console.log('🔘 Adding section-specific save/reset buttons...');
        
        // Voeg save/reset buttons toe voor elke sectie
        this.addButtonsForContext('prognose', 'PROGNOSE', '#2563eb');
        this.addButtonsForContext('voor', 'VOOR', '#dc2626'); 
        this.addButtonsForContext('na', 'NA', '#16a34a');
        
        console.log('🔘 Section buttons added');
    }
    
    addButtonsForContext(context, label, color) {
        // Zoek de tabel/sectie voor deze context
        const contextTable = this.getTableForContext(context);
        
        if (!contextTable) {
            console.log(`❌ No table found for context: ${context}`);
            return;
        }
        
        // Maak button container
        const buttonContainer = document.createElement('div');
        buttonContainer.style.cssText = `
            margin-top: 10px;
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 6px;
        `;
        
        // Save button
        const saveButton = document.createElement('button');
        saveButton.innerHTML = `💾 SAVE ${label}`;
        saveButton.style.cssText = `
            background: ${color};
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            cursor: pointer;
            transition: opacity 0.2s;
        `;
        saveButton.addEventListener('click', () => this.saveContext(context));
        saveButton.addEventListener('mouseenter', () => saveButton.style.opacity = '0.8');
        saveButton.addEventListener('mouseleave', () => saveButton.style.opacity = '1');
        
        // Reset button  
        const resetButton = document.createElement('button');
        resetButton.innerHTML = `🔄 RESET ${label}`;
        resetButton.style.cssText = `
            background: #6b7280;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            cursor: pointer;
            transition: opacity 0.2s;
        `;
        resetButton.addEventListener('click', () => this.resetContext(context));
        resetButton.addEventListener('mouseenter', () => resetButton.style.opacity = '0.8');
        resetButton.addEventListener('mouseleave', () => resetButton.style.opacity = '1');
        
        buttonContainer.appendChild(saveButton);
        buttonContainer.appendChild(resetButton);
        
        // Voeg toe na de tabel
        contextTable.parentElement.appendChild(buttonContainer);
        
        console.log(`✅ Buttons added for ${context}`);
    }
    
    getTableForContext(context) {
        const tables = document.querySelectorAll('table');
        
        // Aanname: table 0 = prognose, table 1 = voor, table 2 = na
        switch(context) {
            case 'prognose': return tables[0] || null;
            case 'voor': return tables[1] || null; 
            case 'na': return tables[2] || null;
            default: return null;
        }
    }
    
    saveContext(context) {
        console.log(`💾 Saving context: ${context}`);
        
        // Verzamel alle gewijzigde waarden voor deze context
        const contextInputs = document.querySelectorAll(`input[data-context="${context}"]`);
        
        if (contextInputs.length === 0) {
            alert(`❌ No input fields found for ${context}`);
            return;
        }
        
        const values = {};
        let hasChanges = false;
        
        console.log(`🔍 Checking ${contextInputs.length} inputs for context ${context}:`);
        
        contextInputs.forEach(input => {
            const fieldName = input.getAttribute('data-field');
            const originalValue = input.getAttribute('data-original');
            const currentValue = input.value;
            
            console.log(`  ${fieldName}: "${originalValue}" → "${currentValue}"`);
            
            // Check voor wijzigingen OF als we expliciet willen forceren
            if (currentValue !== originalValue || currentValue === '-1' || originalValue === '-1') {
                // Converteer naar number, maar alleen als het een geldige waarde is
                const numericValue = parseFloat(currentValue);
                if (!isNaN(numericValue)) {
                    values[fieldName] = numericValue;
                    hasChanges = true;
                    console.log(`  ✅ ${fieldName} = ${numericValue} (was ${originalValue})`);
                } else {
                    console.log(`  ⚠️ Skipping ${fieldName}: invalid number "${currentValue}"`);
                }
            }
        });
        
        // Voor debug: allow forceful save als er geen changes zijn
        if (!hasChanges) {
            const forceAll = confirm(`ℹ️ No changes detected for ${context.toUpperCase()}.\n\nForce save ALL current values?\n(Useful for debugging)`);
            
            if (forceAll) {
                contextInputs.forEach(input => {
                    const fieldName = input.getAttribute('data-field');
                    const currentValue = input.value;
                    const numericValue = parseFloat(currentValue);
                    
                    if (!isNaN(numericValue)) {
                        values[fieldName] = numericValue;
                        hasChanges = true;
                        console.log(`  🔧 Force saving: ${fieldName} = ${numericValue}`);
                    }
                });
            }
        }
        
        if (!hasChanges) {
            console.log(`❌ No valid values to save for ${context}`);
            return;
        }
        
        console.log(`📤 Saving ${Object.keys(values).length} values for ${context}:`, values);
        
        // Verstuur naar backend
        fetch(`/klanten/${this.klantId}/bikefit/${this.bikefitId}/save-custom-results`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                context: context,
                values: values
            })
        })
        .then(response => {
            console.log(`📡 Response status: ${response.status}`);
            return response.json();
        })
        .then(result => {
            console.log(`📡 Save result for ${context}:`, result);
            
            if (result.success) {
                alert(`✅ SUCCESS: ${Object.keys(values).length} values saved for ${context.toUpperCase()}!`);
                
                // Update originele waarden
                contextInputs.forEach(input => {
                    const fieldName = input.getAttribute('data-field');
                    if (values.hasOwnProperty(fieldName)) {
                        input.setAttribute('data-original', input.value);
                        input.style.backgroundColor = '#d1fae5'; // Groen = opgeslagen
                        setTimeout(() => input.style.backgroundColor = '', 2000);
                    }
                });
            } else {
                alert(`❌ ERROR: ${result.message || 'Unknown error'}`);
            }
        })
        .catch(error => {
            console.error('Save error:', error);
            alert(`💥 Save failed: ${error.message}`);
        });
    }
    
    resetContext(context) {
        if (!confirm(`🔄 Reset all ${context.toUpperCase()} values to calculated defaults?\n\nThis will refresh the page.`)) {
            return;
        }
        
        console.log(`🔄 Resetting context: ${context}`);
        
        fetch(`/klanten/${this.klantId}/bikefit/${this.bikefitId}/reset-to-calculated`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                context: context
            })
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                alert(`✅ SUCCESS: ${context.toUpperCase()} values reset to calculated defaults!`);
                setTimeout(() => window.location.reload(), 1000);
            } else {
                alert(`❌ ERROR: ${result.message}`);
            }
        })
        .catch(error => {
            console.error('Reset error:', error);
            alert(`💥 Reset failed: ${error.message}`);
        });
    }
    
    findAllNumericValues() {
        console.log('🔍 Searching for ALL numeric values on page...');
        
        const inputs = document.querySelectorAll('input[type="number"]');
        console.log(`Found ${inputs.length} number inputs`);
        
        let bikefitInputs = 0;
        inputs.forEach((input, index) => {
            const value = input.value;
            const name = input.name || '';
            const form = input.getAttribute('form') || '';
            
            // Check if it's a bikefit input
            if (form === 'bikefit-form' || name.includes('zadel') || name.includes('reach') || name.includes('drop') || name.includes('crank')) {
                console.log(`🎯 BIKEFIT INPUT FOUND: "${name}" = "${value}"`);
                
                // Make it trackable for changes
                this.makeBikefitInputEditable(input, name, value);
                bikefitInputs++;
            }
        });
        
        console.log(`🎯 Found ${bikefitInputs} bikefit input fields`);
        
        // ✨ NIEUWE CODE: Pas custom waarden toe na initialisatie
        this.applyCustomValues();
    }
    
    applyCustomValues() {
        console.log('🎨 Applying custom values from database...');
        
        // Check of custom waarden beschikbaar zijn
        if (!window.bikefitCustomValues) {
            console.log('⚠️ No custom values found in window.bikefitCustomValues');
            return;
        }
        
        console.log('📊 Custom values:', window.bikefitCustomValues);
        
        // Loop door alle contexten (prognose, voor, na)
        ['prognose', 'voor', 'na'].forEach(context => {
            const customValues = window.bikefitCustomValues[context];
            if (!customValues) {
                console.log(`⚠️ No custom values for context: ${context}`);
                return;
            }
            
            console.log(`📝 Applying custom values for ${context}:`, customValues);
            
            // Loop door alle velden in deze context
            Object.keys(customValues).forEach(fieldName => {
                const customValue = customValues[fieldName];
                
                // Skip null/undefined waarden
                if (customValue === null || customValue === undefined) {
                    return;
                }
                
                // Zoek het input veld met deze naam in deze context
                const inputs = document.querySelectorAll(`input[data-context="${context}"][data-field="${fieldName}"]`);
                
                if (inputs.length === 0) {
                    console.log(`⚠️ No input found for ${context}.${fieldName}`);
                    return;
                }
                
                inputs.forEach(input => {
                    const oldValue = input.value;
                    input.value = customValue;
                    input.setAttribute('data-original', customValue); // Update original value
                    
                    // Visuele feedback: lichtgeel voor custom waarden
                    input.style.backgroundColor = '#fef9e7';
                    input.style.border = '2px solid #f59e0b';
                    
                    console.log(`✅ Applied ${context}.${fieldName}: ${oldValue} → ${customValue}`);
                });
            });
        });
        
        console.log('✅ All custom values applied');
    }
    
    makeBikefitInputEditable(input, fieldName, originalValue) {
        // Add data attributes for tracking
        input.setAttribute('data-editable', 'true');
        input.setAttribute('data-field', fieldName);
        input.setAttribute('data-original', originalValue);
        
        // FORCE input to be editable
        input.removeAttribute('readonly');
        input.removeAttribute('disabled');
        input.style.backgroundColor = '#ffffff';
        input.style.cursor = 'text';
        
        // Determine context based on which table it's in
        let context = this.determineInputContext(input);
        input.setAttribute('data-context', context);
        
        // Add visual feedback for changes
        input.addEventListener('input', () => {
            const currentValue = input.value;
            if (currentValue !== originalValue) {
                input.style.backgroundColor = '#fef3c7'; // Yellow = changed
                input.style.fontWeight = 'bold';
                input.style.border = '2px solid #f59e0b';
            } else {
                input.style.backgroundColor = '#ffffff';
                input.style.fontWeight = '';
                input.style.border = '';
            }
        });
        
        // Add focus styling
        input.addEventListener('focus', () => {
            input.style.outline = '2px solid #3b82f6';
            input.style.outlineOffset = '2px';
        });
        
        input.addEventListener('blur', () => {
            input.style.outline = '';
            input.style.outlineOffset = '';
        });
        
        console.log(`📍 Input "${fieldName}" assigned to context: ${context} (made editable)`);
    }
    
    determineInputContext(input) {
        const tables = document.querySelectorAll('table');
        
        for (let i = 0; i < tables.length; i++) {
            if (tables[i].contains(input)) {
                if (i === 0) return 'prognose';
                if (i === 1) return 'voor';  
                if (i === 2) return 'na';
            }
        }
        
        return 'prognose'; // fallback
    }
}

// Globale instantie
let editableResults;

// Initialiseer
if (!editableResults) {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            editableResults = new EditableResults();
        });
    } else {
        editableResults = new EditableResults();
    }
}