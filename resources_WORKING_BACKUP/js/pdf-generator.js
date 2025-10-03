
const puppeteer = require('puppeteer');
const fs = require('fs');

(async () => {
    const url = process.argv[2];
    const outputPath = process.argv[3];
    
    if (!url || !outputPath) {
        console.error('Usage: node pdf-generator.js <url> <output-path>');
        process.exit(1);
    }
    
    try {
        const browser = await puppeteer.launch({
            headless: true,
            args: ['--no-sandbox', '--disable-setuid-sandbox']
        });
        
        const page = await browser.newPage();
        await page.goto(url, { 
            waitUntil: 'networkidle2',
            timeout: 30000 
        });
        
        // Wacht een moment voor volledige rendering
        await page.waitForTimeout(2000);
        
        const pdf = await page.pdf({
            path: outputPath,
            format: 'A4',
            printBackground: true,
            margin: { top: 0, right: 0, bottom: 0, left: 0 }
        });
        
        await browser.close();
        console.log('PDF successfully generated');
        process.exit(0);
    } catch (error) {
        console.error('Error generating PDF:', error);
        process.exit(1);
    }
})();
