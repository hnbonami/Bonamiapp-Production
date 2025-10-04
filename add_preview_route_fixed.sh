#!/bin/bash

echo "🔧 Adding preview route to web.php manually..."

cd /Users/hannesbonami/Desktop/Bonamiapp

# Check if routes/web.php exists and add preview route
if [ -f "routes/web.php" ]; then
    echo "📝 Adding preview route before resource routes..."
    
    # Create backup
    cp routes/web.php routes/web.php.backup
    
    # Find the line with sjablonen resource and add preview route before it
    # Using perl for cross-platform compatibility
    perl -i -pe 'if (/Route::resource.*sjablonen/ && !$seen++) { print "    // Sjablonen preview route\n    Route::get(\"sjablonen/{id}/preview\", [\\App\\Http\\Controllers\\SjablonenController::class, \"preview\"])->name(\"sjablonen.preview\");\n    \n" }' routes/web.php
    
    echo "✅ Preview route added!"
    
    # Verify it was added
    if grep -q "sjablonen.preview" routes/web.php; then
        echo "✅ Route verification successful!"
    else
        echo "❌ Route addition failed, trying alternative method..."
        
        # Alternative method: append before closing brace
        sed -i '' '/});$/i\
    Route::get("sjablonen/{id}/preview", [\\App\\Http\\Controllers\\SjablonenController::class, "preview"])->name("sjablonen.preview");
' routes/web.php
    fi
    
    # Show the relevant part of web.php
    echo "📋 Current routes around sjablonen:"
    grep -A 5 -B 5 "sjablonen" routes/web.php
    
else
    echo "❌ routes/web.php not found!"
fi

echo "🎯 Preview route should now be available!"