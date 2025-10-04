#!/bin/bash

echo "🔧 Adding preview route to web.php..."

cd /Users/hannesbonami/Desktop/Bonamiapp

# Add the preview route before the resource route
if ! grep -q "sjablonen.*preview" routes/web.php; then
    echo "📝 Adding preview route..."
    
    # Create a backup
    cp routes/web.php routes/web.php.backup
    
    # Add the preview route before the resource route
    sed -i '' '/Route::resource.*sjablonen/i\
    // Sjablonen preview route\
    Route::get('\''sjablonen/{id}/preview'\'', [\\App\\Http\\Controllers\\SjablonenController::class, '\''preview'\''])->name('\''sjablonen.preview'\'');\
' routes/web.php
    
    echo "✅ Preview route added successfully!"
else
    echo "✅ Preview route already exists!"
fi

echo "🎯 Preview functionality should now work!"