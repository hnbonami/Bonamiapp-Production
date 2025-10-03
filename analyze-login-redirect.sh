#!/bin/bash
# Analyze login redirect and dashboard naming

echo "🔍 LOGIN REDIRECT & DASHBOARD ANALYSIS"
echo "======================================"

cd /Users/hannesbonami/Herd/app/Bonamiapp

echo "1️⃣ CHECKING LOGIN REDIRECT SETTINGS:"
echo "===================================="

# Check RouteServiceProvider for HOME constant
if [ -f "app/Providers/RouteServiceProvider.php" ]; then
    echo "RouteServiceProvider HOME constant:"
    grep -n "HOME" app/Providers/RouteServiceProvider.php
else
    echo "❌ RouteServiceProvider.php not found"
fi

# Check RedirectIfAuthenticated middleware
if [ -f "app/Http/Middleware/RedirectIfAuthenticated.php" ]; then
    echo ""
    echo "RedirectIfAuthenticated middleware:"
    grep -n -A5 -B5 "dashboard\|home" app/Http/Middleware/RedirectIfAuthenticated.php
else
    echo "❌ RedirectIfAuthenticated.php not found"
fi

echo ""
echo "2️⃣ CHECKING CURRENT DASHBOARD FILES:"
echo "==================================="
echo "Dashboard views that exist:"
find resources/views -name "*dashboard*" -type f | while read file; do
    echo "📄 $file"
done

echo ""
echo "3️⃣ CURRENT DASHBOARD ROUTES:"
echo "============================"
php artisan route:list | grep dashboard

echo ""
echo "4️⃣ CHECKING FOR OLD DASHBOARD REFERENCES:"
echo "========================================"
echo "Routes pointing to 'dashboard' view:"
grep -n "return view('dashboard')" routes/web.php

echo ""
echo "5️⃣ RECOMMENDATIONS:"
echo "=================="
echo "✅ Fix login redirect to always go to route('dashboard')"
echo "✅ Rename old dashboard view to avoid confusion"
echo "✅ Update any remaining references"