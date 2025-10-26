// Dark Mode Toggle Systeem voor Bonami Dashboard
// Plaats dit bestand in: public/js/darkmode.js

class DarkModeManager {
    constructor() {
        this.darkMode = this.getSavedPreference();
        this.init();
    }

    init() {
        // Apply dark mode on load
        if (this.darkMode) {
            this.enable();
        }

        // Listen voor toggle events
        document.addEventListener('DOMContentLoaded', () => {
            this.setupToggleButton();
            this.setupAutoDetect();
        });
    }

    getSavedPreference() {
        const saved = localStorage.getItem('bonami_dark_mode');
        if (saved !== null) {
            return saved === 'true';
        }
        
        // Auto-detect system preference
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            return true;
        }
        
        return false;
    }

    enable() {
        document.documentElement.classList.add('dark-mode');
        document.body.classList.add('dark-mode');
        this.darkMode = true;
        localStorage.setItem('bonami_dark_mode', 'true');
        this.updateToggleButton();
        this.applyDarkModeStyles();
    }

    disable() {
        document.documentElement.classList.remove('dark-mode');
        document.body.classList.remove('dark-mode');
        this.darkMode = false;
        localStorage.setItem('bonami_dark_mode', 'false');
        this.updateToggleButton();
        this.removeDarkModeStyles();
    }

    toggle() {
        if (this.darkMode) {
            this.disable();
        } else {
            this.enable();
        }
    }

    setupToggleButton() {
        const toggleBtn = document.getElementById('dark-mode-toggle');
        if (!toggleBtn) {
            console.warn('Dark mode toggle button not found');
            return;
        }

        toggleBtn.addEventListener('click', () => {
            this.toggle();
        });

        this.updateToggleButton();
    }

    updateToggleButton() {
        const toggleBtn = document.getElementById('dark-mode-toggle');
        if (!toggleBtn) return;

        const icon = toggleBtn.querySelector('svg');
        if (!icon) return;

        if (this.darkMode) {
            // Sun icon (om terug naar light mode te gaan)
            icon.innerHTML = `
                <circle cx="12" cy="12" r="5" fill="currentColor"/>
                <line x1="12" y1="1" x2="12" y2="3" stroke="currentColor" stroke-width="2"/>
                <line x1="12" y1="21" x2="12" y2="23" stroke="currentColor" stroke-width="2"/>
                <line x1="4.22" y1="4.22" x2="5.64" y2="5.64" stroke="currentColor" stroke-width="2"/>
                <line x1="18.36" y1="18.36" x2="19.78" y2="19.78" stroke="currentColor" stroke-width="2"/>
                <line x1="1" y1="12" x2="3" y2="12" stroke="currentColor" stroke-width="2"/>
                <line x1="21" y1="12" x2="23" y2="12" stroke="currentColor" stroke-width="2"/>
                <line x1="4.22" y1="19.78" x2="5.64" y2="18.36" stroke="currentColor" stroke-width="2"/>
                <line x1="18.36" y1="5.64" x2="19.78" y2="4.22" stroke="currentColor" stroke-width="2"/>
            `;
            toggleBtn.setAttribute('title', 'Light mode');
        } else {
            // Moon icon (om naar dark mode te gaan)
            icon.innerHTML = `
                <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z" fill="currentColor"/>
            `;
            toggleBtn.setAttribute('title', 'Dark mode');
        }
    }

    applyDarkModeStyles() {
        // Inject dark mode CSS if not already present
        if (!document.getElementById('dark-mode-styles')) {
            const style = document.createElement('style');
            style.id = 'dark-mode-styles';
            style.textContent = `
                .dark-mode {
                    background-color: #1a202c !important;
                    color: #e2e8f0 !important;
                }

                .dark-mode .bg-white,
                .dark-mode .bg-white\\/80 {
                    background-color: #2d3748 !important;
                }

                .dark-mode .bg-gray-50 {
                    background-color: #374151 !important;
                }

                .dark-mode .bg-gray-100 {
                    background-color: #4b5563 !important;
                }

                .dark-mode .text-gray-900 {
                    color: #f9fafb !important;
                }

                .dark-mode .text-gray-700 {
                    color: #d1d5db !important;
                }

                .dark-mode .text-gray-600 {
                    color: #9ca3af !important;
                }

                .dark-mode .border-gray-100,
                .dark-mode .border-gray-200 {
                    border-color: #4b5563 !important;
                }

                .dark-mode .divide-gray-100,
                .dark-mode .divide-gray-200 {
                    border-color: #4b5563 !important;
                }

                /* Dashboard widgets */
                .dark-mode .dashboard-widget {
                    background-color: #2d3748 !important;
                    color: #e2e8f0 !important;
                }

                .dark-mode .widget-header {
                    border-bottom-color: #4b5563 !important;
                }

                /* Forms */
                .dark-mode input,
                .dark-mode select,
                .dark-mode textarea {
                    background-color: #374151 !important;
                    border-color: #4b5563 !important;
                    color: #e2e8f0 !important;
                }

                .dark-mode input::placeholder {
                    color: #9ca3af !important;
                }

                /* Buttons - behoud Bonami kleuren */
                .dark-mode .bg-orange-100 {
                    background-color: #ea580c !important;
                }

                .dark-mode .bg-blue-100 {
                    background-color: #3b82f6 !important;
                }

                .dark-mode .bg-emerald-100 {
                    background-color: #10b981 !important;
                }

                .dark-mode .bg-rose-100 {
                    background-color: #ef4444 !important;
                }

                /* Shadows */
                .dark-mode .shadow,
                .dark-mode .shadow-xl {
                    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.4), 
                                0 4px 6px -2px rgba(0, 0, 0, 0.2) !important;
                }

                /* Tables */
                .dark-mode table {
                    color: #e2e8f0 !important;
                }

                .dark-mode thead {
                    background-color: #374151 !important;
                }

                .dark-mode tbody tr:hover {
                    background-color: #374151 !important;
                }

                /* Gridstack */
                .dark-mode .grid-stack-item-content {
                    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3) !important;
                }

                /* Smooth transition */
                * {
                    transition: background-color 0.3s ease, 
                                color 0.3s ease, 
                                border-color 0.3s ease !important;
                }
            `;
            document.head.appendChild(style);
        }
    }

    removeDarkModeStyles() {
        // Styles blijven, alleen classes worden verwijderd
        // CSS doet de rest automatisch
    }

    setupAutoDetect() {
        // Listen voor system preference changes
        if (window.matchMedia) {
            const darkModeMediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
            darkModeMediaQuery.addEventListener('change', (e) => {
                // Alleen auto-switchen als gebruiker geen handmatige voorkeur heeft
                if (localStorage.getItem('bonami_dark_mode') === null) {
                    if (e.matches) {
                        this.enable();
                    } else {
                        this.disable();
                    }
                }
            });
        }
    }
}

// Initialize
const darkModeManager = new DarkModeManager();

// Export voor gebruik in andere scripts
window.darkModeManager = darkModeManager;