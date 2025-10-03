#!/bin/bash
# Find sidebar dashboard link

echo "🔍 Searching for sidebar dashboard link..."

cd /Users/hannesbonami/Herd/app/Bonamiapp

echo "📋 Looking for sidebar in app.blade.php:"
if [ -f "resources/views/layouts/app.blade.php" ]; then
    # Search for dashboard links
    echo "Dashboard references in app.blade.php:"
    grep -n -i "dashboard" resources/views/layouts/app.blade.php
    
    echo ""
    echo "📋 Sidebar section with dashboard:"
    # Look for sidebar navigation specifically
    grep -n -A10 -B5 -i "sidebar\|navigation.*dashboard\|Dashboard.*href" resources/views/layouts/app.blade.php
else
    echo "❌ app.blade.php not found"
fi

echo ""
echo "📋 Checking if there are other layout files:"
find resources/views -name "*.blade.php" -exec grep -l -i "sidebar\|dashboard" {} \;

echo ""
echo "📋 Current routes to verify what we should point to:"
php artisan route:list | grep dashboard