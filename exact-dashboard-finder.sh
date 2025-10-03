#!/bin/bash
# EXACT dashboard link finder and fixer

echo "🎯 EXACT DASHBOARD LINK ANALYSIS"
echo "================================="

cd /Users/hannesbonami/Herd/app/Bonamiapp

echo "📋 Step 1: Show entire app.blade.php content with line numbers"
echo "Looking for navigation/sidebar section..."

if [ -f "resources/views/layouts/app.blade.php" ]; then
    # Show lines around any dashboard references with more context
    echo "All lines with 'dashboard' and their context:"
    grep -n -C5 -i "dashboard" resources/views/layouts/app.blade.php
    
    echo ""
    echo "📋 Step 2: Looking for sidebar navigation specifically"
    # Look for common sidebar patterns
    grep -n -C10 -i "sidebar\|nav\|menu" resources/views/layouts/app.blade.php | head -50
    
else
    echo "❌ app.blade.php not found!"
    echo "Looking for other layout files:"
    find resources/views -name "*.blade.php" -exec grep -l -i "dashboard\|sidebar" {} \;
fi

echo ""
echo "📋 Step 3: Current route status"
php artisan route:list | grep dashboard

echo ""
echo "🎯 WHAT TO LOOK FOR:"
echo "- Find the sidebar navigation section"
echo "- Look for a link that says 'Dashboard' or similar"
echo "- See what route/URL it currently uses"
echo "- If it uses route('dashboard.old') or '/dashboard-oud', we need to change it"
echo "- If it already uses route('dashboard'), then the problem is elsewhere"