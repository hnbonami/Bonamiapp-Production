#!/bin/bash
# Detailed route and navigation analysis

echo "🔍 DETAILED ANALYSIS OF DASHBOARD ROUTING..."

cd /Users/hannesbonami/Herd/app/Bonamiapp

echo "1️⃣ CURRENT ROUTES:"
echo "==================="
php artisan route:list | grep -E "(dashboard|GET)"

echo ""
echo "2️⃣ WEB.PHP DASHBOARD ROUTES:"
echo "=============================="
grep -n -A2 -B2 "dashboard" routes/web.php

echo ""
echo "3️⃣ SIDEBAR NAVIGATION CONTENT:"
echo "==============================="
# Look for navigation in the layout
if [ -f "resources/views/layouts/app.blade.php" ]; then
    echo "Found app.blade.php - searching for dashboard links:"
    grep -n -A3 -B3 -i "dashboard\|route.*dashboard" resources/views/layouts/app.blade.php
else
    echo "❌ app.blade.php not found"
fi

echo ""
echo "4️⃣ ANALYSIS:"
echo "============"
echo "Based on our earlier work:"
echo "- /dashboard should point to DashboardContentController@index"
echo "- /dashboard-oud should point to old dashboard view"
echo "- Sidebar 'Dashboard' link should use route('dashboard')"
echo ""

if php artisan route:list | grep -q "dashboard.*GET.*DashboardContentController"; then
    echo "✅ /dashboard route correctly points to DashboardContentController"
    echo "✅ Sidebar dashboard link should already work correctly!"
    echo ""
    echo "🎯 CONCLUSION: No changes needed!"
    echo "The sidebar dashboard button already points to the new dashboard-content via route('dashboard')"
else
    echo "⚠️  Dashboard route needs attention"
    echo "Current dashboard route:"
    php artisan route:list | grep dashboard
fi