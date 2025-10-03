// TESTZADELS layout fixes - Position next to sidebar + Remove duplicates
document.addEventListener('DOMContentLoaded', function() {
    // Check if we're on ANY testzadels page
    const currentPath = window.location.pathname;
    const isTestzadelsPage = currentPath.includes('/testzadels');
    
    if (isTestzadelsPage) {
        console.log('🔧 POSITIONING testzadels next to sidebar for:', currentPath);
        
        // REMOVE DUPLICATE CONTENT FIRST
        function removeDuplicateContent() {
            // Find all "Testzadels Beheer" headers
            const allHeaders = document.querySelectorAll('h1, h2, h3');
            const testzadelsHeaders = [];
            
            allHeaders.forEach(header => {
                const text = header.textContent.trim();
                if (text === 'Testzadels Beheer' || text.includes('Testzadels')) {
                    testzadelsHeaders.push(header);
                }
            });
            
            // If we have more than one "Testzadels Beheer" section, remove duplicates
            if (testzadelsHeaders.length > 1) {
                console.log('🗑️ Found', testzadelsHeaders.length, 'duplicate sections, removing extras');
                
                // Keep the first one, remove the rest
                for (let i = 1; i < testzadelsHeaders.length; i++) {
                    const duplicateHeader = testzadelsHeaders[i];
                    
                    // Find the container/section that holds this duplicate
                    let parentToRemove = duplicateHeader.closest('section, .container, div[class*="container"], main > div');
                    
                    if (parentToRemove) {
                        console.log('🗑️ Removing duplicate section:', duplicateHeader.textContent);
                        parentToRemove.remove();
                    } else {
                        // Fallback: just remove the header and next few siblings
                        duplicateHeader.remove();
                    }
                }
            }
            
            // Also remove any duplicate tables
            const tables = document.querySelectorAll('table');
            if (tables.length > 1) {
                // Check if tables have similar content (both have testzadels data)
                let testzadelsTables = [];
                tables.forEach(table => {
                    const tableText = table.textContent.toLowerCase();
                    if (tableText.includes('testzadel') || tableText.includes('uitgeleend')) {
                        testzadelsTables.push(table);
                    }
                });
                
                if (testzadelsTables.length > 1) {
                    console.log('🗑️ Found', testzadelsTables.length, 'similar tables, removing duplicates');
                    // Keep the first table, remove others
                    for (let i = 1; i < testzadelsTables.length; i++) {
                        let tableContainer = testzadelsTables[i].closest('section, .container, div[class*="container"]');
                        if (tableContainer) {
                            tableContainer.remove();
                        } else {
                            testzadelsTables[i].remove();
                        }
                    }
                }
            }
            
            // Remove duplicate/orphaned numbers and stats elements
            const allDivs = document.querySelectorAll('div');
            allDivs.forEach(div => {
                const text = div.textContent.trim();
                const hasOnlyNumber = /^[0-9]+$/.test(text);
                const isSmallDiv = div.children.length === 0 && text.length <= 3;
                
                // Check if this is an orphaned number (like the green "0")
                if (hasOnlyNumber && isSmallDiv) {
                    // Check if it's positioned above "Verwacht vandaag" or similar
                    const nextSibling = div.nextElementSibling;
                    const parent = div.parentElement;
                    
                    if (nextSibling && nextSibling.textContent.includes('Verwacht')) {
                        console.log('🗑️ Removing orphaned number:', text);
                        div.remove();
                    } else if (parent && parent.textContent.includes('Verwacht') && div !== parent) {
                        console.log('🗑️ Removing orphaned number in parent:', text);
                        div.remove();
                    }
                }
                
                // Also check for green styled numbers that might be duplicates
                if (hasOnlyNumber && (div.style.color === 'green' || div.className.includes('green') || div.className.includes('text-green'))) {
                    // Check if there's already a proper stats section nearby
                    const nearbyStats = div.closest('[class*="stats"], [class*="metric"], [class*="count"]');
                    if (!nearbyStats) {
                        console.log('🗑️ Removing orphaned green number:', text);
                        div.remove();
                    }
                }
            });
            
            // Remove any duplicate metrics/stats sections
            const statsElements = document.querySelectorAll('[class*="metric"], [class*="stat"], [class*="count"]');
            const statsTexts = [];
            statsElements.forEach(element => {
                const text = element.textContent.trim();
                if (statsTexts.includes(text) && text !== '') {
                    console.log('🗑️ Removing duplicate stats element:', text);
                    element.remove();
                } else {
                    statsTexts.push(text);
                }
            });
        }
        
        // POSITION NEXT TO SIDEBAR - Force layout immediately
        function positionNextToSidebar() {
            // Target the main content area
            const main = document.querySelector('main#app-main');
            if (main) {
                // Position main content next to sidebar (16rem = sidebar width)
                main.style.marginLeft = '16rem';
                main.style.paddingLeft = '2rem';
                main.style.paddingRight = '2rem';
                main.style.width = 'calc(100% - 16rem)';
                
                // Ensure all children fill the available space properly
                Array.from(main.children).forEach(child => {
                    child.style.marginLeft = '0';
                    child.style.marginRight = '0';
                    child.style.paddingLeft = '0';
                    child.style.paddingRight = '0';
                    child.style.maxWidth = 'none';
                    child.style.width = '100%';
                    
                    // Override any Tailwind classes
                    if (child.className.includes('mx-auto') || child.className.includes('max-w-')) {
                        child.style.marginLeft = '0';
                        child.style.marginRight = '0';
                        child.style.maxWidth = 'none';
                    }
                });
            }
            
            console.log('✅ Content positioned next to sidebar');
        }
        
        // Apply duplicate removal first
        removeDuplicateContent();
        
        // Then apply positioning
        positionNextToSidebar();
        
        // Setup reminder buttons
        setupReminderButtons();
        
        // Apply again after short delay to catch any dynamic content
        setTimeout(() => {
            removeDuplicateContent();
            positionNextToSidebar();
            setupReminderButtons();
        }, 100);
        
        setTimeout(() => {
            removeDuplicateContent();
            positionNextToSidebar();
        }, 500);
        
        // Watch for any layout changes and re-apply
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList') {
                    setTimeout(() => {
                        removeDuplicateContent();
                        positionNextToSidebar();
                    }, 50);
                }
            });
        });
        
        if (document.querySelector('main#app-main')) {
            observer.observe(document.querySelector('main#app-main'), {
                childList: true,
                subtree: true
            });
        }
    }
});

