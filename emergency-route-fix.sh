#!/bin/bash
# EMERGENCY ROUTE FIX

echo "🚨 EMERGENCY DASHBOARD ROUTE FIX"
echo "================================"

cd /Users/hannesbonami/Herd/app/Bonamiapp

echo "📋 Backing up current routes..."
cp routes/web.php routes/web.php.backup-$(date +%Y%m%d-%H%M%S)

echo ""
echo "🔧 Checking current web.php dashboard routes:"
grep -n "dashboard" routes/web.php

echo ""
echo "⚠️  POTENTIAL ISSUE:"
echo "The route order might be wrong. Let me check if there are multiple dashboard routes..."

# Count dashboard routes
DASHBOARD_COUNT=$(grep -c "'/dashboard'" routes/web.php)
echo "Found $DASHBOARD_COUNT routes with '/dashboard'"

if [ $DASHBOARD_COUNT -gt 1 ]; then
    echo "❌ PROBLEM FOUND: Multiple dashboard routes!"
    echo "This could cause conflicts. Showing all dashboard routes:"
    grep -n "'/dashboard'" routes/web.php
    
    echo ""
    echo "🔧 SOLUTION: The first matching route wins in Laravel."
    echo "We need to ensure DashboardContentController route comes first."
fi

echo ""
echo "📋 Current route order should be:"
echo "1. Route::get('/dashboard', [DashboardContentController::class, 'index'])"
echo "2. Route::get('/dashboard-oud', function () { return view('dashboard'); })"

# Check if routes are in correct order
echo ""
echo "🔍 Checking route order in web.php..."
head -50 routes/web.php | grep -n -A1 -B1 dashboard