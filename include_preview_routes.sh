#!/bin/bash

echo "🔧 Adding preview route include to web.php..."

cd /Users/hannesbonami/Desktop/Bonamiapp

# Add include for preview routes at the end of the auth middleware group
if [ -f "routes/web.php" ]; then
    echo "📝 Adding route include..."
    
    # Add include before the closing brace of the auth middleware group
    sed -i '' '/});$/i\
    // Preview routes\
    include __DIR__ . "/preview.php";
' routes/web.php
    
    echo "✅ Preview route include added!"
    
    # Clear route cache
    php artisan route:clear 2>/dev/null || true
    
    echo "🎯 Preview functionality should now work!"
    echo "📋 Try accessing /sjablonen/1/preview"
    
else
    echo "❌ routes/web.php not found!"
fi