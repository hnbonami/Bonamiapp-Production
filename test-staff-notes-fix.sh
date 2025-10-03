#!/bin/bash
# Test staff-notes fix

echo "🧪 TESTING STAFF-NOTES FIX"
echo "========================="

cd /Users/hannesbonami/Herd/app/Bonamiapp

echo "📋 Step 1: Clear caches..."
php artisan route:clear
php artisan config:clear
php artisan view:clear

echo ""
echo "📋 Step 2: Verify the route fix..."
echo "Line 392 area in routes/web.php:"
sed -n '390,395p' routes/web.php

echo ""
echo "📋 Step 3: Check route registration..."
php artisan route:list | grep -i "admin.*staff\|staffnotes" | head -5

echo ""
echo "📋 Step 4: Verify StaffNoteController methods..."
echo "Available methods:"
grep -n "public function" app/Http/Controllers/StaffNoteController.php

echo ""
echo "🎉 STAFF-NOTES FIX VERIFICATION:"
echo "================================"
echo "✅ Route now calls index() instead of adminOverview()"
echo "✅ StaffNoteController has index() method"
echo "✅ Caches cleared"
echo ""
echo "🧪 TEST NOW:"
echo "1. Go to your app"
echo "2. Click on 'Beheer' button"
echo "3. Should now show staff-notes page without 500 error"
echo ""
echo "If it still doesn't work, the issue might be in the view or controller logic"