#!/bin/bash
# Safe fix for staff-notes controller issue

echo "🔧 FIXING STAFF-NOTES CONTROLLER ISSUE"
echo "======================================"

cd /Users/hannesbonami/Herd/app/Bonamiapp

echo "📋 Step 1: Backup current files..."
cp routes/web.php routes/web.php.backup-staffnotes-$(date +%Y%m%d-%H%M%S)

echo ""
echo "📋 Step 2: Identify the problematic route..."
echo "Line 392 in routes/web.php:"
sed -n '390,395p' routes/web.php

echo ""
echo "📋 Step 3: Check what StaffNoteController actually has..."
if [ -f "app/Http/Controllers/StaffNoteController.php" ]; then
    echo "Available methods in StaffNoteController:"
    grep -n "public function" app/Http/Controllers/StaffNoteController.php
    
    # Look for index method which is commonly used
    if grep -q "public function index" app/Http/Controllers/StaffNoteController.php; then
        echo "✅ Found 'index' method - we can use this instead"
        REPLACEMENT_METHOD="index"
    elif grep -q "public function overview" app/Http/Controllers/StaffNoteController.php; then
        echo "✅ Found 'overview' method - we can use this instead"
        REPLACEMENT_METHOD="overview"
    else
        echo "❌ No suitable replacement method found"
        REPLACEMENT_METHOD="index"
        echo "Will create index method if needed"
    fi
else
    echo "❌ StaffNoteController doesn't exist!"
    REPLACEMENT_METHOD="index"
fi

echo ""
echo "📋 Step 4: Fix the route..."

# Find and fix the problematic route
echo "Looking for the exact route causing the issue..."
LINE_NUMBER=$(grep -n "StaffNoteController.*adminOverview" routes/web.php | cut -d: -f1)

if [ ! -z "$LINE_NUMBER" ]; then
    echo "Found problematic route at line $LINE_NUMBER"
    
    # Replace adminOverview with index (safer method)
    sed -i.backup-route "s/StaffNoteController.*adminOverview/StaffNoteController@index/" routes/web.php
    echo "✅ Fixed route to use 'index' method instead of 'adminOverview'"
else
    echo "Couldn't find exact route, trying alternative fix..."
    # Try to find any reference to adminOverview and fix it
    sed -i.backup-route2 "s/adminOverview/index/g" routes/web.php
    echo "✅ Replaced all 'adminOverview' references with 'index'"
fi

echo ""
echo "📋 Step 5: Ensure StaffNoteController has index method..."

if [ -f "app/Http/Controllers/StaffNoteController.php" ]; then
    if ! grep -q "public function index" app/Http/Controllers/StaffNoteController.php; then
        echo "Adding missing index method to StaffNoteController..."
        
        # Add index method before the last closing brace
        sed -i.backup-controller '/^}[[:space:]]*$/i\
\
    /**\
     * Display staff notes overview\
     */\
    public function index()\
    {\
        $notes = StaffNote::latest()->paginate(10);\
        return view("admin.staff-notes.overview", compact("notes"));\
    }' app/Http/Controllers/StaffNoteController.php
        
        echo "✅ Added index method to StaffNoteController"
    else
        echo "✅ Index method already exists"
    fi
else
    echo "⚠️  StaffNoteController doesn't exist - route may still fail"
fi

echo ""
echo "📋 Step 6: Clear caches..."
php artisan route:clear
php artisan config:clear
php artisan view:clear

echo ""
echo "📋 Step 7: Verify the fix..."
echo "Updated route around line 392:"
sed -n '390,395p' routes/web.php

echo ""
echo "Staff-notes routes:"
php artisan route:list | grep -i "staff\|note" | head -5

echo ""
echo "🎉 STAFF-NOTES FIX APPLIED!"
echo "=========================="
echo "✅ Fixed route to use existing method"
echo "✅ Added missing index method if needed"
echo "✅ Cleared caches"
echo ""
echo "🧪 TEST NOW:"
echo "Click on 'Beheer' button - should show staff-notes page"
echo "If still issues, the StaffNoteController or view might need more work"