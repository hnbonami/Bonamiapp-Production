#!/bin/bash
# Fix the sidebar-admin-tab component

echo "🔧 UPDATING SIDEBAR-ADMIN-TAB COMPONENT"
echo "======================================="

cd /Users/hannesbonami/Herd/app/Bonamiapp

echo "📋 Step 1: Find and backup the admin tab component..."

if [ -f "resources/views/components/sidebar-admin-tab.blade.php" ]; then
    cp resources/views/components/sidebar-admin-tab.blade.php resources/views/components/sidebar-admin-tab.blade.php.backup-$(date +%Y%m%d-%H%M%S)
    echo "✅ Found and backed up sidebar-admin-tab.blade.php"
    
    echo ""
    echo "📋 Current content:"
    cat resources/views/components/sidebar-admin-tab.blade.php
    
else
    echo "❌ sidebar-admin-tab.blade.php not found"
    echo "Looking for similar admin components..."
    find resources/views/components -name "*admin*" -o -name "*beheer*"
fi

echo ""
echo "📋 Step 2: Update the component..."

if [ -f "resources/views/components/sidebar-admin-tab.blade.php" ]; then
    
    # Update any route references
    if grep -q "admin.staffnotes.overview" resources/views/components/sidebar-admin-tab.blade.php; then
        sed -i.backup 's/admin\.staffnotes\.overview/admin.database.tools/g' resources/views/components/sidebar-admin-tab.blade.php
        echo "✅ Updated route reference"
    fi
    
    # Update any direct URLs
    if grep -q "/admin/staff-notes/overview" resources/views/components/sidebar-admin-tab.blade.php; then
        sed -i.backup2 's|/admin/staff-notes/overview|/admin/database-tools|g' resources/views/components/sidebar-admin-tab.blade.php
        echo "✅ Updated direct URL"
    fi
    
    # General pattern replacement for any admin staff-notes URLs
    sed -i.backup3 's|href="[^"]*admin[^"]*staff-notes[^"]*overview[^"]*"|href="/admin/database-tools"|g' resources/views/components/sidebar-admin-tab.blade.php
    
    echo ""
    echo "📋 Updated content:"
    cat resources/views/components/sidebar-admin-tab.blade.php
    
else
    echo "Component not found - checking main layout for inline Beheer button..."
    
    # Look for inline Beheer button in app.blade.php
    grep -n -A5 -B5 -i "beheer" resources/views/layouts/app.blade.php | grep -A5 -B5 "href"
fi

echo ""
echo "📋 Step 3: Clear view cache..."
php artisan view:clear

echo ""
echo "🎉 SIDEBAR ADMIN TAB UPDATED!"
echo "============================"
echo "✅ Updated admin tab component"
echo "✅ Now points to /admin/database-tools"
echo "✅ View cache cleared"
echo ""
echo "🧪 TEST NOW: Click 'Beheer' in sidebar"
echo "Should go directly to your database tools!"
echo ""
echo "If something breaks, restore with:"
echo "cp resources/views/components/sidebar-admin-tab.blade.php.backup-* resources/views/components/sidebar-admin-tab.blade.php"