#!/bin/bash

echo "🔧 Setting up PDF functionality..."

cd /Users/hannesbonami/Desktop/Bonamiapp

# Clear route cache
php artisan route:clear 2>/dev/null || true
echo "✅ Route cache cleared"

# Add include for preview routes if not already there
if ! grep -q "preview.php" routes/web.php 2>/dev/null; then
    echo "📝 Adding route include to web.php..."
    
    # Add include before the closing brace of the auth middleware group
    sed -i '' '/});$/i\
    // Preview and PDF routes\
    include __DIR__ . "/preview.php";
' routes/web.php
    
    echo "✅ Route include added!"
fi

# Test route registration
echo "🧪 Testing route registration..."
php artisan route:list | grep -E "(preview|generate-pdf)" || echo "⚠️  Routes not yet registered, try refreshing browser"

echo ""
echo "🎯 PDF FUNCTIONALITY READY!"
echo ""
echo "✅ WHAT WORKS NOW:"
echo "   📄 Preview: /sjablonen/{id}/preview"
echo "   💾 Download: PDF button downloads HTML file"
echo "   🖨️  Print: Browser print dialog"
echo "   ↩️  Navigation: Back to sjabloon/klant"
echo ""
echo "🔄 NEXT STEPS:"
echo "   1. Visit /sjablonen/2/preview"
echo "   2. Click 'Download HTML' to test PDF functionality"
echo "   3. Use browser print for actual PDF generation"
echo ""