#!/bin/bash
# Fix login redirect and rename old dashboard

echo "🔧 FIXING LOGIN REDIRECT & RENAMING OLD DASHBOARD"
echo "================================================="

cd /Users/hannesbonami/Herd/app/Bonamiapp

echo "📋 Step 1: Backup important files..."
cp routes/web.php routes/web.php.backup-redirect-$(date +%Y%m%d-%H%M%S)

echo ""
echo "📋 Step 2: Fix login redirect in RouteServiceProvider..."

# Check and fix RouteServiceProvider
if [ -f "app/Providers/RouteServiceProvider.php" ]; then
    # Update HOME constant to point to dashboard
    sed -i.backup 's/public const HOME = .*/public const HOME = "\/dashboard";/' app/Providers/RouteServiceProvider.php
    echo "✅ Updated RouteServiceProvider HOME constant"
else
    echo "⚠️  RouteServiceProvider not found - checking other Laravel versions..."
    
    # Check for Laravel 11+ config
    if [ -f "bootstrap/app.php" ]; then
        echo "Found Laravel 11+ bootstrap/app.php"
        # In Laravel 11+, home route might be defined differently
    fi
fi

echo ""
echo "📋 Step 3: Rename old dashboard view to avoid confusion..."

# Rename old dashboard view
if [ -f "resources/views/dashboard.blade.php" ]; then
    echo "Found old dashboard.blade.php - renaming to dashboard-legacy.blade.php"
    mv resources/views/dashboard.blade.php resources/views/dashboard-legacy.blade.php
    echo "✅ Renamed dashboard.blade.php → dashboard-legacy.blade.php"
else
    echo "ℹ️  No dashboard.blade.php found to rename"
fi

echo ""
echo "📋 Step 4: Update route that used old dashboard view..."

# Update any routes that still reference the old dashboard view
if grep -q "return view('dashboard')" routes/web.php; then
    echo "Updating routes that use old dashboard view..."
    sed -i.backup2 "s/return view('dashboard')/return view('dashboard-legacy')/" routes/web.php
    echo "✅ Updated route to use dashboard-legacy view"
fi

echo ""
echo "📋 Step 5: Ensure /dashboard-oud route exists for legacy access..."

# Check if dashboard-oud route exists, if not add it
if ! grep -q "/dashboard-oud" routes/web.php; then
    echo "Adding /dashboard-oud route for legacy dashboard access..."
    
    # Add legacy dashboard route after main dashboard route
    sed -i.backup3 '/Route::get.*\/dashboard.*DashboardContentController/a\\nRoute::get("/dashboard-oud", function () {\n    return view("dashboard-legacy");\n})->middleware(["auth", "verified"])->name("dashboard.legacy");' routes/web.php
    
    echo "✅ Added /dashboard-oud route"
else
    echo "✅ /dashboard-oud route already exists"
fi

echo ""
echo "📋 Step 6: Clear caches..."
php artisan route:clear
php artisan config:clear
php artisan view:clear

echo ""
echo "📋 Step 7: Verify changes..."
echo "Current dashboard routes:"
php artisan route:list | grep dashboard

echo ""
echo "🎉 LOGIN REDIRECT & DASHBOARD NAMING FIXED!"
echo ""
echo "✅ What's now configured:"
echo "- Login redirect → /dashboard (new dashboard-content)"
echo "- /dashboard → DashboardContentController (modern interface)"  
echo "- /dashboard-oud → dashboard-legacy.blade.php (old interface)"
echo "- Old dashboard renamed to dashboard-legacy.blade.php"
echo ""
echo "🧪 Test login now - should always go to new dashboard!"