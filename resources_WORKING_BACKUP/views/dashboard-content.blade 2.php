{{-- Debug: Show actual HTML structure for dashboard-content --}}
<script>
console.log('Dashboard Content HTML structure:');
document.addEventListener('DOMContentLoaded', function() {
    const main = document.querySelector('main');
    if (main) {
        console.log('Main element:', main);
        console.log('Main innerHTML:', main.innerHTML);
        
        // Find all divs that might contain tiles
        const divs = main.querySelectorAll('div');
        divs.forEach((div, index) => {
            if (div.children.length > 0) {
                console.log(`Div ${index}:`, div);
                console.log(`Classes: ${div.className}`);
                console.log(`ID: ${div.id}`);
                console.log(`Children count: ${div.children.length}`);
            }
        });
    }
});
</script>

<style>
/* FORCE GRID LAYOUT ON DASHBOARD-CONTENT PAGE */
body main > div,
body main div,
main > div,
main div,
.container > div,
.row > div,
.col > div {
    display: grid !important;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)) !important;
    gap: 20px !important;
    padding: 20px !important;
    width: 100% !important;
    max-width: 100% !important;
    box-sizing: border-box !important;
}

/* FORCE CHILDREN TO BE GRID ITEMS */
body main > div > *,
body main div > *,
main > div > *,
main div > *,
.container > div > *,
.row > div > *,
.col > div > * {
    width: auto !important;
    max-width: 100% !important;
    min-height: 180px !important;
    flex: none !important;
    display: block !important;
    box-sizing: border-box !important;
    grid-column: span 1 !important;
    background: rgba(0,255,0,0.1) !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚨 DASHBOARD-CONTENT GRID FORCER ACTIVATED');
    
    // Find ANY div with children and force grid
    const allDivs = document.querySelectorAll('div');
    allDivs.forEach(div => {
        if (div.children.length >= 2) {
            console.log('Forcing grid on div with children:', div);
            div.style.cssText = `
                display: grid !important;
                grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)) !important;
                gap: 20px !important;
                padding: 20px !important;
                width: 100% !important;
                max-width: 100% !important;
                box-sizing: border-box !important;
                background: rgba(255,0,0,0.1) !important;
            `;
            
            Array.from(div.children).forEach(child => {
                child.style.cssText = `
                    width: auto !important;
                    max-width: 100% !important;
                    min-height: 180px !important;
                    flex: none !important;
                    display: block !important;
                    box-sizing: border-box !important;
                    background: rgba(0,255,0,0.1) !important;
                `;
            });
        }
    });
});
</script>