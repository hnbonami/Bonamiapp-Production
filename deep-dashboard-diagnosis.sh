#!/bin/bash
# DEEP DIAGNOSIS - Why is dashboard still showing old page?

echo "🕵️ DEEP DASHBOARD DIAGNOSIS"
echo "=========================="

cd /Users/hannesbonami/Herd/app/Bonamiapp

echo "1️⃣ CURRENT ROUTE CONFIGURATION:"
echo "================================"
php artisan route:list | grep -i dashboard

echo ""
echo "2️⃣ WEB.PHP DASHBOARD ROUTES:"
echo "============================"
echo "Lines containing 'dashboard' in routes/web.php:"
grep -n -A2 -B2 "dashboard" routes/web.php

echo ""
echo "3️⃣ CONTROLLER CHECK:"
echo "===================="
echo "Does DashboardContentController exist?"
ls -la app/Http/Controllers/DashboardContentController.php

echo ""
echo "4️⃣ VIEW FILES CHECK:"
echo "===================="
echo "Dashboard-related views:"
find resources/views -name "*dashboard*" -type f

echo ""
echo "5️⃣ CURRENT /dashboard ROUTE DETAILS:"
echo "====================================="
# Get exact route details
php artisan route:list --name=dashboard

echo ""
echo "6️⃣ TESTING ROUTE RESOLUTION:"
echo "============================="
# Test what route('dashboard') resolves to
php artisan tinker --execute="echo route('dashboard');"

echo ""
echo "7️⃣ PROBLEM DIAGNOSIS:"
echo "===================="
echo "If route('dashboard') points to old dashboard, then:"
echo "- Either routes are not in the right order"
echo "- Or cache is not cleared properly"
echo "- Or there's a duplicate route definition"

echo ""
echo "🔧 CLEARING EVERYTHING:"
echo "======================"
php artisan route:clear
php artisan config:clear
php artisan view:clear
php artisan cache:clear

echo ""
echo "📋 FINAL ROUTE CHECK:"
php artisan route:list | grep dashboard