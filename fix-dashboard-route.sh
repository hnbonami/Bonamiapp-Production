#!/bin/bash
# Fix the exact dashboard route issue

echo "🔧 FIXING THE DASHBOARD ROUTE PROBLEM"
echo "====================================="

cd /Users/hannesbonami/Herd/app/Bonamiapp

echo "📋 Current problematic route (lines 66-69):"
sed -n '66,69p' routes/web.php

echo ""
echo "🔧 Replacing the old dashboard route..."

# Use sed to replace the specific lines
sed -i.backup-fix '66,69c\
Route::get('"'"'/dashboard'"'"', [App\\Http\\Controllers\\DashboardContentController::class, '"'"'index'"'"'])->middleware(['"'"'auth'"'"', '"'"'verified'"'"'])->name('"'"'dashboard'"'"');' routes/web.php

if [ $? -eq 0 ]; then
    echo "✅ Route replacement successful!"
    
    echo ""
    echo "📋 New dashboard route:"
    sed -n '66,66p' routes/web.php
    
    echo ""
    echo "🧹 Clearing caches..."
    php artisan route:clear
    php artisan config:clear
    php artisan view:clear
    
    echo ""
    echo "📋 Verifying route fix:"
    php artisan route:list | grep dashboard
    
    echo ""
    echo "🎉 DASHBOARD ROUTE FIXED!"
    echo "Now the sidebar Dashboard button should go to DashboardContentController!"
    
else
    echo "❌ Route replacement failed"
    echo "Restoring backup..."
    mv routes/web.php.backup-fix routes/web.php
fi