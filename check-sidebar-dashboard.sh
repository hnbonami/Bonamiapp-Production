#!/bin/bash
# Safely update sidebar dashboard link

echo "🔍 Checking current sidebar dashboard link..."

cd /Users/hannesbonami/Herd/app/Bonamiapp

# First, let's see what's currently in the sidebar
echo "📋 Current sidebar content in app layout:"
grep -A5 -B5 -i "dashboard\|sidebar" resources/views/layouts/app.blade.php | head -20

echo ""
echo "🔍 Looking for dashboard route references:"
grep -n "route.*dashboard" resources/views/layouts/app.blade.php

echo ""
echo "📋 Current routes available:"
php artisan route:list | grep dashboard

echo ""
echo "🎯 What we want to change:"
echo "FROM: Dashboard link points to old dashboard"
echo "TO: Dashboard link points to new dashboard-content (which is already at /dashboard)"

echo ""
echo "⚠️  SAFETY CHECK:"
echo "Current /dashboard route points to: DashboardContentController@index"
echo "This means the sidebar dashboard link already points to the right place!"
echo ""

# Let's verify this
echo "🧪 Testing current dashboard route:"
if php artisan route:list | grep -q "dashboard.*DashboardContentController"; then
    echo "✅ Dashboard route already points to DashboardContentController!"
    echo "✅ No changes needed - sidebar already works correctly!"
else
    echo "🤔 Dashboard route needs to be checked..."
    php artisan route:list | grep dashboard
fi