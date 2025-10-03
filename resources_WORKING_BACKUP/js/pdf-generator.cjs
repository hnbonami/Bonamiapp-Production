
const puppeteer = require('puppeteer');
const fs = require('fs');

(async () => {
    const url = process.argv[2];
    const outputPath = process.argv[3];
    const cookies = process.argv[4];
    
    if (!url || !outputPath) {
        console.error('Usage: node pdf-generator.js <url> <output-path> [cookies]');
        process.exit(1);
    }
    
    try {
                const browser = await puppeteer.launch({
            headless: 'new',
            args: [
                '--no-sandbox', 
                '--disable-setuid-sandbox',
                '--disable-dev-shm-usage',
                '--disable-gpu',
                '--no-first-run',
                '--disable-extensions',
                '--disable-web-security',
                '--single-process',
                '--disable-background-networking',
                '--disable-background-timer-throttling',
                '--disable-backgrounding-occluded-windows',
                '--disable-breakpad',
                '--disable-client-side-phishing-detection',
                '--disable-component-extensions-with-background-pages',
                '--disable-default-apps',
                '--disable-features=TranslateUI',
                '--disable-hang-monitor',
                '--disable-ipc-flooding-protection',
                '--disable-popup-blocking',
                '--disable-prompt-on-repost',
                '--disable-renderer-backgrounding',
                '--disable-sync',
                '--force-color-profile=srgb',
                '--metrics-recording-only',
                '--no-default-browser-check',
                '--password-store=basic',
                '--use-mock-keychain'
            ]
        });
        
        const page = await browser.newPage();
        
        // Optimale viewport voor achtergrondafbeeldingen
        await page.setViewport({ width: 1200, height: 1600, deviceScaleFactor: 1 });
        
        // Set cookies if provided and valid
        if (cookies && cookies.trim() !== '') {
            try {
                const cookieArray = cookies.split(';').map(c => {
                    const [name, value] = c.trim().split('=');
                    if (name && value) {
                        return {
                            name: name,
                            value: value,
                            domain: new URL(url).hostname
                        };
                    }
                    return null;
                }).filter(c => c !== null);
                
                if (cookieArray.length > 0) {
                    await page.setCookie(...cookieArray);
                }
            } catch (cookieError) {
                console.warn('Cookie parsing failed:', cookieError.message);
            }
        }
        
        // Set user agent
        await page.setUserAgent('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
        
        console.log('Navigating to URL:', url);
        await page.goto(url, { 
            waitUntil: 'networkidle0',
            timeout: 60000 
        });
        
        // Langere wachttijd voor achtergrondafbeeldingen
        await new Promise(resolve => setTimeout(resolve, 2500));
        
        // Preload en forceer laden van achtergrondafbeeldingen
        await page.evaluate(() => {
            return new Promise((resolve) => {
                const elementsWithBg = document.querySelectorAll('[style*="background-image"]');
                let loadedImages = 0;
                const totalImages = elementsWithBg.length;
                
                if (totalImages === 0) {
                    resolve();
                    return;
                }
                
                elementsWithBg.forEach(el => {
                    const computedStyle = window.getComputedStyle(el);
                    const bgImage = computedStyle.backgroundImage;
                    
                    if (bgImage && bgImage !== "none") {
                        // Extract URL from background-image
                        const urlMatch = bgImage.match(/url\(["']?(.+?)["']?\)/);
                        if (urlMatch && urlMatch[1]) {
                            const img = new Image();
                            img.onload = () => {
                                // Force repaint after image loads
                                el.style.transform = "translateZ(0)";
                                el.offsetHeight; // Trigger reflow
                                el.style.transform = "";
                                
                                loadedImages++;
                                if (loadedImages >= totalImages) {
                                    resolve();
                                }
                            };
                            img.onerror = () => {
                                loadedImages++;
                                if (loadedImages >= totalImages) {
                                    resolve();
                                }
                            };
                            img.src = urlMatch[1];
                        } else {
                            loadedImages++;
                            if (loadedImages >= totalImages) {
                                resolve();
                            }
                        }
                    } else {
                        loadedImages++;
                        if (loadedImages >= totalImages) {
                            resolve();
                        }
                    }
                });
                
                // Fallback timeout
                setTimeout(resolve, 5000);
            });
        });
        
        // Extra wachttijd na forceren
        await new Promise(resolve => setTimeout(resolve, 1500));
        
        // Debug: Log alle achtergrondafbeeldingen om te zien wat er mis gaat
        await page.evaluate(() => {
            const allElements = document.querySelectorAll('.a4-preview-content, [style*="background-image"]');
            console.log('=== DEBUGGING ACHTERGRONDEN ===');
            console.log('Totaal gevonden elementen:', allElements.length);
            
            allElements.forEach((el, idx) => {
                const style = el.getAttribute('style') || '';
                const computedStyle = window.getComputedStyle(el);
                const bgImage = computedStyle.backgroundImage;
                
                console.log('Element ' + (idx + 1) + ':');
                console.log('  - Class:', el.className);
                console.log('  - Inline style:', style);
                console.log('  - Computed bg:', bgImage);
                console.log('  - Has bg-image in style:', style.includes('background-image'));
                console.log('---');
            });
        });
        
        // CSS optimalisaties 
        await page.addStyleTag({
            content: `
                .no-print, button { display: none !important; }
                body { 
                    margin: 0 !important; 
                    padding: 0 !important; 
                    background: #888a8d !important;
                }
                * { 
                    -webkit-print-color-adjust: exact !important; 
                    color-adjust: exact !important;
                    print-color-adjust: exact !important;
                }
                .a4-preview-content { 
                    transform: none !important; 
                    margin-top: 0 !important;
                    background-size: cover !important;
                    background-position: center !important;
                    background-repeat: no-repeat !important;
                    background-attachment: local !important;
                }
            `
        });
        
        // ULTRA-SPECIFIEKE FIX VOOR EERSTE PAGINA + RADICALE CONVERSIE
        await page.evaluate(() => {
            console.log('=== ULTRA-SPECIFIEKE EERSTE PAGINA FIX ===');
            
            const allContentElements = document.querySelectorAll('.a4-preview-content');
            console.log('Totaal content elementen:', allContentElements.length);
            
            allContentElements.forEach((el, idx) => {
                const originalStyle = el.getAttribute('style') || '';
                console.log('Element ' + (idx + 1) + ' originele style:', originalStyle);
                
                // SPECIALE BEHANDELING VOOR EERSTE ELEMENT (idx === 0)
                if (idx === 0) {
                    console.log('=== EERSTE PAGINA - EXTRA AGRESSIEVE BEHANDELING ===');
                    
                    // Probeer ALLE mogelijke regex patronen voor background-image
                    const patterns = [
                        /background-image:\s*url\(["']?([^"')]+)["']?\)/i,
                        /background-image:\s*url\(([^)]+)\)/i,
                        /background:\s*[^;]*url\(["']?([^"')]+)["']?\)/i
                    ];
                    
                    let bgUrl = null;
                    for (const pattern of patterns) {
                        const match = originalStyle.match(pattern);
                        if (match && match[1]) {
                            bgUrl = match[1].replace(/["']/g, ''); // Remove quotes
                            console.log('EERSTE PAGINA - URL gevonden met patroon:', bgUrl);
                            break;
                        }
                    }
                    
                    // Fallback: zoek in computed style als inline style faalt
                    if (!bgUrl) {
                        const computedStyle = window.getComputedStyle(el);
                        const computedBg = computedStyle.backgroundImage;
                        console.log('EERSTE PAGINA - Computed bg:', computedBg);
                        
                        if (computedBg && computedBg !== 'none') {
                            const match = computedBg.match(/url\(["']?([^"')]+)["']?\)/);
                            if (match && match[1]) {
                                bgUrl = match[1];
                                console.log('EERSTE PAGINA - URL uit computed style:', bgUrl);
                            }
                        }
                    }
                    
                    if (bgUrl) {
                        console.log('EERSTE PAGINA - Converteer naar IMG:', bgUrl);
                        
                        // Verwijder ALLE background properties
                        let newStyle = originalStyle
                            .replace(/background[^;]*;?/gi, '')
                            .replace(/;;+/g, ';')
                            .replace(/^;|;$/g, '');
                        
                        el.setAttribute('style', newStyle);
                        
                        // Maak IMG met extra eigenschappen voor eerste pagina
                        const img = document.createElement('img');
                        img.src = bgUrl;
                        img.style.cssText = `
                            position: absolute !important;
                            top: 0 !important;
                            left: 0 !important;
                            width: 100% !important;
                            height: 100% !important;
                            object-fit: cover !important;
                            object-position: center !important;
                            z-index: -10 !important;
                            pointer-events: none !important;
                            display: block !important;
                        `;
                        
                        // Forceer positioning
                        el.style.position = 'relative';
                        el.style.zIndex = '1';
                        
                        // Voeg IMG toe als allereerste element
                        if (el.firstChild) {
                            el.insertBefore(img, el.firstChild);
                        } else {
                            el.appendChild(img);
                        }
                        
                        console.log('EERSTE PAGINA - IMG toegevoegd met extra forcering');
                    } else {
                        console.log('EERSTE PAGINA - GEEN URL GEVONDEN!');
                    }
                } else {
                    // Verbeterde behandeling voor andere pagina's (idx > 0)
                    console.log('=== PAGINA ' + (idx + 1) + ' - VERBETERDE BEHANDELING ===');
                    
                    // Probeer meerdere patronen ook voor andere pagina's
                    const patterns = [
                        /background-image:\s*url\(["']?([^"')]+)["']?\)/i,
                        /background-image:\s*url\(([^)]+)\)/i,
                        /background:\s*[^;]*url\(["']?([^"')]+)["']?\)/i
                    ];
                    
                    let bgUrl = null;
                    for (const pattern of patterns) {
                        const match = originalStyle.match(pattern);
                        if (match && match[1]) {
                            bgUrl = match[1].replace(/["']/g, '');
                            console.log('Pagina ' + (idx + 1) + ' - URL gevonden:', bgUrl);
                            break;
                        }
                    }
                    
                    // Fallback naar computed style
                    if (!bgUrl) {
                        const computedStyle = window.getComputedStyle(el);
                        const computedBg = computedStyle.backgroundImage;
                        if (computedBg && computedBg !== 'none') {
                            const match = computedBg.match(/url\(["']?([^"')]+)["']?\)/);
                            if (match && match[1]) {
                                bgUrl = match[1];
                                console.log('Pagina ' + (idx + 1) + ' - URL uit computed:', bgUrl);
                            }
                        }
                    }
                    
                    if (bgUrl) {
                        console.log('Pagina ' + (idx + 1) + ' - Converteer naar IMG:', bgUrl);
                        
                        // Verwijder alle background properties zoals bij eerste pagina
                        let newStyle = originalStyle
                            .replace(/background[^;]*;?/gi, '')
                            .replace(/;;+/g, ';')
                            .replace(/^;|;$/g, '');
                        
                        el.setAttribute('style', newStyle);
                        
                        const img = document.createElement('img');
                        img.src = bgUrl;
                        img.style.cssText = `
                            position: absolute !important;
                            top: 0 !important;
                            left: 0 !important;
                            width: 100% !important;
                            height: 100% !important;
                            object-fit: cover !important;
                            object-position: center !important;
                            z-index: -5 !important;
                            pointer-events: none !important;
                            display: block !important;
                        `;
                        
                        el.style.position = 'relative';
                        el.style.zIndex = '1';
                        
                        if (el.firstChild) {
                            el.insertBefore(img, el.firstChild);
                        } else {
                            el.appendChild(img);
                        }
                        
                        console.log('Pagina ' + (idx + 1) + ' - IMG toegevoegd met verbeterde styling');
                    } else {
                        console.log('Pagina ' + (idx + 1) + ' - GEEN URL GEVONDEN!');
                    }
                }
            });
        });
        
        // Wacht tot alle IMG elementen geladen zijn
        await page.evaluate(() => {
            return new Promise((resolve) => {
                const allImages = document.querySelectorAll('.a4-preview-content img');
                console.log('Wachten op ' + allImages.length + ' afbeeldingen...');
                
                if (allImages.length === 0) {
                    resolve();
                    return;
                }
                
                let loadedCount = 0;
                
                allImages.forEach((img, idx) => {
                    if (img.complete) {
                        loadedCount++;
                        console.log('Afbeelding ' + (idx + 1) + ' al geladen');
                        if (loadedCount >= allImages.length) {
                            resolve();
                        }
                    } else {
                        img.onload = () => {
                            loadedCount++;
                            console.log('Afbeelding ' + (idx + 1) + ' geladen (' + loadedCount + '/' + allImages.length + ')');
                            if (loadedCount >= allImages.length) {
                                resolve();
                            }
                        };
                        img.onerror = () => {
                            loadedCount++;
                            console.log('Afbeelding ' + (idx + 1) + ' FOUT (' + loadedCount + '/' + allImages.length + ')');
                            if (loadedCount >= allImages.length) {
                                resolve();
                            }
                        };
                    }
                });
                
                // Fallback timeout
                setTimeout(() => {
                    console.log('Timeout: verdergaan met ' + loadedCount + '/' + allImages.length + ' geladen');
                    resolve();
                }, 10000);
            });
        });
        
        console.log('Alle afbeeldingen verwerkt, wachten op finale render...');
        await new Promise(resolve => setTimeout(resolve, 3000));
        
        // Skip JavaScript evaluatie voor snelheid
        
        const pdf = await page.pdf({
            path: outputPath,
            format: 'A4',
            printBackground: true,
            preferCSSPageSize: true,
            margin: { top: '0mm', right: '0mm', bottom: '0mm', left: '0mm' }
        });
        
        await browser.close();
        console.log('PDF successfully generated at:', outputPath);
        process.exit(0);
    } catch (error) {
        console.error('Error generating PDF:', error.message);
        console.error('Stack trace:', error.stack);
        process.exit(1);
    }
})();
