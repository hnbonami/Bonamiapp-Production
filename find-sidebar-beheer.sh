#!/bin/bash
# Fix sidebar Beheer button to point to working database-tools route

echo "🔧 FIXING SIDEBAR BEHEER BUTTON LINK"
echo "===================================="

cd /Users/hannesbonami/Herd/app/Bonamiapp

echo "📋 Step 1: Find the sidebar Beheer button..."

# Look for the Beheer button in the sidebar
echo "Searching for Beheer button in layouts..."
grep -n -r "Beheer" resources/views/layouts/ | head -5

echo ""
echo "Searching in app.blade.php..."
if [ -f "resources/views/layouts/app.blade.php" ]; then
    grep -n -A3 -B3 -i "beheer" resources/views/layouts/app.blade.php
else
    echo "app.blade.php not found"
fi

echo ""
echo "📋 Step 2: Current routes check..."
echo "Working route: /admin/database-tools"
echo "Old problematic route: /admin/staff-notes/overview"

php artisan route:list | grep -E "database-tools|admin.*staff"

echo ""
echo "📋 Step 3: Show the fix that needs to be applied..."
echo "Need to change sidebar Beheer link from:"
echo "  /admin/staff-notes/overview"  
echo "to:"
echo "  /admin/database-tools"
echo ""
echo "or from:"
echo "  route('admin.staffnotes.overview')"
echo "to:"  
echo "  route('admin.database.tools')"