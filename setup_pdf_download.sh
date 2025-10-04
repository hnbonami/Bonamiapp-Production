#!/bin/bash

echo "📄 Setting up PDF Download functionality..."

cd /Users/hannesbonami/Desktop/Bonamiapp

# Install DomPDF if not already installed
if ! grep -q "barryvdh/laravel-dompdf" composer.json; then
    echo "📦 Installing DomPDF..."
    composer require barryvdh/laravel-dompdf
    echo "✅ DomPDF installed!"
else
    echo "✅ DomPDF already installed!"
fi

# Create backgrounds directory if it doesn't exist
mkdir -p public/backgrounds
echo "✅ Backgrounds directory ready"

# Clear caches
php artisan config:cache
php artisan route:cache

echo ""
echo "🎯 PDF DOWNLOAD FUNCTIONALITY READY!"
echo ""
echo "✅ WHAT'S NEW:"
echo "   📄 'Download PDF' button replaces 'Download HTML'"
echo "   🎨 PDF includes background images"
echo "   📑 Multi-page PDF generation"
echo "   🔧 Fallback to HTML if PDF fails"
echo ""
echo "🔄 TEST NOW:"
echo "   1. Go to /sjablonen/1/preview"
echo "   2. Click 'Download PDF'"
echo "   3. Should download proper PDF file"
echo ""
echo "💡 NOTE:"
echo "   - Background images should be in public/backgrounds/"
echo "   - PDF generation uses DomPDF library"
echo "   - Fallback to HTML download if issues occur"
echo ""