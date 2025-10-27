/**
 * Dark Mode Script voor Bonami Sportcoaching
 * Systeem-preference detectie + manual toggle
 */

(function() {
    'use strict';
    
    // Check opgeslagen preference of system preference
    function getDarkModePreference() {
        const stored = localStorage.getItem('darkMode');
        
        if (stored !== null) {
            return stored === 'true';
        }
        
        // Fallback naar system preference
        return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    }
    
    // Apply dark mode
    function applyDarkMode(isDark) {
        const html = document.documentElement;
        
        if (isDark) {
            html.classList.add('dark');
            console.log('🌙 Dark mode geactiveerd');
        } else {
            html.classList.remove('dark');
            console.log('☀️ Light mode geactiveerd');
        }
        
        // Update toggle button icon
        updateToggleIcon(isDark);
        
        // Sla preference op
        localStorage.setItem('darkMode', isDark);
    }
    
    // Update toggle button icon
    function updateToggleIcon(isDark) {
        const toggle = document.getElementById('dark-mode-toggle');
        if (!toggle) return;
        
        const svg = toggle.querySelector('svg');
        if (!svg) return;
        
        if (isDark) {
            // Toon zon icon (voor light mode)
            svg.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>';
        } else {
            // Toon maan icon (voor dark mode)
            svg.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>';
        }
    }
    
    // Initialize dark mode op pagina load
    function initDarkMode() {
        const isDark = getDarkModePreference();
        applyDarkMode(isDark);
        
        // Luister naar system preference changes
        if (window.matchMedia) {
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
                // Alleen updaten als er geen manual preference is
                if (localStorage.getItem('darkMode') === null) {
                    applyDarkMode(e.matches);
                }
            });
        }
    }
    
    // Toggle functie (public)
    window.toggleDarkMode = function() {
        const html = document.documentElement;
        const isDark = html.classList.contains('dark');
        applyDarkMode(!isDark);
    };
    
    // Initialize meteen (voor snelle load)
    initDarkMode();
    
    // Setup toggle button na DOM load
    document.addEventListener('DOMContentLoaded', function() {
        const toggle = document.getElementById('dark-mode-toggle');
        
        if (toggle) {
            toggle.addEventListener('click', window.toggleDarkMode);
            console.log('✅ Dark mode toggle button geregistreerd');
        }
        
        // Keyboard shortcut: Ctrl/Cmd + Shift + D
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'D') {
                e.preventDefault();
                window.toggleDarkMode();
                console.log('⌨️ Dark mode toggle via keyboard');
            }
        });
    });
    
    console.log('🌓 Dark mode systeem geladen');
})();