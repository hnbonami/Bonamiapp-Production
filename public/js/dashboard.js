// TOTAL NUCLEAR OPTION - FORCE GRID ON EVERYTHING
function forceGridOnEverything() {
    console.log('� NUCLEAR GRID OVERRIDE ACTIVATED');
    
    // Remove ALL Tailwind classes from everything
    const allElements = document.querySelectorAll('*');
    allElements.forEach(el => {
        const tailwindClasses = [
            'w-full', 'w-screen', 'w-1/1', 'flex', 'flex-col', 'flex-row', 
            'space-x-4', 'space-y-4', 'block', 'inline-block', 'flex-wrap'
        ];
        tailwindClasses.forEach(cls => {
            el.classList.remove(cls);
            el.classList.remove(`sm:${cls}`);
            el.classList.remove(`md:${cls}`);
            el.classList.remove(`lg:${cls}`);
            el.classList.remove(`xl:${cls}`);
        });
    });
    
    // Find main content area and force grid
    const main = document.querySelector('main');
    if (main) {
        console.log('🎯 Found main:', main);
        
        // Find first div with multiple children
        const contentDivs = main.querySelectorAll('div');
        contentDivs.forEach(div => {
            if (div.children.length >= 2) {
                console.log(`🔧 Forcing grid on div with ${div.children.length} children`);
                
                div.style.cssText = `
                    display: grid !important;
                    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)) !important;
                    gap: 20px !important;
                    padding: 20px !important;
                    width: 100% !important;
                    max-width: 100% !important;
                    box-sizing: border-box !important;
                    background: rgba(255,0,0,0.1) !important;
                `;
                
                // Force children to be grid items
                Array.from(div.children).forEach(child => {
                    child.style.cssText = `
                        width: auto !important;
                        max-width: 100% !important;
                        min-height: 200px !important;
                        flex: none !important;
                        display: block !important;
                        box-sizing: border-box !important;
                        background: rgba(0,255,0,0.1) !important;
                    `;
                });
            }
        });
    }
    
    // Also try body as fallback
    const bodyDivs = document.body.querySelectorAll('div');
    bodyDivs.forEach(div => {
        if (div.children.length >= 2) {
            div.style.display = 'grid';
            div.style.gridTemplateColumns = 'repeat(auto-fill, minmax(280px, 1fr))';
            div.style.gap = '20px';
            div.style.padding = '20px';
        }
    });
}

// Run the nuclear function
forceGridOnEverything();

// Run immediately and on various events
forceGridLayout();
document.addEventListener('DOMContentLoaded', forceGridLayout);
window.addEventListener('load', forceGridLayout);

// Also run when content changes (for dynamic loading)
const observer = new MutationObserver(forceGridLayout);
observer.observe(document.body, { childList: true, subtree: true });

// Run every second for the first 10 seconds to catch any dynamic content
let attempts = 0;
const interval = setInterval(() => {
    forceGridLayout();
    attempts++;
    if (attempts >= 10) {
        clearInterval(interval);
    }
}, 1000);