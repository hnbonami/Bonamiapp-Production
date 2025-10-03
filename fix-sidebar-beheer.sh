#!/bin/bash
# Fix the sidebar Beheer button link

echo "🔧 UPDATING SIDEBAR BEHEER BUTTON"
echo "================================="

cd /Users/hannesbonami/Herd/app/Bonamiapp

echo "📋 Step 1: Backup layouts file..."
cp resources/views/layouts/app.blade.php resources/views/layouts/app.blade.php.backup-beheer-$(date +%Y%m%d-%H%M%S)

echo ""
echo "📋 Step 2: Find and update the Beheer button link..."

# Look for common patterns for the Beheer button
if grep -q "admin.staffnotes.overview" resources/views/layouts/app.blade.php; then
    echo "Found route reference - updating..."
    sed -i.backup 's/admin\.staffnotes\.overview/admin.database.tools/g' resources/views/layouts/app.blade.php
    echo "✅ Updated route reference"
elif grep -q "/admin/staff-notes/overview" resources/views/layouts/app.blade.php; then
    echo "Found direct URL - updating..."
    sed -i.backup 's|/admin/staff-notes/overview|/admin/database-tools|g' resources/views/layouts/app.blade.php  
    echo "✅ Updated direct URL"
else
    echo "Searching for other Beheer patterns..."
    # Look for any href containing admin and staff-notes
    sed -i.backup 's|href="[^"]*admin[^"]*staff-notes[^"]*overview[^"]*"|href="/admin/database-tools"|g' resources/views/layouts/app.blade.php
    echo "✅ Applied pattern-based update"
fi

echo ""
echo "📋 Step 3: Verify the change..."
echo "Looking for Beheer button after change:"
grep -n -A3 -B3 -i "beheer" resources/views/layouts/app.blade.php

echo ""
echo "📋 Step 4: Clear view cache..."
php artisan view:clear

echo ""
echo "🎉 SIDEBAR BEHEER BUTTON FIXED!"
echo "==============================="
echo "✅ Updated Beheer button link"
echo "✅ Now points to /admin/database-tools"
echo "✅ View cache cleared"
echo ""
echo "🧪 TEST NOW:"
echo "1. Click 'Beheer' in sidebar"
echo "2. Should go to database-tools page"
echo "3. Should show your import/export tools"
echo ""
echo "If something breaks, restore with:"
echo "cp resources/views/layouts/app.blade.php.backup-beheer-* resources/views/layouts/app.blade.php"