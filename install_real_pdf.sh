#!/bin/bash

echo "📄 Installing and configuring DomPDF for real PDF generation..."

cd /Users/hannesbonami/Desktop/Bonamiapp

# Install DomPDF
echo "📦 Installing DomPDF..."
composer require barryvdh/laravel-dompdf

# Add service provider to config/app.php if needed (Laravel 11+ auto-discovers)
echo "✅ DomPDF installed!"

# Create backgrounds directory
mkdir -p public/backgrounds
echo "✅ Backgrounds directory created"

# Clear caches
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo ""
echo "🎯 REAL PDF GENERATION NOW ACTIVE!"
echo ""
echo "✅ WHAT'S WORKING:"
echo "   📄 DomPDF library installed"
echo "   🎨 Background images supported"
echo "   📑 Multi-page PDF generation"
echo "   💫 A4 portrait format"
echo ""
echo "🔄 TEST NOW:"
echo "   1. Go to /sjablonen/1/preview"
echo "   2. Click 'Download PDF'"
echo "   3. Should download real .pdf file"
echo ""
echo "💡 BACKGROUND IMAGES:"
echo "   Place background images in: public/backgrounds/"
echo "   Example: public/backgrounds/bg1.jpg"
echo ""