// Setup reminder functionality
function setupReminderButtons() {
    // Handle individual reminder buttons
    document.querySelectorAll('[data-action="send-reminder"]').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const testzadelId = this.dataset.testzadelId;
            const klantNaam = this.dataset.klantNaam;
            
            if (!confirm(`Herinnering versturen naar ${klantNaam}?`)) {
                return;
            }
            
            sendReminder(testzadelId, this);
        });
    });
    
    // Handle bulk reminder button
    const bulkButton = document.querySelector('[data-action="send-bulk-reminders"]');
    if (bulkButton) {
        bulkButton.addEventListener('click', function(e) {
            e.preventDefault();
            
            if (!confirm('Herinneringen versturen naar alle klanten met verlopen testzadels?')) {
                return;
            }
            
            sendBulkReminders(this);
        });
    }
}

function sendReminder(testzadelId, button) {
    const originalText = button.textContent;
    button.disabled = true;
    button.textContent = 'Versturen...';
    
    fetch(`/testzadels/${testzadelId}/reminder`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            button.textContent = 'Verstuurd ✓';
            button.classList.add('btn-success');
            setTimeout(() => {
                button.textContent = originalText;
                button.disabled = false;
                button.classList.remove('btn-success');
            }, 3000);
            
            // Show success message
            showToast('success', data.message);
        } else {
            throw new Error(data.message || 'Unknown error');
        }
    })
    .catch(error => {
        button.textContent = originalText;
        button.disabled = false;
        showToast('error', 'Fout: ' + error.message);
    });
}

function sendBulkReminders(button) {
    const originalText = button.textContent;
    button.disabled = true;
    button.textContent = 'Versturen...';
    
    fetch('/testzadels/bulk-reminders', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            button.textContent = originalText;
            button.disabled = false;
            showToast('success', data.message);
        } else {
            throw new Error(data.message || 'Unknown error');
        }
    })
    .catch(error => {
        button.textContent = originalText;
        button.disabled = false;
        showToast('error', 'Fout: ' + error.message);
    });
}

function showToast(type, message) {
    // Create toast notification
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 8px;
        color: white;
        font-weight: bold;
        z-index: 10000;
        max-width: 300px;
        animation: slideIn 0.3s ease;
        ${type === 'success' ? 'background-color: #10b981;' : 'background-color: #ef4444;'}
    `;
    toast.textContent = message;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }, 4000);
}