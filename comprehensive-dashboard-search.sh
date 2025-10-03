#!/bin/bash
# Find and fix dashboard link in sidebar

echo "🔍 COMPREHENSIVE SEARCH for dashboard links..."

cd /Users/hannesbonami/Herd/app/Bonamiapp

echo "📋 1. All files containing 'dashboard' in views:"
find resources/views -name "*.blade.php" -exec grep -l "dashboard" {} \;

echo ""
echo "📋 2. Specific content in app.blade.php:"
if [ -f "resources/views/layouts/app.blade.php" ]; then
    echo "Lines containing 'dashboard' in app.blade.php:"
    grep -n "dashboard" resources/views/layouts/app.blade.php
    
    echo ""
    echo "📋 3. Context around dashboard links:"
    grep -n -A3 -B3 "dashboard" resources/views/layouts/app.blade.php
else
    echo "❌ app.blade.php not found"
fi

echo ""
echo "📋 4. What routes exist:"
echo "Available dashboard routes:"
php artisan route:list | grep dashboard

echo ""
echo "📋 5. What we need to find and change:"
echo "FIND: Any link that goes to the OLD dashboard"
echo "CHANGE TO: Link that goes to NEW dashboard-content"
echo ""
echo "Based on our routes:"
echo "- route('dashboard') should go to DashboardContentController (NEW)"
echo "- route('dashboard.old') should go to old dashboard view"
echo ""
echo "So if sidebar uses route('dashboard'), it should already work!"
echo "Let's check what's actually happening..."

echo ""
echo "📋 6. Testing the routes:"
if php artisan route:list | grep -q "dashboard.*GET.*DashboardContentController"; then
    echo "✅ route('dashboard') points to DashboardContentController (NEW)"
else
    echo "❌ route('dashboard') does NOT point to DashboardContentController"
    echo "Current dashboard route:"
    php artisan route:list | grep "dashboard.*GET"
fi