#!/bin/bash

echo "🔧 Quick fix for PDF route 404..."

cd /Users/hannesbonami/Desktop/Bonamiapp

# Clear all caches
php artisan route:clear
php artisan config:clear
php artisan cache:clear

# Add include to web.php if not exists
if ! grep -q "preview.php" routes/web.php; then
    echo "📝 Adding route include..."
    echo "" >> routes/web.php
    echo "// PDF and Preview routes" >> routes/web.php
    echo "include __DIR__ . '/preview.php';" >> routes/web.php
fi

echo "✅ Routes should now work!"
echo ""
echo "🎯 TEST THESE URLS:"
echo "   📄 Preview: /sjablonen/1/preview"
echo "   💾 PDF: /sjablonen/1/pdf"
echo ""
echo "🔄 If still 404, restart Laravel server:"
echo "   php artisan serve --port=8002"